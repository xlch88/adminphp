<?php
namespace AdminPHP\Module;

class Validator{
	static public $defaultMessages = [
		'between'	=> '{key}长度范围必须在{min}~{max}之间。',
		'min'		=> '{key}至少需要{min}个字符。',
		'max'		=> '{key}不能超过{min}个字符。',
		'must'		=> '{key}是必填项。',
		'true'		=> '{key}为必选项。',
		'false'		=> '{key}必须未选中。',
		'chinaphone'=> '{key}格式错误，请输入正确的中国大陆手机号码。',
		'email'		=> '{key}格式错误，请输入正确的邮件地址。',
		'url'		=> '{key}格式错误，请输入正确的url。',
		'domain'	=> '{key}格式错误，请输入正确的域名(不带http://)。',
		'bool'		=> '{key}必须为“是”或“否”。',
		'same'		=> '{key}必须为与{samekey}相同。',
		'ip'		=> '{key}格式错误，请输入正确的ip地址。',
		'ipv4'		=> '{key}格式错误，请输入正确的ipv4地址。',
		'ipv6'		=> '{key}格式错误，请输入正确的ipv6地址。',
		'int'		=> '{key}格式错误，请输入一个整数。',
		'number'	=> '{key}格式错误，请输入一个数值(整数或小数)。',
		
		'get'		=> '参数 {key} 只能由get方式传入。',
		'post'		=> '参数 {key} 只能由post方式传入。',
		
		'default'	=> '{key}验证未通过。'
	];
	public $messages = [];
	public $data  = [];
	public $rules = [];
	public $names = [];
	public $customRules = [];
	static public $globalCustomRule = [];
	
	public $result = [];
	public $failMessages = [];
	
	public function __construct(array $data, array $rules, $messages = [], $names = []){
		$this->data		= $data;
		$this->rules	= $rules;
		$this->names	= $names;
		$this->messages	= array_merge(self::$defaultMessages, $messages);
	}
	
	public function verify(){
		$this->result = [];
		$this->failMessages = [];
		
		$returnResult = true;
		foreach($this->data as $key => $value){
			if(isset($this->rules[$key])){
				$verifySingleResult = [];
				if(!self::verifySingle($key, $this->rules[$key], $this->data, $verifySingleResult, $this->customRules)){
					$this->result[] = [
						'key'	=> $key,
						'fails'	=> $verifySingleResult
					];
					
					$returnResult = false;
				}
			}
		}
		
		$this->failMessages = self::mkMessages($this->result, $this->messages, $this->names);
		
		return $returnResult;
	}
	
	public function addRule(string $rule, $func, $message = ''){
		$this->customRules[$rule] = $func;
		
		if($message){
			$this->messages[$rule] = $message;
		}
	}
	
	static public function addGlobalRule(string $rule, $func, $message = ''){
		self::$globalCustomRule[$rule] = $func;
		
		if($message){
			self::$defaultMessages[$rule] = $message;
		}
	}
	
	static private function mkMessages($result, $messages, $names){
		$return = [];
		
		foreach($result as $index => $row){
			$row['key'] = isset($names[$row['key']]) ? $names[$row['key']] : $row['key'];
			
			foreach($row['fails'] as $index => $fail){
				$msg = isset($messages[$fail[0]]) ? $messages[$fail[0]] : $messages['default'];
				
				switch($fail[0]){
					case 'between':
						$msg = str_replace(['{key}', '{min}', '{max}'], [$row['key'], $fail[1][0], $fail[1][1]], $msg);
					break;
					
					case 'min':
						$msg = str_replace(['{key}', '{min}'], [$row['key'], $fail[1]], $msg);
					break;
					
					case 'max':
						$msg = str_replace(['{key}', '{max}'], [$row['key'], $fail[1]], $msg);
					break;
					
					case 'same':
						$samekey = isset($names[$fail[1]]) ? $names[$fail[1]] : $fail[1];
						$msg = str_replace(['{key}', '{samekey}'], [$row['key'], $samekey], $msg);
					break;
					
					default:
						$msg = str_replace(['{key}'], [$row['key']], $msg);
					break;
				}
				
				$return[] = $msg;
			}
		}
		
		return $return;
	}
	
	static public function verifySingle($key, $rules, $data = [], &$return = [], $customRules = []){
		if(!is_array($rules)){
			$rules = explode('|', $rules);
		}
		
		$is = true;
		foreach($rules as $index => $rule){
			if(!self::isMatch($key, $rule, $data, $customRules)){
				$is = false;
				$return[] = $rule;
				//break;
			}
		}
		
		return $is;
	}
	
	static public function isMatch($key, &$rule, $data = [], $customRules = []){
		$input = $data[$key];
		$rule = explode(':', $rule);
		
		switch($rule[0] = strtolower($rule[0])){
			case 'max':
			case 'min':
				if(count($rule) != 2){
					throw new Exception(l('验证规则参数有误!'));
				}
				
				$rule[1] = (int)$rule[1];
			break;
			
			case 'same':
				if(count($rule) != 2){
					throw new Exception(l('验证规则参数有误!'));
				}
			break;
			
			case 'between':
				if(count($rule) != 2){
					throw new Exception(l('验证规则参数有误!'));
				}
				
				if(count($rule[1] = explode(',', $rule[1])) != 2){
					throw new Exception(l('验证规则参数有误!'));
				}
			break;
		}
		
		switch($rule[0]){
			case 'between':
				$is = strlen($input) >= (int)$rule[1][0] && strlen($input) <= (int)$rule[1][1];
			break;
			
			case 'min':
				$is = strlen($input) >= $rule[1];
			break;
			
			case 'max':
				$is = strlen($input) <= $rule[1];
			break;
			
			case 'must':
				$is = $input !== '';
			break;
			
			case 'true':
				$is = in_array(strtolower($input), ['1', 'true', 't', 'yes', 'y', '√', 'on']);
			break;
			
			case 'false':
				$is = !in_array(strtolower($input), ['1', 'true', 't', 'yes', 'y', '√', 'on']);
			break;
			
			case 'chinaphone':
				$is = preg_match('/^1[3456789]\d{9}$/', $input);
			break;
			
			case 'email':
				$is = filter_var($input, FILTER_VALIDATE_EMAIL);
			break;
			
			case 'domain':
				$is = filter_var($input, FILTER_VALIDATE_DOMAIN);
			break;
			
			case 'url':
				$is = filter_var($input, FILTER_VALIDATE_URL);
			break;
			
			case 'bool':
				$is = in_array(strtolower($input), ['1', 'true', 't', 'yes', 'y', '√', 'on', /* | */ '0', 'false', 'f', 'no', 'n', '×', 'off']);
			break;
			
			case 'same':
				$is = isset($data[$rule[1]]) ? $data[$rule[1]] === $input : false;
			break;
			
			case 'ip':
				$is = filter_var($input, FILTER_VALIDATE_IP);
			break;
			
			case 'ipv4':
				$is = filter_var($input, FILTER_VALIDATE_IP, ['flags' => [FILTER_FLAG_IPV4]]);
			break;
			
			case 'ipv6':
				$is = filter_var($input, FILTER_VALIDATE_IP, ['flags' => [FILTER_FLAG_IPV6]]);
			break;
			
			case 'int':
				$is = ctype_digit(strval($input));
			break;
			
			case 'number':
				$is = is_numeric($input);
			break;
			
			case 'get':
				$is = isset($_GET[$key]) && !isset($_POST[$key]);
			break;
			
			case 'post':
				$is = isset($_POST[$key]) && !isset($_GET[$key]);
			break;
		}
		
		if(!isset($is)){
			if(isset($customRules[$rule[0]])){
				$is = call_user_func_array($customRules[$rule[0]], [$key, $rule, $data]);
			}elseif(isset(self::$globalCustomRule[$rule[0]])){
				$is = call_user_func_array(self::$globalCustomRule[$rule[0]], [$key, $rule, $data]);
			}else{
				$is = false;
			}
		}
		
		return $is;
	}
}