<?php
$require=[];
$integer=[];
$float=[];
$strings=[];
$property=[];
foreach ($table_desc as $field_set){
    $property[$field_set['Field']]=empty($field_set['Comment'])?$field_set['Field']:$field_set['Comment'];
    if($field_set['Null']=="NO"&&$field_set['Default']===null&&$field_set['Extra']!='auto_increment'){
        $require[]=$field_set['Field'];
    }
    if(strtoupper($field_set['Type'])=='INTEGER'||strpos($field_set['Type'],"int")||strpos($field_set['Type'],"int")===0){
        if($field_set['Extra']!='auto_increment')
            $integer[]=$field_set['Field'];
    }else if(strpos($field_set['Type'],"float")===0
        ||strpos($field_set['Type'],"decimal")===0
        ||strpos($field_set['Type'],"money")===0
        ||strpos($field_set['Type'],"double")===0){
        $float[]=$field_set['Field'];
    }else{
        if(preg_match('/char\((\d+)\)/',$field_set['Type'],$m)){
            $strings[$m[1]][]=$field_set['Field'];
        }else{
            $strings[0][]=$field_set['Field'];
        }
    }
}
$rule=[];
if(!empty($require)){
    $rule[]='[[\''.implode("','",$require)."'],\"require\"]";
}
if(!empty($integer)){
    $rule[]='[[\''.implode("','",$integer)."'],\"integer\"]";
}
if(!empty($float)){
    $rule[]='[[\''.implode("','",$float)."'],\"float\"]";
}
foreach ($strings as $l=>$ss){
    if($l>0){
        $rule[]='[[\''.implode("','",$ss)."'],\"string\",$l]";
    }else{
        $rule[]='[[\''.implode("','",$ss)."'],\"string\"]";
    }
}
?>
namespace ?{$space_name};
/**
 * ?{$table_name}数据记录类
<?php
foreach($property as $c=>$desc){
    echo " * @property string \$$c $desc\n";
}
?> */
class ?{$class_name} extends \W3cRecord{
    <?php if($primary!='id'){
    echo '    protected $primaryName="'.$primary.'";
    ';
    }?>

    static public function recordName(){
        return '<?php if(strpos($table_name,$table_pre)===0) echo substr($table_name,strlen($table_pre));else echo $table_name?>';
    }

    static public function recordRule(){
    <?php
echo "    return [".implode(",        \n",$rule)."];";
?>

    }

    static public function propertyDesc(){
        return <?php echo var_export($property,true);?>;
    }
}