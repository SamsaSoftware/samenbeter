<?php
namespace Application\Service;

use Application\Controller\MongoObjectFactory;
use Dompdf\Dompdf;
use Application;
use Application\DatabaseConnection\Database;
use Application\Document\Workspace;
use Application\Document\Indexed;

class BackupService extends Service
{

    private $mongoFactory;

    private $listOfIds = array();

    public function write($dirname, $objectcol)
    {
        $resultsOut = array();
        $path = "";
        if (isset($objectcol[0])) {
            $result[$objectcol[0]->getTableName()] = $objectcol;
            $path = 'export/' . $dirname . '/' . $objectcol[0]->getTableName() . ".json";
            if (! file_exists(dirname($path)))
                mkdir(dirname($path), 0777, true);
            
            file_put_contents($path, json_encode($result));
        }
        return $path;
    }

    public function export($data)
    {
        $method = $data['method'];
        $objectRef = $data['objectType'];
        $objectId = $data['id']['$id'];
        
        $mongoObjectFactory = new MongoObjectFactory();
        $instances = array();
        $instances[] = $mongoObjectFactory->findObjectInstance($objectRef, (string) $objectId);
        $path = 'export/' . $method . "_" . $instances[0]->name;
        $result = $this->exportToFile($path, $instances);
        // $path = 'export/' . $user->getOrganization()->getActiveWorkspace() . ".json";
        // file_put_contents($path, json_encode($result));
        return $result;
    }
    
    

    public function importUI($data, $workspace)
    {
        $mongoObjectFactory = new MongoObjectFactory();
        // \Application\Controller\Log::getInstance()->AddRow(' XXimportUI ' . json_encode($data));
        $session = $this->getSession();
        $dm = $this->getServiceLocator()->get('doctrine.documentmanager.odm_default');
        $identity = $session->getIdentity();
        $user = $dm->getRepository("\\Application\\Document\\User")->findOneBy(array(
            "id" => $identity['id']
        ));
        $organizationId = $user->getOrganization()->getId();
        $organization = $mongoObjectFactory->findObjectInstance("Organization", (string) $organizationId);
        $nameDir = getcwd();
        $path = $nameDir . "/export/export1_" . $organization->name;
        if (! file_exists($path)) {
            mkdir($path, 0777, true);
        } else {
            $files = scandir($path);
            foreach ($files as $pathItem) {
                $pathItem = $path . "/" . $pathItem;
                if (is_file($pathItem))
                    unlink($pathItem);
            }
            rmdir($path);
            mkdir($path, 0777, true);
        }
        // $fileName = $urlDestination . "/" . $fileData['name'];
        $filePath = $path . "/" . $data['name'];
        $file = fopen($filePath, 'wb');
        $binary = base64_decode($data['content']);
        fwrite($file, $binary);
        fclose($file);
        
        $zip = new \ZipArchive();
        $zip->open($filePath);
        // $zip->open($zipHanlde);
        $zip->extractTo($path);
        $zip->close();
        $datawks1 = array(
            "active" => 'false',
            "title" => "importedWorkspace",
            "name" => "workspace"
        );
        // "parent" => array( array('$id'=> (string) $objectP1['$id'] , '$ref' =>"organizations" ))
        
        $typeW = 'Workspace';
        
        // $idRel = $this->importFromFile($path, $organization, "workspaces", $datawks1, true);
        
        $relations = $workspace->getAdminRelations();
        \Application\Controller\Log::getInstance()->AddRow(' XXimportMain   - pathitem - ' . $path . ' - ' . json_encode($relations));
        $name = $workspace->getTableName();
        // iterate the childs
        foreach ($relations as $key => $value) {
            $class_name = ucfirst(substr($key, 0, strlen($key) - 1));
            // \Application\Controller\Log::getInstance()->AddRow(' XXimportMain removing - pathitem - ' . $path . ' - ' . json_encode(key));
            $instances = $workspace->getInstances($class_name);
            foreach ($instances as $inputObject) {
                // \Application\Controller\Log::getInstance()->AddRow(' XXimportUI - remove - ' . $inputObject->getIdAsString());
                
                $workspace->remove($inputObject, false);
            }
            // $workspace->{$key} = array();
        }
        $this->listOfIds = array();
        $workspace->update();
        // iterate the childs
        foreach ($relations as $key => $value) {
            //
            \Application\Controller\Log::getInstance()->AddRow(' XXimportUI1XX - pathitem - ' . $key);
            // self::importJson($path, $workspace, $key, self::importFile($path, $key), true);
            $newId = self::importJson("export1_" . $organization->name, $workspace, 0, $key, self::importFile("export1_" . $organization->name, $key), true, $workspace->getIdAsString());
            $mongoObjectFactory = new MongoObjectFactory();
            if ($newId > 0) {
                $object = $mongoObjectFactory->findObjectInstance($workspace->getClassNameFromTable($key), $newId);
            }
        }
        // \Application\Controller\Log::getInstance()->AddRow(' XXimportUI144 - array - ' . json_encode($this->listOfIds));
        $updatedIds = array();
        \Application\Controller\Log::getInstance()->AddRow(' RESOLVE REL - pathitem - ' . $key);
        $this->resolveRererenceRelations($this->listOfIds, $workspace, $updatedIds, true);
        return true;
    }

    public function exportUI($data, $workspace)
    {
        \Application\Controller\Log::getInstance()->AddRow(' XXexportUI12345 ' . json_encode($workspace));
        $mongoObjectFactory = new MongoObjectFactory();
        $method = $workspace->title . "_" . date("Ymdhisa");
        
        $instances = array();
        $instances[] = $workspace; // $mongoObjectFactory->findObjectInstance($objectRef, (string) $objectId);
        $path = 'export/' . $method . "_" . $instances[0]->title;
        
        // $adminOnly = TRUE!
        $result = $this->exportToFile($path, $instances, true);
        
        $name = pathinfo($path, PATHINFO_BASENAME);
        $dir = pathinfo($path, PATHINFO_DIRNAME);
        $nameDir = getcwd();
        if (isset($path)) {
            // $pathIs = $path->getPathname();
            \Application\Controller\Log::getInstance()->AddRow(' XXexportUI12 ' . $nameDir . '  - name - ' . $name);
            $zip = new \ZipArchive();
            $zip->open($nameDir . "/export/" . $path . "/" . 'UIArchive.zip', \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
            if (! file_exists($nameDir . "/export/" . $path)) {
                mkdir($nameDir . "/export/" . $path, 0777, true);
            }
            $files = scandir($nameDir . "/export/" . $dir . "/" . $name);
            \Application\Controller\Log::getInstance()->AddRow(' XXexportUI12   - files - ' . json_encode($files));
            foreach ($files as $pathItem) {
                // Skip directories (they would be added automatically)
                
                // Get real and relative path for current file
                $pathItem = $nameDir . "/export/" . $path . "/" . $pathItem;
                $relativePath = pathinfo($pathItem, PATHINFO_BASENAME);
                if (is_dir($pathItem)) {} else {
                    \Application\Controller\Log::getInstance()->AddRow(' XXexportUI123 add   - files - ' . json_encode($pathItem));
                    // Add current file to archive
                    $zip->addFile($pathItem, $relativePath);
                }
            }
            $zip->close();
            \Application\Controller\Log::getInstance()->AddRow(' XXexportUI1234 add   - files - ' . json_encode($zip));
            $result = array();
            
            $pathItem = $nameDir . "/export/" . 'export/' . $method . "_" . $instances[0]->title . "/" . 'UIArchive.zip';
            \Application\Controller\Log::getInstance()->AddRow(' XXexportUI1   - pathitem - ' . $pathItem);
            // $path = $pathItem->getPathname();
            $mime = mime_content_type($pathItem);
            $size = filesize($pathItem);
            
            $data = file_get_contents($pathItem);
            $base64 = base64_encode($data);
            if (isset($mime) && isset($data)) {
                $user = $mongoObjectFactory->getOdmUser();
                $urlDestination = getcwd() . '/public/img/upload/' . $user->getOrganization()->getId();
                
                // make a folder with hotel id if doesn't exist one
                if (! file_exists(realpath($urlDestination))) {
                    mkdir($urlDestination, 0777, true);
                }
                // $fileData = $input;
                
                // print_r($fileData);exit;
                $binary = base64_decode($base64);
                
                $fileName = $urlDestination . "/" . 'UIArchive.zip';
                $filePath = '/public/img/upload/' . $user->getOrganization()->getId() . "/" . 'UIArchive.zip';
                $file = fopen($fileName, 'wb');
                fwrite($file, $binary);
                fclose($file);
                $result = $filePath;
            }
            
            $workspace->workspaceDocument = $result;
        } else {
            $workspace->workspaceDocument = '';
        }
        
        $workspace->update();
        
        $files = scandir($nameDir . "/export/" . $dir . "/" . $name);
        foreach ($files as $pathItem) {
            $pathItem = $nameDir . "/export/" . $path . "/" . $pathItem;
            if (is_file($pathItem))
                unlink($pathItem);
        }
        rmdir($nameDir . "/export/" . $path);
        
        // $path = 'export/' . $user->getOrganization()->getActiveWorkspace() . ".json";
        // file_put_contents($path, json_encode($result));
        return $result;
    }

    public function import($data)
    {
        $method = $data['method'];
        $objectRef = $data['objectType'];
        $objectId = $data['id']['$id'];
        $mongoObjectFactory = new MongoObjectFactory();
        $instances = array();
        $instances[] = $mongoObjectFactory->findObjectInstance($objectRef, (string) $objectId);
        
        $keyMap = array();
        $result = file_get_contents('export/' . $method . "_" . $instances[0]->name);
        $data = json_decode($result, true);
        $typeW = '$objectRef';
        $session = $this->getSession();
        $dm = $this->getServiceLocator()->get('doctrine.documentmanager.odm_default');
        $identity = $session->getIdentity();
        $user = $dm->getRepository("\\Application\\Document\\User")->findOneBy(array(
            "id" => $identity['id']
        ));
        $organization = $user->getOrganization();
        self::importJson($organization, $typeW, $data);
    }

    public function migrateWorkspace($data)
    {
        $method = $data['method'];
        $objectRef = $data['objectType'];
        $objectId = $data['id']['$id'];
        
        $mongoObjectFactory = new MongoObjectFactory();
        $workspace = array();
        $workspace[] = $mongoObjectFactory->findObjectInstance($objectRef, (string) $objectId);
        $path = 'export/' . $method . "_" . $workspace[0]->name;
        $result = $this->migrate($path, $workspace);
        // $path = 'export/' . $user->getOrganization()->getActiveWorkspace() . ".json";
        // file_put_contents($path, json_encode($result));
        return $result;
    }

    public function importWorkspace($organization, $file)
    {
        $keyMap = array();
        $result = file_get_contents('export/' . $file);
        $data = json_decode($result, true);
        $typeW = 'Workspace';
        $session = $this->getSession();
        $dm = $this->getServiceLocator()->get('doctrine.documentmanager.odm_default');
        $identity = $session->getIdentity();
        $user = $dm->getRepository("\\Application\\Document\\User")->findOneBy(array(
            "id" => $identity['id']
        ));
        $organization = $user->getOrganization();
        self::importJson($organization, $typeW, $data);
    }

    private function setRelation($relations, $key, $value, $objectName, $id)
    {
        $mongoObjectFactory = new MongoObjectFactory();
        // relations = "key", "foreign_type, foreign_key"
        // get the relation
        $relation = array();
        $relation = explode(",", $relations[$key]);
        // find the relation instance
        // type = relation[0] , column=relation[2] value =$data[$c]
        $criteria = array(
            $relation[1] => $value
        );
        $typeC = $relation[0];
        $relObject = $mongoObjectFactory->findObjectInstanceByCriteria($typeC, $criteria);
        $log = \Application\Controller\Log::getInstance();
        
        if (isset($relObject)) {
            $nameOfTable = \strtolower($objectName) . 's';
            $reference = array();
            $reference['$ref'] = $nameOfTable;
            $reference['$id'] = (string) $id;
            $relObject->addReferenceObject($nameOfTable, $reference);
        }
    }

    private function importFile($dirname, $type)
    {
        $data = array();
        $filename = 'export/' . $dirname . '/' . $type . ".json";
        if (file_exists($filename)) {
            $result = file_get_contents($filename);
            $data = json_decode($result, true);
        }
        return $data;
    }
    

    public function importJson($dirname, $parent, $parentOldId, $type, $data, $adminOnly = false, $workspaceId = 0)
    {
        $newId = 0;
        // \Application\Controller\Log::getInstance()->AddRow(' XXimportJSON - pathitem - ' . json_encode($type));
        \Application\Controller\Log::getInstance()->AddRow(' XXimportUI - pathitem - ' . json_encode($type));
        $mongoObjectFactory = new MongoObjectFactory();
        if (isset($data) && isset($data[$type])) {
            $inputArray = $data[$type];
            foreach ($inputArray as $item) {
                // \Application\Controller\Log::getInstance()->AddRow(' XXimportUI - pathitem - ' . json_encode($item));
                $id = 0;
                if (isset($item['_id']['$id'])) {
                    if ($parentOldId == 0 || $item['parent'][0]['$id'] == $parentOldId) {
                        // save ID in the main list
                        $id = $item['_id']['$id'];
                        // unset
                        unset($item['_id']);
                        // unset
                        unset($item['parent']);
                        $parentType = $parent->get_class_name($parent);
                        // create the new object
                        $objType = $parent->getClassNameFromTable($type);
                        // $newId = $parent->add($objType, $item);
                        if ($objType == "Mastertable") {
                            $item["workspaceId"] = $workspaceId;
                        }
                        if ($objType == "Field") {
                            
                            if (isset($item["html"])) {
                                $labelV = '';
                                $i = 0;
                                
                                foreach ($item["html"] as $itemH) {
                                    if ($i == 0) {
                                        $labelV = $itemH;
                                    } else 
                                        if ($i == 2) {
                                            $labelV = $labelV . "^" . $itemH;
                                        }
                                    $i = $i + 1;
                                }
                                
                                $item["label"] = $labelV;
                            }
                            if (isset($item["type"]) && $item["type"] == "form") {
                                $item["options"] = $item["optionsString"];
                            }
                            if (isset($item["typeReference"]) && strlen($item["typeReference"]) > 1) {
                                if ($this->substr_startswith($item["typeReference"], 'ref')) {
                                    $item["type"] = $item["typeReference"];
                                    $item["options"] = $item["optionsString"];
                                } else {
                                    $item["options"] = "";
                                }
                            }
                        } else {}
                        $newId = (string) $this->add($parent, $parent->getClassNameFromTable($type), $item);
                        $parent->reload();
                        // save the maping for future reference
                        $this->listOfIds[$newId] = $id;
                    } else {
                        continue;
                    }
                } else {
                    // root object has no id
                    $id = $type;
                    $objType = $parent->getClassNameFromTable($type);
                    $newId = $parent->add($objType, $data[$type][0]);
                    $this->listOfIds[$newId] = $id;
                }
                
                // get the instance
                $object = $mongoObjectFactory->findObjectInstance($parent->getClassNameFromTable($type), $newId);
                // get the relations of the object
                if ($adminOnly == true) {
                    $relations = $object->getAdminRelations();
                } else {
                    $relations = $object->getOwningRelations();
                }
                $name = $object->getTableName();
                // iterate the childs
                foreach ($relations as $key => $value) {
                    // if (isset($inputArray[$key])) {
                    // foreach ($inputArray[$key] as $inputObject) {
                    $object->{$key} = array();
                    $object->update();
                    // \Application\Controller\Log::getInstance()->AddRow(' XXimportUI112 ' . json_encode($object->getIdAsString()) . ' - pathitem - ' . json_encode($key));
                    $this->importJson($dirname, $object, $id, $key, self::importFile($dirname, $key), $adminOnly, $workspaceId);
                    // }
                    // }
                }
            }
        }
        return $newId;
    }

    public function add($parent, $typeC, $json)
    {
        $laf = new \Application\Controller\MongoObjectFactory();
        $typeClass = $laf->getClassPath($typeC) . $typeC;
        
        $typeRelClass = new \ReflectionClass($typeClass);
        $nameRel = \strtolower($typeRelClass->getShortName());
        // encode the string
        $jsonString = json_encode($json);
        // expected to be an array - so create one for next
        $arguments[] = $jsonString;
        // create instance with JSON
        $class = $typeRelClass->newInstanceArgs($arguments);
        $classP = $parent->get_class_name($parent);
        $nameParentRel = \strtolower($classP);
        $nameP = \strtolower($nameParentRel) . 's';
        if ($parent->_id instanceof \MongoId) {
            $idRel['$id'] = (string) $parent->_id;
        } else {
            $idRel = $parent->_id;
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
        
        // create the parent - child relation find the object
        $refInstance = $laf->findObject($typeC, $class->_id);
        $referenceChild = [];
        $idRelChild = $refInstance->getIdAsString();
        $referenceChild['$ref'] = $name;
        // if rel odm
        if ($typeC === "Workspace") {
            $referenceChild['$id'] = new \MongoId($idRelChild);
            $referenceChild['$db'] = $laf->getDBName($typeRelClass->getShortName());
        } else {
            $referenceChild['$id'] = $idRelChild;
        }
        $parent->{$name}[] = $referenceChild;
        // update relation container
        $m = Database::getInstance();
        $db = $m->{$laf->getDBName($parent->get_class_name($parent))};
        $nameTable = $parent->getTableName();
        $collection = $db->$nameTable;
        $log = \Application\Controller\Log::getInstance();
        
        if ($parent->_id instanceof \MongoId) {} else {
            $io = (string) $parent->_id['$id'];
            if ($io != "") {
                $parent->_id = new \MongoId($io);
            }
        }
        
        // update container instance
        // $mongoId = new $id;
        $collection->update(array(
            '_id' => $parent->_id
        ), $parent, array(
            "upsert" => true
        ));
        $parent->persist();
        $parent->reload();
        // \Application\Controller\Log::getInstance()->AddRow(' referenceADD ' . json_encode($parent) . ' - pathitem - ' . json_encode($typeC));
        
        return $idRelChild;
    }

    private function exportJson($object)
    {
        $mongoObjectFactory = new MongoObjectFactory();
        $relations = $object->getOwningRelations();
        $name = $object->getTableName();
        $resultsOut = array();
        $resultsOut = $object->jsonSerialize();
        foreach ($relations as $key => $value) {
            // remove relation
            $class_name = ucfirst(substr($key, 0, strlen($key) - 1));
            $childs = $object->getInstances($class_name);
            $resultsOut[$key] = array();
            foreach ($childs as $child) {
                $resultsOut[$key][] = self::exportJson($child);
            }
        }
        return $resultsOut;
    }

    /**
     *
     * @param unknown $dirname            
     * @param unknown $parent            
     * @param unknown $typeRel:
     *            relation name - orders
     * @param unknown $objectData
     *            - new object to create where the import will be added
     */
    public function importFromFile($dirname, $parent, $typeRel, $objectData, $adminOnly = false)
    {
        $objectDataAr[$typeRel][] = $objectData;
        $newId = $this->importJson($dirname, $parent, 0, $typeRel, $objectDataAr, $adminOnly);
        $mongoObjectFactory = new MongoObjectFactory();
        $object = $mongoObjectFactory->findObjectInstance($parent->getClassNameFromTable($typeRel), $newId);
        $updatedIds = array();
        $this->resolveRererenceRelations($this->listOfIds, $object, $updatedIds);
    }

    public function resolveRererenceRelations($listOfIds, $object, $updatedIds, $admin = false)
    {
        if ($admin) {
            $relations = $object->getAdminRelations();
        } else {
            $relations = $object->getOwningRelations();
        }
        $name = $object->getTableName();
        // $resultsOut = array();
        // $resultsOut[] = $object->jsonSerialize();
        foreach ($relations as $key => $value) {
            $class_name = ucfirst(substr($key, 0, strlen($key) - 1));
            $childs = $object->getInstances($class_name);
            
            foreach ($childs as $child) {
                // $updatedIds = array();
                // if if is updatedIds
                $val = array_search($child->_id['$id'], $updatedIds);
                if ($val) {} else {
                    $refRelations = $child->getReferenceRelations();
                    // update the special reference relations also (e.g. next, prev, first, last)
                    $refRelations = array_merge($refRelations, $child->getSpecialReferenceRelations());
                    $updatedIds[] = $child->_id['$id'];
                    foreach ($refRelations as $keyRef => $valueRef) {
                        $class_nameref = ucfirst(substr($keyRef, 0, strlen($keyRef) - 1));
                        $childsref = $child->getInstances($class_nameref);
                        
                        $parentRef = $this->replaceId($listOfIds, $child, $keyRef);
                        $child->{$keyRef} = $parentRef;
                        $child->update();
                        foreach ($childsref as $childref) {
                            $this->resolveRererenceRelations($listOfIds, $childref, $updatedIds);
                        }
                        $this->resolveRererenceRelations($listOfIds, $child, $updatedIds);
                    }
                }
            }
        }
        return true;
    }

    public function replaceId($listOfIds, $object, $key)
    {
        $newRaa = array();
        if (isset($object->{$key}) && is_array($object->{$key})) {
            foreach ($object->{$key} as $item) {
                $val = array_search($item['$id'], $listOfIds);
                if ($val) {
                    $item['$id'] = $val;
                    $newRaa[] = $item;
                } else {
                    // must be root element - search for ref name e.g. 'workspaces'
                    $val = array_search($item['$ref'], $listOfIds);
                    if ($val) {
                        $item['$id'] = $val;
                        $newRaa[] = $item;
                    } else {
                        $newRaa[] = $item;
                    }
                }
            }
        }
        return $newRaa;
    }

    public function exportToFile($dirname, $object, $adminOnly = false)
    {
        $mongoObjectFactory = new MongoObjectFactory();
        if (isset($object[0])) {
            if ($adminOnly == true) {
                $relations = $object[0]->getAdminRelations();
            } else {
                $relations = $object[0]->getOwningRelations();
            }
            $name = $object[0]->getTableName();
            // $resultsOut = array();
            // $resultsOut[] = $object->jsonSerialize();
            foreach ($relations as $key => $value) {
                $childs = array();
                foreach ($object as $child) {
                    // remove relation
                    $class_name = ucfirst(substr($key, 0, strlen($key) - 1));
                    $childs = array_merge($childs, $child->getInstances($class_name));
                }
                if (isset($childs[0])) {
                    $this->write($dirname, $childs);
                    $this->exportToFile($dirname, $childs, $adminOnly);
                }
            }
        }
        return true;
    }
    
    private function importAdminUIFromFile($organization)
    {
        $data = array();
        $filename = 'export/UI_export.json' ;
        $workspace = $organization->getActiveWorkspace();
        if (file_exists($filename)) {
            $result = file_get_contents($filename);
            $data_json = json_decode($result, true);
        }
        \Application\Controller\Log::getInstance()->AddRow(' Importing  ->  data  ----------> ' . json_encode($data_json) . "   " );
    }
    

    public function exportAdminUIToFile($object)
    {
        if (isset($object[0])) {
            $path = 'export/UI_export.json' ;
            $arrayAdmin = $this->exportToArray([], $object, true);
            $this->write($path, $arrayAdmin);
            return true;
        }
        return false;
    }

    public function exportToArray($main, $object, $adminOnly = false)
    {
        $mongoObjectFactory = new MongoObjectFactory();
        $main = [];
        // $main[] = $object;
        if (isset($object[0])) {
            if ($adminOnly == true) {
                $relations = $object[0]->getAdminRelations();
            } else {
                $relations = $object[0]->getOwningRelations();
            }
            $name = $object[0]->getTableName();
            foreach ($object as $child) {
                
                $main[$child->getIdAsString()] = $child->jsonSerialize();
                foreach ($relations as $key => $value) {
                    $main[$child->getIdAsString()][$key] = [];
                    $class_name = ucfirst(substr($key, 0, strlen($key) - 1));
                    $children = $child->getInstances($class_name);
                    $ret = $this->exportToArray([], $children, $adminOnly);
                    
                    $main[$child->getIdAsString()][$key] = $ret;
                    
                    // childs = array_merge($childs, $child->getInstances($class_name));
                }
            }
        }
        return $main;
    }

    public function migrate($dirname, $object)
    {
        $mongoObjectFactory = new MongoObjectFactory();
        if (isset($object[0])) {
            $relations = $object[0]->getOwningRelations();
            // $name = $object[0]->getTableName();
            $contianerRef = array();
            
            // $resultsOut = array();
            // $resultsOut[] = $object->jsonSerialize();
            foreach ($relations as $key => $value) {
                $childs = array();
                foreach ($object as $child) {
                    // remove relation
                    $class_name = ucfirst(substr($key, 0, strlen($key) - 1));
                    
                    $childs = array_merge($childs, $child->getInstances($class_name));
                    $migrateChilds = $child->getInstances($class_name);
                    foreach ($migrateChilds as $mChilds)
                        if ($mChilds instanceof Workspace) {} else {
                            $mChilds->update();
                            $mChilds->propagate();
                            // $nameX = $mChilds->getTableName();
                            
                            if ($mChilds instanceof Indexed) {
                                $contianerRef = $child->getParent()->getRelationDetails($key);
                                $sortIndex = '';
                                if (isset($contianerRef{'sorted'})) {
                                    $sortIndex = $contianerRef{'sorted'};
                                    $firstObj = $child->reindexReference($mChilds->getClassNameFromTable($key), $sortIndex);
                                    // propagate the entire collection (expensive?)
                                    if (isset($firstObj)) {
                                        $firstObj->getPrev()->propagate();
                                        $firstObj->propagate();
                                    }
                                }
                            }
                        }
                }
                if (isset($childs[0])) {
                    $this->migrate($dirname, $childs);
                }
            }
        }
        return true;
    }

    public function cleanup($object)
    {
        $mongoObjectFactory = new MongoObjectFactory();
        if (isset($object)) {
            $relations = $object->getOwningRelations();
            
            foreach ($relations as $key => $value) {
                \Application\Controller\Log::getInstance()->AddRow(' BackupService ->  cleanup2  1 ----------> ' . $key . "   " . $value);
                
                $class_name = ucfirst(substr($key, 0, strlen($key) - 1));
                foreach ($object->{$key} as $keyRef => $valueRef) {
                    
                    \Application\Controller\Log::getInstance()->AddRow(' BackupService ->  cleanup2  2 ----------> ' . $class_name . "  " . $keyRef . "  " . json_encode($valueRef));
                    $childObject = $mongoObjectFactory->findObject($class_name, $valueRef['$id']);
                    if ($this->isEmpty($childObject)) {
                        \Application\Controller\Log::getInstance()->AddRow(' BackupService ->  cleanup2  5 OLEEEEEEEEEEE');
                        unset($object->{$key}[$keyRef]);
                    }
                }
                $object->update();
                $object->reload();
                
                $childs = $object->getInstances($class_name);
                foreach ($childs as $child) {
                    if (isset($childs)) {
                        $this->cleanup($child);
                        $child->reload();
                    }
                }
            }
        }
    }

    public function isEmpty($object)
    {
        $attr = $object->getAttributes();
        \Application\Controller\Log::getInstance()->AddRow(' isEmpty ' . json_encode($object->id));
        
        /*
         * foreach ($attr as $key => $value) {
         * \Application\Controller\Log::getInstance()->AddRow(' isEmpty ' . json_encode($key) . " " . json_encode($value));
         * if (!empty ($value)) return false;
         * }
         * return true;
         */
        
        // second alternative
        if (empty($object->_id))
            return true;
        else
            return false;
    }

    public function importObject($parentObject, $parentId, $objectName, $dataIn, $relations = [], $mappings = [])
    {
        $raws = array();
        $line = 0;
        // if( $parentObject !== "Workspace"){
        // $parentId =0;
        // }
        
        // signal to remember that " char was found
        $invertedCommas = false;
        // pass all file content character by character
        for ($i = 0; $i <= strlen($dataIn); $i ++) {
            if (isset($dataIn[$i])) {
                // if there is a " char
                if ($dataIn[$i] == "\"") {
                    // if was foudn fisrt " char
                    if (! $invertedCommas) {
                        $invertedCommas = true;
                    } else {
                        $invertedCommas = false;
                    }
                }
                
                // if there is a character which have the ASCII value equal with new line \n -> 10
                if ((ord($dataIn[$i]) == 10 || ord($dataIn[$i]) == 13) && ! $invertedCommas) {
                    $line ++;
                } else {
                    if (! isset($raws[$line])) {
                        $raws[$line] = '';
                    }
                    $raws[$line] .= $dataIn[$i];
                }
            }
        }
        
        $item = array();
        $result = array();
        $keys = array();
        $m = 0;
        $log = \Application\Controller\Log::getInstance();
        
        foreach ($raws as $key => $raw) {
            $m ++;
            $item = array();
            if (strpos($raw, ",") > 0 && strpos($raw, ",") < 40) {
                $data = explode(',', $raw);
            } else {
                $data = explode(';', $raw);
            }
            // $log->AddRow(' MAPPING LIST OF OBJECTS >-on --> ' . json_encode($data) . " == " );
            $num = count($data);
            
            /*
             * if ($m < 5) {
             * print "#".$num;
             * print_r($data);
             *
             * } elseif ($m > 5) {
             * break;
             * }
             */
            
            if ($key == 0) {
                
                for ($c = 0; $c < $num; $c ++) {
                    $log->AddRow(' MAPPING LIST OF OBJECTSX >-on --> ' . json_encode($data[$c]) . " == " . $c);
                    if (count($mappings) > 0 && $mappings != null && isset($mappings[$data[$c]])) {
                        $log->AddRow(' MAPPING LIST OF OBJECTSZ >-on --> ' . json_encode($mappings[$data[$c]]) . " == " . $c);
                        $keys[] = $mappings[$data[$c]];
                    } else {
                        $newStr = str_replace("/", "_", $data[$c]);
                        $newStr = str_replace(" ", "", $newStr);
                        $keys[] = $newStr;
                    }
                }
                $log->AddRow(' MAPPING LIST OF OBJECTSy >-on --> ' . json_encode($keys) . " == ");
            } else {
                for ($c = 0; $c < $num; $c ++) {
                    if (isset($keys[$c]) && $keys[$c] != 'recid' && $keys[$c] != 'id' && $keys[$c] != 'deleted' && $keys[$c] != 'version' && $keys[$c] != 'id_key') {
                        if ($data[$c] != null && $data[$c] != '') {
                            $item[$keys[$c]] = utf8_encode($data[$c]);
                        }
                    }
                }
                $result[] = $item;
            }
        }
        
        // print_r($result);exit;
        
        // $log->AddRow(' IMPORTING LIST OF OBJECTS >-on --> ' . json_encode($result) . " == " . $parentObject);
        $parent = false;
        try {
            
            $mongoObjectFactory = new MongoObjectFactory();
            if ($parentId == 0) {} else {
                $container = $mongoObjectFactory->findObject($parentObject, $parentId);
                $parent = false;
            }
            /*
             * $container = $mongoObjectFactory->findObject($parentObject, $parentId);
             *
             * foreach ($result as $item) {
             * $reference = array();
             * $reference['$ref'] = strtolower($parentObject) . 's';
             * $reference['$id'] = $parentId;
             * $item["parent"][] = $reference;
             * $container->add($objectName, $item);
             * }
             */
            foreach ($result as $item) {
                $relationsTemp = array_merge($relations);
                if ($parentId == 0) {
                    
                    // get parent key
                    foreach ($relations as $key => $value) {
                        $relation = array();
                        $relation = explode(",", $relationsTemp[$key]);
                        
                        if ($parentObject === $relation[0]) {
                            $parentKey = $key;
                            unset($relationsTemp[$key]);
                        }
                    }
                    // find the relation instance
                    // type = relation[0] , column=relation[2] value =$data[$c]
                    $criteria = array(
                        $relation[1] => $item[$parentKey]
                    );
                    $container = $mongoObjectFactory->findObjectInstanceByCriteria($parentObject, $criteria);
                    $log->AddRow(' IMPORTING LIST OF OBJECTS >-on --> ' . json_encode($criteria) . ' == ' . json_encode($container) . " == " . $parentKey);
                    $parent = true;
                    if (isset($container)) {
                        $parentId = $container->getIdAsString();
                    }
                } else {
                    // $container = $mongoObjectFactory->findObject($parentObject, $parentObjectId);
                }
                if (isset($container)) {
                    $reference = array();
                    $reference['$ref'] = strtolower($parentObject) . 's';
                    $reference['$id'] = $parentId;
                    if (isset($item["parent"])) {
                        $item["parent"] = array();
                    }
                    $item["parent"][] = $reference;
                    $id = $container->add($objectName, $item);
                    foreach ($item as $key => $value) {
                        // $log->AddRow(' IMPORTING LIST OF OBJECTSppp >-on --> ' . json_encode($relationsTemp) . " == " . $key);
                        
                        // $relObject1 = $mongoObjectFactory->findObject($objectName, $id);
                        // $relations = $relObject1->getExternalRelations();
                        if (array_key_exists($key, $relationsTemp) === false) {
                            // $item[$keys] = $data[$c];
                        } else {
                            // $log->AddRow(' IMPORTING LIST OF OBJECTSYYY >-on --> ' . json_encode($relationsTemp) . " == " . $id);
                            
                            $this->setRelation($relationsTemp, $key, $value, $objectName, $id);
                        }
                    }
                    if ($parent === true) {
                        $container = null;
                        $parentId = 0;
                    }
                }
            }
            // $this->flashMessenger()->addSuccessMessage('Data from file is imported !');
            return true;
        } catch (\Exception $e) {
            // $this->flashMessenger()->addErrorMessage('An error has occured !');
            return false;
        }
    }
}