<?php 
namespace common\model;
/**
 * w3ca_site_config数据记录类
 * @property string $id id
 * @property string $conf_val conf_val
 */
class SiteConfig extends \W3cRecord{
    
    static public function recordName(){
        return 'site_config';
    }
    
    static public function recordRule(){
        return [[['id'],"string",30],        
        [['conf_val'],"string",2000]];
    }

    static public function propertyDesc(){
        return array (
          'id' => 'id',
          'conf_val' => 'conf_val',
        );
    }
    /*网站设置*/
    static protected $set;

    /**
     * 网站的一些设置信息,通过下标取得不同的设置 style=>模板子目录
     * @return array
     */
    static public function getConfigs(){
        if(self::$set)
            return self::$set;
        $d=self::findAll([]);
        self::$set=array();
        foreach ($d as $val){
            self::$set[$val['id']]=$val['conf_val'];
        }
        if(empty(self::$set['style']))
            self::$set['style']="default";
        return self::$set;
    }
    /**
     * @param $key (web_name>网站名称,web_keyword>关键字,description>description,logo>logo,style>default)
     */
    static public function getSetting($key){
        $set=self::getConfigs();
        return $set[$key];
    }
    static public function saveValue($key,$value){
        $set=self::getConfigs();
        $set[$key]=$value;
        return self::saveConfigs($set);
    }
    static public function indexTPL($dir){
        $tpl_file=W3CA_PATH."TPL/$dir/index.htm";
        if(file_exists($tpl_file)){
            return ['file'=>$tpl_file,"code"=>file_get_contents($tpl_file)];
        }else{
            return null;
        }
    }
    static function saveIndexTPL($dir,$code){
        $tpl_file=W3CA_PATH."TPL/$dir/index.htm";
        return file_put_contents($tpl_file,$code);
    }
    static public function saveConfigs($sets){
        $rs=true;
        foreach ($sets as $key=>$val){
            $record=new self(array("conf_val"=>$val?$val:"","id"=>$key));
            $rs=$rs&&$record->save();
        }
        return $rs;
    }


    static public function clearCache(){
        $cache_dir=W3CA_PATH."data/cache/";
        $d=dir($cache_dir);
        while(false !== ($file = $d->read())) {
            if($file!='.'&&$file!='..'){
                $full_file=$cache_dir.$file;
                if(is_file($full_file)){
                    unlink($full_file);
                }else if(is_dir($full_file)){
                    $d2=dir($full_file);
                    while(false !== ($file2 = $d2->read())) {
                        if($file2!='.'&&$file2!='..'){
                            $full_file2=$full_file.'/'.$file2;
                            if(is_file($full_file2)){
                                unlink($full_file2);
                            }
                        }
                    }
                }
            }
        }
    }
}