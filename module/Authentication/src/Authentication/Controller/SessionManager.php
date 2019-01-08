<?php
namespace Authentication\Controller;

use \Application\Document\Session;

class SessionManager
{

    /*
     * $sessionMgr = new SessionManager();
     *
     * $sessionMgr = new SessionManager();
     * if (! $sessionMgr->validateToken($dm, $identity, 'WhateverIdYouHave')) {
     * // \Application\Controller\Log::getInstance()->AddRow('REQUEST## ' . json_encode($session));
     * $session = $sessionMgr->saveUserSession($dm, $user);
     * }
     * // $session->getId() has the TOKEN ID!!
     * $sessionX = $sessionMgr->validateToken($dm, $identity, $session->getId());
     * \Application\Controller\Log::getInstance()->AddRow('REQUEST## ' . json_encode($sessionX));
     */
    public function saveSession($serviceLocator, $identity)
    {
        // $request = $this->getRequest();
        $result = false;
        try {
            
            // $data = $request->getPost()->toArray();
            // $dm = $this->getServiceLocator()->get('doctrine.documentmanager.odm_default');
            $user = $serviceLocator->getRepository("\\Application\\Document\\User")->findOneBy(array(
                "id" => $identity['id']
            ));
            $result = $this->saveUserSession($serviceLocator, $user);
        } catch (\Exception $e) {
            $result = false;
        }
        return $result;
    }

    public function saveUserSession($serviceLocator, $user)
    {
        $_SESSION['organization'] = $user->getOrganization()->getClasspath();
        $_SESSION['dbname'] = $user->getOrganization()->getDbname();
        $_SESSION['workspaceId'] = $user->getOrganization()
            ->getActiveWorkspace()
            ->getId();
        $_SESSION['userId'] = $user->getId();
        
        $_SESSION['username'] = $user->getEmail();
        $result = true;
        try {
            // $dm = $this->getServiceLocator()->get('doctrine.documentmanager.odm_default');
            $qb = $serviceLocator->createQueryBuilder('\\Application\\Document\\Session');
            $organization = $user->getOrganization()->getName();
            $classpath = $user->getOrganization()->getClasspath();
            $activeWorkspaceId = $user->getOrganization()
                ->getActiveWorkspace()
                ->getId();
            // \Application\Controller\Log::getInstance()->AddRow('REQUEST1 ' . json_encode($activeWorkspaceId));
            $qb->field('user')->references($user);
            $qb->field('organization')->equals($organization);
            // $qb->field('workspaceId')->equals($activeWorkspaceId);
            // \Application\Controller\Log::getInstance()->AddRow('REQUEST2 ' . json_encode($organization));
            
            $settings = $qb->getQuery()->getSingleResult();
            // \Application\Controller\Log::getInstance()->AddRow('REQUEST3 ' . json_encode($settings));
            if ($settings == null) {
                $setting = new Session();
                $setting->setUser($user);
                $setting->setOrganization($organization);
                $setting->setUserId($user->getId());
                $setting->setWorkspaceId($activeWorkspaceId);
                $setting->setClasspath($classpath);
                $serviceLocator->persist($setting);
                
                $serviceLocator->flush();
            } else {
                $setting = $settings;
            }
        } catch (\Exception $e) {
            $setting = false;
        }
        return $setting;
    }

    public function getSession($serviceLocator, $identity, $token = '')
    {
        // $dm = $this->getServiceLocator()->get('doctrine.documentmanager.odm_default');
        $user = $serviceLocator->getRepository("\\Application\\Document\\User")->findOneBy(array(
            "id" => $identity['id']
        ));
        
        $qb = $serviceLocator->createQueryBuilder('\\Application\\Document\\Session');
        
        $qb->field('id')->equals($token);
        $session = $qb->getQuery()->getSingleResult();
        
        return $session;
    }

    public function getSessionOnToken($serviceLocator, $token)
    {
        $qb = $serviceLocator->createQueryBuilder('\\Application\\Document\\Session');
        $qb->field('id')->equals($token);
        $session = $qb->getQuery()->getSingleResult();
        // \Application\Controller\Log::getInstance()->AddRow('VALIDATE TOKEN ' . json_encode($session->getUser()));
        if ($session == null) {
            return false;
        } else {
            
            return $session;
        }
        
        return false;
    }

    public function validateToken($serviceLocator, $token)
    {
        $qb = $serviceLocator->createQueryBuilder('\\Application\\Document\\Session');
        $qb->field('id')->equals($token);
        $session = $qb->getQuery()->getSingleResult();
        
        if ($session == null) {
            return false;
        } else {
            
            $_SESSION['organization'] = $session->getClasspath();
            $_SESSION['workspaceId'] = $session->getWorkspaceId();
            $_SESSION['userId'] = $session->getUserId();
            $_SESSION['username'] = $session->getUser()->getEmail();
            return true;
        }
        return false;
    }
}

?>