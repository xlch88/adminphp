<?php
namespace AdminPHP;

use AdminPHP\Exception\ErrorException;
use AdminPHP\Router;

class ErrorManager{
	static public function init(){
		set_error_handler('\\AdminPHP\\ErrorManager::error');
		set_exception_handler('\\AdminPHP\\ErrorManager::exception');
	}
	
	static public function error(int $errno, string $errstr, string $errfile, int $errline){
		if (!(error_reporting() & $errno)) {
			return false;
		}
		
		if(!class_exists('\\AdminPHP\\Exception\\Error\\WarningException', false)){
			include(adminphp . 'exception' . DIRECTORY_SEPARATOR . 'Error' . DIRECTORY_SEPARATOR . 'Errors.php');
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
	
	static public function exception($ex){
		header('HTTP/1.1 500 Internal Server Error');
		
		\AdminPHP\PerformanceStatistics::log('AdminPHP:error_manager');
		
		$info = [
			'message'		=> $ex->getMessage(),
			'code'			=> $ex->getCode(),
			'file'			=> $ex->getFile(),
			'fileText'		=> self::getFileLines($ex->getFile(), $ex->getLine() - 4, 8, $ex->getLine()),
			'line'			=> $ex->getLine(),
			'class'			=> get_class($ex),
			'trace'			=> self::formatTrace($ex->getTrace()),
			'removeTrace'	=> isset($ex->removeTraceCount) ? $ex->removeTraceCount : 0,
			'exceptionVars'	=> self::getExceptionVars($ex),
			'url'			=> urldecode(Router::getUrl())
		];
		
		if($info['removeTrace'] > 0){
			array_splice($info['trace'], 0, $info['removeTrace']);
		}
		
		$info = self::hiddenRootPath($info);
		
		$sysinfo = [
			'code'		=> '500',
			'type'		=> 'error', //[info, error, success]
			'title'		=> '系统故障',
			'showTips'	=> false,
			'errorInfo'	=> $info
		];
		sysinfo($sysinfo);
		die();
	}
	
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
	
	static private function formatTrace($trace){
		foreach($trace as $i => $row){
			$trace[$i]['file'] = isset($row['file']) ? $row['file'] : 'NO FILE';
			$trace[$i]['line'] = isset($row['line']) ? $row['line'] : 'NO LINE';
			$trace[$i]['fileText'] = isset($row['file']) ? self::getFileLines($row['file'], $row['line'] - 4, 10, $row['line']) : '';
			$trace[$i]['function_'] = isset($row['class']) ? $row['class'] . $row['type'] . $row['function'] : $row['function'];
			$trace[$i]['args'] = isset($trace[$i]['args']) ? self::formatArgs($trace[$i]['args']) : [];
		}
		
		return $trace;
	}
	
	static private function varToString($var, $strlen = 20){
		if(is_string($var)){
			$var = htmlspecialchars('"' . ($strlen != 0 && mb_strlen($var, 'UTF-8') > $strlen ? mb_substr($var, 0, $strlen) . '...' : $var) . '"');
			$var = str_replace(root, '[ROOT]' . DIRECTORY_SEPARATOR, $var);
		}elseif(is_numeric($var) || is_bool($var)){
			$var = $var;
		}elseif(is_callable($var)){
			$var = '(Function)';
		}elseif(is_object($var)){
			$var = '(Object)';
		}else{
			$var = @htmlspecialchars((string)$var);
		}
		
		return $var;
	}
	
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
	
	static private function getFileLines($filename, $startLine = 1, $endLine = 50, $redline = 1) {
		$fp = @fopen($filename, 'rb');
		if (!$fp) return '';
		
		$line = 0;
		
		for ($i = 1; $i < $startLine; ++$i) { // 跳过前$startLine行
			$line++;
			fgets($fp);
		}
		for ($i = 0; $i <= $endLine; ++$i) {
			$line++;
			if(($text = fgets($fp)) === FALSE){
				break;
			}
			$content[] = str_replace(["\r","\n"], '', $line == $redline ? '[redline]' . $text . '[/redline]' : $text);
		}
		fclose($fp);
		
		return implode("\r\n", $content);
		
	}
}