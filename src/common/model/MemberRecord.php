<?php 
namespace common\model;
/**
 * w3ca_member数据记录类
 * @property string $id id
 * @property string $name name
 * @property string $password password
 * @property string $auth_key auth_key
 * @property string $email email
 * @property string $uid uid
 * @property string $groupid groupid
 * @property string $regip regip
 * @property string $regdate regdate
 * @property string $lastlogip lastlogip
 * @property string $lastlogtime lastlogtime
 * @property string $headimg headimg
 * @property string $mobile mobile
 * @property string $follow_count follow_count
 * @property string $fans_count fans_count
 * @property string $salt salt
 * @property string $email_checked email_checked
 */
class MemberRecord extends \W3cRecord{
    
    static public function recordName(){
        return 'member';
    }

    static public function recordRule(){
        return [[['name','password','email','regdate'],"require"],        
            [['uid','groupid','regdate','lastlogtime','follow_count','fans_count','email_checked'],"integer"],
            [['name'],"string",245],
            [['password','auth_key'],"string",50],
            [['email'],"string",45],
            [['regip','lastlogip','mobile'],"string",15],
            [['headimg'],"string",150],
            [['salt'],"string",10]];
    }

    static public function propertyDesc(){
        return array (
          'id' => 'id',
          'name' => 'name',
          'password' => 'password',
          'auth_key' => 'auth_key',
          'email' => 'email',
          'uid' => 'uid',
          'groupid' => 'groupid',
          'regip' => 'regip',
          'regdate' => 'regdate',
          'lastlogip' => 'lastlogip',
          'lastlogtime' => 'lastlogtime',
          'headimg' => 'headimg',
          'mobile' => 'mobile',
          'follow_count' => 'follow_count',
          'fans_count' => 'fans_count',
          'salt' => 'salt',
          'email_checked' => 'email_checked',
        );
    }
}