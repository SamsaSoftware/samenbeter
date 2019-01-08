<?php

namespace Acl;

use Zend\ModuleManager\ModuleManager;
use Zend\Mvc\MvcEvent;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager = $e->getApplication()->getEventManager();
        $application = $e->getApplication();
        $sm = $application->getServiceManager();
        $sharedManager = $application->getEventManager()->getSharedManager();

        $router = $sm->get('router');
        $request = $sm->get('request');

        $matchedRoute = $router->match($request);
        if (!empty($matchedRoute)) {
            $sharedManager->attach(
                'Zend\Mvc\Controller\AbstractActionController',
                'dispatch',
                function ($e) use ($sm) {
                    $acl = $sm->get('Acl\Controller\Acl');
                    if (!$acl->isAllowed($e)) {
                        // make redirect
                        $router = $e->getRouter();
                        $response = $e->getResponse();
                        $url = $router->assemble(
                            array(),
                            array('name' => 'auth')
                        );
                        $response->setStatusCode(302);
                        $response->getHeaders()->addHeaderLine('Location', $url);
                        $e->stopPropagation();
                    }
                },
                2
            );
        }
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
                'Zend\Loader\StandardAutoloader' => array(
                        'namespaces' => array(
                                __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                        ),
                ),
        );
    }

    public function getServiceConfig()
    {
        return array(
                'invokables' => array(
                        'Acl\Controller\AclController' => 'Acl\Controller\AclController',
                        'Zend\Permissions\Acl\Acl' => 'Zend\Permissions\Acl\Acl'
                ),
                'factories' => array(
                        'Acl\Controller\Acl' => function ($sm) {
                            return new \Acl\Controller\AclController($sm);
                        }
                )
        );
    }

    public function getViewHelperConfig()
    {
        return array(
            'factories' => array(
                'permission' => function($sm) {
                    $helper = new  View\Helper\Permission;
                    $sm = $sm->getServiceLocator();// get service locator
                    $helper->setServiceLocator($sm);// set service locator
                    return $helper;
                }
            )
        );
   }
}
