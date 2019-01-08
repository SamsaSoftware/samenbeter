<?php
namespace Application\Document\Samsa;

use Application\Document\Model;

class Calendarday extends Model
{
    public $recid;
    
    public $day;
    public $processorders = array();
    public $specialdaytypes = array();
    
    public static function getRelationType($name)
    {
        $relations = array();
        //many to many
        $relations['specialdaytypes'] = Model::OWNING_ONE_TO_MANY;
        return self::getRelationFromArray($name, $relations);
    }
    
 
    public static function getPK()
    {
        return 'day';
    }



  

}

?>