<?php
namespace common\model;
use w3capp\Record;
/**
 * w3ca_channel_type数据记录类
 * @property string $id id
 * @property string $content_mark 标识
 * @property string $type_name 内容名称
 * @property string $member_form 会员表单
 * @property string $main_form 后台表单
 * @property string $member_publish 会员可单独发布
 */
class ChannelTypeRecord extends Record{

    static public function recordName(){
        return 'channel_type';
    }

    static public function recordRule(){
        return [[['id','member_publish'],"integer"],
            [['content_mark','member_form','main_form'],"string",45],
            [['type_name'],"string",65]];
    }

    static public function propertyDesc(){
        return array (
            'id' => 'id',
            'content_mark' => '标识',
            'type_name' => '内容名称',
            'member_form' => '会员表单',
            'main_form' => '后台表单',
            'member_publish' => '会员可单独发布',
        );
    }
}