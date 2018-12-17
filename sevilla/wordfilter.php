<?php
	
	class WordFilterProcessing {
		public $blacklist;
	
		function __construct() {
		  	$str = file_get_contents(dirname(__FILE__) . "/badwords.json");
		  	$this->blacklist = json_decode($str, true);
		}
		
		function isBlacklisted($string) {
		  	$test_string = mb_strtolower($string, 'UTF-8');
		  	foreach ($this->blacklist as $badword) {
				if (strpos($test_string, $badword) !== false) {
			  		return true;
				}
		  	}
		  	return false;
		}
	}