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

class Memcache {
	public $link = null;
	
	public function __construct($config){
		$config = array_merge([
			'servers'	=> []
		], $config);
		
		if(count($config['server']) == 0){
			throw new \InvalidArgumentException(l('请至少添加一个memcache服务器。'));
		}
		
		$this->link = new \Memcache();
		foreach($config['servers'] as $server){
			if(count($server) < 2) continue;
			
			$this->link->addServer(
				$server[0],
				$server[1],
				isset($server[2]) ? $server[2] : true,
				isset($server[3]) ? $server[3] : 1,
				isset($server[4]) ? $server[4] : 1,
				isset($server[5]) ? $server[5] : 15,
				isset($server[6]) ? $server[6] : true,
				isset($server[7]) ? $server[7] : null,
				isset($server[8]) ? $server[8] : null,
			);
		}
	}
	
	public function get($key, $default = null){
		return $this->link->get($key, false) ?: $default;
	}
	
	public function set($key, $value, $expiry = 0){
		return $this->link->set($key, $value, 0, $expiry);
	}
	
	public function delete($key){
		return $this->link->delete($key);
	}
}