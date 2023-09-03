<?php
namespace cms\controller;
use cms\model\FeatureMenu;
use cms\model\SysUser;
use w3capp\Controller;
use w3capp\W3cApp;
/**
 * 管理平台
 */
class mainCtrl extends Controller{
    var $default_url;
    var $user_info;
    function __construct(){
        $this->default_url=empty($_COOKIE['mreferer'])?'':$_COOKIE['mreferer'];
        self::$app->template()->setDefaultTplPath(W3CA_MASTER_PATH.'app/common/view/');
    }
    public function index($a=null){
        if(!empty($a)&&$a!="index"&&$a!="index.php"){
            return \W3cUI::show404();
        }
        $html=$this->_tpl("main");
        $opt_ids=array();
        if(count($this->user_info->role_options)>0){
            $opt_ids=array_keys($this->user_info->role_options);
        }
        $html->menus=FeatureMenu::get_option_tree($opt_ids,$this->user_info->role_option_type);
        $html->user_name=$this->user_info['name'];
        //echo $this->default_url;
        if($this->default_url!=""&&stripos($this->default_url, self::$app->route("main"))===false){
            $html->default_url=$this->default_url;
        }else{
            $html->default_url=self::$app->route("main/msgs");
        }
        $html->user_info=$this->user_info;
        setcookie("mreferer","",-1,"/");
        $html->output();
    }

    public function _check_operation($fun){

        if($fun=="login"||$fun=="logout"){
            return true;
        }
        if(SysUser::validate()){
            $this->user_info=SysUser::getLoginUser();
            $this->user_info->readRole();
            if(self::$app->instance==$this&&($fun=="index"||$fun=="msgs")){
                return true;
            }
        }else{
            //未登录
            self::$app->setCookie(["mreferer",$_SERVER['REQUEST_URI'],3600,"/"]);
            echo "<script type='text/javascript'>var url='".self::$app->route("main/login")."'; if(window.top!=window.self){alert('登录超时,请重登录！');window.parent.location.href=url;}
			else window.location.href=url;</script>";
            exit;
        }
        if(self::$app->admin==$this->user_info['id'])
            return true;
        $pass=false;
        $ctrl_name=str_replace("\\controller\\","/",self::$app->ctrl_name);
        if($this->user_info->role_option_type){
            //白名单规则
            if(empty($this->user_info->role_options)){
                return false;
            }
            if(preg_match($this->user_info->role_options[0], $_SERVER['REQUEST_URI'])){
                return true;
            }else{
                $ch_uri=$ctrl_name."/".$fun;
                foreach($this->user_info->role_options as $mid=>$option){
                    if($mid===0)continue;
                    if($ctrl_name==$option||stripos($option,$ch_uri)===0){
                        $pass=true;
                        return $pass;
                    }
                }
            }
        }else{
            //黑名单规则
            $pass=true;
            if(empty($this->user_info->role_options)){
                return true;
            }

            if(preg_match($this->user_info->role_options[0], $_SERVER['REQUEST_URI'])){
                return false;
            }else{
                $pass=true;
                $ch_uri=$ctrl_name."/".$fun;
                foreach($this->user_info->role_options as $mid=>$option){
                    if($mid===0)continue;
                    if(stripos($option,$ch_uri)===0){
                        $pass=false;
                        return $pass;
                    }
                }
            }
        }
        return $pass;
    }

    /**
     * 登录
     */
    public function login(){
        if(empty($_POST['name'])){
            $html=$this->_tpl("login");
            $t=time()%100000;
            $tl=5-strlen($t);
            if($tl>0){
                $t=str_repeat('0',$tl).$t;
            }
            $html->hash_code=openssl_encrypt($t.".w3capp.com", 'aes-128-cbc',self::$app->install_config['register_key'],OPENSSL_ZERO_PADDING,self::$app->install_config['register_iv']);
            $html->output();

        }else{
            if(empty($_POST['pwd'])){
                return $this->_referer_to("密码不能为空！");
            }
            if(empty($_POST['hash_code'])){
                return $this->_referer_to("数据请求错误！");
            }
            $encrypted = $_POST['hash_code'];
            $decrypted = openssl_decrypt($encrypted, 'aes-128-cbc', self::$app->install_config['register_key'], OPENSSL_ZERO_PADDING , self::$app->install_config['register_iv']);
            if(abs(time()%100000-$decrypted)>30){
                return $this->_referer_to("请求超时！");
            }
            $user=SysUser::validate($_POST['name'],$_POST['pwd']);
            if($user){
                $user->login();
                $this->_referer_to(null,self::$app->route("main"));
                exit();
            }else{
                return $this->_referer_to("用户不存在或密码错误！");
            }
        }
    }

    /**
     * 退出
     */
    public function logout(){
        SysUser::getLoginUser()->logout();
        return $this->_referer_to(null,self::$app->route("main/login"));
    }
    /**
     * 首界面
     */
    public function msgs(){
        $p=$this->_tpl("main_msgs");
        $p->sys_data=array("dbvs"=>SysUser::myAdapter()->db()->dbVersoin(),
            "system"=>php_uname()
        );
        $p->output();
    }
}
