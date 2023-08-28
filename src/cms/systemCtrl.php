<?php
namespace cms\controller;
use cms\model\FeatureMenu;
use cms\model\SysUser;
use cms\model\UserRole;
use common\model\SysFeatureMenu;

class systemCtrl extends mainCtrl{
	/**
	 * 用户角色
	 */
	public function roles(){
		$role_list=$this->_tpl("system/sys_role");
		$role_list->list_info=UserRole::roleList("id,role_name,note");
		$role_list->output();
	}
	public function role_del(){
		if(empty($_POST['rid'])){
            return $this->_message("请选择要删除的数据");
		}else{
            UserRole::deleteAll(["id"=>implode(",", $_POST['rid'])]);
		}
		$this->roles();
	}
	public function role_edit($id){
		$id=intval($id);
		$rot=new UserRole(['id'=>$id]);
		if($_POST['role_name']){
            $rot->setAttributes($_POST);
			if($rot->save()){
                return $this->_view_return(array("error"=>0,"action"=>"reload","msg"=>"修改成功"));
			}else{
                return $this->_view_return(array("error"=>1,"action"=>"close","msg"=>"修改出错"));
			}
		}else{
			if(empty($rot->id)){
                return $this->_view_return(array("error"=>1,"action"=>"close","msg"=>"数据不存在"));

			}
			$html=$this->_tpl("system/sys_role_input");
			$html->role=$rot->getAttributes();
			$html->title="角色修改";
            return $this->_view_return($html);
		}
	}
	public function role_add(){
		
		if($_POST['role_name']){
            $rot=new UserRole();
            $rot->opt_type=0;
            $rot->setAttributes($_POST);
			if($rot->save()){
                return $this->_view_return(array("error"=>0,"action"=>"reload","msg"=>"添加成功"));
			}else{
                return $this->_view_return(array("error"=>1,"action"=>"close","msg"=>"添加出错"));
			}
		}else{
			$html=$this->_tpl("system/sys_role_input");
			$html->title="角色添加";
            return $this->_view_return($html);
		}
	}
	public function role_option($roid){
		
		if($roid>0){
			$urole=new UserRole(['id'=>$roid]);
			$html=$this->_tpl("system/role_option");
			if($_POST['opt']){
                $urole->options=serialize($_POST['opt']);
                $urole->opt_type=$_POST['opt_type'];
                if($urole->save()){
                    return $this->_referer_to("权限保存成功！","","right");
                }else{
                    return $this->_referer_to("权限保存出错！","","error");
                }
			}
			$rinfo=$urole->getAttributes();
			$html->title="权限配置";
			$html->role_name=$rinfo['role_name'];
			$html->old_opt=unserialize($rinfo['options']);
			$html->role_id=$rinfo['id'];
			$html->opt_type=$rinfo['opt_type'];
			$html->data=FeatureMenu::get_option_tree();
			$html->output();
		}
	}
	function role_names($rids){
		$rns="";
		if($rids){
			$rs=UserRole::rinfo(explode(",",$rids));
			foreach ($rs as $key => $value) {
				$rns.=$rns?",".$value['role_name']:$value['role_name'];
			}
		}
		if(\W3cApp::$holder_response){
		    return \W3cApp::setResponse(200,["Content-type"=>"text/javasript;charset=".W3CA_DB_CHAR_SET],'document.write("'.$rns.'")');
        }else{
            header("Content-type:text/javasript;charset=".W3CA_DB_CHAR_SET);
            echo 'document.write("'.$rns.'")';
        }
	}
	/**
	 * 用户
	 */
	public function users(){
		$pagei=empty($_GET['pageIdx'])?0:$_GET['pageIdx'];
		$html=$this->_tpl("system/sys_user");
		if(empty($_GET['wh_name'])){
            $_GET['wh_name']='';
        }
		$html->list_info=SysUser::search($_GET['wh_name'],$pagei);
		$html->output();
	}
	public function user_del(){
		if(in_array($this->user_info['id'],$_POST['rid'])){
            return $this->_referer_to("您不能删除你自己");
		}
		if(in_array(\W3cApp::$admin, $_POST['rid'])){
            return $this->_referer_to("您不能删除系统管理员");
		}
		foreach ($_POST['rid'] as $userid){
            SysUser::deleteAll(['id'=>$userid]);
        }
        return $this->_referer_to("操作完成！","","right");
	}
	public function user_edit($id){

		if($_POST['name']){
			$user_att=array("name"=>$_POST['name'],"email"=>$_POST['email'],"tel"=>$_POST['tel'],"roles"=>intval($_POST['roles']),"specify_rights"=>implode("",$_POST['rights']));
			$user=new SysUser(['id'=>$_POST['uid']]);
            $user->setAttributes($user_att);
			if(empty($_POST['pwd'])){
			    unset($user_att['pwd']);
            }else{
			    $user->setPassword($_POST['pwd']);
            }
			if($user->save()){
                return $this->_view_return(array("error"=>0,"action"=>"reload","msg"=>"编辑用户成功"));
			}else{
                return $this->_view_return(array("error"=>1,"action"=>"close","msg"=>"用户名信息无法修改"));
			}
		}else{
            $user=new SysUser(['id'=>$id]);
			if(empty($user)){
                return $this->_view_return(array("error"=>1,"action"=>"close","msg"=>"未找到指定用户"));
			}
			$user_edit_v=$this->_tpl("system/sys_user_input");
			$user_edit_v->edit_data=$user;
			$user_edit_v->title="编辑用户";
			$user_edit_v->rlist=UserRole::roleList("id,role_name","","",20);
			$user_edit_v->form_action="user_edit";
            $this->_view_return($user_edit_v);
			
		}
	}
	public function personal_set(){
		$user=SysUser::getLoginUser();
		if($_POST['name']){
			$user->setAttributes(array("name"=>$_POST['name'],"pwd"=>$_POST['pwd'],"email"=>$_POST['email'],"tel"=>$_POST['tel']));
			if($_POST['pwd']){
			    $user->setPassword($_POST['pwd']);
            }
			if($user->save()){
                return $this->_view_return(array("error"=>0,"action"=>"close","msg"=>"用户信息保存成功"));
			}else{
                return $this->_view_return(array("error"=>1,"action"=>"close","msg"=>"用户名信息无法修改"));
			}
		}else{
			if(empty($user)){
                return $this->_view_return(array("error"=>1,"action"=>"close","msg"=>"未找到指定用户"));

			}
			$user_edit_v=$this->_tpl("system/personal_set");
			$user_edit_v->edit_data=$user;
			$user_edit_v->title="修改个人信息";
			$user_edit_v->rlist=UserRole::roleList("id,role_name","","",20);
			$user_edit_v->form_action="personal_set";
            return $this->_view_return($user_edit_v);
			
		}
	}
	public function user_add(){
		
		if($_POST['name']){
			if(SysUser::firstAttr(['name'=>$_POST['name']])){
                return $this->_view_return(array("error"=>1,"action"=>"reload","msg"=>"用户名已经存在"));
			}
			$user=new SysUser(array("name"=>$_POST['name'],"pwd"=>$_POST['pwd'],"email"=>$_POST['email'],"tel"=>$_POST['tel'],"roles"=>intval($_POST['roles']),"specify_rights"=>implode("",$_POST['rights'])));
			if($user->save()){
                return $this->_view_return(array("error"=>0,"action"=>"reload","msg"=>"用户添加成功"));
			}else{
                return $this->_view_return(array("error"=>1,"action"=>"close","msg"=>"用户名无法添加"));
			}
		}else{
			$user_edit_v=$this->_tpl("system/sys_user_input");
			$user_edit_v->rlist=UserRole::roleList("id,role_name","","",20);
			$user_edit_v->form_action="user_add";
			$user_edit_v->title="添加用户";
            return $this->_view_return($user_edit_v);
		}
	}
	/**
	 * 菜单功能设置
	 */
	function feature(){
		$html=$this->_tpl("system/feature_menu");
		$tree_menu=FeatureMenu::get_menu_tree();
        $this->_assign('tree_menu',$tree_menu);
		$html->title="后台菜单";
		$html->output();
        FeatureMenu::put_to_cache($tree_menu);
	}
	function feature_add($pid){
		$html=$this->_tpl("system/feature_input");
		$html->parent_id=$pid;
		$html->title="添加菜单";
		$html->tree_m=FeatureMenu::get_from_cache();
        $this->_view_return($html);
	}
	function feature_edit($id,$pid){
		if(!empty($id)){
			$tree=FeatureMenu::get_from_cache();
			$html=$this->_tpl("system/feature_input");
			$html->parent_id=$pid;
			$html->tree_m=$tree;
			$html->edit_node=$tree[$id];
			$html->title='菜单编辑-'.$tree[$id]['name'].'-编辑';
            $this->_view_return($html);
		}
	}
	function feature_del($id){
		if($id){
		    SysFeatureMenu::deleteAll(['id'=>$id]);
            SysFeatureMenu::deleteAll(['pid'=>$id]);
		}
		$this->_view_return(array('error'=>0,"msg"=>"已删除完成","action"=>"reload"));
	}
	function save_menu_item(){
		if(!$_POST['n_name']){
            return $this->_view_return(array("error"=>1,"msg"=>"未填写名称"),"");
		}
		$pid=$_POST['pid'];
		if($_POST['edid']){
			$menu=new FeatureMenu(['id'=>$_POST['edid']]);
			$menu->setAttributes(["pid"=>$pid,'name'=>$_POST['n_name'],'url'=> $_POST['url'], 'orderid'=>intval($_POST['orderid'])]);

			if($menu->save())
				$msg="修改成功";
		}else{
			if(FeatureMenu::addItem($pid, $_POST['n_name'], $_POST['url'], intval($_POST['orderid'])))
				$msg="添加成功";
		}
		$this->_view_return(array("error"=>0,"msg"=>$msg,"action"=>'reload'));
	}
	/**
	 * 菜单功能操作处理
	 */
	function feature_post(){
		$msg="修改提交成功";
		if(isset($_POST['id'])){
		    $mlist=[];
	        foreach ($_POST['id'] as $value) {
	            if(!empty($_POST['name'.$value])){
	                $menu=array("name"=>$_POST['name'.$value],"url"=>$_POST['url'.$value],"orderid"=>intval($_POST['orderid'.$value]));
                    $mlist[]=[$menu,['id'=>$value]];
	            }
	        }
            FeatureMenu::batchUpdate($mlist);
	        $this->_view_return(array("error"=>0,"msg"=>"数据据已保存"));
	    }else{
	        $this->_view_return(array("error"=>1,"msg"=>"未提交数据"));
	    }
	}
}
