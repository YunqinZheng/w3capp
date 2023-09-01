<?php
namespace w3capp;
use W3cApp\helper\Str;


/**
 * Class W3CApp
 * @property string $ctrl_name 控制器名称
 * @property string $ctrl_app 控制器所在目录
 * @property string $default_app 默认app目录
 * @property string $entrance 入口文件
 * @property Controller $instance 控制器实例
 * @property array $actions 控制器方法与参数
 * @property int $utc_time start的时间
 */
class W3cAppCom{

	public $ctrl_name;
	public $ctrl_app;
    public $default_app="cms";
	public $entrance;
	public $instance;
	public $actions;
    public $holder_response=false;
	public $utc_time;

	//全部配置信息
	private $config;
    //数据库设置
	private static $db_config;
	//模板类
	private static $wtpl;
    //W3cAppSession
    protected $session;
    protected $response;
    protected $cookies;
    protected $uri_key="g";
	public function __construct(){
		
		Core::$app=$this;
		$this->config=$this->initConfig();
		self::$db_config=$this->config['db_config'];
	}
	protected function initConfig(){
		return require(W3CA_MASTER_PATH."data/install.config.php");
	}
	
	/**
	 * 是否开启伪静态
	 */
	public function rewriteurl(){
		return $this->getConfig("open_rewrite");
	}
	/**
	 * 默认的控制器
	 */
	protected function defaultCtrl(){
		return "\\".$this->default_app."\\mainCtrl";
	}
	//返回模板类
	public function template(){
        if(self::$wtpl==null){
            require_once __DIR__."/Template.php";
            self::$wtpl=new Template();
        }
        return self::$wtpl;
    }
    /**
     * @param array $set [key,value,keep_seconds,path]
     */
    public function setCookie($set){
        if(empty($set[0]))return;
        if(empty($set[1]))$set[1]="";
        if(empty($set[2]))$set[2]=0;
        if(empty($set[3]))$set[3]="";
        if(!$this->holder_response){
            if(empty($set[1]))$set[1]="";
            @setcookie($set[0],$set[1],$set[2]>0?($set[2]+time()):$set[2],$set[3]);
            return;
        }
        if(!$this->cookies){
            $this->cookies=[$set[0]=>$set];
        }else{
            $this->cookies[$set[0]]=$set;
        }
    }
    public function getCookies(){
        return $this->cookies;
    }
    
    public function getSession(){
        if(!$this->session)$this->session=new W3cAppSession();
        return $this->session;
    }
    //会清cookie和response
    public function setSession($s){
        $this->cookies=[];
        $this->session=$s;
        $this->response=null;
    }
    /**
     * 如果已开启会话或开启成功就返回session_id否则返回false
     * @param null $session_id
     * @return bool|string
     */
	public function startSession($session_id=null){
        return $this->getSession()->start($session_id);
    }
    public function setResponse($status,$header,$contents){
        $this->response=[$status,$header,$contents];
    }
    public function getResponse(){
        return $this->response;
    }

	/**
	 * 分析url路径,取得要实例化的控制器
	 * @return 返回控制器类名
	 */
	protected function pathParse($req_uri){

		if($req_uri==""){
			$ctrl_name=$this->defaultCtrl();
            $this->actions=["index"];
		}else{
			if($req_uri=="index.php"||$req_uri=="index.html"){
				$this->actions=["index"];
				return $this->defaultCtrl();;
			}
            $dir_path=explode("/",$req_uri);
            if(is_dir(W3CA_MASTER_PATH."app/".$dir_path[0])){
                if(empty($dir_path[1]))$dir_path[1]='main';
                $this->ctrl_app=$dir_path[0];
                $ctrl_name=$dir_path[0].'\\controller\\'.$dir_path[1]."Ctrl";
                if(include_once W3CA_MASTER_PATH.'app/'.array_shift($dir_path).'/'.$dir_path[0]."Ctrl.php"){
                    array_shift($dir_path);
                    $this->actions=$dir_path;
                }else{
                    $ctrl_name=$this->ctrl_app.'\\controller\\mainCtrl';
                    require_once W3CA_MASTER_PATH.'app/'.$this->ctrl_app.'/mainCtrl.php';
                    $this->actions=$dir_path;
                }
            }else{
                $name=array_shift($dir_path);
                $ctrl_file=W3CA_MASTER_PATH.'app/'.$this->default_app.'/'.$name."Ctrl.php";
                $this->ctrl_app=$this->default_app;
                //ctrl_url
                //
                if(false==file_exists($ctrl_file)){
                    $this->actions=["index",$req_uri];
                    return $this->defaultCtrl();
                }
                include_once $ctrl_file;
                $this->actions=$dir_path;
                return "\\cms\\controller\\".$name."Ctrl";
            }
		}

        if($this->ctrl_app=="common"){
            die("Cannot instance common controller!");
        }
        if($this->ctrl_app=="console"){
            die("Cannot instance console controller!");
        }
		return $ctrl_name;
	}
	public function getConfig($key){
		if(empty($this->config[$key])){
			return null;
		}
		return $this->config[$key];
	}
	public function setConfig($config,$v=""){
		if($v&&is_string($config)){
			$this->config[$config]=$v;
		}else if(is_array($config)){
			$this->config=$config;
			self::$db_config=$config['db_config'];
		}
    }
    /**
     * 没有$_GET[urikey]全局变量时使用$request_uri
     */
    public function getUri(){

        if(empty($_GET[$this->uri_key])){
			$request_uri=$_SERVER['REQUEST_URI'];
            //处理子目录对uri的影响
            $app_len=strlen($this->getConfig('url_dir'));
            $sub_str=substr($request_uri,0,$app_len);
            if($this->getConfig('url_dir')==$sub_str){
                $req_uri=substr($request_uri,$app_len);
            }else{
                $req_uri=$request_uri;
				if(strpos($req_uri,$this->getConfig('url_dir'))===0) {
					$req_uri=substr($req_uri,$app_len);
				}
            }
            $req_uri=trim($req_uri,'/');
            
            $search_=strpos($req_uri,"?");
            if($search_!==FALSE) {
                $req_uri=trim(substr($req_uri,0,$search_),'/');
            }
            return $req_uri;
        }else{
            return $_GET[$this->uri_key];
        }
    }
    /**
     * 到达Controler的路径
     */
    public function route($url,$url_param=null){

        if($this->rewriteurl())
            return $this->getConfig("url_dir").$url.($url_param?'?'.http_build_query($url_param):'');
        else
            return $this->getConfig("url_dir").$this->entrance."?".$this->uri_key."=".$url.($url_param?'&'.http_build_query($url_param):'');
    }
	/**
	 * 加载控制器,实例化,并执行
	 * @param $uri 可指定控制器和路由
	 */
	public function start($uri=""){
		$this->run_time=time();
		//POST处理自动转义
		if(get_magic_quotes_gpc())
		{
		    foreach ($_POST as &$val){
		        $val=stripslashes($val);
		    }
		}

		if(empty($uri)){
            $uri=$this->getUri();
        }
        $ctrlname=$this->pathParse($uri);
		$this->instance=new $ctrlname();
        $this->ctrl_name=$ctrlname;
		$arg=$this->instance->_action_routing($this->actions);
        $fun=trim(array_shift($arg),"_");
        $this->instance->action=$fun;
		if($this->instance->_check_operation($fun)&&method_exists($this->instance, $fun)){
            if($this->holder_response&&$this->response){
                return ;
            }
            $crm=new \ReflectionMethod($this->instance,$fun);
            $farg=$crm->getParameters();
            if(count($farg)<count($arg)){
                return UI::show404();
            }
            if($crm->isPublic()){
                return $crm->invokeArgs($this->instance,$arg);
            }else{
                return UI::show404();
            }

			//call_user_func_array(array($this->instance,$fun),$arg);
		}else{
			$this->instance->_action_unfound($fun,$arg);
		}
	}
	
}
