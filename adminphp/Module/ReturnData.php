<?php
namespace AdminPHP\Module;

use AdminPHP\Router;

class ReturnData{
	static $accept = '';
	static $jsonpCallback = 'callback';
	static $allowDataTypeArgs = true;
	static $customMethod = null;
	
	/**
	 * ReturnData类初始化
	 * 一般不用手动执行，在执行ReturnInfo()时会自动执行
	 * @return void
	 */
	static public function init(){
		$HTTP_ACCEPT = isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : '';
		
		if(strpos($HTTP_ACCEPT, 'application/json') !== FALSE){
			self::$accept = 'json';
		}elseif(
			strpos($HTTP_ACCEPT, 'text/javascript') !== FALSE || 
			strpos($HTTP_ACCEPT, 'application/javascript') !== FALSE || 
			strpos($HTTP_ACCEPT, 'application/ecmascript') !== FALSE || 
			strpos($HTTP_ACCEPT, 'application/x-ecmascript') !== FALSE || 
			i('callback')
		){
			self::$accept = 'jsonp';
		}elseif(
			strpos($HTTP_ACCEPT, 'text/html') === FALSE && (
				strpos($HTTP_ACCEPT, 'application/xml') !== FALSE || 
				strpos($HTTP_ACCEPT, 'text/xml') !== FALSE
			)
		){
			self::$accept = 'xml';
		}else{
			self::$accept = 'normal';
		}
		
		if(self::$allowDataTypeArgs){
			if(($accept = i('dataType', 'all', ['json', 'jsonp', 'xml'], 'null')) != 'null'){
				self::$accept = $accept;
			}
		}
	}
	
	/**
	 * 返回信息/输出提示
	 * 自动根据客户端accept返回json、jsonp、xml、网页
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
	static public function returnData($status = 'success', $msg = '', $title = '', $data = '', $code = '', $url = '', $wait = null, $onlyData = false){
		if(!self::$accept){
			self::init();
		}
		
		if(is_array($msg) || is_array($title)){
			$args = array_merge([
				'msg'		=> is_array($msg) ? '' : $msg,
				'title'		=> is_array($title) ? '' : $title,
			], is_array($msg) ? $msg : $title);
			
			extract($args);
		}
		
		if($onlyData){
			if($onlyData === TRUE && self::$accept == 'normal'){
				self::$accept = 'json';
			}else{
				self::$accept = $onlyData;
			}
		}
		
		if(in_array((string)$status, ['success', 'green', '√', 'ok', '1', 'successd', 'successful'])){
			$status = 'success';
		}elseif(in_array((string)$status, ['error', 'danger', 'red', '×', 'no', '0', 'fail', 'failed', 'failure'])){
			$status = 'error';
		}else{
			$status = 'info';
		}
		
		if(!$msg){
			$msg = l([
				'success'	=> '您的操作已成功执行！',
				'error'		=> '操作未能成功进行！',
				'info'		=> '提示'
			][$status]);
		}
		
		if(!$title){
			$title = l([
				'success'	=> '成功',
				'error'		=> '失败',
				'info'		=> '提示'
			][$status]);
		}
		
		if(!$code){
			$code = [
				'success'	=> 1,
				'error'		=> 0,
				'info'		=> 2
			][$status];
		}
		
		if(!$url && $status != 'info'){
			$url = $status == 'success' ? (isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] ? safe_url($_SERVER['HTTP_REFERER']) : '-1') : '-1';
		}

		
		if($wait === null){
			if($status == 'success'){
				$wait = 10;
			}elseif($status == 'error'){
				$wait = 15;
			}else{
				$wait = 0;
			}
		}
		if(in_array(self::$accept, ['json', 'jsonp', 'xml'])){
			header('Content-Type: ' . [
				'json'	=> 'application/json; charset=UTF-8',
				'jsonp'	=> 'application/javascript; charset=UTF-8',
				'xml'	=> 'application/xml; charset=UTF-8'
			][self::$accept]);
		}
		
		die(self::encodeReturnInfo([
			'status'	=> $status,
			'msg'		=> $msg,
			'title'		=> $title,
			'data'		=> $data,
			'code'		=> $code,
			'url'		=> $url,
			'wait'		=> $wait
		], self::$accept));
	}
	
	/**
	 * 将返回数据编码为指定格式
	 * @param array  $returnInfo 数据，传入数组
	 * @param string $encodeType 编码类型
	 * @return mixed
	 */
	static public function encodeReturnInfo($returnInfo, $encodeType = 'json'){
		switch($encodeType){
			case 'json':
				header('Content-Type: application/json');
				return json_encode($returnInfo, JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE);
			break;
			
			case 'jsonp':
				header('Content-Type: application/javascript');
				return safe_html(i(self::$jsonpCallback)) . '(' . json_encode($returnInfo, JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE) . ')';
			break;
			
			case 'xml':
				header('Content-Type: application/xml');
				return arr2xml($returnInfo);
			break;
			
			case 'normal':
			default:
				if(self::$customMethod != null){
					self::$customMethod($returnInfo);
				}
			
				$sysinfo = [
					'type'		=> $returnInfo['status'],
					'title'		=> $returnInfo['title'],
					'info'		=> $returnInfo['msg'],
					'buttons'	=> false,
					'autoJump'	=> false,
					//'color'		=> '#2488ff',
				];
				
				if(isset($returnInfo['data']['more'])){
					$sysinfo['more'] = $returnInfo['data']['more'];
				}else{
					$sysinfo['more'] = [];
				}
				
				if(isset($returnInfo['data']['moreTitle'])){
					$sysinfo['moreTitle'] = $returnInfo['data']['moreTitle'];
				}else{
					$sysinfo['moreTitle'] = '';
				}
				
				if(!in_array($returnInfo['code'], [0, 1, 2]))
					$sysinfo['code'] = $returnInfo['code'];
				
				if($returnInfo['status'] != 'info'){
					$sysinfo['autoJump'] = [
						'url'	=> $returnInfo['url'],
						'sec'	=> $returnInfo['wait']
					];
				}
				
				self::sysinfo($sysinfo);
			break;
		}
	}
	
	/**
	 * 设置自定义处理方法
	 * @param function $func 自定义处理函数
	 * @return void
	 */
	static public function setCustomMethod($func){
		self::$customMethod = $func;
	}
	
	/**
	 * 输出系统页面
	 * 比较丰富美观的系统页面
	 *
	 * @param array $args 参数列表
	 * @return void
	 */
	static public function sysinfo($args = []){
		if(!isset($args['title'])){
			switch($args['type']){
				case 'success':
					$args['title'] = '耶~！成功了呢！';
				break;
				
				case 'info':
					$args['title'] = '提示信息';
				break;
				
				case 'error':
					$args['title'] = '啊哈... 出了一点点小问题_(:з」∠)_';
				break;
			}
		}
		if(!isset($args['more'])){
			switch($args['type']){
				case 'success':
					$args['moreTitle'] = '温馨提示：';
					$args['more'] = [
						'您进行的操作已经完成了！'
					];
				break;
				
				case 'error':
					$args['moreTitle'] = '啊哈... 出了一点点小问题_(:з」∠)_';
					$args['more'] = [
						'你进入了一个未知的领域...',
						'手滑输错了地址',
						'网线被机房管理员踢了',
						'没有张贴『永不宕机』灵☯符'
					];
				break;
			}
		}
		if(!isset($args['code'])){
			switch($args['type']){
				case 'success':
					$args['code'] = '200';
					$args['statusCode'] = '200';
				break;
				
				case 'info':
					$args['code'] = '233';
					$args['statusCode'] = '200';
				break;
				
				case 'error':
					$args['code'] = '500';
					$args['statusCode'] = '500';
				break;
			}
		}
		
		$defaultValue = [
			'code'=>'404',
			'type'=>'error', //[info, error, success]
			'title'=>'啊哈... 出了一点点小问题_(:з」∠)_',
			'info'=>'页面找不到啦...',
			'moreTitle'=>'可能的原因：',
			'more'=>[
				'你进入了一个未知的领域...',
				'手滑输错了地址',
				'网线被机房管理员踢了',
				'没有张贴『永不宕机』灵☯符'
			],
			'statusCode'=>'233',
			'buttons'=>false,
			'autoJump'=>false
			/*
			'autoJump'=>[
				'url'=>'/',
				'sec'=>'60'
			]
			*/
		];
		
		$defaultValue = array_merge($defaultValue, $args);
		
		if($defaultValue['buttons'] == false || !is_array($defaultValue['buttons'])) $defaultValue['buttons'] = [
			[
				'type'=>'success',
				'title'=>'返回首页',
				'href'=>'/',
				'target'=>'_self'
			],
			[
				'type'=>'danger',
				'title'=>'返回上一层',
				'href'=>'javascript:history.go(-1)',
				'target'=>'_self'
			]
		];
		
		$defaultValue['colorList'] = [
			'success'	=> '#a6e3a0',
			'error'		=> '#ff9494',
			'info'		=> '#bdc3fd'
		];
		
		$defaultValue['color'] = isset($defaultValue['color']) ? $defaultValue['color'] : 
			(isset($defaultValue['colorList'][$defaultValue['type']]) ? $defaultValue['colorList'][$defaultValue['type']] : $defaultValue['colorList']['success']);

		extract($defaultValue);
		
		if(Router::$type == 'cmd'){
			echo_c([
				'[' . $type . ':' . $code . ']:' . $title,
				$info,
				$moreTitle,
				implode("\r\n", $more)
			]);
			die();
		}
		
		set_http_code($statusCode);
		include(adminphp . 'Template/sysinfo.php');
		
		/* 性能统计 END */
		\AdminPHP\Module\PerformanceStatistics::log('END');
		\AdminPHP\Module\PerformanceStatistics::show();
		die();
	}
	
	/**
	 * 输出系统提示
	 *
	 * @param string $notice 提示内容
	 * @param string $go     跳转到的页面
	 * @param int    $time   跳转倒计时(秒)
	 * @return void
	 */
	static public function notice($notice, $go = '', $time = 0){
		$notice = [
			'notice'	=>	$notice,
			'time'		=> $time
		];
		
		if($go){
			$notice['go'] = $go;
		}
		
		extract($notice);
		
		if(Router::$type == 'cmd'){
			echo_c([
				'[notice]:' . $notice
			]);
			die();
		}
		
		include(adminphp . 'Template/notice.php');
		
		/* 性能统计 END */
		\AdminPHP\Module\PerformanceStatistics::log('END');
		\AdminPHP\Module\PerformanceStatistics::show();
		die();
	}
	
	/**
	 * 跳转至url
	 * @param string $url  地址
	 * @param int    $type 类型 301=永久跳转 302=临时跳转
	 * @return void
	 */
	static public function go($url, $type = 302){
		if(\AdminPHP\Router::$type == 'cmd'){
			return false;
		}
		
		self::set_http_code($type);
		header('Location: ' . $url);
		
		die();
	}
	
	/**
	 * 发送HTTP状态
	 * @param int $code 状态码
	 * @return boolean
	 */
	static public function set_http_code($code) {
		if(\AdminPHP\Router::$type == 'cmd'){
			return false;
		}
		
		$statusCode = [
			// Informational 1xx
			100 => 'Continue',
			101 => 'Switching Protocols',
			
			// Success 2xx
			200 => 'OK',
			201 => 'Created',
			202 => 'Accepted',
			203 => 'Non-Authoritative Information',
			204 => 'No Content',
			205 => 'Reset Content',
			206 => 'Partial Content',
			
			// Redirection 3xx
			300 => 'Multiple Choices',
			301 => 'Moved Permanently',
			302 => 'Moved Temporarily ',  // 1.1
			303 => 'See Other',
			304 => 'Not Modified',
			305 => 'Use Proxy',
			307 => 'Temporary Redirect',
			
			// Client Error 4xx
			400 => 'Bad Request',
			401 => 'Unauthorized',
			402 => 'Payment Required',
			403 => 'Forbidden',
			404 => 'Not Found',
			405 => 'Method Not Allowed',
			406 => 'Not Acceptable',
			407 => 'Proxy Authentication Required',
			408 => 'Request Timeout',
			409 => 'Conflict',
			410 => 'Gone',
			411 => 'Length Required',
			412 => 'Precondition Failed',
			413 => 'Request Entity Too Large',
			414 => 'Request-URI Too Long',
			415 => 'Unsupported Media Type',
			416 => 'Requested Range Not Satisfiable',
			417 => 'Expectation Failed',
			
			// Server Error 5xx
			500 => 'Internal Server Error',
			501 => 'Not Implemented',
			502 => 'Bad Gateway',
			503 => 'Service Unavailable',
			504 => 'Gateway Timeout',
			505 => 'HTTP Version Not Supported',
			509 => 'Bandwidth Limit Exceeded'
		];
		
		if(isset($statusCode[$code])) {
			header('HTTP/1.1 ' . $code . ' ' . $statusCode[$code]);
			header('Status:' . $code . ' ' . $statusCode[$code]);
			
			return true;
		}else{
			return false;
		}
	}
}