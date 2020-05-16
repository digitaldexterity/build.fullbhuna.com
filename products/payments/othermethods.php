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

$regionID = (isset($regionID) && intval($regionID)>0) ? intval($regionID) : 1;

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsProductPrefs = "SELECT * FROM productprefs WHERE ID = ".$regionID;
$rsProductPrefs = mysql_query($query_rsProductPrefs, $aquiescedb) or die(mysql_error());
$row_rsProductPrefs = mysql_fetch_assoc($rsProductPrefs);
$totalRows_rsProductPrefs = mysql_num_rows($rsProductPrefs);

$colname_rsThisRegion = "1";
if (isset($regionID)) {
  $colname_rsThisRegion = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisRegion = sprintf("SELECT * FROM region WHERE ID = %s", GetSQLValueString($colname_rsThisRegion, "int"));
$rsThisRegion = mysql_query($query_rsThisRegion, $aquiescedb) or die(mysql_error());
$row_rsThisRegion = mysql_fetch_assoc($rsThisRegion);
$totalRows_rsThisRegion = mysql_num_rows($rsThisRegion);

$hidebasket = true;
require_once("../includes/basketcontents.inc.php");
require_once("includes/logtransaction.inc.php"); 
$status = (!isset($_SESSION['basket_grand_total']) || intval($_SESSION['basket_grand_total']) == 0) ? "COMPLETED": "INVOICE";
$paymentmenthod = (!isset($_SESSION['basket_grand_total']) || floatval($_SESSION['basket_grand_total']) == 0) ? "NONE": strtoupper($_GET['paymentmethod']);
$strVendorTxCode = logtransaction("",strtoupper($paymentmenthod ),$status);
$url = isset($row_rsProductPrefs['successURL']) ? $row_rsProductPrefs['successURL'] : "success.php";
header("location: ".$url."?paymentmethod=".$paymentmenthod ."&VendorTxCode=".$strVendorTxCode); exit; ?>