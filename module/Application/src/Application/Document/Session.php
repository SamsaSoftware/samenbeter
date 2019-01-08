<?php
/**
 * Created by PhpStorm.
 * User: mihai.coditoiu
 * Date: 06.10.2015
 * Time: 21:47
 */

namespace Application\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/** @ODM\Document(collection="sessions") */
class Session extends Model {

    /** @ODM\Id */
    public $id;

    /**
     * @ODM\ReferenceOne(targetDocument="User")
     */
    public $user;

    /** @ODM\Field(type="string") */
    public $token;

    /** @ODM\Field(type="string")  */
    public $viewId;

    /** @ODM\Field(type="string")  */
    public $organization;
    
    /** @ODM\Field(type="string")  */
    public $userId;
    
    /** @ODM\Field(type="string")  */
    public $workspaceId;

    /** @ODM\Field(type="string")  */
    public $classpath;
    
    /** @ODM\Field(type="int")  */
    public $deleted = 0;
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
    public function getToken()
    {
        return $this->token;
    }
    
    /**
     * @param mixed $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }


    /**
     * @return mixed
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * @param mixed $organization
     */
    public function setOrganization($organization)
    {
        $this->organization = $organization;
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
    public function getWorkspaceId()
    {
        return $this->workspaceId;
    }

    /**
     * @param mixed $gridId
     */
    public function setWorkspaceId($gridId)
    {
        $this->workspaceId = $gridId;
    }
    
    /**
     * @return mixed
     */
    public function getClasspath()
    {
        return $this->classpath;
    }
    
    /**
     * @param mixed $classpath
     */
    public function setClasspath($classpath)
    {
        $this->classpath = $classpath;
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