<?php
namespace App;

use AdminPHP\Hook;
use AdminPHP\Router;
use AdminPHP\View;
use AdminPHP\Engine\View\KeYao;
use DB;

Hook::add('app_init_router', function(){
	$routerConfig = include(appPath . 'Config/router.php');
	
	Router::addRegexes($routerConfig['regex']);
	Router::addRoutes($routerConfig['router']);
});

Hook::add('app_init', function(){
	global $db;
	
	// DB ------------------------------------------------------------
	$db_config = include(appPath . 'Config/db.php');
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
		return false;
	});
});
