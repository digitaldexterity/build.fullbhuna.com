<?php require_once('../../../Connections/aquiescedb.php'); ?><?php if(!isset($_SESSION["strBillingCountry"])) {
	$msg = "Your session has expired";
	header("location: /products/basket/index.php?msg=".urlencode($msg)); exit;
} ?>
<?php require_once('../../includes/productHeader.inc.php'); ?><?php require_once('../../../core/includes/framework.inc.php'); ?>
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
<?php  $pageTitle = "PayTrail "; $pageTitle .= isset($row_rsProductPrefs['text_ordersummary']) ? htmlentities($row_rsProductPrefs['text_ordersummary'], ENT_COMPAT, "UTF-8") : "Order Summary"; echo $pageTitle." | ".$site_name;?>
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
      <div class="checkout checkout-summary container paytrail"><!--<form action="https://www.sandbox.paypal.com/cgi-bin/webscr" method="post">-->
       
          <div class="crumbs"><div><span class="you_are_in">You are in: </span><a href="/">Home</a><span class="separator">&nbsp;&rsaquo;&nbsp;</span><a href="/products/">Shop</a><span class="separator">&nbsp;&rsaquo;&nbsp;</span><?php echo isset($row_rsProductPrefs['text_yourdetails']) ? htmlentities($row_rsProductPrefs['text_yourdetails'], ENT_COMPAT, "UTF-8") : "Your details" ; ?></div></div><ol class="checkoutprogress">
        <li><a href="#"><?php echo isset($row_rsProductPrefs['text_yourorder']) ? htmlentities($row_rsProductPrefs['text_yourorder'], ENT_COMPAT, "UTF-8") : "Your Order" ?></a></li>
        <li><a href="#"><?php echo isset($row_rsProductPrefs['text_yourdetails']) ? htmlentities($row_rsProductPrefs['text_yourdetails'], ENT_COMPAT, "UTF-8") : "Your details" ; ?></a></li>
        <li class="selected"><a href="#"><?php echo isset($row_rsProductPrefs['paymenttext']) ? htmlentities($row_rsProductPrefs['paymenttext'], ENT_COMPAT, "UTF-8" ): "Payment"; ?></a></li>
        </ol>
        <h1> <?php echo isset($row_rsProductPrefs['text_ordersummary']) ? htmlentities($row_rsProductPrefs['text_ordersummary'], ENT_COMPAT, "UTF-8") : "Order Summary" ?></h1><?php if(isset($seoPrefs['googleanalyticsecommerce']) && $seoPrefs['googleanalyticsecommerce']==1) $track_ecommerce = true; require_once('../../includes/basketcontents.inc.php');
		
// must go after basket to get shipping total avoid calling basket functions again
require_once('../includes/logtransaction.inc.php');
$strVendorTxCode = logtransaction("","PAYTRAIL"); $hash = $row_rsProductPrefs['paymentclientpassword']; ?>
<form action="https://payment.paytrail.com/" method="post">
<input name="MERCHANT_ID" type="hidden" value="<?php $value = $row_rsProductPrefs['paymentclientID']; $hash .= "|".$value; echo $value; ?>">
<input name="ORDER_NUMBER" type="hidden" value="<?php $value = $strVendorTxCode; $hash .= "|".$value; echo $value;  ?>">
<input name="REFERENCE_NUMBER" type="hidden" value="<?php  $hash .= "|"; ?>">
<input name="ORDER_DESCRIPTION" type="hidden" value="<?php $value = $row_rsProductPrefs['shopTitle']; $hash .= "|".$value; echo $value;  ?>">
<input name="CURRENCY" type="hidden" value="<?php $value = $row_rsThisRegion['currencycode']; $hash .= "|".$value; echo $value;  ?>">
<input name="RETURN_ADDRESS" type="hidden" value="<?php $value = ""; if(strpos($row_rsProductPrefs['successURL'],"http")===false) { $value =  getProtocol()."://". $_SERVER['HTTP_HOST']; }  $value .= $row_rsProductPrefs['successURL']."?VendorTxCode=".$strVendorTxCode; $hash .= "|".$value; echo $value;  ?>">
<input name="CANCEL_ADDRESS" type="hidden" value="<?php $value = ""; if(strpos($row_rsProductPrefs['failURL'],"http")===false) { $value =  getProtocol()."://". $_SERVER['HTTP_HOST']; }  $value .= $row_rsProductPrefs['failURL']."?VendorTxCode=".$strVendorTxCode; $hash .= "|".$value; echo $value;  ?>">
<input name="PENDING_ADDRESS" type="hidden" value="<?php $hash .="|"; ?>">
<input name="NOTIFY_ADDRESS" type="hidden" value="<?php $value = getProtocol()."://". $_SERVER['HTTP_HOST']."/products/payments/paytrail/notify.php"; $hash .= "|".$value; echo $value; ?>">
<input name="TYPE" type="hidden" value="<?php $value = "E1"; $hash .= "|".$value; echo $value;  ?>" >
<input name="CULTURE" type="hidden" value="<?php $value = "fi_FI"; $hash .= "|".$value; echo $value;  ?>" >
<?php $value = ""; $hash .= "|".$value; echo $value; // preselected payment method  ?>
<input name="MODE" type="hidden" value="<?php $value = "1"; $hash .= "|".$value; echo $value;  ?>" >
<input name="VISIBLE_METHODS" type="hidden" value="<?php $hash.="|"; ?>" >
<input name="GROUP" type="hidden" value="<?php $hash.="|"; ?>" >
<input name="CONTACT_TELNO" type="hidden" value="<?php $value = isset($_SESSION['strBillingPhone']) ? $_SESSION['strBillingPhone'] : "";
$hash .= "|".$value; echo $value; ?>">
<input name="CONTACT_CELLNO" type="hidden" value="<?php $hash .= "|"; ?>">
<input name="CONTACT_EMAIL" type="hidden" value="<?php $value = $_SESSION['strCustomerEMail'];$hash .= "|".$value; echo $value; ?>">
<input name="CONTACT_FIRSTNAME" type="hidden" value="<?php $value= $_SESSION['strBillingFirstnames']; $hash .= "|".$value; echo $value; ?>">
<input name="CONTACT_LASTNAME" type="hidden" value="<?php $value = $_SESSION['strBillingSurname']; $hash .= "|".$value; echo $value;?>">
<input name="CONTACT_COMPANY" type="hidden" value="<?php $value = ""; $hash .= "|".$value; echo $value;?>">

<input name="CONTACT_ADDR_STREET" type="hidden" value="<?php $value =  $_SESSION['strBillingAddress1']." ".$_SESSION['strBillingAddress2']; $hash .= "|".$value; echo $value;?>">
<input name="CONTACT_ADDR_ZIP" type="hidden" value="<?php $value = $_SESSION['strBillingPostCode']; $hash .= "|".$value; echo $value;  ?>">
<input name="CONTACT_ADDR_CITY" type="hidden" value="<?php $value = $_SESSION['strBillingCity']; $hash .= "|".$value; echo $value;  ?>">
<input name="CONTACT_ADDR_COUNTRY" type="hidden" value="<?php $value =  $billingCountry['iso2']; $hash .= "|".$value; echo $value; ?>">
<input name="INCLUDE_VAT" type="hidden" value="<?php $vatinc = ($item['vatdefault']==0) ? $item['vatincluded'] : $row_rsProductPrefs['vatincluded']; $value = $vatinc; $hash .= "|".$value; echo $value;  ?>">
<?php 
$html = ""; $key = count($items); $subhash = "";
// promos 

if(is_array($discounts)) {
		
	foreach($discounts as $key2 => $discount) { // from basket
			   if(isset($discount['amount']) ) {  

$html .="<input name=\"ITEM_TITLE[".$key."]\" type=\"hidden\" value=\""; $value = $discount['name']; $subhash .= "|".$value; $html .= $value."\">";

$html .="<input name=\"ITEM_NO[".$key."]\" type=\"hidden\" value=\"";$value = strval(10000+$key); $subhash .= "|".$value; $html .= $value."\">";

$html .="<input name=\"ITEM_AMOUNT[".$key."]\" type=\"hidden\" value=\""; $value = "1"; $subhash .= "|".$value; $html .= $value."\">";

$html .="<input name=\"ITEM_PRICE[".$key."]\" type=\"hidden\" value=\""; $promoamount = vatPrices($discount['amount'],$row_rsProductPrefs['vatincluded'],$row_rsThisRegion['vatrate']); $value = number_format($discount['amount'],2,".","")*-1; $subhash .= "|".$value; $html.= $value."\">";

$html .="<input name=\"ITEM_TAX[".$key."]\" type=\"hidden\" value=\""; $value = number_format($row_rsThisRegion['vatrate'],2,".",","); $subhash .= "|".$value; $html .= $value."\">";

$html .="<input name=\"ITEM_DISCOUNT[".$key."]\" type=\"hidden\" value=\"";  $value = "0"; $subhash .= "|".$value; $html .= $value."\">";

$html .="<input name=\"ITEM_TYPE[".$key."]\" type=\"hidden\" value=\""; $value = "1"; $subhash .= "|".$value; $html .= $value."\">";

 $key++; }
	}
	}
	
	
	
	// shipping


if(is_array($shipping)) {  // from basket
			 foreach($shipping as $key2 => $shippingitem) { 


$html .="<input name=\"ITEM_TITLE[".$key."]\" type=\"hidden\" value=\""; $value = $shippingitem['name'] ; $subhash .= "|".$value; $html .= $value."\">";

$html .="<input name=\"ITEM_NO[".$key."]\" type=\"hidden\" value=\""; $value = strval(20000+$key); $subhash .= "|".$value; $html .= $value."\">";

$html .="<input name=\"ITEM_AMOUNT[".$key."]\" type=\"hidden\" value=\""; $value = "1" ; $subhash .= "|".$value; $html .= $value."\">";

$html .="<input name=\"ITEM_PRICE[".$key."]\" type=\"hidden\" value=\""; $value = number_format($shippingitem['amount'],2,".",""); $subhash .= "|".$value; $html .= $value."\">";

$html .="<input name=\"ITEM_TAX[".$key."]\" type=\"hidden\" value=\""; $value = number_format($row_rsThisRegion['vatrate'],2,".",","); $subhash .= "|".$value; $html .= $value."\">";

$html .="<input name=\"ITEM_DISCOUNT[".$key."]\" type=\"hidden\" value=\""; $value = "0"; $subhash .= "|".$value; $html .= $value."\">";

$html .="<input name=\"ITEM_TYPE[".$key."]\" type=\"hidden\" value=\""; $value = "1"; $subhash .= "|".$value; $html .= $value."\">";

$key++; }
}


?>



<input name="ITEMS" type="hidden" value="<?php $value = $key; $hash .= "|".$value; echo $value;  ?>">


<?php 



foreach($items as $key2 => $item) {
			
			
			 ?>
<input name="ITEM_TITLE[<?php echo $key2-1; ?>]" type="hidden" value="<?php $value = $item['title']; $hash .= "|".$value; echo $value;  ?>">
<input name="ITEM_NO[<?php echo $key2-1; ?>]" type="hidden" value="<?php $value = $item['sku']; $hash .= "|".$value; echo $value;  ?>">
<input name="ITEM_AMOUNT[<?php echo $key2-1; ?>]" type="hidden" value="<?php $value = $item['quantity']; $hash .= "|".$value; echo $value;  ?>">
<input name="ITEM_PRICE[<?php echo $key2-1; ?>]" type="hidden" value="<?php $value = number_format($item['price'],2,".",""); $hash .= "|".$value; echo $value;  ?>">
<input name="ITEM_TAX[<?php echo $key2-1; ?>]" type="hidden" value="<?php $value = number_format($item['ratepercent'],2,".",","); $hash .= "|".$value; echo $value; ?>">
<input name="ITEM_DISCOUNT[<?php echo $key2-1; ?>]" type="hidden" value="<?php $value = "0"; $hash .= "|".$value; echo $value;  ?>">
<input name="ITEM_TYPE[<?php echo $key2-1; ?>]" type="hidden" value="<?php $value = "1"; $hash .= "|".$value; echo $value;  ?>">
<?php  } // end for each





echo $html; $hash .= $subhash;

$hash = strtoupper(md5($hash)); 
 ?>
<input name="AUTHCODE" type="hidden" value="<?php echo $hash; ?>">

          <div class="basketnavigation">
             <button type="submit"  class="btn btn-primary makePaymentButton top" ><?php echo isset($row_rsProductPrefs['paymenttext']) ? htmlentities($row_rsProductPrefs['paymenttext'], ENT_COMPAT, "UTF-8" ): "Payment"; ?></button>
          </div>
          
         <h2><?php echo isset($row_rsProductPrefs['text_yourdetails']) ? htmlentities($row_rsProductPrefs['text_yourdetails'], ENT_COMPAT, "UTF-8") : "Your Details" ?>:</h2>
          <p><?php echo isset($row_rsProductPrefs['text_email']) ? htmlentities($row_rsProductPrefs['text_email'], ENT_COMPAT, "UTF-8") : "email" ?>: <?php echo $_SESSION['strCustomerEMail'];  ?> </p>
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
              <td><?php if(isset($_SESSION["bIsDeliverySame"]) && $_SESSION["bIsDeliverySame"]==0) {
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
          </div>
         
          
        </form>
         <?php echo isset($row_rsProductPrefs['checkoutconfirmfooter']) ? htmlentities($row_rsProductPrefs['checkoutconfirmfooter'], ENT_COMPAT, "UTF-8") : ""; ?>
      </div>
      <div id="paymentProviderBadge">
       
      </div></section>
      <!-- InstanceEndEditable --></main>
<?php require_once('../../../local/includes/footer.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsThisRegion);

mysql_free_result($rsProductPrefs);
?>
