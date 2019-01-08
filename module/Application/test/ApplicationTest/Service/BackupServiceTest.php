<?php
namespace AuthenticationTest\Service;

use \Application\Service\BackupService;
use \Application\Controller\MongoObjectFactory;
use Zend\ServiceManager\ServiceManager;
use Zend\Mvc\Service\ServiceManagerConfig;

/**
 * Description of AuthControllerTest
 */
class BackupServiceTest extends \PHPUnit_Framework_TestCase
{

    protected $traceError = true;

    public function setUp()
    {
        static::init();
        $sing = \Application\Document\Helper\NotificationCenter::getInstance();
        $sing->getInstance()->setClasspath("Simulator");
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
    {
       
    }

    public function _testImportFromCSV()
    {
        $serviceName = "Application\\Service\\BackupService";
        $method = "importObject";
        
        $serviceClass = new \ReflectionClass($serviceName);
        $class = $serviceClass->newInstanceArgs();
        $reflectionMethod = new \ReflectionMethod($class, $method);
        
        $mObj = new MongoObjectFactory();
        $type = 'Workspace';
        $listObjectsRet = $mObj->find($type);
        $id = (string) $listObjectsRet[1]['_id'];
        $listObject = $mObj->findObject($type, $id);
        $objectA[] = $listObject;
        // $relations = array();
        // $relations["key"] = "foreign_type, foreign_key";
        $relations = array();
        $relations["id_kl"] = "Customer,id_kl";
        $idRel = $reflectionMethod->invoke($class, 'Workspace', $id, "Customer", "customer.csv", array());
        // $idRel = $reflectionMethod->invoke($class, "klant_tek.csv", 'Customer',0,"Customerprocess",$relations);
        $listArray[] = $idRel;
    }

    public function _testImportComplexFromCSV()
    {
        $serviceName = "Application\\Service\\BackupService";
        $method = "importObject";
        
        $serviceClass = new \ReflectionClass($serviceName);
        $class = $serviceClass->newInstanceArgs();
        $reflectionMethod = new \ReflectionMethod($class, $method);
        
        $mObj = new MongoObjectFactory();
        $type = 'Workspace';
        $listObjectsRet = $mObj->find($type);
        $id = (string) $listObjectsRet[0]['_id'];
        $listObject = $mObj->findObject($type, $id);
        $data = array();
        $data = file_get_contents('./complexxls.csv', true);
        // $idRel = $reflectionMethod->invoke($class, 'Workspace',$id,"Customer","customer.csv", array());
        $idRel = $reflectionMethod->invoke($class, 'Workspace', $id, "Sptextinput", $data);
        $listArray[] = $idRel;
    }

    public function _testCleanup()
    {
        $serviceName = "Application\\Service\\BackupService";
        $method = "cleanup";
        
        $serviceClass = new \ReflectionClass($serviceName);
        $class = $serviceClass->newInstanceArgs();
        $reflectionMethod = new \ReflectionMethod($class, $method);
        
        $mObj = new MongoObjectFactory();
        $type = 'Workspace';
        $listObjectsRet = $mObj->find($type);
        $id = (string) $listObjectsRet[1]['_id'];
        $listObject = $mObj->findObject($type, $id);
        $objectA[] = $listObject;
        
        $idRel = $reflectionMethod->invoke($class, $listObject);
        $listArray[] = $idRel;
    }

    public function _testMigrate()
    {
        $serviceName = "Application\\Service\\BackupService";
        $method = "migrate";
        
        $serviceClass = new \ReflectionClass($serviceName);
        $class = $serviceClass->newInstanceArgs();
        $reflectionMethod = new \ReflectionMethod($class, $method);
        
        $mObj = new MongoObjectFactory();
        $type = 'Workspace';
        $listObjectsRet = $mObj->find($type);
        $id = (string) $listObjectsRet[1]['_id'];
        $listObject = $mObj->findObject($type, $id);
        $objectA[] = $listObject;
        
        $idRel = $reflectionMethod->invoke($class, "", $objectA);
        $listArray[] = $idRel;
    }

    public function testExecute()
    {
        $serviceName = "Application\\Service\\BackupService";
        $methodArray = "exportToFile";
        $method = "exportToFile";
        $methodI = "importFromFile";
        
        $serviceClass = new \ReflectionClass($serviceName);
        $class = $serviceClass->newInstanceArgs();
        $reflectionMethod = new \ReflectionMethod($class, $method);
        $reflectionMethodI = new \ReflectionMethod($class, $methodI);
        $reflectionMethodArray = new \ReflectionMethod($class, $methodArray);
        
        $mongoObjectFactory = new MongoObjectFactory();
        // $WId = "5728b3924e228df28800420a";
        
        $objectA = array();
        // $object = $mongoObjectFactory->findObjectInstance("Workspace", $WId);
        
        $mObj = new MongoObjectFactory();
        $type = 'Workspace';
        $listObjectsRet = $mObj->find($type);
        $WId = (string) $listObjectsRet[0]['_id'];
        $objectA = array();
        $object = $mongoObjectFactory->findObjectInstance("Workspace", $WId);
        
        $objectA[] = $object;
        $objectP1 = $object->getOrganizations();
        $objectP = $mongoObjectFactory->findObjectInstance("Organization", (string) $objectP1['$id']);
        $inArray = [];
        
     //   $returnArray = $reflectionMethodArray->invoke($class,"test", $objectA);
        
    //    $idRel = $reflectionMethod->invoke($class, $objectP->name . "/" . $object->name, $objectA, true);
        //$listArray[] = $idRel;
        
        $d1 = new \DateTime();
        $dateRet = $d1->format("U");
        
        $datawks1 = array(
            "active" => 'false',
            "title" => "importedWorkspace",
            "name" => "workspace" . $dateRet
        )
        // "parent" => array( array('$id'=> (string) $objectP1['$id'] , '$ref' =>"organizations" ))
        ;
        $typeW = 'Workspace';
        
        $idRel = $reflectionMethodI->invoke($class, $objectP->name . "/" . $object->name, $objectP, "workspaces", $datawks1);
      //  $listArray[] = $idRel;
        
        // $arr = array();
        // $arr = self::createArr($arr, 0);
        // print_r($arr);
    }
}
