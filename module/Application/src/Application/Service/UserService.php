<?php
namespace Application\Service;

use Application\Document\PendingUser;
use Application\Document\ResetToken;
use Application\Document\Setting;
use Application\Document\Template;
use Application\Controller\ServiceLocatorFactory;

class UserService extends Service
{

    public function validateToken($token)
    {
        // $auth = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
        $dm = $this->getServiceLocator()->get('doctrine.documentmanager.odm_default');
        // $identity = $auth->getIdentity();
        $sessionMgr = new \Authentication\Controller\SessionManager();
        
        return $sessionMgr->validateToken($dm, $token);
    }

    public function loginAction($UserName, $Password)
    {
        $authService = $this->getServiceLocator()->get('doctrine.authenticationservice.odm_default');
        $adapter = $authService->getAdapter();
        $adapter->setIdentityValue($UserName); // i am using email
        $adapter->setCredentialValue($Password);
        
        $authResult = $authService->authenticate();
        if ($authResult->isValid()) {
            $auth = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
            $dm = $this->getServiceLocator()->get('doctrine.documentmanager.odm_default');
            $identity = $auth->getIdentity();
            $user = $dm->getRepository("\\Application\\Document\\User")->findOneBy(array(
                "id" => $identity['id']
            ));
            $sessionMgr = new \Authentication\Controller\SessionManager();
            $session = $sessionMgr->saveUserSession($dm, $user);
            
            $data = array(
                'email' => $user->getEmail(),
                'last_name' => $user->getLastName(),
                'name' => $user->getName(),
                'role' => $user->getUserRole()->getRole(),
                'workspaceId' => (string) $user->getOrganization()
                    ->getActiveWorkspace()
                    ->getId(),
                // 'workspaceId' => "5743556336dd8117280041ab",
                'Success' => true,
                'token' => $session->getId()
            );
            return $session->getId();
        } else {
            return 'wrong username or password';
        }
    }

    public function deleteeUser($data)
    {
        try {
            $dm = $this->serviceLocator->get('doctrine.documentmanager.odm_default');
            
            $userChecked = $dm->getRepository("\\Application\\Document\\User")->findOneBy(array(
                "email" => $data['email']
            ));
            if (isset($userChecked)) {
                $userChecked->remove();
                $userChecked->flush();
                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    public function isUser($email)
    {
        try {
            $dm = $this->serviceLocator->get('doctrine.documentmanager.odm_default');
            
            $userChecked = $dm->getRepository("\\Application\\Document\\User")->findOneBy(array(
                "email" => $email
            ));
            if (isset($userChecked)) {
                return true;
            }
        } catch (\Exception $e) {
            return false;
        }
        return false;
    }


    public function signUpUserByEmail($data, $organizationDbName)
    {
        try {
            $dm = $this->serviceLocator->get('doctrine.documentmanager.odm_default');
            
            $user = $dm->getRepository("\\Application\\Document\\User")->findOneBy(array(
                "email" => $data['email']
            ));
            
            $organization = $dm->getRepository("\\Application\\Document\\Organization")->findOneBy(array(
                "dbname" => $organizationDbName
            ));
            $samsaOrg = $dm->getRepository("\\Application\\Document\\Organization")->findOneBy(array(
                "dbname" => 'Samsa'
            ));
            $_SESSION['dbname'] = 'Samsa';
            $_SESSION['organization'] = 'Samsa';
            $translator = $this->getServiceLocator()->get('translator');

            // if there is an user assigned to a organization
            if ($user) {
                $organizations = json_decode($user->getOrganizationList());
                
                $workSpaceId = $samsaOrg->getActiveWorkspace()->getId();
                
                $serviceLocator = $this->getServiceLocator();
                $server = $serviceLocator->get('Request')
                    ->getUri()
                    ->getScheme() . '://' . $serviceLocator->get('Request')
                    ->getUri()
                    ->getHost();
                // send already register
                if (in_array($organization->getId(), $organizations)) {
                    
                    $url = $server . $serviceLocator->get('ViewHelperManager')
                        ->get('url')
                        ->__invoke('user', array(
                        'action' => 'sendMailReset'
                    ));
                    
                    $organizationsObj = $dm->createQueryBuilder("\\Application\\Document\\Organization")
                        ->field('id')
                        ->in($organizations)
                        ->getQuery()
                        ->execute();
                    $organizationsName = '';
                    foreach ($organizationsObj as $org) {
                        $organizationsName .= $org->getName() . ',';
                    }
                    $organizationsName = substr($organizationsName, 0, - 1);
                    $dataEmail = array(
                        'to' => $data['email'],
                        'from' => $organization->getEmail(),
                        'fromName' => $organization->getName(),
                        'main' => array(
                            'url' => $url,
                            'username' => $user->getEmail(),
                            'organization' => $organization->getName(),
                            'organizations' => $organizationsName
                        ),
                        'lists' => array()
                    );
                    $mailService = new MailService();
                    $mailService->sendEmailCustomData($dataEmail, Template::ALREADY_PART_OF_ORGANIZATION, $workSpaceId);
                    return $translator->translate('succesfully_sign_up');
                } else {
                    // send registered to org email
                    $user->addOrganization($organization);
                    $dm->persist($user);
                    $dm->flush();
                    $organization->getActiveWorkspace()->createModeledUser($user->getEmail());
                    $url = $server . $serviceLocator->get('ViewHelperManager')
                        ->get('url')
                        ->__invoke('user', array(
                        'action' => 'sendMailReset'
                    ));
                    $dataEmail = array(
                        'to' => $data['email'],
                        'from' => $organization->getEmail(),
                        'fromName' => $organization->getName(),
                        'main' => array(
                            'url' => $url,
                            'username' => $user->getEmail(),
                            'organization' => $organization->getName()
                        ),
                        'lists' => array()
                    );
                    $mailService = new MailService();
                    $mailService->sendEmailCustomData($dataEmail, Template::REGISTERED_TO_ORGANIZATION, $workSpaceId);
                    return $translator->translate('succesfully_sign_up');
                }
            } else {

                $result = $this->generateTokenCreateUser($data);
                return $result;

            }
        } catch (\Exception $e) {
            throw $e;
        }

    }

    /**
     *
     * @param unknown $data
     *            $user->setName($data['name']);
     *            $user->setLastName($data['lastname']);
     *            $user->setEmail($data['email']);
     *            $user->setAddress($data['address']);
     *            $user->setPhone($data['phone']);
     * @param unknown $password            
     * @param unknown $orgId            
     * @param unknown $roleId            
     */
    public function createUser($data, $password, $orgId, $roleId)
    {
        try {
            $dm = $this->serviceLocator->get('doctrine.documentmanager.odm_default');
            
            $userChecked = $dm->getRepository("\\Application\\Document\\User")->findOneBy(array(
                "email" => $data['email']
            ));
            if (isset($userChecked)) {
                return false;
            } else {
                
                $user = new \Application\Document\User();
                if (isset($data['password'])) {
                    $user->setPassword(md5($data['password']));
                } else {
                    $user->setPassword(md5($password));
                }
                $sendMail = true;
                
                $organization = $dm->getRepository("\\Application\\Document\\Organization")->findOneBy(array(
                    "id" => $orgId
                ));
                
                $userRole = $dm->getRepository("\\Application\\Document\\UserRole")->findOneBy(array(
                    "id" => $roleId
                ));
                // \Application\Controller\Log::getInstance()->AddRow(' USERSERVICE add user - add samsa role user id: ' . json_encode($data));

                $mongoObjectFactory = new \Application\Controller\MongoObjectFactory();
                $samsaRole = $mongoObjectFactory->findObjectByCriteria('Samsarole', array(
                    'default' => 'true'
                ));
                $samsaRoleId = null;
                if ($samsaRole) {
                    $samsaRoleId = (string)$samsaRole['_id'];
                }

                $user->setName($data['name']);
                $user->setLastName($data['lastname']);
                $user->setEmail($data['email']);
                $user->setAddress($data['address']);
                $user->setPhone($data['phone']);
                $user->addOrganization($organization, $samsaRoleId);
                $user->setOrganization($organization);
                $user->setUserRole($userRole);
                $dm->persist($user);
                $dm->flush();
                $errorTxt = "";
                
                $mongoObjectFactory = new \Application\Controller\MongoObjectFactory();
                $organizationS = $mongoObjectFactory->findObject('Organization', $orgId);
                $errorTxt = $organizationS->addParentUser($user->getId());
                
                if ($sendMail && $organization != null) {
                    try {
                        $mailService = new \Application\Service\MailService();
                        $mailData = array(
                            'to' => $user->getEmail(),
                            'from' => $organization->getEmail(),
                            'fromName' => $organization->getName(),
                            'main' => array(
                                'password' => $password,
                                'username' => $user->getEmail(),
                                'organization' => $organization->getName()
                            ),
                            'lists' => array()
                        );
                        $sing = \Application\Document\Helper\NotificationCenter::getInstance();
                        $sing->setClassPath($organization->getClassPath());
                        
                        $mailService->sendEmailCustomData($mailData, Template::GENERATE_PASSWORD, $organization->getActiveWorkspace()
                            ->getId());
                    } catch (\Exception $e) {
                        // LOG ERRROR
                        print_r($e->getMessage());
                        exit();
                    }
                }
                // if the organization has settings
                if ($organization != null) {
                    if ($organization->getSettings() != null) {
                        foreach ($organization->getSettings() as $setting) {
                            $newSetting = new Setting();
                            $newSetting->setUser($user);
                            $newSetting->setState($setting->getState());
                            $newSetting->setViewId($setting->getViewId());
                            $newSetting->setUserId($user->getId());
                            $newSetting->setGridId($setting->getGridId());
                            $dm->persist($newSetting);
                            $dm->flush();
                        }
                    }
                }
                return $user->getId();
            }
        } catch (\Exception $e) {
            \Application\Controller\Log::getInstance()->AddRow(' USERSERVICE EXCEPTION - add samsa role user id: ' . json_encode($e));
            return false;
        }
    }

    public function validateOrCreateUser($object, $data)
    {
        // find the user role ID
        if (isset($_SESSION['dbname'])) {
            $serviceLocator = ServiceLocatorFactory::getInstance();
            $dm = $serviceLocator->get('doctrine.documentmanager.odm_default');
            $userRoleObjectInstance = $dm->getRepository("\\Application\\Document\\UserRole")->findOneBy(array(
                "role" => 'user'
            ));
            $userRoleID = $userRoleObjectInstance->getId();
            
            // find the organization ID
            $mongoObjectFactory = new \Application\Controller\MongoObjectFactory();
            $organizationObjectInstance = $mongoObjectFactory->findObjectByCriteria('Organization', array(
                'dbname' => $_SESSION['dbname']
            ));
            // \Application\Controller\Log::getInstance()->AddRow(' USERSERVICEq - add samsa role user id: ' . json_encode($organizationObjectInstance));
            
            $organizationID = $organizationObjectInstance['_id'];
            
            $this->createUser($data, $data['password'], $organizationID, $userRoleID);
            
            // TODO make this finding of the user id more safe - @Reli can we have createUser returning the ID
            // get the id of the new user
            $userObjectJSON = $mongoObjectFactory->findObjectByCriteria('User', array(
                'email' => $data['email']
            ));
            
            $userObjectInstance = $mongoObjectFactory->findObjectInstance('User', (string) $userObjectJSON['_id']);
            if (isset($userObjectInstance)) {
                if (isset($data['samsarole']) && strlen($data['samsarole']) > 1) {
                    $userObjectInstance->addSamsaRole($data['samsarole']);
                    $userObjectInstance->copySettings($data['samsarole']);
                }
                $userObjectInstance = $mongoObjectFactory->findObjectByCriteria('User', array(
                    
                    'email' => $data['email']
                ));
                \Application\Controller\Log::getInstance()->AddRow(' USERSERVICE - add samsa role user id: ' . json_encode($userObjectInstance));
                
                // link the driver to the user
                $reference = array();
                $reference['$ref'] = "users";
                $reference['$id'] = (string) $userObjectInstance['_id'];
                $object->addReferenceObject("users", $reference);
                return $data['email'];
            } else {
                return false;
            }
        }
        return true;
    }

    public function doThis()
    {}

    public function changePassword($data)
    {
        try {
            $dm = $this->getServiceLocator()->get('doctrine.documentmanager.odm_default');
            
            $session = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
            $identity = $session->getIdentity();
            
            $user = $dm->getRepository("\\Application\\Document\\User")->findOneBy(array(
                "id" => $identity['id']
            ));
            
            if (md5($data['current_password']) == $user->getPassword()) {
                $user->setPassword(md5($data['new_password']));
                $dm->persist($user);
                $dm->flush();
                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function updatePassword($data)
    {
        try {
            $dm = $this->getServiceLocator()->get('doctrine.documentmanager.odm_default');
            
            $user = $dm->getRepository("\\Application\\Document\\User")->findOneBy(array(
                "id" => $data['userId']
            ));
            if ($user) {
                $user->setPassword(md5($data['new_password']));
                $dm->persist($user);
                $dm->flush();
                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function generateToken($email)
    {
        try {
            $serviceLocator = $this->getServiceLocator();
            $dm = $this->getServiceLocator()->get('doctrine.documentmanager.odm_default');
            $translator = $this->getServiceLocator()->get('translator');
            $user = $dm->getRepository("\\Application\\Document\\User")->findOneBy(array(
                "email" => $email
            ));
            if ($user != null) {
                $token = md5(time() . $user->getId());
                if ($user->getResetToken() != null) {
                    $user->getResetToken()->setUserId($user->getId());
                    $user->getResetToken()->setToken($token);
                    $user->getResetToken()->setDate(date('Y-m-d H:i:s'));
                } else {
                    $resetToken = new ResetToken();
                    $resetToken->setUserId($user->getId());
                    $resetToken->setDate(date('Y-m-d h:i:s'));
                    $resetToken->setToken($token);
                    $user->setResetToken($resetToken);
                }
                $dm->persist($user);
                $dm->flush();
                $_SESSION['organization'] = $user->getOrganization()->getClassPath();
                $_SESSION['dbname'] = $user->getOrganization()->getDbname();
                $workSpaceId = $user->getOrganization()
                    ->getActiveWorkspace()
                    ->getId();
                $server = $serviceLocator->get('Request')
                    ->getUri()
                    ->getScheme() . '://' . $serviceLocator->get('Request')
                    ->getUri()
                    ->getHost();
                $url = $server . $serviceLocator->get('ViewHelperManager')
                    ->get('url')
                    ->__invoke('user', array(
                    'action' => 'resetPassword',
                    'id' => $token
                ));
                $dataEmail = array(
                    'to' => $email,
                    'from' => $user->getOrganization()->getEmail(),
                    'fromName' => $user->getOrganization()->getName(),
                    'main' => array(
                        'url' => $url,
                        'username' => $user->getName() . " " . $user->getLastName(),
                        'organization' => $user->getOrganization()->getName()
                    ),
                    'lists' => array()
                );
                $mailService = new MailService();
                $mailService->sendEmailCustomData($dataEmail, Template::RESET_PASSWORD, $workSpaceId);
                return $translator->translate('generate_link_reset_password');
            } else {
                throw new \Exception($translator->translate('email_do_not_exist'));
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function generateTokenCreateUser($data) {
        try {
            $serviceLocator = $this->getServiceLocator();
            $dm = $this->getServiceLocator()->get('doctrine.documentmanager.odm_default');
            $translator = $this->getServiceLocator()->get('translator');
            $pendingUser = $dm->getRepository("\\Application\\Document\\PendingUser")->findOneBy(array(
                "email" => $data['email']
            ));
            $token = md5(time() . $data['email']);
            if ($pendingUser != null) {
                $pendingUser->setToken($token);
                $pendingUser->setEmail($data['email']);
                $pendingUser->setDate(date('Y-m-d H:i:s'));
                $pendingUser->setData(json_encode($data));
            } else {
                $pendingUser = new PendingUser();
                $pendingUser->setToken($token);
                $pendingUser->setEmail($data['email']);
                $pendingUser->setDate(date('Y-m-d H:i:s'));
                $pendingUser->setData(json_encode($data));
            }
                $dm->persist($pendingUser);
                $dm->flush();
                $_SESSION['dbname'] = $data['organization'];
                $_SESSION['organization'] = $data['organization'];
                $samsaOrg = $dm->getRepository("\\Application\\Document\\Organization")->findOneBy(array(
                    "dbname" => $data['organization']
                ));
                $workSpaceId = $samsaOrg
                    ->getActiveWorkspace()
                    ->getId();
                $server = $serviceLocator->get('Request')
                        ->getUri()
                        ->getScheme() . '://' . $serviceLocator->get('Request')
                        ->getUri()
                        ->getHost();
                $url = $server . $serviceLocator->get('ViewHelperManager')
                        ->get('url')
                        ->__invoke('user', array(
                            'action' => 'createAccount',
                            'id' => $token
                        ));
                $dataEmail = array(
                    'to' => $data['email'],
                    'from' => $samsaOrg->getEmail(),
                    'fromName' => $samsaOrg->getName(),
                    'main' => array(
                        'url' => $url,
                        'username' => $data['name']. ' '.$data['lastName'],
                        'organization' => $samsaOrg->getName()
                    ),
                    'lists' => array()
                );
                $mailService = new MailService();
                $mailService->sendEmailCustomData($dataEmail, Template::CONFIRM_ACCOUNT, $workSpaceId);
                return $translator->translate('generate_link_create_account');

        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function checkToken($token)
    {
        $dm = $this->getServiceLocator()->get('doctrine.documentmanager.odm_default');
        $result = $dm->getRepository("\\Application\\Document\\ResetToken")->findOneBy(array(
            "token" => $token
        ));
        return $result;
    }

    public function checkCreateAccountToken($token) {
        $dm = $this->getServiceLocator()->get('doctrine.documentmanager.odm_default');
        $result = $dm->getRepository("\\Application\\Document\\PendingUser")->findOneBy(array(
            "token" => $token
        ));
        return $result;
    }
}