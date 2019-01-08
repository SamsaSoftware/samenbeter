<?php
namespace DataFixture\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Console\Request as ConsoleRequest;
use RuntimeException;
use Application\DatabaseConnection\Database;

class SimulatorFixtureController extends AbstractActionController
{

    public function runAction()
    {
        echo "starting\n";
        $_SESSION["dbname"] = "Simulator";
        // >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
        // Create the types
        // >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
        $typeW = 'Workspace';
        $typeV = 'View';
        $typeComp = 'Component';
        $typeComponent = 'Component';
        $typeContextMenu = 'Contextmenu';
        $peM = 'Menu';
        $typeParameter = 'Parameter';
        $typePar = 'Parameter';
        $typeTemplate = 'Template';
        $typeMap = 'Map';
        $typePar = 'Parameter';
        $json_type = array(
            "id" => "121",
            "text" => "schema/json"
        );
        $filter_type = array(
            "id" => "127",
            "text" => "filter"
        );
        
        $gridcolumn_type = array(
            "id" => "124",
            "text" => "gridcolumn"
        );
        
        $gridrowrule_type = array(
            "id" => "125",
            "text" => "gridrowrule"
        );
        
        echo "\n";
        $sing = \Application\Document\Helper\NotificationCenter::getInstance();
        $sing->getInstance()->setClasspath("Simulator");
        $request = $this->getRequest();
        if (! $request instanceof ConsoleRequest) {
            throw new RuntimeException('You can only use this action from a console!');
        }
        $mongoObjectFactory = new \Application\Controller\MongoObjectFactory();
        
        $m = Database::getInstance();
        
        $dbmain = $m->{$mongoObjectFactory->getDBName("Workspace")};
        
        // create collections user & user roles
        // $mobile = $dbmain->createCollection("Mobiles");
        // $SpIssue = $dbmain->createCollection("SpIssues");
        // $SpCustomer = $dbmain->createCollection("SpCustomers");
        // $SpOwner = $dbmain->createCollection("SpOwners");
        // $views = $dbmain->createCollection("views");
        // $menus = $dbmain->createCollection("menus");
        //
        // $SpIssue->drop();
        // $SpCustomer->drop();
        // $SpOwner->drop();
        // $mobile->drop();
        //
        // $views->remove(array(
        // 'scope' => 'user'
        // ));
        // $menus->remove(array(
        // 'scope' => 'user'
        // ));
        
        // > define the types
        $typeV = 'View';
        $typeComp = 'Component';
        // < define the types
        
        /**
         * *******************************************************************************
         * Setup organization
         * *******************************************************************************
         */
        $organizationObjectInstance = $mongoObjectFactory->findObjectByCriteria('Organization', array(
            'classpath' => 'Simulator'
        ));
        $workspaceId = $organizationObjectInstance['workspaces'][0]['$id'];
        
        echo "Setup organization\n";
        // get workspace of organization
        $typeW = 'Workspace';
        $workspaceD = $mongoObjectFactory->findObject($typeW, $workspaceId);
        
        /**
         * *******************************************************************************
         * Setup workspace
         * *******************************************************************************
         */
        $typeW = 'Workspace';
        $returnW = $workspaceD->_id['$id'];
        $data = array(
            "title" => "Home",
            "name" => "workspace1",
            "parentType" => "Workspace",
            "parentId" => (string) $returnW,
            "scope" => "user"
        );
        $returnV = $mongoObjectFactory->createAndAdd($typeW, $returnW, $typeV, $data);
        
        echo "Master table here ...\n";
        $masterdata = $workspaceD->getInstances("Masterdata");
        $masterdataid = (string) $masterdata[0]->_id['$id'];
        
        // TRANSPORT ORDER STATUS
        $dataMasterTable = array(
            "name" => "GROUPTYPE",
            "items" => "[
            {\"recid\":\"0\",\"type\":\"public\", \"select\":\"0\"},
            {\"recid\":\"1\",\"type\":\"protected\", \"select\":\"1\"},
            {\"recid\":\"2\",\"type\":\"private\", \"select\":\"2\"}]",  
            "scope" => "user"
        );
        $typeMasterTable = 'Mastertable';
        $returnMasterDataTransportOrderStatus = $mongoObjectFactory->createAndAdd('Masterdata', $masterdataid, $typeMasterTable, $dataMasterTable);
        // TRANSPORT ORDER STATUS
        $dataMasterTable = array(
            "name" => "REQUESTTYPE",
            "items" => "[
            {\"recid\":\"0\",\"type\":\"invitation\", \"select\":\"0\"},
            {\"recid\":\"1\",\"type\":\"request\", \"select\":\"1\"}]",
            "scope" => "user"
        );
        $typeMasterTable = 'Mastertable';
        $returnMasterDataTransportOrderStatus = $mongoObjectFactory->createAndAdd('Masterdata', $masterdataid, $typeMasterTable, $dataMasterTable);
        
        // TRANSPORT ORDER STATUS
        $dataMasterTable = array(
            "name" => "REQUESTSTATUS",
            "items" => "[
            {\"recid\":\"0\",\"type\":\"new\", \"select\":\"0\"},
            {\"recid\":\"1\",\"type\":\"accepted\", \"select\":\"1\"},
            {\"recid\":\"2\",\"type\":\"refused\", \"select\":\"2\"},
            {\"recid\":\"3\",\"type\":\"canceled\", \"select\":\"3\"},
            {\"recid\":\"4\",\"type\":\"error\", \"select\":\"4\"}]",
            "scope" => "user"
        );
        $typeMasterTable = 'Mastertable';
        $returnMasterDataTransportOrderStatus = $mongoObjectFactory->createAndAdd('Masterdata', $masterdataid, $typeMasterTable, $dataMasterTable);
        // ////////////////////////////////////////////////////////////////////////////////
        // ////////////////////////////////////////////////////////////////////////////////
        // /// END create TEMPLATE
        // ////////////////////////////////////////////////////////////////////////////////
        // ////////////////////////////////////////////////////////////////////////////////
        
        echo "samsarole\n";
        $data = array(
            "name" => "extern",
            "default" => true
        );
        $returnsamsarolecustomer = $mongoObjectFactory->createAndAdd('Workspace', (string) $returnW, 'Samsarole', $data);
        
        $typeContextmenu = 'Contextmenu';
        
        /**
         * *******************************************************************************
         * Start create view USER
         * *******************************************************************************
         */
        
        $data = array(
            "title" => "SUsers",
            "name" => "/listReference/Suser.getSfriend", // numele claselor mele din simulator module/Application/view/application/index/builder.phtml:14
            "parentType" => "Workspace",
            "parentId" => (string) $returnW,
            "scope" => "user"
        ); // rolul user-ului
        
        $typeV = 'View';
        $returnVArchive = $mongoObjectFactory->createAndAdd($typeW, (string) $returnW, $typeV, $data);
        
        echo "USER" . json_encode($returnVArchive);
        // > get the grid component
        $data = array(
            "name" => "grid"
        );
        $returnComponentGridCustomer = $mongoObjectFactory->createAndAdd($typeV, (string) $returnVArchive, $typeComp, $data);
        // < get the grid component
        // > create the components

        
        // create a filter on grid
        // $data = array(
        // "name" => "definitiveInvoicev",
        // "type" => array(
        // $filter_type
        // ),
        // "referencelink" => "getSpproject",
        // "definition" => "Spprojectstate:text-inchis"
        // );
        // $return1 = $mongoObjectFactory->createAndAdd($typeComp, (string) $returnProjectGrid, $typeParameter, $data);
        
        // < create the components
        
        // > get the grid ref component
        $data = array(
            "name" => "gridRef"
        );
        $returnComponentGridRefTrainer = $mongoObjectFactory->createAndAdd($typeV, (string) $returnVArchive, $typeComp, $data);
        // < get the grid ref component
        
        // > add fields
        $workspaceD->createField($returnVArchive, "Suser", \Application\Document\Field::TYPE_FORM, "", "form", "formTiltlr", true, "", '{"maxWidth":600, "maxHeight":400}');
        $workspaceD->createField($returnVArchive, "Suser", \Application\Document\Field::TYPE_BUTTON, "", "save", "Save", false, "saveObject", "");
        $workspaceD->createField($returnVArchive, "Suser", \Application\Document\Field::TYPE_TEXT, "", "name", "First name^style='width: 200px; height: 26px'", false, "", "");
        $workspaceD->createField($returnVArchive, "Suser", \Application\Document\Field::TYPE_TEXT, "", "lastName", "Last name^style='width: 200px; height: 26px'", false, "", "");
        $workspaceD->createField($returnVArchive, "Suser", \Application\Document\Field::TYPE_TEXT, "", "email", "Email^style='width: 200px; height: 26px'", false, "", "");
        $workspaceD->createField($returnVArchive, "Suser", \Application\Document\Field::TYPE_REF_VALUE, "", "stypes^style='width: 200px; height: 26px'", "Types^style='width: 200px; height: 26px'", false, "", "getMethodResultListReference?objectType=@type@&methodName=getStype&id=@objectId@&action=@action@");

        $workspaceD->createField($returnVArchive, "Sfriend", \Application\Document\Field::TYPE_FORM, "", "form", "formTiltlr", true, "", '{"maxWidth":600, "maxHeight":200}');
        $workspaceD->createField($returnVArchive, "Sfriend", \Application\Document\Field::TYPE_BUTTON, "", "save", "Save", false, "saveObject", "");
        $workspaceD->createField($returnVArchive, "Sfriend", \Application\Document\Field::TYPE_TEXT, "", "name", "Name^style='width: 200px; height: 26px'", false, "", "");
        $workspaceD->createField($returnVArchive, "Sfriend", \Application\Document\Field::TYPE_REF_VALUE, "", "stypes", "Types^style='width: 200px; height: 26px'", false, "", "getMethodResultListReference?objectType=@type@&methodName=getStype&id=@objectId@&action=@action@");
        
        $typeContextMenu = 'Contextmenu'; // si aici sunt mi multe tipuri. ex: acitonExecution method | actionResponse

        $data = array(
            "name" => "definitive4",
            "actionExecution" => "method",
            "actionResponse" => "showMessage(\"%name%\");",
            "link" => "Validate user",
            "parentType" => "Workspace",
            "objectType" => "Suser",
            "method" => 'createSimulatorUser();',
            "serviceName" => "",
            "serviceMethod" => "",
            "template" => ""
        );
        $return = $mongoObjectFactory->createAndAdd($typeComp, (string) $returnComponentGridCustomer, $typeContextmenu, $data);
        
        // > create the context menu with method name=dialog name
        $data = array(
            "name" => "test2",
            "actionExecution" => "inputform",
            "viewId" => "viewId",
            "link" => "Test 2",
            // "type" => "list",
            "parentType" => "Workspace",
            "objectType" => "Suser",
            "method" => "importDialog1",
            "icon" => "w2ui-icon-plus"
        );
        $return = $mongoObjectFactory->createAndAdd($typeComp, (string) $returnComponentGridCustomer, $typeContextmenu, $data);


        // > create the dialog with the corresponding fields
        $workspaceD->createField($returnVArchive, "importDialog1", \Application\Document\Field::TYPE_TEXT, "", "textin", "test text", true, "", "");
        $workspaceD->createField($returnVArchive, "importDialog1", \Application\Document\Field::TYPE_BUTTON, "", "save", "Execute  test", true, "method", "", "test2(@textin@);", "", "showMessage(\"@name@\");");
        
        // > create menu Customer
        $data = array(
            "id" => "UsersView",
            "text" => "SUsers",
            "type" => "radio",
            "click" => "buttonClickHandler",
            "icon" => "fa fa-building-o",
            "scope" => 'user',
            "group" => 1
        );
        $typeM = 'Menu';
        $returnMArchive = $mongoObjectFactory->createAndAdd($typeW, (string) $returnW, $typeM, $data);
        // < create menu Customer
        
        // > link menu Customer to view Customer
        $object = $mongoObjectFactory->findObject("Menu", (string) $returnMArchive);
        $reference = array();
        $reference['$ref'] = "views";
        $reference['$id'] = (string) $returnVArchive;
        $object->addReferenceObject("views", $reference);
        // < link menu Cutomer to view Customer
        
        /**
         * *******************************************************************************
         * END create view USER
         * *******************************************************************************
         */

        
        /**
         * *******************************************************************************
         * Start create view ADMIN ConfigProfile
         * *******************************************************************************
         */
        
        $data = array(
            "title" => "configAtrib",
            "name" => "/list/Profileattributetemplate",
            "parentType" => "Workspace",
            "parentId" => (string) $returnW,
            "scope" => "user"
        );
        
        $typeV = 'View';
        $returnVAConfig = $mongoObjectFactory->createAndAdd($typeW, (string) $returnW, $typeV, $data);
        
        // > get the grid component
        $data = array(
            "name" => "grid"
        );
        $returnComponentGridCustomer = $mongoObjectFactory->createAndAdd($typeV, (string) $returnVAConfig, $typeComp, $data);
        
        // > create menu Customer
        $data = array(
            "id" => "ConfigAttrib",
            "text" => "Config profile",
            "type" => "radio",
            "click" => "buttonClickHandler",
            "icon" => "fa fa-building-o",
            "scope" => 'user',
            "group" => 1
        );
        $typeM = 'Menu';
        $returnMAConfig = $mongoObjectFactory->createAndAdd($typeW, (string) $returnW, $typeM, $data);
        
        // > link menu Customer to view Customer
        $object = $mongoObjectFactory->findObject("Menu", (string) $returnMAConfig);
        $reference = array();
        $reference['$ref'] = "views";
        $reference['$id'] = (string) $returnVAConfig;
        $object->addReferenceObject("views", $reference);
        // < link menu Cutomer to view Customer
        
        /**
         * *******************************************************************************
         * END create view ADMIN ConfigProfile
         * *******************************************************************************
         */

        
        /**
         * *******************************************************************************
         * Start create view ADMIN Types
         * *******************************************************************************
         */
        
        $data = array(
            "title" => "StypeKeyToTranslate",
            "name" => "/list/Stype",
            "parentType" => "Workspace",
            "parentId" => (string) $returnW,
            "scope" => "user"
        );
        
        $typeV = 'View';
        $returnVAConfigStype = $mongoObjectFactory->createAndAdd($typeW, (string) $returnW, $typeV, $data);
        
        // > get the grid component
        $data = array(
            "name" => "grid"
        );
        $returnComponentGridCustomer = $mongoObjectFactory->createAndAdd($typeV, (string) $returnVAConfigStype, $typeComp, $data);
        
        // > create menu Customer
        $data = array(
            "id" => "StypeMenuID",
            "text" => "Types",
            "type" => "radio",
            "click" => "buttonClickHandler",
            "icon" => "fa fa-building-o",
            "scope" => 'user',
            "group" => 1
        );
        $typeM = 'Menu';
        $returnMAConfig = $mongoObjectFactory->createAndAdd($typeW, (string) $returnW, $typeM, $data);
        
        // > link menu Customer to view Customer
        $object = $mongoObjectFactory->findObject("Menu", (string) $returnMAConfig);
        $reference = array();
        $reference['$ref'] = "views";
        $reference['$id'] = (string) $returnVAConfigStype;
        $object->addReferenceObject("views", $reference);
        // < link menu Cutomer to view Customer
        
        $workspaceD->createField($returnVAConfigStype, "Stype", \Application\Document\Field::TYPE_BUTTON, "", "save", "Save", false, "saveObject", "");
        $workspaceD->createField($returnVAConfigStype, "Stype", \Application\Document\Field::TYPE_TEXT, "", "name", "data", false, "", "");
        $workspaceD->createField($returnVAConfigStype, "Stype", \Application\Document\Field::TYPE_REF_VALUE, "", "susers", "Owners^style='width: 200px; height: 26px'", false, "", "getMethodResultListReference?objectType=@type@&methodName=getSuser&id=@objectId@&action=@action@");
        
        /**
         * *******************************************************************************
         * END create view ADMIN Types
         * *******************************************************************************
         */
        



        /**
         * *******************************************************************************
         * Start create view ADMIN PROFILE ATTRIBUTES
         * *******************************************************************************
         */
        
        $data = array(
            "title" => "Profile Attributes",
            "name" => "/listReference/Suser.getProfileattribute",
            "parentType" => "Workspace",
            "parentId" => (string) $returnW,
            "scope" => "user"
        );

        $typeV = 'View';
        $returnVAConfigPA = $mongoObjectFactory->createAndAdd($typeW, (string) $returnW, $typeV, $data);
        
        // > get the grid component
        $data = array(
            "name" => "grid"
        );
        $returnComponentGridUser = $mongoObjectFactory->createAndAdd($typeV, (string) $returnVAConfigPA, $typeComp, $data);

        $data = array(
            "name" => "gridRef"
        );
        $returnComponentGridProfileattribute = $mongoObjectFactory->createAndAdd($typeV, (string) $returnVAConfigPA, $typeComp, $data);


        // > create the menu add
        $data = array(
            "name" => "add1",
            "actionExecution" => "",
            "link" => "Add",
            "type" => "list",
            "parentType" => "Workspace",
            "objectType" => "Profileattribute",
            "method" => "add",
            "icon" => "w2ui-icon-plus"
        );
        $returnCompmenu = $mongoObjectFactory->createAndAdd($typeComp, (string) $returnComponentGridUser, $typeContextMenu, $data);
        // < create the menu add


        // > create the menu delete
        $data = array(
            "name" => "delete1",
            "actionExecution" => "",
            "link" => "Delete",
            "type" => "list",
            "parentType" => "Workspace",
            "objectType" => "Profileattribute",
            "method" => "delete",
            "icon" => "w2ui-icon-minus"
        );
        $returnCompmenu = $mongoObjectFactory->createAndAdd($typeComp, (string) $returnComponentGridUser, $typeContextMenu, $data);
        // < create the menu delete


        // > create the menu add
        $data = array(
            "name" => "add5",
            "actionExecution" => "",
            "link" => "Add",
            "type" => "list",
            "parentType" => "Workspace",
            "objectType" => "Profileattribute",
            "method" => "add",
            "icon" => "w2ui-icon-plus"
        );
        $returnCompmenu = $mongoObjectFactory->createAndAdd($typeComp, (string) $returnComponentGridProfileattribute, $typeContextMenu, $data);
        // < create the menu add


        // > create the menu delete
        $data = array(
            "name" => "delete5",
            "actionExecution" => "",
            "link" => "Delete",
            "type" => "list",
            "parentType" => "Workspace",
            "objectType" => "Profileattribute",
            "method" => "delete",
            "icon" => "w2ui-icon-minus"
        );
        $returnCompmenu = $mongoObjectFactory->createAndAdd($typeComp, (string) $returnComponentGridProfileattribute, $typeContextMenu, $data);
        // < create the menu delete

        // > create menu Customer
        $data = array(
            "id" => "PAMenuID",
            "text" => "Profile Attributes",
            "type" => "radio",
            "click" => "buttonClickHandler",
            "icon" => "fa fa-building-o",
            "scope" => 'user',
            "group" => 1
        );
        $typeM = 'Menu';
        $returnMAConfig = $mongoObjectFactory->createAndAdd($typeW, (string) $returnW, $typeM, $data);
        
        // > link menu Customer to view Customer
        $object = $mongoObjectFactory->findObject("Menu", (string) $returnMAConfig);
        $reference = array();
        $reference['$ref'] = "views";
        $reference['$id'] = (string) $returnVAConfigPA;
        $object->addReferenceObject("views", $reference);
        // < link menu Cutomer to view Customer
        $workspaceD->createField($returnVAConfigPA, "Profileattribute", \Application\Document\Field::TYPE_FORM, "", "form", "formTiltlr", true, "", '{"maxWidth":600, "maxHeight":200}');
        $workspaceD->createField($returnVAConfigPA, "Profileattribute", \Application\Document\Field::TYPE_BUTTON, "", "save", "Save", false, "saveObject", "");
        $workspaceD->createField($returnVAConfigPA, "Profileattribute", \Application\Document\Field::TYPE_TEXT, "", "name", "Attribute^style='width: 200px; height: 26px'", false, "", "");
        $workspaceD->createField($returnVAConfigPA, "Profileattribute", \Application\Document\Field::TYPE_TEXT, "", "value", "Value^style='width: 200px; height: 26px'", false, "", "");
        
        // $workspaceD->createField($returnVAConfigPA, "Profileattribute", \Application\Document\Field::TYPE_REF_VALUE, "", "susers", "Owners^style='width: 200px; height: 26px'", false, "", "getMethodResultListReference?objectType=@type@&methodName=getSuser&id=@objectId@&action=@action@");
        
        /**
         * *******************************************************************************
         * END create view ADMIN PROFILE ATTRIBUTES
         * *******************************************************************************
         */
        
        /**
         * *******************************************************************************
         * Start create view ADMIN MODEL PROFILE
         * *******************************************************************************
         */
        
        $data = array(
            "title" => "Model Profile",
            "name" => "/listOwningReference/Suser.getModelprofile.getProfile",
            "parentType" => "Workspace",
            "parentId" => (string) $returnW,
            "scope" => "user"
        );
        
        $typeV = 'View';
        $returnVAConfigMP = $mongoObjectFactory->createAndAdd($typeW, (string) $returnW, $typeV, $data);
        
        // > get the grid component
        $data = array(
            "name" => "grid"
        );
        $returnComponentGridUser = $mongoObjectFactory->createAndAdd($typeV, (string) $returnVAConfigMP, $typeComp, $data);

        $data = array(
            "name" => "gridRef"
        );
        $returnComponentGridModelProfile = $mongoObjectFactory->createAndAdd($typeV, (string) $returnVAConfigMP, $typeComp, $data);

        $data = array(
            "name" => "gridRef2"
        );
        $returnComponentGridProfile = $mongoObjectFactory->createAndAdd($typeV, (string) $returnVArchive, $typeComp, $data);


        // > create the menu add
        $data = array(
            "name" => "add2",
            "actionExecution" => "",
            "link" => "Add",
            "type" => "list",
            "parentType" => "Workspace",
            "objectType" => "Modelprofile",
            "method" => "add",
            "icon" => "w2ui-icon-plus"
        );
        $returnCompmenu = $mongoObjectFactory->createAndAdd($typeComp, (string) $returnComponentGridUser, $typeContextMenu, $data);
        // < create the menu add


        // > create the menu delete
        $data = array(
            "name" => "delete2",
            "actionExecution" => "",
            "link" => "Delete",
            "type" => "list",
            "parentType" => "Workspace",
            "objectType" => "Modelprofile",
            "method" => "delete",
            "icon" => "w2ui-icon-minus"
        );
        $returnCompmenu = $mongoObjectFactory->createAndAdd($typeComp, (string) $returnComponentGridUser, $typeContextMenu, $data);
        // < create the menu delete

        // > create menu Customer
        $data = array(
            "id" => "MPMenuID",
            "text" => "Modelprofile",
            "type" => "radio",
            "click" => "buttonClickHandler",
            "icon" => "fa fa-building-o",
            "scope" => 'user',
            "group" => 1
        );
        $typeM = 'Menu';
        $returnMAConfig = $mongoObjectFactory->createAndAdd($typeW, (string) $returnW, $typeM, $data);
        
        // > link menu Customer to view Customer
        $object = $mongoObjectFactory->findObject("Menu", (string) $returnMAConfig);
        $reference = array();
        $reference['$ref'] = "views";
        $reference['$id'] = (string) $returnVAConfigMP;
        $object->addReferenceObject("views", $reference);
        // < link menu Cutomer to view Customer

        $workspaceD->createField($returnVAConfigMP, "Profileattribute", \Application\Document\Field::TYPE_FORM, "", "form", "formTiltlr", true, "", '{"maxWidth":500, "maxHeight":200}');
        $workspaceD->createField($returnVAConfigMP, "Modelprofile", \Application\Document\Field::TYPE_BUTTON, "", "save", "Save", false, "saveObject", "");
        $workspaceD->createField($returnVAConfigMP, "Modelprofile", \Application\Document\Field::TYPE_TEXT, "", "name", "Template profile name^style='width: 200px; height: 26px'", false, "", "");
        // $workspaceD->createField($returnVAConfigMP, "Modelprofile", \Application\Document\Field::TYPE_REF_VALUE, "", "profiles", "Types^style='width: 200px; height: 26px'", false, "", "getMethodResultListReference?objectType=@type@&methodName=getProfile&id=@objectId@&action=@action@");

        $workspaceD->createField($returnVAConfigMP, "Profileattribute", \Application\Document\Field::TYPE_FORM, "", "form", "formTiltlr", true, "", '{"maxWidth":500, "maxHeight":200}');
        $workspaceD->createField($returnVAConfigMP, "Profile", \Application\Document\Field::TYPE_BUTTON, "", "save", "Save", false, "saveObject", "");
        $workspaceD->createField($returnVAConfigMP, "Profile", \Application\Document\Field::TYPE_TEXT, "", "name", "Attribute^style='width: 200px; height: 26px'", false, "", "");
        $workspaceD->createField($returnVAConfigMP, "Profile", \Application\Document\Field::TYPE_TEXT, "", "value", "Value^style='width: 200px; height: 26px'", false, "", "");
        
        // $workspaceD->createField($returnVAConfigMP, "Profile", \Application\Document\Field::TYPE_REF_VALUE, "", "profiles", "Types^style='width: 200px; height: 26px'", false, "", "getMethodResultListReference?objectType=@type@&methodName=getModelprofile&id=@objectId@&action=@action@");
        
        /**
         * *******************************************************************************
         * END create view ADMIN MODEL PROFILE
         * *******************************************************************************
         */
        

         /**
         * *******************************************************************************
         * Start create view ADMIN Requests
         * *******************************************************************************
         */
        
        $data = array(
            "title" => "RequestKeyToTranslate",
            "name" => "/listReference/Suser.getRequest",
            "parentType" => "Workspace",
            "parentId" => (string) $returnW,
            "scope" => "user"
        );
        
        $typeV = 'View';
        $returnVAConfigRequest = $mongoObjectFactory->createAndAdd($typeW, (string) $returnW, $typeV, $data);
        
        // > get the grid component
        $data = array(
            "name" => "grid"
        );
        $returnComponentGridCustomer = $mongoObjectFactory->createAndAdd($typeV, (string) $returnVAConfigRequest, $typeComp, $data);
        
        // > create menu Customer
        $data = array(
            "id" => "RequestMenuID",
            "text" => "Requests",
            "type" => "radio",
            "click" => "buttonClickHandler",
            "icon" => "fa fa-building-o",
            "scope" => 'user',
            "group" => 1
        );
        $typeM = 'Menu';
        $returnMAConfig = $mongoObjectFactory->createAndAdd($typeW, (string) $returnW, $typeM, $data);
        
        // > link menu Customer to view Customer
        $object = $mongoObjectFactory->findObject("Menu", (string) $returnMAConfig);
        $reference = array();
        $reference['$ref'] = "views";
        $reference['$id'] = (string) $returnVAConfigRequest;
        $object->addReferenceObject("views", $reference);
        // < link menu Cutomer to view Customer
        
        // $workspaceD->createField($returnVAConfigRequest, "Request", \Application\Document\Field::TYPE_BUTTON, "", "save", "Save", false, "saveObject", "");
        // $workspaceD->createField($returnVAConfigRequest, "Request", \Application\Document\Field::TYPE_TEXT, "", "topic", "Topic", false, "", "");
        // $workspaceD->createField($returnVAConfigRequest, "Request", \Application\Document\Field::TYPE_TEXT, "", "tags", "Tags", false, "", "");
        // $workspaceD->createField($returnVAConfigRequest, "Request", \Application\Document\Field::TYPE_REF_VALUE, "", "susers", "Owners^style='width: 200px; height: 26px'", false, "", "getMethodResultListReference?objectType=@type@&methodName=getSuser&id=@objectId@&action=@action@");
        
        /**
         * *******************************************************************************
         * END create view ADMIN Requests
         * *******************************************************************************
         */


        /**
         * *******************************************************************************
         * Start create view PROFILE ATTRIBUTES
         * *******************************************************************************
         */
        
        $data = array(
            "title" => "MyProfilesView",
            "name" => "/canvas",
            "parentType" => "Workspace",
            "parentId" => (string) $returnW
        );
        $typeV = 'View';
        $typeComp = 'Component';
          
        $returnVProfileattribute = $mongoObjectFactory->createAndAdd($typeW, (string) $returnW, $typeV, $data);
        $data = array(
            "name" => "schedulerevents"
        );
        $returnCompA = $mongoObjectFactory->createAndAdd($typeV, (string) $returnVProfileattribute, $typeComp, $data);
        echo "Group 1\n";
        $data = array(
            "name" => "listReference",
            "definition" => "Profileattribute",
            "type" => array(
                $json_type
            ),
            "referencelink" => 'getSuser[email-@username].getProfileattribute',
            "schema" => ""
        );
        $returnPar7 = $mongoObjectFactory->createAndAdd($typeComp, (string) $returnCompA, $typePar, $data);
      
        
        // > get the grid component
        $data = array(
            "name" => "grid"
        );
        $returnComponentGridProfileattribute = $mongoObjectFactory->createAndAdd($typeV, (string) $returnVProfileattribute, $typeComp, $data);

        // > add fields
        $workspaceD->createField($returnVProfileattribute, "Profileattribute", \Application\Document\Field::TYPE_FORM, "", "form", "formTiltlr", true, "", '{"maxWidth":500, "maxHeight":200}');
        $workspaceD->createField($returnVProfileattribute, "Profileattribute", \Application\Document\Field::TYPE_BUTTON, "", "save", "Save", false, "saveObject", "");
        $workspaceD->createField($returnVProfileattribute, "Profileattribute", \Application\Document\Field::TYPE_TEXT, "", "name", "Personal data property^style='width: 200px; height: 26px'", false, "", "");
        $workspaceD->createField($returnVProfileattribute, "Profileattribute", \Application\Document\Field::TYPE_TEXT, "", "value", "Personal data value^style='width: 200px; height: 26px'", false, "", "");

        // > create the menu add
        $data = array(
            "name" => "add3",
            "actionExecution" => "",
            "link" => "Add personal information",
            "type" => "list",
            "parentType" => "Workspace",
            "objectType" => "Profileattribute",
            "method" => "add",
            "icon" => "w2ui-icon-plus"
        );
        $returnCompmenu = $mongoObjectFactory->createAndAdd($typeComp, (string) $returnComponentGridProfileattribute, $typeContextMenu, $data);
        // < create the menu add


        // > create the menu delete
        $data = array(
            "name" => "delete3",
            "actionExecution" => "",
            "link" => "Delete personal information",
            "type" => "list",
            "parentType" => "Workspace",
            "objectType" => "Profileattribute",
            "method" => "delete",
            "icon" => "w2ui-icon-minus"
        );
        $returnCompmenu = $mongoObjectFactory->createAndAdd($typeComp, (string) $returnComponentGridProfileattribute, $typeContextMenu, $data);
        // < create the menu delete

        /*

         $data = array(
             "title" => "MyProfilesView",
             "name" => "/canvas",
             "parentType" => "Workspace",
             "parentId" => (string) $returnW
         );

         $typeV = 'View';
         $returnVPlanning = $mongoObjectFactory->createAndAdd($typeW, (string) $returnW, $typeV, $data);

         //
         $typeComp = 'Component';
         $data = array(
             "name" => "schedulerevents"
         );
         $returnCompA = $mongoObjectFactory->createAndAdd($typeV, (string) $returnVPlanning, $typeComp, $data);
         echo "Group 1\n";

         $data = array(
             "name" => "listReference",
             "definition" => "Profileattribute",
             "type" => array(
                 $json_type
             ),
            "referencelink" => 'getSuser[email-@username].getProfileattribute',
            "schema" => "getModelprofile+getProfile"
         );
         $returnPar7 = $mongoObjectFactory->createAndAdd($typeComp, (string) $returnCompA, $typePar, $data);


         // > get the Route component
         $data = array(
             "name" => "grid"
         );
         $mainGrid = $mongoObjectFactory->createAndAdd($typeV, (string) $returnVPlanning, $typeComp, $data);

         // > get the Route component
         $data = array(
             "name" => "gridRef"
         );
         $returnRouteComponent = $mongoObjectFactory->createAndAdd($typeV, (string) $returnVPlanning, $typeComp, $data);
         // < get the Route component

         */

        // < menu
        $typeM = 'Menu';
        $data = array(
            "id" => "ExternProfileiew",
            "text" => "Default profile",
            "type" => "radio",
            "icon" => "fa fa-calendar",
            "click" => "buttonClickHandler",
            "scope" => 'extern',
            "group" => "1",
            "platform" => 'browser',
            "default" => "true"
        );
        $returnM1 = $mongoObjectFactory->createAndAdd($typeW, (string) $returnW, $typeM, $data);
        // < menu
        
        echo "profiles user 7\n";
        // > link view to menu
        $object = $mongoObjectFactory->findObject("Menu", (string) $returnM1);
        $reference = array();
        $reference['$ref'] = "views";
        $reference['$id'] = (string) $returnVProfileattribute;
        $object->addReferenceObject("views", $reference);
        $object = $mongoObjectFactory->findObject("Menu", (string) $returnM1);
        
        // $workspaceD->createField($returnVAConfigPA, "Profileattribute", \Application\Document\Field::TYPE_REF_VALUE, "", "susers", "Owners^style='width: 200px; height: 26px'", false, "", "getMethodResultListReference?objectType=@type@&methodName=getSuser&id=@objectId@&action=@action@");
        
        /**
         * *******************************************************************************
         * END create view PROFILE ATTRIBUTES
         * *******************************************************************************
         */










        /**
         * *******************************************************************************
         * Start create view PROFILE TEMPLATES
         * *******************************************************************************
         */

        echo "PROFILE TEMPLATES\n";
        
        $data = array(
            "title" => "ProfilesView",
            "name" => "/canvas",
            "parentType" => "Workspace",
            "parentId" => (string) $returnW
        );
        
        $typeV = 'View';
        $returnVPlanning = $mongoObjectFactory->createAndAdd($typeW, (string) $returnW, $typeV, $data);
        // > add fields
        $workspaceD->createField($returnVPlanning, "Modelprofile", \Application\Document\Field::TYPE_FORM, "", "form", "formTiltlr", true, "", '{"maxWidth":500, "maxHeight":200}');
        $workspaceD->createField($returnVPlanning, "Modelprofile", \Application\Document\Field::TYPE_BUTTON, "", "save", "Save", false, "saveObject", "");
        $workspaceD->createField($returnVPlanning, "Modelprofile", \Application\Document\Field::TYPE_TEXT, "", "name", "Template name^style='width: 200px; height: 26px'", false, "", "");
        // $workspaceD->createField($returnVAConfigMP, "Modelprofile", \Application\Document\Field::TYPE_REF_VALUE, "", "profiles", "Types^style='width: 200px; height: 26px'", false, "", "getMethodResultListReference?objectType=@type@&methodName=getProfile&id=@objectId@&action=@action@");

        $workspaceD->createField($returnVPlanning, "Profile", \Application\Document\Field::TYPE_FORM, "", "form", "formTiltlr", true, "", '{"maxWidth":500, "maxHeight":200}');
        $workspaceD->createField($returnVPlanning, "Profile", \Application\Document\Field::TYPE_BUTTON, "", "save", "Save", false, "saveObject", "");
        $workspaceD->createField($returnVPlanning, "Profile", \Application\Document\Field::TYPE_TEXT, "", "name", "Attribute^style='width: 200px; height: 26px'", false, "", "");
        $workspaceD->createField($returnVPlanning, "Profile", \Application\Document\Field::TYPE_TEXT, "", "value", "Value^style='width: 200px; height: 26px'", false, "", "");
        // > get the Route component
        $data = array(
            "name" => "grid"
        );
        $mainGrid = $mongoObjectFactory->createAndAdd($typeV, (string) $returnVPlanning, $typeComp, $data);
    
    
        // > create the menu add
        $data = array(
            "name" => "add4",
            "actionExecution" => "",
            "link" => "Add",
            "type" => "list",
            "parentType" => "Workspace",
            "objectType" => "Profileattribute",
            "method" => "add",
            "icon" => "w2ui-icon-plus"
        );
        $returnCompmenu = $mongoObjectFactory->createAndAdd($typeComp, (string) $mainGrid, $typeContextMenu, $data);
        // < create the menu add

     
        
        // > create the menu delete
        $data = array(
            "name" => "delete4",
            "actionExecution" => "",
            "link" => "Delete",
            "type" => "list",
            "parentType" => "Workspace",
            "objectType" => "Profileattribute",
            "method" => "delete",
            "icon" => "w2ui-icon-minus"
        );
        $returnCompmenu = $mongoObjectFactory->createAndAdd($typeComp, (string) $mainGrid, $typeContextMenu, $data);
        // < create the menu delete
        //
        $typeComp = 'Component';
        $data = array(
            "name" => "schedulerevents"
        );
        $returnCompA = $mongoObjectFactory->createAndAdd($typeV, (string) $returnVPlanning, $typeComp, $data);
        
        
        $data = array(
            "name" => "listReference",
            "definition" => "Modelprofile",
            "type" => array(
                $json_type
            ),
            "referencelink" => 'getSuser[email-@username].getModelprofile',
            "schema" => "getProfile+getRequest"
        );
        $returnPar7 = $mongoObjectFactory->createAndAdd($typeComp, (string) $returnCompA, $typePar, $data);
        
         
        $typeContextMenu = 'Contextmenu';
        // > Context Menu for transport dispatch
        $data = array(
            "name" => "publishdata",
            "actionExecution" => "method",
            "actionResponse" => 'refreshAll();',
            "link" => "validate",
            "parentType" => "Suser",
            "objectType" => "Modelprofile",
            "method" => 'validate();',
            "serviceName" => "",
            "serviceMethod" => "",
            "template" => ""
        );
        $return = $mongoObjectFactory->createAndAdd($typeComponent, (string) $mainGrid, $typeContextmenu, $data);
        
        // > get the Route component

        // < menu
        $typeM = 'Menu';
        $data = array(
            "id" => "ExternView",
            "text" => "Profile templates",
            "type" => "radio",
            "icon" => "fa fa-calendar",
            "click" => "buttonClickHandler",
            "scope" => 'extern',
            "group" => "1",
            "platform" => 'browser',
            "default" => "true"
        );
        $returnM1 = $mongoObjectFactory->createAndAdd($typeW, (string) $returnW, $typeM, $data);
        // < menu
        

        // > link view to menu
        $object = $mongoObjectFactory->findObject("Menu", (string) $returnM1);
        $reference = array();
        $reference['$ref'] = "views";
        $reference['$id'] = (string) $returnVPlanning;
        $object->addReferenceObject("views", $reference);
        $object = $mongoObjectFactory->findObject("Menu", (string) $returnM1);
        /**
         * *******************************************************************************
         * END create view PROFILE TEMPLATES
         * *******************************************************************************
         */





        
        /**
         * *******************************************************************************
         * Start create view MY REQUESTS
         * *******************************************************************************
         */
        echo "MY REQUESTS\n";
        
        $data = array(
            "title" => "MyRequestiew",
            "name" => "/canvas",
            "parentType" => "Workspace",
            "parentId" => (string) $returnW
        );
        
        $typeV = 'View';
        $returnVPlanning = $mongoObjectFactory->createAndAdd($typeW, (string) $returnW, $typeV, $data);
        // > add fields
         
        //
        $typeComp = 'Component';
        $data = array(
            "name" => "schedulerevents"
        );
        $returnCompA = $mongoObjectFactory->createAndAdd($typeV, (string) $returnVPlanning, $typeComp, $data);

        
        $data = array(
            "name" => "listReference",
            "definition" => "Request",
            "type" => array(
                $json_type
            ),
            "referencelink" => 'getSuser[email-@username].getRequest',
            "schema" => "getModelprofile+getProfile[isvisible-true]"
        );
        $returnPar7 = $mongoObjectFactory->createAndAdd($typeComp, (string) $returnCompA, $typePar, $data);
        
        // > get the Route component
        $data = array(
            "name" => "grid"
        );
        $mainGrid = $mongoObjectFactory->createAndAdd($typeV, (string) $returnVPlanning, $typeComp, $data);
        
        // > get the Route component
        $data = array(
            "name" => "gridRef"
        );
        $returnRouteComponent = $mongoObjectFactory->createAndAdd($typeV, (string) $returnVPlanning, $typeComp, $data);
        // < get the Route component
        $typeContextMenu = 'Contextmenu';
        // > Context Menu for transport dispatch
        $data = array(
            "name" => "acchdata",
            "actionExecution" => "method",
            "actionResponse" => 'showMessage("%name%");refreshAll();',
            "link" => "Accept request",
            "parentType" => "Suser",
            "objectType" => "Request",
            "method" => 'accept();',
            "serviceName" => "",
            "serviceMethod" => "",
            "template" => ""
        );
        $return = $mongoObjectFactory->createAndAdd($typeComponent, (string) $mainGrid, $typeContextmenu, $data);
        
        $data = array(
            "name" => "refhdata",
            "actionExecution" => "method",
            "actionResponse" => 'showMessage("%name%");refreshAll();',
            "link" => "Reject request",
            "parentType" => "Suser",
            "objectType" => "Request",
            "method" => 'refuse();',
            "serviceName" => "",
            "serviceMethod" => "",
            "template" => ""
        );
        $return = $mongoObjectFactory->createAndAdd($typeComponent, (string) $mainGrid, $typeContextmenu, $data);
        
        
        $data = array(
            "name" => "groupscmaa3",
            "actionExecution" => "inputform",
            "viewId" => "viewId",
            "link" => "Join this group",
            // "type" => "list",
            "parentType" => "Workspace",
            "objectType" => "Request",
            "method" => "importDialogGroups5",
            "requeststatus" => "",
            "icon" => "w2ui-icon-plus"
        );
        $return = $mongoObjectFactory->createAndAdd($typeComponent, (string) $mainGrid, $typeContextmenu, $data);
        // > create the dialog with the corresponding fields
        $workspaceD->createField($returnVPlanning, "importDialogGroups5", \Application\Document\Field::TYPE_FORM, "", "form", "formTiltlr", true, "", '{"maxWidth":600, "maxHeight":200}');
        $workspaceD->createField($returnVPlanning, "importDialogGroups5", \Application\Document\Field::TYPE_BUTTON, "", "save", "Accept Invite", true, "method", "", "acceptInviteForGroup(@modelprof@);", "", "showMessage(\"@name@\");");
        $workspaceD->createField($returnVPlanning, "importDialogGroups5", \Application\Document\Field::TYPE_REF_VALUE, "", "modelprof", "Model profile^style='width: 200px; height: 26px'", true, "", "getMethodResultListReference?objectType=@type@&methodName=getParent.getParent.getSuser[email-@username].getModelprofile&id=@objectId@&action=@action@");
        
        // < menu
        $typeM = 'Menu';
        $data = array(
            "id" => "MyReqView",
            "text" => "My TODOs",
            "type" => "radio",
            "icon" => "fa fa-calendar",
            "click" => "buttonClickHandler",
            "scope" => 'extern',
            "group" => "1",
            "platform" => 'browser',
            "default" => "true"
        );
        $returnM1 = $mongoObjectFactory->createAndAdd($typeW, (string) $returnW, $typeM, $data);
        // < menu
        

        // > link view to menu
        $object = $mongoObjectFactory->findObject("Menu", (string) $returnM1);
        $reference = array();
        $reference['$ref'] = "views";
        $reference['$id'] = (string) $returnVPlanning;
        $object->addReferenceObject("views", $reference);
        $object = $mongoObjectFactory->findObject("Menu", (string) $returnM1);
        
        // $workspaceD->createField($returnVAConfigPA, "Profileattribute", \Application\Document\Field::TYPE_REF_VALUE, "", "susers", "Owners^style='width: 200px; height: 26px'", false, "", "getMethodResultListReference?objectType=@type@&methodName=getSuser&id=@objectId@&action=@action@");
        
        /**
         * *******************************************************************************
         * END create view MY REQUESTS
         * *******************************************************************************
         */






        /**
         * *******************************************************************************
         * Start create view Open Groups
         * *******************************************************************************
         */

        echo "MY GROUPS\n";
        $typeContextMenuGroups = 'Contextmenu'; // si aici sunt mi multe tipuri. ex: acitonExecution method | actionResponse
        $data = array(
            "title" => "GroupUsersView",
            "name" => "/list/Group",
            "parentType" => "Workspace",
            "parentId" => (string) $returnW,
            "scope" => "user"
        );

        $typeV = 'View';
        
        $returnVAConfigGroup = $mongoObjectFactory->createAndAdd($typeW, (string) $returnW, $typeV, $data);
        
        
        // > get the grid component
        $data = array(
            "name" => "grid"
        );
        $returnComponentGridCustomer = $mongoObjectFactory->createAndAdd($typeV, (string) $returnVAConfigGroup, $typeComp, $data);
        
        // > create the filter for the logged in customer
        $filter_type = array(
            "id" => "127",
            "text" => "filter"
        );
        $typeParameter = 'Parameter';
        $data = array(
            "name" => "groupspublic",
            "type" => array(
                $filter_type
            ),
            "referencelink" => "Group",
            "definition" => "publicgroup-true"
        );
        $return1 = $mongoObjectFactory->createAndAdd($typeComp, (string) $returnComponentGridCustomer, $typeParameter, $data);
        
        
        $workspaceD->createField($returnVAConfigGroup, "Group", \Application\Document\Field::TYPE_FORM, "", "form", "formTiltlr", true, "", '{"maxWidth":600, "maxHeight":300}');
        $workspaceD->createField($returnVAConfigGroup, "Group", \Application\Document\Field::TYPE_TEXT, "", "name", "Group name^style='width: 200px; height: 26px'", false, "", "");
        $workspaceD->createField($returnVAConfigGroup, "Group", \Application\Document\Field::TYPE_ENUM, "GROUPTYPE.type", "grouptype", "Group Type^style='width: 200px; height: 26px'", true, "", "");
        $workspaceD->createField($returnVAConfigGroup, "Group", \Application\Document\Field::TYPE_TEXT, "", "topic", "Group Topic^style='width: 200px; height: 26px'", false, "", "");
        $workspaceD->createField($returnVAConfigGroup, "Group", \Application\Document\Field::TYPE_TEXT, "", "tags", "Group Tags^style='width: 200px; height: 26px'", false, "", "");
        $workspaceD->createField($returnVAConfigGroup, "Group", \Application\Document\Field::TYPE_REFERENCE, "", "modelprofiles", "Profile Template^style='width: 200px; height: 26px'", false, "", "getMethodResultListReference?objectType=@type@&param=name&methodName=getSuser[email-@username].getModelprofile&id=@objectId@&action=@action@");

        // $workspaceD->createField($returnVAConfigGroup, "Group", \Application\Document\Field::TYPE_TEXT, "", "owner", "Owner email", false, "", "");
          
        // > create the menu add
        $data = array(
            "name" => "expgr6",
            "actionExecution" => "",
            "link" => "Export",
            "type" => "list",
            "parentType" => "Workspace",
            "objectType" => "Profileattribute",
            "method" => "export",
            "icon" => "w2ui-icon-plus"
        );
        $returnCompmenu = $mongoObjectFactory->createAndAdd($typeComp, (string) $returnComponentGridCustomer, $typeContextMenu, $data);

        // remove context menu 'Edit Item'

        $data = array(
            "name" => "removeEditItemCommunityGroup",
            "actionExecution" => "",
            "link" => "Remove",
            "type" => "noedit"
        );
        $returnCompmenu = $mongoObjectFactory->createAndAdd($typeComp, (string) $returnComponentGridCustomer, $typeContextMenu, $data);


        // > create menu Customer
        $data = array(
            "id" => "GrouUsersMenuID",
            "text" => "Open Groups",
            "type" => "radio",
            "click" => "buttonClickHandler",
            "icon" => "fa fa-building-o",
            "scope" => 'extern',
            "platform" => 'browser',
            "group" => 1
        );
        $typeM = 'Menu';
        $returnMAConfig = $mongoObjectFactory->createAndAdd($typeW, (string) $returnW, $typeM, $data);

        // > link menu Customer to view Customer
        $object = $mongoObjectFactory->findObject("Menu", (string) $returnMAConfig);
        $reference = array();
        $reference['$ref'] = "views";
        $reference['$id'] = (string) $returnVAConfigGroup;
        $object->addReferenceObject("views", $reference);
        // < link menu Cutomer to view Customer

        // $workspaceD->createField($returnVAConfigGroup, "Group", \Application\Document\Field::TYPE_BUTTON, "", "save", "Save", false, "saveObject", "");
        // $workspaceD->createField($returnVAConfigGroup, "Group", \Application\Document\Field::TYPE_TEXT, "", "topic", "Topic", false, "", "");
        // $workspaceD->createField($returnVAConfigGroup, "Group", \Application\Document\Field::TYPE_TEXT, "", "tags", "Tags", false, "", "");
        // $workspaceD->createField($returnVAConfigGroup, "Group", \Application\Document\Field::TYPE_TEXT, "", "owner", "Owner email", false, "", "");

        // $workspaceD->createField($returnVAConfigGroup, "Group", \Application\Document\Field::TYPE_REF_VALUE, "", "susers", "Owners^style='width: 200px; height: 26px'", false, "", "getMethodResultListReference?objectType=@type@&methodName=getSuser&id=@objectId@&action=@action@");

        $typeContextMenuGroups = 'Contextmenu'; // si aici sunt mi multe tipuri. ex: acitonExecution method | actionResponse

        $data = array(
            "name" => "groupscm3",
            "actionExecution" => "inputform",
            "viewId" => "viewId",
            "link" => "Send a join request to the owner of this group",
            // "type" => "list",
            "parentType" => "Workspace",
            "objectType" => "Group",
            "method" => "importDialogGroups2",
            "icon" => "w2ui-icon-plus"
        );
        $return = $mongoObjectFactory->createAndAdd($typeComp, (string) $returnComponentGridCustomer, $typeContextMenuGroups, $data);

        // > create the dialog with the corresponding fields
        $workspaceD->createField($returnVAConfigGroup, "importDialogGroups2", \Application\Document\Field::TYPE_FORM, "", "form", "formTiltlr", true, "", '{"maxWidth":600, "maxHeight":200}');
        $workspaceD->createField($returnVAConfigGroup, "importDialogGroups2", \Application\Document\Field::TYPE_BUTTON, "", "save", "Send request", true, "method", "", "makeARequestForGroup(@modelprof@, @message@);", "", "showMessage(\"@name@\");");
        $workspaceD->createField($returnVAConfigGroup, "importDialogGroups2", \Application\Document\Field::TYPE_REF_VALUE, "", "modelprof", "Model profile^style='width: 200px; height: 26px'", true, "", "getMethodResultListReference?objectType=@type@&param=name&methodName=getParent.getSuser[email-@username].getModelprofile&id=@objectId@&action=@action@");
        $workspaceD->createField($returnVAConfigGroup, "importDialogGroups2", \Application\Document\Field::TYPE_TEXTAREA, "", "message", "Message^style='width: 200px; height: 26px'", false, "", "");

        /**
         * *******************************************************************************
         * END create view Open Groups
         * *******************************************************************************
         */






        /**
         * *******************************************************************************
         * Start create view MY GROUPS
         * *******************************************************************************
         */
        echo "MY GROUPS\n";

        $data = array(
            "title" => "MyGroupView",
            "name" => "/canvas",
            "parentType" => "Workspace",
            "parentId" => (string) $returnW
        );

        $typeV = 'View';
        $returnVPlanning = $mongoObjectFactory->createAndAdd($typeW, (string) $returnW, $typeV, $data);
        // > add fields

        //
        $typeComp = 'Component';
        $data = array(
            "name" => "schedulerevents"
        );
        $returnCompA = $mongoObjectFactory->createAndAdd($typeV, (string) $returnVPlanning, $typeComp, $data);


        $data = array(
            "name" => "listReference",
            "definition" => "Group",
            "type" => array(
                $json_type
            ),
            "referencelink" => 'getSuser[email-@username].getGroup',
            //getIgrole&getIgperson+getIgprofile+getIgprofile
            "schema" => "getGroupmodelprofile[owner-@username]+getProfile"
        );
        $returnPar7 = $mongoObjectFactory->createAndAdd($typeComp, (string) $returnCompA, $typePar, $data);

        // > get the Route component
        $data = array(
            "name" => "grid"
        );
        $returnGrid = $mongoObjectFactory->createAndAdd($typeV, (string) $returnVPlanning, $typeComp, $data);

        // > get the Route component
        $data = array(
            "name" => "gridRef"
        );
        $returnGridRef = $mongoObjectFactory->createAndAdd($typeV, (string) $returnVPlanning, $typeComp, $data);
        // < get the Route component

        // > get grid2 component
        $data = array(
            "name" => "gridRef2"
        );
        $returnGridRef2 = $mongoObjectFactory->createAndAdd($typeV, (string) $returnVPlanning, $typeComp, $data);
        // < get grid2 component
        
        $workspaceD->createField($returnVPlanning, "Group", \Application\Document\Field::TYPE_FORM, "", "form", "formTiltlr", true, "", '{"maxWidth":600, "maxHeight":300}');
        $workspaceD->createField($returnVPlanning, "Group", \Application\Document\Field::TYPE_BUTTON, "", "save", "Save", false, "saveObject", "");
        $workspaceD->createField($returnVPlanning, "Group", \Application\Document\Field::TYPE_TEXT, "", "name", "Group name^style='width: 200px; height: 26px'", true, "", "");
        $workspaceD->createField($returnVPlanning, "Group", \Application\Document\Field::TYPE_ENUM, "GROUPTYPE.type", "grouptype", "Type^style='width: 200px; height: 26px'", true, "", "");
        $workspaceD->createField($returnVPlanning, "Group", \Application\Document\Field::TYPE_TEXT, "", "topic", "Topic^style='width: 200px; height: 26px'", true, "", "");
        $workspaceD->createField($returnVPlanning, "Group", \Application\Document\Field::TYPE_TEXT, "", "tags", "Tags^style='width: 200px; height: 26px'", true, "", "");
        $workspaceD->createField($returnVPlanning, "Group", \Application\Document\Field::TYPE_REFERENCE, "", "modelprofiles", "Profile Template^style='width: 200px; height: 26px'", false, "", "getMethodResultListReference?objectType=@type@&param=name&methodName=getSuser[email-@username].getModelprofile&id=@objectId@&action=@action@");

        // $workspaceD->createField($returnVAConfigGroup, "Group", \Application\Document\Field::TYPE_TEXT, "", "owner", "Owner email", false, "", "");


        $workspaceD->createField($returnVPlanning, "Groupmodelprofile", \Application\Document\Field::TYPE_BUTTON, "", "save", "Save", false, "saveObject", "");
        $workspaceD->createField($returnVPlanning, "Groupmodelprofile", \Application\Document\Field::TYPE_TEXT, "", "name", "Name^style='width: 200px; height: 26px'", true, "", "");

        $workspaceD->createField($returnVPlanning, "Profile", \Application\Document\Field::TYPE_FORM, "", "form", "formTiltlr", true, "", '{"maxWidth":500, "maxHeight":200}');
        $workspaceD->createField($returnVPlanning, "Profile", \Application\Document\Field::TYPE_BUTTON, "", "save", "Save", false, "saveObject", "");
        $workspaceD->createField($returnVPlanning, "Profile", \Application\Document\Field::TYPE_TEXT, "", "name", "Attribute^style='width: 200px; height: 26px'", false, "", "");
        $workspaceD->createField($returnVPlanning, "Profile", \Application\Document\Field::TYPE_TEXT, "", "value", "Value^style='width: 200px; height: 26px'", false, "", "");
        // > get the Route component
        
        // > create the menu add
        $data = array(
            "name" => "addmygroup1",
            "actionExecution" => "",
            "link" => "Add",
            "type" => "list",
            "parentType" => "Workspace",
            "objectType" => "Group",
            "method" => "add",
            "icon" => "w2ui-icon-plus"
        );
        $returnCompmenu = $mongoObjectFactory->createAndAdd($typeComp, (string) $returnGrid, $typeContextMenu, $data);

        $data = array(
            "name" => "removeEditItemMyGroupMainGrid",
            "actionExecution" => "",
            "link" => "Remove",
            "type" => "noedit"
        );
        $returnCompmenu = $mongoObjectFactory->createAndAdd($typeComp, (string) $returnGrid, $typeContextMenu, $data);


        // < create the menu add

        // > create the menu add
        $data = array(
            "name" => "exp6",
            "actionExecution" => "",
            "link" => "Export",
            "type" => "list",
            "parentType" => "Workspace",
            "objectType" => "Profileattribute",
            "method" => "export",
            "icon" => "w2ui-icon-plus"
        );
        $returnCompmenu = $mongoObjectFactory->createAndAdd($typeComp, (string) $returnGridRef, $typeContextMenu, $data);
        // < create the menu add


        // > create the menu add
        $data = array(
            "name" => "add6",
            "actionExecution" => "",
            "link" => "Add",
            "type" => "list",
            "parentType" => "Workspace",
            "objectType" => "Profileattribute",
            "method" => "add",
            "icon" => "w2ui-icon-plus"
        );
        $returnCompmenu = $mongoObjectFactory->createAndAdd($typeComp, (string) $returnGridRef2, $typeContextMenu, $data);
        // < create the menu add


        // > create the menu delete
        $data = array(
            "name" => "delete6",
            "actionExecution" => "",
            "link" => "Delete",
            "type" => "list",
            "parentType" => "Workspace",
            "objectType" => "Profileattribute",
            "method" => "delete",
            "icon" => "w2ui-icon-minus"
        );
        $returnCompmenu = $mongoObjectFactory->createAndAdd($typeComp, (string) $returnGridRef2, $typeContextMenu, $data);
        // < create the menu delete
        
        $data = array(
            "name" => "groupsinvite",
            "actionExecution" => "inputform",
            "viewId" => "viewId",
            "link" => "Invite someone to join this group",
            // "type" => "list",
            "parentType" => "Workspace",
            "objectType" => "Group",
            "method" => "importDialogGroups3",
            "icon" => "w2ui-icon-plus"
        );
        $return = $mongoObjectFactory->createAndAdd($typeComp, (string) $returnGrid, $typeContextMenu, $data);

        // < menu
        $typeM = 'Menu';
        $data = array(
            "id" => "MyGroupsView",
            "text" => "My Groups",
            "type" => "radio",
            "icon" => "fa fa-calendar",
            "click" => "buttonClickHandler",
            "scope" => 'extern',
            "group" => "1",
            "platform" => 'browser',
            "default" => "true"
        );
        $returnM1 = $mongoObjectFactory->createAndAdd($typeW, (string) $returnW, $typeM, $data);
        // < menu
        
        // > create the dialog with the corresponding fields
        $workspaceD->createField($returnVPlanning, "importDialogGroups3", \Application\Document\Field::TYPE_FORM, "", "form", "formTiltlr", true, "", '{"maxWidth":600, "maxHeight":200}');
        $workspaceD->createField($returnVPlanning, "importDialogGroups3", \Application\Document\Field::TYPE_BUTTON, "", "save", "Send invite", true, "method", "", "makeAnInvitationForUser(@userid@ , @message@);", "", "showMessage(\"@name@\");");
        $workspaceD->createField($returnVPlanning, "importDialogGroups3", \Application\Document\Field::TYPE_TEXT, "", "userid", "User Id^style='width: 200px; height: 26px'", false, "", "");
        $workspaceD->createField($returnVPlanning, "importDialogGroups3", \Application\Document\Field::TYPE_TEXTAREA, "", "message", "Message^style='width: 200px; height: 26px'", false, "", "");

        // > link view to menu
        $object = $mongoObjectFactory->findObject("Menu", (string) $returnM1);
        $reference = array();
        $reference['$ref'] = "views";
        $reference['$id'] = (string) $returnVPlanning;
        $object->addReferenceObject("views", $reference);
        $object = $mongoObjectFactory->findObject("Menu", (string) $returnM1);

        // $workspaceD->createField($returnVAConfigPA, "Profileattribute", \Application\Document\Field::TYPE_REF_VALUE, "", "susers", "Owners^style='width: 200px; height: 26px'", false, "", "getMethodResultListReference?objectType=@type@&methodName=getSuser&id=@objectId@&action=@action@");

        /**
         * *******************************************************************************
         * END create view MY GROUPS
         * *******************************************************************************
         */




        /**
         * *******************************************************************************
         * Start create view THE GAME
         * *******************************************************************************
         */
        echo "THE GAME\n";

        $data = array(
            "title" => "GameView",
            "name" => "/canvas",
            "parentType" => "Workspace",
            "parentId" => (string) $returnW
        );

        $typeV = 'View';
        $returnVPlanning = $mongoObjectFactory->createAndAdd($typeW, (string) $returnW, $typeV, $data);
        // > add fields

        //
        $typeComp = 'Component';
        $data = array(
            "name" => "schedulerevents"
        );
        $returnCompA = $mongoObjectFactory->createAndAdd($typeV, (string) $returnVPlanning, $typeComp, $data);


        $data = array(
            "name" => "listReference",
            "definition" => "Group",
            "type" => array(
                $json_type
            ),
            "referencelink" => 'getSuser[email-@username].getGroup',
            //getIgrole&getIgperson+getIgprofile+getIgprofile
            "schema" => "getGroupmodelprofile+getProfile"
        );
        $returnPar7 = $mongoObjectFactory->createAndAdd($typeComp, (string) $returnCompA, $typePar, $data);

        // > get the Route component
        $data = array(
            "name" => "grid"
        );
        $mainGrid = $mongoObjectFactory->createAndAdd($typeV, (string) $returnVPlanning, $typeComp, $data);

        // > get the Route component
        $data = array(
            "name" => "gridRef"
        );
        $returnGridRef = $mongoObjectFactory->createAndAdd($typeV, (string) $returnVPlanning, $typeComp, $data);
        // < get the Route component

        // > get grid2 component
        $data = array(
            "name" => "gridRef2"
        );
        $returnGridRef2 = $mongoObjectFactory->createAndAdd($typeV, (string) $returnVPlanning, $typeComp, $data);
        // < get grid2 component

        // > create the menu add
        $data = array(
            "name" => "exp9",
            "actionExecution" => "",
            "link" => "Export",
            "type" => "list",
            "parentType" => "Workspace",
            "objectType" => "Profileattribute",
            "method" => "export",
            "icon" => "w2ui-icon-plus"
        );
        $returnCompmenu = $mongoObjectFactory->createAndAdd($typeComp, (string) $mainGrid, $typeContextMenu, $data);

        // > create the menu add
        $data = array(
            "name" => "exp7",
            "actionExecution" => "",
            "link" => "Export",
            "type" => "list",
            "parentType" => "Workspace",
            "objectType" => "Profileattribute",
            "method" => "export",
            "icon" => "w2ui-icon-plus"
        );
        $returnCompmenu = $mongoObjectFactory->createAndAdd($typeComp, (string) $returnGridRef, $typeContextMenu, $data);
        // < create the menu add


        // > create the menu add
        $data = array(
            "name" => "exp8",
            "actionExecution" => "",
            "link" => "Export",
            "type" => "list",
            "parentType" => "Workspace",
            "objectType" => "Profileattribute",
            "method" => "export",
            "icon" => "w2ui-icon-plus"
        );
        $returnCompmenu = $mongoObjectFactory->createAndAdd($typeComp, (string) $returnGridRef2, $typeContextMenu, $data);
        // < create the menu add

        // remove context menu 'Edit Item'

        $data = array(
            "name" => "removeEditItemMyCommunityMainGrid",
            "actionExecution" => "",
            "link" => "Remove",
            "type" => "noedit"
        );
        $returnCompmenu = $mongoObjectFactory->createAndAdd($typeComp, (string) $mainGrid, $typeContextMenu, $data);

        $data = array(
            "name" => "removeEditItemMyCommunityGridRef",
            "actionExecution" => "",
            "link" => "Remove",
            "type" => "noedit"
        );
        $returnCompmenu = $mongoObjectFactory->createAndAdd($typeComp, (string) $returnGridRef, $typeContextMenu, $data);

        $data = array(
            "name" => "removeEditItemMyCommunityGridRef2",
            "actionExecution" => "",
            "link" => "Remove",
            "type" => "noedit"
        );
        $returnCompmenu = $mongoObjectFactory->createAndAdd($typeComp, (string) $returnGridRef2, $typeContextMenu, $data);


        // < menu
        $typeM = 'Menu';
        $data = array(
            "id" => "GameView",
            "text" => "My community",
            "type" => "radio",
            "icon" => "fa fa-calendar",
            "click" => "buttonClickHandler",
            "scope" => 'extern',
            "group" => "1",
            "platform" => 'browser',
            "default" => "true"
        );
        $returnM1 = $mongoObjectFactory->createAndAdd($typeW, (string) $returnW, $typeM, $data);
        // < menu

        // > link view to menu
        $object = $mongoObjectFactory->findObject("Menu", (string) $returnM1);
        $reference = array();
        $reference['$ref'] = "views";
        $reference['$id'] = (string) $returnVPlanning;
        $object->addReferenceObject("views", $reference);
        $object = $mongoObjectFactory->findObject("Menu", (string) $returnM1);

        // $workspaceD->createField($returnVAConfigPA, "Profileattribute", \Application\Document\Field::TYPE_REF_VALUE, "", "susers", "Owners^style='width: 200px; height: 26px'", false, "", "getMethodResultListReference?objectType=@type@&methodName=getSuser&id=@objectId@&action=@action@");

        /**
         * *******************************************************************************
         * END create view THE GAME
         * *******************************************************************************
         */


        /**
         * ********
         * DATA
         */
        
        $typeConfig = 'Stype';
        
        // type 1
        $data = array(
            "name" => "t1"
        );
        $returnType1 = $mongoObjectFactory->createAndAdd($typeW, (string) $returnW, $typeConfig, $data);
        
        // type 2
        $data = array(
            "name" => "t2"
        );
        $returnType2 = $mongoObjectFactory->createAndAdd($typeW, (string) $returnW, $typeConfig, $data);
        
        $typeConfig = 'Profileattributetemplate';
//        $data = array(
//            "name" => "Name",
//            "prottectedattribute" => true,
//            "value"=>"George"
//        );
//        $returnCustomer1 = $mongoObjectFactory->createAndAdd($typeW, (string) $returnW, $typeConfig, $data);
        $data = array(
            "name" => "Alias",
            "prottectedattribute"=>true,
            "value"=>"Geo"
        );
        $returnCustomer1 = $mongoObjectFactory->createAndAdd($typeW, (string) $returnW, $typeConfig, $data);
//        $data = array(
//            "name" => "Email",
//            "prottectedattribute"=>true,
//            "value"=>"geo@email.com"
//        );
//        $returnCustomer1 = $mongoObjectFactory->createAndAdd($typeW, (string) $returnW, $typeConfig, $data);
        
        /*$typeConfig = 'Group';
        $data = array(
            "name" => "Health",
            "topic" => "Health living",
            "tags" => "blood pressure, fat index",
            "owner" => "p1@samsa.com",
            "grouptype" => array(
                array(
                    "id" => "0",
                    "text" => "public"
                )
            )
        );
        $returnGroup1 = $mongoObjectFactory->createAndAdd($typeW, (string) $returnW, $typeConfig, $data);
        $data = array(
            "name" => "Sports",
            "topic" => "Sport in the neighbourhood",
            "tags" => "tennis, footbal, running",
            "owner" => "p1@samsa.com",
            "grouptype" => array(
                array(
                    "id" => "0",
                    "text" => "public"
                )
            )
        );
        $returnGroup2 = $mongoObjectFactory->createAndAdd($typeW, (string) $returnW, $typeConfig, $data);
        
        $data = array(
            "name" => "Relationships",
            "topic" => "Social network",
            "tags" => "friend, partner, help",
            "owner" => "p1@samsa.com",
            "grouptype" => array(
                array(
                    "id" => "1",
                    "text" => "private"
                )
            )
        );
        $returnGroup2 = $mongoObjectFactory->createAndAdd($typeW, (string) $returnW, $typeConfig, $data);*/
        
        $typeConfig = 'Suser';
        
        // customer 1
        $data = array(
            "name" => "Player1",
            "lastname" => "One",
            // "email" => "admin@external.simulator.com"
            "email" => "p1@samsa.com"
        );
        
        $returnCustomer1 = $mongoObjectFactory->createAndAdd($typeW, (string) $returnW, $typeConfig, $data);
        // $userIn = $mongoObjectFactory->findObjectInstance("Suser", $returnCustomer1, "Simulator" );
        // $userIn->validateUser(false);
        
        // customer 1
        $data = array(
            "name" => "Player2",
            "lastname" => "Two",
            //"email" => "hello@samsa.com"
            "email" => "p2@samsa.com"
        );
        $returnCustomer1 = $mongoObjectFactory->createAndAdd($typeW, (string) $returnW, $typeConfig, $data);
        // $userIn = $mongoObjectFactory->findObjectInstance("Suser", $returnCustomer1, "Simulator" );
        // $userIn->validateUser(false);
        
        return "Fixture run successfully!\n";
    }
}
?>
