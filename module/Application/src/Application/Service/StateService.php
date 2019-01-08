<?php
namespace Application\Service;

use Application\Document\Setting;

class StateService extends Service
{

    /**
     *
     * @param
     *            $viewId
     * @param
     *            $componentId
     * @param
     *            $type
     * @return mixed
     */
    public function getState($viewId, $componentId, $type)
    {
        $dm = $this->serviceLocator->get('doctrine.documentmanager.odm_default');
        $session = $this->getSession();
        $identity = $session->getIdentity();
        $user = $dm->getRepository("\\Application\\Document\\User")->findOneBy(array(
            "id" => $identity['id']
        ));
        
        $qb = $dm->createQueryBuilder('\\Application\\Document\\Setting');
        
        $qb->field('viewId')->equals($viewId);
        $qb->field('user')->references($user);
        $qb->field('gridId')->equals($componentId);
        $qb->field('type')->equals($type);
        $setting = $qb->getQuery()->getSingleResult();
        
        if ($setting == null) {
            $setting = new Setting();
        }
        
        $state = $setting->getState();
        return $state;
    }

    public function saveState($viewId, $componentId, $dataIn, $type)
    {
        $dm = $this->serviceLocator->get('doctrine.documentmanager.odm_default');
        $session = $this->getSession();
        $result = true;
        if ($session->hasIdentity()) {
            $identity = $session->getIdentity();
            $user = $dm->getRepository("\\Application\\Document\\User")->findOneBy(array(
                "id" => $identity['id']
            ));
            $result = $this->saveUserState($viewId, $identity, $user, $componentId, $dataIn, $type);
        }
        return $result;
    }

    /**
     *
     * @param
     *            $viewId
     * @param
     *            $identity
     * @param
     *            $user
     * @param
     *            $gridId
     * @param
     *            $data
     * @param
     *            $type
     * @return bool
     */
    public function saveUserState($viewId, $identity, $user, $gridId, $data, $type)
    {
        $result = true;
        try {
            $dm = $this->serviceLocator->get('doctrine.documentmanager.odm_default');
            
            $qb = $dm->createQueryBuilder('\\Application\\Document\\Setting');
            
            $qb->field('viewId')->equals($viewId);
            $qb->field('user')->references($user);
            $qb->field('gridId')->equals($gridId);
            $qb->field('type')->equals($type);
            $setting = $qb->getQuery()->getSingleResult();
            
            if ($setting == null) {
                $setting = new Setting();
            }
            
            $setting->setUser($user);
            $setting->setViewId($viewId);
            $setting->setUserId($identity['id']);
            $setting->setGridId($gridId);
            $setting->setType($type);
            $setting->setState(json_encode($data));
            $dm->persist($setting);
            $dm->flush();
            $mongoObjectFactory = new \Application\Controller\MongoObjectFactory();
            $settingObjectInstance = null;//$mongoObjectFactory->findObjectInstance('Setting', (string) $setting->getId());
            if (isset($settingObjectInstance)) {
                $userObjectInstance = $mongoObjectFactory->findObjectInstance('User', (string) $user->getId());
               // \Application\Controller\Log::getInstance()->AddRow(' raddSamsaRole ' . json_encode($userObjectInstance) . ' value ' . json_encode($user));
                
                if (isset($userObjectInstance) && $userObjectInstance->isSettingHouder()) {
                    $samsaroleDef = $userObjectInstance->readMainSamsaRole();
                    if (isset($samsaroleDef)) {
                       // \Application\Controller\Log::getInstance()->AddRow(' raddSamsaRole2 ' . json_encode($userObjectInstance) . ' value ' . json_encode($samsaroleDef));
                        
                        $samsaroleDef->addSimpleReference("settings",  (string) $setting->getId());
                       // $settingObjectInstance->addSamsaRole($samsaroleDef->name);
                       // $samsaroleDef->update();
                    }
                }
            }

        } catch (\Exception $e) {
            \Application\Controller\Log::getInstance()->AddRow(" Exception -  " . $e->getMessage());
            throw $e;
            $result = false;
        }
        return $result;
    }
}