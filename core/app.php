<?php
class app{
	public $options =array(
		'i18n' =>'zh_cn',
		'path' =>array(
			'c' =>'controllers',
			'm' =>'models',
			'l' =>'libraries',
			'h' =>'helpers',
			'v' =>'vendors'));
	public $route =array();
	public $hook =array();
	public $controllers =array();
	public $buffer =array(
		're' =>array(
			'i18n' =>false,
			'config' =>false),
		'cache' =>array());
	private $cache =array(
		'i18n' =>false,
		'config' =>false);
	public function __construct($config){
		$_path =$config['/'];
		if (is_string($_path)) $config['/'] =array(
			$_path);
		$config['/'][] =___NEXT;
		$this->options =array_merge($this->options, $config);

		$this->buffer['/'] =realpath($this->options['/'][0]) . '/';

		$this->cache['i18n'] =$this->buffer['cache']['i18n'] =$this->buffer['/'] . $this->_file('i18n', 'cache');
		$this->cache['config'] =$this->buffer['cache']['config'] =$this->buffer['/'] . $this->_file('config', 'cache');
	}
	public function __destruct(){
		if ($this->buffer['re']['i18n']) next::_savevarcache($this->buffer['cache']['i18n'], $this->cache['i18n']);
		if ($this->buffer['re']['config']) next::_savevarcache($this->buffer['cache']['config'], $this->cache['config']);
	}
	public function run($route =array()){
		if (empty($route)) $this->error('core.no-route');
		$this->route =$route;
		$this->controll($route);
	}
	public function controll($route){
		$controller =next::import('c' . $route[0]);
		$this->controllers[$controller] =new $controller($route, $this);
		unset($this->controllers[$controller]);
	}
	public function config($word, $params =null){
		if (strpos($word, '.') == false) return null;
		if ($this->cache['config'] !== false){
			if (is_string($this->cache['config'])) if (is_file($this->cache['config'])) $this->cache['config'] =include $this->cache['config'];
			else $this->cache['config'] =array();
			if (isset($this->cache['config'][$word])) return $this->cache['config'][$word];
		}
		$_keys =explode('.', $word, 2);
		$_key =isset($_keys[1]) ? $_keys[1] : null;
		$_ns =$_keys[0];
		$config =array();
		$f =$this->_file($_ns, 'config');
		foreach ($this->options['/'] as $_path)
			if (is_file($_path . $f)) require $_path . $f;
		if (isset($config[$_ns]) && isset($config[$_ns][$_key])){
			$this->cache['config'][$word] =$config[$_ns][$_key];
			$this->buffer['re']['config'] =true;
			return $config[$_ns][$_key];
		}
		return null;
	}
	public function i18n($word, $params =array()){
		if (strpos($word, '.') == false) return $word;
		if ($this->cache['i18n'] !== false){
			if (is_string($this->cache['i18n'])) if (is_file($this->cache['i18n'])) $this->cache['i18n'] =include $this->cache['i18n'];
			else $this->cache['i18n'] =array();
			if (isset($this->cache['i18n'][$word])) return vsprintf($this->cache['i18n'][$word], $params);
		}
		$_keys =explode('.', $word, 2);
		$_key =isset($_keys[1]) ? $_keys[1] : null;
		$_ns =$_keys[0];
		$i18n =array();
		$f =$this->_file($this->options['i18n'] . '/' . $_ns, 'i18n');
		foreach ($this->options['/'] as $_path)
			if (is_file($_path . $f)) require $_path . $f;
		if (isset($i18n[$_ns]) && isset($i18n[$_ns][$_key])){
			$this->cache['i18n'][$word] =$i18n[$_ns][$_key];
			$this->buffer['re']['i18n'] =true;
			return vsprintf($i18n[$_ns][$_key], $params);
		}
		return $word;
	}
	public function error($err){
		//NEED: set hook
		if (isset($this->hook[$err])){return call_user_func_array($this->hook[$err], $this);}
		$args =func_get_args();
		$ln =array_shift($args);
		die($this->i18n($ln, $args));
	}
	public function _file($name, $mod =null){
		if (!empty($mod)){
			$_path =isset($this->options['dir'][$mod]) ? $this->options['dir'][$mod] : $mod . '/';
			$_ext =isset($this->options['ext'][$mod]) ? $this->options['ext'][$mod] : '.php';
			return strtolower($_path . $name . $_ext);
		}
		return $name . '.php';
	}
	public function _path($name, $mod =null){
		if (is_file($name)) return $name;
		$_file =(is_null($mod)) ? $name . '.php' : $this->_file($name, $mod);
		foreach ($this->options['/'] as $_path)
			if (is_file($_path . $_file)) return $_path . $_file;
		return false;
	}
}
class webapp extends app{
	private $status =array(
		403 =>array("HTTP/1.0 403 Forbidden",'core.403-forbidden'),
		404 =>array("HTTP/1.0 404 Not Found",'core.404-not-found'));
	public function run($route =array()){
		if (empty($route)){
			$type =isset($this->options['route']) ? $this->options['route'] : 'router';
			$router =next::import(strpos($type, 'router') ===0 ?$type :'router_'.$type);
			//$router =next::import((substr($type, -6) !== 'router') ? $type . 'router' : $type);
			$router =new $router();
			$route =$router->getRoute();
		}
		parent::run($route);
	}
	public function redirect($Uri ='/', $Info =NULL, $Step =3){
		header('Refresh: ' . $Step . '; url=' . $Uri);
		$Info =is_null($Info) ? 'core.redirect' : $Info;
		die($this->i18n($Info));
	}
	public function status($number, $info =''){
		$status =isset($this->status[$number]) ? $this->status[$number] : array('HTTP/1.0 ' . $number,$info);
		header($status[0]);
		$this->error($status[1], $info);
	}
	public function view($File, $Data =array(), $Return =NULL){
		$_file =$this->_path($File, 'views');
		if ($_file){
			extract($Data, EXTR_REFS);
			ob_start();
			include ($_file);
			if ($Return){
				return ob_get_clean();
			} else
				ob_end_flush();
		}
	}
}
class ajaxapp extends webapp{}
class cliapp extends app{}