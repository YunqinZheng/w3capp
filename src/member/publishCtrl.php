<?php
namespace member\controller;
use common\model\Channel;
use content\model\comment;
use cms\model\ContentType;
use member\model\MemberGroup;

/**
 * Class publish
 * @package member\controller
 * 发布内容界面
 */
class publishCtrl extends contentCtrl{
    function comment(){
        if(!MemberGroup::hasContent($this->login_member['groupid'],"comment")){
            return $this->_json_return(1,"没有发评论的权限");
        }
        if(empty($_POST['form_hash'])){
            return $this->_json_return(1,"表单不正确！");
        }

        if($this->checkForm()==false){
            return $this->_json_return(1,"请求过期，请刷新重试！");
        }
        $cmt=new comment(comment::memberForm($this->login_member));
        if($cmt->save()){
            $this->_assign("comment",array_merge($this->login_member->getAttributes(),$cmt->getAttributes()));
            $this->_tpl("common/comment")->output();
        }else{
            return $this->_json_return(1,"发布失败！");
        }
    }
    function edit($identify,$edit_id){
        if(empty($edit_id)){
            return $this->_referer_to("没有编辑ID");
        }
        $this->index($identify,$edit_id);
    }
    function index($identify,$edit_id=0){
        if(!MemberGroup::hasContent($this->login_member['groupid'],$identify)){
            return $this->_referer_to("内容模型不存在或者没有权限");
        }

        if($this->checkForm()==false){
            return $this->_referer_to("请求过期，请刷新重试！");
        }
        $cid=intval($_GET['channel']);
        $content_model=ContentType::contentRecord($identify);
        $modelinfo=ContentType::firstAttr(["content_mark"=>$identify]);
        if($modelinfo['member_form']==""){
            return $this->_referer_to($modelinfo['ch_name']."模型未设置会员模板");
        }
        $channel=new Channel();
        $chlist=$channel->channelsOfFrame($identify,1);
        //if(empty($chlist)){
        //    $this->_referer_to("没有设置频道栏目");
        //    return ;
        //}
        \self::$app->template()->getTplConst("FORM_TPL",$modelinfo['member_form']);
        \self::$app->template()->getTplConst("FORM_MODEL",$identify);
        \self::$app->template()->getTplConst("FORM_NAME",$modelinfo['type_name']);
        $html=$this->_tpl("Member/content_publish");
        $html->content_model=$content_model;
        $html->default_channel=$cid;
        $html->channels=$chlist;
        if($edit_id){
            $html->edit_data=$content_model->fetchInfo($edit_id);
            $html->title='编辑'.$modelinfo['type_name'];
        }else{
            $html->title='发布'.$modelinfo['type_name'];
        }
        $html->output($identify);
    }
}