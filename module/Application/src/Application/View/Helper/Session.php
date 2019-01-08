<?php
namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\Authentication\AuthenticationService;
use Application;
use Application\Controller\MongoObjectFactory;

class Session extends AbstractHelper
{

    private $serviceLocator;

    private $session;

    public function setServiceLocator($serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
        $this->session = $this->serviceLocator->get('Zend\Authentication\AuthenticationService')->getIdentity();
    }

    public function getSession()
    {
        return $this->session;
    }

    public function getUserId()
    {
        $user = $this->getSession();
        return $user['id'];
    }

    public function getOrganization()
    {
        $session = $this->getSession();
        $dm = $this->serviceLocator->get('doctrine.documentmanager.odm_default');
        $user = $dm->getRepository("\\Application\\Document\\User")->findOneBy(array(
            "id" => $session['id']
        ));
        if ($user != null) {
            return $user->getOrganization();
        } else {
            return null;
        }
    }

    public function getOrganizationId()
    {
        $session = $this->getSession();
        $dm = $this->serviceLocator->get('doctrine.documentmanager.odm_default');
        $user = $dm->getRepository("\\Application\\Document\\User")->findOneBy(array(
            "id" => $session['id']
        ));
        return $user->getOrganization()->getId();
    }

    public function getWorkspaceId()
    {
        $session = $this->getSession();
        $dm = $this->serviceLocator->get('doctrine.documentmanager.odm_default');
        $user = $dm->getRepository("\\Application\\Document\\User")->findOneBy(array(
            "id" => $session['id']
        ));
        if ($user->getOrganization() != null) {
            return $user->getOrganization()
                ->getActiveWorkspace()
                ->getId();
        } else {
            return null;
        }
    }

    public function getSamsarole()
    {
        $laf = new MongoObjectFactory();
        $session = $this->getSession();
        $dm = $this->serviceLocator->get('doctrine.documentmanager.odm_default');
        $user = $dm->getRepository("\\Application\\Document\\User")->findOneBy(array(
            "id" => $session['id']
        ));
        // return json_encode($user->getSamsaroles());
        $samsaroles = $user->getSamsaroles();
        if (isset($samsaroles[0])) {
            //\Application\Controller\Log::getInstance()->AddRow(' LineFORMATprev ' . ' value ' . json_encode($samsaroles[0]) . ' - ');;
            $refT = $samsaroles[0]['$ref'];
            $refT = ucfirst(substr($refT, 0, strlen($refT) - 1));
            $objectRole = $laf->findObject($refT, $samsaroles[0]['$id']);
            //\Application\Controller\Log::getInstance()->AddRow(' LineFORMATprev ' . ' value ' . json_encode($objectRole) . ' - ');;
            $samsaRoleId = $objectRole->name;
            //$samsaRoleId = "ref|" . $samsaroles[0]['$ref'] . "|id|" . $samsaroles[0]['$id'];
            return $samsaRoleId;
        } else {
            return 'user';
        }
    }
}
