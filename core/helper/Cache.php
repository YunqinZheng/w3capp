<?php
namespace w3capp\helper;
class Cache{
    var $type;
    protected $fileStoreDir;
    protected $redis;
    public function __construct($type="file")
    {
        $this->type=$type;
        $init_fun=$this->type."Init";
        $this->$init_fun();
    }
    protected function fileInit(){
        $this->fileStoreDir=W3CA_MASTER_PATH."data/store/";
    }
    public function redisInit(){
        $this->redis = new \Redis();
        if($this->redis->connect('127.0.0.1', 6379)==false){
            throw new \Exception("redis connect error!");
        }
        return $this->redis;
    }
    public function saveValue($key,$val,$expire=0){
        $fun=$this->type."SaveValue";
        return $this->$fun($key,$val,$expire);
    }
    public function value($key){
        $fun=$this->type."Value";
        return $this->$fun($key);
    }
    public function delete($key){
        $fun=$this->type."Delete";
        return $this->$fun($key);
    }
    public function valueExists($key){
        $fun=$this->type."ValueExists";
        return $this->$fun($key);
    }
    protected function redisSaveValue($key,$val,$expire=0){
        $this->redis->set($key,$val,$expire);
    }
    protected function redisValue($key){
        return $this->redis->get($key);
    }
    protected function redisDelete($key){
        return $this->redis->delete($key);
    }
    protected function redisValueExists($key){
        return $this->redis->exists($key);
    }
    protected function fileSaveValue($key,$val,$expire=0){
        $f=$this->fileName($key);
        $ft=$f."%t%";
        $r=file_put_contents($f,$val);
        if($expire>0){
            file_put_contents($ft,time()+$expire);
        }else if(file_exists($ft)){
            unlink($ft);
        }
        return $r;
    }
    protected function fileValue($key){
        $f=$this->fileName($key);
        $ft=$f."%t%";
        if(file_exists($f."%t%")){
            if(time()-file_get_contents($ft)>0){
                return null;
            }
        }
        return file_get_contents($f);
    }
    protected function fileDelete($key){
        $f=$this->fileName($key);
        if(file_exists($f."%t%")){
            @unlink($f."%t%");
        }
        return @unlink($f);
    }
    protected function fileValueExists($key){
        $f=$this->fileName($key);
        if(file_exists($f."%t%")){
            return self::$app->getConfig("random_key")-file_get_contents($f."%t%")<0;
        }
        return file_exists($f);
    }

    protected function fileName($key){
        if(strlen($key)<4){
            return $this->fileStoreDir."0/.".urlencode($key.self::$app->getConfig("random_key"));
        }
        $dir=$this->fileStoreDir.ord($key{3})."/";
        if(!file_exists($dir)){
            mkdir($dir,0777,true);
        }
        return $dir.".".urlencode($key.self::$app->getConfig("random_key"));
    }
}