<?php
namespace AuthenticationTest\Service;


use \Application\Controller\MongoObjectFactory;
use \Application\Service\PubnubService;
use Zend\Mvc\Service\ServiceManagerConfig;
use Zend\ServiceManager\ServiceManager;
use Application\Service\Application\Service;

/**
 * Description of AuthControllerTest
 */
class PubnubTest extends \PHPUnit_Framework_TestCase
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

    public function testExecutePubnub()
    {
        $PubnubSer = new PubnubService();
        PubnubService::create("Organization","pub-c-20a90445-77ca-4de0-833d-edf7cce965da","sub-c-4d14cf20-ceb6-11e6-b82b-0619f8945a4f");
        $PubnubSer->listen("Organization", "Channel-test");
        $PubnubSer->publish("Organization", "Channel-test", "Client-mbukd", '{ "aa" : "test"}');
        
    }

}
