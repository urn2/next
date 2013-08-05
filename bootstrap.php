<?php
(defined('AGREE_LICENSE') && AGREE_LICENSE === true) || die('No access allowed.');

version_compare(PHP_VERSION, '5.3', '<') && exit('NEXT requires PHP 5.3 or newer.');

define('___N', microtime(true));
define('___M', memory_get_usage(true));
define('___NOW', $_SERVER['REQUEST_TIME']);
define('___NEXT', __DIR__ . DIRECTORY_SEPARATOR);

define('___PATH', dirname($_SERVER['SCRIPT_FILENAME']) . '/');

if (!defined('___DEBUG')) define('___DEBUG', '');


require ___NEXT. 'core/type.php';
require ___NEXT. 'core/cache.php';
require ___NEXT. 'helpers/cache/cache_file.php';
require ___NEXT. 'core/debug.php';
require ___NEXT. 'core/controller.php';

$___load =array(
	'Controller' =>'core/controller.php', 
	'Debug' =>'core/debug.php', 
	'Exception' =>'core/exception.php', 
	//'App' =>'core/app.php', 
	'hCache_default' =>'helpers/cache/cache_default.php', 
	'hRouter' =>'helpers/router.php', 
	'hRouter_Rewrite' =>'helpers/router/router_rewrite.php');


require ___NEXT . 'core/next.php';
