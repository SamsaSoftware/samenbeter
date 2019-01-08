<?php
namespace Application\Service;

use Application\Controller\Log;
use Application\Controller\MongoObjectFactory;
use Application\Controller\ServiceLocatorFactory;

class Service
{

    private $mongoFactory;

    protected $serviceLocator;

    public function __construct()
    {
        $this->mongoFactory = new MongoObjectFactory();
        $this->serviceLocator = ServiceLocatorFactory::getInstance();
    }

    protected function getSession()
    {
        $dm = $this->serviceLocator->get('doctrine.documentmanager.odm_default');
        $session = $this->serviceLocator->get('Zend\Authentication\AuthenticationService');
        return $session;
    }

    public static function getStaticOrganization($orgClasspath)
    {
        $serviceLocator = ServiceLocatorFactory::getInstance();
        $dm = $serviceLocator->get('doctrine.documentmanager.odm_default');
        $organizations = $dm->getRepository("\\Application\\Document\\Organization")->findAll();
        foreach ($organizations as $organization) {
            if ($organization->getClasspath() == $orgClasspath) {
                return $organization;
            }
        }
    }

    public function getAllOrganizations()
    {
        $organizations = [];
        try {
            $dm = $this->serviceLocator->get('doctrine.documentmanager.odm_default');
            $organizations = $dm->getRepository("\\Application\\Document\\Organization")->findAll();
        } catch (\Exception $e) {
            // LOG ERRROR
        }
        return $organizations;
    }

    public function prepareData($ids, $typeObj, $param, $method, $viewId, $gridId, $userId, $criteria, $translator, $index = 0, $offset = 0, $search = '', $sort = '', $column = '')
    {
        $log = Log::getInstance();
        
        $log->AddRow(" Start XXXXXX==" . $typeObj . " -- " . $method . " == " . json_encode($ids) . "==" . $column);
        // $methodlist = $this->params()->fromQuery('methodName');
        $methods = explode(".", $method);
        $laf = new MongoObjectFactory();
        $collectionCount = 0;
        // TODO! ONLY GET THE ATTRIBUTES THAT ARE REQUIRED!!
        $attributesShown = array();
        // check if user has settings
        $setting = $this->getUserSettings($userId, $viewId, $gridId, "gridstate");
        $log->AddRow(" Get method result Action XXXXXX---" . $method . "==" . $userId . " == " . $gridId . " -- " . json_encode($setting));
        
        if ($setting) {
            // get columns from state
            $attributesShown = $this->getShowedColumns(json_decode($setting['state']));
            
            // $log->AddRow(" Got shown attribs XXXXXX==" .json_decode($attributesShown). " -- ");
        }
        // $log->AddRow(" Get method result Action XXXXXX---" . $method . "==" . $userId . " == " . $gridId . " -- " . json_encode($attributesShown));
        
        $userFilter = $this->getUserSettings($userId, $viewId, $gridId, "filter");
        
        if ($userFilter) {
            
            // $log->AddRow(" Get USERFILTER ==" . $gridId . " == " . json_encode($userFilter) . " -- ");
        }
        
        $parameters = array();
        // get all extra columns
        $view = $this->mongoFactory->findObject("View", $viewId);
        if (! is_null($view)) {
            $criteria = [];
            $criteria[] = "name";
            $criteria[] = $gridId;
            $grid = $view->getInstancesCriteria("Component", $criteria);
            // $log->AddRow(" Get PARAMETERS2 : " . $id . " -- " . json_encode($grid) . ' --- ' . $gridId);
            // $grid = $view->getReferenceOnPK("components", $gridId);
            if (! is_null($grid) && (sizeof($grid) > 0)) {
                $parameters = $grid[0]->getReferences("parameters");
                // $log->AddRow(" Get PARAMETERS3 : " . $id . " -- " . json_encode($parameters) . ' --- ' . $typeName);
            }
        }
        foreach ($parameters as $paramKey => $paramValue) {
            if ($paramValue->type[0]["text"] == \Application\Document\Parameter::FILTER) {
                
                $filter = $paramValue->definition; // expected format ' name-value '
                                                   // $log->AddRow(" Get FILTER sXXXXXX " . json_encode($method) . "==" . $gridId . " == " . json_encode($parameters) . " -- " . json_encode($filter));
                $methodFilter = $paramValue->referencelink;
                $newMethods = array();
                foreach ($methods as $methodIn) {
                    if ($methodIn == $methodFilter || $methodIn = 'get'.$methodFilter) {
                        $methodIn = $methodIn . '[' . $filter . ']';
                    }
                    $newMethods[] = $methodIn;
                }
                $log->AddRow(" Get FILTER1 sXXXXXX " . json_encode($newMethods) . "==" . $gridId . " == " . json_encode($parameters) . " -- " . json_encode($filter));
                
                $methods = array();
                $methods = $newMethods;
            }
        }
        // $log->AddRow(" Get method result Action XXXXXX ".$method."==".$gridId. " == " . json_encode($parameters) . " -- ".json_encode($grid));
        // $object = $this->mongoFactory->findObject($typeObj, $id);
        $newCollectionAll = array();
        $listOfIds = explode(",", $ids);
        $conflicts = 0;
        $conflictColumns = array();
        $summaryDataColumns = array();
        $summaryData = array();
        $attributesNotShown = array();
        $typeName = null;
        if ($column == true) {
            // $log->AddRow(" Get CGT COLUMNSCOL action : ---- " . json_encode($attributesShown));
            if (sizeof($attributesShown) == 0) {
                // get columns from object
                if (is_null($typeName)) {
                    $typeName = $this->getNameFromMethods($methods);
                }
                $classRef = $typeName;
                if (isset($classRef) && $classRef != false) {
                    $classObject = $this->mongoFactory->findObject($classRef, null);
                    
                    $reflectionMethod = new \ReflectionMethod($classObject, 'getMainAttributes');
                    $attributesNotShown = $reflectionMethod->invoke($classObject, $typeName);
                    // $log->AddRow(" Get CGT COLUMNSCOL action : ---- " . json_encode($attributesNotShown));
                }
            }
        }
        foreach ($listOfIds as $id) {
            $collectionObj = array();
            if ($id == 0) {
                // find all
            } else {
                if ($typeObj == "Samsa") {
                    $object = $laf->getSamsa();
                } else {
                    $object = $laf->findObject($typeObj, $id);
                }
                if (isset($object))
                    $collectionObj[] = $object;
            }
            // $log->AddRow(" Get OBJECT action : ---- " . json_encode($object));
            
            $collectionInstances = array();
            if (isset($object)) {
                $paramValues = array();
                if ($column == '') {
                    $log->AddRow("COLUMNS : ---- ".$method);
                    $collectionInstances = $this->getCollectionRef($methods, $collectionObj, $index, $offset, $search, $sort);
                    $method = $methods[0];
                     $log->AddRow(" Get ColectION00000 " . json_encode($method) . " action : ---- " . json_encode($collectionInstances));
                    
                    $pos = strpos($method, "cg");
                    // $log->AddRow(" Get ColectION00000x " . $pos . " action : ---- " . $index);
                    
                    if ($pos === false) {
                        $pos = 100;
                    }
                    
                    if ($pos != 0 && $index != 0) {
                        // $log->AddRow(" NOCGT Get ColectION000010 " . $method . " action : ---- ");
                        
                        $methodsRef = explode("&", $method);
                        $method = $methodsRef[0];
                        unset($methodsRef[0]);
                        $resultRef = array();
                        $criteria = array();
                        $actionName = substr($method, 0, 3);
                        $typeName = substr($method, 3);
                        $valuesStr = $this->get_string_between($method, "[", "]");
                        
                        if (isset($valuesStr)) {
                            $params = explode(",", $valuesStr);
                            $typeName = $this->get_string_between($method, "get", "[");
                            // $log->AddRow(" PARAMS " . json_encode($params) . ' .... ');
                            if (isset($params[0])) {
                                foreach ($params as $paramv) {
                                    $paramValues = explode("-", $paramv);
                                    if ($paramValues[0] == "_id" && $paramValues[1] != 0) {
                                        $paramValues[0] = "_id";
                                        $paramValues[1] = new \MongoId($paramValues[1]);
                                    } else {
                                        // $criteria[$paramValues[0]] = $paramValues[1];
                                    }
                                }
                            }
                        }
                        // $log->AddRow(" Get ColectION000001 action : ---- " . $collectionCount);
                        // ONLY COUNT!
                        $actualCount = $this->getCollectionRef($methods, $collectionObj, $index, $offset, $search, $sort, true);
                        // print "dd";exit;
                        // $log->AddRow(" Get ColectION0000011 action : ---- " . $actualCount);
                        $collectionCount = $collectionCount + $actualCount;
                        // $log->AddRow(" Get ColectION0000012 action : ---- " . $collectionCount);
                    } else {
                        $actionName = substr($method, 0, 3);
                        $typeName = substr($method, 3);
                        $valuesStr = $this->get_string_between($method, "[", "]");
                        if (isset($valuesStr)) {
                            $typeName = $this->get_string_between($method, $actionName, "[");
                        }
                        
                        $collectionCount = count($collectionInstances);
                        $log->AddRow(" Get CGT ColectION0000011 action : ---- " . $collectionCount);
                    }
                }
                
                $collection = array();
                foreach ($collectionInstances as $collectionInstance) {
                    $typeName = $this->getClassnRef($methods, $collectionObj);
                    $collection[] = $collectionInstance->jsonSerialize();
                }
                
                $newCollection = array();
                
                if (! is_null($typeName)) {
                    foreach ($collectionInstances as $objectRef) {
                        $itemNew = array();
                        
                        $item = $objectRef->jsonSerialize();
                        //$objectRef = $this->mongoFactory->findObject($typeName, (string) $item['_id']);
                        $tooltip = $objectRef->getTooltip('default', []);
                        if (isset($tooltip)) {
                            $itemNew['tooltip'] = $tooltip;
                        }
                        $log->AddRow(" Get CGT ColectION00000113 action : ---- " . json_encode($attributesShown));
                        // $listArray[] = $item;
                        foreach ($item as $key => $columnc) {
                            \Application\Controller\Log::getInstance()->AddRow(' LineXXXvalue ' . json_encode($key));
                            if (sizeof($attributesShown) == 0 || in_array($key, $attributesShown) || $this->substr_startswith($key, 'is_') || $this->substr_startswith($key, 'Is_')) {
                                
                                $arItems = array();
                                $classRef = $typeName;
                               // $objectRef = $this->mongoFactory->findObject($classRef, (string) $item['_id']);
                                
                                if (sizeof($attributesShown) > 0 && is_array($columnc) == true) {
                                    
                                    foreach ($columnc as $itemS => $val) {
                                        $arItem = array();
                                        $refRel = $objectRef->getRelationType($key);
                                        // only if a relation
                                        if (isset($refRel) && is_array($refRel) && count($objectRef->getRelationType($key)) > 0) {
                                            if ($objectRef->getRelationType($key) == \Application\Document\Model::ODM) {
                                                // Application\Controller\Log::getInstance()->AddRow(' Line value ' . json_encode($val));
                                                if (is_array($val)) {
                                                    $arItems[] = $val['$id'];
                                                }
                                            } else {
                                                $i = 0;
                                                foreach ($val as $itemSD => $valD) {
                                                    if ($i == 0) {
                                                        $i = 1;
                                                    } else 
                                                        if ($i == 1) {
                                                            // $arItem[] = (string) $valD;
                                                            $refType = ucfirst(substr($key, 0, strlen($key) - 1));
                                                            // IF ODM then we need to take the Mongo Id
                                                            if ($objectRef->getRelationType($key) == \Application\Document\Model::ODM_OWNING) {
                                                                if ($valD instanceof \MongoId) {
                                                                    $objectRefRef = $this->mongoFactory->findObject($refType, (string) $valD);
                                                                } else {
                                                                    $objectRefRef = $this->mongoFactory->findObject($refType, (string) $valD['$id']);
                                                                }
                                                            } else {
                                                                // otherwise we take our $id direct link
                                                                $objectRefRef = $this->mongoFactory->findObject($refType, (string) $valD);
                                                            }
                                                            // \Application\Controller\Log::getInstance()->AddRow(' prepareData ' . json_encode($objectRefRef));
                                                            $arItem[] = $objectRefRef->{$objectRefRef->getPK()};
                                                            $i = 0;
                                                        }
                                                }
                                                $arItems[] = $arItem;
                                            }
                                        } else 
                                            
                                            if (is_array($val) == true) {
                                                foreach ($val as $itemSD => $valD) {
                                                    $arItem[] = (string) $valD;
                                                }
                                                
                                                $arItems[] = $arItem;
                                            } else
                                                $arItems[] = (string) $val;
                                    }
                                    // $arItems[] = $arItem;
                                    $itemNew[$key] = $arItems;
                                } else {
                                    if (is_array($columnc)) {
                                        $attributesNotShown[] = $key;
                                    } else {
                                        $itemNew[$key] = $item[$key];
                                    }
                                }
                                if (strcmp($key, "_id") == 0) {} elseif (isset($param) && (sizeof($param) >= 1)) {
                                    if (strcmp($key, $param) == 0) {} else {
                                        unset($itemNew[$key]);
                                    }
                                }
                            }
                            // check conflicts!
                            if ($this->substr_startswith($key, 'is_') || $this->substr_startswith($key, 'Is_')) {
                                // TRUE OR FALSE ONLY - Otherwise more then 5 chars expected.. is a JSON
                                $log->AddRow(" Get CONFLICT1 " . $item[$key] . " -- " . strlen($item[$key]));
                                if (isset($item[$key]) && strlen($item[$key]) < 10) {
                                    if ($item[$key] === "true") {
                                        // $conflicts++;
                                        $conflictColumns[$key] = 'false';
                                        // w2ui: { style: "color: red" }
                                        $itemNew1["style"] = "color: red";
                                        $itemNew["w2ui"] = $itemNew1;
                                        $tooltip = $objectRef->getTooltip($key, "");
                                        if (isset($tooltip)) {
                                            $itemNew["conflict_tooltip"][] = $tooltip;
                                        } else {
                                            $itemNew["conflict_tooltip"][] = $translator->translate(ucfirst('tooltip_' . $key));
                                        }
                                    }
                                } else {
                                    $decodeStringVal = array();
                                    $decodeStringVal = json_decode($item[$key], true);
                                    $log->AddRow(" Get CONFLICT " . json_encode($decodeStringVal) . " -- ");
                                    
                                    if ($decodeStringVal["response"] == "true") {
                                        // $conflicts++;
                                        $itemNew[$key] = "true";
                                        $conflictColumns[$key] = 'false';
                                        $itemNew1["style"] = "color: red";
                                        $itemNew["w2ui"] = $itemNew1;
                                        $itemNew["conflict_tooltip"][] = $translator->translate(ucfirst('tooltiplarge_' . $key));
                                        $tooltip = $objectRef->getTooltip($key, $decodeStringVal["data"]);
                                        $existingTooltip = '';
                                        if (isset($itemNew['tooltip'])) {
                                            $existingTooltip = $itemNew['tooltip'];
                                        }
                                        $itemNew['tooltip'] = $existingTooltip . '<br>' . $tooltip;
                                    } else {
                                        $itemNew[$key] = "false";
                                    }
                                }
                            }
                        }
                        
                        foreach ($parameters as $paramKey => $paramValue) {
                            
                            if ($paramValue->type[0]["text"] == \Application\Document\Parameter::GRIDCOLUMN) {
                                $paths = explode("+", $paramValue->referencelink);
                                $itemNew[$paramValue->name] = "";
                                foreach ($paths as $path) {
                                    $methodsRef = explode(".", $path);
                                    $objectX = $laf->findObject($typeName, $item['_id']->__ToString());
                                    $collectionObj1 = array();
                                    $collectionObj1[] = $objectX;
                                    $log->AddRow(" Get FORMATFIELD " . json_encode($this->getCollectionRef($methodsRef, $collectionObj1)) . " -- " . json_encode($methodsRef));
                                    // $item = array_merge($item,$this->getCollectionRef($methodsRef, $collectionObj1));
                                    $result = $this->getCollectionRef($methodsRef, $collectionObj1);
                                    if (is_array($result)) {
                                        $itemNew[$paramValue->name] = $itemNew[$paramValue->name];
                                    } else 
                                        if (isset($result)) {
                                            $itemNew[$paramValue->name] = $itemNew[$paramValue->name] . " " . $result;
                                        }
                                }
                            } else 
                                if ($paramValue->type[0]["text"] == \Application\Document\Parameter::GRIDROWRULE) {
                                    $strToExec = 'return $this->' . $paramValue->actionExecution;
                                    // $log->AddRow(" Get GRIDROWRULE " . json_encode($objectRef) . " -- " . $strToExec);
                                    if ($objectRef->evaluate($strToExec, $item) == true) {
                                        // example: $item["style"] = 'background-color: red; color: white;';
                                        $itemNew[$paramValue->referencelink] = $paramValue->actionResponse;
                                    }
                                } else 
                                    if ($paramValue->type[0]["text"] == \Application\Document\Parameter::FORMATFIELD) {
                                        $strToExec = 'return $this->' . $paramValue->actionExecution;
                                        $log->AddRow(" Get FORMATFIELD " . json_encode($paramValue) . " -- " . $strToExec);
                                        $itemNew[$paramValue->name] = $objectRef->evaluate($strToExec);
                                    }
                        }
                        
                        $itemNew['recid'] = $item['_id']->__ToString();
                        $newCollection[] = $itemNew;
                    }
                    foreach ($parameters as $paramKey => $paramValue) {
                        if ($paramValue->type[0]["text"] == \Application\Document\Parameter::GRIDTOTAL) {
                            if (isset($paramValue->actionExecution)) {
                                $strKeyTotal = $paramValue->actionExecution;
                            } else {
                                $strKeyTotal = 1;
                            }
                            $ret = $object->executeCmdByCriteria($typeName, $paramValues, '$sum', $strKeyTotal, $search);
                            
                            if (isset($summaryData[$strKeyTotal])) {
                                // $summaryData['text'] = $sdata['text'] + $ret;
                                // $summaryData[$strKeyTotal] +
                                $summaryData[$strKeyTotal] = $summaryData[$strKeyTotal] + $ret;
                                // $summaryDataColumns[] = $summaryData;
                                $found = true;
                            } else {
                                $summaryData[$strKeyTotal] = 0 + $ret;
                                
                                // $summaryDataColumns[] = $summaryData;
                            }
                        }
                    }
                    // manage conflicts
                    if (count($conflictColumns) < 0) {
                        $log->AddRow(" Get conflictColumns !! " . json_encode($conflictColumns) . " -- " . $conflicts);
                        $total = $object->countQuickInstancesCriteria($typeName, []);
                        foreach ($conflictColumns as $key => $value) {
                            $conflictColumnsArr = array();
                            $conflictColumnsArr[] = $key;
                            $conflictColumnsArr[] = $value;
                            if (count($conflictColumnsArr >= 1)) {
                                $log->AddRow(" Get conflictColumns1 " . $total . " -- " . $object->countQuickInstancesCriteria($typeName, $conflictColumnsArr));
                                $conflicts = $conflicts + ($total - $object->countQuickInstancesCriteria($typeName, $conflictColumnsArr));
                                $log->AddRow(" Get conflictColumns2 " . json_encode($conflictColumnsArr) . " -- " . $conflicts);
                            }
                        }
                        // $log->AddRow(" Done interation ColectION 1action : -- " . $total);
                    }
                    // $log->AddRow(" Done interation ColectION action : -- ");
                }
                $column = '';
                $newCollectionAll = array_merge($newCollectionAll, $newCollection);
            }
        }
        $log->AddRow(" Done merging ColectIONx action : -- " . $typeName);
        
        if (is_null($typeName)) {
            $typeName = $this->getNameFromMethods($methods);
        }
        $attributes = array();
        
        // get columns from object
        $classRef = $typeName;
        if (sizeof($attributesShown) == 0) {
            if (isset($classRef) && $classRef != false) {
                $classObject = $this->mongoFactory->findObject($classRef, null);
                
                $reflectionMethod = new \ReflectionMethod($classObject, 'getAttributes');
                $attributes = $reflectionMethod->invoke($classObject, $typeName);
                foreach ($parameters as $paramKey => $paramValue) {
                    if ($paramValue->type[0]["text"] == \Application\Document\Parameter::GRIDCOLUMN) {
                        $attributes[$paramValue->name] = $paramValue->name;
                    }
                }
            }
        } else {
            
            $stateIt = json_decode($setting['state'], true);
            $attributes = $stateIt['columns'];
        }
        $searchColumns = array();
        $arrayColumns = array();
        // $log->AddRow(" Get View Type : " . json_encode($view));
        $typeClass = new \ReflectionClass($laf->getClassPath($classRef) . $classRef);
        $nameRel = $typeClass->getShortName();
        $criteria = "object-" . $nameRel;
        $criteriaTo = explode("-", $criteria);
        // $log->AddRow(" Found Field2 : " . json_encode($criteriaTo));
        $fields = $view->getJSONCriteria("Field", $criteriaTo);
        // $log->AddRow(" Found Field3 : " . json_encode($fields));
        $log->AddRow(" Done actionx : -- ");
        foreach ($fields as $field) {
            if (isset($field) && ($field['searchable'] || $field['searchable'] == "true")) {
                $search = array();
                $search['field'] = $field['name'];
                $search['caption'] = $translator->translate(ucfirst($field['name']));
                if ($field['type'] == "enum" || $field['type'] == 'list') {
                    $search['type'] = "text";
                } else {
                    $search['type'] = $field['type'];
                }
                $searchColumns[] = $search;
            }
        }
        if (count($summaryData) > 0) {
            foreach ($summaryData as $summaryDataKey => $summaryDataValue) {
                $summaryDataX['text'] = "-" . $translator->translate($summaryDataKey) . " : " . $summaryDataValue;
                $summaryDataColumns[] = $summaryDataX;
            }
        }
        if ($conflicts > 0) {
            $summaryDataX = array();
            $summaryDataX['text'] = "-" . $translator->translate("Conflicts") . " : " . $conflicts;
            $summaryDataColumns[] = $summaryDataX;
        }
        $summaryColumns = array();
        $i = 0;
        // $log->AddRow(" Done merging ColectIONY action0 : -- " . json_encode($attributes));
        // $log->AddRow(" Done merging ColectIONY action1 : -- " . json_encode($attributesShown));
        // $log->AddRow(" Done merging ColectIONY action 2: -- " . json_encode($attributesNotShown));
        if (sizeof($attributesShown) == 0) {
            
            foreach ($attributes as $key => $column) {
                
                $col = array();
                // $search = array();
                if (sizeof($attributesShown) > 0 && ! in_array($key, $attributesShown)) {
                    $col['hidden'] = true;
                } else {
                    // $log->AddRow(" Done merging ColectIONY action : -- " . $key . "-- " . json_encode($attributesShown));
                    if (sizeof($attributesShown) == 0) {
                        
                        if (in_array($key, $attributesNotShown)) {
                            $col['hidden'] = true;
                        }
                    }
                    if (count($summaryDataColumns) > 0 && $i == 0) {
                        foreach ($summaryDataColumns as $summaryDataColumn) {
                            $summaryColumn = array();
                            $summaryColumn[$key] = $summaryDataColumn['text'];
                            $summaryColumns[] = $summaryColumn;
                        }
                        $i ++;
                    }
                }
                // $search['field'] = $key;
                // $search['caption'] = $translator->translate(ucfirst($key));
                // $search['type'] = 'text';
                
                $col['field'] = $key;
                // $col['caption'] = '<div style="font-size:10px;color:black">' . $translator->translate(ucfirst($key)) . '</div>';
                
                $col['caption'] = $translator->translate(ucfirst($key));
                $col['size'] = '100px';
                $col['sortable'] = true;
                $col['resizable'] = true;
                // $col["title"] = '<div style="font-size:10px;color:black" title="aa">Recid</span>';
                
                if ($this->substr_startswith($key, 'is_') || $this->substr_startswith($key, 'Is_')) {
                    $col['render'] = 'conditionformatter';
                }
                $type = array();
                
                $arrayColumns[] = $col;
            }
        } else {
            if (isset($attributes[0]) && isset($attributes[0]['field'])) {} else {
                unset($attributes[0]);
            }
            foreach ($attributes as $attribute) {
                // $col = array();
                $attribute['caption'] = $translator->translate($attribute['field']);
                // $attribute['caption'] = '<div style="font-size:10px;color:black">' . $translator->translate($attribute['field']) . '</div>';
                if ($attribute['hidden'] == "true") {
                    $attribute['hidden'] = true;
                } else {
                    $attribute['hidden'] = false;
                }
                if ($attribute['frozen'] == "true") {
                    $attribute['frozen'] = true;
                } else {
                    $attribute['frozen'] = false;
                }
                $attribute['sortable'] = true;
                $attribute['resizable'] = true;
                $arrayColumns[] = $attribute;
                if (count($summaryDataColumns) > 0 && $i == 0) {
                    foreach ($summaryDataColumns as $summaryDataColumn) {
                        $summaryColumn = array();
                        $summaryColumn[$key] = $summaryDataColumn['text'];
                        $summaryColumns[] = $summaryColumn;
                    }
                    $i ++;
                }
            }
        }
        return array(
            'columns' => $arrayColumns,
            'searches' => $searchColumns,
            "status" => "success",
            "total" => $collectionCount,
            'records' => $newCollectionAll,
            'summary' => $summaryColumns
        );
    }

    public function undo($data)
    {
        $log = Log::getInstance();
        $log->AddRow(" UNDOING : " . json_encode($data));
        $this->mongoFactory->undoState();
        return true;
    }

    /**
     *
     * @param string $userId            
     * @param string $viewId            
     * @param string $gridId            
     * @return array
     */
    public function getUserSettings($userId, $viewId, $gridId, $type)
    {
        $criteria = array(
            'viewId' => $viewId,
            'gridId' => $gridId,
            'userId' => $userId,
            "type" => $type
        );
        
        return $this->mongoFactory->findObjectByCriteria('Setting', $criteria);
    }

    /**
     *
     * @param array $states            
     * @return array
     */
    public function getShowedColumns($states)
    {
        $columns = array();
        if (isset($states->columns)) {
            foreach ($states->columns as $column) {
                if ($column->hidden == 'false' && isset($column->field)) {
                    $columns[$column->field] = $column->field;
                }
            }
        }
        return $columns;
    }

    /**
     * Input:
     *
     * gets a multi collection result based on multiple paths from a single entry $collectionObj has the origin object:
     * getObject
     * getObject[criteriaName-criteriaValue]
     * getObject_parameter
     * getObject-name-eval(PHP_CODE)
     *
     * @param unknown $multiMethods
     *            getObject+getParent+getObject_getObject[name-value]_name+...
     * @param unknown $collectionObj
     *            origin object in array
     * @return array ["objects":"values", "parent":"values","objects_name":value,...]
     */
    public function getMultiCollectionRef($multiMethods, $collectionObj)
    {
        $methodPaths = explode("+", $multiMethods);
        $collectionObjCopy = $collectionObj;
        $collectionInstances = array();
        foreach ($methodPaths as $methodPath) {
            $methods = explode(".", $methodPath);
            $retValue = array();
            $retValue1 = array();
            $retValue = $this->getCollectionRef($methods, $collectionObjCopy);
            // print_r($retValue);
            if (is_array($retValue)) {
                $retValue1 = $retValue;
                // print_r($this->getCollectionName($methods));
                // print_r($collectionInstances);
                if (isset($collectionInstances[$this->getCollectionName($methods)])) {
                    $newSet = array();
                    $index = 0;
                    $retValue1 = array();
                    foreach ($collectionInstances[$this->getCollectionName($methods)] as $item) {
                        foreach ($retValue as $value) {
                            if (is_array($item)) {
                                if ($value['id'] == $item['id']) {
                                    unset($value['id']);
                                    $newSet[] = array_merge($value, $item);
                                }
                            } else {
                                if ($item->_id['$id'] == $value['id']) {
                                    foreach ($value as $keyIn => $valueIn) {
                                        if ($keyIn != "id") {
                                            $item->{$keyIn} = $valueIn;
                                            $newSet[] = $item;
                                        }
                                    }
                                }
                            }
                        }
                    }
                    $retValue1 = $newSet;
                    // $retValue = array_merge($collectionInstances[$this->getCollectionName($methods)], $retValue);
                }
                $collectionInstances[$this->getCollectionName($methods)] = $retValue1;
            } else {
                $collectionInstances[$this->getCollectionName($methods)] = $retValue;
            }
        }
        return $collectionInstances;
    }

    /**
     *
     * @param unknown $methods            
     * @return string
     */
    public function getCollectionName($methods)
    {
        $count = count($methods);
        $method = $methods[$count - 1];
        // default is items
        $collectionName = "items";
        // if the last method is a get - then read the name and create the link e.g. getOrder -return orders
        if ($this->substr_startswith($method, 'getParent')) {
            $collectionName = "parent";
            if ($count == 1) {
                // $collectionName = "parent";
            } else {
                for ($i = $count - 1; $i == 1; $i --) {
                    if ($this->substr_startswith($methods[$i], 'getParent')) {
                        $collectionName = $collectionName . ".parent";
                    } else {
                        if ($this->substr_startswith($methods[$i], 'get') || $this->substr_startswith($methods[$i], 'cg')) {
                            $actionName = substr($methods[$i], 3);
                            $collectionName = $collectionName . $actionName . 's';
                        }
                    }
                }
            }
        } else 
            if ($this->substr_startswith($method, 'get') || $this->substr_startswith($method, 'cg')) {
                // $actionName = substr($method, 3);
                $listNames = explode("&", $method);
                if (isset($listNames[1])) {
                    $valuesStr = $this->get_string_between($method, "[", "]");
                    if (isset($valuesStr)) {
                        $nameN = $this->get_string_between($listNames[0], "get", "[");
                    } else {
                        $nameN = substr($listNames[0], 3);
                    }
                    $name = $nameN . 's';
                    unset($listNames[0]);
                    
                    $collectionName = $name . $this->recreatePath($listNames, "&");
                } else {
                    $actionName = substr($method, 3);
                    $collectionName = \strtolower($actionName) . 's';
                }
            } else 
                if ($this->substr_startswith($method, 'eval')) {
                    $data = explode("^", $method);
                    if (isset($methods[$count - 2])) {
                        $actionName = substr($methods[$count - 2], 3);
                        $collectionName = \strtolower($actionName) . 's';
                    } else {
                        $collectionName = "main";
                    }
                } else {
                    $actionName = substr($methods[$count - 2], 3);
                    $collectionName = \strtolower($actionName) . 's';
                }
        \Application\Controller\Log::getInstance()->AddRow(' Service - getCollectionName - ' . $collectionName);
        return $collectionName;
    }

    /**
     *
     * @param unknown $methods            
     * @return string
     */
    public function getFieldName($methods)
    {
        $count = count($methods);
        $method = $methods[$count - 1];
        // default is items
        $collectionName = "";
        if ($this->substr_startswith($method, 'eval')) {
            $data = explode("^", $method);
            $collectionName = $data[1];
        } else {
            $collectionName = $methods[$count - 1];
        }
        return $collectionName;
    }

    /**
     *
     * @param unknown $methods            
     * @param unknown $collection            
     * @return multitype:NULL |unknown
     */
    public function getCollectionRef($methods, $collection, $index = 0, $offset = 0, $search = '', $sort = '', $count = false)
    {
        $laf = new MongoObjectFactory();
        $log = Log::getInstance();
        $collectionRet = array();
        // $log->AddRow(" Get Col ref action : -- " . json_encode($methods) . ' --- '.json_encode($count));
        $lenghtOfMethods = count($methods);
        $indexTo = 0;
        
        $sortMust = false;
        if (count($methods) > 1) {
            $sortMust = true;
        }
        $getIds = [];
        foreach ($methods as $method) {
            $log->AddRow(" GETMETOHD : -- " . json_encode($method) . ' --- ');
            if ($this->substr_startswith($method, 'get') || $this->substr_startswith($method, 'cg')) {
                $pos = strpos($method, "cg");
                $methodsRef = explode("&", $method);
                
                $method = $methodsRef[0];
                unset($methodsRef[0]);
            }
            $indexTo = $indexTo + 1;
            $collectionNew = array();
            $collectionCount = 0;
            foreach ($collection as $object) {
                // if ($method . stringEndsWith("()")) {
                // execute on object
                if ($method == "getParent" || $method == "getNext" || $method == "getPrev" || $this->substr_startswith($method, 'getFirst') || $this->substr_startswith($method, 'getLast')) {
                    $arrayNew = array();
                    $valuesStr = $this->get_string_between($method, "[", "]");
                    if (isset($valuesStr)) {
                        $typeName = explode("[", $method);
                        $reflectionMethod = new \ReflectionMethod($object, $typeName[0]);
                    } else {
                        $reflectionMethod = new \ReflectionMethod($object, $method);
                    }
                    
                    $obj = $reflectionMethod->invoke($object, $valuesStr);
                    $arrayNew[] = $obj;
                    // $arrayNew[] = $object->getParent();
                    // $log->AddRow(" 1Get Parent ref parent action : -- " . json_encode($obj) . ' --- ');
                    
                    $collectionNew = array_merge($collectionNew, $arrayNew);
                } else 
                    // parse for getName[value=v]&reference
                    if ($this->substr_startswith($method, 'get') || $this->substr_startswith($method, 'cg')) {
                        /*
                         * $pos = strpos($method, "cgt");
                         * $methodsRef = explode("&", $method);
                         * $method = $methodsRef[0];
                         * unset($methodsRef[0]);
                         */
                        $resultRef = array();
                        $criteria = array();
                        $params = null;
                        $actionName = substr($method, 0, 3);
                        $typeName = substr($method, 3);
                        $log->AddRow(" 1Get TYPE NAME : -- " . $typeName . ' --- ');
                        $valuesStr = $this->get_string_between($method, "[", "]");
                        $paramValues = array();
                        $sortValuesArray = array();
                        if (isset($valuesStr)) {
                            $params = explode(",", $valuesStr);
                            if ($this->substr_startswith($method, 'cg')) {
                                $criteria = array();
                                $actionName = substr($method, 0, 3);
                                $typeName = substr($method, 3);
                                $valuesStr = $this->get_string_between($method, "[", "]");
                                if (isset($valuesStr)) {
                                    $classRet = $this->get_string_between($method, $actionName, "[");
                                } else {
                                    $classRet = $typeName;
                                }
                                $typeName = $classRet; // $this->get_string_between($method, "cg", "[");
                                $method = $actionName . $typeName;
                            } else {
                                $typeName = $this->get_string_between($method, "get", "[");
                            }
                            if (isset($params[0])) {
                                foreach ($params as $param) {
                                    $paramsType = explode("-", $param);
                                    if ($this->substr_startswith($paramsType[0], "orderBy")) {
                                        $sortA = array();
                                        $sortA['field'] = substr($paramsType[0], 7);
                                        $sortA['direction'] = $paramsType[1];
                                        $sortValuesArray[] = $sortA;
                                    } else 
                                        if ($this->substr_startswith($paramsType[1], "@")) {
                                            
                                            $keyValue = substr($paramsType[1], 1);
                                            $index = count($paramValues);
                                            if (isset($_SESSION[$keyValue])) {
                                                \Application\Controller\Log::getInstance()->AddRow(" EXECUTEFINd1 -< " . $_SESSION[$keyValue] . " >-on " . $keyValue . ' --> ');
                                                $newVal = $_SESSION[$keyValue];
                                                $paramValues[$index] = $paramsType[0];
                                                $paramValues[$index + 1] = $newVal;
                                            } else {
                                                $paramValues[$index] = $paramsType[0];
                                                $paramValues[$index + 1] = $keyValue;
                                            }
                                        } else {
                                            $paramValues = explode("-", $param);
                                            $indexCount = count($paramValues);
                                             \Application\Controller\Log::getInstance()->AddRow(" EXECUTEFINd1 1 -< " . $index . " >-on " .json_encode( $paramValues) . ' --> ');
                                            for ($i = 0; $i < $indexCount; $i ++) {
                                                if ($paramValues[$i] == "_id" && $paramValues[$i + 1] != 0) {
                                                    $paramValues[$i] = "_id";
                                                    $paramValues[$i + 1] = new \MongoId($paramValues[$i + 1]);
                                                } else {
                                                    $inArray = strpos($paramValues[$i], ":");
                                                    
                                                    if ($inArray === false) {} else {
                                                        $order = array(
                                                            ":"
                                                        );
                                                        $replace = '.';
                                                        $paramValues[$i] = str_replace($order, $replace, $paramValues[$i]);
                                                    }
                                                }
                                            }
                                        }
                                }
                            }
                        }
                        
                        if (isset($sort) && $sort != '') {
                            $sort = array_merge($sort, $sortValuesArray);
                        } else {
                            $sort = array_merge([], $sortValuesArray);
                        }
                        $log->AddRow(" EXECUTEFINd1 3 : -- " . json_encode($lenghtOfMethods) . ' --- ' . json_encode($indexTo) . ' --- ' . $pos . ' -' . $index . ' == ' . json_encode($paramValues));
                        
                        if ($pos > 2 || $pos === false) {
                            // check for last in line of methods
                            if ($indexTo == $lenghtOfMethods) {
                                // search is only for the last in line of methods!!
                                 $log->AddRow(" RESULTcount : -- " . ' -' . $count);
                                
                                if ($count == true) {
                                    \Application\Controller\Log::getInstance()->AddRow(' -->COUNT1 ' . json_encode($paramValues) . ' -- -' . $typeName . '---' . $collectionCount);
                                    $collectionCount = $collectionCount + $object->countQuickInstancesCriteria($typeName, $paramValues, $search);
                                    \Application\Controller\Log::getInstance()->AddRow(' -->COUNT2 ' . json_encode($paramValues) . ' -- -' . $typeName . '---' . $collectionCount);
                                } else {
                                    $log->AddRow(" RESULTget1 : -- " . json_encode($typeName) . ' --- ' . json_encode($object) . ' --- ' . $offset . ' -' . $index . ' == ' . json_encode($paramValues));
                                    // $resultRef = $object->getQuickInstancesCriteria($typeName, $paramValues, $methodsRef, $index, $offset, $search, $sort);
                                    $getIds = array_merge($getIds, $object->getTypeIds($typeName, $paramValues));
                                    
                                    $log->AddRow(" RESULTfound1 : -- " . json_encode($getIds) . ' --- ' . json_encode($search) . ' --- ' . $offset . ' -' . $index);
                                }
                            } else {
                                // get all - no search
                                $getIds = array_merge($getIds, $object->getTypeIds($typeName, $paramValues));
                                // $resultRef = $object->getQuickInstancesCriteria($typeName, $paramValues, $methodsRef, 0, 0, "", $sort);
                            }
                            
                            // $log->AddRow(" RESULTX : -- " . json_encode($resultRef) . ' --- ' . json_encode($methodsRef) . ' --- ');
                        } else {
                            // $actionName = $method;
                            $collection = array();
                            // $log->AddRow(" EXECUTING1 CGETX : -- " . json_encode($method) . ' --- ' . json_encode($object) . ' --- '. json_encode($paramValues) );
                            
                            $objTypeName = $object->get_class_name();
                            $reflectionMethod = new \ReflectionMethod($object, $method);
                            // $log->AddRow(" EXECUTING CGETX : -- " . json_encode($method) . ' --- ' . json_encode($object) . ' --- '. json_encode($params) );
                            
                            $resultRef = $reflectionMethod->invoke($object, $params);
                            
                            foreach ($resultRef as $resInst) {
                                // $methodsRef = array_values($methodsRef);
                                // RELI CODE REVIEW
                                if (is_object($resInst)) {
                                    $resInst->setReferences(array_values($methodsRef));
                                }
                            }
                            $collectionNew = array_merge($collectionNew, $resultRef);
                        }
                        
                        // }
                    } else 
                        if ($this->substr_startswith($method, 'eval')) {
                            $col = array();
                            $data = array();
                            $data = explode("^", $method);
                            // data[1] name link for eval
                            // data[2] eval string
                            $col[] = $object->execute($data[1], $data[2], array());
                            $collectionNew = array_merge($collectionNew, $col);
                            // $col[$data[1]] = $object->execute($data[2], array());
                            // return $object->execute($data[2], array());
                        } else {
                            $log->AddRow(" Get FORMATFIELD2 " . json_encode($object) . " -- " . json_encode($method));
                            if (method_exists($object, $method)) {
                                // $log->AddRow(" Get METHOD " . " -- " . json_encode($method));
                                $objTypeName = $object->get_class_name();
                                $reflectionMethod = new \ReflectionMethod($object, $method);
                                $resultRef = $reflectionMethod->invoke($object, null);
                                return $resultRef;
                            } else 
                                if (isset($object->$method)) {
                                    $col = array();
                                    if ($method == "_id") {
                                        $arr = $object->$method;
                                        return $arr['$id'];
                                    }
                                    return $object->$method;
                                    // $collectionNew = array_merge($collectionNew, $col);
                                }
                            return "";
                        }
            }
            if (count($getIds) > 0) {
                
                $log->AddRow(" RESULTfoundxx1 : -- " . json_encode($indexTo) . ' --- ' . json_encode($getIds) . ' --- ' . $lenghtOfMethods . ' -' . $typeName);
                
                if ($indexTo == $lenghtOfMethods) {
                    $collectionNew = $laf->getQuickInstancesCriteriaIds($typeName, $getIds, $paramValues, $methodsRef, $index, $offset, $search, $sort);
                    $log->AddRow(" RESULTfoundxx1s : -- " . json_encode($collectionNew) . ' --- ' . json_encode($getIds) . ' --- ' . $lenghtOfMethods . ' -' . $typeName);
                } else {
                    $collectionNew = $laf->getQuickInstancesCriteriaIds($typeName, $getIds, $paramValues, $methodsRef, 0, 0, "", $sort);
                    ;
                }
                $getIds = [];
            }
            $collection = $collectionNew;
        }
        
        if ($count == true) {
            \Application\Controller\Log::getInstance()->AddRow(' -->COUNT3 ' . $collectionCount);
            return $collectionCount;
        } else {
            if ($sort) {}
            return $collection;
        }
    }

    function array_sort($array, $on, $order = 1)
    {
        $new_array = array();
        $sortable_array = array();
        
        if (count($array) > 0) {
            foreach ($array as $k => $v) {
                if (is_array($v)) {
                    foreach ($v as $k2 => $v2) {
                        if ($k2 == $on) {
                            $sortable_array[$k] = $v2;
                        }
                    }
                } else {
                    $sortable_array[$k] = $v;
                }
            }
            
            switch ($order) {
                case 1:
                    asort($sortable_array);
                    break;
                case - 1:
                    arsort($sortable_array);
                    break;
            }
            
            foreach ($sortable_array as $k => $v) {
                $new_array[$k] = $array[$k];
            }
        }
        
        return $new_array;
    }

    public function getClassnRef($methods, $collection)
    {
        $laf = new MongoObjectFactory();
        $log = Log::getInstance();
        $classRet = "";
        // $log->AddRow(" Get Col ref action : -- " . json_encode($methods) . ' --- ' . json_encode($collection) . ' .... ');
        
        // $isCriteria = strpos($criteria, "-");
        foreach ($methods as $method) {
            $collectionNew = array();
            foreach ($collection as $object) {
                // if ($method . stringEndsWith("()")) {
                // execute on object
                if ($method == "getParent" || $method == "getNext" || $method == "getPrev" || $method == "getFirst" || $method == "getLast") {
                    $arrayNew = array();
                    // $log->AddRow(" 1Get Parent ref parent action : -- " . json_encode($object) . ' --- ');
                    
                    // $reflectionMethod = new \ReflectionMethod($type, 'getParent');
                    $reflectionMethod = new \ReflectionMethod($object, $method);
                    $objectNew = $reflectionMethod->invoke($object);
                    $classRet = get_class($objectNew);
                } else 
                    if ($this->substr_startswith($method, 'get') || $this->substr_startswith($method, 'cg')) {
                        $criteria = array();
                        $actionName = substr($method, 0, 3);
                        $typeName = substr($method, 3);
                        $valuesStr = $this->get_string_between($method, "[", "]");
                        if (isset($valuesStr)) {
                            $classRet = $this->get_string_between($method, "get", "[");
                        } else {
                            $classRet = $typeName;
                        }
                    }
            }
            // return $classRet;
        }
        return $classRet;
    }

    function recreatePath($arrayMethod, $char)
    {
        $strPath = "";
        $firstTime = true;
        foreach ($arrayMethod as $method) {
            if ($this->substr_startswith($method, 'eval')) {} else {
                if ($firstTime) {
                    $strPath = $char . $method;
                    $firstTime = false;
                } else {
                    $strPath = $strPath . $char . $method;
                }
            }
        }
        return $strPath;
    }

    function get_string_between($string, $start, $end)
    {
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0)
            return null;
        $ini += strlen($start);
        if (strpos($string, $end, $ini) > $ini) {
            $len = strpos($string, $end, $ini) - $ini;
            return substr($string, $ini, $len);
        } else {
            return null;
        }
    }

    function substr_startswith($haystack, $needle)
    {
        return substr($haystack, 0, strlen($needle)) === $needle;
    }

    private function getNameFromMethods($methods)
    {
        // $isCriteria = strpos($criteria, "-");
        $typeName = "";
        foreach ($methods as $method) {
            $valuesStr = $this->get_string_between($method, "[", "]");
            if (isset($valuesStr)) {
                $typeName = $this->get_string_between($method, "get", "[");
            } else {
                $typeName = substr($method, 3);
            }
        }
        return $typeName;
    }

    public function reportBuilder($template, $data)
    {
        /**
         * Report Builder
         * Takes in a template and an array of data, replaces the keys within the template with the data,
         * and returns the completed combination.
         * - $template: The template, generally HTML, including keys to be replaced.
         * - $data: An array containing the data to replace the template keys, which takes the following form:
         * $data = array(
         * "main" => array($key1 => $value1, $key2 => $value2),
         * "lists" => array(
         * $list1 => array(
         * array($key31 => $value31, $key32 => $value32),
         * array($key41 => $value41, $key42 => $value42)
         * ),
         * $list2 => array(
         * array($key51 => $value51, $key52 => $value52),
         * array($key61 => $value61, $key62 => $value62)
         * )
         * )
         * );
         */
        // Replace values in blocks
        if (is_array($data['lists']) && ! empty($data['lists'])) {
            foreach ($data['lists'] as $list => $list_data) {
                // Get container
                preg_match('/\[' . $list . '\]([^\[]+)\[\/' . $list . '\]/', $template, $matches);
                if (! empty($matches)) {
                    foreach ($list_data as $values) {
                        // Replace contents of a container
                        $container = $matches[1];
                        // print $container;
                        foreach ($values as $var => $value) {
                            // Replace individual variables within container
                            $container = str_replace("%$var%", $value, $container);
                        }
                        
                        // Put the container into pieces array
                        $pieces[] = $container;
                    }
                    
                    // Replace container within template with instances in pieces array
                    $template = preg_replace('/\[' . $list . '\][^\[]+\[\/' . $list . '\]/i', str_replace("\$", "\\\$", implode("", $pieces)), $template);
                    unset($pieces);
                }
            }
        }
        // Replace stand-alone values
        foreach ($data['main'] as $var => $value) {
            if (! is_array($value)) {
                $template = str_replace("%$var%", $value, $template);
            }
        }
        
        // Return completed template
        return $template;
    }

    public function startCron()
    {
        try {
            $command = 'php ' . getcwd() . '/public/index.php run startcron';
            // $command = 'php -v';
            echo exec($command, $test);
        } catch (\Exception $e) {
            print_r($e->getMessage());
            exit();
        }
    }

    /**
     * Constructor: __construct
     * Allow for CORS, assemble and pre-process the data
     */
    public function getServiceLocator()
    {
        return ServiceLocatorFactory::getInstance();
    }
}