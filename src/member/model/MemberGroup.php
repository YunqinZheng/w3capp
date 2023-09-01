<?php
namespace member\model;
use cms\model\ContentType;
use common\model\MemberGroupRecord;

class MemberGroup extends MemberGroupRecord {
    /**
     * 默认会员组
     */
    protected static $default=array(
        99=>array("name"=>"禁用会员","publish_menu"=>""),
        array("name"=>"未验证会员","publish_menu"=>""),
        array("name"=>"初级会员","publish_menu"=>""),
        array("name"=>"中级会员","publish_menu"=>""),
        array("name"=>"高级会员","publish_menu"=>""),
    );
    protected static $group_list;

    static function getList(){
        if(self::$group_list){
            return self::$group_list;
        }
		$cache=\W3cApp::$instance->_cache();
        if($cache->valueExists(self::recordName())){
            self::$group_list=unserialize($cache->value(self::recordName()));
        }else{
            $data=self::myAdapter()->limit(100)->selectAll();
            foreach($data as $d){
                self::$group_list[$d['id']]=$d;
            }
            if(self::$group_list){
                $cache->saveValue(self::recordName(),serialize(self::$group_list),3600);
                return self::$group_list;
            }
            return self::$default;
        }
        return self::$group_list;
    }
    static function groupName($id){
        $list=self::getList();
        if(array_key_exists($id, $list)){
            return $list[$id]['name'];
        }
    }
    static function saveAll($post){
        $data=array();
        foreach ($post['id'] as $id) {

            if($post['name_'.$id]){
                $data[$id]=array('id'=>$id,'name'=>$post['name_'.$id],"publish_menu"=>empty($post['publish_menu_'.$id])?'':implode(",", $post['publish_menu_'.$id]));
                $g=new self($data[$id]);
                if(false===$g->save()){
                    return false;
                }
            }
        }
        foreach ($post['new'] as $id){
            if($post['new_name_'.$id]){
                $d=array('name'=>$post['new_name_'.$id],"publish_menu"=>empty($post['new_publish_menu_'.$id])?'':implode(",", $post['new_publish_menu_'.$id]));
                $g=new self($d);
                if($g->save()){
                    $data[$g->primary()]=$d;
                }

            }
        }
        if($post['delete_items']!='0'){
            self::deleteAll(["id"=>explode(",",$post['delete_items'])]);
        }
        if(empty($data)){
            return false;
        }else{
            self::cacheDelete(self::recordName());
            self::$group_list=$data;
            return true;
        }
    }
    public static function groupInfo($gid){
        $gps=self::getList();
        $info=$gps[$gid];
        $cmodels=ContentType::findAll(["content_mark"=>explode(",",$info['publish_menu'])]);
        foreach($cmodels as $m){
            $info['content_marks'][]=$m;
        }
        return $info;
    }

    /**
     * 内容发布权限
     * @param $gid
     * @param $content_mark
     * @return bool
     */
    function hasContent($gid,$content_mark){
        $gps=self::getList();
        $info=$gps[$gid];
        return strpos('/,'.$info['publish_menu'].",",",".$content_mark.",")>0;
    }
}
