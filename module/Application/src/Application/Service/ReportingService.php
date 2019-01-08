<?php
namespace Application\Service;

use Application\Controller\MongoObjectFactory;
use Dompdf\Dompdf;
use Application;
use Application\Service\PDF\PDFMerger;

class ReportingService extends Service
{

    private $mongoFactory;

    public function formatDocument($data, $fileName = '', $returnFile = false)
    {
        $method = $data['method'];
        $objectRef = $data['objectType'];
        $objectId = $data['id']['$id'];
        
        $template = $data['template'];
        $workspaceId = $data['workspaceId'];
        $laf = new MongoObjectFactory();
        if (is_array($objectId)) {
            $objectId = $objectId[0];
        }
        $mainArrayResult = array();
        $mainArrayResult = $laf->findObjectJSON($objectRef, $objectId);
        
        // get instance from object
        $classObject = $laf->findObjectInstance($objectRef, $objectId);
        
        $collectionObj = array();
        $collectionObj[] = $classObject;
        $collectionInstances = array();
        // extract the last method name : getOrder -> orders
        // $methods = explode(".", $method);
        $collectionInstances = $this->getMultiCollectionRef($method, $collectionObj);
        $data["main"] = $mainArrayResult;
        $collection = array();
        $data["lists"] = $collectionInstances;
        $log = Application\Controller\Log::getInstance();
        $log->AddRow(" FormatData ref actionXX : -- " . json_encode($collectionInstances) . ' --- ' . ' .... ');
        
        return $this->formatDocumentCollection($workspaceId, $template, $data, $fileName, $returnFile);
    }

    public function formatDocumentList($dataList)
    {
        $documents = array();
        
        $workspaceId = "0001";
        foreach ($dataList as $data) {
            $workspaceId = $data['workspaceId'];
            $log = Application\Controller\Log::getInstance();
            $log->AddRow(" Format1Data ref actionXX : -- " . json_encode($data) . ' --- ' . ' .... ');
            
            $ret = $this->formatDocument($data);
            $documents[] = $ret["localpath"];
            $log = Application\Controller\Log::getInstance();
            $log->AddRow(" Format1Data ref actionXX : -- " . json_encode($ret) . ' --- ' . ' .... ');
        }
        $pdf = new PDFMerger();
        foreach ($documents as $document) {
            $pdf->addPDF($document);
            $log = Application\Controller\Log::getInstance();
            $log->AddRow(" Format1Data ref actionXX1 : -- " . ' .... ');
        }
        $uniqId = uniqid();
        $localPath = getcwd() . "/public/prints/".$workspaceId."/";
        
        if (isset($_SESSION)) {
            $s = "http://localhost:8080/application";
            $url = $_SESSION['urlLink'];
            if (isset($url)) {
                $urlTo = explode("/", $url);
                $size = sizeof($urlTo);
                $s = 'http:';
                for ($x = 1; $x <= $size - 3; $x ++) {
                    $s = $s . '/' . $urlTo[$x];
                }
            }
        }
        $uniqueFileName = $uniqId;
        
        $publicPath = $s . "/prints/".$workspaceId."/" . (string) $uniqueFileName . ".pdf";
        // make a folder with hotel id if doesn't exist one
        if (! file_exists(realpath($localPath))) {
            mkdir($localPath, 0777, true);
        }
        $path = $localPath . (string) $uniqueFileName . ".pdf";
        $eval["path"] = $publicPath;
        $log->AddRow(" Format1Data ref actionXX2 : -- " . $publicPath . ' .... ');
        $pdf->merge('file', $path);
        return $eval;
    }

    /**
     * data - contains "main" and "lists"
     */
    public function formatDocumentCollection($workspaceId, $template, $data, $fileName = '', $returnFile = false)
    {
        $laf = new MongoObjectFactory();
 
        $criteria = array(
            'name' => $template,
            'parent.$id' => $workspaceId
        );
        $resultTemplate = $laf->findObjectByCriteria("Template", $criteria);
        
        // print_r($data);exit;
        if (! empty($resultTemplate)) {
            $template = $this->reportBuilder($resultTemplate['text'], $data);
        } else {
            $template = "No template!";
        }
        // print_r($template);exit;
        // print_r($view->render($viewModel));exit;
        
        $dompdf = new Dompdf();
        $options = new \Dompdf\Options([
            'isHtml5ParserEnabled' => true
        ]);
        
        $dompdf->setOptions($options);
        $dompdf->set_option('enable_remote', TRUE);
        
        // $options = new Options();
        $dompdf->set_option('defaultFont', 'Courier');
        ///public/img/upload/5b5fef5af25952524f0042f6/Aanslag5633405.pdf
        // $dompdf->
        $dompdf->loadHtml($template);
        $dompdf->render();
        // $dompdf->stream("print.pdf");
        $output = $dompdf->output();
        $uniqId = uniqid();
        $localPath = getcwd() . "/public/prints/".$workspaceId."/";
        $printPath = "/public/prints/".$workspaceId."/";
        if (isset($_SESSION)) {
            $s = "http://localhost:8080/application";
            $url = $_SESSION['urlLink'];
            if (isset($url)) {
                $urlTo = explode("/", $url);
                $size = sizeof($urlTo);
                $s = 'http:';
                for ($x = 1; $x <= $size - 3; $x ++) {
                    $s = $s . '/' . $urlTo[$x];
                }
            }
        }
        $uniqueFileName = $uniqId;
        if ($fileName != '') {
            $uniqueFileName = $fileName;
        }
        $publicPath = $s . "/prints/".$workspaceId."/" . (string) $uniqueFileName . ".pdf";
        // make a folder with hotel id if doesn't exist one
        if (! file_exists(realpath($localPath))) {
            mkdir($localPath, 0777, true);
        }

        $path = $localPath . (string) $uniqueFileName . ".pdf";
        $ppath = $printPath. (string) $uniqueFileName . ".pdf";
        // unlink($path);
        file_put_contents($path, $output);
        if ($returnFile === true) {
            return $output;
        }
        $publicPath = str_replace('/index.php', '', $publicPath);
        $eval["printpath"] = $ppath;
        $eval["localpath"] = $path;
        $eval["path"] = $publicPath;
        return $eval;
    }

    public function reportBuilder($template, $data)
    {
        /**
         * Report Builder
         * Takes in a template and an array of data, replaces the keys within the template with the data,
         * and returns the completed combination.
         * - $template: The template, generally HTML, including keys to be replaced.
         * - $data: An array containing the data to replace the template keys, which takes the following form:
         * $data = array(
         * "main" => array($key1 => $value1, $key2 => $value2),
         * "lists" => array(
         * $list1 => array(
         * array($key31 => $value31, $key32 => $value32),
         * array($key41 => $value41, $key42 => $value42)
         * ),
         * $list2 => array(
         * array($key51 => $value51, $key52 => $value52),
         * array($key61 => $value61, $key62 => $value62)
         * )
         * )
         * );
         */
        // Replace values in blocks
        // data['lists'] - with all the lists --
        if (is_array($data['lists'])) {
            foreach ($data['lists'] as $list => $list_data) {
                $listNames = explode("&", $list, 10);
                $tag = "-";
                // $list = strtolower($listNames[0]);
                
                // iterate in one list item
                if (sizeof($listNames) > 0) {
                    if (sizeof($listNames) > 1) {
                        // unset($listNames[0]);
                    }
                    // foreach ($listNames as $listName) {
                    
                    // $references = explode("_", $listName, 10);
                    
                    // foreach ($listNames as $listNamex) {
                    // iterate in one list item
                    // if (sizeof($listNames) > 0) {
                    
                    // foreach ($listNames as $listName) {
                    // $references = explode("_", $listName);
                    
                    $template = $this->findMatch($listNames, $template, $list_data, $tag, $listNames[0], "", 0);
                    // Replace stand-alone TOTAL values
                    // }
                    // $tag = $tag . "-";
                    // Put the container into pieces array
                    // $piecesIn = array();
                    // $pieceIn[] = $containernew;
                    // }
                    // }
                    // Replace stand-alone TOTAL values
                    // }
                }
                
                for ($i = 1; $i <= 10; $i ++) {
                    $listNames = explode("&", $list);
                    $tag = "-";
                    // $list = strtolower($listNames[0]);
                    if (sizeof($listNames) > 1) {
                        // unset($listNames[0]);
                    }
                    // foreach ($listNames as $listNamex) {
                    // iterate in one list item
                    // if (sizeof($listNames) > 0) {
                    
                    // foreach ($listNames as $listName) {
                    // $references = explode("_", $listName);
                    
                    $template = $this->findMatch($listNames, $template, $list_data, $tag, "", "$i", 0);
                    // Replace stand-alone TOTAL values
                    // }
                    // $tag = $tag . "-";
                    // Put the container into pieces array
                    // $piecesIn = array();
                    // $pieceIn[] = $containernew;
                    // }
                    // }
                }
            }
        }
        if (is_array($data['main'])) {
            foreach ($data['main'] as $var => $value) {
                if (! is_array($value) && ! is_object($value)) {
                    $template = str_replace("%$var%", $value, $template);
                } else {
                    $template = str_replace("%$var%", '', $template);
                }
            }
        }
        // if no more values clean tags!
        if (preg_match_all("/%(.*?)%/", $template, $m)) {
            foreach ($m[1] as $i => $varname) {
                $template = str_replace($m[0][$i], "", $template);
            }
        }
        return $template;
    }

    /**
     * list names: a_b_c, b_c_d, .
     *
     *
     *
     *
     *
     *
     *
     *
     *
     * @param unknown $listName            
     * @param unknown $template            
     * @param unknown $list_data            
     * @param unknown $tag            
     * @return mixed
     */
    private function findMatch($listNames, $template, $list_data, $tag, $listName, $part, $index)
    {
        if ($part == 0) {
            $part = '';
        }
        $tag = $tag . "-";
        // $listName = $listNames[0];
        
        // Get container
        
        // $tagN = $tag . "-";
        
        // Replace contents of a container
        // foreach ($listNames as $listNamex) {
        if (isset($listNames[0])) {
            $listNamex = $listNames[0];
            $references = explode("_", $listNamex, 10);
            
            if (isset($references[0])) {
                $refTag = strtolower($references[0]);
                $listName = $listName . $references[0];
                $log = Application\Controller\Log::getInstance();
                $log->AddRow(" FormatData ref action : -- " . json_encode($refTag) . ' --- ' . json_encode($listNames) . ' .... ');
                
                // unset($listNames[0]);
                // $listNames = array_values($listNames);
                preg_match('/\<!' . $tag . '' . $refTag . $part . '' . $tag . '>([^\[]+)\<!' . $tag . '\\/' . $refTag . $part . '' . $tag . '>/', $template, $matches);
                $pieces = array();
                $containernew = array();
                $listNamesB = $listNames;
                foreach ($list_data as $values) {
                    
                    if ($index > 0) {
                        // $values = $values->{$refTag};
                    }
                    if (count($matches) > 0) {
                        $containernew = $matches[1];
                        // print $container;
                        foreach ($values as $var => $value) {
                            if (! is_array($value)) {
                                // Replace individual variables within container
                                $containernew = str_replace("%$var%", $value, $containernew);
                            } else {
                                $containernew = str_replace("%$var%", "", $containernew);
                            }
                        }
                        
                        if (isset($listNames[0])) {
                            // if (count($values->{$references[0]}) > 0) {
                            $containernew1 = $this->findMatch1($listNames, $containernew, $values, $tag, $listName . $references[0], $part);
                            $listNames = $listNamesB;
                        }
                        
                        // Replace container within template with instances in pieces array
                        $pieces[] = $containernew1;
                    }
                }
                $template = preg_replace('/\<!' . $tag . '' . $refTag . $part . '' . $tag . '>([^\[]+)\<!' . $tag . '\\/' . $refTag . $part . '' . $tag . '>/i', str_replace("\$", "\\\$", implode("", $pieces)), $template);
                unset($pieces);
                
                return $template;
            }
        }
        
        // Return completed template
        return $template;
    }

    private function findMatch1($listNames, $template, $list_data, $tag, $listName, $part)
    {
        if ($part == 0) {
            $part = '';
        }
        $nameOfRelsA = array();
        $newarray = array();
        // $listName = $listNames[0];
        if (isset($listNames[1])) {
            $nameOfRels = $listNames[1];
            unset($listNames[0]);
            $newarray = array_values($listNames);
            $tag = $tag . "-";
            $nameOfRelsA = explode("_", $nameOfRels, 10);
        }
        // Get container
        foreach ($nameOfRelsA as $nameOfRel) {
            $listNameIn = $listName . $nameOfRel;
            preg_match('/\<!' . $tag . '' . $nameOfRel . $part . '' . $tag . '>([^\[]+)\<!' . $tag . '\\/' . $nameOfRel . $part . '' . $tag . '>/', $template, $matches);
            $pieces = array();
            if (isset($list_data->{$nameOfRel})) {
                $list_dataN = $list_data->{$nameOfRel};
                if (count($matches) > 0) {
                    foreach ($list_dataN as $values) {
                        // GET THE PROPER LIST DATA -- !!
                        
                        // Replace contents of a container
                        $containernew = $matches[1];
                        // print $container;
                        foreach ($values as $var => $value) {
                            if (! is_array($value)) {
                                // Replace individual variables within container
                                $containernew = str_replace("%$var%", $value, $containernew);
                            }
                        }
                        
                        if (isset($nameOfRel)) {
                            // if (count($values->{$nameOfRel}) > 0) {
                            $containernew = $this->findMatch1($newarray, $containernew, $values, $tag, $listNameIn, $part);
                            // }
                        }
                        // Put the container into pieces array
                        $pieces[] = $containernew;
                    }
                    // Replace container within template with instances in pieces array
                    $template = preg_replace('/\<!' . $tag . '' . $nameOfRel . $part . '' . $tag . '>([^\[]+)\<!' . $tag . '\\/' . $nameOfRel . $part . '' . $tag . '>/i', str_replace("\$", "\\\$", implode("", $pieces)), $template);
                    
                    unset($pieces);
                    // return $template;
                }
            }
        }
        
        // Return completed template
        return $template;
    }

    public function exportCSV($dataArray)
    {
        $parentObject = $dataArray['parentObject'];
        $parentId = $dataArray['parentId'];
        $object = $dataArray['object'];
        $searchData = $dataArray['searchData'];
        $searchField = $dataArray['searchField'];
        $viewId = $dataArray['viewId'];
        $gridId = $dataArray['gridId'];
        
        $attributes = array();
        $arrayResults = array();
        $session = $this->getSession();
        $identity = $session->getIdentity();
        
        $fileName = 'noData.csv';
        
        if (isset($parentId) && isset($parentObject) && sizeof($parentId) > 0) {
            $fileName = $parentObject . "_" . $parentId . "_" . $object . '.csv';
            $laf = new MongoObjectFactory();
            if ($parentId == 0) {
                $results = $laf->find($object);
            } else {
                
                $limit = 0;
                $offset = 0;
                $search = array();
                $service = $this->serviceLocator;
                $translator = $service->get('translator');
                $data = json_decode($searchData, true);
                $limit = isset($data['limit']) ? $data['limit'] : 0;
                $offset = isset($data['offset']) ? $data['offset'] : 0;
                $searchL = $data; // isset($data['search']) ? $data['search'] : '';
                if ($searchField == 'multi') {
                    $searchlogic = 'and'; // isset($data['searchLogic']) ? $data['searchLogic'] : '';
                } else {
                    $searchlogic = 'or';
                }
                $search["search"] = $searchL;
                $search["searchLogic"] = $searchlogic;
                $sort = isset($data['sort']) ? $data['sort'] : '';
                $stateService = new StateService();
                if ($sort != '') {
                    $stateService->saveState($viewId, $gridId, $sort, "sort");
                } else {
                    $sortD = $stateService->getState($viewId, $gridId, "sort");
                    if (isset($sortD)) {
                        $sort = array();
                        $sort = json_decode($sortD, TRUE);
                    }
                }
                // no param and criteria?
                $service = new Service();
                $results = $service->prepareData($parentId, $parentObject, null, $object, $viewId, $gridId, $identity['id'], null, $translator, $limit, $offset, $search, $sort);
            }
        } else {
            $attributes['name'] = 'name';
            $arrayResults[] = array(
                'name' => "value"
            );
            $fileName = 'noData.csv';
        }
        
        $attributesList = $results['columns'];
        $recidFound = false;
        foreach ($attributesList as $listAttr) {
            if (isset($listAttr['hidden']) && $listAttr['hidden'] == true) {} else {
                $key = $listAttr['field'];
                $attributes[$key] = $key;
                if ($key == 'Recid') {
                    $recidFound = true;
                }
            }
        }
        foreach ($results['records'] as $res) {
            $lastKey = '';
            $lastValue = '';
            foreach ($res as $key => $item) {
                if ($key == 'conflict_tooltip' || $key == 'style') {
                    // do nothing
                } else {
                    if (! is_array($item)) {
                        $value[$key] = $item;
                    } else {
                        $value[$key] = '';
                    }
                    $lastKey = $key;
                }
            }
            if ($lastKey == '_id') {
                unset($value[$lastKey]);
            }
            
            $arrayResults[] = $value;
        }
        
        if ($this->substr_startswith($object, 'get')) {
            $objectName = substr($object, 3);
            if (strpos($objectName, '.') > 0) {
                $resultObject = explode('.', $objectName);
                if (strpos(end($resultObject), '[') > 0) {
                    $resultObject = explode('[', end($resultObject));
                    $resultObject = $resultObject[0];
                } else {
                    $resultObject = end($resultObject);
                }
                if ($this->substr_startswith($resultObject, 'get')) {
                    $objectName = substr($resultObject, 3);
                } else {
                    $objectName = $resultObject;
                }
            }
        } else {
            $objectName = $object;
        }
        
        $object = $laf->getClassPath($objectName) . $objectName;
        $typeClass = new \ReflectionClass($object);
        
        $reflectionMethod = new \ReflectionMethod($typeClass->name, 'getMappingColumns');
        $mappingArray = $reflectionMethod->invoke(null);
        
        $attributesList = array();
        $arrayResultsMapped = array();
        if (count($mappingArray) > 0) {
            foreach ($attributes as $key => $attrib) {
                if (isset($mappingArray[$key])) {
                    $attributesList[] = $mappingArray[$key]['name'];
                }
            }
            foreach ($arrayResults as &$result) {
                $resultMapped = array();
                foreach ($result as $resKey => $res) {
                    if (in_array($resKey, array_keys($mappingArray))) {
                        $resultMapped[$resKey] = $res;
                    }
                }
                $arrayResultsMapped[] = $resultMapped;
            }
        } else {
            $attributesList = $attributes;
            $arrayResultsMapped = $arrayResults;
        }
        
        $fileName = $parentObject . "_" . $parentId . "_" . $object . '.csv';
        
        $f = fopen('php://memory', 'w');
        fputcsv($f, $attributesList, ";");
        foreach ($arrayResultsMapped as $result) {
            fputcsv($f, $result, ";");
        }
        
        fseek($f, 0);
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="' . $fileName . '";');
        fpassthru($f);
        
        exit();
    }
}