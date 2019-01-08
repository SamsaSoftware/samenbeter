<?php
namespace AuthenticationTest\Controller;

use Zend\Authentication\Result;

/**
 * Description of AuthControllerTest
 *
 * @author mihai.coditoiu
 */
class RemoteCallTest extends \Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase
{
    protected $traceError = true;

    public function setUp ()
    {
        parent::setUp();
        $this->setApplicationConfig(
            include '../../../config/application.config.php'
        );
    }

    public function testIndexCanBeAccesed() {

        $this->dispatch('/application/getlist?objectType=Application\Document\Customer');

        $this->assertResponseStatusCode(200);
 
    }
    

    public function testCreate() {
        $data = array(
            "title" => "that",
            "name" => "who"
        );
        $json = (string)json_encode($data);
        $this->dispatch('/application/save?objectType=Application\Document\Customer&json='.$json);
        $this->dispatch('/application/getMethodResult?objectType=Application\Document\Customer');
    
        $this->assertResponseStatusCode(200);
    
    }


}
