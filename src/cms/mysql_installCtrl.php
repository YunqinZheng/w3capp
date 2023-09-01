<?php
namespace cms\controller;
use cms\model\Theme;
use w3capp\W3cApp;
use w3capp\Controller;
use w3capp\UI;
use w3capp\InstallConfig
class mysql_installCtrl extends Controller {
    public function __construct(){
        if(is_file("./data/install.config.php")&&(empty($_GET[W3cApp::URI_KEY])||$_GET[W3cApp::URI_KEY]!="mysql_install/success")){
			if(W3CA_OPEN_DEBUG){
				return $this->_message('删除<strong>data/install.config.php</strong>文件才能进行安装!');
			}else{
				return UI::show404();
			}
        }
		W3cApp::template()->setDefaultTplPath(W3CA_MASTER_PATH.'app/common/view/');
    }
    public function index($a=null)
    {
        $this->_tpl("system/install")->output();
    }
    public function database(){
        if(false==class_exists("PDO",false)){
            return $this->_message("php PDO not open");
        }
        if(false==is_writeable(W3CA_PATH."data")){
            return $this->_message("/data dir not write able");
        }
        try{

            $pdo=new \PDO("mysql:host=".$_POST['db_host'].";port=".$_POST['db_port'].";dbname=".$_POST['db_name'],$_POST['db_user'],$_POST['db_pass']
                ,array(\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES '.W3CA_DB_CHAR_SET));
            $sql_file=W3CA_PATH."data/install_mysql.sql";
            if(!is_file($sql_file)){
                return $this->_message("data/install.sql not found!");
            }
            $sth = $pdo->prepare("show tables");
            $sth->execute();
            if(empty($_POST['drop_table'])){
                /*
                 * "content_article","content_book","content_forum","content_forum_comment","content_forum_subject","content_news",
                 * */
                $table_list=array("block_content","block_data","block_mark","category","category_value","channel","channel_form","channel_type","mail_box","mail_message",
                    "mail_upload_files","material","member","member_follow","member_group","page_block","site_config","site_navigation","sys_feature_menu","sys_user",
                    "sys_user_role","web_theme","weixin_user");
                foreach ($table_list as &$val){
                    $val=$_POST['db_table_pre'].$val;
                }

                while ($r=$sth->fetch(\PDO::FETCH_NUM)){
                    if(in_array($r[0],$table_list)){
                        return $this->_message("表".$r[0]."已存在！");
                    }
                }
            }

            $sql=file_get_contents($sql_file);
            $sql=str_replace("{\$tab_pre}",$_POST['db_table_pre'],$sql);

            if($pdo->query($sql)===false){
                return $this->_message("mysql init error!");
            }
            $columns=array("name","pwd","roles","pwd_hash");
            $pre_sql="insert INTO ".$_POST['db_table_pre']."sys_user(".implode(",", $columns).") values (:".implode(',:', $columns).")";
            $prep=$pdo->prepare($pre_sql);
            $pre_val=array("name"=>"u".rand(1000,9000),"pwd"=>"","roles"=>1,"pwd_hash"=>dechex(time()%1000000));
            if($prep->execute($pre_val)){
                $new_id=$pdo->lastInsertId();
                if($new_id===false){
                    return $this->_message("sys_user init error");
                }
            }else{
                return $this->_message("sys_user init error2");
            }

            $cache=new \w3capp\helper\Cache();
            if($cache->saveValue("init_post",serialize($_POST))===false){
                return $this->_message("cache error!!");
            }else{
                return $this->_referer_to(null,"install.php?".W3cApp::URI_KEY."=mysql_install/init_admin&name=".$pre_val['name']);
            }

        }catch (\PDOException $e){
            return $this->_message("mysql PDO connect error!请输入正确数库用户和密码");
        }

    }

    public function init_admin(){
        if(empty($_POST['username'])){
            $this->_assign("old_name",$_GET['name']);
            $this->_assign("ctr_name",'mysql_install');
            $this->_tpl("system/init_admin")->output();
        }else{
            $cache=new \w3capp\helper\Cache();
            $init_=$cache->value("init_post");
            if(empty($init_)){
                return $this->_message("init post save error!");
            }
            $init_val=unserialize($init_);
            try {

                $pdo = new \PDO("mysql:host=" . $init_val['db_host'] . ";port=" . $init_val['db_port'] . ";dbname=" . $init_val['db_name'], $init_val['db_user'], $init_val['db_pass']
                    , array(\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES ' . W3CA_DB_CHAR_SET));
                $sth = $pdo->prepare("select * from ".$init_val['db_table_pre']."sys_user where `name`=:name");
                $sth->execute(array(":name"=>$_POST['old_name']));
                while ($r=$sth->fetch(\PDO::FETCH_ASSOC)){
                    $sth=$pdo->prepare("update ".$init_val['db_table_pre']."sys_user set specify_rights='{page_edit}', `pwd`=:pwd,`name`=:new_name where `name`=:name");
                    if(false===$sth->execute(array(":pwd"=>md5($_POST['password'].$r['pwd_hash']),":new_name"=>$_POST['username'],":name"=>$_POST['old_name']))){
                        return $this->_message("administrator save error!");
                    }else{
                        $installer=new InstallConfig();
                        if($installer->save(array('dsn'=>"mysql:host=".$init_val['db_host'].";port=".$init_val['db_port'].";dbname=".$init_val['db_name'],
                            'user'=>$init_val['db_user'],"pwd"=>$init_val['db_pass'],"tab_pre"=>$init_val['db_table_pre']),
                        "MysqlPDO","W3cMyAdapter"
                        )){
                            W3CApp::$install_config=$installer;
                            W3CApp::$db_config=$installer->db_config;
                            W3cApp::$entrance="index.php";
                            $theme=new Theme(['id'=>"default"]);
                            $theme_rs=$theme->install();
                            if(empty($theme_rs['error'])){
								
                                return $this->_referer_to(null,"install.php?".W3cApp::URI_KEY."=success");
                            }else{
                                return $this->_message($theme_rs['error']);
                            }
                            print_r($theme_rs);
                            exit;
                        }
                    }
                    break;
                }
                return $this->_message("error:administrator init!");
            }catch (\PDOException $e){
                return $this->_message("mysql PDO connect error!请输入正确数库用户和密码");
            }
        }
    }
	public function success(){
		$this->_tpl("system/install_success")->output();
	}

}