<?php
$dsn = 'mysql:dbname=test;host=127.0.0.1;charset=utf8;port=3306';
$pdo = new PDO($dsn, 'root', 'root', [ PDO::ATTR_PERSISTENT => true ]);

print_r($pdo->query('select * from testpaper_user'));

/* $a = $pdo->prepare('select * from testpaper_user where username = :a');
$a->execute([':a'=>'xlch88']);

print_r($a->fetchAll(PDO::FETCH_ASSOC)); */