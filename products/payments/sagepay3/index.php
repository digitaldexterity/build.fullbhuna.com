<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php require_once('../../includes/productHeader.inc.php'); ?><?php require_once('../../../core/includes/framework.inc.php'); ?>
<?php if($row_rsProductPrefs['askbillingdetails']==1 && !isset($_SESSION["strBillingCountry"])) {
	$msg = "Your session has expired";
	header("location: /products/basket/index.php?msg=".urlencode($msg)); exit;
} ?>
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


$select = "SELECT fullname, iso2 FROM countries WHERE ID = ".intval($_SESSION["strBillingCountry"]);
	$result = mysql_query($select, $aquiescedb) or die(mysql_error());
	$billingCountry = mysql_fetch_assoc($result);
	
	if(isset($_SESSION["strDeliveryCountry"]) && intval($_SESSION["strDeliveryCountry"])>0) {
		$select = "SELECT fullname, iso2 FROM countries WHERE ID = ".$_SESSION["strDeliveryCountry"];
	$result = mysql_query($select, $aquiescedb) or die(mysql_error());
	$rowDeliveryCountry = mysql_fetch_assoc($result);
}
if ($row_rsProductPrefs['shopstatus']==1) { 
$form_url = "https://live.sagepay.com/gateway/service/vspform-register.vsp";

} else {
	$form_url = "https://test.sagepay.com/gateway/service/vspform-register.vsp";
}


$body_class ="checkout ordersummary";
?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!DOCTYPE html>
<html class="" lang="en"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php  $pageTitle = "SagePay Order Summary"; echo $pageTitle." | ".$site_name;?>
</title>
<!-- InstanceEndEditable -->
<!--[if IE]><![endif]-->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script src="../../../SpryAssets/SpryValidationTextField.js"></script>
<script src="../../../SpryAssets/SpryValidationSelect.js"></script>
<link href="../../../SpryAssets/SpryValidationTextField.css" rel="stylesheet"  />
<style >
<!--
-->
</style>
<link href="../../../SpryAssets/SpryValidationSelect.css" rel="stylesheet"  />
<link href="../../css/defaultProducts.css" rel="stylesheet"  />
<!-- InstanceEndEditable -->
</head>
<body id="OffTemplate" class="bootstrap <?php echo $body_class;  ?>">
<?php require_once('../../../local/includes/header.inc.php'); ?>
<main id="content"><!-- InstanceBeginEditable name="Body" --><?php require_once('../../../core/seo/includes/googletagmanager.inc.php'); ?>
<section>
      <div class="checkout checkout-summary container paypal">
       <?php if($row_rsProductPrefs['askbillingdetails']==1) {  // remove navigation if not asking ddress?>
          <div class="crumbs"><div><span class="you_are_in">You are in: </span><a href="/">Home</a><span class="separator">&nbsp;&rsaquo;&nbsp;</span><a href="/products/">Shop</a><span class="separator">&nbsp;&rsaquo;&nbsp;</span><?php echo isset($row_rsProductPrefs['text_yourdetails']) ? htmlentities($row_rsProductPrefs['text_yourdetails'], ENT_COMPAT, "UTF-8") : "Your details" ; ?></div></div><ol class="checkoutprogress">
        <li><a href="#"><?php echo isset($row_rsProductPrefs['text_yourorder']) ? htmlentities($row_rsProductPrefs['text_yourorder'], ENT_COMPAT, "UTF-8") : "Your Order" ?></a></li>
        <li><a href="#"><?php echo isset($row_rsProductPrefs['text_yourdetails']) ? htmlentities($row_rsProductPrefs['text_yourdetails'], ENT_COMPAT, "UTF-8") : "Your details" ; ?></a></li>
        <li class="selected"><a href="#"><?php echo isset($row_rsProductPrefs['paymenttext']) ? htmlentities($row_rsProductPrefs['paymenttext'], ENT_COMPAT, "UTF-8" ): "Payment"; ?></a></li>
        </ol><?php } // end ask address ?>
          <h1> <?php echo isset($row_rsProductPrefs['text_ordersummary']) ? htmlentities($row_rsProductPrefs['text_ordersummary'], ENT_COMPAT, "UTF-8") : "Order Summary" ?></h1><?php if(isset($seoPrefs['googleanalyticsecommerce']) && $seoPrefs['googleanalyticsecommerce']==1) $track_ecommerce = true; require_once('../../includes/basketcontents.inc.php');
		
// must go after basket to get shipping total avoid calling basket functions again
require_once('../includes/logtransaction.inc.php');
$strVendorTxCode = logtransaction("","SAGEPAY");?>
        
          <form id="SagePayForm" action="<?php echo $form_url; ?>" method="post">
          <div class="basketnavigation">
            <button type="submit"  class="btn btn-primary makePaymentButton top" ><?php echo isset($row_rsProductPrefs['paymenttext']) ? htmlentities($row_rsProductPrefs['paymenttext'], ENT_COMPAT, "UTF-8" ): "Payment"; ?></button>
          </div>
          <?php if($row_rsProductPrefs['askbillingdetails']==1) { ?>
          <h2><?php echo isset($row_rsProductPrefs['text_yourdetails']) ? htmlentities($row_rsProductPrefs['text_yourdetails'], ENT_COMPAT, "UTF-8") : "Your Details" ?>:</h2>
          <p><?php echo isset($row_rsProductPrefs['text_email']) ? htmlentities($row_rsProductPrefs['text_email'], ENT_COMPAT, "UTF-8") : "email" ?>: <?php echo $_SESSION['strCustomerEMail'];  ?>
            
          </p>
          <div class="row">
          <div class="col-sm-6">
          <table class="form-table">
            <tr>
              <td><strong><?php echo isset($row_rsProductPrefs['text_billingdetails']) ? htmlentities($row_rsProductPrefs['text_billingdetails'], ENT_COMPAT, "UTF-8") : "Billing details" ?>:</strong></td>
              </tr>
            <tr>
              <td><?php echo $_SESSION['strBillingFirstnames'];  ?> <?php echo $_SESSION['strBillingSurname'];  ?>
                
              </td>
              </tr>
            <tr>
              <td><?php echo $_SESSION['strBillingAddress1'];  ?>
              </td>
              </tr>
            <tr>
              <td><?php echo $_SESSION['strBillingAddress2'];  ?>
              </td>
              </tr>
            <tr>
              <td><?php echo $_SESSION['strBillingCity'];  ?>
              </td>
              </tr>
            <tr>
              <td><?php echo $_SESSION['strBillingPostCode'];  ?>
              </td>
              </tr>
            <tr>
              <td><?php echo $billingCountry['fullname']; ?>
              </td>
              </tr>
            <tr>
              <td><?php echo isset($_SESSION['strBillingPhone']) ? $_SESSION['strBillingPhone'] : "";  ?>
                
                <?php $custom = "Delivery address: ";
$custom .= (!isset($_SESSION["bIsDeliverySame"]) || $_SESSION["bIsDeliverySame"]==0) ?  $_SESSION['strDeliveryFirstnames']." ".$_SESSION['strDeliverySurname']."\n\r".$_SESSION['strDeliveryAddress1']."\n\r".$_SESSION['strDeliveryAddress2']."\n\r".$_SESSION['strDeliveryCity']."\n\r".$_SESSION['strDeliveryPostCode']."\n\r".@$rowDeliveryCountry['fullname']."\n\r".@$_SESSION['strBillingPhone'] : "same as billing address";  ?></td>
              </tr>
          </table><br>
          </div><div class="col-sm-6">
            <table class="form-table">
              <tr>
                <td><strong><?php echo isset($row_rsProductPrefs['text_deliverydetails']) ? htmlentities($row_rsProductPrefs['text_deliverydetails'], ENT_COMPAT, "UTF-8") : "Delivery details" ?>:</strong></td>
              </tr>
              <tr>
                <td><?php if(isset($_SESSION["bIsDeliverySame"]) && $_SESSION["bIsDeliverySame"]==0) {
				  echo $_SESSION['strDeliveryFirstnames']." ".$_SESSION['strDeliverySurname'];
			  } else if(isset($_SESSION["bIsDeliverySame"]) && $_SESSION["bIsDeliverySame"]==2) {
				  echo isset($row_rsProductPrefs['text_willcollectfrom']) ? htmlentities($row_rsProductPrefs['text_willcollectfrom'], ENT_COMPAT, "UTF-8") : "Will collect";
			  } else {
				  echo isset($row_rsProductPrefs['text_sameasbilling']) ? htmlentities($row_rsProductPrefs['text_sameasbilling'], ENT_COMPAT, "UTF-8") : "Same as billing";
			  } ?></td>
              </tr>
              <tr>
                <td><?php echo (!isset($_SESSION["bIsDeliverySame"]) || $_SESSION["bIsDeliverySame"]==0) ? $_SESSION['strDeliveryAddress1'] : "";  ?></td>
              </tr>
              <tr>
                <td><?php echo (!isset($_SESSION["bIsDeliverySame"]) || $_SESSION["bIsDeliverySame"]==0) ? $_SESSION['strDeliveryAddress2'] : "";  ?></td>
              </tr>
              <tr>
                <td><?php echo (!isset($_SESSION["bIsDeliverySame"]) || $_SESSION["bIsDeliverySame"]==0) ? $_SESSION['strDeliveryCity'] : "";  ?></td>
              </tr>
              <tr>
                <td><?php echo (!isset($_SESSION["bIsDeliverySame"]) || $_SESSION["bIsDeliverySame"]==0) ? $_SESSION['strDeliveryPostCode'] : "";  ?></td>
              </tr>
              <tr>
                <td><?php echo (!isset($_SESSION["bIsDeliverySame"]) || $_SESSION["bIsDeliverySame"]==0) ? @$rowDeliveryCountry['fullname'] : "";  ?></td>
              </tr>
              <tr>
                <td><?php echo isset($_SESSION['strDeliveryPhone']) ? $_SESSION['strDeliveryPhone'] : "";  ?>&nbsp;</td>
              </tr>
            </table>
          </div></div>
         <hr> 
          <p><a href="../index.php" class="btn btn-default btn-secondary"><i class="glyphicon glyphicon-chevron-left"></i> <?php echo isset($row_rsProductPrefs['text_yourdetails']) ? htmlentities($row_rsProductPrefs['text_yourdetails'], ENT_COMPAT, "UTF-8") : "Your details" ; ?></a></p>
          
          <div class="basketnavigation">
            <button type="submit"  class="btn btn-primary makePaymentButton bottom" ><?php echo isset($row_rsProductPrefs['paymenttext']) ? htmlentities($row_rsProductPrefs['paymenttext'], ENT_COMPAT, "UTF-8" ): "Payment"; ?></button>
          </div><?php } // end ask address ?>

<?php $successURL=""; if(strpos($row_rsProductPrefs['successURL'],"http")===false) { $successURL .=  getProtocol()."://". $_SERVER['HTTP_HOST']; } $successURL .= $row_rsProductPrefs['successURL']; $successURL .= "?VendorTxCode=".$strVendorTxCode;

$failureURL=""; if(strpos($row_rsProductPrefs['failURL'],"http")===false) { $failureURL .=  getProtocol()."://". $_SERVER['HTTP_HOST']; } $failureURL .= $row_rsProductPrefs['failURL']; $failureURL .= "?VendorTxCode=".$strVendorTxCode;?>        
        
        <?php
define("SAGEPAY_CRYPT_PASSWORD",$row_rsProductPrefs['paymentclientpassword']);
require_once('lib/sagepay3.php');

$sagePay = new SagePay();
$sagePay->setVendorTxCode($strVendorTxCode);
$sagePay->setDescription($basketDescription);
$sagePay->setCurrency($row_rsThisRegion['currencycode']);
$sagePay->setAmount(number_format($grandtotal,2,".",""));
$sagePay->setBillingSurname($_SESSION['strBillingSurname']);
$sagePay->setBillingFirstnames($_SESSION['strBillingFirstnames']);
$sagePay->setBillingCity($_SESSION['strBillingCity']);
$sagePay->setBillingPostCode($_SESSION['strBillingPostCode']);
$sagePay->setBillingAddress1($_SESSION['strBillingAddress1']);
$sagePay->setBillingCountry($billingCountry['iso2']);
if (isset($_SESSION["bIsDeliverySame"]) && $_SESSION["bIsDeliverySame"]==1) {
$sagePay->setDeliverySameAsBilling();
} else {
	$sagePay->setDeliverySurname($_SESSION['strDeliverySurname']);
$sagePay->setDeliveryFirstnames($_SESSION['strDeliveryFirstnames']);
$sagePay->setDeliveryCity($_SESSION['strDeliveryCity']);
$sagePay->setDeliveryPostCode($_SESSION['strDeliveryPostCode']);
$sagePay->setDeliveryAddress1($_SESSION['strDeliveryAddress1']);
$sagePay->setDeliveryCountry($rowDeliveryCountry['iso2']);
}
$sagePay->setSuccessURL($successURL);
$sagePay->setFailureURL($failureURL);



?>


	<input type="hidden" name="VPSProtocol" value="3.00">
<input type="hidden" name="TxType" value="PAYMENT">
<input type="hidden" name="Vendor" value="<?php echo  $row_rsProductPrefs['paymentclientID']; ?>"> 
	<input type="hidden" name="Crypt" value= "<?php echo $sagePay->getCrypt(); ?>">
</form>

         <?php echo isset($row_rsProductPrefs['checkoutconfirmfooter']) ? htmlentities($row_rsProductPrefs['checkoutconfirmfooter'], ENT_COMPAT, "UTF-8") : ""; ?>
      </div></section>
     
      <!-- InstanceEndEditable --></main>
<?php require_once('../../../local/includes/footer.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsThisRegion);

mysql_free_result($rsProductPrefs);
?>
