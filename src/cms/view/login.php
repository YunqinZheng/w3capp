<!--include::common/header-->

<style>
    .login{
        width: 400px; height: 220px; margin-left:auto; margin-right: auto;
        margin-top:130px;
    }
    .login .topt{
        color: #FFF;
        font-size: 20px;
        height: 80px;
        background-color:#24669B;
    }
    .login .iuser{
        height: 30px;
        line-height: 30px;
        background-color:#dbdbdb;
    }
    .login .iuser input{
        width: 180px;
        height: 26px;
        border: none;
        border-radius: 5px;
        padding-left: 10px;
    }
    .login .sp{
        margin-top:25px;
    }
    .login .iuser td,.login .ipwd td{
        width: 270px;
    }
    .login .iuser th,.login .ipwd th{
        text-align: right;
    }
    .login .btn{
        font-size: 16px;
        border:1px #b1cbd8 solid;
        color:#696969;
        cursor: pointer;
    }
    .login td{text-align: center;}
    .iuser div{padding: 5px 0px;}
    .iuser div:first-child{padding-top:15px;}
</style>
   <form id="login" method="post" action="{APP_PATH}main/login">
		<table class="login">
			<tr><td class="topt">系统登录</td></tr>
			<tr class="iuser">
				<td><div><input name="name" placeholder="用户" type="text" value=""/></div>
                <div><input name="pwd" type="password" placeholder="密码" /></div>
                    <div><button type="submit" class="btn">登录</button></div>
                </td>
			</tr>
		</table>
   </form>
<script type="text/javascript">
    if(self.frameElement && self.frameElement.tagName=="IFRAME"){
        window.parent.location.reload();
    }
    $("#login").append('<input type="hidden" name="hash_code" value="?{$hash_code}"/>');
</script>
<!--include::common/footer-->
