<?php
namespace cms\controller;
use cms\model\PageBlock;
use common\model\ChannelFormRecord;
use common\model\SiteConfig;
use cms\model\ContentType;
use w3capp\helper\ContentForm;
use cms\model\Material;
use w3capp\helper\Uploader;
use w3capp\W3cApp;
class modelCtrl extends mainCtrl{
	protected $content_model;
	protected $content_block;

	/**
	 * 内容模型列表
	 */
	function index($page=0){
		$html=$this->_tpl("model/index");
		$records=ContentType::adaptTo([])->limit(10,intval($page));
		$html->ctypes=$records->selectAll([]);
		$html->output();
	}
	function import(){
		if(false==empty($_POST)){
			if(empty($_POST['file_url'])){
                return $this->_view_return(array("msg"=>"no file_url!","error"=>1));
			}
			$file=W3CA_MASTER_PATH.Material::mainDir().$_POST['file_url'];
			if(file_exists($file)){
				$ct=new ContentType();
				if($ct->import(false==empty($_POST['import_im']),$file)){
					unlink($file);
                    return $this->_view_return(array("msg"=>"导入成功!","action"=>"import_ok","error"=>0));
				}else{
                    return $this->_view_return(array("msg"=>"导入错误!".implode(",",$ct->getError()),"error"=>1));
				}
			}else{
                return $this->_view_return(array("msg"=>"没有找到导入文件!","error"=>1));
			}
		}else{
			$html=$this->_tpl("model/import");
			$html->title="导入模型";
            return $this->_view_return($html);
		}
	}
	function import_file(){

		$store_file=rand(10,99)."_".time().".tmp";
		$upload_=new Uploader();
        $upload_->init(Material::mainDir(),null,true,2048*1024);
		if($upload_->set_input_file("file")){
			$uinf=$upload_->save_to($store_file,true);
            return $this->_json_return(0,"",["file"=>$uinf->save_as]);
		}
        return $this->_json_return(1,"error");
	}
	function export($mark){
		$ct=ContentType::record(["content_mark"=>$mark]);
		if(empty($ct)){
			die("content_mark error!");
		}
        $content= "-- ".json_encode($ct->getAttributes())."\n";
        $content.= $ct->exportSql();
		if(self::$app->holder_response){
            return self::$app->setResponse(200,["Content-Type"=>"text/plain;",
                "Content-Disposition"=>"p_w_upload; filename=".$mark.'.sql',
                "Pragma"=>"no-cache","Expires"=>"0"],$content);

        }else{
            header("Content-Type:text/plain;");
            header("Content-Disposition: p_w_upload; filename=".$mark.'.sql');
            header("Pragma:no-cache");
            header("Expires:0");
            echo $content;

            exit;
        }
	}
	/**
	 * 删除模型，模型表不会删除
	 */
	function del_model(){

		if(empty($_POST['rid'])||false==is_array($_POST['rid'])){
            return $this->_view_return(array("msg"=>"未选择要删除的数据!","error"=>1));
		}
		if(ContentType::deleteAll(['id'=>$_POST['rid']]))
		{
            return $this->_view_return(array("msg"=>"删除成功!","action"=>"reload","error"=>0));
		}else{
            return $this->_view_return(array("msg"=>"删除出错!","error"=>1));
		}
	}
	function add_model(){
		
		if(false==empty($_POST)&&$_POST['type_name']&&$_POST['mark']){
			if(empty($_POST['no_tabel'])){
				if(!ContentType::createForm($_POST['mark'])){
                    return $this->_view_return(array("msg"=>"表创建失败".$this->errors,"error"=>1));

				}
			}
			if(ContentType::addType($_POST['mark'],$_POST['type_name'],intval($_POST["member_publish"]))){
                return $this->_view_return(array("msg"=>"添加成功","action"=>"reload","error"=>0));
			}else{
                return $this->_view_return(array("msg"=>"添加出错","error"=>1));
			}
		}else{
		    $html=$this->_tpl("model/input");
		    $html->title="添加内容模型";
            return $this->_view_return($html);
		}
	}
	function edit_model($id){
		if($_POST['type_name']&&$_POST['mark']){
			if(ContentType::updateType(intval($id),$_POST['type_name']
			,$_POST['member_form'],$_POST['main_form'],intval($_POST["member_publish"]))){
                return $this->_view_return(array("msg"=>"修改成功","action"=>"reload","error"=>0));
			}else
                return $this->_view_return(array("msg"=>"修改出错","error"=>1));

		}
		$html=$this->_tpl("model/input");
		$html->typev=ContentType::record(['id'=>intval($id)])->getAttributes();
	    $html->mb_forms=ContentType::formTpl("member_form");
	    $html->mn_forms=ContentType::formTpl("main_form");
	    $html->member_form_dir='TPL/'.SiteConfig::getSetting("style")."/form";
		$html->title="修改内容模型";
        $this->_view_return($html,"edit_model");
	}
	function make_block($identify){
		if(!$identify){
            return $this->_referer_to("未指写模型");
		}
		$pblock=new PageBlock();
	    $block_info=array('type'=>"query\\selectMod","update_time"=>10,"model_identify"=>$identify,'additional_args'=>'');
	    $data_block=$pblock->newBlock($block_info);
	    $html=$this->_tpl("model/block");
	    $html->edit_columns=$columns=array(
	        "id"=>array("form_input"=>"hidden"),
	        "type"=>array("form_input"=>"hidden","col_name"=>"模块类型","def_value"=>"query\\selectMod"),
	        "mark"=>array("col_name"=>"调用标记","form_input"=>"text"),
	        "remarks"=>array("col_name"=>"说明","form_input"=>"text"),
	        "update_time"=>array("col_name"=>"缓存时间","form_input"=>"diycode","def_value"=>"0","diycode"=>'<div><span class="labt">更新时间:</span><p class="inct"><input class="short_txt" id="update_time" name="update_time" value="{col_value}"/>分钟(-1:不更新,0:无缓存)</p></div>'),
	        "tpl"=>array("col_name"=>"显示模板","form_input"=>"select","value_list"=>$pblock->tplList($data_block->info('type')))
	    );
        $form_helper=new ContentForm();
	    $html->columns_descript=$form_helper->data_descript_input($columns);
	    //$html->edit_data=$block_info;
	    $html->model_info=ContentType::record(['content_mark'=>$identify])->getAttributes();
	    $html->model_form=ChannelFormRecord::findAll(['content_mark'=>$identify]);
	    $html->output();
	}
	function code_file($iden=''){
	    if($_POST['ch_iden']){
	        $iden=$_POST['ch_iden'];
        }
	    if(self::$app->holder_response){
	        return self::$app->setResponse(200,["Content-Type"=>"text/plain;",'Content-Disposition'=>'attachment; filename='.$iden.".php"],ContentType::recordCode($iden));
        }else{
            header("Content-Type:text/plain;");
            header('Content-Disposition: attachment; filename='.$iden.".php");
            echo ContentType::recordCode($iden);
            return ;
        }
    }
	//***********************内容模型end***************************************//

	/**
	 * 表单字段管理
	 */
	function form_model($iden){
		$html=$this->_tpl("model/form");
		$html->form_info=ContentType::record(['content_mark'=>$iden])->getAttributes();
		$html->form_columns=ChannelFormRecord::findAll(['content_mark'=>$iden],"orderi");
		$html->update_url=self::$app->route("model/form_update/".$iden);
		$html->model_id=$iden;
		$html->output();
	}
	function form_update($iden){
		if($_POST['ids']){
		    $batch=[];
			foreach ($_POST['ids'] as $key => $id) {
                $batch[]=[["orderi"=>intval($_POST['order_'.$id])],['id'=>$id]];
			}
            ChannelFormRecord::batchUpdate($batch);
		}
		$this->_view_return(array("msg"=>"排序已更新","action"=>"reload","error"=>0));
	}
	function form_file($iden){
		$columns=ChannelFormRecord::findAll(['content_mark'=>$iden],['orderi'=>"asc"]);
		$columns2=array();
		foreach($columns as $k=>$c){
			if(in_array($c['id'],$_POST['columns'])){
			    $col=array("col_name"=>$c['zh_name'],"form_input"=>$c['form_input']);
			    if($c['form_input']=="diycode"){
			        $col['diycode']=$c['def_value'];
			    }else if($c['form_input']=="audio"||$c['form_input']=="checkbox"||$c['form_input']=="select"||$c['form_input']=="mselect"){
                    $def_list=ContentForm::valueList($c['def_value']);
			        $col['value_list']=$def_list['value_list'];
                    $col['def_value']=$def_list['def_value'];
			    }else{
			        $col['def_value']=$c['def_value'];
			    }
				$columns2[$c['col_name']]=$col;
			}
		}
		$content_type=ContentType::record(['content_mark'=>$iden]);
		if($_POST['file_type']=='1'){
		    $file_name=$iden."_list.htm;";
            $this->_assign("list_column",$columns2);
            $this->_assign("content_type",$content_type->getAttribute("type_name"));
            $this->_assign("ctiden",$iden);
            $this->_assign("path","{APP_PATH}");
            $this->_assign("looplist","<!--{loop \$data(\$key,\$val)}-->");
            $this->_assign("loopend","<!--{/loop}-->");
            $this->_assign("static_start","<!--?\$this->block_args['defaultList']=['page_index'=>\$page,'page_size'=>10];?-->
            <!--static_start::defaultList|列表|query\Page-->");
            $this->_assign("static_end","<!--static_end::defaultList-->");
            $this->_assign("page_foot","<!--?\W3cUI::pageObjLink(\$data,\"javascript:page_goto([page]);\");?-->");
            ob_start();
            $this->_tpl("model/list_tpl")->includeTpl("");
            $contnets=ob_get_contents();
            ob_end_clean();
        }else{
            $file_name=$iden.".htm;";
            $form_helper=new ContentForm();
            $contnets='<!-- '.$iden.$content_type['type_name']."表单 -->\n";
            $contnets.=$form_helper->create_form($columns2);
        }
        if(self::$app->holder_response){
            return self::$app->setResponse(200,["Content-Type"=>"application/force-download;","Content-Disposition"=>"attachment; filename=".$file_name],$contnets);
        }else{
            header("Content-Type: application/force-download;");
            header("Content-Disposition: attachment; filename=".$file_name);
            echo $contnets;
        }
	}
	function column_add($iden){
		if(false==empty($_POST)){
			$error_ms="";
			if(empty($_POST['col_name'])){
				$error_ms="表字段不能为空";
			}else if(empty($_POST['data_type'])){
				$error_ms="数据类型不能为空";
			}else if(empty($_POST['form_input'])){
				$error_ms="表单类型不能为空";
			}
			if($error_ms==""){

				$a_cmid=ContentType::addColumn($_POST['zh_name'], $_POST['col_name'],
				$_POST['data_type'], $_POST['form_input'], $_POST['def_value'], intval($_POST['orderi']), $iden,$_POST['member_able'],$_POST['table_alter']);
				if($a_cmid>0)
				{
                    return $this->_view_return(array("msg"=>"添加成功","action"=>"reload","error"=>0));

				}else{
                    return $this->_view_return(array("msg"=>$a_cmid==-1?"字段已存在,添加失败":"添加失败","error"=>1));
				}
			}else{
                return $this->_view_return(array("msg"=>$error_ms,"error"=>1));
			}
		}
		$html=$this->_tpl("model/column");
		$html->iden=$iden;
		$type_info=ContentType::record(['content_mark'=>$iden])->getAttributes();
		$html->title=$type_info['type_name']."[".$iden."]添加字段";
		$this->_view_return($html);
	}
	function column_edit($iden,$cnid=''){
		if(false==empty($_POST)){
			$error_ms="";
			if(empty($_POST['col_name'])){
				$error_ms="表字段不能为空";
			}else if(empty($_POST['data_type'])){
				$error_ms="数据类型不能为空";
			}else if(empty($_POST['form_input'])){
				$error_ms="表单类型不能为空";
			}
			if($error_ms==""){
				if(ContentType::alterColumn($_POST['zh_name'], $_POST['old_column'], $_POST['col_name'],
				$_POST['data_type'], $_POST['form_input'], $_POST['def_value'], intval($_POST['orderi']), $iden,$_POST['member_able'],$_POST['table_alter']))
				{
                    return $this->_view_return(array("msg"=>"修改成功","action"=>"reload","error"=>0));

				}else{
				 	$this->_view_return(array("msg"=>"修改失败","error"=>1));
				}
			}else{
			    $this->_view_return(array("msg"=>$error_ms,"error"=>1));
			}
			return ;
		}
		$html=$this->_tpl("model/column");
		$html->iden=$iden;
		$html->column_value=ChannelFormRecord::record(['id'=>$cnid])->getAttributes();
        $type_info=ContentType::record(['content_mark'=>$iden])->getAttributes();
		$html->title=$type_info['type_name']."[".$iden."]编辑字段";
        $this->_view_return($html);
	}
	function column_del(){
	    foreach($_POST['columns'] as $id){
	        if(!ChannelFormRecord::deleteAll(["id"=>intval($id)])){
	            $this->_view_return(array("msg"=>"删除出错！","error"=>1));
	            return ;
	        }
	    }
		$this->_view_return(array("msg"=>"删除成功，该操作不会drop对应字段。","action"=>"reload","error"=>0));
		
	}

}
