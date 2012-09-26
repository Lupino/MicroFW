<?
class DBO extends PDO {
	public function __construct($dsn, $username="", $password="", $driver_options=array()) {
		parent::__construct($dsn,$username,$password, $driver_options);
		$this->setAttribute(PDO::ATTR_STATEMENT_CLASS, array('DBOStatement', array($this)));
	}
}


class DBOStatement extends PDOStatement {
	public $dbh;
	
	protected function __construct($dbh) {
		$this->dbh = $dbh;
	}
	
	function getAll($arr=array()){
		//echo $this->queryString.'<br/>';
		$rs=$this->execute($arr);
		if ($rs)
			return $this->fetchALL(PDO::FETCH_ASSOC);
		else
			$this->getError();
		return false;
	}

	function getOne($arr=array()){
		$rs = $this->execute($arr);
		if($rs){
			//return $this->fetchColumn();
			$ret=$this->fetch(PDO::FETCH_ASSOC);
			//print_r($ret);
			return $ret;
		}else 
			$this->getError();
		return false;
	}
	
	function count($arr=array()){
		$rs = $this->execute($arr);
		if($rs){
			return $this->fetchColumn();
		}else 
			$this->getError();
		return false;
	}
	
	protected function getError(){
		$err_arr = $this->errorInfo();
		trigger_error("Error:".$err_arr[2],E_USER_ERROR);
die;
	}
}
?>
