<?php
(defined('AGREE_LICENSE') && AGREE_LICENSE === true) || die('No access allowed.');

class Next{
	public static $Caches =array(
		//'filesystem' =>array('/' =>array(___NEXT), 'dir' =>array(), 'ext' =>array()), 
		//'config' =>array(), 
		//'language' =>array(),
		'views' =>array('in' =>false, 'has' =>false), 
		'benchmark' =>array('level' =>array(), 'marks' =>array()), 
		'event' =>array('callback' =>array(), 'run' =>array()));
	private static $Session =array('router' =>array(), 'cache' =>array());
	private static $Buffer =array(
		'app' =>'next', 
		'apppath' =>'./', 
		'cache' =>'Cache', 
		//'cache' =>'hCache_file', 
		//'cache' =>'hCache_Memcache', 
		'router' =>'hRouter');
	public static $hasAction =false;
	public static $Error =array();
	public static $app ='next';
	protected static function _filesystem($FS){
		$_filesystem =self::Cache()->get('filesystem', array());
		if (empty($_filesystem)){
			if (isset($FS['dir'])) foreach ($FS['dir'] as $_type =>$_path)
				$_filesystem['path'][$_type] =$_path;
			if (isset($FS['ext'])) foreach ($FS['ext'] as $_type =>$_ext)
				$_filesystem['ext'][$_type] =$_ext;
			$_filesystem['/'] =array();
			if (isset($FS['/'])){
				if (is_array($FS['/'])){
					foreach ($FS['/'] as $_path){
						$_path =realpath($_path);
						if ($_path) $_filesystem['/'][] =$_path . DIRECTORY_SEPARATOR;
					}
				} else{
					$_path =realpath($FS['/']);
					if ($_path) $_filesystem['/'][] =$_path . DIRECTORY_SEPARATOR;
				}
			}
			$_filesystem['/'][] =___NEXT;
			self::Cache()->set('filesystem', $_filesystem);
		}
	}
	/**
	 * 初始化应用
	 * 
	 */
	public static function Initialize($Config =array()){
		self::Benchmark('_benchmark_initialize');
		spl_autoload_register(array('Next', 'autoload'));
		set_error_handler(array('Next', 'error'));
		set_exception_handler(array('Next', 'error'));
		if (defined('___DEBUG') && ___DEBUG){
			ob_start();
			register_shutdown_function(array('Next', 'callEvent'), 'system.shutdown');
		}
		if (isset($Config['id'])){
			self::$Buffer['app'] =$Config['id'];
			self::$app =$Config['id'];
		} 
		//if (isset($Config['filesystem'])) 
		self::_filesystem(isset($Config['filesystem']) ?$Config['filesystem'] :array(
			'/' =>array(___NEXT), 
			'dir' =>array(), 
			'ext' =>array()));
		$_cache =self::Config('app.cache', 'file');
		if (!empty($_cache)) self::$Buffer['cache'] ='hCache_' . $_cache;
		$_router =self::Config('app.router', '');
		if (!empty($_router)) self::$Buffer['router'] ='hRouter_' . $_router;
		self::addEvent('system.404', array('Controller', '_404'));
		self::addEvent('system.shutdown', array('Next', 'shutdown'));
		self::addEvent('system.execute', array('Next', 'Execute'));
		self::callEvent('system.ready');
		self::Benchmark('_benchmark_initialize', true);
	}
	public static function Run(){
		Next::callEvent('system.execute');
	}
	public static function Execute($RouterInfo =null, $Router =''){
		$_router =self::Router($Router);
		return $_router->Execute();
	}
	public static function Shutdown(){
		$data =self::Benchmarks();
		//ob_end_flush();
		Debug::Error();
		Debug::Benchmark($data);
		//Next::Dump($_COOKIE);
		//if (isset($_SESSION)) Next::Dump($_SESSION);
		//ob_end_clean();
	}
	/**
	 * 
	 * @param string $Cache
	 * @return Cache
	 */
	public static function Cache($Cache =''){
		$_caches =&self::$Session['cache'];
		if (!isset($_caches[$Cache])){
			$_cache =self::$Buffer['cache'];
			if (!empty($Cache)) $_cache =$Cache; // $_cache .='_' . $Cache;
			$_caches[$Cache] =new $_cache(self::$Buffer['app']);
		}
		return $_caches[$Cache];
	}
	/**
	 * 
	 * @param unknown_type $Router
	 * @return hRouter
	 */
	public static function Router($Router =''){
		$_routers =&self::$Session['router'];
		if (!isset($_routers[$Router])){
			$_router =self::$Buffer['router'];
			if (!empty($Router)) $_router .='_' . $Router;
			$_routers[$Router] =new $_router();
		}
		return $_routers[$Router];
	}
	public static function Link($CAI ='', $Args =array(), $Prefix ='', $Separator ='/', $Router =''){
		$_routers =&self::$Session['router'];
		if (!isset($_routers[$Router])){
			$_router =self::$Buffer['router'];
			if (!empty($Router)) $_router .='_' . $Router;
			$_routers[$Router] =new $_router();
		}
		return $_routers[$Router]->Link($CAI, $Args, $Prefix);
	}
	/**
	 * 读取语言文件 位置为 i18n目录下 语言见配置
	 * 
	 * @param string $KeyWord 关键字
	 */
	public static function Language($KeyWord){
		if (is_array($KeyWord)) return $KeyWord;
		if (strpos($KeyWord, '.') == false) return $KeyWord;
		$_locale =self::Config('app.language', 'zh_CN');
		$_lang =self::Cache()->get('language', array());
		if (!isset($_lang[$_locale][$KeyWord])){
			$_keys =explode('.', $KeyWord, 2);
			$_key =isset($_keys[1]) ?$_keys[1] :null;
			$_ns =$_keys[0];
			$_language =self::Cache()->get('language.all', array());
			$_language =array();
			if (!isset($_language[$_locale][$_ns])){
				$i18n =array();
				$f =self::File("{$_locale}/{$_ns}", 'i18n');
				$_filesystem =self::Cache()->get('filesystem', array());
				//if (empty($_filesystem)) $_filesystem =self::$Caches['filesystem'];
				$_bases =$_filesystem['/'];
				krsort($_bases);
				$_has =false;
				foreach ($_bases as $_base)
					if (file_exists($_base . $f)){
						require $_base . $f;
						$_has =true;
					}
				if ($_has){
					$_language[$_locale][$_ns] =$i18n;
					self::Cache()->set('language.all', $_language);
				} else
					return $KeyWord;
			}
			if (is_null($_key)){
				$_str =$_language[$_locale][$_ns];
			} elseif (isset($_language[$_locale][$_ns][$_key])){
				$_str =$_language[$_locale][$_ns][$_key];
			} else
				$_str =$_key;
			$_lang[$_locale][$KeyWord] =$_str;
			self::Cache()->set('language', $_lang);
		} else
			$_str =$_lang[$_locale][$KeyWord];
		if (is_string($_str) && func_num_args() > 1){
			$_args =array_slice(func_get_args(), 1);
			$_str =vsprintf($_str, is_array($_args[0]) ?$_args[0] :$_args);
		}
		$_lang[$_locale][$KeyWord] =$_str;
		return $_str;
	}
	/**
	 * 读取或设定配置运行配置
	 * 
	 * @param string $Key 配置名 支持“.”分隔
	 * @param any $Value 读取模式为不存在时默认，写入模式为写入内容
	 * @param boolean $IsSet 是否为写入模式
	 */
	public static function Config($Key =null, $Value =null, $IsSet =null){
		static $__level =0;
		$__level++;
		if ($__level > 1) return null;
		$_config =self::Cache()->get('config', array());
		$_keys =explode('.', $Key, 2);
		if ($IsSet){
			if (isset($_keys[1]))
				$_config[$_keys[0]][$_keys[1]] =$Value;
			else
				$_config[$_keys[0]] =$Value;
			return;
		}
		$_key =isset($_keys[1]) ?$_keys[1] :null;
		$_ns =$_keys[0];
		if (!isset($_config[$_ns])){
			$config =array();
			$f =self::File($_ns, 'config');
			//if (empty($_filesystem)) $_filesystem =self::$Caches['filesystem'];
			$_filesystem =self::Cache()->get('filesystem', array());
			$_bases =$_filesystem['/'];
			krsort($_bases);
			foreach ($_bases as $_base){
				if (file_exists($_base . $f)) include $_base . $f;
			}
			$_config[$_ns] =$config;
			self::Cache()->set('config', $_config);
		}
		$__level--;
		if (is_null($_key))
			return $_config[$_ns];
		elseif (isset($_config[$_ns][$_key]))
			return $_config[$_ns][$_key];
		else
			return $Value;
	}
	/**
	 * 根据filesystem整理文件名
	 * 
	 * @param string $FS 文件名
	 * @param string $mod 参见filesystem
	 */
	public static function File($FS, $mod =null){
		$_filesystem =self::Cache()->get('filesystem', array());
		if (!empty($mod)){
			$mod =strtolower($mod);
			$_path =isset($_filesystem['dir'][$mod]) ?$_filesystem['dir'][$mod] :$mod . '/';
			$_ext =isset($_filesystem['ext'][$mod]) ?$_filesystem['ext'][$mod] :'.php';
			return strtolower($_path . $FS . $_ext);
		}
		return $FS . '.php';
	}
	/**
	 * 对文件名进行整理并返回是否存在
	 * 
	 * @param string $pathName 文件名或路径
	 * @param string $mod 参见filesystem
	 */
	public static function Path($pathName, $mod =null){
		if (is_file($pathName)) return $pathName;
		$_path =(empty($mod)) ?$pathName . '.php' :self::File($pathName, $mod);
		$_filesystem =self::Cache()->get('filesystem', array());
		foreach ($_filesystem['/'] as $_base){
			if (is_file($p =$_base . $_path)) return $p;
		}
		return false;
	}
	public static function AppPath(){
		$_filesystem =self::Cache()->get('filesystem', array());
		return $_filesystem['/'][0];
	}
	/**
	 * 读取类 自动读取
	 * 
	 * @param string $ModelName 类名
	 */
	public static function autoload($ModelName){
		self::Benchmark('_benchmark_load' . ",$ModelName");
		if (class_exists($ModelName, false) || interface_exists($ModelName, false)) return $ModelName;
		$_filesystem =self::Cache()->get('filesystem', array());
		$_load =&$_filesystem['load'];
		if (!isset($_load[$ModelName])){
			global $___load;
			if (isset($___load[$ModelName])){
				$p =___NEXT . $___load[$ModelName];
			} else{
				$_path =array(
					'c' =>'controllers', 
					'm' =>'models', 
					'l' =>'libraries', 
					'h' =>'helpers', 
					'v' =>'vendors');
				$_mod =isset($_path[$ModelName[0]]) ?$_path[$ModelName[0]] :'';
				$_name =substr($ModelName, 1);
				if (strpos($_name, '_') !== false) $_name =strtok($_name, '_') . '/' . $_name;
				$p =self::Path($_name, $_mod);
			}
			$_load[$ModelName] =$p;
			self::Cache()->set('filesystem', $_filesystem);
		} else
			$p =$_load[$ModelName];
				//ob_start();
		if (!empty($p)) include ($p);
		//ob_end_clean();
		self::Benchmark('_benchmark_load' . ",$ModelName", true);
	}
	/**
	 * 输出或返回视图
	 * 
	 * @param string $FSName 视图名称
	 * @param array $Data 使用在视图中的变量 key为变量名 value为值
	 * @param boolean $Return 是否返回视图内容
	 */
	public static function View($FSName, $Data =array(), $Return =true){
		return hView::factory($FSName, $Data)->Flush($Return);
		self::Benchmark('_benchmark_view' . ",$FSName");
		$_file =self::Path($FSName, 'views');
		if ($Return){
			if ($_file){
				extract($Data, EXTR_REFS);
				try{
					self::$Caches['views']['in'] =true;
					ob_start();
					include ($_file);
					$r =ob_get_contents();
					ob_end_clean();
					self::$Caches['views']['in'] =false;
				} catch (Exception $e){
					$r =$FSName;
				}
			} else
				$r =Next::Language('core.no_template', $FSName);
			self::Benchmark('_benchmark_view' . ",$FSName", true);
			return $r;
		} else{
			Next::$Caches['views']['has'] =true;
			if ($_file){
				self::$Caches['views']['in'] =true;
				extract($Data, EXTR_REFS);
				include ($_file);
				self::$Caches['views']['in'] =false;
			} else{
				//Next::Dump(Next::Language('core.no_template', $FSName));
				echo Next::Language('core.no_template', $FSName);
				self::Benchmark('_benchmark_view' . ",$FSName", true);
					//throw new Exception(self::Language('core.no_template', $FSName));
			}
		}
		self::Benchmark('_benchmark_view' . ",$FSName", true);
		return '';
	}
	/**
	 * 输出任意变量内容
	 * 
	 * @param any $Dump
	 */
	public static function Dump($Dump){
		//if (defined('___DEBUG') && ___DEBUG && class_exists('Debug')){
		if (class_exists('Debug')){
			call_user_func_array(array('Debug', 'Dump'), func_get_args());
		} else{
			echo '<pre>';
			foreach (func_get_args() as $_arg){
				echo var_export($_arg);
				//var_dump($_arg);
				echo "\n";
			}
			echo '</pre>';
		}
	}
	public static function Error($Exception, $Message =null, $File =null, $Line =null){
		try{
			$_php_error =(func_num_args() === 5);
			//self::$hasError =true;
			if ($_php_error){
				$_error =array(
					'code' =>$Exception, 
					'message' =>$Message, 
					'file' =>$File, 
					'line' =>$Line);
			} else{
				$_error =array(
					'code' =>$Exception->getCode(), 
					'message' =>$Exception->getMessage(), 
					'file' =>$Exception->getFile(), 
					'line' =>$Exception->getLine());
			}
			$_filesystem =self::Cache()->get('filesystem', array());
			$_error['file'] =str_replace($_filesystem['/'], DIRECTORY_SEPARATOR, $_error['file']);
			//$_error['file'] =str_replace(array(___WEB, '\\'), array('WEB/','/'), $_error['file']);
			self::$Error[] =$_error;
			if (self::$Caches['views']['in']) return;
			if ($_php_error && (error_reporting() & $Exception) === 0) return;
			if (is_numeric($_error['code'])){
				$_codes =self::Language('error.' . $_error['code']);
				if (is_array($_codes)){
					list($_error['level'], $_error['error'], $_error['description']) =$_codes;
				} else{
					$_error['level'] =1;
					$_error['error'] =$_php_error ?'Unknown Error' :get_class($Exception);
					$_error['description'] ='';
				}
			} else{
				$_error['level'] =5;
				$_error['error'] =$_error['code'];
				$_error['description'] ='';
			}
			/*
			if ($_php_error){
				$_error['description'] =self::L10n('errors.' .E_RECOVERABLE_ERROR);
				$_error['description'] =is_array($_error['description']) ?$_error['description'][2] :'';
				if (!headers_sent()){
					header('HTTP/1.1 500 Internal Server Error');
				}
			} else{
				if (method_exists($Exception, 'sendHeaders') and !headers_sent()){
					$Exception->sendHeaders();
				}
			}
			*/
			if (!hRequest::hasFirePHP()){
				if (___DEBUG && !empty($_error['line'])) $_error['trace'] =$_php_error ?array_slice(debug_backtrace(), 1) :$Exception->getTrace();
				$f =self::Path('views/error.php');
				if ($f){
					extract($_error, EXTR_REFS);
					include $f;
				}
			} else{
				Debug::Error();
			}
			error_reporting(0);
			exit();
		} catch (Exception $e){
			die(__TEST ?'Fatal Error: ' . $e->getMessage() . ' File: ' . $e->getFile() . ' Line: ' . $e->getLine() :'Fatal Error');
		}
	}
	public static function addEvent($Name ='', $Callback =null, $Replace =false){
		$_events =&self::$Caches['event'];
		$_callbacks =&$_events['callback'];
		if (is_callable($Callback, true)){
			if (!isset($_callbacks[$Name]) || $Replace) $_callbacks[$Name] =array();
			$_callbacks[$Name][] =$Callback;
			return true;
		}
		return false;
	}
	public static function removeEvent($Name ='', $Callback =null){
		$_events =&self::$Caches['event'];
		$_callbacks =&$_events['callback'];
		if (empty($Callback)){
			$_callbacks[$Name] =array();
			return true;
		} elseif (isset($_callbacks[$Name])){
			foreach ($_callbacks[$Name] as $_name =>$_callback)
				if ($Callback === $_callback){
					unset($_callbacks[$Name][$_name]);
					return true;
				}
		}
		return false;
	}
	public static function callEvent($Name ="", $Again =false){
		$_events =&self::$Caches['event'];
		$_callbacks =&$_events['callback'];
		$_num =&$_events['run'][$Name];
		if ($Again === true || (int)$_num == 0){
			$_callbacks =&$_events['callback'][$Name];
			if (isset($_callbacks) && !empty($_callbacks)){
				foreach ($_callbacks as $_callback){
					if (is_callable($_callback)) call_user_func($_callback);
				}
			}
			$_num +=1;
		}
		return (int)$_num;
	}
	/*
	public static function Event($Name ='', $Callback =null, $Remove =false){
		$_events =&self::$Caches['event'];
		$_callbacks =&$_events['callback'];
		if ($Remove){
			if (empty($Callback))
				$_callbacks[$Name] =array();
			elseif (isset($_callbacks[$Name]))
				foreach ($_callbacks[$Name] as $_name =>$_callback)
					if ($Callback === $_callback) unset($_callbacks[$Name][$_name]);
			return;
		//} elseif (is_callable($Callback)){
		} elseif (is_callable($Callback)){
			if (!isset($_callbacks[$Name]))
				$_callbacks[$Name] =array();
			else
				return !in_array($Callback, $_callbacks[$Name]);
			$_callbacks[$Name][] =$Callback;
			return true;
		}
		$_num =&$_events['run'][$Name];
		if ($Callback === true || (int)$_num == 0){
			$_callbacks =&$_events['callback'][$Name];
			if (isset($_callbacks) && !empty($_callbacks)){
				foreach ($_callbacks as $_callback)
					call_user_func($_callback);
			}
			$_num +=1;
			
		//var_dump($Name, $_num);
		}
		return (int)$_num;
		//return empty($_callbacks[$Name]) ?array() :$_callbacks[$Name];
	}*/
	/**
	 * 对程序过程进行统计
	 * 
	 * @param string $Name 统计名
	 * @param boolean $IsEnd 是否结束该统计
	 */
	public static function Benchmark($Name ='', $IsEnd =null){
		if (!___DEBUG) return;
		$_benchmark =&self::$Caches['benchmark'];
		if ($IsEnd === true){
			$Name =implode('|', $_benchmark['level']);
			if (isset($_benchmark['marks'][$Name]) && !isset($_benchmark['marks'][$Name]['end'])){
				$_benchmark['marks'][$Name]['time_end'] =microtime(true) - $_benchmark['time'];
					//$_benchmark['marks'][$Name]['memory_end'] =memory_get_usage() - $_benchmark['memory'];
			}
			return array_pop($_benchmark['level']);
		}
		if (!isset($_benchmark['time'])){
			$_benchmark['level'][] ='_benchmark_total';
			$_benchmark['marks']['_benchmark_total'] =array(
				'time' =>0, 
				'memory' =>0);
			$_benchmark['time'] =___N; // microtime(true);
		//$_benchmark['memory'] =___M; // microtime(true);
		}
		$_benchmark['level'][] =$Name;
		$Name =implode('|', $_benchmark['level']);
		if (!isset($_benchmark['marks'][$Name])){
			$_benchmark['marks'][$Name] =array(
				'time' =>microtime(true) - $_benchmark['time']); //'memory' =>memory_get_usage() - $_benchmark['memory']
		}
	}
	/**
	 * 返回统计结果 如没有设定统计名即返回全部
	 * 
	 * @param string $Name 统计名
	 */
	static public function Benchmarks($Name =null){
		if (!___DEBUG) return;
		$_benchmark =&self::$Caches['benchmark'];
		$_marks =&$_benchmark['marks'];
		$r =array();
		$_ks =array_keys($_marks);
		krsort($_ks);
		foreach ($_ks as $_k =>$n){
			if (!isset($_marks[$n]['time_end'])){
				$_marks[$n]['time_end'] =microtime(true) - $_benchmark['time'];
					//$_marks[$n]['memory_end'] =memory_get_usage(true) - $_benchmark['memory'];
			}
			$r[$_k] =$_marks[$n];
		}
		ksort($r);
		$_r =array();
		foreach (array_keys($_marks) as $_k =>$n)
			$_r[$n] =$r[$_k];
		return $_r;
	}
}

