<?php
namespace w3capp;
use w3capp\helper\Sql;
use w3capp\helper\Str;

class W3cAppAdapter{
    /**
     * @var \driver\DataInterface
     */
    protected $current_driver;
    protected $tablePrefix;
    private $table;
    protected $data;
    protected $filter;
    protected $bindParam;
    /**
     * @param $table要加前缀
     */
    public function __construct($table){
        $this->current_driver=Core::_dbInstance($this->dbDriver(),$this->dbConfigIndex());
        $this->setTablePre($this->current_driver->config['tab_pre']);
        $this->table=$table;
    }
    protected function dbDriver(){
        reset(W3cApp::$db_config);
        return key(W3cApp::$db_config);
    }
    protected function dbConfigIndex(){
        return 0;
    }
    public function setTablePre($setting){
        $this->tablePrefix=$setting;
    }
    public function getTablePre(){
        return $this->tablePrefix;
    }
    public function tableName(){
        return $this->tablePrefix.$this->table;
    }

    /**
     * 追加表前缀
     * @param $name
     * @return string
     */
    public function table($name){
        return $this->tablePrefix.$name;
    }

    /**
     * @return \driver\DataInterface
     */
    public function db(){
        return $this->current_driver;
    }

    var $sql_opt=[];
    protected $sql_opt_replace;
    function select($columns){
        $this->sql_opt['select']=$columns;
        return $this;
    }

    public function from($full_table){
        $this->sql_opt["from"]=$full_table;
        $this->sql_opt["join"]=[];
        return $this;
    }
    function joinTable($table,$type,$on,$clean=false){
        if($clean||empty($this->sql_opt["join"])){
            $this->sql_opt["join"]=[];
        }
        $this->sql_opt["join"][]=$type." join ".$table." on $on";
        return $this;
    }
    function where($condition){
        $this->sql_opt["where"]=$condition;
        return $this;
    }
    function andWhere($condition,$clean=false){
        if($clean||empty($this->sql_opt["andWhere"])){
            $this->sql_opt["andWhere"]=[];
        }
        $this->sql_opt["andWhere"][]=Sql::parse($condition);
        return $this;
    }
    function orWhere($condition,$clean=false){
        if($clean||empty($this->sql_opt["orWhere"])){
            $this->sql_opt["orWhere"]=[];
        }
        $this->sql_opt["orWhere"][]=Sql::parse($condition);
        return $this;
    }
    function groupBy($g){
        $this->sql_opt["group"]=$g;
        return $this;
    }
    function orderBy($order){
        $this->sql_opt["order"]=$order;
        return $this;
    }
    function limit($limit,$page=null){
        $this->sql_opt["limit"]=$limit;
        if($page){
            $this->sql_opt["page_index"]=$page-1;
            $this->offset($limit*$this->sql_opt["page_index"]);
        }else{
            $this->sql_opt["page_index"]=0;
        }
        return $this;
    }
    function offset($offset){
        $this->sql_opt["offset"]=$offset;
        return $this;
    }
    function getSql(){
        $this->bindParam=null;
        $sql_opt=$this->sql_opt_replace?array_merge($this->sql_opt,$this->sql_opt_replace):$this->sql_opt;
        if(empty($this->sql_opt['commend'])||$this->sql_opt['commend']=="select"){
            if(empty($sql_opt['from']))$sql_opt['from']=$this->tableName();
            return Sql::getSql($sql_opt);
        }else if($this->sql_opt['commend']=="insert"){
            $columns=[];
            $pre_val=[];
            //$c_keys=[];
            foreach ($this->data as $key=>$val) {
                if($this->filter instanceof \Closure){
                    $pre_val[":".$key]=$this->filter($key,$val);
                }else{
                    $pre_val[":".$key]=array_key_exists($val,$this->filter)?$this->filter[$val]:Str::xss_filter($val);
                }
                $columns[]=$key;//'`'.$key.'`';
                //$c_keys[]=$c_keys;
            }
            $pre_sql="insert INTO ".$this->tableName()." (`".implode("`,`", $columns)."`) values (:".implode(',:', $columns).")";
            $this->bindParam=$pre_val;
            return $pre_sql;
        }else if($this->sql_opt['commend']=="update"){
            $pre_sql="update ".$this->tableName()." set ";
            foreach ($this->data as $key=>$val) {
                if($this->filter instanceof \Closure){
                    $val = $this->filter($key,$val);
                }else {
                    $val = array_key_exists($val, $this->filter) ? $this->filter[$val] : Str::xss_filter($val);
                }
                $bindParam[":".$key]=$val;
                $pre_sql.='`'.$key."`=:".$key.",";
            }
            $pre_sql=rtrim($pre_sql,",");
            if(empty($sql_opt['where'])){
                $pre_sql.=" where 'error'=1";
            }else if(is_array($sql_opt['where'])){
                $pre_sql.=" where ".Sql::parse($sql_opt['where']);
            }else{
                $pre_sql.=" where ".$sql_opt['where'];
            }
            if(empty($sql_opt["andWhere"])==false){
                $pre_sql.=" and ".implode(" and ",$sql_opt["andWhere"]);
            }
            if(empty($sql_opt["orWhere"])==false){
                if(empty($sql_opt['where'])&&empty($sql_opt["andWhere"])){
                    $pre_sql.=" where ".implode(" or ",$sql_opt["orWhere"]);
                }else{
                    $pre_sql.=" or ".implode(" or ",$sql_opt["orWhere"]);
                }
            }
            $this->bindParam=$bindParam;
            return $pre_sql;
        }else if($this->sql_opt['commend']=="delete"){
            
            $sql="delete from ".$this->tableName();
            if(empty($sql_opt['where'])){
                $sql.=" where 'error'=1";
            }else if(is_array($sql_opt['where'])){
                $sql.=" where ".Sql::parse($sql_opt['where']);
            }else{
                $sql.=" where ".$sql_opt['where'];
            }
            if(empty($sql_opt["andWhere"])==false){
                $sql.=" and ".implode(" and ",$sql_opt["andWhere"]);
            }
            if(empty($sql_opt["orWhere"])==false){
                if(empty($sql_opt['where'])&&empty($sql_opt["andWhere"])){
                    $sql.=" where ".implode(" or ",$sql_opt["orWhere"]);
                }else{
                    $sql.=" or ".implode(" or ",$sql_opt["orWhere"]);
                }
            }
            return $sql;
        }
        return "";
    }
    function tableAs($alias){
        if(empty($this->sql_opt['from']))$this->sql_opt['from']=$this->tableName();
        $this->sql_opt['from'].=" as ".$alias;
        return $this;
    }
    function commend($com){
        $this->sql_opt['commend']=$com;
        return $this;
    }
    function setData($data){
        $this->data=$data;
        return $this;
    }
    function setFilter($filter){
        $this->filter=$filter;
        return $this;
    }

    /**
     * @param bool $count
     * @return W3cAppDataApi
     */
    function selectAll($count=false){

        $itor = $this->db()->getIterator($this->getSql());
        if(empty($this->sql_opt["limit"])==false){
            $itor->page_index=$this->sql_opt["page_index"];
            $itor->page_size=$this->sql_opt["limit"];
        }
        if($count){
            $itor->amount=$this->selectCount();
        }
        return $itor;
    }
    function execute(){
        $sql=$this->getSql();
        if($sql)
            return $this->db()->execute($sql,$this->bindParam,$this->sql_opt['commend']=="insert");
        return false;
    }
    /**
     * @param bool $count
     * @return W3cAppDataApi
     */
    function query($count=false){
        $this->commend("");
        return $this->selectAll($count);
    }
    function queryArray(){
        $this->commend("");
        return  $this->db()->getArray($this->getSql());
    }
    function queryFirst(){
        $this->commend("");
        $this->sql_opt["limit"]=1;
        $sql=$this->getSql();
        return  $this->db()->getFirst($sql);
    }
    function selectCount(){
        $this->commend("");
        $this->sql_opt_replace=['select'=>"count(1) c","limit"=>"","offset"=>"","order"=>""];
        $row=$this->db()->getFirst($this->getSql());
        $this->sql_opt_replace=null;
        return $row['c'];
    }
}