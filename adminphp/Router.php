<?php
namespace AdminPHP;

use AdminPHP\Module\Config;
use AdminPHP\Module\PerformanceStatistics;
use AdminPHP\Module\Router\WebRouter;
use AdminPHP\Module\Router\CmdRouter;
use AdminPHP\Exception\RouterException;

class Router{
	static public $type = [];
	static public $routeTo = '';
	
	static public function init($webRouterConfig = [], $cmdRouterConfig = []){
		global $a, $c, $m;
		
		self::$type = self::getRunType();
		if(self::$type == 'cmd'){
			PerformanceStatistics::$show = false;
		}
		
		$config = self::$type == 'web' ? $webRouterConfig : $cmdRouterConfig;
		if(is_string($config) && !$config = is_string($config) ? Config::readFile($filename = $config) : $config){
			throw new RouterException(3, '', $filename, self::$type);
		}
		
		if(self::$type == 'web'){
			WebRouter::init($config);
		}else{
			CmdRouter::init($config);
		}
		
		if(!is_array(self::$routeTo) || count(self::$routeTo) !== 3 || array_keys(self::$routeTo) != ['a', 'c', 'm']){
			throw new RouterException(4, self::$routeTo);
		}
		
		$a = self::$routeTo['a'];
		$c = self::$routeTo['c'];
		$m = self::$routeTo['m'];
	}
	
	static public function getRunType(){
		global $argv;
		
		return isset($argv) ? 'cmd' : 'web';
	}
	
	static public function getUrl(){
		return '';
	}
}