<?php
namespace cms\model;
use common\model\ChannelFormRecord;
use common\model\ChannelTypeRecord;
use common\model\SiteConfig;
use common\model\SysFeatureMenu;
use helper\ContentForm;
use w3c\helper\Str;

class ContentType extends ChannelTypeRecord {

    /**
     * 表名,不包括表前缀
     */
    static function contentTable($iden){
        return 'content_'.$iden;
    }
    static public function contentRecord($mark,$id=""){
        $class="content\\model\\".$mark;
        $mod=empty($id)?new $class:$class::record(["id"=>$id]);//self::_m(self::$obj_pre.$mark);

        return $mod;
    }
    public function contentExisted($mark){
        return $this->d()->tableExisted($this->m()->table(self::contentTable($mark)));
    }
    /**
     * 创建内容表
     */
    static public function createForm($iden){
        $result=true;
        $m=static::myAdapter();
        $chtab=$m->table(self::contentTable($iden));
        $errors="";
        if($m->db()->tableExisted($chtab)){
            $errors="表已存在！";
            return false;
        }
        $m->db()->tryCommit(function()use($chtab,$iden,&$errors,$m){


            $sql="CREATE TABLE `{$chtab}` (
  		`id` int(10) unsigned NOT NULL AUTO_INCREMENT,`title` varchar(200),
  		`views` INTEGER UNSIGNED,
  		`channel_id` INTEGER UNSIGNED NOT NULL,
  		`deprecated` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  		`dateline` INTEGER UNSIGNED,
  		`keywords` VARCHAR(300),
  		`description` VARCHAR(300),
  		`author` VARCHAR(50),PRIMARY KEY (`id`))ENGINE = INNODB default charset ".W3CA_DB_CHAR_SET;
            return $m->db()->execute($sql)&&ChannelFormRecord::batchInsert(array(
                    array("zh_name"=>'标题', "col_name"=>'title', "data_type"=>'varchar(200)',
                        "form_input"=>'text', "orderi"=>'0', "content_mark"=>$iden),
                    array("zh_name"=>'所属栏目', "col_name"=>'channel_id', "data_type"=>'INTEGER',
                        "form_input"=>'channel', "orderi"=>1, "content_mark"=>$iden),
                    array("zh_name"=>'关键字', "col_name"=>'keywords', "data_type"=>'VARCHAR(300)',
                        "form_input"=>'text', "orderi"=>2, "content_mark"=>$iden,'member_able'=>"0"),
                    array("zh_name"=>'描述', "col_name"=>'description', "data_type"=>'varchar(300)',
                        "form_input"=>'textarea', "orderi"=>3, "content_mark"=>$iden,'member_able'=>"0"),
                    array("zh_name"=>'发布时间', "col_name"=>'dateline', "data_type"=>'INTEGER',
                        "form_input"=>'datetime', "orderi"=>4, "content_mark"=>$iden,'member_able'=>"0"),
                    array("zh_name"=>'删除标记', "col_name"=>'deprecated', "data_type"=>'TINYINT',
                        "form_input"=>'hidden', "orderi"=>4, "content_mark"=>$iden,'member_able'=>"0"),
                    array("zh_name"=>'作者',"col_name"=>'author', "data_type"=>'VARCHAR(50)',
                        "form_input"=>'text', "orderi"=>5, "content_mark"=>$iden,'member_able'=>"0"),
                    array("zh_name"=>'查看数', "col_name"=>'views', "data_type"=>'INTEGER',
                        "form_input"=>'text', "orderi"=>6, "content_mark"=>$iden,'member_able'=>"0")
                ));
        },function($exception)use(&$result){
            throw $exception;
            $result=false;
        });
        return $result;
    }
    //内容发布表单
    static public function formTpl($type){
        $form=array();
        if($type=="main_form")
            $dir=W3CA_MASTER_PATH."app/content/view/form";
        else {
            $dir=W3CA_THEME_TPL.SiteConfig::getSetting("style")."/form";
        }

        $d=dir($dir);
        if(!$d){
            return $form;
        }
        while(false !== ($file = $d->read())) {
            $full_path=$dir."/".$file;
            if($file!="."&&$file!=".."&&is_file($full_path)){
                if(strpos($file,".htm"))
                    $form[str_replace(".htm","",$file)]=$file;
            }

        }
        $d->close();
        return $form;

    }


    static public function addType($content_mark,$name,$member_publish){
        $record=self::record(["content_mark"=>$content_mark],true);
        $record->setAttributes(["type_name"=>$name,"content_mark"=>$content_mark,"member_publish"=>$member_publish]);
        if($record->save()!==false){
            $menu=new SysFeatureMenu(array("name"=>$name,"keyid"=>$record->primary(),"pid"=>'db865672-0047-11e9-ac2d-00ffe5b222bf',"url"=>"{ROOT}content/index/".$content_mark));
            $menu->id=Str::guid();
            $menu->save();
        }
        return $record->primary();
    }
    static public function updateType($id,$name,$member_form,$main_form,$member_publish,$ext=null){
        $v=array("type_name"=>$name,"member_form"=>$member_form,"main_form"=>$main_form,"member_publish"=>$member_publish);
        if($ext)$v['extends']=$ext;
        $record=self::record(["id"=>$id],true);
        $record->setAttributes($v);
        return $record->save();
    }

    static public function recordCode($identify){
        $rs_col=ChannelFormRecord::findAll(["content_mark"=>$identify],"order by orderi");
        $member_val=[];
        $member_code=$default_code='';
        $default_val=[];
        $property=['id'=>'id'];
        foreach ($rs_col as $col){
            $property[$col['col_name']]=$col['zh_name'];
            if($col['def_value']){
                if($col['def_value']=='{MEMBER_ID}'){
                    $member_val[]='"'.$col['col_name'].'"=>$member_info[\'id\']';
                    $default_val[]='"'.$col['col_name'].'"=>$input["'.$col['col_name'].'"]';
                }else if($col['def_value']=='{MEMBER_NAME}'){
                    $member_val[]='"'.$col['col_name'].'"=>$member_info[\'name\']';
                    $default_val[]='"'.$col['col_name'].'"=>$input["'.$col['col_name'].'"]';
                }else if($col['def_value']=='{MEMBER_EMAIL}'){
                    $member_val[]='"'.$col['col_name'].'"=>$member_info[\'email\']';
                    $default_val[]='"'.$col['col_name'].'"=>$input["'.$col['col_name'].'"]';
                }else if($col['def_value']=='{UTC_TIME}'){
                    $member_val[]='"'.$col['col_name'].'"=>time()';
                    $default_val[]='"'.$col['col_name'].'"=>empty($input["'.$col['col_name'].'"])?time():strtotime($input["'.$col['col_name'].'"])';
                }else if($col['form_input']=="audio"||$col['form_input']=="checkbox"||$col['form_input']=="select"||$col['form_input']=="mselect"){
                    $def_list=ContentForm::valueList($col['def_value']);
                    if(empty($def_list['value_list'])){
                        $default_val[]=$member_val[]='"'.$col['col_name'].'"=>empty($input["'.$col['col_name'].'"])?'.$def_list['def_value'].':$input["'.$col['col_name'].'"]';
                    }else{
                        $v_keys=array_keys($def_list['value_list']);
                        $code="\n        if(in_array(\$input[\"".$col['col_name'].'"],array("'.implode('","',$v_keys).'")))$data["'.$col['col_name'].'"]=$input["'.$col['col_name'].'"];';
                        $member_code.=$code;
                        $default_code.=$code;
                    }
                }else if(preg_match('/\/(.+)\//',$col['def_value'])&&($col['form_input']=="text"||$col['form_input']=="textarea")){
                    $code="\n        if(preg_match('".$col['def_value']."',\$input[\"".$col['col_name'].'"]))$data["'.$col['col_name'].'"]=$input["'.$col['col_name'].'"];';
                    $member_code.=$code;
                    $default_code.=$code;
                }else{
                    $default_val[]=$member_val[]='"'.$col['col_name'].'"=>empty($input["'.$col['col_name'].'"])?'.$col['def_value'].':$input["'.$col['col_name'].'"]';
                }
            }else{
                $val='"=>$input["'.$col['col_name'].'"]';
                if($col['data_type']=='int'&&($col['form_input']=='datetime'||$col['form_input']=='date')){
                    $val='"=>empty($input["'.$col['col_name'].'"])?0:strtotime($input["'.$col['col_name'].'"])';
                }
                /*else if($col['data_type']=='int'){
                    $val='"=>intval($input["'.$col['col_name'].'"])';
                }else if($col['data_type']=='float'){
                    $val='"=>floatval($input["'.$col['col_name'].'"])';
                }*/
                if($col['member_able']){
                    $member_val[]='"'.$col['col_name'].$val;
                }
                if($col['form_input']!="hidden")
                    $default_val[]='"'.$col['col_name'].$val;

            }
        }
        return "<?php\nnamespace content\\model;\nclass $identify extends \\W3cRecord{\n    static public function recordName(){
        return \"content_$identify\";\n    }\n    static public function recordRule(){\n        return ".
            static::getTableRule(static::contentTable($identify))
            .";\n    }\n    static public function propertyDesc(){\n        return ".
            var_export($property,true)
            .";\n    }\n    //后台查询条件\n    static function defaultCondition(){\n        return [];\n        }
    //会员中心查询条件\n    static function memberCondition(){\n        return [];\n        }
    //后台表单\n    static function defaultForm(\$input){\n        \$data = array(".implode(",",$default_val).");\n        ".$default_code."\n        return \$data;\n    }
    //会员表单
    static function memberForm(\$member_info,\$input){\n        \$data = array(".implode(",",$member_val).");\n        ".$member_code."\n        return \$data;\n    }\n}";
    }

    /**
     * 生成约束规则
     * @param $table_name
     * @return string
     */
    static function getTableRule($table_name){
        $model=static::myAdapter();
        $list=$model->db()->getArray("desc ".$model->table($table_name));
        $require=[];
        $integer=[];
        $float=[];
        $strings=[];
        foreach ($list as $field_set){
            if($field_set['Null']=="NO"&&!$field_set['Default']&&$field_set['Extra']!='auto_increment'){
                $require[]=$field_set['Field'];
            }
            if(strpos($field_set['Type'],"int")||strpos($field_set['Type'],"int")===0){
                if($field_set['Extra']!='auto_increment'){
                    $integer[]=$field_set['Field'];
                }
            }else if(strpos($field_set['Type'],"float")===0
                ||strpos($field_set['Type'],"decimal")===0
                ||strpos($field_set['Type'],"money")===0
                ||strpos($field_set['Type'],"double")===0){
                $float[]=$field_set['Field'];
            }else{
                if(preg_match('/char\((\d+)\)/',$field_set['Type'],$m)){
                    $strings[$m[1]][]=$field_set['Field'];
                }else{
                    $strings[0][]=$field_set['Field'];
                }
            }
        }
        $rule=[];
        if(!empty($require)){
            $rule[]='[[\''.implode("','",$require)."'],\"require\"]";
        }
        if(!empty($integer)){
            $rule[]='[[\''.implode("','",$integer)."'],\"integer\"]";
        }
        if(!empty($float)){
            $rule[]='[['.implode("','",$float)."],\"float\"]";
        }
        foreach ($strings as $l=>$ss){
            if($l>0){
                $rule[]='[[\''.implode("','",$ss)."'],\"string\",$l]";
            }else{
                $rule[]='[[\''.implode("','",$ss)."'],\"string\"]";
            }
        }
        return "[".implode(",        \n",$rule)."]";
    }
     /**
     * 添加字段
     */
    static public function addColumn($zh_name, $col_name, $data_type, $form_input, $def_value, $order, $ctiden,$member_able=1,$table_alter=1){
        $form_adapter=ChannelFormRecord::myAdapter();

        if(ChannelFormRecord::firstAttr(["col_name"=>$col_name,"content_mark"=>$ctiden],"id"))
            return -1;
        $content_tab=$form_adapter->table(self::contentTable($ctiden));
        if($form_adapter->db()->tableExisted($content_tab)==false){
            //添加新表
            $sql="CREATE TABLE `".$content_tab."` (
  		`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  		PRIMARY KEY (`id`))ENGINE = INNODB default charset ".W3CA_DB_CHAR_SET;
            $form_adapter->db()->execute($sql);
        }
        if($table_alter&&!$form_adapter->db()->execute("alter TABLE `".$content_tab."` add column `$col_name` $data_type"))
            return 0;
        $record=new ChannelFormRecord($form_adapter);
        $record->setAttributes(array("zh_name"=>$zh_name, "col_name"=>$col_name, "data_type"=>$data_type,
            "form_input"=>$form_input,"def_value"=>$def_value,"orderi"=>$order, "content_mark"=>$ctiden,"col_name"=>$col_name,"member_able"=>$member_able));

        return $record->save();
    }
    /**
     * 修改字段
     */
    static public function alterColumn($zh_name,$old_column, $col_name, $data_type, $form_input, $def_value, $order, $ctiden,$member_able=1,$table_alter=1){
        $record=ChannelFormRecord::record(['col_name'=>$old_column,'content_mark'=>$ctiden]);
        if($old_column!=$col_name){
            if($record->d()->execute("alter TABLE `".$record->m()->table(self::contentTable($ctiden))."` CHANGE COLUMN `$old_column` `$col_name` $data_type")===false){
                return 0;
            }
        }else if($table_alter&&!$record->d()->execute("alter TABLE `".$record->m()->table(self::contentTable($ctiden))."` MODIFY column `$col_name` $data_type")){
            return 0;
        }
        $record->setAttributes(array("zh_name"=>$zh_name, "data_type"=>$data_type,
            "form_input"=>$form_input, "def_value"=>$def_value, "orderi"=>$order,"member_able"=>$member_able,"col_name"=>$col_name));
        $rs=$record->save();

        return $rs;
    }

    public function import($replace,$file){
        $fp=fopen($file,"r");
        $main_ct="";
        $attr=[];
        $table_ex="";
        while(!feof($fp)){
            $ct=fgets($fp);
            if($ct){
                if(empty($attr)){
                    $attr=json_decode(substr($ct,3),true);
                    
                }else if(strpos($ct,"-- t//")===0){
                    $table_ex=substr($ct,6);
                }else{
                    $main_ct.=$ct;
                }
                
            }
        }
        fclose($fp);
        if(empty($attr)||empty($table_ex)||empty($attr['content_mark'])){
            $this->errors[]="文件格式错误！";
            return false;
        }
        $d=self::record(["content_mark"=>$attr['content_mark']]);
        if($d){
            if($replace){
                $d->delete();
            }else{
                $this->errors[]="模型已存在";
                return false;
            }
        }
        ChannelFormRecord::deleteAll(['content_mark'=>$attr['content_mark']]);
        $form_adapter=\W3cCore::_adapter(self::contentTable($attr['content_mark']),self::adapterClass(),"");
		$table_name=$form_adapter->tableName();
        if($table_ex!=$table_name){
            $main_ct=str_replace('`'.$table_ex.'`','`'.$table_name.'`',$main_ct);
        }
        if($replace){
            $form_adapter->db()->execute("drop table IF EXISTS `$table_name`");
        }
        if($form_adapter->db()->execute($main_ct)){

            unset($attr['id']);
            $this->setAttributes($attr);
            return $this->save();
        }else{
            return false;
        }
    }
    public function exportSql(){
        $mark=$this->content_mark;
        $form_adapter=\W3cCore::_adapter(self::contentTable($mark),self::adapterClass(),"");
		$table_name=$form_adapter->tableName();
		$sql = 'SHOW CREATE TABLE '.$table_name; 
		$sql_c=$form_adapter->db()->getFirst($sql);
        $createSql="-- t//".$table_name."\n";
		if($sql_c){
			$createSql.=str_replace('CREATE TABLE','CREATE TABLE IF NOT EXISTS',$sql_c['Create Table']).";\n";
		}else{
			$this->errros[]="";
            return $table_name." table no found";
		}
		//$sql = "select * from ".$table_name;
		$p_id="id";
		$q_array=[];
		for($i=0;$i<10;++$i){
			$ps=10000;
			//$sql_d=$sql." where $p_id<=".($i*$ps+10000)." and $p_id>".($i*$ps);
			$q_rs=$form_adapter->select("*")->where(["<="=>[$p_id,$i*$ps+10000],">"=>[$p_id,$i*$ps]])->query();
			$has_c=count($q_array);
			foreach($q_rs as $item){
				$columns="";
				$vals="";
				foreach($item as $c=>$val){
					$val=addslashes($val);
					if($columns){
						$columns.=",`$c`";
						if($val===null){
							$vals.=",null";
						}else{
							$vals.=",'$val'";
						}
					}else{
						$columns="`$c`";
						if($val===null){
							$vals="null";
						}else{
							$vals.="'$val'";
						}
					}
				}
				$q_array[]="insert into `$table_name`(".$columns.")VALUES(".$vals.")";
			}
			if(count($q_array)==$has_c){
				break;
			}
		}
        $form_ad=ChannelFormRecord::adaptTo(['content_mark'=>$mark]);
		$form_set=$form_ad->query();
		
		foreach($form_set as $fs){
			$columns="";
			$vals="";
			foreach($fs as $c=>$val){
				if($c=="id")continue;
				$val=addslashes($val);
				if($columns){
					$columns.=",`$c`";
					if($val===null){
						$vals.=",null";
					}else{
						$vals.=",'$val'";
					}
				}else{
					$columns="`$c`";
					if($val===null){
						$vals="null";
					}else{
						$vals.="'$val'";
					}
				}
			}
			$q_array[]="insert into `".$form_ad->tableName()."` (".$columns.")VALUES(".$vals.")";
		}
		if(count($q_array)){
			$createSql.=implode(";\n",$q_array).";\n";
		}
        return $createSql;
    }
}