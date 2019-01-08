<?php
namespace Application\Document;

class Field extends Model
{

    const TYPE_FORM = 'form';

    const TYPE_REFERENCE_REMOTE = 'ref-by-ref-remote';

    const TYPE_REFERENCE = 'ref-by-ref';

    const TYPE_REF_VALUE = 'ref-by-value';

    const TYPE_REF_VALUE_REMOTE = 'ref-by-value-remote';

    const TYPE_RADIO = 'radio';

    const TYPE_TEXT = 'text';

    const TYPE_FILE = 'file';

    const TYPE_BUTTON = 'button';

    const TYPE_DATE = 'date';

    const TYPE_TIME = 'time';

    const TYPE_DATETIME = 'datetime';

    const TYPE_LIST = 'list';

    const TYPE_CHECKBOX = 'checkbox';

    const TYPE_ENUM = 'enum';

    const TYPE_COLOR = 'color';

    const TYPE_TEXTAREA = 'textarea';

    public $html;

    public $object;

    public $label;

    public $name;

    public $type;

    public $typeReference;

    public $preloadPath;

    public $actionExecution;

    public $required;

    public $readonly;

    public $options;

    public $optionsString;

    public $objectReferenceType;

    public $actionResponse;

    public $method;

    public $group = '';

    public $searchable;

    public static function getRelationType($name)
    {
        $relations = array();
        return self::getRelationFromArray($name, $relations);
    }

    public function load($data)
    {
        $laf = new \Application\Controller\MongoObjectFactory();
        $this->html = array();
        if (is_null($data)) {
            $data = array();
        }
        // \Application\Controller\Log::getInstance()->AddRow(' --> 3 ' . json_encode($data));
        foreach ($data as $key => $value) {
            if ($key == "options") {
                $this->optionsString = $value;
            }
            if (isset($data['_id'])) {
                if ($key == 'label') {
                    $values = explode("^", $value);
                    $size = '';
                    if (isset($values[1])) {
                        $size = $values[1];
                    }
                    $this->html = array(
                        "caption" => $values[0],
                        "span" => 10,
                        "attr" => $size,
                        "column" => 1
                    ); // 'style="width: 300px; height: 90px"'

                    $this->{$key} = $values[0];
                } elseif ($key == 'options') {
                    
                    if (! is_null($value)) {
                        if ($data['type'] == self::TYPE_FORM) {
                            if (is_array($data['options'])) {} else {
                                // print_r(json_encode($data));
                                // var_dump($data['options']);
                                $size = json_decode($data['options'], true);
                                $this->options = array(
                                    'data' => $data['options'],
                                    'maxWidth' => $size['maxWidth'], // min search length to trigger relod from URL
                                    'maxHeight' => $size['maxHeight']
                                );
                            }
                        } else 
                            if ($data['type'] == self::TYPE_REFERENCE || $data['type'] == self::TYPE_REF_VALUE || $data['type'] == self::TYPE_REF_VALUE_REMOTE || $data['type'] == self::TYPE_REFERENCE_REMOTE) {
                                $s = "http://localhost:8080/application";
                                if (isset($_SESSION['urlLink'])) {
                                    $s = '';
                                    $url = $_SESSION['urlLink'];
                                    if (isset($url)) {
                                        $urlTo = explode("/", $url);
                                        $size = sizeof($urlTo);
                                        for ($x = 3; $x <= $size - 2; $x ++) {
                                            $s = $s . '/' . $urlTo[$x];
                                        }
                                    }
                                }
                                
                                $minLength = 0;
                                $cacheMax = 600;
                                if ($data['type'] == self::TYPE_REFERENCE_REMOTE || $data['type'] == self::TYPE_REF_VALUE_REMOTE) {
                                    $minLength = 1;
                                }
                                $this->options = array(
                                    // http://localhost:8080/application/
                                    // http://samsasoftware.nl/mongo/public/application/
                                    'url' => $s . "/" . $data['options'],
                                    'minLength' => $minLength, // min search length to trigger relod from URL
                                    'openOnFocus' => false,
                                    'cacheMax' => $cacheMax
                                );
                                $this->typeReference = $data['type'];
                            } elseif ($data['type'] == self::TYPE_DATETIME) {
                                $this->options = array(
                                    'format' => "dd-mm-yyyy|hh24:mi",
                                    'openOnFocus' => false
                                );
                            } elseif ($data['type'] == self::TYPE_DATE) {
                                $this->options = array(
                                    'format' => 'dd-mm-yyyy',
                                    'openOnFocus' => false
                                );
                            } elseif ($data['type'] == self::TYPE_TIME) {
                                $this->options = array(
                                    'format' => 'h24',
                                    'openOnFocus' => false
                                );
                            }
                    }
                    // } // if ($key == 'object') {} else
                    // }
                } else {
                    $this->{$key} = $value;
                }
            }
        }
        
        if ($this->type == self::TYPE_REFERENCE || $this->type == self::TYPE_REFERENCE_REMOTE) {
            // move this to UI!
            // $this->typeReference = $this->type ;
            $this->type = 'list';
        }
        if ($this->type == self::TYPE_REF_VALUE || $this->type == self::TYPE_REF_VALUE_REMOTE) {
            $this->options['openOnFocus'] = true;
            
            if ($this->type == self::TYPE_REF_VALUE_REMOTE) {
                $this->options['minLength'] = 1;
            } else {
                $this->options['minLength'] = 0;
            }
            $this->type = 'enum';
            $this->options['selected'] = array();
        }
    }

    public function updateSet($data, &$state = '', $forceStopPropagation = false)
    {
        if (isset($data['options'])) {
            $data['optionsString'] = $data['options'];
        }
        parent::updateSet($data);
        if ($this->type == self::TYPE_RADIO) {
            $this->value = (integer) $this->options;
            unset($this->options);
        }
    }
}