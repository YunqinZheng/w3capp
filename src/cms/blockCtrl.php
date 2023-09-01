<?php
namespace cms\controller;
use w3capp\W3cApp;
use w3capp\helper\ContentForm;
use w3capp\helper\Str;
use cms\model\BlockData;
use cms\model\BlockExp;
use cms\model\PageFrame;
use cms\model\PageLayer;
use cms\model\Theme;
use common\model\BlockExtendRecord;
use common\model\BlockRecord;
use common\model\PageBlockRecord;
use common\model\PageLayoutRecord;
use cms\model\PageBlock;

class blockCtrl extends mainCtrl{
    protected $prototype_block;
    protected $data_view_block;
    protected static $page_block;
    protected static function _pb(){
        if(!self::$page_block){
            self::$page_block=new PageBlock();
        }
        return self::$page_block;
    }
	function index($page=0){
	    $html=$this->_tpl("block/index");
	    $html->block_config=self::_pb()->getInis(false);
	    $block_record=BlockRecord::adaptTo(['or'=>['hidden'=>0,'init_hash'=>'static']])
        ->limit(25,intval($page));
		$html->data=$block_record->selectAll(true);
		$html->output();
	}

	//清缓存
	function refetch($cache){
	    W3cApp::template()->clearFile($cache);
		//@unlink(W3CA_PATH."data/cache/template/".$cache);
		echo "ok";
	}
	/**
	 * 更改界面模块的位置
	 */
	function transposition(){
	    $from=PageFrame::record(['theme_id'=>$_POST['theme_id'],"frame_id"=>$_POST['from_area']]);
	    if($_POST['from_area']==$_POST['to_area']){
            $rs=$from->addBlock($_POST['mark'],$_POST['to_lay_id'],intval($_POST['to_cell']),$_POST['before']);
        }else{
            $from->removeBlock($_POST['mark']);
            $to=PageFrame::record(['theme_id'=>$_POST['theme_id'],"frame_id"=>$_POST['to_area']]);
            $rs=$to->addBlock($_POST['mark'],$_POST['to_lay_id'],intval($_POST['to_cell']),$_POST['before']);
        }
		if($rs){
			if($_POST['file_var']){
                W3cApp::template()->clearFile($_POST['file_var']);
			}
			return $this->_json_return(0,"");
		}else{
            return $this->_json_return(1,"transposition error");
		}
	}
	public function select(){
	    $mks=explode(",",$_POST['marks']);
	    return $this->_json_return("","",BlockRecord::arrayData(["mark"=>$mks]));
    }
    public function copy(){
	    if(empty($_POST['new_mark'])){
            $block_id=intval($_POST['block_id']);
            if($block_id){
                $cpBr=BlockRecord::record(['id'=>$block_id]);
                if(empty($cpBr)){
                    return $this->_view_return(array("error"=>1,"msg"=>$block_id." id 不存在","action"=>""));
                }
            }else{
                $cpBr=BlockRecord::record(['mark'=>$_POST['cp_mark']]);
                if(empty($cpBr)){
                    return $this->_view_return(array("error"=>1,"msg"=>$block_id." id 不存在","action"=>""));
                }
            }
            $this->_assign("block",$cpBr->getAttributes());
            $html=$this->_tpl("block/copy");
            $html->title="复制模块-".$cpBr->remarks;
            $html->return_action="copyView";
            $this->_view_return($html);
        }else{
            $newBr=BlockRecord::record(["mark"=>$_POST['new_mark']],true);
            if($newBr->primary()){
                return $this->_view_return(array("error"=>1,"msg"=>$_POST['new_mark']." 已存在","action"=>""));
            }
            $cpBr=new BlockRecord(['id'=>$_POST['block_id']]);
            $cpBrAttr=$cpBr->getAttributes();
            unset($cpBrAttr['mark']);
            unset($cpBrAttr['id']);
            $cpBrAttr['remarks']=$_POST['new_remarks'];
            $newBr->setAttributes($cpBrAttr);
            if($newBr->save()){
                if($newBr->type=="html\\HTML"){
                    $newBr->submitCache($cpBr->info("content"));
                }
                $cp_ex=BlockExtendRecord::record(["block_id"=>$cpBr->primary()]);
                if(false==empty($_POST['area_id'])){
                    $cp_ex->areas=$_POST['area_id'];
                    $frame=PageFrame::record(['frame_id'=>$_POST['area_id'],"theme_id"=>$_POST['theme_id']]);
                    $frame->addBlock($newBr->mark,$_POST['lay_id'],empty($_POST['lay_id'])?-1:intval($_POST['cell']),$_POST['before']);
                }
                if($cp_ex){
                    $bex=new BlockExtendRecord($cp_ex);
                    $bex->block_id=$newBr->primary();
                    if(empty($_POST['file_var'])==false){
                        $bex->files=$_POST['file_var'];
                    }
                    $bex->save();
                }
                if(empty($_POST['file_var'])==false){
                    W3cApp::template()->clearFile($_POST['file_var']);
                    return $this->_view_return(array("error"=>"0",
                        "msg"=>"复制成功","param"=>$newBr->getAttributes(),
                        "action"=>"copyFinish",
                    ));
                }
                return $this->_view_return(array("error"=>"0",
                    "msg"=>"复制成功",
                    "action"=>"reload",
                ));
            }else{
                return $this->_view_return(array("error"=>"1",
                    "msg"=>"save error",
                ));
            }
        }
    }
    public function cell_size(){
        $lay=PageLayer::record(['id'=>$_POST['lay_id']]);
        if(!$lay){
            return $this->_json_return(1,"layout not found!");
        }
        $cell=intval($_POST['cell']);
        $lay->cellSize($cell,$_POST['width'],$_POST['height']);
        if($lay->save()){
            $theme=new Theme(['id'=>$_POST['theme_id']]);
            $file_css="data/theme/".$theme->install_dir.'/theme.css';
            $frm=new PageFrame(['id'=>$lay->page_frame]);
            $frm->cellCssMix("fzy".$lay['id'],$lay->getCss());
            $frm->saveCss("",$file_css);
            $theme->refreshFileVar();
            return $this->_json_return(0,"");
        }else{
            return $this->_json_return(1,"cell save size error!");
        }
    }
	public function export()
	{

		if(empty($_POST['file_var'])){
            if(empty($_POST['id_list'])){
                return $this->_referer_to("未选择要导出的模块");
            }
            $exp=new BlockExp(self::_pb());
            $exp->exportIds($_POST['id_list']);
        }else{
            if(empty($_POST['mark_list'])){
                return $this->_referer_to("未选择要导出的模块");
            }
		    $exp=new BlockExp(self::_pb());
            $exp->setTplExpDir(W3cApp::template()->getExportDir());
            $export_error=$exp->exportFile($_POST['file_var'],$_POST['area'],$_POST['mark_list']);
            if($export_error){
                return $this->_referer_to($export_error);
            }
        }

	}
	public function import(){
	    if(empty($_FILES['file'])){
            return $this->_view_return(array("error"=>1,"msg"=>"upload file error","action"=>""));
        }
		if($_FILES['file']['error']==UPLOAD_ERR_OK){

            $pageblock=self::_pb();
			$upload_txt=file_get_contents($_FILES['file']['tmp_name']);
			$blocks=\w3c\helper\Str::arrayParse($upload_txt);
			if(empty($blocks)){
                return $this->_view_return(array("error"=>1,"msg"=>"发生错误,无法识别导入的内容","action"=>""));
			}
			if(empty($blocks['blocks'])){
                return $this->_view_return(array("error"=>1,"msg"=>"导入的模块为空","action"=>""));
			}
            $exp=new BlockExp($pageblock);
			if($exp->import($blocks,empty($_POST['page_file_var'])?'':$_POST['page_file_var'],empty($_POST['ignore_exist'])?false:true)){
                return $this->_view_return(array("error"=>0,"msg"=>"导入成功","action"=>"reload"));
            }else{
                return $this->_view_return(array("error"=>1,"msg"=>"导入错误","action"=>""));
            }

		}else{
            return $this->_view_return(array("error"=>1,"msg"=>"发生错误：upload error:".$_FILES['file']['error'],"action"=>""));
		}
	}

    /**
     * 把模块从区域移除
     * @param $area
     * @param $mark
     */
	function remove_from(){
        $area=$_POST["area_id"];
        $mark=$_POST["mark"];
        $frame=PageFrame::record(["theme_id"=>$_POST['theme_id'],"frame_id"=>$area]);
        if(!$frame){
            return $this->_json_return(1,"移除模块发生错误");
        }else{
            $frame->removeBlock($mark);
            if($_POST['page_file_var']){
                W3cApp::template()->clearFile($_POST['page_file_var']);
            }
            return $this->_json_return(0,"模块已从区域移除",["areaid"=>$area,"mark"=>$mark]);
        }
	}
	function add_block_to(){
		$block_info=array();
		if($_POST['areaid']){
			if($_POST['marks']){
                $block_info=BlockRecord::findAllData(['mark'=>explode(",",$_POST['marks'])]);
                $marks=array();
                $frame=PageFrame::record(["frame_id"=>$_POST['areaid'],"theme_id"=>$_POST['theme_id']]);
                foreach ($block_info as $key => $value) {
                    $marks[]=$value['mark'];
                    $frame->addBlock($value['mark'],empty($_POST['lay_id'])?0:$_POST['lay_id'],empty($_POST['lay_id'])?-1:intval($_POST['cell']),"");
                }
				
			}else{
                return $this->_json_return(1,"模块未指写");
			}
			
		}
		if(false==empty($_POST['page_file_var'])){
            W3cApp::template()->clearFile($_POST['page_file_var']);
		}
		if($block_info){
            return $this->_json_return(0,"模块已添加",$block_info);
		}else{
            return $this->_json_return(1,$_POST['mark']."模块未找到");
		}
	}

    /**
     * 保存属性
     */
    function save_prototype(){
        $pageblock=self::_pb();
        $info=$pageblock->formData($_POST['id'],$_POST);
        if(empty($info)){
            return $this->_view_return(array("error"=>1,"msg"=>"表单超时，请刷新。","action"=>"reload"));
        }
        if(empty($info['type'])){
            return $this->_view_return(array("error"=>1,"msg"=>"类型不能为空","action"=>""));
        }
        if(empty($info['mark'])){
            return $this->_view_return(array("error"=>1,"msg"=>"调用标记不能为空","action"=>""));
        }
        if(false==preg_match('/^[\d\w]*$/',$_POST['mark'])){
            return $this->_view_return(array("error"=>1,"msg"=>"调用标记只能用英文字母或数字","action"=>""));
        }
        if($info['hidden']==0&&preg_match("/[\\W]/", $info['mark'])){
            return $this->_view_return(array("error"=>1,"msg"=>"调用标记不能使用字母和数字以外的字符","action"=>""));
        }
        $block_=$pageblock->newBlock($info);
        if($block_==null){
            return $this->_view_return(array("error"=>1,"msg"=>"未找到该模块类型","action"=>""));
        }
        $info['data_size']=intval($info['data_size']);
        $block_->onCheckPrototype($info);
        if($info['error']){
            return $this->_view_return(array("error"=>1,"msg"=>$info['msg'],"action"=>""));
        }

        if($info['id']&&$pageblock->saveBlock($info,$info['id'])){
            $pageblock->clearBlockCache(intval($info['id']));
            $block_->onSaved($info['id']);
            if($info['tpl']!=$_POST['old_tpl']){//非自定义模板
                if($info['tpl']==-1){
                    $tplm=$pageblock->getTplFile($info['type'],$info['old_tpl']);
                    if(file_exists($tplm)){
                        $pageblock->saveTemplate($info['id'],strtr(file_get_contents($tplm),parent::_tpl_const()));
                    }else{
                        $pageblock->saveTemplate($info['id'],"w3capp--空白自定义模板");
                    }
                }
            }
            if(false==empty($_POST['zyqareaid'])){
                $page_frame=PageFrame::record(["frame_id"=>$_POST['zyqareaid'],"theme_id"=>$_POST['zyqtheme_id']]);
                if(empty($page_frame)){
                    return $this->_view_return(array("error"=>1,"msg"=>"page frame error","action"=>""));
                }
                $b2area=$page_frame->addBlock($info['mark'],$_POST['zyqlay_id'],intval($_POST['zyqcell']),empty($_POST['zyqbef'])?'':$_POST['zyqbef']);
                if($b2area==false){
                    return $this->_view_return(["error"=>1,"msg"=>"save to frame error"]);
                }
                $b_ex=BlockExtendRecord::record(['block_id'=>$info['id']],true);
                if($b_ex->areas){
                    if(strpos(",".$b_ex->areas.",",",".$_POST['zyqareaid'].",")===false){
                        $b_ex->areas.=",".$_POST['zyqareaid'];
                        $b_ex->save();
                    }

                }else{
                    $b_ex->areas=$_POST['zyqareaid'];
                    $b_ex->save();
                }
            }
            $data=["mark"=>$info['mark'],'type'=>$info['type'],"remarks"=>$info['remarks'],"id"=>$info['id'],"areaid"=>$_POST['zyqareaid']];
            if(empty($_POST['page_file_var'])){
                return $this->_view_return(array("error"=>"0",
                    "msg"=>$info['id']?"修改成功":"添加成功",
                    "action"=>"close",
                    "blockid"=>$info['id'],
                    "mark"=>$info['mark']
                ));
                //清除调用缓存
                $page_b=PageBlockRecord::findAllData(["block_mark"=>$info['mark']]);
                foreach ($page_b  as $pg){
                    $f=W3CA_PATH."data/cache/template/".$pg['file_id'].".php";
                    if(file_exists($f)){
                        @unlink($f);
                    }
                }
            }else {
                W3cApp::template()->setPageBlockManager($pageblock);
                $data= array_merge($data,W3cApp::template()->clearFile($_POST['page_file_var'],$_POST['zyqtpl']));
                return $this->_view_return(array("error"=>"0",
                    "msg"=>$info['id']?"修改成功":"添加成功","param"=>$data,
                    "action"=>"prototype",
                    "blockid"=>$info['id'],
                    "mark"=>$info['mark']
                ));
            }

        }else {
            return $this->_view_return(array("error"=>1,"msg"=>"模块设置出错","action"=>""));
        }
    }
	/**
	 * 模块属性
	 */
	function prototype($bid=0,$appoint=""){
		$block_t=null;
		$bid=intval($bid);
		$html=$this->_tpl("block/prototype");
		$html->url=W3cApp::route("block/save_prototype");
		if($bid){//修改

            $pageblock=self::_pb();
			$info=$pageblock->decodeAttr(BlockRecord::firstAttr(['id'=>$bid]));
			if($appoint){
                $info['type']=$appoint;
			}
			if(!$info['type']){
			    return $this->_view_return(array("error"=>1,"msg"=>"类型不正确".$info['type'],"action"=>""));
			}
            $html->old_tpl=$info['tpl'];
            $info=$pageblock->newBlock($info)->getPrototype();
            $html->block_info=$info;
            $html->tplist=json_encode($pageblock->tplList($info['type']));
			$html->block_type=json_encode($pageblock->getInis(false));//value_list
			$html->init_config=$info['init_config'];//array_merge(,$block_obj->get_pro_val());
			$html->title=$info['remarks'].'-属性';
            return $this->_view_return($html);
		}else{//添加
            return $this->_view_return(array("error"=>1,"msg"=>"模块没有初始化","action"=>""));
		}
	}
	function _tpl_const(){
	    $const=parent::_tpl_const();
	    $form_helper=new ContentForm();
	    if($this->prototype_block){
	        $columns=$this->prototype_block->getPrototypeForm();
			if($this->prototype_block->info('hidden')){
				$columns['type']['form_input']='hidden';
				$columns['mark']['form_input']='hidden';
				$columns["hidden"]=array("form_input"=>"hidden","def_value"=>1);
			}
	        $const['{PROTOTYPE_FORM}']=strtr($form_helper->create_form($columns),$const);
	    }
	    if($this->data_view_block){
	        $columns=$this->data_view_block->get_data_table();
	        $const['{DATA_TABEL_FORM}']=strtr($form_helper->create_form($columns),$const);
	    }
	    return $const;
	}
	function preview($bid,$args=null){
        $info=BlockRecord::firstAttr(['id'=>$bid]);
        $block=self::_pb()->newBlock($info);
        $block->display($args);
    }
    function frame_view(){
	    $fm=PageFrame::record(['frame_id'=>$_POST['area_id'],'theme_id'=>$_POST['theme_id']]);
        $fm->display();
    }
    function frame_layout(){
        $fm=PageFrame::record(['frame_id'=>$_POST['area_id'],'theme_id'=>$_POST['theme_id']]);

        $attr=$fm->getAttributes();
        $attr['lay_list']=PageLayer::allLayout($fm->id);
        return $this->_json_return(0,"",$attr);
    }
    function info($bid){
        $info=BlockRecord::firstAttr(['id'=>$bid]);
        return $this->_json_return(0,"",$info);
    }

    /**
     * 保存布局
     */
    function store_layout(){

        $pf=PageFrame::record(['frame_id'=>$_POST['frame_id'],'theme_id'=>$_POST['theme_id']],true);
        $css_file="";
        $er=$pf->editLayout($_POST)?0:1;
        if($_POST['css_file']){
            $the=new Theme(['id'=>$_POST['theme_id']]);
            if($the->refreshFileVar()&&$the->save()){
                $css_file=$the->install_dir."/".$the->file_var."theme.css";
            }else{
                return $this->_json_return(1, 'css copy error');;
            }

        }

        return $this->_json_return($er,"",['lay_id'=>$pf->layIds(),'css_file'=>$css_file]);
    }
	/**
	 * 修改模块缓存
	 */
	function view_cache($bid,$args){
		$bid=intval($bid);
		$blocki=BlockRecord::firstAttr(['id'=>$bid]);
		if($blocki['id']){
			$block_t=self::_pb()->newBlock($blocki);
			$cache_file=$block_t->getCache();
			if(!$cache_file||$blocki['update_time']==0){
                return $this->_view_return(array("error"=>1,"msg"=>"该模块不可编辑缓存！","action"=>"close"));
			}else{
			    $html=$this->_tpl("web/block_view_cache");
			    $html->cache_content=$block_t->content($args);
			    $html->title=$blocki['remarks']."-模块缓存";
			    $html->block_id=$blocki['id'];
			    $html->block_mark=$blocki['mark'];
			    $html->args=htmlspecialchars($args);
                return $this->_view_return($html);
			}
			
		}else{
            return $this->_view_return(array("error"=>1,"msg"=>"缓存不存在！","action"=>"close"));
		}
	}
	
	function save_html(){
	    $bid=intval($_POST['id']);
        $args=$_POST['args'];
		if(!$bid||empty($_POST['cache_content'])){
            return $this->_json_return(1,"错误无法修改");
		}else if(stripos($_POST['cache_content'],"<script")===false&&stripos($_POST['cache_content'],"<iframe")===false){
			$b_info=BlockRecord::firstAttr(["id"=>$bid]);
			$block_=self::_pb()->newBlock($b_info);
			$block_->submitCache(Str::xss_filter($_POST['cache_content']),$args);
            if($_POST['page_file_var']){
                W3cApp::template()->clearFile($_POST['page_file_var']);
            }
            return $this->_json_return(0,"缓存已保存",array(
			"blockid"=>$bid,
			"mark"=>$b_info['mark']
			));
		}else{
            return $this->_json_return(1,"内容包含非法字符");
        }
	}
	/**
	 * 修改模块模板
	 */
	function view_tpl($bid,$get_content=0){
		$bid=intval($bid);
        $pageblock=self::_pb();
        $info=BlockRecord::firstAttr(['id'=>$bid]);
		if($info['id']){//修改
            $block_config=self::_pb()->getInis(false);
			if($info['hidden']==1&&!$block_config[$info['type']]['has_tpl']){
                return $this->_view_return(array("error"=>1,"msg"=>"该模块不可编辑模板","action"=>"close"));
			}else{
                if($get_content){
                    echo file_get_contents((!$_POST['tpl']||$_POST['tpl']==-1)?$pageblock->tplCacheFile($info['id']):$pageblock->getTplFile($info['type'],$_POST['tpl']));
                    exit;
                }
                $tpl_file=(!$info['tpl']||$info['tpl']==-1)?$pageblock->tplCacheFile($info['id']):$pageblock->getTplFile($info['type'],$info['tpl']);
			    $html=$this->_tpl("block/tpl");
                $html->tplist=$pageblock->tplList($info['type']);
				$html->block_info=$info;
				$html->block_tpl_content=file_get_contents($tpl_file);
				$html->title=$info['remarks']."_模块模板编辑";
                return $this->_view_return($html);
			}
		}else{
            return $this->_view_return(array("error"=>1,"msg"=>"模块不存在","action"=>"close"));
		}
	}
	function save_tpl(){
	    if(empty($_POST['bid'])){
            return $this->_view_return(array("error"=>2,"msg"=>"模块错误","action"=>""));
        }
        $pageblock=self::_pb();
        if(empty($_POST['tpl_type'])){
            if(empty($_POST['tpl_val'])){
                return $this->_view_return(array("error"=>2,"msg"=>"请选择模板","action"=>""));
            }else{
                $block=new BlockRecord(['id'=>$_POST['bid']]);
                $block->tpl=$_POST['tpl_val'];
                if($block->save()){
                    if($_POST['page_file_var']){
                        W3cApp::template()->setPageBlockManager($pageblock);
                        $data= W3cApp::template()->clearFile($_POST['page_file_var'],$_POST['zyqtpl']);
                        $data["mark"]=$block['mark'];
                        $data["remarks"]=$block['remarks'];
                        $data["id"]=$block['id'];
                        $data["areaid"]=$_POST['areaid'];
                        return $this->_view_return(array("error"=>"0",
                            "msg"=>"模板已保存！",
                            "action"=>"4prototype\t ".json_encode($data),
                            "blockid"=>$block->id,
                            "mark"=>$block->mark
                        ));
                    }
                    return $this->_view_return(array("error"=>"0",
                        "msg"=>"模板已保存！",
                        "action"=>"close"
                    ));
                }else{
                    return $this->_view_return(array("error"=>1,"msg"=>"保存错误","action"=>"close"));
                }
            }
        }
		if(empty($_POST['tpl_content'])){
            return $this->_view_return(array("error"=>2,"msg"=>"模板内容不能为空","action"=>""));
		}else{
            $block=new BlockRecord(['id'=>$_POST['bid']]);
            $block->tpl="-1";
			if($pageblock->saveTemplate($_POST['bid'],$_POST['tpl_content'])&&$block->save()){
                if($_POST['page_file_var']){
                    W3cApp::template()->setPageBlockManager($pageblock);
                    $data= W3cApp::template()->clearFile($_POST['page_file_var'],$_POST['zyqtpl']);
                    $data["mark"]=$block['mark'];
                    $data["remarks"]=$block['remarks'];
                    $data["id"]=$block['id'];
                    $data["areaid"]=$_POST['areaid'];
                    return $this->_view_return(array("error"=>"0",
                        "msg"=>"模板已保存！",
                        "action"=>"4prototype\t ".json_encode($data),
                        "blockid"=>$block->id,
                        "mark"=>$block->mark
                    ));
                }
                return $this->_view_return(array("error"=>"0",
				"msg"=>"模板已保存！",
				"action"=>"close"
				));
			}else{
                return $this->_view_return(array("error"=>1,"msg"=>"保存错误","action"=>"close"));
			}
		}
	}
	/**
	 * 数据列表
	 */
	function view_data_list($bid){
		$bid=intval($bid);
		if($bid){
			$blocki=BlockRecord::firstAttr(['id'=>$bid]);
			$obj_block=self::_pb()->newBlock($blocki);
			$html=$this->_tpl("web/block_data_list");
			$html->title=$blocki["remarks"]."-数据列表";
			$html->block_info=$obj_block->getPrototype();
			$edit_desc=$obj_block->info('context_edit');
			$html->edit_desc=$edit_desc&&$edit_desc!='[]'?$edit_desc:"''";
            $obj_block->setEditDisplay(true);
			$html->assign("block_display",$obj_block->content($_POST));
			$html->output();
			return;
		}
	}
	function clear_cache($bid){
        self::_pb()->clearBlockCache(intval($bid));
        return $this->_view_return(array("error"=>1,"msg"=>"模块缓存已清除","action"=>"reload"));
	}
	function reset(){
        $bid=intval($_POST['id']);
        $br=BlockRecord::record(['id'=>$bid]);
        if(empty($br)){
            return $this->_json_return(1,"模块不存在!");
        }else{
            $br->init_hash=uniqid();
            $br->save();
            self::_pb()->clearBlockCache($bid);
            return $this->_json_return(0,"模块已初始化!");
        }
    }

    /**
     * 处理初始化
     */
    function init_action(){
        $pageblock=self::_pb();
        if(empty($_POST['mark'])){
            return $this->_view_return(array("error"=>1,"msg"=>"调用标记,值不能为空","action"=>""));
        }
        if(false==preg_match('/^[\d\w]*$/',$_POST['mark'])){
            return $this->_view_return(array("error"=>1,"msg"=>"调用标记只能用英文字母或数字","action"=>""));
        }
        $info_def=$pageblock->initPrototype($_POST);
        $info_def['type']=$_POST['blocktype'];
        if(empty($info_def['type'])){
            return $this->_view_return(array("error"=>1,"msg"=>"类型不正确".$info_def['type'],"action"=>""));
        }
        if(!empty($info_def['error'])){
            return $this->_view_return(array("error"=>1,"msg"=>$info_def['msg'],"action"=>""));
        }
        $pageblock->newBlock($info_def);

        $bid=$pageblock->saveBlock($info_def,intval($_POST['id']));
        if($bid===false){
            $this->_view_return(array("error"=>1,"msg"=>"模块设置出错","action"=>""));
        }else{
            $this->_view_return(array("error"=>"0","reload"=>1,
                "msg"=>$_POST['id']?"修改成功":"添加成功","param"=>["id"=>($_POST['id']?$_POST['id']:$bid),"page_file_var"=>$_POST['page_file_var']],
                "action"=>"inited"
            ));
        }
    }

    /**
     * 初始化界面
     * @param int $bid
     */
    function init($bid=0){
        $html=$this->_tpl("block/init");

        $pageblock=self::_pb();
        $this->_assign("init_action",W3cApp::route("block/init_action"));
        $this->_assign("block_id",$bid);
        $types=$pageblock->blockTypes(false);
        if($bid>0){
            $blocki=BlockRecord::firstAttr(['id'=>$bid]);
            if(empty($blocki['type'])){
                $blocki['type']="html\\HTML";
            }
            $html->title=$types[$blocki['type']]."_模块字段设置";
            $block_obj=$pageblock->newBlock($blocki);
            $prototype_form=$block_obj->getPrototypeForm();
            $block_inis=$pageblock->getInis(false);
            if(empty($block_inis['has_tpl'])){
            }else{
                $prototype_form["tpl"]=array("col_name"=>"显示模板","form_input"=>"select","value_list"=>$pageblock->tplList($block_obj->info('type')));
            }
            $block_info=$block_obj->getPrototype();
            foreach ($block_info as $key=>$value){
                if(array_key_exists($key,$prototype_form)){
                    $prototype_form[$key]['def_value']=htmlspecialchars($value,ENT_QUOTES);
                }
            }
            $this->_assign("prototype_form",$prototype_form);
            $this->_assign("block_type",$block_info['type']);
            $this->_assign("init_config",$block_info['init_config']);
            $html->title=$types[$block_info['type']].'-'.$block_info['remarks'];
        }else {
            if(empty($_POST['blocktype'])){
                $_POST['blocktype']="html\\HTML";
            }
            $block_obj=$pageblock->newBlock(array('type'=>$_POST['blocktype']));
            $html->title=$types[$_POST['blocktype']]."_模块字段设置";
            $prototype_form=$block_obj->getPrototypeForm();
            $block_inis=$pageblock->getInis(false);
            if($block_inis['has_tpl']){
                $prototype_form["tpl"]=array("col_name"=>"显示模板","form_input"=>"select","value_list"=>$pageblock->tplList($block_obj->info('type')));
            }

            unset($prototype_form['mark']);
            unset($prototype_form['remarks']);
            $this->_assign("prototype_form",$prototype_form);
            $this->_assign("block_type",$_POST['blocktype']);
            //$blocki=array('type'=>$_POST['blocktype'],"update_time"=>10,"areaid"=>$_POST['areaid']);
        }
        $this->_assign("areaid",empty($_POST['areaid'])?'':$_POST['areaid']);
        $this->_assign("page_file_var",empty($_POST['page_file_var'])?'':$_POST['page_file_var']);
        $this->_view_return($html);
    }
    function data_item($bid,$primary_val){

        $pageblock=self::_pb();
        $info=BlockRecord::firstAttr(['id'=>$bid]);
        $block=$pageblock->newBlock($info);
        $primary_key=$block->info("primary_key");
        if(empty($primary_key)){
            $this->_view_return(["error"=>1,"msg"=>"没设置主键数据无法编辑"]);
        }

        $item=BlockData::itemJson($bid,$primary_val);
        if(empty($item)){
            $data=$block->loadData($_POST);
            if(empty($data)){
                $this->_view_return(["error"=>1,"msg"=>"找不到相关数据","action"=>"close"]);
            }
            foreach ($data as $val){
                if($val[$primary_key]==$primary_val){
                    $item=\w3c\helper\Str::toJson($val);
                    break;
                }
            }
        }
        $view=$this->_tpl("web/block_data_item");
        $view->assign("item",$item);
        $view->assign("block_id",$bid);
        $view->assign("replace_id",$primary_val);
        $view->title="编辑数据";
        $this->_view_return($view);
    }
    function reset_data(){
        $bid=intval($_POST['block_id']);
        if(false===BlockData::resetData($_POST['replace_id'],$bid)){
            return $this->_view_return(array("error"=>"1",
                "msg"=>"数据还原错误",
                "action"=>""
            ));
        }else{
            return $this->_view_return(array("error"=>"0",
                "msg"=>"数据已还原",
                "action"=>"reload"
            ));
        }
    }
	function alter_data(){
		$bid=intval($_POST['block_id']);
		if(!$bid){
		    return $this->_view_return(array("error"=>1,"msg"=>"未指定模块"));
		}

		if(empty($_POST['column_names'])){
            return $this->_view_return(array("error"=>"1",
                "msg"=>"关联数据错误！",
                "action"=>"close"
            ));
        }
		$columns=explode(",",$_POST['column_names']);
		$values=[];
		unset($_POST['column_names']);
		foreach($_POST as $k=>$v){
		    if(in_array($k,$columns)){
                $values[$k]=$v;
            }
        }
		if(BlockData::saveData($values,$_POST['replace_id'],$bid)){
		    $this->_view_return(array("error"=>"0",
			"msg"=>"数据已保存",
			"action"=>"reload"
			));
		}else{
			$this->_view_return(array("error"=>1,"msg"=>"数据保存出错！"));
		}
	}
	


	function del(){
	    if(empty($_POST['rid'])){
	        $this->_view_return(array("error"=>1,"msg"=>"没有选择要删除的数据","action"=>""));
	        return;
	    }
		$ids=is_array($_POST['rid'])?$_POST['rid']:implode(",", $_POST['rid']);
		if($ids && self::_pb()->deleteData($ids)){
		    $this->_view_return(array("error"=>0,"msg"=>"模块已删除！","action"=>"reload","bid"=>$ids));
		}else{
		    $this->_view_return(array("error"=>1,"msg"=>"出错","action"=>""));
		}
	}
	function pageinfo(){
		$page=new \W3cUI();
		$page->outInfo($_POST['page_file_var']);
	}
	function refresh($c){
		if($c=='all'){
		    $all_blocks=self::_pb()->getInis(false);
            return $this->_json_return(0,"",$all_blocks);
        }
        $all_blocks=self::_pb()->getInis(true);
		foreach ($all_blocks as $k=>$type)
        {
            W3cApp::template()->clearTplCache("web/block_prototype", "B-".str_replace("\\","-",$k));
        }
        return $this->_json_return(0,"",$all_blocks);
	}
}
