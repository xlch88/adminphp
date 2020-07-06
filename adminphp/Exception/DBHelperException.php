<?php
/* ----------------------------------------------- *
 | [ AdminPHP ] Version : 2.0 beta
 | 简单粗暴又不失高雅的迫真 OOP MVC 框架，，，
 |
 | URL     : https://www.adminphp.net/
 * ----------------------------------------------- *
 | Name    : Exception:DBException (异常类:数据库)
 |
 | Author  : Xlch88 (i@xlch.me)
 | LICENSE : WTFPL http://www.wtfpl.net/about
 * ----------------------------------------------- */

namespace AdminPHP\Exception;

use AdminPHP\Exception\Exception;

class DBHelperException extends Exception{
    public function __construct($code, $args, $val1 = '', $val2 = '')
    {
		$this->code = $code;
		
		switch($this->code){
			case 0: //where
				$this->message		= l('DBHelper传参错误: 传入了无法处理的参数。');
				$this->args			= $args;
			break;
			
			case 1: //orderBy
				$this->message		= l('DBHelper传参错误: orderBy只能传入文本。');
				$this->args			= $args;
				$this->index		= $val1;
				$this->value		= $val2;
			break;
			
			case 2:
				$this->message		= l('row/rows方法仅能用于select查询。');
			break;
		}
    }
}