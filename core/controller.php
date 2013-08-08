<?php
(defined('AGREE_LICENSE') && AGREE_LICENSE === true) || die('No access allowed.');
class Controller{
	const doExt ='on';
	const doBefore ='before';
	const doAfter ='after';
	public $do ='404';
	public $route =array();
	public function __construct($Route =array()){
		$this->route =$Route;
		$this->exec(self::doBefore, false);
		$this->exec($Route[1]);
	}
	public function __destruct(){
		$this->exec(self::doAfter, false);
	}
	public function exec($name, $isAction =true){
		if ($isAction){
			call_user_func(array($this, self::doBefore . $name));
			if (method_exists($this, self::doExt . $name)){
				call_user_func(array($this, self::doExt . $name));
				call_user_func(array($this, self::doAfter . $name));
			} else
				$this->on404();
		} else
			call_user_func(array($this, $name));
	}
	public function __call($m, $a){
		Next::callEvent('system.404');
	}
	public function on403($Info =NULL){
		header("HTTP/1.0 403 Forbidden");
		$Info =is_null($Info) ? 'core.403_forbidden' : $Info;
		die(Next::Language($Info));
	}
	public function on404($Info =NULL){
		header("HTTP/1.0 404 Not Found");
		$Info =is_null($Info) ? 'core.404_not_found' : $Info;
		die(Next::Language($Info));
	}
	public function redirect($Uri ='/', $Info =NULL, $Step =3){
		header('Refresh: ' . $Step . '; url=' . $Uri);
		$Info =is_null($Info) ? 'core.redirect' : $Info;
		Next::$hasAction =false;
		die(Next::Language($Info));
		// die(empty($Info) ?$Info :Next::Language('core.redirect'));
	}
	public static function error(){}
}