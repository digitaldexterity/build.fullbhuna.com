<?php require_once('../../Connections/aquiescedb.php'); ?>
<?php $_SESSION['PrevUrl'] = $_SERVER['REQUEST_URI'];
require_once('../../login/includes/login.inc.php'); // auto log in the user with cookies if applicable ?>
<?php require_once('../includes/productHeader.inc.php'); ?><?php require_once('../includes/basketFunctions.inc.php'); ?>
<?php 

if($row_rsProductPrefs['askbillingdetails']!=1) {
	// if billing not required go straigt to PayPal (not sur eif oter payment providers allow for this just now
	header("location: paypal/"); exit;
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


$error = ""; $js = "";


$varUsername_rsAddresses = "-1";
if (isset($_SESSION['MM_Username'])) {
  $varUsername_rsAddresses = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsAddresses = sprintf("SELECT location.* FROM location LEFT JOIN users ON (location.userID = users.ID) WHERE location.active = 1 AND users.username = %s", GetSQLValueString($varUsername_rsAddresses, "text"));
$rsAddresses = mysql_query($query_rsAddresses, $aquiescedb) or die(mysql_error());
$row_rsAddresses = mysql_fetch_assoc($rsAddresses);
$totalRows_rsAddresses = mysql_num_rows($rsAddresses);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsCountries = "SELECT fullname, ID FROM countries WHERE statusID = 1 ORDER BY ordernum ASC, fullname ASC ";
$rsCountries = mysql_query($query_rsCountries, $aquiescedb) or die(mysql_error());
$row_rsCountries = mysql_fetch_assoc($rsCountries);
$totalRows_rsCountries = mysql_num_rows($rsCountries);

$varUsername_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $varUsername_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT users.ID, users.firstname, users.surname, users.email, users.defaultaddressID, location.address1,
location.locationname,
location.address2,
location.address3,
location.address4,
location.address5,
location.postcode, users.telephone, users.discovered, usergroupmember.groupID, usertypeID FROM users LEFT JOIN location ON (users.defaultaddressID = location.ID) LEFT JOIN usergroupmember ON (usergroupmember.userID = users.ID) WHERE users.username = %s", GetSQLValueString($varUsername_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn); 

$varRegionID_rsShippingRates = "1";
if (isset($regionID)) {
  $varRegionID_rsShippingRates = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsShippingRates = sprintf("SELECT * FROM productshipping WHERE productshipping.regionID = %s AND productshipping.statusID = 1 ", GetSQLValueString($varRegionID_rsShippingRates, "int"));
$rsShippingRates = mysql_query($query_rsShippingRates, $aquiescedb) or die(mysql_error());
$row_rsShippingRates = mysql_fetch_assoc($rsShippingRates);
$totalRows_rsShippingRates = mysql_num_rows($rsShippingRates);



$varRegionID_rsExpressRates = "1";
if (isset($regionID)) {
  $varRegionID_rsExpressRates = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsExpressRates = sprintf("SELECT * FROM productshipping WHERE productshipping.regionID = %s AND productshipping.statusID = 1 AND productshipping.express = 1", GetSQLValueString($varRegionID_rsExpressRates, "int"));
$rsExpressRates = mysql_query($query_rsExpressRates, $aquiescedb) or die(mysql_error());
$row_rsExpressRates = mysql_fetch_assoc($rsExpressRates);
$totalRows_rsExpressRates = mysql_num_rows($rsExpressRates);

$varRegionID_rsDiscovered = "1";
if (isset($regionID)) {
  $varRegionID_rsDiscovered = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsDiscovered = sprintf("SELECT * FROM discovered WHERE statusID = 1 AND regionID = %s ORDER BY ordernum", GetSQLValueString($varRegionID_rsDiscovered, "int"));
$rsDiscovered = mysql_query($query_rsDiscovered, $aquiescedb) or die(mysql_error());
$row_rsDiscovered = mysql_fetch_assoc($rsDiscovered);
$totalRows_rsDiscovered = mysql_num_rows($rsDiscovered);

?>
<?php if(isset($_POST['discovered']) && $_POST['discovered'] >0 ) {
	$_SESSION['discovered'] = $_POST['discovered'];
}

$discovered = isset($_SESSION['discovered']) ? $_SESSION['discovered'] : (isset($row_rsLoggedIn['discovered']) ? $row_rsLoggedIn['discovered'] : 0);


// Manufacturers unwanted characters out of an input string.  Useful for tidying up FORM field inputs
function cleanInput($strRawText,$strType)
{

	if ($strType=="Number") {
		$strClean="0123456789.";
		$bolHighOrder=false;
	}
	else if ($strType=="VendorTxCode") {
		$strClean="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_.";
		$bolHighOrder=false;
	}
	else {
  		$strClean=" ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789.,'/{}@():?-_&?$=%~<>*+\"";
		$bolHighOrder=true;
	}
	
	$strCleanedText="";
	$iCharPos = 0;
		
	do
	{
    	// Only include valid characters
		$chrThisChar=substr($strRawText,$iCharPos,1);
			
		if (strspn($chrThisChar,$strClean,0,strlen($strClean))>0) { 
			$strCleanedText=$strCleanedText . $chrThisChar;
		}
		else if ($bolHighOrder==true) {
				// Fix to allow accented characters and most high order bit chars which are harmless 
				if (bin2hex($chrThisChar)>=191) {
					$strCleanedText=$strCleanedText . $chrThisChar;
				}
			}
			
		$iCharPos=$iCharPos+1;
		}
	while ($iCharPos<strlen($strRawText));
		
  	$cleanInput = ltrim($strCleanedText);
	return $cleanInput;

}

// Function to check validity of email address entered in form fields
function is_valid_email($email) {
  $result = TRUE;
  if(!preg_match("#^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,10})$#i", $email)) {
    $result = FALSE;
  }
  return $result;
}




if(!(isset($_SESSION['basket']) && count($_SESSION['basket'])>0)) { 
	header("location: /products/basket/"); exit;
}

$bIsDeliverySame = isset($_REQUEST["IsDeliverySame"]) ? intval($_REQUEST["IsDeliverySame"]) : 1;

$_SESSION["bIsDeliverySame"] = isset($_SESSION["bIsDeliverySame"]) ? $_SESSION["bIsDeliverySame"] : 1;
// Check for the proceed button click, and if so, go validate the order **/
if (isset($_REQUEST['navigate']) && ($_REQUEST['navigate']=="proceed" || $_REQUEST['navigate']=="calculate")) {
	// Validate and clean the user input here
	$strBillingFirstnames  = cleaninput(@$_REQUEST["BillingFirstnames"], "Text");
	$strBillingSurname     = cleaninput(@$_REQUEST["BillingSurname"], "Text");
	$strBillingCompany    = cleaninput(@$_REQUEST["BillingCompany"], "Text");
	$strBillingAddress1    = cleaninput(@$_REQUEST["BillingAddress1"], "Text");
	$strBillingAddress2    = cleaninput(@$_REQUEST["BillingAddress2"], "Text");
	$strBillingCity        = cleaninput(@$_REQUEST["BillingCity"], "Text");
	$strBillingPostCode    = cleaninput(@$_REQUEST["BillingPostCode"], "Text");
	$strBillingCountry     = cleaninput(@$_REQUEST["BillingCountry"], "Text");
	$strBillingState       = cleaninput(@$_REQUEST["BillingState"], "Text");
	$strBillingPhone       = cleaninput(@$_REQUEST["BillingPhone"], "Text");
	$strBillingMobile       = cleaninput(@$_REQUEST["BillingMobile"], "Text");
	$strCustomerEMail      = cleaninput(@$_REQUEST["CustomerEMail"], "Text");
	$strDeliveryFirstnames = cleaninput(@$_REQUEST["DeliveryFirstnames"], "Text");
	$strDeliverySurname    = cleaninput(@$_REQUEST["DeliverySurname"], "Text");
	$strDeliveryCompany   = cleaninput(@$_REQUEST["DeliveryCompany"], "Text");
	$strDeliveryAddress1   = cleaninput(@$_REQUEST["DeliveryAddress1"], "Text");
	$strDeliveryAddress2   = cleaninput(@$_REQUEST["DeliveryAddress2"], "Text");
	$strDeliveryCity       = cleaninput(@$_REQUEST["DeliveryCity"], "Text");
	$strDeliveryPostCode   = cleaninput(@$_REQUEST["DeliveryPostCode"], "Text");
	$strDeliveryCountry    = cleaninput(@$_REQUEST["DeliveryCountry"], "Text");
	$strDeliveryState      = cleaninput(@$_REQUEST["DeliveryState"], "Text");
	$strDeliveryPhone      = cleaninput(@$_REQUEST["DeliveryPhone"], "Text");
	$strShippingOption      = cleaninput(@$_REQUEST["shippingoptionID"], "Text");
	$strShippingRateID      = cleaninput(@$_REQUEST["shippingrateID"], "Text");
	$deliveryinstructions = cleaninput(@$_REQUEST["deliveryinstructions"], "Text");
	$deliverytime = cleaninput(@$_REQUEST["deliverytime"], "Text");
	$vatnumber = cleaninput(@$_REQUEST["vatnumber"], "Text");
	$purchaseorder = cleaninput(@$_REQUEST["purchaseorder"], "Text");
	$checkoutanswer1 = cleaninput(@$_REQUEST["checkoutanswer1"], "Text");
	$optin = isset($_REQUEST["optin"]) ? intval($_REQUEST["optin"]) : $row_rsPreferences['emailoptinset'];
	
		
		

	// Validate the compulsory fields 
	
	$text_billingdetails =  isset($row_rsProductPrefs['text_billingdetails']) ? $row_rsProductPrefs['text_billingdetails']  : "Billing details";
	$text_deliverydetails =  isset($row_rsProductPrefs['text_deliverydetails']) ? $row_rsProductPrefs['text_deliverydetails'] : "Delivery details";
	
	if ($row_rsProductPrefs['shippingcalctype']>0 && $row_rsProductPrefs['shippingautocalc']==1 && strlen($strShippingOption)==0 && isset($_REQUEST["IsDeliverySame"]) && $_REQUEST["IsDeliverySame"] !=2) {
		$error.="Please enter your Shipping Option where requested below.\n";
		$js .= "$(\"#shippingoptionID\").addClass(\"error\");\n";
	}
	if ($row_rsProductPrefs['shippingcalctype']>0 && $row_rsProductPrefs['shippingautocalc']==0 && strlen($strShippingRateID)==0 && isset($_REQUEST["IsDeliverySame"]) && $_REQUEST["IsDeliverySame"] !=2)  {
		$error.="Please choose your shipping rate where requested below.\n";
		$js .= "$(\"#shippingrateID\").addClass(\"error\");";
	}
	if(trim($strBillingPhone)=="" && trim($strBillingMobile)!="") {
		$strBillingPhone = $strBillingMobile;
	}
		if ($row_rsProductPrefs['checkoutmandatorytelephone']==1 && trim($strBillingPhone.$strBillingMobile) =="") 
		$error.= $text_billingdetails." ".$row_rsProductPrefs['text_telephone']."\n";
	if (strlen($strBillingFirstnames)==0) 
		$error.=$text_billingdetails." ".$row_rsProductPrefs['text_firstname']."\n";
	 if (strlen($strBillingSurname)==0) 
		$error.=$text_billingdetails." ".$row_rsProductPrefs['text_surname']."\n";
	 if (strlen($strBillingAddress1)==0) 
		$error.=$text_billingdetails." ".$row_rsProductPrefs['text_address']."\n";
	 if (strlen($strBillingCity)==0) 
		$error.=$text_billingdetails." ".$row_rsProductPrefs['text_city']."\n";
	 if (strlen($strBillingPostCode)==0 && $row_rsProductPrefs['askpostcode']==1) 
		$error.=$text_billingdetails." ".$row_rsProductPrefs['text_postcode']."\n";
	 if (strlen($strBillingCountry)==0) 
		$error.=$text_billingdetails." ".$row_rsProductPrefs['text_country']."\n";
   /*  if ((strlen($strBillingState) == 0) and ($strBillingCountry == "US")) 
		$error.="Please enter your State code as you have selected United States for billing country.\n";*/
	 if (strlen($strCustomerEMail) < 3 || (strlen($strCustomerEMail) > 0 && is_valid_email($strCustomerEMail)==false)) {
		$error.= isset($region['text_emailerror']) ? $region['text_emailerror']."\n" : "Please enter a valid email address\n";
	 }
	 
	 if ($bIsDeliverySame > 0) {
			$strDeliveryFirstnames= $strBillingFirstnames;
	        $strDeliverySurname= $strBillingSurname;
	        $strDeliveryCompany = $strDeliveryCompany;
			$strDeliveryAddress1 = $strBillingAddress1;
	        $strDeliveryAddress2= $strBillingAddress2;
	        $strDeliveryCity= $strBillingCity;
	        $strDeliveryPostCode= $strBillingPostCode;
	        $strDeliveryCountry= $strBillingCountry;
	        $strDeliveryState= $strBillingState;
	        $strDeliveryPhone= $strBillingPhone;
	    }
		
	 if (($bIsDeliverySame==0) and strlen($strDeliveryFirstnames)==0) 
		$error.=$text_deliverydetails." ".$row_rsProductPrefs['text_firstname']."\n";
	 if (($bIsDeliverySame==0) and strlen($strDeliverySurname)==0) 
		$error.=$text_deliverydetails." ".$row_rsProductPrefs['text_surname']."\n";
	 if (($bIsDeliverySame==false) and strlen($strDeliveryAddress1)==0) 
		$error.=$text_deliverydetails." ".$row_rsProductPrefs['text_address']."\n";
	 if (($bIsDeliverySame==0) and strlen($strDeliveryCity)==0) 
		$error.=$text_deliverydetails." ".$row_rsProductPrefs['text_city']."\n";
	 if (($bIsDeliverySame==0) and strlen($strDeliveryPostCode)==0  && $row_rsProductPrefs['askpostcode']==1) 
		$error.=$text_deliverydetails." ".$row_rsProductPrefs['text_postcode']."\n";
	 if (($bIsDeliverySame==0) and strlen($strDeliveryCountry)==0) 
		$error.=$text_deliverydetails." ".$row_rsProductPrefs['text_country']."\n";
    /* if (($bIsDeliverySame==0) and (strlen($strDeliveryState) == 0) and ($strDeliveryCountry == "US")) 
		$error.="Please enter your State code as you have selected United States for delivery country.\n";*/
	if($row_rsProductPrefs['checkouttermsagree']==1 && !isset($_REQUEST['termsagree']))
	$error.="To proceed you must agree to the site terms and conditions.\n";
	
	if(isset($_POST['noshipinternational']) && intval($_POST['noshipinternational'])>0 && intval($strDeliveryCountry)>0) {
		$select = "SELECT ID FROM countries WHERE (countries.regionID = 0 OR countries.regionID = ".$regionID.") AND countries.ID = ".intval($strDeliveryCountry);
		$result = mysql_query($select, $aquiescedb) or die(mysql_error().": ".$select);
		//die($select.":".mysql_num_rows($result));
		if(mysql_num_rows($result)==0) {
			$error.="Sorry, one or more of the items in your shopping basket cannot be shipped internationally.\n";
		}
	}
	
		 
	if($error!="") {
		$error = $row_rsProductPrefs['text_addresserror']."\n".$error;
	} else {
		//** All validations have passed, so store the details in the session **
	    $_SESSION["strBillingFirstnames"]  = $strBillingFirstnames;
	    $_SESSION["strBillingSurname"]     = $strBillingSurname;
		$_SESSION["strBillingCompany"]    = $strBillingCompany;
	    $_SESSION["strBillingAddress1"]    = $strBillingAddress1;
	    $_SESSION["strBillingAddress2"]    = $strBillingAddress2;
	    $_SESSION["strBillingCity"]        = $strBillingCity;
	    $_SESSION["strBillingPostCode"]    = $strBillingPostCode;
	    $_SESSION["strBillingCountry"]     = $strBillingCountry;
	    $_SESSION["strBillingState"]       = $strBillingState;
	    $_SESSION["strBillingPhone"]       = $strBillingPhone;
		$_SESSION["strBillingMobile"]       = $strBillingMobile;
	    $_SESSION["strCustomerEMail"]      = $strCustomerEMail;
	    $_SESSION["bIsDeliverySame"]       = $bIsDeliverySame;
		$_SESSION["shippingoptionID"]       = $strShippingOption;
		$_SESSION["shippingrateID"]       = $strShippingRateID;
		$_SESSION["deliveryinstructions"]       = $deliveryinstructions;
		$_SESSION["deliverytime"]       = $deliverytime;
		$_SESSION["checkoutanswer1"]       = $checkoutanswer1;
		$_SESSION["optin"]       = $optin;
		$_SESSION["vatnumber"]       = $vatnumber;
		$_SESSION["purchaseorder"]       = $purchaseorder;
		
		
		
	    
	     
	   
	    	$_SESSION["strDeliveryFirstnames"] = $strDeliveryFirstnames;
	        $_SESSION["strDeliverySurname"]    = $strDeliverySurname;
			$_SESSION["strDeliveryCompany"]   = $strDeliveryCompany;
	        $_SESSION["strDeliveryAddress1"]   = $strDeliveryAddress1;
	        $_SESSION["strDeliveryAddress2"]   = $strDeliveryAddress2;
	        $_SESSION["strDeliveryCity"]       = $strDeliveryCity;
	        $_SESSION["strDeliveryPostCode"]   = $strDeliveryPostCode;
	        $_SESSION["strDeliveryCountry"]    = $strDeliveryCountry;
	        $_SESSION["strDeliveryState"]      = $strDeliveryState;
	        $_SESSION["strDeliveryPhone"]      = $strDeliveryPhone;
	    
		
	    
		// Now go to the order confirmation page
		if($_REQUEST['navigate']=="proceed") {
			if(isset($_POST['paymentmethodID']) && ($_POST['paymentmethodID']=="cashdelivery" || $_POST['paymentmethodID']=="cashcollection"|| $_POST['paymentmethodID']=="invoice" || $_POST['paymentmethodID']=="cheque" || !isset($_SESSION['basket_grand_total']) || ceil($_SESSION['basket_grand_total']) == 0)) { // if no price or non-processor
		header("location: /products/payments/othermethods.php?paymentmethod=".urlencode($_POST['paymentmethodID'])); exit;
		} else if($_POST['paymentmethodID']=="paypal") {
			header("location: /products/payments/paypal/index.php"); exit;
		} else if($row_rsProductPrefs['paymentproviderID']==2) {
		header("location: /products/payments/sagepay/orderConfirmation.php"); exit;
		} else if($row_rsProductPrefs['paymentproviderID']==3) {
		header("location: /products/payments/worldpay/"); exit;
		} else 	if($row_rsProductPrefs['paymentproviderID']==5) {
		header("location: /products/payments/securetrading/index.php"); exit;
		} else 	if($row_rsProductPrefs['paymentproviderID']==7) {
		header("location: /products/payments/nochex/index.php"); exit;
		} else 	if($row_rsProductPrefs['paymentproviderID']==8) {
		header("location: /products/payments/paytrail/index.php"); exit;
		} else 	if($row_rsProductPrefs['paymentproviderID']==9) {
		header("location: /products/payments/ingenico/index.php"); exit;
		} else 	if($row_rsProductPrefs['paymentproviderID']==10) {
		header("location: /products/payments/barclays/index.php"); exit;
		} else 	if($row_rsProductPrefs['paymentproviderID']==11) {
		header("location: /products/payments/sagepay3/index.php"); exit;
		} else 	if($row_rsProductPrefs['paymentproviderID']==12) {
		header("location: /products/payments/elavon/index.php"); exit;
		} else { // default PayPal for any others
			header("location: /products/payments/paypal/index.php"); exit;
		}
		} // end proceed
	} // end no error
} // end proceed or calculate
	
else if (isset($_REQUEST["navigate"]) && $_REQUEST["navigate"]=="back") {
	header("location: /products/basket/"); exit;
}

else {
	// Populate customer details from the session if they are there	
    $strBillingFirstnames  = isset($_SESSION["strBillingFirstnames"]) ? $_SESSION["strBillingFirstnames"] : "";
    $strBillingSurname     = isset($_SESSION["strBillingSurname"]) ? $_SESSION["strBillingSurname"] : "";
    $strBillingCompany    = isset($_SESSION["strBillingCompany"]) ? $_SESSION["strBillingCompany"] : "";
	    $strBillingAddress1    = isset($_SESSION["strBillingAddress1"]) ? $_SESSION["strBillingAddress1"] : "";

    $strBillingAddress2    = isset($_SESSION["strBillingAddress2"]) ? $_SESSION["strBillingAddress2"] : "";
    $strBillingCity        = isset($_SESSION["strBillingCity"]) ? $_SESSION["strBillingCity"] : "";
    $strBillingPostCode    = isset($_SESSION["strBillingPostCode"]) ? $_SESSION["strBillingPostCode"] : "";
    $strBillingCountry     = isset($_SESSION["strBillingCountry"]) ? $_SESSION["strBillingCountry"] : "";
    $strBillingState       = isset($_SESSION["strBillingState"]) ? $_SESSION["strBillingState"] : "";
    $strBillingPhone       = isset($_SESSION["strBillingPhone"]) ? $_SESSION["strBillingPhone"] : "";
	$strBillingMobile       = isset($_SESSION["strBillingMobile"]) ? $_SESSION["strBillingMobile"] : "";
    $strCustomerEMail      = isset($_SESSION["strCustomerEMail"]) ? $_SESSION["strCustomerEMail"] : "";
	$bIsDeliverySame       = isset($_SESSION["bIsDeliverySame"]) ? $_SESSION["bIsDeliverySame"] : 1;
    $strDeliveryFirstnames = isset($_SESSION["strDeliveryFirstnames"]) ? $_SESSION["strDeliveryFirstnames"] : "";
    $strDeliverySurname    = isset($_SESSION["strDeliverySurname"]) ? $_SESSION["strDeliverySurname"] : "";
    $strDeliveryCompany   = isset($_SESSION["strDeliveryCompany"]) ? $_SESSION["strDeliveryCompany"] : "";
	 $strDeliveryAddress1   = isset($_SESSION["strDeliveryAddress1"]) ? $_SESSION["strDeliveryAddress1"] : "";
    $strDeliveryAddress2   = isset($_SESSION["strDeliveryAddress2"]) ? $_SESSION["strDeliveryAddress2"] : "";
    $strDeliveryCity       = isset($_SESSION["strDeliveryCity"]) ? $_SESSION["strDeliveryCity"] : "";
    $strDeliveryPostCode   = isset($_SESSION["strDeliveryPostCode"]) ? $_SESSION["strDeliveryPostCode"] : "";
    $strDeliveryCountry    = isset($_SESSION["strDeliveryCountry"]) ? $_SESSION["strDeliveryCountry"] : "";
    $strDeliveryState      = isset($_SESSION["strDeliveryState"]) ? $_SESSION["strDeliveryState"] : "";
    $strDeliveryPhone      = isset($_SESSION["strDeliveryPhone"]) ? $_SESSION["strDeliveryPhone"] : "";
	$deliveryinstructions      = isset($_SESSION["deliveryinstructions"]) ? $_SESSION["deliveryinstructions"] : "";
	$deliverytime      = isset($_SESSION["deliverytime"]) ? $_SESSION["deliverytime"] : "";
	$vatnumber      = isset($_SESSION["vatnumber"]) ? $_SESSION["vatnumber"] : "";
	$purchaseorder      = isset($_SESSION["purchaseorder"]) ? $_SESSION["purchaseorder"] : "";
	$optin      = isset($_SESSION["optin"]) ? $_SESSION["optin"] : "";
}
if($row_rsProductPrefs['stockcontrol']==1) {
	$warning = checkStock();
}


$body_class ="checkout customerdetails";
?><?php $prepay=1; require_once('../../core/seo/includes/trackerprepay.inc.php'); ?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!DOCTYPE html>
<html class="" lang="en"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php  $pageTitle = isset($row_rsProductPrefs['text_yourdetails']) ? htmlentities($row_rsProductPrefs['text_yourdetails'], ENT_COMPAT, "UTF-8") : "Your Details"; echo $pageTitle." | ".$site_name;?>
</title>
<!-- InstanceEndEditable -->
<!--[if IE]><![endif]-->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
	<style >
<!--



<?php



if ($row_rsPreferences['emailoptintype'] ==0) {
 echo ".emailoptin { display:none !important; }";
}
if ($row_rsPreferences['partneremailoptintype'] ==0) {
 echo ".partneremailoptin { display:none !important; }";
}

echo !($row_rsProductPrefs['invoice']==1 || (isset($_SESSION['MM_UserGroup']) && $_SESSION['MM_UserGroup']>=7) || (isset($row_rsPurchaseAccount['payaccount']) &&  $row_rsPurchaseAccount['payaccount']==1))  ? "#optioninvoice { display:none; }\n" : "";

echo ($row_rsProductPrefs['paymentproviderID']==0 || (isset($row_rsPurchaseAccount['payother']) &&  $row_rsPurchaseAccount['payother']==0))  ? "#optioncreditcard { display:none; }\n" : "";

echo ($row_rsProductPrefs['cashdelivery']==0 || (isset($row_rsPurchaseAccount['payother']) &&  $row_rsPurchaseAccount['payother']==0)) ? "#optioncashdelivery { display:none; }\n" : "";

echo ($row_rsProductPrefs['cashcollection']==0  || (isset($row_rsPurchaseAccount['payother']) &&  $row_rsPurchaseAccount['payother']==0)) ? "#optioncashcollection { display:none; }\n" : "";

echo ($row_rsProductPrefs['cheque']==0  || (isset($row_rsPurchaseAccount['payother']) &&  $row_rsPurchaseAccount['payother']==0)) ? "#optioncheque { display:none; }\n" : "";

echo ($row_rsProductPrefs['paypalID']=="" || (isset($row_rsPurchaseAccount['payother']) &&  $row_rsPurchaseAccount['payother']==0)) ? "#optionpaypal { display:none; }\n" : "";

if(($row_rsProductPrefs['cashdelivery']+$row_rsProductPrefs['cashcollection']+$row_rsProductPrefs['invoice']+$row_rsProductPrefs['cheque']==0) && $row_rsProductPrefs['paypalID']=="" && (!isset($_SESSION['MM_UserGroup']) || $_SESSION['MM_UserGroup']<9)) {
	echo "#paymentmethods { display:none; }\n";
}
if($totalRows_rsExpressRates==0) { echo "#shippingoptionID { display:none !important; }\n"; } 
if($totalRows_rsAddresses<1) { echo ".savedaddresses { display: none !important; }\n"; } 
echo $row_rsProductPrefs['allowcollection']==0 ? ".allowcollection { display:none  !important; }\n" : "";
if($row_rsProductPrefs['shippingcalctype']==0) { // no shipping
 echo ".shippingOptions {
	display: none !important;
}";
echo ".freeShipping {
	display: inline;
}";
} 

if($row_rsProductPrefs['askhowdiscovered']==0 || $totalRows_rsDiscovered==0) {
	echo ".howdiscovered { display: none !important; }";
}
if($row_rsProductPrefs['checkoutmandatorytelephone']==0) { // phone not madatory
	echo ".telephone .required { display: none !important; }";
}

if($row_rsProductPrefs['checkouttermsagree']!=1) {
	echo ".terms { display: none !important; }";
}

   if(!((isset($row_rsPurchaseAccount['payaccount']) &&  $row_rsPurchaseAccount['payaccount']==1) || $row_rsProductPrefs['askcompany']==1)) {
				   // ask for company PO number if purchase account  
				   	echo ".company { display: none !important; }";
   } 
   
    if(!(isset($row_rsPurchaseAccount['payaccount']) &&  $row_rsPurchaseAccount['payaccount']==1)) {
				   // ask for company PO number if purchase account  
				   	echo ".purchaseorder { display: none !important; }";
   } 
   
    if($row_rsProductPrefs['askpostcode']==0) {
		echo ".tr.postcode  { display: none !important; }";
	}
?>
-->
</style>
<script src="/products/payments/scripts/customerDetails.js"></script><script src="/products/scripts/productFunctions.js"></script>
<script>
$(document).ready(function() {
		toggleLogin();
		toggleBillingState();
		toggleDeliveryState();
  		$("#deliveryDetails").hide(); 
  		IsDeliverySame_clicked();
  		toggleDeliveryInstructions();
  		$(".error").focus(function() {
		$	(this).removeClass("error");
		});
		<?php echo  $js; ?> 
});
</script>
<link href="../css/defaultProducts.css" rel="stylesheet"  />
<!-- InstanceEndEditable -->
</head>
<body id="OffTemplate" class="bootstrap <?php echo $body_class;  ?>">
<?php require_once('../../local/includes/header.inc.php'); ?>
<main id="content"><!-- InstanceBeginEditable name="Body" --><section>
    <div class="checkout checkout-details container"><ol class="checkoutprogress">
        <li><a href="/products/basket/"><?php echo isset($row_rsProductPrefs['text_yourorder']) ? htmlentities($row_rsProductPrefs['text_yourorder'], ENT_COMPAT, "UTF-8") : "Your Order" ?></a></li>
        <li class="selected"><a href="/products/payments/index.php"><?php echo isset($row_rsProductPrefs['text_yourdetails']) ? htmlentities($row_rsProductPrefs['text_yourdetails'], ENT_COMPAT, "UTF-8") : "Your details" ; ?></a></li>
        <li><a href="#"><?php echo isset($row_rsProductPrefs['paymenttext']) ? htmlentities($row_rsProductPrefs['paymenttext'], ENT_COMPAT, "UTF-8" ): "Payment"; ?></a></li>
        </ol>
            
			         <?php require_once('../../core/includes/alert.inc.php'); ?>
                     
                
          
          
        <div class="row">
         <div class="col-sm-6">
            <h1><?php echo isset($row_rsProductPrefs['text_yourdetails']) ? htmlentities($row_rsProductPrefs['text_yourdetails'], ENT_COMPAT, "UTF-8") : "Your Details" ?></h1>
            
<?php  if($row_rsPreferences['userscanlogin']==1) { // users can log in
if( !isset($_SESSION['MM_Username'])) {  // not logged in
			if($row_rsPreferences['userscansignup']==1 && is_readable('../../login/includes/loginform.inc.php')) { // allow sign ups ?>
<h2><?php echo isset($row_rsProductPrefs['text_returningmember']) ? htmlentities($row_rsProductPrefs['text_returningmember'], ENT_COMPAT, "UTF-8") : "Returning  member?"; ?></h2>
  <p>
    <label>
      <input type="radio" name="returning" value="0" id="Rreturning_1" <?php if(!isset($_REQUEST['returning']) ||  $_REQUEST['returning']==0) { echo "checked"; } ?> onClick="toggleLogin()">
      <?php echo isset($row_rsThisRegion['text_no']) ? htmlentities($row_rsThisRegion['text_no'], ENT_COMPAT, "UTF-8") : "No"; ?></label> &nbsp;&nbsp;&nbsp;
    <label>
      <input type="radio" name="returning" value="1" id="returning_0" <?php if(isset($_REQUEST['returning']) &&  $_REQUEST['returning']==1) { echo "checked"; } ?> onClick="toggleLogin()">
      <?php echo isset($row_rsThisRegion['text_yes']) ? htmlentities($row_rsThisRegion['text_yes'], ENT_COMPAT, "UTF-8") : "Yes"; ?></label></p>
  
    
  
<div class="login">
        <p><?php echo isset($row_rsProductPrefs['text_returningmemberinfo']) ? htmlentities($row_rsProductPrefs['text_returningmemberinfo'], ENT_COMPAT, "UTF-8") : "If you are a returning member, we should already have your address on file, so log in:"; ?></p><?php require_once('../../login/includes/loginform.inc.php'); ?></div>
        
        <div class="nologin"><!-- enter alt text here --></div>
            
<?php }// allow user sign up
		} // not logged in 
		else {  ?>
<h2><?php echo htmlentities(str_replace("{name}",$row_rsLoggedIn['firstname'],$row_rsProductPrefs['text_welcomeback']), ENT_COMPAT, "UTF-8"); ?></h2>
<p><?php $notyou = str_replace("{name}",$row_rsLoggedIn['firstname']." ".$row_rsLoggedIn['surname'],$row_rsProductPrefs['text_welcomeback']); echo str_replace("{logout}","<a href=\"/login/logout.php?returnURL=".urlencode($_SERVER['REQUEST_URI'])>"\">".$row_rsPreferences['logouttext']."</a>",$notyou);?></p>
<?php } // end not logged in 

} // end users can log in ?>
	</div>
            
            
            
            <div class="col-sm-6">
                  <div class="basketnavigation top text-right"><a class="btn btn-default btn-secondary button_continueshopping" href="/products/basket/" role="button"><?php echo isset($row_rsProductPrefs['baskettext']) ? htmlentities($row_rsProductPrefs['baskettext'], ENT_COMPAT, "UTF-8") : "Edit your basket" ?></a> <button  type="button" class="btn btn-primary button_checkout"   onClick="if(checkCustomerDetails()) document.customerform.submit();" ><?php echo isset($row_rsProductPrefs['checkouttext']) ? htmlentities($row_rsProductPrefs['checkouttext'], ENT_COMPAT, "UTF-8") : "Checkout" ?></button> </div>  
			<?php  require_once('../includes/basketcontents.inc.php'); ?>
            
            <div class="text-right"><form action="/products/basket/index.php" method="get" class="promoform form-inline">
          <p>
            <label><?php echo $row_rsProductPrefs['promocodetext']; ?>
              <input name="promocode" type="text"  id="promocode" value="<?php echo isset($_REQUEST['promocode']) ? htmlentities($_REQUEST['promocode'], ENT_COMPAT, "UTF-8") : "" ; ?>" size="20" maxlength="20" class="form-control" />
            </label>
            <button type="submit" name="add"  class="btn btn-default btn-secondary" id="button_entercode" ><?php echo $row_rsProductPrefs['promobuttontext']; ?></button>
          </p>
          
        </form><a href="/products/basket/" name="backbutton" class="btn btn-default btn-secondary" id="backbutton" role="button"  />Back to basket</a></div>
        
       </div>
            </div>
            <h2><?php echo isset($row_rsProductPrefs['text_billingdetails']) ? htmlentities($row_rsProductPrefs['text_billingdetails'], ENT_COMPAT, "UTF-8") : "Billing details" ?>:</h2>
            <form action="index.php" method="post" name="customerform" id="customerform" class="form-horizontal">
			<fieldset>
				
				
                <div class="form-group savedaddresses">
				<label class="col-sm-3 control-label"></label>
				  <div class="col-sm-9 col-md-7">
				    <select name="BillingAddressID" id="BillingAddressID" onchange="prepopulateAddress('Billing');" class="form-control">
				      <option value=" :@:@:@:@:@:@:@:@"><?php echo isset($row_rsProductPrefs['text_choosesaved']) ? htmlentities($row_rsProductPrefs['text_choosesaved'], ENT_COMPAT, "UTF-8") : "Choose a saved address..." ?></option>
				      <?php
do {  
?>
				      <option value="<?php echo $row_rsAddresses['locationname'].":@".$row_rsAddresses['address1'].":@".$row_rsAddresses['address2'].":@".$row_rsAddresses['address3'].":@".$row_rsAddresses['address4'].":@".$row_rsAddresses['address5'].":@".$row_rsAddresses['postcode'].":@".$row_rsAddresses['countryID'].":@".$row_rsAddresses['telephone1']; ?>"><?php echo isset($row_rsAddresses['locationname']) ? htmlentities($row_rsAddresses['locationname'], ENT_COMPAT, "UTF-8") : htmlentities($row_rsAddresses['address1'], ENT_COMPAT, "UTF-8") ; ?></option>
				      <?php
} while ($row_rsAddresses = mysql_fetch_assoc($rsAddresses));
  $rows = mysql_num_rows($rsAddresses);
  if($rows > 0) {
      mysql_data_seek($rsAddresses, 0);
	  $row_rsAddresses = mysql_fetch_assoc($rsAddresses);
  }
?>
                    </select>
			     </div>
			  </div>
              
               <div class="form-group group personal">
				<label class="col-sm-3 control-label" for="BillingFirstnames"><span class="required">*</span><?php echo isset($row_rsProductPrefs['text_firstname']) ? htmlentities($row_rsProductPrefs['text_firstname'], ENT_COMPAT, "UTF-8") : "First Name(s)" ?>:</label><div class="col-sm-9 col-md-7"><input name="BillingFirstnames"  id="BillingFirstnames" type="text"   value="<?php echo ($strBillingFirstnames!="") ? $strBillingFirstnames : htmlentities(@$row_rsLoggedIn['firstname'], ENT_COMPAT, "UTF-8");?>" maxlength="20" class="form-control" />
			    </div></div>
                
                
                 <div class="form-group">
				<label class="col-sm-3 control-label" for="BillingSurname"><span class="required">*</span><?php echo isset($row_rsProductPrefs['text_surname']) ? htmlentities($row_rsProductPrefs['text_surname'], ENT_COMPAT, "UTF-8") : "Surname" ?>:</label><div class="col-sm-9 col-md-7"><input name="BillingSurname"  id="BillingSurname" type="text"   value="<?php echo ($strBillingSurname !="") ? $strBillingSurname : htmlentities(@$row_rsLoggedIn['surname'], ENT_COMPAT, "UTF-8"); ?>" maxlength="20" class="form-control"/>
			     </div></div>
                 
                 
              
             <div class="form-group">
					<label class="col-sm-3 control-label" for="CustomerEMail"><span class="required">*</span><?php echo isset($row_rsProductPrefs['text_email']) ? htmlentities($row_rsProductPrefs['text_email'], ENT_COMPAT, "UTF-8") : "email" ?>:</label><div class="col-sm-9 col-md-7"><input name="CustomerEMail" id="CustomerEMail" type="email"  value="<?php echo (isset($strCustomerEMail) && $strCustomerEMail !="") ? $strCustomerEMail :   $row_rsLoggedIn['email']; ?>"   maxlength="255" class="form-control"/>
			      <?php echo isset($row_rsProductPrefs['text_emailinfo']) ? htmlentities($row_rsProductPrefs['text_emailinfo'], ENT_COMPAT, "UTF-8") : "(to send purchase confirmation)" ?>
			    </div></div>
                
                
                 <div class="form-group emailoptin">
				 <label class="col-sm-3 control-label"></label>
               <div class="col-sm-9 col-md-7"><label class="optin">
                    <input <?php if(isset($optin) && $optin ==1) echo " checked"; ?>  name="optin" type="checkbox" id="optin" value="1"  />  <?php echo $row_rsPreferences['emailoptintext']; ?> </label>
<input name="exitsingoptin" type="hidden"  value="<?php echo (isset($optin) && $optin ==1) ? 1 : 0 ; ?>"></div></div>


             <div class="form-group company">     
             <label class="col-sm-3 control-label" for="BillingCompany"><span class="required">*</span>Company:</label>
             <div class="col-sm-9 col-md-7"><input name="BillingCompany" id="BillingCompany" type="text"   value="<?php echo ($strBillingCompany !="") ? $strBillingCompany : htmlentities(@$row_rsLoggedIn['locationname'], ENT_COMPAT, "UTF-8"); ?>" maxlength="100" placeholder="If applicable"  class="form-control"/></div></div>
              
              
                    
                    
				  <div class="form-group">   
					<label class="col-sm-3 control-label" for="BillingAddress1"><span class="required">*</span><?php echo isset($row_rsProductPrefs['text_address']) ? htmlentities($row_rsProductPrefs['text_address'], ENT_COMPAT, "UTF-8") : "Address" ?>:</label><div class="col-sm-9 col-md-7"><input name="BillingAddress1" id="BillingAddress1" type="text"   value="<?php echo ($strBillingAddress1 !="") ? $strBillingAddress1 : htmlentities(@$row_rsLoggedIn['address1'], ENT_COMPAT, "UTF-8"); ?>" maxlength="100" class="form-control"/></div></div>
                    
                    
				 <div class="form-group">  
					<label class="col-sm-3 control-label" for="BillingAddress2"></label><div class="col-sm-9 col-md-7"><input name="BillingAddress2" id="BillingAddress2" type="text"   value="<?php echo ($strBillingAddress2 !="") ? $strBillingAddress2 : htmlentities(@$row_rsLoggedIn['address2'], ENT_COMPAT, "UTF-8"); ?>" maxlength="100" class="form-control"/></div></div>
                    
                    
                    
				 <div class="form-group">  
					<label class="col-sm-3 control-label" for="BillingCity"><span class="required">*</span><?php echo isset($row_rsProductPrefs['text_city']) ? htmlentities($row_rsProductPrefs['text_city'], ENT_COMPAT, "UTF-8") : "City/State" ?>:</label><div class="col-sm-9 col-md-7"><input name="BillingCity" id="BillingCity" type="text"   value="<?php echo ($strBillingCity!="") ? $strBillingCity : htmlentities(@$row_rsLoggedIn['address3'], ENT_COMPAT, "UTF-8"); ?>" maxlength="40" class="form-control"/></div></div>
                    
                    
				<div class="form-group postcode">
					<label class="col-sm-3 control-label" for="BillingPostCode"><span class="required">*</span><?php echo isset($row_rsProductPrefs['text_postcode']) ? htmlentities($row_rsProductPrefs['text_postcode'], ENT_COMPAT, "UTF-8") : "Post/ZIP code" ?>:</label><div class="col-sm-9 col-md-7"><input name="BillingPostCode"  id="BillingPostCode" type="text"   value="<?php echo ($strBillingPostCode!="") ? $strBillingPostCode : htmlentities(@$row_rsLoggedIn['postcode'], ENT_COMPAT, "UTF-8"); ?>" maxlength="10" class="form-control"/></div></div>
                    
                    
                    
				<div class="form-group">
					<label class="col-sm-3 control-label" for="BillingCountry"><span class="required">*</span><?php echo isset($row_rsProductPrefs['text_country']) ? htmlentities($row_rsProductPrefs['text_country'], ENT_COMPAT, "UTF-8") : "Country" ?>:</label><div class="col-sm-9 col-md-7"><select name="BillingCountry" id="BillingCountry" onChange="toggleBillingState();" class="form-control"><option value="" <?php if (!(strcmp("", $strBillingCountry))) {echo "selected=\"selected\"";} ?>><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option> <?php
do {  
?><option value="<?php echo $row_rsCountries['ID']; ?>"<?php if (!(strcmp($row_rsCountries['ID'], $strBillingCountry))) {echo "selected=\"selected\"";} else if($row_rsCountries['ID']==$row_rsLoggedIn['countryID']) {echo "selected=\"selected\"";} ?>><?php echo $row_rsCountries['fullname']?></option>
				        <?php
} while ($row_rsCountries = mysql_fetch_assoc($rsCountries));
  $rows = mysql_num_rows($rsCountries);
  if($rows > 0) {
      mysql_data_seek($rsCountries, 0);
	  $row_rsCountries = mysql_fetch_assoc($rsCountries);
  }
?></select></div></div>




				<div class="form-group billingState">
					<label class="col-sm-3 control-label" for="BillingState">State Code (U.S. only):</label><div class="col-sm-9 col-md-7"><input name="BillingState" id="BillingState" type="text"  style="width: 40px;" value="<?php echo $strBillingState; ?>" maxlength="2" class="form-control"/> (<span class="required">*</span>State Code for U.S. customers only)</span>
              </div>
              </div>
              
              
              
              	
				<div class="form-group telephone">
					<label class="col-sm-3 control-label" for="BillingPhone"><span class="required">*</span><?php echo isset($row_rsProductPrefs['text_telephone']) ? htmlentities($row_rsProductPrefs['text_telephone'], ENT_COMPAT, "UTF-8") : "Phone" ?>:</label><div class="col-sm-9 col-md-7"><input name="BillingPhone"  id="BillingPhone" type="tel"  value="<?php echo ($strBillingPhone!="") ? $strBillingPhone : htmlentities(@$row_rsLoggedIn['telephone1'], ENT_COMPAT, "UTF-8"); ?>" maxlength="20" class="form-control"/>	
                 </div></div>
                  
                  
                  <div class="form-group"><label class="col-sm-3 control-label" for="BillingMobile"><?php echo isset($row_rsProductPrefs['text_mobile']) ? htmlentities($row_rsProductPrefs['text_mobile'], ENT_COMPAT, "UTF-8") : "Mobile" ?>:</label><div class="col-sm-9 col-md-7"><input name="BillingMobile"  id="BillingMobile" type="tel"   value="<?php echo ($strBillingMobile!="") ? $strBillingMobile : htmlentities(@$row_rsLoggedIn['telephone'], ENT_COMPAT, "UTF-8"); ?>" maxlength="20" class="form-control"/> 
			         <?php echo isset($row_rsProductPrefs['text_mobileinfo']) ? htmlentities($row_rsProductPrefs['text_mobileinfo'], ENT_COMPAT, "UTF-8") : "" ?></div></div>
              
              
              
              <?php if(trim($row_rsProductPrefs['askvatnumber'])==1) { ?><div class="form-group"><label class="col-sm-3 control-label" for="vatnumber"><?php echo isset($row_rsProductPrefs['text_vatnumber']) ? htmlentities($row_rsProductPrefs['text_vatnumber'], ENT_COMPAT, "UTF-8") : "VAT number" ?>:</label><div class="col-sm-9 col-md-7"><input name="vatnumber"  id="vatnumber" type="text"   value="<?php echo ($vatnumber!="") ? $vatnumber : "" ?>" maxlength="50" class="form-control" /></div></div>
              <?php } ?>
              
              
             <div class="form-group purchaseorder"><label class="col-sm-3 control-label" for="purchaseorder">Purchase Order:</label><div class="col-sm-9 col-md-7"><input name="purchaseorder"  id="purchaseorder" type="text"   value="<?php echo isset($_REQUEST['purchaseorder']) ? htmlentities($_REQUEST['purchaseorder'], ENT_COMPAT, "UTF-8") : ""; ?>" maxlength="50" placeholder="(PO number, if required)" class="form-control"/></div></div>
             
              
              
              
				
				
              <?php if(trim($row_rsProductPrefs['checkoutquestion1'])!="") { ?>
				<div class="form-group extraquestion1">
				  <label class="col-sm-3 control-label"  for="checkoutanswer1"><?php echo htmlentities($row_rsProductPrefs['checkoutquestion1'], ENT_COMPAT, "UTF-8").":"; ?></label><div class="col-sm-9 col-md-7"><textarea name="checkoutanswer1"  cols="60" rows="5" class="form-control"><?php echo isset($_REQUEST['checkoutanswer1']) ? htmlentities($_REQUEST['checkoutanswer1'], ENT_COMPAT, "UTF-8") : ""; ?></textarea></div></div>
                <?php } ?>
                
                
                
				<div class="form-group howdiscovered">
				  <label class="col-sm-3 control-label" for="discovered"><?php echo isset($row_rsProductPrefs['text_howfound']) ? htmlentities($row_rsProductPrefs['text_howfound'], ENT_COMPAT, "UTF-8") : "How did you hear of us?" ?></label><div class="col-sm-9 col-md-7"><select name="discovered"  id="discovered" class="form-control">
				    <option value="0" <?php if (!(strcmp("", $discovered))) {echo "selected=\"selected\"";} ?>><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
				    <?php
do {  
?>
				    <option value="<?php echo $row_rsDiscovered['ID']?>"<?php if (!(strcmp($row_rsDiscovered['ID'], $discovered))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsDiscovered['description']?></option>
				    <?php
} while ($row_rsDiscovered = mysql_fetch_assoc($rsDiscovered));
  $rows = mysql_num_rows($rsDiscovered);
  if($rows > 0) {
      mysql_data_seek($rsDiscovered, 0);
	  $row_rsDiscovered = mysql_fetch_assoc($rsDiscovered);
  }
?>
			      </select></div></div>
			</fieldset>
            
            
            
		  <h2 class="subheader"><?php echo isset($row_rsProductPrefs['text_deliverydetails']) ? htmlentities($row_rsProductPrefs['text_deliverydetails'], ENT_COMPAT, "UTF-8") : "Delivery details" ?>:</h2>
               <fieldset>
				
				<div class="form-group  deliveryAddress"><label class="col-sm-3 control-label" for=""><?php echo isset($row_rsProductPrefs['text_deliveryaddress']) ? htmlentities($row_rsProductPrefs['text_deliveryaddress'], ENT_COMPAT, "UTF-8") : "Delivery address" ?>:</label><div class="col-sm-9 col-md-7">
			          <label class="text-nowrap">
			            <input type="radio" name="IsDeliverySame" value="1" id="IsDeliverySame_0" <?php if($bIsDeliverySame==1) echo "checked=\"checked\"";  ?>  onclick="IsDeliverySame_clicked()" />
			            <?php echo isset($row_rsProductPrefs['text_sameasbilling']) ? htmlentities($row_rsProductPrefs['text_sameasbilling'], ENT_COMPAT, "UTF-8") : "Same as billing" ?></label>&nbsp;&nbsp;&nbsp;
			          
			          <label class="text-nowrap">
			            <input type="radio" name="IsDeliverySame" value="0" id="IsDeliverySame_1" <?php if($bIsDeliverySame==0) echo "checked=\"checked\"";  ?> onclick="IsDeliverySame_clicked()"/>
			            <?php echo isset($row_rsProductPrefs['text_differentaddress']) ? htmlentities($row_rsProductPrefs['text_differentaddress'], ENT_COMPAT, "UTF-8") : "Different address" ?></label>&nbsp;&nbsp;&nbsp;
                        
                         <label class="allowcollection text-nowrap" style="width:auto;" ><input type="radio" name="IsDeliverySame" value="2" id="IsDeliverySame_2" <?php if($bIsDeliverySame==2) echo "checked=\"checked\"";  ?> onclick="IsDeliverySame_clicked()" />
		            <?php echo isset($row_rsProductPrefs['text_willcollectfrom']) ? htmlentities($row_rsProductPrefs['text_willcollectfrom'], ENT_COMPAT, "UTF-8") : "Will collect from" ?> <?php echo isset($row_rsProductPrefs['collectionaddress']) ? $row_rsProductPrefs['collectionaddress'] : ""; ?></label>&nbsp;&nbsp;&nbsp;  
			      
                  
                  <label class="text-nowrap delivery">
			            <input type="checkbox" name="showdeliveryinstructions" value="1" id="showdeliveryinstructions" <?php if(isset($_REQUEST['showdeliveryinstructions']) || (isset($deliveryinstructions) && trim($deliveryinstructions) !="")) echo "checked=\"checked\"";  ?> onclick="toggleDeliveryInstructions()" />
			            <?php echo isset($row_rsProductPrefs['text_addspecialinstructions']) ? htmlentities($row_rsProductPrefs['text_addspecialinstructions'], ENT_COMPAT, "UTF-8") : "Add Special Instructions" ?></label> 
                        <div id="deliveryinstructionsbox" class="delivery"><?php echo isset($row_rsProductPrefs['text_deliveryinstructions']) ? htmlentities($row_rsProductPrefs['text_deliveryinstructions'], ENT_COMPAT, "UTF-8") : "Delivery instructions" ?>:<br /><textarea name="deliveryinstructions" cols="60" rows="5" class="form-control"><?php echo isset($deliveryinstructions) ? htmlentities($deliveryinstructions, ENT_COMPAT, "UTF-8")  : ""; ?></textarea></div>
	           </div>
				</div></fieldset>
                
                
                
                
                <fieldset id="deliveryDetails">
             
				<div class="form-group deliveryAddress savedaddresses"><label class="col-sm-3 control-label"></label><div class="col-sm-9 col-md-7"><select name="DeliveryAddressID" id="DeliveryAddressID" onchange="prepopulateAddress('Delivery');" class="form-control" >
				    <option value=" :@:@:@:@:@:@:@:@:@"><?php echo isset($row_rsProductPrefs['text_choosesaved']) ? htmlentities($row_rsProductPrefs['text_choosesaved'], ENT_COMPAT, "UTF-8") : "Choose a saved address..." ?></option>
				    <?php
do {  
?>
				    <option value="<?php echo $row_rsAddresses['locationname'].":@".$row_rsAddresses['address1'].":@".$row_rsAddresses['address2'].":@".$row_rsAddresses['address3'].":@".$row_rsAddresses['address4'].":@".$row_rsAddresses['address5'].":@".$row_rsAddresses['postcode'].":@".$row_rsAddresses['countryID'].":@".$row_rsAddresses['telephone1']; ?>"><?php echo isset($row_rsAddresses['locationname']) ? htmlentities($row_rsAddresses['locationname'], ENT_COMPAT, "UTF-8") : htmlentities($row_rsAddresses['address1'], ENT_COMPAT, "UTF-8") ; ?></option>
				    <?php
} while ($row_rsAddresses = mysql_fetch_assoc($rsAddresses));
  $rows = mysql_num_rows($rsAddresses);
  if($rows > 0) {
      mysql_data_seek($rsAddresses, 0);
	  $row_rsAddresses = mysql_fetch_assoc($rsAddresses);
  }
?>
			      </select></div></div>
                  
                  
                  
                  
				<div class="form-group deliveryAddress">
					<label class="col-sm-3 control-label" for="DeliveryFirstnames"><span class="required">*</span><?php echo isset($row_rsProductPrefs['text_firstname']) ? htmlentities($row_rsProductPrefs['text_firstname'], ENT_COMPAT, "UTF-8") : "First Name(s)" ?>:</label><div class="col-sm-9 col-md-7"><input name="DeliveryFirstnames" id="DeliveryFirstnames" type="text"   value="<?php echo $strDeliveryFirstnames ?>" maxlength="20" class="form-control"/></div></div>
                    
                    
                    
				<div class="form-group deliveryAddress">
					<label class="col-sm-3 control-label" for="DeliverySurname"><span class="required">*</span><?php echo isset($row_rsProductPrefs['text_surname']) ? htmlentities($row_rsProductPrefs['text_surname'], ENT_COMPAT, "UTF-8") : "Surname" ?>:</label><div class="col-sm-9 col-md-7"><input name="DeliverySurname"  id="DeliverySurname" type="text"   value="<?php echo $strDeliverySurname ?>" maxlength="20" class="form-control"/></div></div>
                
                
                <div class="form-group deliveryAddress company">
					<label class="col-sm-3 control-label" for="DeliveryCompany"><span class="required">*</span>Company:</label><div class="col-sm-9 col-md-7"><input name="DeliveryCompany" id="DeliveryCompany" type="text"   value="<?php echo ($strDeliveryCompany !="") ? $strDeliveryCompany : ""; ?>" maxlength="100" placeholder="If applicable" class="form-control"/></div></div>
              
              
				<div class="form-group deliveryAddress">
					<label class="col-sm-3 control-label" for="DeliveryAddress1"><span class="required">*</span><?php echo isset($row_rsProductPrefs['text_address']) ? htmlentities($row_rsProductPrefs['text_address'], ENT_COMPAT, "UTF-8") : "Address" ?>:</label><div class="col-sm-9 col-md-7"><input name="DeliveryAddress1"  id="DeliveryAddress1" type="text"   value="<?php echo $strDeliveryAddress1 ?>" maxlength="100" class="form-control"/></div></div>
				
                
                <div class="form-group deliveryAddress">
					<label class="col-sm-3 control-label">&nbsp;</label><div class="col-sm-9 col-md-7"><input name="DeliveryAddress2" id="DeliveryAddress2" type="text"   value="<?php echo $strDeliveryAddress2 ?>" maxlength="100" class="form-control"/></div></div>
                    
                    
                    
				<div class="form-group deliveryAddress">
					<label class="col-sm-3 control-label" for="DeliveryCity"><span class="required">*</span><?php echo isset($row_rsProductPrefs['text_city']) ? htmlentities($row_rsProductPrefs['text_city'], ENT_COMPAT, "UTF-8") : "City" ?>:</label><div class="col-sm-9 col-md-7"><input name="DeliveryCity" id="DeliveryCity"  type="text"   value="<?php echo $strDeliveryCity ?>" maxlength="40" class="form-control"/></div></div>
                    
                    
                    
				<div class="form-group deliveryAddress postcode">
					<label class="col-sm-3 control-label" for="DeliveryPostCode"><span class="required">*</span><?php echo isset($row_rsProductPrefs['text_postcode']) ? htmlentities($row_rsProductPrefs['text_postcode'], ENT_COMPAT, "UTF-8") : "Post/ZIP code" ?>:</label><div class="col-sm-9 col-md-7"><input name="DeliveryPostCode" id="DeliveryPostCode" type="text"  value="<?php echo $strDeliveryPostCode ?>" maxlength="10" class="form-control" /></div></div>
                    
                    
                    
				<div class="form-group deliveryAddress">
					<label class="col-sm-3 control-label" for="DeliveryCountry"><span class="required">*</span><?php echo isset($row_rsProductPrefs['text_country']) ? htmlentities($row_rsProductPrefs['text_country'], ENT_COMPAT, "UTF-8") : "Country" ?>:</label><div class="col-sm-9 col-md-7"><select name="DeliveryCountry" id="DeliveryCountry"  onChange="toggleDeliveryState()" class="form-control">
				      <option value="" <?php if (!(strcmp("", $strDeliveryCountry))) {echo "selected=\"selected\"";} ?>><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
				      <?php
do {  
?>
				      <option value="<?php echo $row_rsCountries['ID']?>"<?php if (!(strcmp($row_rsCountries['ID'], $strDeliveryCountry))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsCountries['fullname']?></option>
				      <?php
} while ($row_rsCountries = mysql_fetch_assoc($rsCountries));
  $rows = mysql_num_rows($rsCountries);
  if($rows > 0) {
      mysql_data_seek($rsCountries, 0);
	  $row_rsCountries = mysql_fetch_assoc($rsCountries);
  }
?></select></div></div>


				<div class="form-group deliveryState">
					<label class="col-sm-3 control-label" for="DeliveryState">State Code (U.S. only):</label><div class="col-sm-9 col-md-7"><input name="DeliveryState" id="DeliveryState" type="text"  style="width: 40px;" value="<?php echo $strDeliveryState ?>" maxlength="2" class="form-control"/> (<span class="required">*</span>State Code for U.S. customers only)</div></div>
                    
                    
                    
				<div class="form-group deliveryAddress">
					<label class="col-sm-3 control-label" for="DeliveryPhone"><?php echo isset($row_rsProductPrefs['text_telephone']) ? htmlentities($row_rsProductPrefs['text_telephone'], ENT_COMPAT, "UTF-8") : "Phone" ?>:</label><div class="col-sm-9 col-md-7"><input name="DeliveryPhone"  id="DeliveryPhone" type="tel"   value="<?php echo $strDeliveryPhone ?>" maxlength="20" class="form-control"/></div></div></fieldset>
                    
                    
                <?php if(isset($row_rsProductPrefs['deliverytimes1']) && trim($row_rsProductPrefs['deliverytimes1']) !="") { ?>
			<p><?php echo  htmlentities($row_rsProductPrefs['text_delivery_time'], ENT_COMPAT, "UTF-8"); ?>: <label><input type="radio" name="deliverytime" value="<?php echo htmlentities($row_rsProductPrefs['deliverytimes1'],ENT_COMPAT, "UTF-8"); ?>">&nbsp;<?php echo htmlentities($row_rsProductPrefs['deliverytimes1'],ENT_COMPAT, "UTF-8"); ?></label>&nbsp;&nbsp;&nbsp; 
            <?php if(trim($row_rsProductPrefs['deliverytimes2']) !="") { ?><label><input type="radio" name="deliverytime" value="<?php echo htmlentities($row_rsProductPrefs['deliverytimes2'],ENT_COMPAT, "UTF-8"); ?>">&nbsp;<?php echo htmlentities($row_rsProductPrefs['deliverytimes2'],ENT_COMPAT, "UTF-8"); ?></label>&nbsp;&nbsp;&nbsp; 
            <?php } ?>
            
            <?php if(trim($row_rsProductPrefs['deliverytimes3']) !="") { ?><label><input type="radio" name="deliverytime" value="<?php echo htmlentities($row_rsProductPrefs['deliverytimes3'],ENT_COMPAT, "UTF-8"); ?>">&nbsp;<?php echo htmlentities($row_rsProductPrefs['deliverytimes3'],ENT_COMPAT, "UTF-8"); ?></label>&nbsp;&nbsp;&nbsp; 
            <?php } ?>
            
            </p>		
			<?php 	}
           echo isset($row_rsProductPrefs['shippingnotes']) ? "<p class=\"shippingnotes delivery\">".$row_rsProductPrefs['shippingnotes']."</p>" : ""; ?>
          <div id="paymentNav">
          <?php $termsURL = (isset($row_rsPreferences['termsarticleID']) && $row_rsPreferences['termsarticleID']>0) ? "/articles/article.php?articleID=".intval($row_rsPreferences['termsarticleID']) : "/Legal/Terms-and-Conditions/";  ?>
          <fieldset>
         <table  class="terms" ><tbody><tr><td>
            <input <?php if (isset($_REQUEST['termsagree'])) echo "checked=\"checked\""; ?> name="termsagree" type="checkbox" id="termsagree" value="1" /><input name="checkouttermsagree" id="checkouttermsagree" type="hidden" value="<?php echo $row_rsProductPrefs['checkouttermsagree']; ?>"> </td><td><label for="termsagree"><?php if(isset($row_rsPreferences['termsagreetext']) && trim($row_rsPreferences['termsagreetext'])!="") { echo $row_rsPreferences['termsagreetext']; } else { ?>
            I agree to this site's <a href="<?php echo $termsURL; ?>" title="Click to view our web site use terms and conditions." target="_blank" id="terms" rel="noopener" onclick="javascript:MM_openBrWindow('<?php echo $termsURL; ?>','terms','scrollbars=yes,width=400,height=400'); return false;">terms and conditions</a><?php } ?></label></td></tr></tbody></table></fieldset>
            <div id="paymentmethods">
       <?php if(is_readable(SITE_ROOT."local/includes/payment_methods.inc.php")) {
		   require_once(SITE_ROOT."local/includes/payment_methods.inc.php");
	   } else { $defaultpaymentmethod = isset($defaultpaymentmethod) ? $defaultpaymentmethod : "creditcard";
		  if(isset($row_rsPurchaseAccount['payaccount']) &&  $row_rsPurchaseAccount['payaccount']==1) $defaultpaymentmethod = "invoice";  ?>
                &nbsp;&nbsp;&nbsp;<?php echo isset($row_rsProductPrefs['text_payby']) ? htmlentities($row_rsProductPrefs['text_payby'], ENT_COMPAT, "UTF-8") : "Pay by" ?>:
        <label id="optioncreditcard"><input <?php if((isset($_POST["paymentmethodID"]) && $_POST["paymentmethodID"]=="creditcard") || (!isset($_POST["paymentmethodID"]) && isset($defaultpaymentmethod) && $defaultpaymentmethod== "creditcard")) {echo "checked=\"checked\"";} ?> type="radio" name="paymentmethodID" value="creditcard" id="paymentmethodID_0" />
                    <?php echo isset($row_rsProductPrefs['text_creditcard']) ? htmlentities($row_rsProductPrefs['text_creditcard'], ENT_COMPAT, "UTF-8") : "Credit/Debit Card" ?>&nbsp;&nbsp;&nbsp;</label>
                    <label id="optionpaypal">
                    <input <?php if((isset($_POST["paymentmethodID"]) && $_POST["paymentmethodID"]=="paypal") || (!isset($_POST["paymentmethodID"]) && isset($defaultpaymentmethod) && $defaultpaymentmethod== "paypal")) {echo "checked=\"checked\"";} ?> type="radio" name="paymentmethodID" value="paypal" id="paymentmethodID_1" />
                    PayPal&nbsp;&nbsp;&nbsp;</label>
                    <label id="optioncheque">
                    <input <?php if((isset($_POST["paymentmethodID"]) && $_POST["paymentmethodID"]=="cheque") || (!isset($_POST["paymentmethodID"]) && isset($defaultpaymentmethod) && $defaultpaymentmethod== "cheque")) {echo "checked=\"checked\"";} ?> type="radio" name="paymentmethodID" value="cheque" id="paymentmethodID_2" />
                    Cheque/Postal order&nbsp;&nbsp;&nbsp;</label>
                    <label id="optioncashdelivery">
                    <input <?php if((isset($_POST["paymentmethodID"]) && $_POST["paymentmethodID"]=="cashdelivery") || (!isset($_POST["paymentmethodID"]) && isset($defaultpaymentmethod) && $defaultpaymentmethod== "cashdelivery")) {echo "checked=\"checked\"";} ?> type="radio" name="paymentmethodID" value="cashdelivery" id="paymentmethodID_3" />
                    Cash on delivery&nbsp;&nbsp;&nbsp;</label>
                    <label id="optioncashcollection">
                    <input <?php if((isset($_POST["paymentmethodID"]) && $_POST["paymentmethodID"]=="cashcollection") || (!isset($_POST["paymentmethodID"]) && isset($defaultpaymentmethod) && $defaultpaymentmethod== "cashcollection")) {echo "checked=\"checked\"";} ?> type="radio" name="paymentmethodID" value="cashcollection" id="paymentmethodID_4" />
                    Cash on collection&nbsp;&nbsp;&nbsp;</label>
                    <label id="optioninvoice"><input <?php if((isset($_POST["paymentmethodID"]) && $_POST["paymentmethodID"]=="invoice") || (!isset($_POST["paymentmethodID"]) && isset($defaultpaymentmethod) && $defaultpaymentmethod== "invoice")) {echo "checked=\"checked\"";} ?> type="radio" name="paymentmethodID" value="invoice" id="paymentmethodID_5" />
                    Account (<?php echo isset($row_rsProductPrefs['text_invoice']) ? htmlentities($row_rsProductPrefs['text_invoice'], ENT_COMPAT, "UTF-8") : "Invoice"; ?>)</label>&nbsp;&nbsp;&nbsp;
                <?php } // end not include  ?>   
               </div>
               
               
            <span  class="shippingOptions delivery form-inline" > 
             <?php if($row_rsProductPrefs['shippingautocalc']==1) { 
			 // automatically calculated based on address - show just standard / express options ?>
            <select name="shippingoptionID" id="shippingoptionID" class="form-control">
				    <option value="1" <?php if (!(strcmp(1, @$_REQUEST['shippingoptionID']))) {echo "selected=\"selected\"";} ?>>Standard shipping</option>
				    <option value="2" <?php if (!(strcmp(2, @$_REQUEST['shippingoptionID']))) {echo "selected=\"selected\"";} ?>>Express shipping</option>
            </select>
			      <button type="button" name="calculateShipping" id="calculateShipping"  onclick="javascript:submitForm('customerform','calculate'); return false;" class="btn btn-default btn-secondary" ><?php echo isset($row_rsProductPrefs['text_calcshipping']) ? htmlentities($row_rsProductPrefs['text_calcshipping'], ENT_COMPAT, "UTF-8") : "Calculate shipping" ?></button><?php } else {
					  
					  // full shipping rates shown for user selection
					  // to do ?>
					<p>  <select name="shippingrateID" id="shippingrateID" class="form-control">
					    <option value="">Choose shipping rate...</option>
					    <?php 
						// do we have free shipping?
						$exempt = 0;
						foreach($items as $key => $item) {
							$exempt += $item['shippingexempt'];
						}
						if($freeshipping==0 && $exempt == count($items)) { $freeshipping=1; }
do {  
?>
					    <option value="<?php echo $row_rsShippingRates['ID']; ?>" <?php if (!(strcmp($row_rsShippingRates['ID'], @$_SESSION['shippingrateID']))) { echo "selected=\"selected\"";} ?>><?php echo $row_rsShippingRates['shippingname']; 
						if($row_rsShippingRates['promotion']==1 
						&& @$freeshipping > 0) 
						{ echo " (FREE)"; } 
						else { echo  " ".$row_rsThisRegion['currencycode']." ".$row_rsShippingRates['shippingrate']; } ?></option>
					    <?php
} while ($row_rsShippingRates = mysql_fetch_assoc($rsShippingRates));
  $rows = mysql_num_rows($rsShippingRates);
  if($rows > 0) {
      mysql_data_seek($rsShippingRates, 0);
	  $row_rsShippingRates = mysql_fetch_assoc($rsShippingRates);
  }
?>
	        </select></p>
			   <?php   } ?></span>
              
               
               
               
              
                
                
                
                
                <div class="basketnavigation bottom">
       
           
              <a role="button" class="btn btn-default btn-secondary button_continueshopping" href="/products/basket/"><?php echo isset($row_rsProductPrefs['baskettext']) ? htmlentities($row_rsProductPrefs['baskettext'], ENT_COMPAT, "UTF-8") : "Your Order" ?></a>
        
          <input name="noshipinternational" type="hidden" value="<?php echo $noshipinternational; ?>" />
        <input type="hidden" name="navigate" value="proceed" />
                <button name="proceed" type="submit" class="btn btn-primary button_checkout"  border="0" onClick="return checkCustomerDetails();" ><?php echo isset($row_rsProductPrefs['checkouttext']) ? htmlentities($row_rsProductPrefs['checkouttext'], ENT_COMPAT, "UTF-8") : "Checkout" ?></button>
                
          
          
        </div>
        
        
        
                
                
                
            </div>
			</form>
            </div></section>
           
		
<!-- InstanceEndEditable --></main>
<?php require_once('../../local/includes/footer.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php


mysql_free_result($rsAddresses);

mysql_free_result($rsProductPrefs);

mysql_free_result($rsCountries);

mysql_free_result($rsLoggedIn);

mysql_free_result($rsShippingRates);

mysql_free_result($rsPreferences);

mysql_free_result($rsExpressRates);

mysql_free_result($rsDiscovered);

mysql_free_result($rsPurchaseAccount);
?>
