<?php 
namespace common\model;
/**
 * w3ca_material数据记录类
 * @property string $id id
 * @property string $classify classify
 * @property string $bz bz
 * @property string $file file
 * @property string $size size
 * @property string $dateline dateline
 * @property string $member_id member_id
 * @property string $access_key access_key
 */
class MaterialRecord extends \W3cRecord{
    
    static public function recordName(){
        return 'material';
    }
    
    static public function recordRule(){
        return [[['classify','file','dateline'],"require"],        
[['size','dateline','member_id'],"integer"],        
[['classify'],"string",100],        
[['bz'],"string",1000],        
[['file'],"string",60],        
[['access_key'],"string",55]];
    }

    static public function propertyDesc(){
        return array (
  'id' => 'id',
  'classify' => 'classify',
  'bz' => 'bz',
  'file' => 'file',
  'size' => 'size',
  'dateline' => 'dateline',
  'member_id' => 'member_id',
  'access_key' => 'access_key',
);
    }
}