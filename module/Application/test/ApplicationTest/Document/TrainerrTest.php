<?php
namespace ApplicationTest\Controller;

use \Application\Controller\MongoObjectFactory;

/**
 * Description of AuthControllerTest
 *
 * @author mihai.coditoiu
 */
class TrainerTest extends \PHPUnit_Framework_TestCase
{

    protected $traceError = true;

    public function setUp()
    {
        $sing =  \Application\Document\Helper\NotificationCenter::getInstance();
        $sing->getInstance()->setClasspath("Organization");
        parent::setUp();
    }

    public function testPersistData()
    {}

    public function testExecute()
    {
        $mObj = new MongoObjectFactory();
        $typeTraining = 'Ilctraining';
        $listObjectsRet = $mObj->find($typeTraining);
        $trid = (string) $listObjectsRet[0]['_id'];
        $type = 'Ilctrainer';
        $listObjectsRet = $mObj->find($type);
        $trainerid = (string) $listObjectsRet[0]['_id'];
        
        // link a traininer to the training
        $training = $mObj->findObject($typeTraining, (string) $trid);
        
        $reference = array();
        $reference['$ref'] = "ilctrainers";
        $reference['$id'] = (string) $trainerid;       
        $trainer_ref[] = array(
            '$id' => $trainerid,
            '$ref' => "trainers");
        
        // add lessons to the training
        $typeTrainingEvent = 'Ilctrainingevent';
        // create training event 1
        $data = array(
            "Ilctrainingevent_date" => "24-01-2016",
            "Ilctrainingevent_starttime" => "10:00",
            "Ilctrainingevent_endtime" => "12:00",
            //"Ilctrainingevent_duration" => "2",
            "Ilctrainingevent_pattern" => "L2:b1",
           // "trainers" => $trainer_ref
        
        );
        $training->add("Ilctrainingevent",$data);
    }

    public function testExecuteOrder()
    {
        $mObj = new MongoObjectFactory();
        $type = 'Processorder';
        $listObjectsRet = $mObj->find($type);
        $id = (string) $listObjectsRet[0]['_id'];
        $listObject = $mObj->findObject($type, $id);
    
        $collection[] = $listObject;
       // $date = new \DateTime("31/12/20015");
        $this->execute($collection);
        $i = $listObject->evaluate('$this->formatDateTime($this->startdate, \"U\");');
        print_r($listObject->name);
    }
    
    
    public function testDelete()
    {
        $mObj = new MongoObjectFactory();
        $type = 'Customer';
        $listObjectsRet = $mObj->find($type);
        $id = (string) $listObjectsRet[0]['_id'];
        $listObject = $mObj->findObject($type, $id);
        
        $typeRefO = 'Order';
        $data = array(
            "number" =>  666,
            "title" => "that666",
            "text" => "who666",
            "cost" => 8
        );
        
        $returnOrder = $mObj->createAndAdd($type, (string) $id, $typeRefO, $data);
        
        
        $order = $mObj->findObject($typeRefO, (string) $returnOrder);
        
        $typeRef = 'Orderline';
        $data = array(
            "serial" => "AA666XX",
            "amount" => 366641,        
            "profit" => 4
        );
        $returnOrderLine0 = $mObj->createAndAdd($typeRefO, (string) $returnOrder, $typeRef, $data);
        
        $typeRef = 'Orderline';
        $data = array(
            "serial" => "AA666",
            "amount" => 36664,
            "profit" => 1
        );
        $returnOrderLine2 = $mObj->createAndAdd($typeRefO, (string) $returnOrder, $typeRef, $data);
        
        $typeRef = 'Orderline';
        $data = array(
            "serial" => "AA6662",
            "amount" => 366642,
            "profit" => 2
        );
        $returnOrderLine = $mObj->createAndAdd($typeRefO, (string) $returnOrder, $typeRef, $data);

        $typeRef = 'Orderline';
        $data = array(
            "serial" => "AA66621",
            "amount" => 3666421,
            "profit" => 21
        );
        $returnOrderLine2 = $mObj->createAndAdd($typeRefO, (string) $returnOrder, $typeRef, $data);
        
        $normalslot_type[] = array(
            "id" => "7",
            "text" => "Weekly");
        
        $normalslot_type1[] = array(
            "id" => "4",
            "text" => "Do");
        
        $typeRefP = 'Processorder';
        $data = array(
            "color" => "re6666d",
            "startdate" => "01-03-2016",
            "enddate" => "30-03-2016",
            "recurrenceRule" => $normalslot_type,
            "weekRule" => $normalslot_type1,
            "number" => 14666
        );
        $returnProcOrder = $mObj->createAndAdd($typeRefO, (string) $returnOrder, $typeRefP, $data);
        
        $reference = array();
        $reference['$ref'] = "orderlines";
        $reference['$id'] = (string) $returnOrderLine;
        $procorder = $mObj->findObject($typeRef, (string) $returnProcOrder);
        
        // $object->{'views'}[] = $reference;
        $procorder->addReferenceObject("orderlines", $reference);
        
        $returnOrderlineObj = $mObj->findObject($typeRef, $returnOrderLine0);
        $returnOrderObj = $mObj->findObject($typeRefO, $returnOrder);
        $returnOrderObj->remove($returnOrderlineObj);
        $returnOrderObj = $mObj->findObject($typeRefO, $returnOrder);
        //$returnOrderObj = $mObj->findObject($typeRefO, $returnOrder);
        $listObject->remove($returnOrderObj);
    }

    private function execute($collection)
    {
        foreach ($collection as $collectionItem) {
            print_r($collectionItem);
        }
    }
}
