<?php
/* ----------------------------------------------- *
 | [ AdminPHP ] Version : 2.0 beta
 | 简单粗暴又不失高雅的迫真 OOP MVC 框架，，，
 |
 | URL     : https://www.adminphp.net/
 * ----------------------------------------------- *
 | Name    : AdminPHP主程序
 |
 | Author  : Xlch88 (i@xlch.me)
 | LICENSE : WTFPL http://www.wtfpl.net/about
 * ----------------------------------------------- */

namespace AdminPHP;

use AdminPHP\Hook;
use AdminPHP\View;
use AdminPHP\ErrorManager;
use AdminPHP\InitController;
use AdminPHP\Module\Language;
use AdminPHP\Module\AntiCSRF;
use AdminPHP\Module\Cache;
use AdminPHP\Module\PerformanceStatistics;
use AdminPHP\Module\ReturnData;
use AdminPHP\Module\Config;
use AdminPHP\Module\DB;
use AdminPHP\Exception\InitException;

class AdminPHP{
    /* ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■ *\
     * ■ 警告！你不应该直接修改这里的内容！
     * ■ 你应该使用以下方法进行初始化
     * ■ \AdminPHP\AdminPHP::init( *你的配置项* );
    \* ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■ */
    static public $config = [
        'path'          => [
            'template'  => '',
			'errorLog'	=> root . 'runtime' . DIRECTORY_SEPARATOR . 'errorlog',
        ],
        
        'router'    => [
            'web'	=> [],
            'cmd'	=> [],
		],
		
		'debug'			=> false,
		'debugCookie'	=> false,
		'debugInLan'	=> false,
		'debugInLocal'	=> false,
		
		'performanceStatistics'	=> [
			'enable'	=> true,
			'show'		=> true
		],
		
		'db'	=> [
			'type'		=> '',
			
			'ip'		=> '127.0.0.1',
			'port'		=> 3306,
			'username'	=> '',
			'password'	=> '',
			'db'		=> '',
			'unixSocket'=> '',
			
			'file'		=> '',
			
			'dsn'		=> '',
			
			'isLogSQL'	=> false,
			'charset'	=> 'utf8',
			'isThrow'	=> true,
			'options'	=> [
				\PDO::ATTR_ERRMODE	=> \PDO::ERRMODE_EXCEPTION
			],
			
			'prefix'	=> '',
		],
		
		'config'	=> [
			'path'			=> appPath . 'Config',
			'prefix'		=> '',
			'subfix'		=> '.php',
			'webConfigFile'	=> [
				'name'	=> '',
				'type'	=> ''
			]
		],
		
		'AntiCSRF'	=> [
			'enable'		=> false,
			'cookieName'	=> 'adminphp_formhash',
			'sessionName'	=> 'adminphp_formhash',
			'argName'		=> 'formhash',
			'varName'		=> 'formHash'
		],
		
		'language'	=> [
			'use'			=> 'zh-CN',
			'cookieName'	=> 'adminphp_language'
		],
		
		'adminInfo'	=> [],
		
		'cache'		=> [
			'enable'		=> false,
			'engine'		=> 'file',
			'path'			=> root . 'runtime' . DIRECTORY_SEPARATOR . 'cache',
			'file_subfix'	=> '.cache.php'
		],
		
		'view'		=> [
			'engine'	=> 'keyao'
		],
		
		'timezone'	=> 'PRC'
	];
	
	static public $app = null;
	
	/**
	 * 初始化
	 * @param array $config 配置信息
	 * @return void
	 */
	static public function init($config){
		global $a, $c, $m;
		
		// Include APP File
		include(appPath . 'app.php');
		PerformanceStatistics::log('AdmionPHP:include_app_file');
		
		self::$app = new \App\App;
		self::appEvent('onInclude');
		
		// Load App Functions File
		if(is_file(appPath . 'functions.php'))
			include(appPath . 'functions.php');
		
		// Load Functions File
		include(adminphp . 'Functions/functions.helper.php');
		include(adminphp . 'Functions/functions.safe.php');
		include(adminphp . 'Functions/functions.adminphp.php');
		PerformanceStatistics::log('AdmionPHP:init_functions');
		
		self::$config = array_merge2(self::$config, $config);
		unset($config);
		
		// Performance Statistics
		PerformanceStatistics::$show	= self::$config['performanceStatistics']['show'];
		PerformanceStatistics::$enable	= self::$config['performanceStatistics']['enable'];
		
		// Set timezone
		date_default_timezone_set(self::$config['timezone']);

		// AutoLoader
		include(adminphp . 'AutoLoad.php');
		AutoLoad::init();
		
		// Define Constants
		self::define('method');
		
		// AutoLoader
		AutoLoad::initRegister();
		
		self::appEvent('onLoad');
		
		// Config
		Config::init(self::$config['config']);
		
		// Language
		Language::init(self::$config['language']['use'], self::$config['language']['cookieName']);
		PerformanceStatistics::log('AdmionPHP:init_language');
		
		// ErrorManager
		self::registerErrorManager();
		
		//DB
		if(self::$config['db']['type']){
			new DB(self::$config['db']);
			PerformanceStatistics::log('AdmionPHP:init_db');
		}
		
		// Cache
		if(self::$config['cache']['enable']){
			Cache::init(self::$config['cache']);
		}
		PerformanceStatistics::log('AdmionPHP:init_cache');
		
		// Router
		Router::init(self::$config['router']['web'], self::$config['router']['cmd']);
		PerformanceStatistics::log('AdmionPHP:init_router');
		
		if(Router::$type == 'web'){
			header('PHP-Framework: AdminPHP');
			// View
			View::init(self::$config['view']);
			
			// AntiCSRF
			if(self::$config['AntiCSRF']['enable']){
				AntiCSRF::init(
					self::$config['AntiCSRF']['cookieName'],
					self::$config['AntiCSRF']['sessionName'],
					self::$config['AntiCSRF']['argName'],
					self::$config['AntiCSRF']['varName']
				);
			}
			
			Hook::do('app_define_template');
			self::define('templatePath');
		}else{
			ob_implicit_flush(false);
		}
		
		
		// Run App
		Hook::do('app_init');
		self::appEvent('onBoot');
		PerformanceStatistics::log('AdmionPHP:app_init');
		
		// Run Controller
		InitController::init();
		
		/* 性能统计 END */
		PerformanceStatistics::log('END');
		PerformanceStatistics::show();
	}
	
	static public function appEvent($event, $args = []){
		if(method_exists(self::$app, $event)){
			return call_user_func_array([self::$app, $event], $args);
		}
		
		return false;
	}
	
	/**
	 * 定义常量
	 * 
	 * @return void
	 */
	static private function define($var){
		if(defined($var)){
			return;
		}
		switch($var){
			case 'templatePath':
				if(self::$config['path']['template'] != '' && (!realpath(self::$config['path']['template']) || !is_dir(self::$config['path']['template']))){
					throw new InitException(1, self::$config['path']['template']);
				}
				defined('templatePath') or define('templatePath', self::$config['path']['template'] = realpath(self::$config['path']['template']) . DIRECTORY_SEPARATOR);
			break;
			
			case 'method':
				global $argv;
				
				if(!isset($argv)){
					defined('method')	or define('method', strtolower($_SERVER['REQUEST_METHOD']));
					defined('is_post')	or define('is_post', strtolower($_SERVER['REQUEST_METHOD']) == 'post');
					defined('is_get')	or define('is_get', !(strtolower($_SERVER['REQUEST_METHOD']) == 'post'));
				}
			break;
		}
	}
	
	/**
	 * 注册错误管理
	 * 没有错误或异常的时候，没必要加载ErrorManager，所以把回调注册在这里。
	 * 
	 * @return void
	 */
	static private function registerErrorManager(){
		if(AdminPHP::$config['debugCookie'] && isset($_COOKIE['adminphp_debug']) && $_COOKIE['adminphp_debug'] === AdminPHP::$config['debugCookie']){
			AdminPHP::$config['debug'] = true;
		}
		
		if(isset($_SERVER['REMOTE_ADDR'])){
			$ip2long = ip2long($_SERVER['REMOTE_ADDR']);
			if(AdminPHP::$config['debugInLan'] && (
				$ip2long >= ip2long('10.0.0.0')		&& $ip2long <= ip2long('10.255.255.255')  ||
				$ip2long >= ip2long('172.16.0.0')	&& $ip2long <= ip2long('172.31.255.255')  ||
				$ip2long >= ip2long('192.168.0.0')	&& $ip2long <= ip2long('192.168.255.255')
			)){
				AdminPHP::$config['debug'] = true;
			}
			
			if(AdminPHP::$config['debugInLocal'] && substr($_SERVER['REMOTE_ADDR'], 0, 4) == '127.'){
				AdminPHP::$config['debug'] = true;
			}
		}
		
		if(AdminPHP::$config['debug'] == true){
			error_reporting(E_ALL);
		}else{
			error_reporting(E_ERROR | E_WARNING | E_PARSE);
		}
		
		set_error_handler('\\AdminPHP\\AdminPHP::error');
		set_exception_handler('\\AdminPHP\\AdminPHP::exception');
	}
	
	/**
	 * 异常回调
	 * 
	 * @param exception $ex 异常
	 * @return void
	 */
	static public function exception($ex){
		ErrorManager::init();
		ErrorManager::exception($ex);
	}
	
	/**
	 * 错误回调
	 * 
	 * @param int    $errno   错误代码
	 * @param string $errstr  错误信息
	 * @param string $errfile 错误文件
	 * @param int    $errline 错误所在行数
	 * @return void
	 */
	static public function error(int $errno, string $errstr, string $errfile, int $errline){
		ErrorManager::init();
		ErrorManager::error($errno, $errstr, $errfile, $errline);
	}
}