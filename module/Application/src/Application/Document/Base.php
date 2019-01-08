<?php
namespace Application\Document;

use Zend\Stdlib\JsonSerializable;
use Application\Controller\MongoObjectFactory;
use Application\Controller\Application\Controller;
use Application\DatabaseConnection\Database;
use Zend\Validator\NotEmpty;

function executeSafe($command, $command2 = null)
{
    try {
        eval($command);
        return true;
    } catch (\Exception $e) {
        if ($command2) {
            try {
                eval($command2);
                return true;
            } catch (\Exception $e) {}
        }
    }
    // do someting
}

function rand_color()
{
    return sprintf('#%06X', mt_rand(0, 0xFFFFFF));
}

abstract class Base
{

    const ONE_TO_ONE = 'one-to-one';

    const ONE_TO_MANY = 'one-to-many';

    const MANY_TO_ONE = 'many-to-one';

    const OWNING_ONE_TO_ONE = 'owning-one-to-one';

    const OWNING_ONE_TO_MANY = 'owning-one-to-many';

    const SIMPLE_REF_OWNING = 'simple-ref_owning';

    const SIMPLE_REF = 'simple-ref';

    const DECL_OWNING = 'declarative-head';

    const DECL_REF = 'decalrative-ref';

    const DECL_REF_SIMPLE = 'decalrative-ref-simple';

    const DECL_RESULT = 'declarative-result';

    const ODM = 'odm';

    const ODM_OWNING = 'odm_owning';

    const OWNING = 'owning';

    const FORMAT_DATE = 'd-m-Y';

    const FORMAT_DATE_TIME = 'd-m-Y H:i';

    const CALENDAR_REF = 'calendars';

    const KPI_REF = 'kpis';

    const AUTO_INCREMENT = "AUTO_INCREMENT";

    const MASTER_DATA = "MASTER_DATA";

    const CUSTOM_FORMAT = "CUSTOM_FORMAT";

    const FILE_FORMAT = "FILE_FORMAT";

    public $recid;

    public $id;

    public $id_key;

    public $parent = array();

    public $version;

    public $deleted = 0;

    /**
     *
     * @return the $deleted
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     *
     * @param field_type $deleted            
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;
    }

    public function __construct($json = false)
    {
        if ($json) {
            $data = array();
            $data = json_decode($json, true);
            if (isset($data['_id'])) {
                $this->beforeLoad(json_decode($json, true));
                $this->load(json_decode($json, true));
                $this->afterLoad(json_decode($json, true));
            } else {
                $this->beforeCreate(json_decode($json, true));
                $this->set(json_decode($json, true));
                $this->afterCreate(json_decode($json, true));
            }
        }
    }

    /**
     *
     * @param string $title            
     * @param string $name            
     * @param string $parentType            
     * @param string $scope            
     * @return \Application\Controller\unknown
     */
    protected function createAdminViewAndMenuLink($title, $name, $parentType)
    {
        return $this->createViewAndMenuLink($title, $name, $parentType, 'admin');
    }

    /**
     *
     * @param string $title            
     * @param string $name            
     * @param string $parentType            
     * @param string $scope            
     * @return \Application\Controller\unknown
     */
    protected function createViewAndMenuLink($title, $name, $parentType, $scope = 'user')
    {
        (new \ReflectionClass($this))->getShortName();
        $typeW = (new \ReflectionClass($this))->getShortName();
        $typeV = 'View';
        $mObj = new \Application\Controller\MongoObjectFactory();
        if ($this->_id instanceof \MongoId) {
            $returnW = (string) $this->_id;
        } else {
            $returnW = (string) $this->_id['$id'];
        }
        $log = \Application\Controller\Log::getInstance();
        
        // $log->AddRow(" Execute This action CREATE MENU -< " . $returnW . " >-on " . json_encode($this) . ' --> ');
        $data = array(
            "title" => $title,
            "name" => $name,
            "parentType" => $parentType,
            "scope" => $scope
        );
        
        $returnView = $mObj->createAndAdd($typeW, $returnW, $typeV, $data);
        
        $data = array(
            "id" => $title,
            "text" => $title,
            "type" => "button",
            "click" => "buttonClickHandler",
            "scope" => 'admin'
        );
        
        $typeM = 'Menu';
        $returnMenu = $mObj->createAndAdd($typeW, (string) $returnW, $typeM, $data);
        
        $object = $mObj->findObject("Menu", (string) $returnMenu);
        $reference = array();
        $reference['$ref'] = "views";
        $reference['$id'] = (string) $returnView;
        // $object->{'views'}[] = $reference;
        $object->addReferenceObject("views", $reference);
        return $returnView;
    }

    public function initiateUI()
    {
        $viewId = $this->createViewAndMenuLink("Users", "/list/User", "Organization", "admin");
        $this->createAdminField($viewId, "User", Field::TYPE_TEXT, "", "name", "name", true, "", "");
        $this->createAdminField($viewId, "User", Field::TYPE_CHECKBOX, "", "settingHouder", "Main settings", true, "", "");
        $this->createAdminField($viewId, "User", \Application\Document\Field::TYPE_REF_VALUE, "", "samsaroles", "Samsaroles", false, "", "getMethodResultListReference?objectType=@type@&methodName=getSamsarole&id=@objectId@&action=@action@");
        $this->createAdminField($viewId, "User", Field::TYPE_BUTTON, "", "button", "save", true, "saveObject", "");
        
        $viewId = $this->createViewAndMenuLink("Samsaroles", "/list/Samsarole", "Workspace", "admin");
        $this->createAdminField($viewId, "Samsarole", \Application\Document\Field::TYPE_FORM, "", "form", "formTiltlr", true, "", '{"maxWidth":600, "maxHeight":800}');
        $this->createAdminField($viewId, "Samsarole", Field::TYPE_TEXT, "", "name", "name", true, "", "");
        $this->createAdminField($viewId, "Samsarole", Field::TYPE_ENUM, "USERROLETYPE.type", "role", "role", true, "", "");
        $this->createAdminField($viewId, "Samsarole", Field::TYPE_TEXT, "", "title", "title", false, "", "");
        $this->createAdminField($viewId, "Samsarole", Field::TYPE_TEXT, "", "subtitle", "subtitle", false, "", "");
        $this->createAdminField($viewId, "Samsarole", Field::TYPE_FILE, "", "logo", "Logo^style='width: 200px; height: 80px'", false, "", "");
        $this->createAdminField($viewId, "Samsarole", Field::TYPE_BUTTON, "", "button", "save", true, "saveObject", "");
        
        $viewId = $this->createAdminViewAndMenuLink("Transactions", "/list/Transaction", "Organization", "admin");
        $this->createAdminField($viewId, "Transaction", Field::TYPE_TEXT, "", "transactionname", "Name", false, "", "");
        $this->createAdminField($viewId, "Transaction", Field::TYPE_TEXTAREA, "", "transactionobject", "Data", false, "", "");
        
        $viewId = $this->createAdminViewAndMenuLink("Views", "/list/View", "Workspace");
        $this->createAdminField($viewId, "View", Field::TYPE_TEXT, "", "name", "name", true, "", "");
        $this->createAdminField($viewId, "View", Field::TYPE_TEXT, "", "title", "title", false, "", "");
        $this->createAdminField($viewId, "View", Field::TYPE_TEXT, "", "parentType", "parentType", false, "", "");
        $this->createAdminField($viewId, "View", Field::TYPE_TEXT, "", "parentId", "parentId", false, "", "");
        $this->createAdminField($viewId, "View", Field::TYPE_BUTTON, "", "button", "save", true, "saveObject", "");
        
        $viewId = $this->createAdminViewAndMenuLink("Menus", "/list/Menu", "Workspace");
        $this->createAdminField($viewId, "Menu", Field::TYPE_TEXT, "", "name", "name", true, "", "");
        $this->createAdminField($viewId, "Menu", Field::TYPE_TEXT, "", "type", "type", false, "", "");
        $this->createAdminField($viewId, "Menu", Field::TYPE_TEXT, "", "scope", "scope", false, "", "");
        $this->createAdminField($viewId, "Menu", Field::TYPE_TEXT, "", "text", "text", false, "", "");
        $this->createAdminField($viewId, "Menu", Field::TYPE_TEXT, "", "platform", "platform", false, "", "");
        // /$this->createAdminField($viewId, "Menu", Field::TYPE_REF_VALUE, "", "views", "views", true, "", "getMethodResultListReference?objectType=@type@&methodName=getView&id=@objectId@&action=@action@");
        $this->createAdminField($viewId, "Menu", Field::TYPE_REF_VALUE, "", "views", "views", true, "", "getMethodResultListReference?objectType=@type@&methodName=getView&id=@objectId@&action=@action@");
        $this->createAdminField($viewId, "Menu", Field::TYPE_BUTTON, "", "button", "save", true, "saveObject", "");
        
        $viewId = $this->createAdminViewAndMenuLink("GridsView", "/listOwningReference/View.getComponent.getParameter", "Workspace");
        $this->createAdminField($viewId, "View", Field::TYPE_TEXT, "", "name", "name", true, "", "");
        $this->createAdminField($viewId, "View", Field::TYPE_BUTTON, "", "button", "save", true, "saveObject", "");
        $this->createAdminField($viewId, "Component", Field::TYPE_TEXT, "", "name", "name", true, "", "");
        $this->createAdminField($viewId, "Component", Field::TYPE_BUTTON, "", "button", "save", true, "saveObject", "");
        $this->createAdminField($viewId, "Parameter", Field::TYPE_TEXT, "", "name", "name", true, "", "");
        $this->createAdminField($viewId, "Parameter", Field::TYPE_ENUM, "PARAMETERTYPE.type", "type", "type", false, "", "");
        $this->createAdminField($viewId, "Parameter", Field::TYPE_TEXT, "", "referencelink", "referencelink", false, "", "");
        $this->createAdminField($viewId, "Parameter", Field::TYPE_TEXT, "", "schema", "schema", false, "", "");
        $this->createAdminField($viewId, "Parameter", Field::TYPE_TEXT, "", "definition", "defintion", false, "", "");
        $this->createAdminField($viewId, "Parameter", Field::TYPE_TEXT, "", "actionExecution", "actionExecution", false, "", "");
        $this->createAdminField($viewId, "Parameter", Field::TYPE_TEXT, "", "actionResponse", "actionResponse", false, "", "");
        $this->createAdminField($viewId, "Parameter", Field::TYPE_TEXT, "", "resource", "resource", false, "", "");
        $this->createAdminField($viewId, "Parameter", Field::TYPE_BUTTON, "", "button", "save", true, "saveObject", "");
        
        $viewId = $this->createAdminViewAndMenuLink("MasterView", "/listReference/Masterdata.getMastertable", "Workspace");
        $this->createAdminField($viewId, "Masterdata", Field::TYPE_TEXT, "", "name", "name", true, "", "");
        $this->createAdminField($viewId, "Masterdata", Field::TYPE_BUTTON, "", "button", "save", true, "saveObject", "");
        $this->createAdminField($viewId, "Mastertable", Field::TYPE_TEXT, "", "name", "name", true, "", "");
        $this->createAdminField($viewId, "Mastertable", Field::TYPE_BUTTON, "", "button", "save", false, "saveObject", "");
        $this->createAdminField($viewId, "Mastertable", Field::TYPE_TEXT, "", "items", "items", false, "", "");
        
        $viewId = $this->createAdminViewAndMenuLink("CalendarView", "/listOwningReference/Calendar.getCalendarday.getSpecialdaytype", "Workspace");
        $this->createAdminField($viewId, "Calendar", Field::TYPE_TEXT, "", "name", "name", true, "", "");
        $this->createAdminField($viewId, "Calendar", Field::TYPE_BUTTON, "", "button", "save", true, "saveObject", "");
        $this->createAdminField($viewId, "Calendar", Field::TYPE_BUTTON, "", "button", "initCalendar", true, "createCalendar", "");
        
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
            "name" => "extendCalendar",
            "actionExecution" => "inputform",
            "viewId" => "viewId",
            "link" => "extendCalendar",
            "parentType" => "parentType",
            "objectType" => "objectType",
            "method" => "extendCalendarDialog"
        ); // Input Form to be opened
        
        $return = $mObj->createAndAdd($typeComponent, (string) $returngridRefComponentPackage, $typeContextmenu, $data);
        $this->createAdminField($viewId, "extendCalendarDialog", \Application\Document\Field::TYPE_TEXT, "", "name", "name", true, "", "");
        $this->createAdminField($viewId, "extendCalendarDialog", \Application\Document\Field::TYPE_TEXT, "", "year", "From year (YYYY)", false, "", "");
        $this->createAdminField($viewId, "extendCalendarDialog", \Application\Document\Field::TYPE_TEXT, "", "endyear", "End year (YYYY)", false, "", "");
        // $workspaceD->createField($viewId, "createsDialog", \Application\Document\Field::TYPE_TEXT, "WEEKTYPERO.type", "rectype", "Recurrence", false, "", "");
        
        $this->createAdminField($viewId, "extendCalendarDialog", \Application\Document\Field::TYPE_BUTTON, "", "save", "Do....", true, "method", "", "initCalendar(@name@ ,@year@ , @endyear@);", "", "showMessage(\"@name@\");");
        
        // $this->createAdminField($viewId, "Specialdaytype", Field::TYPE_TEXT, "", "type", "Type", true, "", "" );
        $this->createAdminField($viewId, "Specialdaytype", Field::TYPE_ENUM, "SPECIALDAYTYPE.type", "daytype", "daytype", true, "", "");
        $this->createAdminField($viewId, "Specialdaytype", Field::TYPE_BUTTON, "", "button", "save", true, "saveObject", "");
        
        $log = \Application\Controller\Log::getInstance();
        $log->AddRow(" Base::initiateUI ");
        
        $viewId = $this->createAdminViewAndMenuLink("Fields", "/listReference/View.getField", "Workspace");
        $this->createAdminField($viewId, "Field", Field::TYPE_TEXT, "", "object", "object", true, "", "");
        $this->createAdminField($viewId, "Field", Field::TYPE_TEXT, "", "name", "name", true, "", "");
        $this->createAdminField($viewId, "Field", Field::TYPE_TEXT, "", "type", "type", true, "", "");
        $this->createAdminField($viewId, "Field", Field::TYPE_TEXT, "", "typeReference", "typeReference", false, "", "");
        $this->createAdminField($viewId, "Field", Field::TYPE_TEXT, "", "actionExecution", "actionExecution", false, "", "");
        $this->createAdminField($viewId, "Field", Field::TYPE_TEXT, "", "actionResponse", "actionResponse", false, "", "");
        $this->createAdminField($viewId, "Field", Field::TYPE_TEXT, "", "options", "options", false, "", "");
        $this->createAdminField($viewId, "Field", Field::TYPE_TEXT, "", "optionsString", "optionsString", false, "", "");
        $this->createAdminField($viewId, "Field", Field::TYPE_TEXT, "", "method", "method", false, "", "");
        $this->createAdminField($viewId, "Field", Field::TYPE_TEXT, "", "group", "group", false, "", "");
        $this->createAdminField($viewId, "Field", Field::TYPE_CHECKBOX, "", "readonly", "readonly", false, "", "");
        $this->createAdminField($viewId, "Field", Field::TYPE_CHECKBOX, "", "required", "required", false, "", "");
        $this->createAdminField($viewId, "Field", Field::TYPE_CHECKBOX, "", "searchable", "searchable", false, "", "");
        $this->createAdminField($viewId, "Field", Field::TYPE_TEXT, "", "label", "label", false, "", "");
        $this->createAdminField($viewId, "Field", Field::TYPE_TEXT, "", "html", "html", false, "", "");
        $this->createAdminField($viewId, "Field", Field::TYPE_TEXT, "", "preloadPath", "preloadPath", false, "", "");
        $this->createAdminField($viewId, "Field", Field::TYPE_BUTTON, "", "button", "save", true, "saveObject", "");
        
        $viewId = $this->createAdminViewAndMenuLink("Templates", "/listhtml/Template", "Workspace");
        $this->createAdminField($viewId, "Template", Field::TYPE_TEXT, "", "name", "name", true, "", "");
        $this->createAdminField($viewId, "Template", Field::TYPE_TEXT, "", "entity", "entity", false, "", "");
        $this->createAdminField($viewId, "Template", Field::TYPE_TEXT, "", "text", "text", false, "", "");
        $this->createAdminField($viewId, "Template", Field::TYPE_TEXT, "", "subject", "subject", false, "", "");
        $this->createAdminField($viewId, "Template", Field::TYPE_TEXT, "", "messageTemplate", "messageTemplate", false, "", "");
        $this->createAdminField($viewId, "Template", Field::TYPE_BUTTON, "", "button", "save", true, "saveObject", "");
        
        $viewId = $this->createAdminViewAndMenuLink("ContextMenuView", "/listOwningReference/View.getComponent.getContextmenu", "Workspace");
        $this->createAdminField($viewId, "View", Field::TYPE_TEXT, "", "name", "name", true, "", "");
        $this->createAdminField($viewId, "View", Field::TYPE_BUTTON, "", "button", "save", true, "saveObject", "");
        $this->createAdminField($viewId, "Component", Field::TYPE_TEXT, "", "name", "name", true, "", "");
        $this->createAdminField($viewId, "Component", Field::TYPE_BUTTON, "", "button", "save", true, "saveObject", "");
        $this->createAdminField($viewId, "Contextmenu", Field::TYPE_TEXT, "", "name", "name", true, "", "");
        $this->createAdminField($viewId, "Contextmenu", Field::TYPE_TEXT, "", "actionExecution", "actionExecution", false, "", "");
        $this->createAdminField($viewId, "Contextmenu", Field::TYPE_TEXT, "", "actionResponse", "actionResponse", false, "", "");
        $this->createAdminField($viewId, "Contextmenu", Field::TYPE_TEXT, "", "link", "link", false, "", "");
        $this->createAdminField($viewId, "Contextmenu", Field::TYPE_TEXT, "", "parentType", "parentType", false, "", "");
        $this->createAdminField($viewId, "Contextmenu", Field::TYPE_TEXT, "", "objectType", "objectType", false, "", "");
        $this->createAdminField($viewId, "Contextmenu", Field::TYPE_TEXT, "", "method", "method", false, "", "");
        $this->createAdminField($viewId, "Contextmenu", Field::TYPE_TEXT, "", "viewId", "viewId", false, "", "");
        $this->createAdminField($viewId, "Contextmenu", Field::TYPE_TEXT, "", "serviceName", "serviceName", false, "", "");
        $this->createAdminField($viewId, "Contextmenu", Field::TYPE_TEXT, "", "serviceMethod", "serviceMethod", false, "", "");
        $this->createAdminField($viewId, "Contextmenu", Field::TYPE_TEXT, "", "template", "template", false, "", "");
        $this->createAdminField($viewId, "Contextmenu", Field::TYPE_TEXT, "", "params", "params", false, "", "");
        $this->createAdminField($viewId, "Contextmenu", Field::TYPE_BUTTON, "", "button", "save", true, "saveObject", "");
        
        $viewId = $this->createAdminViewAndMenuLink("CronJob", "/list/Cronjob", "Workspace");
        $this->createAdminField($viewId, "Cronjob", Field::TYPE_TEXT, "", "name", "name", true, "", "");
        $this->createAdminField($viewId, "Cronjob", Field::TYPE_TEXT, "", "objectType", "objectType", true, "", "");
        $this->createAdminField($viewId, "Cronjob", Field::TYPE_TEXT, "", "objectId", "PrimaryKey (objects)", true, "", "");
        $this->createAdminField($viewId, "Cronjob", Field::TYPE_TEXT, "", "method", "method", false, "", "");
        $this->createAdminField($viewId, "Cronjob", Field::TYPE_DATETIME, "", "datetime", "date", false, "", "");
       // $this->createAdminField($viewId, "Cronjob", Field::TYPE_DATETIME, "", "time", "time to start", false, "", "");
        $this->createAdminField($viewId, "Cronjob", Field::TYPE_TEXT, "", "status", "status", false, "", "");
        $this->createAdminField($viewId, "Cronjob", Field::TYPE_TEXT, "", "data", "data", false, "", "");
        $this->createAdminField($viewId, "Cronjob", Field::TYPE_CHECKBOX, "", "repeat", "repeat", false, "", "");
        $this->createAdminField($viewId, "Cronjob", Field::TYPE_CHECKBOX, "", "service", "service", false, "", "");
        $this->createAdminField($viewId, "Cronjob", Field::TYPE_TEXT, "", "delay", "delay (seconds)", false, "", "");
        $this->createAdminField($viewId, "Cronjob", Field::TYPE_TEXT, "", "action", "action", false, "", "");
        $this->createAdminField($viewId, "Cronjob", Field::TYPE_BUTTON, "", "button", "save", true, "saveObject", "");
        
        $viewId = $this->createAdminViewAndMenuLink("Configuration", "/list/Configuration", "Organization");
        $this->createAdminField($viewId, "Configuration", Field::TYPE_TEXT, "", "name", "name", true, "", "");
        $this->createAdminField($viewId, "Configuration", Field::TYPE_TEXT, "", "type", "type", false, "", "");
        $this->createAdminField($viewId, "Configuration", Field::TYPE_TEXT, "", "valuestr", "value ", false, "", "");
        $this->createAdminField($viewId, "Configuration", Field::TYPE_BUTTON, "", "button", "save", true, "saveObject", "");
    }

    public function initiateSamsaUI()
    {
        $viewId = $this->createAdminViewAndMenuLink("Samsa", "/listReference/Organization.getWorkspace", "Samsa");
        $this->createAdminField($viewId, "Organization", Field::TYPE_TEXT, "", "name", "name", true, "", "");
        $this->createAdminField($viewId, "Organization", Field::TYPE_BUTTON, "", "button", "save", true, "saveObject", "");
        $this->createAdminField($viewId, "Organization", Field::TYPE_BUTTON, "", "button", "updateWKAdminUI", false, 'method', "", 'updateUIActiveWorkspace();');
        $this->createAdminField($viewId, "Organization", Field::TYPE_BUTTON, "", "button", "cleanupOrganization", true, 'method', "", 'cleanupOrganization();');
        
        $this->createAdminField($viewId, "Workspace", Field::TYPE_TEXT, "", "name", "name", true, "", "");
        $this->createAdminField($viewId, "Workspace", Field::TYPE_TEXT, "", "active", "active", false, "", "");
        $this->createAdminField($viewId, "Workspace", Field::TYPE_BUTTON, "", "button", "updateUI", true, 'method', "", 'updateAdminUI();');
        $this->createAdminField($viewId, "Workspace", Field::TYPE_BUTTON, "", "button", "createUIMasterData", true, 'method', "", 'createUIMasterData();');
        
        $this->createAdminField($viewId, "Workspace", Field::TYPE_BUTTON, "", "button", "initiate", true, 'initiate();', "");
        $this->createAdminField($viewId, "Workspace", Field::TYPE_BUTTON, "", "button", "save", true, "saveObject", "");
        $this->createAdminField($viewId, "Workspace", Field::TYPE_BUTTON, "", "button", "activate", true, 'method', "", 'activate();');
        
        $viewId = $this->createAdminViewAndMenuLink("Scheduler", "/list/Scheduler", "Organization");
        $this->createAdminField($viewId, "Scheduler", Field::TYPE_TEXT, "", "name", "name", true, "", "");
        $this->createAdminField($viewId, "Scheduler", Field::TYPE_TEXT, "", "status", "status", false, "", "");
        $this->createAdminField($viewId, "Scheduler", Field::TYPE_DATETIME, "", "datetime", "datetime", false, "", "");
        $this->createAdminField($viewId, "Scheduler", Field::TYPE_BUTTON, "", "button", "save", true, "saveObject", "");
        
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
            "name" => "startS",
            "actionExecution" => "service",
            "actionResponse" => "showMessage(\"%message%\");",
            "link" => "Start Cron",
            "parentType" => "Organization",
            "objectType" => "Scheduler",
            "method" => "start",
            "parameters" => "",
            "serviceName" => "\Application\Service\Service",
            "serviceMethod" => "startCron"
        );
        $return = $mObj->createAndAdd($typeComponent, (string) $returngridRefComponentPackage, $typeContextmenu, $data);
    }

    public function createWorkspaceMenu()
    {
        $viewId = $this->createViewAndMenuLink("Workspaces", "/list/Workspace", "Organization", "admin");
        $this->createAdminField($viewId, "Workspace", Field::TYPE_TEXT, "", "name", "name", true, "", "");
        $this->createAdminField($viewId, "Workspace", Field::TYPE_TEXT, "", "active", "active", true, "", "");
        // > create the dialog with the corresponding fields
        $this->createAdminField($viewId, "importUI", \Application\Document\Field::TYPE_FILE, "", "fileName", "File", true, "", "");
        $this->createAdminField($viewId, "importUI", \Application\Document\Field::TYPE_BUTTON, "", "save", "Import UI", true, "method", "", "importUI(@fileName@);", "", "showMessage(\"@name@\");");
        // > create the dialog with the corresponding fields
        $this->createAdminField($viewId, "exportUI", \Application\Document\Field::TYPE_TEXT, "", "fileName", "Name", true, "", "");
        $this->createAdminField($viewId, "exportUI", \Application\Document\Field::TYPE_BUTTON, "", "save", "Export UI", true, "method", "", "exportUI(@fileName@);", "", "showMessage(\"@name@\");");

        
        // > create the dialog with the corresponding fields
        $this->createAdminField($viewId, "executeCustom", \Application\Document\Field::TYPE_TEXT, "", "text", "Text", true, "", "");
        $this->createAdminField($viewId, "executeCustom", \Application\Document\Field::TYPE_BUTTON, "", "save", "Execute...", true, "method", "", "executeCustom(@text@);", "", "showMessage(\"@name@\");");
        
        $this->createAdminField($viewId, "Workspace", Field::TYPE_BUTTON, "", "button", "updateUI", true, 'method', "", 'updateAdminUI();');
        $this->createAdminField($viewId, "Workspace", Field::TYPE_BUTTON, "", "button", "createUIMasterData", false, 'method', "", 'createUIMasterData();');
        $this->createAdminField($viewId, "Workspace", \Application\Document\Field::TYPE_FILE, "", "workspaceDocument", "Id Document(file)^style='width: 200px; height: 80px'", false, "", "");
        
        $this->createAdminField($viewId, "Workspace", Field::TYPE_BUTTON, "", "button", "initiate", true, '$this->initiate();', "");
        $this->createAdminField($viewId, "Workspace", Field::TYPE_BUTTON, "", "button", "save", true, "saveObject", "");
        $this->createAdminField($viewId, "Workspace", Field::TYPE_BUTTON, "", "button", "activate", true, 'method', "", 'activate();');
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
            "name" => "exportData",
            "actionExecution" => "service",
            "actionResponse" => "showMessage(\"%message%\");",
            "link" => "Export",
            "parentType" => "Organization",
            "objectType" => "Workspace",
            "method" => "export",
            "parameters" => "to_parent.email+from_parent.parent.email",
            "serviceName" => "\Application\Service\BackupService",
            "serviceMethod" => "export",
            "template" => "templateklant"
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
            "parameters" => "to_parent.email+from_parent.parent.email",
            "serviceName" => "\Application\Service\BackupService",
            "serviceMethod" => "exportUI",
            "template" => "templateklant"
        );
        $return = $mObj->createAndAdd($typeComponent, (string) $returngridRefComponentPackage, $typeContextmenu, $data);
        
        $data = array(
            "name" => "executeCustom",
            "actionExecution" => "inputform",
            "actionResponse" => "showMessage(\"%message%\");",
            "link" => "Execute custom",
            "parentType" => "Organization",
            "objectType" => "Workspace",
            "method" => "executeCustom",
            "parameters" => "",
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
            // "serviceMethod" => "importUI",
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

    public function deleteAdminUI()
    {
        $views = $this->getInstances("View");
        $log = \Application\Controller\Log::getInstance();
        // $log->addRow(" OO" . json_encode($views) . ' --- ');
        $mainTypes = array();
        // add XXX to remove the 0 index
        
        $mainTypes[] = "Views";
        $mainTypes[] = "Workspaces";
        $mainTypes[] = "Users";
        $mainTypes[] = "ContextMenuView";
        $mainTypes[] = "Templates";
        $mainTypes[] = "GridsView";
        $mainTypes[] = "ContextMenuView";
        $mainTypes[] = "CalendarView";
        $mainTypes[] = "Menus";
        $mainTypes[] = "MasterView";
        $mainTypes[] = "Fields";
        $mainTypes[] = "ChatView";
        $mainTypes[] = "Samsa";
        $mainTypes[] = "CronJob";
        $mainTypes[] = "Transactions";
        $mainTypes[] = "TransactionView";
        $mainTypes[] = "Samsaroles";
        $mainTypes[] = "Scheduler";
        $mainTypes[] = "Configuration";
        foreach ($views as $view) {
            if (array_search($view->{'title'}, $mainTypes) === false) {} else {
                $this->remove($view);
            }
        }
        $menus = $this->getInstances("Menu");
        $log = \Application\Controller\Log::getInstance();
        // $log->addRow(" OO".json_encode($views). ' --- ');
        foreach ($menus as $menu) {
            if (array_search($menu->{'text'}, $mainTypes) === false) {} else {
                $this->remove($menu);
            }
        }
        $masterdatas = $this->getInstances("Masterdata");
        $log = \Application\Controller\Log::getInstance();
        // $log->addRow(" OO".json_encode($views). ' --- ');
        foreach ($masterdatas as $masterdata) {
            // $this->remove($masterdata);
        }
        $this->update();
        
        return true;
    }

    public function createAdminField($viewId, $object, $type, $typeReference, $name, $label, $required, $actionExecution, $options, $method = "", $group = "", $actionResponse = "", $searchable = false)
    {
        return $this->createField($viewId, $object, $type, $typeReference, $name, $label, $required, $actionExecution, $options, $method, $group, $actionResponse, $searchable, 'admin');
    }

    public function createField($viewId, $object, $type, $typeReference, $name, $label, $required, $actionExecution, $options, $method = "", $group = "", $actionResponse = "", $searchable = false, $scope = 'user', $preloadPath = '', $readonly = false)
    {
        $data = array(
            "name" => $name,
            "typeReference" => $typeReference,
            "type" => $type,
            "label" => $label,
            "required" => $required,
            "options" => $options,
            "actionExecution" => $actionExecution,
            "actionResponse" => $actionResponse,
            "object" => $object,
            "method" => $method,
            "group" => $group,
            "searchable" => $searchable,
            "preloadPath" => $preloadPath,
            "scope" => $scope,
            "readonly" => $readonly
        );
        $typeV = 'View';
        $typeM = 'Field';
        $mObj = new \Application\Controller\MongoObjectFactory();
        
        $returnField = $mObj->createAndAdd($typeV, (string) $viewId, $typeM, $data);
        return $returnField;
    }

    protected function createUIMasterData()
    {
        $masterdatas = $this->getInstances("Masterdata");
        $log = \Application\Controller\Log::getInstance();
        $masterdataT = null;
        $returnVMaster = null;
        // $log->addRow(" OO".json_encode($views). ' --- ');
        foreach ($masterdatas as $masterdata) {
            $mastertables = $masterdata->getInstances("Mastertable");
            foreach ($mastertables as $mastertable) {
                if ($mastertable->name == "PARAMETERTYPE" || $mastertable->name == "USERROLETYPE" || $mastertable->name == "RECURRENCETYPE" || $mastertable->name == "WEEKTYPE" || $mastertable->name == "SPECIALDAYTYPE" || $mastertable->name == "CALENDARSLOTYPE") {
                    $masterdata->remove($mastertable);
                    $masterdata->reload();
                    $masterdataT = $masterdata;
                }
            }
            // $this->remove($mastertable);
        }
        $this->update();
        if ($masterdataT == null) {
            
            if ($this->_id instanceof \MongoId) {
                $returnW = (string) $this->_id;
            } else {
                $returnW = (string) $this->_id['$id'];
            }
            $typeW = 'Workspace';
            $mObj = new \Application\Controller\MongoObjectFactory();
            $dataMaster = array(
                "name" => "master",
                "scope" => "admin"
            );
            
            $typeMaster = 'Masterdata';
            $returnVMaster = $mObj->createAndAdd($typeW, (string) $returnW, $typeMaster, $dataMaster);
            
            $masterdataT = $mObj->findObjectInstance("Masterdata", $returnVMaster);
        }
        
        $typeW = 'Workspace';
        $mObj = new \Application\Controller\MongoObjectFactory();
        
        $typeMaster = 'Masterdata';
        $returnVMaster = $masterdataT->getIdAsString(); // $mObj->createAndAdd($typeW, (string) $returnW, $typeMaster, $dataMaster);
        
        $dataMasterTable = array(
            "name" => "PARAMETERTYPE",
            "items" => "[{\"recid\":\"121\",\"type\":\"schema/json\"},{\"recid\":\"122\",\"type\":\"objecttype\"},{\"recid\":\"123\",\"type\":\"gridrule\"},{\"recid\":\"124\",\"type\":\"gridcolumn\"},{\"recid\":\"125\",\"type\":\"gridrowrule\"},{\"recid\":\"126\",\"type\":\"formatfield\"},{\"recid\":\"127\",\"type\":\"filter\"},{\"recid\":\"128\",\"type\":\"gridtotal\"}]",
            "scope" => "admin"
        );
        
        $typeMasterTable = 'Mastertable';
        $returnVMasterTable = $mObj->createAndAdd($typeMaster, (string) $returnVMaster, $typeMasterTable, $dataMasterTable);
        
        $dataMasterTable = array(
            "name" => "USERROLETYPE",
            "items" => "[{\"recid\":\"441\",\"type\":\"admin\"},{\"recid\":\"442\",\"type\":\"planner\"},{\"recid\":\"443\",\"type\":\"viewer\"},{\"recid\":\"444\",\"type\":\"mobile\"},{\"recid\":\"445\",\"type\":\"basic\"}]",
            "scope" => "admin"
        );
        
        $typeMasterTable = 'Mastertable';
        $returnVMasterTable = $mObj->createAndAdd($typeMaster, (string) $returnVMaster, $typeMasterTable, $dataMasterTable);
        
        /*
         * create master data for recurrence rule - id is the number of DAYS!
         */
        $dataMasterTable = array(
            "name" => "RECURRENCETYPE",
            "items" => "[{\"recid\":\"0\",\"type\":\"-\", \"rule\":\"-\"},{\"recid\":\"1\",\"type\":\"daily\", \"rule\":\"1D\"},{\"recid\":\"7\",\"type\":\"weekly\", \"rule\":\"7D\"}]",
            "scope" => "admin"
        );
        
        $typeMasterTable = 'Mastertable';
        $returnVMasterTable = $mObj->createAndAdd($typeMaster, (string) $returnVMaster, $typeMasterTable, $dataMasterTable);
        
        $dataMasterTable = array(
            "name" => "WEEKTYPE",
            "items" => "[{\"recid\":\"0\",\"type\":\"*\", \"select\":\"0\"},{\"recid\":\"1\",\"type\":\"Ma\", \"select\":\"1\"},{\"recid\":\"2\",\"type\":\"Di\", \"rule\":\"2\"},{\"recid\":\"3\",\"type\":\"Woe\", \"rule\":\"3\"},{\"recid\":\"4\",\"type\":\"Do\", \"rule\":\"4\"},{\"recid\":\"5\",\"type\":\"Vr\", \"rule\":\"5\"},{\"recid\":\"6\",\"type\":\"Sa\", \"rule\":\"6\"},{\"recid\":\"7\",\"type\":\"Su\", \"rule\":\"7\"}]",
            "scope" => "admin"
        );
        $typeMasterTable = 'Mastertable';
        $returnVMasterTable = $mObj->createAndAdd($typeMaster, (string) $returnVMaster, $typeMasterTable, $dataMasterTable);
        
        $dataMasterTable = array(
            "name" => "WEEKTYPESHORT",
            "items" => "[{\"recid\":\"0\",\"type\":\"*\", \"select\":\"0\"},{\"recid\":\"1\",\"type\":\"Ma-Vr\", \"select\":\"1\"},{\"recid\":\"2\",\"type\":\"Sa\", \"rule\":\"2\"},{\"recid\":\"3\",\"type\":\"Su\", \"rule\":\"3\"}]",
            "scope" => "admin"
        );
        $typeMasterTable = 'Mastertable';
        $returnVMasterTable = $mObj->createAndAdd($typeMaster, (string) $returnVMaster, $typeMasterTable, $dataMasterTable);
        
        $dataMasterTable = array(
            "name" => "WEEKTYPERO",
            "items" => "[{\"recid\":\"0\",\"type\":\"*\", \"select\":\"0\"},{\"recid\":\"1\",\"type\":\"Lu\", \"select\":\"1\"},{\"recid\":\"2\",\"type\":\"Ma\", \"rule\":\"2\"},{\"recid\":\"3\",\"type\":\"Mie\", \"rule\":\"3\"},{\"recid\":\"4\",\"type\":\"Jo\", \"rule\":\"4\"},{\"recid\":\"5\",\"type\":\"Vi\", \"rule\":\"5\"},{\"recid\":\"6\",\"type\":\"Sa\", \"rule\":\"6\"},{\"recid\":\"7\",\"type\":\"Du\", \"rule\":\"7\"}]",
            "scope" => "admin"
        );
        $typeMasterTable = 'Mastertable';
        $returnVMasterTable = $mObj->createAndAdd($typeMaster, (string) $returnVMaster, $typeMasterTable, $dataMasterTable);
        
        $dataMasterTable = array(
            "name" => "WEEKTYPESHORTRO",
            "items" => "[{\"recid\":\"0\",\"type\":\"*\", \"select\":\"0\"},{\"recid\":\"1\",\"type\":\"Lu-Vi\", \"select\":\"1\"},{\"recid\":\"2\",\"type\":\"Sa\", \"rule\":\"2\"},{\"recid\":\"3\",\"type\":\"Du\", \"rule\":\"3\"}]",
            "scope" => "admin"
        );
        $typeMasterTable = 'Mastertable';
        $returnVMasterTable = $mObj->createAndAdd($typeMaster, (string) $returnVMaster, $typeMasterTable, $dataMasterTable);
        
        $dataMasterTable = array(
            "name" => "SPECIALDAYTYPE",
            "items" => "[{\"recid\":\"0\",\"type\":\"holiday\", \"rule\":\"holiday\"},{\"recid\":\"1\",\"type\":\"weekend\", \"rule\":\"weekend\"},{\"recid\":\"2\",\"type\":\"company_day\", \"rule\":\"company_day\"},{\"recid\":\"3\",\"type\":\"other\", \"rule\":\"other\"}]",
            "scope" => "admin"
        );
        
        $typeMasterTable = 'Mastertable';
        $returnVMasterTable = $mObj->createAndAdd($typeMaster, (string) $returnVMaster, $typeMasterTable, $dataMasterTable);
        
        $dataMasterTable = array(
            "name" => "CALENDARSLOTYPE",
            "items" => "[{\"recid\":\"0\",\"type\":\"Class\", \"rule\":\"class\"},{\"recid\":\"1\",\"type\":\"Break\", \"rule\":\"break\"},{\"recid\":\"2\",\"type\":\"other\", \"rule\":\"9\"}]",
            "scope" => "admin"
        );
        
        $typeMasterTable = 'Mastertable';
        $returnVMasterTable = $mObj->createAndAdd($typeMaster, (string) $returnVMaster, $typeMasterTable, $dataMasterTable);
        
        return $returnVMaster;
    }

    function checkStatus($status, $value)
    {
        $s = "";
        \Application\Controller\Log::getInstance()->AddRow(' checkStatus >>>>>>>>>>>> ' . json_encode($status));
        if (isset($status[0]) && isset($status[0]{'text'})) {
            $s = $status[0]{'text'};
        } else {
            if (isset($status)) {
                $s = $status;
            } else {
                return false;
            }
        }
        
        if (strcasecmp($s, $value) == 0) {
            return true;
        }
        return false;
    }

    function inset($pathString, $field, $fieldLocal)
    {
        $log = \Application\Controller\Log::getInstance();
        // $datetimeFrom = $this->{$filterFrom}
        $filterFromS = strtotime($this->{$fieldLocal});
        // str_replace("-", "/", $this->{$filterFrom});
        $s = '';
        if ($filterFromS) {
            $s = "[" . $field . "-" . $filterFromS . "]";
        }
        $collectionInstances = $this->getPathReferences($pathString . $s); // . "[" . $field . "-" . $this->{$fieldLocal} . "]");
        $return = "false";
        if (isset($this->{$fieldLocal})) {
            foreach ($collectionInstances as $collectionInstance) {
                
                if (isset($collectionInstance->{$field}) && $collectionInstance->{$field} === $this->{$fieldLocal} && ($this->getIdAsString() !== $collectionInstance->getIdAsString())) {
                    // $log->AddRow(" EXECp1 -< " . json_encode($collectionInstance) . ' >-on --> ' . json_encode($this));
                    
                    return "true";
                }
            }
        }
        return $return;
    }

    function searchInstances($type, $filterList)
    {
        $search = [];
        // $search["search"] = [];
        $search["searchLogic"] = "AND";
        foreach ($filterList as $filterItem) {
            $searchItem = array();
            $searchItem["type"] = "string";
            $searchItem['operator'] = 'contains';
            $context = preg_replace('/[^A-Za-z0-9\-]/', ' ', $filterItem['value']);
            $searchItem['field'] = $filterItem['field'];
            $searchItem["value"] = $context;
            $search["search"][] = $searchItem;
        }
        \Application\Controller\Log::getInstance()->AddRow(' searchInstances >>>>>>>>>>>> ' . json_encode($search));
        
        // $laf->findInstancesByCriteria($classRef, $ncriteria, $references, $index, $offset, $search, $sort);
        return $this->getQuickInstancesCriteria($type, array(), null, 0, 0, $search);
    }

    function splitAtUpperCase($string)
    {
        return preg_replace('/([a-z0-9])?([A-Z])/', '$1 $2', $string);
    }

    function get_delimited_strings($str, $startDelimiter, $endDelimiter)
    {
        $contents = array();
        $startDelimiterLength = strlen($startDelimiter);
        $endDelimiterLength = strlen($endDelimiter);
        $startFrom = $contentStart = $contentEnd = $outStart = $outEnd = 0;
        while (false !== ($contentStart = strpos($str, $startDelimiter, $startFrom))) {
            $contentStart += $startDelimiterLength;
            $contentEnd = strpos($str, $endDelimiter, $contentStart);
            $outEnd = $contentStart - 1;
            if (false === $contentEnd) {
                break;
            }
            $contents['in'][] = substr($str, $contentStart, $contentEnd - $contentStart);
            $contents['out'][] = substr($str, $outStart, $outEnd - $outStart);
            $startFrom = $contentEnd + $endDelimiterLength;
            $outStart = $startFrom;
        }
        $contents['out'][] = substr($str, $outStart, $contentEnd - $outStart);
        return $contents;
    }

    function getset($pathString, $field, $fieldLocal)
    {
        $log = \Application\Controller\Log::getInstance();
        // $datetimeFrom = $this->{$filterFrom}
        $filterFromS = strtotime($this->{$fieldLocal});
        // str_replace("-", "/", $this->{$filterFrom});
        $s = '';
        if ($filterFromS) {
            $s = "[" . $field . "-" . $filterFromS . "]";
        }
        $collectionInstances = $this->getPathReferences($pathString . $s); // . "[" . $field . "-" . $this->{$fieldLocal} . "]");
        
        $retArray = array();
        $return = 'false';
        if (isset($this->{$fieldLocal})) {
            foreach ($collectionInstances as $collectionInstance) {
                if (isset($collectionInstance->{$field}) && $collectionInstance->{$field} === $this->{$fieldLocal} && ($this->getIdAsString() !== $collectionInstance->getIdAsString())) {
                    // $log->AddRow(" EXECp1 -< " . json_encode($collectionInstance) . ' >-on --> ' . json_encode($this));
                    $ret = array();
                    $ret["id"] = $collectionInstance->getIdAsString();
                    $ret["type"] = $collectionInstance->getClassName();
                    $retArray[] = $ret;
                    $return = 'true';
                }
            }
        }
        if ($return == 'true') {
            $returnSet = array();
            $returnSet['data'] = $retArray;
            $returnSet['response'] = $return;
            $returnSet['responseType'] = 'bool';
            return $returnSet;
        }
        return $return;
    }

    function ininterval($pathString, $field, $start, $end, $fieldLocal, $startLocal, $endLocal)
    {
        $log = \Application\Controller\Log::getInstance();
        // $datetimeFrom = $this->{$filterFrom}
        $filterFromS = strtotime($this->{$fieldLocal});
        // str_replace("-", "/", $this->{$filterFrom});
        $s = '';
        if ($filterFromS) {
            $s = "[" . $field . "-" . $filterFromS . "]";
        }
        $collectionInstances = $this->getPathReferences($pathString . $s); // . "[" . $field . "-" . $this->{$fieldLocal} . "]");
        $return = "false";
        if (isset($this->{$fieldLocal})) {
            // $log->AddRow(" EXECininterval1 -< " . json_encode($collectionInstance) . ' >-on --> ' . json_encode($this));
            
            foreach ($collectionInstances as $collectionInstance) {
                
                if (isset($collectionInstance->{$field}) && $collectionInstance->{$field} === $this->{$fieldLocal} && strtotime($collectionInstance->{$end}) > strtotime($this->{$startLocal}) && strtotime($collectionInstance->{$start}) < strtotime($this->{$endLocal}) && ($this->getIdAsString() !== $collectionInstance->getIdAsString())) {
                    return "true";
                }
            }
        }
        return $return;
    }

    function getFilterredCollection($pathString, $field, $filterFromS)
    {
        $log = \Application\Controller\Log::getInstance();
        // str_replace("-", "/", $this->{$filterFrom});
        $s = '';
        if ($filterFromS) {
            $s = "[" . $field . "-" . $filterFromS . "]";
        }
        // \Application\Controller\Log::getInstance()->AddRow(' DORU999 ' . json_encode($this) . ' ---- ' . $pathString . $s);
        $collectionInstances = $this->getPathReferences($pathString . $s);
        // \Application\Controller\Log::getInstance()->AddRow(' DORU998 ' . json_encode($collectionInstances) . ' ---- ' . $pathString . $s);
        
        return $collectionInstances;
    }

    function getininterval($pathString, $field, $start, $end, $fieldLocal, $startLocal, $endLocal)
    {
        // $datetimeFrom = $this->{$filterFrom}
        $filterFromS = strtotime($this->{$fieldLocal});
        
        $collectionInstances = $this->getFilterredCollection($pathString, $field, $filterFromS);
        return $this->get_ininterval($collectionInstances, $field, $start, $end, $fieldLocal, $startLocal, $endLocal);
    }

    function get_ininterval($collectionInstances, $field, $start, $end, $fieldLocal, $startLocal, $endLocal)
    {
        $retArray = array();
        $return = 'false';
        if (isset($this->{$fieldLocal}) && count($collectionInstances) > 0) {
            foreach ($collectionInstances as $collectionInstance) {
                
                if (isset($collectionInstance->{$field}) && ($collectionInstance->{$field} === $this->{$fieldLocal}) && (strtotime($collectionInstance->{$end}) > strtotime($this->{$startLocal})) && (strtotime($collectionInstance->{$start}) < strtotime($this->{$endLocal})) && ($this->getIdAsString() !== $collectionInstance->getIdAsString())) {
                    $ret = array();
                    $ret["id"] = $collectionInstance->getIdAsString();
                    $ret["type"] = $collectionInstance->getClassName();
                    $retArray[] = $ret;
                    $return = 'true';
                }
            }
        }
        if ($return == 'true') {
            $returnSet = array();
            $returnSet['data'] = $retArray;
            $returnSet['response'] = $return;
            $returnSet['responseType'] = 'bool';
            return $returnSet;
        }
        return $return;
    }

    function orset($pathString, $field)
    {
        $collectionInstances = $this->getPathReferences($pathString);
        $return = null;
        $i = 0;
        foreach ($collectionInstances as $collectionInstance) {
            if (isset($collectionInstance->{$field})) {
                $boolVal = $collectionInstance->{$field} === 'true' ? true : false;
                settype($boolVal, 'bool');
                if ($i == 0) {
                    $return = $boolVal;
                    $i = 1;
                } else {
                    $return = $return || $boolVal;
                }
            }
        }
        $return = $return ? 'true' : 'false';
        return "" . $return;
    }

    function my_server_url()
    {
        $server_name = $_SERVER['SERVER_NAME'];
        
        if (! in_array($_SERVER['SERVER_PORT'], [
            80,
            443
        ])) {
            $port = ":$_SERVER[SERVER_PORT]";
        } else {
            $port = '';
        }
        
        if (! empty($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) == 'on' || $_SERVER['HTTPS'] == '1')) {
            $scheme = 'https';
        } else {
            $scheme = 'http';
        }
        return $scheme . '://' . $server_name . $port;
    }

    function is_after_indexed_list($pathString, $field, $field1)
    {
        $collectionInstances = $this->getPathReferences($pathString);
        $return = '';
        $return = false;
        $i = 0;
        $log = \Application\Controller\Log::getInstance();
        // $log->AddRow(" ANDSET -< " . json_encode($this) . ' >-on --> ' . $pathString . ' - ' . $field);
        
        // $log->AddRow(" ANDSET2 -< " . json_encode($collectionInstances) . ' >-on --> ' . $pathString . ' - ' . $field1);
        if (isset($collectionInstances[0])) {
            $collectionInstance = $collectionInstances[count($collectionInstances) - 1];
            // $log->AddRow(" ANDSET32 -< " . json_encode($collectionInstance) . ' >-on --> ');
            if (isset($collectionInstance->{$field1})) {
                $boolVal = false;
                $endDateIndex = \DateTime::createFromFormat($this::FORMAT_DATE_TIME, $collectionInstance->{$field1});
                $endDateInstance = \DateTime::createFromFormat($this::FORMAT_DATE, $this->{$field});
                if ($endDateIndex > $endDateInstance) {
                    $return = true;
                }
                $log = \Application\Controller\Log::getInstance();
                // $log->AddRow(" ANDSET -< " . $return . ' >-on --> ' . json_encode($endDateIndex) . ' date ' . json_encode($endDateInstance));
            }
        }
        $return = $return ? 'true' : 'false';
        // $log->AddRow(" ANDSET RES -< " . $return . ' >-on --> ');
        return "" . $return;
    }

    function andset($pathString, $field)
    {
        $collectionInstances = $this->getPathReferences($pathString);
        $return = '';
        $i = 0;
        foreach ($collectionInstances as $collectionInstance) {
            if (isset($collectionInstance->{$field})) {
                $boolVal = false;
                if ($collectionInstance->{$field} === true || $collectionInstance->{$field} === 'true' || $collectionInstance->{$field} === 1) {
                    $boolVal = true;
                }
                $log = \Application\Controller\Log::getInstance();
                $log->AddRow(" ANDSET -< " . $boolVal . ' >-on --> ');
                
                if ($i == 0) {
                    $return = $boolVal;
                    $i = 1;
                } else {
                    $return = $return || $boolVal;
                }
                $log->AddRow(" ANDSET -< " . $return . ' >-on --> ');
            }
        }
        $return = $return ? 'true' : 'false';
        // $log->AddRow(" ANDSET RES -< " . $return. ' >-on --> ');
        return "" . $return;
    }

    function triggerset($pathString, $field)
    {
        $collectionInstances = $this->getPathReferences($pathString);
        $return = 0;
        foreach ($collectionInstances as $collectionInstance) {
            // $collectionInstance->{$field};
            $reflectionMethod = new \ReflectionMethod($collectionInstance, $field);
            $obj = $reflectionMethod->invoke($collectionInstance, $this);
        }
        return "true";
    }

    function sumset($pathString, $field)
    {
        $collectionInstances = $this->getPathReferences($pathString);
        $return = 0;
        foreach ($collectionInstances as $collectionInstance) {
            $return = $return + $collectionInstance->{$field};
        }
        return "" . $return;
    }

    function percent($pathString, $field, $amount, $percent)
    {
        $collectionInstances = $this->getPathReferences($pathString);
        $return = 0;
        foreach ($collectionInstances as $collectionInstance) {
            $a = $collectionInstance->{$field};
            $b = $collectionInstance->{$amount};
            $c = $collectionInstance->{$percent};
            if (isset($a) && ($a != null) && isset($b) && ($b != null) && isset($c) && ($c != null)) {
                $return = $return + ($c / 100) * ($b * $a);
            }
        }
        return "" . $return;
    }

    function sumsetAmount($pathString, $field, $amount)
    {
        \Application\Controller\Log::getInstance()->AddRow('sumsetAmount: ' . json_encode($pathString) . json_encode($field) . json_encode($amount));
        
        $collectionInstances = $this->getPathReferences($pathString);
        $return = 0;
        foreach ($collectionInstances as $collectionInstance) {
            $a = $collectionInstance->{$amount};
            $b = $collectionInstance->{$field};
            if (isset($a) && ($a != null) && isset($b) && ($b != null)) {
                $return = $return + $a * $b;
            }
        }
        return "" . $return;
    }

    function sum($pathString, $field1, $field2, $field3)
    {
        $collectionInstances = $this->getPathReferences($pathString);
        $return = 0;
        foreach ($collectionInstances as $collectionInstance) {
            $a = $collectionInstance->{$field1};
            $b = $collectionInstance->{$field2};
            $c = $collectionInstance->{$field3};
            if (isset($a) && ($a != null) && isset($b) && ($b != null) && isset($c) && ($c != null)) {
                $return = $return + $a * $c + ($b / 100) * ($c * $a);
            }
        }
        return "" . $return;
    }

    function dif($pathString, $from, $to)
    {
        $collectionInstances = $this->getPathReferences($pathString);
        $log = \Application\Controller\Log::getInstance();
        // $log->AddRow(" =========><DIF> -< " . "PATH =========> " . $pathString . "COLLETCION =========> " . json_encode($collectionInstances) . ' THIS =========> ' . json_encode($this). ' FROM =========> '. $from . " TO =========> " . $to);
        $return = 0;
        foreach ($collectionInstances as $collectionInstance) {
            $return = $return + ($collectionInstance->{$from} - $collectionInstance->{$to}); // + $collectionInstance->{$field};
        }
        return $return;
    }

    function difBellowZero($pathString, $from, $to)
    {
        $collectionInstances = $this->getPathReferences($pathString);
        $log = \Application\Controller\Log::getInstance();
        // $log->AddRow(" EXECp -< " . json_encode($collectionInstances) . ' >-on --> ' . json_encode($this));
        $return = 0;
        foreach ($collectionInstances as $collectionInstance) {
            $return = $return + ($collectionInstance->{$from} - $collectionInstance->{$to}); // + $collectionInstance->{$field};
        }
        if ($return < 0) {
            return "true";
        }
        return "false";
    }

    function maxAvg($collectionType, $field)
    {
        $collectionInstances = $this->getInstances($collectionType);
        $return = 0;
        foreach ($collectionInstances as $collectionInstance) {
            if ($collectionInstance->{$field} > $return) {
                $return = $collectionInstance->{$field};
            }
        }
        return $return;
    }

    function minAvg($collectionType, $field)
    {
        $collectionInstances = $this->getInstances($collectionType);
        $return = 2147483647;
        foreach ($collectionInstances as $collectionInstance) {
            if ($collectionInstance->{$field} < $return) {
                $return = $collectionInstance->{$field};
            }
        }
        return $return;
    }

    function max($collectionType, $field)
    {
        $collectionInstances = $this->getInstances($collectionType);
        $return = 0;
        foreach ($collectionInstances as $collectionInstance) {
            if ($collectionInstance->{$field} > $return) {
                $return = $collectionInstance->{$field};
            }
        }
        return $return;
    }

    function min($collectionType, $field)
    {
        $collectionInstances = $this->getInstances($collectionType);
        $return = 2147483647;
        foreach ($collectionInstances as $collectionInstance) {
            if ($collectionInstance->{$field} < $return) {
                $return = $collectionInstance->{$field};
            }
        }
        return $return;
    }

    function avg($collectionType, $field)
    {
        $collectionInstances = $this->getInstances($collectionType);
        $return = 0;
        foreach ($collectionInstances as $collectionInstance) {
            $return = $return + $collectionInstance->{$field};
        }
        if (isset($collectionInstances) && count($collectionInstances) > 0) {
            return $return / count($collectionInstances);
        } else {
            return 0;
        }
    }

    function late($dateString, $days)
    {
        if (strlen($dateString) > 3) {
            $format = 'd-m-Y';
            $date = \DateTime::createFromFormat($format, $dateString);
            $dateNow = new \DateTime();
            $days_ago = new \DateInterval("P" . $days . "D");
            $days_ago->invert = 1; // Make it negative.
            $dateNow->add($days_ago);
            if ($date <= $dateNow) {
                return true;
            }
        }
        return false;
    }

    function adddate($pathString, $dateString, $min, $min1 = null)
    {
        $collectionInstances = $this->getPathReferences($pathString);
        $return = 0;
        // \Application\Controller\Log::getInstance()->AddRow(' DORU1554 ' . json_encode($this) . ' -' . $pathString . '- ' . $dateString . '--' . json_encode($collectionInstances));
        foreach ($collectionInstances as $collectionInstance) {
            if ($collectionInstance != null && $collectionInstance->{$dateString} != null && strlen($collectionInstance->{$dateString}) > 3) {
                if (strlen($dateString) > 3 && isset($min)) {
                    
                    $format = 'd-m-Y H:i';
                    $date = \DateTime::createFromFormat($format, $collectionInstance->{$dateString});
                    // \Application\Controller\Log::getInstance()->AddRow(' DORU16 ' . json_encode($date));
                    // $dateNow = new \DateTime();
                    
                    $days_ago = new \DateInterval("PT" . floor($collectionInstance->{$min}) . "M");
                    
                    // $days_ago->invert = 1; // Make it negative.
                    $date->add($days_ago);
                    // \Application\Controller\Log::getInstance()->AddRow(' DORU15 ' . $date->format("d-m-Y"));
                    
                    if (isset($min1) && isset($collectionInstance->{$min1})) {
                        $days_ago = new \DateInterval("PT" . $collectionInstance->{$min1} . "M");
                        
                        // $days_ago->invert = 1; // Make it negative.
                        $date->add($days_ago);
                    }
                    // \Application\Controller\Log::getInstance()->AddRow(' DORU17 ' . $date->format("d-m-Y H:i"));
                    return $date->format("d-m-Y H:i");
                }
            }
        }
        return false;
    }

    function setequal($pathString, $eq1)
    {
        $collectionInstances = $this->getPathReferences($pathString);
        \Application\Controller\Log::getInstance()->AddRow(' DORU17 ' . json_encode($this) . ' -' . $pathString . '- ' . $eq1 . '--' . json_encode($collectionInstances));
        
        $return = 0;
        
        foreach ($collectionInstances as $collectionInstance) {
            if ($collectionInstance != null) {
                // \Application\Controller\Log::getInstance()->AddRow(' DORU17 ' . json_encode($collectionInstance) . ' -' . $pathString . '- ' . $eq1 . '--' . json_encode($collectionInstances));
                
                $return = $collectionInstance->{$eq1};
            }
        }
        // \Application\Controller\Log::getInstance()->AddRow(' DORU16 ' . $return);
        return "" . $return;
    }

    function setequals($eq1, $eq2)
    {
        if (eq1 === eq2) {
            return true;
        }
        return false;
    }

    function firstDayOfWeek($date)
    {
        $format = 'd-m-Y';
        $dateI = \DateTime::createFromFormat($format, $date);
        $week_number = $dateI->format("W");
        $year = $dateI->format("Y");
        
        $dto = new \DateTime();
        $dto->setISODate($year, $week_number, 1);
        return $dto;
    }

    /**
     * Change a full date in day only
     *
     * @param unknown $date            
     * @return \DateTime
     */
    function formatDateToDay($date)
    {
        $format = 'd-m-Y';
        $dateI = \DateTime::createFromFormat($format, $date);
        
        return $dateI;
    }

    function formatDate($dateString, $format)
    {
        $log = \Application\Controller\Log::getInstance();
        
        $dateRet = "";
        if (strlen($dateString) > 2 && strlen($dateString) < 11) {
            $d1 = \DateTime::createFromFormat("d-m-Y", $dateString);
            $dateRet = $d1->format($format);
        } else 
            if (strlen($dateString) > 11) {
                $log = \Application\Controller\Log::getInstance();
                $log->AddRow(' DATETIMECONV >-on --> ' . json_encode($dateString));
                $d1 = \DateTime::createFromFormat("d-m-Y H:i", $dateString);
                $dateRet = $d1->format($format);
            }
        return $dateRet; // ('U');
    }

    function formatNodeIcon()
    {
        $columns = $this->getAttributes();
        foreach ($columns as $key => $value) {
            if ($this->substr_startswith($key, 'is_') || $this->substr_startswith($key, 'Is_')) {
                
                $log = \Application\Controller\Log::getInstance();
                if ($value == null || $value === '') {} else {
                    if (isset($value) && strlen($value) < 10) {
                        if ($value === 'true' || $value === true) {
                            return 'fa fa-ban text-danger';
                        }
                    } else {
                        $decodeStringVal = array();
                        $decodeStringVal = json_decode($value, true);
                        $log->AddRow(" Get CONFLICT " . json_encode($decodeStringVal) . " -- ");
                        if ($decodeStringVal["response"] == "true") {
                            
                            return 'fa fa-ban text-danger';
                        }
                    }
                }
            }
        }
        return 'fa fa-check-square-o';
    }

    function formatDateTime($dateString, $timeString, $format)
    {
        $dateRet = new \DateTime();
        if (strlen($dateString) > 3) {
            $d1 = \DateTime::createFromFormat('d-m-Y H:i', $dateString . ' ' . $timeString);
            if ($d1 instanceof \DateTime) {
                $dateRet->setTimezone(new \DateTimeZone('UTC'));
                $dateRet = $d1->format($format);
            }
        }
        return $dateRet; // ('U');
    }

    public function importParentCsv($id, $classname, $data, $mappings = [])
    {
        $log = \Application\Controller\Log::getInstance();
        $log->AddRow(' IMPORTING >-on parent --> ' . json_encode($this));
        $result = array();
        $keys = array();
        $data = json_decode($data, true);
        $file = base64_decode($data['content']);
        $bService = new \Application\Service\BackupService();
        $returnV = $bService->importObject($this->get_class_name($this), $id, $classname, $file, $this->getExternalRelations(), $mappings);
        $result["message"] = 'Data from file is imported !';
        
        if ($returnV == 'true') {
            $result["message"] = 'Data from file is imported !';
        } else {
            $result["message"] = 'Data from file is not imported !';
        }
        return $result;
    }

    public function importCsv($data, $mappings = [])
    {
        $log = \Application\Controller\Log::getInstance();
        $log->AddRow(' IMPORTING >-on --> ' . json_encode($this->getParent()));
        $result = array();
        $keys = array();
        $data = json_decode($data, true);
        $file = base64_decode($data['content']);
        $bService = new \Application\Service\BackupService();
        $returnV = $bService->importObject($this->get_class_name($this->getParent()), $this->getParent()->_id['$id'], $this->get_class_name($this), $file, $this->getExternalRelations(), $mappings);
        $result["message"] = 'Data from file is imported !';
        if ($returnV == 'true') {
            $result["message"] = 'Data from file is imported !';
        } else {
            $result["message"] = 'Data from file is not imported !';
        }
        return $result;
    }

    public function copyinstance($typeInstance, $typeDest)
    {
        $laf = new \Application\Controller\MongoObjectFactory();
        $log = \Application\Controller\Log::getInstance();
        // "Processorder_processnumber" => $orderlineInstance->Orderline_processnumber,
        $typeX = $laf->getClassPath($typeDest) . $typeDest;
        $getter_names = get_class_vars($typeX);
        $columns = array();
        foreach ($getter_names as $key => $value) {
            if (isset($typeInstance->{$key}) && $key != 'parent' && $key != 'next' && $key != 'prev' && $key != "id") {
                if ($typeInstance->isOwningRelation($key) || $typeInstance->isReferenceRelation($key)) {} else {
                    $columns[$key] = $typeInstance->{$key};
                }
            }
        }
        // $log->AddRow(' COPY >-on --> ' . ' - ' . json_encode($columns));
        
        $data = array();
        foreach ($columns as $key => $value) {
            $data1[$key] = $value;
            $data = array_merge($data, $data1);
        }
        $log->AddRow(' COPY >-on --> ' . ' - ' . json_encode($data));
        $type = $this->get_class_name($this);
        $returnnewid = $laf->createAndAdd($type, $this->getIdAsString(), $typeDest, $data);
        $log->AddRow(' COPY >-on --> ' . ' - ' . json_encode($returnnewid));
        return $returnnewid;
    }

    /**
     *
     * @param unknown $sourcePath
     *            as "a.b.c"
     * @param unknown $destinationType
     *            local destination child where $this is parent
     * @param unknown $ifReference
     *            true if references between copies must be created
     */
    public function copyset($sourcePath, $destinationType, $ifReference = false, $parentPath = '')
    {


        $laf = new \Application\Controller\MongoObjectFactory();
        $log = \Application\Controller\Log::getInstance();
        $log->AddRow(' COPY >-on --> ' . json_encode($sourcePath));
        $log->AddRow(' COPY >-on --> 1 ->' . json_encode($this));


        $typeInstances = $this->getPathReferences($sourcePath);

        $typeX = $laf->getClassPath($destinationType) . $destinationType;
        $returnnewid = null;
        foreach ($typeInstances as $typeInstance) {
            $log->AddRow(' COPY >-on --> ' . json_encode($typeInstance));
            // create the processorder json
            // "Processorder_processnumber" => $orderlineInstance->Orderline_processnumber,
            $getter_names = get_class_vars($typeX);
            $columns = array();
            foreach ($getter_names as $key => $value) {
                if (isset($typeInstance->{$key}) && $key != 'parent' && $key != 'next' && $key != 'prev' && $key != "id") {
                    if ($typeInstance->isOwningRelation($key) || $typeInstance->isReferenceRelation($key)) {} else {
                        $columns[$key] = $typeInstance->{$key};
                    }
                }
            }
            
            $data = array();
            foreach ($columns as $key => $value) {
                $data1[$key] = $value;
                $data = array_merge($data, $data1);
            }
            $typeDest = $destinationType;
            if (isset($parentPath) && (strlen($parentPath) >= 2 || $parentPath !== '')) {
                $log->AddRow(' COPY ON > 2-on PARENT --> ' . json_encode($this) . ' - ' . json_encode($data));
                $parentInstances = $this->getPathReferences($parentPath);
                if (isset($parentInstances[0])) {
                    $parent = $parentInstances[0];
                    $type = $parent->get_class_name($parent);
                    $returnnewid = $laf->createAndAdd($type, $parent->getIdAsString(), $typeDest, $data);
                } else {
                    $log->AddRow(' NO-PARENT >-on --> ' . json_encode($this) . ' - ' . json_encode($parentInstances));
                }
            } else {
                $type = $this->get_class_name($this);
                $log->AddRow(' COPY ON > 2-on --> ' . json_encode($this) . ' - ' . json_encode($data));
                $returnnewid = $laf->createAndAdd($type, $this->getIdAsString(), $typeDest, $data);
                $this->reload();
            }
            
            $instance = $laf->findObject($typeDest, (string) $returnnewid);
            // link the processorder to the orderline
            if ($ifReference === true) {
                $reference = array();
                $reference['$ref'] = $typeInstance->getTableName();
                $reference['$id'] = (string) $typeInstance->_id['$id'];
                $instance->addReferenceObject($typeInstance->getTableName(), $reference);
            }
            // copy tree of relations
            foreach ($getter_names as $key => $value) {
                if (isset($typeInstance->{$key}) && $key != 'parent' && $key != 'next' && $key != 'prev' && $key != "id") {
                    
                    if ($typeInstance->isOwningRelation($key)) {
                        $typeR = ucfirst(substr($key, 0, strlen($key) - 1));
                        $instancesArray = $typeInstance->getInstances($typeR);
                        $instance->copyType($instancesArray);
                    }
                }
            }
        }
        return $returnnewid;
    }
   
    /**
     *
     * @param unknown $sourcePath
     *            as "a.b.c"
     * @param unknown $destinationType
     *            local destination child where $this is parent
     * @param unknown $ifReference
     *            true if references between copies must be created
     */
    public function duplicate()
    {
        $laf = new \Application\Controller\MongoObjectFactory();
        $log = \Application\Controller\Log::getInstance();
        $parent = $this->getParent();
        $classname = $parent->get_class_name($this);
        $id = $this->getIdAsString();
        $method = "get" . $classname . '[_id-' . $id . ']';
        $log->AddRow(' DUPLICATE >-on --> ' . json_encode($method));
        $parent->copyset($method, $classname);
    }

    public function copyType($typeInstances)
    {
        $laf = new \Application\Controller\MongoObjectFactory();
        $log = \Application\Controller\Log::getInstance();
        
        foreach ($typeInstances as $typeInstance) {
            // create the processorder json
            // "Processorder_processnumber" => $orderlineInstance->Orderline_processnumber,
            $getter_names = get_class_vars(get_class($typeInstance));
            $columns = array();
            foreach ($getter_names as $key => $value) {
                if (isset($typeInstance->{$key}) && $key != 'parent' && $key != 'next' && $key != 'prev' && $key != "id") {
                    if ($typeInstance->isOwningRelation($key) || $typeInstance->isReferenceRelation($key)) {} else {
                        $columns[$key] = $typeInstance->{$key};
                    }
                }
            }
            $log->AddRow(' COPY2 >-on --> ' . json_encode($columns));
            
            $data = array();
            foreach ($columns as $key => $value) {
                $data1[$key] = $value;
                $data = array_merge($data, $data1);
            }
            $type = $this->get_class_name($this);
            $typeDest = $typeInstance->getClassNameFromTable($typeInstance->getTableName());
            $returnnewid = $laf->createAndAdd($type, $this->getIdAsString(), $typeDest, $data);
            $instance = $laf->findObject($typeDest, (string) $returnnewid);
            // copy tree of relations
            foreach ($getter_names as $key => $value) {
                if (isset($typeInstance->{$key}) && $key != 'parent' && $key != "id") {
                    if ($typeInstance->isOwningRelation($key)) {
                        $typeR = ucfirst(substr($key, 0, strlen($key) - 1));
                        $instancesArray = $typeInstance->getInstances($typeR);
                        $instance->copyType($instancesArray);
                    }
                }
            }
        }
    }

    public function getGeoLocation($addr_line)
    {
        $geoLoc = array();
        $geoLoc['latitude'] = "0";
        $geoLoc['longitude'] = "0";
        
        try {
            // if no lat then just get it from the address line
            if (isset($addr_line) && strlen($addr_line) > 5) {
                \Application\Controller\Log::getInstance()->AddRow(' getGeoLocation 1' . $addr_line);
                $geocode = file_get_contents('http://maps.google.com/maps/api/geocode/json?address=' . urlencode($addr_line) . '&sensor=true');
                if (isset($geocode)) {
                    $output = json_decode($geocode);
                    \Application\Controller\Log::getInstance()->AddRow(' getGeoLocation 2' . $geocode);
                    if (isset($output->results[0])) {
                        \Application\Controller\Log::getInstance()->AddRow(' getGeoLocation 3');
                        $geoLoc['latitude'] = $output->results[0]->geometry->location->lat;
                        $geoLoc['longitude'] = $output->results[0]->geometry->location->lng;
                    }
                }
            }
        } catch (\Exception $e) {
            print_r($e);
        }
        return $geoLoc;
    }

    public function getDistanceMatrixDirect($origin, $destinationArr, $unit)
    {
        $matrix = [];
        foreach ($origin as $originId) {
            $matrix[$originId['id']] = [];
            foreach ($destinationArr as $destinationId) {
                $originArr = explode(",", $originId['coordinates']);
                $lat1 = $originArr[0];
                $lon1 = $originArr[1];
                $destinationArr1 = explode(",", $destinationId['geocode']);
                $lat2 = $destinationArr1[0];
                $lon2 = $destinationArr1[1];
                $dist = $this->getDistance($lat1, $lon1, $lat2, $lon2, $unit);
                $duration = $dist * 60 / 50; // (50 km/h average) - minutes
                $matrix[$originId['id']][$destinationId['id']] = [];
                $matrix[$originId['id']][$destinationId['id']]['distance']['value'] = $dist;
                $matrix[$originId['id']][$destinationId['id']]['distance']['text'] = $dist;
                $matrix[$originId['id']][$destinationId['id']]['duration']['value'] = $duration;
                $matrix[$originId['id']][$destinationId['id']]['duration']['text'] = $duration;
                $matrix[$originId['id']][$destinationId['id']]['capacity']['value'] = $destinationId['capacity'];
                $matrix[$originId['id']][$destinationId['id']]['capacity']['text'] = '';
                $matrix[$originId['id']][$destinationId['id']]['id']['value'] = $destinationId['id'];
                $matrix[$originId['id']][$destinationId['id']]['id']['text'] = '';
            }
        }
        return $matrix;
    }

    public function getDistanceMatrix($origin, $destinationArr, $type = "google")
    {
        if ($type == "direct") {
            return $this->getDistanceMatrixDirect($origin, $destinationArr, "K");
        }
        $originStr = '';
        $destinationStr = '';
        $index = 0;
        foreach ($origin as $originId) {
            if ($index == 0) {
                $originStr = $originId;
                $index = 1;
            } else {
                $originStr = $originStr . "|" . $originId;
            }
        }
        $index = 0;
        foreach ($destinationArr as $dest) {
            $destination[] = $dest["geocode"];
            $destinationCapacity[] = $dest["capacity"];
            $destinationIds[] = $dest["id"];
        }
        foreach ($destination as $destinationId) {
            if ($index == 0) {
                $destinationStr = $destinationId;
                $index = 1;
            } else {
                $destinationStr = $destinationStr . "|" . $destinationId;
            }
        }
        $matrix = [];
        // \Application\Controller\Log::getInstance()->AddRow(' AUTOPLAN2 ' . json_encode($originStr));
        \Application\Controller\Log::getInstance()->AddRow(' AUTOPLAN2 ' . json_encode($destinationStr));
        $myKey = "AIzaSyCaYWKEFYLuVUKnzViTgdBtlc42vIDrzpk";
        $geocode = file_get_contents('https://maps.googleapis.com/maps/api/distancematrix/json?key=' . $myKey . '&units=kilometer&origins=' . urlencode($originStr) . '&destinations=' . urlencode($destinationStr));
        $output = json_decode($geocode);
        \Application\Controller\Log::getInstance()->AddRow(' AUTOPLAN2 ' . json_encode($output));
        $i = 0;
        $j = 0;
        foreach ($output->rows as $row) {
            // origin
            $originIndex = $origin[$i];
            $matrix[$originIndex] = [];
            $j = 0;
            foreach ($row->elements as $el) {
                // destination
                $destinationIndex = $destination[$j];
                $matrix[$originIndex][$destinationIndex] = [];
                $matrix[$originIndex][$destinationIndex]['distance']['value'] = $el->distance->value;
                $matrix[$originIndex][$destinationIndex]['distance']['text'] = $el->distance->text;
                $matrix[$originIndex][$destinationIndex]['duration']['value'] = $el->duration->value;
                $matrix[$originIndex][$destinationIndex]['duration']['text'] = $el->duration->text;
                $matrix[$originIndex][$destinationIndex]['capacity']['value'] = $destinationCapacity[$j];
                $matrix[$originIndex][$destinationIndex]['capacity']['text'] = '';
                $matrix[$originIndex][$destinationIndex]['id']['value'] = $destinationIds[$j];
                $matrix[$originIndex][$destinationIndex]['id']['text'] = '';
                $j = $j + 1;
            }
            $i = $i + 1;
        }
        return $matrix;
    }

    public function getCompleteOptimalRoute($origin, $_distArr, $comparable, $max)
    {
        $returnArray = [];
        $toReturnArray = [];
        $newReturnArray = [];
        $newReturnArray['path'] = [];
        $newReturnArray['destination'] = '';
        $newReturnArray['duration'] = 0;
        $newReturnArray['capacity'] = 0;
        $newReturnArray['distance'] = 0;
        $returnArray[$comparable] = 0;
        $firstRound = true;
        \Application\Controller\Log::getInstance()->AddRow(' DIMAOPTA1 ' . $comparable . " - " . $max);
        while ($newReturnArray[$comparable] < $max && count($_distArr) > 1) {
            $toReturnArray = $this->getOptimalRoute($origin, $_distArr, $comparable, $max, $firstRound);
            $firstRound = false;
            $returnArray = $toReturnArray;
            if (isset($toReturnArray['path']) && count($toReturnArray['path']) > 0) {
                $i = 0;
                foreach ($toReturnArray['path'] as $index) {
                    if (! $this->arrayHasValue($newReturnArray['path'], $index)) {
                        $newReturnArray['path'][] = $index;
                        if ($i > 0) {
                            $newReturnArray['pathitems'][$index] = $toReturnArray['pathItems'][$i];
                        }
                    }
                    $i = $i + 1;
                }
                
                $newReturnArray['destination'] = $newReturnArray['destination'] + "." + $toReturnArray['destination'];
                $newReturnArray['duration'] = $newReturnArray['duration'] + $toReturnArray['duration'];
                $newReturnArray['capacity'] = $newReturnArray['capacity'] + $toReturnArray['capacity'];
                $newReturnArray['distance'] = $newReturnArray['distance'] + $toReturnArray['distance'];
                $newReturnArray['routedistance'] = $toReturnArray['routedistance'];
                \Application\Controller\Log::getInstance()->AddRow(' DIMAOPTA2 ' . $max . " - " . json_encode($newReturnArray));
                // $returnArray = $this->getOptimalRoute($origin, $_distArr, $comparable, $max);
                $origin = $returnArray['destination'];
                
                foreach ($returnArray['path'] as $index) {
                    if ($index != $origin) {
                        unset($_distArr[$index]);
                    }
                }
            } else {
                break;
            }
        }
        return $newReturnArray;
    }

    public function arrayHasValue($arr, $val)
    {
        if (isset($arr) && count($arr) > 0) {
            foreach ($arr as $value) {
                if ($value == $val) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     *
     * @param unknown $_distArr
     *            array of destinations
     * @param unknown $comparable
     *            "duration", "distance" or "capacity"
     * @param unknown $max
     *            - max value to go for comparable
     * @return array - path and totals per comparable
     *        
     *        
     *         $_distArr[1][2]['distance']['value'] = 7; // km
     *         $_distArr[1][2]['duration']['value'] = 600; //sec
     *         $_distArr[1][2]['capacity']['value'] = 1; //package
     */
    public function getOptimalRoute($origin, $_distArr, $comparable, $max, $firstRound = false)
    {
        \Application\Controller\Log::getInstance()->AddRow(' DIMAOPT0 ' . json_encode($origin) . " - " . $max);
        $retArray = [];
        // origin
        $a = $origin;
        if (isset($_distArr) && count($_distArr) > 1) {
            // initialize the array for storing
            $S = array(); // the nearest path with its parent and weight
            $Q = array(); // the left nodes without the nearest path
            foreach (array_keys($_distArr) as $val) {
                $Q[$val]['distance'] = 99999;
                $Q[$val]['duration'] = 99999;
                $Q[$val]['capacity'] = 99999;
                $Q[$val]['routedistance'] = 99999;
            }
            $Q[$a]['distance'] = 0;
            $Q[$a]['duration'] = 0;
            $Q[$a]['capacity'] = 0;
            $Q[$a]['routedistance'] = 0;
            $lastIndex = $a;
            
            // start calculating
            // while (! empty($Q)) {
            $min = $this->minOfKey($Q, "distance"); // array_search(min($Q), $Q); // the most min weight
                                                    // \Application\Controller\Log::getInstance()->AddRow(' DIMAOPT123 ' . json_encode($origin) . " - " . $min);
            $keyPrev = 0;
            foreach ($_distArr[$min] as $key => $val) {
                if (! empty($Q[$key]) && $Q[$min]['distance'] + $val['distance']['value'] < $Q[$key]['distance']) {
                    
                    $Q[$key]['distance'] = $Q[$min]['distance'] + $val['distance']['value'];
                    $Q[$key]['duration'] = $Q[$min]['duration'] + $val['duration']['value'];
                    $Q[$key]['capacity'] = $Q[$min]['capacity'] + $val['capacity']['value'];
                    $Q[$key]['routedistance'] = $val['distance']['value'];
                    
                    if ($Q[$key][$comparable] <= $max) {
                        if ($keyPrev == 0) {
                            $lastIndex = $key;
                        }
                        
                        if (array_key_exists($key, $S)) {
                            $keyPrev = $key;
                            $S[$key] = array(
                                $S[$key][0],
                                $S[$key][1],
                                $S[$key][2],
                                $S[$key][3],
                                $S[$key][4]
                            );
                        } else {
                            $keyPrev = $key;
                            $S[$key] = array(
                                $min,
                                $Q[$key]['distance'],
                                $Q[$key]['duration'],
                                $Q[$key]['capacity'],
                                $Q[$key]['routedistance']
                            );
                        }
                        $lastval = $S[$key];
                    } else {
                        break;
                    }
                }
            }
            unset($Q[$min]);
            // }
            
            $path = array();
            $pos = $lastIndex;
            // $S = array_reverse($S);
            while ($pos != $a) {
                $path[] = $pos;
                $pos = $S[$pos][0];
            }
            $path[] = $a;
            $path = array_reverse($path);
            
            $pathItems = array();
            $pos = $lastIndex;
            
            while ($pos != $a) {
                $pathItems[] = $S[$pos];
                $pos = $S[$pos][0];
            }
            \Application\Controller\Log::getInstance()->AddRow(' DIMAOPT1 ' . json_encode($S));
            // $S[$key] = array(
            // $min,
            // $Q[$key]['distance'],
            // $Q[$key]['duration'],
            // $Q[$key]['capacity']
            // );
            $pathItems[] = $a;
            
            $pathItems = array_reverse($pathItems);
            
            // print result
            
            $retArray['path'] = $path;
            $retArray['pathItems'] = $pathItems;
            $retArray['destination'] = $lastIndex;
            $retArray['distance'] = $S[$lastIndex][1];
            $retArray['duration'] = $S[$lastIndex][2];
            $retArray['capacity'] = $S[$lastIndex][3];
            $retArray['routedistance'] = $S[$lastIndex][4];
        }
        // \Application\Controller\Log::getInstance()->AddRow(' DIMAOPT2 ' . json_encode($retArray));
        return $retArray;
    }

    function maxOfKey($array, $key)
    {
        if (! is_array($array) || count($array) == 0)
            return false;
        $max = 1;
        $ret = array_keys($array)[0];
        foreach ($array as $keyIx => $a) {
            if ($a[$key] > $max) {
                $max = $a[$key];
                $ret = $keyIx;
            }
        }
        return $ret;
    }

    function minOfKey($array, $key)
    {
        if (! is_array($array) || count($array) == 0)
            return false;
        $min = 9999; // $array[0][$key];
        $ret = array_keys($array)[0];
        foreach ($array as $keyIx => $a) {
            if ($a[$key] < $min) {
                $min = $a[$key];
                $ret = $keyIx;
            }
        }
        return $ret;
    }

    /**
     * *
     *
     * @param unknown $lat1            
     * @param unknown $lon1            
     * @param unknown $lat2            
     * @param unknown $lon2            
     * @param unknown $unit
     *            "K" for km - default miles
     * @return number
     */
    function getDistance($lat1, $lon1, $lat2, $lon2, $unit)
    {
        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        $unit = strtoupper($unit);
        
        if ($unit == "K") {
            return ($miles * 1.609344);
        } else 
            if ($unit == "N") {
                return ($miles * 0.8684);
            } else {
                return $miles;
            }
    }

    public static function getRelationFromArray($name, $array)
    {
        if ($name == '') {
            return $array;
        }
        if (array_key_exists($name, $array) == true) {
            return $array[$name];
        }
        return array();
    }

    public static function getRelationRefFromArray($name, $array)
    {
        $refRet = array();
        if (array_key_exists($name, $array) == true) {
            return $array[$name];
        }
        return array();
    }

    public static function getRelationType($name)
    {
        $relations = array();
        return static::getRelationFromArray($name, $relations);
    }

    public static function isReferenceRelation($keyRel)
    {
        $relation = static::getRelationType($keyRel);
        if (isset($relation) && is_string($relation) && strlen($relation) > 1) {
            // only if a ref relation that has id/ref
            
            if ($relation == 'one-to-one' || $relation == 'one-to-many' || $relation == 'many-to-one') {
                return true;
            }
        }
        return false;
    }

    public function isOwningRelation($keyRel)
    {
        $owning_rel = array();
        $relations = $this->getRelationType('');
        foreach ($relations as $key => $value) {
            $pos = strpos($value, "owning");
            if ($pos === false) {} else {
                if ($key === $keyRel) {
                    return true;
                }
            }
        }
        return false;
    }

    public function isRelation($key)
    {
        if (count($this->getRelationType($key)) > 0) {
            
            return true;
        }
        return false;
    }

    function time_diff(\DateTimeInterface $a, \DateTimeInterface $b, $absolute = false, $cap = 'H')
    {
        
        // Get unix timestamps, note getTimeStamp() is limited
        $b_raw = intval($b->format("U"));
        $a_raw = intval($a->format("U"));
        
        // Initial Interval properties
        $h = 0;
        $m = 0;
        $invert = 0;
        
        // Is interval negative?
        if (! $absolute && $b_raw < $a_raw) {
            $invert = 1;
        }
        
        // Working diff, reduced as larger time units are calculated
        $working = abs($b_raw - $a_raw);
        
        // If capped at hours, calc and remove hours, cap at minutes
        if ($cap == 'H') {
            $h = intval($working / 3600);
            $working -= $h * 3600;
            $cap = 'M';
        }
        
        // If capped at minutes, calc and remove minutes
        if ($cap == 'M') {
            $m = intval($working / 60);
            $working -= $m * 60;
        }
        
        // Seconds remain
        $s = $working;
        
        // Build interval and invert if necessary
        $interval = new \DateInterval('PT' . $h . 'H' . $m . 'M' . $s . 'S');
        $interval->invert = $invert;
        
        return $interval;
    }

    public function reindexAfter($typeRef, $index)
    {
        $list = $this->getInstances($typeRef);
        foreach ($list as $item) {
            if ($item->index >= $index) {
                $item->index = $item->index + 1;
                $item->update();
            }
        }
    }

    function firstDayOfMonth($date)
    {
        $format = 'd-m-Y';
        $dateI = \DateTime::createFromFormat($format, $date);
        // \Application\Controller\Log::getInstance()->AddTrace(' firstDayOfMonth >>>>>>>>>>>> ' . json_encode($this));
        $month_number = $dateI->format("m");
        $year = $dateI->format("Y");
        $dateNew = "1-" . $month_number . "-" . $year;
        $dto = \DateTime::createFromFormat($format, $dateNew);
        return $dto;
    }

    function lastDayOfMonth($date)
    {
        $format = 'd-m-Y';
        $dateI = \DateTime::createFromFormat($format, $date);
        $format = 'Y-m-d';
        $dto = \DateTime::createFromFormat($format, $dateI->format('Y-m-t'));
        return $dto;
    }

    function rand_color()
    {
        $col1 = sprintf('%02X', mt_rand(0, 0xFF));
        $col2 = sprintf('%02X', mt_rand(0, 0xFF));
        $col3 = sprintf('%02X', mt_rand(0, 0xFF));
        return '#' . $col1 . $col2 . $col3;
    }
}

class cmp
{

    var $key;

    var $flag;

    function __construct($key)
    {
        $this->flag = false;
        $this->key = $key;
    }

    function cmp__($a, $b)
    {
        $key = $this->key;
        // $log = \Application\Controller\Log::getInstance();
        
        if ($this->getFormatVarialble($a->$key, $key) == $this->getFormatVarialble($b->$key, $key))
            return 0;
        else 
            if ($this->getFormatVarialble($a->$key, $key) > $this->getFormatVarialble($b->$key, $key)) {
                return 1;
            } else {
                $this->flag = true;
                return - 1;
            }
        // return (($this->getFormatVarialble($a->$key, $key) > $this->getFormatVarialble($b->$key, $key)) ? 1 : - 1);
    }

    public function getFormatVarialble($input, $key = null)
    {
        if (is_array($input)) {
            return $input;
        }
        if (is_numeric($input)) {
            if (substr($input, 0, 1) === '0' || substr($input, 0, 1) === '+') {
                return $input;
            } else {
                return $input + 0;
            }
        }
        
        if ($input == 'TRUE') {
            $input = 'true';
            return $input;
        }
        if ($input == 'FALSE') {
            $input = 'false';
            return $input;
        }
        // $input = trim($input);
        if (strlen($input) < 11) {
            $formats = array(
                "d.m.Y",
                "d/m/Y",
                "d-m-Y"
            );
            foreach ($formats as $format) {
                $date = \DateTime::createFromFormat($format, $input);
                if ($date == false || ! (date_format($date, $format) == $input)) {} 

                else {
                    // date("d-m-Y", strtotime($input));//
                    return new \MongoDate(strtotime($input));
                }
            }
        } else {
            // $input = trim($input);
            $formats = array(
                "d-m-Y H:i",
                "d-m-Y h:i",
                "d-m-Y g:i",
                "d-m-Y G:i"
            );
            foreach ($formats as $format) {
                $date = \DateTime::createFromFormat($format, $input);
                if ($date == false || ! (date_format($date, $format) == $input)) {} 

                else {
                    // date("d-m-Y", strtotime($input));//
                    // flag that this is date time!!
                    $usec = 1241;
                    return new \MongoDate(strtotime($input), $usec);
                }
            }
        }
        return $input;
    }

    public static function getMappingColumns()
    {
        return array();
    }
}

?>