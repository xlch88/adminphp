<?php
/* ----------------------------------------------- *
 | [ AdminPHP ] Version : 2.0 beta
 | 简单粗暴又不失高雅的迫真 OOP MVC 框架，，，
 |
 | URL     : https://www.adminphp.net/
 * ----------------------------------------------- *
 | Name    : 函数集:框架函数
 |
 | Author  : Xlch88 (i@xlch.me)
 | LICENSE : WTFPL http://www.wtfpl.net/about
 * ----------------------------------------------- */

use AdminPHP\Hook;
use AdminPHP\Model\ControllerReturn;
use AdminPHP\Module\Cache;
use AdminPHP\Module\DB;
use AdminPHP\Module\Language;
use AdminPHP\Module\ReturnData;
use AdminPHP\Module\Router\CmdRouter;
use AdminPHP\Module\Router\CmdRouter\Wizard;
use AdminPHP\Module\Router\WebRouter;
use AdminPHP\Router;
use AdminPHP\View;

//========================================================================================
//==== AdminPHP框架核心函数 ==============================================================
//========================================================================================

if(!function_exists('view')){
	/**
	 * 输出模板
	 * 实际位置:\AdminPHP\View::view()
	 *
	 * @param string  $_templateFile 模板文件
	 * @param array   $args          参数
	 * @param boolean $_isRoot       是否从根目录
	 * @return void
	 */
	function view($templateFile_, $args = [], $isRoot = false){
		return View::view($templateFile_, $args, $isRoot);
	}
}

if(!function_exists('url')){
	/**
	 * 生成地址
	 *
	 * @param string $route  指向位置，“应用名/控制器名/方法名?参数1=值1&参数2=值2”
	 * @param string $domain 泛解析时指定的域名
	 * @return string
	 */
	function url(string $path = '', $domain = null){
		return WebRouter::url($path, $domain);
	}
}

if(!function_exists('do_hook')){
	/**
	 * 执行钩子
	 *
	 * @param string $id   钩子ID
	 * @param array  $args 参数们
	 * @return boolean
	 */
	function do_hook($id, $args = []){
		return Hook::do($id, $args);
	}
}

if(!function_exists('add_hook')){
	/**
	 * 将函数添加到钩子队列
	 * 越早添加的越早被执行
	 * 若函数返回FALSE(不包含空) 则停止执行下一个钩子
	 * 若函数返回TRUE 则停止执行并返回TRUE
	 *
	 * @param string   $id       钩子ID
	 * @param function $function 回调函数
	 * @return void
	 */
	function add_hook($id, $function){
		return Hook::add($id, $function);
	}
}

if(!function_exists('i')){
	/**
	 * 获取参数
	 *
	 * @param string|null $i      参数键值
	 * @param string $method 类型[1/get, 2/post, 0/all, args] 其中args为路由值
	 * @param mixed  $filter 过滤，支持functions.safe内的函数，支持多个使用","分割。若传入数组则仅限数组内的值，若都不匹配则返回第一个值
	 * @param mixed  $default 为空时默认值
	 * @return mixed
	 */
	function i(string $i = null, $method = 'all', $filter = '', $default = ''){
		$return = null;

		if(Router::$type === 'cmd' && $method === 'all'){
			$method = 'cli';
		}

		$method = strtolower($method);
		if(is_null($i)){ //获取全部参数
			switch($method){
				case '1':
				case 'get':
					$return = $_GET;
					break;
			
				case '2':
				case 'post':
					$return = $_POST;
					break;
			
				case '0':
				case 'all':
				default:
					$return = array_merge2(array_merge2($_REQUEST, WebRouter::$args), WebRouter::$postJsonArgs);
					break;
				
				case 'args':
					$return = WebRouter::$args;
					break;
				
				case 'json':
					$return = array_merge2($_REQUEST, WebRouter::$args);
					break;
				
				case 'cookie':
					$return = $_COOKIE;
					break;

				case 'cli':
					$return = CmdRouter::$inputArgs['args'];
					break;
			}
			
			return $return;
		}else{
			switch($method){
				case '1':
				case 'get':
					$return = $_GET[$i] ?? i($i, 'args');
					break;
			
				case '2':
				case 'post':
					$return = $_POST[$i] ?? $default;
					break;
			
				case '0':
				case 'all':
				default:
					$return = $_REQUEST[$i] ?? (i($i, 'args') === '' ? i($i, 'json') : i($i, 'args'));
					break;
				
				case 'args':
					$return = WebRouter::$args[$i] ?? $default;
					break;
				
				case 'json':
					$return = WebRouter::$postJsonArgs[$i] ?? $default;
					break;
				
				case 'cookie':
					$return = $_COOKIE[$i] ?? $default;
					break;

				case 'cli':
					$return = isset(CmdRouter::$inputArgs['args'][$i]) ?
								(
									$filter ?
										str_replace(['\r', '\n'], ["\r", "\n"], CmdRouter::$inputArgs['args'][$i]) :
										CmdRouter::$inputArgs['args'][$i]
								)
								: '';
				break;
			}
		}
		if($method !== 'cli'){
			if(is_array($filter)){
				$return = in_array($return, $filter) ? $return : ($default ?: $filter[0]);
			}else{
				$filter_ = explode(',', $filter);
				foreach($filter_ as $filter){
					switch(strtolower($filter)){
						case 'html':
						case 'sql':
						case 'attr':
						case 'url':
						case 'path':
							$return = safe2($return, $filter);
							break;

						case 'int':
							$return = (int)$return;
							break;

						case 'float':
						case 'number':
							$return = (float)$return;
							break;

						case 'bool':
						case 'boolean':
							$return = in_array(strtolower($return), ['1', 'true', 't', 'yes', 'y', '√', 'on']);
							break;
					}
				}
			}
		}
		
		return $return;
	}
}

//========================================================================================
//==== AdminPHP框架模块函数 ==============================================================
//========================================================================================

if(!function_exists('l')){
	/**
	 * 多语言处理
	 * 若未从语言字典中找到$value,则使用$default,若没有传入$default则直接处理$value.
	 *
	 * @param string $value   语言原文或者数组路径
	 * @param array  $args    参数
	 * @param mixed  $default 默认值
	 * @return mixed
	 */
	function l($value, $args = [], $default = null){
		return Language::languagePrintf($value, $args, $default);
	}
}

if(!function_exists('cache')){
	/**
	 * 读/写缓存
	 *
	 * @param string $key   键
	 * @param string $value 值,留空为读取,false为删除
	 * @param int    $expiry 有效期，单位秒
	 * @return mixed
	 */
	function cache(string $key, $value = null, $expiry = false){
		if($value === FALSE){
			return Cache::delete($key);
		}else if($value === null){
			return Cache::get($key);
		}else{
			return Cache::set($key, $value, $expiry);
		}
	}
}

if(!function_exists('getCache')){
	/**
	 * 读缓存
	 *
	 * @param string $key   键
	 * @param string $value 默认值
	 * @return mixed
	 */
	function getCache($key, $value = null){
		return Cache::get($key, $value);
	}
}

if(!function_exists('setCache')){
	/**
	 * 写缓存
	 *
	 * @param string $key    键
	 * @param string $value  值
	 * @param int    $expiry 有效期，单位秒
	 * @return mixed
	 */
	function setCache($key, $value, $expiry = false){
		return Cache::set($key, $value, $expiry);
	}
}

if(!function_exists('db')){
	/**
	 * @param string $id
	 * @return DB
	 */
	function db($id = 'default'){
		if(!isset(DB::$dbList[$id])){
			return false;
		}else{
			return DB::$dbList[$id];
		}
	}
}
//========================================================================================
//==== AdminPHP 数据状态输出返回/跳转/系统提示页面 =======================================
//========================================================================================

if(!function_exists('notice')){
	/**
	 * 输出系统提示
	 *
	 * @param string $notice 提示内容
	 * @param string $go     跳转到的页面
	 * @param int    $time   跳转倒计时(秒)
	 * @return void
	 */
	function notice($notice, $go = '', $time = 0){
		ReturnData::notice($notice, $go, $time);
	}
}

if(!function_exists('sysinfo')){
	/**
	 * 输出系统页面
	 * 比较丰富美观的系统页面
	 *
	 * @param array $args 参数列表
	 * @return void
	 */
	function sysinfo($args = []){
		ReturnData::sysinfo($args);
	}
}
if(!function_exists('set_http_code')){
	/**
	 * 发送HTTP状态
	 * @param int $code 状态码
	 * @return boolean
	 */
	function set_http_code($code) {
		return ReturnData::set_http_code($code);
	}
}

if(!function_exists('go')){
	/**
	 * 跳转至url
	 * @param string $url  地址
	 * @param int    $type 类型 301=永久跳转 302=临时跳转
	 * @return void
	 */
	function go($url, $type = 302){
		ReturnData::go($url, $type = 302);
	}
}

if(!function_exists('returnData')){
	/**
	 * 输出数据
	 * 该函数专门用作返回数据，不会返回提示页面。
	 *
	 * @param mixed  $data     返回数据，仅返回json、jsonp、xml时有效
	 * @param string $status   状态,可为success/error/info
	 * @param string $msg      提示信息
	 * @param mixed  $code     状态码，留空则自动根据$status生成(success = 1, error = 0, info = 2)
	 * @param mixed  $dataType 指定返回类型
	 * @return void
	 */
	function returnData($data = '', $status = 'success', $msg = '', $code = '', $dataType = true){
		ReturnData::returnData($status, $msg, '', $data, '', '', 0, $dataType);
	}
}

if(!function_exists('returnSuccess')){
	/**
	 * 返回成功状态
	 * 可自动分辨XMLHttpRequest请求或者是直接访问，并根据请求类型返回数据。
	 * 如浏览器直接访问，则显示提示页面。如XMLHttpRequest访问，则自动根据请求，返回json/jsonp/xml。
	 *
	 * @param string $msg      提示信息
	 * @param string $title    标题，留空则自动根据$status生成
	 * @param mixed  $data     返回数据，仅返回json、jsonp、xml时有效
	 * @param mixed  $code     状态码，留空则自动根据$status生成(success = 1, error = 0, info = 2)
	 * @param string $url      跳转地址，留空则自动根据$status生成(success为referer, error为history.go(-1), info为空)
	 * @param int    $wait     等待时间
	 * @param mixed	 $dataType 是否为输出数据，若为true，则根据客户端请求自动返回json/jsonp/xml (不会返回提示页面)，也可直接填入返回类型，将强制返回该类型数据。
	 * @return void
	 */
	function returnSuccess($msg = '', $title = '', $data = '', $code = '', $url = '', $wait = null, $onlyData = false){
		ReturnData::returnData('success', $msg, $title, $data, $code, $url, $wait, $onlyData);
	}
}

if(!function_exists('returnError')){
	/**
	 * 返回失败状态
	 * 可自动分辨XMLHttpRequest请求或者是直接访问，并根据请求类型返回数据。
	 * 如浏览器直接访问，则显示提示页面。如XMLHttpRequest访问，则自动根据请求，返回json/jsonp/xml。
	 *
	 * @param string $msg      提示信息
	 * @param string $title    标题，留空则自动根据$status生成
	 * @param mixed  $data     返回数据，仅返回json、jsonp、xml时有效
	 * @param mixed  $code     状态码，留空则自动根据$status生成(success = 1, error = 0, info = 2)
	 * @param string $url      跳转地址，留空则自动根据$status生成(success为referer, error为history.go(-1), info为空)
	 * @param int    $wait     等待时间
	 * @param mixed	 $dataType 是否为输出数据，若为true，则根据客户端请求自动返回json/jsonp/xml (不会返回提示页面)，也可直接填入返回类型，将强制返回该类型数据。
	 * @return void
	 */
	function returnError($msg = '', $title = '', $data = '', $code = '', $url = '', $wait = null, $onlyData = false){
		ReturnData::returnData('error', $msg, $title, $data, $code, $url, $wait, $onlyData);
	}
}

if(!function_exists('returnInfo')){
	/**
	 * 返回提示信息/一般状态
	 * 可自动分辨XMLHttpRequest请求或者是直接访问，并根据请求类型返回数据。
	 * 如浏览器直接访问，则显示提示页面。如XMLHttpRequest访问，则自动根据请求，返回json/jsonp/xml。
	 *
	 * @param string $msg      提示信息
	 * @param string $title    标题，留空则自动根据$status生成
	 * @param mixed  $data     返回数据，仅返回json、jsonp、xml时有效
	 * @param mixed  $code     状态码，留空则自动根据$status生成(success = 1, error = 0, info = 2)
	 * @param string $url      跳转地址，留空则自动根据$status生成(success为referer, error为history.go(-1), info为空)
	 * @param int    $wait     等待时间
	 * @param mixed	 $dataType 是否为输出数据，若为true，则根据客户端请求自动返回json/jsonp/xml (不会返回提示页面)，也可直接填入返回类型，将强制返回该类型数据。
	 * @return void
	 */
	function returnInfo($msg = '', $title = '', $data = '', $code = '', $url = '', $wait = null, $onlyData = false){
		ReturnData::returnData('info', $msg, $title, $data, $code, $url, $wait, $onlyData);
	}
}

if(!function_exists('returnStatus')){
	/**
	 * 返回提示信息/一般状态
	 * 可自动分辨XMLHttpRequest请求或者是直接访问，并根据请求类型返回数据。
	 * 如浏览器直接访问，则显示提示页面。如XMLHttpRequest访问，则自动根据请求，返回json/jsonp/xml。
	 *
	 * @param string $status   状态,可为success/error/info
	 * @param string $msg      提示信息
	 * @param string $title    标题，留空则自动根据$status生成
	 * @param mixed  $data     返回数据，仅返回json、jsonp、xml时有效
	 * @param mixed  $code     状态码，留空则自动根据$status生成(success = 1, error = 0, info = 2)
	 * @param string $url      跳转地址，留空则自动根据$status生成(success为referer, error为history.go(-1), info为空)
	 * @param int    $wait     等待时间
	 * @param mixed	 $dataType 是否为输出数据，若为true，则根据客户端请求自动返回json/jsonp/xml (不会返回提示页面)，也可直接填入返回类型，将强制返回该类型数据。
	 * @return void
	 */
	function returnStatus($status, $msg = '', $title = '', $data = '', $code = '', $url = '', $wait = null, $onlyData = false){
		ReturnData::returnData($status, $msg, $title, $data, $code, $url, $wait, $onlyData);
	}
}


//========================================================================================
//==== AdminPHP Controller return支持 ====================================================
//========================================================================================

if(!function_exists('success')){
	/**
	 * 返回成功状态
	 * 可自动分辨XMLHttpRequest请求或者是直接访问，并根据请求类型返回数据。
	 * 如浏览器直接访问，则显示提示页面。如XMLHttpRequest访问，则自动根据请求，返回json/jsonp/xml。
	 *
	 * @param string $msg      提示信息
	 * @param string $title    标题，留空则自动根据$status生成
	 * @param mixed  $data     返回数据，仅返回json、jsonp、xml时有效
	 * @param mixed  $code     状态码，留空则自动根据$status生成(success = 1, error = 0, info = 2)
	 * @param string $url      跳转地址，留空则自动根据$status生成(success为referer, error为history.go(-1), info为空)
	 * @param int    $wait     等待时间
	 * @param mixed	 $dataType 是否为输出数据，若为true，则根据客户端请求自动返回json/jsonp/xml (不会返回提示页面)，也可直接填入返回类型，将强制返回该类型数据。
	 * @return ControllerReturn
	 */
	function success($msg = '', $title = '', $data = '', $code = '', $url = '', $wait = null, $onlyData = false){
		return new ControllerReturn('status_success', func_get_args());
	}
}

if(!function_exists('error')){
	/**
	 * 返回失败状态
	 * 可自动分辨XMLHttpRequest请求或者是直接访问，并根据请求类型返回数据。
	 * 如浏览器直接访问，则显示提示页面。如XMLHttpRequest访问，则自动根据请求，返回json/jsonp/xml。
	 *
	 * @param string $msg      提示信息
	 * @param string $title    标题，留空则自动根据$status生成
	 * @param mixed  $data     返回数据，仅返回json、jsonp、xml时有效
	 * @param mixed  $code     状态码，留空则自动根据$status生成(success = 1, error = 0, info = 2)
	 * @param string $url      跳转地址，留空则自动根据$status生成(success为referer, error为history.go(-1), info为空)
	 * @param int    $wait     等待时间
	 * @param mixed	 $dataType 是否为输出数据，若为true，则根据客户端请求自动返回json/jsonp/xml (不会返回提示页面)，也可直接填入返回类型，将强制返回该类型数据。
	 * @return ControllerReturn
	 */
	function error($msg = '', $title = '', $data = '', $code = '', $url = '', $wait = null, $onlyData = false){
		return new ControllerReturn('status_error', func_get_args());
	}
}

if(!function_exists('info')){
	/**
	 * 返回提示信息/一般状态
	 * 可自动分辨XMLHttpRequest请求或者是直接访问，并根据请求类型返回数据。
	 * 如浏览器直接访问，则显示提示页面。如XMLHttpRequest访问，则自动根据请求，返回json/jsonp/xml。
	 *
	 * @param string $msg      提示信息
	 * @param string $title    标题，留空则自动根据$status生成
	 * @param mixed  $data     返回数据，仅返回json、jsonp、xml时有效
	 * @param mixed  $code     状态码，留空则自动根据$status生成(success = 1, error = 0, info = 2)
	 * @param string $url      跳转地址，留空则自动根据$status生成(success为referer, error为history.go(-1), info为空)
	 * @param int    $wait     等待时间
	 * @param mixed	 $dataType 是否为输出数据，若为true，则根据客户端请求自动返回json/jsonp/xml (不会返回提示页面)，也可直接填入返回类型，将强制返回该类型数据。
	 * @return ControllerReturn
	 */
	function info($msg = '', $title = '', $data = '', $code = '', $url = '', $wait = null, $onlyData = false){
		return new ControllerReturn('status_info', func_get_args());
	}
}

if(!function_exists('jsonp')){
	/**
	 * 输出jsonp并结束程序
	 * 适用于jquery的jsonp
	 *
	 * @param mixed  $arr 数组或其他可被json_encode编码的值
	 * @param string $arr 回调函数名，留空则从i(callback)获取
	 * @return ControllerReturn
	 */

	function jsonp($arr, $key = ''){
		return new ControllerReturn('data_jsonp', func_get_args());
	}
}

//========================================================================================
//==== AdminPHP 配置文件 =================================================================
//========================================================================================

if(!function_exists('config')){
	/**
	 * 读取/写入配置*文件*
	 * value为null则读取，不为空则写入。
	 *
	 * @param string $configName 配置文件名称
	 * @param string $value      欲写入的值
	 * @param string $configType 配置文件类型[array,json]
	 * @param bool   $isThrow    失败时是否抛出异常
	 *
	 * @return mixed
	 */
	function &config(string $configName, $value = null, string $configType = 'array', $isThrow = false){
		if(is_null($value)){
			return \AdminPHP\Module\Config::readFile($configName, $configType, $isThrow);
		}else{
			$return = \AdminPHP\Module\Config::writeFile($configName, $value, $configType);
			return $return;
		}
	}
}

if(!function_exists('c')){
	/**
	 * 读取/写入配置*数据*
	 * value为null则读取，不为空则写入。
	 *
	 * @param string $configName 配置文件名称
	 * @param string $value      欲写入的值
	 * @param string $configType 配置文件类型[array,json]
	 * @param bool   $isThrow    失败时是否抛出异常
	 *
	 * @return mixed
	 */
	function &c(string $key, string $configName = null, $value = null, string $configType = 'array'){
		if(!is_null($value)){
			$return = \AdminPHP\Module\Config::write($key, $value, $configName, $configType);
			return $return;
		}else{
			return \AdminPHP\Module\Config::read($key, $configName, $configType);
		}
	}
}

//========================================================================================
//==== AdminPHP cli ======================================================================
//========================================================================================
if(!function_exists('print_c')){
	function print_c($value, $time = true, $br = true){
		return CmdRouter::print($value, $time, $br);
	}
}

if(!function_exists('echo_c')){
	function echo_c($value){
		return CmdRouter::echo($value);
	}
}

if(!function_exists('color')){
	function color($text){
		return CmdRouter::renderColor($text);
	}
}

if(!function_exists('input')){
	function input($title, $type, $default = '', $verify = null){
		return Wizard::input($title, $type, $default, $verify);
	}
}
