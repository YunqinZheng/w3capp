<?php
namespace member\controller;
use cms\model\ContentType;
use w3c\helper\Uploader;
use member\model\Member;
use member\model\MemberGroup;

/**
 * 会员后台管理
 * @package member\controller
 */
class managerCtrl extends \cms\controller\mainCtrl{


	function index($a=null){
		$index=intval($_GET['page']);
		$where='';
		if($_GET['search']){
			$where=['or'=>[['id'=>intval($_GET['search'])],['like'=>['email',$_GET['search']]],['like'=>['name',$_GET['search']]]]];
		}
		$html=$this->_tpl("manager/search");
		$html->title="会员管理";
		$html->list=Member::adaptTo($where)->orderBy(['id'=>'desc'])->limit(20,$index)->selectAll(true);
        $html->list->appendFilter(function(&$val){
            $val['group_name']=MemberGroup::groupName($val['groupid']);
        });
		$html->output();
	}
	function delete(){
        Member::deleteAll(["id"=>$_POST['rid']]);
		$this->index();
	}
	function login($id){
	    $mb=new Member(['id'=>$id]);
	    $token=urlencode($mb->getToken());
        return $this->_referer_to(null,ctrl_url(['member',$mb['name']]).(\self::$app->rewriteurl?'?token='.$token:'&token='.$token));

    }
	function edit($id){
		$id=intval($id);
		if($_POST['name']&&$_POST['email']){
			$data=array('name'=>$_POST['name'],
                "password"=>$_POST['password'],
                'email'=>$_POST['email'],
                "mobile"=>$_POST['mobile'],
                "groupid"=>intval($_POST['groupid']),
                "headimg"=>$_POST['headimg']);

			$mb=new Member(['id'=>$id]);
			if(!$mb->primary()){
			    $this->_message("会员不存在！");
            }
            $mb->setAttributes($data);
			if($mb->save()){
				if($_POST['old_headimg']!=$_POST['headimg_data']){
					@unlink(W3CA_MASTER_PATH.$this->member_model->originalAvatarDir().$_POST['old_headimg']);
				}
                return $this->_referer_to("修改已经保存!",\ctrl_url("member/manager"),"right");
			}else{
                return $this->_referer_to("修改失败!");
			}
		}
		$html=$this->_tpl("manager/input");
		$html->title="修改会员";
		$html->group_list=MemberGroup::getList();
		$html->member_info=new Member(["id"=>$id]);
		$html->output();
	}
	function add(){
		if($_POST['name']&&$_POST['email']&&$_POST['password']){
			$data=array('name'=>$_POST['name'],"regdate"=>time(),'password'=>$_POST['password'],'email'=>$_POST['email'],"mobile"=>$_POST['mobile'],"groupid"=>intval($_POST['groupid']),"headimg"=>$_POST['headimg']);
			$mb=new Member($data);
			if($mb->save()){
                return $this->_show_message("会员已经添加!","right",[['href'=>\ctrl_url("member/manager"),'text'=>"返回列表"],
                    ['href'=>\self::$app->route("member/manager/add"),'text'=>"继续添加"]]);
			}else{
                return $this->_referer_to("会员添加失败!");
            }
		}
		$html=$this->_tpl("manager/input");
		$html->group_list=MemberGroup::getList();
		$html->title="添加会员";
		$html->output();
	}
	function headimg_upload(){
		$upload_=new Uploader();
		$upload_->init($this->member_model->originalAvatarDir(),array("gif","jpg","jpeg","png","bmp"),true,512000);
		$data=array();
		$filekey="file";
		$error_msg="";
		if($upload_->set_input_file($filekey)){
			$uinf=$upload_->save_to();
			if($uinf===false){
				$data['file']=null;
				$error_msg=$upload_->get_error();
			}else{
				$data['file_path']=W3CA_URL_ROOT.$this->member_model->originalAvatarDir().$uinf->save_as;
				$data['file_name']=$uinf->save_as;
			}
		}else{
			$error_msg=77;
		}
        return $this->_json_return($error_msg?1:0,$error_msg,"",$data);
	}
	/**
	 *会员组设置
	 */
	function group(){
		if(false==empty($_POST)){
			if(false==MemberGroup::saveAll($_POST)){
			    $this->_message("保存失败！");
            }
            return $this->_referer_to("保存成功!","","right");
		}
		$html=$this->_tpl("manager/group");
		//$html->content_models=self::_lib("web/ContentType")->getFetcher30();
        $html->content_models=ContentType::findAllData(['member_publish'=>1]);
		$html->group_list=MemberGroup::getList();
		$html->title="会员组设置";
		$html->output();
	}
	function seting(){
		if(false==empty($_POST)){
            Member::saveConfig($_POST);
            return $this->_referer_to("设置已保存!","","right");
		}
		$html=$this->_tpl("manager/seting");
		$html->title="会员设置";
		$html->data=Member::configInfo();
		$html->output();
	}
}
