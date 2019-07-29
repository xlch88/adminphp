<?php
namespace App;

use AdminPHP\Hook;
use DB;

Hook::addHook('app_init_functions', function(){

});

Hook::addHook('app_init_router', function($args){
	$args['controller']	= $args['controller']	?: 'index';
	$args['method']		= $args['method']		?: 'hello';
});

Hook::addHook('template_echo', function($args){ //用于切换模板
	//global $webConfig;
	
	//if(!$args['isSystem']){
	//	$args['templateFilePath'] = is_dir(templatePath . $webConfig['template']['file']) ? templatePath . $webConfig['template']['file'] . DIRECTORY_SEPARATOR : '';
	//}
});

Hook::addHook('app_init', function(){
	global $db;
	
	// DB ------------------------------------------------------------
	$db_config = include(appPath . 'config/db.config.php');
	$db = new DB($db_config['ip'], $db_config['user'], $db_config['pass'], $db_config['db'], $db_config['port']);
	if(!$db->link){
		notice('平台维护中...');
	}
	define('T', $db_config['prefix']);
});
