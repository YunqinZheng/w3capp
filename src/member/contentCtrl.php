<?php
namespace member\controller;
use common\controller\MemberEnterCtrl;
use common\model\Channel;
use cms\model\ContentType;

class contentCtrl extends MemberEnterCtrl{
    /**
     *  默认列表页
     */
    function index($identify=null){

    }

    /**
     * 评论列表
     */
    function comment(){

    }
    function _action_unfound($fun,$args){
        $this->index($fun,empty($args[0])?null:$args[0]);
    }
    function view($identify,$id){

        $content_record=ContentType::contentRecord($identify,$id);
        if(method_exists($content_record, "getViewUrl")){
            return $this->_referer_to(null,$content_record->getViewUrl());

        }else{
            if($content_record['deprecated']){
                return $this->_referer_to("内容已被删除！","",2);
            }
            if($content_record['channel_id']){
                $channel=new Channel(['id'=>$content_record['channel_id']]);
                return $this->_referer_to(null,\W3cApp::route("Web/".$channel['path']."/".$content_record['id']));
            }else{
                return $this->_referer_to("内容未设置栏目！");
            }
        }
    }

}