<?php

namespace DataFixture\Controller;

use \Application\Service\PubnubService;
use Zend\Mvc\Controller\AbstractActionController;
use Application\Service\Daemon\JobExecutorDaemon;

class StartCronController extends AbstractActionController
{

    public function startAction()
    {
        echo "StartCronController\n";
        $_SESSION['organization'] = 'Organization';
        $ptask = JobExecutorDaemon::getInstance();
        $ptask->run();
    }
}