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

    	//获取站点配置
        $global = M('setting')->where("`key`='global'")->find();
        $global = json_decode($global['value'],true); 
        //是否允许注册
        $this->assign('allowregister',$global['allowregister']);
        $this->assign('quickreport',$global['quickreport']);

    	if(IS_POST){
            $database = M('user');  
            if(!D("User")->create()){
                $this->error(D("User")->getError(),U('User/login')); 
            }         
            if(!$this->checkVerify(I('post.verify'))){
                $this->error('验证码错误',U('User/login'));
            } 

    		$user = $database->where('uid = :uid')->bind(array(':uid'=>I('post.uid')))->find();
            if(empty($user)){  
                $this->error('用户不存在',U('User/login'));     
            }

            //姓名+一卡通
            if($global['quickreport']=='true'){
	            $user = $database->where('uid = :uid and username = :username')->bind(array(':uid'=>I('post.uid'),':username'=>I('post.username')))->find();
	    		if(empty($user))$this->error('信息不匹配',U('User/login'));
            }else{//用户名+密码
	            $user = $database->where('uid = :uid and password = :password')->bind(array(':uid'=>I('post.uid'),':password'=>sha1(C('DB_PREFIX').I('post.password').'_'.$user['salt'])))->find();
	    		if(empty($user))$this->error('密码不匹配',U('User/login'));
            }                		

			$data['lastip'] = get_client_ip();
			$data['lasttime'] = time();
			$database->where('uid=:uid')->bind(':uid',$user['uid'])->save($data);
			session('uid',$user['uid']);
			if(!empty($user['username']))session('username',$user['username']);
			if(!empty(I('get.returnURL')))redirect(base64_decode(I('get.returnURL')));
			$this->redirect('Main/index');
    	}else{
	        $tips = M('setting')->where("`key`='tips'")->find();
	        $tips = json_decode($tips['value'],true); 
	        $this->assign('tips',$tips['login']);     		
    		$this->display('login');
    	}
    }

    //用户注册
    public function register(){
    	if(session('?uid'))$this->redirect('order');
        //获取站点配置
        $global = M('setting')->where("`key`='global'")->find();
        $global = json_decode($global['value'],true); 
        //是否允许注册
        $this->assign('allowregister',$global['allowregister']);
        $this->assign('quickreport',$global['quickreport']);

        if($global['allowregister']=='false')$this->error('站点已关闭注册');  	
    	if(IS_POST){
    		$database = M('user');          
            if(!D("User")->create()){
                $this->error(D("User")->getError(),U('User/register'));
            }
            if(!$this->checkVerify(I('post.verify'))){
                $this->error('验证码错误',U('User/register'));
            }
            $data['uid'] = I('post.uid');
			if($database->where($map)->find())$this->error('用户已存在',U('User/register'));
            if($global['quickreport']=='false'){
                $salt = salt();
                $data['salt'] = $salt;
                $data['password'] = sha1(C('DB_PREFIX').I('post.password').'_'.$salt);               
            }else{
                $data['username'] = I('post.username');
            }

            $add = $database->strict(true)->data($data)->filter('strip_tags')->add();
            if($add){
            	$this->success('注册成功',U('User/login'));
            }else{
            	$this->error('注册失败');
            }
    	}else{
	        $tips = M('setting')->where("`key`='tips'")->find();
	        $tips = json_decode($tips['value'],true); 
	        $this->assign('tips',$tips['register']);    		
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
    	//获取站点配置
        $global = M('setting')->where("`key`='global'")->find();
        $global = json_decode($global['value'],true); 
        //是否开启快速报修
        $this->assign('quickreport',$global['quickreport']);    	
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
    		if(!empty(I('post.password'))){
                $data['salt'] = salt();
                $data['pssword'] = sha1(C('DB_PREFIX').I('post.password').'_'.$data['salt']);
            }
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

    public function cancel(){
    	if(!session('?uid'))$this->redirect('login');
    	if(IS_AJAX && IS_POST){
    		$database = M('order');
    		$map['order'] = ':order';
    		$map['user'] = ':user';
    		$bind[':order'] = I('post.order');
    		$bind[':user'] = session('uid');
    		$order = $database->where($map)->bind($bind)->find();
    		if(!empty($order) AND $order['status']==0){
    			$data['status'] = -1;
    			$data['canceltime'] = time();
    			$cancel = $database->lock(true)->where($map)->bind($bind)->save($data);
    			echo $cancel;
    		}
    	}
    }
}