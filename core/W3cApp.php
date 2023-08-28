<?php
namespace w3capp;
use W3cApp\helper\Str;

require_once W3CA_MASTER_PATH.'core/W3cAppDataApi.php';
require_once W3CA_MASTER_PATH . 'core/W3cUI.php';
require_once W3CA_MASTER_PATH.'core/W3cController.php';
/**
 * Class W3CApp
 * @property static Controller $instance
 */
class W3cApp{
	/**
	 * 系统管理员
	 */
	static $admin=1;
	/**
	 * 指定动作url规则,用$x指第x个参数
	 */
	protected static $action_rule=array();
	/**
	 * 控制器名称
	 */
	public static $ctrl_name;
    /**
     * @var 控制器所在目录
     */
	public static $ctrl_app;
	/**
	 * 是否开启伪静态
	 */
	public static $rewriteurl;
	/**
	 * 默认的控制器
	 */
	public static $default_ctrl;
    public static $default_app="cms";
	/**
	 * 入口文件
	 */
	public static $entrance;
	/**
	 * 控制器实例
     * @var \W3cControler
	 */
	public static $instance;
	/**
	 * 控制器匹配请求字符串的对应表
	 */
	public static $ctrl_table=array();
	/**
	 * 处理字符
	 */
	public static $action="";
	public static $install_config;
    /**
     * @var 数据库设置
     */
	public static $db_config;
	private static $wtpl;
    //W3cAppSession
    private static $session;
    public static $holder_response=false;
    private static $response;
    private static $cookies;
    const URI_KEY="g";
	static function template(){
        if(self::$wtpl==null){
            require_once W3CA_MASTER_PATH."core/W3cTemplate.php";
            self::$wtpl=new W3cTemplate();
        }
        return self::$wtpl;
    }
    /**
     * @param array $set [key,value,keep_seconds,path]
     */
    public static function setCookie($set){
        if(empty($set[0]))return;
        if(empty($set[1]))$set[1]="";
        if(empty($set[2]))$set[2]=0;
        if(empty($set[3]))$set[3]="";
        if(!self::$holder_response){
            if(empty($set[1]))$set[1]="";
            @setcookie($set[0],$set[1],$set[2]>0?($set[2]+time()):$set[2],$set[3]);
            return;
        }
        if(!self::$cookies){
            self::$cookies=[$set[0]=>$set];
        }else{
            self::$cookies[$set[0]]=$set;
        }
    }
    public static function getCookies(){
        return self::$cookies;
    }
    //
    public static function getSession(){
        if(!self::$session)self::$session=new W3cAppSession();
        return self::$session;
    }
    //会清cookie和response
    public static function setSession($s){
        self::$cookies=[];
        self::$session=$s;
        self::$response=null;
    }
	protected static function includeCtrl($ctrl_to){
	    list($n,self::$ctrl_app)=explode("\\",$ctrl_to);
		self::$action="index";
		return $ctrl_to;
	}
    public static function setResponse($status,$header,$contents){
        self::$response=[$status,$header,$contents];
    }
    public static function getResponse(){
        return self::$response;
    }
    /**
     * 如果已开启会话或开启成功就返回session_id否则返回false
     * @param null $session_id
     * @return bool|string
     */
	public static function startSession($session_id=null){
        return self::getSession()->start($session_id);
    }
	/**
	 * 分析url路径,取得要实例化的控制器
	 * @return 返回控制器类名
	 */
	protected static function pathParse($req_uri){

		if($req_uri==""){
			$ctrl_name=self::$default_ctrl;
            self::$action=["index"];
		}else{
			foreach (self::$ctrl_table as $key => $ctrl_to) {
				if($req_uri==$key||strpos($req_uri,$key)===0){
					return self::includeCtrl($ctrl_to);
				}
				if($key{0}=='/'){
                    //正则表达式的处理
                    $mi=preg_match($key, $req_uri,$match_rs);
                    if($mi>0){
                        if(strpos($ctrl_to, "$")){
                            $m2=array();
                            foreach ($match_rs as $mk => $mv) {
                                $m2["$".$mk]=$mv;
                            }
                            $ctrl_to=strtr($ctrl_to,$m2);
                        }
                        return self::includeCtrl($ctrl_to);
                    }
                }

			}
            $dir_path=explode("/",$req_uri);
            if(is_dir(W3CA_MASTER_PATH."app/".$dir_path[0])){
                if(empty($dir_path[1]))$dir_path[1]='main';
                self::$ctrl_app=$dir_path[0];
                $ctrl_name=$dir_path[0].'\\controller\\'.$dir_path[1]."Ctrl";
                if(include_once W3CA_MASTER_PATH.'app/'.array_shift($dir_path).'/'.$dir_path[0]."Ctrl.php"){
                    array_shift($dir_path);
                    self::$action=$dir_path;
                }else{
                    $ctrl_name=self::$ctrl_app.'\\controller\\mainCtrl';
                    require_once W3CA_MASTER_PATH.'app/'.self::$ctrl_app.'/mainCtrl.php';
                    self::$action=$dir_path;
                }
            }else{
                $name=array_shift($dir_path);
                $ctrl_file=W3CA_MASTER_PATH.'app/'.static::$default_app.'/'.$name."Ctrl.php";
                self::$ctrl_app=static::$default_app;
                //ctrl_url
                //
                if(false==file_exists($ctrl_file)){
                    //\W3cUI::show404();
                    self::$action=["index",$req_uri];
                    return self::$default_ctrl;
                }
                include_once $ctrl_file;
                self::$action=$dir_path;
                return "\\cms\\controller\\".$name."Ctrl";
            }
		}

        if(self::$ctrl_app=="common"){
            die("Cannot instance common controller!");
        }
        if(self::$ctrl_app=="console"){
            die("Cannot instance console controller!");
        }
		return $ctrl_name;
	}
	public static function setConfig($config){
        self::$install_config=$config;
        self::$db_config=$config['db_config'];
        self::$rewriteurl=$config['open_rewrite'];
    }
    /**
     * 没有$_GET[urikey]全局变量时使用$request_uri
     */
    public static function getUri($request_uri){
        //self::$rewriteurl&&
        if(empty($_GET[self::URI_KEY])){
            //处理子目录对uri的影响
            $app_len=strlen(self::$install_config['app_dir']);
            $sub_str=substr($request_uri,0,$app_len);
            if(self::$install_config['app_dir']==$sub_str){
                $req_uri=substr($request_uri,$app_len);
            }else{
                $req_uri=$request_uri;
            }
            $req_uri=trim($req_uri,'/');
            if(strpos($req_uri,W3CA_URL_ROOT)===0) {
                $req_uri=substr($req_uri,strlen(W3CA_URL_ROOT));
            }
            $search_=strpos($req_uri,"?");
            if($search_!==FALSE) {
                $req_uri=trim(substr($req_uri,0,$search_),'/');
            }
            return $req_uri;
        }else{
            return $_GET[self::URI_KEY];
        }
    }
	/**
	 * 加载控制器,实例化,并执行
	 * @param $uri 可指定控制器和路由
	 */
	public static function start($uri=""){
        if(!self::$default_ctrl)
            self::$default_ctrl=W3CA_DEF_CTRL;
		//POST处理自动转义
		if(get_magic_quotes_gpc())
		{
		    foreach ($_POST as &$val){
		        $val=stripslashes($val);
		    }
		}

		if(empty($uri)){
            $uri=self::getUri($_SERVER['REQUEST_URI']);
        }
        $ctrlname=static::pathParse($uri);
		self::$instance=new $ctrlname();
        self::$ctrl_name=$ctrlname;
		$arg=self::$instance->_action_routing(self::$action);
        $fun=trim(array_shift($arg),"_");
        self::$instance->action=$fun;
		if(self::$instance->_check_operation($fun)&&method_exists(self::$instance, $fun)){
            if(self::$holder_response&&self::$response){
                return ;
            }
            $crm=new ReflectionMethod(self::$instance,$fun);
            $farg=$crm->getParameters();
            if(count($farg)<count($arg)){
                return W3cUI::show404();
            }
            if($crm->isPublic()){
                return $crm->invokeArgs(self::$instance,$arg);
            }else{
                return W3cUI::show404();
            }

			//call_user_func_array(array(self::$instance,$fun),$arg);
		}else{
			self::$instance->_action_unfound($fun,$arg);
		}
	}
	
	
	/**
	 * 加载类
	 */
	static function loadClass($classn){
	    if(strpos($classn,'\\controller\\')){
            require_once W3CA_MASTER_PATH.'app/'.str_replace("\\controller\\",'/',$classn).".php";
        }else if(strpos($classn,'\\model\\')){
            include_once W3CA_MASTER_PATH.'app/'.str_replace("\\model\\",'/model/',$classn).".php";
        }else if(strpos($classn,'\\block\\')){
            include_once W3CA_MASTER_PATH.'app/'.str_replace("\\",'/',$classn).".php";
        }else if(strpos($classn,'w3capp\\')===0){
		    $file=W3CA_MASTER_PATH.strtr($classn,['w3capp\\'=>'core/',"\\"=>"/"]).".php";
            require_once $file;
        }else if(strpos($classn,'\\W3c')===0){
            $file=W3CA_MASTER_PATH.'core/'.trim(str_replace("\\","/",$classn),"\\").".php";
            require_once $file;
        }

	}
    /**
     * 到达Controler的路径
     */
    static function route($url,$url_param=null){

        if(self::$rewriteurl)
            return W3CA_URL_ROOT.$url.($url_param?'?'.http_build_query($url_param):'');
        else
            return W3CA_URL_ROOT.self::$entrance."?g=".$url.($url_param?'&'.http_build_query($url_param):'');
    }
}
