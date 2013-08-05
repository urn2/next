<?php

class App{

	public $name ='next';

	public $defaultController ='index';

	public $layout ='layout';

	public function __construct($Config){
		//var_dump($Config);
		Next::Event('system.shutdown', array('Next', 'shutdown'));
		Next::Event('system.execute', array('App', 'Execute'));
		
		Next::Event('system.error', array('App', '_error'));
		Next::Event('system.404', array('App', '_404'));
		Next::Event('system.403', array('App', '_403'));
		
		Next::Event('system.ready');
	}

	public function Run(){
		//Next::Event('system.initialize');
		Next::Event('system.execute');
		Next::Event('system.shutdown');
	}

	public static function Execute($RouterInfo =null, $Router =''){
		Next::Benchmark('_benchmark_router');
		$_router =Next::Router($Router);
		Next::Benchmark('_benchmark_router', true);
		Next::Benchmark('_benchmark_execute');
		return $_router->Execute();
	}

	public function _403($Info =NULL){
		header("HTTP/1.0 403 Forbidden");
		die($Info ?$Info :Next::Language('core.403_forbidden'));
	}

	public function _404($Info =NULL){
		header("HTTP/1.0 404 Not Found");
		die($Info ?$Info :Next::Language('core.404_not_found'));
	}

	public function _error(){}
}