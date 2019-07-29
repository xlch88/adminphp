<?php
namespace AdminPHP;

class Hook{
	private static $hookList = [];
	
	public static function addHook($id, $function){
		self::$hookList[$id][] = $function;
	}
	
	public static function doHook($id, $args = []){
		if(isset(self::$hookList[$id])){
			foreach(self::$hookList[$id] as $index => $function){
				$function($args);
			}
		}
	}
}

/*

$aa = 123;

Hook::addHook('huaq', function($args){
    $args['aa'] = 456;
});

Hook::doHook('huaq', ['aa'=>&$aa]);

echo $aa;

*/