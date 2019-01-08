<?php
namespace ApplicationTest\Controller;

use \Application\Document\Order;
use \Application\Controller\MongoObjectFactory;

/**
 * Description of AuthControllerTest
 *
 * @author mihai.coditoiu
 */
class OrderTest extends \PHPUnit_Framework_TestCase
{

    protected $traceError = true;

    public function setUp()
    {
        $sing = \Application\Document\Helper\NotificationCenter::getInstance();
        $sing->getInstance()->setClasspath("Nw");
        parent::setUp();
    }

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
    public function testD()
    {
        $comparable = 'capacity';
        $max = 3;

        
        // set the distance array
        $_distArr = array();
        $_distArr[1][2]['distance']['value'] = 7;
        $_distArr[1][3]['distance']['value'] = 9;
        $_distArr[1][6]['distance']['value'] = 14;
        $_distArr[2][1]['distance']['value'] = 7;
        $_distArr[2][3]['distance']['value'] = 10;
        $_distArr[2][4]['distance']['value'] = 15;
        $_distArr[3][1]['distance']['value'] = 9;
        $_distArr[3][2]['distance']['value'] = 10;
        $_distArr[3][4]['distance']['value'] = 11;
        $_distArr[3][6]['distance']['value'] = 2;
        $_distArr[4][2]['distance']['value'] = 15;
        $_distArr[4][3]['distance']['value'] = 11;
        $_distArr[4][5]['distance']['value'] = 6;
        $_distArr[5][4]['distance']['value'] = 6;
        $_distArr[5][6]['distance']['value'] = 9;
        $_distArr[6][1]['distance']['value'] = 14;
        $_distArr[6][3]['distance']['value'] = 2;
        $_distArr[6][5]['distance']['value'] = 9;
        $_distArr[5][7]['distance']['value'] = 2;
        $_distArr[7][5]['distance']['value'] = 2;
        
        
        $_distArr[1][2]['duration']['value'] = 600;
        $_distArr[1][3]['duration']['value'] = 900;
        $_distArr[1][6]['duration']['value'] = 1400;
        $_distArr[2][1]['duration']['value'] = 700;
        $_distArr[2][3]['duration']['value'] = 1000;
        $_distArr[2][4]['duration']['value'] = 1500;
        $_distArr[3][1]['duration']['value'] = 900;
        $_distArr[3][2]['duration']['value'] = 1000;
        $_distArr[3][4]['duration']['value'] = 1100;
        $_distArr[3][6]['duration']['value'] = 200;
        $_distArr[4][2]['duration']['value'] = 1500;
        $_distArr[4][3]['duration']['value'] = 1100;
        $_distArr[4][5]['duration']['value'] = 600;
        $_distArr[5][4]['duration']['value'] = 600;
        $_distArr[5][6]['duration']['value'] = 900;
        $_distArr[6][1]['duration']['value'] = 1400;
        $_distArr[6][3]['duration']['value'] = 200;
        $_distArr[6][5]['duration']['value'] = 900;
        $_distArr[5][7]['duration']['value'] = 200;
        $_distArr[7][5]['duration']['value'] = 200;
        
        $_distArr[1][2]['capacity']['value'] = 1;
        $_distArr[1][3]['capacity']['value'] = 1;
        $_distArr[1][6]['capacity']['value'] = 1;
        $_distArr[2][1]['capacity']['value'] = 1;
        $_distArr[2][3]['capacity']['value'] = 1;
        $_distArr[2][4]['capacity']['value'] = 1;
        $_distArr[3][1]['capacity']['value'] = 1;
        $_distArr[3][2]['capacity']['value'] = 1;
        $_distArr[3][4]['capacity']['value'] = 1;
        $_distArr[3][6]['capacity']['value'] = 1;
        $_distArr[4][2]['capacity']['value'] = 1;
        $_distArr[4][3]['capacity']['value'] = 1;
        $_distArr[4][5]['capacity']['value'] = 1;
        $_distArr[5][4]['capacity']['value'] = 1;
        $_distArr[5][6]['capacity']['value'] = 1;
        $_distArr[6][1]['capacity']['value'] = 1;
        $_distArr[6][3]['capacity']['value'] = 1;
        $_distArr[6][5]['capacity']['value'] = 1;
        $_distArr[5][7]['capacity']['value'] = 1;
        $_distArr[7][5]['capacity']['value'] = 1;
        // origin
        $a = 1;
        
        // initialize the array for storing
        $S = array(); // the nearest path with its parent and weight
        $Q = array(); // the left nodes without the nearest path
        foreach (array_keys($_distArr) as $val){
            $Q[$val]['distance'] = 99999;
            $Q[$val]['duration'] = 99999;
            $Q[$val]['capacity'] = 99999;
        }
        $Q[$a]['distance'] = 0;
        $Q[$a]['duration'] = 0;
        $Q[$a]['capacity'] = 0;
        
      
        $lastIndex = $a;
        
        // start calculating
        while (! empty($Q)) {
            $min = array_search(min($Q), $Q); // the most min weight  
            foreach ($_distArr[$min] as $key => $val) {
                if (! empty($Q[$key]) && $Q[$min]['distance'] + $val['distance']['value'] < $Q[$key]['distance']) {
                    
                    $Q[$key]['distance'] = $Q[$min]['distance'] + $val['distance']['value'];
                    $Q[$key]['duration'] = $Q[$min]['duration'] + $val['duration']['value'];
                    $Q[$key]['capacity'] = $Q[$min]['capacity'] + $val['capacity']['value'];
                    if( $Q[$key][$comparable] <= $max)
                    {
                        $lastIndex = $key;
                        $S[$key]= array(
                            $min,
                            $Q[$key]['distance'],
                            $Q[$key]['duration'],
                            $Q[$key]['capacity']
                        );
                    }
                }
            }
            unset($Q[$min]);
        }
        
        // list the path
        $path = array();
        $pos = $lastIndex;
      //  if (! array_key_exists($b, $S)) {
       //     echo "Found no way.";
       //     return;
       // }
         while ($pos != $a) {
            $path[] = $pos;
            $pos = $S[$pos][0];
           // $pos = $S[$pos][0];
        }
        $path[] = $a;
        $path = array_reverse($path);
        
        // print result

        echo "<br />From $a to $lastIndex";
        echo "<br />The length is " . $S[$lastIndex][1];
        echo "<br />The total duration is " . $S[$lastIndex][2];
        echo "<br />The total capacity is " . $S[$lastIndex][3];
        echo "<br />Path is " . implode('->', $path);
    }

    public function testDijkstra()
    {
        
        // set the distance array
        $_distArr = array();
        $_distArr[1][2] = 7;
        $_distArr[1][3] = 9;
        $_distArr[1][6] = 14;
        $_distArr[2][1] = 7;
        $_distArr[2][3] = 10;
        $_distArr[2][4] = 15;
        $_distArr[3][1] = 9;
        $_distArr[3][2] = 10;
        $_distArr[3][4] = 11;
        $_distArr[3][6] = 2;
        $_distArr[4][2] = 15;
        $_distArr[4][3] = 11;
        $_distArr[4][5] = 6;
        $_distArr[5][4] = 6;
        $_distArr[5][6] = 9;
        $_distArr[6][1] = 14;
        $_distArr[6][3] = 2;
        $_distArr[6][5] = 9;
        $_distArr[5][7] = 2;
        // $_distArr[6][7] = 2;
        // $_distArr[7][6] = 2;
        $_distArr[7][5] = 2;
        
        // the start and the end
        $a = 1;
        $b = 7;
        
        // initialize the array for storing
        $S = array(); // the nearest path with its parent and weight
        $Q = array(); // the left nodes without the nearest path
        foreach (array_keys($_distArr) as $val)
            $Q[$val] = 99999;
        $Q[$a] = 0;
        
        // start calculating
        while (! empty($Q)) {
            $min = array_search(min($Q), $Q); // the most min weight
            if ($min == $b)
                break;
            
            foreach ($_distArr[$min] as $key => $val)
                if (! empty($Q[$key]) && $Q[$min] + $val < $Q[$key]) {
                    $Q[$key] = $Q[$min] + $val;
                    $S[$key] = array(
                        $min,
                        $Q[$key]
                    );
                }
            unset($Q[$min]);
        }
        
        // list the path
        $path = array();
        $pos = $b;
        if (! array_key_exists($b, $S)) {
            echo "Found no way.";
            return;
        }
        while ($pos != $a) {
            $path[] = $pos;
            $pos = $S[$pos][0];
        }
        $path[] = $a;
        $path = array_reverse($path);
        
        // print result
        echo "<img src='http://www.you4be.com/dijkstra_algorithm.png'>";
        echo "<br />From $a to $b";
        echo "<br />The length is " . $S[$b][1];
        echo "<br />Path is " . implode('->', $path);
    }

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
