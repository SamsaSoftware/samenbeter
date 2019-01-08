<?php
namespace Application\Document;

class View extends Model
{
    public $recid;
    
    public $title;

    public $name;

    public $parentType;

    public $parentId;

    public $fields = array();

    public $links = array();

    public $menus = array();
    
    public $components = array();

    public static function getRelationType($name)
    {
        $relations = array();
        //many to many
        $relations['components'] = Model::OWNING_ONE_TO_MANY;
        $relations['fields'] = Model::OWNING_ONE_TO_MANY;
        $relations['menus'] = Model::ONE_TO_MANY;
        return self::getRelationFromArray($name, $relations);
    }
    
    
    public static function getPK()
    {
        return 'title';
    }
    
    
    public function cgtLinks($criteria)
    {
        \Application\Controller\Log::getInstance()->AddRow(' --> Get Links ' . json_encode($criteria));
        $items = array();
        $item = array();
       // the component id is $criteria[1]
        if (isset($this->links[$criteria[1]])) {
            $allCriteriasArray = explode("&", $this->links[$criteria[1]]);
            // find the reference View link id :
            // we support two formats:
            // view ref
            // popup parent ref
            foreach ($allCriteriasArray as $key => $value) {
                $laf = new \Application\Controller\MongoObjectFactory();
                
                $criteriasArray = array();
                $criteriasArray = explode("-", $value);
                $nameOfView = $criteriasArray[0];
                $id = $value;
                if ( isset($criteriasArray[1])) {
                    // popup ONLY for the time being
                    $item['_id'] = $id;
                  
                    $item['link'] = $nameOfView;
                    $item['action'] = $criteriasArray[1];
                    if (! is_null($criteriasArray[2])) {
                        $item['viewId'] = $criteriasArray[2];
                    }
                    if (! is_null($criteriasArray[3])) {
                        $item['parentType'] = $criteriasArray[3];
                    }
                    if (! is_null($criteriasArray[4])) {
                        $item['objectType'] = $criteriasArray[4];
                    }
                    if (! is_null($criteriasArray[5])) {
                        $item['method'] = $criteriasArray[5];
                    }
                } else {
                    // view link reference for the time being
                    /* $viewRef = $laf->findObjectByCriteria("\\Application\\Document\\View", array(
                        "title" => $nameOfView
                    ));*/
                    $viewRef = $laf->findObjectJSON("\\Application\\Document\\View", $nameOfView);
                    $item['_id'] = (string) $viewRef['_id'];
                    $item['link'] = $viewRef['title'];
                }
                
                $items[] = $item;
            }
        }
        return $items;
    }
}

?>