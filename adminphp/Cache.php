<?php
/* ----------------------------------------------- *
 | [ AdminPHP ] Version : 2.0 beta
 | 简单粗暴又不失高雅的迫真 OOP MVC 框架，，，
 |
 | URL     : https://www.adminphp.net/
 * ----------------------------------------------- *
 | Name    : Cache (缓存)
 |
 | Author  : Xlch88 (i@xlch.me)
 | LICENSE : WTFPL http://www.wtfpl.net/about
 * ----------------------------------------------- */

namespace AdminPHP;

use AdminPHP\Engine\Cache\File as CacheEngine_File;

class Cache {
	static private $engine = 'file';
	static private $engineClass = null;
	
	static public function init($config = []){
		switch($config['engine']){
			case 'file':
				self::$engineClass = new CacheEngine_File($config);
			break;
			
			default:
				throw new \InvalidArgumentException(l('缓存存储引擎名称无效！'));
			break;
		}
		
		self::$engine = $config['engine'];
	}
	
	static public function e(){
		return self::$engineClass;
	}
	
	static public function eName(){
		return self::$engine;
	}
	
	static public function __callStatic($name, $arguments) {
		return call_user_func_array([self::$engineClass, $name], $arguments);
	}
}