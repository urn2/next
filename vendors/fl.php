<?php
require_once ___NEXT .'vendors/firelogger/firelogger.php';

define('FIRELOGGER_NO_CONFLICT', false);
define('FIRELOGGER_NO_EXCEPTION_HANDLER', false);
define('FIRELOGGER_NO_ERROR_HANDLER', false);

class vFL{
	public static function out(){
            $args = func_get_args();
            call_user_func_array(array(FireLogger::$default, 'log'), $args);
	}
}