<?php 
namespace common\model;
/**
 * w3ca_channel数据记录类
 * @property string $id id
 * @property string $pid 上级栏目ID
 * @property string $ch_name 栏目名
 * @property string $keywords seo 关键字
 * @property string $description 描述
 * @property string $order_val 排序
 * @property string $innav 是否导航
 * @property string $be_publish 是否发文章
 * @property string $hidden 是否隐藏
 * @property string $list_tpl 列表模版
 * @property string $view_tpl 内页模版
 * @property string $pic 封面
 * @property string $frame_mod 内容模型
 * @property string $static_path 固定路径
 * @property string $path 访问路径
 */
class ChannelRecord extends \W3cRecord{
    
    static public function recordName(){
        return 'channel';
    }
    
    static public function recordRule(){
        return [[['id','pid','order_val','innav','be_publish','hidden'],"integer"],        
                [['ch_name'],"string",50],
                [['keywords','list_tpl','view_tpl','frame_mod','path'],"string",100],
                [['description'],"string",800],
                [['pic'],"string",85],
                [['static_path'],"string",300]];
    }

    static public function propertyDesc(){
        return array (
          'id' => 'id',
          'pid' => '上级栏目ID',
          'ch_name' => '栏目名',
          'keywords' => 'seo 关键字',
          'description' => '描述',
          'order_val' => '排序',
          'innav' => '是否导航',
          'be_publish' => '是否发文章',
          'hidden' => '是否隐藏',
          'list_tpl' => '列表模版',
          'view_tpl' => '内页模版',
          'pic' => '封面',
          'frame_mod' => '内容模型',
          'static_path' => '固定路径',
          'path' => '访问路径',
        );
    }
}