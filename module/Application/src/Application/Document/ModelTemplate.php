<?php

namespace Application\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document(collection="modeltemplates")
 */
class ModelTemplate
{

    /**
     * @ODM\Id
     */
    public $id;

    /**
     * @ODM\Field(type="string")
     */
    public $name;


    /**
     * @ODM\Field(type="string")
     */
    public $classpath;

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
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
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


}