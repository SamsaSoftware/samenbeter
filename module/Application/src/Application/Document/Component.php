<?php
namespace Application\Document;

class Component extends Model
{
    public $recid;
    
    public $name;
    
    public $type;

    public $references = array();
    
    public $parameters = array();
    
    public $contextmenus = array();
    
    public static function getRelationType($name)
    {
        $relations = array();
        //many to many
        $relations['parameters'] = Model::OWNING_ONE_TO_MANY;
        $relations['references'] = Model::OWNING_ONE_TO_MANY; 
        $relations['contextmenus'] = Model::OWNING_ONE_TO_MANY;
        return self::getRelationFromArray($name, $relations);
    }
    
}

?>