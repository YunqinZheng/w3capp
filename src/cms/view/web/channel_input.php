<!--include::common/header-->
<div class="pagetop"><?php echo $this->title?></div>
<div class="opbox">
        <form method="post" id="channel_form" class="left-field" enctype="multipart/form-data">
            <input type="hidden" name="sub_t" id="sub_t" />
            <div class="opbox infrom"><div class="form-inputs">
                    <div class="form-inputs">
                        <input name="content_id" value="7" type="hidden"/><input name="id" type="hidden" value="/*?echo $edit_data['id']?*/"/><div class="formline cdiycode"><div class="clearfix"><span class='labt'>上级栏目:</span><p class="inct"><select name='pid'><option value='0'>最上级</option>/*?echo self::arrayToOptions($chnn_tree,$edit_data['pid'])?*/</select></p></div></div>
                        <div class="formline ctext"><span class="labt">栏目名称:</span><p class="inct"><input type="text" name="ch_name" value="/*?echo $edit_data['ch_name']?*/"/></p></div>
                        <div class="formline ctext"><span class="labt">目录路径:</span><p class="inct"><input type="text" name="path" value="/*?echo $edit_data['path']?*/"/></p></div>
                        <div class="formline ctext"><span class="labt">跳转链接:</span><p class="inct"><input type="text" placeholder="http://......" name="static_path" value="/*?echo $edit_data['static_path']?*/"/></p></div>
                        <div class="formline ctext"><span class="labt">导航设置:</span><p class="inct"><select name="innav" iv="/*?echo $edit_data['innav']?*/"><option value="0">没有</option><option value="1">导航项</option><option value="2">导航分类</option></select></p></div>
                        <div class="formline cdiycode"><div><span class="labt">排序:</span><p class="inct"><span><input name="order_val" class="mini_txt" value="/*?echo $edit_data['order_val']?*/"/></span>
                                    <label><input type="checkbox" name="hidden" /*?echo $edit_data['hidden']==1?' checked="true"':''?*/  value="1"/>隐藏</label>
                                    <label><input type="checkbox" name="be_publish" /*?echo $edit_data['be_publish']==1?' checked="true"':''?*/ checked="true" value="1"/>可发布内容</label>
                                </p></div></div>
                        <div class="formline cfile"><span class="labt">封面图:</span><p class="inct"><input name="pic" type="file" /><span></span></p><input name="file_v_pic" type="hidden" value="/*?echo $edit_data['pic']?*/"/>
                            <div class="view_img left-offset"></div></div>
                        <div class="formline cselect"><span class="labt">内容模型:</span><p class="inct"><select name="frame_mod"><option value="">无</option><!--?echo self::arrayToOptions($content_types,$edit_data['frame_mod'])?--></select></p></div>
                        <div class="formline cselect" id="list2tpl"><span class="labt">列表模板:</span><p class="inct"><select name="list_tpl"><!--?echo self::arrayToOptions($tpl_files[0],$edit_data['list_tpl'])?--></select><a ec="edit_list_tpl" class="edt">编辑</a></p></div>
                        <div class="formline cselect" id="view2tpl"><span class="labt">内容模板:</span><p class="inct"><select name="view_tpl"><!--?echo self::arrayToOptions($tpl_files[1],$edit_data['view_tpl'])?--></select><a ec="edit_view_tpl" class="edt">编辑</a></p></div>
                        <div class="formline ctext"><span class="labt">关键字:</span><p class="inct"><input type="text" name="keywords" value="/*?echo $edit_data['keywords']?*/"/></p></div>
                        <div class="formline ctextarea"><span class="labt">描述:</span><p class="inct"><textarea name="description" >/*?echo $edit_data['description']?*/</textarea></p></div>
            </div>
            <div class="cl"><div class="inct">
                <button type="button" onclick="window.location.href='{APP_PATH}channel'">返回</button>
                <button type="submit">提交</button>
                </div>
            </div>
            </div>
            </div>
        </form>
    <form id="tpl_form" class="left-field-off">
        <div class="opbox">
            <h5>编辑模板：<span></span></h5>
            <textarea name="code"></textarea>
            <input type="hidden" name="file" value="" />
            <div class="end_button"><button type="button">保存模板</button></div>
        </div>
    </form>
</div>
<script type="text/javascript">
request_js(["py","form"],function(){
	$("input[name=ch_name]").blur(function(){
		if($("input[name=path]").val()==""){
			$("input[name=path]").val(pinyin.getFullChars(this.value));
		}
	});
	if($("input[name=file_v_pic").val()){
	    $(".view_img").html('<img src="'+$("input[name=file_v_pic").val()+'" />');
    }
    var fh=form_input.Hold("#channel_form");
    fh.ec("a.edt",{"edit_list_tpl":function(et){
            this.edit_tpl($("#channel_form select[name=list_tpl]").val());
        },"edit_view_tpl":function(et){
            this.edit_tpl($("#channel_form select[name=view_tpl]").val());
        },"edit_tpl":function(file){
            $.post(APP_PATH+'channel/tpl_code',{"file":file},function(rs){
                if(rs.error){
                    msg_box.create_short(rs.message,"error");
                }else{
                    $("#tpl_form input[name=file]").val(file);
                    $("#tpl_form").show();
                    $("#tpl_form h5 span").html(rs.data.file);
                    $("#tpl_form textarea").val(rs.data.code);
                }
            });
        }});
	if($("#channel_form select[name=list_tpl]").val()){
	    $("#list2tpl .edt").css({"visibility":"visible"});
    }
    if($("#channel_form select[name=view_tpl]").val()){
        $("#view2tpl .edt").css({"visibility":"visible"});
    }
    $("#tpl_form button").click(function(){
        var data={"file":$("#tpl_form input[name=file]").val(),"code":$("#tpl_form textarea[name=code]").val()};
        $.post(APP_PATH+'channel/save_tpl',data,function(rs){
            if(rs.error){
                msg_box.create_short("无法保存，请确认文件或目录有写入权限","error");
            }else{
                msg_box.create_short("保存成功！","right");
            }
        });
    });
},'append');

</script>
<!--include::common/footer-->