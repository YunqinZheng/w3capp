namespace ?{$space_name};
use ?{$space_m};

class ?{$class_name} extends \medium\W3cEnter{
<!--{if(in_array('index',$_POST['action_name']))}-->
    public function index($page=1){
        $list_obj=?{$space_m_name}::adaptTo([]);
        $list_obj->limit(10,$page);
        $list=$list_obj->selectAll(true);
        <!--{if($_POST['return_type']=="html")}-->
        $this->_assign("list",$list);
        $this->_tpl("?{$class_name}_index")->output();
        <!--{/if}-->
        <!--{if($_POST['return_type']=="json")}-->
        $this->_json_return($list);
        <!--{/if}-->

    }
<!--{/if}-->

<!--{if(in_array('add',$_POST['action_name']))}-->
    public function add(){
        $record_obj=new ?{$space_m_name}();
        $record_obj->setAttributes($_POST);
        <!--{if($_POST['return_type']=="html")}-->
        if($record_obj->save()===false){
            return $this->_referer_to("添加失败!!");
        }else{
            return $this->_referer_to("添加成功!!","","right");
        }
        <!--{/if}-->
        <!--{if($_POST['return_type']=="json")}-->
        if($record_obj->save()===false){
            $this->_json_return(1,"添加失败!!");
        }else{
            $this->_json_return(0,"添加成功!!","",['?{$primary}'=>$record_obj->primary()]);
        }
        <!--{/if}-->

    }
<!--{/if}-->

<!--{if(in_array('edit',$_POST['action_name']))}-->
    public function edit(){
        $record_obj=?{$space_m_name}::record(['?{$primary}'=>$_POST['id']]);
        $record_obj->setAttributes($_POST);
        <!--{if($_POST['return_type']=="html")}-->
        if($record_obj->save()===false){
            return $this->_referer_to("修改失败!!");
        }else{
            return $this->_referer_to("修改成功!!","","right");
        }
        <!--{/if}-->
        <!--{if($_POST['return_type']=="json")}-->
        if($record_obj->save()===false){
            $this->_json_return(1,"修改失败!!");
        }else{
            $this->_json_return(0,"修改成功!!","",['id'=>$record_obj->primary()]);
        }
        <!--{/if}-->

    }
<!--{/if}-->

<!--{if(in_array('delete',$_POST['action_name']))}-->
    public function delete(){
        //$record_obj=?{$space_m_name}::record(['?{$primary}'=>$_POST['id']]);
        //$result=$record_obj->delete();
        $result=?{$space_m_name}::deleteAll(['?{$primary}'=>$_POST['?{$primary}']]);

    }
<!--{/if}-->

}