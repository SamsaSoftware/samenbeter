<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class CronController extends AbstractActionController
{
    public function runAction()
    {

        $mongoObjectFactory = new MongoObjectFactory();
        $_SESSION['organization'] = 'Samsa';
        $result = $mongoObjectFactory->findAllObjectJSON('Cronjob', array());
        foreach ($result as $res) {
            //manage jobs
            //we can use $listArray = $object->executeNew("name", $method, $data['data']);
        }

        print "It must get all jobs and execute them\n";
    }

}