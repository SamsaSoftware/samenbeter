<?php
namespace Application\Service;

use Application\Controller\Log;
use \Pubnub\Pubnub;
use Application\Service\Daemon\JobExecutorDaemon;

class OneSignalService extends Service
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
    {}

    public static function publish($orgId, $channelName, $pubContent, $command = '')
    {
        \Application\Controller\Log::getInstance()->AddRow(' OneSignalServicexx ' .  $orgId . "  " . $channelName);
        $content = array(
            "en" => $pubContent
        );
        
        $fields = array(
            'app_id' => "7b44642e-4cef-4b95-9a8d-36618a1abe79",
            'filters' => array(
                array(
                    "field" => "tag",
                    "key" => "username",
                    "relation" => "=",
                    "value" => $channelName
                ),
                array(
                    "operator" => "AND"
                ),
                array(
                    "field" => "tag",
                    "key" => "orgId",
                    "relation" => "=",
                    "value" => "" . $orgId . ""
                )
            ),
            'data' => array(
                "command" => $command
            ),
            'contents' => $content
        );



        $fields = json_encode($fields);

        \Application\Controller\Log::getInstance()->AddRow(' OneSignalService0 ' .  $fields);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json; charset=utf-8',
            'Authorization: Basic MDVjYzIwNDEtZWRiNy00YTFiLTk5NmUtOTU0NjY0MTk2M2My'
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        $response = curl_exec($ch);
        $info = curl_getinfo($ch);
        \Application\Controller\Log::getInstance()->AddRow(' OneSignalService1 ' . json_encode($info) );
        curl_close($ch);
        
        return $response;
    }

    /**
     * @throw ServiceLocatorFactory\NullServiceLocatorException
     *
     * @return Zend\ServiceManager\ServiceManager
     */
    public static function getInstance($org)
    {}

    /**
     *
     * @param
     *            ServiceManager
     */
    private static function create($org, $publish_key, $subscribe_key)
    {}
}