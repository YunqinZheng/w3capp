<?php
namespace member\controller;

use member\model\Member;
use member\model\MemberGroup;
use common\controller\W3cEnterCtrl;
class mainCtrl extends W3cEnterCtrl{
    function index($a=null){
        if(!empty($a)&&$a!="index"&&$a!="index.php"){
            return \W3cUI::show404();
        }
        $html=$this->_tpl("member/center");
        $html->title="个人中心";
        $login_info=Member::info();
        $group_info=MemberGroup::groupInfo($login_info['groupid']);
		$login_info['group_name']=$group_info['name'];
		$html->login_member=$login_info;
        $html->publish_menu=$group_info['content_marks'];
        $html->output();
    }
    protected function _member_view($id){
        $html=$this->_tpl("member/view");
        echo $id."||\n";
        $mbr=Member::record(['id'=>$id]);
        if(empty($mbr))
            $mbr=Member::record(['name'=>$id]);
        if(empty($mbr)){
            return \W3cUI::show404();
        }
        if($mbr['id']==$id){
            return $this->_referer_to(null,\self::$app->route('member/'.$mbr['name']));

        }
        $html->title=$mbr['name']."--会员信息";
        $html->view_member=$mbr;
        $html->output();
    }
	public function _action_unfound($fun,$arg){
		if(!$arg){
		    if($_GET['token']){
		        if(Member::loginByToken($_GET['token'])){
                    $this->_referer_to(null,\self::$app->route('member/index'));
                }
		        return ;
            }
			$this->_member_view($fun);
			return;
		}
		parent::_action_unfound($fun,$arg);
	}
    function login_action(){
        if(empty($_POST['username'])||empty($_POST['password'])){
            return $this->_json_return(1,"请填写登录名和密码。");
        }
        $dft=$_POST['td'.self::_preCookie()];
        if($dft==0||$dft>12*3600||self::check_form_hash($_POST['form_hash'],60+intval($dft))==false){
            return $this->_json_return(1,"提交的表单无效，请刷新重试。");
        }
        $l=Member::login($_POST['username'], $_POST['password']);
        if($l==2){
            $info=Member::info();
            if($info['groupid']==99){
                Member::logout();
                return $this->_json_return(1,"用户已经被禁用");

            }else{
                return $this->_json_return(0,$info['name'].",欢迎回来.",["url"=>
                    $_POST['referer']&&strpos($_POST['referer'],'member/login')===false
                    &&strpos($_POST['referer'],'member/logout')===false?$_POST['referer']:\self::$app->route("member/index")]);
            }
        }else{
            return $this->_json_return(1,"用户'{$_POST['username']}'密码不正确.");
        }
    }
    function login(){
		
        $html=$this->_tpl("member/login");
		$html->referer=empty($_GET['referer'])?"":$_GET['referer'];
        $html->title="会员登录";
        $html->output();
    }
    function logout(){
        Member::logout();
        return $this->_show_message("您已经成功退出！","right",array(array("text"=>"返回","href"=>$_SERVER['HTTP_REFERER']),array("text"=>"首页","href"=>W3CA_URL_ROOT)),false,0);
    }

    function register($step=0){
        if(!Member::configInfo("open_regist")){
            $this->_message("注册已经关闭","alert");
        }
        if(false==empty($_POST)){
            $dft=$_POST['td'.self::_preCookie()];
            if($dft==0||$dft>1800||self::check_form_hash($_POST['form_hash'],20+$dft)==false){
                return $this->_json_return(1,"您提交的表单无效，请刷新重试。");
            }
            if(empty($_POST['username']))
            {
                return $this->_json_return(1,"用户名称没填写");
            }
            if(empty($_POST['appid'])&&empty($_POST['password'])){
                return $this->_json_return(1,"密码没填写");
            }
            if(empty($_POST['email'])){
                return $this->_json_return(1,"email没填写");
            }
            $black_names=explode(",", Member::configInfo("black_names"));
            foreach($black_names as $name){
                if(stripos(",".$_POST['username'],$name)){
                    return $this->_json_return(1,"用户名使用了系统禁用的关键字，请修改。");
                }
            }
            if(preg_match("/[\[\]<>'\"\(\)@]/", $_POST['username'])){
                return $this->_json_return(1,"用户名不能用\"><')(@\"等字符");
            }
            if(!preg_match("/.+@.+\..+/", $_POST['email'])){
                return $this->_json_return(1,"邮箱格式不对");
            }
            if(false==empty($_POST['appid'])){
                if(empty($_POST['password']))
                    $_POST['password']=uniqid();
            }else if($_POST['password']!=$_POST['password2'])
            {
                return $this->_json_return(1,"确认密码不一致");
            }
            if($_POST['agree']!=1){
                return $this->_json_return(1,"您未同意本站会员的相关协议");
            }
            $user=Member::record(["or"=>["name"=>$_POST['username'],"email"=>$_POST['email']]]);
            if($user['name']==$_POST['username']){
                return $this->_json_return(1,"用户名'{$_POST['username']}'已经被注册过。");
            }
            if($user['email']==$_POST['email']){
                return $this->_json_return(1,"邮箱'{$_POST['email']}'已经被注册过。");
            }
            $mb=new Member(array("name"=>$_POST['username'],"regdate"=>time(),"regip"=>$_SERVER ['REMOTE_ADDR'],"groupid"=>100, "password"=>$_POST['password'], "email"=>$_POST['email'],"auth_key"=>""));
            if($mb->save()){
                Member::login($_POST['username'], $_POST['password']);
                if(false==empty($_POST['app_id'])){
                    $this->_m(":OAuth")->updateData(['member_id'=>$mb->primary()],['appid'=>$_POST['appid'],'open_id'=>$_POST['open_id']]);
                }
                return $this->_json_return(0,"",["url"=>\self::$app->route("member/index")]);
            }else{
                return $this->_json_return(1,"注册失败。",$mb->getError());
            }
        }
        $html=$this->_tpl("member/regist".intval($step));
        $html->title="会员注册";
        $html->output();
    }
    /**
     * 登录权限检查
     */
    public function _check_operation($funName){
        if($funName=="info"||$funName=="avatar")
            return true;
        switch (Member::login("", "")) {
            case 0:
                if($funName=="register"||$funName=="login"||$funName=="login_action")
                    return true;
            case 1:
                //登录后台，没有登录会员
                return false;
            case 2:
                //转到个人中心
                if($funName=="register"||$funName=='login'||$funName=="build"||$funName="login_action"){
                    $this->_referer_to(null,\self::$app->route("member/index"));
                    return true;
                }
                if($funName=="logout")
                    return true;
                $info=Member::info();
                if($info['groupid']==99){
                    //禁用会员
                    Member::logout();
                    $this->_message($info['name'].":您已经被禁止登录");
                    return false;
                }
                return true;
                break;
            default:
                return false;
                break;
        }

    }
}