<?php
namespace w3capp\helper;
class Image{
	protected $loading_type;
	protected $loading_file;
	protected $loading_attr;
	static $max_size=1400;
	function loadImage($file){
		$this->loading_attr=getimagesize($file);
		$type_array=array(1=>"gif",2=>"jpeg",3=>"png",6=>"bmp",17=>"wbmp");
		$type=strtolower($this->loading_attr[2]);
		$this->loading_type=$type_array[$type]?$type_array[$type]:"";
		$this->loading_file=$file;
	}
	function getImageType(){
		return $this->loading_type;
	}
	/**
	 * 剪取中间，可能会放大
	 */
	function copyImageCenter($width,$height,$tofile,$file_ext=""){
		if(self::$max_size<$this->loading_attr[0]||self::$max_size<$this->loading_attr[1]){
			return false;
		}
		if($this->loading_type){
			$f_name="imagecreatefrom".$this->loading_type;
			if(function_exists($f_name)){
				$src_img=$f_name($this->loading_file);
				$dst_img=imagecreatetruecolor($width,$height);
				$ratio_dst=$width/$height;
				$ratio_src=$this->loading_attr[0]/$this->loading_attr[1];
				$s_x=$s_y=$s_w=$s_h=0;
				if($ratio_dst<$ratio_src){
					//剪宽
					$s_h=$this->loading_attr[1];
					$s_w=$s_h*$ratio_dst;
					$s_x=($this->loading_attr[0]-$s_w)/2;
				}else{
					//剪高
					$s_w=$this->loading_attr[0];
					$s_h=$s_w/$ratio_dst;
					$s_y=($this->loading_attr[1]-$s_h)/2;
				}
				$return=imagecopyresampled($dst_img,$src_img,0,0,$s_x,$s_y,$width,$height,$s_w,$s_h);
				$save_fun="image".($file_ext?$file_ext:$this->loading_type);
				if(function_exists($save_fun)){
					$return=$save_fun($dst_img,$tofile);
				}else{
					$return=imagejpeg($dst_img,$tofile);
				}
				$return=imagedestroy($dst_img);
				$return=imagedestroy($src_img);
				return $return;
			}
			
		}
		return false;
	}
	/**
	 * 按宽高等比剪取中间，大于指定宽高会压缩
	 */
	function copyImageArea($width,$height,$tofile,$file_ext=""){

		if(self::$max_size<$this->loading_attr[0]||self::$max_size<$this->loading_attr[1]){
			return false;
		}
		if($this->loading_type){
			$f_name="imagecreatefrom".$this->loading_type;
			if(function_exists($f_name)){
				$src_img=$f_name($this->loading_file);
				
				$ratio_dst=$width/$height;
				$ratio_src=$this->loading_attr[0]/$this->loading_attr[1];
				$s_x=$s_y=$s_w=$s_h=0;
				
				if($ratio_dst<$ratio_src){
					//剪宽
					$s_h=$this->loading_attr[1];
					$s_w=$s_h*$ratio_dst;
					$s_x=($this->loading_attr[0]-$s_w)/2;
				}else{
					//剪高
					$s_w=$this->loading_attr[0];
					$s_h=$s_w/$ratio_dst;
					$s_y=($this->loading_attr[1]-$s_h)/2;
				}
				$d_w=$s_w<$width?$s_w:$width;
				$d_h=$s_h<$height?$s_h:$height;
				$dst_img=imagecreatetruecolor($d_w,$d_h);
				$return=false;
				if(imagecopyresampled($dst_img,$src_img,0,0,$s_x,$s_y,$d_w,$d_h,$s_w,$s_h)){
					$return=true;
				}
				$save_fun="image".($file_ext?$file_ext:$this->loading_type);
				if(function_exists($save_fun)){
					$save_fun($dst_img,$tofile);
				}else{
					imagejpeg($dst_img,$tofile);
				}
				$return=imagedestroy($dst_img);
				$return=imagedestroy($src_img);
				return $return;
			}
		}
		return false;
	}

	function copyImageXYSize($src_x,$src_y,$src_w,$src_h,$width,$height,$tofile,$file_ext=""){
		if(self::$max_size<$this->loading_attr[0]||self::$max_size<$this->loading_attr[1]){
			return false;
		}
		if($this->loading_type){
			$f_name="imagecreatefrom".$this->loading_type;
			if(function_exists($f_name)){
				$src_img=$f_name($this->loading_file);
				if(!$src_img)return false;
				$dst_img=imagecreatetruecolor($width,$height);
				$cp_rs=imagecopyresampled($dst_img,$src_img,0,0,$src_x,$src_y,$width,$height,$src_w,$src_h);
				$save_fun="image".($file_ext?$file_ext:$this->loading_type);
				if(!$cp_rs){
					return false;
				}
				if(function_exists($save_fun)){
					$cp_rs=$save_fun($dst_img,$tofile);
				}else{
					$cp_rs=imagejpeg($dst_img,$tofile);
				}
                if(!$cp_rs){
                    return false;
                }
				$cp_rs=imagedestroy($dst_img);
				$cp_rs=imagedestroy($src_img);
				return $cp_rs;
			}
			
		}
		return false;
	}
}
