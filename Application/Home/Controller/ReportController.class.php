<?php
namespace Home\Controller;
use Think\Controller;
class ReportController extends SimpleController {
	public function __construct(){
		parent::__construct();
	}

	//普通报修
    public function index(){
    	if(session('?uid')){
    		$database = M('user');
 			$user = $database->where('uid = :uid')->bind(':uid',session('uid'))->find(); 
 			$this->assign('user',$user);
    	}else{
    		$this->redirect('User/login');
    	}
    	if(IS_POST){
    		$database = M('order');
            if (!$database->autoCheckToken($_POST)){
                $this->error('令牌验证错误');
            }              
    		$data['area'] = I('post.area/d');//校区
    		if(!selectCheck($data['area']))$this->error('参数非法');
			$data['building'] = I('post.building/d');//楼栋
			if(!selectCheck($data['area'],$data['building']))$this->error('参数非法');
    		$data['location'] = I('post.location');//寝室
    		$data['good'] = I('post.good');//物品
    		$data['description'] = I('post.description');//描述
    		$data['user'] = session('uid');//用户
    		$data['order'] = creatOrderSn();//工单号
    		$data['time'] = time();//时间
    		$data['status'] = 0;//状态 未处理0 处理中1 已处理2
    		$data['emerg'] = 0;//是否紧急 普通0 紧急1
    		$add = $database->data($data)->filter('strip_tags')->add();
    		if($add){
    			$this->success('报修提交成功',U('User/order'));
    		}else{
    			$this->error('报修提交失败');
    		}
    	}else{
            $data = menu();
            $this->assign('data',json_encode($data));
    		$this->display('report');
    	}
    }	

    //紧急报修
    public function emerg(){
    	if(session('?uid')){
    		$database = M('user');
 			$user = $database->where('uid = :uid')->bind(':uid',session('uid'))->find(); 
 			$this->assign('user',$user); 
    	}else{
    		$this->redirect('User/login');
    	}	
    	if(IS_POST){
    		$database = M('order');
            if (!$database->autoCheckToken($_POST)){
                $this->error('令牌验证错误');
            }             
    		$data['area'] = I('post.area/d');//校区
    		if(!selectCheck($data['area']))$this->error('参数非法');
    		$data['location'] = I('post.location');//地点
    		$data['description'] = I('post.description');//描述
    		$data['user'] = session('uid');//用户
    		$data['order'] = creatOrderSn();//工单号
    		$data['time'] = time();//时间
    		$data['status'] = 0;//状态 未处理0 处理中1 已处理2
    		$data['emerg'] = 1;//是否紧急 普通0 紧急1
    		$add = $database->data($data)->filter('strip_tags')->add();
    		if($add){
    			$this->success('报修提交成功',U('User/order'));
    		}else{
    			$this->error('报修提交失败');
    		}
    	}else{
            $data = menu();
            $this->assign('data',json_encode($data));            
    		$this->display('emerg');
    	}    	   	       
    }

    //工单详情
    public function detail(){
    	$detail = M('order')->where('`order`=:order')->bind(':order',I('get.order'))->find();
    	if(empty($detail)){
    		$this->error('该工单不存在');
    	}else{
            $user = M('user')->where('uid = :uid')->bind(':uid',$detail['user'])->find(); 
            $this->assign('user',$user); 
    		$this->assign('detail',$detail);
    		$this->display('detail');
    	}
    }
}