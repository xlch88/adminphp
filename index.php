<?php
ini_set("display_errors", "On");
error_reporting(E_ALL | E_STRICT);

define("root",				dirname(__FILE__) . DIRECTORY_SEPARATOR);
define('adminphp',			root . 'core' . DIRECTORY_SEPARATOR);
define('templatePath',		root . 'template' . DIRECTORY_SEPARATOR);

define('appPath',			root . 'app' . DIRECTORY_SEPARATOR);
define('defaultApp',		'');

include(adminphp . 'AdminPHP.php');