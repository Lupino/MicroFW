<?
//if(!defined('APPDIR'))exit('未定义App位置：APPDIR');
if(!defined('DS'))define('DS', DIRECTORY_SEPARATOR);
/*
 * Router 实现了自定义路由解析
 */
class Router{
	/*
	 * 通过反相匹配路由规则来生成 URL
	 */
	public static function url($url,$arr=array()){
		$page=$_SERVER['SCRIPT_NAME'];
		$uri='/';
		foreach($arr as $key=>$val){
			$uri.=$key.'/'.$val.'/';
		}
		if(isset( $_SERVER['REDIRECT_URL'] )){
			return dirname($page).'/'.$url.$uri;
		}
		return $page.'/'.$url.$uri;
	}
	public function uRemoveGetParams($url){
		$relUrl = explode('?', $url);
		return $relUrl[0];
	}
	public function ugetURI(){
		$req=$this->uRemoveGetParams($_SERVER['REQUEST_URI']);
		$page=$_SERVER['SCRIPT_NAME'];
		if( stripos($req, $page) === FALSE || isset( $_SERVER['REDIRECT_URL'] ) ){
			$page = explode('/', $page);
			$page = array_slice($page, 0, -1);
			$page = join('/', $page)."/";
		}
		if(strlen($req)<strlen($page))
			$req=$page;
		
		if($page != "/")
		    $req=str_replace($page,'',$req);
		if(strlen($req)=== 0 || $req[0]!=='/')
			$req = '/'.$req;
		if($req[strlen($req)-1]!=='/')
		    $req .= '/';
		return $req;
	}
	public function utriggerFunction($uri,$method){
		//  /Controller/Action/
		$uri = substr($uri, 0, strlen($uri) - 1);
		
		$uri = explode('/',$uri);
		$Controller='index';
		array_shift($uri);
		if(count($uri)%2==0){
			$Controller=$uri[0];
			array_shift($uri);
		}
		$action=$uri[0];
		array_shift($uri);
		$context=array();
		while(count($uri)){
			$context[$uri[0]]=$uri[1];
			array_shift($uri);
			array_shift($uri);
		}
		foreach($_GET as $key=>$val){
			$context[$key]=$val;
		}
		foreach($_POST as $key=>$val){
			$context[$key]=$val;
		}
		return MF::run($Controller,$action,$context,$method);
	}
	public function ugetReqMethod(){
		return strtolower( $_SERVER['REQUEST_METHOD'] );
	}
	public function start(){
		$url = $this->ugetURI();
		if($url==='/')$url.='index/index/';
		$ret = $this->utriggerFunction( $url , $this->ugetReqMethod() );
		return $ret;
	}

}
?>
