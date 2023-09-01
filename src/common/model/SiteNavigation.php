<?php 
namespace common\model;
use w3capp\Record;
/**
 * w3ca_site_navigation数据记录类
 * @property string $id id
 * @property string $parent_id id
 * @property string $name 导航名称
 * @property string $url 导航连接
 * @property string $display 是否显示
 */
class SiteNavigation extends Record{
    protected static $seting;
    static public function recordName(){
        return 'site_navigation';
    }
    
    static public function recordRule(){
        return [[['display','ordid'],"integer"],
        [['id','parent_id'],"string",10],
        [['name'],"string",30],
        [['url'],"string",500]];
    }

    static public function propertyDesc(){
        return array (
          'id' => 'id',
          'parent_id' => '上级id',
          'name' => '导航名称',
          'url' => '导航连接',
          'display' => '是否显示',
            'ordid'=>'排序'
        );
    }
    static public function getSeting($parent_id=''){
        if(empty(self::$seting)){
			$cache=\W3cApp::$instance->_cache();
            $seting_val=$cache->value("site_natset");
            if($seting_val){
                self::$seting=unserialize($seting_val);
            }else{
                self::$seting=array('S0'=>array('id'=>'S0','name'=>'首页',"url"=>W3CA_URL_ROOT,"display"=>1,"parent_id"=>""));
                $d=self::findAllData("",["ordid"=>"asc"]);
                foreach ($d as $item){
                    self::$seting[$item['id']]=$item;
                }
                $cache->saveValue("site_natset",serialize(self::$seting));
            }
            // self::$seting=array('S0'=>array('name'=>'首页',"url"=>W3CA_URL_ROOT,"display"=>1));
        }
        if($parent_id==-1){
            return self::$seting;
        }
        $list=[];
        foreach (self::$seting as $nav){
            if($nav['parent_id']==$parent_id){
                $list[$nav['id']]=$nav;
            }
        }
        return $list;
    }
    static public function removeByParent($parent_id){
        \W3cApp::$instance->_cache()->delete("site_natset");
        return self::deleteAll(['parent_id'=>$parent_id]);
    }
    static public function saveChannelNav($chid,$name,$url,$nav_type){
        $nav=self::record(['id'=>'C'.$chid],true);
        $parent_id='';
        if($nav_type==2){
            //导航分类
            $parent_id='00';
            $child_c=ChannelRecord::arrayData(['pid'=>$chid,"innav"=>1]);
            $md=self::myAdapter()->commend("update");
            foreach ($child_c as $item){
                $md->setData(["parent_id"=>$nav->id])->where(["id"=>'C'.$item['id']])->execute();
            }
        }else{
            if($nav->parent_id=='00'){
                self::myAdapter()->commend("update")->setData(["parent_id"=>''])->where(["parent_id"=>$nav->id])->save();
            }
            //导航项
            $p_ch=ChannelRecord::firstAttr(["id"=>$chid]);
            $channel=ChannelRecord::firstAttr(["id"=>$p_ch['pid']]);
            if($channel){
                $parent_id='C'.$channel['id'];
            }
        }
        $nav->setAttributes(array("name"=>$name,"url"=>$url,"display"=>1,'parent_id'=>$parent_id));
        if($nav->save()===false){
            throw new \Exception("保存出错");
        }
        \W3cApp::$instance->_cache()->delete("site_natset");
    }
    static public function setNavByKey($key,$name,$url,$display){
        self::$seting[$key]=array('name'=>$name,"url"=>$url,"display"=>$display);
    }
    static public function deleteNav($key){
        self::getSeting();
        if(empty(self::$seting[$key]))return;
        unset(self::$seting[$key]);
        \W3cApp::$instance->_cache()->saveValue("site_natset",serialize(self::$seting));
        self::deleteAll(['id'=>$key]);
    }
    static public function deleteChannelNav($chid){
        self::batchUpdate([["parent_id"=>''],["parent_id"=>"C".$chid]]);
        self::deleteNav("C".$chid);
    }
    static public function maxTid($t){
        $list=self::getSeting(-1);
        $max_diyn=0;
        foreach ($list as $key=>$item){
            if(strpos($key,$t)===0){
                $k=str_replace($t,'',$key);
                $nkv=intval($k);
                if($max_diyn<$nkv)
                    $max_diyn=$nkv;
            }
        }
        return $max_diyn;
    }
    static public function saveNav($a=null){
        if($a){
            self::$seting=$a;
            foreach ($a as $id=>$val){
                $nav=self::record(['id'=>$id],true);
                $nav->setAttributes($val);
                $nav->save();
            }
            \W3cApp::$instance->_cache()->delete("site_natset");
        }
    }
}