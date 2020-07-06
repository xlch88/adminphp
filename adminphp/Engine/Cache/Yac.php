<?php
/* ----------------------------------------------- *
 | [ AdminPHP ] Version : 2.0 beta
 | 简单粗暴又不失高雅的迫真 OOP MVC 框架，，，
 |
 | URL     : https://www.adminphp.net/
 * ----------------------------------------------- *
 | Name    : CacheEngine/Memcached (缓存引擎 - memcached)
 |
 | Author  : Xlch88 (i@xlch.me)
 | LICENSE : WTFPL http://www.wtfpl.net/about
 * ----------------------------------------------- */

namespace AdminPHP\Engine\Cache;

class Yac {
	public $link = null;
	
	public function __construct($config){
		$this->link = new \Yac('adminphp' . (isset($config['prefix']) ? '_' . $config['prefix'] : ''));
	}
	
	public function get($key, $default = null){
		return $this->link->get($key) ?: $default;
	}
	
	public function set($key, $value, $expiry = 0){
		return $this->link->set($key, $value, $expiry);
	}
	
	public function delete($key){
		return $this->link->delete($key);
	}
}