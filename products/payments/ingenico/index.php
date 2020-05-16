<?php require_once('../../../Connections/aquiescedb.php'); ?><?php if(!isset($_SESSION["strBillingCountry"])) {
	$msg = "Your session has expired";
	header("location: /products/basket/index.php?msg=".urlencode($msg)); exit;
} ?><?php require_once('../../includes/productHeader.inc.php'); ?><?php require_once('../../../core/includes/framework.inc.php'); ?>
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
<?php  $pageTitle = "Ingenico Order Summary"; echo $pageTitle." | ".$site_name;?>
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
      <div class="checkout checkout-summary container ingenico"><!--<form action="https://www.sandbox.paypal.com/cgi-bin/webscr" method="post">-->
       
          <div class="crumbs"><div><span class="you_are_in">You are in: </span><a href="/">Home</a><span class="separator">&nbsp;&rsaquo;&nbsp;</span><a href="/products/">Shop</a><span class="separator">&nbsp;&rsaquo;&nbsp;</span><?php echo isset($row_rsProductPrefs['text_yourdetails']) ? htmlentities($row_rsProductPrefs['text_yourdetails'], ENT_COMPAT, "UTF-8") : "Your details" ; ?></div></div><ol class="checkoutprogress">
        <li><a href="#"><?php echo isset($row_rsProductPrefs['text_yourorder']) ? htmlentities($row_rsProductPrefs['text_yourorder'], ENT_COMPAT, "UTF-8") : "Your Order" ?></a></li>
        <li><a href="#"><?php echo isset($row_rsProductPrefs['text_yourdetails']) ? htmlentities($row_rsProductPrefs['text_yourdetails'], ENT_COMPAT, "UTF-8") : "Your details" ; ?></a></li>
        <li class="selected"><a href="#"><?php echo isset($row_rsProductPrefs['paymenttext']) ? htmlentities($row_rsProductPrefs['paymenttext'], ENT_COMPAT, "UTF-8" ): "Payment"; ?></a></li>
        </ol>
          <h1>&nbsp;</h1><?php if(isset($seoPrefs['googleanalyticsecommerce']) && $seoPrefs['googleanalyticsecommerce']==1) $track_ecommerce = true; require_once('../../includes/basketcontents.inc.php');
		
// must go after basket to get shipping total avoid calling basket functions again
require_once('../includes/logtransaction.inc.php');
$strVendorTxCode = logtransaction("","INGENICO");?>
         <h1> <?php echo isset($row_rsProductPrefs['text_ordersummary']) ? htmlentities($row_rsProductPrefs['text_ordersummary'], ENT_COMPAT, "UTF-8") : "Order Summary" ?></h1>
         
           <form method="post" action="https://secure.ogone.com/ncol/test/orderstandard.asp" id="form1" name="form1">
          <div class="basketnavigation">
            <button type="submit"   class="btn btn-primary makePaymentButton top" ><?php echo isset($row_rsProductPrefs['paymenttext']) ? htmlentities($row_rsProductPrefs['paymenttext'], ENT_COMPAT, "UTF-8" ): "Payment"; ?></button>
          </div>
          
          <h2><?php echo isset($row_rsProductPrefs['text_yourdetails']) ? htmlentities($row_rsProductPrefs['text_yourdetails'], ENT_COMPAT, "UTF-8") : "Your Details" ?>:</h2>
          <p><?php echo isset($row_rsProductPrefs['text_email']) ? htmlentities($row_rsProductPrefs['text_email'], ENT_COMPAT, "UTF-8") : "email" ?>: <?php echo $_SESSION['strCustomerEMail'];  ?>
            
          </p>
          <table border="0" cellpadding="2" cellspacing="2" class="form-table">
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
            <button type="submit"   class="btn btn-primary makePaymentButton bottom"><?php echo isset($row_rsProductPrefs['paymenttext']) ? htmlentities($row_rsProductPrefs['paymenttext'], ENT_COMPAT, "UTF-8" ): "Payment"; ?></button>
          </div>
           
<!-- general parameters: see General Payment Parameters --> 
<input type="hidden" name="PSPID" value="<?php echo $row_rsProductPrefs['paymentclientID']; ?>">
<input type="hidden" name="ORDERID" value="<?php echo $strVendorTxCode; ?>">
<input type="hidden" name="AMOUNT" value="<?php echo number_format($grandtotal,2,".",""); ?>">
<input type="hidden" name="CURRENCY" value="<?php echo $row_rsThisRegion['currencycode']; ?>"> <input type="hidden" name="LANGUAGE" value="<?php echo isset($row_rsThisRegion['iso2']) ? $row_rsThisRegion['iso2'] : ""; ?>">
<!-- optional customer details, highly recommended for fraud prevention: see General parameters and optional customer details -->
<input type="hidden" name="CN" value="<?php echo $_SESSION['strBillingFirstnames'];  ?> <?php echo $_SESSION['strBillingSurname'];  ?>">
<input type="hidden" name="EMAIL" value="<?php echo $_SESSION['strCustomerEMail'];  ?>">
<input type="hidden" name="OWNERZIP" value="<?php echo $_SESSION['strBillingPostCode'];  ?>">
<input type="hidden" name="OWNERADDRESS" value="<?php echo $_SESSION['strBillingAddress1'];  ?>">
<input type="hidden" name="OWNERCTY" value="<?php echo $_SESSION['strBillingCity'];  ?>">
<input type="hidden" name="OWNERTOWN" value="<?php echo $_SESSION['strBillingAddress2'];  ?>">
<input type="hidden" name="OWNERTELNO" value="<?php echo isset($_SESSION['strBillingPhone']) ? $_SESSION['strBillingPhone'] : "";  ?>">
<input type="hidden" name="COM" value="<?php echo (strlen($basketDescription)<255) ? htmlentities($basketDescription, ENT_COMPAT, "UTF-8") : $totalitems." items" ; ?>">
 <!-- FEEDBACK REQUEST MUST BE SET UP IN CUSTOMER ADMIN -->
<!-- check before the payment: see SHA-IN signature -->
<input type="hidden" name="SHASIGN" value="">
<!-- layout information: see Look & Feel of the Payment Page --> 
<input type="hidden" name="TITLE" value="">
<input type="hidden" name="BGCOLOR" value="">
<input type="hidden" name="TXTCOLOR" value="">
<input type="hidden" name="TBLBGCOLOR" value="">
<input type="hidden" name="TBLTXTCOLOR" value="">
<input type="hidden" name="BUTTONBGCOLOR" value="">
<input type="hidden" name="BUTTONTXTCOLOR" value="">
<input type="hidden" name="LOGO" value="">
<input type="hidden" name="FONTTYPE" value="">
<!-- dynamic template page: see Look & Feel of the Payment Page --> <input type="hidden" name="TP" value="">
<!-- payment methods/page specifics: see Payment method and payment page specifics 
<input type="hidden" name="PM" value="">
<input type="hidden" name="BRAND" value="">
<input type="hidden" name="WIN3DS" value="">
<input type="hidden" name="PMLIST" value="">
<input type="hidden" name="PMLISTTYPE" value="">-->
<!-- link to your website: see Default reaction 
<input type="hidden" name="HOMEURL" value="">
<input type="hidden" name="CATALOGURL" value="">-->
<!-- post payment parameters: see Redirection depending on the payment result 
<input type="hidden" name="COMPLUS" value="">
<input type="hidden" name="PARAMPLUS" value="">--> 
<!-- post payment parameters: see Direct feedback requests (Post-payment) 
<input type="hidden" name="PARAMVAR" value="">--> 
<!-- post payment redirection: see Redirection depending on the payment result --> <input type="hidden" name="ACCEPTURL" value="<?php if(strpos($row_rsProductPrefs['successURL'],"http")===false) { echo getProtocol()."://". $_SERVER['HTTP_HOST']; } echo $row_rsProductPrefs['successURL']; ?>?VendorTxCode=<?php echo $strVendorTxCode; ?>">
<input type="hidden" name="DECLINEURL" value="<?php if(strpos($row_rsProductPrefs['failURL'],"http")===false) { echo getProtocol()."://". $_SERVER['HTTP_HOST']; } echo $row_rsProductPrefs['failURL']; ?>?VendorTxCode=<?php echo $strVendorTxCode; ?>">
<input type="hidden" name="EXCEPTIONURL" value="<?php if(strpos($row_rsProductPrefs['failURL'],"http")===false) { echo getProtocol()."://". $_SERVER['HTTP_HOST']; } echo $row_rsProductPrefs['failURL']; ?>?VendorTxCode=<?php echo $strVendorTxCode; ?>">
<input type="hidden" name="CANCELURL" value="<?php if(strpos($row_rsProductPrefs['failURL'],"http")===false) { echo getProtocol()."://". $_SERVER['HTTP_HOST']; } echo $row_rsProductPrefs['failURL']; ?>?VendorTxCode=<?php echo $strVendorTxCode; ?>">
<!-- optional operation field: see Operation -->
<input type="hidden" name="OPERATION" value="">
<!-- optional extra login detail field: see User field -->
<input type="hidden" name="USERID" value="">
<!-- Alias details: see Alias Management documentation -->
<input type="hidden" name="ALIAS" value="">
<input type="hidden" name="ALIASUSAGE" value="">
<input type="hidden" name="ALIASOPERATION" value="">
          
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
