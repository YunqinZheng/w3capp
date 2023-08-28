<?php use w3c\helper\Str;?>
<form id="form_e" method="post" action="{APP_PATH}system/<?php echo $this->role['id']?'role_edit/'.$this->role['id']:'role_add';?>">
	<div class="opbox"><table><tbody>
		<tr><th class="short_txt">*名称：</th><td><input class="long_txt" required name="role_name" value="<?php echo $this->role['role_name']?>"/></td></tr>
		<tr><th>备注：</th><td><input name="note" value="<?php echo $this->role['note']?>" class="long_txt" /></td></tr>
		<tr><td colspan="2"><div class="left-offset opbox">
			<button type="submit">提交</button>
                </div></td></tr>
	</tbody></table></div>
</form>
