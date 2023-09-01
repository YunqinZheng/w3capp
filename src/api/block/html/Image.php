<?php 
namespace api\block\html;
/**
 * 静态HTML模块
 */
class Image extends \api\block\BlockTpl{
	function getPrototypeForm(){
	    $form=parent::getPrototypeForm();
		$form['update_time']=array("form_input"=>"hidden","def_value"=>'-1');
	    $form['image_urls']=array("col_name"=>"图片地址","form_input"=>"diycode","def_value"=>Str::htmlchars($this->info("image_urls")),"diycode"=>'<div class="diycode">
	    <dl class="form_mg"><dt><input name="image_urls" value="{col_value}" type="hidden"/>
<style>
	.photo_list .imgs{margin-left:35px;margin-top:20px;}
	.photo_list .imgs li{float:left; width: 25%;margin-left:2px;}
	.photo_list .imgs li:hover{background:#dee7ff;}
	.photo_list .imgs img{height: 100px;max-width:100%;margin:5px;}
	.photo_list .deleteimg{float: right;margin-right:5px}
	.photo_list{min-width:600px;}
	.form_mg .url_txt{display:none;}
	.form_mg .url_txt textarea{width:100%;height:55px;}
	.btnlink a{padding:0px 5px;font-size:12px;}
	.btnlink a:hover{text-decoration:none;}
</style><div class="url_txt"><div class="explain">多个URL用英文“,”分开</div><textarea></textarea><div class="edit_end"><a class="save">确定</a>
&nbsp;<a class="cancel">取消</a></div></div>
	    <div class="photo_list">
	    <ul class="imgs cl"></ul>
	    </div></dt>
	    <dd class="left-offset btnlink"><a class="addimg" onclick="openfiles()">添加图片</a>
	    <a class="editimg" onclick="editurl()">编辑图片链接</a></dd></dl></div>
	    <script src="{URL_ROOT}static/script/image_block.js"></script>
	    ');
		return $form;
	}
}