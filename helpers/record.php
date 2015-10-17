<?php

/**
 *
 * 1 need cache db table
 * 2 expend table class
 * 3 need extends from table class
 * 4 alone orm class to use
 * 5 from all to sql
 *
 *
 * !! records or record ??? like jQuery
 * need 2 cache
 *
 *
 * $user =hRecord::f('user');
 *
 * $user['name']='vea';
 * $user['password']='123456';
 * $user['nickname']='维';
 * or
 * $user->name ='vea';
 * $user->password ='123456';
 * $user->nickname='维';

 * $user->save();
 * $user->delete();
 * $user->at(1);
 * $user->find(1);//new user ??
 *
 * ar || orm?
 * Enter description here ...
 * @author Vea
 * @since 2014.5.17
 */

class hRecord implements arrayaccess{
	private $_id =null;
	
	private $table =null;
	private $primary =null;
	
	private $records=array();
	private $_records =array();
	
	private $_config =null;
	static public $Log =array();
	static private $_db =array();
	static private $_structure =array();
	
	public $lastResult =null;
	
	public function at($var){
		$this->_records =$this->records =$this->db()->filter($var)->readOne();
		if (isset($this->records[$this->primary])) $this->_id =$this->records[$this->primary];
		return $this;
	}
	public function find($Conditions){
		$r =$this->db()->filter($Conditions)->readOne();
		if ($r) return self::f($this->table, $this->_config)->set($r);
		else return false;
	}
	public function set($key, $var=null){
		if (is_null($var) && is_array($key)) $this->records =array_merge($this->records, $key);
		else $this->records[$key] =$var;
		return $this;
	}
	public function delete(){
		if ($this->_id !==null){
			$this->db()->filter($this->_id)->delete();
		}
		return $this;
	}
	/**
	 *
	 * update[edit] or new
	 * allow from params
	 */
	public function save(){
		if ($this->_id !==null){
			$_2save =$this->records;
			foreach ($this->_records as $_col=>$_val) {
				if (isset($_2save[$_col]) && $_2save[$_col] ==$_val) unset($_2save[$_col]);
			}
			if (empty($_2save)) return $this;
			$_2save[$this->primary] =$this->_id;
			$r =$this->db()->setFields($_2save)->update();
			if ($r) $this->_records =array_merge($this->_records, $_2save);
			$this->lastResult =$r;
		} else{
			$_r =isset($this->records[$this->primary]);
			$r =$this->db()->create($this->records, $_r);
			if ($r) $this->at($r);
			$this->lastResult =$r;
		}
		return $this;
	}
	/**
	 *
	 * Enter description here ...
	 * @param unknown $Table
	 * @param string $Config
	 * @return hRecord
	 */
	static public function f($Table, $Config ='default'){
		return self::factory($Table, $Config);
	}
	/**
	 *
	 * Enter description here ...
	 * @param unknown $Table
	 * @param string $Config
	 * @return hRecord
	 */
	static public function factory($Table, $Config ='default'){
		$self =__CLASS__;
		return new $self($Table, $Config);
	}
	/**
	 *
	 * Enter description here ...
	 * @param unknown $Table
	 * @param string $Config
	 * @return hRecord
	 */
	public function __construct($Table, $Config ='default'){
		$this->table =$Table;
		$this->_config =$Config;
		if (!isset(self::$_db[$this->_config])) self::$_db[$this->_config] =hSQL::factory($this->table, null, $Config); //hDB::factory($Config);
		$this->_structure();
		return $this;
	}
	/**
	 *
	 * Enter description here ...
	 * @return hSQL
	 */
	private function db(){
		return self::$_db[$this->_config];
	}
	private function _structure(){
		if (isset(self::$_structure[$this->table])) return self::$_structure[$this->table];
		
		$_s =array('cols'=>array());
		//$cols =$this->_db()->read('describe '.$this->table);
		$cols =$this->db()->read('show columns from '.$this->table);
		
		$_set =array(
			'tinyint'=>array(127, -128),
			'smallint'=>array(32767, -32768),
			'mediumint'=>array(8388607, -8388608),
			'bigint'=>array(9223372036854775807, -9223372036854775808),
			'int'=>array(2147483647, -2147483648),
			'utinyint'=>array(255, 0),
			'usmallint'=>array(65535, 0),
			'umediumint'=>array(16777215, 0),
			'ubigint'=>array(18446744073709551615, 0),
			'uint'=>array(4294967295, 0),
			'tinytext'=>255,
			'mediumtext'=>16777215,
			'longtext'=>4294967295,
			'text'=>65535,
			'char'=>255,
			'varchar'=>255,
		);
		
		foreach ($cols as $col){
			if ($col['Key'] =='PRI') $_s['pri'] =$col['Field'];
			if ($col['Extra']){
				$col['can_pass'] =true;
			}
			$_t =strtok($col['Type'], '(');
			$_n =strtok(substr($col['Type'], strlen($_t)+1), ')');
			$_e =substr($col['Type'], strlen($_t)+1+strlen($_n)+2);
			//var_dump($_t, $_n, $_e);
			$_u =(strpos($_e, 'unsigned') !==false);
			switch ($_t){
				case 'tinyint':
				case 'smallint':
				case 'mediumint':
				case 'bigint':
				case 'int':
					$col['t'] ='int';
					$col['max'] =$_set[$_u ?'u'.$_t :$_t][0];
					$col['min'] =$_set[$_u ?'u'.$_t :$_t][1];
					break;
				case 'decimal':
				case 'float':
				case 'double':
					break;
				case 'date':
				case 'datetime':
				case 'timestamp':
				case 'time':
				case 'year':
					$col['t'] ='time';
					break;
				case 'tinytext':
				case 'mediumtext':
				case 'longtext':
				case 'text':
					$col['len'] =$_set[$_t];
				case 'char':
				case 'varchar':
					$col['t'] ='str';
					if ((int)$_n >0) $col['len'] =(int)$_n;
					if ($col['Null'] =='YES' || $col['Default'] !=null) $col['can_pass'] =true;
					break;
			}
			$_s['cols'][$col['Field']] =$col;
		}
		if (isset($_s['pri'])) $this->db()->primary =$this->primary =$_s['pri'];
		self::$_structure[$this->table] =$_s;
		
		return $_s;
	}
	
	//魔术方法 对象
	public function __set($name, $value)
    {
        $this->records[$name] = $value;
    }
    public function __get($name)
    {
        if (array_key_exists($name, $this->records)) {
            return $this->records[$name];
        }
        return null;
    }
    public function __isset($name)
    {
        return isset($this->data[$name]);
    }
    public function __unset($name)
    {
        unset($this->data[$name]);
    }
	//魔术方法 数组
	public function offsetSet($offset, $value) {
		if (is_null($offset)) $this->records[] = $value;
			else $this->records[$offset] = $value;
	}
	public function offsetExists($offset) {
		return isset($this->records[$offset]);
	}
	public function offsetUnset($offset) {
		unset($this->records[$offset]);
	}
	public function offsetGet($offset) {
		return isset($this->records[$offset]) ? $this->records[$offset] : null;
	}
	
}

