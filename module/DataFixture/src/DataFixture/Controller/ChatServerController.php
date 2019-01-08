<?php
/**
 * Created by PhpStorm.
 * User: coditoiumihai
 * Date: 14/09/16
 * Time: 22:18
 */

namespace DataFixture\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Chat\Document\FirstChat\Chat;
//use React\ZMQ\Context;
//use React\EventLoop\Factory;


class ChatServerController extends AbstractActionController
{
    
    public function startAction()
    {
        $loop   = Factory::create();
        $pusher = new Chat();
     //   $context = new Context($loop);
      //  $pull = $context->getSocket(\ZMQ::SOCKET_PULL);
      //  $pull->bind('tcp://127.0.0.1:5555'); // Binding to 127.0.0.1 means the only client that can connect is itself
      //  $pull->on('message', array($pusher, 'pushRefresh'));
        
        print_r( 'starting ..');
        $server = IoServer::factory(
            new HttpServer(
                new WsServer(
                    $pusher
                )
            ),
            8081
        );
        $server->run();
    }
}