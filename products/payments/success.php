<?php require_once('../../Connections/aquiescedb.php'); ?><?php  ?><?php require_once('includes/logtransaction.inc.php'); ?>
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
?>
<?php

$regionID = (isset($regionID) && $regionID>0) ? intval($regionID) : 1;


mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT * FROM preferences WHERE ID = ".intval($regionID)." LIMIT 1";
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);

$colname_rsThisRegion = "1";
if (isset($regionID)) {
  $colname_rsThisRegion = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisRegion = sprintf("SELECT * FROM region WHERE ID = %s", GetSQLValueString($colname_rsThisRegion, "int"));
$rsThisRegion = mysql_query($query_rsThisRegion, $aquiescedb) or die(mysql_error());
$row_rsThisRegion = mysql_fetch_assoc($rsThisRegion);
$totalRows_rsThisRegion = mysql_num_rows($rsThisRegion);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsProductPrefs = "SELECT * FROM productprefs WHERE ID = ".intval($regionID)." LIMIT 1";
$rsProductPrefs = mysql_query($query_rsProductPrefs, $aquiescedb) or die(mysql_error());
$row_rsProductPrefs = mysql_fetch_assoc($rsProductPrefs);
$totalRows_rsProductPrefs = mysql_num_rows($rsProductPrefs);

$colname_rsThisOrder = "-1";
if (isset($_GET['VendorTxCode'])) {
  $colname_rsThisOrder = $_GET['VendorTxCode'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisOrder = sprintf("SELECT productorders.*, countries.iso2 FROM productorders LEFT JOIN  countries ON (productorders.deliverycountryID = countries.ID) WHERE VendorTxCode = %s", GetSQLValueString($colname_rsThisOrder, "text"));
$rsThisOrder = mysql_query($query_rsThisOrder, $aquiescedb) or die(mysql_error());
$row_rsThisOrder = mysql_fetch_assoc($rsThisOrder);
$totalRows_rsThisOrder = mysql_num_rows($rsThisOrder);

$strVendorTxCode = isset($_GET['VendorTxCode']) ? $_GET['VendorTxCode'] : "";


if ($_REQUEST['crypt']) {
	define("SAGEPAY_CRYPT_PASSWORD",$row_rsProductPrefs['paymentclientpassword']);
	require_once('sagepay3/lib/sagepay3.php');
	$sagePay = new SagePay();
	$responseArray = $sagePay -> decode($_REQUEST['crypt']);
	//Check status of response
	
	
	
	if($responseArray["Status"] === "OK"){
		// Success
		$status = "AUTHORISED";
	}elseif($responseArray["Status"] === "ABORT"){
		// Payment Cancelled
		$status = "CANCELLED";
	}else{
		// Payment Failed
		$status = "FAILED";
		//throw new \Exception($responseArray["StatusDetail"]);
	}
	logtransaction($responseArray['VendorTxCode'],"",strtoupper($status),0,"","",0,$responseArray['Amount']);
}

// cancel promo code
if(isset($_SESSION['promocode'])) {
		$update = "UPDATE productpromocode SET statusID = 0 WHERE promocode = ".GetSQLValueString($_SESSION['promocode'], "text")."";
		mysql_query($update, $aquiescedb) or die(mysql_error());
}
		
if(isset($row_rsPreferences['googleanalyticsecommerce']) && $row_rsPreferences['googleanalyticsecommerce']==2) $track_ecommerce = true; $postpay=true;require_once('../../core/seo/includes/trackerpostpay.inc.php'); ?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!DOCTYPE html>
<html class="" lang="en"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php  $pageTitle = "Payment Successful"; echo $pageTitle." | ".$site_name;?></title>
<!-- InstanceEndEditable -->
<!--[if IE]><![endif]-->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<style>
<!--

-->
</style>
<link href="../css/defaultProducts.css" rel="stylesheet" >
<!-- InstanceEndEditable -->
</head>
<body id="OffTemplate" class="bootstrap <?php echo $body_class;  ?>">
<?php require_once('../../local/includes/header.inc.php'); ?>
<main id="content"><!-- InstanceBeginEditable name="Body" --><?php require_once('../../core/seo/includes/googletagmanager.inc.php'); ?>
 <section>
      <div  class="checkout checkout-summary container">
        <?php if(isset($row_rsProductPrefs['successpage']) && trim($row_rsProductPrefs['successpage']) !="") { 
		echo $row_rsProductPrefs['successpage'];
		} else { ?>
      <h1>Transaction Successful</h1>
      <p>Your purchase has been processed. We will send a confirmation email once your goods are dispatched (always check your junk folder too).</p>
      <p>You can <a href="javascript:window.print();">print this page</a> for your records.</p><?php } ?>
      <?php if($row_rsPreferences['userscanlogin']==1) { ?>
      <p>Your profile is saved for your next visit. When you return just log in with the details you receive via email.</p>
 <?php }  ?>
       <?php if(isset($_SESSION['basket'])) { ?>
       <h2><?php echo $row_rsProductPrefs['text_yourreceipt']; ?></h2>
      <?php 
	   // show basket one more time...
	   
	    require_once('../includes/basketcontents.inc.php');?>
        <?php if(isset($_GET['paymentsuccess'])) { ?>
        <p><?php echo $row_rsProductPrefs['text_paymentreceived']; ?></p>
        <?php } else { ?>
        <p><?php echo $row_rsProductPrefs['text_paymentpending']; ?></p>
        <?php } ?>
        <h3><?php echo $row_rsProductPrefs['text_forreference']; ?></h3>
        <?php if(strlen($strVendorTxCode)>0) { ?>
        <p><?php echo $row_rsProductPrefs['text_transactionnumber']; ?> <?php echo htmlentities($strVendorTxCode, ENT_COMPAT, "UTF-8"); ?> &raquo;&nbsp;<a href="invoice.php?VendorTxCode=<?php echo $strVendorTxCode ; ?>&amp;token=<?php echo md5(PRIVATE_KEY.$strVendorTxCode) ;?>" class="btn btn-primary"><?php echo $row_rsProductPrefs['text_viewinvoice']; ?></a>.</p><?php } ?>
        
        
      
        
        
       <?php if(isset($row_rsThisRegion['vatnumber'])) { ?> <p><?php echo $row_rsProductPrefs['text_vatnumber']; ?>: <?php echo $row_rsThisRegion['vatnumber']; ?></p><?php } 
 


	   } // end is basket
?>
</div></section>
<?php if(isset($row_rsProductPrefs['googlemerchantID']) && isset($row_rsThisOrder['CustomerEMail'])) { ?>
<script src="https://apis.google.com/js/platform.js?onload=renderOptIn" async defer></script>
<script>
  window.renderOptIn = function() {
    window.gapi.load('surveyoptin', function() {
      window.gapi.surveyoptin.render(
        {
          "merchant_id": <?php echo $row_rsProductPrefs['googlemerchantID']; ?>,
          "order_id": "<?php echo $strVendorTxCode ; ?>",
          "email": "<?php echo htmlentities($row_rsThisOrder['CustomerEMail'],ENT_COMPAT,"UTF-8"); ?>",
          "delivery_country": "<?php echo $row_rsThisOrder['iso2']; ?>",
          "estimated_delivery_date": "<?php echo date('Y-m-d', strtotime("NOW + 1 WEEK")); ?>"
        });
    });
  }
</script>

<script>
// add Google Reviews Badge
  window.renderBadge = function() {
    var ratingBadgeContainer = document.createElement("div");
    document.body.appendChild(ratingBadgeContainer);
    window.gapi.load('ratingbadge', function() {
      window.gapi.ratingbadge.render(ratingBadgeContainer, {"merchant_id": <?php echo $row_rsProductPrefs['googlemerchantID']; ?>});
    });
  }
</script>

<?php } ?>
    <!-- InstanceEndEditable --></main>
<?php require_once('../../local/includes/footer.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
 <?php if(isset($_SESSION['basket'])) {
	 emptyBasket();
 } ?>
<?php
mysql_free_result($rsPreferences);

mysql_free_result($rsThisRegion);

mysql_free_result($rsProductPrefs);

mysql_free_result($rsThisOrder);
?>
