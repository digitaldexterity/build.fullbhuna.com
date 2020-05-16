<?php // for debugging
$debug = false;
if (!isset($_SESSION)) {
  @session_start();
}

if(isset($_SESSION['debug']) || $debug) {
	error_reporting(6143); // 0 = display no errors, 6143 display all
	@ini_set("display_errors", 1); 
 	$debug = true;
}

$site_root =  str_replace("core".DIRECTORY_SEPARATOR."admin".DIRECTORY_SEPARATOR."backup".DIRECTORY_SEPARATOR."backup.php","",__FILE__); 
  /* these global variables do not work in PHP CLI  executed script */
ini_set('memory_limit', '800M' ); 
set_time_limit(1200); // 20 mins
ini_set("max_execution_time",1200);
ini_set('session.gc_maxlifetime',30);
ini_set('session.gc_probability',1);
ini_set('session.gc_divisor',1);
ignore_user_abort(true);
require_once($site_root.'Connections/aquiescedb.php');
require_once($site_root.'core/includes/framework.inc.php');
require_once($site_root.'core/includes/mysqldump.inc.php');
require_once($site_root.'core/admin/backup/includes/backup.inc.php'); 

if (!isset($_SESSION)) {
  session_start();
}

if(isset($_GET['backuptype']) && $_GET['backuptype']==1 && $_SESSION['MM_UserGroup']<9) {
	// must me logged in as manager to download

 	header("location: ../../../login/index.php?notloggedin=true"); exit;
}

if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
  if (PHP_VERSION < 6) {
    $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
  }

  $theValue = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($theValue) : mysql_escape_string($theValue);

  switch ($theType) {
    case "text":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;    
    case "long":
    case "int":
      $theValue = ($theValue != "") ? intval($theValue) : "NULL";
      break;
    case "double":
      $theValue = ($theValue != "") ? doubleval($theValue) : "NULL";
      break;
    case "date":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;
    case "defined":
      $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
      break;
  }
  return $theValue;
}
}

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsBackupPrefs = "SELECT * FROM backupprefs";
$rsBackupPrefs = mysql_query($query_rsBackupPrefs, $aquiescedb) or die(mysql_error());
$row_rsBackupPrefs = mysql_fetch_assoc($rsBackupPrefs);
$totalRows_rsBackupPrefs = mysql_num_rows($rsBackupPrefs);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLastAutoBackup = "SELECT backup.createddatetime FROM backup WHERE createdbyID = 0 ORDER BY backup.createddatetime DESC LIMIT 1";
$rsLastAutoBackup = mysql_query($query_rsLastAutoBackup, $aquiescedb) or die(mysql_error());
$row_rsLastAutoBackup = mysql_fetch_assoc($rsLastAutoBackup);
$totalRows_rsLastAutoBackup = mysql_num_rows($rsLastAutoBackup);

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);
$query_rsBackupPrefs = "SELECT backupprefs.* FROM backupprefs";
$rsBackupPrefs = mysql_query($query_rsBackupPrefs, $aquiescedb) or die(mysql_error());
$row_rsBackupPrefs = mysql_fetch_assoc($rsBackupPrefs);
$totalRows_rsBackupPrefs = mysql_num_rows($rsBackupPrefs);


session_write_close();// unlock PHP session so other functions can work on  page

$backuptype = isset($_GET['backuptype']) ? intval($_GET['backuptype']) : $row_rsBackupPrefs['backuptype'];


if(isset($_GET['backuptype']) || $row_rsBackupPrefs['autobackup'] == 1  || ($row_rsBackupPrefs['autobackup'] == 2 && $row_rsBackupPrefs['backupfrequency']>0 && strtotime($row_rsLastAutoBackup['createddatetime'])+$row_rsBackupPrefs['backupfrequency'] < strtotime(date('Y-m-d H:i:s')))  ) { // do SQL back up
	$statusID = 0; // pending
	$autobackuptype = isset($_GET['backuptype']) ? 0 : $row_rsBackupPrefs['autobackup'];
	$userID = isset($row_rsLoggedIn['ID']) ? $row_rsLoggedIn['ID'] : 0;
	$filename = date('Y-m-d_H-i')."_".$database_aquiescedb."_backup.sql";
	$insert = "INSERT INTO backup (backupfilename, autobackuptype, backupcontenttype, statusID, createdbyID, createddatetime) VALUES (".GetSQLValueString($filename,"text").",".$autobackuptype.",1,".$statusID.",".$userID.",NOW())";
	mysql_query($insert, $aquiescedb) or die(mysql_error());
	$backupID = mysql_insert_id();

			

	
	createDirectory(UPLOAD_ROOT."_backups/");
	$file = UPLOAD_ROOT."_backups/".$filename;
	
	// try first using command as much quicker	
	$command='mysqldump --opt -h' .$hostname_aquiescedb .' -u' .$username_aquiescedb .' -p' .$password_aquiescedb .' ' .$database_aquiescedb .' > ~' .$file;
	
	$result = exec($command,$output,$return_var);	

	if(!isset($output) || $return_var!=0) {
		// try using PHP 
		$result = dumpDatabase($database_aquiescedb, $file); 
		if(!$result) {
		 	die("Couldn't dump database");
		} 
	}
	if($debug) {
		echo "Dumped as ".$file;
		echo isset($return_var) ? " (".$return_var." bytes)...<br>" : "<br>";
	}
	
	if($row_rsBackupPrefs['backupzip']==1) {
		$file = zipFile($file); // sometimes fails due to memory
		if($debug) echo "Zipped as ".$file."...<br>";
	}
	
	
	if(isset($_GET['backuptype']) && $_GET['backuptype']==1) { 
	// backup now to file
	//echo "Right-click link and save here: <a href=\""; echo (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == "on") ? "https://" : "http://";  echo $_SERVER['HTTP_HOST']."/Uploads/_backups/".$filename."\">".$filename."</a>";
	//die();
		header ('Content-disposition: attachment; filename=/Uploads/_backups/'.$filename.';'); 
		header ('Content-Type: text/plain; UTF-8'); 
		header('Content-Length: ' . filesize($file));
		@ob_clean();
		flush();
		readfile($file);		
		$statusID = 1;
		unlink($file); // delete temp file
		
	} else  { // auto 
		if($debug) echo "AUTO Logged in as admin....<br>";
	if ($row_rsBackupPrefs['autobackupdestination']==2){ // Dropbox
	if($debug) echo "Dropbox<br>";

		dropboxUpload($file);
	} else { // FTP
	
	
	// open some file for reading
	$ftppass= decrypt($row_rsBackupPrefs['backupftppassword']); 
	// fall back if decrypt doesn't work
	$ftppass = strlen($ftppass)>1 ? $ftppass : $row_rsBackupPrefs['backupftppassword'];
		
	
		
			if($debug) echo "FTP put...<br>";
			
			$ret = ftp_put_file($file, $row_rsBackupPrefs['backupftpserver'] ,  $row_rsBackupPrefs['backupftpuser'], $ftppass, $row_rsBackupPrefs['backupftppath']);
			if($ret==1) {
				$msg = "FTP complete";
				$statusID = 1;	
				unlink($file); // delete temp file 		
			} else {
				$statusID = 2;
				$msg = "FTP failed";
			}
			
		
		
	}// end FTP
		
		
	} // end auto
	
	
	
	
	
	
	
	if(isset($_GET['backuptype'])) { // from backup page ?>
		<p><a href="log.php">View log</a> | <a href="index.php">Back</a></p>
	<?php } else {
		 // otherwise  probably a cron job
		 if($row_rsBackupPrefs['backupemail']!="") {
			 $server = isset($_SERVER) ? var_export($_SERVER, true) : "";
			 $to = $row_rsBackupPrefs['backupemail'];
			 $subject = $site_name." back up completed ".date('d M Y H:i');
			 $message = $site_name." back up completed ".date('d M Y H:i')."\n\n".$server;
			 
			 mail($to, $subject, $message);
		 }
	}
	$update = "UPDATE backup SET remotefilename = ".GetSQLValueString($row_rsBackupPrefs['backupftppath'].$file,"text").", statusID = ".$statusID." WHERE ID=".$backupID;
	mysql_query($update, $aquiescedb) or die(mysql_error());
} // end do SQL  back up



mysql_free_result($rsBackupPrefs);

mysql_free_result($rsLastAutoBackup);

mysql_free_result($rsLoggedIn);

exit;
?>