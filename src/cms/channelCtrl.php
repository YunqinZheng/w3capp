<?php
namespace cms\controller;
use common\model\Channel;
use common\model\SiteNavigation;
use cms\model\ContentType;

class channelCtrl extends mainCtrl{
    private $cids;
	/**
	 * 栏目列表
	 */
	function index($a=null){
	    $this->cids=array();
		$html=$this->_tpl("web/channel");
		$chm=new Channel();
		$html->channels=$chm->treeList();
		$html->output();
	}
    function tpl_code(){
        $info=Channel::tplInfo($_POST['file']);
        if($info){
            return $this->_json_return(0,"",$info);
        }else{
            return $this->_json_return(1,"文件不存在或无法编辑");
        }
    }
    function save_tpl(){
	    if(empty($_POST['code'])){
            return $this->_json_return(1,"模板内容不能为空");
        }
        if(preg_match("/[\\:\\\\\\/#\\*\\?]/",$_POST['file'])||strpos($_POST['file'],"list_")!==0&&strpos($_POST['file'],"view_")!==0){
            return $this->_json_return(1,"模板文件名不合要求");
        }
        $rs=Channel::saveTpl($_POST['file'],$_POST['code']);
        return $this->_json_return($rs?0:1);
    }
	/**
	 * 提交栏目列表
	 */
	function sub_update(){
        $up_data=[];
		foreach ($_POST['cid'] as $cid) {
			if($_POST['ch_name_'.$cid]){
                $data=array("ch_name"=>$_POST['ch_name_'.$cid],
                    "order_val"=>intval($_POST['order_'.$cid]),
                    "hidden"=>intval($_POST['hidden_'.$cid])
                );
				if(array_key_exists('innav_'.$cid,$_POST)){
                    $data['innav']=intval($_POST['innav_'.$cid]);
				    if($data['innav']){
                        SiteNavigation::saveChannelNav($cid, $_POST['ch_name_'.$cid], \W3cApp::route($_POST['path_'.$cid]),1);
                    }else{
                        SiteNavigation::deleteChannelNav($cid);
                    }
				}
                $up_data[]=[$data,['id'=>intval($cid)]];
			}
		}
        Channel::batchUpdate($up_data);
        return $this->_referer_to("修改已经提交","","right");
	}
	/**
	 * 添加频道栏目
	 */
	function add_channel(){
	    $channel=new Channel();
		if($_POST['ch_name']){
			if(!$_POST['path']){
                return $this->_referer_to("目录路径不能为空");
			}
            $channel->setAttributes(array("ch_name"=>$_POST['ch_name'],"pic"=>$_POST['pic'],"keywords"=>$_POST['keywords'],"description"=>$_POST['description'],
                "frame_mod"=>$_POST['frame_mod'],"path"=>trim($_POST['path'],"/\t\n\r\0\x0B"),'static_path'=>$_POST['static_path'],"list_tpl"=>$_POST['list_tpl'],"view_tpl"=>$_POST['view_tpl'],"pid"=>$_POST['pid'],
                "be_publish"=>intval($_POST['be_publish']),"innav"=>intval($_POST['innav']),"hidden"=>intval($_POST['hidden']),"order_val"=>intval($_POST['order_val'])));
			if($channel->save()){
                $cid=$channel->primary();
				if($_POST['innav'])
                    SiteNavigation::saveChannelNav($cid, $_POST['ch_name'], \W3cApp::route($_POST['path']),$_POST['innav']);
                return $this->_referer_to("添加成功！",$_POST['sub_t']==1?\W3cApp::route("channel/index"):"","right");
			}else{
                return $this->_referer_to("添加失败！");
			}
		}elseif(false==empty($_POST)){
            return $this->_referer_to("栏目名不能为空！");
		}
		$html=$this->_tpl("web/channel_input");
		$html->title="添加栏目";
		$html->chnn_tree=$channel->getChannels(0,"");
		$content_types=ContentType::myAdapter()->fetchArray("content_mark,type_name","",'',100);
		//print_r($content_types);exit;
        $html->content_types=$content_types;
        $html->tpl_files=Channel::contentTpls();
		$html->output();
	}
	function edit_channel($id){
		$cnnm=new Channel(['id'=>intval($id)]);
		
		if($_POST['ch_name']){
			if(!$_POST['path']){
                return $this->_referer_to("目录路径不能为空");
			}
		    $data=array("ch_name"=>$_POST['ch_name'],"pic"=>$_POST['pic'],"keywords"=>$_POST['keywords'],"description"=>$_POST['description'],
		        "frame_mod"=>$_POST['frame_mod'],"path"=>trim($_POST['path'],"/\t\n\r\0\x0B"),'static_path'=>$_POST['static_path'],"list_tpl"=>$_POST['list_tpl'],"view_tpl"=>$_POST['view_tpl'],"pid"=>$_POST['pid'],
		        "be_publish"=>intval($_POST['be_publish']),"innav"=>intval($_POST['innav']),"hidden"=>intval($_POST['hidden']),"order_val"=>intval($_POST['order_val']));
            $cnnm->setAttributes($data);
			if($cnnm->save()){
				if($_POST['innav']){
                    SiteNavigation::saveChannelNav($cnnm->id, $_POST['ch_name'], \W3cApp::route($_POST['path']),$_POST['innav']);
                }else{
                    SiteNavigation::deleteChannelNav($cnnm->id);
				}
				$this->_referer_to("修改成功！",\W3cApp::route("channel/index"),"right");
				return;
			}
            return $this->_referer_to("栏目修改失败！");
		}
		$html=$this->_tpl("web/channel_input");
		$html->title="编辑栏目";
		$html->edit_data=$cnnm->getAttributes();
		$html->chnn_tree=$cnnm->getChannels(0,$cnnm->id);

		$html->content_types=ContentType::myAdapter()->fetchArray("content_mark,type_name",[],'',100);
		$html->tpl_files=Channel::contentTpls();
		$html->output();
	}
	function view_channel($id){
		$cnnm=Channel::record(['id'=>$id]);
		if(empty($cnnm)){
		    echo "内容模型不存在！";
		}else{
            return $this->_referer_to(null,($cnnm['static_path']?$cnnm['static_path']:\W3cApp::route($cnnm['path'])));
		}
	}
	function del_channel($ids){
		if(Channel::deleteAll(['id'=>explode(",",$ids)])){
            return $this->_referer_to("删除成功!",\W3cApp::route("channel"),"right");
		}else{
            return $this->_message("删除出错!");
		}
		$aids=explode(",", $ids);
		foreach ($aids as $id) {
            SiteNavigation::deleteChannelNav($id);
		}
		$this->index();
	}
	
}
