<?php
namespace Application\Document;

class Event extends Model
{
   
    const CREATED = 'CREATED';
    const PROCESSED = 'PROCESSED';    

    public $name;

    public $type;

    public $objectId;
    
    public $notes;
    
    public $state;

    public $datetime;

}