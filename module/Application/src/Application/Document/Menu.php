<?php
namespace Application\Document;

class Menu extends Model
{
    
    public $id;
    
    public $text;
    
    public $icon;

    public $name;
    
    public $views;

    public $type;
    
    public $scope;
    
    public $group;
    
    public $caption;
    
    public $platform;

    public $default;
    
    /**
     *
     * @return the $version
     */
    public function get__text()
    {
        return $this->text;
    }
    
    /**
     *
     * @param field_type $version
     */
    public function set__text($version)
    {
        $this->text = $version;
    }
    
    public static function getRelationType($name)
    {
        $relations = array();
        //many to many
        $relations['views'] = Model::ONE_TO_MANY;
        return self::getRelationFromArray($name, $relations);
    }

}

?>