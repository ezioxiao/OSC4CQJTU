<?php
namespace Admin\Controller;
use Think\Controller;
class TaskController extends SimpleController {
	public function __construct(){
		parent::__construct();
		if(!session('?admin'))$this->redirect('Main/index');
	}

    public function index(){
    	$this->redirect('Main/dashboard');
    }	

    public function todo(){
    	$database = M('order');
    	if(IS_AJAX && IS_POST){
    		$order = I('post.order');
    		if(is_array($order)){  	   				
    			foreach($order as $k=>$v){
		    		$database->status = '0';
		    		$database->donetime = '';
		    		$database->dotime = '';
					$res = $database->lock(true)->where('`order`=:order')->bind(':order',$v)->save();	
    			}
    		}else{
	    		$database->status = '0';
	    		$database->donetime = '';
	    		$database->dotime = '';   			
    			$res = $database->lock(true)->where('`order`=:order')->bind(':order',$order)->save();
    		}
    		echo $res;
    	}else{
            $admin = M('admin')->where('username=:username')->bind(':username',session('admin'))->find();
            $admin = json_decode($admin['location'],true);
            if(!empty($admin['area']) && !empty($admin['building'])){
                $where['area'] = array('in',$admin['area']);
                $this->assign('area',$admin['area']);
                if(!empty(I('get.area')))$where['area'] = array('in',I('get.area'));                
                $where['building'] = array('in',$admin['building']);
                $this->assign('building',$admin['building']);
                if(!empty(I('get.building')))$where['building'] = array('in',I('get.building'));
                $where['_logic'] = 'or';
                $map['_complex'] = $where;                
            }
            elseif(!empty($admin['area'])){
                $map['area'] = array('in',$admin['area']);
                $this->assign('area',$admin['area']);
                if(!empty(I('get.area')))$map['area'] = array('in',I('get.area'));
            }
            elseif(!empty($admin['building'])){
                $map['building'] = array('in',$admin['building']);
                $this->assign('building',$admin['building']);
                if(!empty(I('get.building')))$map['building'] = array('in',I('get.building'));
            }
            //按时间搜索
            if(!empty(I('post.startDate')) && !empty(I('post.endDate'))){
                $map['time'] = array(array('gte',strtotime(I('post.startDate'))),array('lte',strtotime(I('post.endDate'))));
            }
            elseif(!empty(I('post.startDate'))){
                $map['time'] = array('gte',strtotime(I('post.startDate')));
            }
            elseif(!empty(I('post.endDate'))){
                $map['time'] = array('lte',strtotime(I('post.endDate')));
            }
            $map['status'] = 0;
            if(!empty(I('get.emerg/d')))$map['emerg'] = I('get.emerg/d');
            if(!empty(I('get.order')))$map['order'] = array('like','%'.I('get.order').'%');

            //EXCEL导出
            if(I('get.action') == 'export'){
                $goods_list = $database->where($map)->order('time desc')->select();
                if(empty($goods_list)){
                    $this->error('没有搜索结果，无法导出数据');
                }
                $this->goods_export($goods_list);
            }

	    	$list = $database->where($map)->order('time desc')->page(I('get.p/d').',25')->select();
	    	$this->assign('list',$list);
	    	$count = $database->where($map)->count();
	    	$this->assign('count',$count);
	    	$page = pagination($count);
	    	$this->assign('page',$page);
	    	$this->display('admin-table-todo');
    	}
    }

    public function doing(){
    	$database = M('order');
    	if(IS_AJAX && IS_POST){
    		$order = I('post.order');  		
    		if(is_array($order)){
    			foreach($order as $k=>$v){
    				$database->status = '1';
    				$database->dotime = time(); 
    				$database->donetime = '';
                    $database->doctor = session('admin'); //记录操作管理员
                    if(!empty(I('post.repairer')))$database->repairer = I('post.repairer'); //记录维修工人
    				$res = $database->lock(true)->where('`order`=:order')->bind(':order',$v)->save();	
    			}
    		}else{
    			$database->status = '1';
    			$database->dotime = time(); 
    			$database->donetime = ''; 
                $database->doctor = session('admin'); //记录操作管理员 
                if(!empty(I('post.repairer')))$database->repairer = I('post.repairer'); //记录维修工人  			
    			$res = $database->lock(true)->where('`order`=:order')->bind(':order',$order)->save();
    		}
    		echo $res;
    	}else{  
            $admin = M('admin')->where('username=:username')->bind(':username',session('admin'))->find();
            $admin = json_decode($admin['location'],true);
            if(!empty($admin['area']) && !empty($admin['building'])){
                $where['area'] = array('in',$admin['area']);
                $this->assign('area',$admin['area']);
                if(!empty(I('get.area')))$where['area'] = array('in',I('get.area'));                
                $where['building'] = array('in',$admin['building']);
                $this->assign('building',$admin['building']);
                if(!empty(I('get.building')))$where['building'] = array('in',I('get.building'));
                $where['_logic'] = 'or';
                $map['_complex'] = $where;                
            }
            elseif(!empty($admin['area'])){
                $map['area'] = array('in',$admin['area']);
                $this->assign('area',$admin['area']);
                if(!empty(I('get.area')))$map['area'] = array('in',I('get.area'));
            }
            elseif(!empty($admin['building'])){
                $map['building'] = array('in',$admin['building']);
                $this->assign('building',$admin['building']);
                if(!empty(I('get.building')))$map['building'] = array('in',I('get.building'));
            }
            //按时间搜索
            if(!empty(I('post.startDate')) && !empty(I('post.endDate'))){
                $map['time'] = array(array('gte',strtotime(I('post.startDate'))),array('lte',strtotime(I('post.endDate'))));
            }
            elseif(!empty(I('post.startDate'))){
                $map['time'] = array('gte',strtotime(I('post.startDate')));
            }
            elseif(!empty(I('post.endDate'))){
                $map['time'] = array('lte',strtotime(I('post.endDate')));
            }            
            $map['status'] = 1;
            if(!empty(I('get.emerg/d')))$map['emerg'] = I('get.emerg/d');
            if(!empty(I('get.order')))$map['order'] = array('like','%'.I('get.order').'%');            

            //EXCEL导出
            if(I('get.action') == 'export'){
                $goods_list = $database->where($map)->order('dotime desc')->select();
                if(empty($goods_list)){
                    $this->error('没有搜索结果，无法导出数据');
                }
                $this->goods_export($goods_list);
            }

	    	$list = $database->where($map)->order('dotime desc')->page(I('get.p/d').',25')->select();
	    	$this->assign('list',$list);
	    	$count = $database->where($map)->count();
	    	$this->assign('count',$count);
	    	$page = pagination($count);
	    	$this->assign('page',$page);    	
	    	$this->display('admin-table-doing');
  		}
    }

    public function done(){
    	$database = M('order');
    	if(IS_AJAX && IS_POST){
    		$order = I('post.order');
    		if(is_array($order)){    			
    			foreach($order as $k=>$v){
		    		$database->status = '2';
		    		$database->donetime = time();  
                    $database->doctor = session('admin'); //记录操作管理员
                    if(!empty(I('post.repairer')))$database->repairer = I('post.repairer'); //记录维修工人                      				
    				$res = $database->lock(true)->where('`order`=:order')->bind(':order',$v)->save();	
    			}
    		}else{
	    		$database->status = '2';
	    		$database->donetime = time();  
                $database->doctor = session('admin'); //记录操作管理员
                if(!empty(I('post.repairer')))$database->repairer = I('post.repairer'); //记录维修工人                  			
    			$res = $database->lock(true)->where('`order`=:order')->bind(':order',$order)->save();
    		}
    		echo $res;
    	}else{
            $admin = M('admin')->where('username=:username')->bind(':username',session('admin'))->find();
            $admin = json_decode($admin['location'],true);
            //按区域搜索
            if(!empty($admin['area']) && !empty($admin['building'])){
                $where['area'] = array('in',$admin['area']);
                $this->assign('area',$admin['area']);
                if(!empty(I('get.area')))$where['area'] = array('in',I('get.area'));                
                $where['building'] = array('in',$admin['building']);
                $this->assign('building',$admin['building']);
                if(!empty(I('get.building')))$where['building'] = array('in',I('get.building'));
                $where['_logic'] = 'or';
                $map['_complex'] = $where;                
            }
            elseif(!empty($admin['area'])){
                $map['area'] = array('in',$admin['area']);
                $this->assign('area',$admin['area']);
                if(!empty(I('get.area')))$map['area'] = array('in',I('get.area'));
            }
            elseif(!empty($admin['building'])){
                $map['building'] = array('in',$admin['building']);
                $this->assign('building',$admin['building']);
                if(!empty(I('get.building')))$map['building'] = array('in',I('get.building'));
            }
            //按时间搜索
            if(!empty(I('post.startDate')) && !empty(I('post.endDate'))){
                $map['time'] = array(array('gte',strtotime(I('post.startDate'))),array('lte',strtotime(I('post.endDate'))));
            }
            elseif(!empty(I('post.startDate'))){
                $map['time'] = array('gte',strtotime(I('post.startDate')));
            }
            elseif(!empty(I('post.endDate'))){
                $map['time'] = array('lte',strtotime(I('post.endDate')));
            }
            $map['status'] = 2;
            if(!empty(I('get.emerg/d')))$map['emerg'] = I('get.emerg/d');
            if(!empty(I('get.order')))$map['order'] = array('like','%'.I('get.order').'%');

            //EXCEL导出
            if(I('get.action') == 'export'){
                $goods_list = $database->where($map)->order('donetime desc')->select();
                if(empty($goods_list)){
                    $this->error('没有搜索结果，无法导出数据');
                }
                $this->goods_export($goods_list);
            }

	    	$list = $database->where($map)->order('donetime desc')->page(I('get.p/d').',25')->select();
	    	$this->assign('list',$list);
	    	$count = $database->where($map)->count();
	    	$this->assign('count',$count);
	    	$page = pagination($count);
	    	$this->assign('page',$page);    	
	    	$this->display('admin-table-done');
    	}
    }     

    public function del(){
    	$database = M('order');
    	if(IS_AJAX && IS_POST){
    		if(is_array(I('post.order'))){
    			$order = implode(',', I('post.order'));
    			$res = $database->delete($order);
    		}else{
    			$res = $database->delete(I('post.order'));
    		}
    		echo $res;
    	}
    } 

    //导出数据方法
    protected function goods_export($goods_list=array())
    {
        //print_r($goods_list);exit;
        $goods_list = $goods_list;
        $data = array();
        foreach ($goods_list as $k=>$goods_info){
            $data[$k][order] = $goods_info['order'];
            $data[$k][area] = area($goods_info['area']);
            $data[$k][building] = building($goods_info['area'],$goods_info['building']);
            $data[$k][location] = $goods_info['location'];
            $data[$k][good]  = $goods_info['good'];
            $data[$k][description]  = $goods_info['description'];
            $data[$k][user]  = $goods_info['user'];
            $data[$k][time] = date('Y/m/d H:i:s',$goods_info['time']);
            $data[$k][dotime] = date('Y/m/d H:i:s',$goods_info['dotime']);
            $data[$k][donetime] = date('Y/m/d H:i:s',$goods_info['donetime']);
            $data[$k][canceltime] = date('Y/m/d H:i:s',$goods_info['canceltime']);
            $data[$k][status] = status($goods_info['status']);
            $data[$k][emerg] = $goods_info['emerg'];
            $data[$k][doctor] = $goods_info['doctor'];
            $data[$k][repairer] = $goods_info['repairer'];
        }

        //print_r($goods_list);
        //print_r($data);exit;

        foreach ($data as $field=>$v){
            if($field == 'order'){
                $headArr[]='工单号';
            }

            if($field == 'area'){
                $headArr[]='区域';
            }

            if($field == 'building'){
                $headArr[]='楼栋';
            }

            if($field == 'location'){
                $headArr[]='地点';
            }

            if($field == 'good'){
                $headArr[]='物品';
            }

            if($field == 'description'){
                $headArr[]='描述';
            }

            if($field == 'user'){
                $headArr[]='用户';
            }
            if($field == 'time'){
                $headArr[]='报修时间';
            }

            if($field == 'dotime'){
                $headArr[]='确认时间';
            }

            if($field == 'donetime'){
                $headArr[]='完成时间';
            }

            if($field == 'canceltime'){
                $headArr[]='取消时间';
            }

            if($field == 'status'){
                $headArr[]='状态';
            }

            if($field == 'emerg'){
                $headArr[]='紧急';
            } 

            if($field == 'doctor'){
                $headArr[]='管理员';
            }

            if($field == 'repairer'){
                $headArr[]='维修人员';
            } 

        }

        $filename="goods_list";

        $this->getExcel($filename,$headArr,$data);
    }

    private  function getExcel($fileName,$headArr,$data){
        //导入PHPExcel类库，因为PHPExcel没有用命名空间，只能inport导入
        import("Org.Util.PHPExcel");
        import("Org.Util.PHPExcel.Writer.Excel5");
        import("Org.Util.PHPExcel.IOFactory.php");

        $date = date("Y_m_d",time());
        $fileName .= "_{$date}.xls";

        //创建PHPExcel对象，注意，不能少了\
        $objPHPExcel = new \PHPExcel();
        $objProps = $objPHPExcel->getProperties();

        //设置表头
        $key = ord("A");
        //print_r($headArr);exit;
        foreach($headArr as $v){
            $colum = chr($key);
            $objPHPExcel->setActiveSheetIndex(0) ->setCellValue($colum.'1', $v);
            $objPHPExcel->setActiveSheetIndex(0) ->setCellValue($colum.'1', $v);
            $key += 1;
        }

        $column = 2;
        $objActSheet = $objPHPExcel->getActiveSheet();

        //print_r($data);exit;
        foreach($data as $key => $rows){ //行写入
            $span = ord("A");
            foreach($rows as $keyName=>$value){// 列写入
                $j = chr($span);
                $objActSheet->setCellValue($j.$column, $value);
                $span++;
            }
            $column++;
        }

        $fileName = iconv("utf-8", "gb2312", $fileName);
        //重命名表
        //$objPHPExcel->getActiveSheet()->setTitle('test');
        //设置活动单指数到第一个表,所以Excel打开这是第一个表
        $objPHPExcel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename=\"$fileName\"");
        header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output'); //文件通过浏览器下载
        exit;
    }    
}