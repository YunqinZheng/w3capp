<?php
namespace cms\controller;
//use cms\model\IpCheck;
use common\model\Channel;
use cms\model\ContentType;
use common\controller\W3cEnterCtrl;
/**
 * 网站页面父类
 */
class webCtrl extends W3cEnterCtrl{

	/**
	 * 内容ID
	 */
	protected $cntid;
	protected $pageClass;
	/**
	 * 页面块
	 */
	protected $page_blocks=array();
	
	function index($a=null){
        if(!empty($a)&&$a!="index"&&$a!="index.php"&&$a!="index.html"){
            return \W3cUI::show404();
        }
		$this->pageClass="index";
		$html=$this->_tpl("index");
		$html->nav_key="S0";
		$html->putString("title",$this->site_set['web_name']);
		$html->putString("keyword",$this->site_set['web_keywords']);
		$html->putString("description",$this->site_set['description']);
		$html->output();
	}
	/**
	 * 列表频道
	 */
	protected function _channel(){
		$this->pageClass="channel";
		if($this->channel['static_path']){
		    header("Location:".$this->channel['static_path']);
		    return;
		}
		
		if(!$this->channel['list_tpl']){
			$this->_referer_to('你的栏目没有指定模板');
			return;
		}
		$html=$this->_tpl("content/".$this->channel['list_tpl']);
		
		$html->putString("title",$this->channel['ch_name'].'-'.$this->site_set['web_name']);
		$html->putString("keyword",$this->channel['keywords']?$this->channel['keywords']:$this->site_set['web_keywords']);
		$html->putString("description",$this->channel['description']?$this->channel['description']:$this->site_set['description']);
		
		$html->nav_key="C".$this->channel['id'];
		$html->output();
	}
	/**
	 * 内容
	 */
	protected function _view($cntid){
		$this->pageClass="content";
		$content_record=ContentType::contentRecord($this->channel['frame_mod'],$cntid);
		if(empty($content_record['id'])){
			return \W3cUI::show404();
		}
		if($content_record['channel_id']>0&&$content_record['channel_id']!=$this->channel['id']){
            return $this->_referer_to("访问路径不正确");
		}
		$html=$this->_tpl("content/".$this->channel['view_tpl']);
		$html->content=$content_record;
		
		if($html->content['title'])
			$html->putString("title",$html->content['title']);
		else
			$html->putString("title",$this->channel['ch_name'].'-'.$this->site_set['web_name']);
		if($html->content['keywords'])
			$html->putString("keyword",$html->content['keywords']);
		else
			$html->keyword=$this->channel['keywords']?$this->channel['keywords']:$this->site_set['web_keywords'];
		if($html->content['description'])
			$html->putString("description",$html->content['description']);
		else
			$html->putString("description",$this->channel['description']?$this->channel['description']:$this->site_set['description']);
		
		$html->nav_key="C".$this->channel['id'];
		$html->output();
	}
	public function _tpl_const()
    {
        $data= parent::_tpl_const();
        if($this->channel){
            $data['{CONTENT_RECORD}']=$this->channel['frame_mod'];
            $data['{CHANNEL_PATH}']=$this->channel['path'];
        }
        return $data;
    }
    public function display($fun){
        $this->_route($fun,'');
    }
    public function _route($fun,$arg){
        $ch=Channel::record(["path"=>$fun]);
		if(empty($ch)){
		    if(preg_match("/(.+)\\/(\\d+)\\.html$/",$fun,$m)){
		        //内容页
                return $this->_route($m[1],[$m[2]]);
            }
            if(preg_match("/(.+)\\-page\\-(\\d+)\\.html$/",$fun,$m)){
                //列表分页
                $this->page_index=$m[2];
                return $this->_route($m[1]);
            }
        }
		if(empty($ch)||$ch['hidden']){
            return \W3cUI::show404();
        }
        $this->channel=$ch;
		if($this->channel['id']){
			if($arg){
				$this->_view($arg[0]);
			}else{
				$this->_channel();
			}
			return;
		}
	}
}
