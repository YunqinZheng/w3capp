<style>
    th.lb{width: 100px;text-align: right}
</style>
<form action="{APP_PATH}system/save_menu_item/" method="post">
<table class="formt">
		<?php if($_GET['name']) {?> <tr><th class="lb">上级</th><td><?php echo $_GET['name'];?></td></tr><?php }?>
		<?php if($edit_node){
			$node=$edit_node;
			echo '<tr><th class="lb">*上级</th><td><select name="pid" class="long_txt">';
			foreach($tree_m as $item){
				if($node['id']!=$item['id']&&$item['deep']<=1)
				echo '<option value="'.$item['id'].'" '.($node['pid']==$item['id']?'selected':'').'>'.str_repeat("_", $item['deep']*2).$item['name'].'</option>';
			}
			echo '</select><input id="edid" name="edid" type="hidden" value="'.$node['id'].'"/></td></tr>';
		}else{ ?>
			<input type="hidden" name="pid" value="<?php echo $this->parent_id;?>" />
			<input id="edid"  type="hidden" value="0"/>
		<?php }?>
		<tr><th class="lb">*名称</th><td><input type="text" name="n_name" class="long_txt" value="<?php echo $node['name']?>" /></td></tr>
		<tr><th class="lb">链接</th><td><input type="text" name="url" class="long_txt" value="<?php echo $node['url']?>"/></td></tr>
		<tr><th class="lb">排序</th><td><input type="text" name="orderid" class="mini_txt" value="<?php echo $node['orderid']?>" /></td></tr>
    <tr><th class="lb"> </th><td><div class="ctpd"><button type="submit">提交</button></div></td></tr>
</table>

</form>