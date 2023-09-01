<?php 
namespace common\model;
use w3capp\Record;
/**
 * w3ca_web_theme数据记录类
 * @property string $id id
 * @property string $name name
 * @property string $image image
 * @property string $install_dir install_dir
 * @property string $language language
 * @property string $refresh_var refresh_var
 * @property string $file_var 调用文件参数据
 */
class WebThemeRecord extends Record{
    
    static public function recordName(){
        return 'web_theme';
    }
    
    static public function recordRule(){
        return [[['id','name','image','install_dir','language'],"require"],        
        [['id'],"string",100],
        [['name'],"string",200],
        [['image','install_dir'],"string",150],
        [['language'],"string"],[['refresh_var'],'string',20],[['file_var'],'string',15]];
    }

    static public function propertyDesc(){
        return array (
            'id' => 'id',
            'name' => 'name',
            'image' => 'image',
            'install_dir' => 'install_dir',
            'language' => 'language','refresh_var'=>'refresh_var',
            'refresh_var'=>"",
            'file_var'=>"",
            );
    }
}