<?php
namespace AdminPHP;

class AutoLoad{
	private static $registerAutoLoad = [];
	private static $included = [];
	
	public static function init(){
		spl_autoload_register(['\\AdminPHP\\AutoLoad', 'load']);
	}
	
	public static function register($class, $path, $prefix = '', $subfix = '.php'){
		$path = [
			'prefix'	=> $prefix,
			'subfix'	=> $subfix,
			'path'		=> $path . DIRECTORY_SEPARATOR
		];
		
		if(!is_dir($path['path'])){
			return false;
		}
		
		self::$registerAutoLoad[$class] = $path;
		
		return true;
	}
	
	public static function load($className){
		global $a;
	
		if(class_exists($className)){
			return;
		}
		
		$file = [];
		
		foreach(self::$registerAutoLoad as $class => $path){
			if(substr($className, 0, strlen($class)) == $class){
				$file[] = $path['path'] . $path['prefix'] . substr($className, strlen($class) + 1, strlen($className) + 1 - strlen($class)) . $path['subfix'];
			}
		}
		
		$path = explode('\\', $className);
		
		// App\Model\User
		// App\Controller\IndexController
		// App\Lib\QwQ
		
		// App\XLBook\Model\User
		// App\XLBook\Controller\IndexController
		// App\XLBook\Lib\QwQ
		
		// DB (adminphp/libraries)
		
		if($path[0] == 'App' && count($path) >= 3){
			if(in_array($path[1], ['Controller', 'Model', 'Lib'])){
				if($path[1] == 'Lib') $path[1] = 'libraries';
				
				$p = appPath . strtolower($path[1]) . DIRECTORY_SEPARATOR;
				$p3 = appPath . $a . DIRECTORY_SEPARATOR . strtolower($path[1]) . DIRECTORY_SEPARATOR;
				unset($path[0], $path[1]);
				
				$file[] = $p3 . implode(DIRECTORY_SEPARATOR, $path) . '.php';
				$file[] = $p . implode(DIRECTORY_SEPARATOR, $path) . '.php';
			}else{
				if($path[2] == 'Lib') $path[2] = 'libraries';
				
				$p = appPath . lcfirst($path[1]) . DIRECTORY_SEPARATOR . strtolower($path[2]) . DIRECTORY_SEPARATOR;
				unset($path[0], $path[1], $path[2]);
				$file[] = $p . implode(DIRECTORY_SEPARATOR, $path) . '.php';
			}
		}else if($path[0] == 'AdminPHP' && count($path) >= 2){
			if(in_array(strtolower($path[1]), ['lib', 'libraries'])) $path[1] = 'libraries';
			
			$p = adminphp;
			
			unset($path[0]);
			$file[] = $p . implode(DIRECTORY_SEPARATOR, $path) . '.php';
		}else if(count($path) >= 1){
			$file[] = adminphp . 'libraries' . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $path) . '.php';
			$file[] = adminphp . implode(DIRECTORY_SEPARATOR, $path) . '.php';
		}
		
		foreach($file as $file_){
			if($file_ = realpath($file_)){
				if(!in_array($file_, self::$included)){
					include($file_);
					self::$included[] = $file_;
				}
				return;
			}
		}
		
		throw new \Exception('can\'t autoload:' . $className . "\r\n" . print_r($file, true));
	}
}