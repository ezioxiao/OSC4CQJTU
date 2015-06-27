<?php
namespace Admin\Controller;
use Think\Controller;
class MainController extends SimpleController {
    //登录
    public function index(){
        if(session('?admin'))$this->redirect('Main/dashboard');
    	if(IS_POST){
    		$database = M('admin');
            if(!D("admin")->create()){
                $this->error(D("admin")->getError(),U('Main/index')); 
            } 	
			if(!$this->checkVerify(I('post.verify'))){
				$this->error('验证码错误',U('Main/index'));
			}  
            $bind[':username'] = I('post.username'); 
            $admin = $database->where('username=:username')->bind($bind)->find();             		
    		if(empty($admin))$this->error('用户不存在',U('Main/index'));
    		$bind[':password'] = sha1(C('DB_PREFIX').I('post.password').'_'.$admin['salt']);
    		$admin = $database->where('username=:username and password=:password')->bind($bind)->find();
    		if(empty($admin)){
    			$this->error('密码错误',U('Main/index'));
    		}else{
	            session('admin',$admin['username']);
                $data['lastip'] = get_client_ip();
                $data['lasttime'] = time();
                $database->where('username=:username and password=:password')->bind($bind)->save($data);
	    		$this->redirect('dashboard');
    		}
    	}else{
    		$this->display('admin-login');
    	}
    }	

    //Echart.js 7天内统计数据
    public function dashboard(){
        if(!session('?admin'))$this->redirect('Main/index');
        $stat['category'] = json_encode(array(
            date('y-m-d',strtotime('-6 days')),
            date('y-m-d',strtotime('-5 days')),
            date('y-m-d',strtotime('-4 days')),
            date('y-m-d',strtotime('-3 days')),
            date('y-m-d',strtotime('-2 days')),
            date('y-m-d',strtotime('-1 days')),
            date('y-m-d')));
        $stat['todo'] = json_encode(array(
            M('order')->where('status=0 and time>'.strtotime('-6 days').' and time<'.strtotime('-5 days'))->count(),
            M('order')->where('status=0 and time>'.strtotime('-5 days').' and time<'.strtotime('-4 days'))->count(),
            M('order')->where('status=0 and time>'.strtotime('-4 days').' and time<'.strtotime('-3 days'))->count(),
            M('order')->where('status=0 and time>'.strtotime('-3 days').' and time<'.strtotime('-2 days'))->count(),
            M('order')->where('status=0 and time>'.strtotime('-2 days').' and time<'.strtotime('-1 days'))->count(),
            M('order')->where('status=0 and time>'.strtotime('-1 days').' and time<'.strtotime(date('y-m-d')))->count(),
            M('order')->where('status=0 and time>'.strtotime(date('y-m-d')))->count()));
        $stat['doing'] = json_encode(array(
            M('order')->where('status=1 and time>'.strtotime('-6 days').' and time<'.strtotime('-5 days'))->count(),
            M('order')->where('status=1 and time>'.strtotime('-5 days').' and time<'.strtotime('-4 days'))->count(),
            M('order')->where('status=1 and time>'.strtotime('-4 days').' and time<'.strtotime('-3 days'))->count(),
            M('order')->where('status=1 and time>'.strtotime('-3 days').' and time<'.strtotime('-2 days'))->count(),
            M('order')->where('status=1 and time>'.strtotime('-2 days').' and time<'.strtotime('-1 days'))->count(),
            M('order')->where('status=1 and time>'.strtotime('-1 days').' and time<'.strtotime(date('y-m-d')))->count(),
            M('order')->where('status=1 and time>'.strtotime(date('y-m-d')))->count()));
        $stat['done'] = json_encode(array(
            M('order')->where('status=2 and time>'.strtotime('-6 days').' and time<'.strtotime('-5 days'))->count(),
            M('order')->where('status=2 and time>'.strtotime('-5 days').' and time<'.strtotime('-4 days'))->count(),
            M('order')->where('status=2 and time>'.strtotime('-4 days').' and time<'.strtotime('-3 days'))->count(),
            M('order')->where('status=2 and time>'.strtotime('-3 days').' and time<'.strtotime('-2 days'))->count(),
            M('order')->where('status=2 and time>'.strtotime('-2 days').' and time<'.strtotime('-1 days'))->count(),
            M('order')->where('status=2 and time>'.strtotime('-1 days').' and time<'.strtotime(date('y-m-d')))->count(),
            M('order')->where('status=2 and time>'.strtotime(date('y-m-d')))->count()));
        $this->assign('stat',$stat);
        $list = M('order')->where('emerg=1')->order('time desc')->limit(5)->select();
        $this->assign('list',$list);
    	$this->display('admin-index');
    }

    //密码修改
    public function user(){
        if(!session('?admin'))$this->redirect('Main/index');
        if(IS_POST){
            $database = M('admin');
            if (!$database->autoCheckToken($_POST)){
                $this->error('令牌验证错误',U('Main/setting'));
            }           
            $bind[':username'] = session('admin');
            $admin = $database->where('username=:username')->bind($bind)->find();
            if(empty($admin))$this->error('用户不存在');
            $data['password'] = sha1(C('DB_PREFIX').I('post.n-password').'_'.$admin['salt']);
            $update = $database->where('username=:username')->bind($bind)->save($data);
            if($update){
                $this->success('密码已更新',U('Main/user'));
            }else{
                $this->error('原密码不匹配',U('Main/user'));
            }           
        }else{
            $this->display('admin-user');
        }
    }  

    //系统设置
    public function setting(){
        if(!session('?admin'))$this->redirect('Main/index');
        $database = M('setting');
        if(IS_POST){         
    		if (!$database->autoCheckToken($_POST)){
    			$this->error('令牌验证错误',U('Main/setting'));
    		}        	
            $menu = I('post.config');
            $menu = $menu['menu']['button'];
            foreach($menu as $k=>$v){
                $area[] = $v['name'];
                $building[] = $v['sub_button'];
            }
            $data['key'] = 'area';
            $data['value'] = json_encode($area);
            $database->add($data,array(),true);
            $data['key'] = 'building';
            $data['value'] = json_encode($building);
            $database->add($data,array(),true);            
        }
        $area = $database->where("`key`='area'")->find();
        $building = $database->where("`key`='building'")->find();
        $area = json_decode($area['value'],true);
        $building = json_decode($building['value'],true);
        $this->assign('area',$area);
        $this->assign('building',$building);
        $this->display('admin-setting');
    }    

    //注销
    public function logout(){
        session(null);
    	$this->redirect('Main/index');
    }

    //中文验证码
    public function v(){
		$Verify =     new \Think\Verify();
		// 验证码字体使用 ThinkPHP/Library/Think/Verify/ttfs/5.ttf
		$Verify->useZh = true; 
		$Verify->entry();    	
    }

	// 检测输入的验证码是否正确，$code为用户输入的验证码字符串
	public function checkVerify($code, $id = ''){
		$verify = new \Think\Verify();
		return $verify->check($code, $id);
	}

}