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
	
	/**
	 * 初始化类自动加载
	 * 
	 * @return void
	 */
	public static function init(){
		throw new \Exception('The module is obsolete');
		spl_autoload_register(['\\AdminPHP\\AutoLoad', 'load']);
		self::register('', adminphp . 'Libraries', 'none');
		self::register('AdminPHP', adminphp, 'none');
	}
	
	public static function initRegister(){
		throw new \Exception('The module is obsolete');
		self::register('', appPath . 'Common/Lib', 'none');
		self::register('App', appPath, 'none');
		self::register('App\\Model', appPath . 'Common/Model', 'none');
		self::register('App\\Lib', appPath . 'Common/Lib', 'none');
	}
	
	/**
	 * 注册类自动加载
	 * 
	 * @param string $class    命名空间
	 * @param string $path     文件加载路径
	 * @param string $first|lc 路径首字母处理(lc = 首字母转换小写, uc = 首字母大写, none = 不处理)
	 * @param string $prefix   文件名前缀
	 * @param string $subfix   文件名后缀
	 * @return void
	 */
	public static function register($class, $path, $first = 'none', $prefix = '', $subfix = '.php'){
		throw new \Exception('The module is obsolete');
		$path = [
			'prefix'	=> $prefix,
			'subfix'	=> $subfix,
			'path'		=> realpath($path) . DIRECTORY_SEPARATOR,
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
	
	/**
	 * 加载文件
	 * PHP会在找不到类的时候自动回调该函数
	 * 也可以手动执行该函数，获取类的文件路径
	 * 
	 * @param string $class  类名
	 * @param string $path   未找到文件/发生错误后是否抛出异常
	 * @param string $return 不进行加载,只返回文件名
	 * @return void
	 */
	public static function load($className, $exception = true, $return = false){
		throw new \Exception('The module is obsolete');
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
			if(($file = realpath($file)) && is_file($file)){
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
		
		if((!class_exists($className, false) && !trait_exists($className, false) && !interface_exists($className, false)) && $exception){
			if($file){
				throw new AutoLoadException(1, $className, $files, $file);
			}else{
				throw new AutoLoadException(0, $className, $files);
			}
		}
		return false;
	}
}