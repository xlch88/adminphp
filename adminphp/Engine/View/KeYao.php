<?php
/* ----------------------------------------------- *
 | [ AdminPHP ] Version : 2.0 beta
 | 简单粗暴又不失高雅的迫真 OOP MVC 框架，，，
 |
 | URL     : https://www.adminphp.net/
 * ----------------------------------------------- *
 | Name    : ViewEngine/KeYao (模板引擎 - 可耀💊💊💊)
 | Surprise: https://yaoke.cloud/
 | Author  : Xlch88 (i@xlch.me)
 | LICENSE : WTFPL http://www.wtfpl.net/about
 * ----------------------------------------------- */

namespace AdminPHP\Engine\View;

use AdminPHP\AdminPHP;
use AdminPHP\Hook;
use AdminPHP\Exception\ViewException;
use AdminPHP\Engine\View\KeYao\Methods;
use AdminPHP\Engine\View\KeYao\Layout;

class KeYao {
	private $regex = [
		'method'	=> '/\B@(@?\w+(?:::\w+)?)([ \t]*)(\( ( (?>[^()]+) | (?3) )* \))?([\r\n]*)/x',
		'echo'		=> '/(@)?%s\s*(.+?)\s*%s(\r?\n)?/s',
		'echoOr'	=> '/^(?=\$)(.+?)(?:\s+or\s+)(.+?)$/s',
		'comment'	=> '/%s--(.*?)--%s/s'
	];
	
	private $echoTags = [
		'safe'	=> ['{{', '}}'],
		'echo'	=> ['{!!', '!!}']
	];
	
	private $config = [];
	private $footer = '';
	
	static private $methods = [];
	static public $customIfMethods = [];
	
	public function __construct($config){
		$config = array_merge([
			'path'			=> root . 'runtime' . DIRECTORY_SEPARATOR . 'view',
			'file_subfix'	=> '.php',
			'file_render'	=> '.yao.php'
		], $config);
		
		if(!realpath($config['path']) || !is_dir($config['path'])){
			if(!mkdir($config['path'], 0777, true)){
				throw new \InvalidArgumentException(l('路径是无效的，且无法被创建！'));
			}
		}
		$config['path'] = realpath($config['path']) . DIRECTORY_SEPARATOR;
		
		$this->config = $config;
		
		self::$methods = array_merge((new Methods)->get(), Layout::getMethods(), self::$methods);
	}
	
	static public function setMethod(string $name, $func){
		self::$methods[$name] = $func;
	}
	
	static public function setIfMethod(string $name, $func){
		self::$customIfMethods[$name] = $func;
		self::$methods[$name] = function($match)use($name){
			if(!isset($match[4])) return;
			
			return '<?php if(\AdminPHP\Engine\View\KeYao::$customIfMethods[\'' . $name . '\'](' . $match[4] . ')): ?>';
		};
		
		self::$methods['end' . $name] = function($match){
			return '<?php endif; ?>';
		};
	}
	
	public function getFile($file, $isRoot){
		$_templateFilePath = $isRoot ? '' : templatePath;
		$_file = $file . $this->config['file_subfix'];
		
		Hook::do('template_file', ['templateFilePath' => &$_templateFilePath, 'templateFile' => &$_file, 'isRoot' => $isRoot]);
			
		if(is_file($_templateFilePath . $_file)){ //不处理
			return $_templateFilePath . $_file;
		}
		
		$_file = $file . $this->config['file_render'];
		Hook::do('template_file', ['templateFilePath' => &$_templateFilePath, 'templateFile' => &$_file, 'isRoot' => $isRoot]);
		$_file = $_templateFilePath . $_file;
		
		if(!is_file($_file)){
			throw new ViewException(0, $_templateFilePath, $file, $_file);
		}
		
		$cacheFile = str_replace([root, '\\', '_', '/'], ['', '/', '__', '_'], realpath($_file));
		
		if(!is_file($this->config['path'] . $cacheFile) || AdminPHP::$config['debug']){
			$data = file_get_contents($_file);
			$data = $this->render($data);
			
			if(file_put_contents($this->config['path'] . $cacheFile, $data) === FALSE){
				throw new ViewException(1, $_templateFilePath, $file, $this->config['path'] . $cacheFile);
			}
		}
		
		return $this->config['path'] . $cacheFile;
	}
	
	public function render($data){
		$result = '';
		$this->footer = '';
		
        foreach (token_get_all($data) as $token) {
            $result.= !is_array($token) ? $token : (function($token){
				list($id, $content) = $token;

				if ($id == T_INLINE_HTML) {
					$content = $this->render_remove_comments($content);
					$content = $this->render_tag($content);
					$content = $this->render_methods($content);
				}
				
				return $content;
			})($token);
        }
		
		$result .= $this->footer;
		$result = str_replace(['?><?php', '?> <?php'], '', $result);
		
		return $result;
	}
	
	/**
	 * 处理注释
	 * {{- xxxxxxx -}}
     *
	 * @param string $data 数据
	 */
	private function render_remove_comments($data){
        return preg_replace(
			sprintf(
				$this->regex['comment'],
				$this->echoTags['safe'][0], $this->echoTags['safe'][1]
			), '', $data);
	}
	
	/**
	 * 处理输出标签
	 * {{ $a }}     - 经过safe_html输出
	 * {!! $a !!}   - 直接输出
     *
	 * @param string $data 数据
	 */
	private function render_tag($data) {
		foreach(array_keys($this->echoTags) as $tag){
			$regex = sprintf($this->regex['echo'], $this->echoTags[$tag][0], $this->echoTags[$tag][1]);
			
			$data = preg_replace_callback($regex, function($matches) use ($tag){
				$whitespace = empty($matches[3]) ? '' : $matches[3].$matches[3];
				
				$value = preg_replace($this->regex['echoOr'], 'isset($1) ? $1 : $2', $matches[2]);
				
				return $matches[1] ? substr($matches[0], 1) : ('<?=' . ($tag == 'safe' ? 'safe_html(' . $value . ')' : $value) . '; ?>' . $whitespace);
			}, $data);
		}
		
		return $data;
	}
	
	/**
	 * 处理函数
	 * 例子: if(xxx)
	 *       endif
     *
	 * @param string $data 数据
	 */
	private function render_methods($data) {
        $data = preg_replace_callback($this->regex['method'], function ($match){
			if(isset(self::$methods[$match[1]])){
				$result = self::$methods[$match[1]]($match, $this->footer);
				if($result !== ''){
					return $result . $match[5];
				}else{
					return '';
				}
			}
			
			return $match[0];
		}, $data);
		
		return $data;
	}
}