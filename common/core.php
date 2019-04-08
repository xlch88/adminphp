<?php
set_error_handler(function($errno, $errstr, $errfile, $errline){
	throw new \Exception($errno . ',' . $errstr);
});

include('function.php');
include('db.class.php');

$db_config = include('db.config.php');

$db = new DB($db_config['ip'], $db_config['user'], $db_config['pass'], $db_config['db'], $db_config['port']);
if(!$db->link){
	notice('平台维护中...');
}

date_default_timezone_set('PRC');

$namespace = 'AdminPHP';

$controller	= $c = safePath(isset($_GET['c']) ? $_GET['c'] : 'index');
$method		= $m = safePath(isset($_GET['m']) ? $_GET['m'] : 'index');

is_file($controllerFile = realpath(controller . ucfirst($controller) . 'Controller.php')) ?: notice('您正在进行异常操作!如有疑问请联系管理员! [CODE:23331]');

include($controllerFile);
$controller = '\\' . $namespace . '\\' . $controller . 'Controller';

class_exists($controller) ?: notice('系统异常!请联系管理员! [CODE:23332]');
method_exists($controller, $method) ?: notice('您正在进行异常操作!如有疑问请联系管理员! [23333]');
(new ReflectionMethod($controller, $m))->isPublic() ?: notice('您正在进行异常操作!请联系管理员! [CODE:23334]');

include('user.php');

$controller = new $controller;
$controller->$method();