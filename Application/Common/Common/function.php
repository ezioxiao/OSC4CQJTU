<?php
	function pagination($count,$map=array(),$limit=25){
		$Page = new \Think\Page($count,$limit);
    	$Page->setConfig('prev','上一页');
    	$Page->setConfig('next','下一页');
		$Page->setConfig('last','最末页');
    	$Page->setConfig('first','第一页');
    	$Page->rollPage = 7;
    	$Page->lastSuffix = false;
    	if(!empty($map)){
			foreach($map as $key=>$val) {
			    $Page->parameter[$key]   =   urlencode($val);
			} 
    	} 	
    	$show = $Page->show();
    	return $show;
	}

	function area($index){
		$area = M('setting')->where('`key`="area"')->find();
		$area = json_decode($area['value'],true);
		return empty($area[$index])?$index:$area[$index];
	}

	function building($area,$index){
		$building = M('setting')->where('`key`="building"')->find();
		$building = json_decode($building['value'],true);
		foreach($building[$area] as $k=>$v){
			if($v['key']==$index){
				return $v['name'];
			}
		}
		return $index;
	}

	function status($status){
        switch($status){
            case 0:
            return '未维修';
            break;
            case 1:
            return '维修中';
            break;
            case 2:
            return '已维修';
            break;
        }
    }

    function menu(){
        $area = M('setting')->where('`key`="area"')->find();
        $area = json_decode($area['value'],true);
        $building = M('setting')->where('`key`="building"')->find();
        $building = json_decode($building['value'],true);
        foreach($area as $k=>$v){
            $data[$k]['id'] = $k;
            $data[$k]['name'] = $v;
            foreach($building[$k] as $key=>$value){
                $data[$k]['citys'][$key]['id'] = $value['key'];
                $data[$k]['citys'][$key]['name'] = $value['name'];
            }
        }    
        return $data;	
    }

    function salt() {
        $str = substr(md5(time()), 0, 6);
        return $str;
    }