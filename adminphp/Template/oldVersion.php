<?php
extract(array(
	'code'		=> '500',
	'type'		=> 'error', //[info, error, success]
	'title'		=> '您的PHP版本过于古老',
	'info'		=> '您的PHP版本过于古老',
	'moreTitle'	=> '温馨提示：',
	'more'		=> array(
		'当前PHP版本 = <font color="red">' . PHP_VERSION . '</font>',
		'您需要将PHP版本升级到5.5或以上',
		'如果您使用的是虚拟主机，请调整PHP版本',
		'推荐您使用PHP7。'
	),
	'statusCode'=> '500',
	'buttons'	=> false,
	'autoJump'	=> false,
	'yonakaPic'	=> '//img.xlch8.cn/adminphp/sysinfo/info.png',
	'color'		=> '#2488ff',
));

include('sysinfo.php');