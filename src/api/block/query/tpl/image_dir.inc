<ul>
	<?php foreach($data as $val){ ?>
	<li>?{$item_edit_mark}<a href="{APP_PATH}Web/photos/<?php echo $val['id']?>" target="_blank"><?php echo $val['title'] ?></a></li>
	<?php } ?>
</ul>