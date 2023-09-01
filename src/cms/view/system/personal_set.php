<?php use w3capp\helper\Str;?>
<form method="post" action="{APP_PATH}system//*?echo $this->form_action?*/">
	<div>
/*?$editdata=$this->edit_data?$this->edit_data:array (
  'uid' => '',
  'name' => '',
  'pwd' => '',
  'email' => '',
  'tel' => '',
  'roles' => 
  array (
    0 => '',
  ),
);?*/<input name="content_id" value="/*?echo $editdata['id'];?*/" type="hidden"/><div class="formline chidden"><input name="uid" type="hidden" value="/*?echo Str::htmlchars($editdata['id']);?*/"/></div>
<div class="formline ctext"><span class="labt">用户名:</span><p class="inct"><input type="text" readonly="readonly" name="name" value="/*?echo Str::htmlchars($editdata['name']);?*/"/></p></div>
<div class="formline cpassword"><span class="labt">密码:</span><p class="inct"><input type="password" name="pwd" value="" placeholder="为空不修改" /></p></div>
<div class="formline ctext"><span class="labt">电子邮箱:</span><p class="inct"><input type="text" name="email" value="/*?echo Str::htmlchars($editdata['email']);?*/"/></p></div>
<div class="formline ctext"><span class="labt">电话/手机:</span><p class="inct"><input type="text" name="tel" value="/*?echo Str::htmlchars($editdata['tel']);?*/"/></p></div>

	<div class="formline"><span class="labt">&nbsp;</span><button type="submit">提交</button></div>

	</div>
</form>