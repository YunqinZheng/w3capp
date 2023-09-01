<?php
namespace cms\model;
use common\model\BlockDataRecord;
use w3capp\helper\Str;

class BlockData extends BlockDataRecord {
	static function itemJson($bid,$replace_id){
        $data=self::firstAttr(["bid"=>$bid,"replace_id"=>$replace_id],'',"sequence asc");
        if(empty($data)){
            return '';
        }
        if($data['others']){
            $val=trim($data['others'],"{}");
            unset($data['others']);
            return '{'.trim(\w3c\helper\Str::toJson($data),"{}").",".$val."}";
        }else{
            return \w3c\helper\Str::toJson($data);
        }

    }
	static function listData($bid){
	    $obj=self::findAllData(["bid"=>$bid],['sequence'=>"asc"],100);
        $obj->setDataFilter(function(&$row){
            if($row['others']){
                $val=json_decode($row['others'],true);
                unset($row['others']);
                $row=array_merge($val,$row);
            }
        });
	    return $obj;
	}
	static function saveData($data,$replace_id,$bid){
	    $item=self::record(["bid"=>$bid,"replace_id"=>$replace_id],true);
	    $defaul=["replace_id","url","title","mtime","dateline","sequence","pic","summary","others","recommend","hidden","fixed"];
	    $info=[];
	    $others=[];
	    foreach ($data as $key=> $val){
	        if(in_array($key,$defaul)){
                $item[$key]=$val;
            }else{
	            $others[$key]=$val;
            }
        }
        $item['others']=\w3c\helper\Str::toJson($others);
        $item['mtime']=time();
	    if(empty($item->id)){
            $item['replace_id']=$replace_id;
            $item['bid']=$bid;
            $item['id']=Str::guid();
	    }
	    return $item->save();
	}
	static public function resetData($replace_id,$bid){
	    return self::deleteAll(['replace_id'=>$replace_id,"bid"=>$bid]);
    }
}
