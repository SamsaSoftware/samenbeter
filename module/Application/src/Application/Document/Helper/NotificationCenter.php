<?php
namespace Application\Document\Helper;

use Application\Document\Event;
use Application\Document\Workspace;

class NotificationCenter
{
    /**
     * @var Singleton The reference to *Singleton* instance of this class
     */
    private static $instance;
    
    /**
     * 
     */
    private $classpath;
    
    /**
     * Returns the *Singleton* instance of this class.
     *
     * @return Singleton The *Singleton* instance.
     */
    public static function getInstance()
    {
        if (null === static::$instance) {
            static::$instance = new static();
        }
        
        return static::$instance;
    }

    /**
     * Protected constructor to prevent creating a new instance of the
     * *Singleton* via the `new` operator from outside of this class.
     */
    protected function __construct()
    {

    }

    /**
     * Private clone method to prevent cloning of the instance of the
     * *Singleton* instance.
     *
     * @return void
     */
    private function __clone()
    {
    }

    /**
     * Private unserialize method to prevent unserializing of the *Singleton*
     * instance.
     *
     * @return void
     */
    private function __wakeup()
    {
    }
    
    
    public function triggerEvent(Event $evt){
        // trigger Event name Rules 
        
    }
    
    public function getClasspath(){
        return $this->classpath;
    }
    
    public function setClasspath($classp){
        $this->classpath =  $classp;
    }
}

?>