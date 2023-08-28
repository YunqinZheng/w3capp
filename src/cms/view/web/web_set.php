<!--include::common/header-->
	
		<div class="pagetop">网站设置</div>
		<div class="opbox">
			<form id="setform" method="post" enctype="multipart/form-data"  class="infrom left-field" action="<?php echo $this->action_url;?>">
		<div class="formline"><div class="clearfix"><span class="labt">网站名称:</span><p class="inct">
                    <textarea name="web_name" class="long_txt" style="height: 40px;min-height:auto;">/*?echo $set_data['web_name']?*/</textarea></p></div>
		</div>
		<div class="formline"><div class="clearfix"><span class="labt">seo关键字:</span><p class="inct">
			<textarea name="web_keywords" class="long_txt" >/*?echo $set_data['web_keywords']?*/</textarea></p></div>
		</div>
		<div class="formline"><div class="clearfix"><span class="labt">seo描述:</span><p class="inct">
			<textarea name="description" class="long_txt"  style="height: 120px;">/*?echo $set_data['description']?*/</textarea></p></div>
		</div>
		<div class="formline"><div class="clearfix"><span class="labt">网站模板:</span><p class="inct">
			<select name="style">/*?echo self::arrayToOptions($themes,$set_data['style'])?*/</select><a class="e_tpl" ec="cp_index">编辑首页</a></p></div>
		</div>
		<div class="formline"><div class="clearfix"><span class="labt">手机模板:</span><p class="inct">
			<select name="style_mobile">/*?echo self::arrayToOptions($themes,$set_data['style_mobile'])?*/</select><a class="e_tpl" ec="mob_index">编辑首页</a></p></div>
		</div>
			<div class="left-offset"><button type="submit">保存</button>
                <a class="btn btn-default" href="{APP_PATH}cms/Theme">模板设置</a></div>
			</form>
            <form id="tpl_form" class="left-field-off">
                <div class="opbox">
                    <h5>编辑模板：<span></span></h5>
                    <textarea name="code"></textarea>
                    <input type="hidden" name="dir" value="" />
                    <div class="end_button"><button type="button">保存模板</button></div>
                </div>
            </form>
		</div>
<script type="text/javascript">
    request_js("form",function(){
        var fh=form_input.Hold("#setform");
        fh.ec("a.e_tpl",{"cp_index":function(et){
                var file=$("select[name=style]").val();
                this.edit_tpl(file);
            },
            "mob_index":function(et){
                var file=$("select[name=style_mobile]").val();
                this.edit_tpl(file);
            },"edit_tpl":function(dir){
            if(!dir)return;
            $.post(APP_PATH+'site/index_code',{"dir":dir},function(rs){
                if(rs.error){
                    msg_box.create_short(rs.message,"error");
                }else{
                    $("#tpl_form input[name=dir]").val(dir);
                    $("#tpl_form").show();
                    $("#tpl_form h5 span").html(rs.data.file);
                    $("#tpl_form textarea").val(rs.data.code);
                }
            });
        }});
        $("#tpl_form button").click(function(){
            var data={"dir":$("#tpl_form input[name=dir]").val(),"code":$("#tpl_form textarea[name=code]").val()};
            $.post(APP_PATH+'site/save_index',data,function(rs){
                if(rs.error){
                    msg_box.create_short("无法保存，请确认文件或目录有写入权限","error");
                }else{
                    msg_box.create_short("保存成功！","right");
                }
            });
        });
    });
</script>
<!--include::common/footer-->