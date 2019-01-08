<?php
/**
 * Created by PhpStorm.
 * User: mihai.coditoiu
 * Date: 06.10.2015
 * Time: 21:47
 */
namespace Application\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document(collection="states")
 */
class State extends Model
{

    /**
     * @ODM\Id
     */
    public $id;
    
    /**
     * @ODM\Field(type="string")
     */
    public $transactionid;

    /**
     * @ODM\ReferenceOne(targetDocument="Organization")
     */
    public $organization;

    /**
     * @ODM\Field(type="string")
     */
    public $state;

    public $relations = array();

    /**
     * @ODM\ReferenceMany(targetDocument="StateData", mappedBy="state")
     */
    public $statedatas;

    /**
     * @ODM\Field(type="string")
     */
    public $organizationId;

    /**
     * @ODM\Field(type="int")
     */
    public $deleted = 0;
    
    /** @ODM\Field(type="date") */
    protected $datetime;
    
    
    /**
     * @return the $objectid
     */
    public function getDatetime() {
        return $this->datetime;
    }
    
    /**
     * @param field_type $id
     */
    public function setDatetime($datetime) {
        $this->datetime = $datetime;
    }
    /**
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     *
     * @param mixed $id            
     */
    public function setId($id)
    {
        $this->id = $id;
    }
    /**
     *
     * @return mixed
     */
    public function getTransactionid()
    {
        return $this->transactionid;
    }
    
    /**
     *
     * @param mixed $id
     */
    public function setTransactionid($id)
    {
        $this->transactionid = $id;
    }

    /**
     *
     * @return mixed
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     *
     * @param mixed $user            
     */
    public function setOrganization($organization)
    {
        $this->organization = $organization;
    }

    /**
     *
     * @return mixed
     */
    public function getRelations()
    {
        return $this->relations;
    }

    /**
     *
     * @param mixed $user            
     */
    public function setRealtions($relations)
    {
        $this->relations = relations;
    }

    /**
     *
     * @param mixed $user            
     */
    public function addRelation($relation)
    {
        if (isset($this->relations) && is_array($this->relations) && !in_array($relation, $this->relations)) {
            $this->relations[] = $relation;
        }
    }

    /**
     *
     * @param mixed $user            
     */
    public function cleanRelations()
    {
        $this->relations = array();
    }

    public function removeKeyFromRelations($key)
    {
        $state = array();
        foreach ($this->relations as $keyIn) {
            if ($keyIn === $key) {} else {
                $state[] = $keyIn;
            }
        }
        $this->relations = array();
        $this->relations = $state;
        return $this->relations;
    }

    /**
     *
     * @param mixed $user            
     */
    public function getRelation($key)
    {
        if (isset($this->relations) && is_array($this->relations) && in_array($key, $this->relations)) {
            //\Application\Controller\Log::getInstance()->AddRow(' LineUUU ' . $key . ' value ' . json_encode($this->relations));
            return $key;
        }
        return null;
    }

    /**
     *
     * @return mixed
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     *
     * @param mixed $state            
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     *
     * @return mixed
     */
    public function getStateDatas()
    {
        return $this->statedatas;
    }

    /**
     *
     * @param mixed $viewId            
     */
    public function setStateDatas($data)
    {
        $this->statedatas = $data;
    }

    /**
     *
     * @return mixed
     */
    public function getOrganizationId()
    {
        return $this->organizationId;
    }

    /**
     *
     * @param mixed $viewId            
     */
    public function setOrganizationId($organizationId)
    {
        $this->organizationId = $organizationId;
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