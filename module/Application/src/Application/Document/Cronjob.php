<?php
namespace Application\Document;

use Application\Document\Model;

class Cronjob extends Model
{

    public $name;

    public $status;

    public $objectType;

    public $objectId;

    public $method;

    public $datetime;
    
    public $time;

    public $data;

    public $repeat;

    public $delay;

    public $action;

    public $integrationHandler;

    public $service;

    public static function getRelationType($name)
    {
        $relations = array();
        return self::getRelationFromArray($name, $relations);
    }
}