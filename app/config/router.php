<?php
return [
	'router'	=> [
		'/user-(id|username)'		=> 'index/router',
	],
	'regex'		=> [
		'username'	=> '[a-zA-Z0-9_\x7f-\xff]{1,20}'
	]
];