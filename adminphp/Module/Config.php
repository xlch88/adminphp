<?php
/* ----------------------------------------------- *
 | [ AdminPHP ] Version : 2.0 beta
 | 简单粗暴又不失高雅的迫真 OOP MVC 框架，，，
 |
 | URL     : https://www.adminphp.net/
 * ----------------------------------------------- *
 | Name    : 类:配置文件
 |
 | Author  : Xlch88 (i@xlch.me)
 | LICENSE : WTFPL http://www.wtfpl.net/about
 * ----------------------------------------------- */

namespace AdminPHP\Module;

use AdminPHP\Exception\ConfigException;

class Config {
	static public $path = '';
	static public $prefix = '';
	static public $subfix = '.php';
	static public $webConfigFile = [
		'name'	=> 'web',
		'type'	=> 'array'
	];
	
	static public $readList = [];
	
	static public function init($config = []){
		$config = array_merge([
			'path'			=> appPath . 'Config',
			'prefix'		=> '',
			'subfix'		=> '.php',
			'webConfigFile'	=> [
				'name'	=> '',
				'type'	=> ''
			]
		], $config);
		
		self::$path = realpath($config['path']) . DIRECTORY_SEPARATOR;
		self::$prefix = $config['prefix'];
		self::$subfix = $config['subfix'];
		self::$webConfigFile = $config['webConfigFile'];
		
		if(!realpath(self::$path) || !is_dir(self::$path)){
			throw new ConfigException(0);
		}
		
		if(self::$webConfigFile['name']){
			self::readFile(self::$webConfigFile['name'], self::$webConfigFile['type'], true);
		}
	}
	
	static public function mkfilename(string $configName){
		return self::$path . self::$prefix . $configName . self::$subfix;
	}
	
	/**
	 * 读取配置*文件*
	 * 
	 * 
	 */
	static public function &readFile(string $configName, string $configType = 'array', $throwException = false){
		$filename = self::mkfilename($configName);
		
		if(!is_file($filename)){
			if($throwException){
				throw new ConfigException(1, $filename);
			}else{
				$status = false;
				return $status;
			}
		}
		
		try{
			clearstatcache(true, $filename);
			if(function_exists('opcache_invalidate')){
				opcache_invalidate($filename);
			}
			
			$value = include($filename);
			switch($configType){
				case 'json':
					$value = json_decode($value, true);
				break;
				
				case 'array':
					if(!is_array($value))
						throw new \Exception(l('非数组类型!'));
				break;
			}
		}catch(Exception $ex){
			if($throwException){
				throw new ConfigException(2, $filename, $configType, $ex);
			}else{
				$status = false;
				return $status;
			}
		}
		
		self::$readList[$configName] = [
			'data'	=> $value,
			'type'	=> $configType,
			'file'	=> $filename
		];
		
		return self::$readList[$configName]['data'];
	}
	
	/**
	 * 写入配置*文件*
	 * 
	 * 
	 */
	static public function writeFile($configName, $value = null, $configType = 'array'){
		if($value === null){
			if(!isset(self::$readList[$configName])){
				return false;
			}
			
			$value = self::$readList[$configName]['data'];
			$value = self::toSaveFile($value, $configType);
			
			return file_put_contents(self::$readList[$configName]['file'], $value);
		}else{
			$value = self::toSaveFile($value, $configType);
			return file_put_contents(self::mkfilename($configName), $value);
		}
	}
	
	/**
	 * 将值转换成php文件文本
	 * 
	 * @param mixed  $value 值
	 * @param string $type 类型
	 * @return string
	 */
	public static function toSaveFile($value, string $type = 'json'){
		switch($type){
			case 'json':
				$result = "<?php\r\nreturn <<<JSON\r\n";
				$result.= str_replace(["\\", "$"], ["\\\\", "\\$"], json_encode($value, JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE));
				$result.= "\r\nJSON;\r\n";
			break;
			
			default:
				$result = "<?php\r\nreturn ";
				$result.= var_export($value, true);
				$result.= ';';
			break;
		}
		
		return $result;
	}
	
	
	/**
	 * 读取配置*数据*
	 * 
	 * 
	 */
	static public function &read($key = '', $configName = null, $configType = 'array'){
		if(is_null($configName)){
			if(!isset(self::$webConfigFile['name']) || self::$webConfigFile['name'] == ''){
				throw new \InvalidArgumentException(l('参数 configName 为空'));
			}
			
			$configName = self::$webConfigFile['name'];
		}
		if(!isset(self::$readList[$configName])){
			self::readFile($configName, $configType);
		}
		
		return self::getArrayValue(self::$readList[$configName]['data'], $key);
	}
	
	/**
	 * 写入配置*数据*
	 * 
	 * 
	 */
	static public function write($key = '', $value, $configName = null, $configType = 'array'){
		if(is_null($configName)){
			if(!isset(self::$webConfigFile['name']) || self::$webConfigFile['name'] == ''){
				throw new \InvalidArgumentException(l('参数 configName 为空'));
			}
			
			$configName = self::$webConfigFile['name'];
			$configType = self::$webConfigFile['type'];
		}
		if(!isset(self::$readList[$configName])){
			if(!$data = &self::readFile($configName, $configType, false)){
				$data = [];
			}
		}else{
			$data = &self::$readList[$configName]['data'];
		}
		
		if(!self::setArrayValue($data, $key, $value)){
			return false;
		}
		
		return self::writeFile($configName, $data, $configType);
	}
	
	static public function &getArrayValue(&$config, $key){
		if($key == ''){
			return $config;
		}
		
		$key = explode('.', $key);
		
		$value = &$config;
		foreach($key as $index){
			if(isset($value[$index])){
				$value = &$value[$index];
			}else{
				$return = null;
				return $return;
			};
		}
		
		return $value;
	}
	
	static public function setArrayValue(&$config, $key, $value){
		if($key == ''){
			$config = $value;
			return true;
		}
		
		$key = explode('.', $key);
		
		$value_ = &$config;
		foreach($key as $index){
			if(!isset($value_[$index])){
				$value_[$index] = [];
			}
			
			$value_ = &$value_[$index];
		}
		
		$value_ = $value;
		
		return true;
	}
}