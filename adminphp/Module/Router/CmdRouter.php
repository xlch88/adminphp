<?php
namespace AdminPHP\Module\Router;

use AdminPHP\Router;
use AdminPHP\Exception\RouterException;
use AdminPHP\Module\Router\CmdRouter\DocMaker as CmdRouterDocMaker;
use AdminPHP\Module\Router\CmdRouter\Wizard as CmdRouterWizard;

class CmdRouter{
	static public $argv			= [];
	
	static public $inputArgs	= [
		'commands'	=> [],
		'args'		=> [],
		'flags'		=> [],
		'values'	=> []
	];
	
	static public $enableColor	= true;
	
	static public $colors = [
		'&1'	=> '[0m[38;5;21m',	//深蓝
		'&2'	=> '[0m[38;5;2m',		//深绿
		'&3'	=> '[0m[38;5;6m',		//湖蓝
		'&4'	=> '[0m[38;5;124m',	//深红
		'&5'	=> '[0m[38;5;13m',	//紫色
		'&6'	=> '[0m[38;5;208m',	//橘色
		'&7'	=> '[0m[38;5;245m',	//灰色
		'&8'	=> '[0m[38;5;239m',	//深灰
		'&9'	=> '[0m[38;5;12m',	//蓝
		'&0'	=> '[0m[38;5;0m',		//纯黑
		
		'&a'	=> '[0m[38;5;46m',	//亮绿
		'&b'	=> '[0m[38;5;51m',	//艳青
		'&c'	=> '[0m[38;5;196m',	//亮红
		'&d'	=> '[0m[38;5;200m',	//品红
		'&e'	=> '[0m[38;5;226m',	//亮黄
		'&f'	=> '[0m[38;5;255m',	//纯白
		
		'&n'	=> '[4m',	//下划线
		'&l'	=> '[1m',	//粗体
		'&r'	=> '[0m',	//重置
		
		//以下在windows不支持，linux良好
		'&m'	=> '[9m',	//删除线
		'&o'	=> '[3m',	//斜体
	];
	
	static public $config = [
		'welcome'		=> '',
		
		'global'		=> [
			'default'	=> '',
			'options'	=> [],
			'args'		=> []
		],
		
		'methods'	=> [],
		
		'regex'		=> []
	];
	
	static public function init($config){
		global $argv;
		
		//合并配置文件
		self::$config = array_merge2(self::$config, $config);
		
		//格式化参数√
		self::formatArgs($argv);
		if(self::hasFlag('disableColor')){	//禁用颜色
			self::$enableColor = false;
		}
		
		if(php_uname('s') == 'Windows NT' && php_uname('r') < 10){
			if(!self::hasFlag('enableColor')){
				self::$enableColor = false;
			}
			
			exec('chcp 65001');
		}
		
		//输出欢迎信息
		self::echo(self::$config['welcome']);
		
		//判断是否未指定功能
		if(!self::$inputArgs['commands']){
			if(self::hasFlag('help') || self::hasFlag('h') || self::hasFlag('?')){	//查看帮助 -help
				die(CmdRouterDocMaker::help(self::$config));
			}elseif(isset(self::$config['global']['default']) && self::$config['global']['default']){	//有默认功能，使用默认功能
				$method = self::$config['global']['default'];
			}else{	//既没有指定功能，也么有定义默认的
				self::echo("\r\n" . '&a请输入 &e"' . $argv[0] . ' -help" &a查看帮助' . "\r\n");
				die();
			}
		}else{
			//使用传入的值
			$method = self::$inputArgs['commands'][0];
		}
		
		//功能未定义
		if(!isset(self::$config['methods'][$method])){
			self::echo("\r\n" . '&c未找到该功能，请检查您的输入是否有误。' . "\r\n");
			self::echo("\r\n" . '&a请输入 &e"' . $argv[0] . ' -help" &a查看帮助' . "\r\n");
			die();
		}
		
		//查看某个功能的help
		if(self::hasFlag('help') || self::hasFlag('h') || self::hasFlag('?')){
			die(CmdRouterDocMaker::help(self::$config, $method));
		}
		
		
		//合并方法的参数和全局的参数
		$keys = array_merge($config['global']['args'], isset($config['methods'][$method]['args']) ? $config['methods'][$method]['args'] : []);
		//合并方法的flag和全局的flag
		$flags = array_merge($config['global']['flags'], isset($config['methods'][$method]['flags']) ? $config['methods'][$method]['flags'] : []);
		
		//进入向导
		if(
			(
				(isset(self::$config['methods'][$method]['wizard']) && self::$config['methods'][$method]['wizard']) || 
				(self::hasFlag('wizard'))
			) && isset($config['methods'][$method]['input']) && $config['methods'][$method]['input']
		){
			$result = CmdRouterWizard::start($keys, $flags, self::$config['methods'][$method]['input'], self::$config['regex']);
			self::$inputArgs['args'] = array_merge(self::$inputArgs['args'], $result['args']);
			self::$inputArgs['flags'] = array_merge(self::$inputArgs['flags'], $result['flags']);
		}
		
		foreach($keys as $key => $info){
			if(isset($info['default']) && !isset(self::$inputArgs['args'][$key])){
				self::$inputArgs['args'][$key] = $info['default'];
			}
		}
		
		foreach($flags as $key => $info){
			if(isset($info['default']) && $info['default'] === true && !isset(self::$inputArgs['flags'][$key])){
				self::$inputArgs['args'][] = $key;
			}
		}
		
		$errors = self::verifyInputValue($keys, self::$inputArgs);
		
		if($errors){
			self::echo("\r\n" . '&c错误:' . "\r\n");
			self::echo(self::mkErrMessages($keys, $errors));
			die();
		}
		
		if(count($routeTo = is_array(self::$config['methods'][$method]['path']) ? self::$config['methods'][$method]['path'] : explode('/', self::$config['methods'][$method]['path'])) !== 3){
			throw new RouterException(4, self::$config['methods'][$method]['path']);
		}
		
		Router::$routeTo = [
			'a'	=> $routeTo[0],
			'c'	=> $routeTo[1],
			'm'	=> $routeTo[2],
		];
	}
	
	static public function formatArgs($args){
		array_shift($args);	//删除第一个
		
		$v = false;
		
		//挨个遍历，遍历一个删一个
		while($arg = array_shift($args)){
			if($v){
				self::$inputArgs['values'][] = $arg;
				continue;
			}
			
			//两个--开头，是参数
			if(substr($arg, 0, 2) === '--'){
				if($arg === '--'){
					$v = true;
					continue;
				}
				
				//去除--
				$arg = substr($arg, 2);
				
				if (strpos($arg, '=')){
					//是 --key=value 的形式
					list($arg, $value) = explode("=", $arg, 2);
				}elseif (isset($args[0]) && strpos($args[0], '-') !== 0){
					//是 --key value1 value2 value3 的形式，存为数组
					$value = [];
					while (isset($args[0]) && strpos($args[0], '-') !== 0){
						$value[] = array_shift($args);
					}
				}

				self::$inputArgs['args'][$arg] = !empty($value) ? $value : true;
				continue;
			}
			
			//一个-开头，是flag
			if (substr($arg, 0, 1) === '-'){
				self::$inputArgs['flags'][] = substr($arg, 1);
				continue;
			}
			
			//没有前缀-，是命令
			self::$inputArgs['commands'][] = $arg;
			continue;
		}
	}
	
	static public function verifyInputValue($keys, $inputArgs){
		$errors = [];
		
		foreach($keys as $key => $info){
			//必填 但是没填
			if(isset($info['must']) && $info['must'] && !isset($inputArgs['args'][$key])){
				$errors[] = [
					'type'	=> 'need',
					'key'	=> $key
				];
				continue;
			}
			
			//必填的已经跳过了，接下来验证已填写的值
			if(isset($inputArgs['args'][$key])){
				$value = $inputArgs['args'][$key];
				
				//类型为string的不应该传入多个参数
				if((!isset($info['type']) || $info['type'] != 'array') && is_array($value)){
					$errors[] = [
						'type'	=> 'type',
						'key'	=> $key
					];
					continue;
				}
				
				//类型是数组的先转为数组
				if(isset($info['type']) && $info['type'] == 'array' && !is_array($value)){
					$inputArgs['args'][$key] = $value = [ $value ];
				}
				
				//指定传入个数的
				if(isset($info['type']) && $info['type'] == 'array' && isset($info['count']) && $info['count'] != count($value)){
					$errors[] = [
						'type'	=> 'count',
						'key'	=> $key,
						'count'	=> $info['count']
					];
					
					continue;
				}
				
				//正则表达式验证
				if(isset($info['regex']) && $info['regex'] !== ''){
					$values = !is_array($value) ? [ $value ] : $value;
					foreach($values as $index => $value){
						$regex = $info['regex'];
						if(!(substr($regex, 0, 1) == '/' && substr($regex, -1) == '/')){
							if(!isset(self::$config['regex'][$info['regex']])){
								throw new \Exception(l('正则表达式 %s 未找到', $info['regex']));
							}
							
							$regex = '/' . self::$config['regex'][$info['regex']] . '/';
						}
						
						if(!preg_match($regex, $value)){
							$errors[] = [
								'type'	=> 'regex',
								'key'	=> $key,
								'index'	=> $index
							];
							
							continue 2;
						}
					}
				}
			}
		}
		
		return $errors;
	}
	
	static public function mkErrMessages($keys, $errors){
		$return = [];
		
		foreach($errors as $index => $error){
			$name = isset($keys[$error['key']]['valueName']) ? (is_array($keys[$error['key']]['valueName']) ? (isset($error['index']) && isset($keys[$error['key']]['valueName'][$error['index']]) ? $keys[$error['key']]['valueName'][$error['index']] . '(' . $error['key'] . ')' : $error['key']) : $keys[$error['key']]['valueName'] . '(' . $error['key'] . ')') : $error['key'];
			
			switch($error['type']){
				case 'need':
					$return[] = l('&b%s &c为必填', $name);
				break;
				
				case 'type':
					$return[] = l('&b%s &c只能传入一个参数', $name);
				break;
				
				case 'count':
					$return[] = l('&b%s &c必须传入 &e%s &c个参数', [ $name, $error['count'] ]);
				break;
				
				case 'regex':
					if($keys[$error['key']]['type'] !== 'array'){
						$return[] = l('&b%s &c验证未通过', $name);
					}else{
						$return[] = l('&b%s &c的第 &e%s &c个参数验证未通过', [$name, $error['index'] + 1]);
					}
				break;
			}
		}
		
		return $return;
	}
	
	static public function hasFlag($flag){
		return in_array($flag, self::$inputArgs['flags']) || (isset(self::$inputArgs['args'][$flag]) && self::$inputArgs['args'][$flag] === TRUE);
	}
	
	static public function echo($value){
		echo self::renderColor(is_string($value) ? $value : implode("\r\n&r", $value) . "\r\n");
	}
	
	static public function print($value, $time = true, $br = true){
		$time = $time ? '[' . date('H:i:s') . '.' . str_pad(round(explode(" ", microtime())[0] * 1000), 3, '0') . '] ' : '';
		
		if(is_string($value)){
			self::echo($time . $value . ($br ? "\r\n" : ''));
		}else foreach($value as $index => $v){
			self::echo($time . $v . ($br ? "\r\n" : ''));
		}
	}
	
	static public function renderColor($text){
		$text = $text . '&r';
		
		return $text = str_replace(array_keys(self::$colors), self::$enableColor ? array_values(self::$colors) : '', $text);
	}
}