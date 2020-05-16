<?php 
require_once('../../../Connections/aquiescedb.php');
?><?php require_once('../../includes/mysqli_connection.inc.php'); ?><?php require_once('../../includes/install.inc.php'); ?>
<?php

if(!isset($_SESSION['MM_UserGroup']) || $_SESSION['MM_UserGroup']!=10) die("Not authorised");
if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
	global $fb_mysqli_con;
  if (PHP_VERSION < 6) {
    $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
  }

  $theValue = function_exists("mysqli_real_escape_string") ? mysqli_real_escape_string($fb_mysqli_con, $theValue) : mysqli_escape_string($fb_mysqli_con, $theValue);

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





	$alltables = mysqli_query($fb_mysqli_con,"SHOW TABLES");
	// could use "SHOW TABLE STATUS WHERE Data_free / Data_length > 0.1 AND Data_free > 102400"

	while ($table = mysqli_fetch_assoc($alltables))
	{
	   foreach ($table as $db => $tablename)
	   { 
	   		$sql = "OPTIMIZE TABLE `".$tablename."`";
		   	mysqli_query($fb_mysqli_con,$sql) or die(mysqli_error($fb_mysqli_con).": ".$sql);
	
	   }
	}
	
	$sql = "SHOW INDEXES FROM product WHERE Key_name = 'title'";
	$result = mysqli_query($fb_mysqli_con,$sql) or die(mysqli_error($fb_mysqli_con).": ".$sql);
	if(mysqli_num_rows($result)==0) {
		$sql = "ALTER TABLE product
		ADD FULLTEXT INDEX `title` (`title` ASC,`description` 			ASC,`metakeywords` ASC, `sku` ASC)";
		mysqli_query($fb_mysqli_con,$sql) or die(mysqli_error($fb_mysqli_con).": ".$sql);
	}
	
	$sql = "SHOW INDEXES FROM productcategory WHERE Key_name = 'title'";
	$result = mysqli_query($fb_mysqli_con,$sql) or die(mysqli_error($fb_mysqli_con).": ".$sql);
	if(mysqli_num_rows($result)==0) {
		$sql = "ALTER TABLE productcategory
	ADD FULLTEXT INDEX `title` (`title` ASC)";
		mysqli_query($fb_mysqli_con,$sql) or die(mysqli_error($fb_mysqli_con).": ".$sql);
	}
	
	$sql = "SHOW INDEXES FROM users WHERE Key_name = 'fullname'";
	$result = mysqli_query($fb_mysqli_con,$sql) or die(mysqli_error($fb_mysqli_con).": ".$sql);
	if(mysqli_num_rows($result)==0) {
		$sql = "ALTER TABLE users
	ADD FULLTEXT INDEX `fullname` (`firstname` ASC, `middlename` ASC, `surname` ASC)";
		mysqli_query($fb_mysqli_con,$sql) or die(mysqli_error($fb_mysqli_con).": ".$sql);
	}

echo "Done.";

?>