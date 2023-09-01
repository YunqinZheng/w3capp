<?php

namespace common\model;
use w3capp\Record;
/**
 * w3ca_member_group数据记录类
 * @property string $id id
 * @property string $name name
 * @property string $publish_menu publish_menu
 */
class MemberGroupRecord extends Record{
    
    static public function recordName(){
        return 'member_group';
    }

    static public function recordRule(){
        return [[['name'],"require"],
			[['name'],"string",25],
			[['publish_menu'],"string",4000]];
    }

    static public function propertyDesc(){
        return array (
  'id' => 'id',
  'name' => 'name',
  'publish_menu' => 'publish_menu',
);
    }
}