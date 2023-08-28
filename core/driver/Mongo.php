<?php
namespace w3c\driver;
class Mongo implements DataInterface {
    private $client;
    private $collection_pre;
    private $db;
    private function __construct($config){
        $this->connect($config);
    }
    private function connect($config){
        $this->client = new \MongoClient("mongodb://".($config['user']?($config['user'].":".$config['password']."@"):"").$config['host'].":".$config['port']);//new \MongoDB\Driver\Manager("mongodb://".($config['user']?($config['user'].":".$config['password']."@"):"").$config['host'].":".$config['port']);
        $this->db=$this->client->$config['db'];
        $this->collection_pre=$config['pre'];
    }
    static public function init($config){
        return new self($config);
    }
    public function __clone()
    {
        trigger_error('Clone is not allow' ,E_USER_ERROR);
    }
    public function table($tn){
        return $this->collection($tn);
    }
    public function collection($tn){
        return $this->collection_pre.$tn;
    }

    function query($q){

        $this->manager->executeQuery($q[0],$q[1]);
    }
    public function insert($values,$collection,$especial=null){

        return $this->db->$collection->insert($values);
    }
    public function update($values,$collection,$filter,$especial=null)
    {
        return $this->db->$collection->update($filter,['$set'=>$values]);
    }
    public $del_limit=0;
    public function delete($collection,$filter){
        return $this->db->$collection->remove($filter,$this->del_limit);
    }

    public function getArray($collection,$filter,$sort,$limit,$skip){
        $c=$this->db->$collection->find($filter);
        if($sort)
            $c->sort($sort);
        if($skip){
            $c->skip($skip);
        }
        if($limit){
            $c->limit($limit);
        }
        return $c;
    }
    public function getFirst($collection,$filter,$sort){
        $cursor = $this->db->$collection->find($filter);
        if($sort)
            $cursor->sort($sort);
        foreach($cursor as $doc){
            return $doc;
        }
        return null;
    }
    public function aggregate($c,$v){
        $this->manager->executeCommand($c,$v);
    }
    public function mkApi($cursor){
        $data_api=new \W3cAppDataApi([]);
        $key=0;
        $data_api->onRewind(function($api)use($cursor,&$key){
            $cursor->rewind();
            $key=-1;
        });
        $data_api->onKey(function($api)use($cursor,&$key){
            return ++$key;//$cursor->key();
        });
        $data_api->onNext(function($api)use($cursor){
            $cursor->next();
        });
        $data_api->onValid(function($api)use($cursor){

            return $cursor->valid();

        });

        $data_api->onCurrent(function($api)use($cursor){
            return $cursor->current();
        });
        $data_api->onCount(function($api)use($cursor){
            return $cursor->count();
        });
        $data_api->onFetchEnd(function($api){

        });
        return $data_api;
    }
    public function getIterator($collection,$query,$sort,$limit,$skip){

        $cursor = null;

        $data_api=new \W3cAppDataApi([]);
        $key=0;
        $data_api->onInited(function($api,$prepare_val)use($collection,$query,$sort,$limit,$skip,&$cursor){
            if(!array_key_exists('limit',$prepare_val)){
                $limit=$prepare_val['limit'];
            }
            if(!array_key_exists('sort',$prepare_val)){
                $limit=$prepare_val['sort'];
            }
            if(!array_key_exists('skip',$prepare_val)){
                $limit=$prepare_val['skip'];
            }
            $cursor=$this->getArray($collection,$query,$sort,$limit,$skip);
        });
        $data_api->onRewind(function($api)use($cursor,&$key){
            $key=-1;
            $cursor->rewind();
        });
        $data_api->onKey(function($api)use($cursor,&$key){
            return ++$key;//$cursor->key();
        });
        $data_api->onNext(function($api)use($cursor){
            $cursor->next();
        });
        $data_api->onValid(function($api)use($cursor){

            return $cursor->valid();

        });

        $data_api->onCurrent(function($api)use($cursor){
            return $cursor->current();
        });
        $data_api->onCount(function($api)use($cursor){
            return $cursor->count();
        });
        return $data_api;
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

    public function alterColumnType($table, $column, $type)
    {
        // TODO: Implement alterColumnType() method.
    }

    public function execute($q)
    {
        // TODO: Implement execute() method.
    }

    public function tableExisted($table_name)
    {
        // TODO: Implement tableExisted() method.
    }

    public function getSql()
    {
        // TODO: Implement getSql() method.
    }

    public function errorInfo()
    {
        // TODO: Implement errorInfo() method.
    }

    public function dbVersoin()
    {
        // TODO: Implement dbVersoin() method.
        return 'mongo';
    }
}