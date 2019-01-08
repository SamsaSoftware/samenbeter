<?php
namespace Application\Controller;

abstract class ObjectFactory
{
    
    public abstract function createObject();
    
    public abstract function find($type );
    
}

?>