<?php

namespace Application\Service;



class SchedulerService extends Service
{


    /**
     * @param $viewId
     * @param $componentId
     * @param $type
     * @return mixed
     */
    public function executeJob($organizationId)
    {

        $dm = $this->serviceLocator->get('doctrine.documentmanager.odm_default');
     
        $organization = $dm->getRepository("\\Application\\Document\\Organization")->findOneBy(array(
            "id" => $organizationId
        ));

        $workspace =  (string) $organization->getActiveWorkspace();
       
        if ($workspace != null) {
            $jobs = $workspace->getInstances("Cronjobs");
            foreach ($jobs as $job){
                if ((int)method_exists($workspace, $job->method) > 0) {
                    //            echo 1;
                    $return = $workspace->{$job->method}($job->data);
                    
                }
            }
            
        }

        return true;
    }


   
}