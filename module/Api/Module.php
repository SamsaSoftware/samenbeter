<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Api;

use Application\DatabaseConnection\Database;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Application\Controller\ServiceLocatorFactory;
use Zend\Console\Adapter\AdapterInterface as Console;

class Module
{

    public function onBootstrap(MvcEvent $e)
    {
        \Zend\Uri\UriFactory::registerScheme('chrome-extension', 'Zend\Uri\Uri');
        
        $config = $e->getApplication()->getConfig();
        
        $database = new Database($config['database']['dbname'], $config['database']['username'], $config['database']['password']);
        $eventManager = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        
        $eventManager->attach(\Zend\Mvc\MvcEvent::EVENT_DISPATCH_ERROR, array(
            $this,
            'handleDispatchError'
        ));
        
        if (method_exists($e->getRequest(), 'getHeaders')) {
            $headers = $e->getRequest()->getHeaders();
            \Application\Controller\Log::getInstance()->AddRow('HEaders  ' . json_encode($headers));
            $headersArray = $headers->toArray();
            unset($headersArray['Origin']);
            $headers->clearHeaders();
            $headers->addHeaders($headersArray);
            $headers->addHeaderLine('Origin', 'file://mobile');
            
            \Application\Controller\Log::getInstance()->AddRow('HEaders  ' . json_encode($headers));
        }
        
        /*
         * $app = $e->getApplication();
         * $sm = $app->getServiceManager();
         * $router = $sm->get('router');
         * $request = $sm->get('request');
         *
         * $front = \Zend\Mvc\Controller\AbstractController:: Front::getInstance();
         * $response = new Zend_Controller_Response_Http();
         * $response->setRedirect('/profile');
         * $front->setResponse($response);
         * $matchedRoute = $router->match($request);
         * if (!$matchedRoute) {
         * $matchedRoute->setRouteMatch()
         * ->setParam('controller', 'Application\Controller\Index')
         * ->setParam('action', 'index');
         * }
         */
        
        // change language based on organization
        $dm = $e->getApplication()
            ->getServiceManager()
            ->get('doctrine.documentmanager.odm_default');
        $session = $e->getApplication()
            ->getServiceManager()
            ->get('Zend\Authentication\AuthenticationService');
        
        $identity = $session->getIdentity();
        $user = $dm->getRepository("\\Application\\Document\\User")->findOneBy(array(
            "id" => $identity['id']
        ));
        $translator = $e->getApplication()
            ->getServiceManager()
            ->get('translator');
        if ($user != null && $user->getOrganization() != null) {
            $translator->setLocale($user->getOrganization()->getLocale() .
                '_' .
                strtoupper($user->getOrganization()->getLocale()) .
                '_' . $user->getOrganization()->getClassPath());
        } else {
            $translator->setLocale('nl_NL');
        }
        
        ServiceLocatorFactory::setInstance($e->getApplication()->getServiceManager());
    }

    public function handleDispatchError(MvcEvent $e)
    {
        $error = $e->getError();
        
        /*
         * switch($error){
         * case Application::ERROR_CONTROLLER_NOT_FOUND:
         * // set custom stuff here
         * break;
         * case Application::ERROR_CONTROLLER_INVALID:
         * // set custom stuff here
         * break;
         * case Application::ERROR_ROUTER_NO_MATCH:
         * // set custom stuff here
         * break;
         * }
         */
        
        // Here is where the "re-dispatch" happens, you can change the controller and action to your needs
        $em = $e->getApplication()->getEventManager();
        $routerMatch = new \Zend\Mvc\Router\RouteMatch(array(
            'controller' => 'Api\Controller\ApiController',
            'action' => 'success'
        ));
        
        $errorEvent = clone $e;
        $errorEvent->setRouteMatch($routerMatch);
        $e->stopPropagation(true);
        $em->trigger('dispatch', $errorEvent);
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
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__
                )
            )
        );
    }

    public function getServiceConfig()
    {
        return array()

        ;
    }

    public function getViewHelperConfig()
    {
        return array()

        ;
    }

    public function getConsoleUsage(Console $console)
    {
        return array(
            'run cron [--verbose|-v]' => 'Run automated jobs: execute jobs/tasks'
        );
    }
}
