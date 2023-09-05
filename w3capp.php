<?php
if(!defined('W3CA_MASTER_PATH')){
    die("W3CA_MASTER_PATH not defined");
}
if(!defined('W3CA_OPEN_DEBUG'))
	define('W3CA_OPEN_DEBUG',true);

//前台模版目录
define('W3CA_THEME_TPL',W3CA_MASTER_PATH."TPL/");

//字符集
define("W3CA_DB_CHAR_SET", "utf8");

define("W3CAPP_LIB_DIR",__DIR__);
require_once W3CAPP_LIB_DIR."/core/W3cAppCom.php";
spl_autoload_register(function($classn){
	$class_list=["w3capp\\Controller"=>"/core/Controller",
		"w3capp\\Core"=>"/core/Core",
		"w3capp\\InstallConfig"=>"/core/InstallConfig",
		"w3capp\\Record"=>"/core/Record",
		"w3capp\\Template"=>"/core/Template",
		"w3capp\\TplParent"=>"/core/TplParent",
		"w3capp\\W3cAppAdapter"=>"/core/W3cAppAdapter",
		"w3capp\\UI"=>"/core/UI",
		"w3capp\\W3cAppDataApi"=>"/core/W3cAppDataApi",
		"w3capp\\W3cAppSession"=>"/core/W3cAppSession"];
	if(empty($class_list[$classn])){
		if(strpos($classn,'\\controller\\')){
			require_once W3CA_MASTER_PATH.'app/'.str_replace("\\controller\\",'/',$classn).".php";
		}else if(strpos($classn,'\\model\\')){
			include_once W3CA_MASTER_PATH.'app/'.str_replace("\\model\\",'/model/',$classn).".php";
		}else if(strpos($classn,'api\\block\\')===0){
			include_once W3CA_MASTER_PATH.'app/'.str_replace("\\",'/',$classn).".php";
		}else if(strpos($classn,'w3capp\\')===0){
			$file=W3CAPP_LIB_DIR.'/'.strtr($classn,["w3capp\\"=>"core/","\\"=>"/"]).".php";
			require_once $file;
		}
	}else{
		$file=W3CAPP_LIB_DIR.$class_list[$classn].".php";
		require_once $file;
	}
},true,true);
