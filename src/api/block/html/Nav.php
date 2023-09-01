<?php
namespace api\block\html;
use common\model\SiteNavigation;

class Nav extends \api\block\BlockTpl{
    function loadData($args){
        $parent=empty($args['parent_id'])?'':$args['parent_id'];
        return SiteNavigation::getSeting($parent);
    }
}