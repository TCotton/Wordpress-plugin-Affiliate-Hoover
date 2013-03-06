<?php namespace model;

class Model_Gateway extends \model\Database {
    
    private static $db = null;
    
    public function __construct() {

        parent::__construct();
        
    } // end __construct
    
    public static function db(){
        
        if(!(self::$db instanceof \model\Database)){
            
            self::$db = new \model\Database();
            
        }
        
        return self::$db;
        
    }

}

