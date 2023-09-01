<?php
namespace w3capp\driver;
use w3capp\driver\DataInterface;
use w3capp\helper\Str;

/**
 * sqlite类
 */
class SqlitePDO implements DataInterface{
	private $pdo_obj;
	private $last_sql;
	var $config;
	private function __construct($config){
	    $this->config=$config;
		$this->pdo_obj=new \PDO($config['dsn']);
		if($this->pdo_obj==false){
			echo "sqlite connect 失败！";
			return ;
		}
	}
	static public function init($config){
		return new self($config);
	}
	public function __clone()
	{
		trigger_error('Clone is not allow' ,E_USER_ERROR);
	}
    public function dbVersoin(){
        return 'sqLite：'.$this->pdo_obj->getAttribute(\PDO::ATTR_SERVER_VERSION);
    }
	function query($sql){
		$this->last_sql=$sql;
		return $this->pdo_obj->query($sql);
	}
	public function insert($values,$table,$especial=null){
		if(empty($values))return false;
		$columns=array_keys($values);
		$this->last_sql=$pre_sql="insert INTO ".$table." (".implode(",", $columns).") values (".implode(',', array_fill(0, count($columns), '?')).")";
		$prep=$this->pdo_obj->prepare($pre_sql);
		if(!$prep){
            print_r($prep->errorInfo());
		    die("sqlite error:".$pre_sql);
        }
		$pre_val=array();
		foreach ($values as $key=>$val) {
			$pre_val[]=array_key_exists($val,$especial)?$especial[$val]:Str::xss_filter($val);
		}
		if($prep->execute($pre_val)){
		    $result=$this->pdo_obj->lastInsertId();
		    return $result?$result:true;
		}else{
			
		    throw new \Exception("sqlite error:".json_encode($prep->errorInfo()));
			return false;
		}
	}
	public function update($values,$table,$where,$especial=null)
	{
		if(empty($values))return false;
		$bindParam=array();
		$pre_sql="update ".$table." set ";
		foreach ($values as $key=>$val) {
			$val=array_key_exists($val,$especial)?$especial[$val]:Str::xss_filter($val);
			$bindParam[":".$key]=$val;
			$pre_sql.=$key."=:".$key.",";
		}
		$this->last_sql=$pre_sql=rtrim($pre_sql,",")." where ".$where;
		$prep=$this->pdo_obj->prepare($pre_sql);
		if($prep==false){
		    echo $this->last_sql;
		    return false;
		}
		return $prep->execute($bindParam);
	}

	public function execute($sql){
		$this->last_sql=$sql;
		return $this->pdo_obj->exec($sql);
	}
	public function alterColumnType($table,$column,$type){
	    $table_info=$this->getFirst("select * from sqlite_master where name='$table' and type='table'");
	    if($table_info){
	       $copy_sql="create table $table"."_tmp as select * from $table ";
	       $tab_split=explode(" ",$table_info['sql']);
	       $col_i=0;
	       $new_tab_sql="";
	       foreach ($tab_split as $spi=>$split){
	           if("`$column`"==$split){
	               $col_i=$spi;
	               $new_tab_sql.=" ".$split;
	           }else if($col_i==0){
	               $new_tab_sql.=" ".$split;
	           }else{
	               $new_tab_sql.=" ".$type;
	               $col_i=0;
	           }
	       }
	       return $this->execute("$copy_sql ;drop table $table ;$new_tab_sql; insert into $table select * from $table"."_tmp;drop table $table"."_tmp;");
	       
	    }else{
	        return false;
	    }
	}
	public function dropColumn($table,$column){
	    
	}
	public function getArray($sql){
		$this->last_sql=$sql;
		$sth = $this->pdo_obj->prepare($sql);
		$sth->execute();
		return $sth->fetchAll(\PDO::FETCH_ASSOC);
	}
	public function getFirst($sql){
		$this->last_sql=$sql;
		$sth = $this->pdo_obj->prepare($sql);
		if($sth==null){
		    throw new \Exception("sql:".$sql);
		    return;
		}
		$sth->execute();
		$array=array();
		while ($r=$sth->fetch(\PDO::FETCH_ASSOC)){
			return $r;
		}
		return $array;
	}
	public function getIterator($sql){

		$data_api=new \W3cAppDataApi(array("idx"=>0));
        $data_api->setReuseAble(true);
        $data_api->onInited(function($api,$prepare_val)use($sql){
            if (is_array($prepare_val)) {
                foreach ($prepare_val as $search => $rep)
                    $sql = strtr($sql, '{' . $search . "}", $rep);
            }
            $this->last_sql = $sql;
            $sth = $this->pdo_obj->prepare($sql);
            if ($sth->execute() === false) {
                throw new \ErrorException(var_export($sth->errorInfo(), true));
            }
            $api->setInitVal('statement', $sth);
        });
		$data_api->onRewind(function($api){
		    $api->setInitVal('idx',0);
		});
		$data_api->onKey(function($api){
		    return $api->getInitVal("idx");
		});
		$data_api->onNext(function($api){
		    $api->setInitVal('idx',1+$api->getInitVal("idx"));
		});
		$data_api->onValid(function($api){
            if($api->getInitVal('statement')==null)
                return false;
            $current=$api->getInitVal('statement')->fetch(\PDO::FETCH_ASSOC);
            if($current==false){
                $api->setInitVal('statement', null);
                return false;
            }
            $api->setInitVal("current",$current);
            return true;
	    });

		$data_api->onCurrent(function($api){
            return $api->getInitVal("current");
        });
        $data_api->onCount(function($api){
            if($api->getInitVal("statement")){
                return $api->getInitVal("statement")->rowCount();
            }else{
                return 0;
            }
        });
		return $data_api;
	}
	public function tableExisted($table_name){
		$sql="select * from sqlite_master where type='table' and name=:table";
		$this->last_sql=$sql;
		$resoure = $this->pdo_obj->prepare($sql);
		$resoure->execute(array("table"=>$table_name));
		$array=array();
		while ($r=$resoure->fetch(\PDO::FETCH_ASSOC)){
			return true;
		}
		return false;
	}
    public function tableDesc($table,&$primary=null){
        $desc_table=[];
        $resoure = $this->pdo_obj->prepare("select * from sqlite_master where type='table' and name=:table");
        $resoure->execute(array("table"=>$table));
        while ($r=$resoure->fetch(\PDO::FETCH_ASSOC)){
            $sql=$r['sql'];
            $stri=strpos($sql,"(");
            $sql=trim(substr($sql,$stri),"\n\r\t;");
            $sql_split=explode(",",$sql);

            foreach ($sql_split as $column){
                $item=['Comment'=>'',"Null"=>"NO","Default"=>null];
                $column=trim($column,"\n\r\t ");
                if(strpos($column,"-- ")===0)continue;

                $column=str_replace("  "," ",$column);
                if(stripos($column,"PRIMARY")===false){
                    if(preg_match('/`(\w+)`/',$column,$m)){
                        $item['Field']=$m[1];
                        $cs=explode(" ",$column);
                    }else{
                        $cs=explode(" ",$column);
                        $item['Field']=$cs[0];
                    }
                    $item['Type']=$cs[1];
                    if(stripos($column,"not null")===false){
                        $item['Null']="YES";
                    }
                    if(preg_match('/default\s*\'?(\S*)\'?/',$column,$m))
                    {
                        $item['Default']=$m[1];
                    }
                }else{
                    //主键
                    if(preg_match('/key\s*\(`?(\w+)`?\)/',$column,$m))
                    {
                        $primary=$m[1];
                        continue;
                    }else if(preg_match('/`(\w+)`/',$column,$m)){
                        $primary=$m[1];
                        $cs=explode(" ",$column);
                    }else{
                        $cs=explode(" ",$column);
                        $primary=$cs[0];
                    }
                    $item['Field']=$primary;
                    $item['Type']=$cs[1];
                    if(stripos($column,"not null")===false){
                        $item['Null']="YES";
                    }
                    if(preg_match('/default\s*\'?(\S*)\'?/',$column,$m))
                    {
                        $item['Default']=$m[1];
                    }
                }
                $desc_table[]=$item;
            }

        }
        return $desc_table;
    }
	public function getSql(){
		return $this->last_sql;
	}
	public function errorInfo(){
		return $this->pdo_obj->errorInfo();
	}
    public function tryCommit($program,$catch,$final){
        $this->beginTransaction();
        try{
            if($program()){
                $this->commit();
            }else{
                throw new \Exception("program return false;");
            }
        }catch (\Exception $exception){
            $this->rollBack();
            $catch($exception);
        }
        if($final){
            $final();
        }
    }
    public function beginTransaction(){
        $this->pdo_obj->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->pdo_obj->beginTransaction();
    }
    public function commit(){
        $this->pdo_obj->commit();
    }
    public function rollBack(){
        $this->pdo_obj->rollBack();
    }
}
