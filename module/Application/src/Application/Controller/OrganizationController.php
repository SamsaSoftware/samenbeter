<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

class OrganizationController extends AbstractActionController
{

    public function listAction()
    {
        $dm = $this->getServiceLocator()->get('doctrine.documentmanager.odm_default');
        $organizations = $dm->getRepository("\\Application\\Document\\Organization")->findBy(array(), array('name' => 'ASC'));

        return new ViewModel(array(
            'flashMessages' => $this->flashMessenger(),
            'organizations' => $organizations
        ));
    }

    public function addAction()
    {
        $serviceLocator = $this->getServiceLocator();
        $form = $serviceLocator->get('\Application\Form\AddOrganization');
        
        $id = $this->params()->fromRoute('id', 0);
        $dm = $this->getServiceLocator()->get('doctrine.documentmanager.odm_default');
        $organization = $dm->getRepository("\\Application\\Document\\Organization")->findOneBy(array(
            "id" => $id
        ));
        $templates = $dm->getRepository("\\Application\\Document\\ModelTemplate")->findAll();
        $templatesArray = array();
        foreach($templates as $template) {
            $templatesArray[$template->getClassPath()] = $template->getName();
        }
        $form->get('classpath')->setValueOptions($templatesArray);

        if ($organization != null) {
            $form->get('name')->setValue($organization->getName());
            $form->get('id')->setValue($organization->getId());
            $form->get('classpath')->setValue($organization->getClassPath());
            $form->get('deleted')->setValue($organization->getDeleted());
            $form->get('locale')->setValue($organization->getLocale());
            $form->get('email')->setValue($organization->getEmail());
            $form->get('description')->setValue($organization->getDescription());
        }
        
        return new ViewModel(array(
            'form' => $form,
            'organization' => $organization,
            'flashMessages' => $this->flashMessenger()
        ));
    }

    public function saveAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            try {
                
                $data = $request->getPost()->toArray();
                $dm = $this->getServiceLocator()->get('doctrine.documentmanager.odm_default');
                $addNewOrg = true;
                if (isset($data['id']) && ! empty($data['id'])) {
                    $organization = $dm->getRepository("\\Application\\Document\\Organization")->findOneBy(array(
                        "id" => $data['id']
                    ));
                    $addNewOrg = false;
                } else {
                    $organization = new \Application\Document\Organization();
                    $this->createLanguageFilesForOrganization($data['classpath']);
                }
                if ($addNewOrg) {
                    $dbName = $this->createDbName($data);
                    $organization->setOrganizationDbNumber($dbName['organizationDbNumber']);
                    $organization->setDbname($dbName['dbname']);
                }
                $organization->setName($data['name']);
                $organization->setClasspath($data['classpath']);
                $organization->setLocale($data['locale']);
                $organization->setEmail($data['email']);
                $organization->setDeleted($data['deleted']);
                $organization->setDescription($data['description']);

                $dm->persist($organization);
                $dm->flush();
                
                $adapter = new \Zend\File\Transfer\Adapter\Http();
                $urlDestination = getcwd() . '/public/img/organizations/' . $organization->getId();
                
                // make a folder with hotel id if doesn't exist one
                if (! file_exists(realpath($urlDestination))) {
                    mkdir($urlDestination, 0777, true);
                }
                
                $urlDestination = realpath($urlDestination);
                $adapter->setDestination($urlDestination);
                foreach ($adapter->getFileInfo() as $file => $info) {
                    $fileName = uniqid() . '_' . $info['name'];
                    $data['name'] = $fileName;
                    $imageDefault = $urlDestination . '/' . $fileName;
                    $adapter->addFilter(new \Zend\Filter\File\Rename(array(
                        'target' => $imageDefault,
                        'overwrite' => true
                    )), null, $file);
                    
                    if ($adapter->receive($info['name'])) {
                        // in $file we remember if an image is new or if an image must change
                        $data['logo'] = 'img/organizations/' . $organization->getId() . '/' . $fileName;
                        if ($organization->getLogo() != '') {
                            unlink(getcwd() . '/public/' . $organization->getLogo());
                        }
                        $organization->setLogo($data['logo']);
                    }
                    $dm->persist($organization);
                    $dm->flush();
                }
                // if there is a new organization
                if (empty($data['id'])) {
                    // /set in sestion the class path in order to create a database
                    $_SESSION['organization'] = $organization->getClasspath();
                    $_SESSION['dbname'] = $organization->getDbname();
                    $mongoFactory = new MongoObjectFactory();
                    // get organization with data
                    $organizationObjectInstance = $mongoFactory->findObject('Organization', $organization->getId());
                    $samsa = $mongoFactory->getSamsa();
                    if ($organizationObjectInstance->name != "samsa") {
                        $samsa->addChild($organizationObjectInstance, "organizations");
                    }
                    // create a new workspace in the organization database
                    $typeWorkspace = 'Workspace';
                    $dataWorkspace = array(
                        "active" => 'true',
                        "name" => 'Workspace_' . uniqid()
                    );
                    $mObj = new MongoObjectFactory();
                    
                    // set the workspace to the organization
                    $returnW = $organizationObjectInstance->add($typeWorkspace, $dataWorkspace);
                    
                    // initiate the workspace
                    $workSpace = $mongoFactory->findObject($typeWorkspace, $returnW);
                    $workSpace->initiate();
                    if ($organizationObjectInstance->name == "Samsa") {
                        $workSpace->initiateSamsaUI();
                    }
                    // remove classpath from session because of new organizations
                    unset($_SESSION['organization']);
                }
                $this->flashMessenger()->addSuccessMessage('An organization was successfully created !');
                return $this->redirect()->toRoute('organization', array(
                    'action' => 'list'
                ));
            } catch (\Exception $e) {
                $this->flashMessenger()->addErrorMessage('An error has occurred!');
                $this->redirect()->toRoute('organization', array(
                    'action' => 'add'
                ));
            }
        }
    }

    public function deleteAction()
    {
        try {
            
            $id = $this->params()->fromRoute('id', 0);
            $dm = $this->getServiceLocator()->get('doctrine.documentmanager.odm_default');
            $organization = $dm->getRepository("\\Application\\Document\\Organization")->findOneBy(array(
                "id" => $id
            ));
            
            if ($organization != null) {
                if ($organization->getDeleted() == 0) {
                    $organization->setDeleted(1);
                } else {
                    $organization->setDeleted(0);
                }
                $dm->persist($organization);
                $dm->flush();
                $this->flashMessenger()->addSuccessMessage('The organization status was successfully changed !');
            } else {
                $this->flashMessenger()->addSuccessMessage('The organization do not exist !');
            }
            return $this->redirect()->toRoute('organization', array(
                'action' => 'list'
            ));
        } catch (\Exception $e) {
            $this->flashMessenger()->addErrorMessage('An error has occurred!');
            $this->redirect()->toRoute('organization', array(
                'action' => 'list'
            ));
        }
    }

    private function createDbName($data) {
        $dm = $this->getServiceLocator()->get('doctrine.documentmanager.odm_default');
        $result['dbname'] = $data['classpath'];
        $result['organizationDbNumber'] = 0;
        $organizations = $dm->getRepository("\\Application\\Document\\Organization")->findBy(array(
            "classpath" => $data['classpath']
        ), array('organizationDbNumber' => 'DESC'));
        if (!empty($organizations)) {
            $organization = reset($organizations);
            $result['dbname'] = $data['classpath'].($organization->getOrganizationDbNumber() + 1);
            $result['organizationDbNumber'] = ($organization->getOrganizationDbNumber() + 1);
        }

        return $result;
    }

    private function createLanguageFilesForOrganization($organizationName) {

            $directoryLanguage = getcwd() . '/lang';
            $files = scandir($directoryLanguage);
            foreach ($files as $file) {
                if (is_file($directoryLanguage . '/' . $file)) {
                    $splitFile = explode('_', $file);
                    if (count($splitFile) === 2) {
                        $newFileName = $directoryLanguage . '/' . $splitFile[0] . '_' . strtoupper($splitFile[0]) . '_' . $organizationName . '.php';
                        if (!file_exists($newFileName)) {
                            copy($directoryLanguage . '/' . $file, $newFileName);
                            chmod($newFileName, 0777);
                        }
                    }
                }
            }
        return true;
    }
}