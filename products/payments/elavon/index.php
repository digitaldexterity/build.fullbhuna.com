<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php require_once('../../includes/productHeader.inc.php'); ?><?php require_once('../../../core/includes/framework.inc.php'); ?>
<?php

/* https://developer.elavonpaymentgateway.com/#!/hpp */

if(!isset($_SESSION["strBillingCountry"])) {
	$msg = "Your session has expired";
	header("location: /products/basket/index.php?msg=".urlencode($msg)); exit;
}


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

/*
$select = "SELECT fullname, iso2 FROM countries WHERE country_id = ".$_SESSION["strBillingCountry"];
	$result = mysql_query($select, $aquiescedb) or die(mysql_error());
	$billingCountry = mysql_fetch_assoc($result);
	
	if(isset($_SESSION["strDeliveryCountry"]) && intval($_SESSION["strDeliveryCountry"])>0) {
		$select = "SELECT fullname, iso2 FROM countries WHERE country_id = ".$_SESSION["strDeliveryCountry"];
	$result = mysql_query($select, $aquiescedb) or die(mysql_error());
	$rowDeliveryCountry = mysql_fetch_assoc($result);
}
*/
if ($row_rsProductPrefs['shopstatus']==1) { 
	$payment_url = "https://pay.elavonpaymentgateway.com/pay" ;

} else {
	$payment_url = "https://pay.sandbox.elavonpaymentgateway.com/pay";
}

$body_class ="checkout ordersummary";
$shared_secret = "95oHbot7rt";

?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!DOCTYPE html>

<html class="" lang="en"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Elavon Payment"; echo $pageTitle." | ".$site_name; ?>
</title>
<!--[if IE]><![endif]-->
<!-- InstanceEndEditable -->
<!--[if IE]><![endif]-->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<style>
<!--
-->
</style>
<!-- InstanceEndEditable -->
</head>
<body id="OffTemplate" class="bootstrap <?php echo $body_class;  ?>">
<?php require_once('../../../local/includes/header.inc.php'); ?>
<main id="content"><!-- InstanceBeginEditable name="Body" --><section>
      <div class="checkout checkout-summary container elavon">
    <h2><?php echo isset($row_rsProductPrefs['text_yourdetails']) ? htmlentities($row_rsProductPrefs['text_yourdetails'], ENT_COMPAT, "UTF-8") : "Your Details" ?>:</h2>
    <?php require_once('../../includes/basketcontents.inc.php');
		
// must go after basket to get shipping total avoid calling basket functions again
require_once('../includes/logtransaction.inc.php');
$strVendorTxCode = logtransaction("","ELAVON");
?>
          <p><?php echo isset($row_rsProductPrefs['text_email']) ? htmlentities($row_rsProductPrefs['text_email'], ENT_COMPAT, "UTF-8") : "email" ?>: <?php echo $_SESSION['strCustomerEMail'];  ?>
            
          </p>
          <table border="0" cellspacing="2" cellpadding="2">
            <tr>
              <td align="right">&nbsp;</td>
              <td><strong><?php echo isset($row_rsProductPrefs['text_billingdetails']) ? htmlentities($row_rsProductPrefs['text_billingdetails'], ENT_COMPAT, "UTF-8") : "Billing details" ?>:</strong></td>
              <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
              <td><strong><?php echo isset($row_rsProductPrefs['text_deliverydetails']) ? htmlentities($row_rsProductPrefs['text_deliverydetails'], ENT_COMPAT, "UTF-8") : "Delivery details" ?>:</strong></td>
            </tr>
            <tr>
              <td align="right"><strong><?php echo isset($row_rsProductPrefs['text_firstname']) ? htmlentities($row_rsProductPrefs['text_firstname'], ENT_COMPAT, "UTF-8") : "First Name(s)" ?>/<?php echo isset($row_rsProductPrefs['text_surname']) ? htmlentities($row_rsProductPrefs['text_surname'], ENT_COMPAT, "UTF-8") : "Surname" ?>:</strong></td>
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
              <td align="right"><strong><?php echo isset($row_rsProductPrefs['text_address']) ? htmlentities($row_rsProductPrefs['text_address'], ENT_COMPAT, "UTF-8") : "Address" ?>:</strong></td>
              <td><?php echo $_SESSION['strBillingAddress1'];  ?>
              </td>
              <td>&nbsp;</td>
              <td><?php echo (!isset($_SESSION["bIsDeliverySame"]) || $_SESSION["bIsDeliverySame"]==0) ? $_SESSION['strDeliveryAddress1'] : "";  ?></td>
            </tr>
            <tr>
              <td align="right">&nbsp;</td>
              <td><?php echo $_SESSION['strBillingAddress2'];  ?>
              </td>
              <td>&nbsp;</td>
              <td><?php echo (!isset($_SESSION["bIsDeliverySame"]) || $_SESSION["bIsDeliverySame"]==0) ? $_SESSION['strDeliveryAddress2'] : "";  ?></td>
            </tr>
            <tr>
              <td align="right"><strong><?php echo isset($row_rsProductPrefs['text_city']) ? htmlentities($row_rsProductPrefs['text_city'], ENT_COMPAT, "UTF-8") : "City" ?>:</strong></td>
              <td><?php echo $_SESSION['strBillingCity'];  ?>
              </td>
              <td>&nbsp;</td>
              <td><?php echo (!isset($_SESSION["bIsDeliverySame"]) || $_SESSION["bIsDeliverySame"]==0) ? $_SESSION['strDeliveryCity'] : "";  ?></td>
            </tr>
            <tr>
              <td align="right"><strong><?php echo isset($row_rsProductPrefs['text_postcode']) ? htmlentities($row_rsProductPrefs['text_postcode'], ENT_COMPAT, "UTF-8") : "Post/ZIP code" ?>:</strong></td>
              <td><?php echo $_SESSION['strBillingPostCode'];  ?>
              </td>
              <td>&nbsp;</td>
              <td><?php echo (!isset($_SESSION["bIsDeliverySame"]) || $_SESSION["bIsDeliverySame"]==0) ? $_SESSION['strDeliveryPostCode'] : "";  ?></td>
            </tr>
            <tr>
              <td align="right"><strong><?php echo isset($row_rsProductPrefs['text_country']) ? htmlentities($row_rsProductPrefs['text_country'], ENT_COMPAT, "UTF-8") : "Country" ?>:</strong></td>
              <td><?php echo $billingCountry['fullname']; ?>
              </td>
              <td>&nbsp;</td>
              <td><?php echo (!isset($_SESSION["bIsDeliverySame"]) || $_SESSION["bIsDeliverySame"]==0) ? @$rowDeliveryCountry['fullname'] : "";  ?></td>
            </tr>
            <tr>
              <td align="right"><strong><?php echo isset($row_rsProductPrefs['text_telephone']) ? htmlentities($row_rsProductPrefs['text_telephone'], ENT_COMPAT, "UTF-8") : "Phone" ?>:</strong></td>
              <td><?php echo isset($_SESSION['strBillingPhone']) ? $_SESSION['strBillingPhone'] : "";  ?>
               
                <?php $custom = "Delivery address: ";
$custom .= (!isset($_SESSION["bIsDeliverySame"]) || $_SESSION["bIsDeliverySame"]==0) ?  $_SESSION['strDeliveryFirstnames']." ".$_SESSION['strDeliverySurname']."\n\r".$_SESSION['strDeliveryAddress1']."\n\r".$_SESSION['strDeliveryAddress2']."\n\r".$_SESSION['strDeliveryCity']."\n\r".$_SESSION['strDeliveryPostCode']."\n\r".@$rowDeliveryCountry['fullname']."\n\r".@$_SESSION['strBillingPhone'] : "same as billing address";  ?></td>
              <td>&nbsp;</td>
              <td><?php echo isset($_SESSION['strDeliveryPhone']) ? $_SESSION['strDeliveryPhone'] : "";  ?>&nbsp;</td>
            </tr>
          </table>
          <p><a href="../../customerDetails.php" class="link_back"><?php echo isset($row_rsProductPrefs['text_yourdetails']) ? htmlentities($row_rsProductPrefs['text_yourdetails'], ENT_COMPAT, "UTF-8") : "Your details" ; ?></a></p>
          <form method="POST" action="<?php echo $payment_url; ?>">
<input type="hidden" name="TIMESTAMP" value="<?php $timestamp =  date('YmdHis'); echo $timestamp; ?>">
<input type="hidden" name="MERCHANT_ID" value="<?php $merchantid = "allaig"; echo $merchantid; ?>">
<input type="hidden" name="ACCOUNT" value="internet">
<input type="hidden" name="ORDER_ID" value="<?php $orderid = $strVendorTxCode; echo $orderid; ?>">
<input type="hidden" name="AMOUNT" value="<?php $amount = $grandtotal*100; echo $amount; ?>">
<input type="hidden" name="CURRENCY" value="<?php $currency = "GBP"; echo $currency; ?>">
<input type="hidden" name="SHA1HASH" value="<?php $prehash = $timestamp.".".$merchantid.".".$orderid.".".$amount.".".$currency; $hash1 = sha1($prehash); $hash2 = sha1($hash1.".".$shared_secret); echo $hash2;?>"><?php //echo "HASH=".$prehash."<BR>HASH1=".$hash1."<BR>HASH2=".$hash2; ?>
<input type="hidden" name="MERCHANT_RESPONSE_URL" value="<?php echo getProtocol()."://".$_SERVER['HTTP_HOST']; ?>/products/payments/elavon/response.php">
<input type="hidden" name="AUTO_SETTLE_FLAG" value="1">
<!--
<input type="hidden" name="CHANNEL" value="ECOM">
<input type="hidden" name="COMMENT1" value="Mobile Channel">
<input type="hidden" name="COMMENT2" value="Down Payment">
<input type="hidden" name="SHIPPING_CODE" value="E77|4QJ">
<input type="hidden" name="SHIPPING_CO" value="GB">
<input type="hidden" name="BILLING_CODE" value="R90|ZQ7">
<input type="hidden" name="BILLING_CO" value="GB">
<input type="hidden" name="CUST_NUM" value="332a85b">
<input type="hidden" name="VAR_REF" value="Invoice 7564a">
<input type="hidden" name="PROD_ID" value="SKU1000054">
<input type="hidden" name="HPP_LANG" value="EN">
<input type="hidden" name="HPP_VERSION" value="2">

<input type="hidden" name="CARD_PAYMENT_BUTTON" value="Pay Now">
<input type="hidden" name="SUPPLEMENTARY_DATA" value="Custom Value">-->
<input type="submit" value="Click To Pay">
</form></div></section><!-- InstanceEndEditable --></main>
<?php require_once('../../../local/includes/footer.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>