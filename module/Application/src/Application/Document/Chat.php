<?php

namespace Application\Document;


class Chat extends Model
{
    public $name;

    public $recid;

    public static function getPK()
    {
        return 'name';
    }
}