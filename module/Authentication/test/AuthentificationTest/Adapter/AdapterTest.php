<?php
namespace AuthenticationTest\Adapter;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Zend\Authentication\Result;
use Authentication\Adapter\ResultAdapter;
use Zend\Authentication\AuthenticationService;

/**
 * Description of AdapterTest
 *
 * @author mihai.coditoiu
 */
class AdapterTest extends AbstractHttpControllerTestCase
{
    protected $traceError = true;
    private $serviceManager;
    private $aclMockup;

    public function setUp ()
    {
        $this->setApplicationConfig(
            include 'config/application.config.php'
        );
        parent::setUp();

        $this->serviceManager = $this->getApplicationServiceLocator();
        $this->serviceManager->setAllowOverride(true);
    }

    public function testSuccessAuthenticate ()
    {
        $this->markTestSkipped(
            'Skipped'
        );
        $emMock = $this->getMock(
            '\Doctrine\ORM\EntityManager',
            array('getRepository', 'getClassMetadata', 'persist', 'flush'),
            array(),
            '',
            false
        );
        $localServiceMock = $this->getMockBuilder('Dcl\Service\BdLocalService')
                ->disableOriginalConstructor()
                ->getMock();
        $userMock = $this->getMockBuilder('Dcl\Entity\User')
                ->disableOriginalConstructor()
                ->getMock();
        //User mockup
        $authMockup = $this->getMockBuilder('Dcl\SubService\AuthAdapter')
                ->disableOriginalConstructor()
                ->getMock();

        $userManegementMockup = $this->getMockBuilder('Dcl\SubService\UserManagement')
                ->disableOriginalConstructor()
                ->getMock();
        $userManegementMockup->expects($this->once())
                ->method('isLock')
                ->will($this->returnValue(false));

        $authentificationServiceMockup = $this->getMockBuilder('Zend\Authentication\AuthenticationService')
                ->disableOriginalConstructor()
                ->getMock();

        $adpterMock = $this->getMockBuilder('\DoctrineModule\Authentication\Adapter\ObjectRepository')
                ->setConstructorArgs(
                    array(
                            array(
                                'objectManager' => $emMock,
                                'identityClass' => 'Dcl\Entity\User',
                                'identityProperty' => 'email',
                                'credentialProperty' => 'password',
                                'credentialCallable' => "Dcl\Entity\User::hashPassword"
                            )
                        )
                )
                ->setMethods(array('authenticate'))
                ->getMock();

        $result = new Result(Result::SUCCESS, 1);

        $authentificationServiceMockup->expects($this->once())
                ->method('authenticate')
                ->will($this->returnValue($result));

        $authMockup->expects($this->once())
                ->method('getAdapter')
                ->will($this->returnValue($adpterMock));
        $localServiceMock->expects($this->once())
                ->method('getUserBy')
                ->will($this->returnValue($userMock));
        $localServiceMock->expects($this->once())
                ->method('update')
                ->will($this->returnValue(true));

        $authentificationServiceMockup->expects($this->once())
                ->method('setAdapter')
                ->with($adpterMock);
        $localServiceMock->expects($this->once())
            ->method('getUserBy')
            ->will($this->returnValue(true));

        $this->serviceManager->setService('Dcl\SubService\AuthAdapter', $authMockup);
        $this->serviceManager->setService('\Doctrine\ORM\EntityManager', $emMock);
        $this->serviceManager->setService('Dcl\SubService\UserManagement', $userManegementMockup);
        $this->serviceManager->setService('Zend\Authentication\AuthenticationService', $authentificationServiceMockup);
        $this->serviceManager->setService('\DoctrineModule\Authentication\Adapter\ObjectRepository', $adpterMock);
        $this->serviceManager->setService('Dcl\Service\BdLocalService', $localServiceMock);

        $adapter = new \Authentication\Adapter\Adapter();
        $adapter->setEmail('codymihai@yahoo.com');
        $adapter->setPassword('steaua');
        $adapter->setServiceLocator($this->serviceManager);

        $this->assertEquals(new Result(Result::SUCCESS, 1), $adapter->authenticate());
    }
    public function testFailureAuthenticate ()
    {
        $this->markTestSkipped(
            'Skipped'
        );
        $emMock = $this->getMock(
            '\Doctrine\ORM\EntityManager',
            array('getRepository',
                    'getClassMetadata',
                    'persist',
                    'flush'
                    ),
            array(),
            '',
            false
        );
        $localServiceMock = $this->getMockBuilder('Dcl\Service\BdLocalService')
                ->disableOriginalConstructor()
                ->getMock();
        //User mockup
        $authMockup = $this->getMockBuilder('Dcl\SubService\AuthAdapter')
                ->disableOriginalConstructor()
                ->getMock();

        $userManegementMockup = $this->getMockBuilder('Dcl\SubService\UserManagement')
                ->disableOriginalConstructor()
                ->getMock();
        $userManegementMockup->expects($this->once())
                ->method('isLock')
                ->will($this->returnValue(false));

        $authentificationServiceMockup = $this->getMockBuilder('Zend\Authentication\AuthenticationService')
                ->disableOriginalConstructor()
                ->getMock();

        $adpterMock = $this->getMockBuilder('\DoctrineModule\Authentication\Adapter\ObjectRepository')
                ->setConstructorArgs(
                    array(
                        array(
                            'objectManager' => $emMock,
                            'identityClass' => 'Dcl\Entity\User',
                            'identityProperty' => 'email',
                            'credentialProperty' => 'password',
                            'credentialCallable' => "Dcl\Entity\User::hashPassword"
                            )
                        )
                )
                ->setMethods(array('authenticate'))
                ->getMock();

        $result = new Result(Result::FAILURE, null);

        $authentificationServiceMockup->expects($this->once())
                ->method('authenticate')
                ->will($this->returnValue($result));

        $authMockup->expects($this->once())
                ->method('getAdapter')
                ->will($this->returnValue($adpterMock));

        $authentificationServiceMockup->expects($this->once())
                ->method('setAdapter')
                ->with($adpterMock);

        $this->serviceManager->setService('Dcl\SubService\AuthAdapter', $authMockup);
        $this->serviceManager->setService('\Doctrine\ORM\EntityManager', $emMock);
        $this->serviceManager->setService('Dcl\SubService\UserManagement', $userManegementMockup);
        $this->serviceManager->setService('Zend\Authentication\AuthenticationService', $authentificationServiceMockup);
        $this->serviceManager->setService('\DoctrineModule\Authentication\Adapter\ObjectRepository', $adpterMock);
        $this->serviceManager->setService('Dcl\Service\BdLocalService', $localServiceMock);

        $adapter = new \Authentication\Adapter\Adapter();
        $adapter->setEmail('codymihai@yahoo.com');
        $adapter->setPassword('steaua');
        $adapter->setServiceLocator($this->serviceManager);

        $this->assertEquals(new Result(Result::FAILURE, null), $adapter->authenticate());
    }
    public function testIsBlockAuthenticate ()
    {
        $this->markTestSkipped(
            'Skipped'
        );
        $localServiceMock = $this->getMockBuilder('Dcl\Service\BdLocalService')
                ->disableOriginalConstructor()
                ->getMock();

        $authMockup = $this->getMockBuilder('Dcl\SubService\AuthAdapter')
                ->disableOriginalConstructor()
                ->getMock();

        $userManegementMockup = $this->getMockBuilder('Dcl\SubService\UserManagement')
                ->disableOriginalConstructor()
                ->getMock();
        $userManegementMockup->expects($this->once())
                ->method('isLock')
                ->will($this->returnValue(true));

        $authentificationServiceMockup = $this->getMockBuilder('Zend\Authentication\AuthenticationService')
                ->disableOriginalConstructor()
                ->getMock();

        $this->serviceManager->setService('Dcl\SubService\AuthAdapter', $authMockup);
        $this->serviceManager->setService('Dcl\SubService\UserManagement', $userManegementMockup);
        $this->serviceManager->setService('Zend\Authentication\AuthenticationService', $authentificationServiceMockup);
        $this->serviceManager->setService('Dcl\Service\BdLocalService', $localServiceMock);

        $adapter = new \Authentication\Adapter\Adapter();
        $adapter->setEmail('codymihai@yahoo.com');
        $adapter->setPassword('steaua');
        $adapter->setServiceLocator($this->serviceManager);

        $this->assertEquals(new ResultAdapter(ResultAdapter::FAILURE_IS_LOCK, null), $adapter->authenticate());
    }
}
