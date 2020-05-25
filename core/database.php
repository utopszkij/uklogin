<?php
/**
 * OpenId szolgáltatás magyarorszag.hu ügyfélkapu használatával
 * @package uklogin
 * @author Fogler Tibor
 */

define('NONE','_none_');
define('WHERE',' WHERE ');
global $mysqli;
if (!isset($mysqli)) {
    $mysqli = new mysqli(config('MYSQLHOST'), config('MYSQLUSER'), config('MYSQLPSW'));
    defineConfig('MYSQLUSER','');
    defineConfig('MYSQLPSW','');
}
/**
 * SQL feltétel osztály
 * @author utopszkij
 */
class Relation {
    /** reláció */
    public $concat = ''; // 'AND' | 'OR' | ''
    /** relációk */
    public $relations = false; // false | array of Relation
    /** mező név */
    public $fieldName = '';
    /** relációs jel */
    public $rel = ''; // '<' | '<=' | '=' | ">=' | '>' | '<>' | ''
    /** érték */
    public $value = '';
    
    /**
     * konstruktor
     * @param string $concat
     * @param array $relations
     * @param string $fieldName
     * @param string $rel
     * @param string $value
     */
    function __construct(string $concat = '',
                         array $relations = [],
                         string $fieldName = '',
                         string $rel = '',
                         $value = '') {
        $this->concat = $concat;
        $this->relations = $relations;
        $this->fieldName = $fieldName;
        $this->rel = $rel;
        $this->value = $value;
    }
    
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
                $result .= $this->fieldName;
            } else {
                $result .= $this->fieldName.' '.$this->rel.' '.DB::quote($this->value);
            }
        }
        return $result;
    }
}

/** adatbázis kezelő osztály */
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
   protected $sql = '';
   
   /**
    * last mysql errorMsg
    * @var string
    */
   protected $errorMsg = '';
   
   /**
    * last mysql error number
    * @var int
    */
   protected $errorNum = 0;
   
   /**
    * transaction flag
    * @var string
    */
   protected $inTransaction = false;
    
   /**
    * konstruktor
    */
   function __construct() {
       $this->connect();
       $this->errorMsg = '';
       $this->errorNum = 0;
       $this->inTransaction = false;
   }
   
   /**
    * kapcsolodás az sql szerverhez
    */
   public function connect() {
       global $mysqli;
       $mysqli->query($mysqli->real_escape_string('CREATE DATABASE IF NOT EXISTS '. config('MYSQLDB')));
       $mysqli->select_db(config('MYSQLDB'));
       $mysqli->query('SET NAMES utf8');
       $mysqli->set_charset("utf8");
       $this->mysqli = $mysqli;
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
	 * load array of records by $this->sql 
	 * @return array|false
	 */
	public function loadObjectList() {
        $this->errorMsg = '';
        $this->errorNum = 0;
        $result = [];
        try {
            $cursor = $this->mysqli->query($this->sql);
            if ($cursor === false) {
                $this->errorMsg = $this->mysqli->error;
                $this->errorNum = $this->mysqli->errno;
            }
        } catch (Exception $e) {
                try {
                    $cursor = $this->mysqli->query($this->sql);
                    if ($cursor === false) {
                        $this->errorMsg = $this->mysqli->error;
                        $this->errorNum = $this->mysqli->errno;
                    }
                } catch(Exception $e) {
                    $cursor = false;
                    $this->errorMsg = 'error_in_query '.$e->getMessage().' sql='.$this->sql;
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
        } else {
            $result = [];
        }
        $this->writeLog();
        return $result;
	}
	
    /**
     * sql log írása
     */
     private function writeLog() {
        if (config('MYSQLLOG')) {
            $path = './log/mysql.log';
            if (file_exists($path)) {
                $fp = fopen($path,'a+');
            } else {
                $fp = fopen($path,'w+');
            }
            fwrite($fp, date('Y-m-d H:i:s').' '.$this->sql."\n");
            fwrite($fp, $this->getErrorMsg()."\n");
            fclose($fp);
        }
     }
	
	/**
	 * load one record by $this->sqlString or from $dbResult
	 * @return object|false
	 */
	public function loadObject() {
        $res = $this->loadObjectList();
        if (count($res) > 0) {
                $result = $res[0];
        } else {
                $result = false;
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
        try {
                $result = $this->mysqli->query($this->sql);
                if (!$result && $this->inTransaction) {
                    $this->mysqli->rollback();
                    $this->inTransaction = false;
                }
                $this->errorMsg = $this->mysqli->error;
                $this->errorNum = $this->mysqli->errno;
        } catch (Exception $e) {
                $result = false;
                $this->errorMsg = 'error_in_reconnect '.$e->getMessage().' sql='.$this->sql;
                $this->errorNum = 1000;
                if ($this->inTransaction) {
                    $this->mysqli->rollback();
                    $this->inTransaction = false;
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
	    if ($this->errorMsg != '') {
		  $result = $this->errorMsg.' sql:'.$this->getQuery();
	    } else {
	      $result = '';  
	    }
	    return $result;
	}
	
	/**
	 * quote string (adjust " --> \",  \n --> '\n' 
	 * @param string|mixed $str
	 * @return string|mixed
	 */
	public static function quote(string $str): string {
		 global $mysqli;
	    return '"'.$mysqli->real_escape_string($str).'"';
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
        return $this->exec($sqlStr);
   } 
 
   /**
    * create new table object
    * @param string $fromStr
    * @param string $alias OPTIONAL default=''
    * @param string $columns OPTIONAL default='*'
    * @return Table
    */
	public static function table(string $fromStr, string $alias = '', string $columns = '*'): Table {
		$result = new Table($fromStr);
		$result->setFromStr($fromStr, $alias, $columns);
		return $result;	
	}  

	/**
	 * create new Filter object
	 * @param string $fromStr
	 * @param string $alias OPTIONAL default=''
	 * @param string $columns OPTIONAL default='*'
	 * @return Filter
	 */
	public static function filter(string $fromStr, string $alias = '', string $columns = '*'): Filter {
		$result = new Filter($fromStr);
		$result->setFromStr($fromStr, $alias, $columns);
		return $result;	
	} 
	
	/**
	 * set php function in transactions
	 * @param callable $fun function()
	 * @return void
	 */
	public function transaction(callable $fun) {
	    $this->inTransaction = true;
	    $this->mysqli->begin_transaction();
	    $fun();
	    if ($this->inTransaction) {
	       $this->mysqli->commit();
	    }
	    $this->inTransaction = false;
	}
	
	/**
	 * adatbázis tábla kreálása (ha még nem létezik)
	 * @param string $tableName tábla neve
	 * @param array $columns [[name, type, length, primaryKey], ....]
	 * @param array $keys [name, ...]
	 * @return bool
	 */
	public function createTable(string $tableName, array $columns, array $keys): bool {
	    $primary = '';
	    $s = 'CREATE TABLE IF NOT EXISTS `'.$tableName.'` ('."\n";
	    foreach ($columns as $column) {
	        $s1 = '`'.$column[0].'` '.$column[1];
	        if ($column[2] != '') {
	            $s1 .= '('.$column[2].')';
	        }
	        if (isset($column[3]) && ($column[3])) {
	            $s1 .= ' AUTO_INCREMENT';
	            $primary = $column[0];
	        }
	        $s .= $s1.',';
	    }
	    $s1 = '';
	    if ($primary != '') {
	        $s1 = 'PRIMARY KEY (`'.$primary.'`)';
	    }
	    foreach ($keys as $key) {
	        if ($s1 != '') {
	            $s1 .= ','."\n";
	        }
	        $s1 .= 'KEY `'.$tableName.'_'.$key.'_ndx` (`'.$key.'`)';
	    }
	    $s .= $s1."\n".')';
	    return $this->exec($s);
	}
	
	/**
	 * tábla megsemisitése
	 * @param string $tableName
	 * @return bool
	 */
	public function dropTable(string $tableName): bool {
	    return $this->exec('DROP TABLE `'.$tableName.'`');
	}
	
	/**
	 * tábla tartalmának törlése
	 * @param string $tableName
	 * @return bool
	 */
	public function emptyTable(string $tableName): bool {
	    return $this->exec('DELETE FROM `'.$tableName.'`');
	}
	
	/**
	* tábla egy mezőjének felvétele vagy modosítása
	* @param string $tableName
	* @param string $fieldName
	* @param string $type SQL szintaxis szerint pl: varchat
	* @param int length 
	* @return bool
	*/
	public function alterTable(string $tableName, string $fieldName, string $type, int $length): bool {
		$result = true;
		if ($length > 0) {
		    $type .= '('.$length.')';
		}
		$this->setQuery('SHOW COLUMNS FROM `'.$tableName .'` LIKE "'.$fieldName.'"');
		$res = $this->loadObject();	
		if ($res) {
			if ($res->Type != $type) {
				$this->setQuery('ALTER TABLE `'.$tableName.'` MODIFY COLUMN `'.$fieldName.'` '.$type);
				$result = $this->query();
			}
		} else {
			$this->setQuery('ALTER TABLE `'.$tableName.'` ADD COLUMN `'.$fieldName.'` '.$type);
			$result = $this->query();
		}
		return $result;
	} 
	
} // DB

/** tábla kezelő objektum */
class SimpleTable extends DB {
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
    * konstruktor
    * @param string|Table $from
    * @param string $alias
    */
   function __construct($from, string $alias = '') {
       global $mysqli;
       $this->mysqli = $mysqli;
       $this->errorMsg = '';
       $this->errorNum = 0;
       $this->alias = $alias;
       if (is_object($from)) {
           $this->setFromSubselect($from, $alias);
       } else {
           $this->setFromStr($from, $alias);
       }
   }
   
   /**
     * set fromStr
     * @param string $fromStr
     * @param string $alias
     * @param string $columns
     * @return Table
     */
	public function setFromStr(string $fromStr, string $alias = '', string $columns = '*') {
	    $this->fromStr = $fromStr;
	    $this->alias = $alias;
	    $this->columns = $columns;
		return $this;	
	}

	/**
	 * lekérdezés oszlopainak definiálása
	 * @param string $columns
	 */
	public function setColumns(string $columns) {
	    $this->columns = $columns;
	}
	
	/**
	 * SQL lekérdezése
	 * @return string
	 */
	public function getSql() {
	    $this->createWhereHavingStr();
	    if ($this->whereStr == '') {
	        $this->whereStr = '1';
	    }
	    if ($this->orderStr == '') {
	        $this->orderStr = '1';
	    }
	    if ($this->offset == '') {
	        $this->offset = '0';
	    }
	    $sqlStr = 'SELECT '.$this->columns.' FROM `'.$this->fromStr.'` '.$this->alias;
	    $sqlStr .= WHERE.$this->whereStr.' ORDER BY '.$this->orderStr;
	    if ($this->groupStr != '') {
	        $sqlStr .= ' GROUP BY '.$this->groupStr;
	    }
	    if ($this->havingStr != '') {
	        $sqlStr .= ' HAVING '.$this->groupStr;
	    }
	    if ($this->limit != 0) {
	        $sqlStr .= ' LIMIT '.$this->offset.','.$this->limit;
	    }
	    return $sqlStr;
	}
	
	/**
	 * load record set
	 * @return array|false
	 */
	public function get() {
	   $sqlStr = $this->getSql();
		$this->setQuery($sqlStr);
		return $this->loadObjectList();
	}
	
    /**
     * sql where / having str előállítása
     */
     protected function createWhereHavingStr() {
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
	 * @return object|boolean
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
	 * get sql string
	 * @return string
	 */
	public function getQuery() : string {
		if ($this->sql == '') {
			$this->sql = $this->getSql();			
		}
		return $this->sql;
	}


	/**
	 * array paraméter -> relation
	 * @param array $par
	 * @param Relation $relation
	 */
	protected function whereFromArray(array $par, &$relation) {
	    if ((count($par) == 2) && (is_string($par[0]))) {
	        $relation->fieldName = $par[0];
	        $relation->rel = '=';
	        $relation->value = $par[1];
	    }
	    if ((count($par) == 3) && (is_string($par[0]))) {
	        $relation->fieldName = $par[0];
	        $relation->rel = $par[1];
	        $relation->value = $par[2];
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
		$this->$dest[] = new Relation();
		$relation = $this->$dest[count($this->$dest) - 1];
		if (count($this->$dest) == 1) {
		    $relation->concat = '';
		} else {
		    $relation->concat = $con;
		}
		if (is_string($par)) {
		        $relation->fieldName = $par;
		        $relation->rel = '';
		        $relation->value = '';
		} else if (is_array($par)) {
		    $this->whereFromArray($par, $relation);
		} else if (is_object($par)) {
		    if (count($this->$dest) == 1) {
		        $par->concat = '';
		    }
		    $this->$dest[count($this->$dest) - 1] = $par;
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
	 * delete records by whereStr
	 * @return Table
	 */
	public function delete():bool {
	    $this->createWhereHavingStr();
	    if ($this->whereStr == '') {
	        $this->whereStr = '1';
	    }
		$sqlStr = 'DELETE FROM `'.$this->fromStr.'`'.
		WHERE.$this->whereStr;
		$this->setQuery($sqlStr);
		return $this->query();
	}

	/**
	 * update table by whereStr
	 * @param array $record ("colname" => value, ....) 
	 * @return bool
	 */
	public function update($record) {
	    $this->createWhereHavingStr();
	    if ($this->whereStr == '') {
	        $this->whereStr = '1';
	    }
		$s = '';
		foreach ($record as $fn => $fv) {
		    if ($s != '') {
		        $s .= ",\n";
		    }
			$s .= '`'.$fn.'`='.$this->quote($fv);		
		}
		$sqlStr = 'UPDATE `'.$this->fromStr.'` SET '.$s.WHERE.$this->whereStr;
		$this->setQuery($sqlStr);
		return $this->query();
	}

	/**
	 * insert new record
	 * @param array $record ("colName" => value)
	 * @return Table
	 */
	public function insert($record): bool {
		$fnames = '';
		$values = '';
		foreach ($record as $fn => $fv) {
		    if ($fnames != '') {
		        $fnames .= ",\n";
		    }
			$fnames .= '`'.$fn.'`';
			if ($values != '') {
			    $values .= ",\n";
			}
			$values .= $this->quote($fv);
		}
		$sqlStr = 'INSERT INTO `'.$this->fromStr.'` ('.$fnames.') VALUES ('.$values.')';
		$this->setQuery($sqlStr);
		return $this->query();
	}
	
	/**
	 * get last inserted id
	 * @return int
	 */
	public function getInsertedId(): int {
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
	    if ($this->whereStr == '') {
	        $this->whereStr = '1';
	    }
		$sqlStr = 'SELECT count(*) AS cc FROM `'.$this->fromStr.'` WHERE '.$this->whereStr;
		$this->setQuery($sqlStr);
		$res = $this->loadObject(); 
		if ($res) {
		  $result = $res->cc;
		} else {
		  $result = 0;  
		}
		return $result;    
	}	
	
	/**
	 * get column list
	 * @return array of fieldRec (see sql documentation)
	 */
	public function getFieldList() {
		$sqlStr = 'SHOW FIELDS FROM '.$this->fromStr;
		$this->setQuery($sqlStr);
		return $this->loadObjectList(); 
	}
	
} // SimpleTable

/** SQL tábla kezelő  osztály */
class Table extends SimpleTable {

    /**
     * set from subselect
     * @param Table $table
     * @param string $alias
     */
    public function setFromSubselect(Table $table, string $alias) {
        $this->fromStr = '('.$table->getSql().') '.$alias;
    }
    
    /**
     * set $this->groupStr
     * @param array $par (colName, colName,...)
     * @return Table
     */
    public function group($par) {
        $this->groupStr = '';
        foreach ($par as $fn) {
            if ($this->groupStr != '') {
                $this->groupStr .= ',';
            }
            $this->groupStr .= $fn;
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
     * @param int $v
     * @return Table
     */
    public function limit(int $v) {
        $this->limit = $v;
        return $this;
    }
    
    /**
     * set $this->offset
     * @param int $v
     * @return Table
     */
    public function offset(int $v) {
        $this->offset = $v;
        return $this;
    }
    
}

/** Komplex sql lekérdezés */
class Filter extends Table {
    /**
     * array of objects
     * @var array
     */
	protected $joins = array();
	
	/**
	 * add new item into $this->joins
	 * @param string $joinType 'LEFT OUTER JOIN', 'RIGHT OUTER JOIN', 'INNER JOIN'
	 * @param string|Table $from
	 * @param string $alias
	 * @param string $onStr sql syntax without 'ON'
	 * @return Filter
	 */
	public function join(string $joinType, $from, string $alias, string $onStr) {
	    // joinType: 'LEFT OUTER JOIN', 'RIGHT OUTER JOIN', 'INNER JOIN'
	    if (is_object($from)) {
	        $this->joins[] = array($joinType, '('.$from->getSql().') ', $alias, $onStr);
	    } else {
	        $this->joins[] = array($joinType, $from, $alias, $onStr);
	    }
		return $this;
	}
	
	/**
	 * SQL string lekérdezése
	 * @return string
	 */
	public function getSql():string {
	    $this->createWhereHavingStr();
	    if ($this->whereStr == '') {
	        $this->whereStr = '1';
	    }
	    if ($this->orderStr == '') {
	        $this->orderStr = '1';
	    }
	    if ($this->offset == '') {
	        $this->offset = '0';
	    }
		$sqlStr = 'SELECT '.$this->columns.' FROM '.$this->fromStr.' '.$this->alias;
		foreach ($this->joins as $join) {
			$sqlStr .= ' '.$join[0].' '.$join[1].' '.$join[2].
			' ON '.$join[3];
		}
		$sqlStr .= WHERE.$this->whereStr;
		
		if ($this->groupStr != '') {
			$sqlStr .= ' GROUP BY '.$this->groupStr;		
		}
		if ($this->havingStr != '') {
			$sqlStr .= ' HAVING '.$this->groupStr;		
		}
		$sqlStr .= ' ORDER BY '.$this->orderStr;
		if ($this->limit != 0) {
			$sqlStr .= ' LIMIT '.$this->offset.','.$this->limit;		
		}	
		return $sqlStr;	
	}	
	
	/**
	 * get sql string
	 * @return string
	 */
	public function getQuery() : string {
		return $this->getSql();
	}

	/**
	 * load record set
	 * @return array|false
	 */
	public function get() {
		$this->setQuery($this->getSql());
		return $this->loadObjectList();
	}
} // Filter

?>
