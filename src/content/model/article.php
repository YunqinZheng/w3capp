<?php
namespace content\model;
class article extends \W3cRecord{
    static public function recordName(){
        return "content_article";
    }
    static public function recordRule(){
        return [
            [["channel_id"],"require"],
            [['id','views','channel_id','deprecated','dateline','member_id','sort_id'],"integer"],
            [['title'],"string",200],
            [['keywords'],"string",450],
            [['description'],"string",2000],
            [['author'],"string",50],
            [['article_text'],"string"],
            [['pic'],"string",100]];
    }
    static public function propertyDesc(){
        return array (
            'id'=>'id',
        'title' => '标题',
        'article_text' => '文章内容',
        'member_id' => '会员ID',
        'channel_id' => '所属栏目',
        'keywords' => '关键字',
        'description' => '描述',
            'pic' => '封面',
        'dateline' => '发布时间',
        'deprecated' => '删除标记',
        'author' => '作者名称',
        'views' => '查看数',
            'sort_id' => '排序id',
        );
    }
    function memberPage($member,$page){

    }
    //function get_view_url(){}
    function defaultForm($input){
        $data= array("title"=>$input["title"],
            "article_text"=>$input["article_text"],
            "member_id"=>$input["member_id"],
            "channel_id"=>$input["channel_id"],
            "keywords"=>$input["keywords"],
            "description"=>$input["description"],
            "dateline"=>strtotime($input["dateline"]),
            "author"=>$input["author"],
            "pic"=>$input["pic"],
            "views"=>$input["views"]);
        if(empty($data['dateline']))
            $data['dateline']=time();
        return $data;
    }
    function memberForm($member_info,$input){
        $data= array("title"=>$input["title"],"dateline"=>time(),"article_text"=>$input["article_text"],"member_id"=>$member_info['id'],"deprecated"=>$input["deprecated"]);

        return $data;
    }
    //后台查询条件
    function defaultCondition($param){
        if(empty($param['search']))
            return "";
        return ['like'=>['title',$param['search']]];
    }
    //会员中心查询条件
    function memberCondition($param){
        if(empty($param['search']))
            return "";
        return ['like'=>['title',$param['search']]];
    }
}