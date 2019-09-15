<?php
/* ----------------------------------------------- *
 | [ AdminPHP ] Version : 2.0 beta
 | 简单粗暴又不失高雅的迫真 OOP MVC 框架，，，
 |
 | URL     : https://www.adminphp.net/
 * ----------------------------------------------- *
 | Name    : Exception (异常类父级)
 |
 | Author  : Xlch88 (i@xlch.me)
 | LICENSE : WTFPL http://www.wtfpl.net/about
 * ----------------------------------------------- */

namespace AdminPHP\Exception;

class Exception extends \Exception{
	public $removeTraceCount = 0;
	
	/**
	 * 获取参数
	 *
	 * @return array
	 */
	public function getValues(){
		$return = [];
		
		foreach($this as $key => $value){
			if(!in_array($key, ['code', 'message', 'file', 'line', 'removeTraceCount', 'trace'])){
				$return[$key] = $value;
			}
		}
		
		return $return;
	}
}