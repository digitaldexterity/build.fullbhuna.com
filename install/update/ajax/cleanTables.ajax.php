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





	 // clean linking tables with one item missing
	$delete = "DELETE productincategory FROM productincategory LEFT JOIN product ON productincategory.productID = product.ID LEFT JOIN productcategory ON productincategory.categoryID = productcategory.ID WHERE product.ID IS NULL OR productcategory.ID IS NULL";
	mysqli_query($fb_mysqli_con,$delete) or die(mysqli_error($fb_mysqli_con).": ".$delete);
	
	$delete = "DELETE productdetails FROM productdetails LEFT JOIN product ON productdetails.productID = product.ID  WHERE product.ID IS NULL";
	mysqli_query($fb_mysqli_con, $delete) or die(mysqli_error($fb_mysqli_con).": ".$delete);
	
	$delete = "DELETE directoryincategory FROM directoryincategory LEFT JOIN directory ON directoryincategory.directoryID = directory.ID LEFT JOIN directorycategory ON directoryincategory.directoryID = directorycategory.ID WHERE directory.ID IS NULL OR directorycategory.ID IS NULL";
	mysqli_query($fb_mysqli_con,$delete) or die(mysqli_error($fb_mysqli_con).": ".$delete);
	
	
	$delete = "DELETE locationuser FROM locationuser LEFT JOIN location ON locationuser.locationID = location.ID LEFT JOIN users ON users.ID = locationuser.userID WHERE location.ID IS NULL OR users.ID IS NULL";
	mysqli_query($fb_mysqli_con,$delete) or die(mysqli_error($fb_mysqli_con).": ".$delete);

echo "Done.<br>";



?>