<?php
namespace common\controller;
use member\model\Member;

class MemberEnterCtrl extends W3cEnterCtrl{

    /**
     * 表单是否有效
     * @return bool
     */
    protected function checkForm(){
        list($form_hash,$form_time)=explode(",",$_POST['form_hash']);
        if($form_time<11)return false;
        return self::check_form_hash($form_hash,$form_time<150?$form_time-10:150);
    }
	/**
	 * 检查会员是否有权限
	 */ 
	public function _check_operation($funName){
	    $this->login_member=Member::loginMember();
		if($this->login_member==null){
		    if(empty($_POST)){
                $this->_referer_to(null,\self::$app->route("member/main/login",array('referer'=>$_SERVER['REQUEST_URI'])));
            }else{
                $this->_json_return(1,"",\self::$app->route("member/main/login"));
            }
            return true;
		}else if($this->login_member->isForbid()){
            return $this->_show_message("您已被禁止登录！","error",[["href"=>\self::$app->route("member/main/logout"),"text"=>"确定"]]);
        }
		return parent::_check_operation($funName);
	}
}
