<!--include::common/header-->
<div class="pagetop">文件编辑</div>
<form method="post" id="mainform" action="{APP_PATH}cms/explorer/save">
	<div class="opbox">
	<div><p>文件内容：</p>
	<textarea name="content" style="width: 600px;height: 200px;">/*?echo $content?*/</textarea>
	</div>
	<div><span>存储为：</span>
	<input type="text" class="long_txt" name="save_as" value="/*?echo $file?*/">
	</div>
	<div class="left-offset"><button type="button" onclick="window.history.go(-1)">返回</button> <button>提交</button></div>
	</div>
</form>
<!--include::common/footer-->