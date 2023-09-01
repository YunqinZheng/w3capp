<?php 

namespace common\model;
use w3capp\Record;
/**
 * w3ca_sys_feature_menu数据记录类
 * @property string $id id
 * @property string $pid 上级菜单id
 * @property string $name 菜单名
 * @property string $url 菜单链接
 * @property string $orderid 排序
 * @property string $keyid keyid
 */
class SysFeatureMenu extends Record{
    protected $primaryName="id";
    
    static public function recordName(){
        return 'sys_feature_menu';
    }
    
    static public function recordRule(){
        return [[['orderid','keyid'],"integer"],        
        [['id','pid'],"string",50],
        [['name'],"string",140],
        [['url'],"string",240]];
    }

    static public function propertyDesc(){
        return array (
          'id' => 'id',
          'pid' => '上级菜单id',
          'name' => '菜单名',
          'url' => '菜单链接',
          'orderid' => '排序',
          'keyid' => 'keyid',
        );
    }
}