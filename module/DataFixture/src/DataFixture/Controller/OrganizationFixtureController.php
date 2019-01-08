<?php

namespace DataFixture\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Console\Request as ConsoleRequest;
use RuntimeException;
use Application\DatabaseConnection\Database;

class ORG{
    const DEF_ORDERTAPP = 0;
    const DEF_ILC = 0;
    const DEF_VECO = 0;
    const DEF_TCS = 0;
    const DEF_NEWAYS = 0;
    const DEF_CBMN = 0;
    const DEF_SUPPORT = 0;
    const DEF_EBC = 0;
    const DEF_ETC = 0;
    const DEF_INGROOVE =0;
    const DEF_PNA = 0;
    const DEF_DEMO_PROCESS = 0;
    const DEF_SANDBOX = 0;
    const DEF_RALLOCATOR = 0;
    const DEF_ILCDEMO = 0;
    const DEF_ELSIG = 0;
    const DEF_SIMULATOR = 1;
    const DEF_AUTOPLAN = 0;
}

class OrganizationFixtureController extends AbstractActionController
{




    public function runAction()
    {
        $_SESSION["transaction_id"] = 5;
        echo "\n";
        $request = $this->getRequest();
        if (! $request instanceof ConsoleRequest) {
            throw new RuntimeException('You can only use this action from a console!');
        }
        $mongoObjectFactory = new \Application\Controller\MongoObjectFactory();

        $m = Database::getInstance();

        $dbmain = $m->{$mongoObjectFactory->getDBName("Organization")};

        // create collections user & user roles
        $users = $dbmain->createCollection("users");
        if (!$users) {
            $users = $dbmain->users;
        }
        $userRoles = $dbmain->createCollection("userRoles");
        if (!$userRoles){
            $userRoles = $dbmain->userRoles;
        };

        $settings = $dbmain->createCollection("settings");
        if (!$settings) {
            $settings = $dbmain->settings;
        }
        $organizations = $dbmain->createCollection("organizations");
        if (!$organizations) {
            $organizations = $dbmain->organizations;
        }

        if ($users) {
            $users->remove();
        }

        if ($userRoles) {
            $userRoles->remove();
        }

        if ($organizations){
            $organizations->remove();
        }
        if ($settings) {
            $settings->remove();
        }

        //insert roles
        $userRoleAdmin = array(
            'role' => 'admin'
        );
        $userRoleUser = array(
            'role' => 'user'
        );
        $userRoleSuperAdmin = array(
            'role' => 'superadmin'
        );

        $userRoles->insert($userRoleSuperAdmin);

        $userRoles->insert($userRoleAdmin);
        $userRoles->insert($userRoleUser);
        echo "Inserted roles \n";

        /**
         * #################################
         * Start Insert a superadmin and Samsa organization
         * #################################
         */
        $_SESSION['organization'] = 'Samsa';
        $organization = array(
            'name' => 'Samsa',
            'dbname' => 'Samsa',
            'organizationDbNumber' => 0,
            'deleted' => 0,
            'locale' => 'en',
            "classpath" => "Samsa",
            "email" => "samsa@samsa.com"
        );
        $organizations->insert($organization);
        echo "Inserted Organization Samsa \n";
        $organizationNewSamsa = $organizations->findOne(array(
            'name' => 'Samsa'
        ));

        $organizationSamsa = $mongoObjectFactory->findObject("Organization",  $organizationNewSamsa['_id']);
        $typeW = 'Workspace';
        $data = array(
            "active" => 'true',
            "title" => "WorkspaceSamsa",
            "name" => "/view/0"
        );

        $returnW = $organizationSamsa->add($typeW, $data);
        echo "Inserted Workspace for Samsa \n";
        $samsa = $mongoObjectFactory->getSamsa();
        $workspaceSamsaObject = $mongoObjectFactory->findObject($typeW, (string) $returnW);
        echo "Inserted Workspace for Samsa : initiate\n";
        $workspaceSamsaObject->initiate();
        echo "Inserted Workspace for Samsa : initiateSamsaUI\n";
        $workspaceSamsaObject->initiateSamsaUI();
        echo "Inserted Workspace for Samsa : findOne\n";
        $roleSuperAdmin = $userRoles->findOne(array(
            'role' => 'superadmin'
        ));
        $superAdmin = array(
            'name' => 'superadmin',
            'password' => md5('12345'),
            'email' => 'superadmin@samsa.com',
            'userRole' => array(
                '$ref' => 'userRoles',
                '$id' => $roleSuperAdmin['_id'],
                '$db' => 'zf2odm'
            ),
            'organization' => array(
                '$ref' => 'organizations',
                '$id' => $organizationNewSamsa['_id'],
                '$db' => 'zf2odm'

            ),
            'organizationList' => json_encode(array(
                array(
                    'organization' => (string) $organizationNewSamsa['_id'],
                    'samsarole' => null
                )
            )),
            'deleted' => 0
        );
        echo "Inserted Workspace for Samsa : insert\n";
        $users->insert($superAdmin);
        //insert cron jobs
        $typeCronJob = 'Cronjob';
        $cronJob1 = array(
            'name' => 'mihai',
            'status' => 1,
            'objectType' => 'Customer',
            'method' => 'changeName',
            'datetime' => time()
        );
        $returnW = $workspaceSamsaObject->getIdAsString();

        echo "Inserted Workspace for Samsa : createAndAdd\n";
        // DORU
        //$mongoObjectFactory->createAndAdd($typeW, (string)$returnW, $typeCronJob, $cronJob1);

        $cronJob2 = array(
            'name' => 'test',
            'status' => 1,
            'objectType' => 'Customer',
            'method' => 'changeName',
            'datetime' => time()
        );
        echo "Inserted Workspace for Samsa : getIdAsString\n";
        $returnW = $workspaceSamsaObject->getIdAsString();
        echo "Inserted Workspace for Samsa : createAndAdd\n";
        // DORU
        //$mongoObjectFactory->createAndAdd($typeW, (string)$returnW, $typeCronJob, $cronJob2);
        echo "Inserted Workspace for Samsa : unset\n";
        unset($_SESSION['organization']);
        /**
         * #################################
         * End Insert a superadmin and Samsa organization
         * #################################
         */


        /**
         * #################################
         *  Start OrderTapp organization
         * #################################
         */
        if (ORG::DEF_ORDERTAPP) {
            $_SESSION['organization'] = 'Ordertapp';
            $mongoOrdertapp = Database::getInstance();
            $dbOrdertapp = $mongoOrdertapp->Ordertapp;
            $dbOrdertapp->drop();

            $organization = array(
                'name' => 'Organization Ordertapp',
                'deleted' => 0,
                'locale' => 'nl',
                "classpath" => "Ordertapp",
                "dbname" => "Ordertapp",
                'organizationDbNumber' => 0,
                "email" => "ordertapp@ordertapp.com"
            );
            $organizations->insert($organization);
            echo "Inserted Organization OrderTapp \n";
            $organizationNewOrdertapp = $organizations->findOne(array(
                'name' => 'Organization Ordertapp'
            ));

            $organizationOrdertapp = $mongoObjectFactory->findObject("Organization", $organizationNewOrdertapp['_id']);
            $samsa->addChild($organizationOrdertapp, "organizations");
            $data = array(
                "active" => 'true',
                "title" => "WorkspaceOrdertapp",
                "name" => "/view/0"
            );
            $typeW = 'Workspace';
            $returnW = $organizationOrdertapp->add($typeW, $data);
            echo "Inserted Workspace for Ordertapp \n";

            $workspaceOrdertappObject = $mongoObjectFactory->findObject($typeW, (string)$returnW);

            $workspaceOrdertappObject->initiate();

            $roleAdmin = $userRoles->findOne(array(
                'role' => 'admin'
            ));
            $admin1Ordertapp = array(
                'name' => 'adminordertapp',
                'password' => md5('12345'),
                'email' => 'admin@ordertapp.nl',
                'userRole' => array(
                    '$ref' => 'userRoles',
                    '$id' => $roleAdmin['_id'],
                    '$db' => 'zf2odm'
                ),
                'organization' => array(
                    '$ref' => 'organizations',
                    '$id' => $organizationNewOrdertapp['_id'],
                    '$db' => 'zf2odm'

                ),
                'organizationList' => json_encode(array( array('organization' => (string) $organizationNewOrdertapp['_id'], 'samsarole' => null))),
                'deleted' => 0
            );


            $users->insert($admin1Ordertapp);

            echo "Inserted users for OrderTapp \n";
            unset($_SESSION['organization']);
        }
        /**
         * #################################
         *  End OrderTapp organization
         * #################################
         */
        /**
         * #################################
         *  Start Rallocator organization
         * #################################
         */
        if (ORG::DEF_RALLOCATOR) {
            $_SESSION['organization'] = 'Rallocator';
            $mongoRallocator = Database::getInstance();
            $dbRallocator = $mongoRallocator->Rallocator;
            $dbRallocator->drop();

            $organization = array(
                'name' => 'Organization Rallocator',
                'deleted' => 0,
                'locale' => 'ro',
                "classpath" => "Rallocator",
                "dbname" => "Rallocator",
                'organizationDbNumber' => 0,
                "email" => "rallocator@rallocator.com"
            );
            $organizations->insert($organization);
            echo "Inserted Organization OrderTapp \n";
            $organizationNewRallocator = $organizations->findOne(array(
                'name' => 'Organization Rallocator'
            ));

            $organizationRallocator = $mongoObjectFactory->findObject("Organization", $organizationNewRallocator['_id']);
            $samsa->addChild($organizationRallocator, "organizations");
            $data = array(
                "active" => 'true',
                "title" => "WorkspaceRallocator",
                "name" => "/view/0"
            );
            $typeW = 'Workspace';
            $returnW = $organizationRallocator->add($typeW, $data);
            echo "Inserted Workspace for Rallocator \n";

            $workspaceRallocatorObject = $mongoObjectFactory->findObject($typeW, (string)$returnW);

            $workspaceRallocatorObject->initiate();

            $roleAdmin = $userRoles->findOne(array(
                'role' => 'admin'
            ));
            $admin1Rallocator = array(
                'name' => 'adminrallocator',
                'password' => md5('12345'),
                'email' => 'admin@rallocator.nl',
                'userRole' => array(
                    '$ref' => 'userRoles',
                    '$id' => $roleAdmin['_id'],
                    '$db' => 'zf2odm'
                ),
                'organization' => array(
                    '$ref' => 'organizations',
                    '$id' => $organizationNewRallocator['_id'],
                    '$db' => 'zf2odm'

                ),
                'organizationList' => json_encode(array( array('organization' => (string) $organizationNewRallocator['_id'], 'samsarole' => null))),
                'deleted' => 0
            );


            $users->insert($admin1Rallocator);

            echo "Inserted users for Rallocator \n";
            unset($_SESSION['organization']);
        }
        /**
         * #################################
         *  End OrderTapp organization
         * #################################
         */



        /**
         * #################################
         *  Start InGroove organization
         * #################################
         */
        if (ORG::DEF_INGROOVE) {
            $_SESSION['organization'] = 'Ingroove';
            $mongoIngroove = Database::getInstance();
            $dbIngroove = $mongoIngroove->Ingroove;
            $dbIngroove->drop();

            $organization = array(
                'name' => 'Organization Ingroove',
                'deleted' => 0,
                'locale' => 'nl',
                "classpath" => "Ingroove",
                "dbname" => "Ingroove",
                'organizationDbNumber' => 0,
                "email" => "Ingroove@Ingroove.com"
            );
            $organizations->insert($organization);
            echo "Inserted Organization Ingroove \n";
            $organizationNewIngroove = $organizations->findOne(array(
                'name' => 'Organization Ingroove'
            ));

            $organizationIngroove = $mongoObjectFactory->findObject("Organization", $organizationNewIngroove['_id']);
            $samsa->addChild($organizationIngroove, "organizations");
            $data = array(
                "active" => 'true',
                "title" => "WorkspaceIngroove",
                "name" => "/view/0"
            );
            $typeW = 'Workspace';
            $returnW = $organizationIngroove->add($typeW, $data);
            echo "Inserted Workspace for Ingroove \n";

            $workspaceIngrooveObject = $mongoObjectFactory->findObject($typeW, (string)$returnW);

            $workspaceIngrooveObject->initiate();

            $roleAdmin = $userRoles->findOne(array(
                'role' => 'admin'
            ));
            $admin1Ingroove = array(
                'name' => 'Ingroove',
                'password' => md5('12345'),
                'email' => 'admin@ingroove.nl',
                'userRole' => array(
                    '$ref' => 'userRoles',
                    '$id' => $roleAdmin['_id'],
                    '$db' => 'zf2odm'
                ),
                'organization' => array(
                    '$ref' => 'organizations',
                    '$id' => $organizationNewIngroove['_id'],
                    '$db' => 'zf2odm'

                ),
                'organizationList' => json_encode(array( array('organization' => (string) $organizationNewIngroove['_id'], 'samsarole' => null))),
                'deleted' => 0
            );


            $users->insert($admin1Ingroove);

            echo "Inserted users for Ingroove \n";
            unset($_SESSION['organization']);
        }
        /**
         * #################################
         *  End Ingroove organization
         * #################################
         */


        /**
         * #################################
         *  Start ILC organization
         * #################################
         */
        if (ORG::DEF_ILC) {
            $_SESSION['organization'] = 'Ilc';
            $mongoIlc = Database::getInstance();
            $dbIlc = $mongoIlc->Ilc;
            $dbIlc->drop();

            $organization = array(
                'name' => 'Organization Ilc',
                'deleted' => 0,
                'locale' => 'nl',
                "classpath" => "Ilc",
                "dbname" => "Ilc",
                'organizationDbNumber' => 0,
                "email" => "ilc@ilc.com"
            );
            $organizations->insert($organization);
            echo "Inserted Organization Ilc \n";
            $organizationNewIlc = $organizations->findOne(array(
                'name' => 'Organization Ilc'
            ));

            $organizationIlc = $mongoObjectFactory->findObject("Organization", $organizationNewIlc['_id']);
            $samsa->addChild($organizationIlc, "organizations");
            $data = array(
                "active" => 'true',
                "title" => "WorkspaceIlc",
                "name" => "/view/0"
            );
            $typeW = 'Workspace';
            $returnW = $organizationIlc->add($typeW, $data);
            echo "Inserted Workspace for Ilc \n";

            $workspaceIlcObject = $mongoObjectFactory->findObject($typeW, (string)$returnW);

            $workspaceIlcObject->initiate();

            $roleAdmin = $userRoles->findOne(array(
                'role' => 'admin'
            ));
            $admin1Ilc = array(
                'name' => 'adminilc',
                'password' => md5('12345'),
                'email' => 'admin@ilc.nl',
                'userRole' => array(
                    '$ref' => 'userRoles',
                    '$id' => $roleAdmin['_id'],
                    '$db' => 'zf2odm'
                ),
                'organization' => array(
                    '$ref' => 'organizations',
                    '$id' => $organizationNewIlc['_id'],
                    '$db' => 'zf2odm'

                ),
                'organizationList' => json_encode(array( array('organization' => (string) $organizationNewIlc['_id'], 'samsarole' => null))),
                'deleted' => 0
            );
            $admin2Ilc = array(
                'name' => 'adminilc2',
                'password' => md5('12345'),
                'email' => 'admin2@ilc.nl',
                'userRole' => array(
                    '$ref' => 'userRoles',
                    '$id' => $roleAdmin['_id'],
                    '$db' => 'zf2odm'
                ),
                'organization' => array(
                    '$ref' => 'organizations',
                    '$id' => $organizationNewIlc['_id'],
                    '$db' => 'zf2odm'

                ),
                'organizationList' => json_encode(array( array('organization' => (string) $organizationNewIlc['_id'], 'samsarole' => null))),
                    'deleted' => 0
            );

            $users->insert($admin1Ilc);
            $users->insert($admin2Ilc);

            echo "Inserted users for Ilc \n";
            unset($_SESSION['organization']);
        }
        /**
         * #################################
         *  End ILC organization
         * #################################
         */




        /**
         * #################################
         * Start Tcs organization
         * #################################
         */
        if (ORG::DEF_TCS) {
            $_SESSION['organization'] = 'Tcs';
            $mongoTcs = Database::getInstance();
            $dbTcs = $mongoTcs->Tcs;
            $dbTcs->drop();


            $organization = array(
                'name' => 'Organization Tcs',
                'deleted' => 0,
                'locale' => 'en',
                "classpath" => "Tcs",
                "dbname" => "Tcs",
                'organizationDbNumber' => 0,
                "email" => "Tcs@Tcs.com"
            );
            $organizations->insert($organization);
            echo "Inserted Organization Tcs \n";
            $organizationNewTcs = $organizations->findOne(array(
                'name' => 'Organization Tcs'
            ));
            $organizationTcs = $mongoObjectFactory->findObject("Organization", $organizationNewTcs['_id']);
            $samsa->addChild($organizationTcs, "organizations");
            $typeW = 'Workspace';
            $data = array(
                "active" => 'true',
                "title" => "WorkspaceTcs",
                "name" => "/view/0"
            );

            $returnW = $organizationTcs->add($typeW, $data);
            echo "Inserted Workspace for Tcs \n";
            $workspaceTcsObject = $mongoObjectFactory->findObject($typeW, (string)$returnW);

            $workspaceTcsObject->initiate();

            $roleAdmin = $userRoles->findOne(array(
                'role' => 'admin'
            ));
            $admin1 = array(
                'name' => 'admintcs',
                'password' => md5('12345'),
                'email' => 'admin@tcs.nl',
                'userRole' => array(
                    '$ref' => 'userRoles',
                    '$id' => $roleAdmin['_id'],
                    '$db' => 'zf2odm'
                ),
                'organization' => array(
                    '$ref' => 'organizations',
                    '$id' => $organizationNewTcs['_id'],
                    '$db' => 'zf2odm'

                ),
                'organizationList' => json_encode(array( array('organization' => (string) $organizationNewTcs['_id'], 'samsarole' => null))),
                'deleted' => 0
            );

            $users->insert($admin1);

            echo "Inserted users for Tcs \n";
            unset($_SESSION['organization']);
        }
        /**
         * #################################
         * End Tcs organization
         * #################################
         */

        /**
         * #################################
         * Start Veco organization
         * #################################
         */
        if (ORG::DEF_VECO) {
            $_SESSION['organization'] = 'Veco';
            $mongoVeco = Database::getInstance();
            $dbVeco = $mongoVeco->Veco;
            $dbVeco->drop();


            $organization = array(
                'name' => 'Organization Veco',
                'deleted' => 0,
                'locale' => 'en',
                "classpath" => "Veco",
                "dbname" => "Veco",
                'organizationDbNumber' => 0,
                "email" => "veco@veco.com"
            );
            $organizations->insert($organization);
            echo "Inserted Organization Veco \n";
            $organizationNewVeco = $organizations->findOne(array(
                'name' => 'Organization Veco'
            ));
            $organizationVeco = $mongoObjectFactory->findObject("Organization", $organizationNewVeco['_id']);
            $samsa->addChild($organizationVeco, "organizations");
            $typeW = 'Workspace';
            $data = array(
                "active" => 'true',
                "title" => "WorkspaceVeco",
                "name" => "/view/0"
            );

            $returnW = $organizationVeco->add($typeW, $data);
            echo "Inserted Workspace for Veco \n";
            $workspaceVecoObject = $mongoObjectFactory->findObject($typeW, (string)$returnW);

            $workspaceVecoObject->initiate();

            $roleAdmin = $userRoles->findOne(array(
                'role' => 'admin'
            ));
            $admin1 = array(
                'name' => 'adminveco',
                'password' => md5('12345'),
                'email' => 'admin@veco.nl',
                'userRole' => array(
                    '$ref' => 'userRoles',
                    '$id' => $roleAdmin['_id'],
                    '$db' => 'zf2odm'
                ),
                'organization' => array(
                    '$ref' => 'organizations',
                    '$id' => $organizationNewVeco['_id'],
                    '$db' => 'zf2odm'

                ),
                'organizationList' => json_encode(array( array('organization' => (string) $organizationNewVeco['_id'], 'samsarole' => null))),
                'deleted' => 0
            );
            $admin2 = array(
                'name' => 'adminveco2',
                'password' => md5('12345'),
                'email' => 'admin2@veco.nl',
                'userRole' => array(
                    '$ref' => 'userRoles',
                    '$id' => $roleAdmin['_id'],
                    '$db' => 'zf2odm'
                ),
                'organization' => array(
                    '$ref' => 'organizations',
                    '$id' => $organizationNewVeco['_id'],
                    '$db' => 'zf2odm'

                ),
                'organizationList' => json_encode(array( array('organization' => (string) $organizationNewVeco['_id'], 'samsarole' => null))),
                'deleted' => 0
            );

            $users->insert($admin1);
            $users->insert($admin2);

            echo "Inserted users for Veco \n";
            unset($_SESSION['organization']);
        }
        /**
         * #################################
         * End Veco organization
         * #################################
         */


        /**
         * #################################
         * Start Neways organization
         * #################################
         */
        if (ORG::DEF_NEWAYS)
        {
            $_SESSION['organization'] = 'Nw';
            $mongoNw = Database::getInstance();
            $dbNw = $mongoNw->Nw;
            $dbNw->drop();


            $organization = array(
                'name' => 'Organization Neways',
                'deleted' => 0,
                'locale' => 'nl',
                "classpath" => "Nw",
                "dbname" => "Nw",
                'organizationDbNumber' => 0,
                "email" => "nw@nw.com"
            );
            $organizations->insert($organization);
            echo "Inserted Organization Neways \n";
            $organizationNewNw = $organizations->findOne(array(
                'name' => 'Organization Neways'
            ));
            $organizationNw = $mongoObjectFactory->findObject("Organization", $organizationNewNw['_id']);
            $samsa->addChild($organizationNw, "organizations");
            $typeW = 'Workspace';
            $data = array(
                "active" => 'true',
                "title" => "WorkspaceNw",
                "name" => "/view/0"
            );

            $returnW = $organizationNw->add($typeW, $data);
            echo "Inserted Workspace for Neways \n";
            $workspaceNwObject = $mongoObjectFactory->findObject($typeW, (string)$returnW);

            $workspaceNwObject->initiate();

            $roleAdmin = $userRoles->findOne(array(
                'role' => 'admin'
            ));
            $admin1 = array(
                'name' => 'admin',
                'password' => md5('12345'),
                'email' => 'admin@nw.nl',
                'userRole' => array(
                    '$ref' => 'userRoles',
                    '$id' => $roleAdmin['_id'],
                    '$db' => 'zf2odm'
                ),
                'organization' => array(
                    '$ref' => 'organizations',
                    '$id' => $organizationNewNw['_id'],
                    '$db' => 'zf2odm'

                ),
                'organizationList' => json_encode(array( array('organization' => (string) $organizationNewNw['_id'], 'samsarole' => null))),
                'deleted' => 0
            );


            $users->insert($admin1);

            echo "Inserted users for Neways \n";
            unset($_SESSION['organization']);
        }
        /**
         * #################################
         * End Neways organization
         * #################################
         */



        /**
         * #################################
         *  Start CBMN organization
         * #################################
         */
        if (ORG::DEF_CBMN)
        {
            $_SESSION['organization'] = 'Cbmn';
            $mongoCbmn = Database::getInstance();
            $dbCbmn = $mongoCbmn->Cbmn;
            $dbCbmn->drop();

            $organization = array(
                'name' => 'Organization Cbmn',
                'deleted' => 0,
                'locale' => 'de',
                "classpath" => "Cbmn",
                "dbname" => "Cbmn",
                'organizationDbNumber' => 0,
                "email" => "cbmn@cbmn.de"
            );
            $organizations->insert($organization);
            echo "Inserted Organization Cbmn \n";
            $organizationNewCbmn = $organizations->findOne(array(
                'name' => 'Organization Cbmn'
            ));

            $organizationCbmn = $mongoObjectFactory->findObject("Organization", $organizationNewCbmn['_id']);
            $samsa->addChild($organizationCbmn, "organizations");
            $data = array(
                "active" => 'true',
                "title" => "WorkspaceCbmn",
                "name" => "/view/0"
            );
            $typeW = 'Workspace';
            $returnW = $organizationCbmn->add($typeW, $data);
            echo "Inserted Workspace for Cbmn \n";

            $workspaceCbmnObject = $mongoObjectFactory->findObject($typeW, (string)$returnW);

            $workspaceCbmnObject->initiate();

            $roleAdmin = $userRoles->findOne(array(
                'role' => 'admin'
            ));
            $admin1Ilc = array(
                'name' => 'admincbmn',
                'password' => md5('12345'),
                'email' => 'admin@cbmn.de',
                'userRole' => array(
                    '$ref' => 'userRoles',
                    '$id' => $roleAdmin['_id'],
                    '$db' => 'zf2odm'
                ),
                'organization' => array(
                    '$ref' => 'organizations',
                    '$id' => $organizationNewCbmn['_id'],
                    '$db' => 'zf2odm'

                ), 'organizationList' => json_encode(array( array('organization' => (string) $organizationNewCbmn['_id'], 'samsarole' => null))),
                'deleted' => 0
            );

            $users->insert($admin1Ilc);

            echo "Inserted users for Cbmn \n";
            unset($_SESSION['organization']);
        }
        /**
         * #################################
         *  End CBMN organization
         * #################################
         */



        /**
         * #################################
         *  Start Issue organization
         * #################################
         */
        if (ORG::DEF_SUPPORT) {
            $_SESSION['organization'] = 'Issue';
            $mongoIssue = Database::getInstance();
            $dbIssue = $mongoCbmn->Issue;
            $dbIssue->drop();

            $organization = array(
                'name' => 'Organization Issue',
                'deleted' => 0,
                'locale' => 'ro',
                "classpath" => "Issue",
                "dbname" => "Issue",
                'organizationDbNumber' => 0,
                "email" => "issue@issue.ro"
            );
            $organizations->insert($organization);
            echo "Inserted Organization Issue \n";
            $organizationNewIssue = $organizations->findOne(array(
                'name' => 'Organization Issue'
            ));

            $organizationIssue = $mongoObjectFactory->findObject("Organization", $organizationNewIssue['_id']);
            $samsa->addChild($organizationIssue, "organizations");
            $data = array(
                "active" => 'true',
                "title" => "WorkspaceIssue",
                "name" => "/view/0"
            );
            $typeW = 'Workspace';
            $returnW = $organizationIssue->add($typeW, $data);
            echo "Inserted Workspace for Issue \n";

            $workspaceIssueObject = $mongoObjectFactory->findObject($typeW, (string)$returnW);

            $workspaceIssueObject->initiate();

            $roleAdmin = $userRoles->findOne(array(
                'role' => 'admin'
            ));
            $adminIssue = array(
                'name' => 'adminIssue',
                'password' => md5('12345'),
                'email' => 'admin@issue.ro',
                'userRole' => array(
                    '$ref' => 'userRoles',
                    '$id' => $roleAdmin['_id'],
                    '$db' => 'zf2odm'
                ),
                'organization' => array(
                    '$ref' => 'organizations',
                    '$id' => $organizationNewIssue['_id'],
                    '$db' => 'zf2odm'

                ),
                'organizationList' => json_encode(array( array('organization' => (string) $organizationNewIssue['_id'], 'samsarole' => null))),
                'deleted' => 0
            );

            $users->insert($adminIssue);

            echo "Inserted users for Issue \n";
            unset($_SESSION['organization']);
        }
        /**
         * #################################
         *  End Issue organization
         * #################################
         */
        


        /**
         * #################################
         *  Start EBC organization
         * #################################
         */
        if (ORG::DEF_EBC) {
            $_SESSION['organization'] = 'Ebc';
            $mongoEbc = Database::getInstance();
            $dbEbc = $mongoEbc->Ebc;
            $dbEbc->drop();

            $organization = array(
                'name' => 'Organization Ebc',
                'deleted' => 0,
                'locale' => 'nl',
                "classpath" => "Ebc",
                "dbname" => "Ebc",
                'organizationDbNumber' => 0,
                "email" => "ebc@ebc.nl"
            );
            $organizations->insert($organization);
            echo "Inserted Organization Ebc \n";
            $organizationNewEbc = $organizations->findOne(array(
                'name' => 'Organization Ebc'
            ));

            $organizationEbc = $mongoObjectFactory->findObject("Organization", $organizationNewEbc['_id']);
            $samsa->addChild($organizationEbc, "organizations");
            $data = array(
                "active" => 'true',
                "title" => "WorkspaceEbc",
                "name" => "/view/0"
            );
            $typeW = 'Workspace';
            $returnW = $organizationEbc->add($typeW, $data);
            echo "Inserted Workspace for Ebc \n";

            $workspaceEbcObject = $mongoObjectFactory->findObject($typeW, (string)$returnW);

            $workspaceEbcObject->initiate();

            $roleAdmin = $userRoles->findOne(array(
                'role' => 'admin'
            ));
            $admin1Ebc = array(
                'name' => 'admin',
                'password' => md5('12345'),
                'email' => 'admin@ebc.nl',
                'userRole' => array(
                    '$ref' => 'userRoles',
                    '$id' => $roleAdmin['_id'],
                    '$db' => 'zf2odm'
                ),
                'organization' => array(
                    '$ref' => 'organizations',
                    '$id' => $organizationNewEbc['_id'],
                    '$db' => 'zf2odm'

                ),
                'organizationList' => json_encode(array( array('organization' => (string) $organizationNewEbc['_id'], 'samsarole' => null))),
                'deleted' => 0
            );


            $users->insert($admin1Ebc);

            echo "Inserted users for Ebc \n";
            unset($_SESSION['organization']);
        }
        /**
         * #################################
         *  End EBC organization
         * #################################
         */


        /**
         * #################################
         *  Start Etc organization
         * #################################
         */
        if (ORG::DEF_ETC) {
            $_SESSION['organization'] = 'Etc';
            $mongoETM = Database::getInstance();
            $dbETM = $mongoETM->Etc;
            $dbETM->drop();

            $organization = array(
                'name' => 'Organization Etc',
                'deleted' => 0,
                'locale' => 'ro',
                "classpath" => "Etc",
                "dbname" => "Etc",
                'organizationDbNumber' => 0,
                "email" => "etc@etc.com"
            );
            $organizations->insert($organization);
            echo "Inserted Organization Etc \n";
            $organizationNewETM = $organizations->findOne(array(
                'name' => 'Organization Etc'
            ));

            $organizationETM = $mongoObjectFactory->findObject("Organization", $organizationNewETM['_id']);
            $samsa->addChild($organizationETM, "organizations");
            $data = array(
                "active" => 'true',
                "title" => "WorkspaceEtc",
                "name" => "/view/0"
            );
            $typeW = 'Workspace';
            $returnW = $organizationETM->add($typeW, $data);
            echo "Inserted Workspace for Etc \n";
            $workspaceETMObject = $mongoObjectFactory->findObject($typeW, (string)$returnW);

            $workspaceETMObject->initiate();

            $roleAdmin = $userRoles->findOne(array(
                'role' => 'admin'
            ));
            $admin1ETM = array(
                'name' => 'admindetc',
                'password' => md5('12345'),
                'email' => 'admin@etc.ro',
                'userRole' => array(
                    '$ref' => 'userRoles',
                    '$id' => $roleAdmin['_id'],
                    '$db' => 'zf2odm'
                ),
                'organization' => array(
                    '$ref' => 'organizations',
                    '$id' => $organizationNewETM['_id'],
                    '$db' => 'zf2odm'

                ),
                'organizationList' => json_encode(array( array('organization' => (string) $organizationNewETM['_id'], 'samsarole' => null))),
                'deleted' => 0
            );
            $admin2ETM = array(
                'name' => 'admindetm2',
                'password' => md5('12345'),
                'email' => 'admin2@etc.ro',
                'userRole' => array(
                    '$ref' => 'userRoles',
                    '$id' => $roleAdmin['_id'],
                    '$db' => 'zf2odm'
                ),
                'organization' => array(
                    '$ref' => 'organizations',
                    '$id' => $organizationNewETM['_id'],
                    '$db' => 'zf2odm'

                ),
                'organizationList' => json_encode(array( array('organization' => (string) $organizationNewETM['_id'], 'samsarole' => null))),
                'deleted' => 0
            );

            $users->insert($admin1ETM);
            $users->insert($admin2ETM);

            echo "Inserted users for Etc \n";
            unset($_SESSION['organization']);
        }
        /**
         * #################################
         *  End Etc organization
         * #################################
         */

        echo "Organizations created\n";

       // return "Organization fixtures run successfully!\n";


        /**
         * #################################
         *  Start ILC Demo organization
         * #################################
         */
        if (ORG::DEF_ILCDEMO) {
            $_SESSION['organization'] = 'IlcDemo';
            $mongoIlcDemo = Database::getInstance();
            $dbIlcDemo = $mongoIlcDemo->IlcDemo;
            $dbIlcDemo->drop();

            $organization = array(
                'name' => 'Organization IlcDemo',
                'deleted' => 0,
                'locale' => 'nl',
                "classpath" => "IlcDemo",
                "dbname" => "IlcDemo",
                'organizationDbNumber' => 0,
                "email" => "ilcdemo@ilcdemo.com"
            );
            $organizations->insert($organization);
            echo "Inserted Organization IlcDemo \n";
            $organizationNewIlcDemo = $organizations->findOne(array(
                'name' => 'Organization IlcDemo'
            ));

            $organizationIlcDemo = $mongoObjectFactory->findObject("Organization", $organizationNewIlcDemo['_id']);
            $samsa->addChild($organizationIlcDemo, "organizations");
            $data = array(
                "active" => 'true',
                "title" => "WorkspaceIlcDemo",
                "name" => "/view/0"
            );
            $typeW = 'Workspace';
            $returnW = $organizationIlcDemo->add($typeW, $data);
            echo "Inserted Workspace for IlcDemo \n";

            $workspaceIlcDemoObject = $mongoObjectFactory->findObject($typeW, (string)$returnW);

            $workspaceIlcDemoObject->initiate();

            $roleAdmin = $userRoles->findOne(array(
                'role' => 'admin'
            ));
            $admin1IlcDemo = array(
                'name' => 'adminilcdemo',
                'password' => md5('12345'),
                'email' => 'admin@ilcdemo.nl',
                'userRole' => array(
                    '$ref' => 'userRoles',
                    '$id' => $roleAdmin['_id'],
                    '$db' => 'zf2odm'
                ),
                'organization' => array(
                    '$ref' => 'organizations',
                    '$id' => $organizationNewIlcDemo['_id'],
                    '$db' => 'zf2odm'

                ),
                'organizationList' => json_encode(array( array('organization' => (string) $organizationNewIlcDemo['_id'], 'samsarole' => null))),
                'deleted' => 0
            );
            $admin2IlcDemo = array(
                'name' => 'adminilcdemo2',
                'password' => md5('12345'),
                'email' => 'admin2@ilcdemo.nl',
                'userRole' => array(
                    '$ref' => 'userRoles',
                    '$id' => $roleAdmin['_id'],
                    '$db' => 'zf2odm'
                ),
                'organization' => array(
                    '$ref' => 'organizations',
                    '$id' => $organizationNewIlcDemo['_id'],
                    '$db' => 'zf2odm'

                ),
                'organizationList' => json_encode(array( array('organization' => (string) $organizationNewIlcDemo['_id']), 'samsarole' => null)),
                'deleted' => 0
            );

            $users->insert($admin1IlcDemo);
            $users->insert($admin2IlcDemo);

            echo "Inserted users for IlcDemo \n";
            unset($_SESSION['organization']);
        }
        /**
         * #################################
         *  End ILC Demo organization
         * #################################
         */

        /**
         * #################################
         *  Start DemoProcess organization
         * #################################
         */
        if (ORG::DEF_DEMO_PROCESS) {
            $_SESSION['organization'] = 'DemoProcess';
            $mongoDemoProcess = Database::getInstance();
            $dbDemoProcess = $mongoDemoProcess->DemoProcess;
            $dbDemoProcess->drop();

            $organization = array(
                'name' => 'Organization DemoProcess',
                'deleted' => 0,
                'locale' => 'en',
                "classpath" => "DemoProcess",
                "dbname" => "DemoProcess",
                'organizationDbNumber' => 0,
                "email" => "demoprocess@demoprocess.com"
            );
            $organizations->insert($organization);
            echo "Inserted Organization DemoProcess \n";
            $organizationNewDemoProcess = $organizations->findOne(array(
                'name' => 'Organization DemoProcess'
            ));

            $organizationDemoProcess = $mongoObjectFactory->findObject("Organization", $organizationNewDemoProcess['_id']);
            $samsa->addChild($organizationDemoProcess, "organizations");
            $data = array(
                "active" => 'true',
                "title" => "WorkspaceDemoProcess",
                "name" => "/view/0"
            );
            $typeW = 'Workspace';
            $returnW = $organizationDemoProcess->add($typeW, $data);
            echo "Inserted Workspace for DemoProcess \n";
            $workspaceDemoProcessObject = $mongoObjectFactory->findObject($typeW, (string)$returnW);

            $workspaceDemoProcessObject->initiate();

            $roleAdmin = $userRoles->findOne(array(
                'role' => 'admin'
            ));
            $admin1DemoProcess = array(
                'name' => 'admindemoprocess',
                'password' => md5('12345'),
                'email' => 'admindemoprocess@samsa.nl',
                'userRole' => array(
                    '$ref' => 'userRoles',
                    '$id' => $roleAdmin['_id'],
                    '$db' => 'zf2odm'
                ),
                'organization' => array(
                    '$ref' => 'organizations',
                    '$id' => $organizationNewDemoProcess['_id'],
                    '$db' => 'zf2odm'

                ),
                'organizationList' => json_encode(array( array('organization' => (string) $organizationNewDemoProcess['_id'], 'samsarole' => null))),
                'deleted' => 0
            );
            $admin2DemoProcess = array(
                'name' => 'admindemoprocess2',
                'password' => md5('12345'),
                'email' => 'admindemoprocess2@samsa.nl',
                'userRole' => array(
                    '$ref' => 'userRoles',
                    '$id' => $roleAdmin['_id'],
                    '$db' => 'zf2odm'
                ),
                'organization' => array(
                    '$ref' => 'organizations',
                    '$id' => $organizationNewDemoProcess['_id'],
                    '$db' => 'zf2odm'

                ),
                'organizationList' => json_encode(array( array('organization' => (string) $organizationNewDemoProcess['_id'], 'samsarole' => null))),
                'deleted' => 0
            );

            $users->insert($admin1DemoProcess);
            $users->insert($admin2DemoProcess);

            echo "Inserted users for DemoProcess \n";
            unset($_SESSION['organization']);
        }
        /**
         * #################################
         *  End DemoProcess organization
         * #################################
         */

        /**
         * #################################
         *  Start DemoTraining organization
         * #################################
         */
        if (ORG::DEF_DEMO_PROCESS) {
            $_SESSION['organization'] = 'DemoTraining';
            $mongoDemoTraining = Database::getInstance();
            $dbDemoTraining = $mongoDemoTraining->DemoTraining;
            $dbDemoTraining->drop();

            $organization = array(
                'name' => 'Organization DemoTraining',
                'deleted' => 0,
                'locale' => 'en',
                "classpath" => "DemoTraining",
                "dbname" => "DemoTraining",
                'organizationDbNumber' => 0,
                "email" => "demotraining@demotraining.com"
            );
            $organizations->insert($organization);
            echo "Inserted Organization DemoTraining \n";
            $organizationNewDemoTraining = $organizations->findOne(array(
                'name' => 'Organization DemoTraining'
            ));

            $organizationDemoTraining = $mongoObjectFactory->findObject("Organization", $organizationNewDemoTraining['_id']);
            $samsa->addChild($organizationDemoTraining, "organizations");
            $data = array(
                "active" => 'true',
                "title" => "WorkspaceDemoTraining",
                "name" => "/view/0"
            );
            $typeW = 'Workspace';
            $returnW = $organizationDemoTraining->add($typeW, $data);
            echo "Inserted Workspace for DemoTraining \n";
            $workspaceDemoTrainingObject = $mongoObjectFactory->findObject($typeW, (string)$returnW);

            $workspaceDemoTrainingObject->initiate();

            $roleAdmin = $userRoles->findOne(array(
                'role' => 'admin'
            ));
            $admin1DemoTraining = array(
                'name' => 'admindemotraining',
                'password' => md5('12345'),
                'email' => 'admindemotraining@samsa.nl',
                'userRole' => array(
                    '$ref' => 'userRoles',
                    '$id' => $roleAdmin['_id'],
                    '$db' => 'zf2odm'
                ),
                'organization' => array(
                    '$ref' => 'organizations',
                    '$id' => $organizationNewDemoTraining['_id'],
                    '$db' => 'zf2odm'

                ),
                'organizationList' => json_encode(array( array('organization' => (string) $organizationNewDemoTraining['_id'], 'samsarole' => null))),
                'deleted' => 0
            );
            $admin2DemoTraining = array(
                'name' => 'admindemotraining2',
                'password' => md5('12345'),
                'email' => 'admindemotraining2@samsa.nl',
                'userRole' => array(
                    '$ref' => 'userRoles',
                    '$id' => $roleAdmin['_id'],
                    '$db' => 'zf2odm'
                ),
                'organization' => array(
                    '$ref' => 'organizations',
                    '$id' => $organizationNewDemoTraining['_id'],
                    '$db' => 'zf2odm'

                ),
                'organizationList' => json_encode(array( array('organization' => (string) $organizationNewDemoTraining['_id'], 'samsarole' => null))),
                'deleted' => 0
            );

            $users->insert($admin1DemoTraining);
            $users->insert($admin2DemoTraining);

            echo "Inserted users for DemoTraining \n";
            unset($_SESSION['organization']);
        }
        /**
         * #################################
         *  End DemoTraining organization
         * #################################
         */

        /**
         * #################################
         *  Start sandbox organization
         * #################################
         */
        if (ORG::DEF_SANDBOX) {
            $_SESSION['organization'] = 'Sandbox';
            $mongoSandbox = Database::getInstance();
            $dbSandbox = $mongoSandbox->Sandbox;
            $dbSandbox->drop();

            $organization = array(
                'name' => 'Organization Sandbox',
                'deleted' => 0,
                'locale' => 'en',
                "classpath" => "Sandbox",
                "dbname" => "Sandbox",
                'organizationDbNumber' => 0,
                "email" => "sandbox@sandbox.com"
            );
            $organizations->insert($organization);
            echo "Inserted Organization Sandbox \n";
            $organizationNewSandbox = $organizations->findOne(array(
                'name' => 'Organization Sandbox'
            ));

            $organizationSandbox = $mongoObjectFactory->findObject("Organization", $organizationNewSandbox['_id']);
            $samsa->addChild($organizationSandbox, "organizations");
            $data = array(
                "active" => 'true',
                "title" => "WorkspaceSandbox",
                "name" => "/view/0"
            );
            $typeW = 'Workspace';
            $returnW = $organizationSandbox->add($typeW, $data);
            echo "Inserted Workspace for Sandbox \n";
            $workspaceSandboxObject = $mongoObjectFactory->findObject($typeW, (string)$returnW);

            $workspaceSandboxObject->initiate();

            $roleAdmin = $userRoles->findOne(array(
                'role' => 'admin'
            ));
            $admin1Sandbox = array(
                'name' => 'admindsandbox',
                'password' => md5('12345'),
                'email' => 'admindsandbox@samsa.nl',
                'userRole' => array(
                    '$ref' => 'userRoles',
                    '$id' => $roleAdmin['_id'],
                    '$db' => 'zf2odm'
                ),
                'organization' => array(
                    '$ref' => 'organizations',
                    '$id' => $organizationNewSandbox['_id'],
                    '$db' => 'zf2odm'

                ),
                'deleted' => 0
            );
            $admin2Sandbox = array(
                'name' => 'admindsandbox',
                'password' => md5('12345'),
                'email' => 'admindsandbox2@samsa.nl',
                'userRole' => array(
                    '$ref' => 'userRoles',
                    '$id' => $roleAdmin['_id'],
                    '$db' => 'zf2odm'
                ),
                'organization' => array(
                    '$ref' => 'organizations',
                    '$id' => $organizationNewSandbox['_id'],
                    '$db' => 'zf2odm'

                ),
                'organizationList' => json_encode(array( array('organization' => (string) $organizationNewSandbox['_id']), 'samsarole' => null)),
                'deleted' => 0
            );

            $users->insert($admin1Sandbox);
            $users->insert($admin2Sandbox);

            echo "Inserted users for Sandbox \n";
            unset($_SESSION['organization']);
        }
        /**
         * #################################
         *  End sandbox organization
         * #################################
         */

        /**
         * #################################
         *  Start PNA organization
         * #################################
         */
        if (ORG::DEF_PNA) {
            $_SESSION['organization'] = 'Pna';
            $mongoPna = Database::getInstance();
            $dbPna = $mongoPna->Pna;
            $dbPna->drop();

            $organization = array(
                'name' => 'Organization Pna',
                'deleted' => 0,
                'locale' => 'nl',
                "classpath" => "Pna",
                "dbname" => "Pna",
                'organizationDbNumber' => 0,
                "email" => "pna@pna.com"
            );
            $organizations->insert($organization);
            echo "Inserted Organization Pna \n";
            $organizationNewPna = $organizations->findOne(array(
                'name' => 'Organization Pna'
            ));

            $organizationPna = $mongoObjectFactory->findObject("Organization", $organizationNewPna['_id']);
            $samsa->addChild($organizationPna, "organizations");
            $data = array(
                "active" => 'true',
                "title" => "WorkspacePna",
                "name" => "/view/0"
            );
            $typeW = 'Workspace';

            $returnW = $organizationPna->add($typeW, $data);
            echo "Inserted Workspace for Pna \n";
                  print "ddd";exit;
            $workspacePnaObject = $mongoObjectFactory->findObject($typeW, (string)$returnW);

            $workspacePnaObject->initiate();

            $roleAdmin = $userRoles->findOne(array(
                'role' => 'admin'
            ));
            $admin1Pna = array(
                'name' => 'Pna',
                'password' => md5('12345'),
                'email' => 'admin@pna.nl',
                'userRole' => array(
                    '$ref' => 'userRoles',
                    '$id' => $roleAdmin['_id'],
                    '$db' => 'zf2odm'
                ),
                'organization' => array(
                    '$ref' => 'organizations',
                    '$id' => $organizationNewPna['_id'],
                    '$db' => 'zf2odm'

                ),
                'organizationList' => json_encode(array( array('organization' => (string) $organizationNewPna['_id'], 'samsarole' => null))),
                'deleted' => 0
            );


            $users->insert($admin1Pna);

            echo "Inserted users for Pna \n";
            unset($_SESSION['organization']);
        }

        /**
         * #################################
         *  End PNA organization
         * #################################
         */


        /**
         * #################################
         *  Start ELSIG organization
         * #################################
         */
        if (ORG::DEF_ELSIG) {
            $_SESSION['organization'] = 'Elsig';
            $mongoElsig = Database::getInstance();
            $dbElsig = $mongoElsig->Elsig;
            $dbElsig->drop();

            $organization = array(
                'name' => 'Organization Elsig',
                'deleted' => 0,
                'locale' => 'ro',
                "classpath" => "Elsig",
                "dbname" => "Elsig",
                'organizationDbNumber' => 0,
                "email" => "elsig@elsig.com"
            );
            $organizations->insert($organization);
            echo "Inserted Organization Elsig \n";
            $organizationNewElsig = $organizations->findOne(array(
                'name' => 'Organization Elsig'
            ));

            $organizationElsig = $mongoObjectFactory->findObject("Organization", $organizationNewElsig['_id']);
            $samsa->addChild($organizationElsig, "organizations");
            $data = array(
                "active" => 'true',
                "title" => "WorkspaceElsig",
                "name" => "/view/0"
            );
            $typeW = 'Workspace';

            $returnW = $organizationElsig->add($typeW, $data);
            echo "Inserted Workspace for Elsig \n";

            $workspaceElsigObject = $mongoObjectFactory->findObject($typeW, (string)$returnW);

            $workspaceElsigObject->initiate();

            $roleAdmin = $userRoles->findOne(array(
                'role' => 'admin'
            ));
            $admin1Elsig = array(
                'name' => 'adminelsig',
                'password' => md5('12345'),
                'email' => 'admin@elsig.ro',
                'userRole' => array(
                    '$ref' => 'userRoles',
                    '$id' => $roleAdmin['_id'],
                    '$db' => 'zf2odm'
                ),
                'organization' => array(
                    '$ref' => 'organizations',
                    '$id' => $organizationNewElsig['_id'],
                    '$db' => 'zf2odm'

                ),
                'organizationList' => json_encode(array( array('organization' => (string) $organizationNewElsig['_id'], 'samsarole' => null))),
                'deleted' => 0
            );


            $users->insert($admin1Elsig);

            echo "Inserted users for Elsig \n";
            unset($_SESSION['organization']);
        }
        /**
         * #################################
         *  End ELSIG organization
         * #################################
         */


        /**
         * #################################
         *  Start SIMULATOR organization
         * #################################
         */
        if (ORG::DEF_SIMULATOR) {
            $_SESSION['organization'] = 'Simulator';
            $mongoSimulatoe = Database::getInstance();
            $dbSimulator = $mongoSimulatoe->Simulator;
            $dbSimulator->drop();

            $organization = array(
                'name' => 'Social Network PoC',
                'deleted' => 0,
                'locale' => 'en',
                "classpath" => "Simulator",
                "dbname"=>"Simulator",
                'organizationDbNumber' => 0,
                "email" => "simulator@simulatortest.com"
            );
            $organizations->insert($organization);
            echo "Inserted Organization Simulator \n";
            $organizationNewSimulator = $organizations->findOne(array(
                'name' => 'Social Network PoC'
            ));

            $organizationSimulator = $mongoObjectFactory->findObject("Organization", $organizationNewSimulator['_id']);



            $samsa->addChild($organizationSimulator, "organizations");


            $data = array(
                "active" => 'true',
                "title" => "WorkspaceSimulator",
                "name" => "/view/0"
            );
            $typeW = 'Workspace';


            $returnW = $organizationSimulator->add($typeW, $data);

            echo "Inserted Workspace for Simulator \n";

            $workspaceSimulatorObject = $mongoObjectFactory->findObject($typeW, (string)$returnW);

            $workspaceSimulatorObject->initiate();

            $roleAdmin = $userRoles->findOne(array(
                'role' => 'admin'
            ));
            $admin1Simulator = array(
                'name' => 'adminsimulator',
                'password' => md5('12345'),
                'email' => 'admin@simulator.ro',
                'userRole' => array(
                    '$ref' => 'userRoles',
                    '$id' => $roleAdmin['_id'],
                    '$db' => 'zf2odm'
                ),
                'organization' => array(
                    '$ref' => 'organizations',
                    '$id' => $organizationNewSimulator['_id'],
                    '$db' => 'zf2odm'

                ),
                'organizationList' => json_encode(array( array('organization' => (string) $organizationNewSimulator['_id'], 'samsarole' => null))),
                'deleted' => 0
            );


            $users->insert($admin1Simulator);

            echo "Inserted users for Simulator \n";
            unset($_SESSION['organization']);
        }
        /**
         * #################################
         *  End SIMULATOR organization
         * #################################
         */




        /**
         * #################################
         *  Start ELSIG organization
         * #################################
         */
        if (ORG::DEF_AUTOPLAN) {
            $_SESSION['organization'] = 'Autoplan';
            $mongoAutoplan = Database::getInstance();
            $dbAutoplan = $mongoAutoplan->Autoplan;
            $dbAutoplan->drop();

            $organization = array(
                'name' => 'Organization Autoplan',
                'deleted' => 0,
                'locale' => 'ro',
                "classpath" => "Autoplan",
                "dbname" => "Autoplan",
                'organizationDbNumber' => 0,
                "email" => "autoplan@autoplan.com"
            );
            $organizations->insert($organization);
            echo "Inserted Organization Autoplan \n";
            $organizationNewAutoplan = $organizations->findOne(array(
                'name' => 'Organization Autoplan'
            ));

            $organizationAutoplan = $mongoObjectFactory->findObject("Organization", $organizationNewAutoplan['_id']);
            $samsa->addChild($organizationAutoplan, "organizations");
            $data = array(
                "active" => 'true',
                "title" => "WorkspaceElsig",
                "name" => "/view/0"
            );
            $typeW = 'Workspace';

            $returnW = $organizationAutoplan->add($typeW, $data);
            echo "Inserted Workspace for Autoplan \n";

            $workspaceAutoplanObject = $mongoObjectFactory->findObject($typeW, (string)$returnW);

            $workspaceAutoplanObject->initiate();

            $roleAdmin = $userRoles->findOne(array(
                'role' => 'admin'
            ));
            $admin1Autoplan = array(
                'name' => 'adminelsig',
                'password' => md5('12345'),
                'email' => 'admin@autoplan.com',
                'userRole' => array(
                    '$ref' => 'userRoles',
                    '$id' => $roleAdmin['_id'],
                    '$db' => 'zf2odm'
                ),
                'organization' => array(
                    '$ref' => 'organizations',
                    '$id' => $organizationNewAutoplan['_id'],
                    '$db' => 'zf2odm'

                ),
                'organizationList' => json_encode(array( array('organization' => (string) $organizationNewAutoplan['_id'], 'samsarole' => null))),
                'deleted' => 0
            );


            $users->insert($admin1Autoplan);

            echo "Inserted users for Autoplan \n";
            unset($_SESSION['organization']);
        }
        /**
         * #################################
         *  End ELSIG organization
         * #################################
         */



        echo "Insert content- success \n";

        return "Organization fixtures run successfully!\n";
    }
}
?>