<?php
namespace Admin\Controller;
use Think\Controller;
class SimpleController extends Controller {
    /**
     * 空操作处理
     * @return custom or 404 page
     */
    public function _empty() {
        header("HTTP/1.1 404 Not Found");
        header("Status: 404 Not Found");
    }	

    //统计
    public function __construct(){
        parent::__construct();
        if(session('?admin')){
        $database = M('order');

        $admin = M('admin')->where('username=:username')->bind(':username',session('admin'))->find();
        $admin = json_decode($admin['location'],true);
        if(!empty($admin['area']) && !empty($admin['building'])){
            $where['area'] = array('in',$admin['area']);
            $where['building'] = array('in',$admin['building']);
            $where['_logic'] = 'or';
            $map['_complex'] = $where;
        }
        elseif(!empty($admin['area']))$map['area'] = array('in',$admin['area']);
        elseif(!empty($admin['building']))$map['building'] = array('in',$admin['building']);

        $map['status'] = 0;
        $count = $database->cache(true,60)->where($map)->count();
        $this->assign('countTodo',$count); 

        $map['status'] = 1;
        $count = $database->cache(true,60)->where($map)->count();
        $this->assign('countDoing',$count); 

        $map['status'] = 2;
        $count = $database->cache(true,60)->where($map)->count();
        $this->assign('countDone',$count);

        $map['time'] = array('gt',strtotime(date('Y-m-d')));
        $map['status'] = array('neq',-1);//隐藏已取消报修的
        $count = $database->cache(true,60)->where($map)->count();
        $this->assign('countToday',$count);                      
        }
    }
}