<?php
namespace Application\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zend\Server\Reflection\ReflectionClass;
use Application\Service\StateService;

/**
 * @ODM\Document(collection="users")
 */
class User extends Model
{

    const SUPER_ADMIN = 'superadmin';

    const ADMIN = 'admin';

    const USER = 'user';

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
    public $lastName;

    /**
     * @ODM\Field(type="string")
     * @ODM\Index(unique=true, order="asc")
     */
    public $email;

    /**
     * @ODM\Field(type="string")
     */
    public $address;

    /**
     * @ODM\Field(type="string")
     */
    public $phone;

    /**
     * @ODM\Field(type="boolean")
     */
    public $settingHouder = false;

    /**
     * @ODM\Field(type="string")
     */
    public $password;

    /**
     * @ODM\Field(type="int")
     */
    public $deleted = 0;

    /**
     * @ODM\ReferenceOne(targetDocument="UserRole")
     */
    public $userRole;

    /**
     * @ODM\Field(type="string")
     */
    public $userRoleId;

    /**
     * @ODM\Field(type="collection")
     */
    public $samsaroles = array();

    /**
     * @ODM\ReferenceOne(targetDocument="Organization")
     */
    public $organization;

    /**
     * @ODM\ReferenceOne(targetDocument="ResetToken", cascade={"persist"})
     */
    public $resetToken;

    /**
     * @ODM\ReferenceMany(targetDocument="Setting", mappedBy="user")
     */
    public $settings;

    /**
     * @ODM\ReferenceMany(targetDocument="Session", mappedBy="user")
     */
    public $sessions;

    /**
     * @ODM\Field(type="string")
     */
    public $organizationList;

    /**
     * @ODM\ReferenceMany(targetDocument="State", mappedBy="user")
     */
    public $states;

    public static function getRelationType($name)
    {
        $relations = array();
        // 020 - one-to-one
        $relations['samsaroles'] = Model::ONE_TO_MANY;
        $relations['userRole'] = Model::ODM;
        $relations['resetToken'] = Model::ODM;
        $relations['organization'] = Model::ODM;
        return self::getRelationFromArray($name, $relations);
    }

    public function addOrganization(Organization $organization, $roleId = null)
    {
        $organizations = json_decode($this->organizationList);
        $organizations[] = array( 'organization' => $organization->getId(), 'samsarole' => $roleId);
        $this->organizationList = json_encode($organizations);
        return $this;
    }

    /**
     *
     * @return mixed
     */
    public function getOrganizationList()
    {
        return $this->organizationList;
    }

    public function removeOrganization(Organization $organization)
    {
        // TODO
        // $this->organizationList->removeElement($organization);
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
     * @return the $name
     */
    public function getName()
    {
        return $this->name;
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
     * @param field_type $name            
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     *
     * @return the $password
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     *
     * @param field_type $samsaroles            
     */
    public function setSamsaroles($samsaroles)
    {
        $this->samsaroles = $samsaroles;
    }

    /**
     *
     * @return the $samsaroles
     */
    public function getSamsaroles()
    {
        return $this->samsaroles;
    }

    /**
     *
     * @param field_type $password            
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     *
     * @return string $role
     */
    public function getUserRole()
    {
        return $this->userRole;
    }

    /**
     *
     * @param string $role            
     */
    public function setUserRole($userRole)
    {
        $this->userRole = $userRole;
    }

    /**
     *
     * @return string $role
     */
    public function getUserRoleId()
    {
        return $this->userRoleId;
    }

    /**
     *
     * @param string $role            
     */
    public function setOrganization($org)
    {
        $this->organization = $org;
    }

    /**
     *
     * @return string $role
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     *
     * @return mixed
     */
    public function getResetToken()
    {
        return $this->resetToken;
    }

    /**
     *
     * @param mixed $resetToken            
     */
    public function setResetToken($resetToken)
    {
        $this->resetToken = $resetToken;
    }

    /**
     *
     * @return mixed
     */
    public function getSettingHouder()
    {
        return $this->settingHouder;
    }

    /**
     *
     * @param mixed $resetToken            
     */
    public function setSettingHouder($settingHouder)
    {
        $this->settingHouder = $settingHouder;
    }

    /**
     *
     * @param mixed $resetToken            
     */
    public function isSettingHouder()
    {
        if ($this->substr_startswith($this->email, 'admin')) {
            return true;
        } else 
            if ($this->settingHouder == true || $this->settingHouder == 1 || $this->settingHouder == 'true') {
                return true;
            }
        return false;
    }

    /**
     *
     * @param string $role            
     */
    public function setUserRoleId($userRoleId)
    {
        $this->userRoleId = $userRoleId;
    }

    /**
     *
     * @return mixed
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     *
     * @return mixed
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     *
     * @param mixed $lastName            
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     *
     * @return mixed
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     *
     * @param mixed $address            
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

    /**
     *
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     *
     * @param mixed $email            
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     *
     * @return mixed
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     *
     * @param mixed $phone            
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    /**
     *
     * @param mixed $settings            
     */
    public function setSettings($settings)
    {
        $this->settings = $settings;
    }

    /**
     *
     * @param mixed $settings            
     */
    public function setSessions($sessions)
    {
        $this->sessions = $sessions;
    }

    /**
     *
     * @param mixed $states            
     */
    public function setStates($states)
    {
        $this->states = $states;
    }

    /**
     *
     * @return mixed $states
     */
    public function getStates()
    {
        return $this->states;
    }

    public static function hashPassword(User $user, $password)
    {
        $user->password = md5($password);
        return $user->password;
    }

    public function copySettings($samsaRole)
    {
        if ($this->isSettingHouder()) {} else {
            $mongoObjectFactory = new \Application\Controller\MongoObjectFactory();
            $role = $mongoObjectFactory->findObjectInstanceByCriteria('Samsarole', array(
                'name' => $samsaRole
            ));
            \Application\Controller\Log::getInstance()->AddRow(' copySettings ' . json_encode($samsaRole) . ' value ' . json_encode($role));
            if (isset($role)) {
                $settingsDef = $role->getInstances("Setting");
                \Application\Controller\Log::getInstance()->AddRow(' copySettings 1 ' . json_encode($settingsDef) . ' value ' );
                
                foreach ($settingsDef as $settingDef) {
                    \Application\Controller\Log::getInstance()->AddRow(' copySettings 2 ' . json_encode($settingDef) . ' value ' );
                    
                    $this->addSetting($settingDef);
                }
            }
        }
    }

    
    public function addSamsaRole($samsaRole)
    {
        $mongoObjectFactory = new \Application\Controller\MongoObjectFactory();
        $role = $mongoObjectFactory->findObjectInstanceByCriteria('Samsarole', array(
            'name' => $samsaRole
        ));
        \Application\Controller\Log::getInstance()->AddRow(' addSamsaRole ' . json_encode($samsaRole) . ' value ' . json_encode($role));
        if (isset($role)) {
            $this->addRemoteReferenceObject($role);
            $this->reload();
            \Application\Controller\Log::getInstance()->AddRow(' addSamsaRole added ' . ' value ' . json_encode($this));
        }
    }

    public function addSetting($setting)
    {
        $result = 'ok';
        try {
            $stateService = new StateService();
            $result = $stateService->saveUserState($setting->getViewId(), $this->getId(), $this, $setting->getGridId(), json_decode($setting->getState(), true), "gridstate");
        } catch (\Exception $e) {
            \Application\Controller\Log::getInstance()->AddRow(" Exception add Setting -  " . $e->getMessage());
            $result = 'false';
        }
        return $result;
    }

    public function readMainSamsaRole()
    {
        $mongoObjectFactory = new \Application\Controller\MongoObjectFactory();
        $userI = $mongoObjectFactory->findObjectInstanceByCriteria('User', array(
            'email' => $this->email
        ));
        \Application\Controller\Log::getInstance()->AddRow(' ADDINGREOL5 ' . ' value ' . json_encode($userI));
        $ref = $userI->getReferences('samsaroles');
        
        if (isset($ref) && sizeof($ref) > 0) {
            return $ref[0];
        }
        return null;
        // \Application\Controller\Log::getInstance()->AddRow(' ADDING5 ' . ' value ' . json_encode($this));
    }
}