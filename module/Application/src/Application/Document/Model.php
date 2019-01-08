<?php
namespace Application\Document;

use Zend\Stdlib\JsonSerializable;
use Application\Controller\MongoObjectFactory;
use Application\Controller\Application\Controller;
use Application\DatabaseConnection\Database;
use Application\Service\Service;
use Doctrine\Common\Collections\Criteria;

abstract class Model extends Base implements JsonSerializable
{

    public $recid = "";

    public $id;

    public $id_key;

    public $parent = array();

    public $version;

    public $deleted = 0;

    public static function getMappingColumns()
    {
        return array();
    }

    /**
     *
     * @return the $deleted
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    public function getTooltip($templateId = null, $idArray = null)
    {
        return null;
    }

    /**
     *
     * @param field_type $deleted            
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;
    }

    public function __construct($json = false)
    {
        if ($json) {
            $data = array();
            $data = json_decode($json, true);
            if (isset($data['_id'])) {
                $this->beforeLoad(json_decode($json, true));
                $this->load(json_decode($json, true));
                $this->afterLoad(json_decode($json, true));
            } else {
                $this->beforeCreate(json_decode($json, true));
                $this->set(json_decode($json, true));
                $this->afterCreate(json_decode($json, true));
            }
        }
    }

    public function beforeCreate($json = false)
    {}

    public function afterCreate($json = false)
    {}

    public function beforeLoad($json = false)
    {}

    public function afterLoad($json = false)
    {}

    public function beforeSet($json = false)
    {}

    public function afterSet($json = false)
    {}

    public function update($json = false, &$state = '', $forceStopPropagation = false)
    {
        if ($json) {
            $this->beforeSet(json_decode($json, true));
            $this->updateSet(json_decode($json, true), $state, $forceStopPropagation);
            $this->afterSet(json_decode($json, true));
        } else {
            if (isset($_SESSION["transaction_id"])) {
                // $state = new State();
                // $state->setTransactionid("UNKNOWN");
                // \Application\Controller\Log::getInstance()->AddRow(' SAVASTATE SETX value ' . json_encode($this));
                // $laf = new MongoObjectFactory();
                $dataNew = array();
                // save state of this before changing it
                $sData = new StateData();
                $sData->setObjectData($this);
                $sData->setType(StateData::UPDATE);
                $sData->setObjecttype($this->getClassName());
                $sData->setObjectid($this->getIdAsString());
                // $laf->saveStateData($_SESSION["transaction_id"], $sData);
            }
            $this->_id = new \MongoId($this->get_id());
            $this->updateObject((string) $this->_id, $this->getTableName(), $this);
        }
        $this->reload();
    }

    public function reload($deleted = 0)
    {
        if (isset($this->_id) && count_chars($this->get_id() > 1)) {
            $this->_id = new \MongoId($this->get_id());
            
            $laf = new \Application\Controller\MongoObjectFactory();
            $objData = $laf->findObjectJSON($this->get_class_name($this), (string) $this->_id, $deleted);
            if (isset($objData)) {
                $this->load($objData);
            }
        }
    }

    /**
     *
     * @return the $version
     */
    public function get_version()
    {
        return $this->version;
    }

    /**
     *
     * @param field_type $version            
     */
    public function set_version($version)
    {
        $this->version = $version;
    }

    /**
     *
     * @return the $id
     */
    public function get_id()
    {
        if ($this->_id instanceof \MongoId) {
            return (string) $this->_id;
        } else {
            return $this->_id['$id'];
        }
    }

    /**
     *
     * @param field_type $id            
     */
    public function set_id($id)
    {
        $this->_id = $id;
    }

    public function jsonSerialize()
    {
        $getter_names = get_class_vars(get_class($this)); // methods(get_class($this));
        $gettable_attributes = array();
        foreach ($getter_names as $key => $value) {
            $gettable_attributes[$key] = $this->{$key};
        }
        if (! isset($this->_id)) {} else {
            if ($this->_id instanceof \MongoId) {
                $gettable_attributes["_id"] = (string) $this->_id;
            } else if (isset($this->_id['$id'])) {
                $gettable_attributes["_id"] = new \MongoId((string) $this->_id['$id']);
            } else {
                $gettable_attributes["_id"] = new \MongoId((string) $this->_id);
            }
        }
        return $gettable_attributes;
    }

    public function getAttributes()
    {
        $getter_names = get_class_vars(get_class($this));
        $gettable_attributes = array();
        foreach ($getter_names as $key => $value) {
            
            $gettable_attributes[$key] = $this->{$key};
        }
        return $gettable_attributes;
    }

    public function getMainAttributes()
    {
        $getter_names = get_class_vars(get_class($this));
        $gettable_attributes = array();
        foreach ($getter_names as $key => $value) {
            if (is_array($this->{$key})) {
                $gettable_attributes[] = $key;
            } else {}
        }
        return $gettable_attributes;
    }

    public static function getPK()
    {
        return '_id';
    }

    public function getRelationDetails($name)
    {
        $relations = array();
        return static::getRelationRefFromArray($name, $relations);
    }

    public function getExternalRelations()
    {
        $relations = array();
        return $relations;
    }

    public function getReferenceRelations()
    {
        $owning_rel = array();
        $relations = $this->getRelationType('');
        foreach ($relations as $key => $value) {
            // only if a ref relation that has id/ref
            if ($value == 'one-to-one' || $value == 'one-to-many' || $value == 'many-to-one') {
                $owning_rel[$key] = $value;
            }
        }
        return $owning_rel;
    }

    public function getSpecialReferenceRelations()
    {
        $owning_rel = array();
        $relations = $this->getRelationType('');
        foreach ($relations as $key => $value) {
            // only if a ref relation that has id/ref
            if ($value == "next" || $value == 'prev' || $value == 'first' || $value == 'last') {
                $owning_rel[$key] = $value;
            }
        }
        return $owning_rel;
    }

    public function getPropagativeRelations()
    {
        $prop_rel = array();
        $relations = $this->getRelationType('');
        foreach ($relations as $key => $value) {
            // only if a ref relation that has id/ref
            if ($value == $this::DECL_OWNING || $value == $this::DECL_REF) {
                $prop_rel[$key] = $value;
            }
        }
        return $prop_rel;
    }

    public function getOwningRelations()
    {
        $owning_rel = array();
        $relations = $this->getRelationType('');
        foreach ($relations as $key => $value) {
            $pos = strpos($value, "owning");
            if ($pos === false) {} else {
                $owning_rel[$key] = $value;
            }
        }
        return $owning_rel;
    }

    public function getAdminRelations()
    {
        $laf = new \Application\Controller\MongoObjectFactory();
        $owning_rel = array();
        $relations = $this->getRelationType('');
        foreach ($relations as $key => $value) {
            $pos = strpos($value, "owning");
            if ($pos === false) {} else {
                $mainTypes = $laf->getAdminTypes();
                
                foreach ($mainTypes as $key1) {
                    $pos = strpos($key, strtolower($key1));
                    if ($pos === false) {} else {
                        $owning_rel[$key] = $value;
                    }
                }
            }
        }
        return $owning_rel;
    }

    public function getRelations()
    {
        return $this->getRelationType('');
    }

    public static function getFieldType()
    {
        return null;
        //
        // $fields = array();
        // return self::getFieldFromArray($name, $fields);
    }

    public static function getFieldFromArray($name, $field, $param, $array)
    {
        if (array_key_exists($name, $array) == true) {
            if (isset($array[$name][$field][$param])) {
                return $array[$name][$field][$param];
            }
        }
        return null;
    }

    public static function getFieldTypeFromArray($array, $type, $keyIn = false)
    {
        // \Application\Controller\Log::getInstance()->AddRow(' LineFORMATxx1 '.$type. ' - '. $keyIn . ' value ' . json_encode($array));
        if (isset($array)) {
            $columns = array_column($array, "type");
            // \Application\Controller\Log::getInstance()->AddRow(' LineFORMATxx22 ' . $keyIn . ' value ' . json_encode($columns));
            if (isset($columns)) {
                $keys = array_keys(array_column($array, "type"), $type);
                // \Application\Controller\Log::getInstance()->AddRow(' LineFORMATxx2 ' . $keyIn . ' value ' . json_encode($keys));
                $keysArray = array();
                if (count($keys) > 0) {
                    Foreach ($keys as $key) {
                        if ($keyIn) {
                            if ($array[$key]['key'] == $keyIn)
                                $keysArray[] = $array[$key];
                        } else {
                            $keysArray[] = $array[$key];
                        }
                    }
                }
                if (isset($keysArray) && count($keysArray) > 0) {
                    // / \Application\Controller\Log::getInstance()->AddRow(' LineFORMATxx3 ' . $keyIn . ' value ' . json_encode($keysArray));
                    
                    return $keysArray;
                } else {
                    return false;
                }
            }
        }
        return false;
    }

    public function getInstancesReference($path, $arraySearch = '', $orderBy = '')
    {
        $collection = array();
        $collection[] = $this;
        $service = new Service();
        $methodsRef = explode(".", $path);
        // $search["search"] = [{"field":"date","type":"date","operator":"between","value":["10/5/2016","10/5/2016"]}],"searchLogic":"AND"}"
        $resultRef = $service->getCollectionRef($methodsRef, $collection, 0, 0, $arraySearch, $orderBy);
        return $resultRef;
    }

    public function add($typeC, $json)
    {
        $laf = new \Application\Controller\MongoObjectFactory();
        $typeClass = $laf->getClassPath($typeC) . $typeC;
        $foundPk = $this->isUniquePK($typeC, $json);
        if ($foundPk === true) {
            $typeRelClass = new \ReflectionClass($typeClass);
            $nameRel = \strtolower($typeRelClass->getShortName());
            // encode the string
            $jsonString = json_encode($json);
            // expected to be an array - so create one for next
            $arguments[] = $jsonString;
            // create instance with JSON
            // \Application\Controller\Log::getInstance()->AddRow(' LineXXX ' . $typeC . ' value ' . json_encode($arguments));
            
            $class = $typeRelClass->newInstanceArgs($arguments);
            // \Application\Controller\Log::getInstance()->AddRow(' LineXXX ' . $typeC . ' value ' . json_encode($class));
            
            $classP = $this->get_class_name($this);
            $nameParentRel = \strtolower($classP);
            $nameP = \strtolower($nameParentRel) . 's';
            if ($this->_id instanceof \MongoId) {
                $idRel['$id'] = (string) $this->_id;
            } else {
                $idRel = $this->_id;
            }
            $reference = array();
            $reference['$ref'] = $nameP;
            $reference['$id'] = $idRel['$id'];
            $class->parent[] = $reference;
            
            // $class->setId(\uniqid());
            $m = Database::getInstance();
            // select a database
            $db = $m->{$laf->getDBName($typeRelClass->getShortName())};
            $name = \strtolower($typeRelClass->getShortName()) . 's';
            
            $collection = $db->$name;
            $collection->insert($class);
            // create simple id
            $fieldType = $this::getFieldTypeFromArray($class::getFieldType(), Field::AUTO_INCREMENT);
            // \Application\Controller\Log::getInstance()->AddRow(' LinePPPPP ' . $typeC . ' value ' . json_encode($fieldType));
            $json['id'] = hexdec(substr((string) $class->_id, 20)) . hexdec(substr((string) $class->_id, 6, 2));
            
            if (isset($fieldType) && $fieldType) {
                // if ($fieldType == self::AUTO_INCREMENT) {
                // \Application\Controller\Log::getInstance()->AddRow(' LinePPPPP1 ' . $fieldType[0]['key'] . ' value ' . json_encode($typeC));
                
                $lastInstance = $this->getLastInstance($typeC, $fieldType[0]['key']);
                if (isset($lastInstance) && isset($lastInstance[0]) && isset($lastInstance[0]->id)) {
                    $json[$fieldType[0]['key']] = $lastInstance[0]->{$fieldType[0]['key']} + 1;
                } else {
                    $json[$fieldType[0]['key']] = 1;
                }
                // }
            }
            
            $fieldType = $this::getFieldTypeFromArray($class::getFieldType(), Field::AUTO_INCREMENT);
            // \Application\Controller\Log::getInstance()->AddRow(' LinePPPPP ' . $typeC . ' value ' . json_encode($fieldType));
            $json['id'] = hexdec(substr((string) $class->_id, 20)) . hexdec(substr((string) $class->_id, 6, 2));
            
            if (isset($fieldType) && $fieldType) {
                // if ($fieldType == self::AUTO_INCREMENT) {
                // \Application\Controller\Log::getInstance()->AddRow(' LinePPPPP1 ' . $fieldType[0]['key'] . ' value ' . json_encode($typeC));
                
                $lastInstance = $this->getLastInstance($typeC, $fieldType[0]['key']);
                if (isset($lastInstance) && isset($lastInstance[0]) && isset($lastInstance[0]->id)) {
                    $json[$fieldType[0]['key']] = $lastInstance[0]->{$fieldType[0]['key']} + 1;
                } else {
                    $json[$fieldType[0]['key']] = 1;
                }
                // }
            }
            $json['version'] = "" . time();
            $json['id_key'] = hexdec(substr((string) $class->_id, 0, 8)) . hexdec(substr((string) $class->_id, 20));
            // \Application\Controller\Log::getInstance()->AddRow(' LinePPPPP3 ' . $class->_id . ' value ' . json_encode($json));
            
            // save state of this before changing it
            $sData = new StateData();
            $sData->setObjectData($class);
            $sData->setType(StateData::ADD);
            $sData->setObjecttype($class->getClassName());
            $sData->setObjectid((string) $class->_id);
            if (isset($_SESSION)) {
                $laf->saveStateData($_SESSION['transaction_id'], $sData);
            }
            // reset id to real from Mongo
            // $this->_id['$id'] = (string)$this->_id['$id'];
            $state = new State();
            $class->updateSet($json, $state, true);
            $this->reload();
            $name = \strtolower($nameRel) . 's';
            
            // create the parent - child relation find the object
            $refInstance = $laf->findObject($typeC, $class->_id);
            $contianerRef = array();
            $contianerRef = $this->getRelationDetails($name);
            // if (isset($_SESSION["transaction_type"]) && $_SESSION["transaction_type"] != "method") {
            if (isset($contianerRef{'indexed'})) {
                if ($contianerRef{'indexed'}) {
                    $sortIndex = '';
                    if (isset($contianerRef{'sorted'})) {
                        $sortIndex = $contianerRef{'sorted'};
                    }
                    $this->addLinkedReference($nameRel, $refInstance, $sortIndex);
                }
            }
            // }
            // \Application\Controller\Log::getInstance()->AddRow(' LinePPPPP9 ' . $class->_id . ' value ' . json_encode($refInstance));
            
            // get the ref
            $refInstance = $laf->findObject($typeC, $class->_id);
            
            $idRel = $refInstance->_id;
            $reference['$ref'] = $name;
            // if rel odm
            if ($typeC === "Workspace" || $typeC === "User") {
                $reference['$id'] = new \MongoId($idRel['$id']);
                $reference['$db'] = $laf->getDBName($typeRelClass->getShortName());
            } else {
                $reference['$id'] = $idRel['$id'];
            }
            $this->{$name}[] = $reference;
            if ($this->_id instanceof \MongoId) {} else {
                $io = (string) $this->_id['$id'];
                if ($io != "") {
                    $this->_id = new \MongoId($io);
                }
            }
            \Application\Controller\Log::getInstance()->AddRow(' LinePPPPP10 ' . $class->_id . ' value ' . json_encode($this));
            
            $this->persist();
            // \Application\Controller\Log::getInstance()->AddRow(' LinePPPP12 ' . $class->_id . ' value ' . json_encode($refInstance));
            
            // update relation container
            $contianerRef = array();
            
            $name = $refInstance->getTableName();
            // \Application\Controller\Log::getInstance()->AddRow(' LinePPPP13 ' . $class->_id . ' value ' . json_encode($refInstance));
            
            $contianerRef = $this->getRelationDetails($name);
            // \Application\Controller\Log::getInstance()->AddRow(' LinePPPP14 ' . $class->_id . ' value ' . json_encode($refInstance));
            
            $state = new State();
            // if (isset($_SESSION["transaction_type"]) && $_SESSION["transaction_type"] != "methodX") {
            // a new instance has been added - now propagate!
            
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
                // $this->reload();
                // TODORELI - PROPAG
                // $this->reindexReference($refInstance->getClassNameFromTable($name), $sortIndex);
                $this->reload();
                $this->propagate();
                // $this->reload();
                
                $refInstance = $laf->findObject($typeC, $class->_id);
                // $refInstance->reload();
                $refInstance->propagate($state);
                // $refInstance->reload();
                // if ($propagate) {
                // $state = new State();
                // $refInstance->updateSet($json, $state);
                
                // $refInstance->propagate($state, [], false, false);
                // $this->reload();
                // }
            } else {
                $refInstance->propagate($state);
                // $refInstance->reload();
                $this->reload();
                $this->propagate($state);
            }
            // }
            $this->reload();
            
            return $idRel['$id'];
        } else {
            return $foundPk;
        }
        return false;
    }

    public function persist()
    {
        $laf = new \Application\Controller\MongoObjectFactory();
        $m = Database::getInstance();
        $db = $m->{$laf->getDBName($this->get_class_name($this))};
        $nameTable = $this->getTableName();
        $collection = $db->$nameTable;
        $log = \Application\Controller\Log::getInstance();
        $thisClone = clone $this;
        foreach ($this as $key => $value) {
            
            if (is_array($value)) {
                // $dataX->{$key} = $data->{$key};
            } else {
                $thisClone->{$key} = $this->getFormatVarialble($value, $key);
            }
        }
        // update container instance
        // $mongoId = new $id;
        
        $collection->update(array(
            '_id' => $this->_id
        ), $thisClone, array(
            "upsert" => true
        ));
    }

    public function addChild($child, $name)
    {
        $laf = new \Application\Controller\MongoObjectFactory();
        $reference = array();
        $reference['$ref'] = $name;
        // if rel odm
        if ($child->_id instanceof \MongoId) {} else {
            $io = (string) $child->_id['$id'];
            if ($io != "") {
                $child->_id = new \MongoId($io);
            }
        }
        
        $reference['$id'] = (string) $child->_id;
        
        $this->{$name}[] = $reference;
        // update relation container
        $m = Database::getInstance();
        $db = $m->{$laf->getDBName($this->get_class_name($this))};
        $nameTable = $this->getTableName();
        $collection = $db->$nameTable;
        if ($this->_id instanceof \MongoId) {} else {
            $io = (string) $this->_id['$id'];
            if ($io != "") {
                $this->_id = new \MongoId($io);
            }
        }
        // update container instance
        // $mongoId = new $id;
        $collection->update(array(
            '_id' => $this->_id
        ), $this, array(
            "upsert" => true
        ));
    }

    public function addLinkedReference($type, $object, $sortIndex = '')
    {
        if (isset($sortIndex)) {
            $fromValue = $object->{$sortIndex};
            if (is_numeric($fromValue)) {
                $methodsRef = "get" . $type;
                $arrS = array();
                $arrS["field"] = $sortIndex;
                $arrS["type"] = "int";
                $arrS["operator"] = "more";
                $arrS["value"] = $fromValue;
            } else {
                $methodsRef = "get" . $type;
                $arrS = array();
                $arrS["field"] = $sortIndex;
                $arrS["type"] = "date";
                $arrS["operator"] = "more";
                $arrS["value"] = $fromValue;
            }
            $arraySearch = [];
            
            $arraySearch["search"][] = $arrS;
            
            $arraySearch["searchLogic"] = "AND";
            // add order by
            $sort = array();
            $sortF = array();
            $sortF["field"] = $sortIndex;
            $sortF["direction"] = "asc";
            $sort[] = $sortF;
            \Application\Controller\Log::getInstance()->AddRow(' addLinkedReference1 - .' . $methodsRef . "| -- " . json_encode($arraySearch));
            $collection = $this->getInstancesReference($methodsRef, $arraySearch, $sort);
            \Application\Controller\Log::getInstance()->AddRow(' addLinkedReference1 - .' . $methodsRef . "| -- " . json_encode($collection));
            if (sizeof($collection) == 0) {
                $i = $this->countQuickInstancesCriteria($type, array());
                // \Application\Controller\Log::getInstance()->AddRow(' countQuickInstancesCriteria - .' . $i . "| -- " );
                
                if ($i > 0) {
                    // \Application\Controller\Log::getInstance()->AddRow(' addLinkedReference1 - .' . $methodsRef . "| -- " . json_encode($i));
                    $object->index = $i;
                    $object->update();
                    $object->reload();
                    // $last = $collection[count($collection) - 1];
                    $lastX = $this->getLast($type . "s");
                    // \Application\Controller\Log::getInstance()->AddRow(' addLinkedReference1 - .' . $methodsRef . "| -- " . json_encode($lastX));
                    $lastX->next = array();
                    $object->prev = array();
                    $object->update();
                    $lastX->update();
                    $object->reload();
                    $lastX->reload();
                    $lastX->addRelationReferenceObject("next", $object->_id, "prev", $lastX->getTableName());
                    $this->addRelationReferenceObject("last", $object->_id, "next", $object->getTableName());
                } else {
                    $object->prev = array();
                    $object->index = 0;
                    $object->update();
                    $object->reload();
                    $this->addRelationReferenceObject("first", $object->_id, "prev", $object->getTableName());
                    $this->addRelationReferenceObject("last", $object->_id, "next", $object->getTableName());
                }
                return true;
            } else {
                $totI = $this->countQuickInstancesCriteria($type, array());
                
                $subColI = sizeof($collection);
                $item = $collection[0]; // $this->getInstanceAtIndex($totI - $subColI, $type);
                $object->index = $totI - $subColI;
                $index = $totI - $subColI;
                $object->update();
                $object->reload();
                $i = $totI - $subColI;
                \Application\Controller\Log::getInstance()->AddRow(' countQuickInstancesCriteria1 - .' . $i . "| -- ");
                foreach ($collection as $itemI) {
                    $itemI->index = $i + 1;
                    $itemI->update();
                    $itemI->reload();
                    // $itemI = $item;
                    
                    $i = $i + 1;
                }
                $item->reload();
                $prev = $item->getPrev();
                $item->prev = array();
                // $last->prev = array();
                $object->next = array();
                $object->prev = array();
                
                \Application\Controller\Log::getInstance()->AddRow(' addLinkedReference1 - .' . $methodsRef . "| -- " . json_encode($prev));
                
                if (isset($prev->next)) {
                    \Application\Controller\Log::getInstance()->AddRow(' addLinkedReference1 pnextrev - .' . $methodsRef . "| -- " . json_encode($prev));
                    
                    $item->addRelationReferenceObject("prev", $object->_id, "next", $object->getTableName());
                    $prev->addRelationReferenceObject("next", $object->_id, "prev", $object->getTableName());
                } else {
                    \Application\Controller\Log::getInstance()->AddRow(' addLinkedReference1 prev - .' . $methodsRef . "| -- " . json_encode($prev));
                    $item->addRelationReferenceObject("prev", $object->_id, "next", $object->getTableName());
                    $this->addRelationReferenceObject("first", $object->_id, "prev", $object->getTableName());
                }
                return true;
            }
        }
        
        $collection = $this->getInstances($type);
        $object->reload();
        if (sizeof($collection) == 0) {
            $object->prev = array();
            $id = array();
            // TODO - change the FIRST to be a list!
            $object->index = 1;
            $object->update();
            $object->reload();
            $this->addRelationReferenceObject("first", $object->_id, "prev", $object->getTableName());
            $this->addRelationReferenceObject("last", $object->_id, "next", $object->getTableName());
        } else if (sizeof($collection) > 0) {
            
            if ($sortIndex == '') {
                $last = $collection[count($collection) - 1];
                $last->next = array();
                $object->prev = array();
                $last->addRelationReferenceObject("next", $object->_id, "prev", $last->getTableName());
                $this->addRelationReferenceObject("last", $object->_id, "next", $object->getTableName());
            } else {
                $errorReporting = error_reporting(0);
                usort($collection, array(
                    new cmp($sortIndex),
                    "cmp__"
                ));
                error_reporting($errorReporting);
                $last = null;
                $i = 1;
                $found = false;
                $lastX = null;
                $index = 2;
                $next = null;
                foreach ($collection as $item) {
                    
                    if (! $found && ($this->getFormatVarialble($item->{$sortIndex}) >= $this->getFormatVarialble($object->{$sortIndex}))) {
                        $object->index = $i;
                        $index = $i;
                        $object->update();
                        $object->reload();
                        $found = true;
                        $item->index = $i + 1;
                        $item->update();
                        $item->reload();
                        $next = $item;
                        
                        $i = $i + 1;
                    } else {
                        if ($found == true) {
                            $item->index = $i;
                            $item->update();
                        }
                        $lastX = $item;
                    }
                    $i = $i + 1;
                }
                
                if (! $found) {
                    $object->index = $i;
                    $object->update();
                    $object->reload();
                    // $last = $collection[count($collection) - 1];
                    $lastX->next = array();
                    $object->prev = array();
                    $object->update();
                    $lastX->update();
                    $object->reload();
                    $lastX->reload();
                    $lastX->addRelationReferenceObject("next", $object->_id, "prev", $lastX->getTableName());
                    $this->addRelationReferenceObject("last", $object->_id, "next", $object->getTableName());
                } else {
                    
                    if ($index > 1) {
                        $prev = $next->getPrev();
                        $next->prev = array();
                        // $last->prev = array();
                        $object->next = array();
                        $object->prev = array();
                        $next->addRelationReferenceObject("prev", $object->_id, "next", $object->getTableName());
                        $prev->addRelationReferenceObject("next", $object->_id, "prev", $object->getTableName());
                    } else {
                        
                        // $last->prev = array();
                        $object->next = array();
                        $object->prev = array();
                        $this->addRelationReferenceObject("first", $object->_id, "prev", $object->getTableName());
                        $next->addRelationReferenceObject("prev", $object->_id, "next", $object->getTableName());
                    }
                }
            }
        }
    }

    public function getFormatVarialble($input, $key = null)
    {
        // \Application\Controller\Log::getInstance()->AddRow(' LineFORMATx0 ' . $key . ' value ' . json_encode($input));
        $laf = new \Application\Controller\MongoObjectFactory();
        if (isset($key)) {
            // $this->getFieldType($this->getClassName(), $key);
            if ($this->substr_startswith($key, 'id_') || $this->substr_startswith($key, 'Id_') || $this->substr_startswith($key, 'ID_')) {
                return $input;
            } else {
                if (is_array($input)) {
                    $fieldFileType = $this->getFieldTypeFromArray($this::getFieldType(), Field::FILE_FORMAT);
                    // if there is a file type, save it on server
                    if (isset($fieldFileType) && $fieldFileType && isset($input) && isset($input['content'])) {
                        $user = $laf->getOdmUser();
                        $urlDestination = getcwd() . '/public/img/upload/' . $user->getOrganization()->getId();
                        
                        // make a folder with hotel id if doesn't exist one
                        if (! file_exists(realpath($urlDestination))) {
                            mkdir($urlDestination, 0777, true);
                        }
                        $fileData = $input;
                        
                        // print_r($fileData);exit;
                        $binary = base64_decode($fileData['content']);
                        if (isset($fileData['name']) && strlen($fileData['name']) > 1) {
                            $fileName = $urlDestination . "/" . $fileData['name'];
                            $filePath = '/public/img/upload/' . $user->getOrganization()->getId() . "/" . $fileData['name'];
                            $file = fopen($fileName, 'wb');
                            fwrite($file, $binary);
                            fclose($file);
                            return $filePath;
                        }
                        return "";
                    }
                }
                
                $fieldFormat = $this->getFieldTypeFromArray($this::getFieldType(), Field::CUSTOM_FORMAT, $key);
                
                if (isset($fieldFormat) && $fieldFormat) {
                    // \Application\Controller\Log::getInstance()->AddRow(' LineFORMATx99 ' . $key . ' value ' . json_encode($fieldFormat));
                    
                    // if ($fieldFormat == self::CUSTOM_FORMAT) {
                    if (is_numeric($input)) {
                        $input = $input + 0;
                    }
                    $fieldParam = $fieldFormat[0]['param'];
                    $valRet = sprintf($fieldParam, $input);
                    // \Application\Controller\Log::getInstance()->AddRow(' LineFORMATx1 ' . $key . ' value ' . json_encode($valRet));
                    
                    return $valRet;
                }
            }
            // }
        }
        if (is_array($input)) {
            return $input;
        }
        if (is_numeric($input)) {
            if (strlen($input) > 1 && (substr($input, 0, 1) === '0' || substr($input, 0, 1) === '+')) {
                return $input;
            } else {
                return $input + 0;
            }
        }
        
        if ($input == 'TRUE') {
            $input = 'true';
            return $input;
        }
        if ($input == 'FALSE') {
            $input = 'false';
            return $input;
        }
        // $input = trim($input);
        if (strlen($input) < 11) {
            $formats = array(
                "d.m.Y",
                "d/m/Y",
                "d-m-Y"
            );
            foreach ($formats as $format) {
                $date = \DateTime::createFromFormat($format, $input);
                if ($date == false || ! (date_format($date, $format) == $input)) {} 
                else {
                    // date("d-m-Y", strtotime($input));//
                    return new \MongoDate(strtotime($input));
                }
            }
        } else {
            // $input = trim($input);
            $formats = array(
                "d-m-Y H:i:s",
                "d-m-Y H:i",
                "d-m-Y h:i:s",
                "d-m-Y h:i",
                "d-m-Y g:i",
                "d-m-Y g:i:s",
                "d-m-Y G:i",
                "d-m-Y G:i:s"
            );
            foreach ($formats as $format) {
                $date = \DateTime::createFromFormat($format, $input);
                if ($date == false || ! (date_format($date, $format) == $input)) {} 
                else {
                    // date("d-m-Y", strtotime($input));//
                    // flag that this is date time!!
                    $usec = 1241;
                    return new \MongoDate(strtotime($input), $usec);
                }
            }
        }
        return $input;
    }

    public function getFormatProperties()
    {
        return $this;
    }

    public function reindexReference($type, $sortIndex = '', $force = false, $fromValue = null)
    {
        $firstObj = null;
        \Application\Controller\Log::getInstance()->AddRow(' reindex1c ' . $type);
        if (isset($fromValue)) {
            \Application\Controller\Log::getInstance()->AddRow(' reindexReference - getShiftsTypeInInterval');
            if (is_numeric($fromValue)) {
                $methodsRef = "get" . $type;
                $arrS = array();
                $arrS["field"] = $sortIndex;
                $arrS["type"] = "int";
                $arrS["operator"] = "more";
                $arrS["value"] = $fromValue;
            } else {
                $methodsRef = "get" . $type;
                $arrS = array();
                $arrS["field"] = $sortIndex;
                $arrS["type"] = "date";
                $arrS["operator"] = "more";
                $arrS["value"] = $fromValue;
            }
            
            $arraySearch = [];
            
            $arraySearch["search"][] = $arrS;
            
            $arraySearch["searchLogic"] = "AND";
            // add order by
            $sort = array();
            $sortF = array();
            $sortF["field"] = $sortIndex;
            $sortF["direction"] = "asc";
            $sort[] = $sortF;
            $collection = $this->getInstancesReference($methodsRef, $arraySearch, $sort);
        } else {
            $collection = $this->getInstances($type);
        }
        $flag = false;
        foreach ($collection as $item) {
            // \Application\Controller\Log::getInstance()->AddRow(' Reindex1x ' . json_encode($item->getNext()) . " == " . $sortIndex);
            
            if (isset($item->next) && isset($item->getNext()->{$sortIndex}) && isset($item->{$sortIndex})) {
                if ($this->getFormatVarialble($item->getNext()->{$sortIndex}) <= $this->getFormatVarialble($item->{$sortIndex})) {
                    \Application\Controller\Log::getInstance()->AddRow(' Reindex1xFOUND ' . json_encode($item->getNext()) . " == " . $sortIndex);
                    $flag = true;
                    break;
                }
            }
        }
        if ($flag == false && $force == false) {
            $firstObj = $this->getFirst($type);
            return $firstObj;
        }
        
        if (isset($sortIndex) && $sortIndex != '') {
            $my_cmp = new cmp($sortIndex);
            usort($collection, array(
                $my_cmp,
                "cmp__"
            ));
            
            if ($my_cmp->flag == true || count($collection) == 1 || $force == true) {
                \Application\Controller\Log::getInstance()->AddRow(' Reindex1x ' . " == " . $sortIndex);
                if (sizeof($collection) > 0) {
                    for ($i = 0; $i < count($collection); $i ++) {
                        $indexItem = $collection[$i];
                        $indexItem->index = $i;
                        
                        $indexItem->update();
                        // $indexItem->reload();
                        $id = array();
                        $id['$id'] = (string) $indexItem->_id;
                        // first element
                        if ($i == 0) {
                            $laf = new \Application\Controller\MongoObjectFactory();
                            $classRef = $type;
                            $object = $laf->findObject($classRef, (string) $indexItem->_id);
                            $this->addRelationReferenceObject("first", $id, "prev", $object->getTableName());
                            $object = $laf->findObject($classRef, (string) $indexItem->_id);
                            $firstObj = $object;
                            // $object->prev = null;
                            // $object->update();
                        }
                        if ($i > 0) {
                            $laf = new \Application\Controller\MongoObjectFactory();
                            $classRef = $type;
                            $object = $laf->findObject($classRef, (string) $collection[$i - 1]->_id);
                            $object->addRelationReferenceObject("next", $id, "prev", $collection[$i - 1]->getTableName());
                            // $object->update();
                            // $collection[$i]->update();
                        }
                        // last element
                        if ($i == count($collection) - 1) {
                            $laf = new \Application\Controller\MongoObjectFactory();
                            $classRef = $type;
                            $object = $laf->findObject($classRef, (string) $indexItem->_id);
                            $this->addRelationReferenceObject("last", $id, "next", $object->getTableName());
                        }
                        if ($i == 0) {
                            $laf = new \Application\Controller\MongoObjectFactory();
                            $classRef = $type;
                            $object = $laf->findObject($classRef, (string) $collection[$i]->_id);
                            $firstObj = $object;
                        }
                    }
                } else {
                    unset($this->first[0]);
                    unset($this->last[0]);
                    $this->update();
                    return null;
                }
            } else {
                $firstObj = $this->getFirst($type);
            }
            // $collection = $this->getInstances($type);
            // \Application\Controller\Log::getInstance()->AddRow(' Reindex2 ' . json_encode($collection) . ' -- ' . $sortIndex);
        } else {
            $firstObj = $this->getFirst($type);
        }
        return $firstObj;
    }

    public function getIdAsString()
    {
        $io = '';
        if ($this->_id instanceof \MongoId) {
            $io = (string) $this->_id;
        } else {
            $io = (string) $this->_id['$id'];
        }
        return $io;
    }

    public function convertIdToString($inputId)
    {
        if ($inputId instanceof \MongoId) {
            $io = (string) $inputId;
        } else if (is_array($inputId)) {
            $io = (string) $inputId['$id'];
        } else {
            $io = $inputId;
        }
        return $io;
    }

    public function get($typeC)
    {
        $listObjects = array();
        $name = \strtolower($typeC) . 's';
        \Application\Controller\Log::getInstance()->AddRow(' Line valueX ' . $name . ' ---- ' . json_encode($this));
        foreach ($this->{$name} as $id) {
            if (! is_null($id)) {
                $laf = new \Application\Controller\MongoObjectFactory();
                $classRef = $typeC;
                $object = $laf->findObjectJSON($classRef, $id['$id']);
                if ($object != null) {
                    $listObjects[] = $object;
                }
            }
        }
        
        return $listObjects;
    }

    public function getFirstIndex($typeC)
    {
        $listObjects = array();
        $name = \strtolower($typeC) . 's';
        // \Application\Controller\Log::getInstance()->AddRow(' Line value ' . json_encode($this->{$name}));
        foreach ($this->{$name} as $id) {
            if (! is_null($id)) {
                $laf = new \Application\Controller\MongoObjectFactory();
                $classRef = $typeC;
                $object = $laf->findObject($classRef, $id['$id']);
                if ($object instanceof Indexed) {
                    if ($object->index == 0) {
                        return $object;
                    }
                }
            }
        }
        return null;
    }

    public function getInstances($typeC, $dbName = '')
    {
        $listObjects = array();
        $name = \strtolower($typeC) . 's';
        if (isset($this->{$name})) {
            foreach ($this->{$name} as $id) {
                if (! is_null($id)) {
                    
                    $laf = new \Application\Controller\MongoObjectFactory();
                    $classRef = $typeC;
                    $object = $laf->findObjectInstance($classRef, $id['$id'], $dbName);
                    if ($object != null) {
                        $listObjects[] = $object;
                    }
                }
            }
        } else {
            \Application\Controller\Log::getInstance()->AddRow(' --> MAJOR EXCEPTION PARENT -- ' . json_encode($this) . ' -- ' . $typeC);
            \Application\Controller\Log::getInstance()->AddTrace();
        }
        return $listObjects;
    }

    public function getWorkspaceParent()
    {
        $Iparent = $this->getParent();
        while (isset($Iparent)) {
            
            if ($Iparent instanceof WorkspaceTemplate) {
                return $Iparent;
            }
            $Iparent = $Iparent->getParent();
        }
        return null;
    }

    public function getParent()
    {
        $Object = null;
        
        if (isset($this->parent) && isset($this->parent[0])) {
            
            if (is_array($this->parent[0])) {
                // \Application\Controller\Log::getInstance()->AddRow(' --> PARENT -- ' . json_encode($this) . ' -- ');
                $refT = $this->parent[0]['$ref'];
                $refId = $this->parent[0]['$id'];
                $refType = ucfirst(substr($refT, 0, strlen($refT) - 1));
                $laf = new \Application\Controller\MongoObjectFactory();
                $Object = $laf->findObject($refType, $refId);
            }
        }
        return $Object;
    }

    /**
     * Returns a reference instance list of format list - reference type
     *
     * @param type $typeRef            
     * @return List Model instance <\Application\Document\....>
     */
    public function getReferences($type)
    {
        $references = array();
        foreach ($this->{$type} as $key => $value) {
            $typeC = ucfirst(substr($type, 0, strlen($type) - 1));
            
            $references[] = $this->getInstance($typeC, $value['$id']);
        }
        return $references;
    }

    public function getPathReferences($pathString, $search = '')
    {
        if ($pathString == 'this') {
            $collection[] = $this;
            return $collection;
        }
        // get Path
        $path = explode(".", $pathString);
        // execute the eval on Ref's object
        $service = new \Application\Service\Service();
        $collection = array();
        $collection[] = $this;
        
        return $service->getCollectionRef($path, $collection, 0, 0, $search);
    }

    /**
     * SETs a reference list as part of the object - reference type a_b_c
     *
     * @param type $typeRef            
     * @return List Model instance <\Application\Document\....>
     */
    public function setReferences($typeArray)
    {
        $references = array();
        if (is_array($typeArray) && isset($typeArray[0])) {
            $type = $typeArray[0];
            unset($typeArray[0]);
            $typeArray = array_values($typeArray);
            $methods = explode("_", $type);
            
            $retObjs = null;
            // unset($methods[0]);
            $methodsLeft[] = $this->recreatePath($methods);
            foreach ($methods as $method) {
                $references = array();
                if ($this->substr_startswith($method, 'eval')) {
                    $col = array();
                    $data = array();
                    $data = explode("^", $method);
                    // data[1] name link for eval
                    // data[2] eval string
                    $col[] = $this->execute($data[1], $data[2], array());
                    foreach ($col as $values) {
                        $this->{$data[1]} = $values[$data[1]];
                    }
                } else {
                    $type = $method;
                    if (isset($this->{$type})) {
                        $arrayRef = $this->{$type};
                        foreach ($arrayRef as $key => $value) {
                            if (! is_array($value)) {
                                $retObjs[] = $value;
                            } else {
                                $typeC = ucfirst(substr($value['$ref'], 0, strlen($value['$ref']) - 1));
                                $criteria = array(
                                    '_id' => new \MongoId($value['$id'])
                                );
                                $laf = new \Application\Controller\MongoObjectFactory();
                                $classRef = $typeC;
                                // $value->setReferences($typeArray);
                                $retObjs = $laf->findInstancesByCriteria($classRef, $criteria);
                            }
                            foreach ($retObjs as $retObj) {
                                $retObj->setReferences($typeArray);
                            }
                            $references = array_merge($references, $retObjs);
                            // $this->{$type} = $references;
                        }
                        $this->{$type} = $references;
                    }
                    //
                }
            }
        }
        return $references;
    }

    function recreatePath($arrayMethod)
    {
        $strPath = "";
        $firstTime = true;
        foreach ($arrayMethod as $method) {
            if ($firstTime) {
                $strPath = $method;
                $firstTime = false;
            } else {
                $strPath = $strPath . "_" . $method;
            }
        }
        return $strPath;
    }

    /**
     * Returns true if a key exists
     *
     * @param type $typeRef            
     * @param $json input
     *            array
     * @return true or false
     */
    public function isUniquePK($typeC, $json)
    {
        $laf = new \Application\Controller\MongoObjectFactory();
        $typeClass = $laf->getClassPath($typeC) . $typeC;
        // $pkStr = eval($typeClass . '::getPK();');
        $reflectionMethod = new \ReflectionMethod($typeClass, 'getPK');
        $pkStr = $reflectionMethod->invoke(null, null);
        // \Application\Controller\Log::getInstance()->AddRow(' --> UNIQUEPK ' . $typeC . ' -- ' . json_encode($json) . ' -- ');
        
        if (isset($json[$pkStr])) {
            $uniqKey = $json[$pkStr];
            // $criteria = array();
            $criteria[$pkStr] = $uniqKey;
            $criteria["deleted"] = 0;
            $objects = $laf->findObjectsByCriteria($typeC, $criteria, false);
            // \Application\Controller\Log::getInstance()->AddRow(' --> UNIQUEPK1 ' . $pkStr . ' -- ' . json_encode($objects) . ' -- ');
            if (isset($objects) && count($objects) > 0) {
                // \Application\Controller\Log::getInstance()->AddRow(' --> UNIQUEPK FOUND' . $pkStr . ' -- ' . json_encode( $objects[0]) . ' -- ');
                return $objects[0]->getIdAsString();
            }
        }
        return true;
    }

    /**
     * Returns a reference instance list of format list - reference type
     *
     * @param type $typeRef            
     * @return List Model instance <\Application\Document\....>
     */
    public function getReferenceOnPK($type, $pk)
    {
        $references = array();
        foreach ($this->{$type} as $key => $value) {
            $typeC = ucfirst(substr($type, 0, strlen($type) - 1));
            $object = $this->getInstance($typeC, $value['$id']);
            if ($object->{$object->getPK()} == $pk) {
                return $object;
            }
        }
        return null;
    }

    /**
     * Returns a reference instance of format list ["name","ref"]
     *
     * @param MongoRef $idRef            
     * @return Model instance <\Application\Document\....>
     */
    public function getReferenceInstance($idRef)
    {
        return $this->getInstance($idRef[0], $idRef[1]);
    }

    /**
     * returns the instance of this id and type
     *
     * @param
     *            reference type $typeC
     * @param
     *            object key $id
     * @return \Application\Document\'ModelType'
     */
    public function getInstance($typeC, $id)
    {
        $name = \strtolower($typeC) . 's';
        $laf = new \Application\Controller\MongoObjectFactory();
        $classRef = $typeC;
        $Object = $laf->findObject($classRef, $id);
        return $Object;
    }

    public function getOneInstanceCriteria($typeC, $criteria)
    {
        $name = \strtolower($typeC) . 's';
        foreach ($this->{$name} as $id) {
            $laf = new \Application\Controller\MongoObjectFactory();
            $classRef = $typeC;
            $ncriteria = array(
                $criteria[0] => $criteria[1]
            );
            $ncriteria['_id'] = new \MongoId($id['$id']);
            $listObj = $laf->findObjectByCriteria($classRef, $ncriteria);
            // \Application\Controller\Log::getInstance()->AddRow(' --> OBJ ' . $typeC . ' -- ' . json_encode($listObj) . ' -- ' . json_encode($ncriteria));
            
            if (! is_null($listObj)) {
                return $listObj;
            }
            // $obj = $this->getInstance($typeC, $id);
        }
        return null;
    }

    public function getInstanceCriteria($typeC, $criteria)
    {
        $listObjects = array();
        $name = \strtolower($typeC) . 's';
        foreach ($this->{$name} as $id) {
            $laf = new \Application\Controller\MongoObjectFactory();
            $classRef = $typeC;
            // $ncriteria = array(
            // $criteria[0] => $criteria[1]
            // );
            if (isset($criteria[0])) {
                $ncriteria = $laf->createCriteriaFromArray($criteria);
            }
            $ncriteria['_id'] = new \MongoId($id['$id']);
            $listObj = $laf->findObjectByCriteria($classRef, $ncriteria);
            if (! is_null($listObj)) {
                $listObjects[] = $listObj;
            }
            // $obj = $this->getInstance($typeC, $id);
            // \Application\Controller\Log::getInstance()->AddRow(' --> OBJ ' . $classRef . ' -- ' . json_encode($ncriteria) . ' -- ' . json_encode($listObjects));
        }
        return $listObjects;
    }

    public function getJSONCriteria($typeC, $criteria)
    {
        $listObjects = array();
        $name = \strtolower($typeC) . 's';
        foreach ($this->{$name} as $id) {
            $laf = new \Application\Controller\MongoObjectFactory();
            $classRef = $typeC;
            // $ncriteria = array(
            // $criteria[0] => $criteria[1]
            // );
            if (isset($criteria[0])) {
                $ncriteria = $laf->createCriteriaFromArray($criteria);
            }
            $ncriteria['_id'] = new \MongoId($id['$id']);
            $listObjects[] = $laf->findObjectByCriteria($classRef, $ncriteria);
            // \Application\Controller\Log::getInstance()->AddRow(' --> OBJ ' . $classRef . ' -- ' . json_encode($ncriteria) . ' -- ' . json_encode($listObjects));
        }
        return $listObjects;
    }

    public function getInstancesCriteria($typeC, $criteria)
    {
        $listObjects = array();
        $name = \strtolower($typeC) . 's';
        foreach ($this->{$name} as $id) {
            $laf = new \Application\Controller\MongoObjectFactory();
            $classRef = $typeC;
            $ncriteria = array();
            // if (isset($criteria[0])) {
            // $ncriteria = array(
            // $criteria[0] => $criteria[1]
            // );
            // / }
            if (isset($criteria[0])) {
                $ncriteria = $laf->createCriteriaFromArray($criteria);
            }
            \Application\Controller\Log::getInstance()->AddRow(' --> OBJ 1 ' . $classRef . ' -- ' . json_encode($ncriteria) . ' -- ' . json_encode($listObjects));
            $newId = 0;
            if (is_array($id)) {
                $newId = $id['$id'];
            } else {
                $newId = $id;
            }
            $ncriteria['_id'] = new \MongoId($newId);
            $listObjects = array_merge($listObjects, $laf->findObjectInstanceCriteria($classRef, $newId, $ncriteria));
            \Application\Controller\Log::getInstance()->AddRow(' --> OBJ 2 ' . $classRef . ' -- ' . json_encode($ncriteria) . ' -- ' . json_encode($listObjects));
        }
        return $listObjects;
    }

    public function executeCmdByCriteria($typeC, $criteria, $type, $strKeyTotal, $search = "")
    {
        $listObjects = array();
        $classRef = $typeC;
        $name = \strtolower($typeC) . 's';
        // fake ID so empty is triggerd
        $newId = "500000000000000002000000";
        $laf = new \Application\Controller\MongoObjectFactory();
        if (isset($criteria[0])) {
            $ncriteria = $laf->createCriteriaFromArray($criteria);
        }
        if (! isset($ncriteria['_id'])) {
            $in = array();
            $in['$in'][] = new \MongoId($newId);
            foreach ($this->{$name} as $id) {
                $newId = "";
                if (is_array($id)) {
                    $newId = $id['$id'];
                } else {
                    $newId = $id;
                }
                $in['$in'][] = new \MongoId($newId);
            }
            $ncriteria['_id'] = $in;
        }
        $ncriteria['deleted'] = 0;
        
        return $laf->executeCmdByCriteria($classRef, $ncriteria, $type, $strKeyTotal, $search);
    }

    public function countQuickInstancesCriteria($typeC, $criteria, $search = '', $index = 0, $offset = 0)
    {
        $listObjects = array();
        $classRef = $typeC;
        $name = \strtolower($typeC) . 's';
        // fake ID so empty is triggerd
        $newId = "500000000000000002000000";
        $laf = new \Application\Controller\MongoObjectFactory();
        if (isset($criteria[0])) {
            $ncriteria = $laf->createCriteriaFromArray($criteria);
        }
        // \Application\Controller\Log::getInstance()->AddRow(' -->CRIT ' . json_encode($ncriteria) . ' -- -'. json_encode($criteria));
        
        $in = array();
        $in['$in'][] = new \MongoId($newId);
        if (isset($this->{$name})) {
            if (! isset($ncriteria['_id'])) {
                
                foreach ($this->{$name} as $id) {
                    $newId = "";
                    if (is_array($id)) {
                        $newId = $id['$id'];
                    } else {
                        $newId = $id;
                    }
                    $in['$in'][] = new \MongoId($newId);
                }
            }
        }
        $ncriteria['_id'] = $in;
        $ncriteria['deleted'] = 0;
        \Application\Controller\Log::getInstance()->AddRow(' -->CRIT ' . json_encode($ncriteria) . ' -- -' . json_encode($criteria));
        return $laf->countInstancesByCriteria($classRef, $ncriteria, $search, $index, $offset);
    }

    public function getQuickInstancesCriteria($typeC, $criteria, $references = null, $index = 0, $offset = 0, $search = '', $sort = '')
    {
        $listObjects = array();
        $classRef = $typeC;
        $name = \strtolower($typeC) . 's';
        
        $laf = new \Application\Controller\MongoObjectFactory();
        // fake ID so empty is triggerd
        $newId = "500000000000000002000000";
        
        if (isset($criteria) && count($criteria) > 0) {
            
            $ncriteria = $laf->createCriteriaFromArray($criteria);
        }
        if (property_exists($this, $name)) {
            if (! isset($ncriteria['_id'])) {
                $in = array();
                $in['$in'][] = new \MongoId($newId);
                if (isset($this->{$name})) {
                    foreach ($this->{$name} as $id) {
                        $newId = "";
                        if (is_array($id)) {
                            $newId = $id['$id'];
                        } else {
                            $newId = $id;
                        }
                        $in['$in'][] = new \MongoId($newId);
                    }
                }
                $ncriteria['_id'] = $in;
            }
        }
        $ncriteria['deleted'] = 0;
        // \Application\Controller\Log::getInstance()->AddRow(' RESULTget3 ' . json_encode($ncriteria) . $typeC);
        
        return $laf->findInstancesByCriteria($classRef, $ncriteria, $references, $index, $offset, $search, $sort);
    }

    public function getTypeIds($typeC, $criteria)
    {
        $listObjects = array();
        $classRef = $typeC;
        $name = \strtolower($typeC) . 's';
        $laf = new \Application\Controller\MongoObjectFactory();
        $in = array();
        $ncriteria = [];
        \Application\Controller\Log::getInstance()->AddRow(' getTypeIds ' . ' value ' . json_encode($criteria));
        if (isset($criteria) && count($criteria) > 0) {
            $ncriteria = $laf->createCriteriaFromArray($criteria);
        }
        \Application\Controller\Log::getInstance()->AddRow(' getTypeIds ' . ' value ' . json_encode($name));
        \Application\Controller\Log::getInstance()->AddRow(' getTypeIds ' . ' value ' . json_encode($ncriteria));
        if (property_exists($this, $name)) {
            \Application\Controller\Log::getInstance()->AddRow(' getTypeIds11' . ' value ' . json_encode($name));
            if (! isset($ncriteria['_id'])) {
                \Application\Controller\Log::getInstance()->AddRow(' getTypeIds12' . ' value ' . json_encode($name));
                if (isset($this->{$name})) {
                    \Application\Controller\Log::getInstance()->AddRow(' getTypeIds13' . ' value ' . json_encode($name));
                    foreach ($this->{$name} as $id) {
                        $newId = "";
                        if (is_array($id)) {
                            $newId = $id['$id'];
                        } else {
                            $newId = $id;
                        }
                        $in[] = new \MongoId($newId);
                    }
                }
            } else {
                \Application\Controller\Log::getInstance()->AddRow(' getTypeIds1 ' . ' value ' . json_encode($ncriteria));
                if (is_array($ncriteria['_id'])) {
                    $in = $ncriteria['_id'];
                } else {
                    $in[] = $ncriteria['_id'];
                }
            }
            return $in;
        }
        return $in;
    }

    public function load($data)
    {
        if (is_null($data)) {
            $data = array();
        }
        
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $retVal = $this->getRelationType($key);
                // \Application\Controller\Log::getInstance()->AddRow(' TESTING ' . $key . ' value ' . json_encode($this->getRelationType($key)) . " == " . count($this->getRelationType($key)));
                if (((is_array($retVal) && (count($retVal) > 0)) || (is_string($retVal) && strlen($retVal) > 0))) {
                    
                    // if ($key == 'organizations' && isset( $value['$ref'])) {
                    if ($this->getRelationType($key) == Model::ODM) {
                        // \Application\Controller\Log::getInstance()->AddRow(' Line ' . $key . ' value ' . json_encode($value));
                        if (isset($value[0])) {
                            $reference = array();
                            $reference['$ref'] = $value[0]['$ref'];
                            $reference['$id'] = new \MongoID($value[0]['$id']['$id']);
                            $reference['$db'] = 'zf2odm';
                            $this->{$key} = $reference;
                        } else {
                            
                            $reference = array();
                            if (isset($value['$ref'])) {
                                $reference['$ref'] = $value['$ref'];
                                if ($value['$id'] instanceof \MongoID) {
                                    $reference['$id'] = $value['$id'];
                                } else {
                                    $reference['$id'] = new \MongoID($value['$id']['$id']);
                                }
                                $reference['$db'] = 'zf2odm';
                            }
                            $this->{$key} = $reference;
                        }
                    } else if ($this->getRelationType($key) == Model::ODM_OWNING) {
                        $this->{$key} = array();
                        
                        foreach ($value as $keyD => $valueD) {
                            $reference = array();
                            
                            $reference['$ref'] = $valueD['$ref'];
                            if ($valueD['$id'] instanceof \MongoID) {
                                $reference['$id'] = $valueD['$id'];
                            } else if (isset($valueD['$id']['$id'])) {
                                $reference['$id'] = new \MongoID($valueD['$id']['$id']);
                            } else {
                                $reference['$id'] = new \MongoID($valueD['$id']);
                            }
                            $laf = new \Application\Controller\MongoObjectFactory();
                            $reference['$db'] = $laf->getDBName($this->getClassNameFromTable($valueD['$ref']));
                            $this->{$key}[] = $reference;
                        }
                    } else {
                        if (isset($value['usec']) && $value['usec'] > 0) {
                            $valueX = date("d-m-Y H:i", $value["sec"]);
                            $this->{$key} = $valueX;
                        } else if (isset($value['sec'])) {
                            
                            $valueX = date("d-m-Y", $value["sec"]);
                            $this->{$key} = $valueX;
                        } else {
                            $this->{$key} = array();
                            foreach ($value as $keyD => $valueD) {
                                
                                $reference = array();
                                $reference['$ref'] = $valueD['$ref'];
                                $reference['$id'] = $valueD['$id'];
                                // $reference['$text'] = 'text';
                                $this->{$key}[] = $reference;
                            }
                        }
                    }
                } else {
                    // \Application\Controller\Log::getInstance()->AddRow(' Line ' . $key . ' value ' . json_encode($value));
                    if (isset($value['usec']) && $value['usec'] > 0) {
                        // \Application\Controller\Log::getInstance()->AddRow(' DATESSS ' . $key . ' value ' . json_encode($value));
                        $valueX = date("d-m-Y H:i", $value["sec"]);
                        $this->{$key} = $valueX;
                    } else if (isset($value['sec'])) {
                        $valueX = date("d-m-Y", $value["sec"]);
                        $this->{$key} = $valueX;
                    } else {
                        $this->{$key} = $value;
                    }
                }
            } else {
                
                $this->{$key} = $value;
            }
        }
        // \Application\Controller\Log::getInstance()->AddRow(' Line UPDATE value1 ' . json_encode($this));
    }

    public function addReferenceObject($key, $value)
    {
        $mongoObjectFactory = new MongoObjectFactory();
        // save state of this before changing it
        
        \Application\Controller\Log::getInstance()->AddRow(' ADDINGX ' . json_encode($value) . ' value ' . json_encode($this) . ' relation -' . $key);
        if (! is_null($this) && isset($this->_id) && ! is_null($this->_id)) {
            // \Application\Controller\Log::getInstance()->AddRow(' ADDINGX ' . $key . ' value ' . json_encode($value) . ' relation -' . json_encode($this->{$key}));
            // if()
            // if not a relation already
            if (is_null($this->{$key}) || (is_array($this->{$key}) && ! array_search($value['$id'], $this->{$key}))) {
                $retVal = $this->getRelationType($key);
                if (((is_array($retVal) && (count($retVal) > 0)) || (is_string($retVal) && strlen($retVal) > 0))) {
                    $reference = array();
                    
                    // \Application\Controller\Log::getInstance()->AddRow(' ADDING ' . $key . ' value ' . json_encode($this) . ' relation -' . $this->getRelationType($key));
                    if (is_array($value)) {
                        if (isset($_SESSION['transaction_id'])) {
                            $sData = new \Application\Document\StateData();
                            $sData->setObjectData($this);
                            $sData->setType(\Application\Document\StateData::UPDATE);
                            $sData->setObjecttype($this->getClassName());
                            $sData->setObjectid((string) $this->_id);
                            $mongoObjectFactory->saveStateData($_SESSION['transaction_id'], $sData);
                        }
                        // if ($this->getRelationType($key) == Model::ONE_TO_ONE) {
                        // todo - check if an update
                        // if $this->{$key}[] is not null then must do something
                        // one-to-one is bidirectional
                        // update remote object also!
                        // find object and update relation
                        $reference['$ref'] = $key;
                        $reference['$id'] = $value['$id'];
                        if (isset($value['text'])) {
                            $reference['$text'] = $value['$text'];
                        }
                        $this->{$key}[] = $reference;
                        if ($this->_id instanceof \MongoId) {
                            $newId = (string) $this->_id;
                            $this->_id = array();
                            $this->_id['$id'] = $newId;
                        }
                        $io = (string) $this->_id['$id'];
                        if ($io != "") {
                            $this->_id = new \MongoId($io);
                        }
                        // @TODO
                        $refType = ucfirst(substr($key, 0, strlen($key) - 1));
                        $laf = new \Application\Controller\MongoObjectFactory();
                        $referenceObject = $laf->findObject($refType, $value['$id']);
                        
                        $nameOfThisClass = $this->getTableName();
                        // uni dir of bi dir?
                        if (isset($referenceObject->{$nameOfThisClass})) {
                            $listOfRef = $referenceObject->{$nameOfThisClass};
                            $referenceTag = array();
                            
                            $referenceTag['$ref'] = $nameOfThisClass;
                            $referenceTag['$id'] = (string) $this->_id;
                            // reset array - IS ONE TO ONE
                            // $referenceObject->{$nameOfThisClass} = array();
                            $referenceObject->{$nameOfThisClass}[] = $referenceTag;
                        }
                        // update container instance
                        $m = Database::getInstance();
                        $db = $m->{$laf->getDBName()};
                        $nameTable = $referenceObject->getTableName();
                        $collection = $db->$nameTable;
                        
                        $refId = (string) $referenceObject->_id['$id'];
                        $referenceObject->_id = new \MongoId($refId);
                        
                        $state["data"][] = $referenceObject;
                        // save state of this before changing it
                        if (isset($_SESSION['transaction_id'])) {
                            $sData = new \Application\Document\StateData();
                            $sData->setObjectData($referenceObject);
                            $sData->setType(\Application\Document\StateData::UPDATE);
                            $sData->setObjecttype($referenceObject->getClassName());
                            $sData->setObjectid((string) $referenceObject->_id);
                            $mongoObjectFactory->saveStateData($_SESSION['transaction_id'], $sData);
                        }
                        
                        // \Application\Controller\Log::getInstance()->AddRow(' ADDINGnn ' . $key . ' value ' . json_encode($referenceObject));
                        // \Application\Controller\Log::getInstance()->AddRow(' ADDINGnn ' . $key . ' value ' . json_encode($this));
                        $mongoObjectFactory->updateObject(new \MongoId($refId), $nameTable, $referenceObject);
                        $mongoObjectFactory->updateObject($this->_id, $this->getTableName(), $this);
                        $this->reload();
                        if (isset($_SESSION["transaction_type"]) && $_SESSION["transaction_type"] != "method") {
                            \Application\Controller\Log::getInstance()->AddRow(' PROPAGATTT ' . $key . ' value ' . json_encode($this));
                            $referenceObject->reload();
                            $this->propagate();
                            $referenceObject->propagate();
                        } else {
                            $this->propagate();
                        }
                        
                        // \Application\Controller\Log::getInstance()->AddRow(' ADDINGnn1 ' . $key . ' value ' . json_encode($this));
                    }
                    // }
                }
            }
        }
    }

    public function addRemoteReferenceObject($objectRef)
    {
        $reference = array();
        $reference['$ref'] = $objectRef->getTableName();
        $reference['$id'] = (string) $objectRef->getIdAsString();
        $this->addMasterReferenceObject($objectRef->getTableName(), $reference);
        // \Application\Controller\Log::getInstance()->AddRow(' ADDING3 ' . ' value ' . json_encode($this));
        $objectRef->reload();
        $this->reload();
        // \Application\Controller\Log::getInstance()->AddRow(' ADDING4 ' . ' value ' . json_encode($this));
    }

    public function addMasterReferenceObject($key, $value)
    {
        \Application\Controller\Log::getInstance()->AddRow(' ADDINGX ' . json_encode($value) . ' value ' . json_encode($this) . ' relation -' . $key);
        if (! is_null($this) && ! is_null($this->_id)) {
            
            $retVal = $this->getRelationType($key);
            // \Application\Controller\Log::getInstance()->AddRow(' TESTING ' . $key . ' value ' . json_encode($this->getRelationType($key)) . " == " . count($this->getRelationType($key)));
            if (((is_array($retVal) && (count($retVal) > 0)) || (is_string($retVal) && strlen($retVal) > 0))) {
                // $this->{$key} = array();
                $reference = array();
                \Application\Controller\Log::getInstance()->AddRow(' Owning 1 ' . $key . ' value ' . json_encode($this) . ' relation -' . $this->getRelationType($key));
                if (is_array($value)) {
                    if ($this->getRelationType($key) == Model::ONE_TO_ONE || $this->getRelationType($key) == Model::MANY_TO_ONE) {
                        
                        $nameOfThisClass = $this->getTableName();
                        $listOfRef = $this->{$key};
                        $refSearch = $this->searchReference($listOfRef, $value['$id']);
                        \Application\Controller\Log::getInstance()->AddRow(' ONE-T-ONE ' . $key . ' value ' . json_encode($this) . ' relation -' . json_encode($listOfRef) . "-" . $refSearch);
                        // if we have a new relation
                        // remove the existing one
                        if ($refSearch != - 1) {
                            return true;
                        }
                        if (! is_null($listOfRef)) {
                            if (count($listOfRef) > 0) {
                                foreach ($listOfRef as $valueD) {
                                    \Application\Controller\Log::getInstance()->AddRow(' ONE-T-ONE1 KEY DOES NOT EXIST - remove link ' . $valueD['$id'] . ' value ' . json_encode($valueD) . ' relation -' . $this->getRelationType($key));
                                    $this->removeAndUpdateReference($nameOfThisClass, $key, $valueD['$id']);
                                }
                                // \Application\Controller\Log::getInstance()->AddRow(' KEY EXIST - remove link ' . $key . ' value ' . json_encode($this->_id) . ' relation -' . json_encode($this->{$key}));
                                // $this->removeAndUpdateReference($nameOfThisClass, $key, $value['id']);
                            }
                            // \Application\Controller\Log::getInstance()->AddRow(' ARAY1 --->>> value ' . json_encode($this));
                            
                            $valueRef = array();
                            $valueRef['$ref'] = $key;
                            $valueRef['$id'] = $value['$id'];
                            
                            $this->addReferenceObject($key, $valueRef);
                            \Application\Controller\Log::getInstance()->AddRow(' ONE-T-ONE2 ARAY2 --->>> value ' . json_encode($this));
                        } else if (is_null($listOfRef)) {
                            $valueRef = array();
                            $valueRef['$ref'] = $key;
                            $valueRef['$id'] = $value['$id'];
                            
                            \Application\Controller\Log::getInstance()->AddRow(' ONE-T-ONE2 ARAY2 x--->>> value ' . json_encode($this));
                            $this->addReferenceObject($key, $valueRef);
                        }
                    } else  // add it now
if ($this->getRelationType($key) == Model::ONE_TO_MANY) {
                        $nameOfThisClass = $this->getTableName();
                        $listOfRef = $this->{$key};
                        $refSearch = $this->searchReference($listOfRef, $value['$id']);
                        // $refSearch1 = $this->searchReference($nameOfThisClass, $this->getIdAsString());
                        \Application\Controller\Log::getInstance()->AddRow(' ONE-T-MANY ' . $key . ' value ' . json_encode($this) . ' relation -' . json_encode($listOfRef) . "-" . $refSearch . " = ");
                        
                        if ($refSearch != - 1) { // || $refSearch1 != - 1) {
                            return true;
                        }
                        // REMOVE ALL AND ADD ALL TODO optimize
                        // \Application\Controller\Log::getInstance()->AddRow(' ONE TO MANY ' . $key . ' value ' . json_encode($value) . ' relation -' . json_encode($listOfRef));
                        if (isset($listOfRef) && is_array($listOfRef)) {
                            // foreach ($listOfRef as $valueD) {
                            // \Application\Controller\Log::getInstance()->AddRow(' KEY DOES NOT EXIST - remove link ' . $valueD['$id'] . ' value ' . json_encode($valueD) . ' relation -' . $this->getRelationType($key));
                            $this->removeAndUpdateReference($nameOfThisClass, $key, $value['$id']);
                            // }
                        }
                        
                        $nameOfThisClass = $this->getTableName();
                        // \Application\Controller\Log::getInstance()->AddRow(' ONE TO MANY ll --->>> ' . $valueR['_id']['$id'] . ' value ' . json_encode($valueR));
                        
                        // if ($keyR == $nameOfThisClass) {
                        // if ($this->searchReference($listOfRef, $valueR['_id']['$id']) < 0) {
                        // \Application\Controller\Log::getInstance()->AddRow(' KEY DOES NOT EXIST - add link ' . $key . ' value ' . json_encode($valueR) . ' relation -' . $this->getRelationType($key));
                        $valueRef = array();
                        $valueRef['$ref'] = $key;
                        $valueRef['$id'] = $value['$id'];
                        // $valueRef['$text'] = $value['text'];
                        $this->addReferenceObject($key, $valueRef);
                        // \Application\Controller\Log::getInstance()->AddRow(' ADDING4 ' . $key . ' value ' . json_encode($this));
                        // }
                        // }
                    }
                }
            }
        }
        $this->reload();
    }

    public function addRelationReferenceObject($key, $value, $keyRef, $relTypeRef)
    {
        // \Application\Controller\Log::getInstance()->AddRow(' ADDING NEXT/PREV ' . json_encode($value) . ' value ' . json_encode($this) . ' relation -' . $key);
        $value = $this->convertIdToString($value);
        if (! is_null($this) && ! is_null($this->_id)) {
            // \Application\Controller\Log::getInstance()->AddRow(' ADDINGX ' . $key . ' value ' . json_encode($value) . ' relation -' . json_encode($this->{$key}));
            // if()
            // if not a relation already
            if (is_null($this->{$key}) || (is_array($this->{$key}) && ! array_search($value, $this->{$key}))) {
                // if (count($this->getRelationType($key)) > 0) {
                $reference = array();
                // \Application\Controller\Log::getInstance()->AddRow(' ADDING 7 value ' . json_encode($this) . ' relation -' );
                if (isset($value)) {
                    // if ($this->getRelationType($key) == Model::ONE_TO_ONE) {
                    // todo - check if an update
                    // if $this->{$key}[] is not null then must do something
                    // one-to-one is bidirectional
                    // update remote object also!
                    // find object and update relation
                    $reference['$ref'] = $relTypeRef;
                    $reference['$id'] = $value;
                    if (isset($value['text'])) {
                        $reference['$text'] = $value['$text'];
                    }
                    $this->{$key} = array();
                    $this->{$key}[] = $reference;
                    if ($this->_id instanceof \MongoId) {
                        $newId = (string) $this->_id;
                        $this->_id = array();
                        $this->_id['$id'] = $newId;
                    }
                    $io = (string) $this->_id['$id'];
                    if ($io != "") {
                        $this->_id = new \MongoId($io);
                    }
                    // @TODO
                    $refType = ucfirst(substr($relTypeRef, 0, strlen($relTypeRef) - 1));
                    $laf = new \Application\Controller\MongoObjectFactory();
                    $referenceObject = $laf->findObject($refType, $value);
                    $nameOfThisClass = $keyRef; // $this->getTableName();
                    $referenceTag = array();
                    $referenceTag['$ref'] = $this->getTableName();
                    $referenceTag['$id'] = (string) $this->_id;
                    // reset array - IS ONE TO ONE
                    // $referenceObject->{$nameOfThisClass} = array();
                    $referenceObject->{$nameOfThisClass} = array();
                    $referenceObject->{$nameOfThisClass}[] = $referenceTag;
                    // update container instance
                    $m = Database::getInstance();
                    $db = $m->{$laf->getDBName()};
                    $nameTable = $referenceObject->getTableName();
                    $refId = (string) $referenceObject->_id['$id'];
                    $referenceObject->_id = new \MongoId($refId);
                    $this->updateObject(new \MongoId($refId), $nameTable, $referenceObject);
                    
                    $this->updateObject($this->_id, $this->getTableName(), $this);
                }
                // }
                // }
            }
        }
    }

    public function removeReference($fromKey, $toKey, $value, $noPropagation = false)
    {
        if ($this->_id instanceof \MongoId) {} else {
            $this->_id = new \MongoId($this->_id['$id']);
        }
        $refId = (string) $this->_id;
        $laf = new \Application\Controller\MongoObjectFactory();
        // \Application\Controller\Log::getInstance()->AddRow(' ARAY --->>> 9 '. json_encode($this->{$toKey}).' value ' . $value);
        
        if ($this->searchReference($this->{$toKey}, $value) >= 0) {
            // first remove the relation from old key
            $refType = ucfirst(substr($toKey, 0, strlen($toKey) - 1));
            $referenceOldObject = $laf->findObject($refType, $value);
            if (isset($referenceOldObject->id)) {
                // remove all refences
                if ($referenceOldObject->searchReference($referenceOldObject->{$fromKey}, $refId) >= 0) {
                    // \Application\Controller\Log::getInstance()->AddRow(' UNSET --->>> 9 '. $fromKey.' value ' . $value);
                    
                    // if (count($referenceOldObject->{$fromKey}) == 1) {
                    // $referenceOldObject->{$fromKey} = array();
                    // } else {
                    unset($referenceOldObject->{$fromKey}[$referenceOldObject->searchReference($referenceOldObject->{$fromKey}, $refId)]);
                    // }
                }
                
                if ($this->searchReference($this->{$toKey}, $value) >= 0) {
                    // \Application\Controller\Log::getInstance()->AddRow(' ARAY --->>> 55 '. $fromKey.' value ' . $value);
                    
                    // remove local reference also
                    // if (count($this->{$toKey}) == 1) {
                    // $this->{$toKey} = array();
                    // } else {
                    unset($this->{$toKey}[$this->searchReference($this->{$toKey}, $value)]);
                    // }
                }
            }
            $referenceOldObject = $laf->findObject($refType, $value);
            // if (isset($_SESSION["transaction_type"]) && $_SESSION["transaction_type"] != "method") {
            if ($noPropagation = false) {
                $referenceOldObject->propagate();
            }
            // }
        }
    }

    public function removeAndUpdateReference($fromKey, $toKey, $value, $noPropagation = false)
    {
        if ($this->_id instanceof \MongoId) {} else {
            $this->_id = new \MongoId($this->_id['$id']);
        }
        $refId = (string) $this->_id;
        $laf = new \Application\Controller\MongoObjectFactory();
        // \Application\Controller\Log::getInstance()->AddRow(' ARAY --->>> 9 '. json_encode($this->{$toKey}).' value ' . $value);
        
        if ($this->searchReference($this->{$toKey}, $value) >= 0) {
            // first remove the relation from old key
            $refType = ucfirst(substr($toKey, 0, strlen($toKey) - 1));
            $referenceOldObject = $laf->findObject($refType, $value);
            if (isset($referenceOldObject->id)) {
                // remove all refences
                if ($referenceOldObject->searchReference($referenceOldObject->{$fromKey}, $refId) >= 0) {
                    // \Application\Controller\Log::getInstance()->AddRow(' UNSET --->>> 9 '. $fromKey.' value ' . $value);
                    
                    // if (count($referenceOldObject->{$fromKey}) == 1) {
                    // $referenceOldObject->{$fromKey} = array();
                    // } else {
                    unset($referenceOldObject->{$fromKey}[$referenceOldObject->searchReference($referenceOldObject->{$fromKey}, $refId)]);
                    // }
                    $refId = (string) $referenceOldObject->_id['$id'];
                    $referenceOldObject->_id = new \MongoId($refId);
                    $this->updateObject($referenceOldObject->_id, $referenceOldObject->getTableName(), $referenceOldObject);
                }
                
                if ($this->searchReference($this->{$toKey}, $value) >= 0) {
                    // \Application\Controller\Log::getInstance()->AddRow(' ARAY --->>> 55 '. $fromKey.' value ' . $value);
                    
                    // remove local reference also
                    // if (count($this->{$toKey}) == 1) {
                    // $this->{$toKey} = array();
                    // } else {
                    unset($this->{$toKey}[$this->searchReference($this->{$toKey}, $value)]);
                    // }
                    $referenceOldObject->_id = new \MongoId($refId);
                    $this->updateObject($this->_id, $this->getTableName(), $this);
                }
            }
            $referenceOldObject = $laf->findObject($refType, $value);
            // if (isset($_SESSION["transaction_type"]) && $_SESSION["transaction_type"] != "method") {
            if ($noPropagation = false) {
                $referenceOldObject->propagate();
            }
            // }
        }
    }

    /**
     * *
     *
     * @param unknown $fromKey
     *            - from 'events'
     * @param unknown $toKey
     *            -'calendardays' -this has events
     * @param unknown $value
     *            - PRIMARY KEY for toKey -->'calendarday' - object id has already a relation with this one
     */
    public function removeSimpleReference($fromKey, $toKey, $value)
    {
        $refType = ucfirst(substr($toKey, 0, strlen($toKey) - 1));
        if ($this->_id instanceof \MongoId) {} else {
            $this->_id = new \MongoId($this->_id['$id']);
        }
        $refId = (string) $this->_id;
        $laf = new \Application\Controller\MongoObjectFactory();
        $referenceOldObject = $laf->findInstanceByPK($laf->getClassPath($refType) . $refType, $value);
        // remove all refences
        if ($referenceOldObject->searchReference($referenceOldObject->{$fromKey}, $refId) >= 0) {
            unset($referenceOldObject->{$fromKey}[$referenceOldObject->searchReference($referenceOldObject->{$fromKey}, $refId)]);
            $refId = (string) $referenceOldObject->_id['$id'];
            $referenceOldObject->_id = new \MongoId($refId);
            $this->updateObject($referenceOldObject->_id, $referenceOldObject->getTableName(), $referenceOldObject);
        }
    }

    public function addSimpleReference($toKey, $value)
    {
        $refType = ucfirst(substr($toKey, 0, strlen($toKey) - 1));
        if ($this->_id instanceof \MongoId) {} else {
            $this->_id = new \MongoId($this->_id['$id']);
        }
        $refId = (string) $this->_id;
        $laf = new \Application\Controller\MongoObjectFactory();
        // $referenceObject = $laf->findInstanceByPK($laf->getClassPath($refType) . $refType, $value);
        // remove all refences
        if ($this->searchReference($this->{$toKey}, $refId) < 0) {
            $referenceTag = array();
            $referenceTag['$ref'] = $toKey;
            $referenceTag['$id'] = $value;
            $this->{$toKey}[] = $referenceTag;
            $this->updateObject($this->getIdAsString(), $this->getTableName(), $this);
        }
    }

    public function searchReference($arrayRef, $value)
    {
        // \Application\Controller\Log::getInstance()->AddRow(' SEARCH REF ' . json_encode($arrayRef) . ' value ' . json_encode($value));
        if (isset($arrayRef)) {} else if (sizeof($arrayRef) == 0) {
            return - 1;
        }
        foreach ($arrayRef as $keyR => $valueR) {
            if (is_array($valueR)) {
                if (array_search($value, $valueR)) {
                    return $keyR;
                }
            }
        }
        return - 1;
    }

    public function set($data)
    {
        if (is_null($data)) {
            $data = array();
        }
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $retVal = $this->getRelationType($key);
                if (((is_array($retVal) && (count($retVal) > 0)) || (is_string($retVal) && strlen($retVal) > 0))) {
                    
                    $this->{$key} = array();
                    $reference = array();
                    // \Application\Controller\Log::getInstance()->AddRow(' Owning XX ' . $key . ' value ' . json_encode($value));
                    if (isset($value) && count($value) > 0) {
                        if ($this->getRelationType($key) == Model::ONE_TO_MANY) {
                            foreach ($value as $valueD) {
                                // \Application\Controller\Log::getInstance()->AddRow(' Owning XYY ' . $key . ' value ' . json_encode($valueD));
                                
                                $reference = array();
                                $reference['$ref'] = $key;
                                // TODO check to see if relations work!!
                                if (isset($valueD['$id'])) {
                                    $reference['$id'] = $valueD['$id'];
                                } else {
                                    $reference['$id'] = $valueD['_id']['$id'];
                                }
                                // $reference['$id'] = new \MongoId($value['$id']['$id']);
                                $this->{$key}[] = $reference;
                            }
                        } else {
                            $reference['$ref'] = $key;
                            // TODO check to see if relations work!!
                            if (isset($value['$id'])) {
                                $reference['$id'] = $value['$id'];
                            } else if (isset($valueD['$id'])) {
                                $reference['$id'] = $valueD['$id'];
                            } else if (isset($valueD['_id']['$id'])) {
                                $reference['$id'] = $valueD['_id']['$id'];
                            } else {
                                if (isset($value['_id'])) {
                                    $reference['$id'] = $value['_id']['$id'];
                                } else {
                                    if (isset($value[0]) && isset($value[0]['_id'])) {
                                        // \Application\Controller\Log::getInstance()->AddRow(' Owning XYY ' . $key . ' value ' . json_encode($value));
                                        $reference['$id'] = $value[0]['_id'];
                                    } else if (isset($value[0]) && isset($value[0]['$id'])) {
                                        // \Application\Controlle\Log::getInstance()->AddRow(' Owning XYY ' . $key . ' value ' . json_encode($value));
                                        $reference['$id'] = $value[0]['$id'];
                                    } else {
                                        // ERROR - we should not be here
                                        \Application\Controller\Log::getInstance()->AddRow(' BIIIIGERRROR ' . $key . ' value ' . json_encode($value));
                                        
                                        $reference['$id'] = 0;
                                    }
                                }
                            }
                            
                            // $reference['$id'] = new \MongoId($value['$id']['$id']);
                            $this->{$key}[] = $reference;
                        }
                    }
                } else {
                    $this->{$key} = $value;
                }
            } else {
                $this->{$key} = $value;
            }
        }
    }

    public function updateSet($data, &$state = null, $forceStopPropagation = false)
    {
        $propagate = false;
        $laf = new MongoObjectFactory();
        $dataNew = array();
        // $user = $this->getActiveUser();
        // \Application\Controller\Log::getInstance()->AddRow(' UPDATE SET value ' . json_encode($data));
        if (is_null($data)) {
            $data = array();
        }
        if (is_array($state)) {
            $state = null;
        }
        
        if (isset($state) && is_object($state)) {
            // $state = new State();
            // $state->setTransactionid("UNKNOWN");
            // \Application\Controller\Log::getInstance()->AddRow(' SAVASTATE SET value ' . json_encode($this));
            
            // save state of this before changing it
            $sData = new StateData();
            $sData->setObjectData($this);
            $sData->setType(StateData::UPDATE);
            $sData->setObjecttype($this->getClassName());
            $sData->setObjectid($this->getIdAsString());
            if (isset($_SESSION["transaction_id"])) {} else {
                $mtime = microtime();
                $mtime = explode('.', $mtime);
                $mtime = $mtime[1] + $mtime[0];
                $tId = "" . $mtime;
                $_SESSION["transaction_id"] = $tId;
            }
            $laf->saveStateData($_SESSION["transaction_id"], $sData);
        }
        foreach ($data as $key => $value) {
            $dataNew[$key] = $this->{$key};
            $retVal = $this->getRelationType($key);
            if (((is_array($retVal) && (count($retVal) > 0)) || (is_string($retVal) && strlen($retVal) > 0))) {
                if ($this->getRelationType($key) == Model::SIMPLE_REF) {
                    $contianerRef = array();
                    $contianerRef = $this->getRelationDetails($key);
                    if ($contianerRef['$ref'] == Model::CALENDAR_REF) {
                        // container calendar
                        // $arraydata["name"] = $contianerRef{'$pk'};
                        if (isset($_SESSION['workspaceId'])) {
                            $workspaceId = $_SESSION['workspaceId'];
                            $criteria = array(
                                'name' => $contianerRef{'$pk'},
                                'parent.$id' => $workspaceId
                            );
                        } else {
                            $criteria = array(
                                'name' => $contianerRef{'$pk'}
                            );
                        }
                        
                        // TODO add Workspace ID as Parent ID
                        $calendar = $laf->findObjectInstanceByCriteria($this->getClassNameFromTable($contianerRef{'$ref'}), $criteria);
                        // \Application\Controller\Log::getInstance()->AddRow(' CALENDAR ' . $key . ' value ' . json_encode($calendar) . ' relation -' );
                        $criteria = array();
                        $criteria[$contianerRef{'$ref_key'}] = $value;
                        // get this calendar from Workspace
                        // get the day object
                        // reset the reference is date changed
                        $criteria = array();
                        $criteria[$contianerRef{'$ref_key'}] = $this->{$key};
                        $nameOfThisClass = $this->getTableName();
                        $calendarday = $calendar->getDay($value);
                        // $calendarday = $calendar->getReferenceOnPK($contianerRef{'$ref_link'}, $this->getFormatVarialble($value));
                        // TODO change to find through container!!
                        $this->resetSimpleReference($calendar, $contianerRef{'$ref_link'}, $value, $nameOfThisClass);
                        // create new ref if
                        $this->createSimpleReference($calendarday, $nameOfThisClass);
                        // \Application\Controller\Log::getInstance()->AddRow(' UPDATE SET value1 ' . json_encode($calendarday) . ' --- ' . $value);
                    }
                }
            }
            if (is_array($value)) {
                \Application\Controller\Log::getInstance()->AddRow(' LineXXX ' . $key . ' value ' . json_encode($value));
                $retVal = $this->getRelationType($key);
                if (((is_array($retVal) && (count($retVal) > 0)) || (is_string($retVal) && strlen($retVal) > 0))) {
                    // $this->{$key} = array();
                    $reference = array();
                    // \Application\Controller\Log::getInstance()->AddRow(' Owning 1 ' . $key . ' value ' . json_encode($this) . ' relation -' . $this->getRelationType($key));
                    if (is_array($value)) {
                        if ($this->getRelationType($key) == Model::ONE_TO_ONE || $this->getRelationType($key) == Model::MANY_TO_ONE) {
                            
                            $nameOfThisClass = $this->getTableName();
                            $listOfRef = $this->{$key};
                            
                            $refSearch = $this->searchReference($listOfRef, $value['id']);
                            // \Application\Controller\Log::getInstance()->AddRow(' ONE-T-ONE ' . $key . ' value ' . json_encode($this) . ' relation -' . json_encode($listOfRef) . "-" . $refSearch);
                            // if we have a new relation
                            // remove the existing one
                            if (! is_null($listOfRef)) {
                                if (count($listOfRef) > 0) {
                                    foreach ($listOfRef as $valueD) {
                                        // \Application\Controller\Log::getInstance()->AddRow(' KEY DOES NOT EXIST - remove link ' . $valueD['$id'] . ' value ' . json_encode($valueD) . ' relation -' . $this->getRelationType($key));
                                        $this->removeAndUpdateReference($nameOfThisClass, $key, $valueD['$id']);
                                    }
                                    \Application\Controller\Log::getInstance()->AddRow(' KEY EXIST - remove link ' . $key . ' value ' . json_encode($this->_id) . ' relation -' . json_encode($this->{$key}));
                                    // $this->removeAndUpdateReference($nameOfThisClass, $key, $value['id']);
                                }
                                \Application\Controller\Log::getInstance()->AddRow(' ARAY1 --->>> value ' . json_encode($this));
                                
                                $valueRef = array();
                                $valueRef['$ref'] = $key;
                                $valueRef['$id'] = $value['id'];
                                
                                $this->addReferenceObject($key, $valueRef);
                                \Application\Controller\Log::getInstance()->AddRow(' ARAY2 --->>> value ' . json_encode($this));
                            } else if (is_null($listOfRef)) {
                                $valueRef = array();
                                $valueRef['$ref'] = $key;
                                $valueRef['$id'] = $value['id'];
                                
                                $this->addReferenceObject($key, $valueRef);
                            }
                        } else  // add it now
if ($this->getRelationType($key) == Model::ONE_TO_MANY) {
                            $nameOfThisClass = $this->getTableName();
                            $listOfRef = $this->{$key};
                            // REMOVE ALL AND ADD ALL TODO optimize
                            // \Application\Controller\Log::getInstance()->AddRow(' ONE TO MANY ' . $key . ' value ' . json_encode($value) . ' relation -' . json_encode($listOfRef));
                            if (isset($listOfRef) && is_array($listOfRef)) {
                                foreach ($listOfRef as $valueD) {
                                    \Application\Controller\Log::getInstance()->AddRow(' KEY DOES NOT EXIST - remove link ' . $valueD['$id'] . ' value ' . json_encode($valueD) . ' relation -' . $this->getRelationType($key));
                                    $this->removeAndUpdateReference($nameOfThisClass, $key, $valueD['$id']);
                                }
                            }
                            foreach ($value as $keyR => $valueR) {
                                
                                $nameOfThisClass = $this->getTableName();
                                // \Application\Controller\Log::getInstance()->AddRow(' ONE TO MANY ll --->>> ' . $valueR['_id']['$id'] . ' value ' . json_encode($valueR));
                                
                                // if ($keyR == $nameOfThisClass) {
                                // if ($this->searchReference($listOfRef, $valueR['_id']['$id']) < 0) {
                                // \Application\Controller\Log::getInstance()->AddRow(' KEY DOES NOT EXIST - add link ' . $key . ' value ' . json_encode($valueR) . ' relation -' . $this->getRelationType($key));
                                $valueRef = array();
                                $valueRef['$ref'] = $key;
                                $valueRef['$id'] = $valueR['_id']['$id'];
                                $valueRef['$text'] = $valueR['text'];
                                $this->addReferenceObject($key, $valueRef);
                                // }
                                // }
                            }
                            
                            // }
                        }
                        
                        // }
                    } else {
                        // \Application\Controller\Log::getInstance()->AddRow(' UPDATE SET value --->>> ' . $value . ' value ' . json_encode($key));
                        // $dataNew[$key] = $this->getFormatVarialble($value, $key);
                        $this->{$key} = $this->getFormatVarialble($value, $key);
                    }
                } else {
                    
                    $this->{$key} = array();
                    
                    // foreach ($value as $keyR => $valueR) {
                    // \Application\Controller\Log::getInstance()->AddRow(' ARAY --->>> ' . $keyR . ' value ' . json_encode($valueR));
                    $this->{$key} = $this->getFormatVarialble($value, $key);
                    ;
                    
                    // }
                }
            } else {
                
                $retVal = $this->getRelationType($key);
                if (((is_array($retVal) && (count($retVal) > 0)) || (is_string($retVal) && strlen($retVal) > 0))) {
                    if ($value == "") {
                        
                        $this->{$key} = null;
                    } else {
                        if ($this->getRelationType($key) == Model::SIMPLE_REF) {
                            // $dataNew[$key] = $this->getFormatVarialble($value, $key);
                            $this->{$key} = $this->getFormatVarialble($value, $key);
                        } else {
                            // $dataNew[$key] = $value;
                            $this->{$key} = $value;
                        }
                        // is result sstop propagation TODO: revise this to continue on different relations
                        // stop cyclic propagation
                        if (! ($this->getRelationType($key) == Model::DECL_RESULT)) {
                            $propagate = true;
                        }
                    }
                } else {
                    // $dataNew[$key] = $this->getFormatVarialble($value, $key);
                    $this->{$key} = $this->getFormatVarialble($value, $key);
                    
                    $propagate = true;
                }
            }
        }
        $this->version = "" . time();
        $laf->save($this);
        $this->reload();
        \Application\Controller\Log::getInstance()->AddRow('UPDATE SET value! ' . json_encode($this));
        if ($this instanceof Indexed) {
            // \Application\Controller\Log::getInstance()->AddRow('INDEXED! ' );
            $sortIndex = '';
            if (isset($contianerRef{'sorted'}) && strlen($contianerRef{'sorted'}) > 0) {
                $sortIndex = $contianerRef{'sorted'};
            } else {
                $contianerRefP = $this->getParent()->getRelationDetails($this->getTableName());
                if (isset($contianerRefP{'sorted'})) {
                    $sortIndex = $contianerRefP{'sorted'};
                }
            }
            // \Application\Controller\Log::getInstance()->AddRow('INDEXED! '.json_encode($this). ' = '.$propagate);
            // $firstObj = $this->getParent()->reindexReference($this->get_class_name($this), $sortIndex);
            // $this->reload();
            $keyVal = $this->get_class_name($this) . '_' . $sortIndex;
            if (isset($state)) {
                $relKey = $state->getRelation($key);
            }
            if (isset($state)) {} else {
                $state = new State();
            }
            if (isset($relKey)) {
                // \Application\Controller\Log::getInstance()->AddRow(' NO-PROPAGATE ' . json_encode($key) . ' - ' . json_encode($state));
            } else {
                if ($propagate) {
                    if ($forceStopPropagation == 3) {
                        // \Application\Controller\Log::getInstance()->AddRow(' PROPAGATEFURTHER ' . json_encode($state) . ' value ' . $keyVal . ' relation -' . json_encode($this));
                        $this->reload();
                        $firstObj = $this->getParent()->reindexReference($this->get_class_name($this), $sortIndex);
                        if (isset($firstObj)) {
                            // \Application\Controller\Log::getInstance()->AddRow(' Propagate!st link value ' . json_encode($firstObj));
                            $firstObj->getPrev()->propagate();
                            $firstObj->reload();
                            // $firstObj->propagate();
                        }
                        $state->addRelation($keyVal);
                        
                        $this->reload();
                        if ($propagate) {
                            if (isset($_SESSION["transaction_type"]) && $_SESSION["transaction_type"] != "method") {
                                $this->propagate($state, [], false, 3, $dataNew);
                                $this->reload();
                            }
                        }
                    } else if ($forceStopPropagation == true) {} else {
                        
                        if (isset($_SESSION["transaction_type"]) && $_SESSION["transaction_type"] != "method") {
                            $this->reload();
                            $firstObj = $this->getParent()->reindexReference($this->get_class_name($this), $sortIndex);
                            $this->reload();
                            
                            $state->addRelation($keyVal);
                            
                            // $firstObj = $this->getParent()->getFirstIndex($this->get_class_name($this));//getParent()->reindexReference($this->get_class_name($this), $sortIndex);
                            if (isset($firstObj)) {
                                // \Application\Controller\Log::getInstance()->AddRow(' Propagate!st link value ' . json_encode($firstObj));
                                $firstObj->getPrev()->propagate($state);
                            }
                            $this->reload();
                            if ($propagate) {
                                $this->propagate($state, [], false, true, $dataNew);
                                $this->reload();
                            }
                        }
                    }
                }
            }
            // $this->updateObject($this->get_id(), $this->getTableName(), $this);
            // $firstObj->propagate();
        } else {
            // $this->updateObject($this->get_id(), $this->getTableName(), $this);
            $this->reload();
            // \Application\Controller\Log::getInstance()->AddRow(' UPDATE_PROPAGATION1 ' . json_encode($this->get_id()).'-----'.$forceStopPropagation);
            if ($forceStopPropagation == true) {} else {
                if ($propagate) {
                    if (isset($_SESSION["transaction_type"]) && $_SESSION["transaction_type"] != "method") {
                        $this->propagate($state, [], false, $forceStopPropagation, $dataNew);
                        $this->reload();
                    }
                }
            }
        }
    }

    public function propagate(&$state = null, $relations = null, $clean = false, $forceStopPropag = false, $oldData = null)
    {
        $collectionRef = $this->getRelations();
        
        if (isset($state)) {} else {
            $state = new State();
        }
        if ($relations != null) {
            foreach ($collectionRef as $key => $value) {
                if (is_array($relations) && in_array($key, $relations)) {} else {
                    $state->addRelation($key);
                }
            }
        }
        foreach ($collectionRef as $key => $value) {
            // \Application\Controller\Log::getInstance()->AddRow(' Propagate BI_DIRR ' .$key . ' --- '. json_encode($oldData));
            $ref_simple = false;
            $results = array();
            
            if (isset($state)) {
                $relKey = $state->getRelation($key);
            }
            if (isset($relKey)) {
                // \Application\Controller\Log::getInstance()->AddRow(' NO-PROPAGATE ' . json_encode($key) . ' - ' . json_encode($state));
            } else {
                // \Application\Controller\Log::getInstance()->AddRow(' PROPA-GATE ' . json_encode($key) . ' - ' . json_encode($state).'=='.$forceStopPropag);
                $state->addRelation($key);
                // if is a simple relation just map the id's - simple relations are based on PK's
                // remove the old relation
                if ($this->getRelationType($key) == Model::DECL_REF_SIMPLE) {
                    if (isset($_SESSION["transaction_type"]) && $_SESSION["transaction_type"] != "method") {
                        // reference simple if transactions are from UI
                        $ref_simple = true;
                    }
                }
                // add the new one
                if ($this->getRelationType($key) == Model::DECL_OWNING || $this->getRelationType($key) == Model::DECL_REF || $ref_simple == true) {
                    
                    // get REL detials
                    $contianerRef = array();
                    $contianerRef = $this->getRelationDetails($key);
                    
                    // further propagation on REVERSE PATH
                    if (isset($contianerRef{'bi-dir'}) && ($contianerRef{'bi-dir'} === true || $contianerRef{'bi-dir'} === "1")) {
                        // \Application\Controller\Log::getInstance()->AddRow(' Propagate BI_DIR ' . json_encode($contianerRef));
                        $revP = $contianerRef{'reversePath'};
                        if ($revP == "this") {
                            // $results[] = $this->getPathReferences($revP);
                        } else {
                            
                            $results = $this->getPropagationCollection($contianerRef, $oldData);
                            // \Application\Controller\Log::getInstance()->AddRow(' Propagate BI_DIRX ' . json_encode($results));
                            
                            if (isset($results) && count($results) > 0) {
                                foreach ($results as $instanceObj) {
                                    if ($instanceObj != null) {
                                        
                                        // propagate all
                                        if ($this->getRelationType($key) == Model::DECL_OWNING) {
                                            $contianerRefObj = $instanceObj->getRelationDetails($contianerRef{'relations'}[0]);
                                            $instanceObj = $this;
                                            $this->executePropagationModel($contianerRefObj, $contianerRef, $instanceObj, $contianerRef{'relations'}[0], $state, true, $clean);
                                        } else {
                                            $s = '';
                                            $contianerRefObj = $instanceObj->getRelationDetails($contianerRef{'relations'}[0]);
                                            
                                            if (isset($contianerRefObj{'useReverse'})) {
                                                $pathIn = $contianerRefObj{'reversePath'};
                                                // $pathIn = $contianerRefObj{'path'};
                                            } else {
                                                $pathIn = $contianerRefObj{'path'};
                                            }
                                            $resultsIn = array();
                                            if ($pathIn == "this") {
                                                $resultsIn[] = $this;
                                            } else {
                                                // get Path
                                                $resultsIn = $instanceObj->getPropagationCollection($contianerRefObj, $oldData);
                                                // $resultsIn = $instanceObj->getPathReferences($pathIn);
                                                // \Application\Controller\Log::getInstance()->AddRow(' Propagate BI_DIRXXq ' . json_encode($resultsIn). "-" );
                                            }
                                            
                                            if (isset($resultsIn) && is_array($resultsIn) && count($resultsIn) > 0) {
                                                
                                                foreach ($resultsIn as $instanceObjIn) {
                                                    if ($instanceObjIn != null) {
                                                        $contianerRefObjIn = $instanceObjIn->getRelationDetails($contianerRef{'relations'}[0]);
                                                        $instanceObj->executePropagationModel($contianerRefObj, $contianerRefObjIn, $instanceObjIn, $key, $state, $forceStopPropag, $clean);
                                                        $instanceObj->reload();
                                                    }
                                                }
                                            }
                                            // $contianerRefObj = $instanceObj->getRelationDetails($contianerRef{'relations'}[0]);
                                            // $this->executePropagationModel($contianerRefObj, $contianerRef, $instanceObj, $contianerRef{'relations'}[0], $state, true, $clean);
                                            // $instanceObj->propagate($state, $contianerRef{'relations'});
                                        }
                                        $this->reload();
                                        // }
                                    }
                                }
                            }
                            // }
                            // }
                        }
                    } else {
                        // get Method ref
                        \Application\Controller\Log::getInstance()->AddRow(' Propagate NORMAL ' . $key . ' --- ' . json_encode($contianerRef));
                        // get Path - USUALY is NEXT
                        $path = $contianerRef{'path'};
                        $s = '';
                        // if self reference then just use the this object
                        if ($path == "this") {
                            $results[] = $this;
                        } else {
                            
                            if (isset($contianerRef{'filterTo'})) {
                                $filterTo = $contianerRef{'filterTo'};
                                $filterFrom = $contianerRef{'filterFrom'};
                                // $filterType = "date";
                                // $datetimeFrom = $this->{$filterFrom}
                                $filterFromS = strtotime($this->{$filterFrom});
                                // str_replace("-", "/", $this->{$filterFrom});
                                
                                if ($filterFromS) {
                                    $s = "[" . $filterTo . "-" . $filterFromS . "]";
                                }
                                
                                // \Application\Controller\Log::getInstance()->AddRow(' Propagate BI_DIRXX ' . json_encode($contianerRef). "-" . $s, " - ".json_encode($contianerRefObj) );
                            }
                            // get Path
                            $results = $this->getPathReferences($path . $s);
                        }
                        // if is indexed
                        // check for the last one
                        // if is the last the bring a different path
                        // $data = array();
                        if (isset($results) && is_array($results) && count($results) > 0) {
                            foreach ($results as $instanceObj) {
                                if ($instanceObj != null) {
                                    // \Application\Controller\Log::getInstance()->AddRow(' Propagate BI_DIRXX1 ' . $path . $s . "-" . $key . " - ");
                                    $contianerRefObj = $instanceObj->getRelationDetails($key);
                                    $this->executePropagationModel($contianerRef, $contianerRefObj, $instanceObj, $key, $state, $forceStopPropag, $clean);
                                    // \Application\Controller\Log::getInstance()->AddRow(' Propagate BI_DIRXX2 ' . $path . $s . "-" . $key . " - ");
                                    $this->reload();
                                }
                            }
                        } else {
                            if (isset($contianerRef{'param'})) {
                                if ($path == "getNext" || $this->substr_startswith($path, "getFirst")) {
                                    $state->cleanRelations();
                                }
                                $param = $contianerRef{'param'};
                                if (isset($this->{$param})) {
                                    $this->{$param} = null;
                                    $this->update(false, $state);
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    public function removeKeyFromArray($array, $key)
    {
        $state = array();
        foreach ($array as $keyIn) {
            if ($keyIn === $key) {} else {
                $state[] = $keyIn;
            }
        }
        return $state;
    }

    public function setMasterState($key, $id, $value)
    {
        $data = array(
            array(
                "id" => $id,
                "text" => $value
            )
        );
        $this->{$key} = $data;
        $this->update();
    }

    public function getMasterState($key, $value)
    {
        $ret = null;
        $masterdatas = $this->{$key};
        foreach ($masterdatas as $masterdata) {
            if ($masterdata['text'] == $value) {
                $ret = $key;
            }
        }
        return $ret;
    }

    public function getMasterStateValue($key)
    {
        $ret = null;
        $masterdatas = $this->{$key};
        if (isset($masterdatas) && count($masterdatas) > 0) {
            foreach ($masterdatas as $masterdata) {
                $ret = $masterdata['text'];
            }
        }
        return $ret;
    }

    public function getPropagationCollection($contianerRef, $oldData)
    {
        if (isset($contianerRef{'reversePath'})) {
            $revP = $contianerRef{'reversePath'};
            // $pathIn = $contianerRefObj{'path'};
        } else {
            $revP = $contianerRef{'path'};
        }
        // $revP = $contianerRef{'reversePath'};
        if (isset($contianerRef{'filterTo'})) {
            $filterTo = $contianerRef{'filterTo'};
            $filterFrom = $contianerRef{'filterFrom'};
            $filterType = "date";
            $datetimeFrom = $this->{$filterFrom};
            if ($datetimeFrom instanceof \MongoDate) {
                
                $filterFromS = strtotime($datetimeFrom->toDateTime()->format(Base::FORMAT_DATE));
            } else {
                $filterFromS = strtotime($this->{$filterFrom});
            }
            // str_replace("-", "/", $this->{$filterFrom});
            $s = '';
            if ($filterFromS) {
                $s = "[" . $filterTo . "-" . $filterFromS . "]";
            }
            // we have a . from date so no p
            $results = $this->getPathReferences($revP . $s);
            $resultsOld = [];
            // \Application\Controller\Log::getInstance()->AddRow(' Propagate BI_DIRR ' . $revP . $s . " --" . ' --- ' . json_encode($oldData));
            if (isset($oldData[$filterFrom])) {
                // $datetimeFrom = $this->{$filterFrom}
                if ($oldData[$filterFrom] instanceof \MongoDate) {
                    $mongoDate = $oldData[$filterFrom];
                    $filterFromS = strtotime($mongoDate->toDateTime()->format(Base::FORMAT_DATE));
                } else {
                    $filterFromS = strtotime($oldData[$filterFrom]);
                }
                // str_replace("-", "/", $this->{$filterFrom});
                $s = '';
                if ($filterFromS) {
                    $s = "[" . $filterTo . "-" . $filterFromS . "]";
                }
                // we have a . from date so no p
                $resultsOld = $this->getPathReferences($revP . $s);
            }
            $results = array_merge($results, $resultsOld);
            // \Application\Controller\Log::getInstance()->AddRow(' Propagate BI_DIRXX ' . $revP . $s . "-" . json_encode($results));
        } else {
            $results = $this->getPathReferences($revP);
        }
        return $results;
    }

    public function executePropagationModel($contianerRef, $contianerRefObj, $instanceObj, $key, $state, $forceStopPropag = false, $clean = false)
    {
        // example sum(%collectionName%, %field1%);
        if ($instanceObj != null && isset($instanceObj->_id)) {
            // get the ref details from the key!!! - for reverse path only?
            // $contianerRefObj = $instanceObj->getRelationDetails($key);
            $fields = $contianerRef{'field'};
            $path = $contianerRef{'path'};
            $no_param = false;
            // if (isset($contianerRefObj{'param'})) {
            if (isset($contianerRefObj{'param'})) {
                $parameter = $contianerRefObj{'param'};
            } else {
                $parameter = "return_dummy";
                $no_param = true;
            }
            
            $methodC = "";
            $method = $contianerRef{'method'};
            $methodC = $method;
            $methodC = $methodC . "(";
            // calculate from previous - usually revesre path is PREV
            $data["path"] = $contianerRefObj{'reversePath'};
            $methodC = $methodC . "@path@";
            $i = 0;
            foreach ($fields as $field) {
                $i = $i + 1;
                $data['field' . $i] = $field;
                $methodC = $methodC . ",@" . 'field' . $i . "@";
            }
            $methodC = $methodC . ");";
            $jsonData = array();
            $clean1 = false;
            if ($clean1) {
                // if ($clean) {
                // \Application\Controller\Log::getInstance()->AddRow(' CLEANPROPAG ' . json_encode($instanceObj) . ' -- ' . $parameter . ' value ' . $key . ' relation -');
                $jsonData[$parameter] = null;
            } else {
                if (isset($contianerRef{'filter'})) {
                    $filter = $contianerRef{'filter'};
                    $filterData = explode(':', $filter);
                    $filterLen = sizeof($filterData);
                    $indexFilter = 0;
                    $foundFilter = false;
                    $search = array();
                    $log = \Application\Controller\Log::getInstance();
                    while ($indexFilter < $filterLen) {
                        $searchL = array();
                        $searchL['field'] = $filterData[$indexFilter];
                        $searchL['type'] = 'string';
                        $searchL['operator'] = 'contains';
                        $searchL['value'] = $filterData[$indexFilter + 1];
                        $log->AddRow(" Get FILTER PROP ref action n4: " . json_encode($searchL));
                        // $searchlogic = isset($data['searchLogic']) ? $data['searchLogic'] : '';
                        $search["search"][] = $searchL;
                        $indexFilter = $indexFilter + 2;
                    }
                    $search["searchLogic"] = "AND";
                    
                    $collectionInstances = $instanceObj->getPathReferences($data["path"], $search);
                    
                    $log->AddRow(" EXECp fil1 -< " . json_encode($collectionInstances) . ' >-on --> ' . json_encode($filterData));
                    $return = 0;
                    if (sizeof($collectionInstances) == 0) {
                        $jsonData[$parameter] = "";
                    } else {
                        foreach ($collectionInstances as $collectionInstance) {
                            if (isset($collectionInstance)) {
                                if (sizeof($filterData) > 1) {
                                    $jsonData[$parameter] = "" . $collectionInstance->{$data['field1']};
                                } else {
                                    if ($this->substr_startswith($parameter, 'is_') || $this->substr_startswith($parameter, 'Is_')) {
                                        $ret = $instanceObj->evaluateNew($methodC, $data);
                                        // TRUE OR FALSE - otherwise encode
                                        if ($ret < 10) {
                                            $jsonData[$parameter] = "" . $ret;
                                        } else {
                                            $jsonData[$parameter] = json_encode($ret);
                                        }
                                    } else {
                                        $jsonData[$parameter] = "" . $instanceObj->evaluateNew($methodC, $data);
                                    }
                                }
                            }
                        }
                    }
                } else {
                    if ($this->substr_startswith($parameter, 'is_') || $this->substr_startswith($parameter, 'Is_')) {
                        $ret = $instanceObj->evaluateNew($methodC, $data);
                        if ($ret < 10) {
                            $jsonData[$parameter] = "" . $ret;
                        } else {
                            $jsonData[$parameter] = json_encode($ret);
                        }
                    } else {
                        $jsonData[$parameter] = "" . $instanceObj->evaluateNew($methodC, $data);
                    }
                }
                if ($instanceObj instanceof Indexed) {
                    // $forceStopPropag = true;
                    $revpath = '';
                    if (isset($data["reversePath"])) {
                        $revpath = $data["reversePath"];
                    }
                    if ($path == "getNext") {
                        $state->removeKeyFromRelations($key);
                        // $state["relations"] = $this->removeKeyFromArray($state["relations"], $key);
                        $relationsP = $instanceObj->getPropagativeRelations();
                        foreach ($relationsP as $keyP => $valueP) {
                            // \Application\Controller\Log::getInstance()->AddRow(' UPDATE_PROPAGATION1X --' . $keyP. " ====" . $valueP. '_encode($state)');
                            
                            $contianerRef = array();
                            $contianerRef = $instanceObj->getRelationDetails($keyP);
                            if (isset($contianerRef['path']) && ($contianerRef['path'] === 'this')) {
                                $state->removeKeyFromRelations($keyP);
                                // $state["relations"] = $this->removeKeyFromArray($state["relations"], $keyP);
                            }
                        }
                        // $state = array();
                        // unset($state[$key]);
                        if ($forceStopPropag == 3)
                            $forceStopPropag = true;
                        else
                            $forceStopPropag = 3;
                    }
                }
            }
            // $this->reload();
            // if ($instanceObj->getIdAsString() == $this->getIdAsString()) {
            $instanceObj->reload();
            if ($no_param == false) {
                if ($this->substr_startswith($parameter, 'is_') || $this->substr_startswith($parameter, 'Is_')) {
                    if (json_encode($instanceObj->{$parameter}) !== json_encode($jsonData[$parameter])) {
                        // \Application\Controller\Log::getInstance()->AddRow(' UPDATE_PROPAGATIONX --'.$path.'--' . json_encode($instanceObj->{$parameter}) . " ====" . json_encode($jsonData[$parameter]) . ' key ' . $key . ' value relation -' );
                        $instanceObj->update(json_encode($jsonData), $state, $forceStopPropag);
                        $instanceObj->reload();
                    }
                } else if ($instanceObj->{$parameter} !== $jsonData[$parameter]) {
                    // \Application\Controller\Log::getInstance()->AddRow(' UPDATE_PROPAGATION --'.$path.'--' . json_encode($this) . " ====" . json_encode($instanceObj) . ' key ' . $key . ' value ' . json_encode($jsonData) . ' relation -' . json_encode($state));
                    $instanceObj->update(json_encode($jsonData), $state, $forceStopPropag);
                    $instanceObj->reload();
                }
            }
            // } else {
            
            // }
            
            // }
            // $laf = new MongoObjectFactory();
            // $laf->save($instanceObj);
        } else {
            if (isset($contianerRef{'param'})) {
                
                $param = $contianerRef{'param'};
                if (isset($this->{$param})) {
                    $this->{$param} = null;
                    $this->update(false, $state);
                }
            }
        }
        $this->reload();
    }

    /**
     * Simple REF are made unidirectional on PK
     *
     * @param unknown $objectRef            
     * @param unknown $collectionName            
     * @param unknown $primaryKey            
     * @param unknown $collectionThisName            
     */
    public function resetSimpleReference($objectRef, $collectionName, $primaryKey, $collectionThisName)
    {
        // TODO change to find through container!!
        // $oldcalendarday = $calendar->getReferenceOnPK('calendardays', $this->{$key});
        if ($this->_id instanceof \MongoId) {
            $oId = (string) $this->_id;
        } else {
            $oId = $this->_id['$id'];
        }
        $objRef = $objectRef->getDay($primaryKey);
        // \Application\Controller\Log::getInstance()->AddRow(' UNSET1 ' . $oId . ' value ' . json_encode($objRef) . ' relation -');
        
        if (isset($objRef)) {
            $idenx = $objRef->searchReference($objRef->{$collectionThisName}, $oId);
            if ($idenx >= 0) {
                unset($objRef->{$collectionThisName}[$idenx]);
                $refId = (string) $objRef->_id['$id'];
                $objRef->_id = new \MongoId($refId);
                // \Application\Controller\Log::getInstance()->AddRow(' UNSET ' . $idenx . ' value ' . json_encode($objRef) . ' relation -');
                $this->updateObject($objRef->_id, $objRef->getTableName(), $objRef);
            }
        }
    }

    /**
     * Creating a simple ref to THIS object - unidirectional
     *
     * @param unknown $object            
     * @param unknown $nameRef            
     */
    public function createSimpleReference($remoteObject, $nameRef)
    {
        if ($this->_id instanceof \MongoId) {
            $oId = (string) $this->_id;
        } else {
            $oId = $this->_id['$id'];
        }
        if (isset($remoteObject->{$nameRef}) && $remoteObject->searchReference($remoteObject->{$nameRef}, $oId) < 0) {
            $referenceTag = array();
            $refId = $remoteObject->_id['$id'];
            $referenceTag['$ref'] = $nameRef;
            $referenceTag['$id'] = $oId;
            $remoteObject->{$nameRef}[] = $referenceTag;
            $remoteObject->_id = new \MongoId($remoteObject->_id['$id']);
            // \Application\Controller\Log::getInstance()->AddRow(' CALENDARX ' . $oId . ' value ' . json_encode($remoteObject) . ' relation -');
            $this->updateObject($refId, $remoteObject->getTableName(), $remoteObject);
        }
    }

    function substr_startswith($haystack, $needle)
    {
        return substr($haystack, 0, strlen($needle)) === $needle;
    }

    /**
     * Creating a simple ref to THIS object - unidirectional
     *
     * @param unknown $object            
     * @param unknown $nameRef            
     */
    public function getActiveWorkspace()
    {
        $workspace = null;
        if (isset($_SESSION['workspaceId'])) {
            $workspaceId = $_SESSION['workspaceId'];
            $workspace = $this->getInstance("Workspace", $workspaceId);
        }
        return $workspace;
    }

    /**
     * Creating a simple ref to THIS object - unidirectional
     *
     * @param unknown $object            
     * @param unknown $nameRef            
     */
    public function getActiveUser()
    {
        $user = null;
        if (isset($_SESSION['userId'])) {
            $userId = $_SESSION['userId'];
            $user = $this->getInstance("User", $userId);
        }
        return $user;
    }

    public function getTableName()
    {
        $class = $this->get_class_name($this);
        return strtolower($class) . 's';
    }

    public function getClassName()
    {
        $classN = $this->get_class_name($this);
        return $classN;
    }

    public function get_class_name($object = null)
    {
        if (! is_object($object) && ! is_string($object)) {
            return false;
        }
        
        $class = explode('\\', (is_string($object) ? $object : get_class($object)));
        return $class[count($class) - 1];
    }

    public function getFullClassName($object = null)
    {
        if (! is_object($object) && ! is_string($object)) {
            return false;
        }
        
        // $class = explode('\\', (is_string($object) ? $object : get_class($object)));
        return get_class($object);
    }

    function getClassNameFromTable($tableName)
    {
        $refType = ucfirst(substr($tableName, 0, strlen($tableName) - 1));
        return $refType;
    }

    /**
     * Remove all elements from object $name with array of $ids
     *
     * @param array $ids            
     * @param string $name            
     */
    public function remove($object, $softRemove = true, $noPropagation = false)
    {
        $mongoObjectFactory = new MongoObjectFactory();
        $name = $object->getTableName();
        $id = $object->get_id();
        // removefromParent
        $colet = $this->{$name};
        for ($i = 0; $i < count($colet); ++ $i) {
            if (isset($colet[$i])) {
                if ($colet[$i]['$id'] === $id) {
                    unset($colet[$i]);
                }
            }
        }
        $sData = new StateData();
        $sData->setObjectData($this);
        $sData->setType(StateData::UPDATE);
        $sData->setObjecttype($this->getClassName());
        $sData->setObjectid($this->getIdAsString());
        $mongoObjectFactory->saveStateData($_SESSION['transaction_id'], $sData);
        
        $this->{$name} = array();
        foreach ($colet as $col) {
            $this->{$name}[] = $col;
        }
        $this->update();
        // $object = $mongoObjectFactory->findObject($id, $name);
        // get collection;
        $relations = array();
        // set deleted to -1
        $object->deleted = - 1;
        $object->update();
        $object->reload();
        // $state = new State();
        // $state->setTransactionid("UNKNOWN");
        $dataNew = array();
        // save state of this before changing it
        $sData = new StateData();
        $sData->setObjectData($object);
        $sData->setType(StateData::REMOVE);
        $sData->setObjecttype($object->getClassName());
        $sData->setObjectid($object->getIdAsString());
        $mongoObjectFactory->saveStateData($_SESSION['transaction_id'], $sData);
        // a new instance has been removed - now propagate!
        // $object->propagate([],[],true);
        // $object->reload();
        // remove the childrens of the child for owning
        $relations = $object->getOwningRelations();
        // \Application\Controller\Log::getInstance()->AddRow(' REMOVE' . ' value ' . json_encode($object->getClassName()) . ' relation -');
        foreach ($relations as $key => $value) {
            // remove relation
            // \Application\Controller\Log::getInstance()->AddRow(' REMOVEOWNING ' . $key . ' value ' . json_encode($this->getClassNameFromTable($valueRef['$ref'])) . ' relation -');
            
            foreach ($object->{$key} as $keyRef => $valueRef) {
                $childObject = $mongoObjectFactory->findObject($this->getClassNameFromTable($valueRef['$ref']), $valueRef['$id']);
                $object->remove($childObject, $softRemove, $noPropagation);
                unset($object->{$key}[$keyRef]);
            }
        }
        if ($object instanceof Indexed) {
            // $nextObject = $object->getNext();
            $key = 'next';
            unset($object->{$key}[0]);
            $key = 'prev';
            // $nextObject->prev = $object->{$key};
            // $nextObject->update();
            unset($object->{$key}[0]);
            $object->update();
        }
        $state = new State();
        
        $object->reload(- 1);
        // if (isset($_SESSION["transaction_type"]) && $_SESSION["transaction_type"] != "method") {
        if ($noPropagation == false) {
            $object->propagate($state, [], true);
        }
        // $object->propagate([], [], true);
        // for each other ref - remove ref - delete
        $relations = $object->getReferenceRelations();
        foreach ($relations as $key => $value) {
            $listOfRef = $object->{$key};
            // \Application\Controller\Log::getInstance()->AddRow(' ONE TO MANY ' . $key . ' value ' . json_encode($value) . ' relation -' . json_encode($listOfRef));
            if (isset($listOfRef) && is_array($listOfRef)) {
                foreach ($listOfRef as $keyR => $valueR) {
                    // \Application\Controller\Log::getInstance()->AddRow(' Remove link ' . $key . ' value ' . json_encode($valueR) . ' relation -' . $this->getRelationType($key));
                    $object->removeAndUpdateReference($name, $key, $valueR['$id'], $noPropagation);
                }
            }
        }
        
        $object->reload();
        $state = new State();
        
        $contianerRef = array();
        $contianerRef = $this->getRelationDetails($name);
        // \Application\Controller\Log::getInstance()->AddRow(' REMOVE1' . ' value ' . json_encode($object) . ' relation -');
        // if (isset($_SESSION["transaction_type"]) && $_SESSION["transaction_type"] != "method") {
        if ($object instanceof Indexed) {
            $sortIndex = '';
            if (isset($contianerRef{'sorted'})) {
                $sortIndex = $contianerRef{'sorted'};
            } else {
                $contianerRefP = $this->getRelationDetails($object->getTableName());
                if (isset($contianerRefP{'sorted'})) {
                    $sortIndex = $contianerRefP{'sorted'};
                }
            }
            if ($noPropagation == false) {
                // \Application\Controller\Log::getInstance()->AddRow(' REMOVEREINDEX' . ' value ' . json_encode($object) . ' relation -');
                $firstObj = $this->reindexReference($object->getClassNameFromTable($name), $sortIndex, true);
                // propagate the entire collection (expensive?)
                if (isset($firstObj)) {
                    $firstObj->propagate();
                }
                $this->reload();
                $this->propagate();
                $this->reload();
            }
        }
        // }
        if ($softRemove == true) {} else {
            $mongoObjectFactory->remove($object->getClassName(), $id);
        }
    }

    /**
     * Remove all elements from object $name with array of $ids
     *
     * @param array $ids            
     * @param string $name            
     */
    public function removeObjects($ids, $name)
    {
        $m = Database::getInstance();
        $mongoObjectFactory = new MongoObjectFactory();
        // select a database
        $db = $m->{$mongoObjectFactory->getDBName()};
        $collection = $db->$name;
        $collection->update(array(
            '_id' => array(
                '$in' => $ids
            )
        ), array(
            '$set' => array(
                'deleted' => 1
            )
        ), array(
            "multiple" => true
        ));
    }

    public function execute($name, $code, $data)
    {
        /*
         * $log = \Application\Controller\Log::getInstance();
         * $string = $code;
         *
         * if (isset($data) && is_array($data)) {
         * foreach ($data as $key => $value) {
         * $log->AddRow(" Estep -< " . $key . " >-on " . $value . ' --> ');
         * $string = preg_replace("/@" . $key . "@/", $value, $code);
         * }
         * }
         * // $this->_id = new \MongoId($this->_id['$id']);
         * // $log->AddRow(" EXECp -< " . $string . " >-on " . $this->_id. ' --> '.$code)
         */
        $ret[$name] = $this->evaluate($code, $data);
        if ($this->_id instanceof \MongoId) {
            $ret["id"] = (string) $this->_id;
        } else {
            $ret["id"] = $this->_id['$id'];
        }
        return $ret;
    }

    public function executeNew($name, $code, $data)
    {
        try {
            $ret[$name] = $this->evaluateNew($code, $data);
            if ($this->_id instanceof \MongoId) {
                $ret["id"] = (string) $this->_id;
            } else {
                $ret["id"] = $this->_id['$id'];
            }
            return $ret;
        } catch (\Exception $e) {
            
            throw $e;
        }
    }

    public function evaluate($code, $data = null)
    {
        $log = \Application\Controller\Log::getInstance();
        $string = $code;
        $ret = false;
        if (isset($data) && is_array($data)) {
            foreach ($data as $key => $value) {
                if (! is_array($value)) {
                    // $log->AddRow(" EXECUTE -< " . $key . " >-on " . $value . ' --> ');
                    $string = preg_replace("/@" . $key . "@/", $value, $code);
                }
            }
        }
        // $this->_id = new \MongoId($this->_id['$id']);
        
        if (strlen($string) > 20) {
            $log->AddRow(" EXECp -< " . $string . ' >-on --> ' . $code);
            $ret = eval($string);
        }
        return $ret;
    }

    public function evaluateNew($code, $data = null)
    {
        $log = \Application\Controller\Log::getInstance();
        $string = $code;
        $ret = null;
        if (isset($data) && is_array($data)) {
            foreach ($data as $key => $value) {
                if (! is_array($value)) {
                    // $log->AddRow(" EXECUTE -< " . $key . " >-on " . $value . ' --> ');
                    $string = str_replace("@$key@", '"' . $value . '"', $code);
                    $code = $string;
                    // $string = preg_replace("/%" . $key . "%/", $value, $code);
                    // $code = $string;
                } else {
                    $string = str_replace("@$key@", '\'' . json_encode($value) . '\'', $code);
                    $code = $string;
                }
            }
        }
        // eliminate the case of incidental "@"
        
        // clean if @left@
        $string = preg_replace('/@[\w]+?@/', '""', $string);
        
        if (isset($this->_id)) {
            // $this->_id = new \MongoId($this->_id['$id']);
            $execStr = 'return $this->' . $string;
            $log->AddRow(" EXECp -<" . $execStr . '>-on --> ' . json_encode($this));
            $ret = eval($execStr);
        }
        
        return $ret;
    }

    public function prepareDelete()
    {}

    /**
     *
     * @param string $id            
     * @param string $name            
     * @param array $data            
     */
    public function updateObject($id, $name, $data)
    {
        $m = Database::getInstance();
        $mongoObjectFactory = new MongoObjectFactory();
        $refType = ucfirst(substr($name, 0, strlen($name) - 1));
        // select a database
        $db = $m->{$mongoObjectFactory->getDBName($refType)};
        $collection = $db->$name;
        $dataX = clone $data;
        // \Application\Controller\Log::getInstance()->AddRow(' LineFORMATprev ' . $id . ' value ' . json_encode($dataX) . ' - ');
        
        $refInstance = $mongoObjectFactory->findObject($refType, $id);
        if (isset($refInstance)) {
            foreach ($dataX as $key => $value) {
                if (is_array($value)) {} else {
                    // \Application\Controller\Log::getInstance()->AddRow(' getFormatVarialble ' . $key . ' value ' . json_encode($value) . ' - ');
                    $dataX->{$key} = $refInstance->getFormatVarialble($value, $key);
                }
            }
            $dataX->version = "" . time();
        }
        
        // \Application\Controller\Log::getInstance()->AddRow(' LineFORMAT ' . $id . ' value ' . json_encode($dataX) . ' - ');
        
        $collection->update(array(
            '_id' => new \MongoId($id)
        ), $dataX, array(
            "upsert" => true
        ));
    }

    public function getOne($name)
    {
        $refList = $this->getInstances($name);
        foreach ($refList as $refInst) {
            if (isset($refInst)) {
                return $refInst;
            }
        }
        return null;
    }

    public function getLastInstance($type, $orderByField)
    {
        $typeName = $this->getClassName();
        $mongoObjectFactory = new MongoObjectFactory();
        // get all of this TPYE :
        $methodsRef = "getParent.get" . $this->getClassName() . ".get" . $type;
        $paramValues = array();
        // add order byand limit to 1 - we just want the last one
        $sort = array();
        $sortF = array();
        $sortF["field"] = $orderByField;
        $sortF["direction"] = "desc";
        $sortF["limit"] = 1;
        $sort[] = $sortF;
        $ncriteria = array();
        $ncriteria['deleted'] = 0;
        $resultRef = $mongoObjectFactory->findObjectsByCriteria($type, $ncriteria, false, '', $sort);
        // getInstancesReference($methodsRef, '', $sort);
        return $resultRef;
    }
}

?>