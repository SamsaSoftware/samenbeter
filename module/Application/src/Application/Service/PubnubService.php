<?php
namespace Application\Service;

use Application\Controller\Log;
use \Pubnub\Pubnub;
use Application\Service\Daemon\JobExecutorDaemon;

class PubnubService extends Service
{

    /**
     *
     * @var ServiceManager
     */
    private static $chManager = [];

    protected $serviceLocator;

    /**
     */
    protected $organization_name = '';

    /**
     * Constructor: __construct
     * Allow for CORS, assemble and pre-process the data
     */
    public function __construct()
    {}

    public static function listen($orgId, $channelName, $method = '')
    {
        //\Application\Controller\Log::getInstance()->AddRow(' listen ' . $channelName);
    try {
        //print_r(self::getInstance($orgId));
        $ptask = JobExecutorDaemon::getInstance(self::getInstance($orgId));

        $ptask->run();
    } catch (\Exception $e) {
        print_r($e->getMessage());exit;
    }
        //Poller::getInstance()->run();
    }

    public static function publish($orgId, $channelName, $pubnubContent)
    {
        if (! is_null($channelName)) {
            $pubnubCh = $this->getInstance($orgId);
            if (! is_null($pubnubCh)) {
                $pushNum['pubnub'] = $pubnubCh->publish(
                   $channelName,
                    $pubnubContent
                );
            }
        }
    }

    /**
     * @throw ServiceLocatorFactory\NullServiceLocatorException
     *
     * @return Zend\ServiceManager\ServiceManager
     */
    public static function getInstance($org)
    {
        //var_dump(self::$chManager[$org]);exit;
        if (isset(self::$chManager[$org])) {} else {
            $organization = self::getStaticOrganization($org);
            $configuration = $organization->getConfiguration();
            $pub_key = $configuration->getValue("PUB_KEY");
            $sub_key = $configuration->getValue("SUB_KEY");
            //"pub-c-20a90445-77ca-4de0-833d-edf7cce965da"
            // "sub-c-4d14cf20-ceb6-11e6-b82b-0619f8945a4f"
            self::create($org, $pub_key, $sub_key);
            //throw new \Exception('PubNub channel is not set');
        }
        return self::$chManager[$org];
    }

    /**
     *
     * @param
     *            ServiceManager
     */
    private static function create($org, $publish_key, $subscribe_key)
    {
        if (isset(self::$chManager[$org])) {} else {
            $pubnub = new Pubnub($publish_key, $subscribe_key);
        }
        self::$chManager[$org] = $pubnub;
    }
}