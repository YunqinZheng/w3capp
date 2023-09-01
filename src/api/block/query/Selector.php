<?php
namespace api\block\query;
use cms\model\BlockData;
use w3capp\helper\Str;

class Selector extends \api\block\BlockTpl{
    protected $parse_result=array();
	function getPrototypeForm(){
	    $form=parent::getPrototypeForm();
	    $form['primary_key']=array("col_name"=>"数据主键","form_input"=>"text","def_value"=>$this->info("primary_key"));
	    $form['zyq_sql']=array("col_name"=>"查询代码","form_input"=>"diycode","def_value"=>Str::htmlchars($this->info("zyq_sql")),"diycode"=>'<div class="diycode"><dl class="formline"><span class="labt">{col_name}:</span></dl><dl class="form_mg"><dd><textarea request class="form-control" name="zyq_sql" placeholder="select * from [pre]table where ...">{col_value}</textarea></dd><dd><span>*</span>不可以出现delete,dorp,truncate等危险操作,用于接口请注意安全控制，有sql注入的风险</dd></dl></div>');
		return $form;
	}
	function onCheckPrototype(&$data){
	    $zyq_sql=$data["zyq_sql"];
	    if(preg_match("/delete|dorp|truncate|insert|create/i", $zyq_sql)){
	    	$data['error']=1;
        	$data['msg']="sql包含非法字符";
			return;
	    }
	    $zyq_sql=preg_replace_callback("/`(%|=[^`]+)`/",array($this,"columnReplace"),$zyq_sql);
	    $zyq_sql=preg_replace_callback('/\{([\w\d]+)\->(int|string|float|date_str|\[)([^\}]*)\}/', array($this,"interfaceArg"), $zyq_sql);
	    $data['interface_arg']=Str::toJson($this->parse_result['interface']);
	    $data['data_desc']="";
		$data['pro_value']=$zyq_sql;
	    if($this->parse_result['columns'])
	        $data['data_desc']=serialize($this->parse_result['columns']);
	}

    /**
     * 正则回调，解析字段部分
     * @param $match
     * @return string
     */
	protected function columnReplace($match){
	    $column="";$args=array();
	    $cl_str=str_replace(array("%","="), "", $match[1]);
	    if(strpos($cl_str, "|")){
	        list($cl_str,$column)=explode("|", $cl_str);
	        $args=explode("@", $cl_str);
	    }else {
	        $args=explode("@", $cl_str);
	        $column=end($args);
	    }
	    if(!$args[0])
	        return "";
	    $this->parse_result['columns'][$column]=$args;
	    if($match[1]{0}=="%")
	        return "";
	    else{
	        return '`'.$column.'`';
	    }
	}

    /**
     *  正则回调，解析接口
     * @param $match
     * @return string
     */
	protected function interfaceArg($match){
	    $arg_mark='{'.$match[1].'}';
	    if($match[2]=="["){
            $this->parse_result['interface'][$arg_mark]=explode(",", trim($match[3],"[]"));
	    }else
	       $this->parse_result['interface'][$arg_mark]=$match[2];
	    return $arg_mark;
	}

    /**
     * 将数据转格式
     * @param $format
     * @param $data
     * @return false|string
     */
	function formatData($format,$data){
	    switch ($format){
	        case "dt":
	            //时间格式
	            if($data[0]=='human')
	                return Str::human_time($data[1]);
	            else
	                return date($data[0],$data[1]);
	            break;
	        case "st":
	            return strip_tags($data[0]);
	            break;
	        //case "mg":
	        //    return strip_tags($data[0]);
	        //    break;
	        case "len":
	            //英文长度截取
	            $len=intval($data[0]);
	            if($len<strlen($data[2])){
	                substr($data[2], 0,$len).$data['1'];
	            }else{
	                return $data[2];
	            }
	            break;
	        case "sub":
	            $len=intval($data[0]);
	            if($len<strlen($data[2])){
	                Str::strcut($data[2], 0,$len).$data[1];
	            }else{
	                return $data[2];
	            }
	            break;
	        case "htmlchars":
	            return Str::htmlchars($data[0]);
	    }
	}

    /**
     * 按照数据类型处理参数
     * @param $data_type
     * @param $v
     * @return false|float|int|string
     */
	private function interfaceVal($data_type,$v){
        if($data_type=="int"){
            return intval($v);
        }else if($data_type=="float"){
            return floatval($v);
        }else if($data_type=='date_str'){
            return strtotime($v);
        }else if($data_type=='string'){
            return addslashes($v);
        }else{
            return $data_type;
        }
    }

    /**
     * 将接口参数解析成sql替换的数组
     * @param $args
     * @return array|mixed
     */
	function interfaceParse($args){
	    $interface_str=$this->info("interface_arg");
	    if($interface_str){
            $inters=Str::arrayParse($interface_str);
	        foreach ($inters as $rep=>&$val){
                $vkey=trim($rep,"{}");
                if(false==array_key_exists($vkey,$args)){
                    $pro_v=$this->info($vkey);
                    if($pro_v){
                        $args[$vkey]=$pro_v;
                    }else{
                        $val="";
                        continue;
                    }

                }
                if(is_array($val)){
	                if(array_key_exists($args[$vkey],$val)){
                        $val=$val[$args[$vkey]];
                    }else{
                        $val=current($val);
                        $val=$this->interfaceVal($val,$args[$vkey]);
                    }
                }else{
                    $val=$this->interfaceVal($val,$args[$vkey]);
                }
            }
            return $inters;
	    }
	    return array();
	}
	protected $format_description=array();
	protected $replace_data=array();
	protected $primary_key;
	protected $default_limit=10;
	/**
	 * 加载数据
	 */
	function loadData($args){
	    $pro_value=$this->info("pro_value");
	    if(!$pro_value)return array();
	    if($this->info("data_desc")){
	        $this->format_description=unserialize($this->info("data_desc"));
	    }
	    $sql_replqce=$this->interfaceParse($args);
	    $sql_replqce['[pre]']=$this->_db()->config['tab_pre'];
	    $sql_replqce["\t\\'"]="'";
	    $pro_value=strtr($pro_value,$sql_replqce);
	    if($this->default_limit&&stripos($pro_value,'limit')===false){
	        $pro_value.=' limit '.$this->default_limit;
	    }
	    try{
            $result=$this->_db()->getIterator($pro_value);
        }catch (\Exception $e){
	        echo $e->getMessage();
	        return null;
        }

        $this->primary_key=$this->info("primary_key");
	    if($this->primary_key){
            $this->replace_data=BlockData::listData($this->info("id"));
	    }
	    if(empty($this->format_description)&&empty($this->replace_data)){
	        return $result;
	    }
	    $result->setDataFilter(function($k,&$val){
            $replace_col=array();
            $data_columns=$this->info('data_columns');
            if(array_key_exists($val[$this->primary_key], $this->replace_data)){
                $val['isReplaceItem']=true;
                if(empty($data_columns)){
                    foreach ($this->replace_data[$val[$this->primary_key]] as $r_col=>$r_val){
                        $val[$r_col]=$r_val;
                        $replace_col[]=$r_col;
                    }
                }else{
                    foreach ($data_columns as $dc){
                        $val[$dc]=$this->replace_data[$val[$this->primary_key]][$dc];
                    }
                    //$val['mtime']=$this->replace_data[$val[$this->primary_key]]['mtime'];
                }
            }
            foreach ($this->format_description as $col=>$param_list){
                if(in_array($replace_col, $col))continue;
                $params=array();
                foreach ($param_list as $pi=>$param_key){
                    if($pi==0)continue;
                    if($param_key{0}=='&'){
                        $params[]=substr($param_key,1);
                    }else
                        $params[]=$val[$param_key];
                }
                $val[$col]=$this->formatData($param_list[0],$params);
            }
        });
	    return $result;
	}

	
}
