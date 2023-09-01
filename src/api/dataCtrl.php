<?php 
namespace api\controller;
use api\model\ApiDoc;
use cms\model\PageBlock;
use common\model\BlockRecord;
use common\controller\W3cEnterCtrl;
class dataCtrl extends W3cEnterCtrl{
    public function json($mark,$args){
        $block_info=BlockRecord::firstAttr(["mark"=>$mark]);
        if(empty($block_info))
            return $this->_json_return([]);
        else
            return $this->_json_return(PageBlock::newBlock($block_info)->load_data($args));
    }
    public function html($mark,$args){
        $block_info=BlockRecord::firstAttr(["mark"=>$mark]);
        if(!empty($block_info))
            PageBlock::newBlock($block_info)->display($args);
    }
	public function test(){
		$apd=new ApiDoc();
		$apd->display();
	}
	public function tx(){
        echo self::check_form_hash($_GET['hash'],60);
    }
}