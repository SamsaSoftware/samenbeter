<?php

namespace Application\Document;

use Application\Document\Model;

class KPI extends Model
{

    public $name;

    public $status;

    public $objectType;

    public $method;

    public $datetime;
    
    public $type;

    public $value;
    
    public static function getPK()
    {
        return 'name';
    }
    
    public function add($typeC, $json)
    {
        $json{'workspaceId'} = $this->parent[0]{'$id'};
        return parent::add($typeC, $json);
    }
    
    public function update(){
        
    }
}