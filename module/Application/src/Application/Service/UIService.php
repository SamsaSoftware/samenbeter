<?php
namespace Application\Service;

use Application\Controller\MongoObjectFactory;
use Dompdf\Dompdf;
use Application;
use Application\DatabaseConnection\Database;
use Application\Document\Workspace;
use Application\Document\Indexed;

class UIService extends Service
{

    private $mongoFactory;

    private $listOfIds = array();

    public function importUI($data, $workspace)
    {}

    public function exportUI($data, $workspace)
    {}

    private function exportJson($object)
    {}
}