<?php
namespace Application\Document;

class Contextmenu extends Model
{
    public $recid;
    
    public $name;
    
    public $actionExecution;
    
    public $link;
    
    public $icon;
    
    public $viewId;
    
    public $parentType;
    
    public $objectType;
    
    public $method;
    
    public $serviceName;
    
    public $serviceMethod;
    
    public $type;
    
    public $actionResponse;

    public $template;

    public $params;

    public static function getPK()
    {
        return 'name';
    }



  

}

?>