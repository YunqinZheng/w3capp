<?php
namespace w3c\driver;
interface DataInterface{
    public function tryCommit($program,$catch,$final);
    public function beginTransaction();
    public function commit();
    public function rollBack();
    static public function init($config);
    public function alterColumnType($table,$column,$type);
    public function query($q);
    public function insert($values,$table,$especial=null);
    public function update($values,$table,$where,$especial=null);
    public function execute($q,$d=null,$id=false);

    /**
     * @param $q
     * @return array
     */
    public function getArray($q);

    /**
     * @param $q string
     * @return array
     */
    public function getFirst($q);

    /**
     * @param $q string
     * @return \W3cAppDataApi
     */
    public function getIterator($q);
    public function tableExisted($table_name);
    public function getSql();
    public function errorInfo();
    public function dbVersoin();
}