<?php
namespace w3capp\helper;
class Uploader{
	private $save_dir;
	/**
	 * 开启不替换
	 */
	private $no_replace=0;
	/**
	 * 类型列表，要求小写
	 */
	private $file_type;
	private $max_size;
	private $config;
	/**
	 * stdClass("sub_dir"=>子目录,"file_count"=>子目录文件数)
	 * @var Uploaderconfig
	 */
	protected $dir_info;
	protected $file_in_name;
	protected $file_in_size;
	protected $file_in_tmp;
	/**
	 * make_file_name调用次数
	 */
	var $mnc=0;
	var $file_in_error;
	/**
	 * 文件扩展名
	 */
	protected $file_in_ext;
	/**
	 * 自动生成的文件名的第一部分
	 */
	//protected $file_name1="";
	protected $main_dir="./";
	function mainDir($d){
        $this->main_dir=$d;
    }
	/**
	 * $save_dir要有/结尾
	 */
	function init($save_dir,$file_type,$replace_abled,$max_size=1024000){
		$this->no_replace=!$replace_abled;
		$this->save_dir=$this->main_dir.$save_dir;
		$this->max_size=$max_size;
		$this->file_type=$file_type;
	}
	/**
	 * 读取保存目录的的设置
	 */
	protected function auto_config(){
		$configfile=$this->save_dir.".upload_set";
		if(file_exists($configfile)){
            $this->dir_info=unserialize(file_get_contents($configfile));
		}
		if(empty($this->dir_info)){
			$this->dir_info=new \stdClass();
			$this->dir_info->sub_dir="aa";
			$this->dir_info->file_count=0;
		}
		if($this->dir_info->file_count>1000){
			$t0=ord($this->dir_info->sub_dir{0});
			$t1=ord($this->dir_info->sub_dir{1});
			if($t1>=122){
				if($t0<122){
					$this->dir_info->sub_dir=chr(t0+1)."a";
				}else{
					$this->$this->dir_info->sub_dir="00";
				}
			}else{
				$this->dir_info->sub_dir=chr(t0).chr($t1+1);
			}
			$this->dir_info->file_count=0;
		}
		if(!file_exists($this->save_dir.$this->dir_info->sub_dir)){
			@mkdir($this->save_dir.$this->dir_info->sub_dir);
		}
	}
	/**
	 * 设置要处理的$_FILES
	 * @return true||false
	 */
	function set_input_file($key,$i=-1){
		$this->file_in_name=$i<0?$_FILES[$key]['name']:$_FILES[$key]['name'][$i];
		$this->file_in_size=$i<0?$_FILES[$key]['size']:$_FILES[$key]['size'][$i];
		$this->file_in_tmp=$i<0?$_FILES[$key]['tmp_name']:$_FILES[$key]['tmp_name'][$i];
		$this->file_in_error=$i<0?$_FILES[$key]['error']:$_FILES[$key]['error'][$i];
		$filsx=explode(".", $this->file_in_name);
		$this->file_in_ext=end($filsx);
		return $this->upload_enabled($key,$i);
	}
	/**
	 * 生成要保存的文件名
	 */
	function make_file_name($key='',$ext=''){
		if(empty($this->dir_info))$this->auto_config();
		$this->mnc++;
		if($ext)$this->file_in_ext=$ext;
		if(empty($key)){
            $file_name1=self::storeName();
            return $this->dir_info->sub_dir."/".$file_name1.$this->mnc.".".$this->file_in_ext;
        }else{
            return $this->dir_info->sub_dir."/".$key.".".$this->file_in_ext;
        }

	}
	public static function storeName(){
		return chr(rand(97,122)).'Y'.dechex(W3CA_UTC_TIME-19870118);
	}
	/**
	 * 保存，参数为空会用make_file_name()生成,成功返回文件的信息
	 * @return \stdClass(name:文件名,save_as:保存名,size:大小,file_ext:后缀)
	 */
	function save_to($file_name="",$merge=false){
		if($this->file_in_error!=UPLOAD_ERR_OK){
			return false;
		}
		if(!empty($this->file_type)&&!in_array(strtolower($this->file_in_ext), $this->file_type)){
			$this->file_in_error="文件类型错误:".$this->file_in_ext;
			return false;
		}
		$save_f=$file_name==""?$this->make_file_name():$file_name;
		$save_n=$this->save_dir.$save_f;
		if($merge==false&&$this->no_replace&&file_exists($save_n)){
			$this->file_in_error="文件已存在：".$save_n;
			return false;
		}
		if(!file_exists($this->save_dir)){
			$this->file_in_error="保存目录不存在(".$this->save_dir.")";
			return false;
		}
		if($this->max_size<$this->file_in_size){
			$this->file_in_error="文件超过".$this->max_size."字节";
			return false;
		}
		if(file_exists($save_n)){
            $f=\fopen($save_n,'a');
            $c=\file_get_contents($this->file_in_tmp);
            \fwrite($f,$c);
            \fclose($f);
            $info=new \stdClass();
            $info->old_name=$this->file_in_name;
            $info->save_as=$save_f;
            $info->size=filesize($save_n);
            $info->file_ext=$this->file_in_ext;
        }else if(move_uploaded_file($this->file_in_tmp,$save_n)){
			$info=new \stdClass();
			$info->old_name=$this->file_in_name;
			$info->save_as=$save_f;
			$info->size=$this->file_in_size;
			$info->file_ext=$this->file_in_ext;

		}else{
		    return false;
        }

        if($this->dir_info){
            $configfile=$this->save_dir.".upload_set";
            $this->dir_info->file_count++;
            file_put_contents($configfile, serialize($this->dir_info));
        }
		return $info;
	}
	/**
	 * 保存上传文件
	 * @param $file $_FILE[x]
	 * @param $name 不包括后缀的保存文件名
	 * @param $exts 可能上传的后缀列表,统一用小写
	 */
	public function save_file_to($file,$name,$exts){
	    $f_ns=explode(".", $file['name']);
		$ext=end($f_ns);
		if(!in_array(strtolower($ext), $exts))return false;
		if($file['error']==UPLOAD_ERR_OK&&move_uploaded_file($file['tmp_name'],$name.".".$ext)){
			$info=new \stdClass();
			$info->old_name=$file['name'];
			$info->save_as=$name;
			$info->size=$file['size'];
			$info->file_ext=$ext;
			return $info;
		}
		return false;
	} 
	/**
	 * 文件是否可上传
	 */
	public static function upload_enabled($key,$i=-1){
		$u_file_e=$i<0?$_FILES[$key]['error']:$_FILES[$key]['error'][$i];
		return $u_file_e==UPLOAD_ERR_OK;
	}
	public function get_error()
	{
		return $this->file_in_error;
	}
}
