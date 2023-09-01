<?php
namespace console\controller;
use cms\model\SysUser;

class testCtrl extends \W3cController {
    function code(){
        var_export(["a\n99","bb"]);
    }
    function pwd(){
        $u= SysUser::record(['name'=>'zhengyq']);
        $u->setPassword("pwd2021");
        echo $u->save();
    }
    function user(){
        $u=new SysUser();
        foreach($u as $k=>$v){
            echo $k;
            echo "=>".$v."\n";
        }
        $u->setAttributes(["name"=>"test","tel"=>"10001"]);
        foreach($u as $k=>$v){
            echo $k;
            echo "=>".$v."\n";
        }
    }
}