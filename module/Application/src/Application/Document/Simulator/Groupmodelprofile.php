<?php
namespace Application\Document\Simulator;

use Application\Document\Model;
use Application\Document\Indexer;
use Application\Document\UserTemplate;

class Groupmodelprofile extends Model
{

       // public $sfriends = array();
       public $profiles = array();

   public $modelprofiles = array();

   public $groups = array();

   public $name;
   public $tags;


   public static function getRelationType($name)
   {
               $relations = array();
               $relations['profiles'] = Model::OWNING_ONE_TO_MANY;
               $relations['groups'] = Model::MANY_TO_ONE;
               $relations['modelprofiles'] = Model::MANY_TO_ONE;
               return self::getRelationFromArray($name, $relations);
   }

   public function getRelationDetails($name)
   {
               $simplerelations = array();

               return parent::getRelationRefFromArray($name, $simplerelations); // TODO: Change the autogenerated stub
   }

   public function copyAttributes($modelProfile)
   {
               $tags = "";
               $profiles = $modelProfile->getInstances("Profile");
               \Application\Controller\Log::getInstance()->AddRow(' makeRequest USer inside Usr 4 >>>>>>>>>>>> ' . json_encode($profiles));

       foreach ($profiles as $profil) {
                       \Application\Controller\Log::getInstance()->AddRow(' makeRequest USer inside Usr 4 >>>>>>>>>>>> ' . json_encode($profil));

           if ($profil->isvisible == true || $profil->isvisible == 1) {
               $data = array(
                                       "name" => $profil->name,
                                       "value" => $profil->value,
                   "prottectedattribute" => $profil->prottectedattribute
                                       );

                               $this->add("Profile", $data);
                               $tags = $tags . ' ' . $profil->value;
                          }
       }
       // to do  update Profile!
       $this->tags = $tags;
       $this->update();
   }

   public function openPV()
   {
               // return strng
           }

    /* used if you want to deledete the profile attribute from "My Groups" menu, after you choose a group and a profile */
    public function remove($object, $softRemove = true, $noPropagation = false)
    {
        if($object instanceof Profile){
            if($object->prottectedattribute == true){
                return 'Protected attribute can\'t be deleted';
            }
        }
        parent::remove($object, $softRemove, $noPropagation);
    }

}