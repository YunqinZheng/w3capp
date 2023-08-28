function role_select(){
	var rsi=EId("rolems");
	var rls=window.showModalDialog("<?php echo $win_url;?>",{values:rsi.value,names:EId("role_names").innerHTML},"resizable:yes;dialogWidth=400px;dialogHeight=200px");
	if(rls!=null){
		EId("role_names").innerHTML=rls.names;
		rsi.value=rls.values;
	}
}
document.write("<span id=\"role_names\"><?php echo $rns?></span><a href=../../../index.php>点击选择</a>");
