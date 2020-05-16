<?php require_once('../../../Connections/aquiescedb.php'); ?><?php require_once('../../../core/includes/framework.inc.php'); ?>
<?php 

// TEST back office : https://mdepayments.epdq.co.uk/ncol/Test/backoffice/container/index?branding=EPDQ&lang=1
		  
		  // TEST card number 4111111111111111 any CVC and future date
		  
		  
if(!isset($_SESSION["strBillingCountry"])) {
	$msg = "Your session has expired";
	header("location: /products/basket/index.php?msg=".urlencode($msg)); exit;
}

?><?php require_once('../../includes/productHeader.inc.php'); ?>
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


if ($row_rsProductPrefs['shopstatus']==1) { 
	$payment_url = "https://payments.epdq.co.uk/ncol/prod/orderstandard.asp";
} else {
	$payment_url = "https://mdepayments.epdq.co.uk/ncol/test/orderstandard.asp";
}



$password =   $row_rsProductPrefs['paymentclientpassword']; 
/* TEST SCRIPT
$payment_url = "index.php";
if(isset($_POST["CN"])) { 
ksort($_POST);$str="";
foreach($_POST as $key => $value) {
	echo $key ."=>".$value."<br>";
		
		if($key!="SHASIGN") {
			$str.= $key."=".$value.$password;
		}
	
	}
	echo "<br><br><br>".$str."<br><br><br>";
	$sha1= sha1($str);
	echo $_POST['SHASIGN'] ."=".$sha1;
die();

}
*/



$select = "SELECT fullname, iso2 FROM countries WHERE ID = ".$_SESSION["strBillingCountry"];
	$result = mysql_query($select, $aquiescedb) or die(mysql_error().": ".$select);
	$billingCountry = mysql_fetch_assoc($result);
	
	if(isset($_SESSION["strDeliveryCountry"]) && intval($_SESSION["strDeliveryCountry"])>0) {
		$select = "SELECT fullname, iso2 FROM countries WHERE ID = ".$_SESSION["strDeliveryCountry"];
	$result = mysql_query($select, $aquiescedb) or die(mysql_error().": ".$select);
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
<?php  $pageTitle = "Barclays Order Summary"; echo $pageTitle." | ".$site_name;?>
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
      <div class="checkout checkout-summary container barclays"><!--<form action="https://www.sandbox.paypal.com/cgi-bin/webscr" method="post">-->
       
          <div class="crumbs"><div><span class="you_are_in">You are in: </span><a href="/">Home</a><span class="separator">&nbsp;&rsaquo;&nbsp;</span><a href="/products/">Shop</a><span class="separator">&nbsp;&rsaquo;&nbsp;</span><?php echo isset($row_rsProductPrefs['text_yourdetails']) ? htmlentities($row_rsProductPrefs['text_yourdetails'], ENT_COMPAT, "UTF-8") : "Your details" ; ?></div></div><ol class="checkoutprogress">
        <li><a href="#"><?php echo isset($row_rsProductPrefs['text_yourorder']) ? htmlentities($row_rsProductPrefs['text_yourorder'], ENT_COMPAT, "UTF-8") : "Your Order" ?></a></li>
        <li><a href="#"><?php echo isset($row_rsProductPrefs['text_yourdetails']) ? htmlentities($row_rsProductPrefs['text_yourdetails'], ENT_COMPAT, "UTF-8") : "Your details" ; ?></a></li>
        <li class="selected"><a href="#"><?php echo isset($row_rsProductPrefs['paymenttext']) ? htmlentities($row_rsProductPrefs['paymenttext'], ENT_COMPAT, "UTF-8" ): "Payment"; ?></a></li>
        </ol>
          <h1>&nbsp;</h1><?php if(isset($seoPrefs['googleanalyticsecommerce']) && $seoPrefs['googleanalyticsecommerce']==1) $track_ecommerce = true; require_once('../../includes/basketcontents.inc.php');
		
// must go after basket to get shipping total avoid calling basket functions again
require_once('../includes/logtransaction.inc.php');
$strVendorTxCode = logtransaction("","BARCLAYS");?>
         <h1> <?php echo isset($row_rsProductPrefs['text_ordersummary']) ? htmlentities($row_rsProductPrefs['text_ordersummary'], ENT_COMPAT, "UTF-8") : "Order Summary" ?></h1>
         <form method="post" action="<?php echo $payment_url; ?>" id="form1" name="form1">
          <div class="basketnavigation">
            <button type="submit"  class="btn btn-primary makePaymentButton top" ><?php echo isset($row_rsProductPrefs['paymenttext']) ? htmlentities($row_rsProductPrefs['paymenttext'], ENT_COMPAT, "UTF-8" ): "Payment"; ?></button>
          </div>
          
          <h2><?php echo isset($row_rsProductPrefs['text_yourdetails']) ? htmlentities($row_rsProductPrefs['text_yourdetails'], ENT_COMPAT, "UTF-8") : "Your Details" ?>:</h2>
          <p><?php echo isset($row_rsProductPrefs['text_email']) ? htmlentities($row_rsProductPrefs['text_email'], ENT_COMPAT, "UTF-8") : "email" ?>: <?php echo $_SESSION['strCustomerEMail'];  ?>
            
          </p>
          <table  class="form-table">
            <tr>
            
              <td><h2><?php echo isset($row_rsProductPrefs['text_billingdetails']) ? htmlentities($row_rsProductPrefs['text_billingdetails'], ENT_COMPAT, "UTF-8") : "Billing details" ?>:</h2></td>  <td >&nbsp;</td>
            </tr>
            <tr>
              <td ><strong><?php echo isset($row_rsProductPrefs['text_firstname']) ? htmlentities($row_rsProductPrefs['text_firstname'], ENT_COMPAT, "UTF-8") : "First Name(s)" ?>/<?php echo isset($row_rsProductPrefs['text_surname']) ? htmlentities($row_rsProductPrefs['text_surname'], ENT_COMPAT, "UTF-8") : "Surname" ?>:</strong></td>
              <td><?php echo $_SESSION['strBillingFirstnames'];  ?> <?php echo $_SESSION['strBillingSurname'];  ?>
                
              </td>
            </tr>
            <tr>
              <td ><strong><?php echo isset($row_rsProductPrefs['text_address']) ? htmlentities($row_rsProductPrefs['text_address'], ENT_COMPAT, "UTF-8") : "Address" ?>:</strong></td>
              <td><?php echo $_SESSION['strBillingAddress1'];  ?>
              </td>
            </tr>
            <tr>
              <td >&nbsp;</td>
              <td><?php echo $_SESSION['strBillingAddress2'];  ?>
              </td>
            </tr>
            <tr>
              <td ><strong><?php echo isset($row_rsProductPrefs['text_city']) ? htmlentities($row_rsProductPrefs['text_city'], ENT_COMPAT, "UTF-8") : "City" ?>:</strong></td>
              <td><?php echo $_SESSION['strBillingCity'];  ?>
              </td>
            </tr>
            <tr>
              <td ><strong><?php echo isset($row_rsProductPrefs['text_postcode']) ? htmlentities($row_rsProductPrefs['text_postcode'], ENT_COMPAT, "UTF-8") : "Post/ZIP code" ?>:</strong></td>
              <td><?php echo $_SESSION['strBillingPostCode'];  ?>
              </td>
            </tr>
            <tr>
              <td ><strong><?php echo isset($row_rsProductPrefs['text_country']) ? htmlentities($row_rsProductPrefs['text_country'], ENT_COMPAT, "UTF-8") : "Country" ?>:</strong></td>
              <td><?php echo $billingCountry['fullname']; ?>
              </td>
            </tr>
            <tr>
              <td ><strong><?php echo isset($row_rsProductPrefs['text_telephone']) ? htmlentities($row_rsProductPrefs['text_telephone'], ENT_COMPAT, "UTF-8") : "Phone" ?>:</strong></td>
              <td><?php echo isset($_SESSION['strBillingPhone']) ? $_SESSION['strBillingPhone'] : "";  ?>
               
                <?php $custom = "Delivery address: ";
$custom .= (!isset($_SESSION["bIsDeliverySame"]) || $_SESSION["bIsDeliverySame"]==0) ?  $_SESSION['strDeliveryFirstnames']." ".$_SESSION['strDeliverySurname']."\n\r".$_SESSION['strDeliveryAddress1']."\n\r".$_SESSION['strDeliveryAddress2']."\n\r".$_SESSION['strDeliveryCity']."\n\r".$_SESSION['strDeliveryPostCode']."\n\r".@$rowDeliveryCountry['fullname']."\n\r".@$_SESSION['strBillingPhone'] : "same as billing address";  ?></td>
            </tr>
          </table>
          
          
          
          <table  class="form-table">
            <tr>
              <td><h2><?php echo isset($row_rsProductPrefs['text_deliverydetails']) ? htmlentities($row_rsProductPrefs['text_deliverydetails'], ENT_COMPAT, "UTF-8") : "Delivery details" ?>:</h2></td>
            </tr>
            <tr>
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
          <p><a href="../index.php" class="link_back"><i class="glyphicon glyphicon-arrow-left"></i> <?php echo isset($row_rsProductPrefs['text_yourdetails']) ? htmlentities($row_rsProductPrefs['text_yourdetails'], ENT_COMPAT, "UTF-8") : "Your details" ; ?></a></p>
          
          <div class="basketnavigation">
            <button type="submit"   class="btn btn-primary makePaymentButton bottom"><?php echo isset($row_rsProductPrefs['paymenttext']) ? htmlentities($row_rsProductPrefs['paymenttext'], ENT_COMPAT, "UTF-8" ): "Payment"; ?></button>
          </div>
          <?php $successURL = "";
		  if(substr($row_rsProductPrefs['successURL'],0,4)!=="http" ) { 
		  $successURL .= getProtocol()."://".$_SERVER['HTTP_HOST']; 
		  } 
		  $successURL .= $row_rsProductPrefs['successURL']."?VendorTxCode=".$strVendorTxCode; 
		  
		  $cancelURL = "";
		  if(substr($row_rsProductPrefs['failURL'],0,4)!=="http" ) { 
		  $cancelURL .= getProtocol()."://".$_SERVER['HTTP_HOST']; 
		  } 
		  $cancelURL .= $row_rsProductPrefs['failURL']."?VendorTxCode=".$strVendorTxCode;
		  
		  
		  /* PARAMETERS IN ALPHABETICAL ORDER - NO EMPTY VALUES SHOUDL BE INCLUDED */
		  
		  
		  
		  //$bgcolor= "#FFC000";
		   $title= " ";
		  $logo = "https://www.121officefurniture.co.uk/local/images/121-Barclays-Header.gif";
		   
		  $str  = "ACCEPTURL=". $successURL.$password;
		 
		 
          $str  .= "AMOUNT=".($grandtotal*100).$password;
		  
		 
		  
		  $str  .=  (isset($bgcolor) && strlen($bgcolor)) ? "BGCOLOR=".$bgcolor.$password : "";
		    $str  .= "CANCELURL=". $cancelURL.$password;
		  $str  .="CN=".$_SESSION['strBillingFirstnames']." ".$_SESSION['strBillingSurname'].$password;
		  $str  .= "CURRENCY=".$row_rsThisRegion['currencycode'].$password;
		   $str  .= "DECLINEURL=". $cancelURL.$password;
		
		  $str  .= "EMAIL=". $_SESSION['strCustomerEMail'].$password;
		    $str  .= "EXCEPTIONURL=". $cancelURL.$password;
			
		  $str  .="LANGUAGE=en_US".$password;
		  
		  
		  
		  $str  .= (isset($logo) && strlen($logo)>0) ? "LOGO=".trim($logo).$password : "";
		    $str  .="ORDERID=".$strVendorTxCode.$password;
		  $str  .= "OWNERADDRESS=". $_SESSION['strBillingAddress1']." ".$_SESSION['strBillingAddress2'].$password;
		  $str  .= "OWNERCTY=". $billingCountry['iso2'].$password;
		  $str  .= "OWNERTELNO=". $_SESSION['strBillingPhone'].$password;
		  $str  .= "OWNERTOWN=". $_SESSION['strBillingCity'].$password;
		  $str  .= "OWNERZIP=". $_SESSION['strBillingPostCode'].$password;
		  
		
		  
		  
		
		  $str  .="PSPID=".$row_rsProductPrefs['paymentclientID'].$password;
		   $str  .= (isset($title) && strlen($title)>0) ? "TITLE=".$title.$password : "";
		  
		 $sha1 =  (sha1($str)); //echo "STR=".$str;
		 
		 ?>
           
<!-- general parameters -->
<input type="hidden" name="PSPID" value="<?php echo  $row_rsProductPrefs['paymentclientID']; ?>">
<input type="hidden" name="ORDERID" value="<?php echo $strVendorTxCode; ?>">
<input type="hidden" name="AMOUNT" value="<?php echo ($grandtotal*100); ?>">
<input type="hidden" name="CURRENCY" value="<?php echo $row_rsThisRegion['currencycode']; ?>">
<input type="hidden" name="LANGUAGE" value="en_US">
<input type="hidden" name="CN" value="<?php echo $_SESSION['strBillingFirstnames']." ".$_SESSION['strBillingSurname'];  ?>">

<input type="hidden" name="EMAIL" value="<?php echo $_SESSION['strCustomerEMail'];  ?>">
<input type="hidden" name="OWNERZIP" value="<?php echo $_SESSION['strBillingPostCode'];  ?>">
<input type="hidden" name="OWNERADDRESS" value="<?php echo $_SESSION['strBillingAddress1']." ".$_SESSION['strBillingAddress2'];  ?>">
<input type="hidden" name="OWNERCTY" value="<?php echo $billingCountry['iso2']; ?>">
<input type="hidden" name="OWNERTOWN" value="<?php echo $_SESSION['strBillingCity'];  ?>">
<input type="hidden" name="OWNERTELNO" value="<?php echo isset($_SESSION['strBillingPhone']) ? $_SESSION['strBillingPhone'] : "";  ?>">
<!-- check before the payment: see Security: Check before the payment --> 
<input type="hidden" name="SHASIGN" value="<?php echo $sha1; ?>">


<?php //mail("giganticego@gmail.com", "BARCLAYS TEST", $row_rsProductPrefs['paymentclientID']."\n\n".$strVendorTxCode."\n\n".$str."\n\n".$sha1); ?> 
<!-- layout information: see Look and feel of the payment page  -->


<?php if(isset($logo) && strlen($logo)>0) { ?>
<input type="hidden" name="LOGO" value="<?php echo $logo; ?>"><?php } ?>
<?php if(isset($bgcolor) && strlen($bgcolor)>0) { ?>
<input type="hidden" name="BGCOLOR" value="<?php echo $bgcolor; ?>">
<?php } ?>
<?php if(isset($title) && strlen($title)>0) { ?>
<input type="hidden" name="TITLE" value="<?php echo $title; ?>">
<?php } ?>
<input type="hidden" name="TXTCOLOR" value="">
<input type="hidden" name="TBLBGCOLOR" value="">
<input type="hidden" name="TBLTXTCOLOR" value="">
<input type="hidden" name="BUTTONBGCOLOR" value="">
<input type="hidden" name="BUTTONTXTCOLOR" value="">

<input type="hidden" name="FONTTYPE" value="">
<!-- post payment redirection: see Transaction feedback to the customer --> <input type="hidden" name="ACCEPTURL" value="<?php echo $successURL; ?>">
<input type="hidden" name="DECLINEURL" value="<?php echo $cancelURL; ?>">
<input type="hidden" name="EXCEPTIONURL" value="<?php echo $cancelURL; ?>">
<input type="hidden" name="CANCELURL" value="<?php echo $cancelURL; ?>">
          
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
