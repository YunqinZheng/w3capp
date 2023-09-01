<?php 
namespace common\model;
use w3capp\Record;
/**
 * w3ca_block_data数据记录类
 * @property string $id id
 * @property string $bid bid
 * @property string $replace_id replace_id
 * @property string $url url
 * @property string $title title
 * @property string $mtime mtime
 * @property string $dateline dateline
 * @property string $sequence sequence
 * @property string $pic pic
 * @property string $summary summary
 * @property string $others others
 * @property string $recommend recommend
 * @property string $hidden hidden
 * @property string $fixed fixed
 */
class BlockDataRecord extends Record{
    
    static public function recordName(){
        return 'block_data';
    }
    
    static public function recordRule(){
        return [[['id','bid','replace_id','url','mtime','dateline','sequence','fixed'],"require"],        
        [['mtime','sequence','recommend','hidden','fixed'],"integer"],
        [['id','bid','replace_id'],"string",46],
        [['url'],"string",100],
        [['title','dateline'],"string",50],
        [['pic'],"string",160],
        [['summary'],"string",2000],
        [['others'],"string"]];
    }

    static public function propertyDesc(){
        return array (
          'id' => 'id',
          'bid' => 'bid',
          'replace_id' => 'replace_id',
          'url' => 'url',
          'title' => 'title',
          'mtime' => 'mtime',
          'dateline' => 'dateline',
          'sequence' => 'sequence',
          'pic' => 'pic',
          'summary' => 'summary',
          'others' => 'others',
          'recommend' => 'recommend',
          'hidden' => 'hidden',
          'fixed' => 'fixed',
        );
    }
}