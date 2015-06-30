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
    	$database = M('user');
    	$map['uid'] = array('like','%'.I('get.uid').'%');
    	$list = $database->where($map)->page(I('get.p/d').',25')->select();
    	$this->assign('list',$list);
    	$count = $database->where($map)->count();
    	$this->assign('count',$count);
    	$page = pagination($count);
    	$this->assign('page',$page);
        $this->display('admin-table');
    }	

    public function edit(){
    	if(!session('?admin'))$this->redirect('Main/index');
    	$database = M('user');
    	if(IS_POST){
	        if (!$database->autoCheckToken($_POST)){
	            $this->error('令牌验证错误');
	        }  
	        $map['uid'] = ':uid';
	        $bind[':uid'] = I('get.uid');
	        $data['salt'] = salt();
	        $data['password'] = sha1(C('DB_PREFIX').I('post.password').'_'.$data['salt']);
	        $update = $database->where($map)->bind($bind)->data($data)->filter('strip_tags')->save();
	    	if($update){
	    		$this->success('资料修改成功');
	    	}else{
	    		$this->error('资料修改失败');
	    	} 
    	}else{
    		$this->display('admin-edit');
    	}
    }

    public function add(){
    	if(!session('?admin'))$this->redirect('Main/index');
    	if(IS_POST){
    		$database = M('user');
 	        if (!$database->autoCheckToken($_POST)){
	            $this->error('令牌验证错误');
	        }   	
	        $data['uid'] = I('post.uid');
	        $data['salt'] = salt();
	        $data['password'] = sha1(C('DB_PREFIX').I('post.password').'_'.$data['salt']);
	        $add = $database->data($data)->filter('strip_tags')->add();	
	    	if($add){
	    		$this->success('用户增加成功');
	    	}else{
	    		$this->error('用户增加失败');
	    	} 	        
    	}else{
    		$this->display('admin-add');
    	}    	
    }

    public function del(){
    	if(!session('?admin'))$this->redirect('Main/index');
    	$database = M('user');
    	if(IS_AJAX){
    		if(is_array(I('post.uid'))){
    			$uid = implode(',', I('post.uid'));
    			$res = $database->delete($uid);
    		}else{
    			$res = $database->delete(I('post.uid'));
    		}
    		echo $res;
    	}
    } 

}