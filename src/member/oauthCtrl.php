<?php
namespace member\controller;
use helper\qqAPI\qqConnectAPI;
use member\model\Member;
use member\model\OAuth;
class oauthCtrl extends mainCtrl {
    function qqConnect(){
        //qq登录
        $qq_appid=Member::configInfo('qq_appid');
        $qq_appkey=Member::configInfo('qq_appkey');
        $qq_con=new qqConnectAPI($qq_appid,$qq_appkey);
        if($qq_con->checkConfig()){
            $state=self::_form_hash();
            $qq_con->getCode($state);
        }else{
            $this->_message("QQ登录未设置!",'alert');
        }
    }
    function qqLogin(){
        //QQ 回调
        if(empty($_GET['state'])||self::check_form_hashe($_GET['state'],800)){
            $this->_message('参数错误','error');
        }
        $qq_appid=Member::configInfo('qq_appid');
        $qq_appkey=Member::configInfo('qq_appkey');
        $qq_con=new qqConnectAPI($qq_appid,$qq_appkey);
        if(empty($_GET['usercancel'])&&$_GET['code']){
            $token=$qq_con->getToken($_GET['code']);
            if($token){
                $info=$qq_con->getUserInfo();
                $this->_assign('login_type',"QQ");
                $this->oauth_result($info['openid'],$qq_appid,$info['nickname'],$token,$info['figureurl_qq_2'],'qq');

            }else{
                $this->_message('token错误','error');
            }
        }
    }
    private function oauth_result($openid,$appid,$nickname,$token,$image,$type){

        $auth_exist=OAuth::record(['open_id'=>$openid,'appid'=>$appid]);//$this->_m(':OAuth')->firstRow("member_id,id",['open_id'=>$openid,'appid'=>$appid]);
        $member_exist=false;
        if(empty($auth_exist)||$auth_exist['member_id']==0){
            $info_m=['open_id'=>$openid,'type'=>$type,'appid'=>$appid,'nickname'=>$nickname,'access_token'=>$token,'image'=>$image,'token_time'=>time()];
            $info=Member::info();
            if(false==empty($info['id'])){
                $info_m['member_id']=$info['id'];
                $member_exist=true;
            }
            $auth_exist->setAtrributes($info_m);
            $auth_exist->save();
            if($member_exist){
                $this->_message('绑定成功！','right');
            }else{
                $this->_assign('relative_info',$info_m);
                $this->_tpl('member/oauth_regist')->output();
            }
        }else{
            Member::loginMember($auth_exist['member_id']);
            $this->_message("欢迎回来!",'right',\ctrl_url(['member']));
        }
    }
    function weixin(){
        $wx_appid=Member::configInfo('wx_appid');
        $wx_appkey=Member::configInfo('wx_appkey');
        if(empty($wx_appid)){
            $this->_message('微信对接没设置！','alert');
        }
        $wx_login=new \w3c\helper\weixinAPI\login($wx_appid,$wx_appkey);
        $wx_login->getCode($_SERVER['REQUEST_SCHEME']."://".$_SERVER['HTTP_HOST'].$this->_routing_c("wxLogin"),self::_form_hash());
    }
    function wxLogin(){
        //微信回调
        if(empty($_GET['state'])||self::check_form_hash($_GET['state'],800)){
            $this->_message('参数错误！','error');
        }
        if(empty($_GET['code'])){
            $this->_message('授权失败！','error');
        }
        $wx_appid=Member::configInfo('wx_appid');
        $wx_appkey=Member::configInfo('wx_appkey');
        $wx_login=new \w3c\helper\weixinAPI\login($wx_appid,$wx_appkey);
        $wx_user=$wx_login->getUserInfo($_GET['code']);
        if(empty($wx_user)){
            $this->_message('授权信息错误！','error');
        }
        $this->_assign('login_type',"微信");
        $this->oauth_result($wx_user['openid'],$wx_appid,$wx_user['nickname'],$wx_user['access_token'],$wx_user['headimgurl'],'weixin');
    }
    function weibo(){
        $wb_client_id=Member::configInfo('wb_client_id');
        $wb_client_secret=Member::configInfo('wb_client_secret');
        if(empty($wb_client_id)){
            $this->_message('微博对接没设置！','alert');
        }
        $wb_login=new \w3c\helper\weiboAPI\login($wb_client_id,$wb_client_secret,$_SERVER['REQUEST_SCHEME']."://".$_SERVER['HTTP_HOST'].\ctrl_url(['member','oauth',"wbLogin"],['state'=>self::_form_hash()]));
        $wb_login->getCode();
    }
    function wbLogin(){
        //微博回调
        if(empty($_GET['state'])||self::check_form_hash($_GET['state'],800)){
            $this->_message('参数错误！','error');
        }

        $wb_client_id=Member::configInfo('wb_client_id');
        $wb_client_secret=Member::configInfo('wb_client_secret');
        $wb_login=new \w3c\helper\weiboAPI\login($wb_client_id,$wb_client_secret,$_SERVER['REQUEST_SCHEME']."://".$_SERVER['HTTP_HOST'].\ctrl_url(['member','oauth',"wbLogin"],['state'=>$_GET['state']]));
        $wb_user=$wb_login->getUserInfo();
        if(empty($wb_user)){
            $this->_message('授权信息错误！','error');
        }
        $this->_assign('login_type',"微博");
        $this->oauth_result($wb_user['id'],$wb_client_id,$wb_user['screen_name'],$wb_user['access_token'],$wb_user['avatar_large'],'weibo');
    }
}