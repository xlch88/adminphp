<?php
namespace AdminPHP\Module\Router\CmdRouter;

use AdminPHP\Module\Router\CmdRouter;

class DocMaker{
	static public $echo = [];
	static public $maxlen = 35;
	
	static public function help($config, $method = ''){
		global $argv;
		
		if($method != ''){
			$config['methods'] = [ $method => $config['methods'][$method] ];
		}
		
		self::$echo[] = '&e&l用法: &b' . $argv[0] . ' &d<功能名> &a-[选项...] &b--[参数...]&f=&7"参数N的值..." &b--[多值参数..] &7"值1" "值N..."';
		self::$echo[] = '';
		
		self::$echo[] = '&a可选选项:';
		self::flags($config['global']['flags']);
		self::$echo[] = '';
		
		self::$echo[] = '&a传入值:';
		self::args($config['global']['args']);
		self::$echo[] = '';
		
		self::$echo[] = '&a功能:';
		self::methods($config['methods']);
		
		CmdRouter::print(self::$echo, false);
	}
	
	static public function flags($flags, $shift = 2){
		foreach($flags as $flag => $info){
			$tmp = '&6-' . $flag;
			
			if(isset($info['names'])){
				$tmp.= '&f,&6-' . implode('&f,&6-', $info['names']);
			}
			
			$tmp = str_repeat(' ', $shift) . $tmp;
			$tmp = self::pad($tmp, $shift);
			$tmp.= self::bewrite(isset($info['bewrite']) ? $info['bewrite'] : '');
			
			self::$echo[] = $tmp;
		}
	}
	
	static public function args($args, $shift = 2){
		foreach($args as $arg => $info){
			$color = isset($info['must']) && $info['must'] ? '&4' : '&3';
			$tmp = $color . '--';
			
			if(isset($info['names'])){
				$tmp.= implode('&f,' . $color . '--', $info['names']) . '&f,' . $color . '--';
			}
			
			$tmp.= $arg;
			
			if(isset($info['type']) && $info['type'] == 'array'){
				if(isset($info['count'])){
					$tmp.= ' ' . '&f';
					
					$tmp2 = [];
					for($x = 0; $x < $info['count']; $x++){
						if(!isset($info['valueName'])){
							$tmp2[] = '参数' . ($x + 1);
							continue;
						}
						
						if(is_array($info['valueName'])){
							$tmp2[] = (isset($info['valueName'][$x]) ? $info['valueName'][$x] : '参数');
						}else{
							$tmp2[] = $info['valueName'] . ($x + 1);
						}
					}
					
					$tmp.= '"' . implode('" "', $tmp2) . '"';
				}else{
					$tmp.= '&f ' . (isset($info['valueName']) && is_array($info['valueName']) ? implode(' ', $info['valueName']) : (isset($info['valueName']) ? $info['valueName'] : '参数')) . ' ...';
				}
			}else{
				$tmp.= '=&f"' . (isset($info['valueName']) ? $info['valueName'] : '参数') . '"';
			}
			
			$tmp = str_repeat(' ', $shift) . $tmp;
			$tmp = self::pad($tmp, $shift);
			$tmp.= self::bewrite(isset($info['bewrite']) ? $info['bewrite'] : '&7(暂无说明)');
			
			self::$echo[] = $tmp;
		}
	}
	
	static public function methods($methods){
		foreach($methods as $method => $info){
			$tmp = '  ';
			$tmp.= $method;
			$tmp = '&e&l' . self::pad($tmp);
			$tmp.= self::bewrite(isset($info['bewrite']) ? $info['bewrite'] : '&7(暂无说明)');
			
			self::$echo[] = $tmp;
			
			if(isset($info['flags'])){
				self::$echo[] = '    &2可选选项:';
				self::flags($info['flags'], 6);
			}
			
			if(isset($info['args'])){
				self::$echo[] = '    &2传入值:';
				self::args($info['args'], 6);
				self::$echo[] = '';
			}
		}
	}
	
	static public function bewrite($value){
		if(is_array($value)){
			return '&d' . implode("\r\n&5" . str_repeat(' ', self::$maxlen + 1), $value);
		}
		
		return '&d' . $value;
	}
	
	static public function pad($str, $shift = 0){
		$tmp = str_replace(array_keys(CmdRouter::$colors), '', $str);
		$tmp = mb_convert_encoding($tmp, 'GBK', 'UTF-8'); //utf8的strlen会将汉字记做3字节
		
		if(strlen($tmp) < self::$maxlen){
			if(self::$maxlen - strlen($tmp) - 2 < 0){
				$str = $str . str_repeat(' ', self::$maxlen - strlen($tmp));
			}else{
				$str = $str . ' ' . str_repeat('&8-', self::$maxlen - strlen($tmp) - 2) . ' ';
			}
		}else{
			$str = $str . "\r\n" . str_repeat(' ', self::$maxlen);
		}
		
		return $str;
	}
}