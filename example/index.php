<?php
header("Content-Type: text/html;charset=UTF-8");
define('DS', DIRECTORY_SEPARATOR);
$config=require_once('config.php');
$Auth_config=array();
if(is_file('auth.php'))$Auth_config=require_once('auth.php');
//---------------------------------
define("DBNAME","you dbname");
define("DSN","mysql:host=localhost;dbname=".DBNAME);
define("DUN","dbpasswd");
define("DPW","dbuser");
//--------------------------------
define('RUNROOT',dirname(__FILE__));
define('APPROOT',dirname($_SERVER['SCRIPT_NAME']));
define('APPDIR',RUNROOT.DS.$config['app']);
define('CONTROLLERDIR',RUNROOT.DS.$config['Controller']);
define('VIEWDIR',RUNROOT.DS.$config['view']);
define('MODELDIR',RUNROOT.DS.$config['Model']);
define('ONLYONEVIEWDIR',false);
define('MFDIR',$config['MFDIR']);
define('APPLIBDIR',$config['lib']);
session_start();
require_once(MFDIR.DS.'library'.DS.'MF.php');
?>
