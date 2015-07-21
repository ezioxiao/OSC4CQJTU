<?php
error_reporting(0);
if(file_exists('./Application/install.lock')){
	header('location: ./index.php');
	exit;	
}
include './Application/Common/Common/function.php';
?>
<!doctype html>
<html class="no-js">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>安装向导 - 重庆交通大学后勤在线服务系统</title>
  <meta name="description" content="安装向导,重庆交通大学后勤在线服务系统">
  <meta name="keywords" content="index">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
  <meta name="renderer" content="webkit">
  <meta http-equiv="Cache-Control" content="no-siteapp" />
  <link rel="icon" type="image/png" href="PUBLIC/assets/i/favicon.png">
  <link rel="apple-touch-icon-precomposed" href="PUBLIC/assets/i/app-icon72x72@2x.png">
  <meta name="apple-mobile-web-app-title" content="Amaze UI" />
  <link rel="stylesheet" href="PUBLIC/assets/css/amazeui.min.css"/>
</head>
<body>
<!--[if lte IE 9]>
<p class="browsehappy">你正在使用<strong>过时</strong>的浏览器，Amaze UI 暂不支持。 请 <a href="http://browsehappy.com/" target="_blank">升级浏览器</a>
  以获得更好的体验！</p>
<![endif]--> 
<!--[if (gte IE 9)|!(IE)]><!-->
<script src="PUBLIC/assets/js/jquery.min.js"></script>
<script src="PUBLIC/assets/js/amazeui.min.js"></script>
<!--<![endif]-->	
<body>
<style>
.am-container{
	margin-top:100px;
}
</style>
<div class="am-container">
	<?php if(empty($_GET['step'])): ?>
	<div class="am-panel am-panel-default">
	  <div class="am-panel-bd">
	    <h1 class="am-text-center">Online Service Center Installer <small>for CQJTU</small></h1>
	    <hr>
	    <p class="am-text-center">欢迎您使用<strong>重庆交通大学</strong>后勤在线服务系统，这是一个程序安装向导，将帮助您配置相关信息。</p>
	    <ul>
	    <?php 
	    $success='<span class="am-text-success">是</span>';
	    $error='<span class="am-text-danger">否</span>';
	    ?>
	    <li>操作系统：<?=php_uname()?></li>
	    <li>运行方式：<?=PHP_SAPI?></li>
	    <li>PHP版本：<?=PHP_VERSION?></li>
	    <li>MYSQL支持：<?=function_exists('mysql_connect')?$success:$error?></li>
	    <li>PDO支持：<?=extension_loaded('pdo')?$success:$error?></li>
	    <li>PDO_MYSQL支持：<?=extension_loaded('pdo_mysql')?$success:$error?></li>
	    <li>SSL（HTTPS）支持：<?=extension_loaded('openssl')?$success:$error?></li>
	    <li>GD库支持：<?=extension_loaded('gd')?$success:$error?></li>
	    <li>文件上传支持：<?=ini_get('file_uploads')?$success:$error?></li>
	    <li>配置文件写入支持：<?=is_writeable('./Application/Common/Conf/config.php')?$success:$error?></li>
	    <li>上传目录写入支持：<?=is_writeable('./Uploads')?$success:$error?></li>
	    </ul>
	    <p class="am-text-center">以上检测配置通过后即可进行下一步</p>	    
	    <hr>
	    <button data-am-loading="{spinner: 'circle-o-notch'}" class="am-btn am-btn-primary am-round am-center" onclick="window.location.href='?step=1'">马上开始&raquo;</buttom>
	  </div>
	</div>
	<?php elseif($_GET['step']==1): ?>
	<?php
	if($_POST){
		$db = $_POST['db'];
		$pieces = explode(':', $db['server']);
		$db['server'] = $pieces[0];
		$db['port'] = !empty($pieces[1]) ? $pieces[1] : '3306';		
		$db['prefix'] = preg_match('/_$/',$db['prefix'])?$db['prefix']:$db['prefix'].'_';
		$link = mysql_connect($db['server'].':'.$db['port'], $db['username'], $db['password']);
		if(empty($link)) {
			$error = mysql_error();
			if (strpos($error, 'Access denied for user') !== false) {
				$error = '您的数据库访问用户名或是密码错误！';
			} else {
				$error = iconv('gbk', 'utf8', $error);
			}
			showMsg($error);
		} else {
			$query = mysql_query("SHOW DATABASES LIKE  '{$db['name']}';");
			if (!mysql_fetch_assoc($query)) {
				if(mysql_get_server_info() > '4.1') {
					mysql_query("CREATE DATABASE IF NOT EXISTS `{$db['name']}` DEFAULT CHARACTER SET utf8", $link);
				} else {
					mysql_query("CREATE DATABASE IF NOT EXISTS `{$db['name']}`", $link);
				}
			}
			$query = mysql_query("SHOW DATABASES LIKE  '{$db['name']}';");
			if (!mysql_fetch_assoc($query)) {
				showMsg("数据库不存在且创建数据库失败！");
			}
			if(mysql_errno()) {
				showMsg(mysql_error());
			}
			if(empty($error)) {
				mysql_select_db($db['name']);
				$query = mysql_query("SHOW TABLES LIKE '{$db['prefix']}%';");
				if (mysql_fetch_assoc($query)) {
					showMsg('您的数据库不为空，请重新建立数据库或是清空该数据库或更改表前缀！');
				}
			}
			$config = include './Application/Common/Conf/config.php';		
			$conf = array(
			    'DB_TYPE'               =>  'mysql',
			    'DB_HOST'               =>  $db['server'],
			    'DB_USER'               =>  $db['username'],
			    'DB_PWD'                =>  $db['password'],
			    'DB_NAME'               =>  $db['name'],
			    'DB_PORT'               =>  $db['port'],
			    'DB_PREFIX'             =>  $db['prefix'],
			    'DB_CHARSET'            =>  'utf8',	
				);
			$c = array_merge($config,$conf);
			$settingstr = "<?php \n return array(\n";
			foreach($c as $key=>$v){
				if($i == count($c)-1){
					if(is_array($v)){
						$settingstr .= "\t'".$key."'=>array('".implode("','",$v)."')";
					}else{
						$settingstr .= "\t'".$key."'=>'".$v."'";
					}
				}else{
					if(is_array($v)){
						$settingstr .= "\t'".$key."'=>array('".implode("','",$v)."'),\n";
					}else{
						$settingstr .= "\t'".$key."'=>'".$v."',\n";
					}					
				}	
				$i++;
			}
			$settingstr .= "\n);\n?>";	
			file_put_contents('./Application/Common/Conf/config.php',$settingstr);
			header('location: ./install.php?step=2');		
		}		
		die;
	}
	?>	
	<div class="am-panel am-panel-default">
	  <div class="am-panel-bd">
	    <h1 class="am-text-center">Online Service Center Installer <small>for CQJTU</small></h1>
	    <hr>
	    <p class="am-text-center">数据库相关配置</p>
		<form method="post" class="am-form am-form-horizontal" data-am-validator><fieldset>
		  <div class="am-form-group">
		    <label for="db[server]" class="am-u-sm-2 am-form-label">数据库主机</label>
		    <div class="am-u-sm-10">
		      <input type="text" id="db[server]" value="127.0.0.1:3306" placeholder="输入你的数据库主机" name="db[server]" requierd>
		    </div>
		  </div>

		  <div class="am-form-group">
		    <label for="db[username]" class="am-u-sm-2 am-form-label">数据库用户</label>
		    <div class="am-u-sm-10">
		      <input type="text" id="db[username]" name="db[username]" placeholder="输入你的数据库用户" value="root" required>
		    </div>
		  </div>

		  <div class="am-form-group">
		    <label for="db[password]" class="am-u-sm-2 am-form-label">数据库密码</label>
		    <div class="am-u-sm-10">
		      <input type="password" id="db[password]" name="db[password]" placeholder="输入你的数据库密码" required>
		    </div>
		  </div>

		  <div class="am-form-group">
		    <label for="db[prefix]" class="am-u-sm-2 am-form-label">数据表前缀</label>
		    <div class="am-u-sm-10">
		      <input type="text" id="db[prefix]" name="db[prefix]" placeholder="输入你的数据表前缀" value="osc_" required>
		    </div>
		  </div>

		  <div class="am-form-group">
		    <label for="db[name]" class="am-u-sm-2 am-form-label">数据库名称</label>
		    <div class="am-u-sm-10">
		      <input type="text" id="db[name]" name="db[name]" placeholder="输入你的数据库名称" required>
		    </div>
		  </div>
	    <hr>
	    <button data-am-loading="{spinner: 'circle-o-notch'}" type="submit" class="am-btn am-btn-primary am-round am-center">下一步&raquo;</buttom>
	    </fieldset></form>
	  </div>
	</div>
	<?php elseif($_GET['step']==2): ?>
	<?php
	if($_POST){
		$config = include './Application/Common/Conf/config.php';
		$user = $_POST['user'];
		$salt = salt();	
		$password = sha1($config['DB_PREFIX'].$user['password'].'_'.$salt);
		$link = mysql_connect($config['DB_HOST'].':'.$config['DB_PORT'], $config['DB_USER'], $config['DB_PWD']);
		mysql_select_db($config['DB_NAME']);
		mysql_query("SET character_set_connection=utf8, character_set_results=utf8, character_set_client=binary");
		mysql_query("SET sql_mode=''");		
		$sql[]='CREATE TABLE `osc_admin` (`uid` int(11) unsigned NOT NULL AUTO_INCREMENT,`username` varchar(25) NOT NULL,`password` varchar(55) NOT NULL,`salt` varchar(25) NOT NULL,`lastip` varchar(25) DEFAULT NULL,`lasttime` int(11) DEFAULT NULL,`right` int(1) DEFAULT \'0\',`location` varchar(255) DEFAULT NULL,PRIMARY KEY (`uid`)) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;';
		$sql[]='CREATE TABLE `osc_article` (`acid` int(11) unsigned NOT NULL AUTO_INCREMENT,`title` varchar(55) NOT NULL,`content` text NOT NULL,`time` int(11) NOT NULL,`author` varchar(20) NOT NULL,`view` int(11) DEFAULT \'0\',PRIMARY KEY (`acid`)) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;';
		$sql[]='CREATE TABLE `osc_order` (`order` varchar(25) NOT NULL,`area` int(11) unsigned NOT NULL,`building` int(11) unsigned DEFAULT NULL,`location` varchar(25) NOT NULL,`good` varchar(25) DEFAULT NULL,`description` varchar(255) NOT NULL,`user` varchar(25) NOT NULL,`time` int(11) unsigned NOT NULL,`dotime` int(11) unsigned DEFAULT NULL,`donetime` int(11) unsigned DEFAULT NULL,`canceltime` int(11) unsigned DEFAULT NULL,`status` int(11) DEFAULT \'0\',`emerg` int(11) NOT NULL DEFAULT \'0\',`doctor` varchar(25) DEFAULT NULL,`repairer` varchar(25) DEFAULT NULL,PRIMARY KEY (`order`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;';
		$sql[]='CREATE TABLE `osc_setting` (`key` varchar(25) NOT NULL,`value` text NOT NULL,PRIMARY KEY (`key`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;';
		$sql[]='CREATE TABLE `osc_user` (`uid` varchar(25) NOT NULL,`username` varchar(25) DEFAULT NULL,`password` varchar(55) DEFAULT NULL,`area` int(11) DEFAULT NULL,`building` int(11) DEFAULT NULL,`location` varchar(25) DEFAULT NULL,`tel` varchar(25) DEFAULT NULL,`lastip` varchar(25) DEFAULT NULL,`lasttime` int(11) DEFAULT NULL,`salt` varchar(25) DEFAULT NULL,PRIMARY KEY (`uid`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;';
		foreach($sql as $k=>$v){
			$v = str_replace('osc_', $config['DB_PREFIX'], $v);
			mysql_query($v);
		}
		mysql_query("INSERT INTO `{$config['DB_PREFIX']}admin` (`username`, `password`, `salt`, `right`) VALUES('{$user['username']}', '{$password}', '{$salt}', '1')");
		//默认数据
		mysql_query("INSERT INTO `{$config['DB_PREFIX']}setting` VALUES ('area', '[\"\\\\u5357\\\\u5cb8\\\\u6821\\\\u533a\",\"\\\\u53cc\\\\u798f\\\\u6821\\\\u533a\"]');");
		mysql_query("INSERT INTO `{$config['DB_PREFIX']}setting` VALUES ('building', '[{\"28\":{\"name\":\"\\\\u5357\\\\u5cb8\\\\u5b66\\\\u751f\\\\u5bbf\\\\u820d\",\"key\":\"11\"},\"29\":{\"name\":\"\\\\u5357\\\\u5cb8\\\\u6559\\\\u5ba4\",\"key\":\"12\"},\"30\":{\"name\":\"\\\\u5357\\\\u5cb8\\\\u529e\\\\u516c\\\\u697c\",\"key\":\"13\"},\"31\":{\"name\":\"\\\\u5357\\\\u5cb8\\\\u5916\\\\u73af\\\\u5883\",\"key\":\"14\"},\"32\":{\"name\":\"\\\\u5357\\\\u5cb8\\\\u5176\\\\u4ed6\",\"key\":\"15\"}},{\"13\":{\"name\":\"\\\\u53cc\\\\u798f\\\\u5b66\\\\u751f\\\\u5bbf\\\\u820d\",\"key\":\"21\"},\"14\":{\"name\":\"\\\\u53cc\\\\u798f\\\\u6559\\\\u5ba4\",\"key\":\"22\"},\"15\":{\"name\":\"\\\\u53cc\\\\u798f\\\\u529e\\\\u516c\\\\u697c\",\"key\":\"23\"},\"16\":{\"name\":\"\\\\u53cc\\\\u798f\\\\u5916\\\\u73af\\\\u5883\",\"key\":\"24\"},\"17\":{\"name\":\"\\\\u53cc\\\\u798f\\\\u5176\\\\u4ed6\",\"key\":\"25\"}}]');");
		mysql_query("INSERT INTO `{$config['DB_PREFIX']}setting` VALUES ('copyright', '{\"buttom\":\"\",\"aboutus\":\"\",\"link\":\"\"}');");
		mysql_query("INSERT INTO `{$config['DB_PREFIX']}setting` VALUES ('global', '{\"isopen\":\"true\",\"allowregister\":\"false\",\"quickreport\":\"true\"}');");
		mysql_query("INSERT INTO `{$config['DB_PREFIX']}setting` VALUES ('tips', '{\"login\":\"\",\"register\":\"\",\"report\":\"\",\"emerg\":\"\",\"detail\":\"\"}');");
		touch('./Application/install.lock');
		header('location: ./index.php');
	}
	?>
	<div class="am-panel am-panel-default">
	  <div class="am-panel-bd">
	    <h1 class="am-text-center">Online Service Center Installer <small>for CQJTU</small></h1>
	    <hr>
	    <p class="am-text-center">后台管理相关配置</p>
		<form method="post" class="am-form am-form-horizontal" data-am-validator><fieldset>
		  <div class="am-form-group">
		    <label for="user[username]" class="am-u-sm-2 am-form-label">管理员账号</label>
		    <div class="am-u-sm-10">
		      <input type="text" id="user[username]" name="user[username]" placeholder="输入你的管理员账号" maxlength="25" required>
		    </div>
		  </div>

		  <div class="am-form-group">
		    <label for="user[password]" class="am-u-sm-2 am-form-label">管理员密码</label>
		    <div class="am-u-sm-10">
		      <input type="password" id="user[password]" name="user[password]" placeholder="输入你的管理员密码6-20位" minlength="6" maxlength="20" required>
		    </div>
		  </div>		  
	    <hr>
	    <button data-am-loading="{spinner: 'circle-o-notch'}" type="submit" class="am-btn am-btn-primary am-round am-center">下一步&raquo;</buttom>
	    </fieldset></form>
	  </div>
	</div>
	<?php endif;?>	
</div>
<div data-am-widget="gotop" class="am-gotop am-gotop-fixed">
  <a href="#top" title="回到顶部">
    <span class="am-gotop-title">回到顶部</span>
    <i class="am-gotop-icon am-icon-chevron-up"></i>
  </a>
</div>

<footer data-am-widget="footer" class="am-footer am-footer-default" data-am-footer="{  }">
  <div class="am-footer-miscs ">
    <p>由
      <a href="//ty.cqjtu.edu.cn" title="天佑工作室" target="_blank" class="">天佑工作室 </a>提供技术支持</p>
    <p>CopyRight©2015 CQJTU</p>
    <p>渝ICP备11007697号-1</p>
  </div>
</footer>

<!--[if lt IE 9]>
<script src="http://libs.baidu.com/jquery/1.11.1/jquery.min.js"></script>
<script src="http://cdn.staticfile.org/modernizr/2.8.3/modernizr.js"></script>
<script src="PUBLIC/assets/js/polyfill/rem.min.js"></script>
<script src="PUBLIC/assets/js/polyfill/respond.min.js"></script>
<script src="PUBLIC/assets/js/amazeui.legacy.js"></script>
<![endif]-->
<script>
$.AMUI.progress.start();
$(window).load(function(){
  $.AMUI.progress.done();
});
$('button').click(function () {
  var $btn = $(this);
  $btn.button('loading');
    setTimeout(function(){
      $btn.button('reset');
  }, 5000);
});
</script>
<script type="text/javascript">
$(function() {
	$(".am-topbar-nav").find("a[href='{:U()}']").parent().addClass('am-active');
});
</script>
</body>
</html>
<?php
	function showMsg($error=''){
		die('<div class="am-panel am-panel-default"><div class="am-panel-bd">'.$error.'<a href="javascript:history.go(-1);">点击返回重试</a></div></div>');		
	}
?>