<?php
namespace content\model;
class goods extends \W3cRecord{
    static public function recordName(){
        return "content_goods";
    }
    static public function recordRule(){
        return [[['id','views','channel_id','deprecated','dateline'],"integer"],        
                [['title','image'],"string",200],
                [['keywords','description'],"string",250]];
    }
    static public function propertyDesc(){
        return array (
          'title' => '商品名称',
          'channel_id' => '所属栏目',
          'keywords' => '关键字',
          'description' => '描述',
          'dateline' => '添加时间',
          'deprecated' => '删除标记',
          'views' => '查看数',
          'image' => '图片',
        );
    }
    //后台表单
    function defaultForm($input){
        $data = array("title"=>$input["title"],"channel_id"=>$input["channel_id"],"keywords"=>$input["keywords"],"description"=>$input["description"],"dateline"=>empty($input["dateline"])?time():strtotime($input["dateline"]),"views"=>$input["views"],"image"=>$input["image"]);
        
        return $data;
    }
    //会员表单
    function memberForm($member_info,$input){
        $data = array("title"=>$input["title"],"channel_id"=>$input["channel_id"],"dateline"=>time());
        
        return $data;
    }
}