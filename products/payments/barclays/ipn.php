<?php require_once('../../../Connections/aquiescedb.php'); ?><?php require_once('../../../core/includes/framework.inc.php'); ?>
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

$regionID = isset($regionID) ? $regionID : 1;

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsProductPrefs = "SELECT shopstatus, successURL FROM productprefs WHERE ID =".intval($regionID) . "";
$rsProductPrefs = mysql_query($query_rsProductPrefs, $aquiescedb) or die(mysql_error());
$row_rsProductPrefs = mysql_fetch_assoc($rsProductPrefs);
$totalRows_rsProductPrefs = mysql_num_rows($rsProductPrefs); 
?><?php require_once('../includes/logtransaction.inc.php'); ?><?php 


$status = ($_POST['STATUS'] =="5" || $_POST['STATUS'] =="9") ? "SUCCESS" : "CANCELLED"; 
// 5 - authorised - various others effectively cancelled, except
//9 - Payment requested

//https://payment-services.ingenico.com/int/en/ogone/support/guides/user%20guides/statuses-and-errors/statuses

$txid = isset($_POST['orderID']) ? $_POST['orderID'] : "TEST";
$amounpaid = isset($_POST['amount']) ? $_POST['amount'] : ""; 
logtransaction($txid,"",strtoupper($status),0,"","",0,$amounpaid);

$request = var_export($_REQUEST, true);

if(defined("DEBUG_EMAIL")) {
	mail(DEBUG_EMAIL, "Barclays IPN", $request);
}

$protocol = getProtocol()."://";

$url = isset($row_rsProductPrefs['successURL']) ? $row_rsProductPrefs['successURL'] : $protocol.$_SERVER['HTTP_HOST']."/products/payments/success.php";


?><html><head><meta http-equiv="refresh" content="0;url=<?php echo $url; ?>?paymentmethod=BARCLAYS&VendorTxCode=<?php echo $txid; ?>" /></head></html>
<?php /*

set up in Configutration > Transaction Feedback

Direct HTTP server-to-server request


*/




?>