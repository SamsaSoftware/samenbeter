<?php
/**
 * Created by PhpStorm.
 * User: coditoiumihai
 * Date: 11/12/2016
 * Time: 18:38
 */

namespace Application\Document;


class Map extends Model
{

    public $recid;

    public $name;

    public $latitude;

    public $longitude;


    public static function getPK()
    {
        return 'name';
    }
}