<?php
namespace AdminPHP;

use AdminPHP\Hook;

session_start();
date_default_timezone_set('PRC');

defined('appPath') ?: 			define('appPath',		root . 'app' . DIRECTORY_SEPARATOR);
defined('defaultApp') ?: 		define('defaultApp',	'default');
defined('templatePath') ?:		define('templatePath',	root . 'template' . DIRECTORY_SEPARATOR);

include('ErrorManager.php');
include('AutoLoad.php');
AutoLoad::init();

include(appPath . 'app.php');

Hook::doHook('app_init_functions');

include('functions.helper.php');
include('functions.safe.php');
include('functions.adminphp.php');
if(is_file(appPath . 'functions.php'))
	include(appPath . 'functions.php');
include('Router.php');

Hook::doHook('app_init_functions_end');

$controller	= $c = i('c', 0, 'path');
$method		= $m = i('m', 0, 'path');
$app		= $a = i('a', 0, 'path');

Hook::doHook('app_init_router', ['controller' => &$controller, 'method' => &$method, 'app' => &$app]);

$c = $controller = $controller ?: 'index';
$m = $method = $method ?: 'index';
$a = $app ?: defaultApp;

if($a != ''){
	$controller = '\\App\\' . ucfirst($app) . '\\Controller\\' . ucfirst($controller) . 'Controller';
}else{
	$controller = '\\App\\Controller\\' . ucfirst($controller) . 'Controller';
}

class_exists($controller) ?: notice('您正在进行异常操作!请联系管理员! [CODE:23332]');
$controller = new $controller;
method_exists($controller, $m) ?: notice('您正在进行异常操作!如有疑问请联系管理员! [23333]');
(new \ReflectionMethod($controller, $m))->isPublic() ?: notice('您正在进行异常操作!请联系管理员! [CODE:23334]');

Hook::doHook('app_init');

if(method_exists($controller, 'init') && (new \ReflectionMethod($controller, 'init'))->isPublic())
	$controller->init();

$controller->$m();