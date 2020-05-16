<?php require_once('../../../Connections/aquiescedb.php'); ?><?php if(!isset($_SESSION["strBillingCountry"])) {
	$msg = "Your session has expired";
	header("location: /products/basket/index.php?msg=".urlencode($msg)); exit;
} ?><?php require_once('../../includes/productHeader.inc.php'); ?>
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

$selectc = "SELECT fullname, iso2 FROM countries WHERE ID = ".$_SESSION["strBillingCountry"];
	$result = mysql_query($selectc, $aquiescedb) or die(mysql_error());
	$rowBillingCountry = mysql_fetch_assoc($result);
	
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
<?php  $pageTitle = "Secure Trading Order Summary"; echo $pageTitle." | ".$site_name;?>
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
<?php if($totalRows_rsAddresses<1) {
echo ".savedaddresses { display: none; }";
}
?> 
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
    <div class="checkout checkout-summary container securetrading">
        <div class="crumbs"><div><span class="you_are_in">You are in: </span><a href="/">Home</a><span class="separator">&nbsp;&rsaquo;&nbsp;</span><a href="/products/">Shop</a><span class="separator">&nbsp;&rsaquo;&nbsp;</span><?php echo isset($row_rsProductPrefs['text_yourdetails']) ? htmlentities($row_rsProductPrefs['text_yourdetails'], ENT_COMPAT, "UTF-8") : "Your details" ; ?></div></div><ol class="checkoutprogress">
        <li><a href="#"><?php echo isset($row_rsProductPrefs['text_yourorder']) ? htmlentities($row_rsProductPrefs['text_yourorder'], ENT_COMPAT, "UTF-8") : "Your Order" ?></a></li>
        <li><a href="#"><?php echo isset($row_rsProductPrefs['text_yourdetails']) ? htmlentities($row_rsProductPrefs['text_yourdetails'], ENT_COMPAT, "UTF-8") : "Your details" ; ?></a></li>
        <li class="selected"><a href="#"><?php echo isset($row_rsProductPrefs['paymenttext']) ? htmlentities($row_rsProductPrefs['paymenttext'], ENT_COMPAT, "UTF-8" ): "Payment"; ?></a></li>
        </ol>
        <h1>Order Summary </h1>
        <?php $track_ecommerce = true;  require_once('../../includes/basketcontents.inc.php'); 
		
// must go after basket to avoid calling basket functions again
require_once('../includes/logtransaction.inc.php');
$strVendorTxCode = logtransaction("","CREDITCARD"); ?><h2>Your Details:</h2>
        <form action="https://payments.securetrading.net/process/payments/choice" method="post"><p>Your email: <?php echo $_SESSION['strCustomerEMail'];  ?>
          <input name="billingemail" type="hidden" value="<?php echo $_SESSION['strCustomerEMail'];  ?>" />
        </p>
<table border="0" cellpadding="2" cellspacing="2" class="form-table">
  <tr>
    <td >&nbsp;</td>
    <td><strong>Billing details:</strong></td>
    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
    <td><strong>Delivery details:</strong></td>
    </tr>
  <tr>
    <td ><strong>Name:</strong></td>
    <td><?php echo $_SESSION['strBillingFirstnames'];  ?> <?php echo $_SESSION['strBillingSurname'];  ?>
      <input name="billingfirstname" type="hidden"   value="<?php echo $_SESSION['strBillingFirstnames'];  ?>" />
      <input name="billinglastname" type="hidden"  value="<?php echo $_SESSION['strBillingSurname'];  ?>" /></td>
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
    <td><?php echo $_SESSION['strBillingAddress1'];  ?>
      <input name="billingpremise" type="hidden" value="<?php echo $_SESSION['strBillingAddress1'];  ?>" /></td>
    <td>&nbsp;</td>
    <td><?php echo (!isset($_SESSION["bIsDeliverySame"]) || $_SESSION["bIsDeliverySame"]==0) ? $_SESSION['strDeliveryAddress1'] : "";  ?><input name="customerpremise" type="hidden" value="<?php echo (!isset($_SESSION["bIsDeliverySame"]) || $_SESSION["bIsDeliverySame"]==0) ? $_SESSION['strDeliveryAddress1'] : "";  ?>" /></td>
    </tr>
  <tr>
    <td >&nbsp;</td>
    <td><?php echo $_SESSION['strBillingAddress2'];  ?>
      <input name="billingstreet" type="hidden"value="<?php echo $_SESSION['strBillingAddress2'];  ?>" /></td>
    <td>&nbsp;</td>
    <td><?php echo (!isset($_SESSION["bIsDeliverySame"]) || $_SESSION["bIsDeliverySame"]==0) ? $_SESSION['strDeliveryAddress2'] : "";  ?><input name="customerstreet" type="hidden"value="<?php echo (!isset($_SESSION["bIsDeliverySame"]) || $_SESSION["bIsDeliverySame"]==0) ? $_SESSION['strDeliveryAddress2'] : "";  ?>" /></td>
    </tr>
  <tr>
    <td ><strong>City:</strong></td>
    <td><?php echo $_SESSION['strBillingCity'];  ?>
      <input name="billingtown" type="hidden" value="<?php echo $_SESSION['strBillingCity'];  ?>" /></td>
    <td>&nbsp;</td>
    <td><?php echo (!isset($_SESSION["bIsDeliverySame"]) || $_SESSION["bIsDeliverySame"]==0) ? $_SESSION['strDeliveryCity'] : "";  ?><input name="customertown" type="hidden" value="<?php echo (!isset($_SESSION["bIsDeliverySame"]) || $_SESSION["bIsDeliverySame"]==0) ? $_SESSION['strDeliveryCity'] : "";  ?>" /></td>
    </tr>
  <tr>
    <td ><strong>Postcode:</strong></td>
    <td><?php echo $_SESSION['strBillingPostCode'];  ?>
      <input name="billingpostcode" type="hidden" value="<?php echo $_SESSION['strBillingPostCode'];  ?>" /></td>
    <td>&nbsp;</td>
    <td><?php echo (!isset($_SESSION["bIsDeliverySame"]) || $_SESSION["bIsDeliverySame"]==0) ? $_SESSION['strDeliveryPostCode'] : "";  ?><input name="customerpostcode" type="hidden" value="<?php echo (!isset($_SESSION["bIsDeliverySame"]) || $_SESSION["bIsDeliverySame"]==0) ? $_SESSION['strDeliveryPostCode'] : "";  ?>" /></td>
    </tr>
  <tr>
    <td ><strong>Country:</strong></td>
    <td><?php echo $rowBillingCountry['fullname']; ?>
      <input name="billingcountryiso2a" type="hidden" value="GB" /></td>
    <td>&nbsp;</td>
    <td><?php echo (!isset($_SESSION["bIsDeliverySame"]) || $_SESSION["bIsDeliverySame"]==0) ? @$rowDeliveryCountry['fullname'] : "";  ?>
      <input name="customercountryiso2a" type="hidden" value="GB" /></td>
    </tr>
  <tr>
    <td ><strong>Phone:</strong></td>
    <td><?php echo isset($_SESSION['strBillingPhone']) ? $_SESSION['strBillingPhone'] : "";  ?><input type="hidden" name="billingtelephone" value="<?php echo isset($_SESSION['strBillingPhone']) ? $_SESSION['strBillingPhone'] : "";  ?>"/>
      <input type="hidden" name="billingtelephonetype"  value="H" /><!-- H=home, W=work, M=mobile--></td>
    <td>&nbsp;</td>
    <td><?php echo isset($_SESSION['strDeliveryPhone']) ? $_SESSION['strDeliveryPhone'] : "";  ?>&nbsp;<input type="hidden" name="customertelephone" value="<?php echo isset($_SESSION['strDeliveryPhone']) ? $_SESSION['strDeliveryPhone'] : "";  ?>"/>
      <input type="hidden" name="customertelephonetype"  value="H" /><!-- H=home, W=work, M=mobile--></td>
  </tr>
</table>

        <p><a href="../index.php" class="link_back"><i class="glyphicon glyphicon-arrow-left"></i> <?php echo isset($row_rsProductPrefs['text_yourdetails']) ? htmlentities($row_rsProductPrefs['text_yourdetails'], ENT_COMPAT, "UTF-8") : "Your details" ; ?></a></p>
        <div class="basketnavigation">
         
               <button type="submit"  class="btn btn-primary makePaymentButton " ><?php echo isset($row_rsProductPrefs['paymenttext']) ? htmlentities($row_rsProductPrefs['paymenttext'], ENT_COMPAT, "UTF-8" ): "Payment"; ?></button>
           
           
            
        </div>
        <input type="hidden" name="sitereference" value="<?php echo $row_rsProductPrefs['paymentclientID']; ?>" />
        
        <input type="hidden" name="currencyiso3a" value="<?php echo $row_rsThisRegion['currencycode']; ?>" />
        <input type="hidden" name="mainamount" value="<?php echo $grandtotal; ?>" />
        <input type="hidden" name="version" value="1">
        <input type="hidden" name="orderreference" value="<?php echo $strVendorTxCode; ?>" />
        
        
        
      </form> <?php echo isset($row_rsProductPrefs['checkoutconfirmfooter']) ? htmlentities($row_rsProductPrefs['checkoutconfirmfooter'], ENT_COMPAT, "UTF-8") : ""; ?>
    </div>
                <div id="paymentProviderBadge">

    <?php require_once('badge.inc.php'); ?></div></section>
    <!-- InstanceEndEditable --></main>
<?php require_once('../../../local/includes/footer.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsThisRegion);

mysql_free_result($rsProductPrefs);
?>
