<?php
namespace Home\Event;
use Think\Controller;
class OauthEvent extends Controller {
	
	public $tablename;
	public $map;
	public $bind;
	
	protected function uc($uid=''){
		require_cache(MODULE_PATH."Conf/uc.php");
		$info = M()->db(1,"mysql://".UC_DBUSER.":".UC_DBPW."@".UC_DBHOST.":3306/".UC_DBNAME)
				   ->table($this->tablename)
				   ->where($this->map)
				   ->bind($this->bind)
				   ->find();
		if(empty($info)){
			return false; 
		}else{
			return $info;
		}         		
	}
}