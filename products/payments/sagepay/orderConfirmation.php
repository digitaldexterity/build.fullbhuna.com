<?php require_once('../../../Connections/aquiescedb.php'); ?><?php require_once('../../includes/productHeader.inc.php'); ?>
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
$query_rsProductPrefs = "SELECT * FROM productprefs WHERE ID = ".GetSQLValueString($colname_rsThisRegion, "int");
$rsProductPrefs = mysql_query($query_rsProductPrefs, $aquiescedb) or die(mysql_error());
$row_rsProductPrefs = mysql_fetch_assoc($rsProductPrefs);
$totalRows_rsProductPrefs = mysql_num_rows($rsProductPrefs);

$colname_rsBillingCountry = "-1";
if (isset($_SESSION['strBillingCountry'])) {
  $colname_rsBillingCountry = $_SESSION['strBillingCountry'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsBillingCountry = sprintf("SELECT fullname FROM countries WHERE ID = %s", GetSQLValueString($colname_rsBillingCountry, "int"));
$rsBillingCountry = mysql_query($query_rsBillingCountry, $aquiescedb) or die(mysql_error());
$row_rsBillingCountry = mysql_fetch_assoc($rsBillingCountry);
$totalRows_rsBillingCountry = mysql_num_rows($rsBillingCountry);

$colname_rsDeliveryCountry = "-1";
if (isset($_SESSION['strDeliveryCountry'])) {
  $colname_rsDeliveryCountry = $_SESSION['strDeliveryCountry'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsDeliveryCountry = sprintf("SELECT fullname FROM countries WHERE ID = %s", GetSQLValueString($colname_rsDeliveryCountry, "int"));
$rsDeliveryCountry = mysql_query($query_rsDeliveryCountry, $aquiescedb) or die(mysql_error());
$row_rsDeliveryCountry = mysql_fetch_assoc($rsDeliveryCountry);
$totalRows_rsDeliveryCountry = mysql_num_rows($rsDeliveryCountry);
?>
<?php include("includes.php");
/**************************************************************************************************
* Sage Pay Server PHP Kit Order Confirmation Page
***************************************************************************************************

***************************************************************************************************
* Change history
* ==============
*
* 02/04/2009 - Simon Wolfe - Updated UI for re-brand
* 11/02/2009 - Simon Wolfe - Updated for VSP protocol 2.23
* 18/12/2007 - Nick Selby - New PHP version adapted from ASP
***************************************************************************************************
* Description
* ===========
*
* Displays a summary of the order items and customer details and builds the Sage Pay Server Post data
* that will be sent along with the user to the Sage Pay payment pages.  In SIMULATOR and TEST mode
* the decoded version of this field will be displayed on screen for you to check.
***************************************************************************************************/

// Check we have a cart in the session.  If not, go back to the buildOrder page to get one
/*$strCart=$_SESSION["strCart"];
if (strlen($strCart)==0) {
	ob_end_flush();
	redirect("buildOrder.php");
} */

if(!(isset($_SESSION['basket']) && count($_SESSION['basket'])>0)) { 
	header("location: /products/basket/"); exit;
}

// Check we have a billing address in the session.  If not, go back to the customerDetails page to get one
if (strlen($_SESSION["strBillingAddress1"])==0) {
	ob_end_flush();
	redirect("/products/payments/");
}

if (isset($_REQUEST["navigate"]) && $_REQUEST["navigate"]=="back") {
	ob_end_flush();
	redirect("/products/payments/");
}

// Check for the proceed button click, and if so, go validate the order
if (isset($_REQUEST["navigate"]) && $_REQUEST["navigate"]=="proceed") {
	ob_flush();
	$url = "transactionRegistration.php";
	$url .= isset($_POST['strVendorTxCode']) && trim($_POST['strVendorTxCode']) !="" ? "?strVendorTxCode=".urlencode($_POST['strVendorTxCode']) : "";
	redirect($url);
}


//** Gather customer details from the session **
$strCustomerEMail      = $_SESSION["strCustomerEMail"];
$strBillingFirstnames  = $_SESSION["strBillingFirstnames"];
$strBillingSurname     = $_SESSION["strBillingSurname"];
$strBillingAddress1    = $_SESSION["strBillingAddress1"];
$strBillingAddress2    = $_SESSION["strBillingAddress2"];
$strBillingCity        = $_SESSION["strBillingCity"];
$strBillingPostCode    = $_SESSION["strBillingPostCode"];
$strBillingCountry     = $_SESSION["strBillingCountry"];
$strBillingState       = $_SESSION["strBillingState"];
$strBillingPhone       = $_SESSION["strBillingPhone"];
$bIsDeliverySame       = $_SESSION["bIsDeliverySame"];
$strDeliveryFirstnames = $_SESSION["strDeliveryFirstnames"];
$strDeliverySurname    = $_SESSION["strDeliverySurname"];
$strDeliveryAddress1   = $_SESSION["strDeliveryAddress1"];
$strDeliveryAddress2   = $_SESSION["strDeliveryAddress2"];
$strDeliveryCity       = $_SESSION["strDeliveryCity"];
$strDeliveryPostCode   = $_SESSION["strDeliveryPostCode"];
$strDeliveryCountry    = $_SESSION["strDeliveryCountry"];
$strDeliveryState      = $_SESSION["strDeliveryState"];
$strDeliveryPhone      = $_SESSION["strDeliveryPhone"];

$body_class ="checkout ordersummary";

	$strTimeStamp = date("y/m/d : H:i:s", time());
	$intRandNum = rand(0,32000)*rand(0,32000);
	$strVendorTxCode=  cleanInput($strVendorName . "-" . $strTimeStamp . "-" . $intRandNum,"VendorTxCode");
	
	
?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!DOCTYPE html>
<html class="" lang="en"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php  $pageTitle = "Order Confirmation"; echo $pageTitle." | ".$site_name;?></title>
<!-- InstanceEndEditable -->
<!--[if IE]><![endif]-->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
	
	
	<script language="javascript" src="scripts/common.js" ></script>
    <script language="javascript" src="scripts/countrycodes.js"></script>
    <script language="javascript" >
    function makeInactive() { 
	document.getElementById('proceed').value = 'Loading...';
	document.getElementById('proceed').style.backgroundColor = '#ccc';
    }</script>
    <style >
<!--
-->
</style>
<link href="../../css/defaultProducts.css" rel="stylesheet"  />
<!-- InstanceEndEditable -->
</head>
<body id="OffTemplate" class="bootstrap <?php echo $body_class;  ?>">
<?php require_once('../../../local/includes/header.inc.php'); ?>
<main id="content"><!-- InstanceBeginEditable name="Body" --><?php require_once('../../../core/seo/includes/googletagmanager.inc.php'); ?>
      <div class="checkout checkout-summary container sagepay">
    <ol class="checkoutprogress">
        <li><a href="#"><?php echo isset($row_rsProductPrefs['text_yourorder']) ? htmlentities($row_rsProductPrefs['text_yourorder'], ENT_COMPAT, "UTF-8") : "Your Order" ?></a></li>
        <li><a href="/products/payments/index.php"><?php echo isset($row_rsProductPrefs['text_yourdetails']) ? htmlentities($row_rsProductPrefs['text_yourdetails'], ENT_COMPAT, "UTF-8") : "Your Details" ?></a></li>
        <li class="selected"><a href="#"><?php echo isset($row_rsProductPrefs['paymenttext']) ? htmlentities($row_rsProductPrefs['paymenttext'], ENT_COMPAT, "UTF-8" ): "Payment"; ?></a></li>
        </ol> 
            <h1><?php echo isset($row_rsProductPrefs['text_ordersummary']) ? htmlentities($row_rsProductPrefs['text_ordersummary'], ENT_COMPAT, "UTF-8") : "Order Summary" ?></h1>
			
			 
          
            <?php if(isset($seoPrefs['googleanalyticsecommerce']) && $seoPrefs['googleanalyticsecommerce']==1) $track_ecommerce = true;  require_once('../../includes/basketcontents.inc.php'); ?>
			<table class="formTable">
				<tr>
				  <td colspan="2"><h2 class="subheader"><?php echo isset($row_rsProductPrefs['text_billingdetails']) ? htmlentities($row_rsProductPrefs['text_billingdetails'], ENT_COMPAT, "UTF-8") : "Billing details" ?></h2></td>
				</tr>
				<tr>
					<td class="fieldLabel"><?php echo isset($row_rsProductPrefs['text_firstname']) ? htmlentities($row_rsProductPrefs['text_firstname'], ENT_COMPAT, "UTF-8") : "First Name(s)" ?>/<?php echo isset($row_rsProductPrefs['text_surname']) ? htmlentities($row_rsProductPrefs['text_surname'], ENT_COMPAT, "UTF-8") : "Surname" ?>:</td>
					<td class="fieldData"><?php echo $strBillingFirstnames ?>&nbsp;<?php echo $strBillingSurname ?></td>
				</tr>
				<tr>
					<td class="fieldLabel"><?php echo isset($row_rsProductPrefs['text_address']) ? htmlentities($row_rsProductPrefs['text_address'], ENT_COMPAT, "UTF-8") : "Address" ?>:</td>
					<td class="fieldData">
					    <?php echo $strBillingAddress1  ?><br />
					    <?php if (strlen($strBillingAddress2)>0) echo $strBillingAddress2 . "<BR>"; ?>
					    <?php echo $strBillingCity  ?>&nbsp;
					    <?php if (strlen($strBillingState)>0) echo $strBillingState; ?><br />
					    <?php echo $strBillingPostCode;  ?><br />
					   <?php echo $row_rsBillingCountry['fullname']; ?>
					</td>
				</tr>
				<tr>
					<td class="fieldLabel"><?php echo isset($row_rsProductPrefs['text_telephone']) ? htmlentities($row_rsProductPrefs['text_telephone'], ENT_COMPAT, "UTF-8") : "Phone" ?>:</td>
					<td class="fieldData"><?php echo $strBillingPhone; ?>&nbsp;</td>
				</tr>
				<tr>
					<td class="fieldLabel">e-Mail Address:</td>
					<td class="fieldData"><?php echo $strCustomerEMail; ?>&nbsp;</td>
				</tr>
			</table>
			<table class="formTable">
				<tr>
				  <td colspan="2"><h2 class="subheader"><?php echo isset($row_rsProductPrefs['text_deliverydetails']) ? htmlentities($row_rsProductPrefs['text_deliverydetails'], ENT_COMPAT, "UTF-8") : "Delivery details" ?></h2></td>
				</tr>
				<tr>
					<td class="fieldLabel"><?php echo isset($row_rsProductPrefs['text_firstname']) ? htmlentities($row_rsProductPrefs['text_firstname'], ENT_COMPAT, "UTF-8") : "First Name(s)" ?>/<?php echo isset($row_rsProductPrefs['text_surname']) ? htmlentities($row_rsProductPrefs['text_surname'], ENT_COMPAT, "UTF-8") : "Surname" ?>:</td>
					<td class="fieldData"><?php if(isset($_SESSION["bIsDeliverySame"]) && $_SESSION["bIsDeliverySame"]==0) {
				  echo $_SESSION['strDeliveryFirstnames']." ".$_SESSION['strDeliverySurname'];
			  } else if(isset($_SESSION["bIsDeliverySame"]) && $_SESSION["bIsDeliverySame"]==2) {
				  echo isset($row_rsProductPrefs['text_willcollectfrom']) ? htmlentities($row_rsProductPrefs['text_willcollectfrom'], ENT_COMPAT, "UTF-8") : "Will collect";
			  } else {
				  echo isset($row_rsProductPrefs['text_sameasbilling']) ? htmlentities($row_rsProductPrefs['text_sameasbilling'], ENT_COMPAT, "UTF-8") : "Same as billing";
			  } ?></td>
				</tr>
				<tr>
					<td class="fieldLabel"><?php echo isset($row_rsProductPrefs['text_address']) ? htmlentities($row_rsProductPrefs['text_address'], ENT_COMPAT, "UTF-8") : "Address" ?>:</td>
					<td class="fieldData">
					    <?php echo $strDeliveryAddress1  ?><br />
					    <?php if (strlen($strDeliveryAddress2)>0) echo $strDeliveryAddress2 . "<BR>"; ?>
					    <?php echo $strDeliveryCity; ?>&nbsp;
					    <?php if (strlen($strDeliveryState)>0) echo $strDeliveryState; ?><br />
					    <?php echo $strDeliveryPostCode; ?><br />
					   <?php echo $row_rsDeliveryCountry['fullname']; ?>
					</td>
				</tr>
				<tr>
					<td class="fieldLabel"><?php echo isset($row_rsProductPrefs['text_telephone']) ? htmlentities($row_rsProductPrefs['text_telephone'], ENT_COMPAT, "UTF-8") : "Phone" ?>:</td>
					<td class="fieldData"><?php echo $strDeliveryPhone; ?>&nbsp;</td>
				</tr>
			</table>
            
			 	<form action="orderConfirmation.php" method="post" name="customerform" id="customerform">
			 	<input type="hidden" name="navigate" value="proceed" />
			 	<input type="hidden" name="strVendorTxCode" value="<?php echo $strVendorTxCode; ?>" />
				 <div id="paymentNav">
                <a href="/products/payments/index.php" title="Go back to the place order page" style="float: left;" onclick="javascript:submitForm('customerform','back'); return false;" class="btn btn-default btn-secondary">
                    &laquo;&nbsp;Back
                </a>
               
                  <button name="proceed" id="proceed" type="submit" class="btn btn-primary"    border="0" style="float:right" onclick="makeInactive()">Proceed »</button>
                  <div align="center">Orders will be processed securely by <img src="images/sagepay_logo.gif" alt="SagePay Logo" width="141" height="32" style="vertical-align:
middle;" /></div>
           
            </div>
				</form>
		 <?php echo isset($row_rsProductPrefs['checkoutconfirmfooter']) ? htmlentities($row_rsProductPrefs['checkoutconfirmfooter'], ENT_COMPAT, "UTF-8") : ""; ?></div>
<!-- InstanceEndEditable --></main>
<?php require_once('../../../local/includes/footer.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsThisRegion);

mysql_free_result($rsBillingCountry);

mysql_free_result($rsDeliveryCountry);
?>
