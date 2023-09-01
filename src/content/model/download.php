<?php
namespace content\model;
use w3c\helper\Uploader;
class download extends \W3cRecord{
    static public function recordName(){
        return "content_download";
    }
    static public function recordRule(){
        return [[['channel_id'],"require"],        
		[['views','channel_id','deprecated','dateline'],"integer"],        
		[['title','file_url'],"string",200],        
		[['keywords','description'],"string",300],        
		[['author'],"string",50]];
    }
    static public function propertyDesc(){
        return array (
  'id' => 'id',
  'title' => '标题',
  'channel_id' => '所属栏目',
  'keywords' => '关键字',
  'description' => '描述',
  'dateline' => '发布时间',
  'deprecated' => '删除标记',
  'author' => '作者',
  'views' => '查看数',
  'file_url' => '下载URL',
);
    }
    //后台查询条件
    static function defaultCondition(){
        return [];
        }
    //会员中心查询条件
    static function memberCondition(){
        return [];
        }
    //后台表单
    static function defaultForm(){
        $info=$_POST;
        $data = array("title"=>$info["title"],"deprecated"=>0,"channel_id"=>$info["channel_id"],"keywords"=>$info["keywords"],"description"=>$info["description"],"author"=>$info["author"],"views"=>$info["views"],"file_url"=>$info["file_url"]);
		if(empty($info["dateline"])){
			$data['dateline']=time();
		}else{
			$data['dateline']=strtotime($info["dateline"]);
		}
		if(empty($_FILES["file"])){
		}else{
			$upload_=new Uploader();
			$upload_->init('data/material/',array("pdf","doc","docx","jpg"),true,2048*1024);
			if($upload_->set_input_file("file")){
				$uinf=$upload_->save_to();
				if($uinf===false){
					$update_result=$upload_->get_error();
					throw new \Exception("er=".$update_result);
				}else{
					$data['file_url']=W3CA_URL_ROOT.'data/material/'.$uinf->save_as;

				}
			}
		}
        return $data;
    }
    //会员表单
    static function memberForm($member_info){
        $data = array("title"=>$_POST["title"],"channel_id"=>$_POST["channel_id"]);
        
        return $data;
    }
}