<?php

class hCache_Memcache{

	public $prefix ='next.';

	private static $cache =null;

	private $config =array(
		'host' =>array('localhost', 11211), 
		'compressed' =>false, 
		'expire' =>2592000); //60*60*24*30

	
	public function __construct($ID_Prefix ='next'){
		$this->prefix =$ID_Prefix . '.';
		if (!is_resource(self::$cache)) self::$cache =memcache_connect($this->config['host'][0], $this->config['host'][1]);
	}

	public function set($ID, $Data =null, $Expire =null, $Compressed =null){
		if (is_null($Expire)) $Expire =$this->config['expire'];
		if (is_null($Compressed)) $Compressed =$this->config['compressed'];
		return memcache_set(self::$cache, $this->prefix . $ID, $Data, $Compressed, $Expire);
	}

	public function get($ID, $Data =null){
		$r =memcache_get(self::$cache, $this->prefix . $ID);
		return empty($r) ?$Data :$r;
	}

	public function delete($ID){
		return memcache_delete(self::$cache, $this->prefix . $ID);
	}

	public function details(){
		
		$list =array();
		$allSlabs =memcache_get_extended_stats(self::$cache, 'slabs');
		foreach ($allSlabs as $server =>$slabs){
			foreach ($slabs as $slabId =>$slabMeta){
				$cdump =memcache_get_extended_stats(self::$cache, 'cachedump', (int)$slabId);
				$cdump =$cdump[$server];
				if (is_array($cdump) && count($cdump) > 0) foreach ($cdump as $keys =>$arrVal)
					$list[$keys] =memcache_get(self::$cache, $keys);
			}
		}
		Next::Dump($list);
		//var_dump($list);
	}
}