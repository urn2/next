<?php

class hDB_SQLite implements iDB{
	protected $conn =null;
	protected $SQLs =array();
	protected $res =array();
	protected $config =array();

	public function __construct($Config){
		$this->config =$Config;
		//$this->connect($Config);
	}

	public function __destruct(){
		$this->close();
	}

	public function close(){//if (is_resource($this->res)) mysql_free_result($this->res);
//if (is_resource($this->conn)) mysql_close($this->conn);
	}

	public function connect($Config){
		$_file =$Config['file'];
		$this->conn =sqlite_open($_file) or trigger_error(Next::Language('core.db_no_conn'));
	}
	
	private function removeToken($SQL, $List =array('`')) {
		$_sql =str_replace($List, "", $SQL);
		$_sql =str_replace(array('create', 'check'), array("'create'", "'check'"), $_sql);
		return $_sql;
	}
	private function _array($array){
		if (is_array($array))
		foreach ($array as $key => $value){
			$_key =str_replace(array("'"), '', $key);
			if (strpos($_key, '.') !==false){
				$_key =explode('.', $_key);
				$_key =array_pop($_key);
			}
			unset($array[$key]);
			$array[$_key] =$value;
		}
		return $array;
	}

	/**
	 * 
	 * @param unknown_type $SQL
	 * @param unknown_type $Unbuffered
	 * @param unknown_type $FreeNow
	 * @return hDB_MySQL
	 */
	public function exec($SQL, $Unbuffered =null, $FreeNow =null){
		if (!is_resource($this->conn)) $this->connect($this->config);
		$SQL =$this->removeToken($SQL);
		$this->SQLs[] =$SQL;
		//if (is_resource($this->res)) mysql_free_result($this->res);
		$this->res =!$Unbuffered ?@sqlite_query($SQL, $this->conn) :@sqlite_unbuffered_query($SQL, $this->conn);
		return $this;
	}

	/**
	 * 
	 * @param unknown_type $Callback
	 * @param unknown_type $Type
	 * @return array
	 */
	public function fetch($Callback =null, $Type =MYSQL_ASSOC){
		if (!is_resource($this->conn)) return false;
		if (!is_resource($this->res)) return false;
		$r =array();
		if (is_null($Callback) ||!is_callable($Callback)){
			while ($row =sqlite_fetch_array($this->res, $Type))
				$r[] =$this->_array($row);
		} else{
			while ($row =sqlite_fetch_array($this->res, $Type)){
				$_r =$Callback($this->_array($row));
				if (!is_null($_r)) $r[] =$_r;
			}
		}
		return $r;
	}

	/**
	 * 
	 * @param function $Callback
	 * @param const $Type
	 * @return array
	 */
	public function fetchOne($Callback =null, $Type =MYSQL_ASSOC){
		if (!is_resource($this->conn)) return false;
		if (!is_resource($this->res)) return false;
		$row =sqlite_fetch_array($this->res, $Type);
		$row =$this->_array($row);
		if (is_null($Callback)) 
			return $row;
		elseif (is_callable($Callback))
			return $Callback($row);
		else 
			return $row[$Callback];
	}

	/**
	 * @return integer
	 */
	public function affectedRows(){
		//if (!is_resource($this->conn)) return false;
		return sqlite_changes($this->conn);
	}

	/**
	 * @return integer
	 */
	public function getRows(){
		//if (!is_resource($this->res)) return false;
		return sqlite_num_rows($this->res);
	}

	/**
	 * @return integer
	 */
	public function lastId(){
		//if (!is_resource($this->res)) return false;
		return sqlite_last_insert_rowid($this->conn);
	}
}
