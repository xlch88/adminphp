<?php
namespace AdminPHP;

use AdminPHP\Hook;

class View{
	private static $globalVar = [];
	private static $var = [];
	
	public static function addGlobalVar($var){
		self::$globalVar[] = $var;
	}
	
	public static function setGlobalVars($varList){
		self::$globalVar = $varList;
	}
	
	public static function setVar($var_, $value){
		self::$var[$var_] = &$value;
	}
	
	public static function setVars($varList){
		self::$var = $varList;
	}
	
	public static function view($templateFile_, $args = [], $isSystem = 0){
		foreach(self::$globalVar as $var____){
			$$var____ = isset($GLOBALS[$var____]) ? $GLOBALS[$var____] : null;
		}
		
		foreach(self::$var as $var____ => $var____){
			if(is_callable($var____)){
				$$var____ = $var____();
			}else{
				$$var____ = $var____;
			}
		}
		
		if(!isset($GLOBALS['view'])) $GLOBALS['view'] = [];
		foreach($args as $key____ => $value____){
			$GLOBALS['view'][$key____] = $value____;
		}
		foreach($GLOBALS['view'] as $key____ => $value____){
			$$key____ = $value____;
		}
		
		$templateFilePath	= $isSystem ? '' : templatePath;
		$templateFile		= $templateFile_ . '.php';
		Hook::doHook('template_echo', ['templateFilePath' => &$templateFilePath, 'templateFile' => &$templateFile, 'isSystem' => $isSystem]);
		
		include($templateFilePath . $templateFile);
	}
}