<?php
use AdminPHP\View;

class FormHash{
	public static $whiteList	= [];
	public static $cookieName	= 'adminphp_formhash';
	public static $sessionName	= 'adminphp_formhash';
	public static $argName		= 'adminphp_formhash';
	public static $varName		= 'formHash';
	
	public static function init(){
		global $a,$c,$m;
		
		if(!isset($_SESSION[self::$sessionName])){
			self::refush();
		}
		
		if(!isset(self::$whiteList[$a][$c]) || !in_array($m, self::$whiteList[$a][$c])){
			if($_SERVER['REQUEST_METHOD'] == 'POST'){
				if(!self::verify()){
					sysinfo([
						'type'	=> 'error',
						'info'	=> '表单验证未通过!',
						'more'	=> [
							'您同时打开了多个页面',
							'您直接通过网址访问表单提交页面',
							'请尝试关掉其他页面',
							'或者返回首页再试一次'
						]
					]);
				}
			}
		}
		
		@setcookie(self::$cookieName, self::get(), 0, '/');
		
		View::setVar(self::$varName, function(){
			return FormHash::get();
		});
	}
	
	public static function verify(){
		$result = i($argName) == $_SESSION[self::$sessionName];
		self::refush();
		
		return $result;
	}
	
	public static function refush(){
		$_SESSION[self::$sessionName] = 'xl_' . md5(uniqid());
		@setcookie(self::$cookieName, $_SESSION[self::$sessionName], 0, '/');
		return $_SESSION[self::$sessionName];
	}
	
	public static function get(){
		return $_SESSION[self::$sessionName];
	}
}