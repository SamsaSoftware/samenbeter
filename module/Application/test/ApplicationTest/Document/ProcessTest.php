<?php
namespace ApplicationTest\Controller;

use \Application\Document\Order;
use \Application\Controller\MongoObjectFactory;

/**
 * Description of AuthControllerTest
 *
 * @author mihai.coditoiu
 */
class ProcessTest extends \PHPUnit_Framework_TestCase
{

    protected $traceError = true;

    public function setUp()
    {
        $sing = \Application\Document\Helper\NotificationCenter::getInstance();
        $sing->getInstance()->setClasspath("Organization");
        parent::setUp();
    }

    /*
     * public function testFindAll()
     * {
     * $dateI = "18/8/2016 10:30 am";
     * $formats = array(
     * "d/m/Y h:i a"
     * );
     * foreach ($formats as $format) {
     * $date = \DateTime::createFromFormat($format, $dateI);
     * if ($date == false || ! (date_format($date, $format) == $dateI)) {}
     *
     * else {
     * $DATEX = $date;
     * }
     * }
     * $date = "18-03-2016";
     * $DATEX = date('d-m-yy', strtotime($date));
     *
     * $date = "9-03-2016";
     * $format = 'd-m-Y';
     * $date_stamp = strtotime(date('d-m-yy', strtotime($date)));
     * $actualDay = date("N", $date_stamp);
     * $date = "13-03-2016";
     * $format = 'd-m-Y';
     * $date_stamp = strtotime(date('d-m-yy', strtotime($date)));
     * $actualDay = date("N", $date_stamp);
     * $date = "14-03-2016";
     * $format = 'd-m-Y';
     * $date_stamp = strtotime(date('d-m-yy', strtotime($date)));
     * $actualDay = date("N", $date_stamp);
     * $date = "15-03-2016";
     * $format = 'd-m-Y';
     * $date_stamp = strtotime(date('d-m-yy', strtotime($date)));
     * $actualDay = date("N", $date_stamp);
     * $date = "16-03-2016";
     * $format = 'd-m-Y';
     * $date_stamp = strtotime(date('d-m-y', strtotime($date)));
     * $actualDay = date("N", $date_stamp);
     * $date = "17-03-2016";
     * $format = 'd-m-Y';
     * $date_stamp = strtotime(date('d-m-y', strtotime($date)));
     * $actualDay = date("N", $date_stamp);
     * $mObj = new MongoObjectFactory();
     * $type = 'Application\Document\Order';
     * $return = $mObj->find($type);
     * foreach ($return as $item) {
     * $item['recid'] = $item['_id']->__ToString();
     * unset($item['_id']);
     * print_r($item);
     * $listArray[] = $item;
     * foreach ($item as $key => $column) {
     * $columns[$key] = $key;
     * }
     * $arrayColumns = array();
     * foreach ($columns as $key => $column) {
     * $col['field'] = $key;
     * $col['caption'] = ucfirst($key);
     * $col['size'] = '100px';
     * $col['sortable'] = true;
     * $col['resizable'] = true;
     * $col['editable'] = array();
     * $arrayColumns[] = $col;
     * }
     * }
     * print_r($listArray);
     * }
     *
     * public function testPK()
     * {
     * $typeName = 'Process';
     * $typeRefClass = new \ReflectionClass("\\Application\\Document\\" . $typeName);
     * $reflectionMethod = new \ReflectionMethod("\\Application\\Document\\" . $typeName, "getPK");
     * $pk = $reflectionMethod->invoke(null, null);
     * }
     *
     * public function testSasma()
     * {
     * $mObj = new MongoObjectFactory();
     * $samsa = $mObj->getSamsa();
     * $mObj = new MongoObjectFactory();
     * $type = 'Organization';
     * $listObjectsRet = $mObj->find($type);
     * $id = (string) $listObjectsRet[0]['_id'];
     * $organizationObjectInstance = $mObj->findObject($type, $id);
     * $type = 'User';
     * $users = $mObj->find($type);
     * // $id = (string) $listObjectsRet[0]['_id'];
     * $organizationObjectInstance->addParentUser((string) $users[0]['_id']);
     * $samsa = $mObj->getSamsa();
     * // $samsa->addChild($organizationObjectInstance, "organizations");
     * print_r($samsa->_id);
     * }
     */
    public function testPK1()
    {
        
        
        // $v = $this->comb(3, $elems);
        $number_of_groups = 3;
        
        $number_of_iterations = 4;
        for ($tour = 15; $tour < 90; $tour = $tour + 3) {
            $number_of_people = $tour;
            $students = array();
            for ($i = 0; $i < $number_of_people; $i ++) {
                $students[$i] = array();
                $students[$i]['id'] = $i;
                $students[$i]['groups'] = array();
            }
            $group = array();
            for ($i = 0; $i < $number_of_groups; $i ++) {
                $index = $i;
                if (isset($students[$index])) {
                    while ($students[$index]) {
                        $group[$i][] = $students[$index];
                        $index = $index + $number_of_groups;
                        if(isset($students[$index]))
                        {
                            
                        }else{
                            break;
                        }
                    }
                }
            }
            $ret = array();
            $remade = false;
            $newG = array();
            $newI = 0;
            for ($i = 0; $i < $number_of_iterations; $i ++) {
                if ($remade) {
                    $group = $newG;
                    $remade = false;
                    $newG = array();
                    $newI = 0;
                }
                $ret[] = $this->combq($number_of_groups - 1, $group);
                
                for ($index = 0; $index < $number_of_groups; $index ++) {
                    if ($index == 2) {
                        // array_push($group[$index], array_pop($group[$index]));
                        array_unshift($group[$index], array_pop($group[$index]));
                    } else 
                        if ($index == 1) {
                            {
                                array_push($group[$index], array_shift($group[$index]));
                            }
                            
                            $left = $index - 1;
                            if ($left > 0) {
                                // array_push($group[$index], array_shift($group[$index]));
                                $left = $left - 1;
                            }
                        }
                    if ($i == 2) {
                        
                        foreach ($group[$index] as $arr) {
                            
                            $newG[$newI % 3][] = $arr;
                            
                            $newI ++;
                            $remade = true;
                        }
                    }
                }
            }
            print_r("******************\n");
            print_r("run no. " . $number_of_groups . " number of people : " . $number_of_people . "\n");
            foreach ($ret as $rets)
                print_r(json_encode($rets) . "\n");
            print_r("\n******************");
        }
    }

    function combq($n, $elems)
    {
        if ($n >= 0) {
            $tmp_set = array();
            $res = $this->combq($n - 1, $elems);
            $index = 0;
            foreach ($elems[$n] as $e) {
                if (isset($res[$index]))
                    $ce = $res[$index];
                else
                    $ce = '';
                    // foreach ($e as $st) {
                array_push($tmp_set, $ce . '-' . $e['id']);
                
                // array_push($ce , $e['id']);
                // $tmp_set[$index][] =$ce;
                
                $index ++;
                // }
            }
            // }
            return $tmp_set;
        } else {
            return array(
                ''
            );
        }
    }

    function comb($n, $elems)
    {
        if ($n > 0) {
            $tmp_set = array();
            $res = $this->comb($n - 1, $elems);
            foreach ($res as $ce) {
                foreach ($elems as $e) {
                    array_push($tmp_set, $ce . $e);
                }
            }
            return $tmp_set;
        } else {
            return array(
                ''
            );
        }
    }

    function pc_permute($items, $perms = array( ))
    {
        if (empty($items)) {
            $return = array(
                $perms
            );
        } else {
            $return = array();
            for ($i = count($items) - 1; $i >= 0; -- $i) {
                $newitems = $items;
                $newperms = $perms;
                list ($foo) = array_splice($newitems, $i, 1);
                array_unshift($newperms, $foo);
                $return = array_merge($return, $this->pc_permute($newitems, $newperms));
            }
        }
        return $return;
    }
}
