<?php
namespace Application\Document;

class Samsarole extends Model
{

    public $name;

    public $role;

    public $title;

    public $subtitle;

    public $logo;

    public $users = array();

    public $settings = array();

    public static function getRelationType($name)
    {
        $relations = array();
        $relations['users'] = Model::MANY_TO_ONE;
        $relations['settings'] = Model::SIMPLE_REF;
        return self::getRelationFromArray($name, $relations);
    }

    public static function getPK()
    {
        return 'name';
    }

    public static function getFieldType()
    {
        $fields = array();
        $fields[0]['type'] = self::FILE_FORMAT;
        $fields[0]['key'] = "logo";
        return $fields;
    }

    public function getDefaultSettings()
    {
        $settings = $this->getInstances("Setting");
        if (isset($settings) && sizeof($settings) > 0) {
            return $settings[0];
        }
        return null;
    }
}