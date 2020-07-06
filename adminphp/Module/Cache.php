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

namespace AdminPHP\Module;

use AdminPHP\Engine\Cache\File		as CacheEngine_File;
use AdminPHP\Engine\Cache\Memcached	as CacheEngine_Memcached;
use AdminPHP\Engine\Cache\Memcache	as CacheEngine_Memcache;
use AdminPHP\Engine\Cache\Yac		as CacheEngine_Yac;

class Cache {
	static private $engine = 'file';
	static public  $engineClass = null;
	static public  $readList = [];
	static public  $writeList = [];
	
	static public function init($config = []){
		switch($config['engine']){
			case 'file':
				self::$engineClass = new CacheEngine_File($config);
			break;
			
			case 'memcached':
				self::$engineClass = new CacheEngine_Memcached($config);
			break;
			
			case 'memcache':
				self::$engineClass = new CacheEngine_Memcache($config);
			break;
			
			case 'yac':
				self::$engineClass = new CacheEngine_Yac($config);
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
		if(self::$engineClass == null){
			return false;
		}
		
		$result = call_user_func_array([self::$engineClass, $name], $arguments);
		
		if($name == 'get' && $result !== false){
			self::$readList[] = $arguments[0];
		}elseif($name == 'set' && $result !== false){
			self::$writeList[] = $arguments[0];
		}
		
		return $result;
	}
}