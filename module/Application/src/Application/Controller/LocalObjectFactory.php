<?php
namespace Application\Controller;

class LocalObjectFactory extends ObjectFactory
{

    public function createObject()
    {}

    public function find($type)
    {
        $typeClass = new \ReflectionClass($type);
        $objectInstance = $typeClass->newInstanceArgs();
        $method = new \ReflectionMethod($type,'setId');
        $method->invoke($objectInstance , 1);

        $strObj = \json_encode($this->toDataObj($objectInstance));
        /*foreach ($typeClass->getMethods() as $reflectmethod) {
            echo "  {$reflectmethod->getName()}()\n";
            
           if ($this->startsWith($reflectmethod->getName(), "get")) {
                $methods = substr($typeClass->getMethods(),0,3);
                foreach ($reflectmethod->getParameters() as $num => $param) {
                    echo "    Param $num: \$", $param->getName(), "\n";
                    
                    echo "      Class type: ";
                    if ($param->getClass()) {
                        echo $param->getClass()->getName();
                    } else {
                        echo "N/A";
                    }
                    echo "\n\n";
                }
            }
        }*/
        return $strObj;
    }

    function startsWith($haystack, $needle)
    {
        return substr($haystack, 0, strlen($needle)) === $needle;
    }
    
    function toDataObj($myObj) {
        $ref = new \ReflectionClass($myObj);
        $data = array();
        foreach (array_values($ref->getMethods()) as $method) {
            if ((0 === strpos($method->name, "get"))
                && $method->isPublic()) {
                    $name = substr($method->name, 3);
                    $name[0] = strtolower($name[0]);
                    $value = $method->invoke($myObj);
                    if ("object" === gettype($value)) {
                        $value = $this->toDataObj($value);
                    }
                    $data[$name] = $value;
                }
        }
        return $data;
    }
}

?>