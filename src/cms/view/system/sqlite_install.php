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
        .setting-field input[type=text]{height: 20px;width: 220px;border-radius: 5px;border:1px solid #ddd;padding: 2px;}
        .container{width: 360px;margin: 100px auto 0px auto;}
        .form-footer{text-align: center;margin-top: 25px;line-height: 30px;}
        .form-footer button{border-radius: 5px;border:1px solid #ddd;background-color: #0f74a8;color: #fff;
        height: 30px;width: 120px;
        }
    </style>
</head>
<body>
<div class="container">
<b>Welcome to W3CApp!</b>
<p style="margin-left: 60px;">W3CApp安装程序!</p>
<form action="{APP_PATH}sqlite_install/database" method="post">
    <div class="setting-field">
        <p>Database File</p>
        <label><span>数据库文件：</span></label><input type="text" required placeholder="数据库文件存储路径和文件名" name="db_file"/></div>

    <div class="setting-field">
        <p>TableName Prefix</p>
        <label><span>表前缀：</span></label><input type="text" value="w3ca_" name="db_table_pre"></div>
    <div class="setting-field">
        <p>Replace db file exist</p>
        <label><input type="checkbox" checked value="1" name="drop_table" />如数据库存在进行替换</label>
    </div>
    <div class="form-footer">
        <button type="button" onclick="this.disabled=true;this.form.submit();" >安装/Install</button>
    </div>
</form>
</div>
</body>
</html>