<?php
namespace Admin\Controller;
use Think\Controller;
class UserController extends SimpleController {
	public function index(){
		if(!session('?admin'))$this->redirect('Main/index');
        if(session('right')!=1)$this->error('访问无权限');
		$this->redirect('normal');
	}

    public function normal(){
    	if(!session('?admin'))$this->redirect('Main/index');
        if(session('right')!=1)$this->error('访问无权限');
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

    //上传方法
    public function upload()
    {
        if(!session('?admin'))$this->redirect('Main/index');
        if(session('right')!=1)$this->error('访问无权限');        
        header("Content-Type:text/html;charset=utf-8");
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize   =     3145728 ;// 设置附件上传大小
        $upload->exts      =     array('xls', 'xlsx');// 设置附件上传类
        $upload->savePath  =      '/'; // 设置附件上传目录
        // 上传文件
        $info   =   $upload->uploadOne($_FILES['excelData']);
        $filename = './Uploads'.$info['savepath'].$info['savename'];
        $exts = $info['ext'];
        //print_r($info);exit;
        if(!$info) {// 上传错误提示错误信息
              $this->error($upload->getError());
          }else{// 上传成功
              $this->goods_import($filename, $exts);
        }
    }  

    //导入数据页面
    public function import()
    {
        if(!session('?admin'))$this->redirect('Main/index');
        if(session('right')!=1)$this->error('访问无权限');        
        $this->display('goods_import');
    }

    //导入数据方法
    protected function goods_import($filename, $exts='xls')
    {
        if(!session('?admin'))$this->redirect('Main/index');
        if(session('right')!=1)$this->error('访问无权限');        
        //导入PHPExcel类库，因为PHPExcel没有用命名空间，只能inport导入
        import("Org.Util.PHPExcel");
        //创建PHPExcel对象，注意，不能少了\
        $PHPExcel=new \PHPExcel();
        //如果excel文件后缀名为.xls，导入这个类
        if($exts == 'xls'){
            import("Org.Util.PHPExcel.Reader.Excel5");
            $PHPReader=new \PHPExcel_Reader_Excel5();
        }else if($exts == 'xlsx'){
            import("Org.Util.PHPExcel.Reader.Excel2007");
            $PHPReader=new \PHPExcel_Reader_Excel2007();
        }


        //载入文件
        $PHPExcel=$PHPReader->load($filename);
        //获取表中的第一个工作表，如果要获取第二个，把0改为1，依次类推
        $currentSheet=$PHPExcel->getSheet(0);
        //获取总列数
        $allColumn=$currentSheet->getHighestColumn();
        //获取总行数
        $allRow=$currentSheet->getHighestRow();
        //循环获取表中的数据，$currentRow表示当前行，从哪行开始读取数据，索引值从0开始
        for($currentRow=1;$currentRow<=$allRow;$currentRow++){
            //从哪列开始，A表示第一列
            for($currentColumn='A';$currentColumn<=$allColumn;$currentColumn++){
                //数据坐标
                $address=$currentColumn.$currentRow;
                //读取到的数据，保存到数组$arr中
                $data[$currentRow][$currentColumn]=$currentSheet->getCell($address)->getValue();
            }

        }
        $this->save_import($data);
    }

    //保存导入数据
    public function save_import($data)
    {          	
        if(!session('?admin'))$this->redirect('Main/index');
        if(session('right')!=1)$this->error('访问无权限');        
        $database = M('user');
        foreach ($data as $k=>$v){
            if($k == 1){
                foreach ($v as $key => $value) {
                    if(preg_match('/一卡通|卡号|学号|职工号/is',$value)){
                        $item['uid'] = $key;
                    }
                    if(preg_match('/姓名/is',$value)){
                        $item['username'] = $key;
                    }
                    if(preg_match('/身份证/is',$value)){
                        $item['password'] = $key;
                    }
                    if(empty($item['uid']) AND (empty($item['username']) OR empty($item['password'])) ){
                        $this->error('没有检索到一卡通号+姓名或一卡通号+身份证号的有效的字段组合');
                    }
                }
            }
            if($k >= 2){
                $add['uid'] = $v[$item['uid']];
                if(!empty($item['username'])){
                    $add['username'] = $v[$item['username']];
                }
                if(!empty($item['password'])){
                    $add['salt'] = salt();
                    $add['password'] = sha1(C('DB_PREFIX').substr($v[$item['password']],-6,6).'_'.$add['salt']);
                }
                $result = $database->add($add,$options=array(),$replace=true);
            }
        }
        if($result){
            $this->success('用户导入成功', U('User/normal'));
        }else{
            $this->error('用户导入失败');
        }        
    }

    public function edit(){
    	if(!session('?admin'))$this->redirect('Main/index');
        if(session('right')!=1)$this->error('访问无权限');
    	//获取站点配置
        $global = M('setting')->where("`key`='global'")->find();
        $global = json_decode($global['value'],true); 
        $this->assign('quickreport',$global['quickreport']);

    	$database = M('user');
    	if(IS_POST){
	        if (!$database->autoCheckToken($_POST)){
	            $this->error('令牌验证错误');
	        }  
	        $map['uid'] = ':uid';
	        $bind[':uid'] = I('get.uid');
            //姓名+一卡通
            if($global['quickreport']=='true'){
            $data['username'] = I('post.username');
            }else{//用户名+密码
	        $data['salt'] = salt();
	        $data['password'] = sha1(C('DB_PREFIX').I('post.password').'_'.$data['salt']);
	    	}
            $data['area'] = I('post.area');
            $data['building'] = I('post.building');
            $data['tel'] = I('post.tel');
            $data['location'] = I('post.location');
	        $update = $database->where($map)->bind($bind)->data($data)->filter('strip_tags')->save();
	    	if($update){
	    		$this->success('资料修改成功');
	    	}else{
	    		$this->error('资料修改失败');
	    	} 
    	}else{
            $data = menu();
            $this->assign('data',json_encode($data));
            $user = $database->where('uid=:uid')->bind(':uid',I('get.uid'))->find();
            $this->assign('user',$user);            
    		$this->display('admin-edit');
    	}
    }

    public function add(){
    	if(!session('?admin'))$this->redirect('Main/index');
        if(session('right')!=1)$this->error('访问无权限');

    	//获取站点配置
        $global = M('setting')->where("`key`='global'")->find();
        $global = json_decode($global['value'],true);
        $this->assign('quickreport',$global['quickreport']); 

    	if(IS_POST){
    		$database = M('user');
 	        if (!$database->autoCheckToken($_POST)){
	            $this->error('令牌验证错误');
	        }   	
	        $data['uid'] = I('post.uid');
            //姓名+一卡通
            if($global['quickreport']=='true'){
            $data['username'] = I('post.username');
            }else{//用户名+密码
	        $data['salt'] = salt();
	        $data['password'] = sha1(C('DB_PREFIX').I('post.password').'_'.$data['salt']);
            }	   
            $data['area'] = I('post.area');
            $data['building'] = I('post.building');
            $data['tel'] = I('post.tel');
            $data['location'] = I('post.location');                 
	        $add = $database->strict(true)->data($data)->filter('strip_tags')->add();	
	    	if($add){
	    		$this->success('用户增加成功');
	    	}else{
	    		$this->error('用户增加失败');
	    	} 	        
    	}else{
            $data = menu();
            $this->assign('data',json_encode($data));            
    		$this->display('admin-add');
    	}    	
    }

    public function del(){
    	if(!session('?admin'))$this->redirect('Main/index');
        if(session('right')!=1)$this->error('访问无权限');
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

    public function doctor(){
    	if(!session('?admin'))$this->redirect('Main/index');
    	if(session('right')!=1)$this->error('访问无权限');
    	$database = M('admin');
    	$map['username'] = array('like','%'.I('get.username').'%');
    	$list = $database->where($map)->page(I('get.p/d').',25')->select();
    	$this->assign('list',$list);
    	$count = $database->where($map)->count();
    	$this->assign('count',$count);
    	$page = pagination($count);
    	$this->assign('page',$page);
    	$this->display('admin-table-doctor');
    }

    public function doctorAdd(){
    	if(!session('?admin'))$this->redirect('Main/index');
    	if(session('right')!=1)$this->error('访问无权限');
    	if(IS_POST){
    		$database = M('admin');
 	        if (!$database->autoCheckToken($_POST)){
	            $this->error('令牌验证错误');
	        }   	
	        $data['username'] = I('post.username');
            if(in_array(I('post.right/d'), array(0,1)))$data['right'] = I('post.right/d');
	        $data['salt'] = salt();
	        $data['password'] = sha1(C('DB_PREFIX').I('post.password').'_'.$data['salt']);
	        $add = $database->strict(true)->data($data)->filter('strip_tags')->add();	
	    	if($add){
	    		$this->success('用户增加成功');
	    	}else{
	    		$this->error('用户增加失败');
	    	} 	        
    	}else{
    		$this->display('admin-add-doctor');
    	}    	
    }

    public function doctorDel(){
    	if(!session('?admin'))$this->redirect('Main/index');
    	if(session('right')!=1)$this->error('访问无权限');
    	$database = M('admin');
    	$user = $database->where('username=:username')->bind(':username',session('admin'))->find();
    	if(IS_AJAX){
    		if(is_array(I('post.uid'))){
    			if(in_array($user['uid'],I('post.uid')))die;
    			$uid = implode(',', I('post.uid'));
    			$res = $database->delete($uid);
    		}else{
    			if($user['uid']==I('post.uid'))die;
    			$res = $database->delete(I('post.uid'));
    		}
    		echo $res;
    	}
    } 

    public function doctorEdit(){
    	if(!session('?admin'))$this->redirect('Main/index');
    	if(session('right')!=1)$this->error('访问无权限');
    	$database = M('admin');
    	if(IS_POST){
	        if (!$database->autoCheckToken($_POST)){
	            $this->error('令牌验证错误');
	        }  
	        $map['uid'] = ':uid';
	        $bind[':uid'] = I('get.uid');
	        if(!empty(I('post.password'))){
                $data['salt'] = salt();
                $data['password'] = sha1(C('DB_PREFIX').I('post.password').'_'.$data['salt']);
            }
            if(!empty(I('post.area')) or !empty(I('post.building'))){
                $data['location'] = json_encode(array(
                    'area'=>I('post.area'),
                    'building'=>I('post.building')
                    ));
            }
            if(empty($data))$data['location']=null;
	        $update = $database->where($map)->bind($bind)->data($data)->filter('strip_tags')->save();
	    	if($update){
	    		$this->success('资料修改成功');
	    	}else{
	    		$this->error('资料修改失败');
	    	} 
    	}else{
            $area = menu();
            $this->assign('area',$area);
            foreach ($area as $key => $value) {
                foreach ($value['citys'] as $k => $v) {
                    $building[] = $v;
                }
            }
            $this->assign('building',$building);
            $admin = $database->where('uid=:uid')->bind(':uid',I('get.uid'))->find();
            $admin = json_decode($admin['location'],true);
            $this->assign('admin',$admin);
    		$this->display('admin-edit-doctor');
    	}
    }       
}