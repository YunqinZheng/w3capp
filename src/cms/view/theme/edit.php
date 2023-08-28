<form class="opbox" method="post" action="{APP_PATH}site/themeEdit//*?echo $theme['id']?*/">
    <div class="">
        <label>主题名称</label>
        <div><input name="name" class="long_txt" type="text" value="/*?echo $theme['name']?*/"></div>
    </div>
    <div class="">
        <label>主题封面</label>
        <div><input name="image" class="long_txt" type="text" value="/*?echo $theme['image']?*/"></div>
    </div>
    <div class="">
        <label>语言文件</label>
        <div><input name="language" class="long_txt" type="text" value="/*?echo $theme['language']?*/" /></div>
    </div>
    <div class="end_button">
        <button type="submit">提交</button>
    </div>
</form>