<?php
namespace AdminPHP;

use AdminPHP\Exception\RouterException;

class Router{
	static private $finalRoute = [];
	
	static public $args = [];
	static public $routeList = [];
	static public $methodPath = [ 'a' => '', 'c' => '', 'm' => '' ];
	
	static public $regexList = [
		'int'	=> '[0-9]*?'
	];
	
	static private $method2route = [];
	
	
	
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
	
	static public function init(){
		global $a,$c,$m;
		
		Hook::doHook('app_init_router');
		
		list(self::$finalRoute, self::$method2route) = self::getFinalRoute(self::$routeList);
		$methodPath = self::parse_uri();
		
		if($methodPath !== FALSE){
			self::$methodPath = self::real_url($methodPath, true);
		}
		
		self::$methodPath['a'] = i('a', 0, 'path') ?: self::$methodPath['a'];
		self::$methodPath['c'] = i('c', 0, 'path') ?: self::$methodPath['c'];
		self::$methodPath['m'] = i('m', 0, 'path') ?: self::$methodPath['m'];
		
		Hook::doHook('app_init_router_end');
		
		$a = self::$methodPath['a'] = (self::$methodPath['a'] ?: defaultApp);
		$c = self::$methodPath['c'] = (self::$methodPath['c'] ?: 'index');
		$m = self::$methodPath['m'] = (self::$methodPath['m'] ?: 'index');
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
					
					$m2r['preg'] = $route;
				}
			}
			
			$finalRoute[$route] = $option;
			$method2route[implode('/', self::real_url($method))] = $m2r;
		}
		
		return [$finalRoute, $method2route];
	}
	
	static private function parse_uri(){
		$uri = explode('?', $_SERVER['REQUEST_URI'])[0];
		
		foreach(self::$finalRoute as $route => $option){
			if(preg_match_all('/^' . $route . '$/', $uri, $match)){
				//保存url内的参数
				foreach($option['args'] as $index => $key){
					self::$args[$key] = $match[1][$index];
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
		if(isset(self::$method2route[implode('/', self::real_url($route))])){
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
			return $router['route'] . ($router['args'] ? '?' . $router['args'] : '');
		}else{
			if($route !== ''){
				$route = explode('/', $route);
				
				$return = [];
				
				if(count($route) == 3){
					$return[]	= 'a=' . $route[0];
					$return[]	= 'c=' . $route[1];
					$return[]	= 'm=' . $route[2];
				}elseif(count($route) == 2){
					$return[]	= 'c=' . $route[0];
					$return[]	= 'm=' . $route[1];
				}elseif(count($route) == 1){
					$return[]	= 'c=' . $route[0];
				}else{
					throw new Exception('error route');
				}
			}else{
				$return[]	= 'a=' . $a;
				$return[]	= 'c=' . $c;
				$return[]	= 'm=' . $m;
			}
			return '/?' . implode('&', $return) . ($args ? '&' . $args : '');
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
			$return['a'] = lcfirst(defaultApp);
			$return['c'] = $router[0];
			$return['m'] = $router[1];
		}elseif(count($router) == 1){
			$return['a'] = lcfirst(defaultApp);
			$return['c'] = $router[0];
			$return['m'] = 'index';
		}
		
		return $iskey ? $return : array_values($return);
	}

	//获取访问的url
	static public function getUrl(){
		$return = (self::is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . ($_SERVER['SERVER_PORT'] != 80 && $_SERVER['SERVER_PORT'] != 443 ? ':' . $_SERVER['SERVER_PORT'] : '') . $_SERVER['REQUEST_URI'];
		
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