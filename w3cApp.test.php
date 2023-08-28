<?php

ini_set('date.timezone',$install_cf['timezone']);//PRC
define('W3CA_OPEN_DEBUG',true);

//前台模版目录
define('W3CA_THEME_TPL',W3CA_PATH."TPL/");
//是否为post请求
define("W3CA_IS_POST", strtolower($_SERVER['REQUEST_METHOD'])=="post");
//当前时间
define("W3CA_UTC_TIME",time());

//随机数a-z|0-9,用于生成临时数据,程序按装后不要修改'JIT2G3F4'
define("W3CA_YUN_DAT",$install_cf['random_key']);
//url访问目录，除去url中http://+dns分部；默认'/'
define("W3CA_URL_ROOT",$install_cf['app_dir']);
//字符集
define("W3CA_DB_CHAR_SET", "utf8");
//伪静太是否开启
define("W3CA_REWRITE_URL", $install_cf['open_rewrite']);
//默认的控制器
define("W3CA_DEF_CTRL", "\\cms\\controller\\webCtrl");
//define("W3CA_DEF_DB", $install_cf['db_default']);

require_once W3CA_MASTER_PATH.'core/W3cCore.php';
require_once W3CA_MASTER_PATH.'core/W3cApp.php';
require W3CA_MASTER_PATH."ctrl.rewrite.php";
spl_autoload_register("W3cApp::loadClass");