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
$paypal_url = ($row_rsProductPrefs['paymentproviderID']==6) ? "https://securepayments.paypal.com/cgi-bin/acquiringweb" : "https://www.paypal.com/cgi-bin/webscr" ;

} else {
	$paypal_url = "https://www.sandbox.paypal.com/cgi-bin/webscr";
}
$body_class ="checkout ordersummary";
?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!DOCTYPE html>
<html class="" lang="en"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php  $pageTitle = "PayPal Order Summary"; echo $pageTitle." | ".$site_name;?>
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
      <div class="checkout checkout-summary container paypal"><!--<form action="https://www.sandbox.paypal.com/cgi-bin/webscr" method="post">-->
       <?php if($row_rsProductPrefs['askbillingdetails']==1) {  // remove navigation if not asking ddress?>
          <div class="crumbs"><div><span class="you_are_in">You are in: </span><a href="/">Home</a><span class="separator">&nbsp;&rsaquo;&nbsp;</span><a href="/products/">Shop</a><span class="separator">&nbsp;&rsaquo;&nbsp;</span><?php echo isset($row_rsProductPrefs['text_yourdetails']) ? htmlentities($row_rsProductPrefs['text_yourdetails'], ENT_COMPAT, "UTF-8") : "Your details" ; ?></div></div><ol class="checkoutprogress">
        <li><a href="#"><?php echo isset($row_rsProductPrefs['text_yourorder']) ? htmlentities($row_rsProductPrefs['text_yourorder'], ENT_COMPAT, "UTF-8") : "Your Order" ?></a></li>
        <li><a href="#"><?php echo isset($row_rsProductPrefs['text_yourdetails']) ? htmlentities($row_rsProductPrefs['text_yourdetails'], ENT_COMPAT, "UTF-8") : "Your details" ; ?></a></li>
        <li class="selected"><a href="#"><?php echo isset($row_rsProductPrefs['paymenttext']) ? htmlentities($row_rsProductPrefs['paymenttext'], ENT_COMPAT, "UTF-8" ): "Payment"; ?></a></li>
        </ol><?php } // end ask address ?>
          <h1> <?php echo isset($row_rsProductPrefs['text_ordersummary']) ? htmlentities($row_rsProductPrefs['text_ordersummary'], ENT_COMPAT, "UTF-8") : "Order Summary" ?></h1><?php if(isset($seoPrefs['googleanalyticsecommerce']) && $seoPrefs['googleanalyticsecommerce']==1) $track_ecommerce = true; require_once('../../includes/basketcontents.inc.php');
		
// must go after basket to get shipping total avoid calling basket functions again
require_once('../includes/logtransaction.inc.php');
$strVendorTxCode = logtransaction("","PAYPAL");?>
         
          <form action="<?php echo $paypal_url; ?>" method="post">
          <div class="basketnavigation">
            <button type="submit"  class="btn btn-primary makePaymentButton top" ><?php echo isset($row_rsProductPrefs['paymenttext']) ? htmlentities($row_rsProductPrefs['paymenttext'], ENT_COMPAT, "UTF-8" ): "Payment"; ?></button>
          </div>
          <?php if($row_rsProductPrefs['askbillingdetails']==1) { ?>
          <h2><?php echo isset($row_rsProductPrefs['text_yourdetails']) ? htmlentities($row_rsProductPrefs['text_yourdetails'], ENT_COMPAT, "UTF-8") : "Your Details" ?>:</h2>
          <p><?php echo isset($row_rsProductPrefs['text_email']) ? htmlentities($row_rsProductPrefs['text_email'], ENT_COMPAT, "UTF-8") : "email" ?>: <?php echo $_SESSION['strCustomerEMail'];  ?>
            
          </p>
          <table class="form-table">
            <tr>
              <td >&nbsp;</td>
              <td><strong><?php echo isset($row_rsProductPrefs['text_billingdetails']) ? htmlentities($row_rsProductPrefs['text_billingdetails'], ENT_COMPAT, "UTF-8") : "Billing details" ?>:</strong></td>
              <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
              <td><strong><?php echo isset($row_rsProductPrefs['text_deliverydetails']) ? htmlentities($row_rsProductPrefs['text_deliverydetails'], ENT_COMPAT, "UTF-8") : "Delivery details" ?>:</strong></td>
            </tr>
            <tr>
              <td ><strong><?php echo isset($row_rsProductPrefs['text_firstname']) ? htmlentities($row_rsProductPrefs['text_firstname'], ENT_COMPAT, "UTF-8") : "First Name(s)" ?>/<?php echo isset($row_rsProductPrefs['text_surname']) ? htmlentities($row_rsProductPrefs['text_surname'], ENT_COMPAT, "UTF-8") : "Surname" ?>:</strong></td>
              <td><?php echo $_SESSION['strBillingFirstnames'];  ?> <?php echo $_SESSION['strBillingSurname'];  ?>
                
              </td>
              <td>&nbsp;</td>
              <td>
              <?php if(isset($_SESSION["bIsDeliverySame"]) && $_SESSION["bIsDeliverySame"]==0) {
				  echo $_SESSION['strDeliveryFirstnames']." ".$_SESSION['strDeliverySurname'];
			  } else if(isset($_SESSION["bIsDeliverySame"]) && $_SESSION["bIsDeliverySame"]==2) {
				  echo isset($row_rsProductPrefs['text_willcollectfrom']) ? htmlentities($row_rsProductPrefs['text_willcollectfrom'], ENT_COMPAT, "UTF-8") : "Will collect";
			  } else {
				  echo isset($row_rsProductPrefs['text_sameasbilling']) ? htmlentities($row_rsProductPrefs['text_sameasbilling'], ENT_COMPAT, "UTF-8") : "Same as billing";
			  } ?></td>
            </tr>
            <tr>
              <td ><strong><?php echo isset($row_rsProductPrefs['text_address']) ? htmlentities($row_rsProductPrefs['text_address'], ENT_COMPAT, "UTF-8") : "Address" ?>:</strong></td>
              <td><?php echo $_SESSION['strBillingAddress1'];  ?>
              </td>
              <td>&nbsp;</td>
              <td><?php echo (!isset($_SESSION["bIsDeliverySame"]) || $_SESSION["bIsDeliverySame"]==0) ? $_SESSION['strDeliveryAddress1'] : "";  ?></td>
            </tr>
            <tr>
              <td >&nbsp;</td>
              <td><?php echo $_SESSION['strBillingAddress2'];  ?>
              </td>
              <td>&nbsp;</td>
              <td><?php echo (!isset($_SESSION["bIsDeliverySame"]) || $_SESSION["bIsDeliverySame"]==0) ? $_SESSION['strDeliveryAddress2'] : "";  ?></td>
            </tr>
            <tr>
              <td ><strong><?php echo isset($row_rsProductPrefs['text_city']) ? htmlentities($row_rsProductPrefs['text_city'], ENT_COMPAT, "UTF-8") : "City" ?>:</strong></td>
              <td><?php echo $_SESSION['strBillingCity'];  ?>
              </td>
              <td>&nbsp;</td>
              <td><?php echo (!isset($_SESSION["bIsDeliverySame"]) || $_SESSION["bIsDeliverySame"]==0) ? $_SESSION['strDeliveryCity'] : "";  ?></td>
            </tr>
            <tr>
              <td ><strong><?php echo isset($row_rsProductPrefs['text_postcode']) ? htmlentities($row_rsProductPrefs['text_postcode'], ENT_COMPAT, "UTF-8") : "Post/ZIP code" ?>:</strong></td>
              <td><?php echo $_SESSION['strBillingPostCode'];  ?>
              </td>
              <td>&nbsp;</td>
              <td><?php echo (!isset($_SESSION["bIsDeliverySame"]) || $_SESSION["bIsDeliverySame"]==0) ? $_SESSION['strDeliveryPostCode'] : "";  ?></td>
            </tr>
            <tr>
              <td ><strong><?php echo isset($row_rsProductPrefs['text_country']) ? htmlentities($row_rsProductPrefs['text_country'], ENT_COMPAT, "UTF-8") : "Country" ?>:</strong></td>
              <td><?php echo $billingCountry['fullname']; ?>
              </td>
              <td>&nbsp;</td>
              <td><?php echo (!isset($_SESSION["bIsDeliverySame"]) || $_SESSION["bIsDeliverySame"]==0) ? @$rowDeliveryCountry['fullname'] : "";  ?></td>
            </tr>
            <tr>
              <td ><strong><?php echo isset($row_rsProductPrefs['text_telephone']) ? htmlentities($row_rsProductPrefs['text_telephone'], ENT_COMPAT, "UTF-8") : "Phone" ?>:</strong></td>
              <td><?php echo isset($_SESSION['strBillingPhone']) ? $_SESSION['strBillingPhone'] : "";  ?>
               
                <?php $custom = "Delivery address: ";
$custom .= (!isset($_SESSION["bIsDeliverySame"]) || $_SESSION["bIsDeliverySame"]==0) ?  $_SESSION['strDeliveryFirstnames']." ".$_SESSION['strDeliverySurname']."\n\r".$_SESSION['strDeliveryAddress1']."\n\r".$_SESSION['strDeliveryAddress2']."\n\r".$_SESSION['strDeliveryCity']."\n\r".$_SESSION['strDeliveryPostCode']."\n\r".@$rowDeliveryCountry['fullname']."\n\r".@$_SESSION['strBillingPhone'] : "same as billing address";  ?></td>
              <td>&nbsp;</td>
              <td><?php echo isset($_SESSION['strDeliveryPhone']) ? $_SESSION['strDeliveryPhone'] : "";  ?>&nbsp;</td>
            </tr>
          </table>
          
          <p><a href="../index.php" class="link_back"><i class="glyphicon glyphicon-arrow-left"></i> <?php echo isset($row_rsProductPrefs['text_yourdetails']) ? htmlentities($row_rsProductPrefs['text_yourdetails'], ENT_COMPAT, "UTF-8") : "Your details" ; ?></a></p>
          
          <div class="basketnavigation">
            <button type="submit"  class="btn btn-primary makePaymentButton bottom" ><?php echo isset($row_rsProductPrefs['paymenttext']) ? htmlentities($row_rsProductPrefs['paymenttext'], ENT_COMPAT, "UTF-8" ): "Payment"; ?></button>
          </div><?php } // end ask address ?>
           <input type="hidden" name="telephone" id="telephone" value="<?php echo isset($_SESSION['strBillingPhone']) ? $_SESSION['strBillingPhone'] : "";  ?>"/>
           <input type="hidden" name="business" value="<?php echo (isset($row_rsProductPrefs['paypalID']) && $row_rsProductPrefs['paypalID'] !="") ? $row_rsProductPrefs['paypalID'] : $row_rsProductPrefs['paymentclientID']; ?>" />
           <input type="hidden" name="currency_code" value="<?php echo $row_rsThisRegion['currencycode']; ?>" />
            <input type="hidden" name="lc" value="<?php echo isset($row_rsThisRegion['iso2']) ? $row_rsThisRegion['iso2'] : ""; ?>" />
          <input type="hidden" name="return" value="<?php if(strpos($row_rsProductPrefs['successURL'],"http")===false) { echo getProtocol()."://". $_SERVER['HTTP_HOST']; } echo $row_rsProductPrefs['successURL']; ?>?VendorTxCode=<?php echo $strVendorTxCode; ?>" />
          
     
          
          
          
          <input type="hidden" name="cancel_return" value="<?php if(strpos($row_rsProductPrefs['failURL'],"http")===false) { echo getProtocol()."://". $_SERVER['HTTP_HOST']; } echo $row_rsProductPrefs['failURL']; ?>?VendorTxCode=<?php echo $strVendorTxCode; ?>" />
           <input type="hidden" name="notify_url" value="<?php echo getProtocol()."://". $_SERVER['HTTP_HOST']; ?>/products/payments/paypal/ipn.php" />
            <input type="hidden" name="invoice" value="<?php echo $strVendorTxCode; ?>" />   
          
          <!-- Payments Standard/Pro diffreneces-->
           <input type="hidden" name="<?php echo ($row_rsProductPrefs['paymentproviderID']==6) ? "subtotal" : "amount"; ?>" value="<?php echo number_format($grandtotal,2,".",""); ?>" />
          <input type="hidden" name="cmd" value="<?php echo ($row_rsProductPrefs['paymentproviderID']==6) ? "_hosted-payment" : "_xclick"; ?>" />
          <input name="<?php echo ($row_rsProductPrefs['paymentproviderID']==6) ? "buyer_email" : "email"; ?>" type="hidden"  value="<?php echo $_SESSION['strCustomerEMail'];  ?>" />
          <?php $billing =  ($row_rsProductPrefs['paymentproviderID']==6) ? "billing_" : ""; ?>
          <input name="<?php echo $billing; ?>first_name" type="hidden"   value="<?php echo $_SESSION['strBillingFirstnames'];  ?>" />
          <input name="<?php echo $billing; ?>last_name" type="hidden"  value="<?php echo $_SESSION['strBillingSurname'];  ?>" />
            <input name="<?php echo $billing; ?>address1" type="hidden"  value="<?php echo $_SESSION['strBillingAddress1'];  ?>" />
            <input name="<?php echo $billing; ?>address2" type="hidden" value="<?php echo $_SESSION['strBillingAddress2'];  ?>" />
            <input name="<?php echo $billing; ?>city" type="hidden" value="<?php echo $_SESSION['strBillingCity'];  ?>" />
            <input name="<?php echo ($row_rsProductPrefs['paymentproviderID']==6) ? "billing_country" : "country"; ?>" type="hidden" value="<?php echo $billingCountry['iso2']; ?>" />
            <input name="zip" type="hidden" id="zip" value="<?php echo $_SESSION['strBillingPostCode'];  ?>" />
          
          
          <!-- Payments standard only -->
          <input type="hidden" name="on0" value="Items purchased" />
          <input type="hidden" name="os0" value="<?php echo (strlen($basketDescription)<255) ? htmlentities($basketDescription, ENT_COMPAT, "UTF-8") : $totalitems." items" ; ?>" />
          <input type="hidden" name="on1" value="Transaction code" />
          <input type="hidden" name="os1" value="<?php echo $strVendorTxCode; ?>" />
          <input type="hidden" name="item_name" value="<?php echo $row_rsProductPrefs['shopTitle']; ?> goods" />
           <input type="hidden" name="custom" id="custom" value="<?php echo $custom; ?>" />
           <input type="hidden" name="cpp_header_image" id="cpp_header_image" value="<?php echo defined("CPP_HEADER_IMAGE") ? CPP_HEADER_IMAGE : ""; ?>" />
           <input type="hidden" name="cpp_ headerback_color" id="cpp_ headerback_color" value="" />
           <input type="hidden" name="cpp_ headerborder_color" id="cpp_ headerborder_color" value="" />
           <input type="hidden" name="cpp_cart_border_color" id="cpp_cart_border_color" value="" />
           
           <!-- DEPRACATED image_url instead now -->
           
           <input type="hidden" name="image_url" id="image_url" value="<?php echo defined("PAYPAL_IMAGE_URL") ? PAYPAL_IMAGE_URL : ""; ?>" />
           
          
          <!-- PayPal PRO only-->
           
             
          
          
          <!-- currently unused -->
          <input type="hidden" name="item_number" value="" />
          <input type="hidden" name="shipping" value="" />
          
          <!-- force to go to credit card rather than log in-->
          <input type="hidden" name="landing_page" value="billing" />
          
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
