<?php
namespace w3capp;
class InstallConfig implements \ArrayAccess {
    protected $config=array();
    public function __construct()
    {
        extension_loaded('openssl') or die('未启用 OPENSSL 扩展');
        $this->config['error_level']=64|256|1|4|16;
        $this->config['timezone']='Asia/Shanghai';//Etc/GMT-8
        if(empty($_COOKIE['install_random_key'])){
            $this->config['random_key']=dechex(time()%100000+rand(1000,9000));
            setcookie('install_random_key',$this->config['random_key'],0,"/");
        }else{
            $this->config['random_key']=$_COOKIE['install_random_key'];
        }
        //创建必要的目录
        $mkdirs=[W3CA_PATH.'data/',W3CA_PATH.'data/inc/editarea/',W3CA_PATH.'data/cache/template/',W3CA_PATH.'data/cache/block/'
        ,W3CA_PATH."data/store/0/",W3CA_PATH."data/material",W3CA_PATH."data/member/original_img/",W3CA_PATH.'data/member/avatar/',W3CA_PATH.'data/theme/'];
        foreach($mkdirs as $dir){
            if(false==is_dir($dir)&&false==mkdir($dir,0777,true)||false==is_writable($dir)){
                die ("$dir need write able");
            }
            file_put_contents($dir."index.html","<!-- empty page www.w3capp.com -->");
        }
        $this->config['register_key']=uniqid();
        $this->config['register_iv']="www.w3capp.com/v";
        if(empty($_SERVER['REQUEST_URI'])){
            //die("request install.php in web");
			$this->config['url_dir']="./";
        }else if(strpos($_SERVER['REQUEST_URI'],"/install.php")>0){
            list($this->config['url_dir'])=explode("install.php",$_SERVER['REQUEST_URI']);
        }else{
            $this->config['url_dir']="/";
        }

        $this->config['open_rewrite']=0;
    }
    public function offsetExists($offset){
        return array_key_exists($offset,$this->config);
    }
    public function offsetGet($offset){
        return $this->config[$offset];
    }
    public function offsetSet($offset, $value){
        $this->config[$offset]=$value;
    }
    public function offsetUnset($offset){
        unset($this->config[$offset]);
    }
    public function __get($name)
    {
        return $this->config[$name];
    }
    public function __isset($name)
    {
        return empty($this->config[$name]);
    }
    public function __set($name, $value)
    {
        $this->config[$name]=$value;
    }
    public function save($db_config,$db_type,$db_adapter){
        $this->config['db_config'][$db_type][]=$db_config;
        //$this->config['db_default']=$db_type;
        $this->config['db_adapter']=$db_adapter;
        return file_put_contents(W3CA_PATH."data/install.config.php",'<?php return '.var_export($this->config,true).";");
    }

}