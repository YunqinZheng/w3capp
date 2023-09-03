<?php
namespace cms\model;
use common\model\BlockExtendRecord;
use common\model\BlockRecord;
use w3capp\W3cApp
/**
 * Class BlockExp
 * @package cms\model
 * 导出、导入
 */
class BlockExp {
    protected $block_areas;
    protected $block_manager;
    protected $export_marks;
    protected $export_areas;
    protected $file_var;
    protected $export_dir;
    public function __construct($bm)
    {
        $this->block_manager=$bm;
    }
    function setTplExpDir($d){
        $this->export_dir=$d;
    }
    public function exportIds($id_list){
        $export_result=array("blocks"=>array(),"areas"=>array(),"tpls"=>array());
        foreach ($id_list as $id){
            $b=BlockRecord::firstAttr(["id"=>$id]);
            $ext=BlockExtendRecord::firstAttr(["block_id"=>$b['id']]);
            unset($ext['block_id']);
            $export_result["blocks"][$b['mark']]=array_merge($b,$ext);
            if($b['tpl']){
                $file=$b['tpl']==-1?$this->block_manager->tplCacheFile($b['id']):$this->block_manager->getTplFile($b['type'],$b['tpl']);
                $export_result['tpls'][$b['mark']]=file_get_contents($file);
            }
        }
        $file_name=date('Y-m-d').".block";
        if(self::$app->holder_response){
            return self::$app->setResponse(200,["Content-type"=>"application/json;",
                'Content-Disposition'=>'attachment; filename="'.$file_name.'"'],\w3c\helper\Str::toJson($export_result));
        }else{
            header('Content-type: application/json;');
            header('Content-Disposition: attachment; filename="'.$file_name.'"');
            echo \w3c\helper\Str::toJson($export_result);
            exit;
        }
    }
    public function exportFile($file_var,$areas,$marks)
    {
        $cache_tpl_file=W3CA_MASTER_PATH.$this->export_dir.$file_var;
        if(file_exists($cache_tpl_file)){
            $this->block_areas=[];
            $this->file_var=$file_var;
            $this->export_areas=$areas;
            $this->export_marks=$marks;
            include $cache_tpl_file;
            return '';
        }else{
            return '导出文件不存在';
        }

    }
    protected function blocksInit($blocks)
    {
        $export_result=array("blocks"=>array(),"areas"=>array(),"tpls"=>array());
        foreach ($blocks as $key => $value) {
            if(in_array($value['mark'], $this->export_marks)){
                $ext=BlockExtendRecord::firstAttr(["block_id"=>$value['id']]);
                unset($ext['block_id']);
                $export_result["blocks"][$value['mark']]=array_merge($value,$ext);
                if($value['tpl']){
                    $file=$value['tpl']==-1?$this->block_manager->tplCacheFile($value['id']):$this->block_manager->getTplFile($value['type'],$value['tpl']);
                    $export_result['tpls'][$value['mark']]=file_get_contents($file);
                }
            }
        }
        foreach ($this->block_areas as $key => $value) {
            if(in_array($key,$this->export_areas)){
                if($key=="blocks"&&$value){
                    $export_result['areas'][$key]=array_intersect($value, $this->export_marks);
                }else{
                    $export_result['areas'][$key]=$value;
                }
            }
        }
        $file_name=preg_replace('/\d|(\.php)/','',$this->file_var).".json";
        if(self::$app->holder_response){
            return self::$app->setResponse(200,['Content-type'=>'application/json;','Content-Disposition'=>'attachment; filename="'.$file_name.'"'],\w3c\helper\str::toJson($export_result));
        }else{
            header('Content-type: application/json;');
            header('Content-Disposition: attachment; filename="'.$file_name.'"');
            echo \w3c\helper\str::toJson($export_result);
            exit;
        }

    }
    public function import($blocks,$file,$ignore_exist){
        $marks=array_keys($blocks['blocks']);
        $exist_blocks=BlockRecord::findAll(["mark"=>$marks]);
        $exist_marks=array();
        $exist_b_m=array();
        foreach ($exist_blocks as $key => $value) {
            $exist_marks[]=$value['mark'];
            $exist_b_m[$value['mark']]=$value;
        }
        foreach ($blocks['blocks'] as $key => $value) {
            $tplfile="";
            if(in_array($key, $exist_marks)){
                if($ignore_exist)continue;
                unset($value['id']);
                $exist_b_m[$key]->setAttributes($value);
                if($exist_b_m[$key]->save()!==false){
                    $block_ex=new BlockExtendRecord(['block_id'=>$exist_b_m[$key]['id']]);
                    $block_ex->setAttributes($value);
                    $block_ex->save();
                    if($exist_b_m[$key]['tpl']==-1||$exist_b_m[$key]['tpl']!=$value['tpl']){
                        $tplfile=$this->block_manager->saveTemplate($exist_b_m[$key]['id'],$blocks['tpls'][$key]);
                    }
                }else{
                    return false;
                }
            }else{
                unset($value['id']);
                if($value['tpl']&&$value['tpl']!=-1){
                    $tplfile=$this->block_manager->getTplFile($value['type'],$value['tpl']);
                    if(file_exists($tplfile)==false){
                        $tplfile="";
                        $value['tpl']=-1;
                    }
                }
                $block=new BlockRecord($value);
                if($block->save()){
                    $block_ex=new BlockExtendRecord($value);
                    $block_ex->block_id=$block->primary();
                    $block_ex->save();
                    if(!$tplfile){
                        $this->block_manager->saveTemplate($block_ex->block_id,$blocks['tpls'][$key]);
                    }

                }else{
                    $error=2;
                    return false;
                }
            }
        }
        foreach ($blocks['areas'] as $key => $value) {
            $this->block_manager->areaAdd($key,$value['blocks']);
        }
        if($file){
            self::$app->template()->clearFile($file);
        }
        return true;
    }
}