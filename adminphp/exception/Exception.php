<?php
namespace AdminPHP\Exception;

class Exception extends \Exception{
	public $removeTraceCount = 0;
	
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