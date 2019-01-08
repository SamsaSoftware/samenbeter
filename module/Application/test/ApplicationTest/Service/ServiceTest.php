<?php
namespace AuthenticationTest\Service;

use \Application\Service\ReportingService;
use \Application\Controller\MongoObjectFactory;
use Zend\Mvc\Service\ServiceManagerConfig;
use Zend\ServiceManager\ServiceManager;

/**
 * Description of AuthControllerTest
 */
class ServiceTest extends \PHPUnit_Framework_TestCase
{

    protected $traceError = true;

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
        
        //static::initAutoloader();
        
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
        //static::$serviceManager = $serviceManager;
    }

    public function setUp()
    {
        static::init();
        $sing = \Application\Document\Helper\NotificationCenter::getInstance();
        $sing->getInstance()->setClasspath("Organization");
        $_SESSION['userId'] = "57fcfa644e228de32c00421b";
        parent::setUp();
    }

    public function testPersistData()
    {}

    public function testUndo()
    {
        $serviceName = "Application\\Service\\Service";
        $method = "undo";
        $serviceClass = new \ReflectionClass($serviceName);
        $class = $serviceClass->newInstanceArgs();
        $reflectionMethod = new \ReflectionMethod($class, $method);
        $idRel = $reflectionMethod->invoke($class, null);
        $listArray[] = $idRel;
        
        $method = "getAllOrganzations";
        $serviceClass = new \ReflectionClass($serviceName);
        $class = $serviceClass->newInstanceArgs();
        $reflectionMethod = new \ReflectionMethod($class, $method);
        $idRel = $reflectionMethod->invoke($class, null);
        $listArray[] = $idRel;
    }

    public function testExecute()
    {
        $mObj = new MongoObjectFactory();
        $type = 'Customer';
        $listObjectsRet = $mObj->find($type);
        $id = (string) $listObjectsRet[0]['_id'];
        $listObject = $mObj->findObject($type, $id);
        $serviceName = "Application\\Service\\ReportingService";
        $method = "formatDocument";
        $serviceClass = new \ReflectionClass($serviceName);
        $class = $serviceClass->newInstanceArgs();
        $reflectionMethod = new \ReflectionMethod($class, $method);
        
        $data = array();
        $data['data']['template'] = "template";
        $data['data']['objectType'] = "Customer";
        $data['data']['id']['$id'] = $id;
        // orderlines_parent&parent
        $data['data']['method'] = 'cgtOrder&orderlines_parent&parent_processorders+getOrder[available-true]&orderlines_parent&processorders&parent+getOrder.getOrderline+eval^what^return count($this->orders);';
        // $data['data']['method'] = 'getOrder+getParent+getOrder.eval^sum^return $this->sum("orderline","amount");+getOrder.eval^avg^return $this->avg("orderline","amount");+eval^what^return $this->sum("order","number");';
        // $data['data']['method'] = "getParent+getPackageline&orders&orderlines_customerprocesss_processs";
        // 'getParent+getPackageline+getPackageline.getOrder&orderlines_customerprocesss+getPackageline.getOrderline+getPackageline.getOrderline.getCustomerprocess.getProcess';
        $data['data']['workspaceId'] = "577e6cea4e228d184100428c";
        /*
         * $data['data']= array(
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
         */
        // $result = $mongoObjectFactory->update($data['objectType'], $id, $data['data']);
        $idRel = $reflectionMethod->invoke($class, $data['data']);
        $listArray[] = $idRel;
    }

    public function testExecuteTraining()
    {
        $serviceName = "Application\\Service\\ReportingService";
        $method = "formatDocument";
        $serviceClass = new \ReflectionClass($serviceName);
        $class = $serviceClass->newInstanceArgs();
        $reflectionMethod = new \ReflectionMethod($class, $method);
        $mObj = new MongoObjectFactory();
        $type = 'Ilctraining';
        $listObjectsRet = $mObj->find($type);
        $id = (string) $listObjectsRet[0]['_id'];
        $listObject = $mObj->findObject($type, $id);
        $data = array();
        $data['data']['template'] = "template";
        $data['data']['objectType'] = "Ilctraining";
        $data['data']['id']['$id'] = $id;
        $data['data']['method'] = 'getOrder&parent+getParent+getOrder.eval^what^return count($this->orderlines);';
        // $data['data']['method'] = 'getOrder+getParent+getOrder.eval^sum^return $this->sum("orderline","amount");+getOrder.eval^avg^return $this->avg("orderline","amount");+eval^what^return $this->sum("order","number");';
        // $data['data']['method'] = "getParent+getIlctrainingevent+getIlctrainer+getIlcparticipant+getIlctopic";
        // 'getParent+getPackageline+getPackageline.getOrder&orderlines_customerprocesss+getPackageline.getOrderline+getPackageline.getOrderline.getCustomerprocess.getProcess';
        $data['data']['workspaceId'] = "56f6f04b4e228dc66b0041ab";
        /*
         * $data['data']= array(
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
         */
        // $result = $mongoObjectFactory->update($data['objectType'], $id, $data['data']);
        // $idRel = $reflectionMethod->invoke($class, $data['data']);
        // $listArray[] = $idRel;
    }
}
