<?php
namespace AdminPHP;

class Language{
	static public $lang = [];
	static public $path = [
		adminphp . 'language'
	];
	
	public static function init(){
	}
	
	private static function getClientLanguage(){
		$return = [];
		
		if(isset($_COOKIE['adminphp_language'])){
			$return[] = $_COOKIE['adminphp_language'];
		}
		
		$_SERVER["HTTP_ACCEPT_LANGUAGE"];
	}
	
	public static function setLang(){
		
	}
}