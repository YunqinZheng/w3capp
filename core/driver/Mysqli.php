<?php
namespace w3capp\driver;
use w3capp\helper\Str;

/**
 * mysql类
 */
class Mysqli implements DataInterface {
    private $connected;
    private $last_sql;
    private function __construct($config){
        $this->connect($config);
    }
    /**
     * 连接
     */
    private function connect($config){
        $this->connected=mysqli_connect($config['host'],$config['user'],$config['pwd']);
        if($this->connected&&mysqli_select_db($config['dbname'],$this->connected)){
        }else{
            echo "sql connect 失败！";
            echo mysqli_error($this->connected);
            return ;
        }
        
        mysqli_query("set names ".W3CA_DB_CHAR_SET,$this->connected);
    }
    static public function init($config){
        return new self($config);
    }
    public function __clone()
    {
        trigger_error('Clone is not allow' ,E_USER_ERROR);
    }

    function query($sql){
        return mysqli_query($sql,$this->connected);
    }
    
    public function alterColumnType($table,$column,$type){
        $this->execute("alter table $table modify column `$column` $type");
    }
    /**
     * 插入,当$values为数组时$table不能为空
     * @return 表id
     */
    public function insert($values,$table,$especial=null){
        $sql=$cols='';
        $cv=array();
        $fc=1;
        foreach ($values as $key => $value) {
            $key="`".$key."`";
            $cols.=$fc==1?$key:','.$key;
            if($value===null){
                $cv[]="null";
            }else{
                $value=Str::xss_filter($value);
                $cv[]="'".$value."'";
            }
            $fc=2;
        }
        $val_str=implode(",", $cv);
        $sql="insert into `$table`(".$cols.")values(".($especial==null?$val_str:strtr($val_str,$especial)).")";
        
        if(mysqli_query($sql,$this->connected)){
            return mysqli_insert_id($this->connected);
        }else{
            echo mysqli_error($this->connected);
            return FALSE;
        }
    }
    public function getSql(){
        return $this->last_sql;
    }
    /**
     * 更新，当$values为数组时$table不能为空
     */
    public function update($values,$table,$where,$especial=null)
    {
        if(empty($where)){
            echo 'no sql where;update exit.';
            return;
        }
        $sql="";
        foreach ($values as $key => $value) {
            $value=Str::xss_filter($value);
            $sql.=$sql==''?"`$key`='$value' ":",`$key`='$value' ";
        }
        $sql="update `$table` set ".($especial==null?$sql:strtr($sql,$especial))." where ".$where;
        if(mysqli_query($sql,$this->connected)){
            return true;
        }else{
            return false;
        }
    }

    public function delete($table,$where){
        return $this->execute("delete from $table where $where");
    }
    /**
     * 执行，返回$sql是否成功
     * @return bool 是否成功
     */
    public function execute($sql){
        mysqli_query($sql,$this->connected);
        if(mysqli_errno()>0){
            echo mysqli_error($this->connected);
            return false;
        }
        return true;
    }
    
    
    /**
     * 读取数组
     */
    public function getArray($sql){
        $resoure=mysqli_query($sql,$this->connected);
        $result=array();
        if(mysqli_errno()>0){
            mysqli_free_result($resoure);
            echo mysqli_error($this->connected);
            return false;
        }
        while ($r=mysqli_fetch_assoc($resoure))
        {
            $result[]=$r;
        }
        mysqli_free_result($resoure);
        return $result;
    }
    public function getFirst($sql){
        $resoure=mysqli_query($sql,$this->connected);
        $result=array();
        if(mysqli_errno()>0){
            mysqli_free_result($resoure);
            echo mysqli_error($this->connected);
            return false;
        }
        $r=mysqli_fetch_assoc($resoure);
        mysqli_free_result($resoure);
        return $r;
    }
    /**
     * 返回Iterator接口的类
     */
    public function getIterator($sql){
        $resoure=null;
        $data_api=new \W3cAppDataApi(array("idx"=>0));
        $data_api->onInited(function($api,$prepare_val)use($sql,&$resoure){
            if (is_array($prepare_val)) {
                foreach ($prepare_val as $search => $rep)
                    $sql = strtr($sql, '{' . $search . "}", $rep);
            }
            mysqli_query($sql,$this->connected);
            if(mysqli_errno()>0){
                mysqli_free_result($resoure);
                echo mysqli_error($this->connected);
                return null;
            }
        });
        $data_api->onRewind(function($api)use ($resoure){
            $api->setInitVal('idx',0);
            mysqli_data_seek($resoure,0);
        });
        $data_api->onKey(function($api){
            return $api->getInitVal("idx");
        });
        $data_api->onNext(function($api){
            $api->setInitVal('idx',1+$api->getInitVal("idx"));
        });
        $data_api->onValid(function($api)use($resoure){
            $current=mysqli_fetch_assoc($resoure);
            if($current!=false){
                $api->setInitVal("current",$current);
                return true;
            }else{
                return false;
            }
        });

        $data_api->onCurrent(function($api){
            return $api->getInitVal("current");
        });
        $data_api->onCount(function($api)use($resoure){
            return mysqli_num_rows($resoure);
        });
        return $data_api;
    }
    public function table_existed($table_name){
        $resoure=mysqli_query("show tables",$this->connected);
        $result=array();
        if(mysqli_errno()>0){
            mysqli_free_result($resoure);
            echo mysqli_error($this->connected);
            return false;
        }
        while ($r=mysqli_fetch_assoc($resoure))
        {
            foreach($r as $table){
                if($table==$table_name)return true;
            }
        }
        mysqli_free_result($resoure);
        return false;
    }

    public function tryCommit($program, $catch, $final)
    {
        // TODO: Implement tryCommit() method.
    }

    public function beginTransaction()
    {
        // TODO: Implement beginTransaction() method.
    }

    public function commit()
    {
        // TODO: Implement commit() method.
    }

    public function rollBack()
    {
        // TODO: Implement rollBack() method.
    }

    public function tableExisted($table_name)
    {
        // TODO: Implement tableExisted() method.
    }

    public function errorInfo()
    {
        // TODO: Implement errorInfo() method.
    }

    public function dbVersoin()
    {
        // TODO: Implement dbVersoin() method.
    }
}

