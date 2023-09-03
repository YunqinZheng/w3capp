<?php
namespace cms\model;
use common\model\BlockRecord;
use common\model\PageFrameRecord;
use common\model\PageLayoutRecord;
use w3capp\W3cApp;

class PageFrame extends PageFrameRecord{
    protected static $store_css=[];
    protected $in_display;
    protected $block_info;
    protected $cell_blocks;

    public function display(){
        $this->block_info=[];
        $dp=BlockRecord::findAllData(["mark"=>explode(",",$this->block_marks)]);
        foreach ($dp as $b_info){
            $this->block_info[$b_info['mark']]=$b_info;
        }
        $block_css=$this->blockCss();
        $div_ids=$this->blocksDivIds();
        $this->in_display=true;
        $layout=PageLayer::findAll(['page_frame'=>$this->id,"parent_lay"=>0],["id"=>"asc"]);
        $this->cell_blocks=[];
        foreach ($layout as $i=>$lay){
            $lay->display($div_ids,$block_css);
        }
        //$this->remain();
        if(count($layout)==0&&$this->block_marks){
            $css_list=$this->blockCss();
            $view_ids=$this->blocksDivIds();
            $blocks=explode(",",$this->block_marks);
            foreach ($blocks as $i=>$block_mark){
                $id=$view_ids['ab'.$block_mark];
                $bcss=empty($css_list[$block_mark])?"":(" ".$css_list[$block_mark]);
                echo "<div id=\"".$id."\" class=\"block$bcss\">";
                $b_obj=PageBlock::newBlock($this->block_info[$block_mark]);
                $b_obj->display();
                echo "</div>";
            }
        }
        $this->in_display=false;
    }

    protected function cssString($set){
        $css="";
        foreach ($set as $k=>$v){
            if($k{0}!="*"&&$v!=""){
                $css.=str_replace("_","-",$k).":".$v.";";
            }
        }
        return $css;
    }
    public function cellCssMix($id,$css_set){
        $store_css='';
        foreach($css_set as $i=>$set){
            $s=$this->cssString($set);
            if($s=="")continue;
            if($i==0){
                $store_css.='#'.$id.' .frame-cell{'.$s."}";
            }else if($set['*class']){
                $store_css.='#'.$id.">div.{$set['*class']}{ $s }";
            }
        }
        if($store_css){
            self::$store_css[$id]=str_replace("\n",'',$store_css);
        }

    }


    /**
     * 没有在框架的模块
     * @return string
     * @throws \Exception
     *
     */
    public function remain(){
        $rsb=$this->residueBlocks($this->cell_blocks);
        $div_id=$this->blocksDivIds();
        $div_css=$this->blockCss();
        if($this->in_display){
            echo '<dl class="cell-def">';
            foreach ($rsb as $b){
                $_css=$div_css[$b];
                echo '<div class="block'.($_css?' '.$_css:'').'" id="'.$div_id['ab'.$b].'">';
                $b_obj=PageBlock::newBlock($this->block_info[$b]);
                $b_obj->display();
                echo "</div>";
            }
            echo '</dl>';
            return '';
        }else{
            $rsb_tpl='';
            foreach ($rsb as $b){
                $_css=$div_css[$b];
                $rsb_tpl.='<div id="'.$div_id['ab'.$b].'" class="block'.($_css?' '.$_css:'').'"><?'."php \$this->loadBlock(\"$b\");?"."></div>";
            }
            if($rsb_tpl){
                $rsb_tpl='<dl class="cell-def">'.$rsb_tpl.'</dl>';
            }
            return $rsb_tpl;
        }
    }
    /**
     * 不在框架的模块
     * @param $display_b
     * @return array
     */
    public function residueBlocks($display_b){
        if(!$this->block_marks)return [];
        $blocks=explode(",",$this->block_marks);
        $res=[];
        foreach ($blocks as $f_b){
            if(false==in_array($f_b,$display_b)){
                $res[]=$f_b;
            }
        }
        return $res;
    }
    static public function saveCss($string,$file){
        if(empty(self::$store_css))return false;
        $css_load=file_get_contents($file);
        $fsp="\n/**framecss**/\n";
        $css_exp=explode($fsp,$css_load);
        $css_exp[0]=$css_exp[0].$fsp;
        if(count($css_exp)==1){
            $css_exp[1]='';
            $frame_css=[];
        }else{
            $frame_css=explode("\n",trim($css_exp[1]));
        }
        $new_css='';
        foreach (self::$store_css as $id=>$ss){
            if(strpos($css_exp[1],'#'.$id)===false){
                $new_css.="\n".$ss;
            }else{
                foreach ($frame_css as &$fcss){
                    if(strpos($fcss,'#'.$id)===0){
                        $fcss=$ss;
                    }
                }
            }
        }
        return file_put_contents($file,$css_exp[0]."\n".implode("\n",$frame_css).$new_css.$fsp.$string);
    }
    protected $lay_ids;
    public function layIds(){
        return $this->lay_ids;
    }
    protected function mkTpl(){
        $layout=PageLayer::findAllData(['page_frame'=>$this->id,"parent_lay"=>0],["id"=>"asc"]);
        if(empty($layout)){
            $this->tpl="";//先清旧的模板
            $this->tpl=$this->innerTPL();
        }else{
            $tpl="";
            $this->cell_blocks=[];
            $block_css=$this->blockCss();
            $div_ids=$this->blocksDivIds();
            foreach ($layout as $lay_store){
                $cell_css=json_decode($lay_store->cell_css);
                $lay_inner=json_decode($lay_store->lay_inner);
                foreach ($lay_inner as $lnn){
                    $this->cell_blocks=array_merge($lnn['blocks'],$this->cell_blocks);
                }
                $tpl.=$lay_store->innerTPL($div_ids,$block_css,$lay_inner);
                $this->cellCssMix("fzy".$lay_store['id'],$cell_css);
            }
            $this->tpl=$tpl.$this->remain();
        }

    }
    protected $css_n;
    protected $block_style;
    public function cssName(){
        if($this->css_n)return $this->css_n;
        if($this->css_code){
            $this->block_style=json_decode($this->css_code,true);
            if(empty($this->block_style[0]['*class'])){
                return '';
            }else{
                $this->css_n=$this->block_style[0]['*class'];
                return $this->css_n;
            }
        }
        return '';
    }
    protected $block_css="";
    public function blockCss(){
        $this->getBlockStyle();
        $list=[];
        foreach ($this->block_style as $i=>$css){
            if($css['*class']){
                $this->block_css.=".".$css['*class']."{".$this->cssString($css)."}";
                if($i){
                    $list[$css['*mark']]=$css['*class'];
                }
            }
        }
        return $list;
    }
    public function getBlockStyle(){
        if(!$this->block_style){
            if($this->css_code) {
                $this->block_style = json_decode($this->css_code, true);
            }else{
                $this->block_style=[['*div_ini_id'=>0,"*view_ids"=>"",'*class'=>"",'*mark'=>""]];
            }
        }
        return $this->block_style;
    }
    public function blocksDivIds(){
        $this->getBlockStyle();
        $view_ids=[];
        if(empty($this->block_style[0]['*div_ini_id'])){
            $div_ini_id=$this->id%10*10000+time()%10000;
            $blocks=explode(",",$this->block_marks);
            foreach ($blocks as $mark){
                $view_ids['ab'.$mark]='ab_'.dechex($div_ini_id++);
            }
            $this->block_style[0]['*div_ini_id']=$div_ini_id;
            $this->block_style[0]['*view_ids']=$view_ids;
        }else{
            $view_ids=$this->block_style[0]['*view_ids'];
        }

        return $view_ids;
    }
    public function innerTPL(){
        if($this->tpl)return $this->tpl;
        $tpl='';
        if($this->block_marks){
            $css_list=$this->blockCss();
            $view_ids=$this->blocksDivIds();
            $blocks=explode(",",$this->block_marks);
            foreach ($blocks as $i=>$block_mark){
                $id=$view_ids['ab'.$block_mark];
                $bcss=empty($css_list[$block_mark])?"":(" ".$css_list[$block_mark]);
                $tpl.="<div id=\"".$id."\" class=\"block$bcss\"><?";
                $tpl.='php if(array_key_exists("'.$block_mark.'",$this->all_blocks)){
                    self::$app->instance->_display_block($this->all_blocks["'.$block_mark.'"],$this->block_args["'.$block_mark.'"]);
                }';
                $tpl.="?></div>";
            }
        }
        return $tpl;
    }
    public function editLayout($info){
        if(empty($info['page_file_var'])){
            \W3cCore::_error("no page file var");
           return false;
        }
        if(false==empty($info['layout'])){
            $layout=json_decode($info['layout'],true);
            $tpl='';
            $div_ids=[];
            if(false==empty($layout['block_style'])){
                $this->block_style=$layout['block_style'];
                $div_ids=$this->blocksDivIds();
                $this->css_code=json_encode($this->block_style);
            }
            $lay_list=PageLayer::findAll(['page_frame'=>$this->id,"parent_lay"=>0],["id"=>"asc"])->fetch();
            $i=0;
            $this->lay_ids=[];
            $this->cell_blocks=[];
            $blocks=explode(",",$this->block_marks);
            $block_css=$this->blockCss();
            foreach ($layout['lay_list'] as &$lay){
                if(empty($lay_list[$i])){
                    $lay_store=new PageLayer($lay);
                    $lay_store->lay_inner='[]';
                    $lay_store->page_frame=$this->id;
                }else{
                    $lay_store=$lay_list[$i];
                }
                $cell_css=$lay['cell_css'];
                $lay['cell_css']=json_encode($cell_css);
                $lay_inner=$lay['lay_inner'];
                foreach ($lay_inner as $li=>&$lnn){
                    if($li==0)continue;
                    $lnn['blocks']=array_filter($lnn['blocks']);
                    $lnn['class_name']=$cell_css[$li]['*class'];
                    if($lnn['blocks']){
                        $this->cell_blocks=array_merge($lnn['blocks'],$this->cell_blocks);
                    }else{
                        $b_len=count($blocks);
                        $li_i=$li-1;
                        //
                        if($i==0&&empty($lay_store->id)&&$li_i<$b_len){
                            $lnn['blocks']=[];
                            for($li_i;$li_i<$b_len;){
                                $lnn['blocks'][]=$blocks[$li_i];
                                $li_i+=count($lay_inner)-1;
                            }
                            $this->cell_blocks=array_merge($lnn['blocks'],$this->cell_blocks);
                        }
                    }

                }
                $lay['lay_inner']=json_encode($lay_inner);
                $lay_store->setAttributes($lay);
                $lay_store->save();
                if(empty($lay['child_lays'])==false){
                    $child_lays2=[];
                    $child_lays=PageLayer::findAll(['page_frame'=>$this->id,"parent_lay"=>$lay_store->primary()],["parent_cell"=>"asc"]);
                    foreach ($child_lays as $clay){
                        $child_lays2[$clay['parent_cell']]=$clay;
                    }
                    $child_keys=[];
                    foreach ($lay['child_lays'] as $ck=>&$child){
                        $child_keys[]=$ck;
                        $child_css=$child['cell_css'];
                        $child['cell_css']=json_encode($child_css);
                        $child_inner=$child['lay_inner'];
                        $p_cell=intval(str_replace('c','',$ck));
                        foreach ($child_inner as $li=>&$lnn){
                            $lnn['class_name']=$child_css[$li]['*class'];
                            if(!$child['id']){
                                $li_i=$li-1;
                                $b_len=count($lay_inner[$p_cell]['blocks']);
                                $lnn['blocks']=[];
                                for($li_i;$li_i<$b_len;){
                                    $lnn['blocks'][]=$lay_inner[$p_cell]['blocks'][$li_i];
                                    $li_i+=count($child_inner)-1;
                                }
                            }
                        }
                        $child['lay_inner']=json_encode($child_inner);
                        if(array_key_exists($p_cell,$child_lays2)){
                            $clay=$child_lays2[$p_cell];
                        }else{
                            $clay=new PageLayer();
                        }
                        unset($child['id']);
                        $child['parent_lay']=$lay_store->primary();
                        $child['page_frame']=$lay_store->page_frame;
                        $child['parent_cell']=$p_cell;
                        $clay->setAttributes($child);
                        $clay->setInnerCell($child_inner);
                        $clay->save();
                        $this->cellCssMix("fzy".$clay['id'],$child_css);
                        $lay_inner[$p_cell]['child']=$clay;
                    }
                    foreach ($child_lays2 as $cki=>$clay){
                        if(!in_array('c'.$cki,$child_keys)){
                            $clay->delete();
                        }else{
                            $lay_inner[$cki]['child']=$clay;
                        }
                    }
                }
                $this->lay_ids[]=$lay['id']=$lay_store->primary();
                $lay_store->setInnerCell($lay_inner);
                $tpl.=$lay_store->innerTPL($div_ids,$block_css);
                $this->cellCssMix("fzy".$lay['id'],$cell_css);
                ++$i;
            }
            for(;$i<count($lay_list);++$i){
                PageLayer::deleteAll(['parent_lay'=>$lay_list[$i]->id]);
                $lay_list[$i]->delete();
            }
            $this->tpl=$tpl;
            if($tpl==""){
                $this->tpl=$this->innerTPL();
            }
            //.$this->remain();
        }
        if(array_key_exists('css_file',$info)){
            self::saveCss($this->block_css,W3CA_MASTER_PATH.$info['css_file']);
        }
        if(false==empty($info['page_file_var'])){
            self::$app->template()->clearFile($info['page_file_var']);
        }
        return $this->save();
    }
    public function removeBlock($mark){
        $block_marks=",".$this->block_marks.",";
        $this->block_marks=trim(str_replace(",".$mark.",",'',$block_marks),",");
        $lay=PageLayer::record(['page_frame'=>$this->id,"like"=>["lay_inner","\"".$mark."\""]]);
        if(empty($lay)){
            $this->mkTpl();
            return $this->save();
        }
        $lay->rmCellBlock($mark);
        $this->mkTpl();
        return $this->save();
    }
    //删除多个模块
    public function rmBlocks($mark_list){
        if(count($mark_list)==0)return;

    }
    public function addBlock($mark,$lay_id,$cell,$before){
        $mks=explode(",",$this->block_marks);

        if(in_array($mark,$mks)){
            //移动现有模块
            if($before){
                $idx_bf=array_search($mks,$before);
                if($idx_bf!==false){
                    $idx=array_search($mks,$mark);
                    unset($mks[$idx]);
                    array_splice($mks,$idx_bf,0,[$mark]);
                    $this->block_marks=implode(",",$mks);
                }

            }
            $lay=PageLayer::record(['page_frame'=>$this->id,"like"=>["lay_inner","\"".$mark."\""]]);
            if($lay){
                if($lay->id==$lay_id){
                    $lay->mvCellBlock($cell+1,$mark,$before);
                }else{
                    $lay->rmCellBlock($mark);
                    if($lay_id&&is_integer($cell)&&$cell>-1){
                        //转移到新位置
                        $nlay=new PageLayer(["id"=>$lay_id]);
                        $nlay->addCellBlock($cell+1,$mark,$before);
                    }
                }

            }else{
                $nlay=new PageLayer(["id"=>$lay_id]);
                $nlay->addCellBlock($cell+1,$mark,$before);
            }
            $this->mkTpl();
            return $this->save();
        }else{
            if(count($mks)>=32){
                \W3cCore::_error("blocks count max 32");
                return false;
            }
            $this->block_marks=$this->block_marks.",".$mark;
            if($lay_id&&is_integer($cell)&&$cell>-1){
                $lay=new PageLayer(["id"=>$lay_id]);
                $lay->addCellBlock($cell+1,$mark,$before);
                $this->mkTpl();
            }
            return $this->save();
        }
        return false;
    }
}