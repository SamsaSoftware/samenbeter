<?php
namespace DataFixture\Controller;

use Application\Document\Template;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Console\Request as ConsoleRequest;
use RuntimeException;
use Application\DatabaseConnection\Database;

class UpdateUsersFixtureController extends AbstractActionController
{

    public function runAction()
    {

        $_SESSION["transaction_id"] = 5;
        echo "starting\n";
        //$sing = \Application\Document\Helper\NotificationCenter::getInstance();
        //$sing->getInstance()->setClasspath("Samsa");
        $request = $this->getRequest();
        if (!$request instanceof ConsoleRequest) {
            throw new RuntimeException('You can only use this action from a console!');
        }
        $mongoObjectFactory = new \Application\Controller\MongoObjectFactory();
        // select a database

        $m = Database::getInstance();

        $db = $m->{$mongoObjectFactory->getDBName("Organization")};

        $users = $db->users;
        $organizations = $db->organizations;

        echo "Update Organizations\n";
        $organizationsList = $organizations->find();
        foreach ($organizationsList as $orga) {
            $orga['dbname'] = $orga['classpath'];
            $orga['organizationDbNumber'] = 0;
            $organizations->update(array('_id' => $orga['_id']), $orga);
        }
        echo "Finish Update Organizations\n";


        //$templates = $db->createCollection("templates") ? $db->createCollection("templates") : $db->templates;

        echo "Update users\n";

        $userList = $users->find();
        foreach ($userList as $user) {
            if (isset($user['organization'])) {
                $organization = (string)$user['organization']['$id'];
                if (isset($user['organizationList'])) {
                    $organizationList = json_decode($user['organizationList']);
                    $organizationList[] = array('organization' => $organization, 'samsarole' => null);
                    $user['organizationList'] = json_encode($organizationList);
                } else {
                    $user['organizationList'] = json_encode(array('organization' => $organization, 'samsarole' => null));
                }
                $users->update(array('_id' => $user['_id']), $user);
                echo "done " . $user['name'] . "\n";
            }
        }
        echo "finished Update all users";
        echo "add templates";

        $_SESSION['dbname'] = 'Samsa';
        $sing = \Application\Document\Helper\NotificationCenter::getInstance();
        $sing->getInstance()->setClasspath("Samsa");
        echo "Setup organization samsa\n";
        // >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
        // Setup organization
        // >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
        $organizationObjectInstance = $mongoObjectFactory->findObjectByCriteria('Organization', array(
            'classpath' => 'Samsa'
        ));
        $workspaceId = $organizationObjectInstance['workspaces'][0]['$id'];
        echo "Setup organization 1\n";

        // get workspace of organization
        $typeW = 'Workspace';
        $workspaceD = $mongoObjectFactory->findObject($typeW, $workspaceId);

        $typeTemplate = 'Template';
        echo "Setup organization Samsa\n";
        // $workspaceD->createCalendar('default', 2016, 2017);

        $templateGeneratePassObj = $mongoObjectFactory->findObjectByCriteria('Template', array(
            'name' => Template::GENERATE_PASSWORD
        ));

        $typeWorkspace = 'Workspace';
        $workspaceId = $workspaceD->_id['$id'];

        if (!$templateGeneratePassObj) {
            echo "add template generate password\n";
            // create View of an Object - Employee



            $templateGenerateUsernamePassword = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\"><html xmlns=\"http://www.w3.org/1999/xhtml\">
	<head>
		<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />
	    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0\" />


	</head>
	<body>
		<h3>Welcome to %organization% </h3>

			<p>Thank you for creating an <strong>%organization%</strong> account. In order to log in, please use the following credentials:  </p>
			<p>Username: %username%</p>
			<p>Username: %password%</p>
			<p></p>
			<p>For making things easier, we generated a random password for you. You can change it at any time following the instructions to reset your password <a href=\"%url%\">Click here<a></p>
			<p></p>
			<p>Access your organization: <a href=\"%url2%\">Click here</a></p>
			<p></p>
			<p>Cheers,</p>
			<p>Samsa Software team.</p>
		<h6 class=\"text-muted\">(c) 2018 %organization% powered by <a href=\"http://samsasoftware.nl/contact/\">Samsa</a></h6>

	</body>
</html>
";

            $data = array(
                "name" => Template::GENERATE_PASSWORD,
                "text" => $templateGenerateUsernamePassword,
                "entity" => "",
                "subject" => "Username and password SamsaSoftware"
            );
            $return = $mongoObjectFactory->createAndAdd($typeWorkspace, (string)$workspaceId, $typeTemplate, $data);
        }


        $templateResetPasswordObj = $mongoObjectFactory->findObjectByCriteria('Template', array(
            'name' => Template::RESET_PASSWORD
        ));
        if (!$templateResetPasswordObj) {
            echo "add template reset password\n";

            $templateResetPassword = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\"><html xmlns=\"http://www.w3.org/1999/xhtml\">
	<head>
		<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />
	    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0\" />
	</head>
	<body>
		<h3>Hi %username% </h3>

			<p>We got a request to reset your <strong>%organization%</strong> password.</p>
			<p></p>
			<p><a href=\"%url%\">Click here to reset it.</a></p>
			<p></p>
			<p>If you ignore this message, your password won't be changed.</p>
			<p>If you didn't request a password reset, let us know <a href=\"http://samsasoftware.nl/contact/\">samsasoftware.nl</a></p>
			<p></p>
			<p>Cheers,</p>
			<p>Samsa Software team.</p>
		<h6 class=\"text-muted\">(c) 2018 %organization% powered by <a href=\"http://samsasoftware.nl/contact/\">Samsa</a></h6>

	</body>
</html>
		</div>
	</body>
</html>
";
            $data = array(
                "name" => Template::RESET_PASSWORD,
                "text" => $templateResetPassword,
                "entity" => "",
                "subject" => "Reset Password"
            );
            $return = $mongoObjectFactory->createAndAdd($typeWorkspace, (string)$workspaceId, $typeTemplate, $data);
        }

        $templateAlreadyMemberObj = $mongoObjectFactory->findObjectByCriteria('Template', array(
            'name' => Template::ALREADY_PART_OF_ORGANIZATION
        ));
        if (!$templateAlreadyMemberObj) {
            echo "add template already member\n";

            $templateAlreadyMember = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\"><html xmlns=\"http://www.w3.org/1999/xhtml\">
	<head>
		<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />
	    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0\" />


	</head>
	<body>
		<h3>Hi %username% </h3>

			<p>Thank you for registering to <strong>%organization%</strong>. </p>
			<p></p>
			<p>Our records show you are already registered as member of the following organisations:</p>
			<p>%organizations%</p>
			<p>If you want to change your credentials, please follow the link below:</p>
			<p><a href=\"%url%\">Click here<a></p>
			<p></p>
			<p>Cheers</p>
			<p>Samsa Software team.</p>
		<h6 class=\"text-muted\">(c) 2018 %organization% powered by <a href=\"http://samsasoftware.nl/contact/\">Samsa</a></h6>

	</body>
</html>
";
            $data = array(
                "name" => Template::ALREADY_PART_OF_ORGANIZATION,
                "text" => $templateAlreadyMember,
                "entity" => "",
                "subject" => "Already Member"
            );
            $return = $mongoObjectFactory->createAndAdd($typeWorkspace, (string)$workspaceId, $typeTemplate, $data);
        }

        $templateRegisteredToOrgObj = $mongoObjectFactory->findObjectByCriteria('Template', array(
            'name' => Template::REGISTERED_TO_ORGANIZATION
        ));
        if (!$templateRegisteredToOrgObj) {
            echo "add template registered to organization\n";

            $templateRegisteredToOrg = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\"><html xmlns=\"http://www.w3.org/1999/xhtml\">
	<head>
		<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />
	    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0\" />


	</head>
	<body>
		<h3>Hi %username% </h3>

			<p>You were registered to <strong>%organization%</strong>. </p>
			<p></p>
			<p>If you want to change your credentials, please follow the link below:</p>
			<p><a href=\"%url%\">Click here<a></p>
			<p></p>
			<p>Cheers</p>
			<p>Samsa Software team.</p>
		<h6 class=\"text-muted\">(c) 2018 %organization% powered by <a href=\"http://samsasoftware.nl/contact/\">Samsa</a></h6>

	</body>
</html>
";
            $data = array(
                "name" => Template::REGISTERED_TO_ORGANIZATION,
                "text" => $templateRegisteredToOrg,
                "entity" => "",
                "subject" => "Registered to Organization"
            );
            $return = $mongoObjectFactory->createAndAdd($typeWorkspace, (string)$workspaceId, $typeTemplate, $data);
        }

            $templateConfirmAccountObj = $mongoObjectFactory->findObjectByCriteria('Template', array(
                'name' => Template::CONFIRM_ACCOUNT
            ));
            if (!$templateConfirmAccountObj) {
                echo "add template confirm account to organization\n";

                $templateConfirmAccountObj = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\"><html xmlns=\"http://www.w3.org/1999/xhtml\">
	<head>
		<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />
	    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0\" />


	</head>
	<body>
		<h3>Hi %username% </h3>

			<p>Thank you for joining <strong>%organization%</strong>. </p>
			<p></p>
			<p>To finish signing up, you just need to confirm that we got your email right.</p>
			<p><a href=\"%url%\">Click here<a></p>
			<p></p>
			<p>Cheers</p>
			<p>Samsa Software team.</p>
		<h6 class=\"text-muted\">(c) 2018 %organization% powered by <a href=\"http://samsasoftware.nl/contact/\">Samsa</a></h6>

	</body>
</html>
";
                $data = array(
                    "name" => Template::CONFIRM_ACCOUNT,
                    "text" => $templateConfirmAccountObj,
                    "entity" => "",
                    "subject" => "Confirm your account"
                );
                $return = $mongoObjectFactory->createAndAdd($typeWorkspace, (string)$workspaceId, $typeTemplate, $data);
            }
            return "Data fixtures run successfully!\n";

        }
}