<?php
namespace common\model;
/**
 * w3ca_sys_user数据记录类
 * @property string $id id
 * @property string $name name
 * @property string $pwd pwd
 * @property string $pwd_hash pwd_hash
 * @property string $note note
 * @property string $roles roles
 * @property string $realname realname
 * @property string $tel tel
 * @property string $email email
 * @property string $option_type option_type
 * @property string $specify_rights specify_rights
 */
class SysUserRecord extends \W3cRecord{

    static public function recordName(){
        return 'sys_user';
    }

    static public function recordRule(){
        return [[['name','pwd','pwd_hash','option_type'],"require"],
            [['option_type'],"integer"],
            [['name','pwd','pwd_hash','realname'],"string",145],
            [['note'],"string",1245],
            [['roles'],"string",100],
            [['tel'],"string",30],
            [['email'],"string",150],
            [['specify_rights'],"string",400]];
    }

    static public function propertyDesc(){
        return array (
            'id' => 'id',
            'name' => 'name',
            'pwd' => 'pwd',
            'pwd_hash' => 'pwd_hash',
            'note' => 'note',
            'roles' => 'roles',
            'realname' => 'realname',
            'tel' => 'tel',
            'email' => 'email',
            'option_type' => 'option_type',
            'specify_rights' => 'specify_rights',
        );
    }
}