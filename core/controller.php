<?php
class controller{
	const doExt ='on';
	const doBefore ='before';
	const doAfter ='after';
	public $do ='404';
	public $app;
	public $route =array();
	public function __construct($route, $app){
		$this->app =$app;
		$this->route =$route;
		$this->exec(self::doBefore, false);
		$this->exec($route[1]);
	}
	public function __destruct(){
		$this->exec(self::doAfter, false);
	}
	public function exec($name, $isAction =true){
		if ($isAction){
			$_fnc =self::doExt . $name;
			$_fnc1 =self::doBefore . $name;
			$_fnc2 =self::doAfter . $name;

			if (method_exists($this, $_fnc1)) $this->$_fnc1();
			if (method_exists($this, $_fnc)){
				$this->$_fnc();
				if (method_exists($this, $_fnc2)) $this->$_fnc2();
			} else
				$this->app->status(404, $name);
		} else if (method_exists($this, $name)) $this->$name();
	}
	public function arg($name, $def=null){
		return isset($this->route['args'][$name]) ?$this->route['args'][$name] :$def;
	}
	public function method($method=null){
		return is_null($method) ?$this->route['method'] :$method ==$this->route['method'];
	}
}

class rest_controller extends controller{
	public function exec($name, $isAction =true){
		if ($isAction){
			$_fnc =$this->route['method']. $name;
			$_fnc1 =self::doBefore . $name;
			$_fnc2 =self::doAfter . $name;
			if (method_exists($this, $_fnc1)) $this->$_fnc1();
			if (method_exists($this, $_fnc)){
				$this->$_fnc();
				if (method_exists($this, $_fnc2)) $this->$_fnc2();
			} else
				$this->app->status(404, $name);
		} else if (method_exists($this, $name)) $this->$name();
	}
}