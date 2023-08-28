<!--include::common/header-->
<style>
ul.themes{margin-left: 10px;}
.themes li{padding: 10px; float:left;}
.theme-name,.theme-name2{position: absolute;width: 140px;height: 30px;background-color: rgba(0,0,0,0.6);top:95px;left:-10px;line-height: 30px;color:#fff;overflow: hidden;text-align: center;}
.theme-name2{display: none;}
.themes input{width: 99%;}
.theme-img{position: relative;width: 130px;}
.theme-img:hover .theme-name2{display: block;}
.theme-img:hover .theme-name{display: none;}
.theme-img img{width: 120px;height: 120px;}
.theme-img .btns{width: 120px; float: left;margin-top:10px;}
.theme-img .active{position: absolute;color:#fff;background-color: #00CCFF;padding: 0px 10px;top: 0px;}
.theme-img .pc{left: 50%}
.theme-img .mb{left: 0px;}
</style>
<div class="pagetop">模板主题</div>
<div class="opbox">
	<form method="post" action="{APP_PATH}/theme">
		<ul class="themes cl">
            <!--?foreach($themes as $val){?-->
			<li><div class="theme-img"><img src="{URL_ROOT}/*?echo $val['image']?*/"/>
                    <!--?if($pc_style==$val['id']){?--><div class="active pc">电脑版</div><!--?}?-->
                    <!--?if($mb_style==$val['id']){?--><div class="active mb">手机版</div><!--?}?-->
                    <div class="theme-name">/*?echo $val['name']?*/</div>
                    <div class="theme-name2">/*?echo $val['id']?*/</div>
                <div class="btns"><!--?if($val['installed']){?-->
                    <a class="btn-edit" dir="/*?echo $val['id']?*/">编辑</a>
                    <a class="btn-clear" dir="/*?echo $val['id']?*/">清除缓存</a>
                    <a class="btn-tpl" href="{APP_PATH}theme/tpl_list//*?echo $val['id']?*/">模板文件</a>
                    <!--?if($val['id']!='default'){?--><a class="btn-delete" dir="/*?echo $val['id']?*/">御载</a><!--?}?-->
                    <!--?}else{?-->
                    <a class="btn-intall" dir="/*?echo $val['id']?*/">安装</a>
                    <!--?}?-->
                </div>
                </div></li>
            <!--?}?-->
		</ul>
	</form>
</div>
<script>
    request_js('block_lib',function(){
        editv.init();
        $(".btns a").click(function(){
            var dir=this.getAttribute("dir");
            if(this.className=="btn-intall"){
                msg_box.data_get(APP_PATH+"theme/install/"+dir,function(rs){
					window.location.reload();
                });
            }else if(this.className=="btn-edit"){
                editv.form_get(APP_PATH+"theme/edit"+dir);
            }else if(this.className=="btn-clear"){
                msg_box.data_get(APP_PATH+"theme/install/"+dir,function(rs){
                    if(rs.error==0){
                        $.get(APP_PATH+"cms/site/clear_cache",function(){
                            msg_box.create_short("缓存已经更新!");
                        });
                    }else if(rs.error==1){
                        msg_box.create_short(rs.message,'error');
                    }

                });
            }else if(this.className=="btn-tpl"){
                return true;
            }else if(this.className=="btn-delete"){
                msg_box.data_get(APP_PATH+"theme/uninstall/"+dir,function(rs){
					window.location.reload();
                });
            }
            return false;
        });
    });

</script>
<!--include::common/footer-->