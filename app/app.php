<?php
namespace App;

use AdminPHP\Hook;
use AdminPHP\Router;
use DB;

Hook::addHook('app_init_functions', function(){

});

Hook::addHook('app_init_router', function($args){
	Router::$methodPath['c'] = Router::$methodPath['c'] ?: 'index';
	Router::$methodPath['m'] = Router::$methodPath['m'] ?: 'hello';
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
	$db = new DB($db_config);
	if(!$db->link){
		notice('平台维护中...');
	}
});
