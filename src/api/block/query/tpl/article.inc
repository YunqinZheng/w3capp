<ul class="cl"><?php foreach($data as $val){ ?>
	<li>?{$item_edit_mark}<div class="date"><a href="{APP_PATH}Web/CompanyNews/<?php echo $val['id']?>" target="_blank"><b><?php echo date('d',$val['dateline']) ?></b>
		<p><?php echo date('Y/m',$val['dateline']) ?></p></a>
	</div>
	<div class="newstext">
		<h4><a href="{APP_PATH}Web/CompanyNews/<?php echo $val['id']?>" target="_blank"><?php echo $val['title'] ?></a></h4>
		<p><?php echo $val['description'] ?></p>
	</div>
	</li>
	<?php } ?>
</ul>