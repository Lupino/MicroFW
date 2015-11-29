<?php
if(!defined('VIEWDIR'))exit('未定义视图位置：VIEWDIR');
if(!defined('ONLYONEVIEWDIR'))exit('未定义是否单一视图位置<br/>ture:为单一<br/>false:为一个Controller一个view。');
abstract class Controller{
	/*
	 * 封装请求的对象
	 * @var Context
	 */
	protected $_context;
	/*
	 * 是否是POST METHOD
	 */
	protected $_isPOST;
	/*
	 * 是否是GET METHOD
	 */
	protected $_isGET;
	/*
	 * 视图变量
	 */
	protected $_view=array();
	/*
	 * 视图的名称
	 */
	protected $_viewname;
	/*
	 *  视图文件所在目录
	 */
	private $_view_dir;

	public function __construct($myname, $_action, $_context, $method){
		$action='action'.$_action;
		$this->_isPOST=false;
		$this->_isGET=false;
		if($method == "post"){
			$this->_isPOST = true;
		}
		if($method == "get"){
			$this -> _isGET = true;
		}
		$this->_context = $_context;
		if(method_exists($this, $action)){
			Auth::check("$myname::$_action");
			$this -> $action();
			$this->_viewname=$this->_viewname?$this->_viewname.'.php':$_action.'.php';
			if(ONLYONEVIEWDIR){
			    $this->_view_dir = VIEWDIR.DS;
			}else{
				$this->_view_dir = VIEWDIR.DS.$myname.DS;
			}
		}else{throw new NotFindActionException("NotFindActionException: Can't not find Action $myname::$_action!");}
	}
	public function __destruct(){
		if(!is_file($this->_view_dir.$this->_viewname))return;
		$parse = new Render($this->_view_dir);
		echo $parse->assign($this->_view)->parse($this->_viewname);
	}
}
?>
