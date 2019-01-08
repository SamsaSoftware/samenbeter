<?php

namespace Application\Document;


class Configuration extends Model
{

    public $name;

    public $type;

    public $valuestr;

    public static function getPK()
    {
        return 'name';
    }

    public static function getRelationType($name)
    {
        $relations = array();
        return self::getRelationFromArray($name, $relations);
    }
    
    public function getValue($key){
        $value = json_decode($this->valuestr, true);
        return $value[$key];
    }
}