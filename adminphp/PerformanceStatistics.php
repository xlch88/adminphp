<?php
namespace AdminPHP;

class PerformanceStatistics{
	public static $show = true;
	
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
		self::$log[] = [
			'name'		=> $name,
			'time'		=> self::fn() - self::$begin['time'],
			'memory'	=> (memory_get_usage() - self::$begin['memory']) / 1024,
			'sql'		=> self::$SQLCount
		];
	}
	
	public static function show(){
		if(!self::$show) return;
		
		echo "\r\n" . '<!-- AdminPHP - 性能统计' . "\r\n";
		foreach(self::$log as $index => $row){
			echo '#' . $index . ($row['name'] ? ' - ' . $row['name'] : '') . "\r\n";
			echo '内存：' . round($row['memory'], 2) . ' KB' . "\r\n";
			echo '用时：' . round($row['time'] * 1000, 2) . ' ms' . "\r\n";
			echo '查询：' . $row['sql'] . ' 次' . "\r\n\r\n";
		}
		echo 'AutoLoad:' . "\r\n";
		foreach(AutoLoad::$included as $index => $file){
			echo '#' . $index . ' - ' . str_replace(root, '', $file) . "\r\n";
		}
		echo "\r\n\r\n" . '-->';
	}
	
	private static function fn(){
		list($a, $b) = explode(' ',microtime()); //获取并分割当前时间戳和微妙数，赋值给变量
		return $a + $b;
	}
}