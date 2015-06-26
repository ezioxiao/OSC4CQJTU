<?php
namespace Admin\Controller;
use Think\Controller;
class UserController extends SimpleController {
	public function index(){
		if(!session('?admin'))$this->redirect('Main/index');
		$this->redirect('normal');
	}

    public function normal(){
    	if(!session('?admin'))$this->redirect('Main/index');
    	$databse = M('user');
    	$list = $databse->page(I('get.p/d').',25')->select();
    	$this->assign('list',$list);
    	$count = $databse->count();
    	$this->assign('count',$count);
    	$page = pagination($count);
    	$this->assign('page',$page);
        $this->display('admin-table');
    }	

}