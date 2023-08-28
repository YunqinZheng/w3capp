<?php
namespace common\model;
/**
 * w3ca_page_layout数据记录类
 * @property string $id id
 * @property string $page_frame 所在框架
 * @property string $lay_type 布局方式
 * @property string $cell_number 网格数
 * @property string $lay_inner lay_inner
 * @property string $cell_css 样式设置
 * @property string $parent_lay 上级布局
 * @property string $parent_cell 上级网格
 */
class PageLayoutRecord extends \W3cRecord{

    static public function recordName(){
        return 'page_layout';
    }

    static public function recordRule(){
        return [
            [['page_frame','cell_number','parent_lay','parent_cell'],"integer"],
            [['lay_type'],"string",30],
            [['lay_inner','cell_css'],"string",1000]];
    }

    static public function propertyDesc(){
        return array (
            'id' => 'id',
            'page_frame' => '所在框架',
            'lay_type' => '布局方式',
            'cell_number' => '网格数',
            'lay_inner' => 'lay_inner',
            'cell_css' => '样式设置',
            'parent_lay' => '上级布局',
            'parent_cell' => '上级网格',
        );
    }
}