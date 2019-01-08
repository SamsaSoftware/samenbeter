<?php
namespace Application\Service;


use Application\Controller\Log;
use Application\Controller\ServiceLocatorFactory;


class RESTService extends Service
{

    protected $serviceLocator;
    /**
     * Property: method
     * The HTTP method this request was made in, either GET, POST, PUT or DELETE
     */
    protected $method = '';
    

   /**
    * 
    */ 
   protected $organization_name = '';
   
   
   /**
    *
    */
   protected $object_type = '';
    
   
   /**
    *
    */
   protected $object_id = '';
   
   
    /**
     * Property: verb
     * An optional additional descriptor about the endpoint, used for things that can
     * not be handled by the basic methods. eg: /files/process
     */
    protected $verb = '';

    /**
     * Property: file
     * Stores the input of the PUT request
    */
    protected $file = Null;
    
    protected  $log ;
        
        // 
    
    
    /**
     * Constructor: __construct
     * Allow for CORS, assemble and pre-process the data
     */
    public function __construct($organization) {
        $this->serviceLocator = ServiceLocatorFactory::getInstance();

        $this->organization_name = $organization;
    }
    

    public function processAPI($action , $data) {
        try {
            $dm = $this->serviceLocator->get('doctrine.documentmanager.odm_default');
            $organization = $dm->getRepository("\\Application\\Document\\Organization")->findOneBy(array(
                "classpath" => $this->organization_name
            ));
           // \Doctrine\Common\Util\Debug::dump($this->organization_name);
           // \Doctrine\Common\Util\Debug::dump($organization);

            $log = Log::getInstance();
            $workspace = $organization->getActiveWorkspace();

           // $log->AddRow("  REST request on ==" . json_encode($workspace) . " Method " .$action. ' args ' .json_encode($data) );

            $integrationHandler = $workspace->getIntegrationHandler();

            //        echo 1;
            if ((int)method_exists($integrationHandler, $action) > 0) {
                //            echo 1;
                $response = $integrationHandler->{$action}($data);
            
            }
        } catch(\Exception $e) {
            $response = "".$e;

        }
        return $response;
    }
    

}