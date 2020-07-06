<?php
namespace AdminPHP\Module\Router;

use AdminPHP\Router;
use AdminPHP\AdminPHP;
use AdminPHP\Hook;

class WebRouter{
	/**
	 * 路由传入参数
	 *
	 * @var array
	 */
	static public $args = [];

	/**
	 * url信息
	 *
	 * @var array
	 */
	static public $info = [
		'https'			=> '',
		'domain'		=> '',
		'port'			=> '',
		'path'			=> '',
		'root'			=> '',
		
		'matchDomain'	=> '',
		'domainArgs'	=> []
	];
	
	/**
	 * url转为路径，用于将传入的url解析为路由路径
	 *
	 * @var array
	 */
	static private $urlToPath = [];

	/**
	 * 路径转为url
	 *
	 * @var array
	 */
	static private $pathToUrl = [];
	
	/**
	 * 配置信息
	 *
	 * @var array
	 */
	static public $option = [
		'index'		=> 'book/welcome/index',
		
		'default'	=> [
			'a'	=> 'book',
			'c'	=> 'index',
			'm'	=> 'index'
		],
		
		'router'	=> 1,
		'rewrite'	=> 1,
		
		'autoRoute'	=> [
			'enable'	=> true,
			'prefix'	=> '',
			'explode'	=> '/',
			'subfix'	=> '.html',
		],
		
		'disableArgumentAccess'	=> false
	];
	
	/**
	 * 用于匹配url的正则表达式列表
	 *
	 * @var array
	 */
	static public $regexList = [
		'int'		=> '[0-9]*?',
		'text'		=> '.*?'
	];
	
	/**
	 * 初始化
	 *
	 * @param array $config
	 * @return void
	 */
	static public function init($config){
		$config = array_merge([
			'options'	=> [],
			'regex'		=> [],
			'routes'	=> []
		], $config);
		
		self::$option = array_merge2(self::$option, $config['options']);
		self::$regexList = array_merge(self::$regexList, $config['regex']);
		self::initWebRoute($config['routes']);
		
		self::$info = [
			'https'			=> self::isSSL(),
			'domain'		=> isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '',
			'port'			=> isset($_SERVER['SERVER_PORT']) ? isset($_SERVER['SERVER_PORT']) : 80,
			'path'			=> self::parsePath(),
			'root'			=> self::getRoot(),
			
			'matchDomain'	=> '',
			'domainArgs'	=> []
		];
		
		if((Router::$routeTo = self::parseWebRoute()) === false){
			if(!in_array(self::$info['path'], ['', '/'])){//未能匹配到,且url路径不为空3
				self::notFound();
				return;
			}else{
				Router::$routeTo = self::realPath(self::$option['index'], false);
			}
		}
	}
		
	/**
	 * 页面未找到回调
	 *
	 * @return void
	 */
	static private function notFound(){
		if(AdminPHP::appEvent('onNotFound') || Hook::do('router_error')){ //用户自定义处理
			die();
		}
		
		set_http_code(404);
		
		sysinfo(l('@adminphp.sysinfo.statusCode.404', [], [
			'code'		=> 404.1,
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
	

	/**
	 * 初始化web路由
	 *
	 * @param array $routes
	 * @return void
	 */
	static private function initWebRoute(array $routes){
		$pathToUrl = [];
		$urlToPath = [];
		
		//第一层遍历 域名->路由列表
		foreach($routes as $domain => $routeList){
			//第二层遍历 路由列表 -> [地址 : 解析到的路径]
			foreach($routeList as $url => $path){
				//分割解析到的路径?后面的参数
				$path = explode('?', $path);
				//格式化解析到的路径，确保为三个参数。
				$path[0] = self::realPath($path[0], true);
				
				//转义 用于正则表达式匹配浏览器传入的地址
				$url2 = str_replace(
					['/', '?', '*', '+', '.', '[', ']', '^', '{', '}'],
					['\/', '\?', '\*', '\+', '\.', '\[', '\]', '\^', '\{', '\}'],
				$url);
				
				$args = [];
				//判断地址里面有没有括号(url参数)
				if(strpos($url, '(') !== FALSE && strpos($url, ')') !== FALSE){
					//判断是否有未闭合的括号
					if(count(explode('(', $url)) != count(explode(')', $url))){
						die('有未闭合括号');
						//throw new RouterException(0, $route);
					}
					
					//正则表达式匹配出参数列表
					preg_match_all('/\((.*?)\)/', $url, $match);
					
					//遍历匹配结果
					foreach($match[0] as $index => $str){
						$rule = explode('|', $match[1][$index]);
						
						//如果分割后参数数量不为2
						if(count($rule) != 2){
							die('参数未指定其匹配的正则表达式');
							//throw new RouterException(1, $route, $match[1][$index]);
						}
						
						//使用的正则表达式不存在
						if(!in_array($rule[1], array_keys(self::$regexList))){
							die('正则表达式不存在:' . $rule[1]);
							//throw new RouterException(2, $route, $rule[1]);
						}
						
						$args[$rule[0]] = $rule[1];
						$url2 = str_replace($match[0][$index], '(' . self::$regexList[$rule[1]] . ')', $url2);
						$url = str_replace($match[0][$index], '(' . self::$regexList[$rule[1]] . ')', $url);
					}
				}
				
				//解析额外参数
				parse_str(isset($path[1]) ? $path[1] : '', $extArgs);
				
				$pathToUrl[$path[0]][] = [
					'domain'	=> $domain,
					'url'		=> $url,
					'extArgs'	=> $extArgs,
					'args'		=> $args
				];
				
				$urlToPath[$domain][$url2] = [
					'path'		=> $path[0],
					'extArgs'	=> $extArgs,
					'args'		=> array_keys($args)
				];
			}
		}
		
		//排序
		foreach($urlToPath as $index => $value){
			uksort($value, function($a, $b){
				return strlen($a) > strlen($b) ? -1 : 1;
			});
			
			$urlToPath[$index] = $value;
		}
		uksort($urlToPath, function($a, $b){
			return strlen($a) > strlen($b) ? -1 : 1;
		});
		
		//对pathToUrl根据子键domain长度进行排序
		foreach($pathToUrl as $index => $value){
			usort(
			$value, 
			function($a, $b){
				return strlen($a['domain']) > strlen($b['domain']) ? -1 : 1;
			});
			
			$pathToUrl[$index] = $value;
		}
		
		self::$urlToPath = $urlToPath;
		self::$pathToUrl = $pathToUrl;
	}
	/**
	 * 解析WEB路由
	 * 返回 ['a' => app, 'c' => controller, 'm' => method]
	 *
	 * @return array
	 */
	static private function parseWebRoute(){
		foreach(self::$urlToPath as $domain => $urls){
			//转义域名 用于支持通配符* 进行正则表达式匹配
			$domain_ = $domain;
			$domain = '/^' . str_replace(
				['.', '*'],
				['\.', '(.*?)'],
			$domain) . '$/';
			
			//匹配域名
			if($r = preg_match($domain, self::$info['domain'], $matches)){
				//进一步遍历匹配url
				foreach($urls as $url => $info){
					//对于要求指定访问方法的url进行特殊处理
					if(substr($url, 0, 4) == 'get:'){
						$method = 'get';
						$url = substr($url, 4);
					}elseif(substr($url, 0, 5) == 'post:'){
						$method = 'post';
						$url = substr($url, 5);
					}else{
						$method = 'all';
					}
					
					if(
						($method == 'post' && strtolower($_SERVER['REQUEST_METHOD']) != 'post') ||
						($method == 'get' && strtolower($_SERVER['REQUEST_METHOD']) == 'post')
					){
						continue;
					}
					
					//正则表达式匹配url
					if(preg_match_all('/^' . $url . '$/', self::$info['path'], $matches2)){
						//匹配到了，将参数并入$args
						foreach($info['args'] as $index => $key){
							self::$args[$key] = $matches2[$index + 1][0];
						}
						
						//附加参数
						if($info['extArgs']){
							self::$args = array_merge(self::$args, $info['extArgs']);
						}
						
						//装填$info['matchDomain'] 和 $info['domainArgs']
						self::$info['matchDomain'] = $domain_;
						self::$info['domainArgs'] = $matches;
						
						//返回路由到的路径
						return self::realPath($info['path'], false);
					}
				}
			}
		}
		
		//没有匹配到，匹配自动路由
		if(self::$option['autoRoute']['enable'] && !self::$option['disableArgumentAccess']){
			$path = self::$info['path'];
			
			if(substr($path, 0, 1) == '/'){
				$path = substr($path, 1);
			}
			if(
				substr($path, 0, strlen(self::$option['autoRoute']['prefix'])) === self::$option['autoRoute']['prefix'] &&
				count($explode = explode(self::$option['autoRoute']['explode'], substr(
					$path,
					strlen(self::$option['autoRoute']['prefix']),
					strlen($path) - strlen(self::$option['autoRoute']['prefix']) - strlen(self::$option['autoRoute']['subfix'])
				))) === 3 && 
				substr($path, -strlen(self::$option['autoRoute']['subfix'])) === self::$option['autoRoute']['subfix']
			){
				//这里safepath过滤一下
				
				return [
					'a'	=> $explode[0],
					'c'	=> $explode[1],
					'm'	=> $explode[2],
				];
			}
		}
		
		//还是没有匹配到，从参数获取
		if(!self::$option['disableArgumentAccess']){
			if(isset($_GET['a']) || isset($_GET['c']) || isset($_GET['m'])){
				return [
					'a'	=> isset($_GET['a']) ? $_GET['a'] : self::$option['default']['a'],
					'c'	=> isset($_GET['c']) ? $_GET['c'] : self::$option['default']['c'],
					'm'	=> isset($_GET['m']) ? $_GET['m'] : self::$option['default']['m'],
				];
			}
		}
		
		return false;
	}
	/**
	 * 生成url地址
	 *
	 * @param string $path
	 * @param string $domain
	 * @return void
	 */
	static public function url(string $path = '', $domain = null){
		if($path == ''){
			return self::mkurl(null);
		}
		
		$tmp = explode('?', $path);
		$path = self::realPath($tmp[0]);
		
		if(count($tmp) > 1){
			unset($tmp[0]);
			parse_str(implode('?', $tmp), $args);
		}else{
			$args = [];
		}
		
		//至少要开启router功能，才可以生成路由地址 or 自动路由
		if(self::$option['router'] != 0){
			//遍历$pathToUrl
			foreach(self::$pathToUrl as $path_ => $rules){
				//如果传入的path和遍历到的path相等
				if($path_ == $path){
					foreach($rules as $index => $rule){
						//判断extArgs是否都匹配
						foreach($rule['extArgs'] as $key => $value){
							if(!isset($args[$key]) || $args[$key] != $value){
								continue 2;
							}
							
							unset($args[$key]);
						}
						
						//返回的内容
						$return = $rule['url'];
						
						//判断args是否都存在，且能够匹配
						foreach($rule['args'] as $key => $regexName){
							if(!isset($args[$key]) || !preg_match('/^' . self::$regexList[$regexName] . '$/', $args[$key])){
								continue 2;
							}
							
							//顺便替换掉
							$return = str_replace('(' . self::$regexList[$regexName] . ')', $args[$key], $return);
							
							unset($args[$key]);
						}
						
						//判断域名是否能够匹配
						if(!$domain && $rule['domain'] != '*' && strpos($rule['domain'], '*') !== FALSE){
							//如果该路由域名包含通配符，并且$domain未传入，直接跳过。
							continue;
						}elseif(strpos($rule['domain'], '*') === FALSE){
							//域名不包含通配符
							$domain = $rule['domain'];
						}elseif($rule['domain'] == '*' && !$domain){
							//域名任意，则使用当前域名
							$domain = null;
						}else{
							//域名包含通配符，开始判断传入的域名能否匹配
							//转义域名 用于支持通配符* 进行正则表达式匹配
							$preg = '/^' . str_replace(
								['.', '*'],
								['\.', '(.*?)'],
							$rule['domain']) . '$/';
							if(!preg_match($preg, $domain)){
								//传入域名未能匹配
								continue;
							}
						}
						
						//对于要求指定访问方法的url进行特殊处理
						if(substr($return, 0, 4) == 'get:'){
							$return = substr($return, 4);
						}elseif(substr($return, 0, 5) == 'post:'){
							$return = substr($return, 5);
						}
						
						//得 通过验证
						return self::mkurl($return, $args, $domain);
					}
				}
			}
			
			//没匹配到，生成自动路由
			if(self::$option['autoRoute']['enable'] && !self::$option['disableArgumentAccess']){
				$path	=	self::realPath($path, false);
				$return =	self::$option['autoRoute']['prefix'] . 
							implode(self::$option['autoRoute']['explode'], array_values($path)) .
							self::$option['autoRoute']['subfix'];
				return self::mkurl($return, $args, $domain);
			}
		}
		
		//好吧 那只好/?a=[app]&c=[controller]&m=[method]
		if(!self::$option['disableArgumentAccess']){
			$args = array_merge(self::realPath($path, false), $args);
			return self::mkurl('', $args);
		}
		
		//嗯哼，抛异常啦
		die('未能匹配路由:' . $path . '?' . http_build_query($args));
	}
	
	/**
	 * 生成网址
	 *
	 * @param string $path
	 * @param array|string $args
	 * @param string $domain
	 * @param int $port
	 * @param boolean $https
	 * @return string
	 */
	static public function mkurl($path = null, $args = null, $domain = null, $port = null, $https = null){
		if(Router::$type == 'cmd'){
			return false;
		}
		
		//读取缺省值
		if(is_null($path))		$path	= self::$info['path'];
		if(is_null($args))		$args	= $_GET;
		if(is_null($domain))	$domain	= self::$info['domain'];
		if(is_null($port))		$port	= self::$info['port'];
		if(is_null($https))		$https	= self::$info['https'];
		
		//如果是https或者端口是80，就没必要显示端口
		if($https || $port == 80 || $port == 443){
			$port = '';
		}
		
		//数组args转为http参数
		if(!is_string($args)){
			$args = http_build_query($args);
		}
		
		//如非必要，前面都不要带斜杠
		if(substr($path, 0, 1) == '/'){
			$path = substr($path, 1);
		}
		
		//未开启rewrite
		if(!self::$option['rewrite']){
			$root = self::$info['root'];
			if(strtolower(substr($root, -9)) == 'index.php' && (self::$option['router'] == 2 || self::$option['router'] == 3)){
				$root = substr($root, 0, strlen($root) - 9);
			}
			
			if($path !== ''){
				switch(self::$option['router']){
					case 1:
						$path = '/' . $path;
					break;
					
					case 2:
						$path = '?s=' . $path;
					break;
					
					case 3:
						$path = '?' . $path;
					break;
				}
			}
			
			$path = $root . $path;
		}
		
		
		$return = '';
		$return .= $https ? 'https://' : 'http://';
		$return .= $domain;
		$return .= $port ? ':' . $port : '';
		$return .= substr($path, 0, 1) == '/' ? $path : '/' . $path;
		if($args) $return .= strpos($path, '?') !== FALSE ? '&' . $args : '?' . $args;
		
		return $return;
	}
	
	/**
	 * 获取执行脚本名称
	 *
	 * @return void
	 */
	static public function getRoot(){
		$return = $_SERVER['SCRIPT_NAME'];
		return $return;
	}
	
	/**
	 * 是否https
	 *
	 * @return boolean
	 */
	static public function isSSL(){
		return (isset($_SERVER['HTTPS']) && ('1' == $_SERVER['HTTPS'] || 'on' == strtolower($_SERVER['HTTPS']))) || isset($_SERVER['SERVER_PORT']) && ('443' == $_SERVER['SERVER_PORT']);
	}
	
	/**
	 * 获取url传入的路径
	 *
	 * @return string
	 */
	static public function parsePath(){
		$return = '';
		
		switch(self::$option['router']){
			case 1:		//1 = PATH_INFO		=> index.php/aaa/bbb/ccc
				$return = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
			break;
			
			case 2:		//2 = $_GET['s']	=> index.php?s=aaa/bbb/ccc
				$return = isset($_GET['s']) ? $_GET['s'] : '';
			break;
			
			case 3:		//3 = index.php?	=> index.php?aaa/bbb/ccc
				$uri = $_SERVER['REQUEST_URI'];
				$return = '/' . (strpos($uri, '?') === FALSE ? '' : (strpos($uri, '&') !== FALSE ? substr($uri, strpos($uri, '?') + 1, strpos($uri, '&') - (strpos($uri, '?') + 1)) : substr($uri, strpos($uri, '?') + 1)));
				
				if(strpos($return, '=') !== FALSE){
					$return = '';
				}
			break;
		}
		
		return substr($return, 0, 1) == '/' ? $return : '/' . $return;
	}
	
	/**
	 * 补全路由路径
	 *
	 * @param string $path
	 * @param boolean $isString
	 * @return mixed
	 */
	static public function realPath($path, $isString = true){
		$path = explode('/', $path);
		
		$return = [];
		
		if(count($path) == 2){
			$return['a'] = lcfirst(self::$option['default']['a']);
			$return['c'] = $path[0];
			$return['m'] = $path[1];
		}elseif(count($path) == 1){
			$return['a'] = lcfirst(self::$option['default']['a']);
			$return['c'] = $path[0];
			$return['m'] = self::$option['default']['m'];
		}else{
			$return['a'] = $path[0];
			$return['c'] = $path[1];
			$return['m'] = $path[2];
		}
		
		return $isString ? implode('/', array_values($return)) : $return;
	}
}