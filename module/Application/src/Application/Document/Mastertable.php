<?php
namespace Application\Document;

class Mastertable extends Model
{
    public $recid;
    
    public $name;
    
    public $columns;
    
    public $items;
 
    public $workspaceId;
    
    public static function getPK()
    {
        return 'name';
    }



  

}

?>