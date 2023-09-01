<?php
namespace cms\model;
use common\model\MaterialRecord;
use w3capp\helper\Uploader;

class Material extends MaterialRecord{
    var $file_info;
    static $on_edit=0;
    var $file_path;
    private static $store_dir="data/material/";
    var $has_file_post=false;
    static function postCheck(){
        if(empty($_POST["classify"])){
            return "分类不能为空";
        }
        if(self::$on_edit&&Uploader::upload_enabled("file")){
            return "上传文件不能为空";
        }
        return "";
    }
    static function mainDir(){
        if(!is_dir(W3CA_PATH.self::$store_dir)){
            @mkdir(W3CA_PATH.self::$store_dir);
        }
        return self::$store_dir;
    }
    static function filePath($name,$if_null){
        return $name?(W3CA_URL_ROOT.self::mainDir().$name):$if_null;
    }

    static function deleteFiles($id_list,$member_id=0){
        if($id_list){
            $condition=['in'=>array('id',$id_list)];
            if($member_id){
                $condition['member_id']=$member_id;
            }
            $files=self::findAll($condition);
            $del_ids=[];
            foreach($files as $file){
                $file_path=W3CA_PATH.self::mainDir().$file['file'];
                $del_ids[]=$file['id'];
                if(file_exists($file_path)){
                    @unlink($file_path);
                }
                $file->delete();
            }
            
        }

    }
}