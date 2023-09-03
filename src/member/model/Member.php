<?php
namespace member\model;
use common\model\SiteConfig;
use w3c\helper\Image;
use w3c\helper\Cache;
/**
 * 会员
 */
class Member extends \common\model\MemberRecord {
    protected static $member_info=null;
    protected static $avatar_size_list=array("min"=>45,"mid"=>72,"max"=>120,"max2"=>192);
    protected static $set_data=array();

    function saveConfig($P){
        $cache=new Cache('file');
        self::$set_data=$P;
        $cache->saveValue("member_config",serialize($P),0);
        SiteConfig::saveConfigs(self::$set_data);
    }
    static function configInfo($key=""){
        if(self::$set_data)return $key?self::$set_data[$key]:self::$set_data;
        $cache=new Cache('file');
        if($cache->valueExists("member_config")){
            self::$set_data=unserialize($cache->value("member_config"));
        }else{
            $data=SiteConfig::findAllData(["id"=>["open_regist","check_type","agreement","black_names",
                "qq_appid","qq_appkey","wb_client_id","wb_client_secret","wx_appid","alipay_appid","alipay_public_key","alipay_private_key"]]);
			foreach($data as $d){
				self::$set_data[$d['id']]=$d['conf_val'];
			}
        }
        return $key?self::$set_data[$key]:self::$set_data;
    }
    /**
     * 返回数字：1登录后台没关联会员，2正常会员
     */
    static function login($name,$password){

        if(\self::$app->startSession()==false){
            die("session error");
        }
        if(empty(\self::$app->getSession()->member_info)){
            if($name&&$password){
                $mb=self::record(["or"=>["name"=>$name,"email"=>$name]]);
                if($mb['password']==md5($password.$mb['salt'])){
                    $info=$mb->getAttributes();
					$mb->lastlogip=$_SERVER['REMOTE_ADDR'];
					$mb->lastlogtime=time();
					$mb->save();
                    unset($info['password'],$info['salt']);
                    \self::$app->getSession()->member_info=serialize($info);
                    self::$member_info=$info;
                    return 2;
                }
            }
        }else{
            self::$member_info=unserialize(\self::$app->getSession()->member_info);
            if(self::$member_info['id']) return 2;
        }

        return 0;
    }
	public function setPassword($pwd){
		$this->salt="";
		$this->password=$pwd;
	}
    static function loginById($member_id){

        if(\self::$app->startSession()==false){
            die("session error");
        }
        $info=new self(["id"=>$member_id]);
        unset($info['password'],$info['salt']);
        $_SESSION['member_info']=serialize($info);
        self::$member_info=$info;
    }
    static function loginByToken($token){
        $pre=\W3cUI::previousCookie();
        list($pwd_sub,$user_id,$hash,$cookie_pre)=explode("|",base64_decode($token));
        if($cookie_pre==$pre&&self::check_form_hash($hash,15)){
            $mb=new self(['id'=>$user_id]);
            if(substr($mb['password'],2,6)==$pwd_sub){
                \self::$app->startSession();
                $info=$mb->getAttributes();
                $_SESSION['member_info']=serialize($info);
                self::$member_info=$info;
                return true;
            }
        }
        return false;
    }
    function getToken(){
        $pre=\W3cUI::previousCookie();
        return base64_encode(implode("|",array(substr($this->password,2,6),$this->id,self::_form_hash(),$pre)));
    }
    function appUserId(){
        $id=$this->info("id");
        return $id?'u'.$this->info("id"):'';
    }
    static function info($key=null){

        if(\self::$app->startSession()==false){
            die("session error");
        }
        if(self::$member_info){
            return $key==null?self::$member_info:self::$member_info[$key];
        }
        if(empty($_SESSION['member_info'])){
            return null;
        }else{
            self::$member_info=unserialize($_SESSION['member_info']);
        }
        return $key==null?self::$member_info:self::$member_info[$key];
    }

    static function logout(){

        if(\self::$app->startSession()==false){
            die("session error");
        }
        $_SESSION['member_info']="";

    }
    static function avatarPath($member_id){
    	if(!is_writable(W3CA_MASTER_PATH.'data/member/avatar')){
    		throw new \Exception('dir:data/member/avatar not found');
    	}
        if($member_id<=500){
            $sys_dir=W3CA_MASTER_PATH."data/member/avatar/0x00";
            if(!file_exists($sys_dir))
                @mkdir($sys_dir);
            return "data/member/avatar/0x00/".$member_id;
        }else{
            $path=dechex($member_id)."00";
            $path2="";
            $len=strlen($path);
            for($i=0,$l=2;$l<$len;$l=$l+2){
                $path2=$path2.substr($path, $l*$i++,2)."/";
            }
            $sys_dir=W3CA_MASTER_PATH."data/member/avatar/".$path2;
            if(!file_exists($sys_dir))
                @mkdir($sys_dir);
            return "data/member/avatar/".$path."/".($member_id%1000);
        }
    }

    function makeAvatar($image){
        $file_img=W3CA_MASTER_PATH.$this->originalAvatarDir().$image;
		$image_opt=new Image();
        $image_opt->loadImage($file_img);
        $path=self::avatarPath($this->id);
        $ext=stripos($image,".gif")?"gif":"jpeg";

        foreach(self::$avatar_size_list as $k=>$v){
            $image_opt->copyImageArea($v,$v,W3CA_MASTER_PATH.$path."_".$k.".".$ext,$ext);
        }
    }
    function makeAvatarXYSize($image,$x,$y,$src_size){
        $file_img=W3CA_MASTER_PATH.$this->originalAvatarDir().$image;
		$image_opt=new Image();
        $image_opt->loadImage($file_img);
        $path=self::avatarPath($this->id);
        $ext=stripos($image,".gif")?"gif":"jpeg";

        foreach(self::$avatar_size_list as $k=>$v){
			$file=W3CA_MASTER_PATH.$path."_".$k.".".$ext;
			if(file_exists($file))unlink($file);
            if(false==$image_opt->copyImageXYSize($x,$y,$src_size,$src_size,$v,$v,$file,$ext)){
				$this->errors[]="image copy size:".$src_size." error";
			}
        }
    }
    static function originalAvatarDir(){
    	if(false==is_writable(W3CA_MASTER_PATH.'data/member/original_img')){
    		throw new \Exception("dir:data/member/original_img not found");
    	}
        return "data/member/original_img/";
    }
    protected function restrain(&$replace){
        //加密密码
        if(empty($this->salt)&&empty($this->newAttributes['password'])==false){
            $this->newAttributes['salt']=dechex(rand(100000,900000));
            $this->newAttributes['password']=md5($this->newAttributes['password'].$this->newAttributes['salt']);
        }else{
            unset($this->newAttributes['password']);
            unset($this->newAttributes['salt']);
        }
        return parent::restrain($replace);
    }
    //是否为禁用
    public function isForbid(){
        return $this->groupid==99;
    }
    /**
     * 返回已登录会员
     * @return MemberRecord|null
     */
    static public function loginMember(){
        if(empty($_COOKIE['login_key'])){
            if(\self::$app->startSession()==false){
                return null;
            }
            if(empty($_SESSION['member_info'])){
                return null;
            }else{
                $member_info=unserialize($_SESSION['member_info']);
				$member_info['password']='0';
                $mb=new self();
                $mb->write($member_info);
                //刷新会员组和头像
                $attrs=self::firstAttr(['id'=>$mb->id],"groupid,headimg");
                if(empty($attrs)){
                    $_SESSION['member_info']="";
                    return null;
                }else{
                    $mb->write($attrs);
                }
                return $mb;
            }

        }else{
            return self::record(['auth_key'=>$_COOKIE['login_key']]);
        }
    }
    public function storeLogin(){
        if(empty($_COOKIE['login_key'])&&\self::$app->startSession()){
            $_SESSION['member_info']=serialize($this->getAttributes());
        }
    }

    /**
     * 头像
     * @param $size "min"=>45,"mid"=>72,"max"=>120,"max2"=>192
     */
    public function avatar($size){
        $path=Member::avatarPath($this->id);
        $img_path=$path."_".$size.".jpeg";
        $avatar=W3CA_MASTER_PATH.$img_path;
        if(file_exists($avatar)){
            return W3CA_URL_ROOT.$img_path;
        }
        $img_path=$path."_".$size.".gif";
        $avatar=W3CA_MASTER_PATH.$img_path;
        if(file_exists($avatar)){
            return W3CA_URL_ROOT.$img_path;
        }
        return W3CA_URL_ROOT."static/image/avatar-".$size.".png";
    }
}
