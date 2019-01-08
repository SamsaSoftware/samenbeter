<?php
namespace Application\Document\Simulator;

use Application\Document\Model;
use Application\Document\WorkspaceTemplate;
use phpDocumentor\Reflection\DocBlock\Tags\PropertyRead;
use Application\Controller\ServiceLocatorFactory;

class Workspace extends WorkspaceTemplate
{

    public $susers = array();

    public $configs = array();

    public static function getRelationType($name)
    {
        $relations = array();
        $relations['susers'] = Model::OWNING_ONE_TO_MANY;
        
        $relations['configs'] = Model::OWNING_ONE_TO_MANY;
        return parent::getExtraRelationTypes($name, $relations);
    }

    public function getIntegrationHandler()
    {
        return null;
    }

    // not used now
    public function newGroup($name, $topic, $tags)
    {
        $json = array();
        $json['name'] = $name;
        $json['topic'] = $topic;
        $json['tags'] = $tags;
        $id = $this->add("Group", $json);
        return "ok";
    }

    public function add($typeC, $json)
    {
        $laf = new \Application\Controller\MongoObjectFactory();
        $typeRelClass = new \ReflectionClass($laf->getClassPath($typeC) . $typeC);
        $nameRel = \strtolower($typeRelClass->getShortName());
        // $id = 0;
        \Application\Controller\Log::getInstance()->AddRow(' add Group >>>>>>>>>>>> ' . json_encode($json));
        if ($nameRel == 'group') {
            if (isset($json['owner'])) {} else {
                $json['owner'] = $this->getActiveUser()->email;
            }
            \Application\Controller\Log::getInstance()->AddRow(' add Group >>>>>>>>>>>> ' . json_encode($json));
            $modelProBool = false;            
            // admin does not have model profile ... so he can create
            if (isset($json['modelprofiles']) && isset($json['modelprofiles']['id'])) {
                $idModelProfile = $json['modelprofiles']['id'];
                unset($json['modelprofiles']);
                $modelProBool = true;
            }
            
            $typeGroup = $json['grouptype'][0]['id'];
            if ($typeGroup) {
              
                if (isset($typeGroup) && $typeGroup == Group::PRIVATE_GROUP) {
                    $json['publicgroup'] = false;
                }
            }
            $id = parent::add($typeC, $json);
            if ($modelProBool == true) {
                \Application\Controller\Log::getInstance()->AddRow(' add Group1 >>>>>>>>>>>> ' . json_encode($idModelProfile));
                $modelProfileSUser = $laf->findObject('Modelprofile', $idModelProfile);
                \Application\Controller\Log::getInstance()->AddRow(' add Group1 >>>>>>>>>>>> ' . json_encode($modelProfileSUser));
                $group = $this->getInstance("Group", $id);
                $group->addProfileToGroup($modelProfileSUser);
            }
        } else if ($nameRel == 'modelprofile' || $nameRel == 'profileattribute') {
            $user = $this->getActiveExternUser();
            $id = $user->add($typeC, $json);
        } else if ($nameRel == 'suser') {
            $id = $this->createUser($json);
        } else {
            $id = parent::add($typeC, $json);
        }
        
        return $id;
    }

    function createUser($json)
    {
        $json['samsarole'] = 'extern';
        $json['is_not_active'] = 'false';
        $id = parent::add("Suser", $json);
        // \Application\Controller\Log::getInstance()->AddRow(' createProfile USer inside Workspace 1 >>>>>>>>>>>> ' . $nameRel);
        $oMP = $this->getInstance('Suser', (string) $id);
        // \Application\Controller\Log::getInstance()->AddRow(' createProfile USer inside Workspace 2 >>>>>>>>>>>> ' . json_encode($oMP));
        
        $oMP->copyAttributes();
        $oMP->update();
        
        $laf = new \Application\Controller\MongoObjectFactory();
        $userObject = $laf->findObject('Suser', (string) $id);
        $userObject->validateUser(false);
        return $id;
    }

    function createModeledUser($userEmail)
    {
        try {
            $serviceLocator = ServiceLocatorFactory::getInstance();
            
            $dm = $serviceLocator->get('doctrine.documentmanager.odm_default');
            
            $user = $dm->getRepository("\\Application\\Document\\User")->findOneBy(array(
                "email" => $userEmail
            ));
            
            $data = array(
                "name" => $user->getName(),
                "lastname" => $user->getLastName(),
                "email" => $user->getEmail(),
                "address" => $user->getAddress(),
                "phone" => $user->getPhone()
            );
            $this->createUser($data);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    function getActiveExternUser()
    {
        $userS = null;
        if (isset($_SESSION['userId'])) {
            $userId = $_SESSION['userId'];
            $user = $this->getInstance("User", $userId);
            $userS = $this->getReferenceOnPK("susers", $user->email);
        }
        return $userS;
    }
}

?>