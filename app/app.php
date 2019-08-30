<?php
namespace App;

use AdminPHP\Hook;
use AdminPHP\Router;
use AdminPHP\View;
use DB;

Hook::add('app_init_router', function(){
	$routerConfig = include(appPath . 'config/router.php');
	
	Router::addRegexes($routerConfig['regex']);
	Router::addRoutes($routerConfig['router']);
});

Hook::add('app_init', function(){
	global $db;
	
	// DB ------------------------------------------------------------
	$db_config = include(appPath . 'config/db.php');
	//$db = new DB($db_config, false);
	$db = false;
	
	View::setVar('dbStatus', function () use ($db){
		return !!$db;
	});
});
