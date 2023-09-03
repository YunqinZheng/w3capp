<?php
namespace common\model;
use w3capp\helper\Sql;

class Channel extends ChannelRecord{
    /**
     * @param $rootid 根ID
     * @param $passid 忽略的ID
     * @return array
     */
    function getChannels($rootid,$passid){
        $items=array();
        $this->fetchItems($items, $rootid, $passid);
        return $items;
    }

    function treeList(){
        $ch1=self::myAdapter()->select(static::allColumnStr())->orderBy("pid,order_val")->limit(200)->selectAll();
        $list=array();
        $tree_ids=array();
        foreach ($ch1 as $ch) {
            $ch["child"]=array_key_exists($ch["id"], $tree_ids)?$tree_ids[$ch["id"]]:array();
            if($ch["pid"]>0){
                if(array_key_exists($ch["pid"], $list)){
                    $list[$ch["pid"]]["child"][]=$ch["id"];
                }else{
                    $tree_ids[$ch["pid"]][]=$ch["id"];
                }

            }
            $list[$ch["id"]]=$ch;
        }
        return $this->treeFormat($list);
    }
    function channelsOfFrame($frame,$publish){
        $ch1=self::myAdapter()->select(static::allColumnStr())->where(["frame_mod"=>$frame,"be_publish"=>$publish])->orderBy("pid,order_val")
            ->limit(200)->selectAll();
        $list=array();
        foreach ($ch1 as $ch) {
            $ch["child"]=array();
            if($ch["pid"]>0&&array_key_exists($ch["pid"], $list))$list[$ch["pid"]]["child"][]=$ch["id"];
            $list[$ch["id"]]=$ch;
        }
        return $this->treeFormat($list);
    }
    protected function fetchItems(&$items,$parentid,$passid,$depth=0){
        $ch=self::myAdapter()->select("id,ch_name")->where(["pid"=>$parentid])->orderBy("order_val")->limit(200)->selectAll();
        foreach ($ch as $item){
            if($item['id']!=$passid){
                $items[$item['id']]=str_repeat("-", $depth+1).$item['ch_name'];
                $this->fetchItems($items, $item['id'], $passid,$depth+1);
            }
        }
    }

    /**
     * 树型结构
     * @param $pop
     * @param array $rs
     * @param int $cid
     * @param int $depth
     * @return array
     */
    protected function treeFormat(&$pop,&$rs=array(),$cid=0,$depth=0){
        if($cid==0){
            foreach ($pop as $key => $value) {
                if(array_key_exists($key, $rs))
                    continue;
                $child=$value['child'];
                unset($value['child']);
                $value['depth']=$depth;
                $rs[$key]=$value;
                foreach($child as $_id){
                    $this->treeFormat($pop,$rs,$_id,$depth+1);
                }
            }
            return $rs;
        }else{
            $value=$pop[$cid];
            $child=$value['child'];
            unset($value['child']);
            $value['depth']=$depth;
            $rs[$cid]=$value;
            if(!empty($child)){
                foreach($child as $_id){
                    $this->treeFormat($pop, $rs,$_id,$depth+1);
                }
            }
        }

    }
    /**
     * 默认只读取200个栏目
     */
    function frameList($frame){
        $where=Sql::parse(array("frame_mod=%s",array($frame)));
        $ch1=$this->db()->getIterator("select * from  where ".$where." order by pid,order_val limit 200");
        $list=array();
        foreach ($ch1 as $ch) {
            $ch["child"]=array();
            if($ch["pid"]>0&&array_key_exists($ch["pid"], $list))$list[$ch["pid"]]["child"][]=$ch["id"];
            $list[$ch["id"]]=$ch;
        }
        return $list;
    }
    static function tplInfo($file){
        $wstyle=SiteConfig::getSetting("style");
        $tpl_file=W3CA_MASTER_PATH."TPL/$wstyle/content/$file.htm";
        if(file_exists($tpl_file)){
            return ['file'=>$tpl_file,"code"=>file_get_contents($tpl_file)];
        }else{
            return null;
        }
    }
    static function saveTpl($file,$code){
        $wstyle=SiteConfig::getSetting("style");
        $tpl_file=W3CA_MASTER_PATH."TPL/$wstyle/content/$file.htm";
        return file_put_contents($tpl_file,$code);
    }
    /**
     * 模板列表
     */
    static function contentTpls(){
        $rtpls1=array('list_'=>"list_.htm(默认)");
        $rtpls2=array('view_'=>"view_.htm(默认)");
        $wstyle=SiteConfig::getSetting("style");
        $tpl_dir=W3CA_MASTER_PATH."TPL/".$wstyle."/content";
        if ($dh = opendir($tpl_dir)) {
            while (($file_name = readdir($dh)) !== false) {
                if(filetype($tpl_dir.'/'.$file_name)=='file'){
                    if(strpos($file_name,"list_")===0){
                        $f_ls=explode(".", $file_name);
                        if(end($f_ls)=="htm")
                            $rtpls1[$f_ls[0]]=$file_name;
                    }else if(strpos($file_name,"view_")===0){
                        $f_ls=explode(".", $file_name);
                        if(end($f_ls)=="htm")
                            $rtpls2[$f_ls[0]]=$file_name;
                    }
                }
            }
        }
        return array($rtpls1,$rtpls2);
    }
}