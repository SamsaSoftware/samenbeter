<?php

namespace Chat\Controller;


use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;


class ChatController extends AbstractActionController
{

    public function indexAction()
    {
        $dm = $this->getServiceLocator()->get('doctrine.documentmanager.odm_default');
        $session = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');

        $dm = $this->getServiceLocator()->get('doctrine.documentmanager.odm_default');
        $identity = $session->getIdentity();
        $user = $dm->getRepository("\\Application\\Document\\User")->findOneBy(array(
            "id" => $identity['id']
        ));

        return new ViewModel(array(
            'user' => $user
        ));
    }


}
