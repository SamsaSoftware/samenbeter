<?php
namespace Application\Document;

/**
 * @ODM\Document(collection="samsas")
 */
class Samsa extends Model
{
    
    /**
     * @ODM\Id
     */
    public $id;

    /**
     * @ODM\Field(type="string")
     */
    public $name;

    public $deleted = 0;

    public $organizations = array();


    public static function getRelationType($name)
    {
        $relations = array();
        // 020 - one-to-one
        $relations['organizations'] = Model::OWNING_ONE_TO_MANY;
        return self::getRelationFromArray($name, $relations);
    }

    public static function getPK()
    {
        return "name";
    }

    
}