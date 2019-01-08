<?php
namespace Application\Controller;

use Application\Document\Template;
use Application\Document\User;
use Application\Document\UserRole;
use Application\Service\MailService;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Application\Document\Setting;
use Application\Service\UserService;

class UserController extends AbstractActionController
{

    public function listAction()
    {
        $dm = $this->getServiceLocator()->get('doctrine.documentmanager.odm_default');
        
        $session = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
        $identity = $session->getIdentity();
        $user = $dm->getRepository("\\Application\\Document\\User")->findOneBy(array(
            "id" => $identity['id']
        ));
        $qb = $dm->createQueryBuilder('\Application\Document\User', 'u');
        $qb->field('id')->notEqual(new \MongoId($identity['id']));
        if ($user->getOrganization() != null && $user->getUserRole()->getRole() != UserRole::ROLE_SUPER_ADMIN) {
            $qb->field('organization.id')->equals($user->getOrganization()
                ->getId());
        }
        // $qb->where("function() { return this.userRole.role == 'admin'; }");
        $users = $qb->getQuery();
        
        return new ViewModel(array(
            'flashMessages' => $this->flashMessenger(),
            'users' => $users
        ));
    }

    public function addAction()
    {
        $serviceLocator = $this->getServiceLocator();
        $form = $serviceLocator->get('\Application\Form\AddUser');
        
        $id = $this->params()->fromRoute('id', 0);
        $dm = $this->getServiceLocator()->get('doctrine.documentmanager.odm_default');
        $user = $dm->getRepository("\\Application\\Document\\User")->findOneBy(array(
            "id" => $id
        ));
        $oldEmail = '';
        $add = true;
        if ($user != null) {
            $add = false;
            $form->get('name')->setValue($user->getName());
            $form->get('lastname')->setValue($user->getLastName());
            $form->get('email')->setValue($user->getEmail());
            $oldEmail = $user->getEmail();
            $form->get('id')->setValue($user->getId());
            $form->get('password')->setValue('');
            $form->get('phone')->setValue($user->getPhone());
            $form->get('address')->setValue($user->getAddress());
            $form->get('deleted')->setValue($user->getDeleted());
            if ($user->getOrganization() != null) {
                $form->get('organization')->setValue($user->getOrganization()
                    ->getId());
            }
            $form->get('role')->setValue($user->getUserRole()
                ->getId());
        }
        
        return new ViewModel(array(
            'add' => $add,
            'oldEmail' => $oldEmail,
            'form' => $form,
            'flashMessages' => $this->flashMessenger()
        ));
    }

    public function saveAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            try {
                $data = $request->getPost()->toArray();
                $dm = $this->getServiceLocator()->get('doctrine.documentmanager.odm_default');

                $session = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
                $identity = $session->getIdentity();
                $currentUser = $dm->getRepository("\\Application\\Document\\User")->findOneBy(array(
                    "id" => $identity['id']
                ));
                $sendMail = false;
                if (isset($data['id']) && ! empty($data['id'])) {
                    $user = $dm->getRepository("\\Application\\Document\\User")->findOneBy(array(
                        "id" => $data['id']
                    ));
                } else {
                    $user = new \Application\Document\User();
                    $user->setPassword(md5($data['password']));
                    $sendMail = true;
                }
                
                $organization = $dm->getRepository("\\Application\\Document\\Organization")->findOneBy(array(
                    "id" => $data['organization']
                ));
                $userRole = $dm->getRepository("\\Application\\Document\\UserRole")->findOneBy(array(
                    "id" => $data['role']
                ));

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
                if (isset($data['id']) && ! empty($data['id'])) {} else {
                    $_SESSION['organization'] = $organization->getClassPath();
                    $errorTxt = $organization->addParentUser($user->getId());
                    $_SESSION['organization'] = $currentUser->getOrganization()->getClassPath();
                }

                if ($sendMail && $organization != null) {
                    try {
                        $serviceLocator = $this->getServiceLocator();
                        $server = $serviceLocator->get('Request')
                                ->getUri()
                                ->getScheme() . '://' . $serviceLocator->get('Request')
                                ->getUri()
                                ->getHost();
                        $url = $server . $serviceLocator->get('ViewHelperManager')
                                ->get('url')
                                ->__invoke('user', array(
                                    'action' => 'sendMailReset'
                                ));
                        $url2 = $server . $serviceLocator->get('ViewHelperManager')
                                ->get('url')
                                ->__invoke('auth', array(
                                    'action' => 'login'
                                ));
                        $mailService = new \Application\Service\MailService();
                        $mailData = array(
                            'to' => $user->getEmail(),
                            'from' => $organization->getEmail(),
                            'fromName' => $organization->getName(),
                            'main' => array(
                                'password' => $data['password'],
                                'username' => $user->getEmail(),
                                'organization' => $organization->getName(),
                                'url2' => $url2,
                                'url' => $url
                            ),
                            'lists' => array()
                        );
                        $sing = \Application\Document\Helper\NotificationCenter::getInstance();
                        $sing->setClassPath($organization->getClassPath());

                        $_SESSION['organization'] = $organization->getClassPath();
                        $_SESSION['dbname'] = $organization->getDbname();
                        $mailService->sendEmailCustomData($mailData, Template::GENERATE_PASSWORD, $organization->getActiveWorkspace()->getId());
                        $_SESSION['organization'] = $currentUser->getOrganization()->getClassPath();
                    } catch (\Exception $e) {
                        // LOG ERRROR
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
                $_SESSION['organization'] = $organization->getClassPath();
                $activeWorkspace = $organization->getActiveWorkspace();
                $activeWorkspace->createModeledUser($data['email']);
                $_SESSION['organization'] = $currentUser->getOrganization()->getClassPath();
                $this->flashMessenger()->addSuccessMessage('An user was successfully created !');
                return $this->redirect()->toRoute('user', array(
                    'action' => 'list'
                ));
            } catch (\Exception $e) {
                $this->flashMessenger()->addErrorMessage('An error has occurred!:: ' . $errorTxt);
                $this->redirect()->toRoute('user', array(
                    'action' => 'add'
                ));
            }
        }
    }

    public function deleteAction()
    {
        try {
            $serviceLocator = $this->getServiceLocator();
            $form = $serviceLocator->get('\Application\Form\AddUser');
            
            $id = $this->params()->fromRoute('id', 0);
            $dm = $this->getServiceLocator()->get('doctrine.documentmanager.odm_default');
            $user = $dm->getRepository("\\Application\\Document\\User")->findOneBy(array(
                "id" => $id
            ));
            
            if ($user != null) {
                if ($user->getDeleted() == 0) {
                    $user->setDeleted(1);
                } else {
                    $user->setDeleted(0);
                }
                $dm->persist($user);
                $dm->flush();
                $this->flashMessenger()->addSuccessMessage('The user status was successfully changed !');
            } else {
                $this->flashMessenger()->addSuccessMessage('The user do not exist !');
            }
            return $this->redirect()->toRoute('user', array(
                'action' => 'list'
            ));
        } catch (\Exception $e) {
            $this->flashMessenger()->addErrorMessage('An error has occurred!');
            $this->redirect()->toRoute('user', array(
                'action' => 'list'
            ));
        }
    }

    public function profileAction()
    {
        $dm = $this->getServiceLocator()->get('doctrine.documentmanager.odm_default');
        
        $form = $this->getServiceLocator()->get('\Application\Form\AddUser');
        
        $session = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
        $identity = $session->getIdentity();
        
        $user = $dm->getRepository("\\Application\\Document\\User")->findOneBy(array(
            "id" => $identity['id']
        ));
        
        $form->get('name')->setValue($user->getName());
        $form->get('lastname')->setValue($user->getLastName());
        $form->get('email')->setValue($user->getEmail());
        $form->get('id')->setValue($user->getId());
        $form->get('phone')->setValue($user->getPhone());
        $form->get('address')->setValue($user->getAddress());
        
        return new ViewModel(array(
            'form' => $form,
            'flashMessages' => $this->flashMessenger()
        ));
    }

    public function saveprofileAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            try {
                $data = $request->getPost()->toArray();
                $dm = $this->getServiceLocator()->get('doctrine.documentmanager.odm_default');
                
                $user = $dm->getRepository("\\Application\\Document\\User")->findOneBy(array(
                    "id" => $data['id']
                ));
                
                $user->setName($data['name']);
                $user->setLastName($data['lastname']);
                $user->setEmail($data['email']);
                $user->setAddress($data['address']);
                $user->setPhone($data['phone']);
                
                $dm->persist($user);
                $dm->flush();
                $this->flashMessenger()->addSuccessMessage('Your profile was successfully updated !');
                return $this->redirect()->toRoute('user', array(
                    'action' => 'profile'
                ));
            } catch (\Exception $e) {
                $this->flashMessenger()->addErrorMessage('An error has occurred!');
                $this->redirect()->toRoute('user', array(
                    'action' => 'profile'
                ));
            }
        }
    }

    public function passwordAction()
    {
        return new ViewModel(array(
            'flashMessages' => $this->flashMessenger()
        ));
    }

    public function savepasswordAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost()->toArray();
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
                $this->flashMessenger()->addSuccessMessage('Your password has changed !');
                return $this->redirect()->toRoute('user', array(
                    'action' => 'password'
                ));
            } else {
                $this->flashMessenger()->addErrorMessage('The current password does not match!');
                $this->redirect()->toRoute('user', array(
                    'action' => 'password'
                ));
            }
        }
    }

    public function setDefaultSettingsAction()
    {
        $dm = $this->getServiceLocator()->get('doctrine.documentmanager.odm_default');
        
        $session = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
        $identity = $session->getIdentity();
        
        $user = $dm->getRepository("\\Application\\Document\\User")->findOneBy(array(
            "id" => $identity['id']
        ));
        
        try {
            $qb = $dm->createQueryBuilder('\\Application\\Document\\Setting');
            
            $qb->field('user')->references($user);
            
            $settings = $qb->getQuery()->execute();
            
            $organization = $dm->getRepository("\\Application\\Document\\Organization")->findOneBy(array(
                "id" => $user->getOrganization()
                    ->getId()
            ));
            
            foreach ($settings as $setting) {
                $organization->addSetting($setting);
            }
            
            $dm->persist($organization);
            $dm->flush();
            
            $this->flashMessenger()->addSuccessMessage('Your settings was set as default!');
            return $this->redirect()->toRoute('user', array(
                'action' => 'profile'
            ));
        } catch (\Exception $e) {
            $this->flashMessenger()->addErrorMessage('An error has occured!');
            $this->redirect()->toRoute('user', array(
                'action' => 'profile'
            ));
        }
    }

    public function checkemailAction()
    {
        $request = $this->getRequest();
        $result = true;
        if ($request->isPost()) {
            $data = $request->getPost()->toArray();
            $dm = $this->getServiceLocator()->get('doctrine.documentmanager.odm_default');
            if ($data['oldEmail'] != $data['email']) {
                $user = $dm->getRepository("\\Application\\Document\\User")->findOneBy(array(
                    "email" => $data['email']
                ));
                if ($user != null) {
                    $result = false;
                }
            }
        }
        
        if (! $result) {
            echo (json_encode(false));
            exit();
        } else {
            echo (json_encode(true));
            exit();
        }
    }

    public function sendMailResetAction()
    {
        $this->layout('layout/layoutLogin');

        return new ViewModel(array(
            'flashMessages' => $this->flashMessenger()
        ));
    }

    public function generateTokenAction()
    {
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $data = $request->getPost()->toArray();
                $userService = new UserService();
                $result = $userService->generateToken($data['email']);
                $this->flashMessenger()->addSuccessMessage($result);
                return $this->redirect()->toRoute('user', array(
                    'action' => 'sendMailReset'
                ));
            }
        } catch (\Exception $e) {
            $this->flashMessenger()->addErrorMessage($e->getMessage());
            return $this->redirect()->toRoute('user', array(
                'action' => 'sendMailReset'
            ));
        }


    }

    public function resetPasswordAction()
    {
        $token = $this->params()->fromRoute('id', null);
        $userService = new UserService();
        $service = $this->getServiceLocator();
        $translator = $service->get('translator');
        $resetToken = $userService->checkToken($token);
        $userId = null;
        if ($resetToken) {
            $userId = $resetToken->getUserId();
        } else {
            $this->flashMessenger()->addErrorMessage($translator->translate('token_expired'));
            return $this->redirect()->toRoute('user', array(
                'action' => 'sendMailReset'
            ));
        }

        return new ViewModel(array(
            'flashMessages' => $this->flashMessenger(),
            'userId' => $userId
        ));

    }

    public function createAccountAction() {
        try {
            $token = $this->params()->fromRoute('id', null);
            $userService = new UserService();
            $service = $this->getServiceLocator();
            $translator = $service->get('translator');
            $resetToken = $userService->checkCreateAccountToken($token);
            if ($resetToken) {
                $data = json_decode($resetToken->getData(), true);
                $dm = $this->getServiceLocator()->get('doctrine.documentmanager.odm_default');
                $role = $dm->getRepository("\\Application\\Document\\UserRole")->findOneBy(array(
                    "role" => 'user'
                ));
                $organization = $dm->getRepository("\\Application\\Document\\Organization")->findOneBy(array(
                    "dbname" => $data['organization']
                ));
                $lastDbName = isset($_SESSION['dbname']) ? $_SESSION['dbname']: null;
                $lastOrganization = isset($_SESSION['organization']) ? $_SESSION['organization'] : null;
                $_SESSION['dbname'] = $organization->getDbname();
                $_SESSION['organization'] = $organization->getDbname();
                $data['phone'] = '';
                $data['address'] = '';
                $result = $userService->createUser($data, substr(md5(mt_rand()), 0, 7),
                    $organization->getId(), $role->getId());
                $organization->getActiveWorkspace()->createModeledUser($data['email']);
                $_SESSION['dbname'] = $lastDbName;
                $_SESSION['organization'] = $lastOrganization;
                $dm->remove($resetToken);
                $dm->flush();
                $this->flashMessenger()->addSuccessMessage($translator->translate('activated_user'));
                return $this->redirect()->toRoute('auth', array(
                    'action' => 'samenbeter', 'id' => $data['organization']
                ));
            } else {
                $this->flashMessenger()->addErrorMessage($translator->translate('token_expired'));
                return $this->redirect()->toRoute('auth', array(
                    'action' => 'login'
                ));
            }
        } catch (\Exception $e) {
            $this->flashMessenger()->addErrorMessage($translator->translate('token_expired'));
            return $this->redirect()->toRoute('auth', array(
                'action' => 'login'
            ));
        }
    }

    public function changePasswordAfterResetAction()
    {
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $data = $request->getPost()->toArray();
                $userService = new UserService();
                $result = $userService->updatePassword($data);
                $service = $this->getServiceLocator();
                $translator = $service->get('translator');
                if ($result) {
                    $this->flashMessenger()->addSuccessMessage($translator->translate('password_changed_successfully'));
                    return $this->redirect()->toRoute('auth', array(
                        'action' => 'index'
                    ));
                }
            }
        } catch (\Exception $e) {
            $this->flashMessenger()->addErrorMessage($e->getMessage());
            return $this->redirect()->toRoute('auth', array(
                'action' => 'login'
            ));
        }
    }
}