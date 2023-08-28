<?php
namespace cms\controller;
use w3c\helper\EmailSmtp;
class emailCtrl extends mainCtrl{
	protected $server="smtp.ym.163.com";
	protected $server_user="system@w3capp.com";
	protected $server_pwd="pwd2015";
	function seting(){

		if(false==empty($_POST)){
		    if(EmailSmtp::save_seting($_POST)){
                return $this->_referer_to("设置已经保存!","","right");
            }else{
                return $this->_referer_to("设置保存出错!");
            }

		}
		$info=EmailSmtp::get_seting_info();
        $html=$this->_tpl("web/email_seting");
		$html->info=$info;
		$html->output();
	}
	function post_test(){
		$info=EmailSmtp::get_seting_info();
		$email=new EmailSmtp($info['smtp_user'],$info['smtp_pwd'],$info['smtp'],$info['smtp_port']);
		$email->debug=true;
		$email->add_body("text/html", '<html><body><div><img src="http://www.w3capp.com/static/image/logo.png" />'.$_POST['message'].'</div></body></html>');
		$email->sendmail($_POST['receive'],$info['smtp_user'],"测试-".date("H:i:s"));
	}
}
