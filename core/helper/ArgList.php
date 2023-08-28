<?php
namespace w3c\helper;
class ArgList implements \ArrayAccess{
    protected $args;
    public function __construct()
    {
        $this->args=array();
    }

    public function offsetExists($offset){
        return empty($this->args[$offset]);
    }
    public function offsetGet($offset){
        return empty($this->args[$offset])?null:$this->args[$offset];
    }
    public function offsetSet($offset, $value){
        if($offset){
            $this->args[$offset]=$value;
        }
    }
    public function offsetUnset($offset){
        unset($this->args[$offset]);
    }
}