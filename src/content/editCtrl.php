<?php
namespace content\controller;
use common\model\Channel;
use cms\model\ContentType;
use cms\model\Material;

class editCtrl extends mainCtrl{
    function _index($ctt,$aid){
        $aid=intval($aid);
        $content_type=ContentType::record(["content_mark"=>$ctt]);
        $c_info=$content_type->getAttributes();

        $url=$_SERVER['HTTP_REFERER']?$_SERVER['HTTP_REFERER']:
            \W3cApp::route("content/index/".$ctt);
        if(empty($c_info)){
            return $this->_referer_to("内容模型不存在",$url);
        }
        $content_mode=$content_type->contentRecord($ctt,$aid);

        if(empty($content_mode->primary())){
            $this->_message("未指定要编辑的内容ID");
            return ;
        }
        $this->_assign('ct_type',$c_info);
        $this->tpl_form="form/".$c_info['main_form'];
        $this->content_type=$ctt;
        $html=$this->_tpl("content_input");
        $html->title="内容编辑";
        $html->content_id=$aid;
        $html->mate_path=W3CA_URL_ROOT.Material::mainDir();
        $html->action_url=\W3cApp::route("content/save/".$ctt);
        $html->edit_data=$content_mode->getAttributes();
        $html->display_button=true;
        $html->output("ct".$ctt);
    }
}