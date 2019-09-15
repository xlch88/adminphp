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

use AdminPHP\Exception\ConfigException;

class Config {
	public $path = '';
	public $prefix = '';
	public $subfix = '.php';
	
	public $globalVars = [];
	
	static private $list = [];
	
	/**
	 * 初始化
	 * 
	 * @param string $path 路径
	 * @param string $prefix 文件名前缀
	 * @param string $subfix 文件名后缀
	 * @return void
	 */
	public function __construct($path, $prefix = '', $subfix = '.php', $id = 'app'){
		if(!realpath($path)){
			throw new ConfigException(0, $this);
		}
		
		$this->path = realpath($path) . DIRECTORY_SEPARATOR;
		$this->prefix = $prefix;
		$this->subfix = $subfix;
		
		self::$list[$id] = &$this;
	}
	
	static function i($id = 'app'){
		return self::$list[$id];
	}
	
	/**
	 * 读取配置文件
	 * 可以直接返回读取的值 也可以存为全局变量
	 * 
	 * @param string  $filename 文件名
	 * @param string  $type 配置文件类型
	 * @param mixed   $globalVar|null 全局变量名
	 * @param boolean $throw 出现错误是否抛出异常
	 * @return mixed
	 */
	public function read(string $filename, string $type = 'json', $globalVar = null, $throw = false){
		$filename = $this->path . $this->prefix . $filename . $this->subfix;
		
		if(!is_file($filename)){
			if($throw){
				throw new ConfigException(1, $this, $filename);
			}else return false;
		}
		
		$value = include($filename);
		$isThrow = false;
		switch($type){
			case 'json':
				if(!($value = json_decode($value, true))){
					$isThrow = true;
				}
			break;
			
			case 'array':
				if(!is_array($value)){
					$isThrow = true;
				}
			break;
		}
		
		if($isThrow){
			if($throw){
				throw new ConfigException(2, $filename, $type);
			}else return false;
		}
		
		if(is_string($globalVar) && $globalVar !== ''){
			$this->globalVars[$globalVar] = [
				'file'	=> $filename,
				'type'	=> $type
			];
			
			$GLOBALS[$globalVar] = $value;
			return $GLOBALS[$globalVar];
		}else{
			return $value;
		}
	}
	
	/**
	 * 保存配置文件
	 * 
	 * @param string $name 文件名或者全局变量名
	 * @param mixed  $value|null 为空或null则当做全局变量保存
	 * @param string $type 保存类型
	 * @return boolean
	 */
	public function write(string $name, $value = null, $type = 'json'){
		if($value === null){
			if(!isset($this->globalVars[$name])){
				return false;
			}
			
			$value = $GLOBALS[$name];
			$value = self::getSaveValue($value, $type);
			
			return file_put_contents($this->globalVars[$name]['file'], $value);
		}else{
			$value = self::getSaveValue($value, $type);
			
			return file_put_contents($this->path . $this->prefix . $name . $this->subfix, $value);
		}
	}
	
	/**
	 * 将值转换成php文件文本
	 * 
	 * @param mixed  $value 值
	 * @param string $type 类型
	 * @return string
	 */
	public static function getSaveValue($value, string $type = 'json'){
		switch($type){
			case 'json':
				$result = "<?php\r\nreturn <<<JSON\r\n";
				$result.= str_replace("\\", "\\\\", json_encode($value, JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE));
				$result.= "\r\nJSON;\r\n";
			break;
			
			default:
				$result = "<?php\r\nreturn ";
				$result.= var_export($value);
			break;
		}
		
		return $value;
	}
}