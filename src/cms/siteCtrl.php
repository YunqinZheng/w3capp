<?php
namespace cms\controller;
//站点设置
use common\model\SiteConfig;
use common\model\SiteNavigation;
use cms\model\Theme;
use w3capp\W3cApp;

class siteCtrl extends mainCtrl{

	/**
	 * 基本设置
	 */
	public function index($a=null){
		$html=$this->_tpl("web/web_set");
		$sets=SiteConfig::getConfigs();
        $themes=Theme::getInstalledTheme();
		$html->assign("themes",$themes);
		$html->assign("set_data",$sets);
		$html->action_url=self::$app->route("site/save");
		$html->output();
	}
	/**
	 * 保存基本设置
	 */
	public function save(){

		$site_set=array("web_name"=>$_POST["web_name"],
		"web_keywords"=>$_POST["web_keywords"],
		"description"=>$_POST["description"],
		"style"=>$_POST["style"],
		"style_mobile"=>$_POST["style_mobile"]
		);

		if(SiteConfig::saveConfigs($site_set)===FALSE){
            return $this->_referer_to("保存失败！","","error");
        }
		else{
            return $this->_referer_to("已保存！","","right");
        }
	}
	public function index_code(){
	    $info=SiteConfig::indexTPL($_POST['dir']);
	    if($info){
            return $this->_json_return(0,"",$info);
        }else{
            return $this->_json_return(1,"文件不存在或无法编辑！");
        }
    }
    public function save_index(){
        return $this->_json_return(SiteConfig::saveIndexTPL(strtr($_POST['dir'],["\\"=>"","/"=>""]),$_POST['code'])?0:1);
    }
	/**
	 * 导航设置
	 */
	public function navset(){
		if(false==empty($_POST)){
		    if($_POST['parent_id']=='00'){
                if($_POST['op']=="del"){
                    return $this->_json_return(0,SiteNavigation::deleteAll(["id"=>$_POST["id"]])&&SiteNavigation::removeByParent($_POST["id"]));
                }else{
                    $data=$_POST;
                    if(!$data['id']){
                        $data['id']='T'.(SiteNavigation::maxTid('T')+1);
                    }
                    return $this->_json_return(0,SiteNavigation::saveNav([$data["id"]=>$data]));
                }
            }else{
                $allset=array();
                if(!empty($_POST['allkeys'])){
                    $nkeys=explode(",", $_POST['allkeys']);
                    foreach($nkeys as $key){
                        if($key){
                            if($_POST['del_'.$key]==1){
                                SiteNavigation::deleteAll(['id'=>$key]);
                                continue;
                            }
                            $allset[$key]=array("id"=>$key,"name"=>$_POST['name_'.$key],"url"=>$_POST['url_'.$key],"parent_id"=>$_POST['parent_id'],"ordid"=>$_POST['ordid_'.$key],
                                "display"=>$_POST['display_'.$key]);

                        }
                    }
                }
                $max_diyn=SiteNavigation::maxTid('N');
                if($_POST['name_new']&&$_POST['url_new']){
                    $new_id='N'.($max_diyn+1);
                    $allset[$new_id]=array("id"=>$new_id,"name"=>$_POST['name_new'],"parent_id"=>$_POST['parent_id'],"url"=>$_POST['url_new'],"ordid"=>$max_diyn,
                        "display"=>$_POST['display_new']);
                }
                SiteNavigation::saveNav($allset);
                return $this->_referer_to("已保存成功！","","right");
            }

		}
		if(array_key_exists("parent_id",$_GET)){
            return $this->_json_return(0,"",SiteNavigation::getSeting($_GET['parent_id']));
        }else{
            $html=$this->_tpl("web/navset");
            $html->title="导航设置";
            $html->nav_type=SiteNavigation::getSeting("00");
            $html->output();
        }

	}
	public function clear_cache(){
	    if(false==empty($_POST)){
            SiteConfig::clearCache();
            return $this->_json_return(0,"清理完成");
        }
        $this->_tpl("web/clear_cache")->output();
		//$this->_referer_to("缓存数据已清理","javascript:window.history.go(-1);","right");
	}


}
