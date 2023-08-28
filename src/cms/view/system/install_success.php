<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>W3CApp installer</title>
    <script type="text/javascript" src="{URL_ROOT}static/script/jquery-3.3.1.min.js"></script>
    <link rel="stylesheet" href="{URL_ROOT}static/style/icomoon/style.css" type="text/css" />
    <script>var APP_PATH="{APP_PATH}".replace("install.php","");var BASE_PATH="{URL_ROOT}";</script>
    <style>
        .setting-field span{display: inline-block;width: 105px;text-align: right;}
        form{margin-top: 30px;}
        .setting-field{margin-top: 10px;line-height: 20px;color:#666; font-size: 14px;}
        .setting-field p{margin: 0px;font-size: 12px;line-height: 14px;}
        .setting-field input[type=text]{height: 20px;width: 220px;border-radius: 5px;border:1px solid #ddd;padding: 2px;}
        .container{width: 460px;margin: 100px auto 0px auto; text-align: center;}
        .form-footer{text-align: center;margin-top: 25px;line-height: 30px;}
        .form-footer button{border-radius: 5px;border:1px solid #ddd;background-color: #0f74a8;color: #fff;
            height: 30px;width: 120px;
        }
        .link{line-height: 30px;margin-top: 60px;}
        .link a{color:#0f74a8;padding: 0px 10px;text-decoration: none;}
    </style>
</head>
<body>
<div class="container">

    <p style="margin-top: 60px;"><b>Install success!</b><br/>安装完成!</p>
    <p class="link">
        <a href="#" onclick="this.href=APP_PATH+'web';" target="_blank">&lt;--进入前台首页/Frontend</a>
        <a href="#" onclick="this.href=APP_PATH+'main/login';" target="_blank">后台管理/Backend--&gt;</a>
    </p>
</div>
</body>
</html>