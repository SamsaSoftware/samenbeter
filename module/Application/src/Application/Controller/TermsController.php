<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class TermsController extends AbstractActionController
{

    public function termsAction()
    {
        $result = new ViewModel();
        $result->setTerminal(true);

        return $result;
    }
    public function privacyAction()
    {
        $result = new ViewModel();
        $result->setTerminal(true);

        return $result;
    }

    public function copyrightAction()
    {
        $result = new ViewModel();
        $result->setTerminal(true);

        return $result;
    }
}