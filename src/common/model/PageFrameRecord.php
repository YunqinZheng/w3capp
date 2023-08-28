<?php
namespace common\model;
/**
 * w3ca_page_frame数据记录类
 * @property string $id id
 * @property string $theme_id 主题id
 * @property string $frame_id frame_id
 * @property string $frame_name frame_name
 * @property string $block_marks block_marks
 * @property string $css_code css_code
 * @property string $tpl 显示模板
 */
class PageFrameRecord extends \W3cRecord{

    static public function recordName(){
        return 'page_frame';
    }

    static public function recordRule(){
        return [[['frame_id'],"require"],
            [['theme_id','frame_id'],"string",50],
            [['frame_name'],"string",100],
            [['block_marks'],"string",600],
            [['css_code'],"string",1200],
            [['tpl'],"string"]];
    }

    static public function propertyDesc(){
        return array (
            'id' => 'id',
            'theme_id' => '主题id',
            'frame_id' => 'frame_id',
            'frame_name' => 'frame_name',
            'block_marks' => 'block_marks',
            'css_code' => 'css_code',
            'tpl' => '显示模板'
        );
    }
}