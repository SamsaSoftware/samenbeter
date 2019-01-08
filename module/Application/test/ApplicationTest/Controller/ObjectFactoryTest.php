<?php
namespace ApplicationTest\Controller;

use Application\Controller\LocalObjectFactory;

/**
 * Description of AuthControllerTest
 *
 * @author mihai.coditoiu
 */
class ObjectFactoryTest extends \Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase
{
    protected $traceError = true;

    public function setUp ()
    {

        $this->setApplicationConfig(
            include '../../../config/application.config.php'
        );
        parent::setUp();


    }

    public function testIndexCanBeAccesed() {

        $this->dispatch('/application/getlist?objectType=\Application\Document\User');
        
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('Application');
        $this->assertControllerName('Application\Controller\IndexController');
      /*  $laf = new LocalObjectFactory();
        $collection = $laf->find('User');
        $this->assertNotEmpty($collection);*/

    }


}
