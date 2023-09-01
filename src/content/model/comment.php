<?php
namespace content\model;
class comment extends \W3cRecord{
    static public function recordName(){
        return "content_comment";
    }
    static public function recordRule(){
        return [[['member_id','article_id','add_time','support'],"integer"],
            [['comment'],"string",2000]];
    }
    static public function propertyDesc(){
        return array (
            'id' => 'id',
            'member_id' => '会员ID',
            'article_id' => '文章id',
            'add_time' => '评论时间',
            'comment' => '评论',
            'support' => '点赞',
        );
    }
    //function get_view_url(){}
    //默认表单
    static function defaultForm($input){
        $data= array("member_id"=>$input["member_id"],"add_time"=>empty($input["add_time"])?time():strtotime($input["add_time"]),"comment"=>$input["comment"],"support"=>intval($input["support"]));

        return $data;
    }
    //会员表单
    static function memberForm($member_info,$input){
        $data= array("member_id"=>$member_info['id'],"add_time"=>time(),"comment"=>$input["comment"],"article_id"=>$input['article_id']);

        return $data;
    }
    //后台查询条件
    static function defaultCondition($param){
        if(empty($param['search']))
            return "";
        return ['like'=>['comment',$param['search']]];
    }
    //会员中心查询条件
    static function memberCondition(){
        if(empty($param['search']))
            return "";
        return ['like'=>['comment',$param['search']]];
    }
}