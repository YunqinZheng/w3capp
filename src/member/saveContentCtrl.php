<?php
namespace member\controller;

use member\model\MemberGroup;

class saveContentCtrl extends ContentCtrl{
    function index($identify=null){
        if(!MemberGroup::hasContent($this->login_member['groupid'],$identify)){
            return $this->_referer_to("内容模型不存在或者没有权限");
        }
        $dft=$_POST['pf'.$this->cookie_pre];
        if($dft==0||$dft>5*3600||self::check_form_hash($_POST['form_hash'],60+intval($dft))==false){
            return $this->_referer_to("提交的表单无效，请刷新重试。");
        }
        $content_mode=self::_lib("web/ContentType")->content_obj($identify);

        $data=$content_mode->memberForm($this->login_member);
        if(empty($data)){
            return $this->_referer_to("不能提交空白内容");
        }
        if(method_exists($content_mode, "data_check")){
            $checked_str=$content_mode->data_check($data);
            if($checked_str){
                return $this->_referer_to($checked_str);
            }
        }else{
            if(array_key_exists("dateline",$data)&&$data['dateline']==''){
                $data['dateline']=0;
            }
            if(array_key_exists("views",$data)&&$data['views']==''){
                $data['views']=0;
            }

        }
        $data['member_id']=$this->login_member['id'];
        $content_id=intval($_POST['c_id_'.$this->cookie_pre]);
        if($content_id>0&&$content_mode->updateData($data,array('id'=>$content_id))){
            $content_id=$_POST['content_id'];
            $msg="内容修改";
        }else{
            $content_id=$content_mode->addData($data);
            $msg="内容添加";
        }
        $goback_url=$_SERVER['HTTP_REFERER']?$_SERVER['HTTP_REFERER']:\ctrl_url(['member','Content',"index",$identify]);
        if($content_id){
            return $this->_show_message($msg."成功","right", array(
                array("href"=>\ctrl_url(['member','Content',"view",$identify,$content_id]),"text"=>"查看","target"=>"_blank"),
                array("href"=>\ctrl_url(['member','publish',"index",$identify]),"text"=>"继续添加","target"=>""),
                array("href"=>\ctrl_url(['member','Content',"index",$identify]),"text"=>"管理","target"=>""),
                array("href"=>$goback_url,"text"=>"返回","target"=>"")));
        }else{
            return $this->_show_message($msg."出错","errer", array(
                array("href"=>$goback_url,"text"=>"返回","target"=>""),
                array("href"=>\ctrl_url(['member','Content',"index",$identify]),"text"=>"管理","target"=>"")));
        }
    }

    //保存评论
    function comment(){
        $dft=$_POST['cf'.$this->cookie_pre];
        if($dft==0||$dft>3600||self::check_form_hash($_POST['form_hash'],60+intval($dft))==false){
            return $this->_json_return(1,"提交的表单无效，请刷新重试。");
        }
        $content_mode=self::_lib("web/ContentType")->content_obj("comment");
        $data=$content_mode->memberForm($this->login_member);
        if($content_mode->saveInfo($data)===false){
            return $this->_json_return(1,'保存失败！');
        }
        return $this->_json_return(0,'评论成功！');
    }
}