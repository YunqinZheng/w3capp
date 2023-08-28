<?php
namespace common\controller;
use member\model\Member;
require_once W3CA_MASTER_PATH.'app/app_store/oauth2-server-php/src/OAuth2/Autoloader.php';
\OAuth2\Autoloader::register();
class AppStoreCtrl extends \W3cController{

    function _clientCheck($client_id,$inAjax=true){
        $storage= new \OAuth2\Storage\MyPdo();
        $client=$storage->getClientDetails($client_id);
        if($storage->clientExists($client_id,Member::loginMember()->appUserId())==false||empty($client)){
            if($inAjax)
                return $this->_json_return(1,"client id error!");
            else
                return $this->_show_message("应用ID错误");
        }
        $this->_assign("client_id",$client_id);
        $this->_assign("client",$client);
    }
}