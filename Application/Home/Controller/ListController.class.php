<?php
namespace Home\Controller;
use Think\Controller;
class ListController extends SimpleController {
    public function index(){
    	$database = M('order');
        $status = I('get.status');
        $emerg = I('get.emerg');
    	if(isset($status)&&$status!='')$map['status'] = I('get.status');
        if(isset($emerg)&&$emerg!='')$map['emerg'] = I('get.emerg');
    	$list = $database->where($map)->order('time desc')->page(I('get.p').',25')->select();
    	$this->assign('list',$list);
    	$count = $database->where($map)->count();
        $show = pagination($count);
    	$this->assign('page',$show);
        $this->display('list');
    }	

    public function search(){
    	$database = M('order');
        $status = I('get.status');
        $emerg = I('get.emerg');
        if(isset($status)&&$status!='')$map['status'] = I('get.status');
        if(isset($emerg)&&$emerg!='')$map['emerg'] = I('get.emerg');  
        $where['order'] = array('like','%'.I('param.order').'%');
        $where['user'] = I('param.order');
        $where['doctor'] = I('param.order');
        $where['repairer'] = I('param.order');    
        $where['_logic'] = 'or';
        $map['_complex'] = $where;         
    	$list = $database->where($map)->order('time desc')->page(I('get.p').',25')->select();
    	$this->assign('list',$list);
    	$count = $database->where($map)->count();
        $show = pagination($count);
    	$this->assign('page',$show);    	
    	$this->display('search-list');
    }
}