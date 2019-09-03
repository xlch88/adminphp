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
	
	public static $subfix = '.php';
	
	/**
	 * 获取已映射的全局变量
	 * 
	 * @return array
	 */
	public static function getGlobalVar(){
		return self::$globalVar;
	}
	
	/**
	 * 获取注册的变量
	 * 
	 * @return array
	 */
	public static function getVar(){
		return self::$var;
	}
	
	/**
	 * 添加映射全局变量
	 * 
	 * @param string $var 变量名
	 * @return void
	 */
	public static function addGlobalVar($var){
		self::$globalVar[] = $var;
	}
	
	/**
	 * 置映射全局变量
	 * 
	 * @param array $varList 变量名列表
	 * @return void
	 */
	public static function setGlobalVars($varList){
		self::$globalVar = $varList;
	}
	
	/**
	 * 添加变量
	 * 
	 * @param array $var_  变量名
	 * @param array $value 值,可为函数
	 * @return void
	 */
	public static function setVar($var_, $value){
		self::$var[$var_] = &$value;
	}
	
	/**
	 * 置变量列表
	 * 
	 * @param array $varList 变量列表
	 * @return void
	 */
	public static function setVars($varList){
		self::$var = $varList;
	}
	
	/**
	 * 输出模板
	 * 函数别名: view()
	 * 
	 * @param string  $_templateFile 模板文件
	 * @param array   $args          参数
	 * @param boolean $_isRoot       是否从根目录
	 * @return void
	 */
	public static function view($_templateFile, $args = [], $_isRoot = 0){
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
		
		$_templateFilePath	= $_isRoot ? '' : templatePath;
		$_templateFile		= $_templateFile . self::$subfix;
		Hook::do('template_echo', ['templateFilePath' => &$_templateFilePath, 'templateFile' => &$_templateFile, 'isRoot' => $_isRoot]);
		
		
		$_file = $_templateFilePath . $_templateFile;
		
		if(!is_file($_file)){
			throw new ViewException(0, $_templateFilePath, $_templateFile, $_file);
		}
		
		unset($var____, $var___, $key____, $value____, $_templateFile, $_templateFilePath);
		
		include($_file);
	}
}