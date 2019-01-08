<?php
namespace Application\Document;

class Workspaceevents extends Model
{    
    
    public $group_id;
    
    public $events= array();
    
    public $rules = array();

    public static function getPK()
    {
        return '_id';
    }
    
    public function set($data)
    {
        parent::set($data);
    }

    public static function getRelationType($name)
    {
        $relations = array();
        //020 - one-to-one
        $relations['events'] = Model::OWNING_ONE_TO_MANY;  
        $relations['rules'] = Model::OWNING_ONE_TO_MANY;
        return self::getRelationFromArray($name, $relations);
    }
    
    
    public function applyRules(){
        
        // get all new events 
        foreach( $this->events as $key => $eventRef){
            $event  = $this->getReferenceInstance ( $eventRef);
            if( $event->state == Event::CREATED){
                 foreach( $this->rules as $key => $ruleRef){
                     $rule  = $this->getReferenceInstance ( $ruleRef);
                     // for each event apply rule if type and name matches
                     if(  ($rule->name == $event->name) && ($rule->type == $event->type) ){
                         //process ruule
                         $this->processRule($rule, $event->id);
                     }
                 }
            }   
        }

    }
    
    public function processRule($rule, $objectId){
        // map rule to data - $rule->executionContent
        // execute the rule - $rule->executionRule
        
    }
   
    
}

?>