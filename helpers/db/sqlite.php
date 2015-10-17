<?php

if (class_exists("SQLite3")){
		class hdb_sqlite_base{
			var $extension = "SQLite3", $server_info, $affected_rows, $errno, $error, $_link, $connect_error='';


			function connect2($Config){
				return $this->connect($Config['file']);
			}
			function connect($filename, $flags = null, $encryption_key = null){
				$this->_link = new SQLite3($filename);
				$version = $this->_link->version();
				$this->server_info = $version["versionString"];
			}

			function query($query) {
				$result = @$this->_link->query($query);
				$this->error = "";
				$this->affected_rows = $this->_link->changes();
				$this->insert_id =$this->_link->lastInsertRowID();
				if (!$result) {
					$this->errno = $this->_link->lastErrorCode();
					$this->error = $this->_link->lastErrorMsg();
					return false;
				} elseif ($result->numColumns()){
					return new hdb_sqlite_result_1($result);
				}
				return true;
			}

			function quote($string) {
				return "'" . $this->_link->escapeString($string) . "'";
				//return (is_utf8($string)? "'" . $this->_link->escapeString($string) . "'": "x'" . reset(unpack('H*', $string)) . "'");
			}

			function store_result() {
				return $this->_result;
			}

			function result($query, $field = 0) {
				$result = $this->query($query);
				if (!is_object($result)) {
					return false;
				}
				$row = $result->_result->fetchArray();
				return $row[$field];
			}
		}

		class hdb_sqlite_result_1 {
			var $_result, $_offset = 0, $num_rows;

			function __construct($result) {
				$this->_result = $result;
				$this->num_rows =$this->_result->numColumns();
			}

			function fetch_all(){
				$this->_result->reset();
				$r =array();
				while ($_r =$this->fetch_assoc()){
					$r[] =$_r;
				}
				return $r;
			}

			function fetch_assoc() {
				return $this->_result->fetchArray(SQLITE3_ASSOC);
			}

			function fetch_row() {
				return $this->_result->fetchArray(SQLITE3_NUM);
			}

			function fetch_field() {
				$column = $this->_offset++;
				$type = $this->_result->columnType($column);
				return (object) array(
					"name" => $this->_result->columnName($column),
					"type" => $type,
					"charsetnr" => ($type == SQLITE3_BLOB ? 63 : 0), // 63 - binary
				);
			}

			function __desctruct() {
				return $this->_result->finalize();
			}
		}
/*
	} else {

		class hdb_sqlite_base {
			var $extension = "SQLite", $server_info, $affected_rows, $error, $_link;

			public function __construct($Config){
				$filename=$Config['file'];
				$this->server_info = sqlite_libversion();
				$this->_link = new SQLiteDatabase($filename);
			}

			function query($query, $unbuffered = false) {
				$method = ($unbuffered ? "unbufferedQuery" : "query");
				$result = @$this->_link->$method($query, SQLITE_BOTH, $error);
				$this->error = "";
				if (!$result) {
					$this->error = $error;
					return false;
				} elseif ($result === true) {
					$this->affected_rows = $this->changes();
					return true;
				}
				return new hdb_sqlite_result_2($result);
			}

			function quote($string) {
				return "'" . sqlite_escape_string($string) . "'";
			}

			function store_result() {
				return $this->_result;
			}

			function result($query, $field = 0) {
				$result = $this->query($query);
				if (!is_object($result)) {
					return false;
				}
				$row = $result->_result->fetch();
				return $row[$field];
			}
		}

		class hdb_sqlite_result_2 {
			var $_result, $_offset = 0, $num_rows;

			function __construct($result) {
				$this->_result = $result;
				if (method_exists($result, 'numRows')) { // not available in unbuffered query
					$this->num_rows = $result->numRows();
				}
			}

			function fetch_assoc() {
				$row = $this->_result->fetch(SQLITE_ASSOC);
				if (!$row) {
					return false;
				}
				$return = array();
				foreach ($row as $key => $val) {
					$return[($key[0] == '"' ? idf_unescape($key) : $key)] = $val;
				}
				return $return;
			}

			function fetch_row() {
				return $this->_result->fetch(SQLITE_NUM);
			}

			function fetch_field() {
				$name = $this->_result->fieldName($this->_offset++);
				$pattern = '(\\[.*]|"(?:[^"]|"")*"|(.+))';
				if (preg_match("~^($pattern\\.)?$pattern\$~", $name, $match)) {
					$table = ($match[3] != "" ? $match[3] : idf_unescape($match[2]));
					$name = ($match[5] != "" ? $match[5] : idf_unescape($match[4]));
				}
				return (object) array(
					"name" => $name,
					"orgname" => $name,
					"orgtable" => $table,
				);
			}

		}

	}
*/
} elseif (extension_loaded("pdo_sqlite")) {
	require 'pdo.php';
	class hdb_sqlite_base extends hdb_pdo {
		var $extension = "PDO_SQLite";

		public function __construct($Config){
			$filename=$Config['file'];
			$this->dsn(DRIVER . ":$filename", "", "");
		}

		function connect2($Config){
			//$this->config =$Config;
			return $this->select_db($Config['file']);
		}
	}

}

if (class_exists("hdb_sqlite_base")) {
	class hdb_sqlite extends hdb_sqlite_base {

		function select_db($filename=null) {
			//if(is_null($filename)) $filename =$this->config['file'];
			if (is_readable($filename) && $this->query("ATTACH " . $filename . " AS a")) { // is_readable - SQLite 3
				$this->hdb_sqlite_base($filename);
				return true;
			}
			return false;
		}

		function multi_query($query) {
			return $this->_result = $this->query($query);
		}

		function next_result() {
			return false;
		}
	}
}
