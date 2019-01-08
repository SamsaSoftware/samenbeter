<?php
namespace ApplicationTest\Controller;

use \Application\Document\Order;
use \Application\Controller\MongoObjectFactory;

/**
 * Description of AuthControllerTest
 *
 * @author mihai.coditoiu
 */
class ViewTest extends \PHPUnit_Framework_TestCase
{

    protected $traceError = true;

    public function setUp()
    {
        parent::setUp();
    }

    public function testLoadData()
    {
        /*$d1 = \DateTime::createFromFormat("d-m-Y", "2-11-2016");
        $mObj = new MongoObjectFactory();
        $type = 'Application\Document\View';
        $listObjectsRet = $mObj->find($type);
        $id = (string)$listObjectsRet[0]['_id'];
        $listObject = $mObj->findObject($type, $id);
        print_r($listObject);*/
        print_r( date("jS F, Y",strtotime("5 april")) );
        
    }
}
