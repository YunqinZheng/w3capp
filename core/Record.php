<?php
namespace w3capp;
class Record implements \ArrayAccess,\Iterator{
    /**
     * @var W3cappAdapter
     */
    //private $modal;
    //数据库保存值
    protected $attributes;
    protected $newAttributes;
    protected $properties;
    protected $primaryName="id";
    protected $errors;

    /**
     * W3cRecord constructor.
     * @param null $arg 如果参数有主键值,就会优先读取数据库信息，否则创建新的数据记录
     * @throws Exception
     */
    public function __construct($arg=null){
        $this->properties=static::propertyDesc();
        $this->newAttributes=[];
        $this->attributes=[];
        if($arg instanceof Record){
            $attr=$arg->getAttributes();
            unset($attr[$this->primaryName]);
            $this->setAttributes($attr);
        //}else if($arg instanceof \W3cMyAdapter){
        //    $this->modal=$arg;
        }else{
            if(is_array($arg)){
                if(empty($arg[$this->primaryName])){
                    $this->setAttributes($arg);
                }else{
                    //如果参数包含主键
                    //$columns=static::allColumnStr();
                    $data=self::firstAttr([$this->primaryName=>$arg[$this->primaryName]]);
                    if(empty($data)){
                        $this->setAttributes($arg);
                    }else{
                        $this->write($data);
                        unset($arg[$this->primaryName]);
                        $this->setAttributes($arg);
                    }

                }
            }

        }
        $this->errors=[];
    }
    public function getError(){
        return $this->errors;
    }
    public function offsetExists($key){
        return isset($this->attributes[$key]);
    }
    public function offsetSet($key, $value)
    {
        if(empty($key)){
            throw new Exception("error record property");
        }
        $this->setAttribute($key,$value);
    }
    public function offsetGet($key){
        return $this->getAttribute($key);
    }
    public function offsetUnset($key){
        $this->newAttributes[$key]=null;
    }
    public function delete(){
        return self::deleteAll([$this->primaryName=>$this->primary()]);
    }
    static public function deleteAll($condition){
        $d=static::myAdapter();
        if($condition){
            return $d->commend("delete")->where($condition)->execute($sql);
        }
        return false;
    }

    /**
     * 批量修改
     * @param $data_condition 多维数组[[数据1,条件1],[数据2,条件2],....]
     */
    static public function batchUpdate($data_condition){
        $result=[];
        $modal=static::myAdapter()->commend("update");
        foreach($data_condition as $item){
            $result[]=$modal->setData($item[0])->where($item[1])->execute();
        }
        return $result;
    }
	static public function updateAll($values,$condition,$rp=null){
		$modal=static::myAdapter();
        return $modal->commend("update")->setData($values)->where($condition)->setFilter($rp)->execute();
	}
    static public function insert($data,$replace=null){
        $modal=static::myAdapter();
        return $modal->commend("insert")->setData($data)->setFilter($replace)->execute();
    }
    /**
     * @return \driver\DataInterface
     */
    /*public function d(){
        return $this->m()->db();
    }
    */


    public static function adapterClass(){
        return W3cApp::$install_config['db_adapter'];
    }
    /**
     * @return W3cappAdapter
     */
    public static function myAdapter(){
        $model= Core::_adapter(static::recordName(),static::adapterClass(),'');//db_record_adapter
        return $model;
    }
    function __get($name)
    {
        return $this->getAttribute($name);
    }
    function __set($name, $value)
    {
        $this->setAttribute($name,$value);
    }
    function __isset($name){
        if(empty($this->newAttributes[$name])&&empty($this->attributes[$name])){
            return false;
        }
        return true;
    }
    /**
     * @param $attrs array
     */
    public function setAttributes($attrs){
        foreach ($attrs as $name=>$value){
            $this->setAttribute($name,$value);
        }
    }
    public function setAttribute($name,$value){
        if(array_key_exists($name,$this->properties)){
            $this->newAttributes[$name]=$value;
            return true;
        }
        return false;
    }
    public function getAttribute($name){
        if(array_key_exists($name,$this->newAttributes)){
            return $this->newAttributes[$name];
        }
        return array_key_exists($name,$this->attributes)?$this->attributes[$name]:null;
    }
    public function getAttributes(){
        if(empty($this->newAttributes))return $this->attributes;
        return array_merge($this->attributes,$this->newAttributes);
    }
    public function write($data){
        foreach ($data as $name=>$value){
            $this->attributes[$name]=$value;
        }
    }

    /**
     * @return mixed 返回主键值
     */
    public function primary(){
        return array_key_exists($this->primaryName,$this->attributes)?$this->attributes[$this->primaryName]:null;
    }

    /**
     * 约束输入
     * @param $replace
     * @return array
     * @throws Exception
     */
    protected function restrain(&$replace){
        $rule=static::recordRule();
        $values=[];
        foreach ($rule as $r){
            switch ($r[1]){
                case 'require':
                    foreach($r[0] as $item){
                        if($this->getAttribute($item)===null){
                            throw new Exception($this->properties[$item]." is require");
                        }
                    }
                    break;
                case "integer":
                    foreach($r[0] as $item){
                        if(array_key_exists($item,$this->newAttributes)){
                            $values[$item]=intval($this->newAttributes[$item]);
                        }
                    }
                    break;
                case "float":
                    foreach($r[0] as $item){
                        if(array_key_exists($item,$this->newAttributes)){
                            $values[$item]=floatval($this->newAttributes[$item]);
                        }
                    }
                    break;
                case "string":
                    foreach($r[0] as $item){
                        if(array_key_exists($item,$this->newAttributes)){
                            if(empty($r[2])){

                            }else if(is_int($r[2])&&strlen($this->newAttributes[$item])>$r[2]){
                                throw new Exception($this->properties[$item]." is over {$r[2]} chart");
                            }else if($r[2]{0}=="/"&&preg_match($r[2],$this->newAttributes[$item])===fasle){
                                throw new Exception($this->properties[$item]." value not match rule");
                            }
                        }
                    }
                    break;
                case "limitless":
                    foreach($r[0] as $item){
                        if(array_key_exists($item,$this->newAttributes)){
                            $replace['{'.$item.'}']=$this->newAttributes[$item];
                            if(empty($r[2])){

                            }else if(is_int($r[2])&&strlen($this->newAttributes[$item])>$r[2]){
                                throw new Exception($this->properties[$item]." is over {$r[2]} chart");
                            }
                            $values[$item]="{".$item.'}';
                        }
                    }
                    break;
            }
        }
        return array_merge($this->newAttributes,$values);
    }
    public function save(){
        if(empty($this->newAttributes)){
			$this->errors[0]="No data or Attribute unchanged";
            return false;
        }
        $replace=[];
        $values=$this->restrain($replace);
        if(empty($this->attributes[$this->primaryName])){
            //if($values[$this->primaryName]=='')
            //    unset($values[$this->primaryName]);
            $result=self::insert($values,$replace);
            if($result===false){
				$this->errors[0]="insert:未知错误";
                return false;
            }else{
                $this->attributes=$this->newAttributes;
                if(is_bool($result)==false){
                    $this->attributes[$this->primaryName]=$result;
                }
                $this->newAttributes=[];
            }
        }else{
            
            $result=self::updateAll($values,[$this->primaryName=>$this->primary()],$replace);
            if($result===false){
                return false;
            }
            $this->attributes=array_merge($this->attributes,$this->newAttributes);
            $this->newAttributes=[];
        }
        return true;
    }
    static function batchInsert($list){
        $result=[];
        $modal=null;
        foreach ($list as $item){
            //if($modal==null)$modal=static::myAdapter()->commend("insert");
            $record=new static();
            $record->setAttributes($item);
            $result[]=$record->save();
        }
        return $result;
    }
    static function firstAttr($condition,$attrs="",$order=''){
        $modal=static::myAdapter();
        $columns=empty($attrs)?static::allColumnStr():$attrs;
        if($order)$modal->orderBy($order);
        return $modal->where($condition)->select($columns)->queryFirst();
    }

    static function record($condition,$create=false){
        $modal=static::myAdapter();
        $columns=static::allColumnStr();
        $data=$modal->where($condition)->select($columns)->queryFirst();
        if(empty($data)){
            if($create){
                $record=new static();
                if(is_array($condition))
                    $record->setAttributes($condition);
                return $record;
            }
            return null;
        }
        $record=new static();
        $record->write($data);
        return $record;
    }

    /**
     * @param $condition
     * @param string $order
     * @param int $limit
     * @return W3cAppDataApi
     * @throws Exception
     */
    static function findAll($condition,$order='',$limit=0){
        $columns=static::allColumnStr();
        $m=static::myAdapter();
        $m->select($columns)->where($condition)->orderBy($order);
        if($limit){
            $m->limit($limit);
        }
        $data=$m->selectAll();
        $data->setDataFilter(function(&$val)use($m){
            $record=new static();
            $record->write($val);
            $val=$record;
        });
        return $data;
    }
    static function findAllData($condition,$order='',$limit=0){
        $columns=static::allColumnStr();
        $m=static::myAdapter();
        $m->select($columns)->where($condition)->orderBy($order);
        if($limit){
            $m->limit($limit);
        }
        return $m->selectAll();
    }
    static function arrayData($condition,$order='',$limit=0){
        $columns=static::allColumnStr();
        $modal=static::myAdapter();
        $data=$modal->select($columns)->where($condition)->limit($limit)->orderBy($order)->queryArray();
        return $data;
    }
    /**
     * 数组转实例
     * @param $attributes
     * @return static
     */
    static function newRecord($attributes){
        $record=new static();
        $record->write($attributes);
        return $record;
    }
    /**
     * 查询的字段
     * @return string
     * @throws Exception
     */
    static function allColumnStr(){
        return "`".implode("`,`",array_keys(static::propertyDesc()))."`";
    }
    /**
     * @param $condition
     * @return W3cMyAdapter
     * @throws Exception
     */
    static function adaptTo($condition){
        $columns=static::allColumnStr();
        $ad=static::myAdapter();
        return $ad->select($columns)->where($condition);
    }
    /**
     * @return string 记录名
     */
    static public function recordName(){
		return null;
	}
    /**
     * @return string 表名（前缀+记录名）
     */
    static public function recordTable(){
		return static::myAdapter()->tableName();//table(self::recordName());
	}
    /**
     * @return array 字段约定
     */
    static public function recordRule(){
		return null;
	}
    /**
     * @return array 字段说明
     */
    static public function propertyDesc(){
		return [];
	}
    //Iterator
    protected $p_items;
    public function rewind() {
        if(!$this->p_items)$this->p_items=array_keys($this->propertyDesc());
        reset($this->p_items); 
    }
    public function current() {
        return $this->getAttribute(current($this->p_items));
    }
    public function key() {
        return current($this->p_items);
    }
    public function next() {
        return next($this->p_items);
    }
    public function valid() {
        if(empty($this->p_items)||key($this->p_items)==count($this->p_items)-1)return false;
        return true;
    }
}