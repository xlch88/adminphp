<?php
set_error_handler(function($errno, $errstr, $errfile, $errline){
	throw new \Exception($errno . ',' . $errstr);
});