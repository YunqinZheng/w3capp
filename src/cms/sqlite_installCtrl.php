<?php

namespace cms\controller;
use cms\model\Theme;
use w3capp\helper\Sql;
use w3capp\driver\SqlitePDO;
use w3capp\Controller;
use w3capp\InstallConfig;
use w3capp\UI;
class sqlite_installCtrl extends Controller{
    public function __construct(){
        if(is_file("./data/install.config.php")&&(empty($_GET[W3cApp::URI_KEY])||$_GET[W3cApp::URI_KEY]!="mysql_install/success")){
			if(W3CA_OPEN_DEBUG){
				return $this->_show_message('删除<strong>data/install.config.php</strong>文件才能进行安装!');
			}else{
				return UI::show404();
			}
        }
    }
    public function index($a=null)
    {
        $this->_tpl("system/sqlite_install")->output();
    }
    public function database(){
        if(false==class_exists("PDO",false)){
            return $this->_message("php PDO not open");
        }
        if(false==is_writeable(W3CA_PATH."data")){
            return $this->_message("/data dir not write able");
        }
        if(empty($_POST['db_file'])){
            return $this->_message("db file is empty");
        }else{
            $db_file=$_POST['db_file'];
            if(strpos($db_file,W3CA_PATH)===0){
                return $this->_message("db file path is error;the path can't been read by http");
            }
            if(file_exists($db_file)&&empty($_POST['drop_table'])){
                return $this->_message("db file is exists;Replace option is not checked");
            }
        }
        try{

            $pdo=SqlitePDO::init(["dsn"=>"sqlite:".$db_file]);
            $sql_file=W3CA_PATH."data/install_sqlite.sql";
            if(!is_file($sql_file)){
                return $this->_message("data/install_sqlite.sql not found!");
            }


            $sql=file_get_contents($sql_file);
            $sql=str_replace("{\$tab_pre}",$_POST['db_table_pre'],$sql);

            if($pdo->execute($sql)===false){
                return $this->_message("sql init error!");
            }

            $pre_val=array("name"=>"u".rand(1000,9000),"pwd"=>"","roles"=>1,"pwd_hash"=>dechex(time()%1000000));
            if($new_id=$pdo->insert($pre_val,$_POST['db_table_pre']."sys_user")){
                if($new_id===false){
                    return $this->_message("sys_user init error");
                }
            }else{
                return $this->_message("sys_user init error2");
            }

            $cache=new \w3capp\helper\Cache();
            if($cache->saveValue("init_post",serialize($_POST),600)===false){
                return $this->_message("cache error!!");
            }else{
                return $this->_referer_to(null,"install.php?".W3cApp::URI_KEY."=sqlite_install/init_admin&name=".$pre_val['name']);
            }

        }catch (\PDOException $e){
            return $this->_message("PDO sqlite error".$e->getMessage());
        }

    }

    public function init_admin(){
        if(empty($_POST['username'])){
            $this->_assign("old_name",$_GET['name']);
            $this->_assign("ctr_name",'sqlite_install');
            $this->_tpl("system/init_admin")->output();
        }else{
            $cache=new \w3capp\helper\Cache();
            $init_=$cache->value("init_post");
            if(empty($init_)){
                return $this->_message("init post save error!");
            }
            $init_val=unserialize($init_);
            try {
                $pdo=SqlitePDO::init(["dsn"=>"sqlite:".$init_val['db_file']]);
                $user_ex=$pdo->getFirst("select * from ".$init_val['db_table_pre']."sys_user where ".Sql::parse(["name"=>$_POST['old_name']]));
                if($user_ex){
                    if($pdo->update(array("pwd"=>md5($_POST['password'].$user_ex['pwd_hash']),"specify_rights"=>"{page_edit}","name"=>$_POST['username']),
                        $init_val['db_table_pre']."sys_user",Sql::parse(["name"=>$_POST['old_name']]))===false){
                        return $this->_message("administrator save error!");
                    }else{
                        $installer=new InstallConfig();
                        if($installer->save(array('dsn'=>"sqlite:".$init_val['db_file'],"tab_pre"=>$init_val['db_table_pre']),
                            "SqlitePDO","W3cMyAdapter")){
                            W3cApp::$install_config=$installer;
                            W3cApp::$db_config=$installer->db_config;
                            W3cApp::$entrance="index.php";
                            //header("location:".app_path()."w3c_install/complete");
                            //$this->_tpl("system/install_success")->output();
                            $theme=new Theme(["id"=>"default"]);
                            $theme_rs=$theme->install();
                            if(empty($theme_rs['error'])){
                                $this->_referer_to(null,"index.php");
                            }else{
                                return $this->_message($theme_rs['error']);
                            }
                            print_r($theme_rs);
                            exit;
                        }
                    }
                }else{
                    @unlink("data/install.config");
                    return $this->_message("error:administrator init!");
                }

            }catch (\PDOException $e){
                return $this->_message("PDO error:".$e->getMessage());
            }
        }
    }

}