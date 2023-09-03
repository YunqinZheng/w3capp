<?php
namespace cms\model;
use common\model\SysUserRecord;
use common\model\SysUserRoleRecord;
use w3capp\W3cApp;
class SysUser extends SysUserRecord{
    static protected $login_user;
    var $role_name;
    var $role_options;
    var $role_option_type;
    static function search($name,$pagei=1){
        $where=array();
        if($name){
            $where=['like'=>['name'=>$name]];
        }
        $page=self::adaptTo($where)->limit(25,$pagei)->orderBy(['id'=>"desc"]);
        return $page->selectAll(true);
    }
    static function getLoginUser(){
        if(empty(self::$login_user)){
            if(self::$app->startSession()==false){
                die("session error");
            }
            if(empty(self::$app->getSession()->sys_user_id)){
                self::$login_user=new self();
            }else{
                self::$login_user=new self(['id'=>self::$app->getSession()->sys_user_id]);
            }
        }
        return self::$login_user;
    }
    function hasSpecifyRights($specify){
        if(empty($this->specify_rights)){
            return false;
        }
        if(strpos($this->specify_rights,$specify)===false){
            return false;
        }
        return true;
    }
    public function setPassword($pwd){
        $this->pwd_hash=dechex(time()%100000);
        $this->pwd=md5($pwd.$this->pwd_hash);
        return $this;
    }
    static function validate($username="",$password=""){
        $user=self::getLoginUser();
        if(!$username&&$user['id']){
            return true;
        }
        if($user['id']&&$user['name']!=$username){
            return false;
        }
        $user=self::record(["name"=>$username]);
        if(empty($user['id'])){
            return false;
        }
        if(empty($user['pwd'])&&empty($user['pwd_hash'])){
            return $user;
        }
        if($user['pwd']==md5($password.$user['pwd_hash'])){
            return $user;
        }
        return false;
    }
    public function readRole(){

        if($this->roles){
            $rls=explode(",",$this->roles);
            $role=SysUserRoleRecord::firstAttr(['id'=>$rls[0]]);
            $this->role_name=$role['role_name'];
            $this->role_options=unserialize($role['options']);
            $this->role_option_type=$role['opt_type'];
        }
    }
    function login(){
        if(self::$app->startSession()==false){
            die("session error");
        }
        $this->readRole();
        self::$app->getSession()->sys_user_id=$this->id;
    }

    function logout(){
        if(self::$app->startSession()==false){
            die("session error");
        }
        self::$app->getSession()->sys_user_id="";
        //$_SESSION['sys_user_id']="";
        self::$login_user=null;
    }
}