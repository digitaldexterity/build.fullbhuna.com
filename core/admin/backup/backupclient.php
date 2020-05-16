<?php die(); //disabled by default
require_once('../../../Connections/aquiescedb.php'); ?><?php require_once('../../includes/adminAccess.inc.php'); ?>
<?php require_once('../../includes/framework.inc.php'); ?>
<?php

ini_set('memory_limit', '80M' ); 
set_time_limit(1200); // 20 mins
ini_set("max_execution_time",1200);
ini_set('session.gc_maxlifetime',30);
ini_set('session.gc_probability',1);
ini_set('session.gc_divisor',1);
ignore_user_abort(true);



if(isset($_POST['backup'])) { // backup request posted
	$result = ftp_get_file($_POST['ftpfile'],  $_POST['ftpserver'], $_POST['ftpuser'], $_POST['ftppass'], UPLOAD_ROOT, true);
	
	// now send notification back to site
	$values = array();
	$values['backupnotifcationID'] = $_POST['backupID'];
	$url = $_POST['notifcationURL'];
	$ret = post_request($url, $values);
	
} 


if(isset($_POST['backupnotifcationID'])) {
	// recieve notification
	$update = "UPDATE backup SET statusID = 1 WHERE ID=".intval($_POST['backupnotifcationID']);
	mysql_query($update, $aquiescedb) or die(mysql_error());
}

die();
?>