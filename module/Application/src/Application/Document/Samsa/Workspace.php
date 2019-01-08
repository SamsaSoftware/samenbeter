<?php
namespace Application\Document\Samsa;

use Application\Document\WorkspaceTemplate;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Application\Document\Model;
use Application\Document\Field;
class Workspace extends WorkspaceTemplate
{
    // public $_id;
    /**
     * @ODM\Id
     */
    public $id;

    public $title;

    public $name;

    public $active;

    public $processs = array();

    public $customers = array();


    /**
     *
     * @return mixed
     */
    public function getParent()
    {
        $Object = null;
        if (! is_null($this->organizations)) {
            $refT = $this->organizations['$ref'];
            $refId = $this->organizations['$id'];
            $laf = new \Application\Controller\MongoObjectFactory();
            $typeT = ucfirst(substr($refT, 0, strlen($refT) - 1));
            $refType = $laf->getClassPath($typeT) . $typeT;
            
            $Object = $laf->findObject($typeT, $refId);
        }
        return $Object;
    }



    public static function getRelationType($name)
    {
        $relations = array();
        // 020 - one-to-one
        $relations['customers'] = Model::OWNING_ONE_TO_MANY;
        $relations['processs'] = Model::OWNING_ONE_TO_MANY;
        return self::getExtraRelationTypes($name, $relations);
    }
    public function getIntegrationHandler()
    {
        return null;
    }
    
    public function createModeledUser($userID){
        
    }


    

}

?>