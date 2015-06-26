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
    	$database = M('order');
        $count = $database->where('time>:time')->bind(':time',strtotime(date('Y-m-d')))->count();
        $this->assign('countToday',$count); 
        $count = $database->where('status=0')->count();
        $this->assign('countTodo',$count);  
        $count = $database->where('status=1')->count();
        $this->assign('countDoing',$count);   
        $count = $database->where('status=2')->count();
        $this->assign('countDone',$count);  
    }
}