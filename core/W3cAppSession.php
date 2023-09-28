<?php
namespace w3capp;
use w3capp\helper\Str;
class W3cAppSession{
    static $session_id;
	var $sskey;
    public function __construct($key){
		$this->sskey=$key;
    }
    public function start($session_id){

        if(self::$session_id)return self::$session_id;
        session_name("w3cs".$this->sskey);
        $sn=session_name();
        if($session_id){
            self::$session_id=$session_id;
            session_id($session_id);
            if(false==session_start()){
                echo "session_start fails!";
                return false;
            }
        }else{
            if(empty($_COOKIE[$sn])){
                self::$session_id=md5(Str::guid());
                session_id(self::$session_id);
                if(false==session_start()){
                    echo "session_start fails!";
                    return false;
                }
            }else{
                session_start();
                self::$session_id=session_id();
            }
        }
        return self::$session_id;
    }
    public function getSessionId(){
        return self::$session_id;
    }
    public function __set($key,$val){
        $_SESSION[$key]=$val;
    }
    public function __get($key){
        return $_SESSION[$key];
    }
    function __isset($key){
        return empty($_SESSION[$key]);
    }
}