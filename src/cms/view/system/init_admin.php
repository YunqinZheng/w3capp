<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>W3CApp installer</title>
    <script type="text/javascript" src="{URL_ROOT}static/script/jquery-3.3.1.min.js"></script>
    <link rel="stylesheet" href="{URL_ROOT}static/style/icomoon/style.css" type="text/css" />
    <script>var APP_PATH="{APP_PATH}";var BASE_PATH="{URL_ROOT}";</script>
    <style>
        .setting-field span{display: inline-block;width: 105px;text-align: right;}
        form{margin-top: 30px;}
        .setting-field{margin-top: 10px;line-height: 20px;color:#666; font-size: 14px;}
        .setting-field p{margin: 0px;font-size: 12px;line-height: 14px;}
        .setting-field input[type=text],.setting-field input[type=password]{height: 20px;width: 220px;border-radius: 5px;border:1px solid #ddd;padding: 2px;}
        .container{width: 360px;margin: 100px auto 0px auto;}
        .form-footer{text-align: center;margin-top: 25px;line-height: 30px;}
        .form-footer button{border-radius: 5px;border:1px solid #ddd;background-color: #0f74a8;color: #fff;
            height: 30px;width: 120px;
        }
    </style>
</head>
<body>
<div class="container">
    <b>Administrator user to W3CApp!</b>
    <p style="margin-left: 60px;">W3CApp 设置后台管理员!</p>
    <form action="{APP_PATH}?{$ctr_name}/init_admin" method="post">
        <input name="old_name" value="<?php echo $old_name?>" type="hidden">
        <div class="setting-field">
            <p>Administrator account name</p>
            <label><span>管理员名称：</span></label><input type="text" required name="username"/></div>
        <div class="setting-field">
            <p>Account password</p>
            <label><span>密码：</span></label><input id="pwd" class="pwd" type="password" required placeholder="密码" name="password"/></div>
        <div class="setting-field">
            <p>Verification password</p>
            <label><span>确认密码：</span></label><input id="vpwd" class="pwd" type="password" required placeholder="密码" name="password2"/></div>
        <div class="form-footer">
            <button type="submit" onclick="if($('#pwd').val()!=$('#vpwd').val()){$('.pwd').css({borderColor:'red'});return false;}return true;">完成/Finish</button>
        </div>
    </form>
</div>
<script>
    $('.pwd').change(function(){
        $(this).css({borderColor:'#ddd'});
    });
</script>
</body>
</html>