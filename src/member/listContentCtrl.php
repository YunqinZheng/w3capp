<?php
namespace member\controller;
use cms\model\ContentType;

class listContentCtrl extends contentCtrl {
    function index($identify=null){

        $content_model=ContentType::contentRecord($identify);
        $html=$this->_tpl("member/content_list");
        //array("deprecated"=>0,"member_id"=>$this->login_member['id']), array('dateline'=>"desc"), 20,
        $html->list_data=$content_model->memberPage($this->login_member['id'],intval($_GET['page']));
        $html->content_mark=$identify;
        $html->output();
    }
    //评论
    function comment(){
        $content_model=ContentType::contentRecord("comment");
    }
}