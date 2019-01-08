<?php
namespace Application\Service;

use Application\Controller\Log;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class OneSignalService extends Service
{

    protected $clients = array();
    
    public function __construct()
    {
         
    }
    
    public function onOpen(ConnectionInterface $conn)
    {
        // var_dump($conn);
        $querystring = $conn->WebSocket->request->getQuery()->toArray();
        $organizationId = $querystring['organization'];
        if (isset($this->clients[$organizationId])) {
            $this->clients[$organizationId]->attach($conn);
        } else {
            $this->clients[$organizationId] = new \SplObjectStorage();
            $this->clients[$organizationId]->attach($conn);
    
        }
        // echo json_encode($this->clients);
        $querystring = $conn->WebSocket->request->getQuery();
        //print_r($querystring);
        echo "New connection! ({$conn->resourceId}) for ({ $organizationId })\n";
    }
    
    public function onMessage(ConnectionInterface $from, $msg)
    {
        $querystring = $from->WebSocket->request->getQuery()->toArray();
        $organizationId = $querystring['organization'];
        // var_dump($this->clients);
        foreach ($this->clients[$organizationId] as $client) {
            if ($from !== $client) {
                $client->send($msg);
            }
        }
    }
    
    public function onClose(ConnectionInterface $conn)
    {
        $querystring = $conn->WebSocket->request->getQuery()->toArray();
    
        $organizationId = $querystring['organization'];
    
        $this->clients[$organizationId]->detach($conn);
    
        echo "Connection {$conn->resourceId} has disconnected\n";
    }
    
    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "An error has occurred: {$e->getMessage()}\n";
    
        $conn->close();
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