<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Application\DatabaseConnection\Database;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Application\Controller\ServiceLocatorFactory;
use Zend\Console\Adapter\AdapterInterface as Console;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $config = $e->getApplication()->getConfig();

        $database = new Database(
            $config['database']['dbname'],
            $config['database']['username'],
            $config['database']['password']
        );
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        $eventManager->attach(\Zend\Mvc\MvcEvent::EVENT_DISPATCH_ERROR, array($this, 'handleDispatchError'));




        /*$app = $e->getApplication();
        $sm = $app->getServiceManager();
        $router = $sm->get('router');
        $request = $sm->get('request');

        $front = \Zend\Mvc\Controller\AbstractController:: Front::getInstance();
        $response = new Zend_Controller_Response_Http();
        $response->setRedirect('/profile');
        $front->setResponse($response);
        $matchedRoute = $router->match($request);
        if (!$matchedRoute) {
            $matchedRoute->setRouteMatch()
                ->setParam('controller', 'Application\Controller\Index')
                ->setParam('action', 'index');
        }*/

        //change language based on organization
        $dm = $e->getApplication()->getServiceManager()->get('doctrine.documentmanager.odm_default');
        $session = $e->getApplication()->getServiceManager()->get('Zend\Authentication\AuthenticationService');

        $identity = $session->getIdentity();
        $user = $dm->getRepository("\\Application\\Document\\User")->findOneBy(array(
            "id" => $identity['id']
        ));
        $translator = $e->getApplication()->getServiceManager()->get('translator');
        if ($user != null && $user->getOrganization() != null) {
            $translator->setLocale(
                $user->getOrganization()->getLocale() .
                '_' .
                strtoupper($user->getOrganization()->getLocale()) .
                '_' . $user->getOrganization()->getClassPath());
        } else {
            $translator->setLocale('nl_NL');
        }

        ServiceLocatorFactory::setInstance($e->getApplication()->getServiceManager());

    }

    public function handleDispatchError(MvcEvent $e){

        $error  = $e->getError();

        /*switch($error){
            case Application::ERROR_CONTROLLER_NOT_FOUND:
                // set custom stuff here
                break;
            case Application::ERROR_CONTROLLER_INVALID:
                // set custom stuff here
                break;
            case Application::ERROR_ROUTER_NO_MATCH:
                // set custom stuff here
                break;
        }*/

        // Here is where the "re-dispatch" happens, you can change the controller and action to your needs
        $em = $e->getApplication()->getEventManager();
        $routerMatch = new \Zend\Mvc\Router\RouteMatch(array('controller' => 'Authentication\Controller\Auth', 'action' => 'success'));

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
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }


    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                '\Application\Form\AddUser' => function ($sm) {

                    $form = new \Application\Form\AddUser($sm);

                    return $form;
                },
                '\Application\Form\AddOrganization' => function ($sm) {

                    $form = new \Application\Form\AddOrganization($sm);

                    return $form;
                }
            )
        );
    }

    public function getViewHelperConfig ()
    {
        return array(
            'factories' => array(
                'flashMessages' => function ($sm) {
                    $helper = new View\Helper\FlashMessages;
                    return $helper;
                },
                'sessionHelper' => function ($sm) {
                    $helper = new View\Helper\Session;
                    $sm = $sm->getServiceLocator(); // get service locator
                    $helper->setServiceLocator($sm); // set service locator
                    return $helper;
                }
            )
        );
    }

    public function getConsoleUsage(Console $console)
    {
        return array(
            'run cron [--verbose|-v]' => 'Run automated jobs: execute jobs/tasks'
        );
    }
}
