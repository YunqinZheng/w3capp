<?php
namespace api\block\query;

use cms\model\ContentType;
use cms\model\PageBlock;
use w3capp\helper\Str;
class SelectMod extends Selector{
    function getPrototypeForm($is_from_model=false){
        $columns=array(
	        "id"=>array("form_input"=>"hidden"),
	        "type"=>array("form_input"=>"hidden","col_name"=>"模块类型","def_value"=>"query\\selectMod"),
	        "mark"=>array("col_name"=>"调用标记","form_input"=>"text"),
	        "remarks"=>array("col_name"=>"说明","form_input"=>"text"),
	        "update_time"=>array("col_name"=>"缓存时间","form_input"=>"diycode","def_value"=>"0","diycode"=>'<div><span class="labt">更新时间:</span><p class="inct"><input class="short_txt" id="update_time" name="update_time" value="?col_value?"/>分钟(-1:不更新,0:无缓存)</p></div>'),
	        "tpl"=>array("col_name"=>"显示模板","form_input"=>"hidden","def_value"=>PageBlock::tplList($this->info('type')))
	    );
        $columns['content_model']=array("col_name"=>"内容模型","form_input"=>"diycode","def_value"=>'');
        $columns['select_columns']=array("col_name"=>"查询字段","form_input"=>"diycode","def_value"=>'');
        $columns['data_filter']=array("col_name"=>"数据过滤","form_input"=>"text","def_value"=>'id');
        $default_val=Str::spencode(Str::toJson($columns));
        return array("id"=>$columns['id'],"type"=>$columns['type'],'select_arg'=>array("form_input"=>"diycode","diycode"=>'<div id="arg_view"><dl><dd></dd></dl></div><div id="select_arg"><div class="default_val hide">'.
            $default_val.'</div><input name="select_arg" type="hidden"/><p class="hide">{col_value}</p><script src="{URL_ROOT}static/script/select_mod.js"></script></div>'));
    }
    function onCheckPrototype(&$return){

        $interface=$this->info("interface");
        if($interface){
            $this->parse_result['interface']=Str::arrayParse($interface);
        }
        if($_POST['select_arg']){
            $this->parse_result['select_arg']=Str::arrayParse($_POST['select_arg']);
            
            if($_POST['model_identify']){//创建
                $table='[pre]'.ContentType::contentTable($_POST['model_identify']);
                $sp_c=array();
                foreach ($_POST['select_column'] as $format){
                    if($format{0}=='%'||$format{0}=='='){
                        if($_POST['edit_limit'])$format{0}='=';
                        $sp_c_=$this->columnReplace(array('',$format));
                        if($sp_c_){
                            $sp_c[]=$sp_c_;
                        }
                    }else{
                        $sp_c[]=$format;
                    }
                    
                }
                $sp_cs=implode(",", $sp_c);
                $select_where=Str::arrayParse($_POST['select_where']);
                $where=$this->parseWhere($select_where,$_POST['where_option']);
                $select_order=Str::arrayParse($_POST['select_order']);
                $order=$this->parseOrder($select_order,$_POST['order_option']);
                $limit=" limit ";
                if($this->parse_result['select_arg']['page_index']){
                    //$this->parse_result['select_arg']['page_index']=array('col_name'=>"第几页数据","form_input"=>"text","def_value"=>"1");
                    $limit.="{page_index}";
                    $this->parse_result['interface']["{page_index}"]=0;
                }else{
                    $page_index=intval($_POST['page_index']);
                    if($page_index>=1)$page_index--;
                    $limit.=$page_index;
                }
                if($this->parse_result['select_arg']['page_size']){
                    //$this->parse_result['select_arg']['page_size']=array('col_name'=>"每页数据条数","form_input"=>"text","def_value"=>"1");
                    $limit.=',{page_size}';
                    $this->parse_result['interface']["{page_size}"]=10;
                }else{
                    $page_size=intval($_POST['page_size']);
                    $limit.=','.$page_size;
                }
                $return['pro_value']=($_POST['edit_limit']?("select ".($sp_cs?('id,'.$sp_cs):"*")):("select *".($sp_cs?",".$sp_cs:"")))." from $table $where $order $limit";
                if($this->parse_result['columns'])
                    $return['data_desc']=serialize($this->parse_result['columns']);
            }else if(!$this->info('id')){
                $return= array("error"=>1,"msg"=>"未指定内容模型！");
                return;
            }
            if($this->parse_result['select_arg']){
                foreach ($this->parse_result['select_arg'] as $argKey=>$argVal){
                    if(isset($_POST[$argKey])){
                        $argVal['def_value']=$_POST[$argKey];
                        $this->parse_result['select_arg'][$argKey]=$argVal;
                    }
                }
                $return['select_arg']=Str::spencode(Str::toJson($this->parse_result['select_arg']));
            }
			if(!$return['select_arg']){
				$return= array("error"=>1,"msg"=>"select_arg null");
				return;
			}
			
            if($_POST['context_edit']){
                $return['context_edit']=$_POST['context_edit'];
            }
            
        }else if(!$this->info('id')){
            $return= array("error"=>1,"msg"=>"此模块类型只能在内容模型管理中生成");
            return;
        }
        
        if($_POST['where_option']){
            $this->parse_result['interface']['{where}']=$this->parse_result['interface']['{w'.$_POST['where_option'].'}'];
        }
        if($_POST['order_option']){
            $this->parse_result['interface']['{order}']=$this->parse_result['interface']['{o'.$_POST['order_option'].'}'];
        }
        if($_POST['page_index']=="?"){
            $this->parse_result['interface']["{page_index}"]='?';
        }else{
            $this->parse_result['interface']["{page_size}"]=intval($_POST['page_index']);
        }
        if($_POST['page_size']=="?"){
            $this->parse_result['interface']["{page_size}"]='?';
        }else{
            $this->parse_result['interface']["{page_size}"]=intval($_POST['page_size']);
        }
        $return["interface_arg"]=Str::toJson($this->parse_result['interface']);
        return $return;
        /*
        if($this->info("id")){
            
        }else{
            
            return $return;
        }
        */
    }
    protected function parseWhere($where_info,$isOption){
        if(empty($where_info['set'])){
            return '';
        }
        $where_all=array("deprecated=0");
        foreach ($where_info['set'] as $gk=>$where_set){
            $where=array();
            foreach ($where_set as $s){
                $sign="";
                $val="";
                switch ($s['sign']){
                    case 'eq':
                        $sign=" = ";
                        $val=" '".$s['val']."' ";
                        break;
                    case '!eq':
                        $sign=" <> ";
                        $val=" '".$s['val']."' ";
                        break;
                    case '!null':
                        $sign=" is not null ";
                        break;
                    case 'null':
                        $sign=" is null ";
                        break;
                    case 'lg':
                        $sign=" > ";
                        $val=" '".$s['val']."' ";
                        break;
                    case 'lt':
                        $sign=" < ";
                        $val=" '".$s['val']."' ";
                        break;
                    case 'lgeq':
                        $sign=" >= ";
                        $val=" '".$s['val']."' ";
                        break;
                    case 'lteq':
                        $sign=" <= ";
                        $val=" '".$s['val']."' ";
                        break;
                    default:
                        $val=" '%".$s['val']."%' ";
                        $sign=" like ";
                        break;
                }
                $where[]=$s['column'].$sign.$val;
            }
            $str_w=implode(" and ", $where);
            if($isOption||strpos($str_w, "?")!==false){
                $wkey='{w'.$gk.'}';
                $this->parse_result['interface'][$wkey]=$str_w;
                $where_all[]=$wkey;
            }else{
                $where_all[]=$str_w;
            }
            
        }
        if($isOption){
            $this->parse_result['interface']['{where}']=$this->parse_result['interface']['{wg0}'];
            $glist=array();
            foreach ($where_info['group'] as $k=>$g){
                $glist['g'.$k]=$g;
            }
            $glist['']="无";
            $this->parse_result['select_arg']['where_edit_arg']=array("col_name"=>"数据条件","form_input"=>"radio","value_list"=>$glist,"def_value"=>"");
            return ' where {where}';
        }else
            return ' where '.implode(" or ",$where_all);
    }
    function parseOrder($order_info,$isOption){
        if(empty($order_info['set'])){
            return '';
        }
        $order_all=array();
        foreach ($order_info['set'] as $gk=>$order_set){
            $order=array();
            foreach ($order_set as $s){
                $order[]=$s['column'].' '.($s['type']=='1'?'asc':'desc');
            }
            $str_o=implode(" , ", $order);
            if($isOption){
                $okey='{o'.$gk.'}';
                $this->parse_result['interface'][$okey]=$str_o;
                $order_all[]=$okey;
            }else{
                $order_all[]=$str_o;
            }
        
        }
        if($isOption){
            $this->parse_result['interface']['{order}']=$this->parse_result['interface']['{og0}'];
            $glist=array();
            foreach ($order_info['group'] as $k=>$g){
                $glist['g'.$k]=$g;
            }
            $glist['']="无";
            $this->parse_result['select_arg']['order_edit_arg']=array("form_input"=>"radio","col_name"=>"数据排序","value_list"=>$glist,"def_value"=>"");
            return ' order by {order}';
        }else
            return ' order by '.implode(" , ",$order_all);
    }

}