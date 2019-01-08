<?php
namespace Application\Document;

class Reference extends Model
{
    public $recid;
    
    public $name;

    
    public static function getRelationType($name)
    {
        $relations = array();
        //many to many
        return self::getRelationFromArray($name, $relations);
    }
    
 
    public static function getPK()
    {
        return 'name';
    }



  

}

?>