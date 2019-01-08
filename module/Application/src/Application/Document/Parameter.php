<?php
namespace Application\Document;

class Parameter extends Model
{
    public $recid;
    
    public $name;
    
    public $referencelink;
    
    public $schema;
    
    public $type;
    
    public $resource;
    
    public $definition;
    
    public $actionExecution;
    
    public $actionResponse;    

    const SCHEMA_JSON = 'schema/json';
    const OBJECT_TYPE = 'objecttype';
    const GRIDRULE = 'gridrule';
    const GRIDROWRULE = 'gridrowrule';
    const GRIDCOLUMN = 'gridcolumn';
    const GRIDTOTAL = 'gridtotal';
    const FORMATFIELD = 'formatfield';
    const FILTER = 'filter';


}

?>