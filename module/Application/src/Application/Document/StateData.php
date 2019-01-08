<?php
namespace Application\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document(collection="statedatas")
 */
class StateData extends Model
{

    
    const ADD = 'add';
    
    const UPDATE = 'update';

    const REMOVE = 'remove';

    /**
     * @ODM\ReferenceOne(targetDocument="State")
     */
    public $state;

    /**
     * @ODM\Id
     */
    public $id;

    /**
     * @ODM\Field(type="string")
     */
    protected $objectid;

    /**
     * @ODM\Field(type="string")
     */
    protected $objecttype;

    /**
     * @ODM\Field(type="string")
     */
    protected $data;

    /**
     * @ODM\Field(type="string")
     */
    protected $type;

    /**
     * @ODM\Field(type="date")
     */
    protected $datetime;

    /**
     *
     * @return the $objectid
     */
    public function getDatetime()
    {
        return $this->datetime;
    }

    /**
     *
     * @param field_type $id            
     */
    public function setDatetime($datetime)
    {
        $this->datetime = $datetime;
    }

    /**
     *
     * @return the $objectid
     */
    public function getObjecttype()
    {
        return $this->objecttype;
    }

    /**
     *
     * @param field_type $id            
     */
    public function setObjecttype($objecttype)
    {
        $this->objecttype = $objecttype;
    }

    /**
     *
     * @return the $objectid
     */
    public function getObjectid()
    {
        return $this->objectid;
    }

    /**
     *
     * @param field_type $id            
     */
    public function setObjectid($objectid)
    {
        $this->objectid = $objectid;
    }

    /**
     *
     * @return the $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     *
     * @param field_type $id            
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     *
     * @return string $data
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     *
     * @param string $data            
     */
    public function setState($data)
    {
        $this->state = $data;
    }

    /**
     *
     * @return string $data
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     *
     * @param string $data            
     */
    public function setData($object)
    {
        $this->data = json_encode($object);
    }

    public function setObjectData($object)
    {
        if (isset($object->_id)) {
            if ($object->_id instanceof \MongoId) {} else {
                if (is_array($object->_id)) {
                    $object->_id = new \MongoId($object->_id['$id']);
                }
            }
            $getter_names = get_class_vars(get_class($object)); // methods(get_class($this));
            
            foreach ($getter_names as $key => $value) {
                if (isset($object->{$key}) && is_scalar($object->{$key}) && $key != '_id') {
                    $object->{$key} = $object->getFormatVarialble($object->{$key}, $key);
                }
            }
        }
        $this->data = json_encode($object);
    }

    /**
     *
     * @return string $data
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     *
     * @param string $data            
     */
    public function setType($type)
    {
        $this->type = $type;
    }
}