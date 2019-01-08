<?php

namespace AclTest\Controller;

use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;
use Zend\Mvc\Router\Http\TreeRouteStack as HttpRouter;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Acl\Controller\AclController;
use Authentication\Adapter\Adapter;
use Zend\Authentication\Result;
use Zend\Permissions\Acl\Role\GenericRole as Role;

/**
 *  Acl test
 */
class AclControllerTest extends AbstractHttpControllerTestCase
{

    public function setUp()
    {
        $this->setApplicationConfig(
            include '../../../config/application.config.php'
        );
        parent::setUp();
    }

    public function testIsAllowed()
    {
        //create event
        $event = new MvcEvent();

        $serviceManager = $this->getApplicationServiceLocator();

        $authMockup = $this->getMockBuilder('Acl\Controller\AclController')
            ->disableOriginalConstructor()
            ->getMock();

        $authMockup->expects($this->once())
            ->method('isAllowed')
            ->with($event)
            ->will($this->returnValue(true));

        $this->assertEquals(true, $authMockup->isAllowed($event));
    }

    public function testIsNotAllowed()
    {
        //create event
        $event = new MvcEvent();

        $serviceManager = $this->getApplicationServiceLocator();

        $authMockup = $this->getMockBuilder('Acl\Controller\AclController')
            ->disableOriginalConstructor()
            ->getMock();

        $authMockup->expects($this->once())
            ->method('isAllowed')
            ->with($event)
            ->will($this->returnValue(false));

        $this->assertEquals(false, $authMockup->isAllowed($event));
    }
}
