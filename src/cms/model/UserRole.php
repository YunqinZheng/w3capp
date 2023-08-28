<?php
namespace cms\model;
use common\model\SysUserRoleRecord;

class UserRole extends SysUserRoleRecord {

	static function roleList($column,$condition='',$order='',$limit=0){
	    return self::adaptTo($condition)->select($column)->orderBy($order)->limit($limit)->selectAll();
	}

	function updateOptions($opts,$type,$id){
		$o=array();
		foreach ($opts as $value) {
			if(strpos($value, ":")){
				list($k,$v)=explode(":", str_replace("{ROOT}", "", $value));
				$o[$k]=$v;
			}else{
				$o[0]=$value;
			}
		}
		return $this->db()->update(array("options"=>'{new_opt}',"opt_type"=>$type),$this->tableName(),"id=$id",array('{new_opt}'=>serialize($o)));
	}

	static function rinfo($ids){
		return self::adaptTo(["id"=>$ids])->select("id,role_name")->selectAll();
	}
}
