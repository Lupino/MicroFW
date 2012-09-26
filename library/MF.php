<?
//if(!defined('MODELDIR'))exit('未定义模型位置：MODELDIR');
if(!defined('CONTROLLERDIR'))exit('未定义Controller位置：CONTROLLERDIR');
//if(!defined('APPDIR'))exit('未定义App位置：APPDIR');
define('MFROOT',dirname(__FILE__));
if(!defined('DS'))define('DS', DIRECTORY_SEPARATOR);
$MF_config=require_once(MFROOT.DS.'config.php');

class MF{
    protected $router;
    public function __construct($arr=array()){
        $this->router=new Router();
    }
    public static function run($Controller, $action, $context, $method){
        try{
            if(!class_exists($Controller))
                if(!self::Import(str_replace('_', DS, CONTROLLERDIR.DS.$Controller.'.php')))
                    throw new NotFindControllerException("NotFindControllerException: Can't not find Controller ".$Controller."!");
            $_context=new Context($context);
            Auth::check($Controller);
            $Controller=new $Controller($Controller, $action, $_context, $method);
        }catch(NotFindControllerException $e){
            echo $e->getMessage();
        }catch(NotFindActionException $e){
            echo $e->getMessage();
        }catch(NotAuthException $e){
            //header("Location: ".DEFAULT);
            echo $e->getMessage();
        }catch(NotFileFindException $e){
            echo $e->getMessage();
        }catch(Exception $e){
            echo $e->getMessage();
        }
    }
    public static function Import($File){
        /*
         * require_once 文件
         * @param:
         *      $File: 文件
         */
        if(is_file($File)){
            //echo "<br/>require_once {$File}";
            require_once($File);
            //echo " OK!";
            return true;
        }
        return false;
    }
    public static function LoadClass($ClassName){
        /*
         * 自动加载 Class
         * @param:
         *      $ClassName: 需要加载的类名
         */
        global $MF_config;
        //echo "<br/>".$ClassName;
        if(class_exists($ClassName))return;
        $T = str_replace('_', DS, $ClassName);
        if(defined('MODELDIR')&&self::Import(MODELDIR.DS.$T.'.php'))return;
        if(defined('APPDIR')&&self::Import(APPDIR.DS.$T.'.php'))return;
        if(defined('APPLIBDIR')&&self::Import(APPLIBDIR.DS.$T.'.php'))return;
        if (!empty($MF_config[$ClassName]))
            self::Import(MFROOT.DS.$MF_config[$ClassName]);
    }
    public function start(){
        $this->router->start();
    }
}
/*
 * 注册自动加载的函数
 */
spl_autoload_register(array('MF', 'loadClass'));
//-------------------------------------------------------
function url($url,$arr=array()){
    /*
     * @ 格式化URL
     * @param
     *      $url: string 如：user/login
     *      $arr: 其它参数如： array("id"=>"20")
     * @eg: 
     *      url("user/login", array("id"=>20));
     *      @return "user/login/id/20";
     */
    return Router::url($url,$arr);
}
//-------------------------------------------------------
$MF=new MF();
$MF->start();
?>
