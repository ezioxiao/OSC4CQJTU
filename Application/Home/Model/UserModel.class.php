<?php
namespace Home\Model;
use Think\Model;
class UserModel extends Model{

   protected $_validate = array(
     array('verify','require','验证码必须'),
     array('uid','require','用户名必须'), 
     array('password','6,20','密码长度不正确',0,'length')
   );
   
}
