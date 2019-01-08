<?php
namespace Application\Service\Daemon;

use Application\Controller\MongoObjectFactory;

/**
 * Demonstrate using a Core_ITask object to create a more complex task
 * This won't actually do anything but you get the idea
 *
 * @author Shane Harter
 * @todo Create a plausible demo of a complex task that implements \Core_ITask
 */
class ExecutorTask extends SamsaTask
{

    /**
     * A handle to the Daemon object
     *
     * @var \Core_Daemon
     */
    private $daemon = null;

    private $object;

    private $method;
    
    private $orgName;

    public function __construct($orgName, $workspace, $job, $data)
    {
        $this->orgName = $orgName;
        $this->job = $job;
        $this->workspace = $workspace;
    }

    /**
     * Called on Construct or Init
     *
     * @return void
     */
    public function setup()
    {
        $this->daemon = JobExecutorDaemon::getInstance();
    }

    public function teardown()
    {
        $this->job->reload();
        $this->daemon->log("Starting teardown..." . json_encode($this->job));
        if ($this->job->repeat == true) {
            $this->daemon->log("repeat job -- ");
            // format of delay is in seconds
            $delayToAdd = $this->job->delay;
            $format = "d-m-Y H:i:s";
            // now date
            $startdatePO = new \DateTime('now', new \DateTimeZone('Europe/Stockholm'));
            $this->daemon->log("repeat job -- " . json_encode($startdatePO));
            // $startdatePO = \DateTime::createFromFormat($format, $job->datetime);
            $this->daemon->log("ProcessManager 2");
            $delay_nextI = new \DateInterval("PT" . $delayToAdd . "S");
            $startdatePO->add($delay_nextI);
            $this->job->reload();
            $this->job->datetime = $startdatePO->format("d-m-Y H:i:s");
            $this->daemon->log(json_encode($this->job));
            $this->job->status = "scheduled";
        } else {
            $this->job->status = "stopped";
        }
        $this->job->update();
    }

    /**
     * This is called after setup() returns
     *
     * @return void
     */
    public function start()
    {
        // This is just going to sleep a really long time.
        // I'll replace this with a better demo in a future version.
        // The idea is that the easiest way to parallelize some code in your daemon is to pass a closure or callback to the task() method.
        // But if you have a complex task that can get ugly and difficult to read and understand. In those cases, you can implement
        // a Core_ITask object like this one.
        $this->daemon->log("Starting Executor..." . $this->job->getIdAsString());
        
        $action = $this->job->method;
        // echo 1;
        
        if ($this->job->integrationHandler) {
            $integrationHandler = $this->workspace->getIntegrationHandler();
            if ((int) method_exists($integrationHandler, $action) > 0) {
                // echo 1;
                $response = $integrationHandler->{$action}($this->job->data);
            }
        } else {
            $serviceName = $this->job->objectType;
            $service = $this->job->service;
            
            if ($service == 'true') {
                $serviceClass = new \ReflectionClass("Application\\Service\\" . $serviceName);
                $class = $serviceClass->newInstanceArgs();
                
                $reflectionMethod = new \ReflectionMethod($class, $action);
                // $result = $mongoObjectFactory->update($data['objectType'], $id, $data['data']);
               
                if (isset($this->job->data)) {
                    $response = $reflectionMethod->invoke($class, $this->job->data);
                } else {
                    $response = $reflectionMethod->invoke($class, array());
                }
            } else {
                $this->daemon->log(" Executing..." . "Application\\Document\\". $this->orgName. "\\" .$this->job->objectType);
                echo $this->job->objectType;
                $mongoFactory = new MongoObjectFactory();
                $object = $mongoFactory->findInstanceByPK("Application\\Document\\". $this->orgName. "\\" .$this->job->objectType,$this->job->objectId );
                if ((int)method_exists($object, $action) > 0) {
                // // echo 1;
                 $response = $object->{$action}($this->job->data);
                }
            }
           
        }
        return true;
    }

    /**
     * Give your ITask object a group name so the ProcessManager can identify and group processes.
     * Or return Null
     * to just use the current __class__ name.
     *
     * @return string
     */
    public function group()
    {}

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
}
