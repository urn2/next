<?php

class hDB_MySQL{
	protected $conn =null;
	protected $SQLs =array();
	protected $res =array();
	protected $config =array();
	public function __construct($Config){
		$this->config =$Config;
	}
	public function __destruct(){
		$this->close();
	}
	public function close(){
		//if (is_resource($this->res)) mysql_free_result($this->res);
	//if (is_resource($this->conn)) mysql_close($this->conn);
	}
	public function connect($Config){
		Next::Benchmark('_db_mysql_connect');
		$_host =!isset($Config['port']) ?$Config['host'] :$Config['host'] . ':' . $Config['port'];
		$this->conn =mysql_connect($_host, $Config['user'], $Config['password']) or trigger_error(Next::Language('core.db_no_conn'));
		mysql_select_db($Config['dbname'], $this->conn) or trigger_error(Next::Language('core.db_no_db'));
		mysql_unbuffered_query("SET NAMES UTF8", $this->conn);
		Next::Benchmark('_db_mysql_connect', true);
	}
	/**
	 * 
	 * @param unknown_type $SQL
	 * @param unknown_type $Unbuffered
	 * @param unknown_type $FreeNow
	 * @return hDB_MySQL
	 */
	public function exec($SQL, $Unbuffered =null, $FreeNow =null){
		Next::Benchmark('_db_mysql_exec');
		if (!is_resource($this->conn)) $this->connect($this->config);
		$this->SQLs[] =$SQL;
		if (is_resource($this->res)) mysql_free_result($this->res);
		$this->res =!$Unbuffered ?mysql_query($SQL, $this->conn) :mysql_unbuffered_query($SQL, $this->conn);
		if (is_resource($this->res) && $FreeNow) mysql_free_result($r);
		Next::Benchmark('_db_mysql_exec', true);
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
		if (is_null($Callback) || !is_callable($Callback)){
			while ($row =mysql_fetch_array($this->res, $Type))
				$r[] =$row;
		} else{
			while ($row =mysql_fetch_array($this->res, $Type)){
				$_r =$Callback($row);
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
		$row =mysql_fetch_array($this->res, $Type);
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
		return mysql_affected_rows();
	}
	/**
	 * @return integer
	 */
	public function getRows(){
		//if (!is_resource($this->res)) return false;
		return mysql_num_rows();
	}
	/**
	 * @return integer
	 */
	public function lastId(){
		//if (!is_resource($this->res)) return false;
		return mysql_insert_id();
	}
	/**
	 * @return boolean
	 * Enter description here ...
	 */
	public function status() {
		return $this->res;
	}
}
