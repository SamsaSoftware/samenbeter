<?php

namespace Acl\Controller;

use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Role\GenericRole as Role;
use Zend\Permissions\Acl\Resource\GenericResource as Resource;
use Zend\Authentication\AuthenticationService;

class AclController
{
    private $acl;
    private $serviceLocator;

    public function __construct($serviceLocator)
    {
        $this->serviceLocator = $serviceLocator; //set service locator

        $this->acl = $this->serviceLocator->get('Zend\Permissions\Acl\Acl');
        $this->acl->deny();

        $this->getUsersRoles();
    }

    private function getUsersRoles()
    {
        $config = $this->serviceLocator->get('config');
        $usersRoles = $config['usersRoles'];
        $resources = $config['resources'];

        foreach ($resources as $resource) {
            $this->acl->addResource($resource);
        }

        $anonymousRole = new Role('anonymous');
        $this->acl->addRole($anonymousRole);

        /*$this->acl->allow('superadmin');*/

        foreach ($usersRoles as $role => $modules) {
            if (!empty($modules)) {
                if ($role != 'anonymous') {
                    $this->acl->addRole(new Role($role), $anonymousRole); //inherits from anonymous
                }
                foreach ($modules as $module => $privileges) {
                    if ($module == 'helpers' || empty($privileges) || !in_array($module, $resources)) {
                        continue;
                    }
                    foreach ($privileges as $privilege) {
                        $this->acl->allow($role, $module, $privilege);
                    }
                }
            }
        }
        //$this->acl->allow('anonymous');
    }

    public function isAllowed($e)
    {
        $response = $e->getResponse();
        $session = $this->serviceLocator->get('Zend\Authentication\AuthenticationService');
        $router = $e->getRouter();

        $controller = $e->getTarget();
        $controllerClass = get_class($controller);

        $routeMatch = $e->getRouteMatch();

        //$moduleName = ($routeMatch->getParam('moduleName'))?$routeMatch->getParam('moduleName'):'application';
        $moduleName = $routeMatch->getMatchedRouteName();

        $actionName = $routeMatch->getParam('action', 'not-found');

        $userRole = 'anonymous';
        //if user is authenticated
        if ($session->hasIdentity()) {
            $identity = $session->getIdentity();
            $dm = $this->serviceLocator->get('doctrine.documentmanager.odm_default');
  //          print_r($identity);
            $user = $dm->getRepository("\\Application\\Document\\User")->findOneBy(array("id" => $identity['id']));
//var_dump($user);exit;
            $userRole = $user->getUserRole()->getRole();
        }

        //check is allowed
        $routeMatch = $this->serviceLocator->get('Application')->getMvcEvent()->getRouteMatch();

        if (!$this->acl->isAllowed($userRole, $moduleName, $actionName)) {
            return false;
        }

        return true;
    }
}
