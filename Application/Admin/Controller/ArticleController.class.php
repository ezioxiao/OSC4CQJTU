<?php
namespace Admin\Controller;
use Think\Controller;
class ArticleController extends SimpleController {
    public function index(){
    	if(!session('?admin'))$this->redirect('Main/index');
    	$database = M('article');
    	$list = $database->order('acid desc')->page(I('get.p/d').',25')->select();
    	$this->assign('list',$list);
    	$count = $database->count();
    	$this->assign('count',$count);
		$page = pagination($count);
		$this->assign('page',$page);
        $this->display('admin-table');
    }	

    public function add(){
    	if(!session('?admin'))$this->redirect('Main/index');
    	if(IS_POST){
            $database = M('article');
            if (!$database->autoCheckToken($_POST)){
                $this->error('令牌验证错误');
            }            
            $data['title'] = I('post.title');
            $data['content'] = I('post.content');
            $data['time'] = time();
            $data['view'] = 0;
            $data['author'] = session('admin');
            $add = $database->strict(true)->data($data)->add();
            if($add){
                    $this->success('添加成功',U('Article/index'));
            }else{
                    $this->error('添加失败');
            }
    	}else{
            $this->display('admin-add');
    	}
    }

    public function edit(){
    	if(!session('?admin'))$this->redirect('Main/index');
    	if(IS_POST){
            $database = M('article');
            if (!$database->autoCheckToken($_POST)){
                $this->error('令牌验证错误');
            }             
    		$data['title'] = I('post.title');
    		$data['content'] = I('post.content');
    		$update = $database->where('acid=:acid')->bind(':acid',I('post.acid'))->save($data);
    		if($update){
    			$this->success('更新成功',U('Article/index'));
    		}else{
    			$this->error('更新失败');
    		}
    	}else{
    		$article = M('article')->where('acid=:acid')->bind(':acid',I('get.acid'))->find();
    		$this->assign('article',$article);
    		$this->display('admin-edit');
    	}
    }
    
    public function ueditor(){
    	if(!session('?admin'))$this->redirect('Main/index');
        $data = new \Org\Util\Ueditor();
        echo $data->output();
    }

    public function del(){
        if(!session('?admin'))$this->redirect('Main/index');
    	$database = M('article');
    	if(IS_AJAX){
    		if(is_array(I('post.id'))){
    			$order = implode(',', I('post.id'));
    			$res = $database->delete($order);
    		}else{
    			$res = $database->delete(I('post.id'));
    		}
    		echo $res;
    	}    	
    }
}