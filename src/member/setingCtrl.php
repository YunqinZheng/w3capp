<?php
namespace member\controller;

use member\model\Member;
use common\controller\MemberEnterCtrl;

class setingCtrl extends MemberEnterCtrl{
	function index($a=null){
		if(false==empty($_POST)){
			if(self::check_form_hash($_POST['form_hash'],30)==false){
                return $this->_json_return(1,"您提交的表单无效，请刷新重试。");
            }
			$mbr=Member::loginMember();
			if(!$mbr){
                return $this->_json_return(1,"login error");
            }
			$data=$_POST;
			if(!preg_match("/.+@.+\..+/", $_POST['email'])){
				return $this->_json_return(1,"邮箱格式不对");
			}
			if($mbr['email']!=$_POST['email']){
				$data['email_checked']=0;
				$data['email']=$_POST['email'];
				$member=Member::record(["email"=>$_POST['email']]);
				if($member){
					return $this->_json_return(1,"邮箱'{$_POST['email']}'已经被注册过。");
				}
			}
			if($_POST['password']){
				$mbr->setPassword($_POST['password']);
			}
			unset($data['password']);
            $mbr->setAttributes($data);
			if($mbr->save()){
			    $mbr->storeLogin();
                return $this->_json_return(0,"修改信息已保存!","");
			}else{
                return $this->_json_return(1,"信息保存失败!","");
			}
		}else{
			$html=$this->_tpl("member/seting");
			$html->title="个人设置";
			$html->output();
		}
	}
}