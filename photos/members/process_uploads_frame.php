<?php require_once('../../Connections/aquiescedb.php'); ?>
<?php 
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

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);
?>
<?php
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsMediaPrefs = "SELECT * FROM mediaprefs";
$rsMediaPrefs = mysql_query($query_rsMediaPrefs, $aquiescedb) or die(mysql_error());
$row_rsMediaPrefs = mysql_fetch_assoc($rsMediaPrefs);
$totalRows_rsMediaPrefs = mysql_num_rows($rsMediaPrefs);
 
require_once('../../includes/upload.inc.php'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Process image frame</title>
<link href="/local/css/styles.css" rel="stylesheet"  />
<script src="/core/scripts/ajax.js"></script>
<script src="/core/scripts/common.js"></script>
</head>
<body>

    <?php 
	$active = ($row_rsMediaPrefs['uploadapprove']==1) ? 0 : 1 ; // approval needed on photos
	
	$hot_directory = SITE_ROOT."Uploads".DIRECTORY_SEPARATOR."_ftpupload".DIRECTORY_SEPARATOR."users".DIRECTORY_SEPARATOR.$_SESSION['MM_Username'].DIRECTORY_SEPARATOR; 
	if(is_dir($hot_directory)) { // directory exists
	$dh = opendir($hot_directory);
	$images = 0;
	while($filename = readdir($dh))
	{
		if(is_file($hot_directory.$filename)) { // is file
			if(preg_match("/(.jpg|.jpeg|.png|.gif)/i",$filename)) { // is image
				$images ++; $_SESSION['images_processed']++ ;
				$pathinfo = pathinfo($filename);
				$filetype = $pathinfo['extension'];
				$title = explode(".",$pathinfo['basename']);
				$title[0] = str_replace("_"," ",$title[0]);
				$newfilename = time()."_".str_replace(" ", "_" , $filename);
				copy($hot_directory.$filename,$uploaddir.$newfilename);
				set_time_limit(60); // reset time limit counter to 1 minutes before each resize
				createImageSizes($uploaddir,$newfilename,$filetype);
				$insert = "INSERT INTO photos (title, imageURL, userID, createddatetime, categoryID, active) VALUES (".GetSQLValueString($title[0],"text").",".GetSQLValueString($newfilename,"text").",".$row_rsLoggedIn['ID'].",NOW(),".GetSQLValueString($_GET['categoryID'],"int").",".$active.")";
				mysql_select_db($database_aquiescedb, $aquiescedb);
  				$result = mysql_query($insert, $aquiescedb) or die(mysql_error());
				if(unlink($hot_directory.$filename)) { // try to delete with usual method
				echo "Processed: ".$filename."<br />";
				} else {  // try to delete with FTP as sometimes this is the only way to delete FTP uploaded files
				$conn_id = ftp_connect("localhost");
				// login with username and password
				$login_result = ftp_login($conn_id, FTP_USERNAME, FTP_PASSWORD);
				$ftp_dir = FTP_PATH."users/".$_SESSION['MM_Username']."/"; 
				// try to delete 
				if (ftp_delete($conn_id, $ftp_dir.$filename)) {
 				echo "Processed: ".$filename." (FTP)<br />";
				} else {
 				echo "Failed: ".$filename."<br />";
				}				
				}				
				if($images == 10) { // finish and reload page after 10 images
				echo "<script language = \"javascript\">window.location.href=\"process_uploads_frame.php?".$_SERVER['QUERY_STRING']."\"</script><p>Reload page</p>"; exit;
				} // end reload
			} // end is iamge
		
		} // end is file
	}// directory loop
	echo "<p>Processing complete.</p>";
	if($_SESSION['images_processed']>0) {
		echo "<p>".$_SESSION['images_processed']." images processed.</p>";
		unset($_SESSION['images_processed']);
		} else {
		echo "<p>No images found.</p>";
		}
	} else { die("ERROR: The directory '".$hot_directory."' does not exist."); }
	?>
<p><a href="../index.php?galleryID=<?php echo intval($_GET['categoryID']); ?>" target="_top" class="link_forward">Go to gallery...</a></p>
<script>
window.parent.document.getElementById('working').style.visibility = 'hidden';
</script>
</body>
</html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsMediaPrefs);
?>
