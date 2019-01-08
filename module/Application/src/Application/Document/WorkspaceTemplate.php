<?php
namespace Application\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Application\Document\Model;

abstract class WorkspaceTemplate extends Indexer
{
    // public $_id;
    /**
     * @ODM\Id
     */
    public $id;

    public $title;

    public $name;

    public $active;

    public $views = array();

    public $workspaceevents = array();

    public $menus = array();

    public $samsaroles = array();
    
    public $calendars = array();

    public $masterdatas = array();

    public $templates = array();

    public $referencedatas = array();

    public $organizations;

    public $integrationHandler;

    public $cronjobs = array();

    public $workspaceDocument;

    public static function getFieldType()
    {
        $fields = array();
        $fields[0]['type'] = self::FILE_FORMAT;
        $fields[0]['key'] = "workspaceDocument";
        return $fields;
    }

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

    /**
     *
     * @return mixed
     */
    public function getOrganizations()
    {
        return $this->organizations;
    }

    /**
     *
     * @param mixed $user            
     */
    public function setOrganizations($organizations)
    {
        $this->organizations = $organizations;
    }

    public static function getExtraRelationTypes($name, $relations)
    {
        // 020 - one-to-one
        $relations['templates'] = Model::OWNING_ONE_TO_MANY;
        $relations['samsaroles'] = Model::OWNING_ONE_TO_MANY;
        $relations['views'] = Model::OWNING_ONE_TO_MANY;
        $relations['menus'] = Model::OWNING_ONE_TO_MANY;
        $relations['calendars'] = Model::OWNING_ONE_TO_MANY;
        $relations['workspaceevents'] = Model::OWNING_ONE_TO_ONE;
        $relations['masterdatas'] = Model::OWNING_ONE_TO_ONE;
        $relations['organizations'] = Model::ODM;
        $relations['referencedatas'] = Model::OWNING_ONE_TO_MANY;
        $relations['cronjobs'] = Model::OWNING_ONE_TO_MANY;
        $relations['samsaroles'] = Model::OWNING_ONE_TO_MANY;
        return self::getRelationFromArray($name, $relations);
    }

    public function cgtAdminMenu()
    {
        $adminMenus = array();
        foreach ($this->menus as $key => $value) {
            if ($this->getInstance("Menu", $value['$id'])->{'$active'}) {}
        }
    }

    public static function getPK()
    {
        return 'title';
    }

    /**
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->_id['$id'];
    }

    /**
     *
     * @param mixed $id            
     */
    public function setId($id)
    {
        $this->_id = $id;
    }

    /**
     *
     * @return mixed
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     *
     * @param mixed $id            
     */
    public function setActive($id)
    {
        $this->active = $id;
    }

    public function isActive()
    {
        return $this->active;
    }

    public function activate()
    {
        // foreach ($this->organizations as $org){
        $oInst = $this->getInstance("Organization", $this->organizations['$id']);
        // $log = \Application\Controller\Log::getInstance();
        // $log->addRow(" OO".json_encode($oInst->workspaces). ' --- '.json_encode($oInst));
        foreach ($oInst->workspaces as $wksp) {
            if ($wksp['$id'] == $this->_id['$id']) {
                $this->active = 'true';
            } else {
                $wInst = $this->getInstance("Workspace", $wksp['$id']);
                $wInst->active = 'false';
                $wInst->update();
            }
        }
        // }
        $this->update();
        // $this->update(json_encode($this));
    }

    public function initiate()
    {
        $this->createUIMasterData();
        // $this->createCalendar('default', 2016, 2017);
        $this->initiateUI();
    }

    public function createAdminUI($samsaAdmin = false)
    {
        $this->createUIMasterData();
        $this->createWorkspaceMenu();
        $this->initiateUI();
        if ($samsaAdmin) {
            $this->initiateSamsaUI();
        }
        return true;
    }

    public function updateAdminUI()
    {
        $this->deleteAdminUI();
        $this->reload();
        $this->createAdminUI();
    }

    public function importUI($data)
    {
        $log = \Application\Controller\Log::getInstance();
        $log->AddRow(' IMPORTING >-on --> ' . json_encode($this->getParent()));
        $result = array();
        $keys = array();
        $data = json_decode($data, true);
        $file = base64_decode($data['content']);
        $bService = new \Application\Service\BackupService();
        $returnV = $bService->importUI($data, $this);
        if ($returnV == true) {
            return $result["message"] = 'Data from file is imported !';
        } else {
            return $result["message"] = 'Data from file is not imported !';
        }
    }

    public function exportUI($data)
    {
        $log = \Application\Controller\Log::getInstance();
        $log->AddRow(' EXPORTING >-on --> ' . json_encode($this->getParent()));
        $result = array();
        $keys = array();
        $bService = new \Application\Service\BackupService();
        $returnV = $bService->exportUI($data, $this);
        if ($returnV == true) {
            return $result["message"] = 'Data from file is exporten !';
        } else {
            return $result["message"] = 'Data from file is not exported !';
        }
    }

    public function createCalendar($name, $year, $endyear)
    {
        if ($this->_id instanceof \MongoId) {
            $returnW = (string) $this->_id;
        } else {
            $returnW = (string) $this->_id['$id'];
        }
        $typeW = 'Workspace';
        $mObj = new \Application\Controller\MongoObjectFactory();
        $startDate = new \DateTime($year . '0101');
        $endDate = new \DateTime($endyear . '1231');
        $typeCal = 'Calendar';
        $dataCal = array(
            "name" => $name
        );
        $returnVCal = $mObj->createAndAdd($typeW, (string) $returnW, $typeCal, $dataCal);
        $calendar = $this->getInstance($typeCal, (string) $returnVCal);
        $calendar->initCalendar($name, $year, $endyear);
    }

    public function getCalendar($name)
    {
        $returnW = 0;
        if ($this->_id instanceof \MongoId) {
            $returnW = (string) $this->_id;
        } else {
            $returnW = (string) $this->_id['$id'];
        }
        $criteria = array(
            'name' => $name,
            'parent.$id' => $returnW
        );
        $typeC = 'Calendar';
        $mObj = new \Application\Controller\MongoObjectFactory();
        
        $calendar = $mObj->findObjectInstanceByCriteria($typeC, $criteria);
        return $calendar;
    }

    public function getMasterDataTexttForIndex($mastertablename, $id)
    {
        $mtable = $this->getMastertable($mastertablename);
        if (isset($mtable[0])) {
            $items = json_decode($mtable[0]->items);
            foreach ($items as $item) {
                if ($item->recid == $id) {
                    return $item->type;
                }
            }
        }
        return null;
    }

    public function getMastertable($mastertablename)
    {
        // $workspace = $this->getWorkspace();
        $filter = 'name-' . $mastertablename;
        \Application\Controller\Log::getInstance()->AddRow('TESTXXX0 ' . '  ' . $filter);
        return $this->getPathReferences('getMasterdata.getMastertable[' . $filter . ']');
    }
    
    abstract function getIntegrationHandler();
    
    abstract function createModeledUser($userId);
}

?>