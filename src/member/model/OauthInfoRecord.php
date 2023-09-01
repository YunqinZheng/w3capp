<?php 
namespace member\model;
/**
 * w3ca_oauth_info数据记录类
 * @property string $id id
 * @property string $member_id member_id
 * @property string $appid appid
 * @property string $open_id open_id
 * @property string $access_token access_token
 * @property string $token_time token_time
 * @property string $type type
 * @property string $nickname nickname
 * @property string $image image
 */
class OauthInfoRecord extends \W3cRecord{
    
    static public function recordName(){
        return 'oauth_info';
    }

    static public function recordRule(){
        return [[['access_token','image'],"require"],        
[['member_id','token_time'],"integer"],        
[['appid','open_id'],"string",35],        
[['access_token'],"string",60],        
[['type'],"string",10],        
[['nickname'],"string",255],        
[['image'],"string",200]];
    }

    static public function propertyDesc(){
        return array (
  'id' => 'id',
  'member_id' => 'member_id',
  'appid' => 'appid',
  'open_id' => 'open_id',
  'access_token' => 'access_token',
  'token_time' => 'token_time',
  'type' => 'type',
  'nickname' => 'nickname',
  'image' => 'image',
);
    }
}