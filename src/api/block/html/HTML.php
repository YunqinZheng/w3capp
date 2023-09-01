<?php
namespace api\block\html;
use w3capp\helper\Str;

/**
 * 静态HTML模块
 */
class HTML extends \api\block\BlockTpl{
	
    protected $content;
	function getPrototypeForm(){
	    return array(
	        "id"=>array("form_input"=>"hidden"),
	        "type"=>array("form_input"=>"hidden","col_name"=>"模块类型","def_value"=>"html\\HTML"),
	        "data_size"=>array("form_input"=>"hidden","def_value"=>1),
	        "mark"=>array("col_name"=>"调用标记","form_input"=>"text"),
	        "remarks"=>array("col_name"=>"说明","form_input"=>"text"),
	        "content"=>array("col_name"=>"静态内容","form_input"=>"textarea"),
	        "update_time"=>array("form_input"=>"hidden","def_value"=>'-1')
	    );
	}

	public function info($column){
        if($column=="content"){
        	return $this->getCache();
        }
        return parent::info($column);
    }
	function onCheckPrototype(&$data){
		if(stripos($data['content'],"<script")===false&&stripos($data['content'],"<iframe")===false){
		    //不让content保存到数据库上
            $this->content=Str::xss_filter($data['content']);
            $data['content']='';
			parent::onCheckPrototype($data);
			return;
		}else{
			$data['error']=1;
			$data['msg']="静态内容包含非法字符";
		}
	}
	public function getPrototype(){
	    $data=parent::getPrototype();
        $data['content']=$this->getCache();
		return $data;
	}
	function onSaved($id){
		parent::onSaved($id);//saveCache
		$this->submitCache($this->content);
	}
}
