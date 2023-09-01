<?php
namespace common\controller;
use cms\model\PageBlock;
use cms\model\SysUser;
use cms\model\Theme;
use common\model\BlockRecord;
use common\model\SiteConfig;
use member\model\Member;
use member\model\MemberGroup;
use w3capp\helper\Str;
use w3capp\Controller;

class W3cEnterCtrl extends Controller{

    protected $template;
	function __construct(){
		$this->site_set=SiteConfig::getConfigs();
		if(stripos($_SERVER['HTTP_USER_AGENT'],'mobile')>0||array_key_exists('mobile_type',$_GET)&&$_GET['mobile_type']=='w3capp'){
			$this->theme_id=$this->site_set['style_mobile'];

			$theme=new Theme(['id'=>$this->theme_id]);
			if(empty($theme->name)){
				throw new \Exception('主题不在：'.$this->theme_id);
			}
            self::$app->template()->setTplDir(W3CA_THEME_TPL.$this->site_set['style_mobile'].'/');
            $this->theme=$theme;
		}else{
            $this->theme_id=$this->site_set['style'];
            $theme=new Theme(['id'=>$this->theme_id]);
			if(empty($theme->name)){
				throw new \Exception('主题不在：'.$this->theme_id);
			}
            self::$app->template()->setTplDir(W3CA_THEME_TPL.$this->site_set['style'].'/');
			$this->theme=$theme;
		}
		
	}
	
	/**
	 * 重载应函数要重新生成模版
	 */
	public function _tpl_const(){
		$const=parent::_tpl_const();
		$const['{P_CLASSIFY}']=$this->pageClass;
        if($this->theme_id){
            $theme=$this->theme;

            if($theme['language']){
                self::$app->template()->setLanguageFile(W3CA_THEME_TPL.$this->theme_id."/".$theme['language']);
            }
            $const['{THEME_PATH}']=W3CA_URL_ROOT."data/theme/".$theme['install_dir']."/";
            if($theme['file_var']){
                $const['{THEME_CSS}']=W3CA_URL_ROOT."data/theme/".$theme['install_dir']."/".$theme['file_var']."theme.css";
            }else{
                $const['{THEME_CSS}']=W3CA_URL_ROOT."data/theme/".$theme['install_dir']."/theme.css?v=".$theme->refresh_var;
            }

            $const['{REF_VAR}']='?v='.$theme->refresh_var;
        }
		return $const;
	}

	function api($bid,$page){
		$blocki=BlockRecord::firstAttr(['mark'=>$bid]);
		$block_t=PageBlock::newBlock($blocki);
		$block_t->page=$page;
		if(self::$app->$hold_response){
			self::$app->setResponse(200,["Content-Type"=>"text/html; charset=".W3CA_DB_CHAR_SET],$block_t->content());
		}else{
			header("Content-Type:text/html; charset=".W3CA_DB_CHAR_SET);
			echo $block_t->content();
		}
	}

	protected function _tpl($tpl){
	    $view = new \W3cUI($tpl);
	    $user=SysUser::getLoginUser();
	    $view->edit_enabled=$user->id?$user->hasSpecifyRights("{page_edit}"):false;
	    if(false==empty($_POST['open_page'])&&$_POST['open_page']=='y'){
	        //返回模板编译信息
	        $view->return_page=true;
        }
        self::$app->template()->setPageBlockManager(new PageBlock());
	    return $view;
	}
	public function _query($key,$default){
		if($key=="login_member"){
			$login_member=Member::loginMember();
			if($login_member){
				$group_info=MemberGroup::groupInfo($login_member['groupid']);
				$login_info=$login_member->getAttributes();
				$login_info['group_name']=$group_info['name'];
				unset($login_info['password']);
				unset($login_info['salt']);
				$this->_assign("group",$group_info);
				return $login_info;
			}
		}
		return parent::_query($key,$default);
	}
    public function _init_block($info){
        return PageBlock::newBlock($info);
    }
    function _show_message($msg,$type,$links,$widthHtml=false,$out_index=-1){
        $v=$this->_tpl("common/message");
        $v->message=$widthHtml?$msg:Str::htmlchars($msg);
        $v->class_type=$type;
        if(is_array($links)){
            $v->link_list=$links;
            $v->out_index=$out_index;
        }else{
            $v->time_out_link=$links;
            $v->out_index=0;
        }
        $v->widthHtml=$widthHtml;
        $v->output();
    }

    public function _check_operation($funName){
	    if(false==empty($_POST)&&empty($_POST['form_hash'])&&empty($_GET['form_hash'])){
	        return false;
        }
	    return parent::_check_operation($funName);
    }
}
