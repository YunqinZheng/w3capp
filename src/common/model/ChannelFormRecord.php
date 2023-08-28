<?php 
namespace common\model;
/**
 * w3ca_channel_form数据记录类
 * @property string $id id
 * @property string $zh_name 字段中文名称
 * @property string $col_name 字段
 * @property string $data_type 数据类型
 * @property string $form_input 表单输入框
 * @property string $def_value 值设置
 * @property string $orderi 排序
 * @property string $content_mark content_mark
 * @property string $member_able 会员可用
 */
class ChannelFormRecord extends \W3cRecord{
        protected $primaryName="id";
    
    static public function recordName(){
        return 'channel_form';
    }
    
    static public function recordRule(){
        return [[['id','orderi','member_able'],"integer"],        
        [['zh_name','col_name','form_input','content_mark'],"string",45],
        [['data_type'],"string",25],
        [['def_value'],"string"]];
    }

    static public function propertyDesc(){
        return array (
          'id' => 'id',
          'zh_name' => '字段中文名称',
          'col_name' => '字段',
          'data_type' => '数据类型',
          'form_input' => '表单输入框',
          'def_value' => '值设置',
          'orderi' => '排序',
          'content_mark' => 'content_mark',
          'member_able' => '会员可用',
        );
    }
}