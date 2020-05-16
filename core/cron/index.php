<?php 
ignore_user_abort(true);
set_time_limit(1200); // 20 mins
ob_start(); 
// if running as a cron job this makes relative incldes work...
chdir(dirname(__FILE__)); $is_cron = true;?>
<?php if(is_readable('../../Connections/aquiescedb.php')) {
		require_once('../../Connections/aquiescedb.php');  
	} else {
		die();
	}
	
	require_once(SITE_ROOT.'core/includes/framework.inc.php');
?>
<?php if(is_readable(SITE_ROOT.'products/payments/includes/logtransaction.inc.php')) require_once(SITE_ROOT.'products/payments/includes/logtransaction.inc.php'); ?>
<?php if(is_readable(SITE_ROOT.'mail/includes/reminders.inc.php')) require_once(SITE_ROOT.'mail/includes/reminders.inc.php'); ?>
<?php if(is_readable(SITE_ROOT.'core/admin/backup/includes/backup.inc.php')) require_once(SITE_ROOT.'core/admin/backup/includes/backup.inc.php'); ?>
<?php if(is_readable(SITE_ROOT.'calendar/includes/calendar.inc.php')) require_once(SITE_ROOT.'calendar/includes/calendar.inc.php'); ?>
<?php if(is_readable(SITE_ROOT.'local/includes/cron.inc.php')) require_once(SITE_ROOT.'local/includes/cron.inc.php');


/* 


DO CRON JOBS

remember path to php then full path to file, e.g.

/usr/local/bin/php /home/limecatc/public_html/purelogicol.com/core/cron/index.php

OR /usr/bin/wget http://www.example.com/core/cron/index.php
*/



if(function_exists("cleanSales")) { 
	cleanSales(); echo "Sales cleaned. ";
} 
if(function_exists("sendReminder")) { 
	sendReminder(); echo "Reminders sent. "; 
}
if(function_exists("sendCalendarReminder")) { 
	sendCalendarReminder(); echo "Calendar Reminders sent. "; 
}
if(function_exists("backupNextFileInDirectory")) {
	// backup any new filemanager files
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$select = "SELECT * FROM backupprefs";
	$result = mysql_query($select, $aquiescedb) or die(mysql_error());
	$backupprefs = mysql_fetch_assoc($result);
	if(isset($backupprefs['backupfiles']) && $backupprefs['backupfiles']==1) {
		
		$ftpserver['server'] =  $backupprefs['backupftpserver'];
		$ftpserver['username'] = $backupprefs['backupftpuser'];
		$ftpserver['password'] = decrypt($backupprefs['backupftppassword']);
		
		
		// add domain to remote backup path unless already part of set path
		$backupdirectory = "Uploads/filemanager/";
		$ftpserver['path']= strpos($backupprefs['backupftppath'],$_SERVER['HTTP_HOST'])==false ?
		$backupprefs['backupftppath'].$_SERVER['HTTP_HOST']."/".$backupdirectory : $backupprefs['backupftppath'].$backupdirectory;	
		$localpath = SITE_ROOT.$backupdirectory;		
		$fileuploaded = backupNextFileInDirectory($localpath, $ftpserver);
		if($fileuploaded) {
			echo "File backed up: ".$fileuploaded."<br>";
		} else {
			echo "No new  files found in ".$ftpserver['path']."<br>";
		}
		
		$backupdirectory = "Uploads/filemanager_thumbs/";
		$ftpserver['path']= strpos($backupprefs['backupftppath'],$_SERVER['HTTP_HOST'])==false ?
		$backupprefs['backupftppath'].$_SERVER['HTTP_HOST']."/".$backupdirectory : $backupprefs['backupftppath'].$backupdirectory;		
		$localpath = SITE_ROOT.$backupdirectory;		
		$fileuploaded = backupNextFileInDirectory($localpath, $ftpserver);
		if($fileuploaded) {
			echo "File backed up: ".$fileuploaded."<br>";
		} else {
			echo "No new  files found in ".$ftpserver['path']."<br>";
		}
	}
}


$email = (isset($_GET['email']) && strpos($_GET['email'],"@")!==false)  ? $_GET['email'] : (defined('DEBUG_EMAIL') ? DEBUG_EMAIL : "");

echo "Cron jobs completed from ".$_SERVER["REMOTE_ADDR"].". "; 
echo isset($email) ? "(Email:".$email.")" : "";

$output = ob_get_contents();
ob_flush();

if($email!="") {
	mail($email, $_SERVER['HTTP_HOST']." CRON SCRIPTS RUN", $output);
}


?>