<?php 
namespace common\model;
use w3capp\Record;
/**
 * w3ca_sys_user_role数据记录类
 * @property string $id id
 * @property string $role_name role_name
 * @property string $note note
 * @property string $options options
 * @property string $opt_type opt_type
 */
class SysUserRoleRecord extends Record{
    
    static public function recordName(){
        return 'sys_user_role';
    }
    
    static public function recordRule(){
        return [[['role_name','opt_type'],"require"],        
[['opt_type'],"integer"],        
[['role_name'],"string",50],        
[['note'],"string",900],        
[['options'],"string"]];
    }

    static public function propertyDesc(){
        return array (
  'id' => 'id',
  'role_name' => 'role_name',
  'note' => 'note',
  'options' => 'options',
  'opt_type' => 'opt_type',
);
    }
}