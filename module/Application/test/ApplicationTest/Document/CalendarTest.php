<?php
namespace ApplicationTest\Document;

use \Application\Service\SemanticService;
use \Application\Controller\MongoObjectFactory;
use Zend\ServiceManager\ServiceManager;
use Zend\Mvc\Service\ServiceManagerConfig;
use \EasyRdf\Graph;

/**
 * Description of AuthControllerTest
 *
 * @author mihai.coditoiu
 */
class CalendarTest extends \PHPUnit_Framework_TestCase
{

    protected $traceError = true;

    public function setUp()
    {
        static::init();
        $sing = \Application\Document\Helper\NotificationCenter::getInstance();
        $sing->getInstance()->setClasspath("Organization");
        parent::setUp();
    }

    protected static function findParentPath($path)
    {
        $dir = __DIR__;
        $previousDir = '.';
        while (! is_dir($dir . '/' . $path)) {
            $dir = dirname($dir);
            if ($previousDir === $dir) {
                return false;
            }
            $previousDir = $dir;
        }
        return $dir . '/' . $path;
    }

    public static function init()
    {
        $zf2ModulePaths = array(
            dirname(dirname("/Apps/projectMihai"))
        );
        if (($path = static::findParentPath('vendor'))) {
            $zf2ModulePaths[] = "/Apps/projectMihai/vendor";
        }
        if (($path = static::findParentPath('module')) !== $zf2ModulePaths[0]) {
            $zf2ModulePaths[] = $path;
        }
        
        // static::initAutoloader();
        
        // use ModuleManager to load this module and it's dependencies
        $config = array(
            'module_listener_options' => array(
                'module_paths' => $zf2ModulePaths
            ),
            'modules' => array(
                'Application',
                'Chat',
                'DoctrineModule',
                'DoctrineMongoODMModule'
            )
        );
        
        $serviceManager = new ServiceManager(new ServiceManagerConfig());
        $serviceManager->setService('ApplicationConfig', $config);
        $serviceManager->get('ModuleManager')->loadModules();
        \Application\Controller\ServiceLocatorFactory::setInstance($serviceManager);
        // static::$serviceManager = $serviceManager;
    }

    public function testPersistData()
    {}

    public function tesastExecute()
    {
        $mObj = new MongoObjectFactory();
        $type = 'Workspace';
        $listObjectsRet = $mObj->find($type);
        $id = (string) $listObjectsRet[1]['_id'];
        // $listObject = $mObj->findObject($type, $id);
        // $calendar = $listObject->getCalendar("default");
        // $day = $calendar->getDay("04-03-2016");
        // $foaf = new Graph("http://njh.me/foaf.rdf");
        // $foaf->load();
        // $me = $foaf->primaryTopic();
        // print_r("My name is: " . $me->get('foaf:name') . "\n");
        
        $employment = new Graph();
        $people = new Graph();
        $jobs = new Graph();
        $sets = new Graph();
        $lab = new Graph();
        // $jobs = new Graph("http://users.jyu.fi/~olkhriye/ties4520/rdf/jobs.rdf");
        // $employment->load();
        // $people->load();
        // $jobs->load();
        $jobs->parseFile("/data/rdf/jobs.rdf");
        $employment->parseFile("/data/rdf/employment.rdf");
        $people->parseFile("/data/rdf/people.rdf");
        $sets->parseFile("/data/rdf/sets.rdf");
        $lab->parseFile("/data/rdf/lab.rdf");
        $gs = new \EasyRdf\GraphStore('http://localhost:8888/data/');
        
        // Add the current time in a graph
        $joe = $people->resource('http://example.org/#joe');
        $joe->add('http://example.org/#firstName', 'Joeii ');
        $joe->add('http://example.org/#surName', 'Moreii ');
        $joe->addResource('http://example.org/#loves', 'http://example.org#mary');
        // Add the current time in a graph
        $sets = $gs->get('sets.rdf');
        $test1 = $sets->resource('http://lab.nl/sets#testterm');
        $test1->add('http://lab.nl/sets#match', 'test1');
        $test1->add('http://lab.nl/sets#match', 'test2');
        
        $gs->replace($employment, 'employment.rdf');
        $gs->replace($people, 'people.rdf');
        $gs->replace($jobs, 'jobs.rdf');
        $gs->replace($sets, 'sets.rdf');
        $gs->replace($lab, 'lab.rdf');
        // Get the graph back out of the graph store and display it
        $graph2 = $gs->get('lab.rdf');
        print $graph2->dump();
        
        \EasyRdf\RdfNamespace::set('f', 'http://example.org#');
        \EasyRdf\RdfNamespace::set('j', 'http://jyu.fi/jobs#');
        \EasyRdf\RdfNamespace::set('e', 'http://jyu.fi/employment#');
        \EasyRdf\RdfNamespace::set('rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');
        $sparql = new \EasyRdf\Sparql\Client('http://localhost:8888/sparql/');
        // $result = $sparql->query('SELECT ?person WHERE {' . ' ?person e:worksAs j:seniorResearcher' . '}');
        // print_r($result);
        
        $result = $sparql->query('SELECT * WHERE { ' . 
        // ' ?person f:age ?age .'.
        ' ?person f:firstName ?firstName .' . 
        // ' ?person e:worksAs ?job .' .
        // ' FILTER regex(?firstName, "Bill", "i")' .
        // ' ?job rdf:type j:EducationJob ' .
        '}');
        
        // FILTER regex(?xx, $textIn , "i")
        // print_r($result);
        foreach ($result as $row) {
            print "<li>" . $row->person . ' - ' . $row->age . ' - ' . $row->firstName . "</li>\n";
            // print_r($row);
        }
        
        print "<loooooooooooooooooooooooooooo>\n";
        \EasyRdf\RdfNamespace::set('t', 'http://lab.nl/types#');
        \EasyRdf\RdfNamespace::set('l', 'http://lab.nl/lab#');
        \EasyRdf\RdfNamespace::set('s', 'http://lab.nl/sets#');
        $result = $sparql->query('SELECT * WHERE { ' . ' ?tag s:match "test1" .' . '}');
        print_r($result);
        foreach ($result as $row) {
            print "<lp>" . $row->tag . "</lp>\n";
        }
        // Hoe lang duurt een DNA onderzoek voor hepatitis E?
        // Hoe moet hepatitis PCR in bloed getransporterd worden?
        // Welk hepatitis onderzoek heeft een doorlooptijd korter dan 4 dagen?
        $resultMatch = "afnameconditie";
        $queryData = "alcohol";
        $queryRet = "parameter";
        \EasyRdf\RdfNamespace::set('t', 'http://lab.nl/types#');
        \EasyRdf\RdfNamespace::set('l', 'http://lab.nl/lab#');
        \EasyRdf\RdfNamespace::set('s', 'http://lab.nl/sets#');
        $result = $sparql->query('SELECT * WHERE { ' . 
        // '?'.$resultMatch.'l:'.$resultMatch."?".$resultMatch.' .'.
        ' ?' . $queryRet . ' l:' . $resultMatch . ' ?' . $resultMatch . ' .' . ' FILTER regex(?' . $resultMatch . ', "' . $queryData . '", "i")' . '}');
        print_r($result);
        foreach ($result as $row) {
            // print "<ll>".$row->tag."</ll>\n";
            print_r($row);
        }
    }

    public function testP1()
    {
        // Hoe lang duurt een DNA onderzoek voor hepatitis E
        $strIn = "Hoe lang duurt een DNA onderzoek voor hepatitis E alcohol";
        $sets = new Graph();
        $lab = new Graph();
        $sets->parseFile("/data/rdf/sets.rdf");
        $lab->parseFile("/data/rdf/lab.rdf");
        $gs = new \EasyRdf\GraphStore('http://localhost:8888/data/');
        // parse string
        $retAr = explode(" ", $strIn);
        $retArCombi = array();
        $i = 0;
        $newT = "";
        foreach ($retAr as $text) {
            $i = $i + 1;
            $newT = $newT . " " . $text;
            if ($i == 2) {
                $retArCombi[] = $newT;
                $i = 0;
                $newT = "";
            }
        }
        
        $gs->replace($sets, 'sets.rdf');
        $gs->replace($lab, 'lab.rdf');
        //
        $sets = $gs->get('sets.rdf');
        $test1 = $sets->resource('http://lab.nl/sets#testterm');
        $test1->add('http://lab.nl/sets#match', 'test1');
        $test1->add('http://lab.nl/sets#match', 'test2');
        
        // SemanticService
        $ret = array();
        $sr = new SemanticService();
        foreach ($retArCombi as $text) {
            $ret[] = $sr->getSynonimMatchList("lab", $text);
        }
        foreach ($retAr as $text) {
            $ret[] = $sr->getSynonimMatchList("lab", $text);
        }
        print_r("--" . json_encode($ret) . "    -----");
    }

    function insert_data($endpoint)
    {
        $graph = new \EasyRdf\Graph();
        $res = $graph->resource("http://www.example.com");
        $graph->add($res, 'prefix:property', 'value');
        
        $graph->add($res, array(
            'foaf:knows' => array(
                'foaf:name' => 'Name'
            )
        ));
        
        $endpoint->insert($graph, 'time2.rdf');
        $graph2 = $endpoint->get('time2.rdf');
        print $graph2->dump();
    }

    function insert_where($endpoint)
    {
        $result = $endpoint->update("
        PREFIX : <http://example.org/>
        INSERT {?s :loves ?o}
        WHERE {?s :name 'bob'. ?o :name 'alice'}");
    }

    function select_where($endpoint)
    {
        $result = $endpoint->query("
        SELECT * WHERE {?s ?p ?o}");
        print($result->numRows());
    }

    public function testUseCalendar()
    {
        $mObj = new MongoObjectFactory();
        $type = 'Processorder';
        $listObjectsRet = $mObj->find($type);
        $id = (string) $listObjectsRet[0]['_id'];
        $listObject = $mObj->findObject($type, $id);
        $dayRef = $listObject->getSimpleReference("startdate");
        print_r($listObject->name);
        $test = array();
    }
}
