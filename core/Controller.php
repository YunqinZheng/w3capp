<?php
namespace w3capp;
use w3capp\helper\Str;
class Controller extends Core {

	/**
	 * 检查是否有权限
	 */ 
	public function _check_operation($funName){
		return true;
	}
	public function index($arg=null){
		if(self::$app->holder_response){
			self::$app->setResponse(200,["Content-Type:text/html;charset=".W3CA_DB_CHAR_SET],"<b>Welcome to W3CApp!</b>");
		}else{
			echo "<b>Welcome to W3CApp!</b>";
		}
		
	}
	/**
	 * 获取控制器内部的路由规则
	 */ 
	public function _action_routing($uri){
		if(empty($uri)){
			return array('index');
		}else {
			return $uri;
			
		}
	}
	
	/**
	 * 模板基本的替规则
	 * 重载应函数要重新生成模版
	 */
	public function _tpl_const(){
		return array(
		"{ACTION}"=>$this->action,
		"{CHAR_SET}"=>W3CA_DB_CHAR_SET,
		'{URL_ROOT}'=>self::$app->getConfig("url_dir"),
		'{THEME_PATH}'=>'',
        '{REF_VAR}'=>'',
		'{APP_PATH}'=>self::$app->route(""),
        '{GV_INPUT}'=>self::$app->rewriteurl?'':'<input name="g" value="<?php echo urlencode($_GET[\'g\']) ?>" type="hidden"/>',
		'{?||&}'=>self::$app->rewriteurl?'?':'&',
		'{&||?}'=>self::$app->rewriteurl?'&':'?',
		'/*?'=>'<?php ','?*/'=>' ?>','<!--?'=>"<?php ","?-->"=>" ?>");
	}


    /**
     * 初始化模块
     * @param $info
     * @return null
     */
	public function _init_block($info){
	    return null;
    }
    /**
     * 显示模块
     */
	public function _display_block($block,$arg){
        if($block)
	       $block->display($arg);
	}
	public function _action_unfound($fun,$arg){
		UI::show404();
	}
    protected function _view_return($view,$tpl_mark="main"){
        UI::outputInside($view,$tpl_mark);
    }
	protected function _json_return($error,$message="",$data=null){

		if($data instanceof W3cAppDataApi){
			$content=\w3c\helper\Str::toJson(array('error'=>$error,"message"=>$message,"data"=>$data->toArray()));
		}else
			$content=\w3c\helper\Str::toJson(array('error'=>$error,"message"=>$message,"data"=>$data));
		if(self::$app->holder_response){
			self::$app->setResponse(200,["Content-Type"=>"application/json; charset=".W3CA_DB_CHAR_SET],$content);
		}else{
			header("Content-Type: application/json; charset=".W3CA_DB_CHAR_SET);
			echo $content;
			exit;
		}
	}

    function _show_message($msg,$type,$links,$widthHtml=false,$out_index=-1){
        $v=$this->_tpl("common/message");
        $v->message=$widthHtml?$msg:Str::htmlchars($msg);
        $v->class_type=$type;
        $v->link_list=$links;
        $v->widthHtml=$widthHtml;
        $v->out_index=$out_index;
        $v->output();
    }
    function _message($msg,$type="error"){
        $this->_show_message($msg,$type,[]);
    }
	//$msg 为null时直接跳转
    function _referer_to($msg,$url="",$type="error"){
		if($msg==null&&$url){
			if(self::$app->holder_response){
				self::$app->setResponse(302,["Location"=>$url],"");
			}else{
				header("Location: ".$url);
				exit;
			}
		}else
        	$this->_show_message($msg,$type,array(array("href"=>$url?$url:$_SERVER['HTTP_REFERER'],"text"=>"返回")),false,0);
    }
    protected function _tpl($tpl)
    {
        if(self::$app->ctrl_app){
            $app_view_path=W3CA_MASTER_PATH.'app/'.self::$app->ctrl_app.'/view/';
            self::$app->template()->setTplDir($app_view_path);
        }
        $view = new UI($tpl);
        return $view;
    }
}
