<?php
namespace App;

use AdminPHP\Hook;
use AdminPHP\Router;
use AdminPHP\View;
use AdminPHP\Engine\View\KeYao;
use DB;
use Config;

Hook::add('app_include', function(){
	new Config(appPath . 'Config');
});

Hook::add('app_init_router', function(){
	$routerConfig = Config::i()->read('router', 'array');
	
	Router::addRegexes($routerConfig['regex']);
	Router::addRoutes($routerConfig['router']);
});

Hook::add('app_init', function(){
	global $db;
	
	// DB ------------------------------------------------------------
	$db_config = Config::i()->read('db', 'array');
	//$db = new DB($db_config, false);
	$db = false;
	
	View::setVar('dbStatus', function () use ($db){
		return !!$db;
	});
	
	KeYao::setMethod('qwq', function($match){
		if(!isset($match[4])) return;
		
		return '<?php qwq(' . $match[4] . '); ?>';
	});
	
	KeYao::setIfMethod('iftest', function($if){
		return is_numeric($if);
	});
});
