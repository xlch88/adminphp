<?php
/* ----------------------------------------------- *
 | [ AdminPHP ] Version : 2.0 beta
 | 简单粗暴又不失高雅的迫真 OOP MVC 框架，，，
 |
 | URL     : https://www.adminphp.net/
 * ----------------------------------------------- *
 | Name    : Language (多语言支持)
 |
 | Author  : Xlch88 (i@xlch.me)
 | LICENSE : WTFPL http://www.wtfpl.net/about
 * ----------------------------------------------- */

namespace AdminPHP;

class Language {
	static private $cookieName	= '';
	static private $subfix		= '.php';
	static private $custom		= null;	//程序定义
	static private $path		= [
		adminphp . 'language' . DIRECTORY_SEPARATOR
	];
	
	static public $languages	= [];		//语言列表
	static public $default		= 'zh-CN';	//最后缺省
	static public $loadFiles	= [];		//记录已加载文件
	static public $lang			= [];		//语言数组
	
	public static function init($custom = 'zh-CN', $cookieName = 'adminphp_language', $subfix = '.php'){
		self::$custom		= $custom ?: null;
		self::$cookieName	= $cookieName;
		self::$subfix		= $subfix;
		
		self::initLanguages();
		self::loadLanguage();
	}
	
	private static function initLanguages(){
		if(isset($_COOKIE[self::$cookieName])){
			self::$languages[] = self::format($_COOKIE['adminphp_language']);
		}
		
		$accept = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
		
		foreach($accept as $index => $value){
			self::$languages[] = self::format(substr($value, 0, strpos($value, ';') ?: strlen($value)));
		}
		
		self::$languages = array_slice(array_values(array_unique(array_filter(self::$languages))), 0, 8);
		
		self::$languages[] = self::format(self::$custom);
		self::$languages[] = self::format(self::$default);
		
		self::$languages = array_unique(self::$languages);
		
		
		if(!isset($_COOKIE[self::$cookieName]) || !in_array($_COOKIE[self::$cookieName], self::$languages)){
			setcookie(self::$cookieName, self::$languages[0], 0, '/');
		}
	}
	
	private static function format($value){
		if(!preg_match('/^[a-z]{2}(|-[A-Z]{2})$/', $value)){
			return null;
		}
		
		return $value;
	}
	
	private static function loadLanguage(){
		foreach(self::$path as $path){
			self::loadLanguagePath($path);
		}
	}
	
	private static function loadLanguagePath($path){
		$files = scandir($path);
		
		if(($file = self::getLanguageFile($path, $files, self::$default)) !== null){
			$loadFile[] = $file;
		}
		
		if(($file = self::getLanguageFile($path, $files, self::$custom)) !== null){
			$loadFile[] = $file;
		}
		
		$loadFile = [];
		foreach(self::$languages as $lang){
			if(($file = self::getLanguageFile($path, $files, $lang)) !== null){
				$loadFile[] = $file;
				break;
			}
		}
		
		foreach($loadFile as $file){
			self::loadLanguageFile($file);
		}
	}
	
	private static function getLanguageFile($path, $files, $lang){
		$loadFile = null;
		
		if(in_array($lang . self::$subfix, $files)){
			$loadFile = $path . $lang . self::$subfix;
		}elseif(strlen($lang) == 2){
			foreach($files as $file){
				if(substr($file, 0, 2) == $lang && substr($file, -strlen(self::$subfix)) == self::$subfix){
					$loadFile = $path . $file;
					break;
				}
			}
		}
		
		return $loadFile;
	}
	
	private static function loadLanguageFile($file){
		if(in_array($file, self::$loadFiles)){
			return false;
		}
		
		self::$loadFiles[] = $file;
		
		if($result = include($file)){
			if(is_array($result)){
				self::$lang = array_merge($result, self::$lang);
				return true;
			}
		}
		
		return false;
	}
	
	/* 多语言处理
	 *
	 * $value   语言原文或者数组路径
	 * $args    参数
	 * $default 默认值
	 * 
	 * 若未从语言字典中找到$text,则使用$default,若没有传入$default则直接处理$text.
	 * 
	 * 返回 已处理的内容
	 */
	public static function languagePrintf($value, $args = [], $default = null){
		$args = !is_array($args) ? [ $args ] : $args;
		
		if($v = substr($value, 0, 1) == '@'){
			$v = self::getPathValue(self::$lang, substr($value, 1));
		}elseif(isset(self::$lang[$value])){
			$v = self::$lang[$value];
		}
		
		if($v === null){
			$v = $default !== NULL ? $default : $value;
		}
		
		$return = @self::arrayPrintf($v, $args);
		
		return $return;
	}
	
	public static function arrayPrintf($var, &$args){
		if(is_array($var)){
			foreach($var as $key => $value){
				$var[$key] = self::arrayPrintf($value, $args);
			}
		}else{
			$args_ = $args;
			
			$var = str_replace('%%', '[!u0025!]', $var);
			$args = substr_count($var, '%') > 0 ? array_slice($args, substr_count($var, '%')) : $args;
			$var = str_replace('[!u0025!]', '%%', $var);
			
			array_unshift($args_, $var);
			
			$var = @call_user_func_array('sprintf', $args_);
		}
		
		return $var;
	}
	
	public static function getPathValue($arr, $path, $explode = '.'){
		$path = explode($explode, $path);
		
		$value = $arr;
		foreach($path as $index){
			if(isset($value[$index])){
				$value = $value[$index];
			}else return null;
		}
		
		return $value;
	}
	
	public static function addPath($path){
		self::$path[] = in_array(['/', '\\'], substr($path, -1)) ? $path : $path . DIRECTORY_SEPARATOR;
	}
}