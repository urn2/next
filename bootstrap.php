<?php
(defined('AGREE_LICENSE') && AGREE_LICENSE === true) || die('No access allowed.');
//version_compare(PHP_VERSION, '5.4', '<') && exit('NEXT requires PHP 5.4 or newer.');

//define('___N', microtime(true));
//define('___M', memory_get_usage(true));
define('___NOW', $_SERVER['REQUEST_TIME']);

define('___NEXT', __DIR__ . DIRECTORY_SEPARATOR);
define('___PATH', dirname($_SERVER['SCRIPT_FILENAME']) . '/');

if (!defined('___DEBUG')) define('___DEBUG', '');

$___load =array(
	'router' =>'core/router.php',
	'hView'=>'helpers/view.php',
	'hdb'=>'helpers/db.php',
	'hdb_mysql'=>'helpers/db/mysql.php',
	'hdb_sqlite'=>'helpers/db/sqlite.php',
	'hImage'=>'helpers/image.php',
	'hFile'=>'helpers/file.php',
	'vFL' =>'vendors/fl.php',
	'vFire' =>'vendors/fire.php',
);

require ___NEXT . 'core/app.php';
require ___NEXT . 'core/controller.php';
require ___NEXT . 'core/model.php';
require ___NEXT . 'core/next.php';
