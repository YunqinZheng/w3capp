<?php
namespace content\controller;
use cms\model\ContentType;
class delCtrl extends mainCtrl{
    function _index($ctt){
        $result=true;
        $content_type=ContentType::record(["content_mark"=>$ctt]);
        foreach ($_POST['rid'] as $rid){
            $content=$content_type->contentRecord($ctt,$rid);
            if($content->getAttribute("deprecated")!=null){
                $content->setAttribute("deprecated",1);
                $result=$result&&$content->save();
            }else{
                $result=$result&&$content->delete();
            }
        }
        if($_POST['rid']&&$result){
            $url=$_SERVER['HTTP_REFERER']?$_SERVER['HTTP_REFERER']:\self::$app->route("content/add/".$ctt);
            \W3cUI::message("删除成功","right", array(
                array("href"=>$url,"text"=>"返回","target"=>""),
                array("href"=>\self::$app->route("content/index/".$ctt),"text"=>"内容管理","target"=>"")));
        }else{
            $url=$_SERVER['HTTP_REFERER']?$_SERVER['HTTP_REFERER']:\self::$app->route("content/add/".$ctt);
            \W3cUI::message("删除出错","errer", array(
                array("href"=>$url,"text"=>"返回","target"=>""),
                array("href"=>\self::$app->route("content/index/".$ctt),"text"=>"内容管理","target"=>"")));
        }
    }
}