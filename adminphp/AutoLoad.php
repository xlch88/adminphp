<?php
/* ----------------------------------------------- *
 | [ AdminPHP ] Version : 2.0 beta
 | 简单粗暴又不失高雅的迫真 OOP MVC 框架，，，
 |
 | URL     : https://www.adminphp.net/
 * ----------------------------------------------- *
 | Name    : AutoLoad (Class自动加载)
 |
 | Author  : Xlch88 (i@xlch.me)
 | LICENSE : WTFPL http://www.wtfpl.net/about
 * ----------------------------------------------- */

namespace AdminPHP;

use AdminPHP\Exception\AutoLoadException;

class AutoLoad{
	private static $registerAutoLoad = [];
	public static $included = [];
	
	public static function init(){
		spl_autoload_register(['\\AdminPHP\\AutoLoad', 'load']);
		
		self::register('AdminPHP', adminphp);
		self::register('App', appPath);
		self::register('App\\Model', appPath . 'common/model');
		self::register('App\\Lib', appPath . 'common/lib');
		
		self::register('', adminphp . 'libraries', 'none');
		self::register('', appPath . 'common/lib', 'none');
	}
	
	public static function register($class, $path, $first = 'lc', $prefix = '', $subfix = '.php'){
		$path = [
			'prefix'	=> $prefix,
			'subfix'	=> $subfix,
			'path'		=> in_array(substr($path, -1), ['\\', '/']) ? $path : $path . DIRECTORY_SEPARATOR,
			'first'		=> $first
		];
		
		if(!is_dir($path['path'])){
			return false;
		}
		
		$class = substr($class, 0, 1) == '\\' ? $class : '\\' . $class;
		
		self::$registerAutoLoad[$class][] = $path;
		
		uksort(self::$registerAutoLoad, function($a, $b){
			return strlen($a) > strlen($b) ? -1 : 1;
		});
		
		return true;
	}
	
	public static function load($className, $exception = true, $return = false){
		$className = substr($className, 0, 1) == '\\' ? $className : '\\' . $className;
		
		if(class_exists($className, false)){
			return;
		}
		
		$files = [];
		
		foreach(self::$registerAutoLoad as $class => $paths){
			foreach($paths as $index => $path){
				if(substr($className, 0, strlen($class)) == $class){
					$filepath = [];
					
					$className_ = explode('\\', substr($className, -(strlen($className) - strlen($class))));
					$loadpathc = count(explode('\\', $className));
					foreach($className_ as $index => $path_){
						if(count($className_) == $index + 1){
							$path_ = $path['prefix'] . $path_ . $path['subfix'];
						}else{
							switch($path['first']){
								case 'lc':
									$path_ = lcfirst($path_);
								break;
								
								case 'uc':
									$path_ = ucfirst($path_);
								break;
							}
						}
						
						$filepath[] = $path_;
					}
					
					$files[] = $path['path'] . implode(DIRECTORY_SEPARATOR, $filepath);
				}
			}
		}
		
		$file = '';
		foreach($files as $file){
			if($file = realpath($file)){
				if(!in_array($file, self::$included)){
					if($return){
						return $file;
					}
					
					include($file);
					self::$included[] = $file;
				}
				break;
			}
		}
		
		if($return){
			return false;
		}
		
		if(!class_exists($className, false) && $exception){
			if($file){
				throw new AutoLoadException(1, $className, $files, $file);
			}else{
				throw new AutoLoadException(0, $className, $files);
			}
		}
		return false;
	}
}