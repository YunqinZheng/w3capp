<?php
namespace cms\model;
use common\model\SysFeatureMenu;
use w3c\helper\Str;

class FeatureMenu extends SysFeatureMenu {
	//id, pid, name, url, orderid
	static $f_c="data/cache/tree_menu";
	protected $options=null;

	static function get_menu_tree(){
		$menu=self::findAllData("",["pid"=>"asc","orderid"=>"asc"],200);
		$tree_tmp=array();
		$tree=array();
		foreach ($menu as $item) {
			$tree_tmp[$item['pid']][]=$item;
		}
        self::tree_line($tree_tmp, $tree,0,0);
		return $tree;
	}
	private static function tree_line(&$tree1,&$tree2,$pid,$deep)
	{
		foreach ($tree1[$pid] as $key => $value) {
			$value['deep']=$deep;
			if(isset($tree1[$value['id']])){
				$value['has_child']=1;
				$tree2[$value['id']]=$value;
                self::tree_line($tree1, $tree2, $value['id'], $deep+1);
			}else{
				$value['has_child']=0;
				$tree2[$value['id']]=$value;
			}
			
		}
	}
	static function get_option_tree($ids="",$r_type=""){
		$tree=$options=array();
        $condition=[];
		if(is_array($ids)&&$ids){
            if($r_type==1){
                $condition=["id"=>$ids];
            }else{
                $condition=["not in"=>["id",$ids]];
            }
        }
		$fetch=self::findAllData($condition,["pid"=>"asc","orderid"=>"asc"],120);
		foreach ($fetch as $key => $value) {
			$value['level']=$value['pid']==0?0:$options[$value['pid']]['level']+1;
			$options[$value['id']]=$value;
			$tree[$value['pid']][]=$value['id'];
		}
		return array("options"=>$options,"tree"=>$tree);
	}

	static function addItem($pid, $name, $url, $orderid){
	    $mu=new self();
	    $mu->setAttributes(array("id"=>Str::guid(),"name"=>$name,"pid"=>$pid,"url"=>$url,"orderid"=>$orderid));
	    if($mu->save()){
	        return $mu->id;
        }
		return false;
	}

	static function put_to_cache($tree){
		file_put_contents(W3CA_PATH.self::$f_c, serialize($tree));
	}
    static function get_from_cache(){
		return unserialize(file_get_contents(W3CA_PATH.self::$f_c));
	}
}
