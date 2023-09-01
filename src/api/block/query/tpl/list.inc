<ul><?php foreach($data as $val){ ?>
        ?{$item_edit_mark}
	<li><a href="<?php echo $val['url']?>" target="_blank"><?php echo $val['title'] ?></a></li>
	<?php } ?>
</ul>