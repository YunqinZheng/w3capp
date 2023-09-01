<?php
namespace cms\controller;
use w3capp\W3cApp;
/**
 * 针对mysql生成代码
 * Class coderCtrl
 * @package ctrl
 */
class coderCtrl extends mainCtrl{
    public function index($a=null)
    {
        $dp=opendir(W3CA_MASTER_PATH."app");
        if($dp==false){
            echo "app dir error!!";
            return;
        }
        $dir_list=[];
        while($d=readdir($dp)){
            if(is_dir(W3CA_MASTER_PATH."app/".$d)&&$d{0}!="."){
                $dir_list[]=$d;
            }
        }
        closedir($dp);
        $table_pre='';
        foreach (W3cApp::$db_config as  $conf){
            $config=(array)current($conf);
            $table_pre=$config['tab_pre'];
            break;
        }
        $this->_assign("table_pre",$table_pre);
        $this->_assign("app_dirs",$dir_list);
        $this->_tpl("system/code_writer")->output();
    }
    private function modelFile(){
        $this->_assign("table_name",$_POST['table']);
        $this->_assign("space_name",$_POST["app_dir"]."\\model");
        ob_start();
        $this->_tpl("system/model_tpl")->includeTpl("");
        $content="<?php \n".ob_get_contents();
        ob_end_clean();
        if(W3cApp::$holder_response){
            W3cApp::setResponse(200,["Content-Type"=>"text/plain",'Content-Disposition'=>'attachment; filename='.$_POST['class_name'].".php"],$content);
        }else{
            header("content-type:text/plain;");
            header('Content-Disposition: attachment; filename='.$_POST['class_name'].".php");
            echo $content;
            exit;
        }
    }
    private function controllerFile(){

        $this->_assign("space_name",$_POST["app_dir"]."\\controller");
        $this->_assign("space_m",$_POST["app_dir"]."\\model\\".$_POST['record_name']);
        $this->_assign("space_m_name",$_POST['record_name']);

        ob_start();
        $this->_tpl("system/controller_tpl")->output();
        $content="<?php \n".ob_get_contents();
        ob_end_clean();
        if(W3cApp::$holder_response){
            return W3cApp::setResponse(200,["Content-Type"=>"text/plain",'Content-Disposition'=>'attachment; filename='.$_POST['class_name'].".php"],$content);
        }else{

            header("content-type:text/plain;");
            header('Content-Disposition: attachment; filename='.$_POST['class_name'].".php");
            echo $content;
            exit;
        }

    }
    private function desc($table){
        $db=$this->_db();
        if(method_exists($db,"tableDesc")){
            $primary="";
            if($db->tableExisted($table)){
                $desc_table=$db->tableDesc($table,$primary);
                $this->_assign("primary",$primary);
                return $desc_table;
            }else{
                return $this->_message($table."：表不存在！");
            }
        }else{
            return $this->_message(W3cApp::$install_config['db_default']."：数据驱动不支持该功能！");
        }
        return false;
    }
    public function table_desc(){
        $desc_table=$this->desc($_POST['table']);
        return $this->_json_return(0,"","",$desc_table);
    }
    public function file(){
        if(empty($_POST['table'])){
            return $this->_referer_to("请填写表名");
        }
        if(empty($_POST['file_type'])){
            return $this->_referer_to("请选择代码类型");
        }
        $this->_assign("table_pre",$this->_db()->config['tab_pre']);
        $this->_assign('class_name',$_POST['class_name']);
        $desc_table=$this->desc($_POST['table']);
        $this->_assign("table_desc",$desc_table);
        $ftn=$_POST['file_type']."File";
        $this->$ftn();
    }
}