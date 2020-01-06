<?php
extract(array(
	'code'		=> '500',
	'type'		=> 'error', //[info, error, success]
	'title'		=> '您的PHP版本过于古老',
	'info'		=> '您的PHP版本过于古老<br/><small style="font-size: 20px;line-height: 16px;color: #2196F3;">You PHP Version is too low.</small>',
	'moreTitle'	=> '温馨提示：',
	'more'		=> array(
		'当前PHP版本(php version) = <font color="red">' . PHP_VERSION . '</font>',
		'您需要将PHP版本升级到5.6或以上。',
		'如果您使用的是虚拟主机，请调整PHP版本。',
		'为了<font color="red">提升性能和安全性</font>，推荐您使用<font color="red">PHP7</font>。'
	),
	'statusCode'=> '500',
	'buttons'	=> false,
	'autoJump'	=> false,
	'color'		=> '#2488ff',
));

include('sysinfo.php');