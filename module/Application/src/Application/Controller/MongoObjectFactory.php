<?php
namespace Application\Controller;

use Application\DatabaseConnection\Database;
use Application\Controller\Log;
use Application\Document\Model;

class MongoObjectFactory extends ObjectFactory
{

    const MAX_UNDO_QUEUE = 6;

    public function createObject()
    {}

    /**
     * returns the database table name for an object/document
     *
     * @param string $object
     *            - Object name
     * @return mixed
     */
    public function getObjectTableName($object)
    {
        $object = $this->getClassPath($object) . $object;
        $typeClass = new \ReflectionClass($object);
        
        $reflectionMethod = new \ReflectionMethod($typeClass->name, 'getTableName');
        
        return $reflectionMethod->invoke(new $typeClass->name());
    }

    /**
     * update an object or insert it
     *
     * @param string $object
     *            - Object name that you want to save
     * @param array $data
     *            - data to be stored in table
     * @return bool
     */
    public function saveObject($object, $data)
    {
        try {
            $db = Database::getInstance();
            $table = $this->getObjectTableName($object);
            $entity = $db->$table;
            
            // if there is id of object make update
            if (isset($data['recid']) && strlen($data['recid']) > 0) {
                $id = new \MongoId($data['recid']);
                unset($data['recid']);
                $this->update($object, $id, $data);
                /*
                 * $entity->update(array(
                 * '_id' => $id
                 * ), $data, array(
                 * "upsert" => true
                 * ));
                 */
            } else {
                if (isset($data['recid'])) {
                    unset($data['recid']);
                }
                $entity->insert($data);
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function createType($type)
    {
        $collection = $db->command(array(
            "create" => $name,
            "capped" => $options["capped"],
            "size" => $options["size"],
            "max" => $options["max"],
            "autoIndexId" => $options["autoIndexId"]
        ));
    }

    /**
     * create and adds the object to an instance
     *
     * @param string $typeTo            
     * @param string $id            
     * @param string $typeRel            
     * @param array $json            
     * @return unknown
     */
    public function createAndAdd($typeTo, $id, $typeRel, $json)
    {
        
        // find the container
        $container = $this->findObject($typeTo, $id);
        // $typeClass = new \ReflectionClass($typeTo);
        // create the object
        // $idRel = $this->create($typeRel, $json);
        // $name = \strtolower($typeClass->getShortName()) . 's';
        // add relation
        // $typeRelClass = new \ReflectionClass($typeRel);
        // $nameRel = \strtolower($typeRelClass->getShortName());
        $reflectionMethod = new \ReflectionMethod($container, 'add');
        $idRel = $reflectionMethod->invoke($container, $typeRel, $json);
        // update relation container
        // $m = new \MongoClient();
        // $db = $m->{$this->getDBName()};
        // $collection = $db->$name;
        // $container->_id = new \MongoId($id);
        // update container instance
        // $mongoId = new $id;
        // $collection->update(array('_id' => $container->_id),$container,array("upsert" => true));
        return $idRel;
    }

    /**
     * create and adds the object to an instance
     *
     * @param unknown $typeTo            
     * @param unknown $id            
     * @param unknown $typeRel            
     * @param unknown $json            
     * @return unknown
     */
    public function addReference($typeTo, $id, $typeRel, $idRef)
    {
        // find the container
        $container = $this->findObject($typeTo, $id);
        $typeClass = new \ReflectionClass($typeTo);
        // find the object
        $refInstance = $this->findObject($typeRel, $idRef);
        $idRel = $refInstance->_id;
        $name = \strtolower($typeClass->getShortName()) . 's';
        // add relation
        $typeRelClass = new \ReflectionClass($typeRel);
        $nameRel = \strtolower($typeRelClass->getShortName());
        $reflectionMethod = new \ReflectionMethod($container, 'add');
        $reflectionMethod->invoke($container, $typeRel, $idRef);
        // update relation container
        // $m = new \MongoClient();
        // $db = $m->{$this->getDBName()};
        // $collection = $db->$name;
        // $container->_id = new \MongoId($id);
        // update container instance
        // $mongoId = new $id;
        // $collection->update(array('_id' => $container->_id),$container,array("upsert" => true));
        return $idRel;
    }

    /**
     *
     * @param string $id            
     * @param string $name            
     * @param array $data            
     */
    public function updateObject($id, $name, $data)
    {
        // print "ffff";
        // var_dump($data);exit;
        // save state of this before changing it
        $m = Database::getInstance();
        
        // select a database
        $typeRef = ucfirst(substr($name, 0, strlen($name) - 1));
        $db = $m->{$this->getDBName($typeRef)};
        $collection = $db->$name;
        $dataX = clone $data;
        // \Application\Controller\Log::getInstance()->AddRow(' --> UPDATEQQ ' . json_encode($dataX));
        foreach ($dataX as $key => $value) {
            if (is_array($value)) {} else {
                // \Application\Controller\Log::getInstance()->AddRow(' --> UPDATEQQ2 ' . json_encode($key));
                $dataX->{$key} = $dataX->getFormatVarialble($value, $key);
            }
        }
        // \Application\Controller\Log::getInstance()->AddRow(' --> UPDATEQQ1 ' . json_encode($dataX));
        $collection->update(array(
            '_id' => new \MongoId($id)
        ), $dataX, array(
            "upsert" => true
        ));
    }

    public function undoState()
    {
        // {"search":[{"field":"date","type":"date","operator":"is","value":"10\/4\/2016"},{"field":"deliverydate","type":"date","operator":"is","value":"10\/5\/2016"}],"searchLogic":"AND"}
        $serviceLocator = ServiceLocatorFactory::getInstance();
        $log = Log::getInstance();
        $dm = $serviceLocator->get('doctrine.documentmanager.odm_default');
        
        $identity = $_SESSION['userId'];
        $user = $dm->getRepository("\\Application\\Document\\User")->findOneBy(array(
            "id" => $identity
        ));
        $dm1 = $serviceLocator->get('doctrine.documentmanager.odm_default');
        $organization = $user->getOrganization();
        $dm1->getConfiguration()->setDefaultDB($organization->getClasspath());
        $qb = $dm1->createQueryBuilder('\\Application\\Document\\State')->sort('datetime', 'desc');
        $organization = $user->getOrganization();
        $qb->field('organization')->references($organization);
        
        // $qb->field('transactionid')->equals($transactionId);
        $settings = $qb->getQuery()->execute();
        $log = Log::getInstance();
        // $log->AddRow(" undo " );
        $i = 0;
        // if (count($settings) > self::MAX_UNDO_QUEUE) {
        foreach ($settings as $setting) {
            if ($i == 0) {
                
                $log->AddRow(" setting" . $setting->getTransactionid());
                
                $qb1 = $dm1->createQueryBuilder('\\Application\\Document\\StateData'); // ->sort('datetime', "asc");
                $qb1->field('state')->references($setting);
                $states = $qb1->getQuery()->execute();
                foreach ($states as $state) {
                    // $log->AddRow(" STATES -- " . json_encode($state));
                    $this->activateUndoState($state);
                }
                $i = $i + 1;
                $this->removeIndexUndoStack("desc", 0);
                continue;
            }
        }
        // }
        
        return true;
    }

    public function activateUndoState($state)
    {
        $typeTo = $state->getObjecttype();
        $id = $state->getObjectid();
        $json = $state->getData();
        if ($state->getType() == \Application\Document\StateData::REMOVE) {
            // add it to parent??
            $container = $this->findObject($typeTo, $id);
            ;
            //
            $container->load(json_decode($json, true), true);
            // \Application\Controller\Log::getInstance()->AddRow(' --> cX ' . json_encode($container));
            $container->_id = new \MongoId($container->_id);
            $this->save($container);
        } else {
            // find the container
            $container = $this->findObject($typeTo, $id);
            $typeClass = new \ReflectionClass($this->getClassPath($typeTo) . $typeTo);
            // find the object
            $name = \strtolower($typeClass->getShortName()) . 's';
            // add relation
            $log = Log::getInstance();
            // $log->AddRow(" Update: " .json_encode($container). ' with '.json_encode($json));
            // //$reflectionMethod = new \ReflectionMethod($container, 'updateSet');
            // $state = null;
            // $reflectionMethod->invoke($container, $json, $state);
            $container->load(json_decode($json, true), true);
            \Application\Controller\Log::getInstance()->AddRow(' --> cX ' . json_encode($container));
            $container->_id = new \MongoId($container->_id);
            $this->save($container);
        }
        // $this->load(json_decode($json, true));
    }

    public function getODMInstances($type)
    {
        $serviceLocator = ServiceLocatorFactory::getInstance();
        
        $dm = $serviceLocator->get('doctrine.documentmanager.odm_default');
        
        $identity = $_SESSION['userId'];
        $user = $dm->getRepository("\\Application\\Document\\User")->findOneBy(array(
            "id" => $identity
        ));
        $organization = $user->getOrganization();
        $qb = $dm->createQueryBuilder('\\Application\\Document\\' . $type)->sort('datetime', "desc");
        $qb->field('organization')->references($organization);
        
        // $qb->field('transactionid')->equals($transactionId);
        $settings = $qb->getQuery()->execute();
        $log = Log::getInstance();
        
        $i = 0;
        return $settings;
    }

    public function countODMInstances($type)
    {
        $serviceLocator = ServiceLocatorFactory::getInstance();
        
        $dm = $serviceLocator->get('doctrine.documentmanager.odm_default');
        
        $identity = $_SESSION['userId'];
        $user = $dm->getRepository("\\Application\\Document\\User")->findOneBy(array(
            "id" => $identity
        ));
        $organization = $user->getOrganization();
        $qb = $dm->createQueryBuilder('\\Application\\Document\\' . $type)->count();
        $qb->field('organization')->references($organization);
        
        // $qb->field('transactionid')->equals($transactionId);
        $settings = $qb->getQuery()->execute();
        $log = Log::getInstance();
        
        return $settings;
    }

    public function removeIndexUndoStack($first_or_last, $limit)
    {
        $serviceLocator = ServiceLocatorFactory::getInstance();
        
        $dm = $serviceLocator->get('doctrine.documentmanager.odm_default');
        
        $identity = $_SESSION['userId'];
        $user = $dm->getRepository("\\Application\\Document\\User")->findOneBy(array(
            "id" => $identity
        ));
        if (isset($user)) {
            $organization = $user->getOrganization();
            $dm1 = $serviceLocator->get('doctrine.documentmanager.odm_default');
            
            $dm1->getConfiguration()->setDefaultDB($organization->getClasspath());
            $qb = $dm1->createQueryBuilder('\\Application\\Document\\State')->sort('datetime', $first_or_last);
            $qb->field('organization')->references($organization);
            
            // $qb->field('transactionid')->equals($transactionId);
            $settings = $qb->getQuery()->execute();
            $log = Log::getInstance();
            
            $i = 0;
            if (count($settings) >= $limit) {
                
                foreach ($settings as $setting) {
                    
                    if ($i == 0) {
                        $log->AddRow(" REMOVE state " . $setting->getTransactionid());
                        $qb->remove()
                            ->field('transactionid')
                            ->equals($setting->getTransactionid())
                            ->getQuery()
                            ->execute();
                        $qb1 = $dm1->createQueryBuilder('\\Application\\Document\\StateData'); // ->sort('datetime', "asc");
                        $qb1->remove()
                            ->field('state')
                            ->references($setting)
                            ->getQuery()
                            ->execute();
                        
                        $i = $i + 1;
                    }
                    // $log = Log::getInstance();
                    // $log->AddRow(" REMOVE1 " . $setting->getTransactionid());
                }
            }
        }
    }

    public function getStateData($transactionId)
    {
        //
        // $this->undoState();
        $result = true;
        $log = Log::getInstance();
        $setting = null;
        if (isset($_SESSION['userId'])) {
            try {
                $serviceLocator = ServiceLocatorFactory::getInstance();
                
                $dm = $serviceLocator->get('doctrine.documentmanager.odm_default');
                
                $identity = $_SESSION['userId'];
                $user = $dm->getRepository("\\Application\\Document\\User")->findOneBy(array(
                    "id" => $identity
                ));
                $organization = $user->getOrganization();
                $dm1 = $serviceLocator->get('doctrine.documentmanager.odm_default');
                
                $dm1->getConfiguration()->setDefaultDB($organization->getClasspath());
                $qb = $dm1->createQueryBuilder('\\Application\\Document\\State');
                $qb->field('organization')->references($organization);
                $qb->field('transactionid')->equals($transactionId);
                $setting = $qb->getQuery()->getSingleResult();
            } catch (\Exception $e) {
                $result = false;
                $log = Log::getInstance();
                $log->AddRow(" Exeception : " . json_encode($e));
            }
        }
        return $setting;
    }

    public function saveStateData($transactionId, $stateData)
    {
        //
        // $this->undoState();
        $result = true;
        $log = Log::getInstance();
        if (isset($_SESSION['userId'])) {
            try {
                $serviceLocator = ServiceLocatorFactory::getInstance();
                
                $dm = $serviceLocator->get('doctrine.documentmanager.odm_default');
                if (isset($_SESSION['dbname'])) {
                    $identity = $_SESSION['dbname'];
                    
                    $organization = $dm->getRepository("\\Application\\Document\\Organization")->findOneBy(array(
                        "dbname" => $identity
                    ));
                    
                    $dm1 = $serviceLocator->get('doctrine.documentmanager.odm_default');
                    
                    $dm1->getConfiguration()->setDefaultDB($organization->getClasspath());
                    $qb = $dm1->createQueryBuilder('\\Application\\Document\\State');
                    $qb->field('organization')->references($organization);
                    $qb->field('transactionid')->equals($transactionId);
                    $setting = $qb->getQuery()->getSingleResult();
                    
                    if ($setting == null) {
                        
                        $setting = new \Application\Document\State();
                        $setting->setOrganization($organization);
                        $setting->setOrganizationId($organization->getId());
                        $setting->setTransactionid($transactionId);
                        $setting->setDatetime(new \DateTime());
                        $log->AddRow(" Save state action : ");
                        // $setting->setState(json_encode($data));
                        $dm->persist($setting);
                        $dm->flush();
                        $dm1 = $serviceLocator->get('doctrine.documentmanager.odm_default');
                        
                        $dm1->getConfiguration()->setDefaultDB($organization->getClasspath());
                        $transaction = $this->createSessionTransaction($organization);
                        // $setting->setState(json_encode($data));
                        $dm1->persist($transaction);
                        $dm1->flush();
                    }
                    // $this->balanceUndoStack("asc");
                    $serviceLocator = ServiceLocatorFactory::getInstance();
                    $dm1 = $serviceLocator->get('doctrine.documentmanager.odm_default');
                    
                    $dm1->getConfiguration()->setDefaultDB($organization->getClasspath());
                    $dm = $serviceLocator->get('doctrine.documentmanager.odm_default');
                    $qb = $dm1->createQueryBuilder('\\Application\\Document\\State');
                    $qb->field('organization')->references($organization);
                    $qb->field('transactionid')->equals($transactionId);
                    $setting = $qb->getQuery()->getSingleResult();
                    $dm = $serviceLocator->get('doctrine.documentmanager.odm_default');
                    $qb = $dm1->createQueryBuilder('\\Application\\Document\\Transaction');
                    $qb->field('organization')->references($organization);
                    $qb->field('transactionid')->equals($transactionId);
                    $transaction = $qb->getQuery()->getSingleResult();
                    // $log->AddRow(" get state action1 : " . $setting->getTransactionid());
                    if ($setting != null) {
                        $dm = $serviceLocator->get('doctrine.documentmanager.odm_default');
                        $qb = $dm1->createQueryBuilder('\\Application\\Document\\StateData');
                        $qb->field('state')->references($setting);
                        $qb->field('type')->equals($stateData->getType());
                        $qb->field('objectid')->equals($stateData->getObjectid());
                        $stateData1 = $qb->getQuery()->getSingleResult();
                        if ($stateData1 == null) {
                            // $stateData = new \Application\Document\StateData();
                            $stateData->setState($setting);
                            // $stateData->setDatetime(n);
                            $log = Log::getInstance();
                            // $log->AddRow(" Save datastate action : " .json_encode($stateData));
                            // $setting->setStateDatas($stateData);
                            $dm1->persist($stateData);
                            // $dm->persist($setting);
                            $dm1->flush();
                        }
                        if ($transaction != null) {
                            $count = $transaction->getCount();
                            $transaction->setCount($count + 1);
                            $transaction->setEnddatetime(new \DateTime());
                            $dm1->persist($transaction);
                            // $dm->persist($setting);
                            $dm1->flush();
                        }
                    }
                }
                // remove fifo
                $this->removeIndexUndoStack("asc", self::MAX_UNDO_QUEUE);
            } catch (\Exception $e) {
                $result = false;
            }
        }
        return $result;
    }

    protected function getSession()
    {
        $serviceLocator = ServiceLocatorFactory::getInstance();
        $dm = $serviceLocator->get('doctrine.documentmanager.odm_default');
        $session = $serviceLocator->get('Zend\Authentication\AuthenticationService');
        return $session;
    }

    public function getOdmUser()
    {
        $serviceLocator = ServiceLocatorFactory::getInstance();
        $dm = $serviceLocator->get('doctrine.documentmanager.odm_default');
        $session = $serviceLocator->get('Zend\Authentication\AuthenticationService');
        $identity = $session->getIdentity();
        return $user = $dm->getRepository("\\Application\\Document\\User")->findOneBy(array(
            "id" => $identity['id']
        ));
    }

    public function createSessionTransaction($organization)
    {
        $serviceLocator = ServiceLocatorFactory::getInstance();
        $dm = $serviceLocator->get('doctrine.documentmanager.odm_default');
        $identity = $_SESSION['organization'];
        
        /*
         * $organization = $dm->getRepository("\\Application\\Document\\Organization")->findOneBy(array(
         * "classpath" => $identity
         * ));
         */
        $transaction = new \Application\Document\Transaction();
        $transaction->setOrganization($organization);
        $transaction->setOrganizationId($organization->getId());
        
        $time = \DateTime::createFromFormat('U.u', sprintf('%.f', microtime(true)))->format('Y-m-d\TH:i:s.uO');
        
        $transaction->setDatetime($time);
        $transaction->setUserId($_SESSION['userId']);
        $transaction->setTransactionid($_SESSION["transaction_id"]);
        if (isset($_SESSION['transaction_name']) && isset($_SESSION['transaction_objectId']) && isset($_SESSION['transaction_type'])) {
            $transaction->setTransactionname($_SESSION['transaction_name']);
            $transaction->setTransactionobject($_SESSION['transaction_objectId']);
            $transaction->setTransactiontype($_SESSION['transaction_type']);
        }
        return $transaction;
    }

    public function getFormatVarialble($input, $key = null)
    {
        // \Application\Controller\Log::getInstance()->AddRow(' LineFORMAT ' . $key . ' value ' . json_encode($input));
        if (isset($key)) {
            if ($this->substr_startswith($key, 'id_') || $this->substr_startswith($key, 'Id_') || $this->substr_startswith($key, 'ID_')) {
                return $input;
            }
        }
        
        if (is_numeric($input)) {
            if (substr($input, 0, 1) === '0' || substr($input, 0, 1) === '+') {
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
                if ($date == false || ! (date_format($date, $format) == $input)) {} else {
                    // date("d-m-Y", strtotime($input));//
                    return new \MongoDate(strtotime($input));
                }
            }
        } else {
            // $input = trim($input);
            $formats = array(
                "d-m-Y H:i"
            );
            foreach ($formats as $format) {
                $date = \DateTime::createFromFormat($format, $input);
                if ($date == false || ! (date_format($date, $format) == $input)) {} else {
                    // date("d-m-Y", strtotime($input));//
                    return new \MongoDate(strtotime($input), 1241);
                }
            }
        }
        return $input;
    }

    public function update($typeTo, $id, $json)
    {
        try {
            // find the container
            $container = $this->findObject($typeTo, $id);
            $typeClass = new \ReflectionClass($this->getClassPath($typeTo) . $typeTo);
            // find the object
            $name = \strtolower($typeClass->getShortName()) . 's';
            // add relation
            $log = Log::getInstance();
            // $log->AddRow(" Update: " .json_encode($container). ' with '.json_encode($json));
            $reflectionMethod = new \ReflectionMethod($container, 'updateSet');
            
            $state = new \Application\Document\State();
            
            $state->setTransactionid($_SESSION["transaction_id"]);
            
            // $reflectionMethod->invoke($container, $json, $state);
            $container->beforeSet($json);
            $container->updateSet($json, $state);
            $container->afterSet($json);
            
            // $log->AddRow(" Execute Action>-" . $actionToExecute . " >-on " . json_encode($data) . " >-with " . json_encode($data));
            
            // $log->AddRow(" REFERENCEUPDATE: " .json_encode($user). ' with '.$identity);
            // $this->saveStateData($state );
            $log->AddRow(" REFERENCEUPDATE: " . json_encode($container) . ' with ' . json_encode($state));
            // update relation container
            $m = Database::getInstance();
            $db = $m->{$this->getDBName($typeTo)};
            $collection = $db->$name;
            $container->_id = new \MongoId($id);
            // update container instance
            // $mongoId = new $id;
            /*
             * $collection->update(array(
             * '_id' => $container->_id
             * ), $container, array(
             * "upsert" => true
             * ));
             */
            return $id;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function save($object)
    {
        if (isset($object->_id)) {
            $m = Database::getInstance();
            $db = $m->{$this->getDBName($object->getClassName())};
            $name = $object->getTableName();
            $collection = $db->$name;
            if ($object->_id instanceof \MongoId) {} else {
                if (is_array($object->_id)) {
                    $object->_id = new \MongoId($object->_id['$id']);
                }
            }
            $getter_names = get_class_vars(get_class($object)); // methods(get_class($this));
            
            foreach ($getter_names as $key => $value) {
                if (isset($object->{$key}) && is_scalar($object->{$key}) && $key != '_id') {
                    $log = Log::getInstance();
                    
                    $object->{$key} = $object->getFormatVarialble($object->{$key}, $key);
                }
            }
            // $log->AddRow(" Loooo: " .$key. ' = '. json_encode($object));
            
            // update container instance
            // $mongoId = new $id;
            $collection->update(array(
                '_id' => $object->_id
            ), $object, array(
                "upsert" => true
            ));
            return (string) $object->_id;
        }
        return null;
    }

    /**
     * Creates
     *
     * a single instance
     *
     * @param unknown $type            
     * @param unknown $json            
     */
    public function create($typeI, $json)
    {
        $pathT = $this->getClassPath($typeI);
        $type = $pathT . $typeI;
        // encode the string
        $jsonString = json_encode($json);
        // instantiate class type through reflexion
        $typeClass = new \ReflectionClass($type);
        // expected to be an array - so create one for next
        $arguments[] = $jsonString;
        // create instance with JSON
        $class = $typeClass->newInstanceArgs($arguments);
        // $class->setId(\uniqid());
        
        $m = Database::getInstance();
        // select a database
        $db = $m->{$this->getDBName($typeI)};
        $name = \strtolower($typeClass->getShortName()) . 's';
        $collection = $db->$name;
        $collection->insert($class);
        return $class->_id;
    }

    public function getMainTypes()
    {
        $mainTypes = array();
        $mainTypes[] = "Model";
        // $mainTypes[] = "Workspace";
        $mainTypes[] = "Organization";
        $mainTypes[] = "User";
        $mainTypes[] = "Transaction";
        $mainTypes[] = "View";
        $mainTypes[] = "Parameter";
        $mainTypes[] = "Field";
        $mainTypes[] = "Menu";
        $mainTypes[] = "Setting";
        $mainTypes[] = "UserRole";
        $mainTypes[] = "Samsarole";
        $mainTypes[] = "Masterdata";
        $mainTypes[] = "Mastertable";
        $mainTypes[] = "Component";
        $mainTypes[] = "Contextmenu";
        $mainTypes[] = "Rule";
        $mainTypes[] = "Calendar";
        $mainTypes[] = "Cronjob";
        $mainTypes[] = "Scheduler";
        $mainTypes[] = "Configuration";
        // $mainTypes[] = "Calendarday";
        $mainTypes[] = "Component";
        $mainTypes[] = "Event";
        $mainTypes[] = "Template";
        $mainTypes[] = "Map";
        $mainTypes[] = "Workspaceevents";
        $mainTypes[] = "Reference";
        $mainTypes[] = "State";
        $mainTypes[] = "StateData";
        $mainTypes[] = "Samsa";
        return $mainTypes;
    }

    public function getAdminTypes()
    {
        $mainTypes = array();
        $mainTypes[] = "User";
        $mainTypes[] = "View";
        $mainTypes[] = "Parameter";
        $mainTypes[] = "Field";
        $mainTypes[] = "Menu";
        $mainTypes[] = "Setting";
        $mainTypes[] = "UserRole";
        $mainTypes[] = "Samsarole";
        $mainTypes[] = "Masterdata";
        $mainTypes[] = "Mastertable";
        $mainTypes[] = "Component";
        $mainTypes[] = "Contextmenu";
        $mainTypes[] = "Rule";
        // $mainTypes[] = "Calendar";
        $mainTypes[] = "Cronjob";
        $mainTypes[] = "Scheduler";
        $mainTypes[] = "Configuration";
        // $mainTypes[] = "Calendarday";
        $mainTypes[] = "Event";
        $mainTypes[] = "Template";
        $mainTypes[] = "Map";
        $mainTypes[] = "Workspaceevents";
        $mainTypes[] = "Reference";
        return $mainTypes;
    }

    public function getClassPath($type = "")
    {
        $organization = "";
        $mainTypes = $this->getMainTypes();
        if (array_search($type, $mainTypes) == false) {
            if (isset($_SESSION['organization'])) {
                $organization = $_SESSION['organization'] . "\\";
            } else if (strlen($organization) < 2) {
                $sing = \Application\Document\Helper\NotificationCenter::getInstance();
                $organization = $sing->getClasspath() . "\\";
            }
        }
        
        return "\\Application\\Document\\" . $organization;
    }

    public function getDBName($type = "")
    {
        $organizationDbName = "zf2odm";
        
        $mainTypes = array();
        $mainTypes[] = "Organization";
        $mainTypes[] = "User";
        $mainTypes[] = "Setting";
        $mainTypes[] = "Role";
        $mainTypes[] = "UserRole";
        // $mainTypes[] = "Transaction";
        $mainTypes[] = "Samsa";
        // $mainTypes[] = "Samsa";
        $log = Log::getInstance();
        if (array_search($type, $mainTypes) === false) {
            if (isset($_SESSION['organization'])) {
                $organizationDbName = $_SESSION['organization'];
                
                // $log->AddRow(' --> executeGETDBNAME ' . json_encode($_SESSION['dbname']));
                if (isset($_SESSION['dbname'])) {
                    $organizationDbNameNew = $_SESSION['dbname'];
                    if (strlen($organizationDbNameNew) > 1) {
                        $organizationDbName = $organizationDbNameNew;
                    }
                }
            } else {
                // if ( $organization) < 2) {
                $sing = \Application\Document\Helper\NotificationCenter::getInstance();
                $organizationDbName = $sing->getClasspath();
                // }
            }
        }
        $log->AddRow(' --> executeGETDBNAME back ' . $type . ' = ' . json_encode($organizationDbName));
        return $organizationDbName;
    }

    public function find($type)
    {
        $m = Database::getInstance();
        // select a database
        $db = $m->{$this->getDBName($type)};
        $typeClass = new \ReflectionClass($this->getClassPath($type) . $type);
        $name = \strtolower($typeClass->getShortName()) . 's';
        // Log::_info("Find For Class - ".$name);
        $collection = $db->$name;
        $listObjectsRet = array();
        $listObjects = $collection->find(array(
            'deleted' => 0
        ));
        // \Application\Controller\Log::getInstance()->AddRow(' --> 2X '.json_encode($listObjects));
        foreach ($listObjects as $object) {
            if (isset($object)) {
                $jsonString = json_encode($object);
                $arguments = array();
                // \Application\Controller\Log::getInstance()->AddRow(' --> 2111 '.$jsonString);
                $arguments[] = $jsonString;
                $containerInstance = $typeClass->newInstanceArgs($arguments);
                //
                $listObjectsRet[] = $containerInstance->jsonSerialize();
            }
        }
        
        return $listObjectsRet;
    }

    public function remove($type, $id)
    {
        $m = Database::getInstance();
        // select a database
        $db = $m->{$this->getDBName($type)};
        $typeClass = new \ReflectionClass($this->getClassPath($type) . $type);
        $name = \strtolower($typeClass->getShortName()) . 's';
        // Log::_info("Find For Class - ".$name);
        $collection = $db->$name;
        if ($id instanceof \MongoId) {
            $id = (string) $id;
        } else if (is_array($id)) {
            $id = $id['$id'];
        }
        
        // Log::_info("Complete removing " . $id);
        
        $ret = $collection->remove(array(
            '_id' => new \MongoId($id)
        ));
        
        return $ret;
    }

    /**
     *
     * @param string $type
     *            - class name
     * @param string $id
     *            - id of object
     * @return object
     */
    public function findObject($type, $id)
    {
        $typeX = $this->getClassPath($type) . $type;
        $m = Database::getInstance();
        // select a database
        $db = $m->{$this->getDBName($type)};
        $typeClass = new \ReflectionClass($typeX);
        $name = \strtolower($typeClass->getShortName()) . 's';
        $collection = $db->$name;
        $object = $collection->findOne(array(
            '_id' => new \MongoId($id),
            'deleted' => 0
        ));
        $containerInstance = null;
        // \Application\Controller\Log::getInstance()->AddRow(' --> 2XVVXX '. " -- ".$this->getDBName($object)." -- ".$id);
        $jsonString = json_encode($object);
        $arguments[] = $jsonString;
        // \Application\Controller\Log::getInstance()->AddRow(' --> 2XVVXX '.$jsonString. " -- ".$this->getDBName($type)." -- ".$id);
        $containerInstance = $typeClass->newInstanceArgs($arguments);
        // \Application\Controller\Log::getInstance()->AddRow(' --> 2XVVXX ' . $jsonString . " -- " . json_encode($containerInstance) . " -- ");
        return $containerInstance;
    }

    public function findAllObjectJSON($type, $criteriaTo)
    {
        $typex = $this->getClassPath($type) . $type;
        $m = Database::getInstance();
        // select a database
        $db = $m->{$this->getDBName($type)};
        $typeClass = new \ReflectionClass($typex);
        $criteriaTo['deleted'] = 0;
        // \Application\Controller\Log::getInstance()->AddRow(' --> find all obj JSON1 '.json_encode($criteriaTo));
        $name = \strtolower($typeClass->getShortName()) . 's';
        $collection = $db->$name;
        // \Application\Controller\Log::getInstance()->AddRow(' --> find all obj JSON2 '.$name);
        
        $object = $collection->find($criteriaTo);
        // \Application\Controller\Log::getInstance()->AddRow(' --> find all obj JSON3 '.json_encode($object));
        
        return $object;
    }

    public function findObjectJSON($type, $id, $deleted = 0)
    {
        $typeN = $this->getClassPath($type) . $type;
        $m = Database::getInstance();
        // select a database
        $db = $m->{$this->getDBName($type)};
        $typeClass = new \ReflectionClass($typeN);
        $name = \strtolower($typeClass->getShortName()) . 's';
        $collection = $db->$name;
        $object = $collection->findOne(array(
            '_id' => new \MongoId($id),
            'deleted' => $deleted
        ));
        
        if (isset($object)) {
            $jsonString = json_encode($object);
            $arguments[] = $jsonString;
            $containerInstance = $typeClass->newInstanceArgs($arguments);
            // \Application\Controller\Log::getInstance()->AddRow(' -->findObjectJSON '.json_encode($arguments));
            
            return $containerInstance->jsonSerialize();
        }
        // \Application\Controller\Log::getInstance()->AddRow(' --> 2 '.json_encode($containerInstance->jsonSerialize()));
        return null;
    }

    public function findObjectInstanceCriteria($type, $id, $criteria)
    {
        $typeN = $this->getClassPath($type) . $type;
        $m = Database::getInstance();
        // select a database
        $db = $m->{$this->getDBName($type)};
        $typeClass = new \ReflectionClass($typeN);
        $name = \strtolower($typeClass->getShortName()) . 's';
        $collection = $db->$name;
        if ($id instanceof \MongoId) {
            $id = (string) $id;
        } else if (is_array($id)) {
            $id = $id['$id'];
        }
        $criteria['_id'] = new \MongoId($id);
        $criteria['deleted'] = 0;
        $container = array();
        $listObjects = $collection->find($criteria);
        // \Application\Controller\Log::getInstance()->AddRow(' --> 2X '.json_encode($criteria));
        foreach ($listObjects as $object) {
            if (isset($object)) {
                $jsonString = json_encode($object);
                $arguments = array();
                // \Application\Controller\Log::getInstance()->AddRow(' --> 2111 '.$jsonString);
                $arguments[] = $jsonString;
                $containerInstance = $typeClass->newInstanceArgs($arguments);
                $container[] = $containerInstance;
            }
        }
        return $container;
    }

    public function findObjectInstance($type, $id, $dbName = '')
    {
        $m = Database::getInstance();
        // select a database
        // print $type;
        if ($dbName == '') {
            $dbName = $this->getDBName($type);
        }
        $db = $m->{$dbName};
        $type = $this->getClassPath($type) . $type;
        $typeClass = new \ReflectionClass($type);
        $name = \strtolower($typeClass->getShortName()) . 's';
        $collection = $db->$name;
        if ($id instanceof \MongoId) {
            $id = (string) $id;
        } else {
            if (is_array($id)) {
                $id = $id['$id'];
            }
        }
        $object = $collection->findOne(array(
            '_id' => new \MongoId($id),
            'deleted' => 0
        ));
        
        if (isset($object)) {
            $jsonString = json_encode($object);
            $arguments[] = $jsonString;
            $containerInstance = $typeClass->newInstanceArgs($arguments);
            return $containerInstance;
        }
        return null;
    }

    public function findObjectByCriteria($type, $criteria)
    {
        $typeX = $this->getClassPath($type) . $type;
        $m = Database::getInstance();
        // select a database
        $db = $m->{$this->getDBName($type)};
        $typeClass = new \ReflectionClass($typeX);
        $name = \strtolower($typeClass->getShortName()) . 's';
        $collection = $db->$name;
        
        $criteria['deleted'] = 0;
        // \Application\Controller\Log::getInstance()->AddRow(' --> 1XV ' . $name . ' -- -' . $this->getDBName($type) . '--' . json_encode($criteria));
        
        $object = $collection->findOne($criteria);
        // \Application\Controller\Log::getInstance()->AddRow(' --> 2XV ' . $name . ' -- -' .$this->getDBName($type).'--'. json_encode($object));
        
        if (isset($object)) {
            $jsonString = json_encode($object);
            $arguments[] = $jsonString;
            $containerInstance = $typeClass->newInstanceArgs($arguments);
            return $containerInstance->jsonSerialize();
        }
        return null;
    }

    function searchInstances($type, $filterList, $ncriteria = [])
    {
        $search = [];
        // $search["search"] = [];
        $search["searchLogic"] = "AND";
        foreach ($filterList as $filterItem) {
            $searchItem = array();
            $searchItem["type"] = "string";
            $searchItem['operator'] = 'contains';
            $context = preg_replace('/[^A-Za-z0-9\-]/', '', $filterItem['value']);
            $searchItem['field'] = $filterItem['field'];
            $searchItem["value"] = $context;
            $search["search"][] = $searchItem;
        }
        // $laf->findInstancesByCriteria($classRef, $ncriteria, $references, $index, $offset, $search, $sort);
        return $this->findInstancesByCriteria($type, $ncriteria, null, 0, 0, $search);
    }

    public function findObjectInstanceByCriteria($type, $criteria)
    {
        $typeN = $this->getClassPath($type) . $type;
        $m = Database::getInstance();
        // select a database
        $db = $m->{$this->getDBName($type)};
        $typeClass = new \ReflectionClass($typeN);
        $name = \strtolower($typeClass->getShortName()) . 's';
        $collection = $db->$name;
        $criteria['deleted'] = 0;
        $object = $collection->findOne($criteria);
        if (isset($object)) {
            $jsonString = json_encode($object);
            $arguments[] = $jsonString;
            $containerInstance = $typeClass->newInstanceArgs($arguments);
            return $containerInstance;
        }
        return null;
    }

    public function findObjectsByCriteria($type, $criteria, $json = true, $search = '', $sort = '')
    {
        $typeN = $this->getClassPath($type) . $type;
        $listObjectsRet = array();
        $m = Database::getInstance();
        // select a database
        $db = $m->{$this->getDBName($type)};
        $typeClass = new \ReflectionClass($typeN);
        $name = \strtolower($typeClass->getShortName()) . 's';
        $collection = $db->$name;
        // \Application\Controller\Log::getInstance()->AddRow(' --> 2X ' . json_encode($criteria) . ' -- -' . $collection);
        $cursor = $collection->find($criteria);
        $sortArray = array();
        if ($sort != '') {
            foreach ($sort as $sortItem) {
                $order = 0;
                if ($sortItem['direction'] == 'asc') {
                    $order = 1;
                } else {
                    $order = - 1;
                }
                if ($sortItem['field'] == "recid") {
                    $sortItem['field'] = "_id";
                }
                if (isset($sortItem['limit'])) {
                    $limit = $sortItem['limit'];
                }
                $sortArray[$sortItem['field']] = $order;
            }
            // \Application\Controller\Log::getInstance()->AddRow(' --> SORTING ' . json_encode($sortArray) . ' -- -');
            if (isset($limit)) {
                $cursor->sort($sortArray)->limit($limit);
            } else {
                $cursor->sort($sortArray);
            }
        }
        
        // $cursor = $collection->find();
        foreach ($cursor as $id => $value) {
            $jsonString = json_encode($value);
            $arguments = array();
            // \Application\Controller\Log::getInstance()->AddRow(' --> 2111 '.$jsonString);
            $arguments[] = $jsonString;
            $containerInstance = $typeClass->newInstanceArgs($arguments);
            if ($json == true) {
                $listObjectsRet[] = $containerInstance->jsonSerialize();
            } else {
                $listObjectsRet[] = $containerInstance;
            }
        }
        
        return $listObjectsRet;
    }

    public function executeCmdByCriteria($type, $criteria, $cmd, $key, $search = '')
    {
        $typeN = $this->getClassPath($type) . $type;
        \Application\Controller\Log::getInstance()->AddRow(' --> executeCMD ' . json_encode($search) . ' -- -' . json_encode($cmd));
        
        $listObjectsRet = array();
        $m = Database::getInstance();
        // select a database
        $db = $m->{$this->getDBName($type)};
        $typeClass = new \ReflectionClass($typeN);
        $name = \strtolower($typeClass->getShortName()) . 's';
        $searchFilter = array();
        $collection = $db->$name;
        $searchItemFilters = array();
        $search_array = array();
        \Application\Controller\Log::getInstance()->AddRow(' --> executeCMD1 ' . json_encode($search) . ' -- -' . json_encode($cmd));
        
        if (isset($search) && isset($search['search']) && is_array($search['search'])) {
            $search_array = $this->getSearchJSON($search, $name, true);
        }
        
        \Application\Controller\Log::getInstance()->AddRow(' --> eexecuteCMD1 ' . json_encode($search_array) . ' -- -');
        
        if (count($search_array) > 0) {
            $searchFilter = array_merge($searchFilter, $search_array);
        }
        $searchFilter['$and'][] = $criteria;
        $cmdArray = array();
        $cmdArray['_id'] = 1;
        $cmdArray[$key] = array(
            $cmd => '$' . $key
        );
        $agrregArray = array();
        $matchArray = array();
        $matchArray['$match'] = $searchFilter;
        $grpArray = array();
        $grpArray['$group'] = $cmdArray;
        $agrregArray[] = $matchArray;
        $agrregArray[] = $grpArray;
        \Application\Controller\Log::getInstance()->AddRow(' --> executed mongox1 ' . json_encode($agrregArray) . ' -- -');
        
        $cursor = $collection->aggregate($agrregArray, [
            "cursor" => [
                "batchSize" => 0
            ]
        ]); // '{ $match: {'.$searchFilter.'},{ $group: { _id : null,'.$cmdArray.'}}');
            // \Application\Controller\Log::getInstance()->AddRow(' --> executed mongox ' . json_encode($cursor['result']) . ' -- -');
        if (isset($cursor['result'])) {
            $result = $cursor['result'];
            if (isset($result[0][$key])) {
                return $result[0][$key];
            }
        }
        return "0";
    }

    public function countInstancesByCriteria($type, $criteria, $search = '')
    {
        $typeN = $this->getClassPath($type) . $type;
        $listObjectsRet = array();
        $m = Database::getInstance();
        // select a database
        $db = $m->{$this->getDBName($type)};
        $typeClass = new \ReflectionClass($typeN);
        $name = \strtolower($typeClass->getShortName()) . 's';
        $searchFilter = array();
        $collection = $db->$name;
        $searchItemFilters = array();
        $search_array = array();
        if (isset($search) && isset($search['search']) && is_array($search['search'])) {
            $search_array = $this->getSearchJSON($search, $name);
        }
        \Application\Controller\Log::getInstance()->AddRow(' -->COUNT XX ' . json_encode($search_array) . ' -- -' . json_encode($criteria) . " --> " . $type);
        
        if (count($search_array) > 0) {
            $searchFilter = array_merge($searchFilter, $search_array);
        }
        $searchFilter['$and'][] = $criteria;
        
        $cursor = $collection->find($searchFilter);
        $knt = $cursor->count();
        \Application\Controller\Log::getInstance()->AddRow(' -->COUNT ' . json_encode($searchFilter) . ' -- -' . $collection . " --> " . $knt);
        return $knt;
    }

    public function getQuickInstancesCriteriaIds($typeC, $ins, $criteria, $references = null, $index = 0, $offset = 0, $search = '', $sort = '')
    {
        $listObjects = array();
        $classRef = $typeC;
        $name = \strtolower($typeC) . 's';
        
        // fake ID so empty is triggerd
        $newId = "500000000000000002000000";
        if (isset($criteria) && count($criteria) > 0) {
            
            $ncriteria = $this->createCriteriaFromArray($criteria);
        }
        \Application\Controller\Log::getInstance()->AddRow(' RESULTget3x ' . json_encode($ins));
        $in['$in'] = $ins;
        $ncriteria['_id'] = $in;
        
        $ncriteria['deleted'] = 0;
        // \Application\Controller\Log::getInstance()->AddRow(' RESULTget3 ' . json_encode($ncriteria) . $typeC);
        
        $laf = new \Application\Controller\MongoObjectFactory();
        return $laf->findInstancesByCriteria($classRef, $ncriteria, $references, $index, $offset, $search, $sort);
    }

    public function hasMultipleCriteria($criterias, $criteria)
    {
        $count = 0;
        for ($i = 0; $i <= count($criterias); $i += 2) {
            if (isset($criterias[$i]) && isset($criterias[$i + 1])) {
                $pos = strpos($criterias[$i], $criteria);
                if ($pos === false) {} else {
                    $count = $count + 1;
                }
            }
        }
        if ($count > 1) {
            return true;
        }
    }

    public function createCriteriaFromArray($criterias)
    {
        $criteriaArray = array();
        // \Application\Controller\Log::getInstance()->AddRow(' RESULTget1 ' . json_encode($criterias));
        for ($i = 0; $i <= count($criterias); $i += 2) {
            if (isset($criterias[$i]) && isset($criterias[$i + 1])) {
                
                $pos = strpos($criterias[$i], "date");
                
                if ($pos === false) {
                    if ($this->hasMultipleCriteria($criterias, $criterias[$i])) {
                        
                        $criteriaArray[$criterias[$i]]['$in'][] = $criterias[$i + 1];
                        // \Application\Controller\Log::getInstance()->AddRow(' Multiuple RESULTget1.5 ' . json_encode($criteriaArray));
                    } else {
                        if (is_bool($criterias[$i + 1])) {
                            if (isset($criterias[$i])) {
                                $criteriaArray[$criterias[$i]] = "" . $criterias[$i + 1];
                            }
                        } else {
                            $criteriaArray[$criterias[$i]] = $criterias[$i + 1];
                        }
                        // \Application\Controller\Log::getInstance()->AddRow(' RESULTget1.5 ' . json_encode($criteriaArray));
                    }
                } else {
                    $posNow = strpos($criterias[$i + 1], "now");
                    if ($posNow === false) {
                        $criteriaArray[$criterias[$i]] = new \MongoDate($criterias[$i + 1]);
                    } else {
                        $posNow = strpos($criterias[$i + 1], ">now");
                        if ($posNow === false) {
                            $today = array();
                            $format = "d-m-Y";
                            $date = strtotime($criterias[$i + 1]);
                            $dt = new \DateTime(date('Y-m-d', $date), new \DateTimeZone('UTC'));
                            $ts = $dt->getTimestamp();
                            $criteriaArray[$criterias[$i]] = new \MongoDate($ts);
                        } else {
                            $today = array();
                            $format = "d-m-Y";
                            $timeString = str_replace("min", "-", substr($criterias[$i + 1], 1));
                            // \Application\Controller\Log::getInstance()->AddRow(' RESULTget1 ' . json_encode($timeString));
                            
                            $date = strtotime($timeString);
                            $dt = new \DateTime(date('Y-m-d', $date), new \DateTimeZone('UTC'));
                            $ts = $dt->getTimestamp();
                            $today['$gte'] = new \MongoDate($ts);
                            $criteriaArray[$criterias[$i]] = $today;
                        }
                    }
                }
            }
        }
        // \Application\Controller\Log::getInstance()->AddRow(' RESULTget1.5 ' . json_encode($criteriaArray));
        return $criteriaArray;
    }

    public function findInstancesByCriteria($typeI, $criteria, $ref = null, $index = 0, $offset = 0, $search = '', $sort = '')
    {
        $type = $this->getClassPath($typeI) . $typeI;
        \Application\Controller\Log::getInstance()->AddRow(' --> findInstancesByCriteria1 ' . json_encode($typeI) . ' -- -' . json_encode($criteria));
        
        $listObjectsRet = array();
        $m = Database::getInstance();
        // select a database
        $db = $m->{$this->getDBName($typeI)};
        $typeClass = new \ReflectionClass($type);
        $name = \strtolower($typeClass->getShortName()) . 's';
        $searchFilter = array();
        $searchFilterUI = array();
        $collection = $db->$name;
        $search_array = array();
        // \Application\Controller\Log::getInstance()->AddRow(' --> findInstancesByCriteria1 search ' . json_encode($search) );
        $criteriaRes = $this->iterateCriteria($criteria, $name);
        \Application\Controller\Log::getInstance()->AddRow(' --> RESULTgetV1xx main ' . json_encode($criteriaRes));
        if (isset($search) && isset($search['search']) && is_array($search['search'])) {
            $search_array = $this->getSearchJSON($search, $name);
        }
        if (count($search_array) > 0) {
            $searchFilter = array_merge($searchFilter, $search_array);
        }
        // if($this->substr_startswith("date", $criteria[1])){
        
        // }
        $searchFilter['$and'][] = $criteriaRes;
        \Application\Controller\Log::getInstance()->AddRow(' --> findInstancesByCriteria search ' . json_encode($sort) . ' -- -' . json_encode($searchFilter));
        $cursor = $collection->find($searchFilter);
        if ($index > 0) {
            \Application\Controller\Log::getInstance()->AddRow(' --> SEARCHINGF ' . json_encode($offset) . ' -- -' . json_encode($index));
            $cursor->skip($offset);
            $cursor->limit($index);
        } else {
            $cursor->skip(0);
            $cursor->limit(0);
        }
        $limit = null;
        $sortArray = array();
        if ($sort != '') {
            foreach ($sort as $sortItem) {
                $order = 0;
                if ($sortItem['direction'] == 'asc') {
                    $order = 1;
                } else {
                    $order = - 1;
                }
                if ($sortItem['field'] == "recid") {
                    $sortItem['field'] = "_id";
                }
                if (isset($sortItem['limit'])) {
                    $limit = $sortItem['limit'];
                }
                $sortArray[$sortItem['field']] = $order;
            }
        }
        // \Application\Controller\Log::getInstance()->AddRow(' --> findInstancesByCriteria sort ' . json_encode($sortArray) . ' -- -');
        if (isset($limit)) {
            $cursor->sort($sortArray)->limit($limit);
        } else {
            $cursor->sort($sortArray);
        }
        // $cursor = $collection->find();
        
        foreach ($cursor as $id => $value) {
            \Application\Controller\Log::getInstance()->AddRow(' --> findInstancesByCriteria3 ' . $limit);
            $jsonString = json_encode($value);
            $arguments = array();
            \Application\Controller\Log::getInstance()->AddRow(' --> findInstancesByCriteria3 ' . $jsonString);
            $arguments[] = $jsonString;
            
            $containerInstance = $typeClass->newInstanceArgs($arguments);
            // $ret = array();
            //
            if (isset($ref) && is_array($ref) && ! empty($ref)) {
                $ref = array_values($ref);
                // foreach ($ref as $methodRef) {
                // $ret = $containerInstance;
                // add references if required . iterate for relations
                if ($this->substr_startswith($ref[0], 'get')) {} else if (strlen($ref[0]) > 0) {
                    $containerInstance->setReferences($ref);
                }
                // }
            }
            // TODO to review later
            // $containerInstance = $containerInstance->getFormatProperties();
            $listObjectsRet[] = $containerInstance;
        }
        
        // \Application\Controller\Log::getInstance()->AddRow(' --> findInstancesByCriteria result ' . json_encode($listObjectsRet));
        return $listObjectsRet;
    }

    public function getSearchJSON($search, $tableName)
    {
        $searchFilter = array();
        $searchFilterUI = array();
        $typeRef = ucfirst(substr($tableName, 0, strlen($tableName) - 1));
        $class = $this->getClassPath($typeRef) . $typeRef;
        
        $typeClass = new \ReflectionClass($class);
        \Application\Controller\Log::getInstance()->AddRow(' --> searchJSOP3 ' . json_encode($search));
        
        // $reflectionMethodx = new \ReflectionMethod($class, 'getRelationType');
        if (isset($search) && is_array($search)) {
            $searchA = null;
            
            if (isset($search["search"])) {
                $searchA = $search["search"];
            }
            \Application\Controller\Log::getInstance()->AddRow(' --> searchJSOP2 ' . json_encode($search));
            if (isset($searchA) && is_array($searchA)) {
                if (isset($searchA[0]["searchLogic"])) {
                    \Application\Controller\Log::getInstance()->AddRow(' --> searchJSOP1 ' . json_encode($searchA[0]));
                    foreach ($searchA as $searchAList) {
                        $searchLogic1 = "$" . strtolower($searchAList["searchLogic"]);
                        $searchA1 = $searchAList["search"];
                        $searchItemFilter = array();
                        
                        $searchFilterUI = $this->iterateSearchCriteria($searchA1, $searchLogic1, $class);
                        if (count($searchFilterUI) > 0) {
                            $searchFilter['$and'][] = $searchFilterUI;
                        }
                    }
                } else {
                    // \Application\Controller\Log::getInstance()->AddRow(' --> searchJSOP2 ' . json_encode($searchA[0]));
                    $searchLogic = "$" . strtolower($search["searchLogic"]);
                    $searchItemFilter = array();
                    
                    $searchFilterUI = $this->iterateSearchCriteria($searchA, $searchLogic, $class);
                    if (count($searchFilterUI) > 0) {
                        $searchFilter['$and'][] = $searchFilterUI;
                    }
                }
                // $searchFilter = $searchFilterUI;
            }
        }
        // print_r($searchFilter);exit;
        return $searchFilter;
    }

    public function getSamsa()
    {
        $type = "Samsa";
        // $type = $this->getClassPath($type) . $type;
        // $typeClass = new \ReflectionClass($type);
        $criteria = array(
            "name" => "SAMSA"
        );
        $samsa = $this->findInstancesByCriteria($type, $criteria);
        if (! isset($samsa) || empty($samsa) || ! isset($samsa[0]->_id)) {
            $json = array(
                "name" => "SAMSA"
            );
            $id = $this->create($type, $json);
            $samsa = $this->findInstancesByCriteria($type, $criteria);
        }
        return $samsa[0];
    }

    public function iterateSearchCriteria($searchA, $searchLogic, $class)
    {
        $searchFilterUI = array();
        foreach ($searchA as $searchItem) {
            $searchItemFilter = array();
            
            \Application\Controller\Log::getInstance()->AddRow(' --> searchJSOPN1 ' . json_encode($searchItem) . ' == ' . $searchLogic);
            if ($searchItem['type'] == 'string') {
                \Application\Controller\Log::getInstance()->AddRow(' --> searchJSOPN String' . json_encode($searchItem));
                $name = $searchItem['value'];
                $nameTok = explode(" & ", $name);
                $searchItemFilter = array();
                foreach ($nameTok as $nameT) {
                    
                    \Application\Controller\Log::getInstance()->AddRow(' --> searchoperator ' . json_encode($searchItem) . " = " . $searchLogic);
                    if (isset($searchItem['operator'])) {
                        if ($searchItem['operator'] == "begins" || $searchItem['operator'] == "ends") {
                            if ($searchItem['operator'] == "begins") {
                                $nameTTrim = trim($nameT);
                                // search all contains for the time being
                                $searchItemFilter[$searchItem['field']] = new \MongoRegex("/$nameTTrim/i"); // begins with: ("/^$name/i");
                                
                                $searchFilterUI[$searchLogic][] = $searchItemFilter;
                            } else {
                                $nameTTrim = trim($nameT);
                                // search all contains for the time being
                                $searchItemFilter[$searchItem['field']] = new \MongoRegex("/$nameTTrim$/i"); // begins with: ("/^$name/i");
                                
                                $searchFilterUI[$searchLogic][] = $searchItemFilter;
                            }
                            
                            $searchFilterUI[$searchLogic][] = $searchItemFilter;
                        } else if ($searchItem['operator'] == "contains" || $searchItem['operator'] == "is") {
                            $mongoRegex = '';
                            $nameTTrim = trim($nameT);
                            if ($searchItem['operator'] == "contains") {
                                $mongoRegex = new \MongoRegex("/^$nameTTrim/i");
                            } else {
                                $mongoRegex = $nameTTrim;
                            }
                            $nameTTrim = trim($nameT);
                            // search all contains for the time being
                            $searchItemFilter[$searchItem['field']] = $mongoRegex; // begins with: ("/^$name/i");
                            
                            $searchFilterUI[$searchLogic][] = $searchItemFilter;
                        }
                    } else {
                        $nameTTrim = trim($nameT);
                        // search all contains for the time being
                        $searchItemFilter[$searchItem['field']] = new \MongoRegex("/^$nameTTrim/i"); // begins with:$nameTTrim = trim($nameT);
                        
                        $searchFilterUI[$searchLogic][] = $searchItemFilter;
                    }
                }
            } else if ($searchItem['type'] === 'date' || $searchItem['type'] === 'datetime') {
                try {
                    $strFormat = '';
                    if ($searchItem['type'] === 'date') {
                        $format = 'mm\/dd\/yyyy';
                        $strFormat = 'd-m-Y';
                    } else {
                        $format = 'mm\/dd\/yyyy hh:ii';
                        $strFormat = 'd-m-Y H:i';
                    }
                    if ($searchItem['operator'] == "between") {
                        
                        // \Application\Controller\Log::getInstance()->AddRow(' --> executeCMD2 ' . json_encode($searchItem) . ' -- -');
                        
                        $datetimeFrom = new \DateTime($searchItem['value'][0]);
                        // $dateFromA['$gte'] = new \MongoDate($datetimeFrom->format('d-m-Y'));
                        $datetimeTo = new \DateTime($searchItem['value'][1]);
                        // $dateToA['$lte'] = new \MongoDate($datetimeTo->format('d-m-Y')); // ::createFromFormat($format, $searchItem['value']);
                        $log = Log::getInstance();
                        // $log->AddRow(" executeCMD4: " . $datetimeFrom->format('d-m-Y'));
                        $searchItemFilter[$searchItem['field']] = array(
                            
                            '$gte' => new \MongoDate(strtotime($datetimeFrom->format($strFormat))),
                            '$lte' => new \MongoDate(strtotime($datetimeTo->format($strFormat)))
                        );
                        // $searchItemFilter[$searchItem['field']][] = $dateToA;
                        $searchFilterUI[$searchLogic][] = $searchItemFilter;
                        $log->AddRow(" executeCMD5: " . json_encode($searchFilterUI));
                    } else if ($searchItem['operator'] == "less") {
                        // \Application\Controller\Log::getInstance()->AddRow(" SEARCHDATE: " .json_encode($searchItem['value']));
                        $datetimeTo = new \DateTime($searchItem['value']);
                        // \Application\Controller\Log::getInstance()->AddRow(" SEARCHDATE: " .json_encode($datetimeTo));
                        $dateToA['$lte'] = new \MongoDate(strtotime($datetimeTo->format($strFormat))); // ::createFromFormat($format, $searchItem['value']);
                        $searchItemFilter[$searchItem['field']] = $dateToA;
                        $searchFilterUI[$searchLogic][] = $searchItemFilter;
                    } else if ($searchItem['operator'] == "more") {
                        $datetimeFrom = new \DateTime($searchItem['value']);
                        $dateFromA['$gte'] = new \MongoDate(strtotime($datetimeFrom->format($strFormat)));
                        $searchItemFilter[$searchItem['field']] = $dateFromA;
                        $searchFilterUI[$searchLogic][] = $searchItemFilter;
                    } elseif ($searchItem['operator'] == "is") {
                        
                        $datetime = new \DateTime($searchItem['value']); // ::createFromFormat($format, $searchItem['value']);
                        $searchItemFilter[$searchItem['field']] = new \MongoDate(strtotime($datetime->format($strFormat)));
                        $searchFilterUI[$searchLogic][] = $searchItemFilter;
                    }
                } catch (\Exception $e) {
                    // wrong date time conversion - ignore
                    echo $e->getMessage();
                    // exit(1);
                }
            } else {
                
                $regex = array();
                
                \Application\Controller\Log::getInstance()->AddRow(' --> searchJSOPNN ' . json_encode($searchFilterUI));
                $reflectionMethod = new \ReflectionMethod($class, 'getFieldType');
                $fieldType = $reflectionMethod->invoke(null);
                $fieldFormat = null;
                if (isset($fieldType)) {
                    \Application\Controller\Log::getInstance()->AddRow(' --> searchJSOPNN1 ' . json_encode($fieldType));
                    $reflectionMethod = new \ReflectionMethod($class, 'getFieldTypeFromArray');
                    $fieldFormat = $reflectionMethod->invoke(null, $fieldType, "MASTER_DATA", $searchItem['field']);
                }
                // \Application\Controller\Log::getInstance()->AddRow(' --> searchJSOPNN2 ' . json_encode($fieldFormat));
                // $fieldFormat = $typeClass::getFieldTypeFromArray($typeClass::getFieldType(), $typeClass::MASTER_DATA, $searchItem['field']);
                \Application\Controller\Log::getInstance()->AddRow(' --> searchJSOPNN1 ' . json_encode($class));
                $reflectionMethod1 = new \ReflectionMethod($class, 'isReferenceRelation');
                $fieldFormat1 = $reflectionMethod1->invoke(null, $searchItem['field']);
                // $fieldFormat = $typeClass::getFieldTypeFromArray($typeClass::getFieldType(), $typeClass::MASTER_DATA, $searchItem['field']);
                
                if (isset($fieldFormat) && $fieldFormat) {
                    $name = $searchItem['value'];
                    $searchItemFilter = array();
                    \Application\Controller\Log::getInstance()->AddRow(' --> searchJSOPNN1 MASTER ' . json_encode($fieldFormat));
                    $nameTok = explode(" ", $name);
                    if (isset($nameTok) && count($nameTok) > 1) {
                        
                        foreach ($nameTok as $nameT) {
                            $searchFilter2 = array();
                            $searchFilter2[$searchItem['field'] . ".text"] = new \MongoRegex("/^$nameT/i");
                            $searchFilter1['$or'][] = $searchFilter2;
                        }
                    } else {
                        $searchItemFilter[$searchItem['field'] . ".text"] = new \MongoRegex("/^$name/i"); // begins with: ("/^$name/i");
                    }
                    $searchFilterUI[$searchLogic][] = $searchItemFilter;
                } else if (isset($fieldFormat1) && $fieldFormat1) {
                    $typeRef = ucfirst(substr($searchItem['field'], 0, strlen($searchItem['field']) - 1));
                    $class = $this->getClassPath($typeRef) . $typeRef;
                    \Application\Controller\Log::getInstance()->AddRow(' --> searchJSOPNN4x ' . json_encode($class));
                    
                    $reflectionMethod = new \ReflectionMethod($class, 'getPK');
                    $pkField = $reflectionMethod->invoke(null);
                    
                    \Application\Controller\Log::getInstance()->AddRow(' --> searchJSOPNN4qx ' . json_encode($pkField));
                    
                    // $searchItemFilter = array();
                    // $searchItemFilter[$searchItem['field'] . $pkField] = new \MongoRegex("/$name/i"); // begins with: ("/^$name/i");
                    
                    $m = Database::getInstance();
                    // select a database
                    $db = $m->{$this->getDBName($typeRef)};
                    $typeClass = new \ReflectionClass($class);
                    $name = $searchItem['value'];
                    $searchFilterM = array();
                    $searchFilter1 = array();
                    \Application\Controller\Log::getInstance()->AddRow(' --> searchJSOPNN4c ' . json_encode($name));
                    $nameTok = explode(" ", $name);
                    if (isset($nameTok) && count($nameTok) > 1) {
                        
                        foreach ($nameTok as $nameT) {
                            $searchFilter2 = array();
                            $searchFilter2[$pkField] = new \MongoRegex("/^$nameT/i");
                            $searchFilter1['$or'][] = $searchFilter2;
                        }
                    } else {
                        $searchFilter1[$pkField] = new \MongoRegex("/^$name/i"); // begins with: ("/^$name/i");
                                                                                     // $searchFilterM["and"][] = $searchFilter1;
                                                                                     // $searchFilter2 = array();
                                                                                     // $searchFilter2['_id'] = new \MongoId($searchItem['field'] . '.$id'); // begins with: ("/^$name/i");
                                                                                     // $searchFilterM["and"][] = $searchFilter1;
                    }
                    $tablename = $searchItem['field'];
                    \Application\Controller\Log::getInstance()->AddRow(' --> searchJSOPNN4cx ' . json_encode($searchFilter1));
                    $searchFilterUI1 = null;
                    $collection = $db->$tablename;
                    $cursorX = $collection->find($searchFilter1);
                    foreach ($cursorX as $id => $value) {
                        \Application\Controller\Log::getInstance()->AddRow(' --> searchJSOPNN4cxx ' . $id . " == " . json_encode($value));
                        $searchItemFilter = array();
                        $searchItemFilter[$searchItem['field'] . '.0.$id'] = "" . $value['_id']; // begins with: ("/^$name/i")
                        $searchFilterUI1['$or'][] = $searchItemFilter; // $searchItemFilter['id'] = 19302150 ; // begins with: ("/^$name/i");
                    }
                    if (isset($searchFilterUI1) && count($searchFilterUI1) > 1) {
                        $searchFilterUI[$searchLogic][] = $searchFilterUI1;
                    }
                } else {
                    $searchFilterUI1 = array();
                    if (is_numeric($searchItem['value'])) {
                        \Application\Controller\Log::getInstance()->AddRow(' --> searchJSOPNN numeric ' . " == " . json_encode($searchItem));
                        $nameTTrim = "" . $searchItem['value'];
                        $searchItemFilter1 = array();
                        $nameTok = explode(" ", $nameTTrim);
                        // search all contains for the time being
                        if (isset($nameTok) && count($nameTok) > 1) {
                            foreach ($nameTok as $nameT) {
                                $searchFilter2 = array();
                                $searchFilter2[$searchItem['field']] = new \MongoRegex("/$nameT/i");
                                $searchItemFilter1['$or'][] = $searchFilter2;
                            }
                        } else {
                            $searchItemFilter1[$searchItem['field']] = new \MongoRegex("/$nameTTrim" . '/i');
                        }
                        $searchFilterUI1['$or'][] = $searchItemFilter1;
                        if (isset($searchItem['operator'])) {
                            
                            if ($searchItem['operator'] == "more" || $searchItem['operator'] == "less") {
                                if ($searchItem['operator'] == "more") {
                                    $searchItemFilter[$searchItem['field']] = array(
                                        '$gte' => new \MongoInt32($searchItem['value'])
                                    );
                                } else {
                                    $searchItemFilter[$searchItem['field']] = array(
                                        '$lte' => new \MongoInt32($searchItem['value'])
                                    );
                                }
                                $searchLogic = '$or';
                                $searchFilterUI1['$or'][] = $searchItemFilter1;
                            } else if ($searchItem['operator'] == "between") {
                                $datetimeFrom = new \MongoInt32($searchItem['value'][0]);
                                $datetimeTo = new \MongoInt32($searchItem['value'][1]);
                                $searchItemFilter[$searchItem['field']] = array(
                                    '$gte' => $datetimeFrom,
                                    '$lte' => $datetimeTo
                                );
                                $searchFilterUI1['$or'][] = $searchItemFilter1;
                                // $searchFilterUI[$searchLogic][] = $searchItemFilter;
                            } else {
                                
                                \Application\Controller\Log::getInstance()->AddRow(' --> searchJSOPNNDef numeric ' . " == " . json_encode($searchItem));
                                // $where: "/^123.*/.test(this.example)"
                                // $searchItemFilter['$where'] =
                                // '/^'.$searchItem['value'].'.*/.test(this.'.$searchItem['field'].')'
                                // ;
                                $searchItemFilter[$searchItem['field']] = array(
                                    '$eq' => new \MongoInt32($searchItem['value'])
                                );
                                // $searchLogic = '$or';
                                // $searchFilterUI[$searchLogic][] = $searchItemFilter;
                                $searchFilterUI1['$or'][] = $searchItemFilter1;
                            }
                        } else {
                            $searchItemFilter[$searchItem['field']] = array(
                                '$eq' => new \MongoInt32($searchItem['value'])
                            );
                            // $searchLogic = '$or';
                            // $searchFilterUI[$searchLogic][] = $searchItemFilter;
                            $searchFilterUI1['$or'][] = $searchItemFilter;
                        }
                    } else {
                        // '/^'.$searchItem['value'].'/i'
                        $name = $searchItem['value'];
                        $nameTok = explode(" & ", $name);
                        
                        foreach ($nameTok as $nameT) {
                            $searchItemFilter = array();
                            \Application\Controller\Log::getInstance()->AddRow(' --> searchoperatorc ' . json_encode($searchItem) . " = " . $searchLogic);
                            if (isset($searchItem['operator'])) {
                                if ($searchItem['operator'] == "begins" || $searchItem['operator'] == "ends") {
                                    if ($searchItem['operator'] == "begins") {
                                        
                                        $nameTTrim = trim($nameT);
                                        $nameTok = explode(" ", $nameTTrim);
                                        // search all contains for the time being
                                        if (isset($nameTok) && count($nameTok) > 1) {
                                            foreach ($nameTok as $nameT) {
                                                $searchFilter2 = array();
                                                $searchFilter2[$searchItem['field']] = new \MongoRegex("/$nameT/i");
                                                $searchItemFilter['$or'][] = $searchFilter2;
                                            }
                                        } else {
                                            // search all contains for the time being
                                            $searchItemFilter[$searchItem['field']] = new \MongoRegex("/$nameTTrim/i"); // begins with: ("/^$name/i");
                                        }
                                        // $searchFilterUI[$searchLogic][] = $searchItemFilter;
                                    } else {
                                        $nameTTrim = trim($nameT);
                                        // search all contains for the time being
                                        $searchItemFilter[$searchItem['field']] = new \MongoRegex("/$nameTTrim$/i"); // begins with: ("/^$name/i");
                                                                                                                         
                                        // $searchFilterUI[$searchLogic][] = $searchItemFilter;
                                    }
                                    // $searchLogic = '$or';
                                    $searchFilterUI1['$or'][] = $searchItemFilter;
                                } else if ($searchItem['operator'] == "contains" || $searchItem['operator'] == "is") {
                                    $mongoRegex = '';
                                    $nameTTrim = trim($nameT);
                                    if ($searchItem['operator'] == "contains") {
                                        $mongoRegex = new \MongoRegex("/^$nameTTrim/i");
                                    } else {
                                        $mongoRegex = $nameTTrim;
                                    }
                                    $nameTTrim = trim($nameT);
                                    // search all contains for the time being
                                    $searchItemFilter[$searchItem['field']] = $mongoRegex; // begins with: ("/^$name/i");
                                    $searchFilterUI1['$or'][] = $searchItemFilter;
                                    // $searchFilterUI[$searchLogic][] = $searchItemFilter;
                                }
                            } else {
                                $nameTTrim = trim($nameT);
                                // search all contains for the time being
                                $searchItemFilter[$searchItem['field']] = new \MongoRegex("/$nameTTrim/i"); // begins with:$nameTTrim = trim($nameT);
                                $searchFilterUI1['$or'][] = $searchItemFilter;
                                // $searchFilterUI[$searchLogic][] = $searchItemFilter;
                            }
                        }
                    }
                    // search all contains for the time being
                    //
                    $searchFilterUI['$or'][] = $searchFilterUI1;
                }
            }
            // $searchFilterUI[$searchLogic][] = $searchItemFilter;
        }
        \Application\Controller\Log::getInstance()->AddRow(' --> searchJSOPNN4qxw ' . json_encode($searchFilterUI));
        
        return $searchFilterUI;
    }

    public function iterateCriteria($criterias, $tableName)
    {
        $retCriterias = [];
        $typeRef = ucfirst(substr($tableName, 0, strlen($tableName) - 1));
        $class = $this->getClassPath($typeRef) . $typeRef;
        // \Application\Controller\Log::getInstance()->AddRow(' --> RESULTgetV1xx ' . json_encode($criterias));
        // \Application\Controller\Log::getInstance()->AddRow(' --> RESULTgetV1xx ' . json_encode($class));
        foreach ($criterias as $criteriaKey => $criteriaValue) {
            // \Application\Controller\Log::getInstance()->AddRow(' --> RESULTgetV1xxxx1 ' . json_encode($criteriaKey));
            $reflectionMethod = new \ReflectionMethod($class, 'getFieldType');
            $fieldType = $reflectionMethod->invoke(null);
            \Application\Controller\Log::getInstance()->AddRow(' --> searchJSOPNN1 ' . json_encode($fieldType));
            $reflectionMethod = new \ReflectionMethod($class, 'getFieldTypeFromArray');
            $fieldFormat = $reflectionMethod->invoke(null, $fieldType, "MASTER_DATA", $criteriaKey);
            // \Application\Controller\Log::getInstance()->AddRow(' --> searchJSOPNN2 ' . json_encode($fieldFormat));
            // $fieldFormat = $typeClass::getFieldTypeFromArray($typeClass::getFieldType(), $typeClass::MASTER_DATA, $searchItem['field']);
            
            if (isset($fieldFormat) && $fieldFormat) {
                \Application\Controller\Log::getInstance()->AddRow(' --> RESULTgetV1xx $fieldFormat ' . json_encode($criterias));
                $retCriterias[$criteriaKey . ".text"] = new \MongoRegex("/$criteriaValue/i"); // begins with: ("/^$name/i");
            } else {
                $posId = strpos(json_encode($criteriaValue), "_id");
                // \Application\Controller\Log::getInstance()->AddRow(' --> RESULTgetV1xx $$posId ' . json_encode($posId));
                if (($posId === true) && is_array($criteriaValue)) {
                    $retCriterias[$criteriaKey] = $criteriaValue;
                } else {
                    if (is_float($criteriaValue) || is_numeric($criteriaValue)) {
                        // \Application\Controller\Log::getInstance()->AddRow(' --> RESULTgetV1xx $$float ' . json_encode($criteriaValue));
                        $retCriterias[$criteriaKey] = new \MongoInt32($criteriaValue);
                    } else {
                        $pos = strpos(json_encode($criteriaValue), "sec");
                        // \Application\Controller\Log::getInstance()->AddRow(' --> RESULTgetV1xx $$pos ' . json_encode($pos));
                        if ($pos === true) {
                            $retCriterias[$criteriaKey] = new \MongoRegex("/^$criteriaValue/i");
                        } else {
                            // \Application\Controller\Log::getInstance()->AddRow(' --> RESULTgetV1xx good ' . json_encode($criteriaValue));
                            $retCriterias[$criteriaKey] = $criteriaValue;
                        }
                    }
                }
            }
        }
        // \Application\Controller\Log::getInstance()->AddRow(' --> RESULTgetV1xx end ' . json_encode($retCriterias));
        return $retCriterias;
    }

    public function findInstanceByPK($type, $pkValue)
    {
        \Application\Controller\Log::getInstance()->AddRow(' --> findInstanceByPK ' . json_encode($type));
        // $typeClass = new \ReflectionClass($type);
        $reflectionMethod = new \ReflectionMethod($type, "getPK");
        $pkName = $reflectionMethod->invoke(null, null);
        $criteria = array(
            $pkName => $pkValue
        );
        $class = explode('\\', $type);
        $typeC = $class[count($class) - 1];
        
        $foundPk = $this->findInstancesByCriteria($typeC, $criteria);
        if (isset($foundPk) && sizeof($foundPk) > 0) {
            return $foundPk[0];
        }
        return null;
    }

    public function findSubset($type, $key, $value)
    {
        $type = $this->getClassPath($type) . $type;
        $m = Database::getInstance();
        // select a database
        $db = $m->{$this->getDBName()};
        $typeClass = new \ReflectionClass($type);
        $name = \strtolower($typeClass->getShortName()) . 's';
        $collection = $db->$name;
        $subSet = $collection->find(array(
            $key => $value
        ));
        return $subSet;
    }

    function substr_startswith($haystack, $needle)
    {
        return substr($haystack, 0, strlen($needle)) === $needle;
    }
}

?>