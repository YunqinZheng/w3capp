<?php
namespace api\block;

use common\model\BlockExtendRecord;

/**
 *
 */
class BlockTpl extends \W3cCore {
    
	/**
	 * 更新过的模块个数
	 */
	static $updata_c=0;

	/**
	* 模块嵌套
	*/
	static $is_obstart=false;
    protected $block_info;
	/**
	 * 缓存
	 */
	private $cache_mark;
	/**
	 * 当前模板
	 */
    protected $tpl_file;

	//读取通用值
	static function ig($key){
	    return self::__get($key);
    }
	public function __construct($info){
	    $this->block_info=$info;
	}

	public function setInfo($column,$value){
	    $this->block_info[$column]=$value;
	}
    public function info($column){
	    if(empty($this->block_info[$column]))return '';
        return $this->block_info[$column];
    }
	/**
	 * 缓存更新判断
	 */
    private function shouldUpdate(){
        if(self::$is_obstart)return true;

        if($this->cacheExists($this->cache_mark)==false){
            return true;
        }
        if($this->info('update_time')==-1||self::$updata_c>5){
            return false;
        }
        self::$updata_c++;
        return true;
    }
	/**
	 * 显示模块
	 */
	function display($args=null){
	    echo $this->content($args);
	}
    function content($args=null){
	    if($this->info('update_time')==0){
            return $this->createCache($args);
        }
        if($args){
            if(is_array($args)){
                $arg_str="#".urlencode(http_build_query($args));
            }else{
                $arg_str="#".urlencode($args);
            }
            $mark="x".$this->cache_mark;
            if($this->cacheExists($mark)){
                $this->cacheSave($mark,$this->cacheValue($mark)."|".$arg_str);
            }else{
                $this->cacheSave($mark,$arg_str);
            }
            $this->cache_mark.=$arg_str;
        }
        if($this->shouldUpdate()||$this->cacheExists($this->cache_mark)==false){
            $tmp=$this->createCache($args);
            if($this->info('update_time')>0){
                $this->cacheSave($this->cache_mark,$tmp,$this->info('update_time')*60);
            }else{
                //保存成静态 时间
                $this->cacheSave($this->cache_mark,$tmp);
            }
            return $tmp;
        }else{
            return $this->cacheValue($this->cache_mark);
        }
    }
    function tplFile(){
        return $this->tpl_file;
    }

    /**
     * 附加样式表
     * @return string
     */
    public static function stylesheet(){
        $class_path=explode("\\",static::class);
        $class=array_pop($class_path);
        $class_path[]='assets';
        $class_path[]=$class.".css";
        $file_p=W3CA_MASTER_PATH."core/".implode("/",$class_path);
        if(is_file($file_p)){
            return file_get_contents($file_p);
        }
	    return '';
    }

    /**
     * 附加js 会在页面尾部引用
     * @return string
     */
    public static function script(){
        $class_path=explode("\\",static::class);
        $class=array_pop($class_path);
        $class_path[]='assets';
        $class_path[]=$class.".js";
        $file_p=W3CA_MASTER_PATH."core/".implode("/",$class_path);
        if(is_file($file_p)){
            return file_get_contents($file_p);
        }
        return '';
    }
	/**
	 * 加载数据,由子类重写
	 */
	function loadData($args){
	    return array();
	}
	/**
	 * 缓存文件
	 */
	public function getCache(){
		if($this->info('update_time')==0)return "";
		return $this->cacheValue($this->cache_mark);
	}
	/**
	 * 创建缓存数据,返回缓存
	 */
	protected function createCache($args=null){
	    if(self::$is_obstart){
	        $data=$this->loadData($args);
	        $item_edit_mark='';
	        include self::$app->template()->file($this->tplFile(),'block');

	    }else{
	        self::$is_obstart=true;
	        ob_start();
	        $data=$this->loadData($args);
            include self::$app->template()->file($this->tplFile(),'block');
	        $tmp=ob_get_contents();
	        ob_end_clean();
	        self::$is_obstart=false;
	        return $tmp;
	    }
	
	}
	public function setCacheMark($mark){
        $this->cache_mark=$mark;
    }
    public function setTplFile($file){
        $this->tpl_file=$file;
    }

	/**
	 * 模块属性,表单字段
	 * @return array
	 */
	function getPrototypeForm(){
	    $columns=array(
	        "id"=>array("form_input"=>"hidden"),
	        "init_hash"=>array("form_input"=>"hidden"),
	        "data_size"=>array("form_input"=>"hidden","col_name"=>"数据条数","def_value"=>"10"),
	        "mark"=>array("col_name"=>"调用标记","form_input"=>"text"),
	        "remarks"=>array("col_name"=>"说明","form_input"=>"text"),
            "tpl"=>array("form_input"=>"hidden","def_value"=>''),
	        "update_time"=>array("col_name"=>"缓存时间","form_input"=>"hidden","def_value"=>"0",));
                //"diycode"=>'<div><span class="labt">更新时间:</span><p class="inct"><input class="short_txt" id="update_time" name="update_time" value="{col_value}"/>分钟(-1:不更新,0:无缓存)</p></div>')

	    return $columns;
	}

    /**
     * 保存属性时调用
     * @param $data
     *
     */
	function onCheckPrototype(&$data){
        $data["interface_arg"]="";
        $data["data_desc"]="";
        $data["pro_value"]="";
    }

    /**
     * 所有属性
     * @return array|mixed
     */
	function getPrototype(){
	    if(empty($this->block_info['id']))
		    return $this->block_info;
	    $ext=BlockExtendRecord::firstAttr(['block_id'=>$this->block_info['id']]);
	    return array_merge($this->block_info,$ext);
	}

    /**
     * 保存成功后回调
     * @param $id
     */
	function onSaved($id){
		$this->block_info['id']=$id;
	}
    function submitCache($cache,$args=''){

        $this->cacheSave($this->cache_mark.$args,$cache,$this->info('update_time')?$this->info('update_time')*60:0);
    }
}
