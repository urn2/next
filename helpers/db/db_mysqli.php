<?php

class hDB_MySQLi{
	protected $conn =null;
	protected $SQLs =array();
	protected $res =array();
	protected $config =array();
	
	protected $mysqli =null;
	protected $result =null;
	
	public function __construct($Config){
		$this->config =$Config;
	}
	public function __destruct(){
		$this->close();
	}
	public function close(){
		//$mysqli =$this->mysqli;
		//$mysqli->close();
		//if (is_resource($this->res)) mysql_free_result($this->res);
	//if (is_resource($this->conn)) mysql_close($this->conn);
	}
	public function connect($Config){
		Next::Benchmark('_db_mysqli_connect');
		$_host =!isset($Config['port']) ?$Config['host'] :$Config['host'] . ':' . $Config['port'];
		$mysqli =new mysqli($_host, $Config['user'], $Config['password'], $Config['dbname']);
		if ($mysqli->connect_error)	trigger_error(Next::Language('core.db_no_conn'));
		$mysqli->query("SET NAMES UTF8");
		$this->mysqli =$mysqli;
		Next::Benchmark('_db_mysqli_connect', true);
	}
	/**
	 *
	 * @param unknown_type $SQL
	 * @param unknown_type $Unbuffered
	 * @param unknown_type $FreeNow
	 * @return hDB_MySQL
	 */
	public function exec($SQL, $Unbuffered =null, $FreeNow =null){
		Next::Benchmark('_db_mysqli_exec');
		if (is_null($this->mysqli)) $this->connect($this->config);
		$this->SQLs[] =$SQL;
		if (!is_null($this->result)) $this->result->close();
		$this->result =$this->mysqli->query($SQL);
		
		
		//if (is_resource($this->res) && $FreeNow) mysql_free_result($r);
		Next::Benchmark('_db_mysqli_exec', true);
		return $this;
	}
	/**
	 *
	 * @param unknown_type $Callback
	 * @param unknown_type $Type
	 * @return array
	 */
	public function fetch($Callback =null, $Type =MYSQLI_ASSOC){
		if (is_null($this->result)) return null;
		if (is_null($Callback)){
			return $this->result->fetch_all($Type);
		} else {
			return $Callback($this->result->fetch_all($Type));
		}
	}
	/**
	 *
	 * @param function $Callback
	 * @param const $Type
	 * @return array
	 */
	public function fetchOne($Callback =null, $Type =MYSQL_ASSOC){
		if (is_null($this->result)) return null;
		$row =$this->result->fetch_array($Type);
		if (is_null($Callback)){
			return $row;
		} elseif (is_callable($Callback)) {
			return $Callback($row);
		} else
			return $row[$Callback];
	}
	/**
	 * @return integer
	 */
	public function affectedRows(){
		//if (!is_resource($this->conn)) return false;
		return $this->mysqli->affected_rows;
	}
	/**
	 * @return integer
	 */
	public function getRows(){
		//if (!is_resource($this->res)) return false;
		return $this->result->num_rows;
	}
	/**
	 * @return integer
	 */
	public function lastId(){
		//if (!is_resource($this->res)) return false;
		return $this->mysqli->insert_id;
	}
	/**
	 * @return boolean
	 * Enter description here ...
	 */
	public function status() {
		return $this->mysqli->errno;
	}
}
