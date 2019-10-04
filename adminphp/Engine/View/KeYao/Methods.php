<?php
/* ----------------------------------------------- *
 | [ AdminPHP ] Version : 2.0 beta
 | 简单粗暴又不失高雅的迫真 OOP MVC 框架，，，
 |
 | URL     : https://www.adminphp.net/
 * ----------------------------------------------- *
 | Name    : ViewEngine/KeYao/Methods
 |
 | Author  : Xlch88 (i@xlch.me)
 | LICENSE : WTFPL http://www.wtfpl.net/about
 * ----------------------------------------------- */

namespace AdminPHP\Engine\View\KeYao;

use AdminPHP\AntiCSRF;

class Methods {
	function get(){
		$methods = [];
		
		// xxx(arg): 形式
		foreach([
			'if',
			'elseif',
			'for',
			'foreach'
		] as $method){
			$methods[$method] = function($match)use($method){
				if(!isset($match[4]) || $match[4] == '') return;
				
				return '<?php ' . $match[1] . (isset($match[3]) ? $match[3] : '') . ': ?>';
			};
		}
		
		// xxx: 形式
		foreach([
			'else'
		] as $method){
			$methods[$method] = function($match)use($method){
				return '<?php ' . $method . ': ?>';
			};
		}
		
		// xxx; 形式
		foreach([
			'endif',
			'endfor',
			'endforeach'
		] as $method){
			$methods[$method] = function($match)use($method){
				return '<?php ' . $method . '; ?>';
			};
		}
		
		
		$methods['switch'] = function($match){
			if(!isset($match[4]) || $match[4] == '') return;
			
			return '<?php switch' . $match[3] . ':';
		};
		
		$methods['endswitch'] = function($match){
			return 'endswitch; ?>';
		};
		
		$methods['case'] = function($match){
			if(!isset($match[4]) || $match[4] == '') return;
			
			return 'case ' . $match[4] . ': ?>';
		};
		
		$methods['default'] = function($match){
			return 'default: ?>';
		};
		
		$methods['breakswitch'] = function(){
			return '<?php break;';
		};
		
		$methods['continue'] = function($match){
			if(!isset($match[4]) || $match[4] == ''){
				return '<?php continue; ?>';
			}
			
			if(is_numeric($match[4])){
				return '<?php continue ' . $match[4] . '; ?>';
			}
			
			return '<?php if' . $match[3] . ' continue; ?>';
		};
		
		$methods['break'] = function($match){
			if(!isset($match[4]) || $match[4] == ''){
				return '<?php break; ?>';
			}
			
			if(is_numeric($match[4])){
				return '<?php break ' . $match[4] . '; ?>';
			}
			
			return '<?php if' . $match[3] . ' break; ?>';
		};
		
		$methods['unless'] = function($match){
			if(!isset($match[4]) || $match[4] == '') return;
			
			return '<?php if(!' . $match[3] . '): ?>';
		};
		$methods['isset'] = function($match){
			if(!isset($match[4]) || $match[4] == '') return;
			
			return '<?php if(isset' . $match[3] . '): ?>';
		};
		
		$methods['empty'] = function($match){
			if(!isset($match[4]) || $match[4] == '') return;
			
			return '<?php if(empty' . $match[3] . '): ?>';
		};
		
		$methods['endunless'] = $methods['endempty'] = $methods['endisset'] = $methods['endtrue'] = function($match){
			return '<?php endif; ?>';
		};
		
		$methods['php'] = function($match){
			if(!isset($match[4]) || $match[4] == '') return '<?php ';
			
			return '<?php ' . substr($match[3], 1, -1) . ' ?>';
		};
		
		$methods['endphp'] = function($match){
			return '?>';
		};
		
		$methods['json'] = function($match){
			if(!isset($match[4]) || $match[4] == '') return;
			
			return '<?=json_encode' . $match[3] . '; ?>';
		};
		
		$methods['u'] = $methods['url'] = function($match){
			if(!isset($match[4]) || $match[4] == '') return;
			
			return '<?=url' . $match[3] . '; ?>';
		};
		
		$methods['formhash'] = function($match){
			return '<input type="hidden" name="' . AntiCSRF::$argName . '" value="<?=\AdminPHP\AntiCSRF::get(); ?>" />';
		};
		
		$methods['true'] = function($match){
			if(!isset($match[4]) || $match[4] == '') return;
			
			return '<?php if(isset' . $match[3] . ' && ' . $match[3] . '): ?>';
		};
		
		return $methods;
	}
}