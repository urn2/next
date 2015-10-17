<?php
(defined('AGREE_LICENSE') && AGREE_LICENSE === true) || die('No access allowed.');
version_compare(PHP_VERSION, '5.4', '<') && die('next requires PHP 5.4 or newer.');

require __DIR__.'/core/o2.php';
require __DIR__.'/core/cache.php';
require __DIR__.'/core/view.php';
require __DIR__.'/core/router.php';
require __DIR__.'/core/model.php';
require __DIR__.'/core/controller.php';
require __DIR__.'/core/app.php';
require __DIR__.'/core/next.php';

define('___NOW', $_SERVER['REQUEST_TIME']);
next::init();
next::loadFrom(['hView' => 'helpers/view.php',
	'hdb' => 'helpers/db.php',
	'hdb_mysql' => 'helpers/db/mysql.php',
	'hdb_sqlite' => 'helpers/db/sqlite.php',
	'hSQL' => 'helpers/sql.php',
	'hRecord' => 'helpers/record.php',
	'hMemcache' => 'helpers/memcache.php',
	'hImage' => 'helpers/image.php',
	'hImageCAPTCHA' => 'helpers/image/captcha.php',
	'hImageTTFCAPTCHA' => 'helpers/image/ttfcaptcha.php',
	'hFile' => 'helpers/file.php',
	'hCURL' => 'helpers/curl.php',]);

