<?php
namespace w3c\driver;
use w3c\driver\DataInterface;
use w3c\helper\Str;

/**
 * mysql类
 */
class MysqlPDO implements DataInterface{

	private $pdo_obj;
	private $last_sql;
	var $config;
	private function __construct($config){
	    $this->config=$config;
		$this->pdo_obj=new \PDO($config['dsn'],$config['user'],$config['pwd'],array(\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES '.W3CA_DB_CHAR_SET));

		if($this->pdo_obj==false){
			die("MysqlPDO connect 失败！");
			return ;
		}
	}
    public function dbVersoin(){
	    return 'mySql：'.$this->pdo_obj->getAttribute(\PDO::ATTR_SERVER_VERSION);
    }
    /**
     * @param $program \Closure
     * @param $catch \Closure
     * @param null $final
     */
	public function tryCommit($program,$catch,$final=null){
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
        if($final instanceof \Closure){
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
	static public function init($config){
		return new self($config);
	}
	public function __clone()
	{
		trigger_error('Clone is not allow' ,E_USER_ERROR);
	}

	public function alterColumnType($table,$column,$type){
	    $this->execute("alter table $table modify column `$column` $type");
	}
	function query($sql){
		$this->last_sql=$sql;
		return $this->pdo_obj->query($sql);
	}
	public function insert($values,$table,$especial=null){
		if(empty($values))return false;
		$columns=[];
		$pre_val=[];
		//$c_keys=[];
		foreach ($values as $key=>$val) {
		    if($especial instanceof \Closure){
                $pre_val[":".$key]=$especial($key,$val);
            }else{
                $pre_val[":".$key]=array_key_exists($val,$especial)?$especial[$val]:Str::xss_filter($val);
            }
			$columns[]=$key;//'`'.$key.'`';
			//$c_keys[]=$c_keys;
		}
		$this->last_sql=$pre_sql="insert INTO ".$table." (`".implode("`,`", $columns)."`) values (:".implode(',:', $columns).")";
		$prep=$this->pdo_obj->prepare($pre_sql);
		if($prep->execute($pre_val)){
		    $new_id=$this->pdo_obj->lastInsertId();
		    if($new_id){
		        return $new_id;
            }
			return true;
		}else{
		    throw new \ErrorException(var_export($prep->errorInfo(),true));
			return false;
		}
	}
	public function update($values,$table,$where,$especial=null)
	{
		if(empty($values))return false;
		$bindParam=array();
		$pre_sql="update ".$table." set ";
		foreach ($values as $key=>$val) {
            if($especial instanceof \Closure){
                $val =$especial($key,$val);
            }else {
                $val = array_key_exists($val, $especial) ? $especial[$val] : Str::xss_filter($val);
            }
			$bindParam[":".$key]=$val;
			$pre_sql.='`'.$key."`=:".$key.",";
		}
		$this->last_sql=$pre_sql=rtrim($pre_sql,",")." where ".$where;
		$prep=$this->pdo_obj->prepare($pre_sql);

        if($prep->execute($bindParam)===false){
			//echo $this->last_sql;exit;
            throw new \ErrorException(var_export($prep->errorInfo(),true));
            return false;
        }
        return true;
	}

	public function execute($sql,$bind=null,$re_id=false){
		$this->last_sql=$sql;
		$prep=$this->pdo_obj->prepare($sql);
        if($prep->execute($bind)===false){
            throw new \ErrorException(var_export($prep->errorInfo(),true));
            return false;
        }
		if($re_id){
			$new_id=$this->pdo_obj->lastInsertId();
			if($new_id){
				return $new_id;
			}
		}
		
        return true;
	}
	public function getArray($sql){
		$this->last_sql=$sql;
		$sth = $this->pdo_obj->prepare($sql);
        if($sth->execute()===false){
            throw new \ErrorException(var_export($sth->errorInfo(),true));
        }
		return $sth->fetchAll(\PDO::FETCH_ASSOC);
	}
	public function getFirst($sql){
		$this->last_sql=$sql;
		$sth = $this->pdo_obj->prepare($sql);
        if($sth->execute()===false){
			throw new \ErrorException(var_export($sth->errorInfo(),true));
        }
		$array=array();
		while ($r=$sth->fetch(\PDO::FETCH_ASSOC)){
			return $r;
		}
		return $array;
	}
	public function getIterator($sql){
		//$sth = $this->pdo_obj->prepare($sql);
		//if($sth->execute()===false){
        //    throw new \ErrorException(var_export($sth->errorInfo(),true));
        //}
		$data_api=new \W3cAppDataApi([]);
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
            $api->setInitVal('idx', 0);
		});
		$data_api->onKey(function($api){
			return $api->getInitVal("idx");
		});
		$data_api->onNext(function($api){
			$api->setInitVal('idx',1+$api->getInitVal("idx"));
		});
		$data_api->onValid(function($api){
            $resoure=$api->getInitVal("statement");
            if($resoure==null)
                return false;
            $current=$resoure->fetch(\PDO::FETCH_ASSOC);
            if($current==false){
                $api->setInitVal("statement",null);
                return false;
            }
            $api->setInitVal("current",$current);
            return true;
		});

		$data_api->onCurrent(function($api){
			return $api->getInitVal("current");
		});
		$data_api->onCount(function($api)use($sql){
		    if($api->getInitVal("statement")){
                return $api->getInitVal("statement")->rowCount();
            }else{
                return 0;
            }

		});
		return $data_api;//new DataObj($sth);
	}
	public function tableExisted($table_name){
		$sth = $this->pdo_obj->prepare("show tables");
		$sth->execute();
		while ($r=$sth->fetch(\PDO::FETCH_NUM)){
			if($r[0]==$table_name)return true;
			
		}
		return false;
	}

    /**
     * mysql 表结构
     * @param $table
     * @return array
     * @throws \ErrorException
     */
    public function tableDesc($table,&$primary=null){
        $desc_table=$this->getArray("desc ".$table);
        if($desc_table){
            $sql=array_values($this->getFirst("show create table ".$table));
            $sql_split=explode(",\n",$sql[1]);
            foreach ($sql_split as $item){

                foreach ($desc_table as &$column){
                    if($column['Key']=='PRI'){
                        $primary=$column['Field'];
                        //$this->_assign("primary",);
                    }
                    if(strpos($item," COMMENT")>0&&strpos($item,"`".$column['Field']."`")){
                        list($mf,$cm)=explode("COMMENT '",$item);
                        $column['Comment']=trim($cm,"'");
                    }else if(false==isset($column['Comment'])){
                        $column['Comment']='';
                    }
                }

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

}
