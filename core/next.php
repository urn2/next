<?php
(defined('AGREE_LICENSE') && AGREE_LICENSE === true) || die('No access allowed.');

class next{
	public static $buffer = ['loader' => []];
	/**
	 *
	 * @var app
	 */
	public static $app = null;//active app;
	public static $path = __DIR__;
	public static $work = __DIR__;
	public static function loadFrom($loadPath){
		self::$buffer['loader'] = empty(self::$buffer['loader']) ?$loadPath :array_merge(self::$buffer['loader'], $loadPath);
	}
	public static function init(){
		//set next :path,ext ...
		self::$path = dirname(__DIR__).'/';
		self::$work = dirname($_SERVER['SCRIPT_FILENAME']).'/';
		spl_autoload_register([__CLASS__, 'import']);
	}
	/**
	 *
	 * Enter description here ...
	 * @param array $config
	 * @return app
	 */
	public static function createApp($config){
		if(isset($config['load'])) self::loadFrom($config['load']);
		$type = isset($config['app']) ?$config['app'] :'web';
		$type .= 'app';
		if(!isset($config['/'])) $config['/'] = self::$path;
		self::$app = self::$buffer['app'][$config['id']] = new $type($config);
		return self::$app;
	}
	private static function _importFromLoader($model, $paths = []){
		if(class_exists($model, false) || interface_exists($model, false)) return $model;
		if(isset(self::$buffer['loader'][$model])){
			if(is_string($paths)) $paths = [$paths, self::$path];elseif(is_null($paths)) $paths = [self::$path];
			foreach($paths as $_path){
				$file = is_file($_path.self::$buffer['loader'][$model]) ?$_path.self::$buffer['loader'][$model] :false;
				if($file){
					include $file;
					return $model;
				}
			}
		}
		return false;
	}
	public static function import($model){
		$_opts = is_null(self::$app) ?['/' => self::$path,
			'path' => ['c' => 'controllers',
				'm' => 'models',
				'l' => 'libraries',
				'h' => 'helpers',
				'v' => 'third-parties']] :self::$app->options();
		$inloader = self::_importFromLoader($model, $_opts['/']);
		if($inloader) return $inloader;else{
			$_mod_path = $_opts['path'];
			$_mod = null;
			$_name = null;
			if(isset($_mod_path[$model[0]])){
				$_mod = $_mod_path[$model[0]];
				$_name = substr($model, 1);
			}
			if(strpos($_name, '_') !== false) $_name = strtok($_name, '_').'/'.$_name;
			$file = self::$app->_path($_name, $_mod);
			if($file == false){
				if($model[0] == 'c') throw new Exception(self::i18n('core.no-import', $model));else self::$app->error('core.no-import', $model);
			}
			include $file;
			if(!class_exists($model, false)) self::$app->error('core.no-'.$_mod, $model);
			return $model;
		}
	}
	public static function config($word, $params = null){
		return self::$app->config($word, $params);
	}
	public static function i18n($word, $params = []){
		return self::$app->i18n($word, $params);
	}
	public static function saveCache($var, $type = 'next'){
		return self::$app->_saveCache($var, $type);
	}
	public static function loadCache($type = 'next'){
		return self::$app->_loadCache($type);
	}
}