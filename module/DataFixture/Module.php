<?php
namespace DataFixture;

use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Zend\Console\Adapter\AdapterInterface as Console;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\ModuleRouteListener;
use Application\Controller\ServiceLocatorFactory;

class Module implements ConsoleUsageProviderInterface
{

    public function onBootstrap(MvcEvent $e)
    {
        $config = $e->getApplication()->getConfig();
        
        $database = new \Application\DatabaseConnection\Database($config['database']['dbname'], $config['database']['username'], $config['database']['password']);
        $eventManager = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        
        $eventManager->attach(\Zend\Mvc\MvcEvent::EVENT_DISPATCH_ERROR, array(
            $this,
            'handleDispatchError'
        ));
        
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
            $translator->setLocale($user->getOrganization()
                ->getLocale() . '_' . strtoupper($user->getOrganization()
                ->getLocale()));
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
            'controller' => 'Authentication\Controller\Auth',
            'action' => 'success'
        ));
        
        $errorEvent = clone $e;
        $errorEvent->setRouteMatch($routerMatch);
        $e->stopPropagation(true);
        $em->trigger('dispatch', $errorEvent);
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

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getServiceConfig()
    {}

    public function getConsoleUsage(Console $console)
    {
        return array(
            'run organizationfixture [--verbose|-v]' => 'Run organizationfixture',
            'run chatserver [--verbose|-v]' => 'Run Chat server',
            'run startcron [--verbose|-v]' => 'Run CronService',
            'run simulatorfixture [--verbose|-v]' => 'Run simulatorfixture',
            'run updateusersfixture [--verbose|-v]' => 'Run updateusersfixture'
        );
    }
}
