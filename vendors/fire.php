<?php (defined('AGREE_LICENSE') &&AGREE_LICENSE ===true) ||die('No access allowed.');

require_once ___NEXT .'vendors/FirePHP/fb.php';

class vFire extends FB{
	public static function Has() {
		$instance = FirePHP::getInstance(true);
		return $instance->detectClientExtension();
	}
}