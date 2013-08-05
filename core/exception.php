<?php (defined('AGREE_LICENSE') &&AGREE_LICENSE ===true) ||die('No access allowed.');
class NSException extends Exception{
	function __construct($message, $code =0)
	{
		parent::__construct($message, $code);
	}
	static function dump(Exception $ex)
	{
		$out =str_replace(array(___APP, ___SYS, ___WEB, '\\'), array('APP/', 'SYS/', 'WEB/', '/'), $ex->getTraceAsString());
		if (ini_get('html_errors')){
			echo nl2br(htmlspecialchars($out));
		} else{
			echo $out;
		}
		//debug_print_backtrace();
	}
}