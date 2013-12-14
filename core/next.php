<?php
(defined('AGREE_LICENSE') && AGREE_LICENSE === true) || die('No access allowed.');
class next{
	public static $buffer =array();
	public static $app =null;//active app;
	public static function init($config =array()){
		//set next :path,ext ...
		global $___load;
		self::$buffer['loader'] =$___load;
		spl_autoload_register(array('next', 'import'));
	}
	/**
	 *
	 * Enter description here ...
	 * @param array $config
	 * @return app
	 */
	public static function createApp($config){
		$type =isset($config['app']) ? $config['app'] : 'web';
		//$app =strpos($type, 'app') ===0 ?$type :'app_'.$type;
		$app =self::import((substr($type, -3) !== 'app') ? $type . 'app' : $type);
		self::$app =self::$buffer['app'][$config['id']] =new $app($config);
		return self::$app;
	}
	public static function import($model){
		if (class_exists($model, false) || interface_exists($model, false)) return $model;
		if (isset(self::$buffer['loader'][$model])){
			foreach (self::$app->options['/'] as $_path) {
				$file =realpath($_path.self::$buffer['loader'][$model]);
				if($file){
					include $file;
					return $model;
				}
			}
			self::$app->error('core.no-import', $model);
		} else{
			$_mod_path =self::$app->options['path'];
			$_mod =null;
			$_name =null;
			if (isset($_mod_path[$model[0]])){
				$_mod =$_mod_path[$model[0]];
				$_name =substr($model, 1);
			}
			if (strpos($_name, '_') !== false) $_name =strtok($_name, '_') . '/' . $_name;
			$file =self::$app->_path($_name, $_mod);
			if ($file == false) self::$app->error('core.no-import', $model);
			include $file;
			if (!class_exists($model, false)) self::$app->error('core.no-' . $_mod, $model);
			return $model;
		}
	}
	public static function config($word, $params =null){
		return self::$app->config($word, $params);
	}
	public static function i18n($word, $params =array()){
		return self::$app->i18n($word, $params);
	}
	public static function _savevarcache($filename, $var){
		return @file_put_contents($filename, "<?php return " . var_export($var, true) . ";");
	}
}