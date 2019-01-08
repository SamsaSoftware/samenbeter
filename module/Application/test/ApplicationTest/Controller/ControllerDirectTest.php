<?php
namespace ApplicationTest\Controller;


use Application\Controller\MongoObjectFactory;
use Application\Document\Relation;

/**
 * Description of AuthControllerTest
 *
 * @author mihai.coditoiu
 */
class ControllerDirectTest extends \PHPUnit_Framework_TestCase
{

    protected $traceError = true;

    public function setUp()
    {
        parent::setUp();
        $m = new \MongoClient();
        $db = $m->zf2odm;
        // create collections user & user roles
        $users = $db->createCollection("users");
        $userRoles = $db->createCollection("userRoles");
        $workspaces = $db->createCollection("workspaces");
        $views = $db->createCollection("views");
        $menus = $db->createCollection("menus");
        $fields = $db->createCollection("fields");
        $customers = $db->createCollection("customers");
        $customerprocesss = $db->createCollection("customerprocesss");
        $orders = $db->createCollection("orders");
        $orderlines = $db->createCollection("orderlines");
        $process = $db->createCollection("processs");
        // drop old collections
        $users->drop();
        $userRoles->drop();
        $workspaces->drop();
        $views->drop();
        $menus->drop();
        $fields->drop();
        $customerprocesss->drop();
        $customers->drop();
        $orders->drop();
        $orderlines->drop();
        $workspaces->drop();
        $process->drop();
    }

    public function testRelations()
    {
        $m = new \MongoClient();
        $db = $m->zf2odm;
        $userRoles = $db->createCollection("userRoles");
        $users = $db->createCollection("users");
        $userRoleAdmin = array(
            'role' => 'admin'
        );
        $userRoleUser = array(
            'role' => 'user'
        );
        
        $userRoles->insert($userRoleAdmin);
        $userRoles->insert($userRoleUser);
        
        $role = $userRoles->findOne(array(
            'role' => 'admin'
        ));
        
        $mObj = new \Application\Controller\MongoObjectFactory();
        $typeW = 'Application\Document\Workspace';
        
        $data = array(
            
            "title" => "Home",
            "name" => "/view/0"
        );
        $typeW = 'Application\Document\Workspace';
        $returnW = $mObj->create($typeW, $data);
        print_r('WORKSPACE' . $returnW);
        
        // create organization collection
        $organizations = $db->createCollection("organizations");
        $organizations->drop();
        $organization = array(
            'name' => 'Organization 1',
            "workspaceId" => (string) $returnW
        );
        $idOrganization = $organizations->insert($organization);
        $organizationNew = $organizations->findOne(array(
            'name' => 'Organization 1'
        ));
        
        $user = array(
            'name' => 'admin',
            'password' => md5('12345'),
            'userRole' => array(
                '$ref' => 'userRoles',
                '$id' => $role['_id'],
                '$db' => 'zf2odm'
            ),
            'organization' => array(
                '$ref' => 'organizations',
                '$id' => $organizationNew['_id'],
                '$db' => 'zf2odm'
            )
        );
        $users->insert($user);
        
        echo "User inserted- success \n";
        
        $data = array(
            "title" => "Home",
            "name" => "/view/0",
            "parentType" => "Workspace",
            "parentId" => (string) $returnW
        );
        
        $typeV = 'Application\Document\View';
        $returnV = $mObj->createAndAdd($typeW, $returnW, $typeV, $data);
        print_r($returnV);
        
        $data = array(
            "title" => "CustomerView",
            "name" => "/list/Customer",
            "parentType" => "Workspace",
            "parentId" => (string) $returnW
        );
        
        $typeV = 'Application\Document\View';
        $returnVCust = $mObj->createAndAdd($typeW, (string) $returnW, $typeV, $data);
        print_r($returnVCust);
        
        $linksFor = array(
            "gridRef" => "OrderView"
        );
        
        $data = array(
            "title" => "CustomerOrderView",
            "name" => "/listReference/Customer_getOrder",
            "parentType" => "Workspace",
            "parentId" => (string) $returnW,
            "links" => array(
                "gridRef" => "OrderView"
            )
        );
        
        $typeV = 'Application\Document\View';
        $returnV = $mObj->createAndAdd($typeW, (string) $returnW, $typeV, $data);

        $data = array(
            "title" => "CustomerprocessProcessView",
            "name" => "/listReference/Customerprocess_getProcess",
            "parentType" => "Workspace",
            "parentId" => (string) $returnW,
            "links" => array(
                "gridRef" => "OrderView"
            )
        );
        
        $typeV = 'Application\Document\View';
        $returnVx = $mObj->createAndAdd($typeW, (string) $returnW, $typeV, $data);
        
        
        
        $data = array(
            "title" => "CustomerProcessView",
            "name" => "/listReference/Customer_getCustomerprocess",
            "parentType" => "Workspace",
            "parentId" => (string) $returnW,
            "links" => array(
                "gridRef" => "CustomerprocessView"
            )
        );
        
        $typeV = 'Application\Document\View';
        $returnVx = $mObj->createAndAdd($typeW, (string) $returnW, $typeV, $data);
       // print_r($returnV);
        
        $testB = array(
            "gridRef" => "OrderlineView"
        );
        $data1 = array(
            "title" => "OrderView",
            "name" => "/listReference/Order_getOrderline",
            "parentType" => "Workspace",
            "parentId" => (string) $returnW,
            "links" => $testB
        );
        
        $typeV1 = 'Application\Document\View';
        $returnV1 = $mObj->createAndAdd($typeW, (string) $returnW, $typeV1, $data1);
        print_r($returnV1);
        
        $data = array(
            "id" => "CustomerView",
            "text" => "Customers"
        );
        
        $typeM = '\Application\Document\Menu';
        $returnMCust = $mObj->createAndAdd($typeW, (string) $returnW, $typeM, $data);
        print_r($returnMCust);
        
        $data = array(
            "id" => "CustomerOrderView",
            "text" => "Customer Orders"
        );
        
        $returnM = $mObj->createAndAdd($typeW, (string) $returnW, $typeM, $data);
        print_r($returnM);
        $data = array(
            "id" => "CustomerProcessView",
            "text" => "Customer Processes"
        );
        
        $returnM1 = $mObj->createAndAdd($typeW, (string) $returnW, $typeM, $data);
        print_r($returnM1);
        
        $object = $mObj->findObject("\Application\Document\Menu", (string) $returnMCust);
        
        $data = array();
        $reference = array();
        $reference['$ref'] = "views";
        $reference['$id'] = (string) $returnVCust;
        
       //$object->{'views'}[] = $reference;
        $object->addReferenceObject("views", $reference);
        
        $typeCus = 'Application\Document\Customer';
        $data = array(
            "name" => 666,
            "title" => 3404
        );
        $returnC1 = $mObj->createAndAdd($typeW, (string) $returnW, $typeCus, $data);
        
        
        $typePr = 'Application\Document\Process';
        $data = array(
            "name" => 666,
            "title" => 3404
        );
        $returnP1 = $mObj->createAndAdd($typeW, (string) $returnW, $typePr, $data);
        
        //$pp = array("nane"=> 'aa');
        $process = array("id" => $returnP1);

        
        $typeRef = 'Application\Document\Customerprocess';
        $data = array(
            "amout" => 666,
            "revenue" => 3404,
            "processs" => $process
        );
        $mObj->createAndAdd($typeCus, (string) $returnC1, $typeRef, $data);
    }
        

  
}
