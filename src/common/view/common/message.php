<!Doctype html><html><head><meta http-equiv="Content-Type" content="text/html; charset={CHAR_SET}"><title>?{$this->title}</title><meta name="keywords" content="?{$this->keyword}"/><meta name="description" content="?{$this->description}"/><link rel="stylesheet" href="{URL_ROOT}static/style/main_ui.css" type="text/css" />
<script type="text/javascript">var APP_PATH="{APP_PATH}";var BASE_PATH="{URL_ROOT}";</script>
<script type="text/javascript" src="{URL_ROOT}static/script/jquery-3.3.1.min.js"></script>
<script id="w3capp_scr" src="{URL_ROOT}static/script/com.js" type="text/javascript"></script>
    <link rel="stylesheet" href="{URL_ROOT}static/style/icomoon/style.css" type="text/css" /></head><body>
<div class="mess_tisp">
<div class="t ?{$class_type}"><span class="icon"></span>?{$message}</div>
<div class="links"><?php 
	$time_out_link;
	foreach ($this->link_list as $li=>$value) {
		if($out_index==$li)
			$time_out_link=$value;
		$tag=empty($value['target'])?'':('target="'.$value['target'].'"');
		echo '<a href="'.$value["href"].'" '.$tag.'>'.$value['text'].'</a>';
	}
	?></div>
</div>
/*?if($out_index>-1&&$time_out_link){?*/
<script type="text/javascript">
	setTimeout(function(){
		window.location.href="?{$time_out_link['href']}";
	},4000);
</script>
/*?}?*/
</body></html>
