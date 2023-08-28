<!--include::common/header-->
<style>
    .inline{margin-bottom:10px;}
    .inline .lab{width: 150px; display: inline-block;text-align: right}
    .ctrl-set{display:none;}
</style>
<div class="pagetop">代码生成</div>
<div class="opbox">
    <form method="post" action="{APP_PATH}coder/file" id="writer_form">
        <input name="table_pre" type="hidden" value="?{$table_pre}"/>
        <div class="left-offset">
            <div class="inline">
                <label class="lab">表名:</label>
                <input name="table" id="table_name" type="text" placeholder="数据库表名"/>
            </div>
            <div class="inline">
                <label class="lab">类型:</label>
                <label><input name="file_type" type="radio" value="model" checked/>Record代码</label>
                <label><input name="file_type" type="radio" value="controller"/>Controller代码</label>
                <label><input name="file_type" type="radio" value="form"/>表单代码</label>
                <label><input name="file_type" type="radio" value="list"/>列表代码</label>
            </div>
            <div class="inline">
            <label class="lab">空间目录:</label>
            <select name="app_dir" title="master/app/的子文件夹">
                <!--{loop $app_dirs($dir)}-->
                <option value="?{$dir}">?{$dir}</option>
                <!--{/loop}-->
            </select>
            </div>
            <div class="inline">
                <label class="lab">文件名（类名）:</label>
                <label><input name="class_name" type="text"/></label>
            </div>
            <div class="ctrl-set">
                <div class="inline"><input name="record_name" type="hidden"/>
                <label class="lab">方法:</label>
                <label><input name="action_name[]" type="checkbox" checked value="add"/>添加</label>
                <label><input name="action_name[]" type="checkbox" checked value="edit"/>编辑</label>
                <label><input name="action_name[]" type="checkbox" checked value="delete"/>删除</label>
                <label><input name="action_name[]" type="checkbox" checked value="index"/>显示列表</label>
                </div>
                <div class="inline">
                    <label class="lab">返方式:</label>
                    <label><input name="return_type" checked type="radio" value="html"/>html</label>
                    <label><input name="return_type" type="radio" value="json"/>json</label>
                </div>
            </div>
            <div class="form-set"></div>
            <div class="list-set"></div>
            <div class="left-offset"><button type="submit">生成文件</button>
            </div>
        </div>
    </form>
</div>
<script src="{URL_ROOT}static/script/code_writer.js"></script>
<!--include::common/footer-->