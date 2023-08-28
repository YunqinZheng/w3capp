<?php
namespace cms\model;
use common\model\BlockExtendRecord;
use common\model\BlockRecord;
use common\model\PageBlockRecord;
use common\model\PageFrameRecord;
use w3c\helper\ContentForm;

class PageBlock extends \W3cCore {
    static public function encodeAttr($info){
        $d=array();
        $new_info=array();
        $info['param_str']='';
        foreach ($info as $k=>$v){
            if(in_array($k, array("id","mark","update_time","type","tpl","data_size","interface_arg","remarks","pro_value","interface_arg","data_desc","context_edit","hidden",'init_hash','init_config','template'))){
                $new_info[$k]=$v;
            }else{
                $d[$k]=$v;
            }
        }
        //$param=$d;
        $new_info['param_str']=urlencode(serialize($d));
        return $new_info;
    }
    static public function decodeAttr($info){
        if(false==empty($info['param_str'])){
            if(strpos($info['param_str'],'%')){
                $info['param_str']=urldecode($info['param_str']);
            }
            $d=unserialize($info['param_str']);
            foreach ($d as $k=>$v){
                $info[$k]=$v;
            }
            //$param=$d;
            unset($info['param_str']);
        }
        return $info;
    }
	/**
	 * 成功返回类名
	 * @param string $type
	 */
	protected static function includeClass($type){
        $file=W3CA_MASTER_PATH."app/api/block/".str_replace("\\","/",$type).".php";
        $class="\\api\\block\\$type";

	    //if(file_exists($file)){
	         require_once $file;
	         return $class;
	    //}else{
	    //     return false;
	    //}
	}

    /**
     * 临时模板文件
     * @param $id
     * @param $content 模板内容
     * @return string
     */
	static function tplCacheFile($id,$content=""){

		$file=W3CA_PATH.'data/cache/block/tpl_'.$id;
        if($content){
            $ext=BlockExtendRecord::record(['block_id'=>$id],true);
            $ext->template=$content;
            if($ext->save()){
                file_put_contents($file,$content);
            }
        }
		if(file_exists($file)){
            return $file;
        }else{
            $ext=BlockExtendRecord::record(['block_id'=>$id]);
            file_put_contents($file,empty($ext)?"<!--空白模板-->":$ext->template);
        }
		return $file;
	}

    /**
     * 保存模版
     */
    function saveTemplate($block_id,$content){
        $this->tplCacheFile($block_id,$content);
        $bex=BlockExtendRecord::record(['block_id'=>$block_id]);
        $bex->template=$content;
        return $bex->save();
    }
	/**
	 * 实列化一个模块
     * @return \api\block\BlockTpl
	 */
	static function newBlock($info){
	    $class_=self::includeClass($info['type']);
		if($class_){
		    $b= new $class_($info);
		    if(false==empty($info['id'])){
		        $b->setCacheMark(self::cacheMark($info['id']));
                $b->setTplFile((empty($info['tpl'])||$info['tpl']==-1)?self::tplCacheFile($info['id']):self::getTplFile($info['type'],$info['tpl']));
            }
            return $b;
		}
		throw new \Exception($class_.",newBlock_error:".var_export($info,true));
		return null;
	}

	protected static function getTplDir($sub_dir){
	    return W3CA_MASTER_PATH."app/api/block/".$sub_dir."/tpl/";
	}

    /**
     * 固定模板
     * @param $type
     * @param $tpl
     * @return string
     */
	static function getTplFile($type,$tpl){
	    if(strpos($type,"\\")){
	        list($type,$class)=explode("\\", $type);
	    }
	    return self::getTplDir($type).$tpl;
	}
	static function tplList($type){
	    if(strpos($type,"\\")){
	        list($type,$class)=explode("\\", $type);
	    }
	    $tpl_dir=self::getTplDir($type);
	    $d=opendir($tpl_dir);
	    $tpls=array();
	    if($d){
	        while (($file=readdir($d))!== false) {
	            if(strpos($file,".inc")){
	                $tpls[$file]=str_replace(".inc","",$file);
	            }
	        }
	    }
	    closedir($d);
	    return $tpls;
	}
	//清除数据缓存
    function clearBlockCache($bid){
        $m=$this->cacheMark($bid);
        $this->cacheDelete($m);
        $m2='x'.$m;
        if($this->cacheExists($m2)){
            $ms=explode("|",$this->cacheValue($m2));
            foreach ($ms as $_m){
                $this->cacheDelete($m.$_m);
            }
            $this->cacheDelete($m2);
        }
    }
    static function cacheMark($id){
	    return "block_cache".$id;
    }
	/**
	 * 读取模块类型列表
	 */
	function getInis($uncache){
		$bt=array("BlockTpl"=>array("name"=>"默认","icon_url"=>"static/image/default.jpg","class"=>"","has_tpl"=>true,"load_data"=>false,"has_cache"=>false));

		if($uncache||$this->cacheExists("block_type_store")==false){
			$dir=W3CA_MASTER_PATH."app/api/block";
			$d=dir($dir);
			while(false !== ($file = $d->read())) {
			    $full_path=$dir."/".$file;
			    if($file!="."&&$file!=".."&&is_dir($full_path)){
			        $ini=array();
			        include $full_path.'/block.ini.php';
			        if($ini){
			            if(isset($ini[0])){
			                foreach ($ini as $class_block){
			                    $icon_file=$full_path."/assets/".$class_block['class'].".png";
                                if(file_exists($icon_file)&&copy($icon_file,W3CA_PATH."data/theme/$file-".$class_block['class'].".png")){
                                    $class_block['icon_url']="data/theme/$file-".$class_block['class'].".png";
                                }else{
                                    $class_block['icon_url']="static/image/block.png";
                                }
			                    $bt[$file."\\".$class_block['class']]=$class_block;
			                }
			            }else{
			                $bt[$file]=$ini;
			            }
			        }
			    }
				
			}
			$d->close();
			$this->cacheSave("block_type_store",serialize($bt),36000);
			return $bt;
		}else{
			return unserialize($this->cacheValue("block_type_store"));
		}
	}

	function blockTypes($uncache){
	    $inis=$this->getInis($uncache);
	    $type=array();
	    foreach ($inis as $k=>$v){
	        $type[$k]=$v['name'];
	    }
	    return $type;
	}

    function frameCss($ids){
	    $pflist=PageFrameRecord::findAllData(["frame_id"=>$ids,"theme_id"=>$this->theme_id]);
	    $list=[];
	    $bcss=[];
	    foreach($pflist as $pf){
	        $list[$pf['frame_id']]=$pf['css_name'];
	        if($pf['block_css']){
                $bcss[$pf['frame_id']]=explode(",",$pf['block_css']);
            }
        }
	    return ['f_css'=>$list,"b_css"=>$bcss?$bcss:[]];
    }


	function frame($area_id){
        return PageFrame::record(['frame_id'=>$area_id,"theme_id"=>$this->theme_id],true);
    }
    function saveHiddenBlock($filevar,$mark,$remarks,$type,$tpl,$init_value){

        $data=array('remarks'=>$remarks,'type'=>$type,'tpl'=>'-1','update_time'=>'-1','hidden'=>'1','init_hash'=>"");

        $block_obj=$this->newBlock($data);
        $propertes=$block_obj->getPrototypeForm();
        $input_init=['remarks'=>$remarks,'mark'=>$mark,"column"=>[],"col_name"=>[],"form_input"=>[],"form_input"=>[],"def_value"=>[],"value_set"=>[]];
        foreach ($propertes as $col_name=>$val){
            if(array_key_exists($col_name,$data))continue;
            $input_init['column'][]=$col_name;
            if(empty($val['def_value'])){
                $val['def_value']='';
            }
            if(empty($val['value_set'])){
                $val['value_set']='';
            }
            if(empty($val['col_name'])){
                $val['col_name']='';
            }
            $input_init['col_name'][]=$val['col_name'];
            $input_init['form_input'][]=$val['form_input'];
            $input_init['def_value'][]=$val['def_value'];
            $input_init['value_set'][]=$val['value_set'];
        }
        foreach ($init_value as $column=>$item){
            if(is_array($item)&&false==empty($item['form_input'])){
                if(array_key_exists($column,$data))continue;
                $input_init['column'][]=$column;
                $input_init['col_name'][]=$item['col_name'];
                $input_init['form_input'][]=$item['form_input'];
                $input_init['def_value'][]=$item['def_value'];
                $input_init['value_set'][]=$item['value_set'];
            }else{
                $data[$column]=$item;
            }
        }

        $row=BlockRecord::firstAttr(['mark'=>$mark]);
        $clear_c=true;
        if(empty($row['id'])){
            $data['mark']=$mark;
            $data['file_var']=$filevar;
            $info_def=$this->initPrototype($input_init);
            $data=array_merge($info_def,$data);
            $data['template']=$tpl;
            $block_obj->onCheckPrototype($data);
            if($data['error']){
                throw new \Exception($data['msg']);
            }
            $data['id']=$this->saveBlock($data,0);
            $block_obj->onSaved($data['id']);
        }else{
            if($row['type']!=$type||$data['init_hash']!=$row['init_hash']){
                unset($row['param_str']);
                $row=self::decodeAttr($row);
                $data=array_merge($row,$data);
                $data['file_var']=$filevar;
                $data['type']=$type;
                unset($data['id']);
                $info_def=$this->initPrototype($input_init);
                $data=array_merge($info_def,$data);
                $data['template']=$tpl;
                $block_obj->onCheckPrototype($data);
                if(false==empty($data['error'])){
                    throw new \Exception($data['msg']);
                }

                $this->saveBlock($data, $row['id']);
                $block_obj->onSaved($row['id']);
            }else if($type=="html\\html"){
                $row=$this->decodeAttr($row);
                $data=array_merge($row,$data);
                $clear_c=false;
            }
            $data['id']=$row['id'];
        }
        $tplfile=$this->tplCacheFile($data['id']);
        if($tplfile){
            file_put_contents($tplfile, $tpl);
        }
        if($clear_c)
            $this->clearBlockCache($data['id']);
    }
    static function copyBlock($mark,$new,$new_attr){
        $new=BlockRecord::record(['mark'=>$new],true);
        if($new->primary()){
            return $new;
        }
        $from_b=BlockRecord::record(['mark'=>$mark]);
        if(empty($from_b)){
            return null;
        }
        $attr=self::decodeAttr($from_b->getAttributes());
        $new_attr=array_merge($attr,$new_attr);
        $new_attr['mark']=$new;
        unset($new_attr['id']);
        $info=self::encodeAttr($new_attr);
        $new->setAttributes($info);
        if($new->save()){
            $bexr=BlockExtendRecord::record(['block_id'=>$from_b->primary()]);
            if(empty($bexr))return $new;

            $ex_attr=$bexr->getAttributes();
            $new_ex=new BlockExtendRecord();
            $ex_attr['block_id']=$new->primary();
            $new_ex->setAttributes($ex_attr);
            $new_ex->setAttributes($info);
            if($new_ex->save()==false){
                throw new \Exception("block extend error");
            }
            return $new;
        }
        return null;
    }
	function saveBlock($in,$bid){
	    $info=self::encodeAttr($in);
	    unset($info['id']);
	    $r=0;

	    if($bid){// and islock=0
            $block=new BlockRecord(['id'=>$bid]);
            $block->setAttributes($info);
            if($block->save()!==false){
                $r=$bid;
            }
	    }else{
	        if(empty($in['mark'])){
	            return 0;
            }
            $block=new BlockRecord($info);
	        if($block->save()){
	            $r=$block->primary();
            }
	    }
	    if($r&&false==empty($info['init_config'])){
	        $bexr=BlockExtendRecord::record(['block_id'=>$r],true);
            $bexr->setAttributes($info);
            if($bexr->save()==false){
                throw new \Exception("block extend error");
            }
        }
		return $r;
	}
	function deleteData($ids){
	    foreach ($ids as $id){
	        $this->clearBlockCache($id);
        }
	    $rs=true;
        $bexs=BlockExtendRecord::findAll(['block_id'=>$ids]);
	    foreach($bexs as $bx){
	        $areas=explode(",",$bx->areas);
            $frames=PageFrame::findAll(["frame_id"=>$areas]);
            $block=BlockRecord::record(['id'=>$bx->block_id]);
	        foreach ($frames as $f){
                $f->removeBlock($block['mark']);
            }
            $bx->delete();
            $rs=$rs&&$block->delete();
        }
		return $rs;
	}
	//更新页面上的模块
	function updateBlock($file_name,$load_blocks){
        $pblocks=PageBlockRecord::findAllData(["file_id"=>$file_name]);
        $block_in_page=[];
        $del_ids=[];
        foreach ($pblocks as $pb){
            if(in_array($pb['block_mark'],$load_blocks)){
                $block_in_page[]=$pb['block_mark'];
            }else{
                //删除不再引用的模块
                PageBlockRecord::deleteAll(['id'=>$pb['id']]);
                $br=BlockRecord::firstAttr(['mark'=>$pb['block_mark']]);
                if($br['hidden']&&empty(PageBlockRecord::firstAttr(['block_mark'=>$pb['block_mark']]))){
                    $del_ids[]=$br['id'];
                }
            }
        }
        if($del_ids)
            $this->deleteData($del_ids);
        foreach ($load_blocks as $mark){
            if(false==in_array($mark,$block_in_page)){
                $pbr=new PageBlockRecord(["file_id"=>$file_name,'block_mark'=>$mark,"mark_time"=>time()]);
                $pbr->save();
            }
        }
    }
    //模块列表
    function listOf($marks){
        $bd=BlockRecord::findAllData(["mark"=>$marks]);
        $rs=[];
        foreach ($bd as $b_data){
            $rs[]=self::decodeAttr($b_data);
        }
        return $rs;
    }
    function formData($id,$post){
	    $info=BlockRecord::firstAttr(['id'=>$id]);
	    $info=self::decodeAttr($info);
	    foreach ($info as $c=>&$value){
	        if(array_key_exists($c,$post)){
                $value=$post[$c];
            }
        }
        return $info;
    }
    /**
     * 属性值
     */
    function initPrototype($input){
        $info_def=[];
        $pro_form=['mark'=>["col_name"=>"调用标记","form_input"=>"text","def_value"=>$input['mark']],
            "remarks"=>["col_name"=>"模块说明","form_input"=>"text","def_value"=>$input['remarks']]];
        foreach($input['column'] as $key=>$column){
            $pro_form[$column]["col_name"]=$input['col_name'][$key];
            $pro_form[$column]["form_input"]=$input['form_input'][$key];
            $info_def[$column]='';
            if($pro_form[$column]["form_input"]=="diycode"){
                $pro_form[$column]["diycode"]=$input['value_set'][$key];
            }else if($pro_form[$column]["form_input"]=="audio"||$pro_form[$column]["form_input"]=="checkbox"||$pro_form[$column]["form_input"]=="select"||$pro_form[$column]["form_input"]=="mselect"){
                $def_list=ContentForm::valueList($input['value_set'][$key]);
                $pro_form[$column]['value_list']=$def_list['value_list'];
                $info_def[$column]=$pro_form[$column]['def_value']=$def_list['def_value'];
            }else{
                $info_def[$column]=$pro_form[$column]["def_value"]=$input['value_set'][$key];
            }
            if(false==empty($input['def_value'][$key])){
                $info_def[$column]=$pro_form[$column]["def_value"]=$input['def_value'][$key];
            }
            if($column=="type"){
                $info_def[$column]=$pro_form[$column]["def_value"]=$input['blocktype'];
            }
        }

        $info_def['mark']=$input['mark'];
        $info_def['remarks']=$input['remarks'];
        $info_def["interface_arg"]="";

        $info_def["pro_value"]="";
        $edit_desc=[];
        if(array_key_exists('new_column',$input))
        foreach($input['new_column'] as $key=>$column){
            if(empty($column))continue;
            $col_set=["col_name"=>$input['new_col_name'][$key]];
            $col_set["form_input"]=$input['new_form_input'][$key];
            if($col_set["form_input"]=="diycode"){
                $col_set["diycode"]=$input['new_value_set'][$key];
            }else if($col_set["form_input"]=="audio"||$col_set["form_input"]=="checkbox"||$col_set["form_input"]=="select"||$col_set["form_input"]=="mselect"){
                $def_list=ContentForm::valueList($input['new_value_set'][$key]);
                $col_set['value_list']=$def_list['value_list'];
                $col_set['def_value']=$def_list['def_value'];
            }else{
                $col_set["def_value"]=$input['new_value_set'][$key];
            }
            if(empty($input['data_row'][$key])){
                $col_set['data_row']=0;
                $info_def[$column]=$col_set["def_value"];
            }else{
                $edit_desc[$column]=$col_set;
                $col_set['data_row']=1;
            }
            $pro_form[$column]=$col_set;

        }
        if(false==empty($edit_desc)){
            $info_def['data_columns']=array_keys($edit_desc);
        }
        $info_def["context_edit"]=json_encode($edit_desc);
        $info_def['init_config']=json_encode($pro_form);
        return $info_def;
    }
}
