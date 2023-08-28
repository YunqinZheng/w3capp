<?php
namespace w3capp;
class TplParent{
    protected static $parent_tpl=array();
    private $tpl_parse;
    private $tpl;
    function __construct($tpl){
        $this->tpl=$tpl;
        if(array_key_exists($tpl,self::$parent_tpl)){
            $this->tpl_parse=true;
        }else{
            $this->tpl_parse=false;
            self::$parent_tpl[$tpl]=array("start"=>1,"end"=>1,"child_marks"=>array(),"explain"=>"");
        }
    }
    function tplHasParsed(){
        return $this->tpl_parse;
    }

    function setTplContent($tpl){
        self::$parent_tpl[$this->tpl]['explain']=$tpl;
        $marks=array();
        preg_replace_callback("/<!--extends_([^>]+)-->/",function($matched)use(&$marks){$marks[]=$matched[1];return $matched[0];},$tpl);
        self::$parent_tpl[$this->tpl]['child_marks']=$marks;
    }

    public function extendsExplain($content){
        $replace=array();$exp_c=$content;
        foreach(self::$parent_tpl[$this->tpl]['child_marks'] as $mark){
            $replace['<!--extends_'.$mark.'-->']="";
            $lp2=explode("<!--[".$mark."]-->",$exp_c);
            if(false==empty($lp2[1])){
                $exp_c=$lp2[1];
                list($replace['<!--extends_'.$mark.'-->'],$exp_c)=explode("<!--[/".$mark."]-->",$exp_c);
            }
        }
        return strtr(self::$parent_tpl[$this->tpl]['explain'],$replace);
    }
}