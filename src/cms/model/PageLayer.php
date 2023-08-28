<?php
namespace cms\model;
use common\model\BlockRecord;
use common\model\PageLayoutRecord;

class PageLayer extends PageLayoutRecord{
    protected $css_exp;
    var $in_display=false;
    public function getCss(){
        if(!$this->css_exp){
            $this->css_exp=empty($this->cell_css)?[["*class"=>"","background_color"=>"","color"=>"","width"=>"","height"=>""],["*class"=>"cell0","background_color"=>"","color"=>"","width"=>"","height"=>""]]:json_decode($this->cell_css,true);
        }
        return $this->css_exp;
    }
    public function setCss($cell,$key,$value){
        $this->getCss();
        if(empty($this->css_exp[$cell])){
            for($cc=count($this->css_exp);$cc<=$cell;++$cc){
                $this->css_exp[]=["*class"=>"cell".$cc,"background_color"=>"","color"=>"","width"=>"","height"=>""];
            }

        }
        $this->css_exp[$cell][$key]=str_replace("\n","",$value);
    }
    public function cellSize($cell,$width,$height){
        $this->setCss($cell+1,'width',$width);
        $this->setCss($cell+1,'height',$height);
        $this->cell_css=json_encode($this->css_exp);
    }
    protected $ex_view_ids;
    protected $ex_block_css;
    protected $ex_cell_list;
    public function innerTPL($view_ids,$block_css){
        $do='tpl_'.$this->lay_type;
        $this->ex_view_ids=$view_ids;
        $this->ex_block_css=$block_css;
        return $this->$do();
    }
    public function setInnerCell($cell_list){
        $this->ex_cell_list=$cell_list;
    }
    public function getInnerCell(){
        return $this->ex_cell_list;
    }
    public function display($view_ids,$block_css){
        $this->in_display=true;
        $this->setInnerCell(json_decode($this->lay_inner,true));
        $this->innerTPL($view_ids,$block_css);
    }
    public function tpl_slide(){
        $lay_set=$this->ex_cell_list[0];
        return $this->tpldf('frame-slide','<div class="slide-switch" switch_type="'.
            $lay_set['switch_type'].'" time_off="'.$lay_set['time_off'].'"></div>');
    }
    public function tpl_tab(){
        $pre='<div class="ftb-title"><dl>';
        for($ti=1;$ti<count($this->ex_cell_list);++$ti){
            $pre.='<dt '.($ti==0?'class="selected"':'').'>'.$this->ex_cell_list[$ti]['switch_title'].'</dt>';
        }
        $pre.='</dl></div>';
        return $this->tpldf('frame-tab',$pre);
    }
    public function tpl_vline(){
        return $this->tpldf('frame-vl');
    }
    public function tpl_hline(){
        return $this->tpldf('frame-hl');
    }
    public function addCellBlock($pcell,$mark,$before){
        $lay_inner=json_decode($this->lay_inner,true);
        if(empty($lay_inner[$pcell])){
            $lay_inner[$pcell]=["blocks"=>[],"title"=>""];
        }
        if($before){
            $in_search=array_search($lay_inner[$pcell]["blocks"],$before);
            if($in_search===false){
                $lay_inner[$pcell]['blocks'][]=$mark;
            }else{
                array_splice($lay_inner[$pcell]['blocks'],$in_search,0,[$mark]);
            }
        }else{
            $lay_inner[$pcell]['blocks'][]=$mark;
        }
        $this->lay_inner=json_encode($lay_inner);
        return $this->save();
    }

    /**
     * @param $page_frame
     * @return array 所有布局，包括子布局
     * @throws \Exception
     */
    static public function allLayout($page_frame){
        $lays=[];
        $child_relate=[];
        self::findAll(['page_frame'=>$page_frame],["id"=>"asc"])->fetchWith(function($val)use(&$lays,&$child_relate){
            $val['lay_inner']=json_decode($val['lay_inner'],true);
            $val['cell_css']=json_decode($val['cell_css'],true);
            if($val['parent_lay']){
                if(empty($child_relate[$val['parent_lay']])){
                    //先读到子布局
                    $child_relate[$val['parent_lay']]=['index'=>-1,'children'=>['c'.$val['parent_cell']=>$val]];
                }else{
                    if($child_relate[$val['parent_lay']]['index']==-1){
                        //上层布局未出现
                        $child_relate[$val['parent_lay']]['children']['c'.$val['parent_cell']]=$val;
                    }else{
                        //上层布局已出现
                        $parent_lay=$lays[$child_relate[$val['parent_lay']]['index']];
                        if(!$parent_lay['child_lays'])$parent_lay['child_lays']=[];
                        $parent_lay['child_lays']['c'.$val['parent_cell']]=$val;
                        $lays[$child_relate[$val['parent_lay']]['index']]=$parent_lay;
                    }
                }
            }else{
                if(empty($child_relate[$val['id']])){
                    $val['child_lays']=null;
                    $child_relate[$val['id']]=['index'=>count($lays),'children'=>[]];
                }else{
                    $val['child_lays']=$child_relate[$val['id']]['children'];
                    $child_relate[$val['id']]['index']=count($lays);
                }

                $lays[]=$val;
            }

        });
        return $lays;
    }
    public function mvCellBlock($pcell,$mark,$before){
        $lay_inner=json_decode($this->lay_inner,true);
        foreach($lay_inner as $i=>&$b_cell){
            $mk=array_search($mark,$b_cell['blocks']);
            if($mk===false){
                continue;
            }
            array_splice($b_cell['blocks'],$mk,1);
            break;
        }
        if(empty($lay_inner[$pcell])){
            $lay_inner[$pcell]=["blocks"=>[],"title"=>""];
        }
        if($before){
            $in_search=array_search($lay_inner[$pcell]["blocks"],$before);
            if($in_search===false){
                $lay_inner[$pcell]['blocks'][]=$mark;
            }else{
                array_splice($lay_inner[$pcell]['blocks'],$in_search,0,[$mark]);
            }
        }else{
            $lay_inner[$pcell]['blocks'][]=$mark;
        }
        $this->lay_inner=json_encode($lay_inner);
        return $this->save();
    }
    public function rmCellBlock($mark){
        $lay_inner=json_decode($this->lay_inner,true);
        foreach($lay_inner as $i=>&$b_cell){
            $mk=array_search($mark,$b_cell['blocks']);
            if($mk===false){
                continue;
            }
            array_splice($b_cell['blocks'],$mk,1);
            break;
        }
        $this->lay_inner=json_encode($lay_inner);
        $this->save();
    }
    protected function tpldf($cs,$preset=''){

        $id='fzy'.$this->id;
        $tpl='<div class="'.$cs.'" id="'.$id.'">'.$preset;
        $cell_number=count($this->ex_cell_list);
        for($i=1;$i<$cell_number;++$i){
            //$c_id=$id."q$i";
            $block_display='';
            $bs_in_cell=$this->ex_cell_list[$i]['blocks'];
            $class_name=$this->ex_cell_list[$i]['class_name'];
            //子布局
            $child_lay=null;
            if(array_key_exists('child',$this->ex_cell_list[$i])){
                $child_lay=$this->ex_cell_list[$i]['child'];
                $class_name.=" child-lay";
            }
            if($this->in_display){
                echo $tpl.'<div class="frame-cell '.$class_name.'" area="'.$this->frame_id.'">';
                if($child_lay){
                    $child_lay->display($this->ex_view_ids,$this->ex_block_css);
                }else
                foreach ($bs_in_cell as $b){
                    if($b){
                        $bcn=empty($this->ex_block_css[$b])?'':(' '.$this->ex_block_css[$b]);
                        $block_info=BlockRecord::record(["mark"=>$b]);
                        $block_info=PageBlock::decodeAttr($block_info);
                        $bid=$this->ex_view_ids['ab'.$b];
                        echo '<div class="block'.$bcn.'" base="'.$b.'" id="'.$bid.'">';
                        $b_obj=PageBlock::newBlock($block_info);
                        $b_obj->display();
                        echo "</div>";
                        $this->cell_blocks[]=$b;
                    }
                }
                echo '</div>';
                $tpl='';
            }else{
                $a='?';
                if($child_lay){
                    $block_display=$child_lay->innerTPL($this->ex_view_ids,$this->ex_block_css);
                }else
                foreach ($bs_in_cell as $b){
                    if($b){
                        $bcn=empty($this->ex_block_css[$b])?'':(' '.$this->ex_block_css[$b]);
                        $bid=$this->ex_view_ids['ab'.$b];
                        $block_display.='<div id="'.$bid.'" class="block'.$bcn.'"><'.$a.'php echo $this->loadBlock("'.$b.'");'.$a.'></div>';
                        $this->cell_blocks[]=$b;
                    }

                }

                $tpl.='<div class="frame-cell '.$class_name.'">'.$block_display.'</div>';
            }
        }



        if($this->in_display){
            echo '</div>';
            return;
        }
        return $tpl.'</div>';
    }
}