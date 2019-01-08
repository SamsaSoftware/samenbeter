<?php
/**
 * Created by PhpStorm.
 * User: mihai.coditoiu
 * Date: 06.10.2015
 * Time: 21:47
 */

namespace Application\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/** @ODM\Document(collection="settings") */
class Setting extends Model {

    /** @ODM\Id */
    public $id;

    /**
     * @ODM\ReferenceOne(targetDocument="User")
     */
    public $user;

    /** @ODM\Field(type="string") */
    public $state;

    /** @ODM\Field(type="string")  */
    public $viewId;

    /** @ODM\Field(type="string")  */
    public $gridId;
    
    /** @ODM\Field(type="string")  */
    public $userId;
    
    /** @ODM\Field(type="string")  */
    public $type;
    
    /** @ODM\Field(type="int")  */
    public $deleted = 0;
    
    
    public static function getRelationType($name)
    {
        $relations = array();
        // 020 - one-to-one
        //$relations['samsaroles'] = Model::ONE_TO_MANY;
        return self::getRelationFromArray($name, $relations);
    }
    
    public function addSamsaRole($samsaRole)
    {
        $mongoObjectFactory = new \Application\Controller\MongoObjectFactory();
        $role = $mongoObjectFactory->findObjectInstanceByCriteria('Samsarole', array(
            'name' => $samsaRole
        ));
      //  \Application\Controller\Log::getInstance()->AddRow(' addSamsaRole ' . json_encode($samsaRole) . ' value ' . json_encode($role));
        if (isset($role)) {
            $this->addRemoteReferenceObject($role);
            $this->reload();
          //  \Application\Controller\Log::getInstance()->AddRow(' addSamsaRole added ' . ' value ' . json_encode($this));
        }
    }
    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }
    
    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }
    
    /**
     * @param mixed $user
     */
    public function setType($type)
    {
        $this->type = $type;
    }


    /**
     * @return mixed
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param mixed $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * @return mixed
     */
    public function getViewId()
    {
        return $this->viewId;
    }

    /**
     * @param mixed $viewId
     */
    public function setViewId($viewId)
    {
        $this->viewId = $viewId;
    }
    

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->userId;
    }
    
    /**
     * @param mixed $viewId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    /**
     * @return mixed
     */
    public function getGridId()
    {
        return $this->gridId;
    }

    /**
     * @param mixed $gridId
     */
    public function setGridId($gridId)
    {
        $this->gridId = $gridId;
    }
    /**
     *
     * @param array $states
     * @return array
     */
    public function getShowedColumns($states)
    {
        $columns = array();
        foreach ($states->columns as $column) {
            if ($column->hidden == 'false') {
                $columns[$column->field] = $column->field;
            }
        }
        return $columns;
    }
    


}