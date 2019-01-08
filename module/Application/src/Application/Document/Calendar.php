<?php
namespace Application\Document;

class Calendar extends Model
{

    public $recid;

    public $name;

    public $calendardays = array();

    public static function getRelationType($name)
    {
        $relations = array();
        // many to many
        $relations['calendardays'] = Model::OWNING_ONE_TO_MANY;
        return self::getRelationFromArray($name, $relations);
    }

    public static function getPK()
    {
        return 'name';
    }

    public function add($typeC, $json)
    {
        $json{'workspaceId'} = $this->parent[0]{'$id'};
        return parent::add($typeC, $json);
    }

    public function initCalendar($name, $year, $endyear)
    {
        $returnW = $this->parent[0]{'$id'};
        
        $typeW = 'Workspace';
        $mObj = new \Application\Controller\MongoObjectFactory();
        $startDate = new \DateTime($year . '0101');
        $endDate = new \DateTime($endyear . '1231');
        $typeCal = 'Calendar';
        $returnVCal = null;
        $date = null;
        $dayObj = null;
        while ($startDate <= $endDate) {
            $date = $startDate->format("d-m-Y");
            $typeCald = 'Calendarday';
            $date_stamp = strtotime(date('d-m-yy', strtotime($date)));
            $actualDay = date("N", $date_stamp);
            
            $dataCald = array(
                "day" => $date
            );
            $calDay = $this->getDay($date);
            if (isset($calDay)) {} else {
                $returnVCal = $mObj->createAndAdd($typeCal, (string) $this->_id['$id'], $typeCald, $dataCald);
            }
            /*
             * $dayObj = $this->getInstance($typeCald, (string) $returnVCal);
             *
             * if ($actualDay == 6 || $actualDay == 7) {
             * // set weeekend
             * $weeekend = array(
             * "id" => "1",
             * "text" => "weekend"
             * );
             * $dataCaldType = array(
             * "daytype" => array(
             * $weeekend
             * )
             * );
             * $typeCalType = 'Specialdaytype';
             *
             * $mObj->createAndAdd($typeCald, (string) $returnVCal, $typeCalType, $dataCaldType);
             * }
             */
            
            $startDate->add(new \DateInterval('P1D'));
        }
    }

    public function initCalendarType($startDate, $endDate)
    {
        $mObj = new \Application\Controller\MongoObjectFactory();
        while ($startDate <= $endDate) {
            $date = $startDate->format("d-m-Y");
            $date_stamp = strtotime(date('d-m-yy', strtotime($date)));
            $actualDay = date("N", $date_stamp);
            /*
             * $calendarDay = $this->getDay($date);
             * if ($actualDay == 6 || $actualDay == 7) {
             * // set weeekend
             * $weeekend = array(
             * "id" => "1",
             * "text" => "weekend"
             * );
             * $dataCaldType = array(
             * "daytype" => array(
             * $weeekend
             * )
             * );
             * $typeCalType = 'Specialdaytype';
             * $typeCald = 'Calendarday';
             * $returnVCalType = $mObj->createAndAdd($typeCald, (string) $calendarDay->_id['$id'], $typeCalType, $dataCaldType);
             * }
             */
        }
    }

    /**
     * Returns a reference instance list of format list - reference type
     *
     * @param type $typeRef            
     * @return List Model instance <\Application\Document\....>
     */
    public function getReferenceDay($type, $pk)
    {
        $references = array();
        foreach ($this->{$type} as $key => $value) {
            $typeC = ucfirst(substr($type, 0, strlen($type) - 1));
            $object = $this->getInstance($typeC, $value['$id']);
            if ($object->{$object->getPK()} == $pk) {
                return $object;
            }
        }
        return null;
    }

    public function getDay($date)
    {
        $mObj = new \Application\Controller\MongoObjectFactory();
        
        $criteria = array(
            "day",
            $this->getFormatVarialble($date)
        );
        $calendarday = $this->getQuickInstancesCriteria("Calendarday", $criteria);
        if (isset($calendarday[0])) {
            return $calendarday[0];
        }
        return null;
    }
}

?>