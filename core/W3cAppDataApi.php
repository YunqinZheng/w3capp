<?php
namespace w3capp;
class W3cAppDataApi implements \Iterator,\Countable{
	var $amount=0;
	var $page_size=0;
	//从1开始
	var $page_index=0;
	private $currentKey;
	protected $_filters=array();
	private $rewind_action;
	private $key_action;
	private $next_action;
	private $valid_action;
	private $current_action;
    private $prepare_action;
    private $inited_action;
	private $count_action;
	private $init_data;
	private $cache_data;
	private $cache_key;
	private $use_cache;
	private $is_fetch_end;
	//提前读的值
	private $pread_val;
	public function __construct($param){
		$this->init_data=is_array($param)?$param:array();
	}
	function setReuseAble($cache){
	    $this->use_cache=$cache;
    }
    function getReuseAble(){
        return $this->use_cache;
    }
	function getInitVal($key){
		return empty($this->init_data[$key])?null:$this->init_data[$key];
	}
	function setInitVal($key,$val){
		$this->init_data[$key]=$val;
	}
    function setDataFilter($filter){
        $this->_filters=array($filter);
    }
	function appendFilter($filter){
		$this->_filters[]=$filter;
	}
	function fetchWith($filter){
	    $old_ft=$this->_filters;
        $this->_filters=[];
		foreach ($this as $key => $value) {
			$filter($value);
		}
        $this->_filters=$old_ft;
	}
    /**
     * @param \Closure $filter
     * @return array
     */
	public function fetch($filter=null){
	    $all=[];
        foreach ($this as $key => $value) {
            if($filter){
                $filter($value);
            }
            $all[]=$value;
        }
		return $all;
	}
	public function onRewind($f){
		$this->rewind_action=$f;
	}
	public function onInited($f){
	    $this->inited_action=$f;
    }
    public function onPrepare($f){
        $this->prepare_action=$f;
    }
	public function onKey($f){
		$this->key_action=$f;
	}
	
	public function onNext($f){
		$this->next_action=$f;
	}
	public function onValid($f){
		$this->valid_action=$f;
	}

	public function onCurrent($f){
		$this->current_action=$f;
	}

	public function onCount($f){
	    $this->count_action=$f;
	}
	public function toArray(){
	    $_array=array();
	    $this->fetchWith(function($var)use(&$_array){
	        //$var instanceof \W3cRecord?$var->getAttributes():
	        $_array[]=$var;
	    });
	    return array("amount"=>$this->amount,"page_size"=>$this->page_size
	        ,"page_index"=>$this->page_index,"record"=>$_array);
	        
	}

	var $debug=false;
	function rewind(){
        if($this->use_cache){
            if($this->is_fetch_end){
                //数据复用
                reset($this->cache_key);
                reset($this->cache_data);
                $this->pread_val=current($this->cache_data);
                return;
            }
            $this->cache_key=array();
            $this->cache_data=array();
            $this->pread_val=false;
        }

        if($this->inited_action){

            if($this->prepare_action instanceof \Closure){
                call_user_func_array($this->inited_action,[$this,
                    call_user_func_array($this->prepare_action,[$this->init_data])
                ]);
            }else{
                call_user_func_array($this->inited_action,[$this,null]);
            }
            $this->inited_action=null;
        }
        call_user_func_array($this->rewind_action,[$this]);

	}
	function key(){
        if($this->is_fetch_end&&$this->use_cache) {
            //数据复用
            return current($this->cache_key);
        }
        $key = call_user_func_array($this->key_action,[$this]);
        if($this->use_cache){
            $this->cache_key[]=$key;
        }
	    return $key;
	}
	function next(){

        if($this->is_fetch_end&&$this->use_cache) {
            //数据复用
            $this->pread_val=next($this->cache_data);
            next($this->cache_key);
        }else{
            call_user_func_array($this->next_action,[$this]);
        }
	}
	function valid(){
	    if($this->is_fetch_end&&$this->use_cache){
	        //数据复用
            if(empty($this->cache_data)){
                return false;
            }
            return $this->pread_val===false?false:true;
        }
	    if(call_user_func_array($this->valid_action,[$this])){
	        return true;
        }else{
	        $this->is_fetch_end=true;
            $this->_filters=array();
	        return false;
        }
	}
	function current(){
        if($this->is_fetch_end&&$this->use_cache) {
            //数据复用
            foreach($this->_filters as $fun){
                $fun($this->pread_val);
            }
            return $this->pread_val;
        }
	    $value=call_user_func_array($this->current_action,[$this]);
        foreach($this->_filters as $fun){
            $fun($value);
        }
        if($this->use_cache){
            $this->cache_data[]=$value;
        }
	    return $value;
	}
	function count(){
        if($this->is_fetch_end&&$this->use_cache) {
            //数据复用
            return count($this->cache_data);
        }
        if($this->inited_action){
            if($this->prepare_action instanceof \Closure){
                call_user_func_array($this->inited_action,[$this,
                    call_user_func_array($this->prepare_action,[$this->init_data])
                ]);
            }else{
                call_user_func_array($this->inited_action,[$this,null]);
            }
            $this->inited_action=null;
        }
	    return call_user_func_array($this->count_action,[$this]);
	}
} 