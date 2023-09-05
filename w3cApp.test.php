<?php

define('W3CA_MASTER_PATH',__DIR__."/../w3ctest/");
require_once 'w3capp.php';

$install_cf=new \w3capp\InstallConfig();
ini_set('date.timezone',$install_cf['timezone']);//PRC

error_reporting($install_cf['error_level']);

//url访问目录，除去url中http://+dns分部；默认'/'
//define("W3CA_URL_ROOT",$install_cf['app_dir']);
//字符集
define("W3CA_DB_CHAR_SET", "utf8");
$install_cf->random_key="18dcc";
class Web extends \w3capp\W3cAppCom{

	/**
	 * 默认的控制器
	 */
	protected function defaultCtrl(){
		return "\\w3capp\\Controller";
	}
	protected function initConfig(){
		$this->entrance="w3cApp.test.php";
		$this->holder_response=true;
		$_POST['db_file']="test.db";
		$_POST['drop_table']=1;
		$_POST['db_table_pre']="";
		global $install_cf;
		return $install_cf;
	}
}
$w=new Web();
//$w->start("sqlite_install/database");
//$rp=$w->getResponse();
//print_r($rp);
//$rslc=explode("sqlite_install/init_admin&name=",$rp[1]['Location']);
$_POST['old_name']="system";//$rslc[1];
echo $_POST['old_name']."\n";
$_POST['username']="system";
$_POST['password']="123456";

$w->start("sqlite_install/init_admin");

print_r($w->getResponse());