<?php
namespace cms\controller;
use cms\model\Theme;
use cms\model\Material;
use member\model\Member;
use w3capp\helper\Uploader;

class explorerCtrl extends mainCtrl{
    function index($page=1){
        $html=$this->_tpl("explorer/index");
        $themes=Theme::getInstalledTheme();
        $s_name='';
        if(empty($_GET['search_file'])){
            $this->_assign("search_file","");
        }else{
            $this->_assign("search_file",Str::htmlchars($_GET['search_file']));
            $mp=W3CA_URL_ROOT.Material::mainDir();
            if(strpos($_GET['search_file'],$mp)!==false){
                $_GET['theme_id']=null;
                list($sn,$s_name)=explode($mp,$_GET['search_file']);
                list($s_name)=explode("?",$s_name);
            }
        }
        if(empty($_GET['theme_id'])){
            $this->_assign('theme_id','');
            $this->_assign('sub_path','');
            $mt=Material::adaptTo($s_name?['like'=>["file",$s_name]]:'')
                ->limit(20,intval($page))
                ->orderBy(['id'=>'desc']);
            $this->_assign('page_d',$mt->selectAll(true));
            $html->main_dir=Material::mainDir();
        }else{
            $this->_assign('theme_id',$_GET['theme_id']);
            $this->_assign('sub_path',$_GET['sub_path']);
            $this->_assign('page_d','');
        }
        $html->assign("themes",$themes);
        $html->output();
    }
    function del($ids){
        if(false==empty($this->memberInfo['id'])){
            Material::deleteFiles(explode(",",$ids),$this->memberInfo['id']);
        }else{
            Material::deleteFiles(explode(",",$ids));
        }
        if(empty($_GET['isAjax'])){
            return $this->_referer_to("删除成功","","right");
        }
        $this->_json_return(0,'删除成功');
    }

    function add_file(){
		if(empty($_POST['file_name'])){
			$this->_json_return(1,"file null");
		}
		$names=explode(".",$_POST['bz']);
		$store_file=W3CA_PATH.Material::mainDir().trim($_POST['file_name'],"/");
		if(!file_exists($store_file)){
			$this->_json_return(1,"file not exists:".$store_file);
		}
		if(empty($_POST['theme'])){
			if(empty($_POST['replace_file'])){
				$dirs=explode("/",$_POST['file_name']);
				$dirs[count($dirs)-1]=Uploader::storeName().".".end($names);
				$file_name=implode("/",$dirs);
				rename($store_file,W3CA_PATH.Material::mainDir().$file_name);
				if(empty($_POST['classify']))$_POST['classify']="file";
				$add_=array('bz'=>$_POST['bz'],"file"=>$file_name,"classify"=>$_POST['classify'],"dateline"=>time(),"size"=>$_POST['file_size']);
				$mt=new Material($add_);
				if($mt->save()===false){
					$this->_json_return(1,"file save error!");
				}
			}else{
				$mf=Material::firstAttr(['file'=>$_POST['replace_file']]);
				if($mf){
					rename($store_file,W3CA_PATH.Material::mainDir().$_POST['replace_file']);
				}else{
					$this->_json_return(1,"上传文件出错！替换文件不存在");
				}
			}
			
		}else{
			$theme=new Theme(['id'=>$_POST['theme']]);
			if(empty($theme)){
				$this->_json_return(1,"theme not found!");
			}
			if(empty($_POST['replace_file'])){
				$tofile=W3CA_PATH.'data/theme/'.$theme->install_dir."/".$_POST['sub_path'].$_POST['bz'];
				//str_replace(["/",".blob"],["-",""],$_POST['file_name']).".".end($names)
				rename($store_file,$tofile);
			}else{
				rename($store_file,W3CA_PATH.'data/theme/'.$theme->install_dir."/".$_POST['sub_path']."/".$_POST['replace_file']);
			}
		}
		$this->_json_return(0,"文件保存成功!");
    }


    function select(){
        if(false==empty($_POST)){
            $condition=["classify"=>"image"];
            $mt=Material::adaptTo($condition);
            $page=intval($_POST['page']);
            $mt->limit(16,$page)->orderBy(['id'=>'desc']);
            $data=$mt->selectAll()->toArray();
            $this->_json_return(0,"ok",$data);
        }
        $html=$this->_tpl("explorer/select");
        $html->title="选择文件";
        $html->main_dir=Material::mainDir();
        $this->_view_return($html);
    }

    /**
     * 编辑器上传
     */

    function ckeditor_pic(){
        if(empty($_GET['page'])){
            $page=0;
        }else{
            $page=intval($_GET['page']);
        }
        //$count=intval($count);
        $condition=["classify"=>'image'];
        if(false==empty($this->memberInfo['id'])){
            $condition['member_id']=$this->memberInfo['id'];
        }
        $data=Material::adaptTo($condition)->limit(20,intval($page))->orderBy(['id'=>'desc'])->selectAll(true);
        if($page>1){
            $this->_json_return(0,"ok",$data);
        }
        $html=$this->_tpl('explorer/browser_pic');
        $html->title="选择图片";
        $html->main_dir=Material::mainDir();
        $html->data=$data;
        if(empty($_GET['mselect'])){
            //$mselect,$page,$count
            $html->mselect=0;
        }else{
            $html->mselect=1;
        }
        $html->output();
    }
    function picform(){

        echo json_encode(array("formData"=>array("token"=>self::_form_hash(),"classify"=>"image"),
            "uploader"=>W3cApp::route('cms/explorer/pic_post'),
            "fileObjName"=>"file","multi"=>false,
            "fileTypeExts"=>"*.jpg;*.jpeg;*.gif;*.png;",
            "fileSizeLimit"=>"2MB"));
    }
    function upload(){
        if(!self::check_form_hash($_POST['token'],3600)){
            $ms="上传超时！";
            $this->_json_return(1,$ms);
        }
		if(empty($_POST['classify']))$_POST['classify']="file";
        $mt=new Material();
        $ms=$mt->postCheck();
        if($ms){
            $this->_json_return(1,$ms);
        }
        $upload_=new Uploader();
        $upload_->init($mt->mainDir(),null,true,2048*1024);
        if($upload_->set_input_file("file")){
            if(empty($_POST['guid'])){
                $uinf=$upload_->save_to();
                $mt_ex=null;
            }else{
                $fext=empty($_POST['fext'])?"":$_POST['fext'];
                $save_name=$upload_->make_file_name($_POST['guid'],$fext);
                $uinf=$upload_->save_to($save_name,true);
				if(empty($_POST['only_save_file'])){
					$mt_ex=Material::record(["file"=>$uinf->save_as]);
				}
            }

            $data=array();
            if($uinf===false){
                $data['file_name']=null;
                $update_result=$upload_->get_error();
                $this->_json_return(1,"er=".$update_result,"",$data);
            }else{
                $data['file_name']=$uinf->save_as;
                $data['file_path']=$mt->filePath($uinf->save_as,'');
				if(empty($_POST['only_save_file'])){
					$add_=array('bz'=>$_POST['bz']?$_POST['bz']:$uinf->old_name,"file"=>$uinf->save_as,"classify"=>$_POST['classify'],"dateline"=>time(),"size"=>$uinf->size);
					if(empty($_POST['access'])){
						$add_['access_key']='';
					}else{
						$add_['access_key']=$_POST['access'];
					}
					if(empty($mt_ex)){
						$mt->setAttributes($add_);
						$mt->save();
					}else{
						$mt_ex->setAttributes($add_);
						$mt->save();
					}
				}
                $this->_json_return(0,"ok",$data);
            }
        }
    }
    function pic_post(){
        if(!self::check_form_hash($_POST['token'],3600)){
            $ms="上传超时！";
        }else{
            $mt=new Material();
            $ms=$mt->postCheck();
            if(false==empty($_POST)&&$ms==""){
                $upload_=new Uploader();
                $upload_->init($mt->mainDir(),array("gif","jpg","jpeg","png"),true,2048*1024);
                if($upload_->set_input_file("file")){
                    $uinf=$upload_->save_to();
                    $data=array();
                    if($uinf===false){
                        $data['file_name']=null;
                        $update_result=$upload_->get_error();
                        $this->_json_return(1,"er=".$update_result,"",$data);
                    }else{
                        $data['file_name']=$uinf->save_as;
                        $data['file_path']=$mt->filePath($uinf->save_as,'');
                        $add_=array('bz'=>$_POST['bz']?$_POST['bz']:$uinf->old_name,"file"=>$uinf->save_as,"classify"=>$_POST['classify'],"dateline"=>time(),"size"=>$uinf->size);
                        if($_POST['access']){
                            $add_['access_key']=$_POST['access'];
                        }
                        $mt->setAttributes($add_);
                        $mt->save();
                        $this->_json_return(0,"ok",$data);
                    }
                }else{
                    $this->_json_return(1,"error init","");
                }
            }else{
                $this->_json_return(1,"error:".$ms,"");
            }
        }
        $this->_json_return(1,$ms,"");
    }
    function _check_operation($f){
        if($f=='pic_post'||$f=="picform"||false==empty(Member::info())&&($f=='ckeditor_pic'||$f=='addFiles'||$f=='del'))return true;
        return parent::_check_operation($f);
    }
}