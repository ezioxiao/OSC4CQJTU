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
    		$this->redirect('User/login',array('returnURL'=>base64_encode(__SELF__)));
    	}
    	if(IS_POST){
    		$database = M('order');
            if (!$database->autoCheckToken($_POST)){
                $this->error('令牌验证错误');
            }        
            if(!empty(I('post.tel'))){
            	M('user')->where('uid=:uid')->bind(':uid',session('uid'))->save(array('tel'=>I('post.tel')));
            }      
    		$data['area'] = I('post.area/d');//校区
    		if(!selectCheck($data['area']))$this->error('参数非法');
			$data['building'] = I('post.building/d');//楼栋
			if(!selectCheck($data['area'],$data['building']))$this->error('参数非法');
    		$data['location'] = I('post.location');//寝室
    		$data['good'] = I('post.good');//物品
    		$data['description'] = I('post.description');//描述
    		$data['user'] = session('uid');//用户
    		$data['order'] = creatOrderSn($data['area']);//工单号
    		$data['time'] = time();//时间
    		$data['status'] = 0;//状态 未处理0 处理中1 已处理2
    		$data['emerg'] = 0;//是否紧急 普通0 紧急1
    		$add = $database->strict(true)->data($data)->filter('strip_tags')->add();
    		if($add){
    			$this->success('报修提交成功',U('User/order'));
    		}else{
    			$this->error('报修提交失败');
    		}
    	}else{
            $tips = M('setting')->where("`key`='tips'")->find();
            $tips = json_decode($tips['value'],true); 
            $this->assign('tips',$tips['report']);             
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
    		$this->redirect('User/login',array('returnURL'=>base64_encode(__SELF__)));
    	}	
    	if(IS_POST){
    		$database = M('order');
            if (!$database->autoCheckToken($_POST)){
                $this->error('令牌验证错误');
            } 
            if(!empty(I('post.tel'))){
            	M('user')->where('uid=:uid')->bind(':uid',session('uid'))->save(array('tel'=>I('post.tel')));
            }                        
    		$data['area'] = I('post.area/d');//校区
    		if(!selectCheck($data['area']))$this->error('参数非法');
    		$data['location'] = I('post.location');//地点
    		$data['description'] = I('post.description');//描述
    		$data['user'] = session('uid');//用户
    		$data['order'] = creatOrderSn($data['area']);//工单号
    		$data['time'] = time();//时间
    		$data['status'] = 0;//状态 未处理0 处理中1 已处理2
    		$data['emerg'] = 1;//是否紧急 普通0 紧急1
    		$add = $database->strict(true)->data($data)->filter('strip_tags')->add();
    		if($add){
    			$this->success('报修提交成功',U('User/order'));
    		}else{
    			$this->error('报修提交失败');
    		}
    	}else{
            $tips = M('setting')->where("`key`='tips'")->find();
            $tips = json_decode($tips['value'],true); 
            $this->assign('tips',$tips['emerg']);             
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
            $tips = M('setting')->where("`key`='tips'")->find();
            $tips = json_decode($tips['value'],true); 
            $this->assign('tips',$tips['detail']);             
            $user = M('user')->where('uid = :uid')->bind(':uid',$detail['user'])->find(); 
			//用户最新评价
			$rank['user'] = M('rank')->where("`order` = :order and `type`='0'")->bind(':order',I('get.order'))->order('time desc')->find();
			//管理最新回复
			$rank['admin'] = M('rank')->where("`order` = :order and `type`='1'")->bind(':order',I('get.order'))->order('time desc')->find();
            $this->assign('user',$user); 
    		$this->assign('detail',$detail);
			$this->assign('rank',$rank);
    		$this->display('detail');
    	}
    }
	
	//评价与回复
	public function rank(){
		if(!session('?admin') or !session('?uid'))$this->error('非法访问');
		if(IS_POST){
			$data['order'] = I('get.order');
			$order = M('order')->where($data)->find();
			if(time()-$order['time']>3600*24*3)$this->error('评价超时');
			if(session('?uid') and I('get.type')==0){
				$data['user']=session('uid');
				if(session('uid') != $order['user'])$this->error('操作无权限');
				$data['type']=0;
			}
			elseif(session('?admin') and I('get.type')==1){
				$data['user']=session('admin');
				$data['type']=1;
			}
			else{
				$this->error('参数错误');
			}
			$data['content'] = I('post.rankc');
			$data['time'] = time();
			if(M('rank')->data($data)->filter('strip_tags')->add()){
				$this->success('操作成功');
			}else{
				$this->error('操作失败');
			}
		}
	}
	
	public function avatar(){
		$data = I('get.data');
		$identicon = new \Org\Identicon\Identicon();
		$identicon->displayImage($data,256);
	}	
}