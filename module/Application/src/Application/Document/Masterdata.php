<?php
namespace Application\Document;

class Masterdata extends Model
{
    public $recid;
    
    public $name;

    public $mastertables = array();
    
    
    
    public static function getRelationType($name)
    {
        $relations = array();
        //many to many
        $relations['mastertables'] = Model::OWNING_ONE_TO_MANY;
        return self::getRelationFromArray($name, $relations);
    }
    
    public function add($typeC, $json){
        $json{'workspaceId'} = $this->parent[0]{'$id'};
        return parent::add($typeC, $json);
    }

}

?>