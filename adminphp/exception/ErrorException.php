<?php
namespace AdminPHP\Exception;

use AdminPHP\Exception\Exception;

class ErrorException extends Exception{
    public function __construct($errno, $errstr, $errfile, $errline)
    {
		$this->code = $errno;
		$this->message = $errstr;
		$this->file = $errfile;
		$this->line = $errline;
		
		$this->removeTraceCount = 1;
    }
}