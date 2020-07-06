<?php
/* ----------------------------------------------- *
 | [ AdminPHP ] Version : 2.0 beta
 | 简单粗暴又不失高雅的迫真 OOP MVC 框架，，，
 |
 | URL     : https://www.adminphp.net/
 * ----------------------------------------------- *
 | Name    : Exception:AutoLoad (异常类:自动加载)
 |
 | Author  : Xlch88 (i@xlch.me)
 | LICENSE : WTFPL http://www.wtfpl.net/about
 * ----------------------------------------------- */

namespace AdminPHP\Exception;

use AdminPHP\Exception\Exception;
use AdminPHP\View;

class AutoLoadException extends Exception{
    public function __construct($code, $class, $tryFiles = [], $loadFile = '')
    {
		$this->code = $code;
		$this->tryFiles = $tryFiles;
		
		switch($this->code){
			case 0:
				$this->message = l('无法自动加载类:' . $class);
				$this->class = $class;
			break;
			
			case 1:
				$this->message = l('已加载类文件，但未成功加载类。');
				$this->loadFile = $loadFile;
			break;
		}
    }
}