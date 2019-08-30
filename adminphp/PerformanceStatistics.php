<?php
/* ----------------------------------------------- *
 | [ AdminPHP ] Version : 2.0 beta
 | 简单粗暴又不失高雅的迫真 OOP MVC 框架，，，
 |
 | URL     : https://www.adminphp.net/
 * ----------------------------------------------- *
 | Name    : PerformanceStatistics (性能统计模块)
 |
 | Author  : Xlch88 (i@xlch.me)
 | LICENSE : WTFPL http://www.wtfpl.net/about
 * ----------------------------------------------- */

namespace AdminPHP;

class PerformanceStatistics{
	public static $show = true;
	public static $enable = true;
	
	public static $log = [];
	public static $begin = [];
	public static $SQLCount = 0;
	
	private $logIndex = 0;
	
	public static function begin(){
		self::$begin = [
			'time'		=> self::fn(),
			'memory'	=> memory_get_usage()
		];
	}
	
	public static function log($name = null){
		if(!self::$enable) return;
		
		self::$log[] = [
			'name'		=> $name,
			'time'		=> self::fn() - self::$begin['time'],
			'memory'	=> (memory_get_usage() - self::$begin['memory']) / 1024,
			'sql'		=> self::$SQLCount
		];
	}
	
	public static function show(){
		if(!self::$enable || !self::$show) return;
		
		echo "\r\n" . '<!-- AdminPHP - 性能统计' . "\r\n";
		foreach(self::$log as $index => $row){
			echo '#' . $index . ($row['name'] ? ' - ' . $row['name'] : '') . "\r\n";
			echo 'Memory   ：' . round($row['memory'], 2) . ' KB' . "\r\n";
			echo 'Time     ：' . round($row['time'] * 1000, 2) . ' ms' . "\r\n";
			echo 'SQLQuery ：' . $row['sql'] . ' 次' . "\r\n\r\n";
		}
		
		echo 'AutoLoad: ' . count(AutoLoad::$included) . "\r\n";
		foreach(AutoLoad::$included as $index => $file){
			echo '#' . $index . ' - ' . str_replace(root, '', $file) . "\r\n";
		}
		
		echo "\r\n";
		
		echo 'Language Key Count:' . count(Language::$lang) . "\r\n";
		echo 'Language Files: ' . count(Language::$loadFiles) . "\r\n";
		foreach(Language::$loadFiles as $index => $file){
			echo '#' . $index . ' - ' . str_replace(root, '', $file) . "\r\n";
		}
		
		echo "\r\n" . '-->';
	}
	
	private static function fn(){
		list($a, $b) = explode(' ',microtime()); //获取并分割当前时间戳和微妙数，赋值给变量
		return $a + $b;
	}
}