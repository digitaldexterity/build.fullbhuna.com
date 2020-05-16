<?php require_once('../../../Connections/aquiescedb.php'); ?><?php require_once('../../../core/includes/adminAccess.inc.php'); ?>
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

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsDirectory = "SELECT directory.*, directorycategory.`description` AS category FROM directory LEFT JOIN directorycategory ON (directory.categoryID = directorycategory.ID) WHERE directory.statusID = 1 ORDER BY name ASC";
$rsDirectory = mysql_query($query_rsDirectory, $aquiescedb) or die(mysql_error());
$row_rsDirectory = mysql_fetch_assoc($rsDirectory);
$totalRows_rsDirectory = mysql_num_rows($rsDirectory);$varRegionID_rsDirectory = "0";
if (isset($_GET['regionID'])) {
  $varRegionID_rsDirectory = $_GET['regionID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsDirectory = sprintf("SELECT directory.*, directorycategory.`description` AS category FROM directory LEFT JOIN directorycategory ON (directory.categoryID = directorycategory.ID) WHERE directory.statusID = 1 AND  (directorycategory.regionID = %s OR %s = 0) ORDER BY name ASC", GetSQLValueString($varRegionID_rsDirectory, "int"),GetSQLValueString($varRegionID_rsDirectory, "int"));
$rsDirectory = mysql_query($query_rsDirectory, $aquiescedb) or die(mysql_error());
$row_rsDirectory = mysql_fetch_assoc($rsDirectory);
$totalRows_rsDirectory = mysql_num_rows($rsDirectory);

header ('Content-disposition: attachment; filename=directory-'.date('d-m-Y').'.csv;'); 
header("Content-type: application/octet-stream");
 print "name,description,address1,address2,address3,address4,address5,postcode,telephone,fax,mobile,email,url,category\n";

 do {  print "\"".$row_rsDirectory['name']."\",\"".$row_rsDirectory['description']."\",\"".$row_rsDirectory['address1']."\",\"".$row_rsDirectory['address2']."\",\"".$row_rsDirectory['address3']."\",\"".$row_rsDirectory['address4']."\",\"".$row_rsDirectory['address5']."\",\"".$row_rsDirectory['postcode']."\",\"".$row_rsDirectory['telephone']."\",\"".$row_rsDirectory['fax']."\",\"".$row_rsDirectory['mobile']."\",\"".$row_rsDirectory['email']."\",\"".$row_rsDirectory['url']."\",\"".$row_rsDirectory['category']."\"\n"; } while ($row_rsDirectory = mysql_fetch_assoc($rsDirectory));

mysql_free_result($rsDirectory);
?>
