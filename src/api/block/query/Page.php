<?php
namespace api\block\query;

use w3capp\helper\Sql;

class Page extends Selector{
    protected $default_limit=0;
    function onCheckPrototype(&$data){
        if(false==empty($data['content_record'])){
            if(empty($data['base_space'])){
                $class="content\\model\\".$data['content_record'];
            }else{
                $class=$data['base_space']."\\model\\".$data['content_record'];
            }

            $record=new $class();
            $desc=$record->propertyDesc();
            if(empty($data['select'])){
                if(empty($data['alias'])){
                    $columns=$record->allColumnStr();
                }else{
                    $columns=$data['alias'].".`".implode("`,{$data['alias']}.`",array_keys($desc))."`";;
                }
            }else{
                if(strpos($data['select'],";")){
                    $data['error']=1;
                    $data['msg']="sql包含非法字符";
                    return;
                }
                $columns=$data['select'];
            }
            $rad=$record->myAdapter();
            $table=$rad->tableName();
            if(empty($data['alias'])){
                $sql="select ".$columns.' from '.$table;
                $sql_count="select count(1) c from ".$table;
            }else{
                $sql="select ".$columns.' from '.$table." as {$data['alias']} ";
                $sql_count="select count(1) c from ".$table." as {$data['alias']} ";
                if(false==empty($data['join'])){
                    if(is_array($data['join'])){
                        foreach($data['join'] as $rc=>$items){
                            if(empty($items['base_space'])){
                                $items['base_space']='content';
                            }
                            $join=$items['type']." join ".$rad->getTablePre().$rc." ".$items['alias'] ." on ".$items['on']." ";
                            $sql.=$join;
                            $sql_count.=$join;
                        }
                    }else{
                        $sql.=$data['join'];
                        $sql_count.=$data['join'];
                    }

                }
            }
            $where=is_array($data['condition'])?$data['condition']:[];
            if(empty($where['deprecated'])&&array_key_exists("deprecated",$desc)){
                if(empty($data['alias'])){
                    $where['deprecated']=0;
                }else{
                    $where[$data['alias'].'.deprecated']=0;
                }
            }
            $data['default_condition']=Sql::parse($where);
            if(empty($data['default_condition']))$data['default_condition']=1;
            $order='';
            if(empty($data['order'])==false){
                $order=Sql::parse($data['order'],'order');
            }
            $data['record_count']=str_replace('[pre]',$rad->getTablePre(),$sql_count)." where [where]";
            $data['zyq_sql']=$sql."  where [where] ".$order." limit {offset->int},{page_size->int}";
        }
        parent::onCheckPrototype($data);
    }
    public function loadData($args)
    {
        $record_count_sql=$this->info('record_count');
        if(empty($record_count_sql)){
            echo 'record_count_error';
            return null;
        }
        $record_count=empty($args['cache_count'])?0:intval($args['cache_count']);
        if(empty($args['condition'])){
            if($record_count==0){
                $record_count_sql=str_replace('[where]',$this->info('default_condition'),$record_count_sql);
                $q=$this->_db()->getFirst($record_count_sql);
                $record_count=$q['c'];
            }
            $this->block_info['pro_value']=str_replace('[where]',$this->info('default_condition'),$this->info('pro_value'));
        }else{
            if($record_count==0) {
                $record_count_sql = str_replace('[where]', Sql::parse($args['condition']), $record_count_sql);
                $q = $this->_db()->getFirst($record_count_sql);
                $record_count = $q['c'];
            }
            $this->block_info['pro_value']=str_replace('[where]',Sql::parse($args['condition']),$this->info('pro_value'));
        }
        if(empty($args['page_size'])){
            $args['page_size']=10;
        }
        if(empty($args['offset'])){
            if($args['page_index']){
                $args['offset']=($args['page_index']>0?$args['page_index']-1:0)*$args['page_size'];
            }else{
                $args['page_index']=1;
                $args['offset']="0";
            }
        }
        $page=parent::loadData($args);
        $page->amount=$record_count;
        $page->page_size=$args['page_size'];
        $page->page_index=$args['page_index'];
        return $page;
    }
}