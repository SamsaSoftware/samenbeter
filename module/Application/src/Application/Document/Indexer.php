<?php
namespace Application\Document;

class Indexer extends Model
{

    public $first;

    public $last;

    public function getFirst($typeC)
    {
        $Object = null;
        if (isset($this->first) && isset($this->first[0]['$ref'])) {
            foreach ($this->first as $item) {
                if ($item['$ref'] == $typeC) {
                    $refT = $item['$ref'];
                    $refId = $item['$id'];
                    $refType = ucfirst(substr($refT, 0, strlen($refT) - 1));
                    $laf = new \Application\Controller\MongoObjectFactory();
                    $Object = $laf->findObject($refType, $refId);
                }
            }
           // \Application\Controller\Log::getInstance()->AddRow(' --> PARENT -- ' . json_encode($Object) . ' -- ');
        }
        return $Object;
    }

    public function getLast($typeC)
    {
         $Object = null;
        if (isset($this->last) && isset($this->last[0]['$ref'])) {
            foreach ($this->last as $item) {
                if ($item['$ref'] == $typeC) {
                    $refT = $item['$ref'];
                    $refId = $item['$id'];
                    $refType = ucfirst(substr($refT, 0, strlen($refT) - 1));
                    $laf = new \Application\Controller\MongoObjectFactory();
                    $Object = $laf->findObject($refType, $refId);
                }
            }
            // \Application\Controller\Log::getInstance()->AddRow(' --> PARENT -- ' . json_encode($this) . ' -- ');
        }
        return $Object;
    }

    
    
    
    public function getInstanceAtIndex($index, $typeC){
        $criteria = array();
        $criteria[0] = "index";
        $criteria[1] = $index;
        $instances = $this->getQuickInstancesCriteria($typeC, $criteria);
        if(isset($instances[0])){
            return $instances[0];
        }
    }
    
    public function getPrev()
    {
        return null;
    }

    public function getNext()
    {
        return null;
    }
    
    public function addNext($object, $objectNew){
                
    }
}

?>