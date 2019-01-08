<?php
namespace Application\Service\Daemon;

use Application\Controller\MongoObjectFactory;
use Application\Service\Service;
use Core_Daemon;
use Application\Controller\ServiceLocatorFactory;

class JobExecutorDaemon extends Core_Daemon
{

    protected $loop_interval = 10;

    public $channel_name;

    public $started;

    public $shutdown = false;

    public $running_processes = [];

    public function __construct($pubnub = '')
    {
        $this->pubnub = $pubnub;
        $this->started = false;
    }

    /**
     * The only plugin we're using is a simple file-based lock to prevent 2 instances from running
     */
    protected function setup_plugins()
    {
        // $this->plugin('Lock_File');
    }

    /**
     * This is where you implement any once-per-execution setup code.
     *
     * @return void
     * @throws \Exception
     */
    protected function setup()
    {}

    /**
     * This is where you implement the tasks you want your daemon to perform.
     *
     * This method is called at the frequency defined by loop_interval.
     *
     * @return void
     */
    protected function execute()
    {
        $this->log(" Cron check - ");
        if ($this->shutdown) {
            $this->log("Shutting Down..");
            $this->shutdown(true);
        }
        $this->daemon = JobExecutorDaemon::getInstance();
        $service = new Service();
        $orgs = $service->getAllOrganizations();
        
        foreach ($orgs as $org) {
            $_SESSION['organization'] = $org->getClassPath();
            $this->updateScheduler($org, 'start');
            if ($org->getName() == "Samsa") {
                // main DAEMON!
                // checkForShutdown();
            }
            $this->log("  ORG " . $org->getName());
            $workspace = $org->getActiveWorkspace();
            if (isset($workspace) && ! is_null($workspace)) {
                $listJobsRet = $this->cgtScheduledJobs($workspace);
                // iterate for all Orgs
                // get all cron jobs - status - scheduled and planned for lees then 'now'
                foreach ($listJobsRet as $job) {
                    $this->log("  JOB " . $job->action);
                    if (isset($job->action) && $job->action == "shutdown") {
                        $this->log("Stopping job -- " . json_encode($job));
                        $task = isset($this->running_processes[$job->getIdAsString()]) ? $this->running_processes[$job->getIdAsString()] : $this;
                        $this->log("Stopping job -- " . json_encode($task));
                        $this->updateScheduler($org, 'stop');
                        $task->teardown();
                        // call __destruct
                        $this->shutdown();
                        $job->status = "destroyed";
                        $job->update();
                        $this->logJob($job->id, $job->name, $job->status);
                    } else {
                        
                        // update status to running
                        // use relfexion to start the TASK - job type as task name!
                        // $serviceClass = new \ReflectionClass("Application\\Service\\Daemon\\" . $job->type . "Task");
                        $serviceClass = new \ReflectionClass("Application\\Service\\Daemon\\ExecutorTask");
                        $data = json_decode($job->data, true);
                        $this->log("Starting job -- " . json_encode($job) . " --- " . $job->data . " --" . json_encode($data));
                        $task = $serviceClass->newInstance($org->getClasspath(), $workspace, $job, $data);
                        
                        $process = $this->task($task);
                        
                        $this->running_processes[$job->getIdAsString()] = $task;
                        $job->status = "running";
                        $job->update();
                        $this->logJob($job->id, $job->name, $job->status);
                        $this->log("ProcessManager 2");
                        $this->log(json_encode($job));
                    }
                }
            }
        }
        
        // TEST
        if ($this->started == 1000) {
            $this->started = true;
            $this->log("Creating Sleepy Task:" . $this->started);
            $data = array();
            $data['org'] = "Organization";
            $data['channel_name'] = "Channel-test";
            // PubnubListenerTask
            // $this->task(new PubnubListenerTask(90, $data));
            // $this->task(array( $this, 'listen' ));
        }
        return true;
    }

    public function updateScheduler($organization, $status)
    {
        $mongoObjectFactory = new MongoObjectFactory();
        $object = $mongoObjectFactory->findObject('Organization', (string) $organization->getId());
        
        $scheduler = isset($object->schedulers[0]) ? $object->schedulers[0] : null;
        $schedulerObject = null;
        if ($scheduler != null) {
            $schedulerObject = $mongoObjectFactory->findObjectInstance('Scheduler', $scheduler['$id']);
            $schedulerObject->status = $status;
            $schedulerObject->datetime = new \MongoDate(time());
            $schedulerObject->update();
        } else {
            $_SESSION['transaction_id'] = rand(0, 99999999);
            $schedulerData = [
                'name' => 'scheduler',
                'status' => $status,
                'datetime' => new \MongoDate(time())
            ];
            $mongoObjectFactory->createAndAdd('Organization', (string) $organization->getId(), 'Scheduler', $schedulerData);
        }
    }

    public function cgtScheduledJobs($workspace)
    {
        $arraySearch = array();
        $typeName = "Cronjob";
        $methodsRef = "getCronjob";
        $paramValues = array();
        $arrS = array();
        $arrS["field"] = "datetime";
        $arrS["type"] = "datetime";
        $arrS["operator"] = "less";
        
        $format = 'd-m-Y';
        $dateStart = new \DateTime('now', new \DateTimeZone('Europe/Stockholm'));
        $dateStartF = $dateStart->format("d-m-Y H:i:s"); // \DateTime::createFromFormat($format,$dateStart)
        $arrS["value"] = $dateStartF;
        $arraySearch["search"][] = $arrS;
        
        $arrS = array();
        $arrS["field"] = "status";
        $arrS["value"] = "scheduled";
        $arrS["type"] = "string";
        
        // Todo: add running status like (time && running) or (time && scheduled)
        $arraySearch['search'][] = $arrS;
        $arraySearch["searchLogic"] = "AND";
        // add order by
        $sort = array();
        $sortF = array();
        $sortF["field"] = "datetime";
        $sortF["direction"] = "asc";
        $sort[] = $sortF;
        $this->log("cgtScheduledJobs  -- " . json_encode($arraySearch));
        $resultRef = $workspace->getInstancesReference($methodsRef, $arraySearch, $sort);
        return $resultRef;
    }

    protected function listen()
    {
        $this->started = true;
        $this->log("start listening -- " . json_encode($this->pubnub));
        $this->pubnub->subscribe("Channel-test", function ($message) {
            
            $this->log("Listen now For 20 Seconds -- " . json_encode($message));
            $keepListening = true;
            return $keepListening;
        });
        $this->log("Sleeping For 20 Seconds -- ");
    }

    /**
     * Dynamically build the file name for the log file.
     * This simple algorithm
     * will rotate the logs once per day and try to keep them in a central /var/log location.
     *
     * @return string
     */
    protected function log_file()
    {
        $dir = '.';
        if (@file_exists($dir) == false)
            @mkdir($dir, 0777, true);
        
        if (@is_writable($dir) == false)
            $dir = '/example_logs';
        
        return $dir . '/log_' . date('Ymd');
    }

    public function logJob($id, $name, $status)
    {
        $serviceLocator = ServiceLocatorFactory::getInstance();
        $dm = $serviceLocator->get('doctrine.documentmanager.odm_default');
        $organizationClassPath = $_SESSION['organization'];
        $organization = $dm->getRepository("\\Application\\Document\\Organization")->findOneBy(array(
            "classpath" => $organizationClassPath
        ));
        // \Doctrine\Common\Util\Debug::dump($organization);exit;
        
        $transaction = new \Application\Document\Transaction();
        $transaction->setOrganization($organization);
        $transaction->setOrganizationId($organization->getId());
        
        $time = \DateTime::createFromFormat('U.u', sprintf('%.f', microtime(true)))->format('Y-m-d\TH:i:s.uO');
        
        $transaction->setDatetime($time);
        $transaction->setUserId('cron');
        $transaction->setTransactionid($id);
        $transaction->setTransactionname($name);
        $transaction->setTransactionobject($status);
        $transaction->setTransactiontype('scheduler');
        $dm->persist($transaction);
        $dm->flush();
    }
}
