<?php
/* ----------------------------------------------- *
 | [ AdminPHP ] Version : 2.0 beta
 | 简单粗暴又不失高雅的迫真 OOP MVC 框架，，，
 |
 | URL     : https://www.adminphp.net/
 * ----------------------------------------------- *
 | Name    : Hook (钩子)
 |
 | Author  : Xlch88 (i@xlch.me)
 | LICENSE : WTFPL http://www.wtfpl.net/about
 * ----------------------------------------------- */

namespace AdminPHP;

use AdminPHP\PerformanceStatistics;

class Hook{
	private static $hookList = [];
	
	public static function add($id, $function){
		self::$hookList[$id][] = $function;
	}
	
	public static function do($id, $args = []){
		if(isset(self::$hookList[$id])){
			foreach(self::$hookList[$id] as $index => $function){
				if($result = $function($args) === FALSE){
					break;
				}
				
				if($result === TRUE){
					return TRUE;
				}
				PerformanceStatistics::log('Hook:' . $id . ' #' . $index . '');
			}
		}
		
		return false;
	}
}