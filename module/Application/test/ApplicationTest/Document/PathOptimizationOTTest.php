<?php
namespace ApplicationTest\Controller;

use \Application\Controller\MongoObjectFactory;
use Zend\Mvc\Service\ServiceManagerConfig;
use Zend\ServiceManager\ServiceManager;

/**
 * Description of AuthControllerTest
 *
 * @author mihai.coditoiu
 */
class PathOptimizationOTTest extends \PHPUnit_Framework_TestCase
{

    protected $traceError = true;

    public function setUp()
    {
        static::init();
        $sing = \Application\Document\Helper\NotificationCenter::getInstance();
        $sing->getInstance()->setClasspath("Ordertapp");
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

    public function testPath()
    {
        $mObj = new MongoObjectFactory();
        $type = 'Otmerchant';
        $listObjectsRet = $mObj->find($type);
        $id = (string) $listObjectsRet[1]['_id'];
       
        $listObject = $mObj->findObject($type, $id);
       // $veh = $listObject->getCloseByVechicles()[0];
        $veh = $listObject->getPathReferences('getParent.getOtvehicle')[0];
        $veh->autoplanOnCustomer($listObject);
        return true;
        
        $tos = $listObject->getPathReferences('getOttransportorder[status-new]');
        $origins = [];
        $dest = [];
        $i = 0;
        //$locations = $veh->getPathReferences('getParent');
        
        //foreach ($locations as $location) {
            if (isset($listObject->latitude) && strlen($listObject->latitude) > 1) {} else {
                
                $listObject->calculateGeoCode();
            }
            $mainOrigin = $listObject->getIdAsString();
            $origins[0]['coordinates'] = $listObject->latitude . "," . $listObject->longitude;
            $origins[0]['id'] = $listObject->getIdAsString();
            foreach ($tos as $to) {
                if (isset($to->latitude) && strlen($to->latitude) > 1) {} else {
                    $to->calculateGeoCode();
                }
                $origins[$i + 1]['coordinates'] =  $to->latitude . "," . $to->longitude;
                $origins[$i + 1]['id'] = $to->getIdAsString();
                $dest[$i] = [];
                $dest[$i]['geocode'] = $to->latitude . "," . $to->longitude;
                
                if (isset($to->size)) {
                    $dest[$i]['capacity'] = $to->size;
                } else {
                    $dest[$i]['capacity'] = $to;
                    $dest[$i]['id'] = $to->getIdAsString();
                }
                $i = $i + 1;
            }
            $retM = $listObject->getDistanceMatrix($origins, $dest ,'direct');
            $optR = $listObject->getCompleteOptimalRoute($mainOrigin, $retM, "duration", 10000);
        }
   // }

    /*
     * public function testPersistData()
     * {
     * $data = array(
     * "title" => "that",
     * "name" => "who"
     * );
     * $mObj = new MongoObjectFactory();
     * $type = 'Customer';
     * $return = $mObj->create($type, $data);
     * print_r($return);
     *
     * $customer = $mObj->findObject($type, (string) $return);
     * print_r('found customer' . $return);
     *
     * $typeRef = 'Order';
     * $data = array(
     * "title" => "that",
     * "text" => "who"
     * );
     *
     * $mObj->createAndAdd($type, (string) $return, $typeRef, $data);
     *
     * $data1 = array(
     * "title" => "that1",
     * "text" => "who1"
     * );
     *
     * $returnN = $mObj->createAndAdd($type, (string) $return, $typeRef, $data1);
     * print_r($return);
     *
     * $customer = $mObj->findObject($type, (string) $return);
     *
     * $customer->get('Order');
     * print_r('creating orderline for - ' . (string) $returnN);
     * $typeRefL = 'Orderline';
     * $data = array(
     * "serial" => "serial_text",
     * "text" => "who"
     * );
     *
     * $return = $mObj->createAndAdd($typeRef, (string) $returnN, $typeRefL, $data);
     *
     * $order = $mObj->findObject($typeRef, (string) $returnN);
     * print_r($return);
     *
     * $order->copyset("getOrderline", "Processorder", true);
     * }
     */
    public function testFindAll()
    {
        $gmap = array(
            array(
                6,
                5,
                6,
                4,
                6,
                1,
                2,
                6,
                9,
                6,
                6,
                5,
                6,
                4,
                6,
                1,
                2,
                6,
                9,
                6
            ),
            array(
                8,
                5,
                3,
                2,
                9,
                9,
                3,
                2,
                4,
                7,
                8,
                5,
                3,
                2,
                9,
                9,
                3,
                2,
                4,
                7
            ),
            array(
                4,
                8,
                7,
                7,
                3,
                5,
                2,
                8,
                3,
                5,
                4,
                8,
                7,
                7,
                3,
                5,
                2,
                8,
                3,
                5
            ),
            array(
                7,
                5,
                5,
                8,
                7,
                7,
                8,
                4,
                8,
                5,
                7,
                5,
                5,
                8,
                7,
                7,
                8,
                4,
                8,
                5
            ),
            array(
                4,
                5,
                1,
                6,
                4,
                6,
                9,
                3,
                8,
                1,
                4,
                5,
                1,
                6,
                4,
                6,
                9,
                3,
                8,
                1
            ),
            array(
                4,
                6,
                6,
                4,
                2,
                7,
                8,
                8,
                2,
                2,
                4,
                6,
                6,
                4,
                2,
                7,
                8,
                8,
                2,
                2
            ),
            array(
                9,
                4,
                4,
                5,
                8,
                4,
                9,
                5,
                5,
                2,
                9,
                4,
                4,
                5,
                8,
                4,
                9,
                5,
                5,
                2
            ),
            array(
                2,
                9,
                4,
                9,
                4,
                8,
                4,
                1,
                4,
                6,
                2,
                9,
                4,
                9,
                4,
                8,
                4,
                1,
                4,
                6
            ),
            array(
                5,
                8,
                1,
                2,
                2,
                2,
                9,
                1,
                6,
                9,
                5,
                8,
                1,
                2,
                2,
                2,
                9,
                1,
                6,
                9
            ),
            array(
                8,
                9,
                2,
                7,
                5,
                1,
                2,
                9,
                8,
                9,
                8,
                9,
                2,
                7,
                5,
                1,
                2,
                9,
                8,
                9
            ),
            array(
                6,
                5,
                6,
                4,
                6,
                1,
                2,
                6,
                9,
                6,
                6,
                5,
                6,
                4,
                6,
                1,
                2,
                6,
                9,
                6
            ),
            array(
                8,
                5,
                3,
                2,
                9,
                9,
                3,
                2,
                4,
                7,
                8,
                5,
                3,
                2,
                9,
                9,
                3,
                2,
                4,
                7
            ),
            array(
                4,
                8,
                7,
                7,
                3,
                5,
                2,
                8,
                3,
                5,
                4,
                8,
                7,
                7,
                3,
                5,
                2,
                8,
                3,
                5
            ),
            array(
                7,
                5,
                5,
                8,
                7,
                7,
                8,
                4,
                8,
                5,
                7,
                5,
                5,
                8,
                7,
                7,
                8,
                4,
                8,
                5
            ),
            array(
                4,
                5,
                1,
                6,
                4,
                6,
                9,
                3,
                8,
                1,
                4,
                5,
                1,
                6,
                4,
                6,
                9,
                3,
                8,
                1
            ),
            array(
                4,
                6,
                6,
                4,
                2,
                7,
                8,
                8,
                2,
                2,
                4,
                6,
                6,
                4,
                2,
                7,
                8,
                8,
                2,
                2
            ),
            array(
                9,
                4,
                4,
                5,
                8,
                4,
                9,
                5,
                5,
                2,
                9,
                4,
                4,
                5,
                8,
                4,
                9,
                5,
                5,
                2
            ),
            array(
                2,
                9,
                4,
                9,
                4,
                8,
                4,
                1,
                4,
                6,
                2,
                9,
                4,
                9,
                4,
                8,
                4,
                1,
                4,
                6
            ),
            array(
                5,
                8,
                1,
                2,
                2,
                2,
                9,
                1,
                6,
                9,
                5,
                8,
                1,
                2,
                2,
                2,
                9,
                1,
                6,
                9
            ),
            array(
                8,
                9,
                2,
                7,
                5,
                1,
                2,
                9,
                8,
                9,
                8,
                9,
                2,
                7,
                5,
                1,
                2,
                9,
                8,
                9
            ),
            array(
                6,
                5,
                6,
                4,
                6,
                1,
                2,
                6,
                9,
                6,
                6,
                5,
                6,
                4,
                6,
                1,
                2,
                6,
                9,
                6
            ),
            array(
                8,
                5,
                3,
                2,
                9,
                9,
                3,
                2,
                4,
                7,
                8,
                5,
                3,
                2,
                9,
                9,
                3,
                2,
                4,
                7
            ),
            array(
                4,
                8,
                7,
                7,
                3,
                5,
                2,
                8,
                3,
                5,
                4,
                8,
                7,
                7,
                3,
                5,
                2,
                8,
                3,
                5
            ),
            array(
                7,
                5,
                5,
                8,
                7,
                7,
                8,
                4,
                8,
                5,
                7,
                5,
                5,
                8,
                7,
                7,
                8,
                4,
                8,
                5
            ),
            array(
                4,
                5,
                1,
                6,
                4,
                6,
                9,
                3,
                8,
                1,
                4,
                5,
                1,
                6,
                4,
                6,
                9,
                3,
                8,
                1
            ),
            array(
                4,
                6,
                6,
                4,
                2,
                7,
                8,
                8,
                2,
                2,
                4,
                6,
                6,
                4,
                2,
                7,
                8,
                8,
                2,
                2
            ),
            array(
                9,
                4,
                4,
                5,
                8,
                4,
                9,
                5,
                5,
                2,
                9,
                4,
                4,
                5,
                8,
                4,
                9,
                5,
                5,
                2
            ),
            array(
                2,
                9,
                4,
                9,
                4,
                8,
                4,
                1,
                4,
                6,
                2,
                9,
                4,
                9,
                4,
                8,
                4,
                1,
                4,
                6
            ),
            array(
                5,
                8,
                1,
                2,
                2,
                2,
                9,
                1,
                6,
                9,
                5,
                8,
                1,
                2,
                2,
                2,
                9,
                1,
                6,
                9
            ),
            array(
                8,
                9,
                2,
                7,
                5,
                1,
                2,
                9,
                8,
                9,
                8,
                9,
                2,
                7,
                5,
                1,
                2,
                9,
                8,
                9
            ),
            array(
                6,
                5,
                6,
                4,
                6,
                1,
                2,
                6,
                9,
                6,
                6,
                5,
                6,
                4,
                6,
                1,
                2,
                6,
                9,
                6
            ),
            array(
                8,
                5,
                3,
                2,
                9,
                9,
                3,
                2,
                4,
                7,
                8,
                5,
                3,
                2,
                9,
                9,
                3,
                2,
                4,
                7
            ),
            array(
                4,
                8,
                7,
                7,
                3,
                5,
                2,
                8,
                3,
                5,
                4,
                8,
                7,
                7,
                3,
                5,
                2,
                8,
                3,
                5
            ),
            array(
                7,
                5,
                5,
                8,
                7,
                7,
                8,
                4,
                8,
                5,
                7,
                5,
                5,
                8,
                7,
                7,
                8,
                4,
                8,
                5
            ),
            array(
                4,
                5,
                1,
                6,
                4,
                6,
                9,
                3,
                8,
                1,
                4,
                5,
                1,
                6,
                4,
                6,
                9,
                3,
                8,
                1
            ),
            array(
                4,
                6,
                6,
                4,
                2,
                7,
                8,
                8,
                2,
                2,
                4,
                6,
                6,
                4,
                2,
                7,
                8,
                8,
                2,
                2
            ),
            array(
                9,
                4,
                4,
                5,
                8,
                4,
                9,
                5,
                5,
                2,
                9,
                4,
                4,
                5,
                8,
                4,
                9,
                5,
                5,
                2
            ),
            array(
                2,
                9,
                4,
                9,
                4,
                8,
                4,
                1,
                4,
                6,
                2,
                9,
                4,
                9,
                4,
                8,
                4,
                1,
                4,
                6
            ),
            array(
                5,
                8,
                1,
                2,
                2,
                2,
                9,
                1,
                6,
                9,
                5,
                8,
                1,
                2,
                2,
                2,
                9,
                1,
                6,
                9
            ),
            array(
                8,
                9,
                2,
                7,
                5,
                1,
                2,
                9,
                8,
                9,
                8,
                9,
                2,
                7,
                5,
                1,
                2,
                9,
                8,
                9
            )
        );
        $map = $queue = $visited = array();
        $result = $this->findLowest(array(
            0,
            0,
            18,
            39
        ), $gmap);
        print_r($result);
        /*
         * $mObj = new MongoObjectFactory();
         * $type = 'Nwplannedorder';
         * $returnPOS = $mObj->find($type);
         * $intI = 0;
         *
         * $id = (string) $returnPOS[6]['_id'];
         *
         * $listObject = $mObj->findObject($type, $id);
         *
         * // $listObject->planOnMachine();
         *
         * $mObj = new MongoObjectFactory();
         * $type = 'Nwtask';
         * $returnTasks = $mObj->find($type);
         * $intI = 0;
         * $indexT = count($returnTasks);
         * $id = (string) $returnTasks[$indexT-1]['_id'];
         * $data1 = array(
         * "nwtask_startdate" => "17-09-2016 03:27",
         * );
         * $listObject = $mObj->findObject($type, $id);
         * $listObject->nwtask_startdate = "17-09-2016 03:27";
         * $listObject->update(json_encode($data1));
         * $listObject->getParent()->remove($listObject);
         * $data1 = array(
         * "nwtask_startdate" => "17-09-2016 19:27",
         * );
         * $listObject = $mObj->findObject($type, $id);
         * $listObject->nwtask_startdate = "17-09-2016 19:27";
         * $listObject->update(json_encode($data1));
         */
    }

    function findLowest($endpoints, $map)
    {
        $queue = $visited = array();
        
        // Add start position to queue.
        $queue[$endpoints[0] . 'x' . $endpoints[1]] = array(
            array(
                $endpoints[0],
                $endpoints[1]
            ),
            true,
            $map[$endpoints[1]][$endpoints[0]]
        );
        
        while (true) {
            /* START FETCH ITEM */
            if (! count($queue)) {
                return false;
            }
            
            $cost = INF;
            $next_item = false;
            
            foreach ($queue as $item) {
                if ($item[2] < $cost) {
                    $next_item = $item;
                    $cost = $item[2];
                }
            }
            
            unset($queue[$next_item[0][0] . 'x' . $next_item[0][1]]);
            
            if (! $next_item) {
                break;
            }
            $item = $next_item;
            /* END FETCH ITEM */
            
            $item_id = $item[0][0] . 'x' . $item[0][1];
            if (! empty($visited[$item_id])) {
                continue;
            }
            $visited[$item_id] = $item[1];
            
            // If current node is the end node, we've found our path!
            if ($item[0][0] == $endpoints[2] && $item[0][1] == $endpoints[3]) {
                $path = array();
                $cost = 0;
                $node = $item[0];
                
                while ($parent = $visited[$node[0] . 'x' . $node[1]]) {
                    $cost += $path[] = $map[$node[1]][$node[0]];
                    
                    if ($parent === true) {
                        break;
                    }
                    
                    $node = $parent;
                }
                
                return array(
                    $cost,
                    array_reverse($path)
                );
            }
            
            /* START QUEUE ADJACENTS */
            $directions = array(
                array(
                    0,
                    - 1
                ),
                array(
                    1,
                    0
                ),
                array(
                    0,
                    1
                ),
                array(
                    - 1,
                    0
                )
            );
            
            foreach ($directions as $direction) {
                $adjacent = array(
                    $item[0][0] + $direction[0],
                    $item[0][1] + $direction[1]
                );
                
                $in_range = ! empty($map[$adjacent[1]]) && ! empty($map[$adjacent[1]][$adjacent[0]]);
                
                if (! empty($visited[$adjacent[0] . 'x' . $adjacent[1]]) || ! $in_range) {
                    continue;
                }
                
                /* START QUEUE ADD */
                $adjacent_id = $adjacent[0] . 'x' . $adjacent[1];
                $adjacent_cost = $item[2] + $map[$adjacent[1]][$adjacent[0]];
                
                $adjacent_queue = true;
                
                if (! empty($visited[$adjacent_id])) {
                    $adjacent_queue = false;
                }
                
                if (! empty($queue[$adjacent_id])) {
                    if ($adjacent_cost < $queue[$adjacent_id][2]) {
                        $queue[$adjacent_id][1] = true;
                        $queue[$adjacent_id][2] = $adjacent_cost;
                    }
                    
                    $adjacent_queue = false;
                }
                
                if ($adjacent_queue) {
                    $queue[$adjacent_id] = array(
                        $adjacent,
                        $item[0],
                        $adjacent_cost
                    );
                }
                /* END QUEUE ADD */
            }
            /* END QUEUE ADJACENTS */
        }
    }
}
