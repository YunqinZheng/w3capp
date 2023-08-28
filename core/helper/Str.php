<?php
namespace w3c\helper;
class Str{

//人性化时间
    static public function human_time($time){
        $tdf=W3CA_UTC_TIME-$time;
        if($tdf<60){
            return '刚刚';
        }
        if($tdf<3600){
            return ceil($tdf/60).'分钟前';
        }
        if($tdf<3600*24){
            return floor($tdf/3600).'小时前';
        }
        if($tdf<3600*24*2){
            return '昨天'.ltrim(date("H",$time),'0')."时";
        }
        if($tdf<3600*24*3){
            return '前天'.ltrim(date("H",$time),'0')."时";
        }
        if($tdf<3600*24*30){
            return floor($tdf/3600/24).'天前';
        }
        if($tdf<3600*24*30*6){
            return date('m月d日',$time);
        }
        return date('y年m月',$time);
    }
    static public function arrayParse($content){
        if(W3CA_DB_CHAR_SET=='GBK'&&preg_match("/[^\\x00-\\x7F]/", $content)){
            $content=iconv(W3CA_DB_CHAR_SET, "utf8", $content);
            $input=json_decode($content,true);
            foreach ($input as &$v){
                foreach($v as $i=>$v2){
                    $v[$i]=iconv("utf8",W3CA_DB_CHAR_SET,$v2);
                    /*if(preg_match("/[^\\x00-\\x7F]/", $v2)){
                        $v[$i]=iconv("utf8",W3CA_DB_CHAR_SET,$v2);
                    }else{
                        $v[$i]=$v2;
                    }*/
                }
            }
        }else{
            $input=json_decode($content,true);
        }
        return $input;
    }
    static public function spencode($str){
        return strtr($str,array("\n"=>"%0D","%"=>"%25","&"=>"26%",";"=>"%3B",":"=>"%3A","\""=>"%22","'"=>"%27","\\"=>"%5C","{"=>"%7B","}"=>"%7D","<"=>"%3C","="=>"%3D",">"=>"%3E"));
    }

    static public function toJson($array){
        if($array instanceof \W3cAppDataApi){
            return self::toJson($array->toArray());
        }
        if(W3CA_DB_CHAR_SET=="utf8"&&is_array($array))return json_encode($array);
        $echo=$start_=$end_='';
        foreach ($array as $key => $value) {
            if($start_==''){
                if($key===0){
                    $start_='[';
                    $end_=']';
                }else{
                    $start_='{';
                    $end_='}';
                }
            }else{
                $echo.=',';
            }
            if($start_=='[')
                $echo.=is_array($value)||$array instanceof \W3cAppDataApi?\w3c\helper\Str::toJson($value):"\"".strtr(str_replace('\\','\\\\',$value),array("\n"=>"\\n","\r"=>"\\r","\t"=>"\\t","\""=>"\\\""))."\"";
            else{
                $key=strtr(str_replace('\\','\\\\',$key),array("\n"=>"\\n","\r"=>"\\r","\t"=>"\\t","\""=>"\\\""));
                $echo.="\"$key\":".(is_array($value)||$array instanceof \W3cAppDataApi?\w3c\helper\Str::toJson($value):"\"".strtr(str_replace('\\','\\\\',$value),array("\n"=>"\\n","\r"=>"\\r","\t"=>"\\t","\""=>"\\\""))."\"");
            }
        }

        return $start_.$echo.$end_;
    }

    static public function gbk_json_utf8($a){
        if(W3CA_DB_CHAR_SET=="utf8")return json_encode($a);
        return iconv("GBK", "utf-8", self::toJson($a));
    }

    static public function htmlchars($s){
        return strtr($s,array("&"=>"&amp;","\""=>'&quot;',"'"=>'&#039;',"<"=>"&lt;",">"=>"&gt;"));
    }
    static public function strcut($string,$start,$len){
        if($start < 0)
            $start = strlen($string)+$start;
        $retstart=$retend=0;
        if(W3CA_DB_CHAR_SET=="GBK"){
            $p = "[".chr(0xa1)."-".chr(0xff)."]+$";
            preg_match("/$p/",substr($string,$start),$res);
            if (isset($res[0]) and fmod(strlen($res[0]),2) == 1)
                $retstart=$start-1;
            $start_=$start+$len;
            if (isset($res[0]) and fmod(strlen($res[0]),2) == 1)
                $retend=$start_-1;
        }else{
            $char_aci = ord($string{$start-1});
            if(223<$char_aci && $char_aci<240)
                $retstart=$start-1;
            else{
                $char_aci = ord(substr($string,$start-2,1));
                if(223<$char_aci && $char_aci<240)
                    $retstart=$start-2;
            }
            /***/
            $start_=$start+$len;
            $char_aci = ord($string{$start-1});
            if(223<$char_aci && $char_aci<240)
                $retend=$start_-1;
            else{
                $char_aci = ord(substr($string,$start-2,1));
                if(223<$char_aci && $char_aci<240)
                    $retend=$start_-2;
            }
        }
        return substr($string,$retstart,$retend-$retstart+1);
    }



    static public function xss_filter($val){
        if(stripos($val, "</script>")||stripos($val, "<frame")||stripos($val, "<iframe")){
            $val='content error:'.strtr($val,array('&'=>'&amp;','"'=>'&#34;','<'=>'&lt;','>'=>'&gt;'));
        }
        if(preg_match('/<[^>]+\son[^>]{2,12}=/i', $val)||stripos($val, "javascript:")>0)
            $val=str_ireplace(array('on','javascript:'), array("<span>o</span>n","java<span>script:</span>"), $val);
        return $val;
    }
    static public function strap_xss($v){
        $vh=explode("<", $v);
        $rstr='';
        foreach ($vh as $i=> $value) {
            if(empty($value))continue;
            if(strpos($value,">")===false){
                $rstr.=$value;
                continue;
            }
            if($i>0)
                $value='<'.$value;
            if(stripos($value, 'script ')==1||
                stripos($value, 'iframe ')==1||
                stripos($value, 'frame ')==1||
                stripos($value, '/script')==1||
                stripos($value, '/iframe')==1||
                stripos($value, '/frame')==1
            ){
                continue;
            }
            $rstr.=preg_replace('/href\s*=\s*("|\')javascript/i','x=$1',preg_replace('/on\w+\s*=\s*("|\')/i',"x=$1", $value));
        }
        return $rstr;
    }


    static public function guid(){
        if (function_exists('com_create_guid')){
            return trim(com_create_guid(),"{}");
        }else{
            mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
            $charid = strtoupper(md5(uniqid(rand(), true)));
            $hyphen = chr(45);// "-"
            $uuid = substr($charid, 0, 8).$hyphen
                .substr($charid, 8, 4).$hyphen
                .substr($charid,12, 4).$hyphen
                .substr($charid,16, 4).$hyphen
                .substr($charid,20,12);
            return $uuid;
        }
    }
}