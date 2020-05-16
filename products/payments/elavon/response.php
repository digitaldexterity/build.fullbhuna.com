<?php 
/* https://developer.elavonpaymentgateway.com/#!/hpp */

/*

array (
 'RESULT' => '00',
 'AUTHCODE' => '12345',
 'MESSAGE' => '[ test system ] AUTHORISED',
 'PASREF' => '15448117512428',
 'AVSPOSTCODERESULT' => 'M',
 'AVSADDRESSRESULT' => 'M',
 'CVNRESULT' => 'M',
 'ACCOUNT' => 'internet',
 'MERCHANT_ID' => 'allaig',
 'ORDER_ID' => 'BAYLTAMO-181214182153',
 'TIMESTAMP' => '20181214182153',
 'AMOUNT' => '5900',
 'MERCHANT_RESPONSE_URL' => 'https://www.purelogicol.com/products/payments/elavon/ipn.php',
 'pas_uuid' => 'cc38da80-5332-476d-bf7b-b330168f0579',
 'SHA1HASH' => '482ded367d10de5c12a4904b6d3365a26f1d8128',
 'HPP_FRAUDFILTER_RESULT' => 'NOT_EXECUTED',
 'BATCHID' => '-1',
)
*/
?>
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
?>
<?php require_once('../includes/logtransaction.inc.php'); ?><?php 

if(isset($_POST['RESULT'])) {
$status = ($_POST['RESULT'] =="00") ? "SUCCESS" : "CANCELLED"; 
// 00 - authorised - various others effectively cancelled, except


$txid = isset($_POST['ORDER_ID']) ? $_POST['ORDER_ID'] : "TEST";
$amounpaid = isset($_POST['amount']) ? number_format(($_POST['amount']/100),2,".","") : ""; 
logtransaction($txid,"",strtoupper($status),0,"","",0,$amounpaid);

$protocol = getProtocol()."://";

$successURL = isset($row_rsProductPrefs['successURL']) ? $row_rsProductPrefs['successURL'] : $protocol.$_SERVER['HTTP_HOST']."/products/payments/success.php";
$cancelURL = isset($row_rsProductPrefs['failURL']) ? $row_rsProductPrefs['failURL'] : $protocol.$_SERVER['HTTP_HOST']."/products/payments/unsuccessful.php";

$url = ($status=="SUCCESS") ? $successURL : $cancelURL;

?>
<html><head><meta http-equiv="refresh" content="0;url=<?php echo $url; ?>?paymentmethod=ELAVON&VendorTxCode=<?php echo $txid; ?>" /></head></html>
<?php } ?>