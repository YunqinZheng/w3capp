<?php
namespace api\block\query;
use common\model\Channel;
use cms\model\Material;


class Content extends \api\block\BlockTpl{
    public function loadData($args)
    {
        if(empty($args['model'])){
            return [];
        }
        $class_fun=["\\content\\model\\".$args['model'],"page"];
        $instant=call_user_func($class_fun,$args['condition']);
        if(empty($args['page_size'])){
            $args['page_size']=10;
        }else{
            $args['page_size']=intval($args['page_size']);
        }
        if(empty($args['page_index'])==false){
            $args['page_index']=intval($args['page_index']);
        }else if(self::__get('page_index')){
            $args['page_index']=intval(self::__get('page_index'));
        }else{
            $args['page_index']=0;
        }
        $instant->limit($args['page_size'],$args['page_index']);
        if(empty($args['order'])){
            if(self::__get('page_order')){
                $instant->orderBy(self::__get('page_order'));
            }
        }else{
            $instant->orderBy($args['order']);
        }
        $channels=Channel::findAllData(['frame_mod'=>$args['model']]);
        $c_list=[];
        foreach($channels as $ch){
            $c_list[$ch['id']]=$ch['path'];
        }
        $data=$instant->selectAll(true);
        $data->setDataFilter(function(&$item) use ($c_list){
            if(empty($item['channel_id'])||empty($c_list[$item['channel_id']])){
                $item['url']="#".$item['id'];
            }else{
                $item['url']=self::$app->route($c_list[$item['channel_id']]."/".$item['id'].".html");
            }
            if(empty($item['pic_url'])){
                $item['pic_url']=Material::filePath($item['pic'],"static/image/item_image.png");
            }
        });
        return $data;
    }
}