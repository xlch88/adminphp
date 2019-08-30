<?php
/* ----------------------------------------------- *
 | [ AdminPHP ] Version : 2.0 beta
 | 简单粗暴又不失高雅的迫真 OOP MVC 框架，，，
 |
 | URL     : https://www.adminphp.net/
 * ----------------------------------------------- *
 | Name    : Router (URL路由处理)
 |
 | Author  : Xlch88 (i@xlch.me)
 | LICENSE : WTFPL http://www.wtfpl.net/about
 * ----------------------------------------------- */

namespace AdminPHP;

use AdminPHP\Exception\RouterException;
use AdminPHP\AdminPHP;

class Router{
	static private $finalRoute = [];
	
	static public $args = [];
	static public $routeList = [];
	static public $methodPath = [ 'a' => '', 'c' => '', 'm' => '' ];
	static public $config = [];
	
	static public $regexList = [
		'int'		=> '[0-9]*?',
		'text'		=> '.*?'
	];
	
	static private $method2route = [];
	
	static public function init($config){
		global $a,$c,$m;
		
		self::$config = $config;
		
		Hook::do('app_init_router');
		
		uksort(self::$routeList, function($a, $b){
			return strlen($a) > strlen($b) ? -1 : 1;
		});
		
		list(self::$finalRoute, self::$method2route) = self::getFinalRoute(self::$routeList);
		$methodPath = self::parse_uri();
		
		if($methodPath !== FALSE){
			self::$methodPath = self::real_url($methodPath, true);
		}
		if($methodPath == FALSE && !in_array(self::get_uri(), ['/', ''])){ //未成功解析到路由且路径不为空
			self::notFound();
		}
		
		Hook::do('app_init_router_end');
		
		self::$methodPath['a'] = i('a', 0, 'path') ?: self::$methodPath['a'];
		self::$methodPath['c'] = i('c', 0, 'path') ?: self::$methodPath['c'];
		self::$methodPath['m'] = i('m', 0, 'path') ?: self::$methodPath['m'];
		
		if(!self::$methodPath['a'] && !self::$methodPath['c'] && !self::$methodPath['m']){
			list(self::$methodPath['a'], self::$methodPath['c'], self::$methodPath['m']) = self::real_url(self::$config['index']);
		}
		
		$a = self::$methodPath['a'] = (self::$methodPath['a'] ?: self::$config['default']['a']);
		$c = self::$methodPath['c'] = (self::$methodPath['c'] ?: self::$config['default']['c']);
		$m = self::$methodPath['m'] = (self::$methodPath['m'] ?: self::$config['default']['m']);
	}
	
	static private function notFound(){
		if(Hook::do('router_error')){ //用户自定义处理
			return;
		}
		
		sysinfo(l('@adminphp.sysinfo.statusCode.404', [], [
			'code'		=> 404,
			'type'		=> 'error',
			'title'		=> '啊哈... 出了一点点小问题_(:з」∠)_',
			'info'		=> '页面找不到啦...',
			'moreTitle'	=> '可能的原因：',
			'more'		=> [
				'该页面已移至其他地址',
				'手滑输错了地址',
				'你在用脸滚键盘',
				'你的猫在键盘漫步'
			]
		]));
	}
	
	static public function setRoutes($routes){
		self::$routeList = $routes;
	}
	static public function addRoute($route, $controller){
		self::$routeList[$route] = $controller;
	}
	static public function addRoutes($routes){
		self::$routeList = array_merge(self::$routeList, $routes);
	}
	
	static public function setRegex($regexList){
		self::$regexList = $regexList;
	}
	static public function addRegex($name, $regex){
		self::$regexList[$name] = $regex;
	}
	static public function addRegexes($addRegexes){
		self::$regexList = array_merge(self::$regexList, $addRegexes);
	}
	
	static private function getFinalRoute($routeList){
		$finalRoute = [];
		$method2route = [];
		foreach($routeList as $route => $method){
			$route_ = $route;

			$route = str_replace( //你永远不知道别人会写什么操蛋玩意儿
				['/', '?', '*', '+', '.', '[', ']', '^', '{', '}'],
				['\/', '\?', '\*', '\+', '\.', '\[', '\]', '\^', '\{', '\}'],
			$route);
			
			$option = [
				'method'	=> $method,
				'args'		=> []
			];
			
			$m2r = [
				'route'	=> $route_,
				'args'	=> []
			];
				
			if(strpos($route, '(') !== FALSE && strpos($route, ')') !== FALSE){
				//判断是否有未闭合的括号
				if(count(explode('(', $route)) != count(explode(')', $route))){
					throw new RouterException(0, $route);
				}
				preg_match_all('/\((.*?)\)/', $route, $match);
				
				foreach($match[0] as $index => $str){
					$rule = explode('|', $match[1][$index]);
					
					if(count($rule) != 2){
						throw new RouterException(1, $route, $match[1][$index]);
					}
					
					if(!in_array($rule[1], array_keys(self::$regexList))){
						throw new RouterException(2, $route, $rule[1]);
					}
					
					$option['args'][] = $rule[0];
					$m2r['args'][] = $rule;
				
					$route = str_replace($match[0][$index], '(' . self::$regexList[$rule[1]] . ')', $route);
				}
			}
			
			$m2r['preg'] = $route;
			$finalRoute[$route] = $option;
			$method2route[implode('/', self::real_url($method))] = $m2r;
		}
		
		return [$finalRoute, $method2route];
	}
	
	static public function get_uri(){
		$return = '';
		
		switch(self::$config['router']){
			case 1:
				if(!self::$config['rewrite']){
					$return = explode('?', substr($_SERVER['REQUEST_URI'], stripos($_SERVER['PHP_SELF'], '.php') + 4))[0];
				}else{
					$return = explode('?', $_SERVER['REQUEST_URI'])[0];
				}
			break;
			
			case 2:
				$return = '/' . i('s');
			break;
			
			case 3:
				if(!self::$config['rewrite']){
					$uri = $_SERVER['REQUEST_URI'];
					$return = '/' . (strpos($uri, '?') === FALSE ? '' : (strpos($uri, '&') !== FALSE ? substr($uri, strpos($uri, '?') + 1, strpos($uri, '&') - (strpos($uri, '?') + 1)) : substr($uri, strpos($uri, '?') + 1)));
				}else{
					$return = explode('?', $_SERVER['REQUEST_URI'])[0];
				}
			break;
		}
		
		return $return;
	}
	
	static public function mkurl($url, $args){
		$return = self::getUrl(false);
		
		if(!self::$config['rewrite']){
			$return.= substr($_SERVER['PHP_SELF'], 0, stripos($_SERVER['PHP_SELF'], '.php') + 4);
			
			switch(self::$config['router']){
				case 1:
					$return.= $url;
				break;
				
				case 2:
					$return.= '?s=' . substr($url, 1);
				break;
				
				case 3:
					$return.= '?' . substr($url, 1);
				break;
			}
		}else{
			$return = $url;
		}
		
		return $return . ($args ? (strpos($return, '?') !== FALSE ? '&' . $args : '?' . $args) : '');
	}
	
	static private function parse_uri(){
		$uri = self::get_uri();
		
		foreach(self::$finalRoute as $route => $option){
			if(preg_match_all('/^' . $route . '$/', $uri, $match)){
				//保存url内的参数
				foreach($option['args'] as $index => $key){
					self::$args[$key] = $match[$index + 1][0];
				}
				
				//返回方法
				return $option['method'];
			}
		}
		
		return false;
	}
	
	static public function url($route = '', $args = ''){
		global $c, $m, $a;
		
		$getRoute = false;
		if(self::$config['router'] != 0 && isset(self::$method2route[implode('/', self::real_url($route))])){
			$getRoute = true;
		
			$router = self::$method2route[implode('/', self::real_url($route))];
			
			$routerArgs = array_column($router['args'], 0);
			parse_str($args, $args_);
			foreach($routerArgs as $index => $arg_){
				if(!in_array($arg_, array_keys($args_))){ //参数不匹配 跳过
					$getRoute = false;
					break;
				}
				
				$router['route'] = str_replace('(' . $router['args'][$index][0] . '|' . $router['args'][$index][1] . ')', $args_[$arg_], $router['route']);
				
				unset($args_[$arg_]);
			}
			
			$router['args'] = http_build_query($args_);
			
			if(!preg_match('/^' . $router['preg'] . '$/', $router['route'])){
				$getRoute = false;
			}
		}
		
		if($getRoute){
			return self::mkurl($router['route'], $router['args']);
		}else{
			return self::getUrl(false) . '?' . http_build_query(self::real_url($route, true)) . ($args ? '&' . $args : '');
		}
	}

	//文本转换为实际路由
	static public function real_url($router, $iskey = false){
		$router = explode('/', $router);
		
		$return = [];
		
		if(count($router) == 3){
			$return['a'] = $router[0];
			$return['c'] = $router[1];
			$return['m'] = $router[2];
		}elseif(count($router) == 2){
			$return['a'] = lcfirst(self::$config['default']['a']);
			$return['c'] = $router[0];
			$return['m'] = $router[1];
		}elseif(count($router) == 1){
			$return['a'] = lcfirst(self::$config['default']['a']);
			$return['c'] = $router[0];
			$return['m'] = self::$config['default']['m'];
		}
		
		return $iskey ? $return : array_values($return);
	}

	//获取访问的url
	static public function getUrl($uri = true){
		$return = (self::is_ssl() ? 'https://' : 'http://') . (explode(':', $_SERVER['HTTP_HOST'])[0]) . ($_SERVER['SERVER_PORT'] != 80 && $_SERVER['SERVER_PORT'] != 443 ? ':' . $_SERVER['SERVER_PORT'] : '') . ($uri ? $_SERVER['REQUEST_URI'] : '/');
		
		return $return;
	}

	static public function is_ssl() {
		if(isset($_SERVER['HTTPS']) && ('1' == $_SERVER['HTTPS'] || 'on' == strtolower($_SERVER['HTTPS']))){
			return true;
		}elseif(isset($_SERVER['SERVER_PORT']) && ('443' == $_SERVER['SERVER_PORT'] )) {
			return true;
		}
		return false;
	}
}