<?php

namespace Application\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/** @ODM\Document(collection="userRoles") */
class UserRole extends Model
{
    const ROLE_SUPER_ADMIN = 'superadmin';
    const ROLE_ADMIN = 'admin';
    const ROLE_USER = 'user';

    /** @ODM\Id */
    public $id;

    /** @ODM\Field(type="string") */
    protected $role;

    /**
     * @return the $id
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param field_type $id
     */
    public function setId($id) {
        $this->id = $id;
    }
    /**
     * @return string $role
     */
    public function getRole() {
        return $this->role;
    }

    /**
     * @param string $role
     */
    public function setRole($role) {
        $this->role = $role;
    }
}