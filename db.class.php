<?php
/*
Copyright (C) 2011 by Creative5 - Samuel Ronce

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/

/**
 * @class DB
 * @version Beta 1.0
 * @constructor
 * @param {String} user Username
 * @param {String} pass Password
 * @param {String} db Database name
 * @param {String} (optional) interface Interface name : "pgsql", "sqlite", "firebird", "informix", "oracle" or "OCI", " dblib", "ibm", "mysql". See http://www.php.net/manual/en/pdo.drivers.php. "mysql" by default
 * @param {String} (optional) host Hostname. "localhost" by default
 * @example
	<pre>
		$db = new DB("my_username", "my_password", "db_name");
		$data = $db	->select("my_table")
					->where(array(
						"id"	=> 	 1
					))
					->fetch();
		print_r($data);
	</pre>
 */
class DB extends PDO {

	private $user, $pass, $db, $host;
	
	public function __construct($user, $pass, $db, $interface = 'mysql', $host = 'localhost') {
		try {
			// No tested
			switch ($interface) {
				case 'pgsql':
					$str = 'pgsql:host='.$host.';dbname='. $db;
				break;
				case 'sqlite':
					$str = 'sqlite:'. $db;
				break;	
				case 'firebird':
					$str = 'firebird:dbname='.$host.':'. $db . '", "SYSDBA", "masterkey';
				break;	
				case 'informix':
					$str = 'informix:DSN='. $db;
				break;	
				case 'oracle':
				case 'OCI':
					$str = 'OCI:dbname='. $db . ';charset=UTF-8';
				break;	
				case 'dblib':
					$str = 'dblib:host='.$host.':10060;dbname='. $db;
				break;	
				case 'ibm':
					$str = 'ibm:DRIVER={IBM DB2 ODBC DRIVER};DATABASE='. $db . '; HOSTNAME='.$host.';PORT=56789;PROTOCOL=TCPIP;';
				break;	
			// --
				default:
					$str = 'mysql:host='.$host.';dbname='. $db;
				break;
			}
			parent::__construct($str, $user, $pass);
			$this->user = $user;
			$this->pass = $pass;
			$this->db = $db;
			$this->host = $host;
		}	 
		catch(Exception $e) {
				throw $e;
		}
	}
	
	/**
	 * Starts a query to select data from database.
	 * @method select
	 * @param {String} table Table name
	 * @param {String} (optional) fields Fields. "*" by default
	 * @return {DB_Select}
	*/	
	public function select($table, $fields = "*") {
		return new DB_Statement($this, $table, 'SELECT', array('fields' => $fields));
	}
	
	/**
	 * Starts a query to insert data in database.
	 * @method insert
	 * @param {String} table Table name
	 * @return {DB_Insert}
	*/	
	public function insert($table) {
		return new DB_Statement($this, $table, 'INSERT');
	}
	
	/**
	 * Starts a query to update data in database.
	 * @method udpate
	 * @param {String} table Table name
	 * @return {DB_Update}
	*/	
	public function update($table) {
		return new DB_Statement($this, $table, 'UPDATE');
	}
	
	/**
	 * Starts a query to delete data in database.
	 * @method delete
	 * @param {String} table Table name
	 * @return {DB_Delete}
	*/	
	public function delete($table) {
		return new DB_Statement($this, $table, 'DELETE');
	}
	
}

/**
 * @class DB_Exec
 * @constructor
 * @param {DB} db Database
 * @param {Array} chain Chain queries
 * @param {String} The request string
 */
class DB_Exec {
	
	protected $sql = "", $db, $chain;
	protected $table;
	
	public function __construct($db, $chain, $sql) {
		$this->chain = isset($chain) ? $chain : array();
		$this->sql = isset($sql) ? $sql : "";
		$this->db = $db;
	}
	
	private function sqlConstruct() {
		ksort($this->chain);
		foreach ($this->chain as $key 	=> 	$value) {
			$key = preg_replace("#[0-9]+\-#", "", $key);
			if (preg_match("#^~#", $key)) {
				$this->sql .= ' ' . $value;
			}
			else {
				$this->sql .= ' ' . $key . ' ' . $value;
			}
		}
		return $this->sql;
	}
	
	private function _exec($fetchmode) {
		if (!isset($fetchmode)) $fetchmode = PDO::FETCH_ASSOC;
		$this->sqlConstruct();
		$result = $this->db->query($this->sql);
		$result->setFetchMode($fetchmode);
		return $result;
	}
	
	/**
	 * Execute a query (INSERT, UPDATE or DELETE)
	 * http://www.php.net/manual/en/pdostatement.fetch.php
	 * @method exec
	 * @return {Boolean}
	*/	
	public function exec() {
		$this->sqlConstruct();
		$b = $this->db->exec($this->sql);
		$ret = $this->db->errorInfo();
		return $ret[0] === "00000";
	}
	
	/**
	 * Fetches a row from a result set associated with a PDOStatement object. (SELECT)
	 * @method fetch
	 * @param fetchmode (optional)  The fetchmode parameter determines how PDO returns the row. Array by default
	 * @return {Array}
	*/	
	public function fetch($fetchmode = null) {
		return $this->_exec($fetchmode)->fetch();
	}
	
	/**
	 * Query result (SELECT)
	 * @method result
	 * @param fetchmode (optional)  The fetchmode parameter determines how PDO returns the row. Array by default
	 * @return {PDOStatement}
	*/	
	public function result($fetchmode = null) {
		return $this->_exec($fetchmode);
	}
	
	/**
	 * Returns an array containing all of the result set rows (SELECT)
	 * http://www.php.net/manual/en/pdostatement.fetchall.php
	 * @method fetchAll
	 * @param fetchmode (optional)  The fetchmode parameter determines how PDO returns the row. Array by default
	 * @return {Array}
	*/	
	public function fetchAll($fetchmode = null) {
		return $this->_exec($fetchmode)->fetchAll();
	}
	
	/**
	 * Returns the SQL query
	 * @method sql
	 * @return {String}
	*/	
	public function sql() {
		$this->sqlConstruct();
		return $this->sql;
	}

}

/**
 * @class DB_Element
 * @extends DB_Exec
 * @constructor
 * @param {DB} db Database
 * @param {Array} chain Chain queries
 * @param {String} The request string
 */
class DB_Element extends DB_Exec {
	
	/**
	 * Priority element in the construction of the query. If the name begins with "~", the name of the element will not be displayed in the query
	 * @static
	 * @property INDEX
	 * @type {Array}
	*/	
	public static $INDEX = array(
		"~COLUMN"			=>		1,
		"SET"				=>		1,
		"VALUES"			=>		2,	
		"WHERE"				=>		2,
		"IN"				=> 		3,
		"BETWEEN"			=>		3,
		"LIKE"				=>		3,
		"REGEXP"			=>		3,
		"~MULTIVALUES"		=>		3,
		"GROUP BY"			=>		4,
		"HAVING"			=>		5,
		"ORDER BY"			=>		6,
		"LIMIT"				=>		7
	);

	public function __construct($db, $chain, $sql) {
		parent::__construct($db, $chain, $sql);
	}

	/**
	 * Applies the WHERE element of query
	 * @method where
	 * @param {Array} params. If the value begins with a comparison sign, the sign "=" sign is replaced by the assigned. See example.
	 * @param {Array|String} options. If the parameter is "secure", the "htmlspecialchars" function is applied to the value. "operator" is "AND" or "OR"
	 * Example : array(
		"secure" 	=> true,
		"operator" 	=> "OR"
	 )
	 * @example
		<pre>
			$db	->select("table")
				->where(array(
					"id" 	=>	1,
					"old"	=>	"<50"
				))
				->fetch();
		</pre>
		is equal to 
		<pre>
			$db	->select("table")
				->where(array(
					"id" 	=>	1
				))
				->where(array(
					"old"	=>	"<50"
				))
				->fetch();
		</pre>
		is equal to 
		<pre>
			$db	->query('SELECT * FROM table WHERE id = "1" AND old < "50"')
				->fetch();
		</pre>
	 * @return {DB_Element}
	*/	
	public function where($params, $options = null) {
		$default = array(
			'secure'	=>		false,
			'operator'	=>		'AND'
		);
		$this->extendOptions($default, $options);
		if (is_array($params)) {
			$str = $this->strConstruct($params, $options['secure'], $options['operator']);
		}
		else {
			$str = $params;
		}
		$this->addElement('WHERE', $str);
		return $this->element();
	}
	

	/**
	 * In. Place only after the method "where"
	 * http://www.w3schools.com/sql/sql_in.asp
	 * @method in
	 * @param {Array} params. Params
	 * @example
		<pre>
			$db	->select("table")
				->where("age")
				->in(array(10, 18, 25))
				->fetchAll();
		</pre>
	 * @return {DB_Element}
	*/	
	public function in($params) {
		$str = '';
		for ($i=0 ; $i < count($params) ; $i++) {
			$str .= '"' . $params[$i] . '", ';
		}
		$str = '(' . preg_replace('#, $#', '', $str) . ')';
		$this->addElement('IN', $str);
		return $this->element();
	}
	
	/**
	 * Between. Place only after the method "where"
	 * http://www.w3schools.com/sql/sql_between.asp
	 * @method between
	 * @param {Interger|String} param1. First value
	 * @param {Integer|String} param2. Second value
	 * @example
		<pre>
			$db	->select("table")
				->where("age")
				->between(18, 25)
				->fetchAll();
		</pre>
	 * @return {DB_Element}
	*/	
	public function between($param1, $param2) {
		$this->addElement('BETWEEN', '"' . $param1 . '" AND "' . $param2 . '"');
		return $this->element();
	}
	
	/**
	 * Like. Place only after the method "where"
	 * http://www.w3schools.com/sql/sql_like.asp
	 * @method like
	 * @param {String} expr. Pattern
	 * @example
		<pre>
			$db	->select("table")
				->where("name")
				->like("Sam%")
				->fetchAll();
		</pre>
	 * @return {DB_Element}
	*/	
	public function like($expr) {
		$this->addElement('LIKE', '"' . $expr . '"');
		return $this->element();
	}
	
	/**
	 * Regexp. Place only after the method "where"
	 * @method regexp
	 * @param {String} expr Regular expression
	 * @example
		<pre>
			$db	->select("table")
				->where("name")
				->regexp("[a-z]+")
				->fetchAll();
		</pre>
	 * @return {DB_Element}
	*/	
	public function regexp($expr) {
		$this->addElement('REGEXP', '"' . $expr . '"');
		return $this->element();
	}
	
	/**
	 * LIMIT is used in SQL to specify the number of records to be returned by the query
	 * @method limit
	 * @param {String} params. Param
	 * @example
		<pre>
			$db->select("table")->limit("2,5");
		</pre>
	 * @return {DB_Element}
	*/	
	public function limit($param) {
		$this->addElement('LIMIT', $param);
		return $this->element();
	}
	
	/**
	 * http://www.w3schools.com/sql/sql_orderby.asp
	 * @method orderBy
	 * @param {String} params. Param
	 * @param {String} (optional) option. Sort in ascending order "ASC" or decreasing "DESC". "ASC" by default
	 * @example
		<pre>
			$db	->select("table")
				->orderBy("time", "DESC")
				->fetchAll();
		</pre>
		or 
		<pre>
			$db	->select("table")
				->orderBy("time", "DESC")
				->orderBy("age")
				->fetchAll();
		</pre>
		or 
		<pre>
			$db	->select("table")
				->orderBy(array(
					"time"	=>	"DESC",
					"age"	=>	"ASC"
				))
				->fetchAll();
		</pre>
	 * @return {DB_Element}
	*/	
	public function orderBy($param, $option = null) {
		$str = "";
		if (is_array($param)) {
			foreach ($param as $key => $value) {
				$str .= $key . ' ' . $value . ', ';
			}
			$str = preg_replace('#, $#', '', $str);
		}
		else {
			$str = $param . (isset($option) ? ' ' . $option : '');
		}
		$this->addElement('ORDER BY', $str);
		return $this->element();
	}
	
	/**
	 * Group By
	 * http://www.w3schools.com/sql/sql_groupby.asp
	 * @method groupBy
	 * @param {String|Array} params List of Columns
	 * @example
		<pre>
			$db	->select("table", "SUM(price)")
				->groupBy(array("column1", "column2"));
				->fetchAll();
		</pre>
		or 
		<pre>
			$db	->select("table", "SUM(price)")
				->groupBy("column1, column2");
				->fetchAll();
		</pre>
		
	 * @return {DB_Element}
	*/	
	public function groupBy($params) {
		$str = $params;
		if (is_array($str)) {
			$str = '';
			for ($i=0 ; $i < count($params) ; $i++) {
				$str .= $params[$i] . ', ';
			}
			$str = preg_replace('#, $#', '', $str);
		}
		$this->addElement('GROUP BY', $str);
		return $this->element();
	}
	
	/**
	 * Having
	 * http://www.w3schools.com/sql/sql_having.asp
	 * @method having
	 * @param {Array} params See "params" of method "where"
	 * @example
		<pre>
			$db	->select("table", "SUM(price)")
				->groupBy("colum");
				->having(array(
					"SUM(price)" => ">1500"
				))
				->fetchAll();
		</pre>	
	 * @return {DB_Element}
	*/	
	public function having($params) {
		$str = $this->strConstruct($params, false);
		$this->addElement('HAVING', $str);
		return $this->element();
	}
	
	/**
	 * Values ​​for the insertion of a data. http://www.w3schools.com/sql/sql_insert.asp
	 * @method values
	 * @param {String|Array} insert. Params
	 * @param {String|Array} (optional) options. 
		 <ul>
			<li>"secure". Apply htmlspecialchars on values</li>
			<li>"merge". Merges the tables of values</li>
		</ul>
		Example 1 :
		<pre>
			...->values(..., "secure")
		</pre>
		is equal to 
			...->values(..., array(
				"secure"	=>	true
			))
		Example 2 :
		<pre>
			...->values(..., array(
				"secure"	=>	true,
				"merge"		=>	true
			))
		</pre>
	 * @example
		<pre>
			$db	->insert("table")
				->values(array(
					"text"		=>	"foo"
				))
				->exec();
		</pre>
		is equal to
		<pre>
			$db	->query("INSERT INTO table ('text') VALUES ('foo')")
				->exec();
		</pre>
	  * @example
		<pre>
			$db	->insert("table")
				->values(array(
					"text"		=>	"foo"
				))
				->values(array(
					"text"		=>	"bar"
				))
				->exec();
		</pre>
		or 
		<pre>
			$db	->insert("table")
				->values(array(
					"text"		=>	"foo"
				))
				->values(array("bar"))
				->exec();
		</pre>
		or 
		<pre>
			$db	->insert("table")
				->values(array(
					"text"		=>	"foo"
				))
				->values("bar")
				->exec();
		</pre>
		is equal to
		<pre>
			$db	->query("INSERT INTO table ('text') VALUES ('foo'), ('bar')")
				->exec();
		</pre>
	* @example
		<pre>
			$db	->insert("table")
				->values(array(
					"text"		=>	"foo"
				))
				->values(array(
					"title"		=>	"bar"
				), "merge")
				->exec();
		</pre>
		is equal to
		<pre>
			$db	->insert("table")
				->values(array(
					"text"		=>	"foo",
					"title"		=>	"bar"
				))
				->exec();
		</pre>
		is equal to
		<pre>
			$db	->query("INSERT INTO table ('text', 'title') VALUES ('foo', 'bar')")
				->exec();
		</pre>
	 * @return {DB_Element}
	*/	
	public function values($insert, $options = null) {
		$default = array(
			'secure'	=>		false,
			'merge'		=>		false
		);
		$this->extendOptions($default, $options);
		$str_key = '';
		$str_value = '';
		$values_exist = isset($this->chain[self::$INDEX['VALUES'] . '-VALUES']);
		if ($values_exist && !self::is_assoc($insert)) {
			for ($i=0 ; $i < count($insert) ; $i++) {
				if(empty($insert[$i]) && $insert[$i] !== 0 && $insert[$i] !== "0") {
					$str_value .= 'NULL, ';
				} else {
					$str_value .= $this->db->quote($options['secure'] ? htmlspecialchars($insert[$i]) : $insert[$i]) . ', ';
				}
			}
		}
		elseif ($values_exist && is_string($insert)) {
				if(empty($str_value) && $str_value !== 0 && $str_value !== "0") {
					$str_value = 'NULL';
				} else {
					$str_value = $this->db->quote($options['secure'] ? htmlspecialchars($insert) : $insert);
				}
		}
		else {
			foreach ($insert as $key => $value) {
				$str_key .= '`' . $key . '`, ';
				if(!isset($value) && $value !== 0 && $value !== "0") {
					$str_value .= 'NULL, ';
				} else {
					$str_value .= $this->db->quote($options['secure'] ? htmlspecialchars($value) : $value) . ', ';
				}
			}
			$str_key = '(' . preg_replace('#, $#', '', $str_key) . ')';
		}
		$str_value = '(' . preg_replace('#, $#', '', $str_value) . ')';
		if ($values_exist && !$options['merge']) { 
			$this->addElement('MULTIVALUES', ',' . $str_value);
		}
		else {
			$this->addElement('COLUMN', $str_key);
			$this->addElement('VALUES', $str_value);
		}
		return $this->element();
	}
	
	// http://www.php.net/manual/fr/function.is-array.php#98305
	public static function is_assoc($array) {
		return (is_array($array) && (count($array)==0 || 0 !== count(array_diff_key($array, array_keys(array_keys($array))) )));
	} 

	
	/**
	 * http://www.w3schools.com/sql/sql_update.asp
	 * @method set
	 * @param {Array} params. See "where" params
	 * @param {String} secure (optional)
	 * @return {DB_Element}
	*/	
	public function set($params, $secure = false) {
		$str = $this->strConstruct($params, $secure, ',');
		$this->addElement('SET', $str);
		return $this->element();
	}
	
	private function extendOptions($default, &$options) {
		if (!isset($options)) {
			$options = $default;
		}
		elseif (is_string($options)) {
			$str = $options;
			$options = $default;
			if (is_bool($options[$str])) {
				$options[$str] = true;
			}
		}
		else {
			foreach ($default as $key => $value) {
				if (!isset($options[$key])) {
					$options[$key] = $value;
				}
			}
		}
		return $options;		
	}
	
	private function addElement($type, $str) {
		if(isset(self::$INDEX[$type])) {
			$index = self::$INDEX[$type];
		} else {
			$index = NULL;
		}
		if (!isset($index)) {
			if (isset(self::$INDEX["~" . $type])) {
				$index = self::$INDEX["~" . $type];
				$el_name = $index . '-~' . $type;
			}
		}
		else {
			$el_name = $index . '-' . $type;
		}
		if(isset($this->chain[$el_name])) {
			$value = $this->chain[$el_name];
		} else {
			$value = NULL;
		}
		if (isset($value)) {
			switch($type) {
				case "WHERE":
					$str = $value . ' AND ' . $str;
				break;
				case "SET":
				case "ORDER BY":
				case "GROUP BY":
					$str = $value . ', ' . $str;
				break;
				case "IN":
				case "VALUES":
				case "COLUMN":
					$regex = "#\((.*?)\)#";
					if (preg_match($regex, $str, $match_v) && preg_match($regex, $value, $match_c)) {
						$str = '(' . $match_c[1] . ',' . $match_v[1] . ')';
					}
				break;
				case "MULTIVALUES":
					$str = $value . $str;
				break;
			}
		}
		$this->chain[$el_name] = $str;
	}
	
	private function element() {
		return new DB_Element($this->db, $this->chain, $this->sql);
	}
	
	private function strConstruct($params, $secure = false, $separator = "AND") {
		$str = '';
		$operation = '=';
		$quote = true;
		foreach ($params as $key => $value) {
			if (preg_match('#^(=|!=|<|>|>=|<=|<>|!<|!>)#', $value, $match)) {
				$operation = $match[1];
				$quote = false;
				$value = str_replace($operation, '', $value);
			}
			if(empty($value) && $value !== 0 && $value !== '0') { 
				$str .= $key . $operation . ' NULL ' . $separator . ' ';
			} else {
				$str .= $key . $operation . ($quote ? $this->db->quote($secure ? htmlspecialchars($value) : $value) : $value) . ' ' . $separator . ' ';
			}
		}
		$str = preg_replace('#' . $separator . ' $#', '', $str);
		return $str;
	}
}

/**
 * @class DB_Statement
 * @extends DB_Element
 * @constructor
 * @param {DB} db Database
 * @param {String} table Table name
 * @param {String} type SELECT, UPDATE, INSERT or DELETE
 * @param {Array} params Params (null by default). Example : array("fields" => "id")
 */
class DB_Statement extends DB_Element {

	public function __construct($db, $table, $type, $params = null) {
		parent::__construct($db, null, null);
		$this->table = $table;
		switch ($type) {
			case 'SELECT':
				$this->sql = 'SELECT ' . $params['fields'] . ' FROM ' . $table;
			break;
			case 'UPDATE':
				$this->sql = 'UPDATE ' . $table;
			break;
			case 'INSERT':
				$this->sql = 'INSERT INTO ' . $table;
			break;
			case 'DELETE':
				$this->sql = 'DELETE FROM ' . $table;
			break;
		}
	}
}
?>