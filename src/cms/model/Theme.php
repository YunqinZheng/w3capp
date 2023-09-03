<?php
namespace cms\model;
use common\model\WebThemeRecord;

class Theme extends WebThemeRecord{
    public function uninstall(){
        if(empty($this->install_dir))return false;
        self::deleteDir(W3CA_MASTER_PATH."data/theme/".$this->install_dir);
        return $this->delete();
    }
    private static function deleteDir($dirName){
        if ( $handle = opendir( $dirName ) ) {
            while (false !== ($item = readdir($handle))) {
                if ($item != "." && $item != "..") {
                    if (is_dir("$dirName/$item")) {
                        self::deleteDir("$dirName/$item");
                    } else {
                        @unlink("$dirName/$item");
                    }
                }
            }
        }
        closedir( $handle );
        @rmdir( $dirName );
    }
    public function refreshFileVar($new_var=false){
        if(!$this->install_dir)return false;
        $style_target=W3CA_MASTER_PATH."data/theme/".$this->install_dir."/";

        if(!$this->file_var||$new_var)
        {
            if($this->file_var){
                $css_file=$style_target.$this->file_var."theme.css";
                if(file_exists($css_file)){
                    unlink($css_file);
                }
            }
            $this->file_var=rand(10000,100000);
        }
        return copy($style_target."theme.css",$style_target.$this->file_var."theme.css");
    }
    public function install(){
        $dir=$this->getAttribute('id');
        $t_json=W3CA_THEME_TPL.$dir."/theme.json";
        if(file_exists($t_json)==false){
            return array("error"=>"json not exists");
        }
        $theme_json=\w3c\helper\Str::arrayParse(file_get_contents($t_json));
        if(empty($theme_json['language']))$theme_json['language']='';
        $style_rs=W3CA_THEME_TPL.$dir."/".$theme_json['style_dir'];
        $target_dir=empty($this->install_dir)?("t_".uniqid()."_".W3CA_YUN_DAT):$this->install_dir;
        //[DOCUMENT_ROOT]W3CA_MASTER_PATH.
        $style_target=W3CA_MASTER_PATH."data/theme/".$target_dir."/";
        if(is_dir($style_target)==false&&false==@mkdir($style_target,0777,true)||false==is_writable($style_target)){
            return array("error"=>"theme mkdir error：".$style_target);
        }

        if (is_dir($style_rs)&&$this->copyFiles($style_rs,$style_target,$this->install_dir?false:true)==false){
            return array("error"=>"theme dir copy error：".$style_rs);
        }
        $css_file=$style_target."theme.css";
        if(file_exists($css_file)){
            $rfile=$style_rs."/theme.css";
            $css_text=file_get_contents(file_exists($rfile)?$rfile:$css_file);
            if(false===strpos($css_text,'/**w3capp_css**/')){
                file_put_contents($css_file,$css_text."/**w3capp_css**/\n");
            }else{
                file_put_contents($css_file,$css_text);
            }
        }else{
            echo __LINE__;exit;
            file_put_contents($css_file,"/**w3capp_css**/\n");
        }
        //PageFrame::readCss($dir);
        //PageFrame::saveCss($css_file);
        $theme_var=array("name"=>$theme_json['name']);
        if(empty($theme_json['theme_image'])){
            $theme_var['image']="static/image/colorful.png";
        }else{
            $theme_var['image']="data/theme/t_".$dir.str_replace("/","",$theme_json['theme_image']);
            $f_img=W3CA_MASTER_PATH.$theme_var['image'];
            if(!file_exists($f_img))@copy(W3CA_THEME_TPL.$dir."/".$theme_json['theme_image'],$f_img);
        }
        $theme_var['install_dir']=$target_dir;
        $theme_var['language']=$theme_json['language'];
        $this->setAttributes($theme_var);
        $this->refresh_var=rand(1000,10000);
        $this->refreshFileVar();
        $this->save();
        return $this->getAttributes();
    }
    public static function getInstalledTheme(){
        return self::adaptTo([])->limit(50)->orderBy("id")->select("id,name,install_dir")->selectAll();
    }
    static public function readDir($dir,$filter){
        $df=["dirs"=>[],"files"=>[]];
        if ($dh = opendir(W3CA_THEME_TPL.$dir)) {
            while (($file = readdir($dh)) !== false) {
                if($file=='/'||$file==".."||$file==".")continue;
                if(filetype(W3CA_THEME_TPL.$dir.'/'.$file)=='dir'){
                    $df['dirs'][]=$dir."/".$file;
                }else{
                    if($filter){
                        if(strpos($file,$filter)){
                            $df['files'][]=$dir."/".$file;
                        }
                    }else{
                        $df['files'][]=$dir."/".$file;
                    }

                }
            }
            closedir($dh);
        }
        return $df;
    }
    public static function AllTpl($theme_dir){
        $dir_file=[$theme_dir=>[]];
        $df=self::readDir($theme_dir,'htm');
        $dir_file[$theme_dir]=$df['files'];
        reset($df['dirs']);
        $dir=current($df['dirs']);
        while ($dir){
            $df2=self::readDir($dir,'htm');
            foreach ($df2['dirs'] as $dr2){
                $df['dirs'][]=$dr2;
            }
            if(count($df2['files'])){
                $dir_file[$dir]=$df2['files'];
            }

            $dir=next($df['dirs']);
        }
        return $dir_file;
    }
    //包括没安装的主题
    public static function getThemes(){
        $themes=self::findAllData(null,"id",50);
        $theme_list=array();
        foreach ($themes as $t){
            $t['installed']=1;
            $theme_list[$t['id']]=$t;
        }

        if(empty($themes)){
            $theme_list=array("default"=>array("name"=>"默认主题","installed"=>1,"dir"=>"default","image"=>"static/image/colorful.png"));
        }

        if ($dh = opendir(W3CA_THEME_TPL)) {
            while (($file = readdir($dh)) !== false) {
                if(filetype(W3CA_THEME_TPL.$file)=='dir'&&$file!='/'&&$file!=".."&&$file!="."){
                    if(array_key_exists($file,$theme_list))continue;
                    $t_json=W3CA_THEME_TPL.$file."/theme.json";
                    if(file_exists($t_json)){
                        $theme_json=\w3c\helper\Str::arrayParse(file_get_contents($t_json));
                        $theme_var=array("name"=>$theme_json['name']);
                        if(empty($theme_json['theme_image'])){
                            $theme_var['image']="static/image/colorful.png";
                        }else{
                            $theme_var['image']="data/theme/t_".$file.str_replace("/","",$theme_json['theme_image']);
                            $f_img=W3CA_MASTER_PATH.$theme_var['image'];
                            if(!file_exists($f_img))@copy(W3CA_THEME_TPL.$file."/".$theme_json['theme_image'],$f_img);
                        }
                        $theme_var['id']=$file;
                        $theme_var['installed']=0;
                        $theme_list[$file]=$theme_var;
                    }
                }
            }
            closedir($dh);
        }
        return $theme_list;
    }
    private function copyFiles($from_dir,$to_dir,$replace_all){
        if ($dh = opendir($from_dir)) {
            $from_dir=rtrim($from_dir,"/")."/";
            while ($file=readdir($dh)){
                if($file=="."||$file==".."||$file=="/")continue;
                $file_from=$from_dir.$file;
                $file_to=$to_dir.$file;
                if(filetype($file_from)=="file"){
                    if(false==$replace_all&&file_exists($file_to)&&filemtime($file_from)<filemtime($file_to)){
                        continue;
                    }
                    $filepx=explode(".",$file);
                    $file_ex_name=strtolower(end($filepx));
                    if($file_ex_name=="css"){
                        $css=file_get_contents($file_from);
                        $css=strtr($css,array("{BASE_PATH}"=>W3CA_URL_ROOT,"{STATIC_DIR}"=>W3CA_URL_ROOT."static/"));
                        if(false===file_put_contents($file_to,$css)){
                            echo $file_to;exit;
                            return false;
                        }
                    }else if(in_array($file_ex_name,array("js","json","jpg","jpeg","png","gif","mp3","mp4","flv","swa","txt","pdf","doc","docx","svg","ttf","woff","woff2","eot"))){

                        if(copy($file_from,$file_to)){
                            //echo filemtime($file_from).":".filemtime($file_to)."||\n".(file_exists($file_to)?1:2);
                            //echo $file_to;exit;
                            //return false;
                        }

                    }
                }else if(filetype($file_from)=="dir"){
                    if(is_dir($file_to)||@mkdir($file_to,0777)){
                        if(false==$this->copyFiles($file_from."/",$file_to."/",$replace_all)){
                            return false;
                        }
                    }
                }
            }
            closedir($dh);
            return true;
        }else{
            return false;
        }
    }
}