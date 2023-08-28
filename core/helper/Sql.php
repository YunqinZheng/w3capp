<?php
namespace w3c\helper;
class Sql{
    static public function getSql($sql_opt){
        $sql="select ".(empty($sql_opt['select'])?"*":$sql_opt['select'])." from ".$sql_opt['from'];
        if(false==empty($sql_opt["join"])){
            $sql.=" ".implode(" ",$sql_opt["join"]);
        }
        if(empty($sql_opt["orWhere"])==false){
            if(empty($sql_opt['where'])){
                $sql_opt['where']=["or"=>$sql_opt["orWhere"]];
            }else{
                $sql_opt['where']=["and"=>[$sql_opt['where'],["or"=>$sql_opt["orWhere"]]]];
            }
        }
        if(empty($sql_opt['where'])){

        }else if(is_array($sql_opt['where'])){
            $sql.=" where ".self::parse($sql_opt['where']);
        }else{
            $sql.=" where ".$sql_opt['where'];
        }
        if(empty($sql_opt["andWhere"])==false){
            if(empty($sql_opt['where'])){
                $sql.=" where ".implode(" and ",$sql_opt["andWhere"]);
            //    $sql_opt['where']=$sql_opt["andWhere"];
            }else{
                $sql.=" and ".implode(" and ",$sql_opt["andWhere"]);
            //    $sql_opt['where']=array_merge($sql_opt['where'],$sql_opt["andWhere"]);
            }
        }
        if(empty($sql_opt["orWhere"])==false){
            if(empty($sql_opt['where'])&&empty($sql_opt["andWhere"])){
                $sql.=" where ".implode(" or ",$sql_opt["orWhere"]);
            }else{
                $sql.=" or ".implode(" or ",$sql_opt["orWhere"]);
            }
        }
        if(false==empty($sql_opt['group'])){
            $sql.=" group by ".$sql_opt['group'];
        }
        if(false==empty($sql_opt['order'])){
            $sql.=is_array($sql_opt['order'])?self::parse($sql_opt['order'],"order"):(" order by ".$sql_opt['order']);
        }
        if(false==empty($sql_opt['limit'])){
            $sql.=" limit ".$sql_opt['limit'];
            if(false==empty($sql_opt['offset'])){
                $sql.=" offset ".$sql_opt['offset'];
            }
        }
        return $sql;
    }
    static public function parse($array,$part="where"){

        $condition=[];
        foreach ($array as $k=>$val){
            if($k===0){
                if(empty($array[1])){
                    return self::parse($val);
                }
                $array_arg=array($val);
                foreach ($array[1] as $key=>$value) {
                    if(empty($array[2])){
                        if(is_array($value)){
                            $arg_=array();
                            $arg_c=count($value);
                            $arg_[]=trim(str_repeat("%s,", $arg_c),",");
                            $arg_[]=$value;
                            $array_arg[]=self::parse($arg_);
                        }else{
                            $array_arg[]="'".addslashes($value)."'";
                        }
                    }else if(is_array($array[2])){
                        if(is_array($value)){
                            $arg_=array();
                            $arg_c=count($value);
                            $arg_[]=trim(str_repeat("%s,", $arg_c),",");
                            $arg_[]=$value;
                            $arg_[]=array_fill(0, $arg_c, $array[2][$key]);
                            $array_arg[]=self::parse($arg_);
                        }else if($array[2][$key]==="int"){
                            $array_arg[]=intval($value);
                        }else if($array[2][$key]==="normal"){
                            $array_arg[]=addslashes($value);
                        }else if($array[2][$key]==="float"){
                            $array_arg[]=floatval($value);
                        }else if($array[2][$key]==="native"){
                            $array_arg[]=$value;
                        }else{
                            $array_arg[]="'".addslashes($value)."'";
                        }

                    }
                }
                return call_user_func_array("sprintf",$array_arg);
            }
            switch ($k){
                case 'like':
                    $condition[]=self::parse(["{$val[0]} like %s",['%'.$val[1].'%'],isset($val[2])?[$val[2]]:null]);
                    break;
                case 'not like':
                    $condition[]=self::parse(["{$val[0]} not like %s",['%'.$val[1].'%'],isset($val[2])?[$val[2]]:null]);
                    break;
                case 'in':
                    $condition[]=self::parse(["{$val[0]} in (%s)",[$val[1]],isset($val[2])?[$val[2]]:null]);
                    break;
                case 'not in':
                    $condition[]=self::parse(["{$val[0]} not in (%s)",[$val[1]],isset($val[2])?[$val[2]]:null]);
                    break;
                case 'or':
                    $or_w=[];
                    foreach($val as $vk=>$or_item){
                        if(is_string($vk)){
                            $or_w[]=self::parse([$vk=>$or_item]);
                        }else{
                            $or_w[]=self::parse($or_item);
                        }

                    }
                    $condition[]='('.implode(' or ',$or_w).')';
                    break;
                case 'and':
                    $and_w=[];
                    foreach($val as $vk=>$and_item){
                        if(is_string($vk)){
                            $and_w[]=self::parse([$vk=>$and_item]);
                        }else{
                            $and_w[]=self::parse($and_item);
                        }
                    }
                    $condition[]=implode(' and ',$and_w);
                    break;
                case '>':
                case '>=':
                case '<':
                case '<=':
                case '<>':
                    $condition[]=self::parse(["{$val[0]} $k %s",[$val[1]],isset($val[2])?[$val[2]]:null]);
                    break;
                default:
                    //if(in_array($val[1],["int","normal","float","native"])){
                    //    $condition[]=self::parse(["$k = %s",[$val[0]],[$val[1]]]);
                    //    break;
                    //}
                    if($part=='order'){
                        $condition[]=$k." ".$val;
                        break;
                    }
                    if(is_array($val)){
                        $condition[]=self::parse(["{$k} in (%s)",[$val]]);
                        break;
                    }
                    $condition[]=self::parse(["$k = %s",[$val]]);
                    break;
            }
        }
        if(empty($condition))return '';
        if($part=='order'){
            return  ' order by '.implode(',',$condition);
        }
        return implode(" and ",$condition);
    }
}