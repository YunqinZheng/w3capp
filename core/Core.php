<?php
namespace w3capp;
use w3capp\helper\Cache;
class Core{
    protected static $all_db_m=array();
    protected static $assign_val=array();
    protected static $db_drivers=array();
	public static $app;
    /**
     * 数据库实例
     * @param $db_driver
     * @param $config_i
     * @return \driver\DataInterface
     */
    static public function _dbInstance($db_driver,$config_i){
        $key_driver=$db_driver.$config_i;
        if(empty(self::$db_drivers[$key_driver])){
            $driver_class="\\w3capp\\driver\\".$db_driver;
            $config=(array)W3cApp::$db_config[$db_driver][$config_i];
            self::$db_drivers[$key_driver]=$driver_class::init($config);
            return self::$db_drivers[$key_driver];
        }else{
            return self::$db_drivers[$key_driver];
        }
    }
    public static function _db(){
        reset(W3cApp::$db_config);
        return self::_dbInstance(key(W3cApp::$db_config),0);
    }


    static function _adapter($record,$adapter_class,$instance_name){
        if(!$instance_name)return new $adapter_class($record);
        if(empty(self::$all_db_m[$instance_name])){
            $mod=new $adapter_class($record);
            self::$all_db_m[$instance_name][$record]=$mod;
            return $mod;
        }
        if(array_key_exists($record,self::$all_db_m[$instance_name])){
            return self::$all_db_m[$instance_name][$record];
        }
        self::$all_db_m[$instance_name][$record]=new $adapter_class($record);
        return self::$all_db_m[$instance_name][$record];
    }
    /**
     * 赋值
     */
    function _assign($key,$val){
        self::$assign_val[$key]=$val;
    }
    function __set($name, $value)
    {
        $this->_assign($name,$value);
    }
    function __get($name)
    {
        if(empty(self::$assign_val[$name]))return null;
        return self::$assign_val[$name];
    }
    function __isset($key){
        return empty(self::$assign_val[$key]);
    }
    public static function clearAssign(){
        self::$assign_val=[];
    }
	public function _query($key,$default){
		if($default instanceof \Closure){
			return $default();
		}
		return $default;
	}
	protected function queryFor($key,$def_call=null){
		$val=$this->__get($key);
		if($val)return $val;
		return W3cApp::$instance->_query($key,$def_call);
	}
    static protected function check_form_hash($hash,$time_df=60){
        $dat=0;
        for ($i=0;$i<strlen(W3CA_YUN_DAT);$i++){
            $dat+=ord(W3CA_YUN_DAT[$i])*$i;
        }
        if($dat>100000)$dat=$dat%100000;
        $dt=time()-19870118-($dat^hexdec($hash));
        return abs($dt) < $time_df;
    }
    static protected function _form_hash(){
        $dat=0;
        for ($i=0;$i<strlen(W3CA_YUN_DAT);$i++){
            $dat+=ord(W3CA_YUN_DAT[$i])*$i;
        }
        if($dat>100000)$dat=$dat%100000;

        return dechex((time()-19870118)^$dat);
    }
	//previous cookie
	static function _preCookie(){
        if(empty($_COOKIE['pre'])){
            $pr="w3ca".chr(rand(65, 90));
            W3cApp::setCookie(["pre",$pr,10000,"/"]);
            return $pr;
        }else{
            return $_COOKIE['pre'];
        }
    }

	//缓存处理
	protected $cache_obj;
	public function _cache(){
		if(!$this->cache_obj){
			$this->cache_obj=new Cache();
		}
		return $this->cache_obj;
	}
    protected function cacheExists($key){
        return $this->_cache()->valueExists($key);
    }
    protected function cacheValue($key){
        return $this->_cache()->value($key);
    }
    protected function cacheSave($key,$val,$expire=0){
        return $this->_cache()->saveValue($key,$val,$expire);
    }
    protected function cacheDelete($key){
        $this->_cache()->delete($key);
    }
    private static $error_msg;
    public static function _error($msg,$exit){
        self::$error_msg=$msg;
        if($exit){
            throw new Exception(self::$error_msg);
        }
    }
    public static function _lastError(){
        return self::$error_msg;
    }
}