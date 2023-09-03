<?php

define('W3CA_MASTER_PATH',__DIR__."/../wmaster/");
require_once 'w3capp.php';

$install_cf=new \w3capp\InstallConfig();
ini_set('date.timezone',$install_cf['timezone']);//PRC

error_reporting($install_cf['error_level']);

//url访问目录，除去url中http://+dns分部；默认'/'
//define("W3CA_URL_ROOT",$install_cf['app_dir']);
//字符集
define("W3CA_DB_CHAR_SET", "utf8");

class Web extends \w3capp\W3cAppCom{
	/**
	 * 默认的控制器
	 */
	protected function defaultCtrl(){
		return "\\w3capp\\Controller";
	}
	protected function initConfig(){
		global $install_cf;
		return $install_cf;
	}
}
(new Web())->start();