<?php
    function creatOrderSn(){
        $yCode = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
        $orderSn = $yCode[intval(date('Y')) - 2015] . strtoupper(dechex(date('m'))) . date('d') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf('%02d', rand(0, 99));        
        return $orderSn;
    }

	function selectCheck(){
        $numargs = func_num_args();$args = func_get_args();
        $menu = menu();
        if($numargs==1){
            if(!empty($menu[$args[0]]))return true;
        }elseif($numargs==2){
            foreach($menu[$args[0]]['citys'] as $k=>$v){
                if($args[1]==$v['id'])return true;
            }
        }
        return false;
    }    