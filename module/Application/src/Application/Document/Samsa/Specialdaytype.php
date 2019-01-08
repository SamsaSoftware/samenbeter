<?php
namespace Application\Document\Samsa;

use Application\Document\Model;

class Specialdaytype extends Model
{
    public $recid;
    
    public $daytype;
    
    public static function getRelationType($name)
    {
        $relations = array();
        return self::getRelationFromArray($name, $relations);
    }
    
 
    public static function getPK()
    {
        return 'daytype';
    }



  

}

?>