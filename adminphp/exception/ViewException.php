<?php
namespace AdminPHP\Exception;

use AdminPHP\Exception\Exception;
use AdminPHP\View;

class ViewException extends Exception{
    public function __construct($code, $filepath = '', $filename = '', $file = '')
    {
		$this->code = $code;
		
		switch($this->code){
			case 0:
				$this->message = '模板文件未找到!';
			break;
		}
		
		$this->template_filepath = $filepath;
		$this->template_filename = $filename;
		$this->template_file = $file;
		$this->GlobalVars = View::getGlobalVar();
		$this->Vars = View::getVar();
		
		$this->removeTraceCount = 1;
    }
}