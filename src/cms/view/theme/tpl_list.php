<!--include::common/header-->
<style>
    #theme_files{width: 260px;float: left;}
    #edit_tpl_area{margin-left: 280px;padding-left: 10px;min-width: 500px;}
    #theme_files .dir-item{line-height: 35px;border-top: 1px solid #ddd;margin-top: 10px;}
    #theme_files li.file-item>div:hover{background: #c4e5ff;}
    #theme_files .file-item{margin-left: 8px;padding-left: 6px;}
    #theme_files .file-item a.noview{display: none;}
    #theme_files li.file-item>div:hover a.noview{display: inline-block;margin-left: 10px;float: right;}
    #edit_tpl{display: none;}
    .tpl-ct textarea{height: 500px;width: 850px;}
    .tab-bar{margin-top:10px;line-height: 30px;border-bottom: 1px solid #aaa;height: 31px;}
    .tab-bar .tab-item{display: inline-block;width: 150px;text-align: center;overflow: hidden;white-space: nowrap;position: relative; cursor: default;}
    .tab-bar .tab_on{border-bottom: 1px solid #aaa;}
    .tab-bar .tab-item a{color: red;line-height: 16px;width: 12px;position: absolute;right: 0px;text-align: center;text-decoration: none;}
</style>
<div class="pagetop"><a href="{APP_PATH}/theme">模板主题</a><span>?{$theme_dir}-模板文件</span></div>
<div id="edit_tpl">
    <div class="opbox">
    <div class="tpl-ct">
        <textarea></textarea>
    </div>
    <div class="end_button">
        <button type="button" class="finish">保存</button>
    </div>
    </div>
</div>
<form id="theme_files" class="opbox">
    <div class="hide"></div>
    <input type="hidden" name="theme_dir" value="?{$theme_dir}"/>
    <input type="hidden" name="to_dir" value=""/>
    <ul>
        <!--?foreach($all_files as $dir=>$files){?-->
        <li class="dir-item"><div class="Rft"><a href="javascript:;" ec="upload" title="上传文件到此目录" to_dir="?{$dir}"><span class="icon-folder-upload"></span>上传</a></div><span class="icon-folder-open"></span>?{$dir}</li>
        <li class="file-item" in_dir="?{$dir}">
            <!--?foreach($files as $f){?-->
            <div>
            <a href="javascript:;" ec="edit" title="点击修改" to_file="?{$f}"><span>?{str_replace($dir.'/','',$f)}</span></a>
            <a href="javascript:;" ec="del" class="noview" title="删除" to_file="?{$f}"><span class="icon-uniE611"></span>删除</a>
            </div>
            <!--?}?-->
        </li>
        <!--?}?-->
    </ul>
</form>
<div id="edit_tpl_area">
    <div class="tab-bar"><span v-for="(t,i) in tabs" @click="view_tab=i" :title="t.file" :class="{'tab-item':1,'tab_on':i==view_tab}">{{t.file}}<a class="close-tab" @click="closeT(i)">-</a></span></div>
    <div class="edit_text">
        <div class="opbox" v-for="(t,i) in tabs" :style="{display:i==view_tab?'block':'none'}">
            <div class="tpl-ct">
                <textarea v-model="t.text"></textarea>
            </div>
            <div class="end_button">
                <button type="button" class="cancel" @click="closeT(i)">关闭</button>
                <button type="button" class="finish" @click="saveText(i)">保存</button>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="{URL_ROOT}static/script/vue.js"></script>
<script type="text/javascript" src="{URL_ROOT}static/script/cms/theme_tpl_list.js"></script>
<!--include::common/footer-->