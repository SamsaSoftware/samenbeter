<?php

namespace Application\Document;


class Scheduler extends Model
{

    public $name;

    public $status;

    public $datetime;

    public static function getPK()
    {
        return 'name';
    }

    public static function getRelationType($name)
    {
        $relations = array();
        return self::getRelationFromArray($name, $relations);
    }
}