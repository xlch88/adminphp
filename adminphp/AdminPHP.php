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
use AdminPHP\ErrorManager;
use AdminPHP\PerformanceStatistics;
use AdminPHP\Controller;
use AdminPHP\Language;
use AdminPHP\AntiCSRF;

class AdminPHP{
	/* ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■ *\
	 * ■ 警告！你不应该直接修改这里的内容！
	 * ■ 你应该使用以下方法进行初始化
	 * ■ \AdminPHP\AdminPHP::init( *你的配置项* );
	\* ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■ */
	static public $config = [
		'path'		=> [
			'template'	=> root . 'template',
			'app'		=> root . 'app'
		],
			
		'router'	=> [
			'index'		=> 'index/index/index',
			
			'default'	=> [
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
		
		'adminInfo'	=> []
	];
	
	static public function init($config){
		global $a, $c, $m;
		self::$config = array_merge(self::$config, $config);
		
		// Define Constants --------------------------------------------------------------------------------
		self::define();

		// AutoLoader --------------------------------------------------------------------------------------
		include('AutoLoad.php');
		AutoLoad::init();

		// Performance Statistics --------------------------------------------------------------------------
		PerformanceStatistics::$show	= self::$config['performanceStatistics']['show'];
		PerformanceStatistics::$enable	= self::$config['performanceStatistics']['enable'];

		// Include APP File --------------------------------------------------------------------------------
		include(appPath . 'app.php');
		PerformanceStatistics::log('AdmionPHP:include_app_file');

		// Load Functions File -----------------------------------------------------------------------------
		include('functions/functions.helper.php');
		include('functions/functions.safe.php');
		include('functions/functions.adminphp.php');
		Hook::do('adminphp_init_functions');
		PerformanceStatistics::log('AdmionPHP:init_functions');

		// Language ------------------------------------------------------------------------------------------
		Language::init(self::$config['language']['use'], self::$config['language']['cookieName']);
		PerformanceStatistics::log('AdmionPHP:init_language');
		
		// ErrorManager ------------------------------------------------------------------------------------
		self::registerErrorManager();
		
		// Load App Functions File -------------------------------------------------------------------------
		if(is_file(appPath . 'functions.php'))
			include(appPath . 'functions.php');

		Hook::do('app_init_functions');
		PerformanceStatistics::log('AdmionPHP:init_app_function');

		// Router ------------------------------------------------------------------------------------------
		Router::init(self::$config['router']);
		PerformanceStatistics::log('AdmionPHP:init_router');

		// AntiCSRF ----------------------------------------------------------------------------------------
		if(self::$config['AntiCSRF']['enable']){
			AntiCSRF::init(
				self::$config['AntiCSRF']['cookieName'],
				self::$config['AntiCSRF']['sessionName'],
				self::$config['AntiCSRF']['argName'],
				self::$config['AntiCSRF']['varName']
			);
		}
		
		// Run App -----------------------------------------------------------------------------------------
		Hook::do('app_init');
		PerformanceStatistics::log('AdmionPHP:app_init');
		
		// Run Controller ----------------------------------------------------------------------------------
		Controller::init();
		
		/* 性能统计 END */
		\AdminPHP\PerformanceStatistics::log('END');
		\AdminPHP\PerformanceStatistics::show();
	}
	
	static private function path($path){
		return in_array(substr($path, -1), ['/', '\\']) ? $path : $path . DIRECTORY_SEPARATOR;
	}
	
	static private function define(){
		defined('appPath') 		or define('appPath',		self::$config['path']['app']		= self::path(self::$config['path']['app']));
		defined('templatePath')	or define('templatePath',	self::$config['path']['template']	= self::path(self::$config['path']['template']));
	}
	
	/* 没有错误或异常的时候，没必要加载ErrorManager，所以把回调注册在这里。 */
	static private function registerErrorManager(){
		if(AdminPHP::$config['debug'] == true){
			error_reporting(E_ALL);
		}else{
			error_reporting(E_ERROR | E_WARNING | E_PARSE);
		}
		
		set_error_handler('\\AdminPHP\\AdminPHP::error');
		set_exception_handler('\\AdminPHP\\AdminPHP::exception');
	}
	
	static public function exception($ex){
		ErrorManager::init();
		ErrorManager::exception($ex);
	}
	
	static public function error(int $errno, string $errstr, string $errfile, int $errline){
		ErrorManager::init();
		ErrorManager::error($errno, $errstr, $errfile, $errline);
	}
}