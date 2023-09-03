<?php
namespace content\controller;
use content\model\product;
use helper\ContentForm;
use content\model\article;
use cms\model\ContentType;

class saveCtrl extends mainCtrl{
    function article_sort(){
        $ids=explode(",",$_POST['ids']);
        $data_list=[];
        foreach ($ids as $id){
            $data_list[]=[["sort_id"=>intval($_POST['sort_'.$id])],["id"=>$id]];
        }
        $rs=article::batchUpdate($data_list);
        return $this->_json_return(0,"",$rs);
    }
    function product_sort(){
        $ids=explode(",",$_POST['ids']);
        $data_list=[];
        foreach ($ids as $id){
            $data_list[]=[["sort_id"=>intval($_POST['sort_'.$id])],["id"=>$id]];
        }
        $rs=product::batchUpdate($data_list);
        return $this->_json_return(0,"",$rs);
    }
    //更新排序
    function sort(){
        $ids=explode(",",$_POST['ids']);
        $data_list=[];
        foreach ($ids as $id){
            $data_list[]=[["sort_id"=>intval($_POST['sort_'.$id])],["id"=>$id]];
        }
        $rs=call_user_func(array("content\\model\\".$_POST['mod'],"batchUpdate"),$data_list);
        return $this->_json_return(0,"",$rs);
    }
    function _index($ctt=''){
        if(!$ctt){
            $this->_message('模型参数不正确');
            return ;
        }
        $content_type=ContentType::record(["content_mark"=>$ctt]);
        $content_record=$content_type->contentRecord($ctt,$_POST['content_id']);

        $data=$content_record->defaultForm($_POST);
        if(empty($data)){
            $this->_message( "提交内容不能为空");
            return ;
        }
        if(array_key_exists("dateline",$data)&&$data['dateline']==''){
            $data['dateline']=0;
        }
        if(array_key_exists("views",$data)&&$data['views']==''){
            $data['views']=0;
        }

        $content_record->setAttributes($data);
        try{
            $content_record->save();
            $content_id=$content_record->primary();
        }catch (\Exception $e){
            $this->_message($e->getMessage(),"error");
        }

        if($_POST['content_id']>0){
            $content_id=$_POST['content_id'];
            $msg="内容修改";
        }else{
            $msg="内容添加";
        }
        if($content_id){
            \W3cUI::message($msg."成功","right", array(
                array("href"=>\self::$app->route("content/view/".$ctt."/".$content_id),"text"=>"查看","target"=>"_blank"),
                array("href"=>\self::$app->route("content/add/".$ctt),"text"=>"继续添加","target"=>""),
                array("href"=>\self::$app->route("content/index/".$ctt),"text"=>"管理","target"=>""),
                array("href"=>\self::$app->route("content/edit/".$ctt."/".$content_id),"text"=>"编辑","target"=>"")));
        }else{
            $this->_message($msg."出错");
        }
    }
}