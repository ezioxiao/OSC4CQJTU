<?php
namespace Home\Controller;
use Think\Controller;
class MainController extends SimpleController {
    public function index(){
    	//公告
    	$notice = M('article')->order('acid desc')->limit(5)->select();
        $this->assign('notice',$notice);
    	//统计
    	$stat['today'] = M('order')->cache(true,60)->where('time>:time')->bind(':time',strtotime(date("Y-m-d")))->count();
		$stat['todo'] = M('order')->cache(true,60)->where('status=0')->count();
    	$stat['doing'] = M('order')->cache(true,60)->where('status=1')->count();
    	$stat['done'] = M('order')->cache(true,60)->where('status=2')->count();
        $this->assign('stat',$stat);
    	//最新报修
        //$map['status'] = array('neq',-1);//是否显示已取消工单
    	$list = M('order')->where($map)->order('time desc')->limit(25)->select();
        $this->assign('list',$list);
        $this->display('main');
    }

    public function refresh(){
    	$list = M('order')->order('time desc')->limit(25)->select();
    	$html = '';
    	foreach($list as $k=>$v){
            $html .= ($v['emerg']==1)?'<tr class="am-active">':'<tr>';
            $html .= '<td>'.$v['order'].'</td>';//工单号
            $building = ($v['emerg']==1)?$v['description']:(empty($v['building'])?'-':building($v['area'],$v['building']));
            $html .= '<td class="am-show-md-up">'.$building.'</td>';//报修楼栋
            $html .= '<td class="am-show-md-up">'.$v['location'].'</td>';//报修寝室
            $html .= '<td class="am-show-md-up">'.date("Y年m月d日",$v['time']).'</td>';//报修时间
            $html .= '<td>'.status($v['status']).'</td>';//维修状态
            $html .= '<td><a href="'.U('Report/detail',array('order'=>$v['order'])).'">详细&raquo;</a></td>';
            $html .= '</tr>';
    	}
    	echo $html;
    }


}