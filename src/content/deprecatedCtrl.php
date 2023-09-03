<?php
namespace content\controller;
use cms\model\ContentType;

class deprecatedCtrl extends mainCtrl{
    function _index($ctt){

        foreach ($_POST['rid'] as $rid){
            $content_mode=ContentType::contentRecord($ctt,$rid);
            if($content_mode->primary()){
                $content_mode->setAttribute("deprecated",1);
                $content_mode->save();
            }
        }
        $url=$_SERVER['HTTP_REFERER']?$_SERVER['HTTP_REFERER']:\self::$app->route("content/add/".$ctt);
        \W3cUI::message("内容已经转移动回收站","right", array(
            array("href"=>$url,"text"=>"返回","target"=>""),
            array("href"=>\self::$app->route("content/index/".$ctt),"text"=>"内容管理","target"=>""),
            array("href"=>\self::$app->route("content/recycle/".$ctt),"text"=>"查看回收站","target"=>"")));
    }
}
