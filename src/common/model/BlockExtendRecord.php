<?php 
namespace common\model;
/**
 * w3ca_block_extend数据记录类
 * @property string $block_id block_id
 * @property string $template 模版内容
 * @property string $areas 所在区域
 * @property string $files 所在文件
 * @property string $context_edit 内容编辑设置
 * @property string $init_config 初始设置
 */
class BlockExtendRecord extends \W3cRecord{
    protected $primaryName="block_id";
    
    static public function recordName(){
        return 'block_extend';
    }
    
    static public function recordRule(){
        return [[['block_id'],"integer"],        
        [['template','init_config'],"limitless"],
        [['areas','files'],"string",1000],
        [['context_edit'],"limitless",2000]];
    }

    static public function propertyDesc(){
        return array (
          'block_id' => 'block_id',
          'template' => '模版内容',
          'areas' => '所在区域',
          'files' => '所在文件',
          'context_edit' => '内容编辑设置',
          'init_config' => '初始设置',
        );
    }
}