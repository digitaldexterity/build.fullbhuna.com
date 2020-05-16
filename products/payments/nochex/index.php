<?php require_once('../../../Connections/aquiescedb.php'); ?><?php if(!isset($_SESSION["strBillingCountry"])) {
	$msg = "Your session has expired";
	header("location: /products/basket/index.php?msg=".urlencode($msg)); exit;
} ?>
<?php require_once('../../includes/productHeader.inc.php'); ?>
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


$select = "SELECT fullname, iso2 FROM countries WHERE ID = ".$_SESSION["strBillingCountry"];
	$result = mysql_query($select, $aquiescedb) or die(mysql_error());
	$billingCountry = mysql_fetch_assoc($result);
	
	if(isset($_SESSION["strDeliveryCountry"]) && intval($_SESSION["strDeliveryCountry"])>0) {
		$select = "SELECT fullname, iso2 FROM countries WHERE ID = ".$_SESSION["strDeliveryCountry"];
	$result = mysql_query($select, $aquiescedb) or die(mysql_error());
	$rowDeliveryCountry = mysql_fetch_assoc($result);
}
$body_class ="checkout ordersummary";

?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!DOCTYPE html>
<html class="" lang="en"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php  $pageTitle = "Nochex Order Summary"; echo $pageTitle." | ".$site_name;?>
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
      <div class="checkout checkout-summary container nochex">
        
        <div class="crumbs"><div><span class="you_are_in">You are in: </span><a href="/">Home</a><span class="separator">&nbsp;&rsaquo;&nbsp;</span><a href="/products/">Shop</a><span class="separator">&nbsp;&rsaquo;&nbsp;</span><?php echo isset($row_rsProductPrefs['text_yourdetails']) ? htmlentities($row_rsProductPrefs['text_yourdetails'], ENT_COMPAT, "UTF-8") : "Your details" ; ?></div></div><ol class="checkoutprogress">
        <li><a href="#"><?php echo isset($row_rsProductPrefs['text_yourorder']) ? htmlentities($row_rsProductPrefs['text_yourorder'], ENT_COMPAT, "UTF-8") : "Your Order" ?></a></li>
        <li><a href="#"><?php echo isset($row_rsProductPrefs['text_yourdetails']) ? htmlentities($row_rsProductPrefs['text_yourdetails'], ENT_COMPAT, "UTF-8") : "Your details" ; ?></a></li>
        <li class="selected"><a href="#"><?php echo isset($row_rsProductPrefs['paymenttext']) ? htmlentities($row_rsProductPrefs['paymenttext'], ENT_COMPAT, "UTF-8" ): "Payment"; ?></a></li>
        </ol>
        <h1>Order Summary </h1>
        <?php if(isset($seoPrefs['googleanalyticsecommerce']) && $seoPrefs['googleanalyticsecommerce']==1) $track_ecommerce = true; require_once('../../includes/basketcontents.inc.php');
		
// must go after basket to get shipping total avoid calling basket functions again
require_once('../includes/logtransaction.inc.php');
$strVendorTxCode = logtransaction("","NOCHEX");?>
         <form action="https://secure.nochex.com/" method="POST">
        <div class="basketnavigation">
          <button type="submit"  class="btn btn-primary makePaymentButton top" ><?php echo isset($row_rsProductPrefs['paymenttext']) ? htmlentities($row_rsProductPrefs['paymenttext'], ENT_COMPAT, "UTF-8" ): "Payment"; ?></button>
        </div>
        <h2>Your Details:</h2>
        <p>Your email: <?php echo $_SESSION['strCustomerEMail'];  ?> </p>
        <table border="0" cellpadding="2" cellspacing="2" class="form-table">
          <tr>
            <td >&nbsp;</td>
            <td><strong>Billing details:</strong></td>
            <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
            <td><strong>Delivery details:</strong></td>
          </tr>
          <tr>
            <td ><strong>Name:</strong></td>
            <td><?php echo $_SESSION['strBillingFirstnames'];  ?> <?php echo $_SESSION['strBillingSurname'];  ?></td>
            <td>&nbsp;</td>
            <td><?php if(isset($_SESSION["bIsDeliverySame"]) && $_SESSION["bIsDeliverySame"]==0) {
				  echo $_SESSION['strDeliveryFirstnames']." ".$_SESSION['strDeliverySurname'];
			  } else if(isset($_SESSION["bIsDeliverySame"]) && $_SESSION["bIsDeliverySame"]==2) {
				  echo isset($row_rsProductPrefs['text_willcollectfrom']) ? htmlentities($row_rsProductPrefs['text_willcollectfrom'], ENT_COMPAT, "UTF-8") : "Will collect";
			  } else {
				  echo isset($row_rsProductPrefs['text_sameasbilling']) ? htmlentities($row_rsProductPrefs['text_sameasbilling'], ENT_COMPAT, "UTF-8") : "Same as billing";
			  } ?></td>
          </tr>
          <tr>
            <td ><strong>Address:</strong></td>
            <td><?php echo $_SESSION['strBillingAddress1'];  ?></td>
            <td>&nbsp;</td>
            <td><?php echo (!isset($_SESSION["bIsDeliverySame"]) || $_SESSION["bIsDeliverySame"]==0) ? $_SESSION['strDeliveryAddress1'] : "";  ?></td>
          </tr>
          <tr>
            <td >&nbsp;</td>
            <td><?php echo $_SESSION['strBillingAddress2'];  ?></td>
            <td>&nbsp;</td>
            <td><?php echo (!isset($_SESSION["bIsDeliverySame"]) || $_SESSION["bIsDeliverySame"]==0) ? $_SESSION['strDeliveryAddress2'] : "";  ?></td>
          </tr>
          <tr>
            <td ><strong>City:</strong></td>
            <td><?php echo $_SESSION['strBillingCity'];  ?></td>
            <td>&nbsp;</td>
            <td><?php echo (!isset($_SESSION["bIsDeliverySame"]) || $_SESSION["bIsDeliverySame"]==0) ? $_SESSION['strDeliveryCity'] : "";  ?></td>
          </tr>
          <tr>
            <td ><strong>Postcode:</strong></td>
            <td><?php echo $_SESSION['strBillingPostCode'];  ?></td>
            <td>&nbsp;</td>
            <td><?php echo (!isset($_SESSION["bIsDeliverySame"]) || $_SESSION["bIsDeliverySame"]==0) ? $_SESSION['strDeliveryPostCode'] : "";  ?></td>
          </tr>
          <tr>
            <td ><strong>Country:</strong></td>
            <td><?php echo $billingCountry['fullname']; ?></td>
            <td>&nbsp;</td>
            <td><?php echo (!isset($_SESSION["bIsDeliverySame"]) || $_SESSION["bIsDeliverySame"]==0) ? @$rowDeliveryCountry['fullname'] : "";  ?></td>
          </tr>
          <tr>
            <td ><strong>Phone:</strong></td>
            <td><?php echo isset($_SESSION['strBillingPhone']) ? $_SESSION['strBillingPhone'] : "";  ?>
              <?php $custom = "Delivery address: ";
$custom .= (!isset($_SESSION["bIsDeliverySame"]) || $_SESSION["bIsDeliverySame"]==0) ?  $_SESSION['strDeliveryFirstnames']." ".$_SESSION['strDeliverySurname']."\n\r".$_SESSION['strDeliveryAddress1']."\n\r".$_SESSION['strDeliveryAddress2']."\n\r".$_SESSION['strDeliveryCity']."\n\r".$_SESSION['strDeliveryPostCode']."\n\r".@$rowDeliveryCountry['fullname']."\n\r".@$_SESSION['strBillingPhone'] : "same as billing address";  ?></td>
            <td>&nbsp;</td>
            <td><?php echo isset($_SESSION['strDeliveryPhone']) ? $_SESSION['strDeliveryPhone'] : "";  ?>&nbsp;</td>
          </tr>
        </table>
        <p><a href="../index.php" class="link_back"><i class="glyphicon glyphicon-arrow-left"></i> <?php echo isset($row_rsProductPrefs['text_yourdetails']) ? htmlentities($row_rsProductPrefs['text_yourdetails'], ENT_COMPAT, "UTF-8") : "Your details" ; ?></a></p>
      
          
     

       
        <input name="billing_fullname" type="hidden" value="<?php echo $_SESSION['strDeliveryFirstnames']." ".$_SESSION['strDeliverySurname'];  ?>" />
          <input name="billing_address" type="hidden" value="<?php echo $_SESSION['strDeliveryAddress1']."\n".$_SESSION['strDeliveryAddress2']."\n".$_SESSION['strBillingCity']. "\n".$billingCountry['fullname']; ?>" />
          <input name="billing_postcode" type="hidden" value="<?php echo $_SESSION['strDeliveryPostCode'];  ?>" />
          <?php if (!isset($_SESSION["bIsDeliverySame"])) { ?>
          <input name="delivery_fullname" type="hidden" value="<?php echo $_SESSION['strDeliveryFirstnames']." ".$_SESSION['strDeliverySurname'];  ?>" />
          <input name="delivery_address" type="hidden" value="<?php echo $_SESSION['strDeliveryAddress1']."\n".$_SESSION['strDeliveryAddress2']."\n".$_SESSION['strDeliveryCity']. "\n".$rowDeliveryCountry['fullname']; ?>" />
          <input name="delivery_postcode" type="hidden" value="<?php echo $_SESSION['strDeliveryPostCode'];  ?>" />
          
          <?php } ?>
          <input name="email_address" type="hidden" value="<?php echo $_SESSION['strCustomerEMail'];  ?>" />
          <input name="customer_phone_number" type="hidden" value="<?php echo isset($_SESSION['strBillingPhone']) ? $_SESSION['strBillingPhone'] : "";  ?>" />
          <input type="hidden" name ="merchant_id" value="<?php echo  $row_rsProductPrefs['paymentclientID']; ?>">
          <input type="hidden" name ="amount" value="<?php echo number_format($grandtotal,2,".",""); ?>">
          <input type="hidden" name ="description" value="<?php echo (strlen($basketDescription)<255) ? htmlentities($basketDescription, ENT_COMPAT, "UTF-8") : $totalitems." items" ; ?>">
          <input type="hidden" name ="order_id" value="<?php echo $strVendorTxCode; ?>">
           <button type="submit"  class="btn btn-primary makePaymentButton bottom" ><?php echo isset($row_rsProductPrefs['paymenttext']) ? htmlentities($row_rsProductPrefs['paymenttext'], ENT_COMPAT, "UTF-8" ): "Payment"; ?></button>
        </form> <?php echo isset($row_rsProductPrefs['checkoutconfirmfooter']) ? htmlentities($row_rsProductPrefs['checkoutconfirmfooter'], ENT_COMPAT, "UTF-8") : ""; ?>
      </div>
      <div id="paymentProviderBadge"> </div>
      <!-- InstanceEndEditable --></main>
<?php require_once('../../../local/includes/footer.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsThisRegion);

mysql_free_result($rsProductPrefs);
?>
