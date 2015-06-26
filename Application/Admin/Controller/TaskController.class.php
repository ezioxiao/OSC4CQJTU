<?php
namespace Admin\Controller;
use Think\Controller;
class TaskController extends SimpleController {
	public function __construct(){
		parent::__construct();
		if(!session('?admin'))$this->redirect('Main/index');
	}

    public function index(){
    	$this->redirect('Main/dashboard');
    }	

    public function todo(){
    	$database = M('order');
    	if(IS_AJAX){
    		$order = I('post.order');
    		if(is_array($order)){  	   				
    			foreach($order as $k=>$v){
		    		$database->status = '0';
		    		$database->donetime = '';
		    		$database->dotime = '';     				
					$res = $database->where('`order`=:order')->bind(':order',$v)->save();	
    			}
    		}else{
	    		$database->status = '0';
	    		$database->donetime = '';
	    		$database->dotime = '';    			
    			$res = $database->where('`order`=:order')->bind(':order',$order)->save();
    		}
    		echo json_encode($res);
    	}else{
            $map['status'] = 0;
            if(!empty(I('get.emerg/d')))$map['emerg'] = I('get.emerg/d');
            if(!empty(I('get.order')))$map['order'] = array('like','%'.I('get.order').'%');
	    	$list = $database->where($map)->order('time desc')->page(I('get.p/d').',25')->select();
	    	$this->assign('list',$list);
	    	$count = $database->where($map)->count();
	    	$this->assign('count',$count);
	    	$page = pagination($count);
	    	$this->assign('page',$page);
	    	$this->display('admin-table-todo');
    	}
    }

    public function doing(){
    	$database = M('order');
    	if(IS_AJAX){
    		$order = I('post.order');  		
    		if(is_array($order)){
    			foreach($order as $k=>$v){
    				$database->status = '1';
    				$database->dotime = time(); 
    				$database->donetime = '';
    				$res = $database->where('`order`=:order')->bind(':order',$v)->save();	
    			}
    		}else{
    			$database->status = '1';
    			$database->dotime = time(); 
    			$database->donetime = '';    			
    			$res = $database->where('`order`=:order')->bind(':order',$order)->save();
    		}
    		echo json_encode($res);
    	}else{    	
            $map['status'] = 1;
            if(!empty(I('get.emerg/d')))$map['emerg'] = I('get.emerg/d');
            if(!empty(I('get.order')))$map['order'] = array('like','%'.I('get.order').'%');            
	    	$list = $database->where($map)->order('dotime desc')->page(I('get.p/d').',25')->select();
	    	$this->assign('list',$list);
	    	$count = $database->where($map)->count();
	    	$this->assign('count',$count);
	    	$page = pagination($count);
	    	$this->assign('page',$page);    	
	    	$this->display('admin-table-doing');
  		}
    }

    public function done(){
    	$database = M('order');
    	if(IS_AJAX){
    		$order = I('post.order');
    		if(is_array($order)){    			
    			foreach($order as $k=>$v){
		    		$database->status = '2';
		    		$database->donetime = time();    				
    				$res = $database->where('`order`=:order')->bind(':order',$v)->save();	
    			}
    		}else{
	    		$database->status = '2';
	    		$database->donetime = time();     			
    			$res = $database->where('`order`=:order')->bind(':order',$order)->save();
    		}
    		echo json_encode($res);
    	}else{
            $map['status'] = 2;
            if(!empty(I('get.emerg/d')))$map['emerg'] = I('get.emerg/d');
            if(!empty(I('get.order')))$map['order'] = array('like','%'.I('get.order').'%');             
	    	$list = $database->where($map)->order('donetime desc')->page(I('get.p/d').',25')->select();
	    	$this->assign('list',$list);
	    	$count = $database->where($map)->count();
	    	$this->assign('count',$count);
	    	$page = pagination($count);
	    	$this->assign('page',$page);    	
	    	$this->display('admin-table-done');
    	}
    }     

    public function del(){
    	$database = M('order');
    	if(IS_AJAX){
    		if(is_array(I('post.order'))){
    			$order = implode(',', I('post.order'));
    			$res = $database->delete($order);
    		}else{
    			$res = $database->delete(I('post.order'));
    		}
    		echo json_encode($res);
    	}
    } 
}