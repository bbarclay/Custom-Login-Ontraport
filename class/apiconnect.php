<?php

//Add Base PHP
require_once( OLR_PLUGIN_DIR . 'lib/src/Ontraport.php');

//Use Ontraport 
use OntraportAPI\Ontraport;





class ontraconnect {


    private $appkey = null, $appid = null;
    private static $instance = null;
     
    private function __construct($ontraDetails = array()) {
         
        // Please note that this is Private Constructor
        $this->appkey = $ontraDetails['app_key'];
        $this->appid = $ontraDetails['app_id'];

 
        // Your Code here to connect to database //
        $this->client = new Ontraport($this->appid ,$this->appkey);

    }
     
    public static function connect($ontraDetails = array()) {
         
        // Check if instance is already exists      
        if(self::$instance == null) {
            self::$instance = new ontraconnect($ontraDetails);
        }
         
        return self::$instance;
         
    }

    public function getData() {
    	return $this->client;
    }
}
