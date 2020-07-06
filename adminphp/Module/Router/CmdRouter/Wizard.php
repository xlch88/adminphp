<?php
namespace AdminPHP\Module\Router\CmdRouter;

use AdminPHP\Module\Router\CmdRouter;

class Wizard{
	static public function start($keys, $flags, $inputs, $regexes){
		//与CmdRouter::$inputArgs合并的列表
		$merge = [
			'args'	=> [],
			'flags'	=> []
		];
		
		//开始向导
		foreach($inputs as $index => $info){
			//打印tips
			if($info['tips']) CmdRouter::print($info['tips'], false);
			
			if(!isset($info['type']) || $info['type'] !== 'flag'){ //类型:参数, 输入值
				//已输入，跳过。
				if(isset(CmdRouter::$inputArgs['args'][$info['arg']])){
					continue;
				}
			
				//缺省字段填充
				$keyinfo = array_merge([
					'type'		=> 'string',
					'bewrite'	=> '',
					'valueName'	=> $info['arg'],
					'regex'		=> '',
					'must'		=> false
				], isset($keys[$info['arg']]) ? $keys[$info['arg']] : []);
				
				$verifyFunction = function($value)use($keyinfo, $regexes){
					if($keyinfo['type'] == 'array' && $value == '#end'){
						return true;
					}
					
					if(isset($keyinfo['regex']) && $keyinfo['regex'] !== ''){ //需要验证
						$regex = $keyinfo['regex'];
						
						//如果是以/xxxx/的形式，则直接当做正则表达式，否则当做名称，从regexList获取。
						if(!(substr($regex, 0, 1) == '/' && substr($regex, -1) == '/')){
							//正则表达式名称未找到
							if(!isset($regexes[$keyinfo['regex']])){
								throw new \Exception(l('正则表达式 %s 未找到', $keyinfo['regex']));
							}
							
							//找到了
							$regex = '/' . $regexes[$keyinfo['regex']] . '/';
						}
						
						//正则表达式验证
						if(!preg_match($regex, $value)){
							return '验证未通过';
						}
					}
					
					//必填
					if($keyinfo['must'] && $value === ''){
						return '不能为空';
					}
					
					return true;
				};
				
				if($keyinfo['type'] == 'string'){ //单值
					//进入输入状态
					$input = self::input(
						'&b请输入 ' . $keyinfo['valueName'],
						'string',
						isset($info['default']) ? $info['default'] : (isset($keyinfo['default']) ? $keyinfo['default'] : ''),
						$verifyFunction
					);
					
					$merge['args'][$info['arg']] = $input;
				}else{	//多值
					//计数器 记录输入了几个参数了
					$count = 0;
					
					//输入开始
					while(true){
						//若已满足输入数量
						if(isset($keyinfo['count']) && $count >= $keyinfo['count']){
							break;
						}
						
						//进行输入
						$input = self::input(
							'&b请输入 ' . (is_array($keyinfo['valueName']) ? (
								isset($keyinfo['valueName'][$count]) ? $keyinfo['valueName'][$count] : $keyinfo['valueName'][count($keyinfo['valueName']) - 1] . '[' . ($count - count($keyinfo['valueName']) + 2) . '] (输入#end结束)'
							) : $keyinfo['valueName'] . '[' . $count . '] (输入#end结束)'),
							'string',
							isset($info['default']) ? (
								is_array($info['default']) ? (
									isset($info['default'][$count]) ? $info['default'][$count] : ''
								) : $info['default']
							) : (isset($keyinfo['default']) ? (
								is_array($keyinfo['default']) ? (
									isset($keyinfo['default'][$count]) ? $keyinfo['default'][$count] : ''
								) : $keyinfo['default']
							) : ''),
							$verifyFunction
						);
						
						if($input == '#end'){
							break;
						}
						
						$merge['args'][$info['arg']][] = $input;
						
						$count++;
					}
				}
			}else{ //类型:flag
				//已输入，跳过。
				if(isset(CmdRouter::$inputArgs['flags'][$info['arg']])){
					continue;
				}
				
				$flaginfo = isset($flags[$info['arg']]) ? $flags[$info['arg']] : [];
				
				//进入输入状态
				$input = self::input(
					'&b请输入 ' . $info['arg'],
					'bool',
					isset($flaginfo['default']) ? $flaginfo['default'] : (isset($keyinfo['default']) ? $keyinfo['default'] : '')
				);
				
				$merge['flags'][$info['arg']] = $input;
			}
		}
		
		return $merge;
	}
	
	static public function input($title, $type, $default = '', $verify = null){
		if($type !== 'bool'){
			CmdRouter::echo($title . ($default !== '' ? '(默认:' . $default . ')' : '') . ' : ');
			
			if(($input = trim(fgets(STDIN))) === ''){
				$input = $default;
			}
			
			if(!is_null($verify) && ($result = $verify($input)) !== TRUE){
				CmdRouter::echo('&c输入错误!&b' . $result . "\r\n");
				$input = self::input($title, $type, $default, $verify);
			}
			
			return $input;
		}else{
			CmdRouter::echo($title . ' [Y/n]' . ($default !== '' ? '(默认:' . $default . ')' : '') . ': ');
			
			if(($input = trim(fgets(STDIN))) === ''){
				$input = $default;
			}
			
			if(in_array(strtolower($input), ['yes', 'y', '1', 't', 'true', '√'])){
				$input = true;
			}elseif(in_array(strtolower($input), ['no', 'n', '0', 'f', 'false', '×'])){
				$input = false;
			}else{
				CmdRouter::echo('&e输入错误! 请输入 &aY(yes/1/t/true/√) &e或者 &cN(no/0/f/false/×)' . "\r\n");
				$input = self::input($title, $type, $default, $verify);
			}
			
			return $input;
		}
	}
}