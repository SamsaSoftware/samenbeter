<?php
namespace Acl\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\Authentication\AuthenticationService;

class Permission extends AbstractHelper
{
    private $serviceLocator;

    public function setServiceLocator($serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    private function isAllowed($role, $helper)
    {
        $config = $this->serviceLocator->get('config');
        $usersRoles = $config['usersRoles'];
        if (!isset($usersRoles[$role]['helpers'])) {
            return false;
        }

        $helperAccess = $usersRoles[$role]['helpers'];
        if (empty($helperAccess)) {
            return false;
        }

        return in_array($helper, $helperAccess);
    }

    public function checkAccess($helper)
    {
        $session = $this->serviceLocator->get('Zend\Authentication\AuthenticationService');
        if (!$session->hasIdentity()) {
            return false;
        }

        $identity = $session->getIdentity();
        $dm = $this->serviceLocator->get('doctrine.documentmanager.odm_default');
        $user = $dm->getRepository("\\Application\\Document\\User")->findOneBy(array("id" => $identity['id']));
        $curentUserRole = $user->getUserRole()->getRole();

        return $this->isAllowed($curentUserRole, $helper);
    }

    public function getUser(){
        $session = $this->serviceLocator->get('Zend\Authentication\AuthenticationService');
        $identity = $session->getIdentity();
        if ($session->hasIdentity()){
            $dm = $this->serviceLocator->get('doctrine.documentmanager.odm_default');
            return $dm->getRepository("\\Application\\Document\\User")->findOneBy(array("id" => $identity['id']));
        }
    }
    public function getAcl()
    {
        return $this->serviceLocator->get('Zend\Permissions\Acl\Acl');
    }
}
