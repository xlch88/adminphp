<?php
/* ----------------------------------------------- *
 | [ AdminPHP ] Version : 2.0 beta
 | 简单粗暴又不失高雅的迫真 OOP MVC 框架，，，
 |
 | URL     : https://www.adminphp.net/
 * ----------------------------------------------- *
 | Name    : ViewEngine/KeYao/Section
 |
 | Author  : Xlch88 (i@xlch.me)
 | LICENSE : WTFPL http://www.wtfpl.net/about
 * ----------------------------------------------- */

namespace AdminPHP\Engine\View\KeYao;

use AdminPHP\View;

class Layout {
	static private $sections = [];
	static private $push = [];
	static public $footer = '';
	
	static public function getMethods(){
		$methods = [];
		
		$methods['section'] = function($match){
			if(!isset($match[4]) || $match[4] == '') return;
			
			return '<?php \\AdminPHP\\Engine\\View\\KeYao\\Layout::setSection(' . substr($match[3], 1, -1) . ', function($__args){ extract($__args); foreach(\AdminPHP\View::$globalVar as $__globalVar){ global $$__globalVar; } unset($__args, $__globalVar); ?>';
		};
		
		$methods['push'] = $methods['prepend'] = function($match){
			if(!isset($match[4]) || $match[4] == '') return;
			
			return '<?php \\AdminPHP\\Engine\\View\\KeYao\\Layout::push(' . substr($match[3], 1, -1) . ', function($__args){ extract($__args); foreach(\AdminPHP\View::$globalVar as $__globalVar){ global $$__globalVar; } unset($__args, $__globalVar); ?>';
		};
		
		$methods['endpush'] = $methods['endsection'] = function($match){
			return '<?php }); ?>';
		};
		
		$methods['endprepend'] = function($match){
			return '<?php }, true); ?>';
		};
		
		$methods['yield'] = function($match){
			if(!isset($match[4]) || $match[4] == '') return;
			
			return '<?php \\AdminPHP\\Engine\\View\\KeYao\\Layout::getSection(' . substr($match[3], 1, -1) . ', get_defined_vars()); ?>';
		};
		
		$methods['stack'] = function($match){
			if(!isset($match[4]) || $match[4] == '') return;
			
			return '<?php \\AdminPHP\\Engine\\View\\KeYao\\Layout::stack(' . substr($match[3], 1, -1) . ', get_defined_vars()); ?>';
		};
		
		$methods['include'] = function($match){
			if(!isset($match[4]) || $match[4] == '') return;
			
			return '<?php \\AdminPHP\\Engine\\View\\KeYao\\Layout::include(' . substr($match[3], 1, -1) . ', get_defined_vars()); ?>';
		};
		
		$methods['extends'] = function($match, &$data) use ($methods){
			if(!isset($match[4]) || $match[4] == '') return;
			
			$data .= $methods['include']($match);
			return '';
		};
		
		return $methods;
	}
	
	static public function include($file, $args, $moreargs = []){
		View::view($file, array_merge($moreargs, $args), 0, true);
	}
	
	static public function push($name, $func, $prepend = false){
		if(!$prepend){
			self::$push[$name][] = $func;
		}else{
			array_unshift(self::$push[$name], $func);
		}
	}
	
	static public function stack($name, $args){
		unset($args['__file']);
		if(isset(self::$push[$name])){
			foreach(self::$push[$name] as $func){
				$func($args);
			}
		}
	}
	
	static public function setSection($name, $func){
		self::$sections[$name] = $func;
	}
	
	static public function getSection($name, $args){
		unset($args['__file']);
		
		if(isset(self::$sections[$name])){
			self::$sections[$name]($args);
		}
	}
}