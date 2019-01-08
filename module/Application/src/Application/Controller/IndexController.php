<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Application\Controller;

use Application\DatabaseConnection\Database;
use Application\Document\Setting;
use Application\Service\Service;
use Application\Service\ReportingService;
use Application\Document\Field;
use Application\Document\State;
use Application\Service\StateService;
use Dompdf\Dompdf;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Application\Controller\MongoObjectFactory;
use Swagger\Annotations as SWG;
use Zend\Session\Container;

/**
 * @SWG\Resource(
 * apiVersion="1.0.0",
 * swaggerVersion="1.2",
 * basePath="http://localhost/ZendSkeletonApplication/public",
 * resourcePath="/index",
 * description="Project Listing",
 * produces="['application/json']"
 * )
 */
class IndexController extends AbstractActionController
{

    public function indexAction()
    {
        try {
            
            $dm = $this->getServiceLocator()->get('doctrine.documentmanager.odm_default');
            $session = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
            
            $identity = $session->getIdentity();
            $user = $dm->getRepository("\\Application\\Document\\User")->findOneBy(array(
                "id" => $identity['id']
            ));
            
            $organization = $user->getOrganization();
            
            // $session->write($user->getOrganization()->getWorkspace());
            
            // print_r($user->getOrganization()->getWorkspace());exit;
            /*
             * $organizatie1 = $dm->getRepository('Application\Document\Organization')->findOneBy(array('classpath' => 'Autoplan'));
             * $organizatie2 = $dm->getRepository('Application\Document\Organization')->findOneBy(array('classpath' => 'Elsig'));
             * //\Doctrine\Common\Util\Debug::dump($organizatie2);
             *
             * $user->setName(rand(1,100));
             * $user->addOrganization($organizatie1);
             * $user->addOrganization($organizatie2);
             * \Doctrine\Common\Util\Debug::dump($user);
             * $dm->persist($user);
             * $dm->flush();
             *
             *
             * $user1 = $dm->getRepository("\\Application\\Document\\User")->findOneBy(array(
             * "email" => 'admin@autoplan.com'
             * ));
             * //\Doctrine\Common\Util\Debug::dump($user1);
             */
        } catch (\Exception $e) {
            // print_r($e->getMessage());
            exit();
        }
        return new ViewModel(array(
            'flashMessages' => array(),
            'organization' => $organization
        ));
    }

    public function listOwningReferenceAction()
    {
        $refParam = array();
        try {
            $page = $this->params()->fromRoute('id', '');
            $refParam = explode(".", $page);
            $viewId = $this->params()->fromQuery('viewId');
            $objectId = $this->params()->fromQuery('objectId');
            $parentType = $this->params()->fromQuery('parentType');
            $parentId = $this->params()->fromQuery('parentId');
        } catch (\Exception $e) {
            print_r($e->getMessage());
            exit();
        }
        
        // in your constructor
        $this->_view = new ViewModel();
        $layout = $this->params()->fromQuery('layout');
        // in your initialize values method
        $this->_view->setVariable("layout", $layout);
        // in your initialize values method
        $this->_view->setVariable("id", $refParam[0]);
        // in your initialize values method
        $this->_view->setVariable("idRef", $refParam[1]);
        // in your initialize values method
        $this->_view->setVariable("idRefRef", $refParam[2]);
        // in your initialize values method
        $this->_view->setVariable("viewId", $viewId);
        // in your initialize values method
        $this->_view->setVariable("objectId", $objectId);
        // in your initialize values method
        $this->_view->setVariable("parentType", $parentType);
        // in your initialize values method
        $this->_view->setVariable("parentId", $parentId);
        $this->_view->setTerminal(true);
        // in your controller action
        return $this->_view;
    }

    public function listOwningDoubleReferenceAction()
    {
        $refParam = array();
        try {
            $page = $this->params()->fromRoute('id', '');
            $refParam = explode(".", $page);
            $viewId = $this->params()->fromQuery('viewId');
            $objectId = $this->params()->fromQuery('objectId');
            $parentType = $this->params()->fromQuery('parentType');
            $parentId = $this->params()->fromQuery('parentId');
        } catch (\Exception $e) {
            print_r($e->getMessage());
            exit();
        }
        
        // in your constructor
        $this->_view = new ViewModel();
        $layout = $this->params()->fromQuery('layout');
        // in your initialize values method
        $this->_view->setVariable("layout", $layout);
        // in your initialize values method
        $this->_view->setVariable("id", $refParam[0]);
        // in your initialize values method
        $this->_view->setVariable("idRef", $refParam[1]);
        // in your initialize values method
        $this->_view->setVariable("idRef2", $refParam[2]);
        // in your initialize values method
        $this->_view->setVariable("idRef3", $refParam[3]);
        // in your initialize values method
        $this->_view->setVariable("viewId", $viewId);
        // in your initialize values method
        $this->_view->setVariable("objectId", $objectId);
        // in your initialize values method
        $this->_view->setVariable("parentType", $parentType);
        // in your initialize values method
        $this->_view->setVariable("parentId", $parentId);
        $this->_view->setTerminal(true);
        // in your controller action
        return $this->_view;
    }

    public function listOwningSixthReferenceAction()
    {
        $refParam = array();
        try {
            $page = $this->params()->fromRoute('id', '');
            $refParam = explode(".", $page);
            $viewId = $this->params()->fromQuery('viewId');
            $objectId = $this->params()->fromQuery('objectId');
            $parentType = $this->params()->fromQuery('parentType');
            $parentId = $this->params()->fromQuery('parentId');
        } catch (\Exception $e) {
            print_r($e->getMessage());
            exit();
        }
        
        // in your constructor
        $this->_view = new ViewModel();
        $layout = $this->params()->fromQuery('layout');
        // in your initialize values method
        $this->_view->setVariable("layout", $layout);
        // in your initialize values method
        $this->_view->setVariable("id", $refParam[0]);
        // in your initialize values method
        $this->_view->setVariable("idRef", $refParam[1]);
        // in your initialize values method
        $this->_view->setVariable("idRef2", $refParam[2]);
        // in your initialize values method
        $this->_view->setVariable("idRef3", $refParam[3]);
        // in your initialize values method
        $this->_view->setVariable("idRef4", $refParam[4]);
        // in your initialize values method
        $this->_view->setVariable("idRef5", $refParam[5]);
        // in your initialize values method
        $this->_view->setVariable("viewId", $viewId);
        // in your initialize values method
        $this->_view->setVariable("objectId", $objectId);
        // in your initialize values method
        $this->_view->setVariable("parentType", $parentType);
        // in your initialize values method
        $this->_view->setVariable("parentId", $parentId);
        $this->_view->setTerminal(true);
        // in your controller action
        return $this->_view;
    }

    public function listReferenceAction()
    {
        $refParam = array();
        try {
            $page = $this->params()->fromRoute('id', '');
            $refParam = explode(".", $page);
            $viewId = $this->params()->fromQuery('viewId');
            $objectId = $this->params()->fromQuery('objectId');
            $parentType = $this->params()->fromQuery('parentType');
            $parentId = $this->params()->fromQuery('parentId');
        } catch (\Exception $e) {
            print_r($e->getMessage());
            exit();
        }
        // in your constructor
        $this->_view = new ViewModel();
        $layout = $this->params()->fromQuery('layout');
        // in your initialize values method
        $this->_view->setVariable("layout", $layout);
        // in your initialize values method
        $this->_view->setVariable("id", $refParam[0]);
        // in your initialize values method
        $this->_view->setVariable("idRef", $refParam[1]);
        // in your initialize values method
        $this->_view->setVariable("viewId", $viewId);
        // in your initialize values method
        $this->_view->setVariable("objectId", $objectId);
        // in your initialize values method
        $this->_view->setVariable("parentType", $parentType);
        // in your initialize values method
        $this->_view->setVariable("parentId", $parentId);
        $this->_view->setTerminal(true);
        // in your controller action
        return $this->_view;
    }

    public function mobileViewAction()
    {
        $mongoObjectFactory = new MongoObjectFactory();
        
        $id = $this->params()->fromRoute('id', '');
        $mode = $this->params()->fromQuery('mode');
        $parentType = $this->params()->fromQuery('parentType');
        $viewId = $this->params()->fromQuery('viewId');
        
        $request = $this->getRequest();
        $headers = $request->getHeaders();
        
        $headerAuth = $headers->get('Authorization')->getFieldValue();
        $auth = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
        $dm = $this->getServiceLocator()->get('doctrine.documentmanager.odm_default');
        $identity = $auth->getIdentity();
        
        $sessionMgr = new \Authentication\Controller\SessionManager();
        
        if ($sessionMgr->validateToken($dm, $identity, $headerAuth) === true) {
            $this->_view = new ViewModel();
            $this->_view->setVariable("token", $headerAuth);
            
            if (strlen($parentType) > 1) {
                $parentType = $this->params()->fromQuery('parentType');
                $parentId = $this->params()->fromQuery('parentId');
                
                $laf = new MongoObjectFactory();
                $object = $laf->findObjectJSON("View", $viewId);
                // in your initialize values method
                $this->_view->setVariable("linkView", $object['name']);
                
                // in your initialize values method
                if ($object['parentType'] == 'Organization') {
                    $this->_view->setVariable("parentId", $this->getOrganization());
                } else {
                    $this->_view->setVariable("parentId", $object['parent'][0]['$id']);
                }
                // in your initialize values method
                $this->_view->setVariable("parentType", $parentType);
            } else {
                $laf = new MongoObjectFactory();
                /*
                 * @RT removed title as link - using ID's from now on
                 * $object = $laf->findObjectByCriteria("\Application\Document\View", array(
                 * 'title' => $id
                 * ));
                 */
                $object = $laf->findObjectJSON("View", $viewId);
                // in your initialize values method
                $this->_view->setVariable("linkView", $object['name']);
                // in your initialize values method
                $this->_view->setVariable("parentType", $object['parentType']);
                // in your initialize values method
                if ($object['parentType'] == 'Organization') {
                    $this->_view->setVariable("parentId", $this->getOrganization());
                } else {
                    $this->_view->setVariable("parentId", $object['parent'][0]['$id']);
                }
            }
            $this->_view->setVariable("mode", $mode);
            $viewId = (string) $object['_id']; // $this->params()->fromQuery('viewId');
            $objectId = $this->params()->fromQuery('objectId');
            $this->_view->setVariable("objectId", $objectId);
            
            // session_start();
            if (is_null($viewId) || $viewId == 0) {
                $_SESSION['viewId'] = 0;
                // in your initialize values method
                $this->_view->setVariable("viewId", 0);
            } else {
                $_SESSION['viewId'] = $viewId;
                // in your initialize values method
                $this->_view->setVariable("viewId", $viewId); // $object['$viewId']);
                $this->_view->setTerminal(true);
            }
            // in your controller action
            return $this->_view;
        }
    }

    public function viewAction()
    {
        // get something from session
        $session = $this->getSession();
        $dm = $this->getServiceLocator()->get('doctrine.documentmanager.odm_default');
        $identity = $session->getIdentity();
        $user = $dm->getRepository("\\Application\\Document\\User")->findOneBy(array(
            "id" => $identity['id']
        ));
        //
        $_SESSION['organization'] = $user->getOrganization()->getClasspath();
        $_SESSION['dbname'] = $user->getOrganization()->getDbname();
        $_SESSION['workspaceId'] = $user->getOrganization()
            ->getActiveWorkspace()
            ->getId();
        $_SESSION['userId'] = $identity['id'];
        $_SESSION['username'] = $user->getEmail();
        $mongoObjectFactory = new MongoObjectFactory();
        
        $id = $this->params()->fromRoute('id', '');
        $mode = $this->params()->fromQuery('mode');
        $parentType = $this->params()->fromQuery('parentType');
        $viewId = $this->params()->fromQuery('viewId');
        // in your constructor
        $this->_view = new ViewModel();
        if (strlen($parentType) > 1) {
            $parentType = $this->params()->fromQuery('parentType');
            $parentId = $this->params()->fromQuery('parentId');
            $refresh = $this->params()->fromQuery('refresh');
            $laf = new MongoObjectFactory();
            $object = $laf->findObjectJSON("View", $viewId);
            // in your initialize values method
            $this->_view->setVariable("linkView", $object['name']);
            $this->_view->setVariable("refresh", $refresh);
            // in your initialize values method
            if ($object['parentType'] == 'Organization') {
                $this->_view->setVariable("parentId", $this->getOrganization());
            } else {
                $this->_view->setVariable("parentId", $object['parent'][0]['$id']);
            }
            // in your initialize values method
            $this->_view->setVariable("parentType", $parentType);
        } else {
            $laf = new MongoObjectFactory();
            /*
             * @RT removed title as link - using ID's from now on
             * $object = $laf->findObjectByCriteria("\Application\Document\View", array(
             * 'title' => $id
             * ));
             */
            $object = $laf->findObjectJSON("View", $viewId);
            // in your initialize values method
            $this->_view->setVariable("linkView", $object['name']);
            // in your initialize values method
            $this->_view->setVariable("parentType", $object['parentType']);
            // in your initialize values method
            if ($object['parentType'] == 'Organization') {
                $this->_view->setVariable("parentId", $this->getOrganization());
            } else {
                $this->_view->setVariable("parentId", $object['parent'][0]['$id']);
            }
        }
        $this->_view->setVariable("mode", $mode);
        $viewId = (string) $object['_id']; // $this->params()->fromQuery('viewId');
        $objectId = $this->params()->fromQuery('objectId');
        $this->_view->setVariable("objectId", $objectId);
        $this->_view->setVariable("organization", $user->getOrganization()
            ->getClasspath());
        // session_start();
        if (is_null($viewId) || $viewId == 0) {
            $_SESSION['viewId'] = 0;
            // in your initialize values method
            $this->_view->setVariable("viewId", 0);
        } else {
            $_SESSION['viewId'] = $viewId;
            // in your initialize values method
            $this->_view->setVariable("viewId", $viewId); // $object['$viewId']);
            $this->_view->setTerminal(true);
        }
        
        // in your controller action
        return $this->_view;
    }

    public function viewadminAction()
    {
        return $this->redirect()->toRoute('home', array(
            'action' => 'view',
            'mode' => 'admin'
        ));
    }

    public function listAction()
    {
        $page = $this->params()->fromRoute('id', '');
        
        // in your constructor
        $this->_view = new ViewModel();
        
        // in your initialize values method
        $this->_view->setVariable("id", $page);
        $layout = $this->params()->fromQuery('layout');
        $parentType = $this->params()->fromQuery('parentType');
        $parentId = $this->params()->fromQuery('parentId');
        $viewId = $this->params()->fromQuery('viewId');
        // in your initialize values method
        $this->_view->setVariable("layout", $layout);
        // in your initialize values method
        $this->_view->setVariable("parentId", $parentId);
        // in your initialize values method
        $this->_view->setVariable("parentType", $parentType);
        if (is_null($viewId) || $viewId == 0) {
            $this->_view->setVariable("viewId", "0");
        } else {
            // in your initialize values method
            $this->_view->setVariable("viewId", $viewId);
        }
        $this->_view->setTerminal(true);
        // in your controller action
        return $this->_view;
    }

    public function listnewAction()
    {
        
        // in your constructor
        $this->_view = new ViewModel();
        $layout = $this->params()->fromQuery('layout');
        $parentType = $this->params()->fromQuery('parentType');
        $parentId = $this->params()->fromQuery('parentId');
        $viewId = $this->params()->fromQuery('viewId');
        // in your initialize values method
        $this->_view->setVariable("layout", $layout);
        // in your initialize values method
        $this->_view->setVariable("parentId", $parentId);
        // in your initialize values method
        $this->_view->setVariable("parentType", $parentType);
        if (is_null($viewId) || $viewId == 0) {
            $this->_view->setVariable("viewId", "0");
        } else {
            // in your initialize values method
            $this->_view->setVariable("viewId", $viewId);
        }
        $this->_view->setTerminal(true);
        // in your controller action
        return $this->_view;
    }

    public function listhtmlAction()
    {
        $page = $this->params()->fromRoute('id', '');
        
        // in your constructor
        $this->_view = new ViewModel();
        
        // in your initialize values method
        $this->_view->setVariable("id", $page);
        $layout = $this->params()->fromQuery('layout');
        $parentType = $this->params()->fromQuery('parentType');
        $parentId = $this->params()->fromQuery('parentId');
        $viewId = $this->params()->fromQuery('viewId');
        // in your initialize values method
        $this->_view->setVariable("layout", $layout);
        // in your initialize values method
        $this->_view->setVariable("parentId", $parentId);
        // in your initialize values method
        $this->_view->setVariable("parentType", $parentType);
        if (is_null($viewId) || $viewId == 0) {
            $this->_view->setVariable("viewId", "0");
        } else {
            // in your initialize values method
            $this->_view->setVariable("viewId", $viewId);
        }
        // in your controller action
        return $this->_view;
    }

    public function listmapAction()
    {
        $page = $this->params()->fromRoute('id', '');
        
        // in your constructor
        $this->_view = new ViewModel();
        
        // in your initialize values method
        $this->_view->setVariable("id", $page);
        $layout = $this->params()->fromQuery('layout');
        $parentType = $this->params()->fromQuery('parentType');
        $parentId = $this->params()->fromQuery('parentId');
        $viewId = $this->params()->fromQuery('viewId');
        // in your initialize values method
        $this->_view->setVariable("layout", $layout);
        // in your initialize values method
        $this->_view->setVariable("parentId", $parentId);
        // in your initialize values method
        $this->_view->setVariable("parentType", $parentType);
        if (is_null($viewId) || $viewId == 0) {
            $this->_view->setVariable("viewId", "0");
        } else {
            // in your initialize values method
            $this->_view->setVariable("viewId", $viewId);
        }
        // in your controller action
        return $this->_view;
    }

    public function reportAction()
    {
        $page = $this->params()->fromRoute('id', '');
        
        // in your constructor
        $this->_view = new ViewModel();
        
        // in your initialize values method
        $this->_view->setVariable("id", $page);
        $layout = $this->params()->fromQuery('layout');
        $parentType = $this->params()->fromQuery('parentType');
        $parentId = $this->params()->fromQuery('parentId');
        $viewId = $this->params()->fromQuery('viewId');
        // in your initialize values method
        $this->_view->setVariable("layout", $layout);
        // in your initialize values method
        $this->_view->setVariable("parentId", $parentId);
        // in your initialize values method
        $this->_view->setVariable("parentType", $parentType);
        if (is_null($viewId) || $viewId == 0) {
            $this->_view->setVariable("viewId", "0");
        } else {
            // in your initialize values method
            $this->_view->setVariable("viewId", $viewId);
        }
        // in your controller action
        return $this->_view;
    }

    public function canvasAction()
    {
        $page = $this->params()->fromRoute('id', '');
        
        // in your constructor
        $this->_view = new ViewModel();
        
        // get language based on organization
        $dm = $this->getServiceLocator()->get('doctrine.documentmanager.odm_default');
        $session = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
        
        $identity = $session->getIdentity();
        $user = $dm->getRepository("\\Application\\Document\\User")->findOneBy(array(
            "id" => $identity['id']
        ));
        
        $lang = $user->getOrganization()->getLocale();
        if ($lang == 'en') {
            $schedulerLanguage = $lang . '-GB';
        } else {
            $schedulerLanguage = $lang . '-' . strtoupper($lang);
        }
        $this->_view->setVariable("schedulerLanguage", $schedulerLanguage);
        
        // in your initialize values method
        $this->_view->setVariable("id", $page);
        $layout = $this->params()->fromQuery('layout');
        $parentType = $this->params()->fromQuery('parentType');
        $parentId = $this->params()->fromQuery('parentId');
        $objectId = $this->params()->fromQuery('objectId');
        $viewId = $this->params()->fromQuery('viewId');
        // in your initialize values method
        $this->_view->setVariable("layout", $layout);
        // in your initialize values method
        $this->_view->setVariable("parentId", $parentId);
        // in your initialize values method
        $this->_view->setVariable("parentType", $parentType);
        
        $this->_view->setVariable("objectId", $objectId);
        if (is_null($viewId) || $viewId == 0) {
            $this->_view->setVariable("viewId", "0");
        } else {
            // in your initialize values method
            $this->_view->setVariable("viewId", $viewId);
        }
        // if (!isset($_SESSION['schedulerLibJS'])) {
        // $_SESSION['schedulerLibJS'] = "loaded";
        // } else {
        $this->_view->setTerminal(true);
        // }
        // in your controller action
        return $this->_view;
    }

    public function popupAction()
    {
        $object = $this->params()->fromRoute('id', '');
        $viewId = $this->params()->fromQuery('viewId');
        
        return new ViewModel(array(
            'id' => $object,
            'viewId' => $viewId
        ));
    }

    public function popuplistAction()
    {
        $laf = new MongoObjectFactory();
        $newCollection = array();
        $objectType = $this->params()->fromQuery('objectType');
        $objectId = $this->params()->fromQuery('objectId');
        $viewId = $this->params()->fromQuery('viewId');
        $gridId = $this->params()->fromQuery('gridId');
        $workspaceId = $this->params()->fromQuery('workspaceId');
        $log = Log::getInstance();
        $log->AddRow(" Get List action : " . $objectId . ' --- ' . $workspaceId . ' .... ' . $viewId);
        if (strlen($objectId) > 1) {
            $itemFound = $laf->findObjectJSON($this->params()
                ->fromQuery('objectType'), $objectId);
            $collection[] = $itemFound;
            // $log->AddRow(" Find one id: " . json_encode($collection));
        } else {
            $collection = $laf->find($this->params()
                ->fromQuery('objectType'));
            // $log->AddRow(" Find all: " . json_encode($collection));
        }
        $param = $this->params()->fromQuery('param');
        
        foreach ($collection as $item) {
            // $listArray[] = $item;
            foreach ($item as $key => $column) {
                $arItems = array();
                if (is_array($column) == true) {
                    $arItem = array();
                    $i = 0;
                    foreach ($column as $itemS => $val) {
                        // if we have a DB ref then start to investigate
                        if (! is_null($val['$ref'])) {
                            // get ref field type
                            $refType = ucfirst(substr($val['$ref'], 0, strlen($val['$ref']) - 1));
                            // $log->AddRow(" Get REF Type : " . $refType . ' -- ' . $val['$ref'] . ' key' . $key);
                            // get field type -
                            $workspace = $this->getWorkspace($workspaceId);
                            $view = $workspace->getInstance("View", new \MongoId($viewId));
                            // $log->AddRow(" Get View Type : " . json_encode($view));
                            
                            $typeClass = new \ReflectionClass($laf->getClassPath($objectType) . $objectType);
                            
                            $criteria = "name-" . $val['$ref'] . "-objectReferenceType-" . $refType;
                            $criteriaTo = explode("-", $criteria);
                            $field = $view->getOneInstanceCriteria("Field", $criteriaTo);
                            // $log->AddRow(" Found Field : " . json_encode($field));
                            if (! is_null($field)) {
                                // $log->AddRow(" Found Field : " . json_encode($field). " -- ". $field['type']);
                                // if
                                if ($field['typeReference'] == Field::TYPE_REF_VALUE || $field['typeReference'] == Field::TYPE_REF_VALUE_REMOTE || $field['typeReference'] == Field::TYPE_REFERENCE || $field['typeReference'] == Field::TYPE_REFERENCE_REMOTE) {
                                    // get reference type
                                    // $log->AddRow(" Found Object Ref field : " . json_encode($field) . ' -- ' . json_encode($val));
                                    // if type == one-to-one add a single item
                                    // get from DB item based on id
                                    $mongoObjectFactory = new MongoObjectFactory();
                                    $object = $mongoObjectFactory->findObject($refType, $val['$id']);
                                    $log->AddRow(" Found Object Ref   : " . json_encode($object));
                                    // get PK
                                    // set data for return
                                    $arItems['id'] = $val['$id'];
                                    $arItems['recid'] = $val['$id'];
                                    $arItems['text'] = $object->getPK();
                                } else {
                                    // $i = $i + 1;
                                    $arItem['id'] = $val['$id'];
                                    $arItem['recid'] = $val['$id'];
                                    $arItem['text'] = $val['$id'];
                                    $arItems[] = $arItem;
                                }
                            }
                        }
                    }
                    
                    $item[$key] = $arItems;
                }
            }
            $newCollection[] = $item;
        }
        
        $listArray = array();
        $columns = array();
        foreach ($newCollection as $item) {
            $item['recid'] = $item['_id']->__ToString();
            unset($item['_id']);
            $listArray[] = $item;
            foreach ($item as $key => $column) {
                if (is_array($column) == true) {
                    $columns[$key] = "+" . $key;
                } else {
                    $columns[$key] = $key;
                }
            }
        }
        $searchColumns = array();
        $arrayColumns = array();
        foreach ($columns as $key => $column) {
            $search['field'] = $key;
            $search['caption'] = ucfirst($key);
            $search['type'] = 'text';
            $searchColumns[] = $search;
            $col = array();
            $col['field'] = $key;
            $col['caption'] = ucfirst($key);
            $col['size'] = '100px';
            $col['sortable'] = true;
            $col['resizable'] = true;
            $type = array();
            // if is ARRAY column set the expectations
            if (strpos($column, "+") === 0) {
                // $col['size'] = '50%';
                $type['type'] = 'list';
                $name = ucfirst(substr($column, 1));
                $type['items'] = strtolower($name);
                $type['showAll'] = true;
                // $col['editable'] = true;
                // $type['url'] = 'http://localhost:8080/application/getMethodResultList?objectType=' . $objectType . '&methodName=get' . $name . '&id=0&param=id';
                // "http://localhost:8080/application/getMethodResult?objectType=\\Application\\Document\\Customer&methodName=getOrders&id=55e5c6238f7b68cf640041a7&param=id;
                // $type['render'] = 'function (record, index, col_index) { var html = this.getCellValue(index, col_index); return html.text;}';
                $col['editable'] = $type;
                // $col['render'] = 'function (record, index, col_index) { var html = this.getCellValue(index, col_index); return html.text;}';
            }
            $arrayColumns[] = $col;
        }
        
        return new JsonModel(array(
            'search' => $searchColumns,
            'columns' => $arrayColumns,
            'result' => $listArray
        ));
    }

    public function saveAction()
    {
        $laf = new MongoObjectFactory();
        $json = $this->params()->fromQuery('json');
        $type = $this->params()->fromQuery('objectType');
        $typeClass = new \ReflectionClass($laf->getClassPath($type) . $type);
        
        $object = $laf->create($type, $json);
        
        return new JsonModel(array(
            'id' => $object->_id,
            'result' => ''
        ));
    }

    public function getSchedulerDataAction()
    {
        
        // die(date_default_timezone_get());
        $d1 = new \DateTime('2013-06-13 10:00');
        $d2 = new \DateTime('2013-06-13 12:30');
        $s1 = $d1->format('U');
        $s2 = $d2->format('U');
        $model = array();
        $model["MeetingID"] = 1;
        $model["RoomID"] = 2;
        $model["Attendees"] = array(
            1
        );
        $model["Title"] = "Evaluations of Employees";
        $model["Description"] = "test";
        $model["StartTimezone"] = null;
        $micro = 1000;
        $model["Start"] = "/Date(" . $s1 * $micro . ")/";
        $model["End"] = "/Date(" . $s2 * $micro . ")/";
        $model["EndTimezone"] = null;
        $model["RecurrenceRule"] = null;
        $model["RecurrenceID"] = null;
        $model["RecurrenceException"] = null;
        $model["IsAllDay"] = false;
        
        return new JsonModel(array(
            $model
        ));
    }

    /**
     * Executes and returns a type
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function executeAction()
    {
        $request = $this->getRequest();
        $result = false;
        $action = "";
        $log = Log::getInstance();
        $mtime = microtime();
        $mtime = explode('.', $mtime);
        $mtime = (int) $mtime[1] + (int) $mtime[0];
        $tId = "" . $mtime;
        $_SESSION["transaction_id"] = $tId;
        $listArray = array();
        $mongoObjectFactory = new MongoObjectFactory();
        if ($request->isPost()) {
            $data = $request->getPost()->toArray();
            $log->AddRow(" Execute Transaction - " . json_encode($data));
            $actionToExecute = $data['actionExecution'];
            if (isset($data['id'])) {
                if ($data['id'] == 0) {
                    unset($data['id']);
                }
            }
            
            // if we have an recid then find the object and execute on it
            if (isset($data['id'])) {
                $_SESSION["transaction_type"] = $actionToExecute;
                if (isset($data)) {
                    $_SESSION["transaction_objectId"] = json_encode($data);
                }
                
                if (isset($data['method'])) {
                    $_SESSION["transaction_name"] = $data['method'];
                }
                $id = $data['id']; // ->fromQuery('id');
                $log->AddRow(" Execute Action 2 -  " . json_encode($data));
                // $id = $data['data']['recid'];
                if ($actionToExecute == 'saveObject') {
                    try {
                        // update object
                        $_SESSION["transaction_name"] = "update";
                        $result = $mongoObjectFactory->update($data['objectType'], $id, $data['data']);
                        $data['data']['id'] = $id;
                        $listArray[] = $data['data'];
                    } catch (\Exception $e) {
                        $log->AddRow(" Exception -  " . $e->getMessage());
                        $error = array();
                        $error['error'] = $e->getMessage();
                        $listArray['name'] = json_encode($id);
                        $listArray['name'] = $error;
                    }
                    // $listArray = array_merge($listArray, $data['data']);
                } elseif ($actionToExecute == 'service') {
                    $serviceName = $data['data']['serviceName'];
                    $method = $data['data']['serviceMethod'];
                    
                    $serviceClass = new \ReflectionClass($serviceName);
                    $class = $serviceClass->newInstanceArgs();
                    
                    $reflectionMethod = new \ReflectionMethod($class, $method);
                    // $result = $mongoObjectFactory->update($data['objectType'], $id, $data['data']);
                    
                    if (isset($data['data'])) {
                        $idRel = $reflectionMethod->invoke($class, $data['data']);
                    } else {
                        $idRel = $reflectionMethod->invoke($class, array());
                    }
                    
                    $listArray = $idRel;
                } elseif ($actionToExecute == 'method') {
                    $typeObj = $data['objectType'];
                    $method = $data['method'];
                    $log->AddRow(" Execute Action 40ax -  " . json_encode($data));
                    $laf = new MongoObjectFactory();
                    $typeClass = new \ReflectionClass($laf->getClassPath($typeObj) . $typeObj);
                    $log->AddRow(" Execute Action 401x -  " . json_encode($typeClass));
                    if (is_array($id)) {
                        $log->AddRow(" Execute Action 4ax -  " . json_encode($id));
                        foreach ($id as $idInstance) {
                            $object = $laf->findObject($typeObj, $idInstance);
                            if (strlen($actionToExecute) > 1) {
                                try {
                                    $log->AddRow(" Execute Action 42x -  " . json_encode($object));
                                    if (isset($data['data'])) {
                                        $log->AddRow(" Execute Action 4x -  " . json_encode($method));
                                        $listArray = $object->executeNew("name", $method, $data['data']);
                                    } else {
                                        $log->AddRow(" Execute Action 4c -  " . json_encode($method));
                                        $listArray = $object->executeNew("name", $method, array());
                                    }
                                } catch (\Exception $e) {
                                    $log->AddRow(" Exception -  " . $e->getMessage());
                                    $error = array();
                                    $error['error'] = $e->getMessage();
                                    $listArray['name'] = json_encode($id);
                                    $listArray['name'] = $error;
                                }
                                
                                $object->propagate();
                            }
                        }
                    } else {
                        
                        $log->AddRow(" Execute Action 40Zx -  " . json_encode($typeObj));
                        $object = $laf->findObject($typeObj, $id);
                        $log->AddRow(" Execute Action 43x -  " . json_encode($object));
                        // $log->AddRow(" Execute Action 441x - " . json_encode($object->_id));
                        if (isset($object->id) || isset($object->_id)) {
                            $log->AddRow(" Execute Action 442x -  " . json_encode($actionToExecute));
                            try {
                                if (strlen($actionToExecute) > 1) {
                                    if (isset($data['data'])) {
                                        $log->AddRow(" Execute Action 44x -  " . json_encode($data['data']));
                                        $listArray = $object->executeNew("name", $method, $data['data']);
                                    } else {
                                        $log->AddRow(" Execute Action 45x -  " . json_encode($object));
                                        $listArray = $object->executeNew("name", $method, array());
                                    }
                                }
                            } catch (\Exception $e) {
                                $log->AddRow(" Exception -  " . $e->getMessage());
                                $error = array();
                                $error['error'] = $e->getMessage();
                                $listArray['name'] = json_encode($id);
                                $listArray['name'] = $error;
                            }
                            $object->propagate();
                        }
                    }
                    
                    // propagate ALL !!!! changes !!
                    $this->recalculateAllChanges($tId);
                } elseif ($actionToExecute == 'filter') {
                    $typeObj = $data['objectType'];
                    $method = $data['method'];
                    $laf = new MongoObjectFactory();
                    $typeClass = new \ReflectionClass($laf->getClassPath($typeObj) . $typeObj);
                    $dm = $this->getServiceLocator()->get('doctrine.documentmanager.odm_default');
                    $session = $this->getSession();
                    $identity = $session->getIdentity();
                    $user = $dm->getRepository("\\Application\\Document\\User")->findOneBy(array(
                        "id" => $identity['id']
                    ));
                    
                    $dataIn = $data['data'];
                    $viewId = $dataIn['viewId'];
                    $componentId = $dataIn['componentId'];
                    unset($dataIn['recid']);
                    unset($dataIn['object']);
                    unset($dataIn['parentType']);
                    unset($dataIn['parentId']);
                    unset($dataIn['actionExecution']);
                    unset($dataIn['viewId']);
                    unset($dataIn['componentId']);
                    $stateService = new StateService();
                    $result = $stateService->saveUserState($viewId, $identity, $user, $componentId, $dataIn, "filter");
                }
            } else {
                try {
                    $_SESSION["transaction_type"] = "add";
                    $_SESSION["transaction_name"] = "create";
                    $_SESSION["transaction_objectId"] = json_encode($data);
                    // find the container - creating a new ID
                    $container = $mongoObjectFactory->findObject($data['parentType'], $data['parentId']);
                    $reflectionMethod = new \ReflectionMethod($container, 'add');
                    $log->AddRow(" Execute Action 41x -  " . json_encode($data['data']));
                    if (isset($data['data']) && ! isset($data['data']['$id'])) {
                        $idRel = $reflectionMethod->invoke($container, $data['objectType'], $data['data']);
                    }
                } catch (\Exception $e) {
                    $log->AddRow(" Exception -  " . $e->getMessage());
                    $error = array();
                    $error['error'] = $e->getMessage();
                    $idRel = 0;
                    $listArray['name'] = json_encode(0);
                    $listArray['name'] = $error;
                }
                $listArray[] = $idRel;
            }
        }
        $_SESSION["transaction_id"] = "";
        $_SESSION["transaction_type"] = "";
        $_SESSION["transaction_name"] = "";
        $_SESSION["transaction_objectId"] = "";
        return new JsonModel(array(
            "status" => "success",
            "total" => count($listArray),
            'items' => $listArray
        ));
    }

    public function getTransaction($tId)
    {
        // $log->AddRow(" Execute Action 41x - " . json_encode($tId));
        $dm = $this->getServiceLocator()->get('doctrine.documentmanager.odm_default');
        $trans = $dm->getRepository("\\Application\\Document\\Transaction")->findOneBy(array(
            "transactionid" => $tId
        ));
        return $trans;
    }

    public function getState($tId)
    {
        \Application\Controller\Log::getInstance()->AddRow(' getState >>>>>>>>>>>> ' . json_encode($tId));
        $mongoObjectFactory = new \Application\Controller\MongoObjectFactory();
        $trans = $mongoObjectFactory->findObjectInstanceByCriteria('State', array(
            "transactionid" => $tId
        ));
        \Application\Controller\Log::getInstance()->AddRow(' getState >>>>>>>>>>>> ' . json_encode($trans));
        
        return $trans;
    }

    public function getStateDatas($tId)
    {
        // \Application\Controller\Log::getInstance()->AddRow(' getStateDatas >>>>>>>>>>>> ' . json_encode($tId));
        $sid = $this->getState($tId);
        // \Application\Controller\Log::getInstance()->AddRow(' getStateDatas >>>>>>>>>>>> ' . json_encode($sid));
        $mongoObjectFactory = new \Application\Controller\MongoObjectFactory();
        if (isset($sid)) {
            $trans = $mongoObjectFactory->findObjectsByCriteria('StateData', array(
                'state.$id' => new \MongoId($sid->getIdAsString())
            ), false);
            
            return $trans;
        }
        return null;
    }

    public function recalculateAllChanges($tId)
    {
        $_SESSION["transaction_type"] = "propagation";
        // \Application\Controller\Log::getInstance()->AddRow(' recalculateAllChanges >>>>>>>>>>>> ' . json_encode($tId));
        $states = $this->getStateDatas($tId);
        
        $laf = new MongoObjectFactory();
        if (isset($states) && sizeof($states) > 0) {
            foreach ($states as $stateTo) {
                // \Application\Controller\Log::getInstance()->AddRow(' recalculateAllChanges >>>>>>>>>>>> ' . json_encode($stateTo));
                $objId = $stateTo->getObjectid();
                $objType = $stateTo->getObjecttype();
                $obj = $laf->findObject($objType, $objId);
                if (isset($obj) && isset($obj->id)) {
                    // \Application\Controller\Log::getInstance()->AddRow(' recalculateAllChanges >>>>>>>>>>>> ' . json_encode($obj));
                    $this->reIndexAndPropagate($obj);
                }
            }
        }
    }

    public function reIndexAndPropagate($refInstance)
    {
        $state = new State();
        $paernetInstance = $refInstance->getParent();
        $contianerRef = array();
        $name = $refInstance->getTableName();
        $contianerRef = $paernetInstance->getRelationDetails($name);
        
        if ($refInstance instanceof Indexed) {
            $sortIndex = '';
            if (isset($contianerRef{'sorted'})) {
                $sortIndex = $contianerRef{'sorted'};
            } else {
                $contianerRefP = $refInstance->getParent()->getRelationDetails($this->getTableName());
                if (isset($contianerRefP{'sorted'})) {
                    $sortIndex = $contianerRefP{'sorted'};
                }
            }
            $paernetInstance->reload();
            // $firstObj = $paernetInstance->reindexReference($refInstance->getClassNameFromTable($name), $sortIndex);
            $paernetInstance->reload();
            $paernetInstance->propagate();
            $paernetInstance->reload();
            
            $refInstance = $paernetInstance->getInstance($refInstance->getClassNameFromTable($name), $refInstance->getIdAsString());
            
            $refInstance->propagate($state);
            
            // }
        } else {
            $refInstance->propagate($state);
            $refInstance->reload();
            $paernetInstance->reload();
            $paernetInstance->propagate($state);
        }
    }

    /**
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function getMethodResultAction()
    {
        try {
            $session = $this->getSession();
            $identity = $session->getIdentity();
            $limit = 0;
            $offset = 0;
            $search = array();
            $service = $this->getServiceLocator();
            $translator = $service->get('translator');
            $request = $this->getRequest();
            $results = array();
            $searchL = '';
            $searchlogic = '';
            $sort = '';
            $log = Log::getInstance();
            if ($request->isPost()) {
                
                $dataRequest = $request->getPost()->toArray();
                // $log->AddRow(" Get COLLECTION ref action n33: " . $dataRequest['request']);
                if (isset($dataRequest['request'])) {
                    $data = json_decode($dataRequest['request'], true);
                }
                $sort = isset($data['sort']) ? $data['sort'] : '';
                $limit = isset($data['limit']) ? $data['limit'] : 0;
                $offset = isset($data['offset']) ? $data['offset'] : 0;
                $searchL = isset($data['search']) ? $data['search'] : '';
                $searchlogic = isset($data['searchLogic']) ? $data['searchLogic'] : '';
                $search["search"] = $searchL;
                $search["searchLogic"] = $searchlogic;
            }
            $viewId = $this->params()->fromQuery('viewId');
            $searchOnly = $this->params()->fromQuery('searchOnly');
            if ($searchOnly) {
                if ($searchL == '') {
                    
                    // return new JsonModel($results);
                }
            }
            $gridId = $this->params()->fromQuery('gridId');
            $stateService = new StateService();
            if ($sort != '') {
                $stateService->saveState($viewId, $gridId, $sort, "sort");
            } else {
                $sortD = $stateService->getState($viewId, $gridId, "sort");
                if (isset($sortD)) {
                    $sort = array();
                    $sort = json_decode($sortD, TRUE);
                }
            }
            
            $service = new Service();
            $results = $service->prepareData($this->params()
                ->fromQuery('id'), $this->params()
                ->fromQuery('objectType'), $this->params()
                ->fromQuery('param'), $this->params()
                ->fromQuery('methodName'), $viewId, $gridId, $identity['id'], $this->params()
                ->fromQuery('criteria'), $translator, $limit, $offset, $search, $sort, $this->params()
                ->fromQuery('column'));
            // print_r($results);exit;
            return new JsonModel($results);
        } catch (\Exception $e) {
            print_r($e->getMessage());
            exit();
        }
    }

    /**
     * @SWG\Api(
     * path="/application/getMethodResultList",
     * description="Get list of information",
     * @SWG\Operations(
     * @SWG\Operation(
     * method="GET",
     * summary="Get list of information",
     * notes="Get list of information",
     * nickname="getMethodResultListAction",
     * @SWG\Parameter(
     * name="objectType",
     * description="Class name of Object",
     * defaultValue="Workspace",
     * paramType="query",
     * required="true",
     * allowMultiple="false",
     * type="string"
     * ),
     * @SWG\Parameter(
     * name="id",
     * description="Id of object",
     * defaultValue="569fe6aecdaf90141c8b456a",
     * paramType="query",
     * required="true",
     * allowMultiple="false",
     * type="string"
     * ),
     * @SWG\Parameter(
     * name="methodName",
     * description="Name of method in order to get results",
     * defaultValue="getMenu",
     * paramType="query",
     * required="false",
     * allowMultiple="false",
     * type="string"
     * ),
     * @SWG\Parameter(
     * name="criteria",
     * description="Filters - ex:scope-user",
     * defaultValue="scope-user",
     * paramType="query",
     * required="false",
     * allowMultiple="false",
     * type="string"
     * ),
     * @SWG\Parameter(
     * name="urlLink",
     * description="Url link",
     * defaultValue="http://localhost/ZendSkeletonApplication/public/application/view",
     * paramType="query",
     * required="false",
     * allowMultiple="false",
     * type="string"
     * )
     * )
     * )
     * )
     */
    public function getMethodResultListAction()
    {
        $id = $this->params()->fromQuery('id');
        $refType = $this->params()->fromQuery('type');
        $objRefType = $refType;
        $action = $this->params()->fromQuery('action');
        $type = $this->params()->fromQuery('objectType');
        $objectId = $this->params()->fromQuery('objectId');
        $laf = new MongoObjectFactory();
        $typeClass = new \ReflectionClass($laf->getClassPath($type) . $type);
        $param = $this->params()->fromQuery('param');
        $criteria = $this->params()->fromQuery('criteria');
        $workspaceId = $this->params()->fromQuery('workspaceId');
        $urlLink = $this->params()->fromQuery('urlLink');
        
        if (isset($urlLink)) {
            $_SESSION['urlLink'] = $urlLink;
        }
        
        $laf = new MongoObjectFactory();
        if ($id == 0) {
            // find all
        } else {
            $object = $laf->findObject($type, $id);
        }
        
        $method = $this->params()->fromQuery('methodName');
        $actionName = array();
        $isCriteria = strpos($criteria, "-");
        $pos = strpos($method, "cg");
        if ($pos === false) {
            $actionName = substr($method, 0, 3);
            $typeName = substr($method, 3);
            if ($id == 0) {
                // TODO add organization filter!
                $criteriaTo = array();
                $criteriaArray = array();
                if (! is_null($criteria)) {
                    $criteriaTo = explode("-", $criteria);
                    $arrayCount = count($criteriaTo);
                    for ($i = 0; $i < $arrayCount; $i = $i + 2) {
                        $criteriaValue = $criteriaTo[$i + 1];
                        if ($this->substr_startswith($criteriaValue, "ref")) {
                            $criteriaValueArr = explode("|", $criteriaValue);
                            $objectRole = $laf->findObject($criteriaValueArr[1], $criteriaValueArr[3]);
                            $criteriaArray[$criteriaTo[$i]] = $objectRole->role[0]['text'];
                        } else if ($this->substr_startswith($criteriaValue, "@")) {
                            $keyValue = substr($criteriaValue, 1);
                            if (isset($_SESSION[$keyValue])) {
                                \Application\Controller\Log::getInstance()->AddRow(" EXECUTEFINd -< " . $_SESSION[$keyValue] . " >-on " . $keyValue . ' --> ');
                                $newVal = $_SESSION[$keyValue];
                                $criteriaArray[$criteriaTo[$i]] = $newVal;
                            } else {
                                $criteriaArray[$criteriaTo[$i]] = $keyValue;
                            }
                        } else {
                            $criteriaArray[$criteriaTo[$i]] = $criteriaTo[$i + 1];
                        }
                    }
                    // $criteriaArray[$criteriaTo[0]] = $criteriaTo[1];
                }
                // \Application\Controller\Log::getInstance()->AddRow('WE SHOULD NOT BE HERE WITHOUT ORG ID or WKSP ID' . json_encode($criteriaArray));
                $collection = $laf->findAllObjectJSON($typeName, $criteriaArray);
            } else {
                if ($isCriteria === false) {
                    $reflectionMethod = new \ReflectionMethod($laf->getClassPath($type) . $type, 'get');
                    $collection = $reflectionMethod->invoke($object, $typeName);
                } else {
                    $reflectionMethod = new \ReflectionMethod($laf->getClassPath($type) . $type, "getInstanceCriteria");
                    $criteriaTo = explode("-", $criteria);
                    // \Application\Controller\Log::getInstance()->AddRow(' --> getMethodResultListAction ' . json_encode($criteria));
                    $collection = $reflectionMethod->invoke($object, $typeName, $criteriaTo);
                }
            }
        } else {
            
            // $actionName = $method;
            $collection = array();
            $reflectionMethod = new \ReflectionMethod($laf->getClassPath($type) . $type, $method);
            if ($isCriteria === false) {
                $collection = $reflectionMethod->invoke($object, null);
            } else {
                $criteriaTo = explode("-", $criteria);
                $collection = $reflectionMethod->invoke($object, $criteriaTo);
            }
        }
        
        $listArray = array();
        foreach ($collection as $item) {
            // \Application\Controller\Log::getInstance()->AddRow(' --> XXYY ' . json_encode($item));
            if (isset($item)) {
                $item['recid'] = (string) $item['_id']; // ->__ToString();
                                                        // $item['id'] = (string) $item['_id']; // ->__ToString();
                                                        // $listArray[] = $item;
                foreach ($item as $key => $column) {
                    if (! is_array($column)) {
                        
                        if (strcmp($column, Field::TYPE_ENUM) == 0) {
                            // \Application\Controller\Log::getInstance()->AddRow(' --> XXYY ' . json_encode($item));
                            $arraydata = array();
                            $methods = explode(".", $item['typeReference']);
                            $arraydata["name"] = $methods[0];
                            $arraydata["workspaceId"] = $workspaceId;
                            // TODO add Workspace ID as Parent ID
                            $collection = $laf->findObjectsByCriteria("Mastertable", $arraydata);
                            \Application\Controller\Log::getInstance()->AddRow(' -->  ' . json_encode($collection));
                            $itemsArray = array();
                            foreach ($collection as $key => $itemData) {
                                $itemsArray = json_decode($itemData['items']);
                                $itemsArrayNew = array();
                                $i = 0;
                                foreach ($itemsArray as $keyItem => $itemD) {
                                    // $itemsArrayNew[] =$itemD->{$methods[1]};
                                    $i = $i + 1;
                                    $itemsArrayNew[] = array(
                                        'id' => $itemD->{'recid'},
                                        'text' => $itemD->{$methods[1]}
                                    );
                                }
                                // $item["options"]["status"]= "success";
                                $item["options"]["items"] = $itemsArrayNew;
                                
                                $item["options"]['openOnFocus'] = true;
                                $item['type'] = 'enum';
                                $item["options"]['selected'] = array();
                            }
                        }
                    }
                    if (strcmp($key, "options") == 0 && ($item['type'] == 'list')) {
                        if (is_array($column)) {
                            foreach ($column as $keyU => $valU) {
                                // \Application\Controller\Log::getInstance()->AddRow(' --> XX1 ' . json_encode($valU));
                                // foreach($data as $key => $value){
                                // $log->AddRow(" Estep -< " . $key." >-on " . $value. ' --> ');
                                $valU1 = preg_replace("/@objectId@/", $objectId, $valU);
                                $valU2 = preg_replace("/@type@/", $objRefType, $valU1);
                                $valU3 = preg_replace("/@action@/", $action, $valU2);
                                
                                // \Application\Controller\Log::getInstance()->AddRow(' --> XX1 ' . json_encode($valU3));
                                $item[$key][$keyU] = $valU3;
                                // }
                            }
                        }
                    } else if (strcmp($key, "options") == 0 && $item['type'] == 'enum') {
                        if (is_array($column)) {
                            foreach ($column as $keyU => $valU) {
                                // \Application\Controller\Log::getInstance()->AddRow(' --> XX1 ' . json_encode($valU));
                                // foreach($data as $key => $value){
                                // $log->AddRow(" Estep -< " . $key." >-on " . $value. ' --> ');
                                $valU1 = preg_replace("/@objectId@/", $objectId, $valU);
                                $valU2 = preg_replace("/@type@/", $objRefType, $valU1);
                                $valU3 = preg_replace("/@action@/", $action, $valU2);
                                
                                // \Application\Controller\Log::getInstance()->AddRow(' --> XX1 ' . json_encode($valU3));
                                $item[$key][$keyU] = $valU3;
                                // }
                            }
                        }
                    }
                    if (strcmp($key, "recid") == 0) {} else if (isset($param) && sizeof($param) >= 1) {
                        if (strcmp($key, $param) == 0) {} else {
                            unset($item[$key]);
                        }
                    }
                }
                $listArray[] = $item;
            }
        }
        
        return new JsonModel(array(
            "status" => "success",
            "total" => count($listArray),
            'items' => $listArray
        ));
    }

    public function getObjectAction()
    {
        $collection = array();
        $id = $this->params()->fromQuery('id');
        $log = Log::getInstance();
        $type = $this->params()->fromQuery('objectType');
        $laf = new MongoObjectFactory();
        
        if ($id == 0) {} else {
            $object = $laf->findObject($type, $id);
            $collection[] = $object;
        }
        return new JsonModel(array(
            "status" => "success",
            "total" => count($collection),
            'items' => $collection
        ));
    }

    public function getMethodResultListReferenceAction()
    {
        $request = $this->getRequest();
        $id = $this->params()->fromQuery('id');
        $action = $this->params()->fromQuery('action');
        $log = Log::getInstance();
        $type = $this->params()->fromQuery('objectType');
        
        // $typeClass = new \ReflectionClass($type);
        $param = $this->params()->fromQuery('param');
        $criteria = $this->params()->fromQuery('criteria');
        
        $searchD = $this->params()->fromQuery('search');
        $searchQ = $this->params()->fromQuery('searchQ');
        $service = $this->getServiceLocator();
        $translator = $service->get('translator');
        $searchL = '';
        $searchlogic = '';
        $sort = '';
        $log = Log::getInstance();
        $log->AddRow(" Get COLLECTION ref action n33: " . json_encode($this));
        if ($request->isPost()) {
            $dataRequest = $request->getPost()->toArray();
            $log->AddRow(" Get COLLECTION ref action n33: " . json_encode($dataRequest));
            if (isset($dataRequest['searchQ'])) {
                $data = json_decode($dataRequest['searchQ'], true);
                $searchQ = isset($dataRequest['searchQ']) ? $dataRequest['searchQ'] : '';
            }
        }
        $actionName = array();
        // $isCriteria = strpos($criteria, "-");
        $typeName = '';
        $laf = new MongoObjectFactory();
        if (isset($searchQ)) {
            // $request= $searchQ->toArray();
            $log->AddRow(" Get COLLECTION ref action n1: " . $searchQ);
            // $request=implode("",explode("\\",$searchQ));
            // str_replace('\\\\"', '\\"', $searchQ);
            // return stripslashes(trim($string));
            $request = stripslashes($searchQ);
            // $request = $searchQ;
            $log->AddRow(" Get COLLECTION ref action n2: " . json_encode($request));
            $data = json_decode($request, true);
            $log->AddRow(" Get COLLECTION ref action nX: " . json_encode($data));
            $sort = isset($data['sort']) ? $data['sort'] : '';
            $limit = isset($data['limit']) ? $data['limit'] : 0;
            $offset = isset($data['offset']) ? $data['offset'] : 0;
            $searchL = isset($data['search']) ? $data['search'] : '';
            $searchlogic = isset($data['searchLogic']) ? $data['searchLogic'] : '';
            $search["search"] = $searchL;
            $search["searchLogic"] = $searchlogic;
        } else {
            $request = $this->params()->fromQuery('request');
            
            if (isset($request)) {
                $data = json_decode($request, true);
                $log->AddRow(" Get COLLECTION ref action nX: " . json_encode($data));
                if (isset($data['search']) && strlen($data['search']) > 0) {
                    $search = array();
                    $paramKey = '';
                    $log->AddRow(" Get COLLECTION ref action nX1: " . json_encode($data));
                    if (isset($param) && strlen($param) > 1) {
                        // $actionName = $method;
                        $pos = strpos($param, "cg");
                        $paramKey = $param;
                    } else {
                        $log->AddRow(" Get COLLECTION ref action n1: " . json_encode($this->params()
                            ->fromQuery('class')));
                        $typeName = $this->params()->fromQuery('class');
                        // $typeName = ucfirst(substr($tableName, 0, strlen($tableName) - 1));
                        $reflectionMethod = new \ReflectionMethod($laf->getClassPath($typeName) . $typeName, "getPK");
                        $pk = $reflectionMethod->invoke(null, null);
                        // $log->AddRow(" Get COLLECTION ref action n1: " . json_encode($pk));
                        $paramKey = $pk;
                    }
                    // "search":[{"field":"date","type":"date","operator":"is","value":"6/7/2016"}],"searchLogic":"AND"
                    $log->AddRow(" Get COLLECTION ref action nX1: " . json_encode($paramKey));
                    $searchV = isset($data['search']) ? $data['search'] : '';
                    
                    $searchL = array();
                    $searchL['field'] = $paramKey;
                    $searchL['type'] = 'string';
                    $searchL['operator'] = 'contains';
                    $searchL['value'] = $searchV;
                    $log->AddRow(" Get COLLECTION ref action n4: " . json_encode($searchL));
                    // $searchlogic = isset($data['searchLogic']) ? $data['searchLogic'] : '';
                    $search["search"][] = $searchL;
                    $search["searchLogic"] = "AND";
                } else {
                    $search = '';
                }
                // $search["searchLogic"] = $searchlogic;
            } else {
                $search = '';
            }
        }
        $log->AddRow(" Get COLLECTION ref action n4: " . json_encode($searchL));
        $methodlist = $this->params()->fromQuery('methodName');
        $log->AddRow(" Get COLLECTION ref action n: " . json_encode($methodlist));
        $methods = explode(".", $methodlist);
        // remove get parent from new
        if ($action == 'new') {
            // unset($methods[0]);
        }
        $collection = array();
        if ($id == 0) {} else {
            $object = $laf->findObject($type, $id);
            $collection[] = $object;
        }
        $collectionInstances = array();
        $service = new Service();
        
        $collectionInstances = $service->getCollectionRef($methods, $collection, 0, 0, $search); // getCollectionRef($methods, $type, $id, $criteria, $collection);
        $log->AddRow(" Get collectionInstances : " . json_encode($collectionInstances)); // $log->AddRow(" Get COLLECTION ref action ns: " . json_encode($collectionInstances)); // $log->AddRow(" Get COLLECTION ref action nx: " . json_encode($collectionInstances));
        $collection = array();
        foreach ($collectionInstances as $collectionInstance) {
            $typeName = $collectionInstance->get_class_name($collectionInstance);
            $collection[] = $collectionInstance->jsonSerialize();
        }
        
        $listArray = array();
        $parameters = array();
        // get all extra columns
        $viewId = $this->params()->fromQuery('viewId');
        // $log->AddRow(" Get PARAMETERS : " . $viewId . " -- " . ' --- ' . $typeName);
        
        if ($viewId != 0) {
            $gridId = $this->params()->fromQuery('gridId');
            // $log->AddRow(" Get PARAMETERS11 : " . $gridId . " -- " . ' --- ' . $typeName);
            if (strlen($gridId) > 0) {
                $laf = new MongoObjectFactory();
                $view = $laf->findObject("View", $viewId);
                $log->AddRow(" Get PARAMETERS1 : " . $id . " -- " . json_encode($view) . ' --- ' . $typeName);
                if (! is_null($view)) {
                    $criteria = [];
                    $criteria[] = "name";
                    $criteria[] = $gridId;
                    $grid = $view->getInstancesCriteria("Component", $criteria);
                    $log->AddRow(" Get PARAMETERS2 : " . $id . " -- " . json_encode($grid) . ' --- ' . $gridId);
                    // $grid = $view->getReferenceOnPK("components", $gridId);
                    if (! is_null($grid) && sizeof($grid) > 0) {
                        $parameters = $grid[0]->getReferences("parameters");
                        $log->AddRow(" Get PARAMETERS3 : " . $id . " -- " . json_encode($parameters) . ' --- ' . $typeName);
                    }
                }
            }
        }
        
        foreach ($collection as $item) {
            if (isset($item)) {
                $itemNew = array();
                $itemNew = $item;
                // TODO optimize what we send back!
                /*
                 * if (isset($search) && strlen($search) > 1) {
                 * $itemNew = $item;
                 * } else {
                 * $itemNew = $item;
                 * }
                 */
                // $item['recid'] = (string) $item['_id']; // ->__ToString();
                $itemNew['id'] = (string) $item['_id']->{'$id'}; // ->__ToString(); // get pk
                if (isset($param) && strlen($param) > 1) {
                    // $actionName = $method;
                    $pos = strpos($param, "cg");
                    if ($pos === false) {
                        $itemNew['text'] = $item[$param];
                    } else {
                        $collection = array();
                        $reflectionMethod = new \ReflectionMethod($laf->getClassPath($typeName) . $typeName, $param);
                        $object1 = $laf->findObject($typeName, $item['_id']->__ToString());
                        $itemNew['text'] = $reflectionMethod->invoke($object1, null);
                    }
                } else {
                    $reflectionMethod = new \ReflectionMethod($laf->getClassPath($typeName) . $typeName, "getPK");
                    $pk = $reflectionMethod->invoke(null, null);
                    
                    $itemNew['text'] = $item[$pk];
                }
                
                $itemNew['recid'] = (string) $item['_id']->{'$id'};
                
                // Translate for canvas grids
                if (isset($itemNew['referencelink'])) {
                    // Create new key, header - the translation of the first grid
                    $itemNew['header'] = $translator->translate($itemNew['referencelink']);
                    
                    // Create new key, schemaTrans - the translation of all schema (schema is made like this gridRef&gridRef2_gridRef3..etc))
                    $itemNew['schemaTrans'] = explode("&", str_replace("+", "&", $itemNew['schema']));
                    foreach ($itemNew['schemaTrans'] as $key => &$value) {
                        $value = $translator->translate($value);
                    }
                }
                
                foreach ($parameters as $paramKey => $paramValue) {
                    if ($paramValue->type[0]["text"] == \Application\Document\Parameter::GRIDCOLUMN) {
                        $paths = explode("+", $paramValue->referencelink);
                        $itemNew[$paramValue->name] = "";
                        $firstTime = true;
                        foreach ($paths as $path) {
                            $methodsRef = explode(".", $path);
                            $object = $laf->findObject($typeName, $item['_id']->__ToString());
                            $collectionObj1 = array();
                            $collectionObj1[] = $object;
                            $log->AddRow(" Get GRIDCOLUMN " . json_encode($collectionObj1) . " -- " . $item['_id']->__ToString());
                            if ($firstTime) {
                                $itemNew[$paramValue->name] = $service->getCollectionRef($methodsRef, $collectionObj1);
                                $firstTime = false;
                            } else {
                                // $item = array_merge($item,$this->getCollectionRef($methodsRef, $collectionObj1));
                                $log->AddRow(" Get GRIDCOLUMN2 " . $paramValue->name . " -- " . json_encode($service->getCollectionRef($methodsRef, $collectionObj1)));
                                $result = $service->getCollectionRef($methodsRef, $collectionObj1);
                                if (is_string($result) == true) {
                                    $itemNew[$paramValue->name] = $itemNew[$paramValue->name] . " " . json_encode($result);
                                } else {
                                    $itemNew[$paramValue->name] = $itemNew[$paramValue->name] . "";
                                }
                            }
                            // $log->AddRow(" Get GRIDCOLUMN2 " . $paramValue->name. " -- " . $item[$paramValue->name] );
                        }
                    } else if ($paramValue->type[0]["text"] == \Application\Document\Parameter::FORMATFIELD) {
                        $strToExec = 'return $this->' . $paramValue->actionResponse;
                        // $log->AddRow(" Get FORMATFIELD " . json_encode($paramValue) . " -- " . $strToExec);
                        $object = $laf->findObject($typeName, $item['_id']->__ToString());
                        // $log->AddRow(" Get FORMATFIELD1 " . json_encode($object) . " -- " . $paramValue->name);
                        $itemNew[$paramValue->name] = $object->evaluate($strToExec);
                        // $log->AddRow(" Get FORMATFIELD2 " . $paramValue->name . " -- " . $item[$paramValue->name]);
                    }
                }
                // $log->AddRow(" Get SEARCHy " . json_encode($itemNew) . " -- " . json_encode($search));
                
                if (isset($searchD) && strlen($searchD) > 1) {
                    if ($this->substr_startswith($itemNew['text'], $searchD)) {
                        $listArray[] = $itemNew;
                    }
                } else {
                    $listArray[] = $itemNew;
                }
            }
        }
        
        return new JsonModel(array(
            "status" => "success",
            "total" => count($listArray),
            'items' => $listArray
        ));
    }

    function substr_startswith($haystack, $needle)
    {
        return substr($haystack, 0, strlen($needle)) === $needle;
    }

    private function getCollectionRef($methods, $type, $id, $criteria, $collection)
    {
        $laf = new MongoObjectFactory();
        $log = Log::getInstance();
        // $log->AddRow(" Get Col ref action : " .$id." -- " .json_encode($methods) . ' --- ' . json_encode($collection) . ' .... ' . $type);
        
        // $isCriteria = strpos($criteria, "-");
        foreach ($methods as $method) {
            $collectionNew = array();
            foreach ($collection as $object) {
                // if ($method . stringEndsWith("()")) {
                // execute on object
                if ($method == "getParent") {
                    $arrayNew = array();
                    // $log->AddRow(" 1Get Parent ref parent action : " . $id . " -- " . json_encode($object) . ' --- ');
                    
                    // $reflectionMethod = new \ReflectionMethod($type, 'getParent');
                    $arrayNew[] = $object->getParent();
                    // $log->AddRow(" 2Get Col ref parent action : " . json_encode($arrayNew) . ' .... ');
                    
                    $collectionNew = array_merge($collectionNew, $arrayNew);
                } else {
                    $actionName = substr($method, 0, 3);
                    $typeName = substr($method, 3);
                    
                    if ($id == 'undefined') {
                        $log->AddRow(" WE SHOULD NEVER BE HERE - FIND ALL for TYPE : " . json_encode($object) . ' .... ' . $typeName . ' --.' . $method);
                        // $collectionNew = $laf->findAllObjectJSON("\\Application\\Document\\" . $typeName);
                    } else {
                        $collectionNew = array_merge($collectionNew, $object->getInstancesCriteria($typeName, array()));
                    }
                }
            }
            $collection = $collectionNew;
        }
        return $collection;
    }

    private function getData($collection, $param)
    {
        $listArray = array();
        foreach ($collection as $item) {
            $item['recid'] = $item['_id']->__ToString();
            // $listArray[] = $item;
            foreach ($item as $key => $column) {
                
                if (strcmp($key, "recid") == 0) {} else if (sizeof($param) >= 1) {
                    if (strcmp($key, $param) == 0) {} else {
                        unset($item[$key]);
                    }
                }
            }
            $listArray[] = $item;
        }
        return $listArray;
    }

    public function getlistAction()
    {
        /*
         * $m = new \mongoclient();
         *
         * // select a database
         * $db = $m->zf2odm;
         * $users = $db->users;
         * $listusers = $users->find();
         */
        $laf = new MongoObjectFactory();
        $newCollection = array();
        $objectType = $this->params()->fromQuery('objectType');
        $objectId = $this->params()->fromQuery('objectId');
        $viewId = $this->params()->fromQuery('viewId');
        $gridId = $this->params()->fromQuery('gridId');
        $workspaceId = $this->params()->fromQuery('workspaceId');
        $methodlist = $this->params()->fromQuery('methodName');
        $methods = explode(".", $methodlist);
        
        if (strlen($objectId) > 1) {
            $itemFound = $laf->findObjectJSON($this->params()
                ->fromQuery('objectType'), $objectId);
            $collection[] = $itemFound;
            // $log->AddRow(" Find one id: " . json_encode($collection));
        } else {
            $collection = $laf->find($this->params()
                ->fromQuery('objectType'));
            // $log->AddRow(" WE SHOULD NEVER BE HERE: FIND ALL");
        }
        $param = $this->params()->fromQuery('param');
        
        foreach ($collection as $item) {
            // $listArray[] = $item;
            foreach ($item as $key => $column) {
                $arItems = array();
                if (is_array($column) == true) {
                    $arItem = array();
                    $i = 0;
                    foreach ($column as $itemS => $val) {
                        // if we have a DB ref then start to investigate
                        if (! is_null($val['$ref'])) {
                            // get ref field type
                            $refType = ucfirst(substr($val['$ref'], 0, strlen($val['$ref']) - 1));
                            // $log->AddRow(" Get REF Type : " . $refType . ' -- ' . $val['$ref'] . ' key' . $key);
                            // get field type -
                            $workspace = $this->getWorkspace($workspaceId);
                            $view = $workspace->getInstance("View", new \MongoId($viewId));
                            // $log->AddRow(" Get View Type : " . json_encode($view));
                            $typeClass = new \ReflectionClass($laf->getClassPath($objectType) . $objectType);
                            
                            $criteria = "name-" . $val['$ref'] . "-objectReferenceType-" . $refType;
                            $criteriaTo = explode("-", $criteria);
                            $field = $view->getOneInstanceCriteria("Field", $criteriaTo);
                            // $log->AddRow(" Found Field : " . json_encode($field));
                            if (! is_null($field)) {
                                // $log->AddRow(" Found Field : " . json_encode($field). " -- ". $field['type']);
                                // if
                                if ($field['typeReference'] == Field::TYPE_REF_VALUE_REMOTE || $field['typeReference'] == Field::TYPE_REF_VALUE || $field['typeReference'] == Field::TYPE_REFERENCE || $field['typeReference'] == Field::TYPE_REFERENCE_REMOTE) {
                                    // get reference type
                                    // $log->AddRow(" Found Object Ref field : " . json_encode($field) . ' -- ' . json_encode($val));
                                    // if type == one-to-one add a single item
                                    // get from DB item based on id
                                    $mongoObjectFactory = new MongoObjectFactory();
                                    $object = $mongoObjectFactory->findObject($refType, $val['$id']);
                                    // $log->AddRow(" Found Object Ref : " . json_encode($object));
                                    // get PK
                                    // set data for return
                                    $arItems['id'] = $val['$id'];
                                    $arItems['recid'] = $val['$id'];
                                    $arItems['text'] = $object->getPK();
                                } else {
                                    // $i = $i + 1;
                                    $arItem['id'] = $val['$id'];
                                    $arItem['recid'] = $val['$id'];
                                    $arItem['text'] = $val['$id'];
                                    $arItems[] = $arItem;
                                }
                            }
                        }
                    }
                    
                    $item[$key] = $arItems;
                }
            }
            $newCollection[] = $item;
        }
        
        $listArray = array();
        $columns = array();
        foreach ($newCollection as $item) {
            $item['recid'] = $item['_id']->__ToString();
            unset($item['_id']);
            $listArray[] = $item;
            foreach ($item as $key => $column) {
                if (is_array($column) == true) {
                    $columns[$key] = "+" . $key;
                } else {
                    $columns[$key] = $key;
                }
            }
        }
        $searchColumns = array();
        $arrayColumns = array();
        foreach ($columns as $key => $column) {
            $search['field'] = $key;
            $search['caption'] = ucfirst($key);
            $search['type'] = 'text';
            $searchColumns[] = $search;
            $col = array();
            $col['field'] = $key;
            $col['caption'] = ucfirst($key);
            $col['size'] = '100px';
            $col['sortable'] = true;
            $col['resizable'] = true;
            $type = array();
            // if is ARRAY column set the expectations
            if (strpos($column, "+") === 0) {
                // $col['size'] = '50%';
                $type['type'] = 'list';
                $name = ucfirst(substr($column, 1));
                $type['items'] = strtolower($name);
                $type['showAll'] = true;
                // $col['editable'] = true;
                // $type['url'] = 'http://localhost:8080/application/getMethodResultList?objectType=' . $objectType . '&methodName=get' . $name . '&id=0&param=id';
                // "http://localhost:8080/application/getMethodResult?objectType=\\Application\\Document\\Customer&methodName=getOrders&id=55e5c6238f7b68cf640041a7&param=id;
                // $type['render'] = 'function (record, index, col_index) { var html = this.getCellValue(index, col_index); return html.text;}';
                $col['editable'] = $type;
                // $col['render'] = 'function (record, index, col_index) { var html = this.getCellValue(index, col_index); return html.text;}';
            }
            $arrayColumns[] = $col;
        }
        
        return new JsonModel(array(
            'search' => $searchColumns,
            'columns' => $arrayColumns,
            'result' => $listArray
        ));
    }

    public function getformAction()
    {
        $laf = new MongoObjectFactory();
        $newCollection = array();
        $objectType = $this->params()->fromQuery('objectType');
        $objectId = $this->params()->fromQuery('objectId');
        $viewId = $this->params()->fromQuery('viewId');
        $workspaceId = $this->params()->fromQuery('workspaceId');
        $type = $this->params()->fromQuery('type');
        $condition = $this->params()->fromQuery('condition');
        
        // TAKE ACTION ID FOR PRELOAD PATH
        $actionId = $this->params()->fromQuery('actionId');
        
        $log = Log::getInstance();
        $log->AddRow(" Get Form action : " . $actionId . " ==" . $type . " == " . $objectId . ' --- ' . $workspaceId . ' .... ' . $objectType);
        if ($actionId == "inputForm" && isset($condition) && strlen($condition) > 0) {
            $log->AddRow(" Get INPUT FORM action : " . $condition . " ==" . $objectType . " == " . $objectId . ' --- ' . $workspaceId . ' .... ' . $objectType);
            
            $objectI = $laf->findObject($type, $objectId);
            $conditions = explode('.', $condition);
            $key = $objectI->getMasterState($conditions[0], $conditions[1]);
            $objectTypes = explode('.', $objectType);
            foreach ($objectTypes as $objT) {
                $objTs = explode('_', $objT);
                if ($objTs[0] == $key) {
                    $objectType = $objTs[1];
                }
            }
        }
        // PRELOAD PATH
        if ($actionId == "inputForm" || $actionId == "new") {
            
            // GET WORKSPACE BY $workspaceId
            $preWorkspace = $this->getWorkspace($workspaceId);
            
            // GET VIEW FROM THAT WORKSPACE by $viewId
            $preView = $preWorkspace->getInstance("View", new \MongoId($viewId));
            
            // USE CRITERIA TO GET FIELDS WITH getJSONCriteria
            $criteria = array();
            $criteria[] = 'object';
            $criteria[] = $objectType;
            
            // GET FIELD FROM VIEW WITH GIVEN CRITERIA
            $preField = $preView->getInstancesCriteria("Field", $criteria);
            $log->AddRow("preField " . json_encode($preField));
            
            // ITERATE FIELDS
            foreach ($preField as $field) {
                if ($field->preloadPath != '') {
                    if ($field->preloadPath[0] == "=") {
                        // REMOVE @ AND GET PATH
                        $field->preloadPath = substr($field->preloadPath, 1);
                        // USE getPathReferences() FROM Model TO SPLIT PATH AND TAKE WANTED FIELDS
                        $newCollection[$field->name] = json_decode($field->preloadPath);
                    } else if ($field->preloadPath[0] == "@") {
                        // TAKE INSTANCE
                        $instance = $laf->findObject($type, $objectId);
                        
                        // REMOVE @ AND GET PATH
                        $field->preloadPath = substr($field->preloadPath, 1);
                        
                        // / TEST ME FOR @THIS.A_FIELD
                        if (substr($field->preloadPath, 0, 4) == "this")
                            $field->preloadPath = substr($field->preloadPath, 5);
                        
                        // USE getPathReferences() FROM Model TO SPLIT PATH AND TAKE WANTED FIELDS
                        $newCollection[$field->name] = $instance->getPathReferences($field->preloadPath);
                    } else
                        // DEFAULT VALUE GIVEN IN createField()
                        $newCollection[$field->name] = $field->preloadPath;
                }
            }
        } else if (strlen($objectId) > 1) {
            $objectType = $this->params()->fromQuery('objectType');
            if (strpos($objectType, '.') > 0) {
                $objectSplit = explode('.', $objectType);
                $objectSplit = end($objectSplit);
                if (strpos($objectSplit, '[') > 0) {
                    $objectSplit = explode('[', $objectSplit);
                    $objectType = current($objectSplit);
                } else {
                    $objectType = $objectSplit;
                }
            } elseif (strpos($objectType, '[') > 0) {
                $objectSplit = explode('[', $objectType);
                $objectType = current($objectSplit);
            }
            if (substr($objectType, 0, 3) == 'get' || substr($objectType, 0, 2) == 'cg') {
                $objectType = substr($objectType, 3);
            }
            $instance = $laf->findObject($objectType, $objectId);
            $log->AddRow(" Find one id: " . json_encode($instance));
            $param = $this->params()->fromQuery('param');
            $workspace = $this->getWorkspace($workspaceId);
            $view = $workspace->getInstance("View", new \MongoId($viewId));
            $log->AddRow(" Get View Type : " . json_encode($view));
            $typeClass = new \ReflectionClass($laf->getClassPath($objectType) . $objectType);
            $nameRel = $typeClass->getShortName();
            $criteria = "object-" . $nameRel;
            $criteriaTo = explode("-", $criteria);
            $log->AddRow(" Found Field2 : " . json_encode($criteriaTo));
            $fields = $view->getJSONCriteria("Field", $criteriaTo);
            $log->AddRow(" Found Field3 : " . json_encode($fields));
            foreach ($fields as $field) {
                
                // prepare CheckBox Data if not boolean (W2UI ISSUE)
                if ($field['type'] == Field::TYPE_CHECKBOX) {
                    $log->AddRow(" Found Field4 : " . $instance->{$field['name']});
                    
                    if ($instance->{$field['name']} === "1" || $instance->{$field['name']} === "true") {
                        $instance->{$field['name']} = true;
                    } else {
                        $instance->{$field['name']} = false;
                    }
                }
                
                if ($field['type'] == Field::TYPE_FILE) {
                    $log->AddRow(" Found Field4 : " . json_encode($field));
                    $log->AddRow(" Found Field4 : " . json_encode($instance));
                    $path = getcwd() . $instance->{$field['name']};
                    $name = pathinfo($path, PATHINFO_BASENAME);
                    if (is_file($path)) {
                        $mime = mime_content_type($path);
                        $size = filesize($path);
                        
                        $data = file_get_contents($path);
                        $base64 = base64_encode($data);
                        $result = array(
                            'type' => $mime,
                            'name' => $name,
                            'content' => $base64,
                            'size' => $size
                        );
                        
                        $instance->{$field['name']} = array(
                            $result
                        );
                    } else {
                        $instance->{$field['name']} = array(
                            'type' => '',
                            'name' => '',
                            'content' => '',
                            'size' => 0
                        );
                    }
                }
                
                // $log->AddRow(" Found Field3 : " . json_encode($field));
                if (! is_null($field) && ! ($field['type'] == Field::TYPE_BUTTON)) {
                    
                    if ($field['typeReference'] == Field::TYPE_REF_VALUE || $field['typeReference'] == Field::TYPE_REF_VALUE_REMOTE) {
                        // $log->AddRow(" Found Field : " . json_encode($field) . " -- " . $field['type'] . " --- " . json_encode($instance->{$field['name']}));
                        
                        $reference = $instance->{$field['name']};
                        if (count($reference) > 0 && isset($reference[0]['$id'])) {
                            // get reference type
                            foreach ($reference as $ref) {
                                $refType = ucfirst(substr($field['name'], 0, strlen($field['name']) - 1));
                                // $log->AddRow(" Found Object Ref field : " . json_encode($ref) . ' -- ' . $refType);
                                // if type == one-to-one add a single item
                                // get from DB item based on id
                                $mongoObjectFactory = new MongoObjectFactory();
                                $object = $mongoObjectFactory->findObject($refType, $ref['$id']);
                                // $log->AddRow(" Found Object Ref : " . json_encode($object));
                                // get PK
                                // set data for return
                                $arItem['_id']['$id'] = $ref['$id'];
                                $arItem['id'] = $ref['$id'];
                                $arItem['recid'] = $ref['$id'];
                                $param = $field['actionExecution'];
                                if (isset($param) && strlen($param) > 1) {
                                    // $actionName = $method;
                                    $pos = strpos($param, "cg");
                                    if ($pos === false) {
                                        $arItem['text'] = $object->{$param};
                                    } else {
                                        
                                        $reflectionMethod = new \ReflectionMethod($laf->getClassPath($refType) . $refType, $param);
                                        
                                        $arItem['text'] = $reflectionMethod->invoke($object, null);
                                    }
                                } else {
                                    $arItem['text'] = $object->{$object->getPK()};
                                }
                                $newCollection[$field['name']][] = $arItem;
                            }
                        }
                    } else if ($field['typeReference'] == Field::TYPE_REFERENCE || $field['typeReference'] == Field::TYPE_REFERENCE_REMOTE || $field['typeReference'] == Field::TYPE_REF_VALUE_REMOTE) {
                        $reference = $instance->{$field['name']};
                        if (count($reference) > 0) {
                            // get reference type
                            $refType = ucfirst(substr($field['name'], 0, strlen($field['name']) - 1));
                            // $log->AddRow(" Found Object Ref field : " . json_encode($reference) . ' -- ' . $refType);
                            // if type == one-to-one add a single item
                            // get from DB item based on id
                            $mongoObjectFactory = new MongoObjectFactory();
                            $object = $mongoObjectFactory->findObject($refType, $reference[0]['$id']);
                            $log->AddRow(" Found Object Ref : " . json_encode($object));
                            // get PK
                            // set data for return
                            $arItem['id'] = $reference[0]['$id'];
                            $arItem['recid'] = $reference[0]['$id'];
                            $param = $field['actionExecution'];
                            if (isset($param) && strlen($param) > 1) {
                                // $actionName = $method;
                                $pos = strpos($param, "cg");
                                if ($pos === false) {
                                    $arItem['text'] = $object->{$param};
                                } else {
                                    $collection = array();
                                    $reflectionMethod = new \ReflectionMethod($laf->getClassPath($refType) . $refType, $param);
                                    
                                    $arItem['text'] = $reflectionMethod->invoke($object, null);
                                }
                            } else {
                                $arItem['text'] = $object->{$object->getPK()};
                            }
                            $newCollection[$field['name']] = $arItem;
                        }
                    } else if (isset($instance->{$field['name']})) {
                        $newCollection[$field['name']] = $instance->{$field['name']};
                    }
                }
            }
        }
        if (! isset($instance->{'_id'}) && isset($instance->{'_id'}['$id'])) {
            $newCollection['recid'] = $instance->{'_id'}['$id'];
        }
        $log->AddRow(" Found FieldX : " . json_encode($newCollection));
        return new JsonModel($newCollection);
    }

    public function typesAction()
    {}

    /*
     * public function savefieldAction()
     * {
     * $request = $this->getRequest();
     * $result = false;
     * if ($request->isPost()) {
     * $data = $request->getPost()->toArray();
     * $data['data']['object'] = $data['objectType'];
     * $mongoObjectFactory = new MongoObjectFactory();
     * $result = $mongoObjectFactory->saveObject('Application\\Document\\Field', $data['data']);
     * }
     *
     * return new JsonModel(array(
     * 'success' => $result
     * ));
     * }
     */
    public function saveobjectAction()
    {
        $request = $this->getRequest();
        $result = false;
        if ($request->isPost()) {
            $data = $request->getPost()->toArray();
            if (empty($data['data']['recid'])) {
                $mongoObjectFactory = new MongoObjectFactory();
                $result = $mongoObjectFactory->createAndAdd($data['parentType'], $data['parentId'], $data['objectType'], $data['data']);
            } else {
                // update object
                $mongoObjectFactory = new MongoObjectFactory();
                $result = $mongoObjectFactory->update($data['objectType'], $data['data']['recid'], $data['data']);
            }
        }
        
        return new JsonModel(array(
            'success' => $result
        ));
    }

    public function deleteobjectsAction()
    {
        $request = $this->getRequest();
        $result = false;
        $mtime = microtime();
        $mtime = explode(' ', $mtime);
        $mtime = $mtime[1] + $mtime[0];
        $tId = "" . $mtime;
        $_SESSION["transaction_id"] = $tId;
        $_SESSION["transaction_type"] = "delete";
        $_SESSION["transaction_name"] = "remove";
        
        if ($request->isPost()) {
            try {
                $data = $request->getPost()->toArray();
                if (strpos($data['objectType'], '.') > 0) {
                    $objectSplit = explode('.', $data['objectType']);
                    $objectSplit = end($objectSplit);
                    if (strpos($objectSplit, '[') > 0) {
                        $objectSplit = explode('[', $objectSplit);
                        $data['objectType'] = current($objectSplit);
                    }
                } elseif (strpos($data['objectType'], '[') > 0) {
                    $objectSplit = explode('[', $data['objectType']);
                    $data['objectType'] = current($objectSplit);
                }
                if (substr($data['objectType'], 0, 3) == 'get' || substr($data['objectType'], 0, 2) == 'cg') {
                    $data['objectType'] = substr($data['objectType'], 3);
                }
                
                $_SESSION["transaction_objectId"] = $data['objectType'] . " - " . json_encode($data['data']);
                $objectIds = $data['data'];
                if (! empty($objectIds)) {
                    foreach ($objectIds as $objectId) {
                        $this->deleteObject($objectId, $data['objectType']);
                    }
                }
                
                $result = true;
            } catch (\Exception $e) {
                print_r($e->getMessage());
                exit();
                $log = Log::getInstance();
                $result = false;
                $log->AddRow(" EXCEPTION : " . $e);
            }
        }
        $_SESSION["transaction_id"] = "";
        $_SESSION["transaction_type"] = "";
        $_SESSION["transaction_name"] = "";
        $_SESSION["transaction_objectId"] = "";
        return new JsonModel(array(
            'success' => $result
        ));
    }

    public function savestateAction()
    {
        $request = $this->getRequest();
        
        $result = true;
        try {
            if ($request->isPost()) {
                $data = $request->getPost()->toArray();
                $dm = $this->getServiceLocator()->get('doctrine.documentmanager.odm_default');
                $session = $this->getSession();
                $identity = $session->getIdentity();
                $user = $dm->getRepository("\\Application\\Document\\User")->findOneBy(array(
                    "id" => $identity['id']
                ));
                $stateService = new StateService();
                $result = $stateService->saveUserState($data['view'], $identity, $user, $data['grid'], $data['data'], "gridstate");
            }
        } catch (\Exception $e) {
            \Application\Controller\Log::getInstance()->AddRow(" Exception -  " . $e->getMessage());
        }
        
        return new JsonModel(array(
            'success' => $result
        ));
    }

    /**
     * Delete the Object and all children
     *
     * @param string|integer $childId            
     * @param string $childClass            
     * @param string $parentClass            
     * @param string $parentId            
     */
    private function deleteObject($childId, $childClass)
    {
        $mongoObjectFactory = new MongoObjectFactory();
        // $log = Log::getInstance();
        $object = $mongoObjectFactory->findObject($childClass, $childId);
        if (null !== $object && null !== $object->getParent()) {
            $object->prepareDelete();
            $objectN = $mongoObjectFactory->findObject($childClass, $childId);
            $parent = $objectN->getParent();
            $parent->remove($objectN);
        }
        
        /*
         * $mongoObjectFactory = new MongoObjectFactory();
         * $object = $mongoObjectFactory->findObject($childClass, $childId);
         *
         * $parent = $object->getParent();
         * $parent->remove($childId, $childClass);
         *
         *
         * $this->deleteChildFromParent($parentClass, $parentId, $childClass, $childId);
         *
         * // get relations between object and children
         * $relationsArray = $object->getOwningRelations();
         * $relations = array();
         * foreach ($object as $key => $field) {
         * if (is_array($field) && is_array($relationsArray) && in_array($key, $relationsArray)) {
         * foreach ($field as $item) {
         * $relations[$item['$ref']][] = new \MongoId($item['$id']);
         * }
         * }
         * }
         * $reflectionMethod = new \ReflectionMethod($childClass, 'removeObjects');
         *
         * if (! empty($relations)) {
         * foreach ($relations as $key => $relation) {
         * $reflectionMethod->invokeArgs($object, array(
         * $relation,
         * $key
         * ));
         * }
         * }
         *
         * // remove the main object
         * $typeClass = new \ReflectionClass($childClass);
         * $name = \strtolower($typeClass->getShortName()) . 's';
         * $reflectionMethod->invokeArgs($object, array(
         * array(
         * new \MongoId($childId)
         * ),
         * $name
         * ));
         */
    }

    public function exportAction()
    {
        $object = $this->params()->fromQuery('object');
        $viewId = $this->params()->fromQuery('viewId');
        $gridId = $this->params()->fromQuery('gridId');
        $parentObject = $this->params()->fromQuery('parentObject', '');
        $parentId = $this->params()->fromQuery('parentId');
        $searchData = $this->params()->fromQuery('searchData', 0);
        $searchField = $this->params()->fromQuery('searchField', '');
        $sort = $this->params()->fromQuery('sortData', 0);
        $data = array(
            'object' => $object,
            'viewId' => $viewId,
            'gridId' => $gridId,
            'parentObject' => $parentObject,
            'parentId' => $parentId,
            'searchData' => $searchData,
            'searchField' => $searchField,
            'sort' => $sort
        );
        
        $reportingService = new ReportingService();
        $reportingService->exportCSV($data);
    }

    public function exportprintAction()
    {
        $log = Log::getInstance();
        $object = $this->params()->fromQuery('object');
        $viewId = $this->params()->fromQuery('viewId');
        $gridId = $this->params()->fromQuery('gridId');
        $parentObject = $this->params()->fromQuery('parentObject', '');
        $parentId = $this->params()->fromQuery('parentId');
        $searchData = $this->params()->fromQuery('searchData', 0);
        $searchField = $this->params()->fromQuery('searchField', '');
        $attributes = array();
        $arrayResults = array();
        $fileName = 'noData';
        if (isset($parentId) && isset($parentObject) && sizeof($parentId) > 0) {
            $attributes = array();
            $arrayResults = array();
            $session = $this->getSession();
            $identity = $session->getIdentity();
            $fileName = $parentObject . "_" . $parentId . "_" . $object;
            $laf = new MongoObjectFactory();
            if ($parentId == 0) {
                $results = $laf->find($object);
            } else {
                
                $identity = $session->getIdentity();
                $limit = 0;
                $offset = 0;
                $search = array();
                $service = $this->getServiceLocator();
                $translator = $service->get('translator');
                $request = $this->getRequest();
                $data = json_decode($searchData, true);
                $limit = isset($data['limit']) ? $data['limit'] : 0;
                $offset = isset($data['offset']) ? $data['offset'] : 0;
                // $log->AddRow(" Export actionX : " . json_encode($data));
                $searchL = $data; // isset($data['search']) ? $data['search'] : '';
                if ($searchField == 'multi') {
                    $searchlogic = 'and'; // isset($data['searchLogic']) ? $data['searchLogic'] : '';
                } else {
                    $searchlogic = 'or';
                }
                $search["search"] = $searchL;
                $search["searchLogic"] = $searchlogic;
                $sort = isset($data['sort']) ? $data['sort'] : '';
                $stateService = new StateService();
                if ($sort != '') {
                    $stateService->saveState($viewId, $gridId, $sort, "sort");
                } else {
                    $sortD = $stateService->getState($viewId, $gridId, "sort");
                    if (isset($sortD)) {
                        $sort = array();
                        $sort = json_decode($sortD, TRUE);
                    }
                }
                // no param and criteria?
                $service = new Service();
                $results = $service->prepareData($parentId, $parentObject, null, $object, $viewId, $gridId, $identity['id'], null, $translator, $limit, $offset, $search);
            }
            $attributesList = $results['columns'];
            $recidFound = false;
            foreach ($attributesList as $listAttr) {
                if (isset($listAttr['hidden']) && $listAttr['hidden'] == true) {} else {
                    $key = $listAttr['caption'];
                    $attributes[$key] = $key;
                    if ($key == 'Recid') {
                        $recidFound = true;
                    }
                }
            }
            foreach ($results['records'] as $res) {
                $lastKey = '';
                $lastValue = '';
                foreach ($res as $key => $item) {
                    if (! is_array($item)) {
                        $value[$key] = $item;
                    } else {
                        $value[$key] = '';
                    }
                    $lastKey = $key;
                }
                // if ($recidFound == true) {
                // $log->AddRow(" Export action : " . json_encode($value));
                if ($lastKey == '_id') {
                    unset($value[$lastKey]);
                }
                // }
                $arrayResults[] = $value;
            }
        } else {
            $log->AddRow(" No Parent Id : " . json_encode($attributes));
            $attributes['name'] = 'name';
            $arrayResults[] = array(
                'name' => "value"
            );
            $fileName = 'noData';
        }
        // $f = fopen('php://memory', 'w');
        // fputcsv($f, $attributes, ";");
        $template = "<html><body><table>";
        if (! empty($attributes)) {
            $template .= '<tr>';
            foreach ($attributes as $attrib) {
                $template .= '<th>' . $attrib . '</th>';
            }
            $template .= '</tr>';
        }
        
        if (! empty($arrayResults)) {
            foreach ($arrayResults as $result) {
                $template .= '<tr>';
                foreach ($result as $res) {
                    $template .= '<td>' . $res . '</td>';
                }
                $template .= '</tr>';
            }
        }
        
        $template .= "</table></body></html>";
        
        $dompdf = new Dompdf();
        $dompdf->set_option('enable_remote', TRUE);
        // $dompdf->
        
        $dompdf->loadHtml($template);
        $dompdf->render();
        // $dompdf->stream("print.pdf");
        $output = $dompdf->output();
        $uniqId = uniqid();
        $localPath = getcwd() . "/public/prints/";
        
        if (isset($_SESSION)) {
            $s = "http://localhost:8080/application";
            $url = $_SESSION['urlLink'];
            if (isset($url)) {
                $urlTo = explode("/", $url);
                $size = sizeof($urlTo);
                $s = 'http:';
                for ($x = 1; $x <= $size - 3; $x ++) {
                    $s = $s . '/' . $urlTo[$x];
                }
            }
        }
        $publicPath = $s . "/prints/" . (string) $uniqId . $fileName . ".pdf";
        // make a folder with hotel id if doesn't exist one
        if (! file_exists(realpath($localPath))) {
            mkdir($localPath, 0777, true);
        }
        $path = $localPath . (string) $uniqId . $fileName . ".pdf";
        // unlink($path);
        file_put_contents($path, $output);
        
        return new JsonModel(array(
            'url' => $publicPath,
            'success' => true
        ));
    }

    public function importAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            
            $files = $request->getFiles()->toArray();
            if (! empty($files)) {
                if (strpos($files['file']['name'], '.csv') > 0) {
                    $file = $files['file']['tmp_name'];
                    $fileName = str_replace('.csv', '', $files['file']['name']);
                    $fileName = explode("_", $fileName);
                    $parentObject = $fileName[0];
                    $parentId = $fileName[1];
                    $objectName = $fileName[2];
                    
                    $result = array();
                    $keys = array();
                    if (($handle = fopen($file, "r")) !== FALSE) {
                        $raw = 1;
                        while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                            
                            $num = count($data);
                            // $result[] = $data;
                            if ($raw == 1) {
                                $keys = $data;
                            } else {
                                for ($c = 0; $c < $num; $c ++) {
                                    if ($keys[$c] != 'recid' && $keys[$c] != 'id' && $keys[$c] != 'deleted') {
                                        $item[$keys[$c]] = $data[$c];
                                    }
                                }
                                $result[] = $item;
                            }
                            $raw ++;
                        }
                    }
                    try {
                        
                        $mongoObjectFactory = new MongoObjectFactory();
                        $container = $mongoObjectFactory->findObject($parentObject, $parentId);
                        
                        foreach ($result as $item) {
                            $container->add($objectName, $item);
                        }
                        $this->flashMessenger()->addSuccessMessage('Data from file were imported !');
                        return $this->redirect()->toRoute('home', array(
                            'action' => 'import'
                        ));
                    } catch (\Exception $e) {
                        $this->flashMessenger()->addErrorMessage('An error has occured !');
                        return $this->redirect()->toRoute('home', array(
                            'action' => 'import'
                        ));
                    }
                }
            }
        }
        
        return new ViewModel(array(
            'flashMessages' => $this->flashMessenger()
        ));
    }

    /*
     * public function printAction()
     * {
     * $object = $this->params()->fromQuery('object');
     * $workspace = $this->params()->fromQuery('workspace');
     * $laf = new MongoObjectFactory();
     * $results = $laf->find($object);
     * $criteria = array(
     * 'entity' => $object,
     * 'parent.$id' => $workspace
     * $object->{$object->getPK()};
     * );
     * $resultTemplate = $laf->findObjectByCriteria("\\Application\\Document\\Template", $criteria);
     *
     * $arrayResults = array();
     * foreach ($results as $res) {
     * foreach ($res as $key => $item) {
     * if (! is_array($item)) {
     * $value[$key] = $item;
     * } else {
     * $value[$key] = '';
     * }
     * }
     * $value['recid'] = $value['_id']->__ToString();
     * unset($value['_id']);
     * $arrayResults[] = $value;
     * }
     *
     * // get columns from object
     * $classObject = $laf->findObject($object, null);
     *
     * $reflectionMethod = new \ReflectionMethod($classObject, 'getAttributes');
     * $fields = $reflectionMethod->invoke($classObject);
     * foreach ($fields as $key => $field) {
     * $attributes[$key] = $key;
     * }
     *
     * $service = new Service();
     * $data = array(
     * "main" => array(
     * "date" => "February 24, 2012",
     * "customer" => "John Doe",
     * "total" => "\$100.00"
     * ),
     * "lists" => array(
     * "items" => array(
     * array(
     * "quantity" => "2",
     * "item" => "Some Item",
     * "unit" => "\$25.00",
     * "total" => "\$50.00"
     * ),
     * array(
     * "quantity" => "5",
     * "item" => "Some Other Item",
     * "unit" => "\$10.00",
     * "total" => "\$50.00"
     * )
     * )
     * )
     * );
     *
     * $data['lists']['items'] = $arrayResults;
     * if (! empty($resultTemplate)) {
     * $template = $service->reportBuilder($resultTemplate['text'], $data);
     * } else {
     * $template = "No template!";
     * }
     * // print_r($view->render($viewModel));exit;
     * $dompdf = new Dompdf();
     * $dompdf->loadHtml($template);
     * $dompdf->render();
     * // $dompdf->stream("print.pdf");
     * $output = $dompdf->output();
     * unlink("public/print.pdf");
     * file_put_contents("public/print.pdf", $output);
     *
     * return new JsonModel(array(
     * 'url' => $this->request->getBasePath() . "/print.pdf",
     * 'success' => true
     * ));
     * }
     */
    public function exportgeneralAction()
    {
        $session = $this->getSession();
        $dm = $this->getServiceLocator()->get('doctrine.documentmanager.odm_default');
        $identity = $session->getIdentity();
        $user = $dm->getRepository("\\Application\\Document\\User")->findOneBy(array(
            "id" => $identity['id']
        ));
        $mongoObjectFactory = new MongoObjectFactory();
        
        $result = $this->exportJson(array(
            new \MongoId($user->getOrganization()
                ->getActiveWorkspace()
                ->getId())
        ), 'workspaces', $mongoObjectFactory, array());
        
        file_put_contents('export/' . $user->getOrganization()->getActiveWorkspace() . ".json", json_encode($result));
        
        return new ViewModel();
    }

    public function getOrganization()
    {
        $user = $this->getUser();
        return $user->getOrganization()->getId();
    }

    public function getUser()
    {
        $session = $this->getSession();
        $dm = $this->getServiceLocator()->get('doctrine.documentmanager.odm_default');
        $identity = $session->getIdentity();
        $user = $dm->getRepository("\\Application\\Document\\User")->findOneBy(array(
            "id" => $identity['id']
        ));
        return $user;
    }

    private function importJson($class, $result)
    {
        $data = json_decode($result, true);
        $className = ucfirst($class);
        /* print $class."#".$className.'$</br>'; */
        $mongoObjectFactory = new MongoObjectFactory();
        
        if ($className != 'Organization') {
            if ($className != 'Workspace') {
                foreach ($data as $key => $res) {
                    if (isset($res['_id'])) {
                        $res['recid'] = $res['_id']['$id'];
                        unset($res['_id']);
                    }
                    $mongoObjectFactory->saveObject($className, $res);
                }
            } else {
                $data['recid'] = $data['_id']['$id'];
                unset($data['_id']);
                $mongoObjectFactory->saveObject($className, $data);
            }
        } else {
            $workSpaceId = 0;
            foreach ($data as $key => $item) {
                $workSpaceId = $item['workspaces']['$id']['$id'];
            }
            
            $session = $this->getSession();
            $dm = $this->getServiceLocator()->get('doctrine.documentmanager.odm_default');
            $identity = $session->getIdentity();
            $user = $dm->getRepository("\\Application\\Document\\User")->findOneBy(array(
                "id" => $identity['id']
            ));
            $organization = $user->getOrganization();
            // \Doctrine\Common\Util\Debug::dump($organization);
            // TODO: ROOOT
            $workspace = new \Application\Document\Workspace();
            $workspace->setId($workSpaceId);
            $organization->setWorkspace($workspace);
            /*
             * print $workSpaceId;
             * \Doctrine\Common\Util\Debug::dump($organization);exit;
             */
            $dm->persist($workspace);
            $dm->persist($organization);
            $dm->flush();
        }
    }

    public function listchatAction()
    {
        $page = $this->params()->fromRoute('id', '');
        
        // in your constructor
        $this->_view = new ViewModel();
        
        // in your initialize values method
        $this->_view->setVariable("id", $page);
        $layout = $this->params()->fromQuery('layout');
        $parentType = $this->params()->fromQuery('parentType');
        $parentId = $this->params()->fromQuery('parentId');
        $viewId = $this->params()->fromQuery('viewId');
        // in your initialize values method
        $this->_view->setVariable("organization", $_SESSION['organization']);
        // in your initialize values method
        $this->_view->setVariable("parentId", $parentId);
        // in your initialize values method
        $this->_view->setVariable("parentType", $parentType);
        if (is_null($viewId) || $viewId == 0) {
            $this->_view->setVariable("viewId", "0");
        } else {
            // in your initialize values method
            $this->_view->setVariable("viewId", $viewId);
        }
        $this->_view->setTerminal(true);
        // in your controller action
        return $this->_view;
    }

    private function getWorkspace($id)
    {
        $mongoObjectFactory = new MongoObjectFactory();
        return $mongoObjectFactory->findObject("Workspace", $id);
    }

    private function getSession()
    {
        $dm = $this->getServiceLocator()->get('doctrine.documentmanager.odm_default');
        $session = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
        return $session;
    }
}
