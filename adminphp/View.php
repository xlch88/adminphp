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
use AdminPHP\Engine\View\KeYao as ViewEngine_KeYao;

class View{
	private static $globalVar = [];
	private static $var = [];
	private static $engine = '';
	private static $engineClass = null;
	
	public static $subfix = '.php';
	
	public static function init($config){
		switch($config['engine']){
			case 'keyao':
				self::$engineClass = new ViewEngine_KeYao($config);
			break;
			
			default:
				throw new \InvalidArgumentException(l('模板引擎名称无效！'));
			break;
		}
		
		self::$engine = $config['engine'];
	}
	
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
	 * @param boolean $engine        是否使用模板渲染引擎
	 * @return void
	 */
	public static function view($file, $args = [], $isRoot = 0, $engine = true){
		global $a, $c, $m;
		
		if($engine){
			$file = self::$engineClass->getFile($file, $isRoot);
		}else{
			$_templateFilePath	= $_isRoot ? '' : templatePath;
			$file				= $file . self::$subfix;
			
			Hook::do('template_echo', ['templateFilePath' => &$_templateFilePath, 'templateFile' => &$file, 'isRoot' => $isRoot]);
			
			$_file = $_templateFilePath . $file;
		
			if(!is_file($_file)){
				throw new ViewException(0, $_templateFilePath, $file, $_file);
			}
			
			$data = file_get_contents($_file);
		}
		
		foreach(self::$var as $var_ => $value){
			if(is_callable($value)){
				$args[$var_] = $value();
			}else{
				$args[$var_] = &$value;
			}
		}
		
		unset($args['__file']);
		
		(function($__args, $__file){
			foreach(\AdminPHP\View::$globalVar as $__globalVar){
				global $$__globalVar;
			}
			
			extract($__args);
			unset($__args, $__globalVar);
			
			include($__file);
		})($args, $file);
	}
}