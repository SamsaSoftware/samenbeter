<?php
namespace Api\Controller;

use Application\DatabaseConnection\Database;
use Application\Document\Setting;
use Application\Service\Service;
use Application\Document\Field;
use Application\Service\UserService;
use Dompdf\Dompdf;
use Symfony\Component\HttpFoundation\JsonResponse;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Application\Controller\MongoObjectFactory;
use Application\Controller\Log;
use Application\Service\RESTService;
use Swagger\Annotations as SWG;

class ApiController extends \Zend\Mvc\Controller\AbstractRestfulController
{

    public function indexAction()
    {
        $request = $this->getRequest();
        $headers = $request->getHeaders();
        
        $userToken = "qwertyuiopasdgjklzxcvbnm123456789";
        // $headerAuth = $headers->get('Authorization')->getFieldValue();
        
        return new \Zend\View\Model\JsonModel(array(
            'Authorization' => base64_encode($userToken)
        ));
    }

    private function getWorkspace($id)
    {
        $mongoObjectFactory = new MongoObjectFactory();
        return $mongoObjectFactory->findObject("Workspace", $id);
    }

    /**
     * Request REST API call :
     *
     * server/api/request/COMPANY_NAME/object/METHOD
     * - will be executed on active workspace for company COMPANY_NAME
     */
    public function requestAction()
    {
        $request = $this->getRequest();
        $response = null;
        if ($request->isPost() || $request->isGet()) {
            $organization = $this->params()->fromRoute('organization', '');
            $action = $this->params()->fromRoute('id', '');
            $data = $request->getPost()->toArray();
            if (count($data) == 0) {
                $data = $this->params()->fromQuery();
            }
            $log = Log::getInstance();
            // $log->AddRow(" Get Form action : " . $action . " ==" . json_encode($data) . " == " . $organization );
            
            $_SESSION['organization'] = $organization;
            try {
                
                $restService = new RESTService($organization); // , $_REQUEST, $_SERVER['HTTP_ORIGIN']);
                
                $response = $restService->processAPI($action, $data);
            } catch (\Exception $e) {
                
                $response = array(
                    'error' => $e->getMessage()
                );
            }
        } else {
            $response = new \Api\Response\ApiResponse(126);
        }
        
        return new \Zend\View\Model\JsonModel($response);
    }

    public function contentAction()
    {
        $request = $this->getRequest();
        $response = null;
        $this->_view = new ViewModel();
        if ($request->isPost() || $request->isGet()) {
            $organization = $this->params()->fromRoute('organization', '');
            $action = $this->params()->fromRoute('id', '');
            $data = $request->getPost()->toArray();
            if (count($data) == 0) {
                $data = $this->params()->fromQuery();
            }
            $log = Log::getInstance();
            $log->AddRow(" Get Content action : " . $action . " ==" . json_encode($data) . " == " . $organization);
            
            $_SESSION['organization'] = $organization;
            try {
                
                $restService = new RESTService($organization); // , $_REQUEST, $_SERVER['HTTP_ORIGIN']);
                $httpresponse = new \Zend\HTTP\Response();
                // $httpresponse->setStatusCode(\Zend\HTTP\Response::STATUS_CODE_200);
                // $httpresponse->getHeaders()->addHeaders([]);
                $response = $restService->processAPI($action, $data);
                $string = preg_replace('/\s+/', ' ', trim($response));
                $this->_view->setVariable("content", $string); // $object['$viewId']);
                $this->_view->setTerminal(true);
                // $httpresponse = \Zend\HTTP\Response::fromString('<html><body>' . $restService->processAPI($action, $data) . '</body></html>');
            } catch (\Exception $e) {
                $log->AddRow(" ERROR : " . $e);
                $this->_view->setVariable(" Something went wrong... contact Webmaster ");
            }
        }
        
        // in your controller action
        return $this->_view;
    }

    public function generateTokenAction()
    {
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $data = $request->getPost()->toArray();
                $userService = new UserService();
                $result = $userService->generateToken($data['email']);
                return new \Zend\View\Model\JsonModel(array(
                    "result" => $result
                ));
            }
        } catch (\Exception $e) {
            return new \Zend\View\Model\JsonModel(array(
                $e->getMessage()
            ));
        }
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
        
        // TAKE ACTION ID FOR PRELOAD PATH
        $actionId = $this->params()->fromQuery('actionId');
        $request = $this->getRequest();
        $headers = $request->getHeaders();
        // \Doctrine\Common\Util\Debug::dump('aa');exit;
        $userToken = "qwertyuiopasdgjklzxcvbnm123456789";
        $headerAuth = base64_decode($headers->get('Authorization')->getFieldValue());
        
        // $auth = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
        $dm = $this->getServiceLocator()->get('doctrine.documentmanager.odm_default');
        // $identity = $auth->getIdentity();
        $sessionMgr = new \Authentication\Controller\SessionManager();
        
        if ($sessionMgr->validateToken($dm, $headerAuth) === true) {
            $session = $sessionMgr->getSessionOnToken($dm, $headerAuth);
            
            $log = Log::getInstance();
            $log->AddRow(" Get Form action : " . $actionId . " ==" . $type . " == " . $objectId . ' --- ' . $workspaceId . ' .... ' . $objectType);
            
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
                        
                        // @ - NOT DEFAULT VALUE
                        if ($field->preloadPath[0] == "@") {
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
            } else 
                if (strlen($objectId) > 1) {
                    $objectIdList = explode("-", $objectId);
                    $i = 0;
                    $criteria = [];
                    if (count($objectIdList) == 1) {
                        $criteria['_id'] = new \MongoId($objectIdList[0]);
                    } else
                        foreach ($objectIdList as $key => $value) {
                            if (! is_array($value) && (strpos($value, '@') !== FALSE)) {
                                $keyValue = substr($value, 1);
                                \Application\Controller\Log::getInstance()->AddRow(" EXECUTE -< " . $key . " >-on " . $value . ' --> ' . $objectIdList[$i - 1]);
                                
                                if (isset($_SESSION[$keyValue])) {
                                    \Application\Controller\Log::getInstance()->AddRow(" EXECUTEFINd -< " . $_SESSION[$keyValue] . " >-on " . $keyValue . ' --> ');
                                    $newVal = $_SESSION[$keyValue];
                                    $criteria[$objectIdList[$i - 1]] = $newVal;
                                } else {
                                    $criteria[$objectIdList[$i - 1]] = $keyValue;
                                }
                            }
                            $i = $i + 1;
                        }
                    
                    $instance = $laf->findObjectByCriteria($this->params()
                        ->fromQuery('objectType'), $criteria);
                    $log->AddRow(" Find one id: " . json_encode($instance));
                    $param = [];
                    $workspace = $this->getWorkspace($workspaceId);
                    $log->AddRow(" Get wk Type : " . json_encode($workspace));
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
                        $log->AddRow(" Found Field4 : " . $field['name']);
                        // prepare CheckBox Data if not boolean (W2UI ISSUE)
                        if ($field['type'] == Field::TYPE_CHECKBOX) {
                            $log->AddRow(" Found Field4 : " . $field['name'] . $instance[$field['name']]);
                            
                            if ($instance[$field['name']] === "1" || $instance[$field['name']] === "true") {
                                $instance[$field['name']] = true;
                            } else {
                                $instance[$field['name']] = false;
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
                                            $pos = strpos($param, "cgt");
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
                            } else 
                                if ($field['typeReference'] == Field::TYPE_REFERENCE || $field['typeReference'] == Field::TYPE_REFERENCE_REMOTE || $field['typeReference'] == Field::TYPE_REF_VALUE_REMOTE) {
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
                                            $pos = strpos($param, "cgt");
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
                                } else
                                    $log->AddRow(" Found Field43 : " . $field['name'] . " == " . json_encode($instance));
                            if (isset($instance[$field['name']])) {
                                $log->AddRow(" Found Field45 : " . $field['name']);
                                
                                $newCollection[$field['name']] = $instance[$field['name']];
                            }
                        }
                    }
                }
            if (isset($instance['_id'])) {
                $newCollection['recid'] = "" . $instance['_id'];
            }
        }
        $log->AddRow(" Found FieldX : " . json_encode($newCollection));
        return new JsonModel($newCollection);
    }

    public function changePasswordAction()
    {
        try {
            $request = $this->getRequest();
            $headers = $request->getHeaders();
            $service = $this->getServiceLocator();
            $translator = $service->get('translator');
            $headerAuth = base64_decode($headers->get('Authorization')->getFieldValue());
            
            $dm = $this->getServiceLocator()->get('doctrine.documentmanager.odm_default');
            
            $sessionMgr = new \Authentication\Controller\SessionManager();
            if ($sessionMgr->validateToken($dm, $headerAuth) === true) {
                $session = $sessionMgr->getSessionOnToken($dm, $headerAuth);
                $data = $request->getPost()->toArray();
                $userService = new UserService();
                $result = $userService->changePassword($data);
                if ($result) {
                    $response = $translator->translate('password_changed_successfully');
                } else {
                    $response = $translator->translate('password_not_match');
                }
            } else {
                $response = $translator->translate('error_occurred');
            }
            
            return new JsonModel(array(
                $response
            ));
        } catch (\Exception $e) {
            return new JsonModel(array(
                $translator->translate('error_occurred')
            ));
        }
    }

    /**
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function getMethodResultAction()
    {
        $request = $this->getRequest();
        $headers = $request->getHeaders();
        // \Doctrine\Common\Util\Debug::dump('aa');exit;
        $userToken = "qwertyuiopasdgjklzxcvbnm123456789";
        $headerAuth = base64_decode($headers->get('Authorization')->getFieldValue());
        
        // $auth = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
        $dm = $this->getServiceLocator()->get('doctrine.documentmanager.odm_default');
        // $identity = $auth->getIdentity();
        $sessionMgr = new \Authentication\Controller\SessionManager();
        
        if ($sessionMgr->validateToken($dm, $headerAuth) === true) {
            $session = $sessionMgr->getSessionOnToken($dm, $headerAuth);
            // $session = $this->getSession();
            // $identity = $session->getIdentity();
            $limit = 0;
            $offset = 0;
            $search = array();
            $service = $this->getServiceLocator();
            $translator = $service->get('translator');
            $request = $this->getRequest();
            
            $searchL = '';
            $searchlogic = '';
            $sort = '';
            if ($request->isPost()) {
                $dataRequest = $request->getPost()->toArray();
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
            
            $gridId = $this->params()->fromQuery('gridId');
            if ($sort != '') {
                // $this->saveState($viewId, $gridId, $sort, "sort");
            } else {
                // $sortD = $this->getState($viewId, $gridId, "sort");
                $sortD = null;
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
                ->fromQuery('methodName'), $viewId, $gridId, $session->getUserId(), $this->params()
                ->fromQuery('criteria'), $translator, $limit, $offset, $search, $sort, $this->params()
                ->fromQuery('column'));
        }
        return new JsonModel($results);
    }

    public function getMethodResultListReferenceAction()
    {
        $request = $this->getRequest();
        $headers = $request->getHeaders();
        // \Doctrine\Common\Util\Debug::dump('aa');exit;
        $userToken = "qwertyuiopasdgjklzxcvbnm123456789";
        $headerAuth = base64_decode($headers->get('Authorization')->getFieldValue());
        $listArray = array();
        // $auth = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
        $dm = $this->getServiceLocator()->get('doctrine.documentmanager.odm_default');
        // $identity = $auth->getIdentity();
        $sessionMgr = new \Authentication\Controller\SessionManager();
        
        if ($sessionMgr->validateToken($dm, $headerAuth) === true) {
            \Application\Controller\Log::getInstance()->AddRow('VALIDATE TOKEN1 ' . json_encode($headerAuth));
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
                    // $log->AddRow(" Get PARAMETERS1 : " . $id . " -- " . json_encode($view) . ' --- ' . $typeName);
                    
                    if (! is_null($view)) {
                        $grid = $view->getReferenceOnPK("components", $gridId);
                        if (! is_null($grid)) {
                            $parameters = $grid->getReferences("parameters");
                            // $log->AddRow(" Get PARAMETERS2 : " . $id . " -- " . json_encode($parameters) . ' --- ' . $typeName);
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
                                // $log->AddRow(" Get GRIDCOLUMN " . json_encode($collectionObj1) . " -- " . $item['_id']->__ToString());
                                if ($firstTime) {
                                    $itemNew[$paramValue->name] = $service->getCollectionRef($methodsRef, $collectionObj1);
                                    $firstTime = false;
                                } else {
                                    // $item = array_merge($item,$this->getCollectionRef($methodsRef, $collectionObj1));
                                    // $log->AddRow(" Get GRIDCOLUMN2 " . $paramValue->name. " -- " . json_encode($service->getCollectionRef($methodsRef, $collectionObj1)) );
                                    $result = $service->getCollectionRef($methodsRef, $collectionObj1);
                                    if (is_string($result) == true) {
                                        $itemNew[$paramValue->name] = $itemNew[$paramValue->name] . " " . json_encode($result);
                                    } else {
                                        $itemNew[$paramValue->name] = $itemNew[$paramValue->name] . "";
                                    }
                                }
                                // $log->AddRow(" Get GRIDCOLUMN2 " . $paramValue->name. " -- " . $item[$paramValue->name] );
                            }
                        } else 
                            if ($paramValue->type[0]["text"] == \Application\Document\Parameter::FORMATFIELD) {
                                $strToExec = 'return $this->' . $paramValue->actionResponse;
                                // $log->AddRow(" Get FORMATFIELD " . json_encode($paramValue) . " -- " . $strToExec);
                                $object = $laf->findObject($typeName, $item['_id']->__ToString());
                                // $log->AddRow(" Get FORMATFIELD1 " . json_encode($object) . " -- " . $strToExec);
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

    public function canvasMobileAction()
    {
        $request = $this->getRequest();
        $headers = $request->getHeaders();
        // in your constructor
        $this->_view = new ViewModel();
        $headerAuth = $headers->get('Authorization')->getFieldValue();
        // $auth = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
        $dm = $this->getServiceLocator()->get('doctrine.documentmanager.odm_default');
        // $identity = $auth->getIdentity();
        // \Doctrine\Common\Util\Debug::dump('aa');exit;
        $sessionMgr = new \Authentication\Controller\SessionManager();
        
        if ($sessionMgr->validateToken($dm, $headerAuth) === true) {
            $this->_view->setVariable("token", $headerAuth);
            $page = $this->params()->fromRoute('id', '');
            $session = $sessionMgr->getSessionOnToken($dm, $headerAuth);
            /*
             * $lang = $user->getOrganization()->getLocale();
             * if ($lang == 'en') {
             * $schedulerLanguage = $lang . '-GB';
             * } else {
             * $schedulerLanguage = $lang . '-' . strtoupper($lang);
             * }
             * $this->_view->setVariable("schedulerLanguage", $schedulerLanguage);
             */
            
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
            // if (!isset($_SESSION['schedulerLibJS'])) {
            // $_SESSION['schedulerLibJS'] = "loaded";
            // } else {
            $this->_view->setTerminal(true);
        } // }
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
        // \Doctrine\Common\Util\Debug::dump('aa');exit;
        $headerAuth = $headers->get('Authorization')->getFieldValue();
        // $auth = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
        $dm = $this->getServiceLocator()->get('doctrine.documentmanager.odm_default');
        // $identity = $auth->getIdentity();
        
        $sessionMgr = new \Authentication\Controller\SessionManager();
        
        if ($sessionMgr->validateToken($dm, $headerAuth) === true) {
            $this->_view = new ViewModel();
            $this->_view->setVariable("token", $headerAuth);
            // \Doctrine\Common\Util\Debug::dump($viewId);exit;
            if (strlen($parentType) > 1) {
                $parentType = $this->params()->fromQuery('parentType');
                $parentId = $this->params()->fromQuery('parentId');
                
                $laf = new MongoObjectFactory();
                $object = $laf->findObjectJSON("View", $viewId);
                // in your initialize values method
                // \Doctrine\Common\Util\Debug::dump($object);exit;
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

    public function loginAction()
    {
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            $authService = $this->getServiceLocator()->get('doctrine.authenticationservice.odm_default');
            $adapter = $authService->getAdapter();
            $userName = $this->getRequest()->getPost('UserName');
            $adapter->setIdentityValue($this->getRequest()
                ->getPost('UserName')); // i am using email
            $adapter->setCredentialValue($this->getRequest()
                ->getPost('Password'));
            
            $authResult = $authService->authenticate();
            if ($authResult->isValid()) {
                $auth = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
                $dm = $this->getServiceLocator()->get('doctrine.documentmanager.odm_default');
                $identity = $auth->getIdentity();
                $user = $dm->getRepository("\\Application\\Document\\User")->findOneBy(array(
                    "id" => $identity['id']
                ));
                
                $sessionMgr = new \Authentication\Controller\SessionManager();
                $session = $sessionMgr->saveUserSession($dm, $user);
                $_SESSION["user"] = $userName;
                $log = Log::getInstance();
                $log->AddRow(" USER name " . $userName);
                $userToken = "qwertyuiopasdgjklzxcvbnm123456789";
                $data = array(
                    'email' => $user->getEmail(),
                    'last_name' => $user->getLastName(),
                    'name' => $user->getName(),
                    'role' => $user->getUserRole()->getRole(),
                    'organization' => $user->getOrganization()->getClasspath(),
                    'organizationId' => $user->getOrganization()->getId(),
                    'workspaceId' => (string) $user->getOrganization()
                        ->getActiveWorkspace()
                        ->getId(),
                    // 'workspaceId' => "5743556336dd8117280041ab",
                    'Success' => true,
                    'token' => $session->getId(),
                    'user' => $userName
                );
                return new JsonModel($data);
            } else {
                return new JsonModel(array(
                    'code' => 403,
                    'mgs' => 'wrong username or password'
                ));
            }
        } else {
            return new JsonModel(array(
                'code' => 403,
                'msg' => 'no post'
            ));
        }
    }

    public function getMethodResultListAction()
    {
        $request = $this->getRequest();
        $headers = $request->getHeaders();
        // \Doctrine\Common\Util\Debug::dump('aa');exit;
        $userToken = "qwertyuiopasdgjklzxcvbnm123456789";
        \Application\Controller\Log::getInstance()->AddRow('WE SHOULD NOT BE HERE WITHOUT ORG ID or WKSP ID' . json_encode($headers));
        
        $headerAuth = base64_decode($headers->get('Authorization')->getFieldValue());
        $listArray = array();
        // $auth = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
        $dm = $this->getServiceLocator()->get('doctrine.documentmanager.odm_default');
        // $identity = $auth->getIdentity();
        $sessionMgr = new \Authentication\Controller\SessionManager();
        
        if ($sessionMgr->validateToken($dm, $headerAuth) === true) {
            
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
            $pos = strpos($method, "cgt");
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
                            $criteriaArray[$criteriaTo[$i]] = $criteriaTo[$i + 1];
                        }
                        // $criteriaArray[$criteriaTo[0]] = $criteriaTo[1];
                    }
                    \Application\Controller\Log::getInstance()->AddRow('WE SHOULD NOT BE HERE WITHOUT ORG ID or WKSP ID' . json_encode($criteriaArray));
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
                        } else 
                            if (strcmp($key, "options") == 0 && $item['type'] == 'enum') {
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
                        if (strcmp($key, "recid") == 0) {} else 
                            if (sizeof($param) >= 1) {
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
        } else {
            return new JsonModel(array(
                "status" => "error",
                "code" => 403
            ));
        }
    }

    public function viewAction()
    {
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

    public function listMobileAction()
    {
        return new JsonModel(array(
            'msg' => 'Hello World'
        ));
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

    /**
     * Executes and returns a type
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function executeAction()
    {
        $request = $this->getRequest();
        $headers = $request->getHeaders();
        $log = Log::getInstance();
        // \Doctrine\Common\Util\Debug::dump('aa');exit;
        // $userToken = "qwertyuiopasdgjklzxcvbnm123456789";
        $headerAuth = base64_decode($headers->get('Authorization')->getFieldValue());
        $listArray = array();
        // $auth = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
        $dm = $this->getServiceLocator()->get('doctrine.documentmanager.odm_default');
        // $identity = $auth->getIdentity();
        $sessionMgr = new \Authentication\Controller\SessionManager();
        //$log->AddRow(" Execute Action 0 - ");
        if ($sessionMgr->validateToken($dm, $headerAuth) === true) {
            $mtime = microtime();
            $mtime = explode('.', $mtime);
            $mtime = $mtime[1] + $mtime[0];
            $tId = "" . $mtime;
            $_SESSION["transaction_id"] = $tId;
            $result = false;
            $action = "";
            
            //$log->AddRow(" Execute Action 1 - " . $request);
            $listArray = array();
            $mongoObjectFactory = new MongoObjectFactory();
            if ($request->isPost()) {
                
                $data = $request->getPost()->toArray();
                // $log->AddRow(" POst Execute Action 1 - " . $request);
                $actionToExecute = $data['actionExecution'];
                if (isset($data['id'])) {
                    if ($data['id'] == 0) {
                        unset($data['id']);
                    }
                }
                $_SESSION["transaction_type"] = $actionToExecute;
                if (isset($data)) {
                    $_SESSION["transaction_objectId"] = json_encode($data);
                }
                
                if (isset($data['method'])) {
                    $_SESSION["transaction_name"] = $data['method'];
                }
                // if we have an recid then find the object and execute on it
                if (isset($data['id'])) {
                    $id = $data['id']; // ->fromQuery('id');
                                       // $log->AddRow(" Execute Action 2 - " . json_encode($data));
                                       // $id = $data['data']['recid'];
                    if ($actionToExecute == 'saveObject') {
                        // update object
                        $_SESSION["transaction_name"] = "update";
                        $result = $mongoObjectFactory->update($data['objectType'], $id, $data['data']);
                        $data['data']['id'] = $id;
                        $listArray[] = $data['data'];
                        $log->AddRow(" Execute Action 2 -  " . json_encode($data));
                        // $listArray = array_merge($listArray, $data['data']);
                    } elseif ($actionToExecute == 'service') {
                        $serviceName = $data['data']['serviceName'];
                        $method = $data['data']['serviceMethod'];
                        $serviceClass = new \ReflectionClass($serviceName);
                        $class = $serviceClass->newInstanceArgs();
                        
                        $reflectionMethod = new \ReflectionMethod($class, $method);
                        // $result = $mongoObjectFactory->update($data['objectType'], $id, $data['data']);
                        $idRel = $reflectionMethod->invoke($class, $data['data']);
                        $listArray = $idRel;
                    } elseif ($actionToExecute == 'method') {
                        $typeObj = $data['objectType'];
                        $method = $data['method'];
                        $laf = new MongoObjectFactory();
                        $typeClass = new \ReflectionClass($laf->getClassPath($typeObj) . $typeObj);
                        
                        $object = $laf->findObject($typeObj, $id);
                        // $log->AddRow(" Execute Action>-" . $method . " >-on " . json_encode($object) . " >-with " . json_encode($data));
                        if (strlen($actionToExecute) > 1) {
                            if (isset($data['data'])) {
                                $listArray = $object->executeNew("name", $method, $data['data']);
                            } else {
                                $listArray = $object->executeNew("name", $method, array());
                            }
                        }
                    } elseif ($actionToExecute == 'filter') {
                        $typeObj = $data['objectType'];
                        $method = $data['method'];
                        $laf = new MongoObjectFactory();
                        $typeClass = new \ReflectionClass($laf->getClassPath($typeObj) . $typeObj);
                        
                        // $object = $laf->findObject($typeObj, $id);
                        // $log->AddRow(" Execute Action>-" . $actionToExecute . " >-on " . json_encode($data) . " >-with " . json_encode($data));
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
                        $result = $this->saveUserState($viewId, $identity, $user, $componentId, $dataIn, "filter");
                    }
                } else {
                    // find the container - creating a new ID
                    $container = $mongoObjectFactory->findObject($data['parentType'], $data['parentId']);
                    $reflectionMethod = new \ReflectionMethod($container, 'add');
                    $idRel = $reflectionMethod->invoke($container, $data['objectType'], $data['data']);
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
    }

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
}
