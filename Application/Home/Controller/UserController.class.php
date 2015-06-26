<?php
namespace Home\Controller;
use Think\Controller;
class UserController extends SimpleController {
    public function index(){
    	$this->redirect('login');
    }

    //用户登录
    public function login(){
    	if(session('?uid'))$this->redirect('order');
    	if(IS_POST){
            $database = M('user');
            if (!$database->autoCheckToken($_POST)){
                $this->error('令牌验证错误');
            }             
            if(!$this->checkVerify(I('post.verify'))){
                $this->error('验证码错误',U('User/login'));
            }                 		
    		$user = $database->where('uid = :uid and password = :password')->bind(array(':uid'=>I('post.uid'),':password'=>sha1(I('post.password'))))->find();
    		if(empty($user)){
    			$this->error('账户不存在或密码不匹配',U('User/login'));
    		}else{
    			$data['lastip'] = get_client_ip();
    			$data['lasttime'] = time();
    			$database->where('uid=:uid')->bind(':uid',$user['uid'])->save($data);
    			session('uid',$user['uid']);
    			if(!empty($user['username']))session('username',$user['username']);
    			$this->success('登录成功',U('Main/index'));
    		}
    	}else{
    		$this->display('login');
    	}
    }

    //用户注册
    public function register(){
    	if(session('?uid'))$this->redirect('order');
    	if(IS_POST){
    		$database = M('user');
            if (!$database->autoCheckToken($_POST)){
                $this->error('令牌验证错误');
            }             
            if(!$this->checkVerify(I('post.verify'))){
                $this->error('验证码错误',U('User/register'));
            }
            $data['uid'] = I('post.uid');
            $salt = salt();
            $data['password'] = sha1(C('DB_PREFIX').I('post.password').'_'.$salt);
            $add = $database->data($data)->filter('strip_tags')->add();
            if($add){
            	$this->success('注册成功',U('User/login'));
            }else{
            	$this->error('注册失败');
            }
    	}else{
    		$this->display('register');
    	}
    }

    //找回密码
    public function findpsw(){

    }

    //用户注销
    public function logout(){
    	session(null);
    	$this->redirect('login');
    }

    //个人报修记录
    public function order(){
    	if(!session('?uid'))$this->redirect('login');   
    	$database = M('order');	
    	$list = $database->where('user=:user')->bind(':user',session('uid'))->order('time desc')->page(I('get.p/d').',25')->select();
    	$this->assign('list',$list);
		$count = $database->where('user=:user')->bind(':user',session('uid'))->count();// 查询满足要求的总记录数
		$show = pagination($count);// 分页显示输出
		$this->assign('page',$show);// 赋值分页输出
    	$this->display('order'); 
    }

    //个人信息设置
    public function setting(){
    	if(!session('?uid'))$this->redirect('login');
    	$database = M('user');
    	if(IS_POST){   
            if (!$database->autoCheckToken($_POST)){
                $this->error('令牌验证错误');
            }                 
    		$data['area'] = I('post.area/d');
            if(!selectCheck($data['area']))$this->error('参数非法');
    		$data['building'] = I('post.building/d');
            if(!selectCheck($data['area'],$data['building']))$this->error('参数非法');
    		$data['location'] = I('post.location');
    		$data['tel'] = I('post.tel');
    		$data['pssword'] = I('post.password');
    		$update = $database->where('uid=:uid')->bind(':uid',session('uid'))->filter('strip_tags')->save($data);
    		if($update){
    			$this->success('个人信息更新成功');
    		}else{
    			$this->error('个人信息无更新或失败');
    		}
    	}else{
	    	$user = $database->where('uid=:uid')->bind(':uid',session('uid'))->find();
            $data = menu();
            $this->assign('data',json_encode($data));
	    	$this->assign('user',$user);
	    	$this->display('setting');
    	}
    }

    public function v(){
        $Verify =     new \Think\Verify();
        // 设置验证码字符为纯数字
        $Verify->codeSet = '0123456789'; 
        $Verify->entry();       
    }

    // 检测输入的验证码是否正确，$code为用户输入的验证码字符串
    public function checkVerify($code, $id = ''){
        $verify = new \Think\Verify();
        return $verify->check($code, $id);
    }      
}