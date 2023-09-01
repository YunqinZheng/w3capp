<?php 
namespace common\model;
use w3capp\Record;
/**
 * w3ca_page_block数据记录类
 * @property string $id id
 * @property string $file_id 生成文件id
 * @property string $block_mark 模块标记
 * @property string $mark_time 记录时间
 */
class PageBlockRecord extends Record{
    
    static public function recordName(){
        return 'page_block';
    }
    
    static public function recordRule(){
        return [[['file_id','block_mark'],"require"],        
[['mark_time'],"integer"],        
[['file_id'],"string",250],        
[['block_mark'],"string",100]];
    }

    static public function propertyDesc(){
        return array (
  'id' => 'id',
  'file_id' => '生成文件id',
  'block_mark' => '模块标记',
  'mark_time' => '记录时间',
);
    }
}