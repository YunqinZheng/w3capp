<?php
namespace w3capp;
use w3capp\helper\Str;
class UI extends Core {
	var $block_args;
    protected $tpl;
    protected $block_marks;
	protected $all_blocks_info;
    protected $all_block;
    protected $fun_block_obj;
    protected $tpl_dir;

    protected $attach_block_marks;
    var $return_page;

	function __construct($tpl=""){
        $this->cookie_pre=self::_preCookie();
	    $this->block_args=new \w3c\helper\ArgList();
	    $this->block_marks=array();
	    $this->tpl=$tpl;
		$this->attach_block_marks=[];
		$this->return_page=false;
	}

	static function show404(){
        $content='<html><body><h1 style="font-size:72px;margin:100px;text-align:center; text-shadow: 0px 0px 10px #F44336;color: #ddd;text-shadow: 0px 0px 10px #2c3542;">404</h1></body></html>';
        if(W3cApp::$holder_response){
            W3cApp::setResponse(404,[],$content);
        }else{

            header("HTTP/1.1 404 Not Found");
            echo $content;
            exit;
        }
	}
	/**
	 * 分页按钮
	 */
	static function pageFoot($idx,$size,$amount,$url=""){
		echo "<span class='explain'>总共有".$amount."条记录</span>";
		echo self::pageLink($idx, $size, $amount,$url);
	}
	static public function pageLink($index,$size,$amount,$url){
		$pc=ceil($amount/$size);
		$link_url="";
		$link_start="";
		$loop_s=1;
		if($index>5){
			$link_start="<span class='link'><a href=\"".($url?str_replace("[page]", 1, $url):"javascript:pageGoto(1)").'">1</a></span><span class="space">...</span>';
			$loop_s=$index-4;
		}
		$link_end="";
		if($pc-$index>5){
			$link_end="<span class=\"space\">...</span><span class='link'><a href=\"".($url?str_replace("[page]", $pc, $url):"javascript:pageGoto(".$pc.")").'">'.$pc.'</a></span>';
			$pc=$index+4;
		}
		if($pc>1)
		for($i=$loop_s;$i<=$pc;$i++){
			if($i==$index)
			{
				$link_url.="<span class='link pidx'>$i</span>";
			}else{
				if($url){
					$linkurl=str_replace("[page]", $i, $url);
				}
				else{
					$linkurl="javascript:pageGoto($i)";
				}
				$link_url.="<span class='link'><a href='$linkurl'>$i</a></span>";
			}
		}
		return $link_start.$link_url.$link_end;
	}
	static public function pageObjLink($obj,$url){
	    if(empty($obj)||$obj->amount==0){
	        echo '<div class="text-center">没有相关数据！</div>';
	        return;
        }
	    self::pageFoot($obj->page_index, $obj->page_size, $obj->amount,$url);
	}
	/**
	 * 提示+链接
	 */
	static function message($msg,$type,$links,$widthHtml=false,$out_index=-1){
	    $v=new self("common/message");
		$v->message=$widthHtml?$msg:Str::htmlchars($msg);
		$v->class_type=$type;
		$v->link_list=$links;
		$v->widthHtml=$widthHtml;
		$v->out_index=$out_index;
		$v->output();
		exit;
	}

    /**
     * 提示后返回或跳转
     * @param $msg
     * @param string $url
     * @param string $type
     */
	static function referer($msg,$url="",$type="error"){
		$url=strtr($url,W3cApp::$instance->_tpl_const());
	    self::message($msg,$type,array(array("href"=>$url?$url:$_SERVER['HTTP_REFERER'],"text"=>"返回")),false,0);
	}

	static function outputInside($return_value,$tpl_mark="main"){
	    $value="";
	    if($return_value instanceof self){
	        ob_start();
	        $return_value->output($tpl_mark);
	        $value=ob_get_contents();
	        ob_end_clean();
	        if(strpos($value, "<body")){
	           $value=strip_tags($value,"span,div,p,script,link,style,table,tr,td,th,button,input,textarea,a,form");
	        }
	    }
        if(is_array($return_value)){
            $value=Str::toJson($return_value);
            $content='<html><head></head><body><script type="w3capp/js">editv.oncallback('.$value.',"w3capp");</script>w3cappscript</body></html>';
            if(W3cApp::$holder_response){
                W3cApp::setResponse(200,["Content-Type"=>"text/html;charset=".W3CA_DB_CHAR_SET],$content);
            }else{
                header("Content-Type:text/html;charset=".W3CA_DB_CHAR_SET);
                echo $content;
                exit;
            }
            
        }else{
            $body_attr=' action="'.$return_value->return_action.'" error=0 code=""';
            $content='<html><body title="'.$return_value->title.'" '.$body_attr.'>'.strtr($value,array("<script>"=>"<script type=\"w3capp/js\">","text/javascript"=>"w3capp/js","application/javascript"=>"w3capp/js")).'</body></html>';
            if(W3cApp::$holder_response){
                W3cApp::setResponse(200,["Content-Type"=>"text/html;charset=".W3CA_DB_CHAR_SET],$content);
            }else{
                header("Content-Type:text/html;charset=".W3CA_DB_CHAR_SET);
                echo $content;
                exit;
            }
        }
	}

    /**
     * pageEnd 一定要放在</body>之前
     */
	protected function pageEnd(){
	    echo '<script type="text/javascript" src="'.W3CA_URL_ROOT.'static/script/cms/w3cui.js"></script>';
	    if($this->edit_enabled){
            echo '<link rel="stylesheet" href="'.W3CA_URL_ROOT.'static/style/web_page.css" type="text/css"/><script type="text/javascript" src="'.W3CA_URL_ROOT.'static/ckeditor/ckeditor.js" ></script>
			<script type="text/javascript" src="'.W3CA_URL_ROOT.'static/ckeditor/init_function.js"></script>
			<script type="text/javascript">window.page_cache_file="'.$this->file_var.'";if(parent==this&&$("#w3capp_scr").length){ $("body").append(\'<button type="button" title="编辑界面" class="pgbutton" style="width: 22px; height: 22px;" onclick="openEdit(&#39;'.self::_form_hash().'&#39;);"></button>\');}if(window.location.hash=="#edit_page")openEdit("'.self::_form_hash().'");</script>';
	    }
	}
    function includeTpl($tpl_mark=""){
        if($this->tpl){
	        $this->tpl_mark=$tpl_mark;
            extract(self::$assign_val);
			if(empty(W3cApp::$instance)){
				throw new Exception('控制器未无成初始化！');
			}
	        include W3cApp::template()->parse($this->tpl, $tpl_mark);
	    }
    }
	function output($tpl_mark=""){
	    if(W3cApp::$holder_response){
            ob_start();
            $this->includeTpl($tpl_mark);
            $content=ob_get_contents();
	        ob_end_clean();
            W3cApp::setResponse(200,["Content-Type"=>"text/html;charset=".W3CA_DB_CHAR_SET],$content);
        }else{
            $this->includeTpl($tpl_mark);
        }
	}
	public function outInfo($file){
		$this->return_page=true;
		include W3CA_PATH.W3cApp::template()->getExportDir().$file;
	}
    function assign($key,$val){
	    parent::_assign($key,$val);
    }
    function putString($key,$val){
	    if(is_array($val)){
	        foreach ($val as $k=>$str){
                parent::_assign($key."_".$k,Str::htmlchars($str));
            }
        }else{
            parent::_assign($key,Str::htmlchars($val));
        }

    }
    /**
     * 把数组变成Option
     * @param $array array(1=>"显示名")或者array(array(1,"显示名"))
     */
    static public function arrayToOptions($array,$defvalue){
        $rs="";
        foreach($array as $key=>$value){
            if(is_array($value)){
                $value=array_values($value);
                if(is_array($defvalue)&&in_array($value[0], $defvalue)
                    ||$defvalue==$value[0]&&$value!==0){
                    $rs.="<option value=\"".$value[0]
                        ."\" selected >".$value[1]."</option>";
                }else{
                    $rs.="<option value=\"".$value[0]
                        ."\" >".$value[1]."</option>";
                }
            }else{

                if(is_array($defvalue)&&in_array($key, $defvalue)||$defvalue==$key&&$key!==0){
                    $rs.="<option value=\"".$key."\" selected >$value</option>";
                }else{
                    $rs.="<option value=\"".$key
                        ."\">$value</option>";
                }
            }
        }
        return $rs;
    }

    /**
     * 把数组变成Radio
     * @param $array array(1=>"显示名")或者array(array(1,"显示名"))
     * @param $boxname 表单名
     */
    static public function arrayToRadio($array,$boxname,$defvalue=''){
        $rs="";
        foreach($array as $key=>$value){
            if(is_array($value)){
                $value=array_values($value);
                $rs.="<label><input type=\"radio\" name=\"".$boxname."\" value=\"".$value[0]
                    ."\" ".($defvalue==$value[0]?'checked="checked"':'').
                    "/>".$value[1]."</label>";
            }else{
                $rs.="<label><input type=\"radio\" name=\"".$boxname."\" value=\"".$key
                    ."\" ".($defvalue==$key?'checked="checked"':'').
                    "/>$value</label>";
            }
        }
        return $rs;
    }
    /**
     * 把数组变成checkbox
     * @param $array array(1=>"显示名")或者array(array(1,"显示名"))
     * @param $boxname 表单名
     */
    static public function arrayToCheckbox($array,$boxname,$defvalue=''){
        $rs="";
        foreach($array as $key=>$value){
            if(is_array($value)){
                $value=array_values($value);
                if(is_array($defvalue)&&in_array($value[0],$defvalue)||!empty($defvalue)&&$value[0]==$defvalue){
                    $rs.="<label><input type=\"checkbox\" name=\"".$boxname."\" value=\"".$value[0]
                        ."\" checked/>".$value[1]."</label>";
                }else{
                    $rs.="<label><input type=\"checkbox\" name=\"".$boxname."\" value=\"".$value[0]
                        ."\" />".$value[1]."</label>";
                }
            }else{
                if(is_array($defvalue)&&in_array($key,$defvalue)||!empty($defvalue)&&$key==$defvalue){
                    $rs.="<label><input type=\"checkbox\" name=\"".$boxname."\" value=\"".$key
                        ."\" checked/>$value</label>";
                }else{
                    $rs.="<label><input type=\"checkbox\" name=\"".$boxname."\" value=\"".$key
                        ."\" />$value</label>";
                }
            }
        }
        return $rs;
    }

	public function attach($mark_){
	    if(is_array($mark_)){
            $this->attach_block_marks=array_merge($this->attach_block_marks,$mark_);
        }else{
            $this->attach_block_marks[]=$mark_;
        }
    }
    protected function outputParam(){
        $ctrl_path=empty($this->edit_page_controler)?"cms/block":$this->edit_page_controler;
        $block_api=["file_var"=>$this->file_var,"addform"=>"addblockform","page_arg"=>["tpl"=>$this->tpl,"tpl_mark"=>$this->tpl_mark,"tpl_dir"=>$this->tpl_dir,"attachs"=>$this->attach_block_marks],
            "edit_ctrl_url"=>W3cApp::route($ctrl_path)];
        $page_param=W3cApp::template()->readParseParam($this->file_var);
        $block_api['blocks_info']=$this->all_blocks_info;
        $block_api['view_ids']=$page_param['view_ids'];
        $block_api['block_areas']=$page_param['block_areas'];
        $block_api['theme']=null;
        if($this->theme_id){
            $block_api['theme']=$this->theme->getAttributes();
        }
        if($this->frame_layout){
            $block_api['frame_layout']=$this->frame_layout;
        }
        if(W3cApp::$holder_response){
            W3cApp::setResponse(200,["Content-Type"=>"application/json; charset=".W3CA_DB_CHAR_SET],Str::toJson(array('error'=>0,"message"=>'',"data"=>$block_api)));
        }else{
            header("Content-Type: application/json; charset=".W3CA_DB_CHAR_SET);
            echo Str::toJson(array('error'=>0,"message"=>'',"data"=>$block_api));
            exit;
        }
    }
	protected function blocksInit($init_info){
        $this->all_blocks_info=array();
	    if(empty($init_info))return;
        $all_blocks=array();
	    foreach ($init_info as $info){
            $all_blocks[$info['mark']]=W3cApp::$instance->_init_block($info);
			$this->all_blocks_info[$info['mark']]=$info;
	    }
        $this->all_blocks=$all_blocks;
	    if($this->return_page){
            $this->outputParam();
        }
	}
	/**
	 * 加载指定的数据
	 */
	protected function loadBlock($mark){
	    $block_obj=$this->all_blocks[$mark];
	    if($block_obj){
	        W3cApp::$instance->_display_block($block_obj,$this->block_args[$mark]);
	    }
	}

    static function _get($key){
        return Str::xss_filter($_GET[$key]);
    }
    static function _post($key){
        return Str::xss_filter($_POST[$key]);
    }
}
