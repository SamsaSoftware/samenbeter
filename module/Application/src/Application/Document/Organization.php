<?php
namespace Application\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Application\Controller\MongoObjectFactory;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ODM\Document(collection="organizations")
 */
class Organization extends Model
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
     * @ODM\Field(type="string")
     */
    public $dbname;

    /**
     * @ODM\Field(type="string")
     */
    public $logo = "";

    /**
     * @ODM\Field(type="string")
     */
    public $locale;

    /**
     * @ODM\Field(type="string")
     */
    public $email;

    /**
     * @ODM\Field(type="string")
     */
    public $workspaceId = "";

    /**
     * @ODM\Field(type="string")
     */
    public $description = "";

    /**
     * @ODM\Field(type="int")
     */
    public $deleted = 0;

    /**
     * @ODM\Field(type="int")
     */
    public $organizationDbNumber;

    /**
     * @ODM\ReferenceMany(targetDocument="Setting")
     */
    public $settings = array();

    /**
     * @ODM\ReferenceMany(targetDocument="State")
     */
    public $states;

    /**
     * @ODM\ReferenceMany(targetDocument="Transaction")
     */
    public $transactions;

    public $workspaces = array();

    public $users = array();

    public $configurations = array();

    public $schedulers = array();
    


    public function addSetting(Setting $setting)
    {
        if (! $this->settings->contains($setting)) {
            $this->settings->add($setting);
        }
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

    /**
     *
     * @param
     *            mixed transactions
     */
    public function setTransactions($transactions)
    {
        $this->transactions = transactions;
    }

    /**
     *
     * @return mixed transactions
     */
    public function getTransactions()
    {
        return $this->transactions;
    }

    /**
     *
     * @return mixed
     */
    public function getId()
    {
        if (isset($this->id)) {
            return $this->id;
        }
        return $this->getIdAsString();
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
    public function getWorkspaceId()
    {
        return $this->workspaceId;
    }

    /**
     *
     * @param mixed $id            
     */
    public function setWorkspaceId($id)
    {
        
        // $this->workspaces[0] = array();
        // $this->workspaces[0]['$ref'] = 'workspaces';
        // $this->workspaces[0]['$id'] = $this->workspaceId;
        $this->workspaceId = $id;
    }

    /**
     *
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     *
     * @param mixed $name            
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     *
     * @return mixed
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     *
     * @param mixed $locale            
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     *
     * @return mixed
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     *
     * @param mixed $logo            
     */
    public function setLogo($logo)
    {
        $this->logo = $logo;
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
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     *
     * @return mixed
     */
    public function getClasspath()
    {
        return $this->classpath;
    }

    /**
     *
     * @param mixed $classpath            
     */
    public function setClasspath($classpath)
    {
        $this->classpath = $classpath;
    }
    

    /**
     *
     * @return mixed
     */
    public function getDbname()
    {
        return $this->dbname;
    }
    
    /**
     *
     * @param mixed $classpath
     */
    public function setDbname($dbname)
    {
        $this->dbname = $dbname;
    }

    /**
     *
     * @return mixed
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     *
     * @param mixed $deleted            
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;
    }

    /**
     * @return mixed
     */
    public function getOrganizationDbNumber()
    {
        return $this->organizationDbNumber;
    }

    /**
     * @param mixed $organizationDbNumber
     */
    public function setOrganizationDbNumber($organizationDbNumber)
    {
        $this->organizationDbNumber = $organizationDbNumber;
    }


    public static function getRelationType($name)
    {
        $relations = array();
        // 020 - one-to-one
        $relations['workspaces'] = Model::ODM_OWNING;
        $relations['users'] = Model::OWNING;
        $relations['schedulers'] = Model::OWNING_ONE_TO_MANY;
        $relations['configurations'] = Model::OWNING_ONE_TO_MANY;

        return self::getRelationFromArray($name, $relations);
    }

    public function getConfiguration()
    {
        $organization = $this->getInstance('Organization', (string) $this->getId());
        $configuration = isset($organization->configurations[0]) ? $organization->configurations[0] : null;

        $configurationObject = null;
        if ($configuration != null) {
            $configurationObject = $this->getInstance('Configuration', $configuration['$id']);

        }
        return $configurationObject;
    }

    public function addParentUser($id)
    {
        $user = $this->getInstance("User", $id);
        $user->parent = array();
        $user->parent[0]['$ref'] = $this->getTableName();
        $user->parent[0]['$id'] = $this->get_id();
        $user->samsaroles = array();
        $user->userRole['$id'] = new \MongoId($user->userRole['$id']);
        $user->organization['$id'] = new \MongoId($user->organization['$id']);
        $user->update();
        
        // $user = $mongoObjectFactory->findObjectJSON("User", $id);
        $reference = array();
        
        $reference['$ref'] = $user->getTableName();
        $reference['$id'] = $user->getIdAsString();
        $reference['$db'] = "zf2odm";
        
        $mongoObjectFactory = new \Application\Controller\MongoObjectFactory();
        // get main root ID!!
        $org = $mongoObjectFactory->findObject('Organization', (string) $this->getId());
        
        if (isset($org->users)) {
            $org->users[] = $reference;
        } else {
            $org->users = array();
            $org->users[] = $reference;
        }
        $org->update();
        return true;
    }

    public static function getPK()
    {
        return "name";
    }

    /**
     *
     * @param
     *            array workspaces
     */
    public function setWorkspaces($org)
    {
        $this->workspaces = $org;
    }

    /**
     *
     * @return string workspaces
     */
    public function getWorkspaces()
    {
        return $this->workspaces;
    }

    public function add($typeC, $json)
    {
        $arr = $json; // json_decode($json);
        $arr['organizations'] = array();
        $arrId = $this->_id;
      
        $retId = parent::add($typeC, $arr);
        if ($typeC == "Workspace") {
            $this->initWorkspace($retId);
        }
        return $retId;
    }

    public function updateUIActiveWorkspace()
    {
        \Application\Controller\Log::getInstance()->AddRow(' --> 2X1 ');
        $orgI = $_SESSION['organization'];
        $_SESSION['organization'] = $this->classpath;
        $_SESSION['dbname'] = $this->dbname;
        $mongoObjectFactory = new \Application\Controller\MongoObjectFactory();
        $object = $mongoObjectFactory->findObject('Organization', (string) $this->getIdAsString());
        //\Application\Controller\Log::getInstance()->AddRow(' --> 2X2 ' . json_encode($object));
        
        $workspaces = $object->getInstances('Workspace');
        //\Application\Controller\Log::getInstance()->AddRow(' --> 2X3 ' . json_encode($workspaces) . '-' . json_encode($object));
        
        if (empty($workspaces)) {
            return false;
        }
        foreach ($workspaces as $workspace) {
            if ($workspace->isActive() == 'true') {
                $workspaces[0]->deleteAdminUI();
            }
        }
        
        $workspaces = $object->getInstances('Workspace');
        foreach ($workspaces as $workspace) {
            if ($workspace->isActive() == 'true') {
                $samsaAdmin = false;
                if ($this->name == "Samsa") {
                    $samsaAdmin = true;
                }
                $workspaces[0]->createAdminUI($samsaAdmin);
            }
        }
        $_SESSION['organization'] = $orgI;
        return $workspaces[0];
    }

    public function cleanupOrganization()
    {
        \Application\Controller\Log::getInstance()->AddRow(' Organization ->  cleanupOrganization 1');
        $orgI = $_SESSION['organization'];
        $_SESSION['organization'] = $this->classpath;
        $_SESSION['dbname'] = $this->dbname;
        $mongoObjectFactory = new \Application\Controller\MongoObjectFactory();
        $object = $mongoObjectFactory->findObject('Organization', (string) $this->getIdAsString());
        \Application\Controller\Log::getInstance()->AddRow(' Organization ->  cleanupOrganization 2 :: organization -----' . json_encode($object));

        $workspaces = $object->getInstances('Workspace');
        \Application\Controller\Log::getInstance()->AddRow('Organization ->  cleanupOrganization 3 :: workspaces -----' . json_encode($workspaces));

        if (empty($workspaces)) {
            return false;
        }

        foreach ($workspaces as $workspace) {
            if ($workspace->isActive() == 'true') {
                \Application\Controller\Log::getInstance()->AddRow('Organization ->  cleanupOrganization 4 :: workspace -----' . json_encode($workspace));

                $bService = new \Application\Service\BackupService();
                $returnV = $bService->cleanup($workspace);
            }
        }

        $_SESSION['organization'] = $orgI;
        return $workspaces[0];
    }


    public function initWorkspace($retId)
    {
        $this->id = 0;
        $arr = array();
        $wksp = $this->getInstance("Workspace", $retId);
        $wksp->organizations['$ref'] = $wksp->parent[0]['$ref'];
        $wksp->organizations['$id'] = new \MongoId($wksp->parent[0]['$id']);
        // $wksp->_id == (string)$wksp->_id['$id'];
        $wksp->update();
        $viewId = $wksp->createViewAndMenuLink("Workspaces", "/list/Workspace", "Organization", "admin");
        $this->createAdminField($viewId, "Workspace", Field::TYPE_TEXT, "", "name", "name", true, "", "");
        $this->createAdminField($viewId, "Workspace", Field::TYPE_TEXT, "", "active", "active", false, "", "");
        $this->createAdminField($viewId, "Workspace", \Application\Document\Field::TYPE_FILE, "", "workspaceDocument", "UI Documents^style='width: 200px; height: 80px'", false, "", "");
         
        // > create the dialog with the corresponding fields
        $this->createAdminField($viewId, "importUI", \Application\Document\Field::TYPE_FILE, "", "fileName", "File", true, "", "");
        $this->createAdminField($viewId, "importUI", \Application\Document\Field::TYPE_BUTTON, "", "save", "Import UI", true, "method", "", "importUI(@fileName@);", "", "showMessage(\"@name@\");");
        
        // > create the dialog with the corresponding fields
        $this->createAdminField($viewId, "exportUI", \Application\Document\Field::TYPE_TEXT, "", "fileName", "Name", true, "", "");
        $this->createAdminField($viewId, "exportUI", \Application\Document\Field::TYPE_BUTTON, "", "save", "Export UI", false, "method", "", "exportUI(@fileName@);", "", "showMessage(\"@name@\");");
         
        
        $this->createAdminField($viewId, "Workspace", Field::TYPE_BUTTON, "", "button", "updateUI", true, 'method', "", 'updateAdminUI();');
        $this->createAdminField($viewId, "Workspace", Field::TYPE_BUTTON, "", "button", "initiate", true, 'method', "", 'initiate();');
        $this->createAdminField($viewId, "Workspace", Field::TYPE_BUTTON, "", "button", "save", true, "saveObject", "");
        $this->createAdminField($viewId, "Workspace", Field::TYPE_BUTTON, "", "button", "activate", false, 'method', "", 'activate();');
        $typeW = 'Workspace';
        $typeV = 'View';
        $typeComponent = 'Component';
        $data = array(
            "name" => "grid"
        );
        $mObj = new \Application\Controller\MongoObjectFactory();
        $returngridRefComponentPackage = $mObj->createAndAdd($typeV, (string) $viewId, $typeComponent, $data);
        
        // create the context menu and link it to the template
        $typeContextmenu = 'Contextmenu';
        $data = array(
            "name" => "export",
            "actionExecution" => "service",
            "actionResponse" => "showMessage(\"%message%\");",
            "link" => "Export",
            "parentType" => "Organization",
            "objectType" => "Workspace",
            "method" => "export",
            "parameters" => "",
            "serviceName" => "\Application\Service\BackupService",
            "serviceMethod" => "export",
            "template" => ""
        );
        $return = $mObj->createAndAdd($typeComponent, (string) $returngridRefComponentPackage, $typeContextmenu, $data);
        $data = array(
            "name" => "exportUI",
            "actionExecution" => "method",
            "actionResponse" => "showMessage(\"%message%\");",
            "link" => "ExportUI",
            "parentType" => "Organization",
            "objectType" => "Workspace",
            "method" => "exportUI(@fileName@);",
            "parameters" => "name",
            "template" => "templateklant"
        );
        $return = $mObj->createAndAdd($typeComponent, (string) $returngridRefComponentPackage, $typeContextmenu, $data);
        $data = array(
            "name" => "importUI",
            "actionExecution" => "inputform",
            "actionResponse" => "showMessage(\"%message%\");",
            "link" => "importUI",
            "parentType" => "Organization",
            "objectType" => "Workspace",
            "method" => "importUI",
            "parameters" => "to_parent.email+from_parent.parent.email",
           // "serviceName" => "\Application\Service\BackupService",
           //"serviceMethod" => "importUI",
            "template" => "templateklant"
        );
        $return = $mObj->createAndAdd($typeComponent, (string) $returngridRefComponentPackage, $typeContextmenu, $data);
   
        
        // create the context menu and link it to the template
        $typeContextmenu = 'Contextmenu';
        $data = array(
            "name" => "migrateWorkspace",
            "actionExecution" => "service",
            "actionResponse" => "showMessage(\"%message%\");",
            "link" => "Migrate",
            "parentType" => "Organization",
            "objectType" => "Workspace",
            "method" => "migrate",
            "parameters" => "",
            "serviceName" => "\Application\Service\BackupService",
            "serviceMethod" => "migrateWorkspace",
            "template" => ""
        );
        $return = $mObj->createAndAdd($typeComponent, (string) $returngridRefComponentPackage, $typeContextmenu, $data);
    }

    /**
     *
     * @return string $active workspace
     */
    public function getActiveWorkspace()
    {
        $mongoObjectFactory = new \Application\Controller\MongoObjectFactory();
        $object = $mongoObjectFactory->findObject('Organization', (string) $this->getId());
        \Application\Controller\Log::getInstance()->AddRow(' --> 2X ' . json_encode($object));
        
        //
        $workspaces = $object->getInstances('Workspace');
        \Application\Controller\Log::getInstance()->AddRow(' --> 2X ' . json_encode($workspaces) . '-' . json_encode($object));
        
        if (empty($workspaces)) {
            $json = array(
                "active" => 'true',
                "title" => "Home",
                "name" => "/view/1"
            );
            $object->add("Workspace", $json);
        }
        foreach ($workspaces as $workspace) {
            if ($workspace->isActive() === 'true') {
                return $workspace;
            }
        }
        if (isset($workspaces[0])) {
            return $workspaces[0];
        } else {
            return null;
        }
    }

    public function set($data)
    {
        if (is_null($data)) {
            $data = array();
        }
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                
                // \Application\Controller\Log::getInstance()->AddRow(' Reference '.$keyS. ' value '.json_encode($value));
                if (count($this->getRelationType($key)) > 0) {
                    $this->{$key} = array();
                    $reference = array();
                    // \Application\Controller\Log::getInstance()->AddRow(' Owning ' . $key . ' value ' . json_encode($value));
                    $reference['$ref'] = $key;
                    $reference['$id'] = new \MongoId($value['$id']['$id']);
                    $this->{$key}[] = $reference;
                } else {
                    $this->{$key} = $value;
                }
            } else {
                $this->{$key} = $value;
            }
        }
    }

    public function addSamsaUser($userNameEmail, $role)
    {
        $m = new \MongoClient();
        $mongoObjectFactory = new \Application\Controller\MongoObjectFactory();
        
        $dbmain = $m->{$mongoObjectFactory->getDBName("Organization")};
        // $organizationTable = $dbmain->selectCollection("userRoles");
        print_r($mongoObjectFactory->getDBName("Organization"));
        $users = $dbmain->selectCollection("users");
        $userRoles = $dbmain->selectCollection("userRoles");
        
        $role = $userRoles->findOne(array(
            'role' => $role
        ));
        $user = array(
            'name' => $userNameEmail,
            'password' => md5('12345'),
            'email' => $userNameEmail,
            'userRole' => array(
                '$ref' => 'userRoles',
                '$id' => $role['_id'],
                '$db' => 'zf2odm'
            ),
            'organization' => array(
                '$ref' => 'organizations',
                '$id' => $this->getIdAsString(),
                '$db' => 'zf2odm'
            ),
            'parent' => array(
                0 => array(
                    '$ref' => 'organizations',
                    '$id' => $this->getIdAsString(),
                    '$db' => 'zf2odm'
                )
            ),
            'deleted' => 0
        );
        
        $users->insert($user);
    }

    public function getQuickInstancesCriteria($typeC, $criteria, $references = null, $index = 0, $offset = 0, $search = '', $sort = '')
    {
        //\Application\Controller\Log::getInstance()->AddRow(' RESULTget2 ' . json_encode($this) . $typeC);
        
        if ($typeC === 'Transaction') {
            // \Application\Controller\Log::getInstance()->AddRow(' RESULTget3 ' . $typeC);
            $criteria = array(
                'organization.$id',
                new \MongoId($this->getIdAsString())
            );
        }
        $laf = new \Application\Controller\MongoObjectFactory();
        return parent::getQuickInstancesCriteria($typeC, $criteria, $references, $index, $offset, $search, $sort);
    }

    public function countQuickInstancesCriteria($typeC, $criteria, $search = '', $index = 0, $offset = 0)
    {
        if ($typeC === 'Transaction') {
            $criteria = array(
                'organization.$id',
                new \MongoId($this->getIdAsString())
            );
        }
        $laf = new \Application\Controller\MongoObjectFactory();
        return parent::countQuickInstancesCriteria($typeC, $criteria, $index, $offset, $search);
    }
}