<?php
//路由这个坑有空再填...
function url($controller, $method = 'index', $args = ''){
	if($method == ''){
		$method = 'index';
	}
	return '?c=' . $controller . '&m=' . $method . ($args ? '&' . $args : '');
}