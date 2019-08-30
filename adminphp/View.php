<?php
/* ----------------------------------------------- *
 | [ AdminPHP ] Version : 2.0 beta
 | 简单粗暴又不失高雅的迫真 OOP MVC 框架，，，
 |
 | URL     : https://www.adminphp.net/
 * ----------------------------------------------- *
 | Name    : View (视图、模板)
 |
 | Author  : Xlch88 (i@xlch.me)
 | LICENSE : WTFPL http://www.wtfpl.net/about
 * ----------------------------------------------- */

namespace AdminPHP;

use AdminPHP\Hook;
use AdminPHP\Exception\ViewException;

class View{
	private static $globalVar = [];
	private static $var = [];
	
	public static function getGlobalVar(){
		return self::$globalVar;
	}
	
	public static function getVar(){
		return self::$var;
	}
	
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
	
	public static function view($templateFile_, $args = [], $_isSystem = 0){
		global $a, $c, $m;
		
		foreach(self::$globalVar as $var____){
			$$var____ = isset($GLOBALS[$var____]) ? $GLOBALS[$var____] : null;
		}
		
		foreach(self::$var as $var___ => $var____){
			if(is_callable($var____)){
				$$var___ = $var____();
			}else{
				$$var___ = $var____;
			}
		}
		
		if(!isset($GLOBALS['view'])) $GLOBALS['view'] = [];
		foreach($args as $key____ => $value____){
			$GLOBALS['view'][$key____] = $value____;
		}
		
		foreach($GLOBALS['view'] as $key____ => $value____){
			if(isset($$key____) && is_array($value____) && is_array($$key____)){
				$$key____ = array_merge($$key____, $value____);
			}else{
				$$key____ = $value____;
			}
		}
		
		$_templateFilePath	= $_isSystem ? '' : templatePath;
		$_templateFile		= $templateFile_ . '.php';
		Hook::do('template_echo', ['templateFilePath' => &$_templateFilePath, 'templateFile' => &$_templateFile, 'isSystem' => $_isSystem]);
		
		
		$_file = $_templateFilePath . $_templateFile;
		
		if(!is_file($_file)){
			throw new ViewException(0, $_templateFilePath, $_templateFile, $_file);
		}
		
		unset($var____, $var___, $key____, $value____, $_templateFile, $_templateFilePath);
		
		include($_file);
	}
}