<?php
namespace Application\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Application\Service\UserService;
use Zend\Server\Reflection\ReflectionClass;

/**
 * @ODM\Document(collection="users")
 */
class UserTemplate extends Indexer
{

    const SUPER_ADMIN = 'superadmin';

    const ADMIN = 'admin';

    const USER = 'user';

    public $id;

    public $name;

    public $lastName;

    public $email;

    public $address;

    public $phone;

    public $classpath;

    public $samsarole;

    public $users;
    
    public $password;

    public static function getPK()
    {
        return 'email';
    }

    public static function getRelationTypeArray($name, $relations = array())
    {
       
        $relations['users'] = Model::ONE_TO_ONE;
        return self::getRelationFromArray($name, $relations);
    }

    public function validateUser($generatePasseword = true)
    {
        $userService = new UserService();
        $data = array();
        $data['name'] = $this->name;
        $data['lastname'] = '';
        $data['email'] = $this->email;
        $data['phone'] = $this->phone;
        $data['address'] = $this->address;
        $data['classpath'] = $this->classpath;
        if ($userService->isUser($this->email)) {
            return true;
        }
        if (isset($this->password)) {} else {
            if ($generatePasseword) {
                $data['password'] = bin2hex(openssl_random_pseudo_bytes(4));
            } else {
                $data['password'] = "12345";
            }
        }
        $data['samsarole'] = $this->samsarole;
       // \Application\Controller\Log::getInstance()->AddRow(' USER - validateUser ' . json_encode($data));
        $ret = $userService->validateOrCreateUser($this, $data);
       // \Application\Controller\Log::getInstance()->AddRow(' USER - validateUser2 ' . $ret);
        
        if ($ret != false) {
            $this->password = $data['password'];
            $this->update();
            return $data;
        }
        return false;
    }

    public function sendEMAIL($emailSubject, $emailMessage)
    {
        $emailTO = $this->email;
        
        if (! isset($emailTO) || $emailTO == null) {
            return "No email address";
        }
        
        $data = array(
            'to' => $emailTO,
            'from' => "noreply@samsasoftware.com",
            'fromName' => $this->name,
            'subject' => $emailSubject,
            'htmlContent' => $emailMessage,
            'cc' => array()
        );
        
        $this->hasEmail = "yes";
        $this->update();
        $mailService = new \Application\Service\MailService();
        $mailService->callEmail($data);
        
        return "Email sent";
    }

    public function createUserAndNotifyApp($subject, $extraText, $sendNotificationsToUser = true, $generatePasseword = true)
    {
        $mongoObjectFactory = new \Application\Controller\MongoObjectFactory();
        
        $loginData = '';
        // validate customer - so it gets an account if it does not have one
        $ret = $this->validateUser($generatePasseword);
        if ($sendNotificationsToUser) {
            if ($ret == true) {
                $loginData = " Here are your Samsa app access details: ";
                $loginData = $loginData . " Username: " . $ret['email'];
                $loginData = $loginData . " Password: " . $ret['password'];
                $loginData = ' Please change your password in the application. ';
            } else {
                $loginData = " Here are your Samsa app access details: ";
                $loginData = $loginData . " Username: " . $ret['email'];
                $loginData = ' Account is already created for this address. ';
            }
            // and send him an email
            $emailMessage = "Dear " . $this->name . " " . $extraText . " ,
             You can user now our SAMSA app from any Web Store (Apple or Android).
              " . $loginData . " Kind regards,
             Samsa Webmaster -- info@samsa.nl";
            
            $this->sendEMAIL($subject, $emailMessage);
        }
    }

    public function getName()
    {
        if (isset($this->name))
            return $this->name . " " . $this->lastName;
        
        return $this->lastName;
    }
}