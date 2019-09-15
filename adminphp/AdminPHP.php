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
use AdminPHP\PerformanceStatistics;
use AdminPHP\Controller;
use AdminPHP\Language;
use AdminPHP\AntiCSRF;
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
            'app'       => root . 'app'
        ],
        
        'router'    => [
            'index'     => 'index/index/index',
			
            'default'   => [
                'a'	=> 'index',
                'c'	=> 'index',
				'm'	=> 'index'
			],
			
			'router'	=> 1,
			'rewrite'	=> 0,
		],
		
		'debug'		=> false,
		
		'performanceStatistics'	=> [
			'enable'	=> true,
			'show'		=> true
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
	
	/**
	 * 初始化
	 * @param array $config 配置信息
	 * @return void
	 */
	static public function init($config){
		global $a, $c, $m;
		self::$config = array_merge(self::$config, $config);
		
		// Performance Statistics
		PerformanceStatistics::$show	= self::$config['performanceStatistics']['show'];
		PerformanceStatistics::$enable	= self::$config['performanceStatistics']['enable'];
		
		// Set timezone
		date_default_timezone_set(self::$config['timezone']);
		
		// Define Constants
		self::define('method');
		self::define('appPath');

		// AutoLoader
		include(adminphp . 'AutoLoad.php');
		AutoLoad::init();

		// Load App Functions File
		if(is_file(appPath . 'functions.php'))
			include(appPath . 'functions.php');
		
		// Load Functions File
		include(adminphp . 'Functions/functions.helper.php');
		include(adminphp . 'Functions/functions.safe.php');
		include(adminphp . 'Functions/functions.adminphp.php');
		PerformanceStatistics::log('AdmionPHP:init_functions');

		// Include APP File
		include(appPath . 'app.php');
		PerformanceStatistics::log('AdmionPHP:include_app_file');
		Hook::do('app_include');
		
		// Language
		Language::init(self::$config['language']['use'], self::$config['language']['cookieName']);
		PerformanceStatistics::log('AdmionPHP:init_language');
		
		// ErrorManager
		self::registerErrorManager();
		
		// Cache
		if(self::$config['cache']['enable']){
			Cache::init(self::$config['cache']);
		}
		
		// View
		View::init(self::$config['view']);
		
		// Router
		Router::init(self::$config['router']);
		PerformanceStatistics::log('AdmionPHP:init_router');

		// AntiCSRF
		if(self::$config['AntiCSRF']['enable']){
			AntiCSRF::init(
				self::$config['AntiCSRF']['cookieName'],
				self::$config['AntiCSRF']['sessionName'],
				self::$config['AntiCSRF']['argName'],
				self::$config['AntiCSRF']['varName']
			);
		}
		
		// Run App
		Hook::do('app_init');
		PerformanceStatistics::log('AdmionPHP:app_init');
		self::define('templatePath');
		
		// Run Controller
		Controller::init();
		
		/* 性能统计 END */
		\AdminPHP\PerformanceStatistics::log('END');
		\AdminPHP\PerformanceStatistics::show();
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
			case 'appPath':
				if(!realpath(self::$config['path']['app'])){
					throw new InitException(0, self::$config['path']['app']);
				}
				defined('appPath') or define('appPath', self::$config['path']['app'] = realpath(self::$config['path']['app']) . DIRECTORY_SEPARATOR);
			break;
			
			case 'templatePath':
				if(self::$config['path']['template'] != '' && !realpath(self::$config['path']['template'])){
					throw new InitException(1, self::$config['path']['template']);
				}
				defined('templatePath') or define('templatePath', self::$config['path']['template'] = realpath(self::$config['path']['template']) . DIRECTORY_SEPARATOR);
			break;
			
			case 'method':
				defined('method')	or define('method', strtolower($_SERVER['REQUEST_METHOD']));
				defined('is_post')	or define('is_post', strtolower($_SERVER['REQUEST_METHOD']) == 'post');
				defined('is_get')	or define('is_get', !(strtolower($_SERVER['REQUEST_METHOD']) == 'post'));
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