<?php
namespace AuthenticationTest\Controller;

use Zend\Authentication\Result;
use AuthenticationTest\Bootstrap;

/**
 * Description of AuthControllerTest
 *
 * @author mihai.coditoiu
 */
class AuthControllerTest extends \Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase
{
    protected $traceError = true;

    public function setUp ()
    {
        $this->setApplicationConfig(
            include 'config/application.config.php'
        );
        parent::setUp();


    }

    public function testIndexCanBeAccesed() {

        $this->dispatch('/auth');

        $this->assertResponseStatusCode(200);
        $this->assertModuleName('Authentication');
        $this->assertControllerName('Authentication\Controller\Auth');
    }


}
