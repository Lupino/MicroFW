<?php
/*
 * Context 封装了运行时上下文
 */
class Context{
	protected $_context=array();
	public function __construct($arr=array()){
		$this->_context=$arr;
	}
	public function __get($parameter){
		return $this->query($parameter);
	}

	public function __set($parameter, $value){
		$this->_context[$parameter]=$value;
	}
	protected function query($parameter, $default = null){
		if(!empty($this->_context[$parameter]))return $this->_context[$parameter];
		return $default;
	}
	public function toArray(){
    		return $this->_context;
    }
}
?>
