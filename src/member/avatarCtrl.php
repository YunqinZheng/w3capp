<?php
namespace member\controller;
use common\controller\W3cEnterCtrl;
use member\model\Member;

class avatarCtrl extends W3cEnterCtrl{
	public function index($size,$member_id)
	{
		if(!in_array($size,["max","mid","min"]))$size="min";
		$mb=new Member(["id"=>$member_id]);
        return $this->_referer_to(null,$mb->avatar($size).(empty($_GET['v'])?"":"?v1=".$_GET['v']));
	}
	public function update(){
		if(false==empty($_POST)){
			if(self::check_form_hash($_POST['form_hash'],300)==false){
                return $this->_json_return(1,"error:form is overtime","");
			}
			$member=Member::loginMember();
			$upload_=new \w3c\helper\Uploader();
			$upload_->init($member->originalAvatarDir(),array("gif","jpg","jpeg","png","bmp"),true,512000);

			$filekey="file";
			if($upload_->set_input_file($filekey)){
				$uinf=$upload_->save_to();
				if($uinf===false){
					$data['file']=null;
					$update_result=$upload_->get_error();
                    return $this->_json_return(1,"error=".$update_result,"");
				}else{

                    $old_img=$member->headimg;
                    $member->headimg=$uinf->save_as;
					if($member->save()){
						if($old_img)
							@unlink(W3CA_PATH.$member->originalAvatarDir().$old_img);
						$member->makeAvatarXYSize($uinf->save_as,intval($_POST['img_x']),intval($_POST['img_y']),intval($_POST['img_size']));
						$member->storeLogin();
						$er=$member->getError();
						if(empty($er)){
                            return $this->_json_return(0,"修改头像保存成功！");
						}else{
                            return $this->_json_return(1,"图像文件处理错误！","");
						}
					}else{
                        return $this->_json_return(1,"error=66","");
					}
					
				}
			}else{
                return $this->_json_return(1,"error=77","");
			}
		}else{
			return W3cUI::show404();
		}
	}
	public function _action_unfound($fun,$arg)
	{
		if(in_array($fun, array("max2","max","mid","min"))){
			$this->index($fun, $arg[0]);
		}else{
			parent::_action_unfound($fun,$arg);
		}
	}
}
