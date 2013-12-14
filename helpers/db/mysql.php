<?php
// MySQLi supports everything, MySQL doesn't support multiple result sets, PDO_MySQL doesn't support orgtable
if (extension_loaded("mysqli")) {
	class hdb_mysql extends MySQLi {
		var $extension = "MySQLi";
		public function __construct(){
			parent::init();
		}

		function connect($Config){
			$server=$Config['host'];
			$username=$Config['user'];
			$password=$Config['password'];
			mysqli_report(MYSQLI_REPORT_OFF); // stays between requests, not required since PHP 5.3.4
			//list($host, $port)
			$r = explode(":", $server, 2); // part after : is used for port or socket
			$host =$r[0];
			$port =(isset($r[1])) ?$r[1] :null;
			$return = @$this->real_connect(
				($server != "" ? $host : ini_get("mysqli.default_host")),
				($server . $username != "" ? $username : ini_get("mysqli.default_user")),
				($server . $username . $password != "" ? $password : ini_get("mysqli.default_pw")),
				null,
				(is_numeric($port) ? $port : ini_get("mysqli.default_port")),
				(!is_numeric($port) ? $port : null)
			);
			if ($return) {
				if (method_exists($this, 'set_charset')) {
					$this->set_charset("utf8");
				} else {
					$this->query("SET NAMES utf8");
				}
			}
			return $return;
		}

		function result($query, $field = 0) {
			$result = $this->query($query);
			if (!$result) {
				return false;
			}
			$row = $result->fetch_array();
			return $row[$field];
		}

		function quote($string) {
			return "'" . $this->escape_string($string) . "'";
		}
	}

} elseif (extension_loaded("mysql") && !(ini_get("sql.safe_mode") && extension_loaded("pdo_mysql"))) {
	class hdb_mysql {
		var
		$extension = "MySQL", ///< @var string extension name
		$server_info, ///< @var string server version
		$affected_rows, ///< @var int number of affected rows
		$errno, ///< @var int last error code
		$error, ///< @var string last error message
		$_link, $_result ///< @access private
		;

		/** Connect to server
		 * @param string
		 * @param string
		 * @param string
		 * @return bool
		 */
		function connect($Config){
			$server=$Config['host'];
			$username=$Config['user'];
			$password=$Config['password'];
			$this->_link = @mysql_connect(
				($server != "" ? $server : ini_get("mysql.default_host")),
				("$server$username" != "" ? $username : ini_get("mysql.default_user")),
				("$server$username$password" != "" ? $password : ini_get("mysql.default_password")),
				true,
				131072 // CLIENT_MULTI_RESULTS for CALL
			);
			if ($this->_link) {
				$this->server_info = mysql_get_server_info($this->_link);
				if (function_exists('mysql_set_charset')) {
					mysql_set_charset("utf8", $this->_link);
				} else {
					$this->query("SET NAMES utf8");
				}
			} else {
				$this->error = mysql_error();
			}
			return (bool) $this->_link;
		}

		/** Quote string to use in SQL
		 * @param string
		 * @return string escaped string enclosed in '
		 */
		function quote($string) {
			return "'" . mysql_real_escape_string($string, $this->_link) . "'";
		}

		/** Select database
		 * @param string
		 * @return bool
		 */
		function select_db($database) {
			return mysql_select_db($database, $this->_link);
		}

		/** Send query
		 * @param string
		 * @param bool
		 * @return mixed bool or Min_Result
		 */
		function query($query, $unbuffered = false) {
			$result = @($unbuffered ? mysql_unbuffered_query($query, $this->_link) : mysql_query($query, $this->_link)); // @ - mute mysql.trace_mode
			$this->error = "";
			if (!$result) {
				$this->errno = mysql_errno($this->_link);
				$this->error = mysql_error($this->_link);
				return false;
			}
			if ($result === true) {
				$this->affected_rows = mysql_affected_rows($this->_link);
				$this->info = mysql_info($this->_link);
				return true;
			}
			return new hdb_mysql_result($result);
		}

		/** Send query with more resultsets
		 * @param string
		 * @return bool
		 */
		function multi_query($query) {
			return $this->_result = $this->query($query);
		}

		/** Get current resultset
		 * @return Min_Result
		 */
		function store_result() {
			return $this->_result;
		}

		/** Fetch next resultset
		 * @return bool
		 */
		function next_result() {
			// MySQL extension doesn't support multiple results
			return false;
		}

		/** Get single field from result
		 * @param string
		 * @param int
		 * @return string
		 */
		function result($query, $field = 0) {
			$result = $this->query($query);
			if (!$result || !$result->num_rows) {
				return false;
			}
			return mysql_result($result->_result, 0, $field);
		}
	}

	class hdb_mysql_result {
		var
		$num_rows, ///< @var int number of rows in the result
		$_result, $_offset = 0 ///< @access private
		;

		/** Constructor
		 * @param resource
		 */
		function __construct($result) {
			$this->_result = $result;
			$this->num_rows = mysql_num_rows($result);
		}

		/** Fetch next row as associative array
		 * @return array
		 */
		function fetch_assoc() {
			return mysql_fetch_assoc($this->_result);
		}

		/** Fetch next row as numbered array
		 * @return array
		 */
		function fetch_row() {
			return mysql_fetch_row($this->_result);
		}

		/** Fetch next field
		 * @return object properties: name, type, orgtable, orgname, charsetnr
		 */
		function fetch_field() {
			$return = mysql_fetch_field($this->_result, $this->_offset++); // offset required under certain conditions
			$return->orgtable = $return->table;
			$return->orgname = $return->name;
			$return->charsetnr = ($return->blob ? 63 : 0);
			return $return;
		}

		/** Free result set
		 */
		function __destruct() {
			mysql_free_result($this->_result); //! not called in PHP 4 which is a problem with mysql.trace_mode
		}
	}

} elseif (extension_loaded("pdo_mysql")) {
	require 'pdo.php';
	class hdb_mysql extends hdb_pdo {
		var $extension = "PDO_MySQL";

		function connect($Config){
			$server=$Config['host'];
			$username=$Config['user'];
			$password=$Config['password'];
			$this->dsn("mysql:host=" . str_replace(":", ";unix_socket=", preg_replace('~:(\\d)~', ';port=\\1', $server)), $username, $password);
			$this->query("SET NAMES utf8"); // charset in DSN is ignored
			return true;
		}

		function select_db($database) {
			// database selection is separated from the connection so dbname in DSN can't be used
			return $this->query("USE " . idf_escape($database));
		}

		function query($query, $unbuffered = false) {
			$this->setAttribute(1000, !$unbuffered); // 1000 - PDO::MYSQL_ATTR_USE_BUFFERED_QUERY
			return parent::query($query, $unbuffered);
		}
	}

}
