<?php
class Auth{
    /*
        deny role1,role2...
        allow role1,role2...
    */
    public static function register($role, $info){
        $_SESSION["Auth"] = $role;
        $_SESSION["AuthInfo"] = $info;
    }
    public static function check($obj){
        global $Auth_config;
        if(empty($Auth_config[$obj]))return;
        $auth = $Auth_config[$obj];
        $is_deny = false;
        foreach(explode(" ", $auth) as $auth){
            if($auth == "deny"){$is_deny = true;continue;}
            if($auth == "allow"){$is_deny = false;continue;}
            if($is_deny){
                if($auth == "all"||!isset($_SESSION["Auth"])){throw new NotAuthException('NotAuthException: Not auth to the '.$obj.'!');}
                foreach(explode(",", $auth) as $deny){
                    if($_SESSION["Auth"] == $deny){
                        throw new NotAuthException('NotAuthException: Not auth to the '.$obj.'!');
                    }
                }
            }else{
                if($auth == "all"){return true;}
                if(!isset($_SESSION["Auth"])){throw new NotAuthException('NotAuthException: Not auth to the '.$obj.'!');}
                $is_allow = false;
                foreach(explode(",", $auth) as $allow){
                    if($_SESSION["Auth"] == $allow){
                        $is_allow = true;
                        //throw new NotAuthException('NotAuthException: Not auth to the '.$obj.'!');
                    }
                }
                if(!$is_allow)throw new NotAuthException('NotAuthException: Not auth to the '.$obj.'!');
            }
        }
    }
    public static function destory(){
        unset($_SESSION["Auth"]);
        unset($_SESSION["AuthInfo"]);
    }
    public static function getInfo(){
        return isset($_SESSION["AuthInfo"])?$_SESSION["AuthInfo"]:null;
    }
}
?>
