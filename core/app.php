<?php

class app{
	public $options = ['i18n' => 'zh_cn',
		'path' => ['c' => 'controllers',
			'm' => 'models',
			'l' => 'libraries',
			'h' => 'helpers',
			'v' => 'third-parties'],
		'ext' => ['data' => '',]];
	public $route = [];
	public $hook = [];
	public $controllers = [];
	private $_reCache = false;
	private $cache = ['i18n' => false,
		'config' => false];
	/**
	 * @var cache
	 */
	private $_cache = null;
	public function __construct($config){
		if(isset($config['cache'])){
			$_cache = $config['cache'];
			$c = 'cache_'.array_shift($_cache);
			$n = array_shift($_cache);
			$this->_cache = new $c($n, $_cache);
			unset($config['cache']);
		}
		$opts = $this->_loadCache('opts');
		if(empty($opts)){
			$_path = $config['/'];
			if(is_string($_path)) $config['/'] = [$_path];
			$config['/'][] = next::$path;
			//krsort($config['/']);
			foreach($config as $key => $value){
				if(!isset($this->options[$key])) $this->options[$key] = [];
				$this->options[$key] = is_array($value) ?array_merge($this->options[$key], $value) :$value;
			}
		}else $this->options = $opts;
		$this->cache = $this->_loadCache('caches');
		if(empty($this->cache)){
			$this->cache['config'] = isset($config['config']) ?$config['config'] :[];
			$this->cache['i18n'] = isset($config['i18n']) ?$config['i18n'] :[];
		}
	}
	public function __destruct(){
		$this->_saveCache($this->options, 'opts');
		if($this->_reCache) $this->_saveCache($this->cache, 'caches');
	}
	public function _saveCache($var, $type = 'config'){
		return (is_null($this->_cache)) ?false :$this->_cache->set('cache.'.$type, $var, 60*3);
	}
	public function _loadCache($type = 'config'){
		$r = (is_null($this->_cache)) ?false :$this->_cache->get('cache.'.$type);
		return empty($r) ?[] :$r;
	}
	public function options(){
		return $this->options;
	}
	public function run($route = []){
		if(empty($route)) $this->error('core.no-route');
		$this->route = $route;
		$this->control($route);
	}
	public function control($route){
		$_controller = 'c'.$route[0];
		try{
			$this->controllers[$_controller] = new $_controller($route, $this);
		}catch(Exception $e){
			$this->controllers[$_controller] = new controller($route, $this);
		}
		unset($this->controllers[$_controller]);
	}
	public function config($word, $params = null){
		if(strpos($word, '.') == false) return $params;
		if($this->cache['config'] === false) $this->cache['config'] = $this->_loadCache('config');
		if(isset($this->cache['config'][$word])) return $this->cache['config'][$word];

		$_keys = explode('.', $word, 2);
		$_key = isset($_keys[1]) ?$_keys[1] :null;
		$_ns = $_keys[0];
		$config = [];
		$f = $this->_file($_ns, 'config');
		$_root =$this->options['/'];
		krsort($_root);
		foreach($_root as $_path) if(is_file($_path.$f)) require $_path.$f;
		if(isset($config[$_ns]) && isset($config[$_ns][$_key])){
			$this->cache['config'][$word] = $config[$_ns][$_key];
			$this->_reCache = true;
			return $config[$_ns][$_key];
		}
		return $params;
	}
	public function i18n($word, $params = []){
		if(strpos($word, '.') == false) return $word;
		if($this->cache['i18n'] === false) $this->cache['i18n'] = $this->_loadCache('i18n');
		if(isset($this->cache['i18n'][$word])) return vsprintf($this->cache['i18n'][$word], $params);

		$_keys = explode('.', $word, 2);
		$_key = isset($_keys[1]) ?$_keys[1] :null;
		$_ns = $_keys[0];
		$i18n = [];
		$f = $this->_file($this->options['i18n'].'/'.$_ns, 'i18n');
		$_root =$this->options['/'];
		krsort($_root);
		foreach($_root as $_path) if(is_file($_path.$f)) require $_path.$f;
		if(isset($i18n[$_ns]) && isset($i18n[$_ns][$_key])){
			$this->cache['i18n'][$word] = $i18n[$_ns][$_key];
			$this->_reCache = true;
			return vsprintf($i18n[$_ns][$_key], $params);
		}
		return $word;
	}
	public function error($err){
		//NEED: set hook
		if(isset($this->hook[$err])){
			return call_user_func_array($this->hook[$err], [$this, $err]);
		}
		$args = func_get_args();
		$ln = array_shift($args);
		die($this->i18n($ln, $args));
	}
	public function _file($name, $mod = null){
		if(!empty($mod)){
			$_path = isset($this->options['dir'][$mod]) ?$this->options['dir'][$mod] :$mod.'/';
			$_ext = isset($this->options['ext'][$mod]) ?$this->options['ext'][$mod] :'.php';
			return strtolower($_path.$name.$_ext);
		}
		return $name.'.php';
	}
	public function _path($name, $mod = null, $inherit = true){
		if(is_file($name)) return $name;
		$_file = (is_null($mod)) ?$name.'.php' :$this->_file($name, $mod);
		if(!$inherit || $mod == 'controllers'){//控制器不允许继承
			$_path =$this->options['/'][0];
			return (is_file($_path.$_file)) ?$_path.$_file :false;
		}
		foreach($this->options['/'] as $_path){
			if(is_file($_path.$_file)) return $_path.$_file;
		}
		return false;
	}
}

class webapp extends app{
	private $_status = [403 => ["HTTP/1.0 403 Forbidden", 'core.status.403'],
		404 => ["HTTP/1.0 404 Not Found", 'core.status.404']];
	public function __construct($config){
		parent::__construct($config);
		if(!isset($this->options['route'])) $this->options['route'] = 'web';
		$this->header('NEXT', 'vea 2005-2015');
		//header_remove('X-Powered-By');
		//header('NEXT: vea 2005-2015');
	}
	public function header($name, $value){
		if(is_array($name)){
			if(func_num_args() ==1) foreach($name as $_name=>$_value) header($_name.':'.$_value);
			else foreach($name as $_name) header($_name.':'.$value);
		} else header($name.':'.$value);
	}
	public function run($route = []){
		if(empty($route)){
			$type = isset($this->options['route']) ?$this->options['route'] :'router';
			$router = strpos($type, 'router') === 0 ?$type :'router_'.$type;
			$router = new $router();
			$route = $router->getRoute();
		}
		parent::run($route);
	}
	public function redirect($Uri = null, $Info = null, $Step = 3){
		if(is_null($Uri)) $Uri = $_SERVER['REQUEST_URI'];
		if($Step ==0){
			header('Location: '.$Uri);
			die();
		} else{
			header('Refresh: '.$Step.'; url='.$Uri);
			$Info = is_null($Info) ?'core.redirect' :$Info;
			die($this->i18n($Info, $Uri));
		}
	}
	public function status($number, $info = ''){
		$status = isset($this->_status[$number]) ?$this->_status[$number] :['HTTP/1.0 '.$number, $info];
		header($status[0]);
		$this->error($status[1], $info);
	}
}

class cliapp extends app{
	private $status = [403 => ["HTTP/1.0 403 Forbidden", 'core.status.403'],
		404 => ["HTTP/1.0 404 Not Found", 'core.status.404']];
	public function __construct($config){
		parent::__construct($config);
		if(!isset($this->options['route'])) $this->options['route'] = 'cli';
	}
	public function run($route = []){
		if(empty($route)){
			$type = isset($this->options['route']) ?$this->options['route'] :'router';
			$router = strpos($type, 'router') === 0 ?$type :'router_'.$type;
			$router = new $router();
			$route = $router->getRoute();
		}
		parent::run($route);
	}
	public function redirect($Uri = null, $Info = null, $Step = 3){
		die($Uri.' '.$Info);
	}
	public function status($number, $info = ''){
		$status = isset($this->status[$number]) ?$this->status[$number] :['HTTP/1.0 '.$number, $info];
		//header($status[0]);
		$this->error($status[1], $info);
	}
}