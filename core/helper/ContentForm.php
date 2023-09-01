<?php
namespace w3capp\helper;
class ContentForm{

	var $loadid_htmljs=0;
	protected $edit_data_default=array();
	/*
	 * 生成表单
	 */
	function create_form($columns){
		$text="/*?\$editdata=empty(\$edit_data)?{DATA_DEFAULT}:\$edit_data;?*/<input name=\"content_id\" value=\"/*?echo \$editdata['id'];?*/\" type=\"hidden\"/>";
		$column_type=array();
		foreach ($columns as $name=>$c) {
			//if(empty($this->model))$this->model=$c['ctiden'];
			$mname="show_".$c['form_input'];
			$column_type[$name]=$c['form_input'];
			//if(in_array($c['form_input'], array("checkbox","radio","select","mselect"))&&!is_array($value)){
			//	$value=unserialize($value);
			//}
			if(isset($c['def_value'])&&false==in_array($c['def_value'],array('{MEMBER_ID}','{MEMBER_NAME}','{MEMBER_EMAIL}','{UTC_TIME}'))){
			    $this->edit_data_default[$name]=$c['def_value'];
			}else{
                $c['def_value']='';
            }
			if(method_exists($this, $mname)){
				if($c['form_input']=="hidden"){
					$text.=$this->$mname($name,$c)."\n";
				}else{
					$text.='<div class="formline c'.$c['form_input'].'">'.
					$this->$mname($name,$c)."</div>\n";
				}
				
			}else{
				$text.='<div class="formline c'.$c['form_input'].'">'.
				$this->show_text($name,$c)."</div>\n";
			}
		}
		if(!isset($column_type['tpl'])){
		    $column_type['tpl']="hidden";
		}
		if(!isset($column_type['type'])){
		    $column_type['type']="hidden";
		}
		if(!isset($column_type['mark'])){
		    $column_type['mark']="hidden";
		}
		if(!isset($column_type['remarks'])){
		    $column_type['remarks']="hidden";
		}
		if(!isset($column_type['update_time'])){
		    $column_type['update_time']="hidden";
		}
		return str_replace("{DATA_DEFAULT}", var_export($this->edit_data_default,true), $text).'
		<input type="hidden" value="'.base64_encode(serialize($column_type))
		.'" name="data_/*?echo $this->cookie_pre?*/"/>';
	}
	function data_descript_input($columns){
	    $column_type=array();
	    foreach ($columns as $name=>$c) {
	        $column_type[$name]=$c['form_input'];
	    }
	    return '<input type="hidden" value="'.base64_encode(serialize($column_type))
		.'" name="data_'.\W3cUI::previousCookie().'"/>';
	}
	function get_form_data($filter=null){
		$post=array();
		$post_key='data_'.\W3cUI::previousCookie();
		if($_POST[$post_key]){
			
		    //$this->columns=
			$columns=unserialize(base64_decode($_POST[$post_key]));
			foreach($columns as $column=>$type){
				if($filter&&!in_array($column, $filter))continue;
			    if(isset($_POST[$column]))
				switch($type){
					case 'date':
					case 'datetime':
						$post[$column]=strtotime($_POST[$column]);
						break;
					/*case 'html':
						$post[$column]="var:".$column;
						$this->var_replace["var:".$column]=Str::xss_filter($_POST[$column]);//str_ireplace(array('on','javascript'), array("<span>o</span>n","java<span>script</span>"), preg_replace("/(<script|<iframe|<frame)/i", "$1&nbsp;\n", $_POST[$column]));
						break;
					*/
					case 'file':
					    $uploader=new \w3c\helper\Uploader();
						if($uploader->upload_enabled($column)){
							$uploader->init("data/upload/",array("gif","jpg","jpeg","png","zip","txt","html","htm","xml","css"));
                            $uploader->set_input_file($column);
							$u_info=$uploader->save_to();
							if($u_info!==FALSE){
								$post[$column]=$u_info->save_as;
							}
						}
						break;
					default:
						$post[$column]=$_POST[$column];
				}
			}
		}
		return $post;
	}
	/*?
	 * 字段值
	 * 如果$type为radio\select\checkbox\mselect则返回array('set'=>?,'value'=>?)
	 */
	protected function get_c_value($col,$type,$formset){
		if(in_array($type, array("checkbox","radio","select","mselect"))){
			return array("value"=>' $editdata[\''.$col.'\'] ',"set"=>unserialize($formset));
		}else if($type=="diycode"){
			return strtr($formset,array("{value}"=>'/*?echo $editdata[\''.$col.'\'];?*/',"{column}"=>$col));
		}else if($formset){
			return '/*?echo $editdata[\''.$col.'\']?$editdata[\''.$col.'\']:\''.$formset.'\';?*/';
		}else{
			return '/*?echo $editdata[\''.$col.'\'];?*/';
		}
	}
	
	protected function show_hidden($name,$column){
	    $v=$column['def_value'];
		return '<input name="'.$name.'" type="hidden" value="/*?echo Str::htmlchars($editdata[\''.$name.'\']);?*/"/>';
	}
	protected function show_file($name,$column){
	    $name2=$column['col_name'];
		return '<span class="labt">'.$name2.':</span><p class="inct"><input name="'.$name.
		'" type="file" /><span>'.$this->c_values[$name].'</span></p>'.
		"<input name=\"file_v_$name2\" type=\"hidden\" value=\"".'/*?echo Str::htmlchars($editdata[\''.$name.'\']);?*/"/>';
	}
	protected function show_password($name,$column){
	    $name2=$column['col_name'];
		return '<span class="labt">'.$name2.':</span><p class="inct"><input type="password" name="'.$name.'" value="/*?echo Str::htmlchars($editdata[\''.$name.'\']);?*/"/></p>';
	}
	protected function show_text($name,$column){
	    $v=$column['def_value'];
	    $name2=$column['col_name'];
		return '<span class="labt">'.$name2.':</span><p class="inct"><input type="text" name="'.$name.'" value="/*?echo Str::htmlchars($editdata[\''.$name.'\']);?*/"/></p>';
	}
	protected function show_diycode($name,$column){
	    $v=$column['def_value'];
		return strtr($column['diycode'],array("{col_value}"=>'/*?echo $editdata[\''.$name.'\'];?*/',"{col_name}"=>$column['col_name']));
	}
	protected function show_date($name,$column,$no_t=1){
	    $v=$column['def_value'];
	    $name2=$column['col_name'];
	    $date_format=$no_t?'Y-m-d':'Y-m-d H:i:s';
		$s='<span class="labt">'.$name2.':</span><div class="inct"><input type="text" id="'.$name
		.'" name="'.$name.'" value="/*?echo $editdata[\''.$name.'\']?date("'.$date_format.'",$editdata[\''.$name.'\']):\''.$v.'\';?*/"/><div style="position:relative;"><div id="'.$name.'ca" style="display:none;" class="ca_sel"></div></div><script>request_js("date",function(){date_view("'.$name.'",'.$no_t.')});</script></div>';
		return $s;
	}
	protected function show_datetime($name,$column){
		return $this->show_date($name, $column,0);
	}
	protected function show_script($name,$column){
	    $v=$column['def_value'];
	    $name2=$column['col_name'];
		return '<script>document.write(zyqform.'.$name.'("'.$v.'","'.$name2.'"));</script>';
	}
	protected function show_textarea($name,$column){
	    $v=$column['def_value'];
	    $name2=$column['col_name'];
		return '<span class="labt">'.$name2.':</span><p class="inct"><textarea name="'.$name.'" >/*? echo Str::htmlchars($editdata[\''.$name.'\']);?*/</textarea></p>';
	}
	protected function show_picupload($name,$column){
	    $name2=$column['col_name'];
		return '<script src="{URL_ROOT}static/script/html5uploader.js" type="text/javascript"></script><span class="labt">'.$name2.'</span><div class="inct"><input name="'.$name.
		'" type="hidden" /><input id="file_pic_'.$name.'" name="f_pic_'.$name.'" type="file" data="/*?echo $editdata[\''.$name.'\'];?*/"/>
		<link rel="stylesheet" href="{URL_ROOT}static/yunfile/upload.css"><script>
		up2local_pic("file_pic_'.$name.'","'.$name2.'");
		</script></div>';
	}
	protected function show_radio($name,$column){
	    $name2=$column['col_name'];
	    if(isset($column['def_value']))$this->edit_data_default[$name]=explode(",", $column['def_value']);
		return '<span class="labt">'.$name2.':</span><p class="inct">/*? echo self::arrayToRadio('.
		var_export($column['value_list'],true).', "'.$name.'",$editdata[\''.$name.'\']);?*/</p>';
	}
	protected function show_checkbox($name,$column){
	    $name2=$column['col_name'];
	    if(isset($column['def_value']))$this->edit_data_default[$name]=explode(",", $column['def_value']);
		return '<span class="labt">'.$name2.':</span><p class="inct">/*? echo self::arrayToCheckbox('.
		var_export($column['value_list'],true).', "'.(count($this->c_values[$name]['set'])>1?$name."[]":$name
		).'",$editdata[\''.$name.'\']);?*/</p>';
	}
	protected function show_select($name,$column){
	    if(isset($column['def_value']))$this->edit_data_default[$name]=explode(",", $column['def_value']);
	    $name2=$column['col_name'];
		return '<span class="labt">'.$name2.':</span><p class="inct"><select name="'.$name.'">/*? echo self::arrayToOptions('.
		var_export($column['value_list'],true).',$editdata[\''.$name.'\']);?*/</select></p>';
	}
	protected function show_mselect($name,$column){
	    $v=$column['def_value'];
	    $name2=$column['col_name'];
	    if(isset($column['def_value']))$this->edit_data_default[$name]=explode(",", $column['def_value']);
		return '<span class="labt">'.$name2.':</span><p class="inct"><select name="'.$name.'[]" multiple="multiple">/*? echo self::arrayToOptions('.
		var_export($column['value_list'],true).',$editdata[\''.$name.'\']);?*/</select></p>';
	}
	protected function show_memberhtml($name,$column){
	    $v=$column['def_value'];
	    $name2=$column['col_name'];
		if($this->loadid_htmljs==0){
			$this->loadid_htmljs++;
			$s="<script src=\"{URL_ROOT}static/ckeditor/ckeditor.js\" type=\"text/javascript\"></script>
			<script src=\"{URL_ROOT}static/ckeditor/init_function.js\" type=\"text/javascript\"></script>";
		}
		$s.='<span class="labt">'.$name2.':</span><div class="htmlp"><textarea id="id'.
		$name.'" name="'.$name.'">/*?echo Str::htmlchars($editdata[\''.$name.'\']);?*/</textarea>'.
		'</div><script>init_with_upload("id'.$name.'");</script>';
		return $s;
	}
	protected function show_html($name,$column){
	    $v=$column['def_value'];
	    $name2=$column['col_name'];
		if($this->loadid_htmljs==0){
			$this->loadid_htmljs++;
			$s="<script src=\"{URL_ROOT}static/ckeditor/ckeditor.js\" ></script>
			<script src=\"{URL_ROOT}static/ckeditor/init_function.js\"></script>";
		}
		$s.='<span class="labt">'.$name2.':</span><div class="htmlp"><textarea id="id'.
		$name.'" name="'.$name.'">/*?echo Str::htmlchars($editdata[\''.$name.'\']);?*/</textarea>'.
		'</div><script>init_ckeditor("id'.$name.'");</script>';
		return $s;
	}
	protected function show_channel($n,$column){
	    $v=$column['def_value'];
	    $zn=$column['col_name'];
	    if($v)$this->edit_data_default[$n]=$v;
		return '<span class="labt">'.$zn.':</span><p class="inct"><select name="'.$n.'">/*? echo self::arrayToOptions($channels,$editdata[\''.$n.'\']);?*/</select></p>';
	}
	static function valueList($default){
        $col=array('def_value'=>'');
        $df_list=explode("\n",$default);
        foreach ($df_list as $dfi=>$df_val){
            if($df_val){
                if(strpos($df_val,"=")){
                    list($df_k,$df_v)=explode("=",$df_val);
                    if($df_k{0}=='@'){
                        $df_k=substr($df_k,1);
                        $col['def_value']=$df_k;
                    }
                    $col['value_list'][$df_k]=$df_v;
                }
            }
        }

        return $col;
    }
}
