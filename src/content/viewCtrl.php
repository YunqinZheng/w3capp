<?php
namespace content\controller;
use common\model\Channel;
use cms\model\ContentType;

class viewCtrl extends mainCtrl{
    function _index($model,$id){

        $content_record=ContentType::contentRecord($model,$id);
        if(method_exists($content_record, "getViewUrl")){
            return $this->_referer_to(null,$content_record->getViewUrl());

        }else{
            if($content_record['deprecated']){
                return $this->_referer_to("内容已经被标记删除,无法查看!");
            }
            if($content_record['channel_id']){
                $channel=new Channel(['id'=>$content_record['channel_id']]);
                if($channel['frame_mod']==$model){
                    return $this->_referer_to(null,app_path().$channel['path']."/".$id.".html");
                }else{
                    return $this->_referer_to("栏目内容模型有误!");
                }

            }else{
                return $this->_referer_to("内容未设置栏目");
            }
        }
    }
}