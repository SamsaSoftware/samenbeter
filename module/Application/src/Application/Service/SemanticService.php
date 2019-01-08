<?php
namespace Application\Service;

use Application\Document\Setting;

class SemanticService extends Service
{

    /**
     *
     * @param unknown $type            
     * @param unknown $queryRet            
     * @param unknown $queryData            
     * @param unknown $tag            
     * @return \EasyRdf\Sparql\Result|\EasyRdf\Graph
     */

    public function getQueryResult($type, $queryRet, $queryData, $tag)
    {
        \EasyRdf\RdfNamespace::set('t', 'http://lab.nl/types#');
        \EasyRdf\RdfNamespace::set('l', 'http://lab.nl/.' . $type . '#');

        \EasyRdf\RdfNamespace::set('s', 'http://lab.nl/sets#');
        $sparql = new \EasyRdf\Sparql\Client('http://localhost:8888/sparql/');
        // $result = $sparql->query('SELECT ?person WHERE {' . ' ?person e:worksAs j:seniorResearcher' . '}');
        // print_r($result);

        $resultMatch = $this->getSynonimMatch($type, $tag);
        $result = $sparql->query('SELECT * WHERE { ' . ' ?' . $queryRet . ' l:' . $queryData . ' ?' . $queryData . ' .' . ' FILTER regex(?' . $queryData . ', "' . $resultMatch . '", "i")' . '}');

        
        // FILTER regex(?xx, $textIn , "i")
        
        // foreach ($result as $row) {
        // print "<li>".$row->person. ' - '.$row->age. ' - ' . $row->firstName."</li>\n";
        // print_r($row);
        // }
        return $result;
    }


    /**
     * Returns set synonim
     * 
     * @param unknown $type            
     * @param unknown $tag            
     * @return string
     */
    public function getSynonimMatchList($typeIn, $tag)
    {
        $sparql = new \EasyRdf\Sparql\Client('http://localhost:8888/sparql/');
        \EasyRdf\RdfNamespace::set('t', 'http://lab.nl/types#');
        \EasyRdf\RdfNamespace::set('l', 'http://lab.nl/.' . $typeIn . '#');
        \EasyRdf\RdfNamespace::set('s', 'http://lab.nl/sets#');
        $result = $sparql->query('SELECT ?tag ?tuple  WHERE { ' . ' ?tag  s:match "' . $tag . '" . ?tag  s:tuple ?tuple . }');
        
      
        $tags = array();
        foreach ($result as $row) {
            var_dump($row);
            $tagTxt = substr($row->tag, strlen('http://lab.nl/sets#'));
            if (isset($tagTxt) && strlen($tagTxt) > 1) {
                print_r("ll>".$tagTxt);
                print_r("lffl|".$row->tuple);
                
                $rett = array();
                $rett["tag"] = $tagTxt;
                $rett["tuple"] = "".$row->tuple;
                $tags[] = $rett;
            }
        }
        return $tags;
    }


    /**
     * Add to store
     *
     * @param unknown $namespace            
     * @param unknown $resource            
     * @param unknown $data            
     */
    public function addTripleToStore($namespace, $resource, $data)
    {
        \EasyRdf\RdfNamespace::set('t', 'http://lab.nl/types#');
        \EasyRdf\RdfNamespace::set('l', 'http://lab.nl/.' . $namespace . '#');
        \EasyRdf\RdfNamespace::set('s', 'http://lab.nl/sets#');
        $gs = new \EasyRdf\GraphStore('http://localhost:8888/data/');
        $sets = $gs->get($namespace . '.rdf');
        $test1 = $sets->resource('http://lab.nl/' . $namespace . '#');
        foreach ($data as $key => $value) {
            $test1->add('http://lab.nl/' . $namespace . '#' . $key, $value);
        }
    }
}