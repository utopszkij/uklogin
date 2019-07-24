<?php
/** mysql database interface 
*
* requed DEFINE: MYSQLHOST, MYSQLUSER, MYSQLPSW, MYSQLDB, MYSQLLOG;
*
* DB class
*    setQuery($sqlStr)
*    getQuery() : sqlStr
*    statement() : bool
*    loadObject() : recordObject
*    loadObjectList() : Array of recordObjects
*    getErrorNum() : numeric
*    getErrorMsg() : string
*    table($tableName, $alias='', $columns='*') : Table
*    filter($tableName, $alias='', $columns='*') : Filter
*    transaction(function);
* Table class
*    where($whereStr or [field, value] or [field, relStr, value]) or Relation  : Table 
*    orWhere($whereStr or [field, value] or [field, relStr, value]) or Relation: Table
*    group([field, field, ...]) : table   
*    having($whereStr or [field, value] or [field, relStr, value]) or Relation : Table 
*    orHaving($whereStr or [field, value] or [field, relStr, value]) or Relation : Table
*    offset($num) : Table
*    limit($num) : Table
*    order([fild, filed,...]) : Table
*    get() : array of RecordsObject
*    first() : recordObject
*    count() : numeric 
*    update(record)
*    insert(record)
*    delete(record)
*    getInsertedId() : numeric
*    getLastUpdate() : numeric - timestamp
*    getErrorNum() : numeric
*    getErrorMsg() : string
* Filer class
*    join($type, $tableName, $alias, $onStr) : Filter;
*    where($whereStr or [field, value] vagy [field, relStr, value]) : Filter 
*    orWhere($whereStr or [field, value] vagy [field, relStr, value]) : Filter
*    group([field, field, ...]) : Filter   
*    having($whereStr or [field, value] vagy [field, relStr, value]) : Filter 
*    orHaving($whereStr or [field, value] vagy [field, relStr, value]) : Filter
*    offset($num) : Filter
*    limit($num): Filter
*    order([fild, filed,...]) : Filter
*    get() : array of RecordsObject
*    first() : recordObject
*    count() : numeric 
*    getErrorNum() : numeric
*    getErrorMsg() : string
*
* global $dbResult array használható UNITTEST -hez
* 
* Licensz: GNU/GPL
* Szerző: Fogler Tibor    tibor.fogler@gmail.com
*/

global $mysqli, $dbResult;
$dbResult = [];
if (MYSQLHOST != '') {
    $mysqli = new mysqli(MYSQLHOST, MYSQLUSER, MYSQLPSW);
    $mysqli->query('CREATE DATABASE IF NOT EXISTS '.MYSQLDB);
    $mysqli->select_db(MYSQLDB);
}
if (!DEFINED('MYSQLLOG')) {
    DEFINE('MYSQLLOG',false);
}

class Relation {
    public $concat = ''; // 'AND' | 'OR' | ''
    public $relations = false; // false | array of Relation
    public $fieldName = '';
    public $rel = ''; // '<' | '<=' | '=' | ">=' | '>' | '<>' | ''
    public $value = '';
    
    /**
     * get sql string
     * @return string
     */
    public function getSQL(): string {
        $result = ' '.$this->concat.' ';
        if ($this->relations) {
            $res = '';
            foreach ($this->relations as $relation) {
                if ($res == '') {
                    $relation->concat = '';
                }
                $res .= $relation->getSQL();
            }
            $result .= '('.$res.')';
        } else {
            if ($this->rel == '') {
                $result .= $this->filedName;
            } else {
                $result .= '`'.$this->fieldName.'` '.$this->rel.' '.DB::quote($this->value);
            }
        }
        return $result;
    }
}

class DB {
   /**
    * php mysqli handler
    * @var object
    */
   protected $mysqli;
   
   /**
    * sql string
    * @var string
    */
   protected $sql;
   
   /**
    * last mysql errorMsg
    * @var string
    */
   protected $errorMsg;
   
   /**
    * last mysql error number
    * @var int
    */
   protected $errorNum;
   
   /**
    * transaction flag
    * @var string
    */
   protected $inTransaction = false;
    
   function __construct() {
    	global $mysqli;
        $this->mysqli = $mysqli;
        $this->errorMsg = '';
        $this->errorNum = 0;
   }
   
	/**
	 * set sql string
	 * @param string $sql
	 * @return void		        $this->$dest[] = new Relation();

	 */
	public function setQuery(string $sql) {
		$this->sql = $sql;
	}

	/**
	 * get sql string
	 * @return string
	 */
	public function getQuery() : string {
		return $this->sql;
	}

	/**
	 * load array of records by $this->sql or from $dbResult
	 * @return array|false
	 */
	public function loadObjectList() {
		global $dbResult;
        $this->errorMsg = '';
        $this->errorNum = 0;
        $result = '_none_';
        if (count($dbResult) > 0) {
            $result = $dbResult[0];
            array_splice($dbResult,0,1);
        }
        if ($result == '_none_') {
            $result = [];
            if (MYSQLHOST == '') {
                $result = [];
            }
            try {
                $cursor = $this->mysqli->query($this->sql);
            } catch (Exception $e) {
                try {
                    $this->mysqli = new mysqli("localhost", TESTDBUSER, TESTDBPSW, TESTDB);
                    try {
                        $cursor = $this->mysqli->query($this->sql);
                    } catch(Exception $e) {
                        $cursor = false;
                        $this->errorMsg = 'error_in_query '.$e->getMessage().' sql='.$this->sql;
                        $this->errorNum = 1000;
                    }
                } catch(Exception $e) {
                        $cursor = false;
                        $this->errorMsg = 'error_in_reconnect '.$e->getMessage();
                        $this->errorNum = 1000;
                }
            }
            if ($cursor) {
                $w = $cursor->fetch_object();
                while ($w != null) {
                    $i = count($result);
                    $result[$i] = $w;
                    $w = $cursor->fetch_object();
                }
                $cursor->close();
            }
        }
        $this->writeLog();
        return $result;
	}
    /**
     * @param fp
     */
     private function writeLog() {
        if (MYSQLLOG) {
            if (file_exists('./log/mysql.log')) {
                $fp = fopen('./log/mysql.log','a+');
            } else {
                $fp = fopen('./log/mysql.log','w+');
            }
            fwrite($fp, date('Y-m-d H:i:s').' '.$this->sql."\n");
            fwrite($fp, $this->getErrorMsg()."\n");
            fclose($fp);
        }
     }

	
	/**
	 * load one record by $this->sqlString or from $dbResult
	 * @return recordObject|false
	 */
	public function loadObject() {
	    global $dbResult;
	    $result = '_none_';
	    if (count($dbResult) > 0) {
	        $result = $dbResult[0];
	        array_splice($dbResult,0,1);
        }
	    if ($result == '_none_') {
            $res = $this->loadObjectList();
            if (count($res) > 0) {
                $result = $res[0];
            } else {
                $result = false;
            }
        }
        return $result;
	}

	/**
	 * execute $this->sql
	 * @return bool
	 */	
	
	public function query() : bool {
	    global $dbResult;
	    $this->errorMsg = '';
	    $this->errorNum = 0;
	    $result = '_none_';
	    if (count($dbResult) > 0) {
	        $result = $dbResult[0];
	        array_splice($dbResult,0,1);
	    }
		if ($result == '_none_') {
		    if (MYSQLHOST == '') {
		        $result = true;
		        return $result;
		    }
            if (!isset($this->sql)) $this->sql = '';
            try {
                $result = $this->mysqli->query($this->sql);
                if (!$result && $this->inTransaction) {
                    $this->mysqli->rollback();
                    $this->inTransaction = false;
                }
                $this->errorMsg = $this->mysqli->error;
                $this->errorNum = $this->mysqli->errno;
            } catch (Exception $e) {
                $return = false;
                $this->errorMsg = 'error_in_reconnect '.$e->getMessage().' sql='.$this->sql;
                $this->errorNum = 1000;
                if ($this->inTransaction) {
                    $this->mysqli->rollback();
                    $this->inTransaction = false;
                }
            }
        }
        $this->writeLog();
        return $result;
	}
	
	/**
	 * get $this->errorNum
	 * @return int
	 */
	public function getErrorNum() : int {
		return $this->errorNum;
	}

    /**
     * get $this->errorMsg
     * @return string
     */
	public function getErrorMsg() : string {
		return $this->errorMsg;
	}
	
	/**
	 * quote string (adjust " --> \",  \n --> '\n' 
	 * @param string|mixed $str
	 * @return string|mixed
	 */
	public static function quote($str) {
        // global $mysqli;	    
	    // $result = $mysqli->real_escape_string($str);
        $str = str_replace('"','\"',$str);
        $str = str_replace("\n",'\n',$str);
        if (is_string($str)) {
	        $str = '"'.$str.'"';
	    }
	    return $str;
	}
	
   /**
    * exec sqlStr
    * @param string $sqlStr
    * @return bool
    */	
   public function exec(string $sqlStr) : bool {
        $this->setQuery($sqlStr);
        return $this->query();
   }

   /**
    * exec sqlStr
    * @param string $sqlStr
    * @return bool
    */
   public function statement(string $sqlStr) : bool {
		$this->setQuery($sqlStr);
		return $this->query();	
   } 
 
   /**
    * create new table object
    * @param string $fromStr
    * @param string $alias OPTIONAL default=''
    * @param string $columns OPTIONAL default='*'
    * @return Table
    */
	public static function table(string $fromStr, string $alias = '', string $columns = '*') {
		$result = new Table();
		$result->setFromStr($fromStr, $alias, $columns);
		return $result;	
	}  

	/**
	 * create new Filter object
	 * @param string $formStr
	 * @param string $alias OPTIONAL default=''
	 * @param string $columns OPTIONAL default='*'
	 * @return Filter
	 */
	public static function filter(string $formStr, string $alias = '', string $columns = '*') {
		$result = new Filter();
		$result->setFromStr($fromStr, $alias, $columns);
		return $result;	
	} 
	
	/**
	 * set php function in transactions
	 * @param callable $fun function()
	 * @return void
	 */
	public static function transaction(callable $fun) {
	    $this->inTransaction = true;
	    $this->mysqli->begin_transaction();
	    $fun();
	    if ($this->inTransaction) {
	       $this->mysqli->commit();
	    }
	    $this->inTransaction = false;
	}
	 
} // DB

class Table extends DB {
   /**
    * sql from string
    * @var string
    */ 
   protected $fromStr = '';
   
   /**
    * sql select string
    * @var string
    */
   protected $columns = '*';
   
   /**
    * sql alias string
    * @var string
    */
   protected $alias = '';
   
   /**
    * sql where string
    * @var string
    */
   protected $whereStr = '';
   
   /**
    * where array
    * @var array of Relation
    */
   protected $whereArray = array(); // array of Relation
   
   /**
    * sql group by string
    * @var string
    */
   protected $groupStr = '';
   
   /**
    * sql having string
    * @var string
    */
   protected $havingStr = '';
   
   /**
    * having array 
    * @var array og Realtion
    */
   protected $havingArray = array();
   
   /**
    * sql order by string
    * @var string
    */
   protected $orderStr = '';
   
   /**
    * sql offset value
    * @var integer
    */
   protected $offset = 0;
   
   /**
    * sql limit value
    * @var integer
    */
   protected $limit = 0;
   
    /**
     * set fromStr
     * @param string $fromStr
     * @param string $alias
     * @param string $columns
     * @return Table
     */
	public function setFromStr(string $fromStr, string $alias, string $columns) {
		$this->fromStr = $fromStr;
		return $this;	
	}

	/**
	 * load record set
	 * @return arrayOfRecordObject
	 */
	public function get() {
	    $this->createWhereHavingStr();
	    if ($this->whereStr == '') $this->whereStr = '1';
		if ($this->orderStr == '') $this->orderStr = '1';
		if ($this->offset == '') $this->offset = '0';
		$sqlStr = 'SELECT '.$this->columns.' FROM '.$this->fromStr.' '.$this->alias;
		$sqlStr .= ' WHERE '.$this->whereStr.' ORDER BY '.$this->orderStr;
		if ($this->limit != 0) {
			$sqlStr .= ' LIMIT '.$this->offset.','.$this->limit;		
		}	
		if ($this->groupStr != '') {
			$sqlStr .= ' GROUP BY '.$this->groupStr;		
		}
		if ($this->havingStr != '') {
			$sqlStr .= ' HAVING '.$this->groupStr;		
		}
		$this->setQuery($sqlStr);
		return $this->loadObjectList();
	}
    /**
     * 
     */
     private function createWhereHavingStr() {
        $this->whereStr = '';
	    foreach ($this->whereArray as $rel) {
	        $this->whereStr .= $rel->getSQL();
	    }
	    $this->havingStr = '';
	    foreach ($this->havingArray as $rel) {
	        $this->havingStr .= $rel->getSQL();
	    }
    }
	
	
	/**
	 * load one record
	 * @return recordObject|boolean
	 */
	public function first() {
		$this->limit = 1;
		$res = $this->get();
		if (count($res) > 0) {
		  return $res[0];
		} else {
		  return false;  
		}
	}
	
	/**
	 * add expression into existing whereStr  
	 * @param array|string|Relation $par
	 * @param string $con  OPTIONAL default = 'AND'
	 * @param string $dest OPTIONAL default = 'whereStr'
	 * @return Table $this
	 */
	public function where($par, string $con = ' AND ', string $dest = 'whereArray') {
		/*
	    if ($this->$dest != '') $this->$dest .= $con;
		if (is_string($par)) {
			$this->$dest .= $par;		
		} else if (is_array($par)) {
			if ((count($par) == 2) && (is_string($par[0]))) 
					$this->$dest .= '`'.$par[0].'` = '.$this->quote($par[1]);
		    if ((count($par) == 3) && (is_string($par[0]))) 
					$this->$dest .= '`'.$par[0].'` '.$par[1].' '.$this->quote($par[2]);
		}
		*/
		$this->$dest[] = new Relation();
		$relation = $this->$dest[count($this->$dest) - 1];
		if (count($this->$dest) == 1) {
		    $relation->concat = '';
		} else {
		    $relation->concat = $con;
		}
		if (is_string($par)) {
		        $relation->filedName = $par;
		        $relation->rel = '';
		        $relation->value = '';
		        // $this->$dest .= $par;
		} else if (is_array($par)) {
		    if ((count($par) == 2) && (is_string($par[0]))) {
		            // $this->$dest .= '`'.$par[0].'` = '.$this->quote($par[1]);
		        $relation->fieldName = $par[0];
		        $relation->rel = '=';
		        $relation->value = $par[1];
		    }
		    if ((count($par) == 3) && (is_string($par[0]))) {
		        // $this->$dest .= '`'.$par[0].'` '.$par[1].' '.$this->quote($par[2]);
		        $relation->fieldName = $par[0];
		        $relation->rel = $par[1];
		        $relation->value = $par[2];
		    }
		} else if (is_object($par)) {
		    $relation->relation = $par;
		}
		    
		return $this;	
	}	
	
	/**
	 * add new extension into existing whereStr OR operand
	 * @param array|string|Relation $par
	 * @return Table $this
	 */
	public function orWhere($par) {
		$this->where($par, ' OR ','whereArray');
		return $this;	
	}

	/**
	 * set $this->groupStr
	 * @param array $par (colName, colName,...)
	 * @return Table
	 */
	public function group($par) {
		$this->groupStr = '';
		foreach ($par as $fn) {
			if ($this->groupStr != '') $this->groupStr .= ',';
				$this->groupStr .= '`'.$fn.'`';
		}
		return $this;	
	}
	
	/**
	 * add expression into existing havingeStr by AND operand
	 * @param array|string|Relation $par
	 * @return Table $this
	 */
	public function having($par) {
		$this->where($par,' AND ','havingArray');
		return $this;
	}

	
	/**
	 * add expression into existing havingeStr by OR operand
	 * @param array|string $par
	 * @return Table $this
	 */
	public function orHaving($par) {
		$this->where($par,' OR ','havingArray');
		return $this;
	}
		
	/**
	 * set $this->orderStr
	 * @param string $s
	 * @return Table
	 */
	public function order(string $s) {
		$this->orderStr = $s;
		return $this;	
	}	

	/**
	 * set $this->limit
	 * @param numeric $v
	 * @return Table
	 */
	public function limit(numeric $v) {
		$this->limit = $v;
		return $this;	
	}	

	/**
	 * set $this->offset
	 * @param numeric $v
	 * @return Table
	 */
	public function offset(numeric $v) {
		$this->offset = $v;
		return $this;	
	}	
	
	/**
	 * delete records by whereStr
	 * @return Table
	 */
	public function delete() {
	    $this->createWhereHavingStr();
	    if ($this->whereStr == '') $this->whereStr = '1';
		$sqlStr = 'DELETE FROM '.$this->fromStr.
		' WHERE '.$this->whereStr;
		$this->setQuery($sqlStr);
		$this->query();
		return $this;
	}

	/**
	 * update table by whereStr
	 * @param array $record ("colname" => value, ....) 
	 * @return Table
	 */
	public function update($record) {
	    $this->createWhereHavingStr();
	    if ($this->whereStr == '') $this->whereStr = '1';
		$s = '';
		foreach ($record as $fn => $fv) {
			if ($s != '') $s .= ',';
			$s .= '`'.$fn.'`='.$this->quote($fv);		
		}
		$sqlStr = 'UPDATE '.$this->fromStr.' SET '.$s.' WHERE '.$this->whereStr;
		$this->setQuery($sqlStr);
		$this->query();
		return $this;
	}

	/**
	 * insert new record
	 * @param array $record ("colName" => value)
	 * @return Table
	 */
	public function insert($record) {
		$fnames = '';
		$values = '';
		foreach ($record as $fn => $fv) {
			if ($fnames != '') $fnames .= ',';
			$fnames .= '`'.$fn.'`';
			if ($values != '') $values .= ',';
			$values .= $this->quote($fv);
		}
		$sqlStr = 'INSERT INTO '.$this->fromStr.' ('.$fnames.') VALUES ('.$values.')';
		$this->setQuery($sqlStr);
		$this->query();
		return $this;
	}
	
	/**
	 * get last inserted id
	 * @return unknown
	 */
	public function getInsertedId() {
	    global $mysqli;
	    return $mysqli->insert_id;
	}
	
	/**
	 * get count of recordset
	 * @return int
	 */
	public function count() : int {
	    $this->whereStr = '';
	    foreach ($this->whereArray as $rel) {
	        $this->whereStr .= $rel->getSQL();
	    }
	    $this->havingStr = '';
	    foreach ($this->havingArray as $rel) {
	        $this->havingStr .= $rel->getSQL();
	    }
	    if ($this->whereStr == '') $this->whereStr = '1';
		$sqlStr = 'SELECT count(*) AS cc FROM '.$this->fromStr.' WHERE '.$this->whereStr;
		$this->setQuery($sqlStr);
		$res = $this->loadObject(); 
		return $res->cc;
	}	
	
	/**
	 * get column list
	 * @return array of fieldRec (see sql documentation)
	 */
	public function getFieldList() {
		$sqlStr = 'SHOW FIELDS FROM '.$this->fromStr;
		$this->setSql($sqlstr);
		return $this->loadObjectList(); 
	}
	
} // Table

class Filter extends Table {
    /**
     * array of objects
     * @var array
     */
	protected $joins = array();
	
	/**
	 * add new item into $this->joins
	 * @param string $joinType 'LEFT OUTER JOIN', 'RIGHT OUTER JOIN', 'INNER JOIN'
	 * @param string $tableName
	 * @param string $alias
	 * @param string $onStr sql syntax without 'ON'
	 * @return Filter
	 */
	public function join(string $joinType, string $tableName, string $alias, string $onStr) {
	    // joinType: 'LEFT OUTER JOIN', 'RIGHT OUTER JOIN', 'INNER JOIN'
		$this->joins.push(array($joinType, $tableName, $alias, $onStr));
		return $this;
	}

	/**
	 * load record set
	 * @return arrayOfRecordObject|false
	 */
	public function get() {
	    $this->createWhereHavingStr();
	    if ($this->whereStr == '') $this->whereStr = '1';
		if ($this->orderStr == '') $this->orderStr = '1';
		if ($this->offset == '') $this->offset = '0';
		$sqlStr = 'SELECT '.$this->columns.' FROM '.$this->formStr.' '.$this->alias;
		foreach ($this->joins as $join) {
			$sqlstr .= ' '.$join[0].' '.$joinn[1].' '.$join[2].
			' ON '.$join[3];
		}
		$sqlStr .= ' WHERE '.$this->whereStr.' ORDER BY '.$this->orderStr;
		if ($this->limit != 0) {
			$sqlStr .= 'LIMIT '.$this->offset.','.$this->limit;		
		}	
		if ($this->groupStr != '') {
			$sqlStr .= ' GROUP BY '.$this->groupStr;		
		}
		if ($this->havingStr != '') {
			$sqlStr .= ' HAVING '.$this->groupStr;		
		}
		$this->setQuery($sqlStr);
		return $this->loadObjectList();
	}
} // Filter

?>
