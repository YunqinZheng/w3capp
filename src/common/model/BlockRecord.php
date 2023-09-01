<?php 
namespace common\model;
use w3capp\Record;
/**
 * w3ca_block数据记录类
 * @property string $id id
 * @property string $mark 标记
 * @property string $update_time 数据刷新时间分钟
 * @property string $type 类型
 * @property string $param_str 参数
 * @property string $tpl 模版
 * @property string $data_size 数据大小
 * @property string $remarks 备注名称
 * @property string $pro_value 重要值
 * @property string $data_desc 数据描述
 * @property string $interface_arg 接口描述
 * @property string $hidden 是否隐藏
 * @property string $init_hash 初始标记
 */
class BlockRecord extends Record{
    
    static public function recordName(){
        return 'block';
    }
    
    static public function recordRule(){
        return [[['update_time','data_size','hidden'],"integer"],
        [['mark','type'],"string",100],
        [['param_str'],"string"],
        [['tpl','remarks'],"string",200],
        [['pro_value'],"limitless",1000],
        [['data_desc','interface_arg'],"limitless",4000],
        [['init_hash'],"string",30]];
    }

    static public function propertyDesc(){
        return array (
          'id' => 'id',
          'mark' => '标记',
          'update_time' => '数据刷新时间分钟',
          'type' => '类型',
          'param_str' => '参数',
          'tpl' => '模版',
          'data_size' => '数据大小',
          'remarks' => '备注名称',
          'pro_value' => '重要值',
          'data_desc' => '数据描述',
          'interface_arg' => '接口描述',
          'hidden' => '是否隐藏',
          'init_hash' => '初始标记',
        );
    }
}