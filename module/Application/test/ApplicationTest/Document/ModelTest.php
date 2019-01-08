<?php
namespace ApplicationTest\Controller;

use \Application\Document\Order;
use Application\Controller\MongoObjectFactory;

/**
 * Description of AuthControllerTest
 *
 * @author mihai.coditoiu
 */
class ModelTest extends \PHPUnit_Framework_TestCase
{

    protected $traceError = true;

    public function setUp()
    {
        parent::setUp();
    }

    public function testPersistData()
    {
        $data = array(
            "title" => "that",
            "text" => "who"
        );
        // $jsonString = json_encode($data);
        // $class = new Order();
        $mObj = new MongoObjectFactory();
        $return = $mObj->create('Order', $data);
        print_r($return);
        // print_r($class->_id);
    }

    public function testOrderJson()
    {
        $data = array(
            "title" => "that",
            "text" => "who"
        );
        $jsonString = json_encode($data);
        
        /*
         * // Here's the sweetness.
         * $class = new Order($jsonString);
         * print_r($class);
         * $order = new \Application\Document\Order();
         * $order->set_title('lol');
         * $order->set_text('haha');
         */
        // assertEquals('{"title":"lol","text":"haha"}', json_encode($order->jsonSerialize()));
    }

    public function testDate()
    {
        $url = "http://localhost:8080/admin/view";
        $urlTo = explode("/", $url);
        $size = sizeof($urlTo);
        $s = 'http:';
        for ($x = 1; $x <= $size-2; $x++) {
           $s = $s.'/'.$urlTo[$x];
        }
        print_r($s);
        
    }
}
