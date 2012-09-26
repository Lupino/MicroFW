<?
if(!defined('DSN'))exit('未定义数据库信息：<br/>Eg:define("DSN","mysql:host=localhost;dbname=Model");');
if(!defined('DUN'))exit('未定义数据库用户名：<br/>Eg:define("DUN","")');
if(!defined('DPW'))exit('未定义数据库用户密码：<br/>Eg:define("DPW","")');
//require_once('DBO.php');
//-------------------------------------------------
/* Simple abbreviation, and allows for implementing faster escape algo */
function cl($string)
{ return mysql_escape_string($string); }
//-------------------------------------------------
class Query{
	const DESC = 'DESC';
	const ASC='ASC';
	private $DBinit;
	private $where=array();
	private $table;
	private $orWhere=array();
	private $Model;
	private $_limit='';
	private $order='';
	public function __construct($ModelName,$table){
		$this->DBinit=self::getDBinit();
		$this->Model=$ModelName;
		$this->table=$table;
	}
	public function find($arr=array()){
		foreach($arr as $key=>$val){
			$this->where[]=$val;
		}
		return $this;
	}
	public function where(){
		$args=func_get_args();
		return $this->find($args);
	}
	public function orWhere(){
		$args=func_get_args();
		foreach($args as $key=>$val){
			$this->orWhere[]=$val;
		}
		return $this;
	}
	public function limit($limit){
		$this->_limit=" limit {$limit}";
		return $this;
	}
	public function limitPages($start,$N){
		$start = $start*($N);
		$this->_limit=" limit {$start},{$N}";
		return $this;
	}
	public function orderBy($name,$dec=self::DESC){
		$this->order=" ORDER BY  `{$name}` {$dec}";
		return $this;
	}
	private function getPartSql(){
		$wh=join(' and ',$this->where);
		$oWh=join(' or ',$this->orWhere);
		$sql='';
		if(!empty($wh)&&!empty($oWh))$sql.=' where '.$wh.' or '.$oWh;
		elseif(!empty($wh))$sql.=' where '.$wh;
		elseif(!empty($oWh))$sql.=' where '.$wh;
		return $sql.$this->order.$this->_limit;
	}
	private function prepare($is_count = false){
		$sql="select * from `{$this->table}`";
		if($is_count)$sql = "select count(*) from `{$this->table}`";
		return $this->DBinit->prepare($sql.$this->getPartSql());
	}
	public function getAll($arr=array()){
		$obj=array();
		$query=$this->prepare();
		foreach($query->getAll($arr) as $key=>$val){
			$obj[]=new $this->Model($val);
		}
		return $obj;
	}
	public function getOne($arr=array()){
		$query=$this->prepare();
		return new $this->Model($query->getOne($arr));
	}
	public function count($arr=array()){
		$query=$this->prepare(true);
		return $query->count($arr);
	}
	public function destory($arr=array()){
		$sql="DELETE FROM `{$this->table}`";
		$prepare=$this->DBinit->prepare($sql.$this->getPartSql());
		return $prepare->execute($arr);
	}
	//-----------------------------------------------------------------------
	public static function getDBinit(){
		global $DBinit_cache;
		if(empty($DBinit_cache))
			$DBinit_cache=new DBO(DSN,DUN,DPW,array(PDO::MYSQL_ATTR_INIT_COMMAND=>"SET NAMES 'utf8';"));
		return $DBinit_cache;
	}
	//-----------------------------------------------------------------------
	public static function getColumns($tablename){
		global $table_cache; // cache for speed
		if (!empty($table_cache[$tablename])){
			return $table_cache[$tablename];
		}
		$colss = self::getDBinit()->prepare("describe `{$tablename}`");
		$cols=$colss->getAll();
		$table_cache[$tablename] = array();
		foreach ($cols as $col){
			$table_cache[$tablename][$col['Field']] = 1;
		}
		return $table_cache[$tablename];
	}
	public static function insert_array($array, $table){
		$table_columns = self::getColumns($table);
		$insertString = '';
		$valueString = '';
		foreach ($array as $key => $value) {
			if (isset($table_columns[$key])){
				$insertString .= "`" . $key . "`,";
				$valueString .= "'" . cl($value) . "',";
			}
		}
		// drop commmas off end
		$insertString = substr($insertString, 0, strlen($insertString) - 1);
		$valueString = substr($valueString, 0, strlen($valueString) - 1);
		$sql = "INSERT INTO `$table` ($insertString) VALUES ($valueString)";
		$DBinit=self::getDBinit();
		//echo $sql;
		if($DBinit->exec($sql))	return $DBinit->lastInsertId();
		return 0;
	}
	public static function update_array($array, $id, $table){
		$DBinit=self::getDBinit();
		$table_columns = self::getColumns($table);
		$updateString = '';
		foreach ($array as $key => $value){
			// make sure we only update fields that exist in the db table
			if (isset($table_columns[$key])){
				$updateString .= "`" . $key . "`='" . cl($value) . "',";
			}
		}
		// drop commmas off end
		$updateString = substr($updateString, 0, strlen($updateString) - 1);
		$sql = "update `$table` set $updateString WHERE (`id` = '$id') limit 1;";
		return $DBinit->exec($sql);
	}
}
?>
