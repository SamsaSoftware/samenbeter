<?php

namespace Application\Document;


class Template extends Model
{

    const GENERATE_PASSWORD = 'templateGeneratePassword';
    const RESET_PASSWORD = 'resetPassword';
    const ALREADY_PART_OF_ORGANIZATION = 'alreadyPartOfOrganization';
    const REGISTERED_TO_ORGANIZATION = 'registerToOrganization';
    const CONFIRM_ACCOUNT = 'confirmAccount';

    public $recid;
    
    public $name;
    
    public $text;

    public $subject;

    public $messageTemplate;

    public $entity;
    

    public static function getPK()
    {
        return 'name';
    }
}