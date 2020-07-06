<?php
/* ----------------------------------------------- *
 | [ AdminPHP ] Version : 2.0 beta
 | 简单粗暴又不失高雅的迫真 OOP MVC 框架，，，
 |
 | URL     : https://www.adminphp.net/
 * ----------------------------------------------- *
 | Name    : ErrorManager (错误管理模块)
 |
 | Author  : Xlch88 (i@xlch.me)
 | LICENSE : WTFPL http://www.wtfpl.net/about
 * ----------------------------------------------- */

namespace AdminPHP;

use AdminPHP\Exception\ErrorException;
use AdminPHP\Module\Router\WebRouter;
use AdminPHP\Module\PerformanceStatistics;

class ErrorManager{
	static private $debug = false;
	static public $adminInfo = [];
	static public $logId = '';
	static public $logPath = '';
	
	/**
	 * 初始化错误管理
	 * 该方法会在发生错误/异常的时候进行调用
	 * 
	 * @return void
	 */
	static public function init(){
		self::$adminInfo = AdminPHP::$config['adminInfo'] ?: l('@adminphp.errorManager.adminInfo', [], [
			'adminphp框架'		=> '<a href="https://www.adminphp.net/" target="_blank">https://www.adminphp.net/</a>',
			'adminphp框架作者'	=> 'Xlch88 (i@xlch.me)',
		]);
		
		self::$debug = AdminPHP::$config['debug'];
		self::$logPath = AdminPHP::$config['path']['errorLog'] ? AdminPHP::$config['path']['errorLog'] . DIRECTORY_SEPARATOR : '';

		if(self::$logPath && !is_dir(self::$logPath)) @mkdir(self::$logPath, 0777, true);
	}
	
	/**
	 * 错误回调
	 * 
	 * @param int    $errno   错误代码
	 * @param string $errstr  错误信息
	 * @param string $errfile 错误文件
	 * @param int    $errline 错误所在行数
	 * @return void
	 */
	static public function error(int $errno, string $errstr, string $errfile, int $errline){
		if (!(error_reporting() & $errno)) {
			return false;
		}
		
		if(!class_exists('\\AdminPHP\\Exception\\Error\\WarningException', false)){
			include(adminphp . 'Exception' . DIRECTORY_SEPARATOR . 'Errors.php');
		}
		
		switch($errno){
			 case E_ERROR:               throw new \AdminPHP\Exception\Error\ErrorException            ($errno, $errstr, $errfile, $errline);
			 case E_WARNING:             throw new \AdminPHP\Exception\Error\WarningException          ($errno, $errstr, $errfile, $errline);
			 case E_PARSE:               throw new \AdminPHP\Exception\Error\ParseException            ($errno, $errstr, $errfile, $errline);
			 case E_NOTICE:              throw new \AdminPHP\Exception\Error\NoticeException           ($errno, $errstr, $errfile, $errline);
			 case E_CORE_ERROR:          throw new \AdminPHP\Exception\Error\CoreErrorException        ($errno, $errstr, $errfile, $errline);
			 case E_CORE_WARNING:        throw new \AdminPHP\Exception\Error\CoreWarningException      ($errno, $errstr, $errfile, $errline);
			 case E_COMPILE_ERROR:       throw new \AdminPHP\Exception\Error\CompileErrorException     ($errno, $errstr, $errfile, $errline);
			 case E_COMPILE_WARNING:     throw new \AdminPHP\Exception\Error\CoreWarningException      ($errno, $errstr, $errfile, $errline);
			 case E_USER_ERROR:          throw new \AdminPHP\Exception\Error\UserErrorException        ($errno, $errstr, $errfile, $errline);
			 case E_USER_WARNING:        throw new \AdminPHP\Exception\Error\UserWarningException      ($errno, $errstr, $errfile, $errline);
			 case E_USER_NOTICE:         throw new \AdminPHP\Exception\Error\UserNoticeException       ($errno, $errstr, $errfile, $errline);
			 case E_STRICT:              throw new \AdminPHP\Exception\Error\StrictException           ($errno, $errstr, $errfile, $errline);
			 case E_RECOVERABLE_ERROR:   throw new \AdminPHP\Exception\Error\RecoverableErrorException ($errno, $errstr, $errfile, $errline);
			 case E_DEPRECATED:          throw new \AdminPHP\Exception\Error\DeprecatedException       ($errno, $errstr, $errfile, $errline);
			 case E_USER_DEPRECATED:     throw new \AdminPHP\Exception\Error\UserDeprecatedException   ($errno, $errstr, $errfile, $errline);
		}
      
		return true;
	}
	
	/**
	 * 异常回调
	 * 
	 * @param exception $ex 异常
	 * @return void
	 */
	static public function exception($ex){
		set_http_code(500);
		
		PerformanceStatistics::log('AdminPHP:error_manager');
		
		if(!self::$logId){
			self::$logId = substr(md5(uniqid()), 0, 8);
		}
		
		$info = [
			'message'		=> $ex->getMessage(),
			'code'			=> $ex->getCode(),
			'file'			=> $ex->getFile(),
			'fileText'		=> self::getFileLines($ex->getFile(), $ex->getLine() - 9, 20, $ex->getLine()),
			'line'			=> $ex->getLine(),
			'class'			=> get_class($ex),
			'trace'			=> self::formatTrace($ex->getTrace()),
			'removeTrace'	=> isset($ex->removeTraceCount) ? $ex->removeTraceCount : 0,
			'exceptionVars'	=> self::getExceptionVars($ex),
			'url'			=> htmlspecialchars(urldecode(WebRouter::mkurl())),
			'debug'			=> self::$debug,
			'logId'			=> self::$logId
		];
		
		if($info['removeTrace'] > 0){
			array_splice($info['trace'], 0, $info['removeTrace']);
		}
		
		$info = self::hiddenRootPath($info);

		$log = self::mkErrorLog(self::hiddenRootPath([
			'message'		=> $ex->getMessage(),
			'code'			=> $ex->getCode(),
			'file'			=> $ex->getFile(),
			'line'			=> $ex->getLine(),
			'class'			=> get_class($ex),
			'trace'			=> $ex->getTrace(),
			'exceptionVars'	=> self::getExceptionVars($ex),
			'url'			=> urldecode(WebRouter::mkurl())
		]));
		$info['log'] = $log;
		
		$sysinfo = l('@adminphp.sysinfo.statusCode.500', [], [
			'title'		=> '系统故障',
			'moreTitle'	=> '啊哈... 出了一点点小问题_(:з」∠)_',
			'more'		=> [
				'程序猿/媛写错了什么东西。',
				'发生了一些不可预料的事情。',
				'如果你发现了有什么不对，请迅速联系站点管理员',
				'如果没什么不对的，那就再试一次看看？'
			],
			'code'		=> '500',
			'type'		=> 'error', //[info, error, success]
			'title'		=> '系统故障'
		]);
		
		$sysinfo['showTips'] = !self::$debug;
		$sysinfo['errorInfo'] = $info;
		$sysinfo['adminInfo'] = self::$adminInfo;
		
		if(Router::$type == 'web'){
			sysinfo($sysinfo);
		}else{
			die($log);
		}
		
		die();
	}
	
	static private function mkErrorLog($info){
		$log[] = '#' . self::$logId . ' - ' . date('Y-m-d H:i:s') . ' - ' . $info['class'] . ' : ' . $info['message'];
		$log[] = "file : $info[file] (line:$info[line])";
		$log[] = "url  : $info[url]";
		$log[] = "Stack trace:";
		
		foreach($info['trace'] as $index => $row){
			$t = " #${index} - ";
			$t.= isset($row['file']) ? $row['file'] : 'NO FILE';
			$t.= (isset($row['line']) ? ' (line:' . $row['line'] . ')' : ' (NO LINE)') . ' : ';
			$t.= isset($row['class']) ? $row['class'] . $row['type'] . $row['function'] : $row['function'];
			
			$args = [];
			if(isset($row['args'])){
				foreach($row['args'] as $i => $row){
					$args[] = htmlspecialchars_decode(self::varToString($row));
				}
			}
			
			$t.= '(' . implode(', ', $args) . ')';
			$log[] = $t;
		}
		
		if($info['exceptionVars']){
			$log[] = "exception data:";
			foreach($info['exceptionVars'] as $index => $row){
				if($row['type'] == 0){
					$t = "  ${index} -> " . print_r($row['value'][1], true);
				}else{
					$t = "  ${index} => [\r\n";
					//$row['value'] = array_column($row['value'], 1);
					foreach($row['value'] as $index2 => $value2){
						$t.= '    ' . $index2 . ' : ' . $value2[1] . "\r\n";
					}
					
					$t.= '  ]';
				}
				
				$log[] = $t;
			}
		}
		
		$log = implode("\r\n", $log);
		
		if(self::$logPath && !self::$debug){
			$logFile = self::$logPath . date('Y-m-d') . '.log';
			//能写入就写入，写不成那也不应该再报错惹...
			@file_put_contents($logFile, $log . "\r\n\r\n\r\n", FILE_APPEND);
		}
		
		return $log;
	}
	
	/**
	 * 获取异常参数并格式化返回
	 * 用来获取\AdminPHP\Exception\Exception类的异常自定义值
	 * 
	 * @param exception $ex 异常
	 * @return array
	 */
	static private function getExceptionVars($ex){
		$return = [];
		
		if(method_exists($ex, 'getValues')){
			$values = $ex->getValues();
			
			foreach($values as $key => $value){
				if(is_array($value)){
					$return[$key] = [
						'type'	=> 1,
						'value'	=> self::formatArgs($value, 0)
					];
				}else{
					$return[$key] = [
						'type'	=> 0,
						'value'	=> [
							self::varToString($value, 0),
							var_export($value, true),
							$value
						]
					];
				}
			}
		}
		
		return $return;
	}
	
	/**
	 * 格式化函数参数
	 * 
	 * @param array $arr    参数们
	 * @param int   $strlen 文本长度
	 * @return array
	 */
	static private function formatArgs($arr, $strlen = 20){
		$args = [];
		
		foreach($arr as $i => $row){
			$args[$i] = [
				self::varToString($row, $strlen),
				@var_export($row, true),
				$row
			];
		}
		
		return $args;
	}
	
	/**
	 * 格式化Trace
	 * 
	 * @param array $trace PHP异常追踪信息
	 * @return array
	 */
	static private function formatTrace($trace){
		foreach($trace as $i => $row){
			$trace[$i]['file']		= isset($row['file']) ? $row['file'] : 'NO FILE';
			$trace[$i]['line']		= isset($row['line']) ? $row['line'] : 'NO LINE';
			$trace[$i]['fileText']	= isset($row['file']) ? self::getFileLines($row['file'], $row['line'] - 4, 10, $row['line']) : '';
			$trace[$i]['function_']	= isset($row['class']) ? $row['class'] . $row['type'] . $row['function'] : $row['function'];
			$trace[$i]['args']		= isset($trace[$i]['args']) ? self::formatArgs($trace[$i]['args']) : [];
		}
		
		return $trace;
	}
	
	/**
	 * 把任何类型的值转换为文本型
	 * 
	 * @param mixed $var    值
	 * @param int   $strlen 最大长度
	 * @return mixed
	 */
	static private function varToString($var, $strlen = 20){
		if(is_string($var)){
			$var = htmlspecialchars('"' . ($strlen != 0 && mb_strlen($var, 'UTF-8') > $strlen ? mb_substr($var, 0, $strlen) . '...' : $var) . '"');
			$var = str_replace(root, '[ROOT]' . DIRECTORY_SEPARATOR, $var);
		}elseif(is_numeric($var) || is_bool($var)){
			$var = (string)$var;
		}elseif(is_object($var) && ($var instanceof Closure)){
			$var = '(Function)';
		}elseif(is_object($var)){
			$var = '('.gettype($var).' Object)';
		}else{
			$var = @htmlspecialchars((string)$var);
		}
		
		return $var;
	}
	
	/**
	 * 隐藏根路径
	 * 
	 * @param mixed $arr 值
	 * @return mixed
	 */
	static private function hiddenRootPath($arr){
		if(is_array($arr)){
			foreach($arr as $index => $value){
				$arr[$index] = self::hiddenRootPath($value);
			}
		}else if(is_string($arr)){
			$arr = str_replace(root, '', $arr);
		}
		
		return $arr;
	}
	
	/**
	 * 获取指定文件的指定范围行内容
	 * 
	 * @param string $filename  文件名
	 * @param int    $startLine 起始行
	 * @param int    $endLine   结束行
	 * @param int    $redline   添加标记行
	 * @return string
	 */
	static private function getFileLines($filename, $startLine = 1, $endLine = 50, $redline = 1) {
		$fp = @fopen($filename, 'rb');
		if (!$fp) return '';
		
		$line = 0;
		
		for ($i = 1; $i < $startLine; ++$i) {
			$line++;
			fgets($fp);
		}
		for ($i = 0; $i <= $endLine; ++$i) {
			$line++;
			if(($text = fgets($fp)) === FALSE){
				break;
			}
			$content[] = str_replace(["\r","\n"], '', $text);
		}
		fclose($fp);
		
		return implode("\r\n", $content);
	}
}