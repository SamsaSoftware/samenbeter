<?php
namespace Application\Document;

class Indexed extends Model
{

    public $index;

    public $next;

    public $prev;

    public function getFirst()
    {
        //$Object = null;
        // $Object1 = null;
        $typeC = $this->get_class_name($this);
        $parentI = $this->getParent();
        $listIo = $parentI->getInstanceAtIndex(1, $typeC);
        
        return $listIo;
    }

    public function getLast()
    {
        $Object = null;
        // $Object1 = null;
        $typeC = $this->getTableName();
        $parentI = $this->getParent();
        $listIo = $parentI->getLast($typeC);
        if (isset($listIo)) {
            \Application\Controller\Log::getInstance()->AddRow(' --> GETLAST -- ' . json_encode($listIo) . ' -- ');
            return $listIo;
        }
        return $Object;
    }

    public function getNext()
    {
        $Object = null;
        if (isset($this->next) && isset($this->next[0]['$ref'])) {
            // \Application\Controller\Log::getInstance()->AddRow(' --> PARENT -- ' . json_encode($this) . ' -- ');
            $refT = $this->next[0]['$ref'];
            $refId = $this->next[0]['$id'];
            $refType = ucfirst(substr($refT, 0, strlen($refT) - 1));
            $laf = new \Application\Controller\MongoObjectFactory();
            $Object = $laf->findObject($refType, $refId);
        }
        return $Object;
    }

    public function getPrev()
    {
        $Object = null;
        if (isset($this->prev) && isset($this->prev[0]['$ref'])) {
            // \Application\Controller\Log::getInstance()->AddRow(' --> PARENT -- ' . json_encode($this) . ' -- ');
            $refT = $this->prev[0]['$ref'];
            $refId = $this->prev[0]['$id'];
            $refType = ucfirst(substr($refT, 0, strlen($refT) - 1));
            $laf = new \Application\Controller\MongoObjectFactory();
            $Object = $laf->findObject($refType, $refId);
        }
        return $Object;
    }

    public function moveTo($paramIndex, $paramKeystart, $paramKeyend, $duration, $paramIntervalRequired = false)
    {
        $typeClass = $this->get_class_name($this);
        $laf = new \Application\Controller\MongoObjectFactory();
        $fullClassName = $laf->getClassPath($typeClass) . $typeClass;
        $format = 'd-m-Y H:i';
        // \Application\Controller\Log::getInstance()->AddRow(' plan MOVEAFTER1 tasksX ' . json_encode($this));
        // $startDateTask = \DateTime::createFromFormat($format, $objInstance->nwtast_startdate);
        $objInstance = $this->getFirst();
        // \Application\Controller\Log::getInstance()->AddRow(' plan MOVEAFTER2 tasksX ' . json_encode($objInstance));
        $index = 0;
        $startDate = $paramIndex;
        // $prevObj = $objInstance->getPrev();
        if ($paramIntervalRequired == true) {
            $foundPlace = false;
            if (isset($objInstance)) {
                do {
                    // $startDate = \DateTime::createFromFormat($format, $objInstance->{$paramKeyend});
                    //\Application\Controller\Log::getInstance()->AddRow(' plan MOVEAFTER5 for instance ' . json_encode($startDate) . ' -- ' . $duration);
                    
                    // calculate if there is enough place
                    $nextObj = $objInstance->getNext();
                    if ((isset($nextObj)) && $nextObj instanceof $fullClassName) {
                        $startDateNextTask = \DateTime::createFromFormat($format, $nextObj->{$paramKeystart});
                        $prevObj = $objInstance->getPrev();
                        if ((isset($prevObj)) && $objInstance->getPrev() instanceof $fullClassName) {
                            $endDatePrevTask = \DateTime::createFromFormat($format, $prevObj->{$paramKeyend});
                            $startDatePrevTask = \DateTime::createFromFormat($format, $prevObj->{$paramKeystart});
                           // \Application\Controller\Log::getInstance()->AddRow(' plan MOVEAFTER3 tasksX ' . json_encode($endDatePrevTask) . " --" . json_encode($objInstance) . '  --- ' . json_encode($paramIndex));
                            if ($startDate < $endDatePrevTask) {
                                if (($paramIndex < $endDatePrevTask)) {
                                    $startDate = $endDatePrevTask;
                                }
                            }
                        }
                        
                        $startDateTaskN = clone ($startDate);
                        $interval = $this->time_diff($startDateNextTask, $startDateTaskN);
                        $dif = (int) $interval->format('%r%i');
                        // \Application\Controller\Log::getInstance()->AddRow(' plan MOVEAFTER7 for instance ' . json_encode($dif) . ' -- ' . $duration);
                        if ($duration < $dif) {
                            $foundPlace = true;
                            break;
                        }
                    }
                    
                    $objInstance = $objInstance->getNext();
                    // /\Application\Controller\Log::getInstance()->AddRow(' plan MOVEAFTER6 for instance ' . json_encode($objInstance) . ' -- ' . $duration);
                } while ($objInstance instanceof $fullClassName);
            }
            if ($foundPlace == true) {
                $hours_nextI = new \DateInterval("PT" . floor($duration) . "M");
                $startDateTaskN = clone ($startDate);
                $endDate = $startDateTaskN->add($hours_nextI);
                // if (isset($objInstance) && $objInstance instanceof $fullClassName) {
                // \Application\Controller\Log::getInstance()->AddRow(' plan MOVEAFTER88 for instance ' . json_encode($startDate) . ' -- ' . $duration);
                $this->{$paramKeystart} = $startDate->format("d-m-Y H:i");
                $this->{$paramKeyend} = $endDate->format("d-m-Y H:i");
                $this->update();
                $this->reindexAll('nwtask_startdate', 'nwtask_enddate');
                return true;
            }
        } else {
            $hours_nextI = new \DateInterval("PT" .floor($duration) . "M");
            $startDateTaskN = clone ($startDate);
            $endDate = $startDateTaskN->add($hours_nextI);
            // if (isset($objInstance) && $objInstance instanceof $fullClassName) {
            // \Application\Controller\Log::getInstance()->AddRow(' plan MOVEAFTER88 for instance ' . json_encode($startDate) . ' -- ' . $duration);
            $this->{$paramKeystart} = $startDate->format("d-m-Y H:i");
            $this->{$paramKeyend} = $endDate->format("d-m-Y H:i");
            $this->update();
            $this->reindexAll('nwtask_startdate', 'nwtask_enddate');
            return true;
        }
         $this->reindexAll('nwtask_startdate', 'nwtask_enddate');
        // }
        
        return false;
    }

    public function reindexAll($paramKeystart, $paramKeyend)
    {
        \Application\Controller\Log::getInstance()->AddRow(' reindexall MOVEAFTER10 tasksX ' . json_encode($paramKeystart) . ' --- ');
        
        $typeClass = $this->get_class_name($this);
        $laf = new \Application\Controller\MongoObjectFactory();
        $fullClassName = $laf->getClassPath($typeClass) . $typeClass;
        $format = 'd-m-Y H:i';
        $this->getParent()->reindexReference($this->get_class_name($this),$paramKeystart);
        // $startDateTask = \DateTime::createFromFormat($format, $objInstance->nwtast_startdate);
        $objInstance = $this->getFirst($this->getTableName());
       // \Application\Controller\Log::getInstance()->AddRow(' reindexall MOVEAFTER1 tasksX ' . json_encode($objInstance) . ' --- ');
        
        $index = 0;
        if (isset($objInstance)) {
            do {
                if (($objInstance instanceof $fullClassName)) {
                   // \Application\Controller\Log::getInstance()->AddRow(' reindexall MOVEAFTER2 tasksX ' . json_encode($objInstance));
                    $endDate = \DateTime::createFromFormat($format, $objInstance->{$paramKeystart});
                    
                    // indexed relation - start with the first index
                    
                    $prevObj = $objInstance->getPrev();
                    if (isset($prevObj) && $prevObj instanceof $fullClassName) {
                        $endDatePrevTask = \DateTime::createFromFormat($format, $objInstance->getPrev()->{$paramKeyend});
                        if ($endDatePrevTask > $endDate) {
                           // \Application\Controller\Log::getInstance()->AddRow(' reindexall MOVEAFTER2 tasksX ' . json_encode($objInstance));
                             $objInstance->{$paramKeystart} = $endDatePrevTask->format("d-m-Y H:i");
                            $objInstance->{$paramKeyend} = $objInstance->calculatetotalduration();
                            $objInstance->update();
                            //$objInstance->reload();
                           // $objInstance->replanOnMachine(false);
                          //  $objInstance->reload(); // $objInstance->moveTo($startDTask, 'nwtask_startdate', 'nwtask_enddate', $objInstance->getParent()
                                                        // ->calculateTimeCapacity($objInstance->Nw_capacity_res_required), true);
                        }
                    }
                }
                $objInstance = $objInstance->getNext();
            } while ($objInstance instanceof $fullClassName);
        }
        return true;
    }
}

?>