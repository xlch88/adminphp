<?php
ini_set("display_errors", "On");

error_reporting(E_ALL | E_STRICT);
define("root",			dirname(__FILE__) . DIRECTORY_SEPARATOR);
define('template',		root . 'template' . DIRECTORY_SEPARATOR);
define('controller',	root . 'controller' . DIRECTORY_SEPARATOR);

include(root . 'common/core.php');