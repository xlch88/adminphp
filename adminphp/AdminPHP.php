<?php
namespace AdminPHP;

use AdminPHP\Hook;
use AdminPHP\ErrorManager;
use AdminPHP\PerformanceStatistics;

// 定义常量 ----------------------------------------------------------------------------------------
defined('appPath') ?: 			define('appPath',		root . 'app' . DIRECTORY_SEPARATOR);
defined('defaultApp') ?: 	    	define('defaultApp',	'default');
defined('templatePath') ?:		define('templatePath',	root . 'template' . DIRECTORY_SEPARATOR);

// AutoLoader --------------------------------------------------------------------------------------
include('AutoLoad.php');
AutoLoad::init();

// Include APP File --------------------------------------------------------------------------------
include(appPath . 'app.php');
PerformanceStatistics::log('AdmionPHP:include_app_file');

// Load Functions File -----------------------------------------------------------------------------
Hook::doHook('app_init_functions');
include('functions/functions.helper.php');
include('functions/functions.safe.php');
include('functions/functions.adminphp.php');

// ErrorManager ------------------------------------------------------------------------------------
ErrorManager::init();

// Load App Functions File -------------------------------------------------------------------------
if(is_file(appPath . 'functions.php'))
	include(appPath . 'functions.php');

PerformanceStatistics::log('AdmionPHP:init_function_end');
Hook::doHook('app_init_functions_end');

// Router ------------------------------------------------------------------------------------------
Router::init();

// Get Controller ----------------------------------------------------------------------------------
if($a != ''){
	$controller = '\\App\\' . ucfirst($a) . '\\Controller\\' . ucfirst($c) . 'Controller';
}else{
	$controller = '\\App\\Controller\\' . ucfirst($c) . 'Controller';
}
PerformanceStatistics::log('AdmionPHP:init_router_end');

AutoLoad::load(substr($controller, 1), false);
class_exists($controller, false) ?: notice('您正在进行异常操作!请联系管理员! [CODE:23332]');
$controller = new $controller;
method_exists($controller, $m) || $m == 'init' ?: notice('您正在进行异常操作!如有疑问请联系管理员! [23333]');
(new \ReflectionMethod($controller, $m))->isPublic() ?: notice('您正在进行异常操作!请联系管理员! [CODE:23334]');

// Run App -----------------------------------------------------------------------------------------
PerformanceStatistics::log('AdmionPHP:init_controller_end');
Hook::doHook('app_init');

if(method_exists($controller, 'init') && (new \ReflectionMethod($controller, 'init'))->isPublic())
	$controller->init();

$controller->$m();