<?php
namespace Application\Service\Daemon;

use Application\Service\PubnubService;

/**
 * Demonstrate using a Core_ITask object to create a more complex task
 * This won't actually do anything but you get the idea
 *
 * @author Shane Harter
 * @todo Create a plausible demo of a complex task that implements \Core_ITask
 */
class PubnubListenerTask extends SamsaTask
{

    /**
     * A handle to the Daemon object
     *
     * @var \Core_Daemon
     */
    private $daemon = null;

    private $pubnub;

    private $channel_name;

    private $keepListening;


    public function __construct($workspace, $job, $data)
    {
        $this->job = $job;
        //\Application\Controller\Log::getInstance()->AddRow(' listen mihai ' . $data);
        // $pubnubservice = new PubnubService();
        $this->pubnub = PubnubService::getInstance($data['org']);
        // notification method:: method name :: pubnub"channel_name"Handler"
        $this->channel_name = $data['channel_name'];
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

    /**
     * Called on Destruct
     *
     * @return void
     */
    public function teardown()
    {
        $this->keepListening = false;
        self::teardown();
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
        $this->daemon->log("Starting Pubnub...");
        $this->keepListening = true;
        $this->listen();
        if ($this->pubnub)
            $this->daemon->log("pubnub");
    }

    protected function listen()
    {
        $this->daemon->log("Starting Pubnub listener on ..." . $this->channel_name);
        // $this->log("Listen now For 20 Seconds -- ");
        $this->pubnub->subscribe($this->channel_name, function ($message) {
            $this->daemon->log("Got pubnub message -- " . json_encode($message));
            $integrationHandler = $this->workspace->getIntegrationHandler();
            if ((int) method_exists($integrationHandler, "pubnub".$this->channel_name."Handler") > 0) {
                // echo 1;
                // method name :: pubnub_".$this->channel_name."Handler"
                $integrationHandler->{"pubnub_".$this->channel_name."Handler"}($message);
            }
            
            return $this->keepListening;
        });
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
