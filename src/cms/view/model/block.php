<!--include::common/header-->
<style>
.labt{float:left;display:inline-block;width:110px;text-align:right;line-height:30px;}
.formline{clear:both;}
.set_val .labt{width:80px;}
.tpage{background: #fff;}
</style>
<div class="pagetop">
	生成内容模块
</div>
<div class="opbox">
<h3>[模型]/*?echo $this->model_info['type_name']?*/</h3>
	<form method="post" id="mk_b_f" enctype="multipart/form-data"
		target="ajaxf">
		/*?echo $this->columns_descript;?*/
		<div class="config_seting">
			<div id="default_set" class="opbox">
				<table class="tpage">
					<thead>
						<tr>
							<th colspan="5">默认属性</th>
						</tr>
						<tr>
							<td style="width: 100px;">属性</td>
							<td style="width: 100px;">属性名</td>
							<td style="width: 110px;">表单</td>
							<td>值设置</td>
							<td style="width: 50px;"></td>
						</tr>
					</thead>
					<tbody></tbody>
					<tfoot>
						<tr>
							<td colspan="5"><a class="new_set">+添加</a></td>
						</tr>
					</tfoot>
				</table>
			</div>
			<div id="column_list" class="opbox">
				<table class="tpage">
					<thead>
						<tr>
							<th colspan="4">查询字段与格式化</th>
						</tr>
						<tr>
							<td>字段</td>
							<td>指定格式</td>
							<td>格式化字符</td>
							<td style="width: 50px;"></td>
						</tr>
					</thead>
					<tbody></tbody>
					<tfoot>
						<tr>
							<td colspan="4"><a class="new_set">+添加</a><label><input
									name="column_limit" type="checkbox" value="1" />只查询设定的字段</label></td>
						</tr>
					</tfoot>
				</table>
			</div>

			<div id="arg_list" class="opbox">
				<table class="tpage where">
					<thead>
						<tr>
							<th colspan="4">查询条件/数据过滤</th>
						</tr>
						<tr>
							<td style="width: 250px;">条件</td>
							<td>值</td>
							<td>条件名称</td>
							<td style="width: 50px;"></td>
						</tr>
					</thead>
					<tbody></tbody>
					<tfoot>
						<tr>
							<td colspan="4"><a class="new_set">+添加</a><label><input
									name="where_option" value="1" type="checkbox" />设为属性选项</label></td>
						</tr>
					</tfoot>
				</table>
			</div>
			<div class="opbox" id="order_list">
				<table class="tpage order">
					<thead>
						<tr>
							<th colspan="4">排序设置</th>
						</tr>
						<tr>
							<td>排序字段</td>
							<td>排序方式</td>
							<td>排序名称</td>
							<td style="width: 50px;"></td>
						</tr>
					</thead>
					<tbody></tbody>
					<tfoot>
						<tr>
							<td colspan="4"><a class="new_set">+添加</a><label><input
									name="order_option" value="1" type="checkbox" />设为属性选项</label></td>
						</tr>
					</tfoot>
				</table>
			</div>
			<div class="opbox formline cl">
				<table class="tpage page_limit">
					<thead>
						<tr>
							<th colspan="2">分页设置</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td><div class="page-size">
									每页数据条数：<select>
										<option value="5">5条</option>
										<option value="10">10条</option>
										<option value="15">15条</option>
										<option value="1">1条</option>
										<option value="i">自定义</option>
										<option value="i2">属性设置</option>
									</select>
									<div class="append">
										<input name="page_size" value="5" type="hidden"
											class="short_txt" />
									</div>
								</div></td>
							<td><div class="page-index">
									使用页： <select>
										<option value="1">第1页</option>
										<option value="2">第2页</option>
										<option value="3">第3页</option>
										<option value="4">第4页</option>
										<option value="i">自定义</option>
										<option value="i2">属性设置</option>
									</select>
									<div class="append">
										<input name="page_index" value="1" type="hidden"
											class="short_txt" />
									</div>
								</div></td>
						</tr>
					</tbody>
				</table>
			</div>
			<div id="context_config" class="opbox">
				<table class="tpage">
					<thead>
						<tr>
							<th colspan="4">内容编辑设置</th>
						</tr>
						<tr>
							<td>字段</td>
							<td>表单</td>
							<td>值设置</td>
							<td style="width: 50px;"></td>
						</tr>
					</thead>
					<tbody></tbody>
					<tfoot>
						<tr>
							<td colspan="4"><a class="new_set">+添加</a><label><input
									name="edit_limit" type="checkbox" value="1" />只能编辑设定的字段</label></td>
						</tr>
					</tfoot>
				</table>
			</div>
			<div class="endbts">
				&nbsp; &nbsp;
				<button type="button" id="next_step_btn" class="submit_btn">下一步</button>
				&nbsp; &nbsp;
				<button type="button" onclick="window.history.go(-1)">返回</button>
			</div>
		</div>
		<div id="block_pre_view" class="hide">
			<dl>
				<dt>模块属性</dt>
				<dd></dd>
			</dl>
			<dl>
				<dt>
					<div class="endbts">
					<input type="hidden" name="model_identify" value="/*?echo $this->model_info['content_mark']?*/" />
					<input type="hidden" name="select_where" />
					<input type="hidden" name="select_order" /> <input type="hidden" name="context_edit" />
						<input type="hidden" name="select_arg" />
						
						<button type="button" class="previous_btn">上一步</button>
						&nbsp; &nbsp;
						<button type="submit" class="submit_btn"
							onclick="editv.open_url('{APP_PATH}block/save_prototype/',null,this.form);return false;">提交</button>
						&nbsp; &nbsp;
						<button type="button" onclick="window.history.go(-1)">返回</button>
					</div>
				</dt>
			</dl>
		</div>
	</form>

	<script type="text/javascript">
var model_info=/*?echo array_to_json($this->model_info)?*/;
var _form=/*?echo array_to_json($this->model_form)?*/;
var model_form=_form.record;
var editfrom_model="w3capp";
var default_columns={data:/*?unset($this->edit_columns['select_arg']);echo str_replace("script","\"+\"script\"+\"",array_to_json($this->edit_columns))?*/};
default_columns.init=function(){
	var inum=0;
	for(var i in this.data){
		if(i=='id'){
			continue;
		}
		var arg=this.data[i];
		var col_name=arg["col_name"]?arg["col_name"]:'';
		$("#default_set tbody").append('<tr class="tri'+inum+'"><td><input is_append="" title="系统默认不可修改" readonly class="short_txt attr_id" value="'+i+
				'"/></td><td><div class="argname"><input class="short_txt attr_name" value="'+col_name+
			'"/></div></td><td><select class="form_input"><option value="default|'+arg['form_input']+'">默认</option>'+
			form_input.arrayToOptions(form_input.input_type,'')+
			'</select></td><td class="set_val"></td><td></td></tr>');
   		$("#default_set .tri"+inum+" select").change(form_input.c_type_fun(arg,$("#default_set .tri"+inum+" .set_val")));
   		inum++;
	}
	$("#default_set .new_set").click(function(){
    	var i=$("#default_set tbody tr").length;
    	if(i>0){
    		i=$("#default_set tbody tr")[i-1].className.replace("tri","")*1+1;
    	}
    	$("#default_set tbody").append('<tr class="tri'+i+'"><td><input is_append="1" class="short_txt attr_id" value="arg_'+i+
				'"/></td><td><div class="argname"><input class="short_txt attr_name" value="arg_'+i+
				'"/></div></td><td><select class="form_input">'+
				form_input.arrayToOptions(form_input.input_type,'')+
				'</select></td><td class="set_val"></td><td><a class="btn-del">删除</a></td></tr>');
       var tf=form_input.c_type_fun({},$("#default_set .tri"+i+" .set_val"));
       tf();
       $("#default_set .tri"+i+" select").change(tf);
       $("#default_set .tri"+i+" .btn-del").click(function(){$("#default_set .tri"+i)[0].remove();});
    });
	$("#block_pre_view")[0].show_pre_view=function(where,order){
		var form={};
		var select_arg_keys="";
		
		$("#default_set tbody tr").each(function(ii,tr){
			var input_list=$(tr).find("textarea[name=value_list]").val();
			var input_list_v=form_input.listParse(input_list);
			var attr_id=$(tr).find(".attr_id").val();
			form[attr_id]={};
			if(select_arg_keys==""){
				select_arg_keys=attr_id;
			}else{
				select_arg_keys+=','+attr_id;
			}
			var input_type=$(tr).find("select.form_input").val();
			form[attr_id]['is_append']=$(tr).find(".attr_id").attr("is_append");
			if(/^default\|/.test(input_type)){
				form[attr_id]=default_columns.data[attr_id];
				form[attr_id].form_default="1";
				if(!form[attr_id].def_value){
					form[attr_id].def_value="";
				}
			}else{
				attr_id=default_columns.data[attr_id]?attr_id:"_"+attr_id;
				form[attr_id]={"def_value":$(tr).find("input[name=def_value]").val(),"form_input":input_type,"col_name":$(tr).find("input.attr_name").val()};
				if(input_list_v)
					form[attr_id]['value_list']=input_list_v;
			}
			
		});
		if($("input[name=where_option]")[0].checked){
			var glist={};
			for(var gi=0;gi<where['group'].length;gi++){
				glist['g'+gi]=where['group'][gi];
			}
			glist['']="无";
            form['where_edit_arg']={"col_name":"数据条件","form_input":"radio","value_list":glist,"def_value":''};
		}
		if($("input[name=order_option]")[0].checked){
			var glist={};
			for(var gi=0;gi<order['group'].length;gi++){
				glist['g'+gi]=order['group'][gi];
			}
			glist['']="无";
			form['order_edit_arg']={"col_name":"数据排序","form_input":"radio","value_list":glist,"def_value":''};
		}
		if("i2"==$(".page-index select").val()){
			form['page_index']={"col_name":"第几页数据","form_input":"text","def_value":$(".page-index input").val()};
		}
		if("i2"==$(".page-size select").val()){
			form['page_size']={"col_name":"每页数据条数","form_input":"text","def_value":$(".page-size input").val()};
		}
		form_input.create_form(form,$("#block_pre_view dd"));
		$("input[name=select_arg]").val(JSON.stringify(form));
		$("#block_pre_view")[0].className="";
		$(".config_seting").hide();
	};
}
$(".page-size select").change(function(){
	if(this.value=='i'||this.value=='i2'){
		$(".page-size .append input")[0].type="text";
		$(".page-size .append input").val('?');
	}else{
		$(".page-size .append input")[0].type="hidden";
		$(".page-size .append input").val(this.value);
	}
});
$(".page-index select").change(function(){
	if(this.value=='i'||this.value=='i2'){
		$(".page-index .append input")[0].type="text";
		$(".page-index .append input").val('?');
	}else{
		$(".page-index .append input")[0].type="hidden";
		$(".page-index .append input").val(this.value);
	}
});
$(".previous_btn").click(function(){
	$(this).hide();
	$("#block_pre_view")[0].className="hide";
	$(".config_seting").show();
});
var col_format_change=function(i){
	var col_name=$("#column_list .tri"+i).find(".col_name");
	var col_format=$("#column_list .tri"+i).find(".col_format");
	$("#column_list .tri"+i+" .mid_txt").val(col_format.val().replace("?",col_name.val()));
}
</script>
<script src="{URL_ROOT}static/script/block_lib.js"></script>
<script src="{URL_ROOT}static/script/select_mod.js?v=f4ias"></script>
</div>
<!--include::common/footer-->