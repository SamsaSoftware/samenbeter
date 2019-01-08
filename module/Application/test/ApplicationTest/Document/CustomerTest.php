<?php
namespace ApplicationTest\Document;

use \Application\Service\PDF\PDFMerger;
use \Application\Service\Daemon\ParallelTasks;
use \Application\Controller\MongoObjectFactory;
use Zend\ServiceManager\ServiceManager;
use Zend\Mvc\Service\ServiceManagerConfig;

/**
 * Description of AuthControllerTest
 *
 * @author mihai.coditoiu
 */
class CustomerTest extends \PHPUnit_Framework_TestCase
{

    protected $traceError = true;

    public function setUp()
    {
        static::init();
        $sing = \Application\Document\Helper\NotificationCenter::getInstance();
        $sing->getInstance()->setClasspath("Veco");
        parent::setUp();
    }

    protected static function findParentPath($path)
    {
        $dir = __DIR__;
        $previousDir = '.';
        while (! is_dir($dir . '/' . $path)) {
            $dir = dirname($dir);
            if ($previousDir === $dir) {
                return false;
            }
            $previousDir = $dir;
        }
        return $dir . '/' . $path;
    }

    public static function init()
    {
        $zf2ModulePaths = array(
            dirname(dirname("/Apps/projectMihai"))
        );
        if (($path = static::findParentPath('vendor'))) {
            $zf2ModulePaths[] = "/Apps/projectMihai/vendor";
        }
        if (($path = static::findParentPath('module')) !== $zf2ModulePaths[0]) {
            $zf2ModulePaths[] = $path;
        }
        
        // static::initAutoloader();
        
        // use ModuleManager to load this module and it's dependencies
        $config = array(
            'module_listener_options' => array(
                'module_paths' => $zf2ModulePaths
            ),
            'modules' => array(
                'Application',
                'Chat',
                'DoctrineModule',
                'DoctrineMongoODMModule'
            )
        );
        
        $serviceManager = new ServiceManager(new ServiceManagerConfig());
        $serviceManager->setService('ApplicationConfig', $config);
        $serviceManager->get('ModuleManager')->loadModules();
        \Application\Controller\ServiceLocatorFactory::setInstance($serviceManager);
        // static::$serviceManager = $serviceManager;
    }

    public function testPersistData()
    {}

    public function tesstbDocPDF()
    {
        $pdf = new PDFMerger();
        
        $pdf->addPDF(getcwd() . '/samplepdf/a1.pdf')
            ->addPDF(getcwd() . '/samplepdf/a2.pdf')
            ->merge('file', getcwd() . '/samplepdf/TEST2v.pdf');
    }

    public function tessstRegression()
    {
        $mObj = new MongoObjectFactory();
        $object = $mObj->findInstanceByPK("Application\\Document\\Autoplan\\Workspace", "default");
    }

    public function testRemoveData()
    {
        $mObj = new MongoObjectFactory();
        
        $type = 'Workspace';
        $listObjectsRet = $mObj->find($type);
        $id = (string) $listObjectsRet[0]['_id'];
        $listWObject = $mObj->findObject($type, $id);
        $listWObject->deleteAdminUI();
    }

    public function tesatRares()
    {
        $mObj = new MongoObjectFactory();
        
        $type = 'Workspace';
        $listObjectsRet = $mObj->find($type);
        $id = (string) $listObjectsRet[0]['_id'];
        $listWObject = $mObj->findObject($type, $id);
        
        $dateX = new \DateTime();
        $type = 'Nwcustomer';
        $listObjectsRet = $mObj->find($type);
        $id = (string) $listObjectsRet[0]['_id'];
        $lisEmpObject = $mObj->findObject($type, $id);
        $dateX = new \DateTime();
        
        $dateX = new \DateTime();
        $type = 'Nwplannedorder';
        $listObjectsRet = $mObj->find($type);
        $id = (string) $listObjectsRet[2]['_id'];
        $lisPOObject = $mObj->findObject($type, $id);
        $dateX = new \DateTime();
        $lisPOObject->planOnMachine();
    }

    public function tesstRares()
    {
        $mObj = new MongoObjectFactory();
        
        $type = 'Workspace';
        $listObjectsRet = $mObj->find($type);
        $id = (string) $listObjectsRet[0]['_id'];
        $listWObject = $mObj->findObject($type, $id);
        
        $dateX = new \DateTime();
        $type = 'Pnaemployee';
        $listObjectsRet = $mObj->find($type);
        $id = (string) $listObjectsRet[14]['_id'];
        $lisEmpObject = $mObj->findObject($type, $id);
        $dateX = new \DateTime();
        // $mSLotEmp = $lisEmpObject->getEmployeeMonthlySlot($dateX->format("d-m-Y"));
        // $mSLotEmp->getCountShift1();
        // $aa = $mSLotEmp->getPathReferences('getParent.pnacolorcode');
        // $aa = $mSLotEmp->checkEmployeeHolidays();
        
        $listMObject = $listWObject->getOrCreateMonthlySlot($dateX->format("d-m-Y"));
        // $listMObject->cleanMonth();
        // $listMObject->autoplanFree();
        $listMObject->autoplan();
        // $listMObject = $listWObject->getOrCreateMonthlySlot($dateX->format("d-m-Y"));
        
        // $listMObject->autoplanFree();
        // $dateFrom = $listObject->firstDayOfMonth($dateX->format("d-m-Y"));
        // $dateTo = $listObject->lastDayOfMonth($dateX->format("d-m-Y"));
        
        // $listObject->getShiftsTypeInInterval($dateFrom->format("d-m-Y"), $dateTo->format("d-m-Y"), "2");
        // $listObject->autoplanSlots(date("d-m-Y"));
    }

    public function taestExecute()
    {
        $s = "{\"org\": \"Organization\", \"channel_name\" : \"Channel-test\"}";
        $a = json_decode($s, true);
        
        $mObj = new MongoObjectFactory();
        $type = 'Otlocation';
        $listObjectsRet = $mObj->find($type);
        $id = (string) $listObjectsRet[0]['_id'];
        $listObject = $mObj->findObject($type, $id);
        $veh = $listObject->getCloseByVechicles();
        
        $type = 'Workspace';
        $listObjectsRet = $mObj->find($type);
        $id = (string) $listObjectsRet[0]['_id'];
        $listObject = $mObj->findObject($type, $id);
        $aa = $this->cgtScheduledJobs($listObject);
        
        // $arrayLonLat = $listObject->getGeoLocation('ranselberg 24 veldhoven');
        $distances = array();
        
        // 16.538048,80.613266|16.568048,80.613266
        $origins[] = '16.538048,80.613266';
        $origins[] = '16.568048,80.613266';
        $origins[] = '16.569048,80.613266';
        $origins[] = '16.569748,80.613266';
        $dest = [];
        // 23.0225,72.5714|16.538048,81.613266
        $dest[0]['geocode'] = '16.568048,80.613266';
        $dest[0]['capacity'] = '1';
        $dest[1]['geocode'] = '16.569048,80.613266';
        $dest[1]['capacity'] = '2';
        $dest[2]['geocode'] = '16.569148,80.613266';
        $dest[2]['capacity'] = '3';
        // /$dest[] = '16.569048,80.613266';
        // $dest[] = '16.569748,80.613266';
        
        $mainOrigin = '51.4543606,5.399994';
        // $retM = $listObject->getDistanceMatrix($origins, $dest);
        
        $retM = json_decode('{"51.4543606,5.399994":{"51.4447499,5.4103075":{"distance":{"value":2355,"text":"2.4 km"},"duration":{"value":327,"text":"5 mins"},"capacity":{"value":1,"text":""}},"51.4448159,5.4102129":{"distance":{"value":2345,"text":"2.3 km"},"duration":{"value":317,"text":"5 mins"},"capacity":{"value":1,"text":""}},"51.444829,5.4104602":{"distance":{"value":2353,"text":"2.4 km"},"duration":{"value":318,"text":"5 mins"},"capacity":{"value":1,"text":""}},"51.44482,5.4104746":{"distance":{"value":2353,"text":"2.4 km"},"duration":{"value":318,"text":"5 mins"},"capacity":{"value":1,"text":""}},"51.444793,5.4105033":{"distance":{"value":2353,"text":"2.4 km"},"duration":{"value":318,"text":"5 mins"},"capacity":{"value":1,"text":""}}},"51.4447499,5.4103075":{"51.4447499,5.4103075":{"distance":{"value":0,"text":"1 m"},"duration":{"value":0,"text":"1 min"},"capacity":{"value":1,"text":""}},"51.4448159,5.4102129":{"distance":{"value":10,"text":"10 m"},"duration":{"value":11,"text":"1 min"},"capacity":{"value":1,"text":""}},"51.444829,5.4104602":{"distance":{"value":0,"text":"1 m"},"duration":{"value":0,"text":"1 min"},"capacity":{"value":1,"text":""}},"51.44482,5.4104746":{"distance":{"value":1,"text":"1 m"},"duration":{"value":0,"text":"1 min"},"capacity":{"value":1,"text":""}},"51.444793,5.4105033":{"distance":{"value":5,"text":"5 m"},"duration":{"value":1,"text":"1 min"},"capacity":{"value":1,"text":""}}},"51.4448159,5.4102129":{"51.4447499,5.4103075":{"distance":{"value":10,"text":"10 m"},"duration":{"value":10,"text":"1 min"},"capacity":{"value":1,"text":""}},"51.4448159,5.4102129":{"distance":{"value":0,"text":"1 m"},"duration":{"value":0,"text":"1 min"},"capacity":{"value":1,"text":""}},"51.444829,5.4104602":{"distance":{"value":8,"text":"8 m"},"duration":{"value":1,"text":"1 min"},"capacity":{"value":1,"text":""}},"51.44482,5.4104746":{"distance":{"value":8,"text":"8 m"},"duration":{"value":1,"text":"1 min"},"capacity":{"value":1,"text":""}},"51.444793,5.4105033":{"distance":{"value":8,"text":"8 m"},"duration":{"value":1,"text":"1 min"},"capacity":{"value":1,"text":""}}},"51.444829,5.4104602":{"51.4447499,5.4103075":{"distance":{"value":0,"text":"1 m"},"duration":{"value":0,"text":"1 min"},"capacity":{"value":1,"text":""}},"51.4448159,5.4102129":{"distance":{"value":8,"text":"8 m"},"duration":{"value":1,"text":"1 min"},"capacity":{"value":1,"text":""}},"51.444829,5.4104602":{"distance":{"value":0,"text":"1 m"},"duration":{"value":0,"text":"1 min"},"capacity":{"value":1,"text":""}},"51.44482,5.4104746":{"distance":{"value":0,"text":"1 m"},"duration":{"value":0,"text":"1 min"},"capacity":{"value":1,"text":""}},"51.444793,5.4105033":{"distance":{"value":0,"text":"1 m"},"duration":{"value":0,"text":"1 min"},"capacity":{"value":1,"text":""}}},"51.44482,5.4104746":{"51.4447499,5.4103075":{"distance":{"value":1,"text":"1 m"},"duration":{"value":0,"text":"1 min"},"capacity":{"value":1,"text":""}},"51.4448159,5.4102129":{"distance":{"value":8,"text":"8 m"},"duration":{"value":1,"text":"1 min"},"capacity":{"value":1,"text":""}},"51.444829,5.4104602":{"distance":{"value":0,"text":"1 m"},"duration":{"value":0,"text":"1 min"},"capacity":{"value":1,"text":""}},"51.44482,5.4104746":{"distance":{"value":0,"text":"1 m"},"duration":{"value":0,"text":"1 min"},"capacity":{"value":1,"text":""}},"51.444793,5.4105033":{"distance":{"value":0,"text":"1 m"},"duration":{"value":0,"text":"1 min"},"capacity":{"value":1,"text":""}}},"51.444793,5.4105033":{"51.4447499,5.4103075":{"distance":{"value":5,"text":"5 m"},"duration":{"value":1,"text":"1 min"},"capacity":{"value":1,"text":""}},"51.4448159,5.4102129":{"distance":{"value":8,"text":"8 m"},"duration":{"value":1,"text":"1 min"},"capacity":{"value":1,"text":""}},"51.444829,5.4104602":{"distance":{"value":0,"text":"1 m"},"duration":{"value":0,"text":"1 min"},"capacity":{"value":1,"text":""}},"51.44482,5.4104746":{"distance":{"value":0,"text":"1 m"},"duration":{"value":0,"text":"1 min"},"capacity":{"value":1,"text":""}},"51.444793,5.4105033":{"distance":{"value":0,"text":"1 m"},"duration":{"value":0,"text":"1 min"},"capacity":{"value":1,"text":""}}}}', true);
        
        $optR = $listObject->getOptimalRoute($mainOrigin, $retM, "duration", 1000);
        
        $views = $listObject->getInstances("View");
        $log = \Application\Controller\Log::getInstance();
        // $log->addRow(" OO" . json_encode($views) . ' --- ');
        $mainTypes = array();
        
        $mainTypes[] = "Views";
        $mainTypes[] = "Workspaces";
        $mainTypes[] = "ContextMenuView";
        $mainTypes[] = "Templates";
        $mainTypes[] = "GridsView";
        $mainTypes[] = "ContextMenuView";
        $mainTypes[] = "CalendarView";
        $mainTypes[] = "Menus";
        $mainTypes[] = "MasterView";
        $mainTypes[] = "ChatView";
        $mainTypes[] = "Fields";
        foreach ($views as $view) {
            if (array_search($view->{'title'}, $mainTypes)) {
                $listObject->remove($view);
            }
        }
        $menus = $listObject->getInstances("Menu");
        $log = \Application\Controller\Log::getInstance();
        // $log->addRow(" OO".json_encode($views). ' --- ');
        foreach ($menus as $menu) {
            if (array_search($menu->{'text'}, $mainTypes)) {
                $listObject->remove($menu);
            }
        }
        
        $type = 'Customer';
        $listObjectsRet = $mObj->find($type);
        $id = (string) $listObjectsRet[0]['_id'];
        $listObject = $mObj->findObject($type, $id);
        
        $collection[] = $listObject;
        
        $this->execute($collection);
        $i = $listObject->execute('name', '$this->name=\'test\';', array());
        print_r($listObject->name);
    }

    public function cgtScheduledJobs($workspace)
    {
        $typeName = "Cronjob";
        $methodsRef = "getCronjob";
        $paramValues = array();
        $arrS = array();
        $arrS["field"] = "datetime";
        $arrS["type"] = "datetime";
        $arrS["operator"] = "less";
        
        $format = 'd-m-Y';
        $dateStart = new \DateTime();
        $dateStartF = $dateStart->format("d-m-Y H:i"); // \DateTime::createFromFormat($format,$dateStart)
        $arrS["value"] = $dateStartF;
        $arraySearch["search"][] = $arrS;
        
        $arrS = array();
        $arrS["field"] = "status";
        $arrS["value"] = "scheduled";
        $arraySearch["search"][] = $arrS;
        $arraySearch["searchLogic"] = "AND";
        // add order by
        $sort = array();
        $sortF = array();
        $sortF["field"] = "datetime";
        $sortF["direction"] = "asc";
        $sort[] = $sortF;
        $resultRef = $workspace->getInstancesReference($methodsRef, $arraySearch, $sort);
        return $resultRef;
    }

    public function teSstExecuteOrder()
    {
        $mObj = new MongoObjectFactory();
        $type = 'Processorder';
        $listObjectsRet = $mObj->find($type);
        $id = (string) $listObjectsRet[0]['_id'];
        $listObject = $mObj->findObject($type, $id);
        
        $collection[] = $listObject;
        // $date = new \DateTime("31/12/20015");
        $this->execute($collection);
        $i = $listObject->evaluate('$this->formatDateTime($this->startdate, \"U\");');
        print_r($listObject->name);
    }

    public function tSestDelete()
    {
        $mObj = new MongoObjectFactory();
        $type = 'Customer';
        $listObjectsRet = $mObj->find($type);
        $id = (string) $listObjectsRet[0]['_id'];
        $listObject = $mObj->findObject($type, $id);
        $date1 = new \DateTime();
        $typeRefO = 'Order';
        $data = array(
            "number" => 666,
            "title" => "that666",
            "text" => "who666",
            "cost" => 8,
            "date" => $date1->format("d-m-Y")
        );
        
        $returnOrder = $mObj->createAndAdd($type, (string) $id, $typeRefO, $data);
        
        $order = $mObj->findObject($typeRefO, (string) $returnOrder);
        $order->reload();
        
        $typeRef = 'Orderline';
        $data = array(
            "serial" => "AA666XX",
            "amount" => 366641,
            "profit" => 4,
            "avdate" => $date1->format("d-m-Y")
        );
        $returnOrderLine0 = $mObj->createAndAdd($typeRefO, (string) $returnOrder, $typeRef, $data);
        
        $typeRef = 'Orderline';
        $data = array(
            "serial" => "AA666",
            "amount" => 36664,
            "profit" => 1,
            "avdate" => $date1->format("d-m-Y")
        );
        $returnOrderLine2 = $mObj->createAndAdd($typeRefO, (string) $returnOrder, $typeRef, $data);
        
        $typeRef = 'Orderline';
        $data = array(
            "serial" => "AA6662",
            "amount" => 366642,
            "profit" => 2,
            "avdate" => $date1->format("d-m-Y")
        );
        $returnOrderLine = $mObj->createAndAdd($typeRefO, (string) $returnOrder, $typeRef, $data);
        
        $typeRef = 'Orderline';
        $data = array(
            "serial" => "AA66621",
            "amount" => 3666421,
            "profit" => 21,
            "avdate" => $date1->format("d-m-Y")
        );
        $returnOrderLine2 = $mObj->createAndAdd($typeRefO, (string) $returnOrder, $typeRef, $data);
        
        $normalslot_type[] = array(
            "id" => "7",
            "text" => "Weekly"
        );
        
        $normalslot_type1[] = array(
            "id" => "4",
            "text" => "Do"
        );
        
        $typeRefP = 'Processorder';
        $data = array(
            "color" => "re6666d",
            "startdate" => "01-03-2016",
            "enddate" => "30-03-2016",
            "recurrenceRule" => $normalslot_type,
            "weekRule" => $normalslot_type1,
            "number" => 14666
        );
        $returnProcOrder = $mObj->createAndAdd($typeRefO, (string) $returnOrder, $typeRefP, $data);
        
        $reference = array();
        $reference['$ref'] = "orderlines";
        $reference['$id'] = (string) $returnOrderLine;
        $procorder = $mObj->findObject($typeRef, (string) $returnProcOrder);
        
        // $object->{'views'}[] = $reference;
        $procorder->addReferenceObject("orderlines", $reference);
        
        $returnOrderlineObj = $mObj->findObject($typeRef, $returnOrderLine0);
        $returnOrderObj = $mObj->findObject($typeRefO, $returnOrder);
        $returnOrderObj->remove($returnOrderlineObj);
        $returnOrderObj = $mObj->findObject($typeRefO, $returnOrder);
        // $returnOrderObj = $mObj->findObject($typeRefO, $returnOrder);
        
        $testuu = $listObject->getLastOrders();
        $listObject->remove($returnOrderObj);
    }

    public function tSestCompleteDelete()
    {
        $mObj = new MongoObjectFactory();
        $type = 'Customer';
        $listObjectsRet = $mObj->find($type);
        $id = (string) $listObjectsRet[0]['_id'];
        $listObject = $mObj->findObject($type, $id);
        $date1 = new \DateTime();
        $typeRefO = 'Order';
        $data = array(
            "number" => 666,
            "title" => "toremovemain",
            "text" => "toremove",
            "cost" => 8,
            "date" => $date1->format("d-m-Y")
        );
        
        $returnOrder = $mObj->createAndAdd($type, (string) $id, $typeRefO, $data);
        
        $order = $mObj->findObject($typeRefO, (string) $returnOrder);
        $order->reload();
        
        $typeRef = 'Orderline';
        $data = array(
            "serial" => "toremoveXX",
            "amount" => 366641,
            "profit" => 4,
            "avdate" => $date1->format("d-m-Y")
        );
        $returnOrderLine0 = $mObj->createAndAdd($typeRefO, (string) $returnOrder, $typeRef, $data);
        
        $typeRef = 'Orderline';
        $data = array(
            "serial" => "toremove1xxx",
            "amount" => 36664,
            "profit" => 1,
            "avdate" => $date1->format("d-m-Y")
        );
        $returnOrderLine2 = $mObj->createAndAdd($typeRefO, (string) $returnOrder, $typeRef, $data);
        
        $typeRef = 'Orderline';
        $data = array(
            "serial" => "toremove2xxxx",
            "amount" => 366642,
            "profit" => 2,
            "avdate" => $date1->format("d-m-Y")
        );
        $returnOrderLine = $mObj->createAndAdd($typeRefO, (string) $returnOrder, $typeRef, $data);
        
        $typeRef = 'Orderline';
        $data = array(
            "serial" => "toremove3xxxx",
            "amount" => 3666421,
            "profit" => 21,
            "avdate" => $date1->format("d-m-Y")
        );
        $returnOrderLine2 = $mObj->createAndAdd($typeRefO, (string) $returnOrder, $typeRef, $data);
        
        $normalslot_type[] = array(
            "id" => "7",
            "text" => "Weekly"
        );
        
        $normalslot_type1[] = array(
            "id" => "4",
            "text" => "Do"
        );
        
        $typeRefP = 'Processorder';
        $data = array(
            "color" => "toremovePO1xxxx",
            "startdate" => "01-03-2016",
            "enddate" => "30-03-2016",
            "recurrenceRule" => $normalslot_type,
            "weekRule" => $normalslot_type1,
            "number" => 14666
        );
        $returnProcOrder = $mObj->createAndAdd($typeRefO, (string) $returnOrder, $typeRefP, $data);
        
        $reference = array();
        $reference['$ref'] = "orderlines";
        $reference['$id'] = (string) $returnOrderLine;
        $procorder = $mObj->findObject($typeRef, (string) $returnProcOrder);
        
        // $object->{'views'}[] = $reference;
        $procorder->addReferenceObject("orderlines", $reference);
        
        $returnOrderlineObj = $mObj->findObject($typeRef, $returnOrderLine0);
        $returnOrderObj = $mObj->findObject($typeRefO, $returnOrder);
        $returnOrderObj->remove($returnOrderlineObj);
        $returnOrderObj = $mObj->findObject($typeRefO, $returnOrder);
        // $returnOrderObj = $mObj->findObject($typeRefO, $returnOrder);
        
        $testuu = $listObject->getLastOrders();
        $listObject->remove($returnOrderObj, false);
    }

    private function execute($collection)
    {
        foreach ($collection as $collectionItem) {
            print_r($collectionItem);
        }
    }
}
