<?php
namespace api\controller;
use w3capp\Controller;
class versionCtrl extends Controler{
    public function index($v=null)
    {
        echo 'var W3CA_new="1.3";var W3CA_old="'.$v.'";if(W3CA_new!=W3CA_old)document.write("<a href=\"http://www.w3capp.com\" target="_blank">W3C_APP升级</a>");';
    }
    public function _action_unfound($fun,$arg){
        $this->index($fun);
    }
}