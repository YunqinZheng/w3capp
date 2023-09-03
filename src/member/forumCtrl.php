<?php
namespace ctrl;
use cms\model\ContentType;
use helper\ContentForm;
use common\controller\W3cEnterCtrl;
use member\model\Member;

class forumCtrl extends W3cEnterCtrl{
	function index($a=null){
	    if(empty($a)||$a=="index.php")
		return $this->_referer_to(null,\self::$app->route("/"));
	}
	public function thread($id)
	{
		$content_model=ContentType::contentRecord("forum_subject");
		$html=$this->_tpl("forum/thread");
		$html->content=$content_model->firstRow("*","deprecated=0 and id=".intval($id));
		
		$html->title=$html->content['title'].'-'.$this->site_set['web_name'];
		if($html->content['keywords'])
			$html->keywords=$html->content['keywords'];
		else
			$html->keyword=$this->site_set['web_keyword'];
		if($html->content['description'])
			$html->description=$html->content['description'];
		else
			$html->description=$this->site_set['description'];
		$content_model->updateData(array("views"=>$html->content['views']+1),"id=".$html->content['id']);
		$html->nav_key="C2";
		$html->output();
	}
	public function comment(){

		if(self::check_form_hash($_POST['form_hash'],20)==false){
			return $this->_json_return(1,"你提交的表单无效，请刷新重试。");
		}

		$member=Member::info();
        $form_helper=new ContentForm();
		$content_mode=ContentType::contentRecord("forum_comment");
		$member_columns=self::_lib("web/ContentType")->member_columns("forum_comment");
		$data=$form_helper->get_form_data($member_columns);
		if(empty($data)||$data['description']==''){
			return $this->_json_return(1,"不能提交空白评论");
		}
		if(method_exists($content_mode, "data_check")){
		    $checked_str=$content_mode->data_check($data);
		    if($checked_str){
		        return $this->_json_return(1,$checked_str);
		    }
		}else{
		    $data['dateline']=time();
		    if(array_key_exists("views",$data)&&$data['views']==''){
		        $data['views']=0;
		    }
		}
		$data['member_id']=$member['id'];
		$data['dateline']=time();
		$content_id=$content_mode->addData($data,$form_helper->var_replace);
		if($content_id){
			return $this->_json_return(0,"评论已经发布");
		}else{
			return $this->_json_return(1,"评论已经出错");
		}

	}
}