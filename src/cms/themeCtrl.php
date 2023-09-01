<?php
namespace cms\controller;
use cms\model\Theme;
use common\model\SiteConfig;
use w3capp\helper\Uploader;
use w3capp\W3cApp;

class themeCtrl extends mainCtrl{
    public function index($a=null)
    {
        $html=$this->_tpl("theme/index");
        $themes= Theme::getThemes();
        $html->assign('pc_style',SiteConfig::getSetting('style'));
        $html->assign('mb_style',SiteConfig::getSetting('style_mobile'));
        $html->assign("themes", $themes);
        $html->output();
    }
    public function install($theme_name){
        $theme=new Theme(['id'=>$theme_name]);
        $theme_json=$theme->install();
        if(empty($theme_json['error'])){
            return $this->_json_return(0,"'".$theme_json['name']."'安装成功！",W3cApp::route("site/theme"));
        }else{
            return $this->_json_return(1,$theme_json['error'],"");
        }
    }
    public function tpl_list($theme_name){
        $this->_assign("theme_dir",$theme_name);
        $this->_assign('all_files',Theme::AllTpl($theme_name));
        $this->_assign('title',$theme_name);
        $this->_tpl("theme/tpl_list")->output();
    }
    public function tpl_content(){
        if(strpos($_POST['file'],$_POST['theme_dir'])===0){
            $file=W3CA_THEME_TPL.$_POST['file'];
            if(stripos($file,".php")||is_writable($file)==false){
                return $this->_json_return(1,"文件无法写入");
            }else if(empty($_POST['content'])){
                return $this->_json_return(0,"",file_get_contents($file));
            }else{
                return $this->_json_return(file_put_contents($file,$_POST['content'])?0:1,"");
            }
        }else{
            $this->_json_return(1,"文件目录不正确");
        }
    }
    public function save_tpl(){
        if(self::check_form_hash($_POST['token'],3600)&&strpos($_POST['to_dir'],$_POST['theme_dir'])===0){
            if(empty($_FILES['file']['name'])){
                $this->_json_return(1,"上传错误！");
            }
            $mdir=W3CA_THEME_TPL.$_POST['to_dir'];
            if(is_writable($mdir)==false){
                $this->_json_return(1,"文件无法写入");
            }
            $upload_=new Uploader();
            $upload_->mainDir(W3CA_THEME_TPL);
            $upload_->set_input_file("file");
            $upload_->init(str_replace("..","",trim($_POST['to_dir'],"/"))."/",array("htm"),true,1024*1024);
            $uinf = $upload_->save_to($_FILES['file']['name']);
            if($uinf===false){
                $this->_json_return(1,$upload_->get_error());
            }else{
                $this->_json_return(0,"文件保存成功");
            }
        }
    }
    public function del_tpl(){
        if(strpos($_POST['file'],$_POST['theme_dir'])===0){
            $file=W3CA_THEME_TPL.$_POST['file'];
            if(stripos($file,".php")||is_writable($file)==false){
                $this->_json_return(1,"文件无法写入");
            }else{
                $this->_json_return(unlink($file)?0:1,"");
            }
        }else{
            $this->_json_return(1,"文件目录不正确");
        }
    }
    public function uninstall($theme_name){
        $theme=new Theme(['id'=>$theme_name]);
        if($theme->uninstall()){
            $this->_json_return(0,"主题已删除!",W3cApp::route("site/theme"));
        }else{
            $this->_json_return(1,"删除出错!","");
        }
    }
    public function edit($dir){
        $theme=new Theme(['id'=>$dir]);
        if(false==empty($_POST)){
            $theme->setAttributes($_POST);
            $theme->save();
            $this->_view_return(array("action"=>"reload","msg"=>"信息已保存!"));
        }else{
            $html=$this->_tpl("web/edit");
            $html->title=$theme->name;
            $html->assign("theme",$theme->getAttributes());
            $this->_view_return($html);
        }

    }
}