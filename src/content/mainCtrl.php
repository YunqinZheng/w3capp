<?php
namespace content\controller;

use cms\model\PageBlock;
use common\model\Channel;
use cms\model\ContentType;

class mainCtrl extends \cms\controller\mainCtrl{
    protected $content_type;
    protected $tpl_form;
    function _index($ctt)
    {
        $type=ContentType::record(['content_mark'=>$ctt]);
        if(empty($type)){
            return $this->_referer_to("内容模型不在!");
        }
        $ctpinfo=$type->getAttributes();
        \self::$app->template()->setPageBlockManager(new PageBlock());
        $html=$this->_tpl($ctt."_list");
        if(empty($_GET['page'])&&empty($_POST['page'])){
            $page=1;
        }else{
            $page=false==empty($_POST)?intval($_POST['page']):intval($_GET['page']);
        }
        $this->_assign("page",$page);
        if(empty($_GET['search'])&&empty($_POST['search'])) {
            $_POST['search']=$_GET['search']='';
        }
        $html->content_type=$ctpinfo['type_name'];
        $html->ctiden=$ctt;
        $html->output();
    }
    function _action_unfound($fun,$args){
        $channel_list=Channel::findAllData(["frame_mod"=>$fun,'be_publish'=>1]);
        $chlist=[];
        foreach ($channel_list as $item){
            $chlist[$item['id']]=$item['ch_name'];
        }
        $this->_assign("channels",$chlist);
        $this->_index($fun,empty($args[0])?null:$args[0]);
    }
    function _tpl_const(){
        $const=parent::_tpl_const();
        if($this->tpl_form){
            $const['{CONTENT_TYPE}']=$this->content_type;
            $const['{CONTENT_FORM}']=$this->tpl_form;
        }
        return $const;
    }
    public function _init_block($info){
        return PageBlock::newBlock($info);
    }
}