<?php
namespace content\controller;
use common\model\Channel;
use cms\model\ContentType;
use cms\model\Material;

class addCtrl extends mainCtrl{
    function _index($ctt){
        $c_info=ContentType::record(["content_mark"=>$ctt])->getAttributes();
        $error_url=$_SERVER['HTTP_REFERER']?$_SERVER['HTTP_REFERER']:
            \self::$app->route("content/index/".$ctt);
        if(empty($c_info)){
            $this->_referer_to("内容模型不存在",$error_url);
            return ;
        }
        if(!$c_info['main_form']){
            $this->_referer_to("模型未设置表单模板",$error_url);
            return ;
        }
        $this->_assign('ct_type',$c_info);
        $this->tpl_form="form/".$c_info['main_form'];
        $html=$this->_tpl("content_input");
        $html->title="内容发布";
        $html->mate_path=W3CA_URL_ROOT.Material::mainDir();
        $html->action_url=\self::$app->route("content/save/".$ctt);
        $html->display_button=true;
        $html->output("ct".$ctt);
    }
}