<?php
namespace member\controller;

/**
 * 
 */
class favoriteCtrl extends \common\controller\MemberEnterCtrl {
	
	function index($a=null) {
		$view=$this->_tpl("member/favorite");
		$view->output();
	}
}
