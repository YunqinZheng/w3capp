<?php
namespace w3capp;
use w3capp\helper\Cache;
class Template extends Core{
    //模版完整路径
	protected $tpl_dirs;
	//编译常量
    protected $tpl_const=array();
	protected $edit_export;
	protected $tpl_list;
	protected $load_blocks;
	protected $static_block=array();
	protected $file_var;
	protected $export_dir;
    protected $block_manager;
    protected $div_ini_id;
    protected $view_ids=array();
    protected $css_list=array();
    protected $attach_blocks=array();
    //protected $var_layout=array();
    public function __construct()
    {

        $this->tpl_dirs=[];
        $this->getTplConst('<!--{/loop}-->','<?php }?>');
        $this->getTplConst('<!--{/if}-->','<?php }?>');
        $this->getTplConst('<!--{else}-->','<?php }else{ ?>');
        $this->export_dir='data/cache/template/';
        if(!is_dir(W3CA_MASTER_PATH.$this->export_dir)){
            if(@mkdir(W3CA_MASTER_PATH.$this->export_dir,0777,true)==false){
                throw new Exception('mkdir error: '.$this->export_dir);
            }
        }
    }

    /**
	 * 设置模板目录
	 * @param $path 一般为绝对路径，右边要"/"
	 */
	function setTplDir($path){
		array_unshift($this->tpl_dirs,$path);
	}

    /**
     * @param $dir 完整目录
     * 追加目录
     */
	function includeDir($dir){
        $this->tpl_dirs[]=$dir;
    }
    /**
     * 设置编译输出目录
     * @param $d 为相对目录，右边要"/"
     */
	function setExportDir($d){
        $this->export_dir=$d;
	    if(file_exists(W3CA_MASTER_PATH.$d)==false){
	        return mkdir(W3CA_MASTER_PATH.$d);
        }
        return true;
    }
    function getExportDir(){
	    return $this->export_dir;
    }
    function setPageBlockManager($manager){
        $this->block_manager=$manager;
    }
	/**
	 * 设置模版编译常量，生成模版才有用
	 */
	function getTplConst($a,$v){
		$this->tpl_const[$a]=$v;
	}
	function unsetTplConst($a){
	    unset($this->tpl_const[$a]);
    }

    /**
     * @param $language_file
     * 设置语言常量
     */
	function setLanguageFile($language_file){
	    $lang=\w3c\helper\Str::arrayParse(file_get_contents($language_file));
	    foreach ($lang as $key=>$val){
	        $this->getTplConst('{L:'.$key.'}',$val);
        }
    }
	function tplInclude($matche){
        return $this->template($this->tpl($matche[1]));
	}
	function tplEcho($matche){
	    if($matche[0]{0}=="\\"){
	        return ltrim($matche[0],"\\");
        }
	    if(strpos($matche[1],'block::')===0){
	        return $this->tplBlock([$matche[1],str_replace('block::','',$matche[1])]);
        }else if(strpos($matche[1],'html$')===0){
			return '<?php echo '.str_replace('html$','$',$matche[1])."; ?>";
		}else
	    return '<?php echo htmlspecialchars('.$matche[1]."); ?>";
    }
    function tplBlockHold(){

    }
	function tplEditarea($matche){
	    if($this->block_manager==null)return '';
	    $arg=explode(",", $matche[1]);
	    if(count($arg)<2)return;
        $areaid=array_shift($arg);
        if(array_key_exists($areaid,$this->edit_export)){
            return '区域重复';
        }
        $hint=array_shift($arg);
        $pfr=$this->block_manager->frame($areaid);

	    if($pfr->id){
            //区域已存在
            if($pfr->block_marks){
                $blocks=explode(",",trim($pfr->block_marks));
                $this->view_ids=array_merge($this->view_ids,$pfr->blocksDivIds());
                $this->load_blocks=array_merge($this->load_blocks,$blocks);
                if(empty($arg)==false){
                    $this->attach_blocks=array_merge($this->attach_blocks,$arg);
                }
            }else{
                $blocks=[];
            }

        }else{
            $pfr->frame_name=$hint;
            if(empty($arg)){
                $blocks=[];
            }else{
                $arg_marks=array_unique($arg);
                $pfr->block_marks=implode(",", $arg_marks);
                $mb=[];
                foreach ($arg_marks as $mark){
                    $this->appendBlock($mark);
                }
                $blocks=$arg;
            }
            $pfr->save();
        }

        $id='ar_'.dechex($this->div_ini_id++);
	    $this->edit_export[$areaid]=['hint'=>$hint,"frame_id"=>$pfr->id,"view_id"=>$id,'blocks'=>$blocks];
        $this->view_ids['fr'.$areaid]=$id;
	    return '<div id="'.$id.'" class="area '.$pfr->cssName().'">'.$pfr->innerTPL().'</div>';
	}
	function appendBlock($b){
		if(empty($this->load_blocks)){
			$this->load_blocks=[$b];
		}else{
			if(false==in_array($b,$this->load_blocks)){
				$this->load_blocks[]=$b;
			}
		}
	}
	function tplBlock($matche){
	    
	    $block_mark=$matche[1];
		$this->appendBlock($block_mark);
        //$id='b_'.dechex($this->div_ini_id++);
        //$this->view_ids['b'.$matche[1]]=$id;
	    //return '<div id="'.$id.'" class="block"><'.'?php $this->loadBlock("'.$matche[1].'");?'.'></div>';
        return '<?php $this->loadBlock("'.$block_mark.'");?>';
	}
	function tplStaticBegin($matche){
		$block_mark=$matche[1];
	    if(strpos($block_mark,"|")){

	        list($mark,$remark,$type)=explode("|", $block_mark);
	        if(empty($mark))return '';
	        if(empty($type)){
                $type="html\\html";
            }else{
                $type=str_replace("_","\\",$type);
            }
            if(empty($remark)){
                $remark="未命名";
            }
    	    $this->static_block['start'][$mark]=0;
			$this->static_block['start'][$mark]=count($this->static_block['start']);
			$this->static_block['mark_arg'][$mark]=array('mark'=>$mark,'remark'=>$remark,'type'=>$type);
            $id='bs_'.dechex($this->div_ini_id++);
            $this->view_ids['b'.$mark]=$id;
	        return '<!--static_mark_//w3capp.com-->';
	    }
	    return '';
	}
	function tplStaticEnd($matche){
	    $this->static_block['end'][$matche[1]]=0;
		$this->static_block['end'][$matche[1]]=count($this->static_block['end']);
	    return '<!--static_mark_//w3capp.com-->';
	}
    function explainParent($content){
        $parent_tpl=null;
        $content=preg_replace_callback("/<!--parent_start::([^>]+)-->/",
            function($matched)use(&$parent_tpl){
                $parent_tpl=$matched[1];return '<!--parent_mark_//w3capp.com-->';
            }
            ,$content,1);

        if($parent_tpl==null){
            $content=preg_replace_callback("/<!--parent_file::([^>]+)-->/",
                function($matched)use(&$parent_tpl){
                    $parent_tpl=$matched[1];return '<!--parent_file_//w3capp.com-->';
                }
                ,$content,1);
            if($parent_tpl==null) {
                return $content;
            }
        }
        if(strpos($content,'<!--parent_end::'.$parent_tpl.'-->')){
            list($lp1,$lp_2)=explode("<!--parent_mark_//w3capp.com-->",$content);
            list($lp2,$lp3)=explode('<!--parent_end::'.$parent_tpl.'-->',$lp_2);
            if(!$lp2){
                return $content;
            }
            $parent_explain=new W3cTplParent($parent_tpl);
            if($parent_explain->tplHasParsed()==false){
                $parent_explain->setTplContent($this->template($this->tpl($parent_tpl)));
            }

            $content=$lp1.$this->explainParent($parent_explain->extendsExplain($lp2)).$this->explainParent($lp3);
        }else{
            list($lp1,$lp2)=explode("<!--parent_file_//w3capp.com-->",$content);
            $parent_explain=new W3cTplParent($parent_tpl);
            if($parent_explain->tplHasParsed()==false){
                $parent_explain->setTplContent($this->template($this->tpl($parent_tpl)));
            }
            $content=$lp1.$this->explainParent($parent_explain->extendsExplain($lp2));
        }
        return $content;
    }
    function explain($tplct){
        $this->static_block['start']=[];
        $this->static_block['end']=[];
        if(!empty($this->tpl_const))
            $tplct=strtr($tplct,$this->tpl_const);
        $tplct=preg_replace_callback("/[\\\\]*\\?{([^}]+)}/",
            array($this,"tplEcho"),
            $tplct);
        $tplct=preg_replace_callback("/<!--include::([^>\"'\\-]+)-->/",
            array($this,"tplInclude"),
            $tplct);
        $tplct=preg_replace_callback("/<!--editarea::([^>\"'\\-]+)-->/",
            array($this,"tplEditarea")
            ,$tplct);
        //$tplct=preg_replace_callback("/<!--block::([^>]+)-->/",
        //    array($this,"tplBlockHold")
        //    ,$tplct);
        $tplct=preg_replace_callback("/<!--static_start::([^>\"'\\-]+)-->/",
            array($this,"tplStaticBegin")
            ,$tplct);
        $tplct=preg_replace_callback("/<!--static_end::([^>\"'\\-]+)-->/",
            array($this,"tplStaticEnd")
            ,$tplct);
        $tplct=preg_replace_callback('/<!--\{loop\s+\$([^(]+)\((["\s\w\d\$,_\(\)\'\[\]]+)\)\s*\}-->/',
            function($match){
                if(strpos($match[2],',')){
                    list($v1,$v2)=explode(",",$match[2]);
                    return '<?php foreach($'.$match[1]." as $v1 => $v2){?>";
                }else{
                    return '<?php foreach($'.$match[1]." as {$match[2]}){?>";
                }
            }
            ,$tplct);
        $tplct=preg_replace_callback('/<!--\{(skip|eif|if|loop)\s*\(([\-\*\/"\s\w\d\$,;_\.\(\)\'=<>&\|!\^\[\]]+)\)\s*\}-->/',
            function($match){
                if($match[1]=="skip"){
                    return '<?php if('.$match[2].')continue;?>';
                }else if($match[1]=="eif"){
                    $m_title='}else if';
                }else{
                    $m_title=$match[1]=='if'?'if':'for';
                }
                return '<?php '.$m_title.'('.$match[2]."){?>";
            }
            ,$tplct);
        if(count($this->static_block['start'])){
            $tpl_blocks=explode('<!--static_mark_//w3capp.com-->', $tplct);
            $tpl_result=array();
            foreach($tpl_blocks as $ti=>$tpl_text){
                if($ti%2==0){
                    $tpl_result[]=$tpl_text;
                }
            }
            foreach ($this->static_block['start'] as $mark_key => $val) {
                if(empty($this->static_block['end'][$mark_key]))continue;
                if($this->static_block['end'][$mark_key]==$val){
                    $blockmark=$this->file_var.'_'.$mark_key;
                    $tpl=$tpl_blocks[1+($val-1)*2];
                    preg_match('/<!--init--(\s+)\{([\s\S]+)\}(\s+)--init-->/',$tpl,$init_set);
                    $init_arg=array();
                    if($init_set){
                        $tpl=str_replace($init_set[0], '', $tpl);
                        //$init_hash=md5(str_replace(array("\n"," ","\t","\r"),'',$init_set[2]));
                        $init_arg=json_decode('{'.$init_set[2].'}',true);
                        if($init_set[2]&&empty($init_arg)){
                            $tpl.="init 格式无法识别";
                        }else if($init_arg['is_common']){
                            unset($init_arg['is_common']);
                            $blockmark=$mark_key;
                            if(false==empty($init_arg['css'])){
                                $this->css_list[$blockmark]=$init_arg['css'];
                            }
                        }
                        if(empty($init_arg['init_hash'])){
                            $ln=strlen($init_set[2]);
                            $ir=0;
                            for($i=0;$i<$ln;$i+=2){
                                if($ir>100000){
                                    break;
                                }
                                $ir+=ord($init_set[2]{$i});
                            }
                            $init_arg['init_hash']='l'.$ln.$ir;
                        }
                        //$init_hash;
                    }
                    if(empty($init_arg['data_output'])){
                        if(empty($init_arg['just_store'])){
                            $this->appendBlock($blockmark);
                            $this->view_ids['ab'.$blockmark]=$this->view_ids['b'.$mark_key];
                            $tpl_result[$val-1].='<div id="'.$this->view_ids['b'.$mark_key].'" class="block"><?php $this->loadBlock("'.$blockmark.'",$this->block_args["'.$mark_key.'"])?></div>';
                        }else{
                            $blockmark=$mark_key;
                            $this->appendBlock($blockmark);
                            $this->attach_blocks[]=$blockmark;
                            unset($init_arg['just_store']);
                        }
                    }else{
                        $w_as=ord(strtoupper($init_arg['data_output']{0}));
                        if($init_arg['data_output']{0}=='_'||$w_as>=65&&$w_as<=90){
                            $this->appendBlock($blockmark);
                            $tpl_result[$val-1].='<?'."php \${$init_arg['data_output']}=\$this->all_blocks[\"$blockmark\"]->loadData(\$this->block_args[\"$mark_key\"]); ?".'>';
                        }else{
                            $tpl_result[$val-1].="data output error";
                        }

                    }


                    unset($this->view_ids['b'.$mark_key]);
                    if($this->block_manager){
                        $this->block_manager->saveHiddenBlock($this->file_var,$blockmark,
                            $this->static_block['mark_arg'][$mark_key]['remark'],
                            $this->static_block['mark_arg'][$mark_key]['type'],
                            $tpl,$init_arg
                        );
                    }

                }else{
                    unset($this->static_block['start'][$mark_key]);
                }
            }

            $tplct=implode("", $tpl_result);
        }
		if(strpos($tplct,'<!--c-->')!==false)$tplct=preg_replace("/>[\\s\\n]+</","><",str_replace("<!--c-->","",$tplct));
        return $this->explainParent($tplct);
    }
	/**
	 * 编译模版文件
	 */
	protected function template($tplfile){
	    if(count($this->tpl_list)>120)return '编译的模版文件超过了上限';
		if($tplct=file_get_contents($tplfile)){
		    $file_=str_replace(W3CA_MASTER_PATH,"",$tplfile);
		    if($this->tpl_list&&$file_==end($this->tpl_list))
		        return '';
			$this->tpl_list[]=$file_;
		}else{
			return '';
		}
		return $this->explain($tplct);
	}

	function clearTplCache($f_name,$dir_key){
	    $viewname=str_replace("/", "_", $f_name);
	    $load_file=W3CA_MASTER_PATH.$this->export_dir.$dir_key.$viewname.".php";
	    return unlink($load_file);
	}
	function file($tplfile,$toDir){
	    if(!file_exists($tplfile)){
	        return null;
	    }
	    $load_file=W3CA_MASTER_PATH.'data/cache/'.rtrim($toDir,'/');
	    if(!file_exists($load_file)){
	        mkdir($load_file);
	    }
	    $key_dat=self::$app->getConfig("random_key");
	    $load_file.='/'.$key_dat.str_replace([W3CA_MASTER_PATH,"/","\\",":","?"],'',$tplfile);
	    if(file_exists($load_file)==false||filemtime($tplfile)>filemtime($load_file)){
	        $this->tpl_const=array_merge(self::$app->instance->_tpl_const(),$this->tpl_const);
	        $tpl_ct=$this->template($tplfile);
	        file_put_contents($load_file, $tpl_ct);
	    }
	    return $load_file;
	}
	protected function tpl($name){
        foreach ($this->tpl_dirs as $dir){
            $file=$dir.$name.".php";
            if(is_file($file)){
                return $file;
            }
            $file=$dir.$name.".htm";
            if(is_file($file)){
                return $file;
            }
        }
		//模板不存在
        echo '模板不存在:' . $name;
        return null;
	}
	protected function tplKey($dir_key){
	    $cache=new Cache();
        $key_dat=self::$app->getConfig("random_key");
	    $dir_keys=$cache->value($key_dat."tpl2dir");

	    if($dir_keys){
            $dir_i=strpos($dir_keys,$this->tpl_dirs[0]);
            if($dir_i===false){
                $dir_keys=$dir_keys.$this->tpl_dirs[0];
                $cache->saveValue($key_dat."tpl2dir",$dir_keys);
                $dir_i=strpos($dir_keys,$this->tpl_dirs[0]);
            }
        }else{
            $dir_keys=$key_dat.$this->tpl_dirs[0];
            $cache->saveValue($key_dat."tpl2dir",$dir_keys);
            $dir_i=strpos($dir_keys,$this->tpl_dirs[0]);
        }
        return $dir_i.W3CA_REWRITE_URL.$dir_key;
    }

    /**
     * @param $file_var
     * @param $file_param
     * 保存编译用到的参数
     */
    protected function saveParseParam($file_var,$file_param){
        $store_file=W3CA_MASTER_PATH.$this->export_dir.$file_var.".arg";
        return file_put_contents($store_file,serialize($file_param));
    }
    public function readParseParam($file_var){
        $store_file=W3CA_MASTER_PATH.$this->export_dir.$file_var.".arg";
        $param=unserialize(file_get_contents($store_file));
        foreach ($param as $key=>$value){
            $this->$key=$value;
        }
        return $param;
    }
    function varCode($array){
        return strtr(var_export($array,true),["',\n  '"=>"','","',\n)"=>"')","',\n    '"=>"','","',\n  )"=>"')"]);
    }
    /**
     * @param $fname
     * @param $dir_key
     * @return string|void
     * 开始编译
     */
	function parse($fname,$dir_key){
	    $tplfile=$this->tpl($fname);
		if($tplfile==null){
			return;
		}
	    $viewname=str_replace("/", "_", $fname);
		$this->file_var=$file_name=$this->tplKey($dir_key).$viewname.".php";
	    $load_file=W3CA_MASTER_PATH.$this->export_dir.$file_name;
		$this->div_ini_id=10000+W3CA_UTC_TIME%10000;
		$file_var="\$this->file_var";
	    $class_cont="<?php ".$file_var."='".$file_name."';";
	    if(file_exists($load_file)==false||filemtime($tplfile)>filemtime($load_file)){
	        if(strlen($viewname)>80){
	            throw new \Exception("tpl filename length out of 80");
            }
	        if(preg_match("/[\\:#&*?<>\\\\\\/]/",$viewname)){
                throw new \Exception("tpl filename error char(*&?: ......)");
            }
	        $this->edit_export=$this->tpl_list=$this->load_blocks=array();
	        //固定变量
	        $this->tpl_const=array_merge(self::$app->instance->_tpl_const(),$this->tpl_const);
	        $tpl_ct=$this->template($tplfile);
	        $class_vars="\$this->tpl_dir='".base64_encode(str_replace("'","",$this->tpl_dirs[0]))."';";
	        if($this->load_blocks){
	            $class_vars.="\$this->block_marks=".$this->varCode($this->load_blocks,true).";";
	        }
            if($this->attach_blocks){
                $class_vars.="\$this->attach(".$this->varCode($this->attach_blocks,true).");\n";
            }
			if($this->block_manager&&$this->load_blocks){
                $infos_var=$this->varCode($this->block_manager->listOf($this->load_blocks),true);
            }else{
                $infos_var='[]';
            }


            $this->saveParseParam($file_name,['file'=>$fname,
                "dir_key"=>$dir_key,
                "tpl_const"=>$this->tpl_const,
                "view_ids"=>$this->view_ids,
                "div_ini_id"=>$this->div_ini_id,
                "load_blocks"=>$this->load_blocks,
                "tpl_list"=>$this->tpl_list,
                "block_areas"=>$this->edit_export,
                "tpl_dirs"=>$this->tpl_dirs]);

	        $class_cont.=$class_vars."\$this->blocksInit($infos_var); self::$app->template()->clearTimeout(".$file_var.",array(\"".implode("\",\"",$this->tpl_list)."\"));?>".$tpl_ct;
	        if(file_put_contents($load_file, preg_replace('/([;\}\{])\s*\?><\?php/','$1',$class_cont))&&$this->block_manager){
                $this->block_manager->updateBlock($file_name,$this->load_blocks);
            }
            try{
                $this->checkSyntax($load_file);
            }catch (Exception $e){
                unlink($load_file);
                throw $e;
            }
	    }
	    return $load_file;
	}
	public function clear(){
	    $this->readParseParam($this->file_var);
	    $this->clearTimeout($this->file_var,$this->tpl_list);
    }
    //受命行限定可能无效
    function checkSyntax($fileName)
    {
        // If it is not a file or we can't read it throw an exception
        if(!is_file($fileName) || !is_readable($fileName))
            throw new Exception("Cannot read file ".$fileName);

        // windows 要用全路径：D:\\wamp64\\bin\\php\\php5.6.25\\
        $output = shell_exec('php -l "'.$fileName.'"');
        // Try to find the parse error text and chop it off
        $syntaxError = preg_replace("/Errors parsing.*$/", "", $output, -1, $count);

        // If the error text above was matched, throw an exception containing the syntax error
        if($count > 0)
            throw new Exception(trim($syntaxError));

    }
	//清除过期模板
	function clearTimeout($cf,$tpls){
	    $tf=W3CA_MASTER_PATH.$this->export_dir.$cf;
		$time0=filemtime($tf);
		foreach ($tpls as $t) {
			if(filemtime(W3CA_MASTER_PATH.$t)>$time0){
				if(!unlink($tf)){
					echo "<!--clear error!-->";
				}
				return;
			}
		}
	}

    /**
     * 重新编译模版
     * @param $file
     * @param string $tpl
     * @param string $tpl_dir
     * @param string $default_dir
     * @param string $tpl_mark
     * @return array
     */
	function clearFile($file,$tpl=""){
		if(unlink(W3CA_MASTER_PATH.$this->export_dir.$file)){
		    if($tpl){
                $param=$this->readParseParam($file);
                $this->parse($tpl, $param['dir_key']);
		        return ['view_ids'=>$this->view_ids,'clear'=>"OK"];
            }
            return ['clear'=>"OK"];
        }
        return ['clear'=>"fails"];
	}


}

