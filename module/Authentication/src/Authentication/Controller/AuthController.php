<?php
namespace Authentication\Controller;

use Application\Document\Samsarole;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Application\Service\UserService;



/**
 * Description of AuthController
 *
 * @author mihai.coditoiu
 */
class AuthController extends AbstractActionController
{

    public function indexAction()
    {
        $this->layout('layout/layoutLogin');
        $auth = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
        if ($auth->hasIdentity()) {
            $identity = $auth->getIdentity();
            return $this->redirect()->toRoute('home');
        }
        $form = $this->getServiceLocator()->get('Authentication\Forms\LoginForm');
        return new ViewModel(array(
            'form' => $form,
            'flashMessages' => $this->flashMessenger()
        ));
    }

    public function samenbeterAction()
    {

        $organization = $this->params()->fromRoute('id', '');
        if ($organization !== '') {
            $dm = $this->getServiceLocator()->get('doctrine.documentmanager.odm_default');
            $organizationObj = $dm->getRepository("\\Application\\Document\\Organization")->findOneBy(array(
                "dbname" => $organization
            ));
        } else {
            $this->flashMessenger()->addErrorMessage('No organization added!');
            return $this->redirect()->toRoute('auth', array(
                'action' => 'index'
            ));
        }
        //\Doctrine\Common\Util\Debug::dump($organizationObj);exit;
        if ($organizationObj == null) {
            $this->flashMessenger()->addErrorMessage('Wrong Organization!');
            return $this->redirect()->toRoute('auth', array(
                'action' => 'index'
            ));
        }
        $this->layout('layout/layoutLogin');
        $auth = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
        if ($auth->hasIdentity()) {
            $identity = $auth->getIdentity();
            return $this->redirect()->toRoute('home');
        }

        $form = $this->getServiceLocator()->get('Authentication\Forms\LoginForm');
        return new ViewModel(array(
            'form' => $form,
            'flashMessages' => $this->flashMessenger(),
            'organizationObj' => $organizationObj
        ));
    }

    public function signupAction() {
        $this->layout('layout/layoutLogin');
        $organization = $this->params()->fromRoute('id', '');
        if ($organization !== '') {
            $dm = $this->getServiceLocator()->get('doctrine.documentmanager.odm_default');
            $organizationObj = $dm->getRepository("\\Application\\Document\\Organization")->findOneBy(array(
                "dbname" => $organization
            ));
        } else {
            $this->flashMessenger()->addErrorMessage('No organization selected!');
            return $this->redirect()->toRoute('auth', array(
                'action' => 'index'
            ));
        }

        if ($organizationObj == null) {
            $this->flashMessenger()->addErrorMessage('Wrong Organization!');
            return $this->redirect()->toRoute('auth', array(
                'action' => 'samenbeter', 'id' => $organization
            ));
        }

        return new ViewModel(array(
            'flashMessages' => $this->flashMessenger(),
            'organization' => $organizationObj
        ));
    }

    public function createUserAction() {
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $data = $request->getPost()->toArray();
                $userService = new UserService();
                $result = $userService->signUpUserByEmail($data, $data['organization']);
                $this->flashMessenger()->addSuccessMessage($result);
                return $this->redirect()->toRoute('auth', array(
                    'action' => 'samenbeter', 'id' => $data['organization']
                ));
            }

            return $this->redirect()->toRoute('auth', array(
                'action' => 'samenbeter'
            ));
        } catch (\Exception $e) {
            $this->flashMessenger()->addErrorMessage('An error has occurred!');
            return $this->redirect()->toRoute('user', array(
                'action' => 'samenbeter', 'id' => $data['organization']
            ));
        }
    }

    /**
     *
     * @author Mihai Coditoiu <mihai.coditoiu@arobs.com>
     */
    public function loginAction()
    {
        $request = $this->getRequest();
        $dm = $this->getServiceLocator()->get('doctrine.documentmanager.odm_default');
        if ($this->getRequest()->getPost('api')) {
            if ($request->isPost()) {
          
                $authService = $this->getServiceLocator()->get('doctrine.authenticationservice.odm_default');
                $adapter = $authService->getAdapter();
                $adapter->setIdentityValue($this->getRequest()
                    ->getPost('UserName')); // i am using email
                $adapter->setCredentialValue($this->getRequest()
                    ->getPost('Password'));
                $data = array();
                $authResult = $authService->authenticate();
                if ($authResult->isValid()) {
                    $auth = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
                    $identity = $auth->getIdentity();
                    $user = $dm->getRepository("\\Application\\Document\\User")->findOneBy(array(
                        "id" => $identity['id']
                    ));
                    $user = $this->setOrganizationOnUserAndSessionData($user, $data);

                    // \Doctrine\Common\Util\Debug::dump($user->getOrganization()->getActiveWorkspace()->getId());
                    $userToken = "qwertyuiopasdgjklzxcvbnm123456789";
                    $data = array(
                        'email' => $user->getEmail(),
                        'last_name' => $user->getLastName(),
                        'name' => $user->getName(),
                        'role' => $user->getUserRole()->getRole(),
                        'organization' => $user->getOrganization()->getClasspath(),
                        'organizationId' => $user->getOrganization()->getId(),
                        'workspaceId' => $user->getOrganization()
                            ->getActiveWorkspace()
                            ->getId(),
                        'Success' => true,
                        'token' => "qwertyuiopasdgjklzxcvbnm123456789"
                    );
                    
                    return new JsonModel($data);
                } else {
                    return new JsonModel(array(
                        'code' => 403,
                        'mgs' => 'wrong username or password'
                    ));
                }
            }
        } else {
            $loginForm = $this->getServiceLocator()->get('Authentication\Forms\LoginForm');
            $loginForm->setData($request->getPost());
            if ($loginForm->isValid()) {
                $data = $loginForm->getData();
                $authService = $this->getServiceLocator()->get('doctrine.authenticationservice.odm_default');
                
                $adapter = $authService->getAdapter();
                $adapter->setIdentityValue($data['email']); // i am using email
                $adapter->setCredentialValue($data['password']);
                
                $authResult = $authService->authenticate();
                if ($authResult->isValid()) {
                    $auth = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
                    $identity = $auth->getIdentity();
                    
                    $user = $dm->getRepository("\\Application\\Document\\User")->findOneBy(array(
                        "id" => $identity['id']
                    ));

                    if (!$this->checkUserBelongToOrganization($data, $user)) {
                        $auth->clearIdentity();
                        $this->flashMessenger()->addErrorMessage('This user does not belong to this Organization!');
                        return $this->redirect()->toRoute('auth', array(
                            'action' => 'samenbeter', 'id' => $data['organization']
                        ));
                    }
                    $this->setOrganizationOnUserAndSessionData($user, $data);
                    $this->flashMessenger()->addSuccessMessage('Successfully log in!');
                    if ($user->getUserRole()->getRole() == \Application\Document\User::SUPER_ADMIN) {
                        return $this->redirect()->toRoute('user', array(
                            'action' => 'list'
                        ));
                    } else {
                        return $this->redirect()->toRoute('home', array(
                            'action' => 'view'
                        ));
                    }
                }

                $this->flashMessenger()->addErrorMessage('Username or password are wrong!');

                if (isset($data['organization'])) {
                    return $this->redirect()->toRoute('auth', array(
                        'action' => 'samenbeter', 'id' => $data['organization']
                    ));
                } else {
                    return $this->redirect()->toRoute('auth', array(
                        'action' => 'index'
                    ));
                }
            } else {
                $this->flashMessenger()->addErrorMessage('An error has occurred!');
                return $this->redirect()->toRoute('auth', array(
                    'action' => 'index'
                ));
            }
        }
    }

    public function successAction()
    {
        $auth = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
        if ($auth->hasIdentity()) {
            $identity = $auth->getIdentity();
        } else {
            return $this->redirect()->toRoute('auth', array(
                'action' => 'index'
            ));
        }
        return new ViewModel();
    }

    public function logoutAction()
    {
        $auth = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
        $auth->clearIdentity();
        $session = new Container('login');
        $session->getManager()->destroy();
        return $this->redirect()->toRoute('auth', array(
            'action' => 'index'
        ));
    }

    private function checkUserBelongToOrganization($data, $user) {
        if (isset($data['organization'])) {
            $dm = $this->getServiceLocator()->get('doctrine.documentmanager.odm_default');
            $orgObj = $dm->getRepository("\\Application\\Document\\Organization")->findOneBy(array(
                "dbname" => $data['organization']
            ));
            $organizations = json_decode($user->getOrganizationList(), true);
            foreach($organizations as $org) {
                if ($orgObj->getId() === $org['organization']) {
                    return true;
                }
            }
            return false;
        }

        return true;
    }

    private function setOrganizationOnUserAndSessionData($user, $data) {
        $dm = $this->getServiceLocator()->get('doctrine.documentmanager.odm_default');
        $organizations = json_decode($user->getOrganizationList(), true);
        $samsaRoleId = null;
        if (isset($data['organization'])) {
            $orgObj = $dm->getRepository("\\Application\\Document\\Organization")->findOneBy(array(
                "dbname" => $data['organization']
            ));
            foreach($organizations as $org) {
                if ($org['organization'] === $orgObj->getId()) {
                    $orgObjSelected = $dm->getRepository("\\Application\\Document\\Organization")->findOneBy(array(
                        "id" => $orgObj->getId()
                    ));
                    $organization = $orgObjSelected;
                    $samsaRoleId = $org['samsarole'];
                }
            }

        } else {
            $orgObj = $dm->getRepository("\\Application\\Document\\Organization")->findOneBy(array(
                "id" => $organizations[0]['organization']
            ));
            $organization = $orgObj;
            $samsaRoleId = $organizations[0]['samsarole'];
        }


        if ($samsaRoleId) {
            $_SESSION['organization'] = $organization->getClasspath();
            $_SESSION['dbname'] = $organization->getDbname();
            $mongoObjectFactory = new \Application\Controller\MongoObjectFactory();
            $userOb = $mongoObjectFactory->findObject('User', (string)$user->getId());
            $reference = array();
            $reference['$ref'] = "samsaroles";
            $reference['$id'] = $samsaRoleId;
            $userOb->addMasterReferenceObject("samsaroles", $reference);
            unset($_SESSION['organization']);
            unset($_SESSION['dbname']);

        }

        $user->setOrganization($organization);
        $dm->persist($user);
        $dm->flush();

        //create a storage - login
        $session = new Container('login');

        $dbName = $organization->getDbname();

        if(isset($dbName)){
            // set something in session
            $session->offsetSet('dbName', $dbName);
        }

        return $user;
    }
}
