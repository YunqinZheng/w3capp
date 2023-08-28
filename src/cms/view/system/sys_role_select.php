<style>#r_checkboxes{padding:10px 5px;margin:10px;}</style>
<div class="pagetop">选择角色</div>
<form id="rpage" method="get" target="role_select" action="<?php echo $this->action_url;?>">
	<input name="page" type="hidden" id="pagei"/>
	<input name="selected" id="selected" type="hidden" value="<?php echo $_POST['selected']?>"/>
	<button type="button" onclick="return_v()">确定</button>
	<button type="button" onclick="window.close()">取消</button>
	<button type="button" onclick="clear_selected()">清除</button>
	<div id="select_v"><?php echo $_POST['selectedn']?"已选择：".$_POST['selectedn']:""?></div>
	<input name="selectedn" id="selectedn" type="hidden" value="<?php echo $_POST['selectedn']?>" />
</form>
	<div id="r_checkboxes">
		<?php
		foreach ($this->roles["data"] as $role) {
			echo '<dt><label><input name="role" tname="'.$role['role_name'].'" type="checkbox" value="'.$role["id"].'"/>'.
			$role['role_name']."</label></dt>";
		}
		echo "<dt class=\"clear\">";
		SimpleHtml_v::mpage_buttons($this->roles);
		echo "</dt>"
		?>
	</div><script>
var wargs=window.dialogArguments;
var selected_r=<?php if($_POST['selected']){?>{values:"<?php echo $_POST['selected'];?>",
names:"<?php echo $_POST['selectedn']?>"}<?php
 }else{ ?>{names:wargs.names,values:wargs.values}<?php }?>;
window.name="role_select";
if(EId("selected").value==""&&selected_r.values!=""){
	EId("selected").value=selected_r.values;
	EId("selectedn").value=selected_r.names;
	EId("select_v").innerHTML="已选择："+selected_r.names;
}
function pageGoTo(pi){
	EId("pagei").value=pi;
	EId("rpage").submit();
}
function return_v(){
	window.returnValue=selected_r;
	window.close();
}
function clear_selected(){
	EId("selected").value="";
	EId("selectedn").value="";
	selected_r.values="";
	selected_r.names="";
	EId("select_v").innerHTML="";
	for(var i=0;i<boxes.length;i++){
		boxes[i].checked=false;
	}
}
function box_click(){
	return function(){
		var sv=","+selected_r.values+",";
		if(this.checked&&!sv.match(new RegExp(","+this.value+","))){
		selected_r.values+=selected_r.values==""?this.value:","+this.value;
		var name=this.getAttribute("tname");
		EId("select_v").innerHTML+=selected_r.names==""?"已选择："+name:","+name;
		selected_r.names+=selected_r.names==""?name:","+name;
		}
	};
}
var boxes=EId("r_checkboxes").getElementsByTagName("input");
for(var i=0;i<boxes.length;i++){
	boxes[i].onclick=box_click();
}
</script>