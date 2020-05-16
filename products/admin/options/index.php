<?php require_once('../../../Connections/aquiescedb.php'); ?><?php require_once('../../../core/includes/framework.inc.php'); ?><?php require_once('../../../core/includes/adminAccess.inc.php');  ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "8,9,10";
$MM_donotCheckaccess = "false";

// *** Restrict Access To Page: Grant or deny access to this page
function isAuthorized($strUsers, $strGroups, $UserName, $UserGroup) { 
  // For security, start by assuming the visitor is NOT authorized. 
  $isValid = False; 

  // When a visitor has logged into this site, the Session variable MM_Username set equal to their username. 
  // Therefore, we know that a user is NOT logged in if that Session variable is blank. 
  if (!empty($UserName)) { 
    // Besides being logged in, you may restrict access to only certain users based on an ID established when they login. 
    // Parse the strings into arrays. 
    $arrUsers = Explode(",", $strUsers); 
    $arrGroups = Explode(",", $strGroups); 
    if (in_array($UserName, $arrUsers)) { 
      $isValid = true; 
    } 
    // Or, you may restrict access to only certain users based on their username. 
    if (in_array($UserGroup, $arrGroups)) { 
      $isValid = true; 
    } 
    if (($strUsers == "") && false) { 
      $isValid = true; 
    } 
  } 
  return $isValid; 
}

$MM_restrictGoTo = "/login/index.php?notloggedin=true";
if (!((isset($_SESSION['MM_Username'])) && (isAuthorized("",$MM_authorizedUsers, $_SESSION['MM_Username'], $_SESSION['MM_UserGroup'])))) {   
  $MM_qsChar = "?";
  $MM_referrer = $_SERVER['PHP_SELF'];
  if (strpos($MM_restrictGoTo, "?")) $MM_qsChar = "&";
  if (isset($_SERVER['QUERY_STRING']) && strlen($_SERVER['QUERY_STRING']) > 0) 
  $MM_referrer .= "?" . $_SERVER['QUERY_STRING'];
  $MM_restrictGoTo = $MM_restrictGoTo. $MM_qsChar . "accesscheck=" . urlencode($MM_referrer);
  header("Location: ". $MM_restrictGoTo); 
  exit;
}

?>
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

$regionID = isset($regionID) ? $regionID : 1;



$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE productprefs SET shopTitle=%s, successURL=%s, failURL=%s, paymentproviderID=%s, paymentclientID=%s, paymentclientcode=%s, paymentclientpassword=%s, paypalID=%s, cashdelivery=%s, cashcollection=%s, invoice=%s, invoiceURL=%s, cheque=%s, bacsdetails=%s, shopstatus=%s, basketpageURL=%s, moreinfotext=%s, addtobasket=%s, baskettext=%s, updatebaskettext=%s, checkouttext=%s, continueshoppingtext=%s, promocodetext=%s, promobuttontext=%s, quantitytext=%s, itemtext=%s, pricetext=%s, paymenttext=%s, taxname=%s, subtotaltext=%s, grandtotaltext=%s, stockcontrol=%s, stocklowamount=%s, saleends=%s, basketshowpricem2=%s, basketshowupdatequantity=%s, basketshowadjustablequantity=%s, basketshowremove=%s, basketshowweight=%s, buyposition=%s, askhowdiscovered=%s, basketrelatedcategoryID=%s, checkoutmandatorytelephone=%s, checkouttermsagree=%s, checkoutquestion1=%s, checkoutconfirmfooter=%s, text_yourorder=%s, text_usingpromo=%s, text_promonotexists=%s, text_bagitems=%s, text_noitems=%s, text_update=%s, text_remove=%s, text_yourdetails=%s, text_firstname=%s, text_surname=%s, text_email=%s, text_emailinfo=%s, text_address=%s, text_city=%s, text_postcode=%s, text_country=%s, text_telephone=%s, text_mobile=%s, text_mobileinfo=%s, text_howfound=%s, text_billingdetails=%s, text_choosesaved=%s, text_deliverydetails=%s, text_deliveryaddress=%s, text_sameasbilling=%s, text_differentaddress=%s, text_willcollectfrom=%s, text_addspecialinstructions=%s, text_deliveryinstructions=%s, text_calcshipping=%s, text_payby=%s, text_creditcard=%s, text_ordersummary=%s, text_myaccount=%s, text_addresserror=%s, text_confirmremovebasket=%s, text_returningmember=%s, text_returningmemberinfo=%s, text_invoice=%s, text_viewinvoice=%s, text_welcomeback=%s, text_notyou=%s, text_mypurchases=%s, askpostcode=%s, askbillingdetails=%s, askcompany=%s WHERE ID=%s",
                       GetSQLValueString($_POST['shopTitle'], "text"),
                       GetSQLValueString($_POST['successURL'], "text"),
                       GetSQLValueString($_POST['failURL'], "text"),
                       GetSQLValueString($_POST['paymentproviderID'], "int"),
                       GetSQLValueString($_POST['paymentclientID'], "text"),
                       GetSQLValueString($_POST['paymentclientcode'], "text"),
                       GetSQLValueString($_POST['paymentclientpassword'], "text"),
                       GetSQLValueString($_POST['paypalID'], "text"),
                       GetSQLValueString(isset($_POST['cashdelivery']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['cashcollection']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['invoice']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['invoiceURL'], "text"),
                       GetSQLValueString(isset($_POST['cheque']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['bacsdetails'], "text"),
                       GetSQLValueString($_POST['shopstatus'], "int"),
                       GetSQLValueString($_POST['basketpageURL'], "text"),
                       GetSQLValueString($_POST['moreinfotext'], "text"),
                       GetSQLValueString($_POST['addtobasket'], "text"),
                       GetSQLValueString($_POST['baskettext'], "text"),
                       GetSQLValueString($_POST['updatebaskettext'], "text"),
                       GetSQLValueString($_POST['checkouttext'], "text"),
                       GetSQLValueString($_POST['continueshoppingtext'], "text"),
                       GetSQLValueString($_POST['promocodetext'], "text"),
                       GetSQLValueString($_POST['promobuttontext'], "text"),
                       GetSQLValueString($_POST['quantitytext'], "text"),
                       GetSQLValueString($_POST['itemtext'], "text"),
                       GetSQLValueString($_POST['pricetext'], "text"),
                       GetSQLValueString($_POST['paymenttext'], "text"),
                       GetSQLValueString($_POST['taxname'], "text"),
                       GetSQLValueString($_POST['subtotaltext'], "text"),
                       GetSQLValueString($_POST['grandtotaltext'], "text"),
                       GetSQLValueString(isset($_POST['stockcontrol']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['stocklowamount'], "int"),
                       GetSQLValueString($_POST['saleends'], "date"),
                       GetSQLValueString(isset($_POST['basketshowpricem2']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['basketshowupdatequantity']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['basketshowadjustablequantity']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['basketshowremove']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['basketshowweight']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['buyposition'], "int"),
                       GetSQLValueString(isset($_POST['askhowdiscovered']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['basketrelatedcategoryID'], "int"),
                       GetSQLValueString(isset($_POST['checkoutmandatorytelephone']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['checkouttermsagree']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['checkoutquestion1'], "text"),
                       GetSQLValueString($_POST['checkoutconfirmfooter'], "text"),
                       GetSQLValueString($_POST['text_yourorder'], "text"),
                       GetSQLValueString($_POST['text_usingpromo'], "text"),
                       GetSQLValueString($_POST['text_promonotexists'], "text"),
                       GetSQLValueString($_POST['text_bagitems'], "text"),
                       GetSQLValueString($_POST['text_noitems'], "text"),
                       GetSQLValueString($_POST['text_update'], "text"),
                       GetSQLValueString($_POST['text_remove'], "text"),
                       GetSQLValueString($_POST['text_yourdetails'], "text"),
                       GetSQLValueString($_POST['text_firstname'], "text"),
                       GetSQLValueString($_POST['text_surname'], "text"),
                       GetSQLValueString($_POST['text_email'], "text"),
                       GetSQLValueString($_POST['text_emailinfo'], "text"),
                       GetSQLValueString($_POST['text_address'], "text"),
                       GetSQLValueString($_POST['text_city'], "text"),
                       GetSQLValueString($_POST['text_postcode'], "text"),
                       GetSQLValueString($_POST['text_country'], "text"),
                       GetSQLValueString($_POST['text_telephone'], "text"),
                       GetSQLValueString($_POST['text_mobile'], "text"),
                       GetSQLValueString($_POST['text_mobileinfo'], "text"),
                       GetSQLValueString($_POST['text_howfound'], "text"),
                       GetSQLValueString($_POST['text_billingdetails'], "text"),
                       GetSQLValueString($_POST['text_choosesaved'], "text"),
                       GetSQLValueString($_POST['text_deliverydetails'], "text"),
                       GetSQLValueString($_POST['text_deliveryaddress'], "text"),
                       GetSQLValueString($_POST['text_sameasbilling'], "text"),
                       GetSQLValueString($_POST['text_differentaddress'], "text"),
                       GetSQLValueString($_POST['text_willcollectfrom'], "text"),
                       GetSQLValueString($_POST['text_addspecialinstructions'], "text"),
                       GetSQLValueString($_POST['text_deliveryinstructions'], "text"),
                       GetSQLValueString($_POST['text_calcshipping'], "text"),
                       GetSQLValueString($_POST['text_payby'], "text"),
                       GetSQLValueString($_POST['text_creditcard'], "text"),
                       GetSQLValueString($_POST['text_ordersummary'], "text"),
                       GetSQLValueString($_POST['text_myaccount'], "text"),
                       GetSQLValueString($_POST['text_addresserror'], "text"),
                       GetSQLValueString($_POST['text_confirmremovebasket'], "text"),
                       GetSQLValueString($_POST['text_returningmember'], "text"),
                       GetSQLValueString($_POST['text_returningmemberinfo'], "text"),
                       GetSQLValueString($_POST['text_invoice'], "text"),
                       GetSQLValueString($_POST['text_viewinvoice'], "text"),
                       GetSQLValueString($_POST['text_welcomeback'], "text"),
                       GetSQLValueString($_POST['text_notyou'], "text"),
                       GetSQLValueString($_POST['text_mypurchases'], "text"),
                       GetSQLValueString(isset($_POST['askpostcode']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['askbillingdetails']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['askcompany']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}


mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsProductPrefs = "SELECT * FROM productprefs WHERE ID = ".$regionID."";
$rsProductPrefs = mysql_query($query_rsProductPrefs, $aquiescedb) or die(mysql_error());
$row_rsProductPrefs = mysql_fetch_assoc($rsProductPrefs);
$totalRows_rsProductPrefs = mysql_num_rows($rsProductPrefs);

$varRegionID_rsCategories = "1";
if (isset($regionID)) {
  $varRegionID_rsCategories = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsCategories = sprintf("SELECT ID, title FROM productcategory WHERE statusID = 1 AND regionID = %s ORDER BY title", GetSQLValueString($varRegionID_rsCategories, "int"));
$rsCategories = mysql_query($query_rsCategories, $aquiescedb) or die(mysql_error());
$row_rsCategories = mysql_fetch_assoc($rsCategories);
$totalRows_rsCategories = mysql_num_rows($rsCategories);

if($totalRows_rsProductPrefs==0) { 
if($regionID == 1) {
	$insert= "INSERT INTO productprefs (ID) VALUES (1)";
	mysql_query($insert, $aquiescedb) or die(mysql_error());
} else {
	if(duplicateMySQLRecord ("productprefs", 1, "ID", $regionID)) {
		header("location: index.php"); exit;
	}
}
	
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
	 $updateGoTo = (isset($_POST['returnURL']) && $_POST['returnURL'] != "") ? $_POST['returnURL'] : "../index.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo));exit;
}
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Payment and Shipping"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script src="../../../SpryAssets/SpryTabbedPanels.js"></script><script src="/core/scripts/formUpload.js"></script>
<script src="../../../SpryAssets/SpryValidationTextField.js"></script>
<link href="../../../SpryAssets/SpryTabbedPanels.css" rel="stylesheet"  />

<link href="../../../SpryAssets/SpryValidationTextField.css" rel="stylesheet"  /><?php require_once('../../../core/tinymce/tinymce.inc.php'); ?>
<link href="../../css/defaultProducts.css" rel="stylesheet"  />
<script src="/core/scripts/date-picker/js/datepicker.js"></script>
<?php if(isset($body_class)) $body_class .= " products ";  ?>
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" -->
    <?php require_once('../../../core/region/includes/chooseregion.inc.php'); ?><?php require_once('../../../core/includes/alert.inc.php'); ?>
<form action="<?php echo $editFormAction; ?>" method="post" enctype="multipart/form-data" name="form1" id="form1">
 <h1><i class="glyphicon glyphicon-shopping-cart"></i> Payment &amp; Shipping</h1>
 <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
   <li><a href="/products/admin/" class="link_undo"><i class="glyphicon glyphicon-arrow-left"></i> Manage Shop</a></li>
   
     <li><a href="/products/admin/shipping/" ><i class="glyphicon glyphicon-gift"></i> Shipping</a></li>
      <li><a href="/products/admin/subscriptions/index.php" ><i class="glyphicon glyphicon-calendar"></i> Subscriptions</a></li>
   <li><a href="/products/admin/options/prices.php" class="link_manage"><i class="glyphicon glyphicon-gbp"></i> Prices</a></li>
  <li><a href="accounts.php" ><i class="glyphicon glyphicon-user"></i> Purchase Accounts</a></li>
  
          <li><a href="/products/" target="_blank" rel="noopener"  onClick="openMainWindow('/products/'); return false;"><i class="glyphicon glyphicon-new-window"></i> Go to Shop</a></li>
    
 </ul></div></nav>
    <div id="TabbedPanels1" class="TabbedPanels">
      <ul class="TabbedPanelsTabGroup">
        <li class="TabbedPanelsTab" tabindex="0">General</li>
        <li class="TabbedPanelsTab" tabindex="0">Basket</li>
        <li class="TabbedPanelsTab" tabindex="0">Checkout</li>
        <li class="TabbedPanelsTab" tabindex="0">Payments</li>
<li class="TabbedPanelsTab" tabindex="0">Transaction Success</li>
</ul>
      <div class="TabbedPanelsContentGroup">
        <div class="TabbedPanelsContent">
          <table  class="form-table">
            <tr>
              <td align="right">Shop title:</td>
              <td><input name="shopTitle" type="text"  id="shopTitle" value="<?php echo $row_rsProductPrefs['shopTitle']; ?>" size="50" maxlength="50" class="form-control" /></td>
            </tr>
            <tr>
              <td align="right"><label>Status:</label></td>
              <td><label>
                <input <?php if (!(strcmp($row_rsProductPrefs['shopstatus'],"1"))) {echo "checked=\"checked\"";} ?> type="radio" name="shopstatus" id="shopstatus1" value="1" />
                Open</label> &nbsp;&nbsp;
                <label><input <?php if (!(strcmp($row_rsProductPrefs['shopstatus'],"0"))) {echo "checked=\"checked\"";} ?> type="radio" name="shopstatus" id="shopstatus0" value="0" />
                Closed (Administrators can view shop in 'sandbox' mode when logged in)</label></td>
            </tr>
            
            <tr>
              <td align="right">Use in-built stock control:</td>
              <td class="form-inline"><input <?php if (!(strcmp($row_rsProductPrefs['stockcontrol'],1))) {echo "checked=\"checked\"";} ?> name="stockcontrol" type="checkbox" id="stockcontrol" value="1" />&nbsp;&nbsp;&nbsp;<label>Send stock low email on:
                <input name="stocklowamount" type="text" id="stocklowamount" value="<?php echo $row_rsProductPrefs['stocklowamount']; ?>" size="3" maxlength="3" class="form-control">
              (0 = don't send)</label></td>
            </tr>
            <tr>
              <td align="right">Sale ends:</td>
              <td><input name="saleends" type="hidden" id="saleends" value="<?php $setvalue =  $row_rsProductPrefs['saleends']; echo $setvalue; $inputname = "saleends"; $time = true; ?>"  class='highlight-days-67 split-date format-y-m-d divider-dash'>
                <?php require('../../../core/includes/datetimeinput.inc.php'); ?></td>
            </tr>
            
          </table>
        </div>
        <div class="TabbedPanelsContent">
          <table border="0" cellpadding="2" cellspacing="0" class="form-table">
            <tr>
              <td align="right">Basket name:</td>
              <td><input name="baskettext" type="text" id="baskettext" value="<?php echo $row_rsProductPrefs['baskettext']; ?>" size="50" maxlength="20" class="form-control"/></td>
            </tr>
             <tr>
              <td align="right">Basket title:</td>
              <td><input name="text_yourorder" type="text" id="text_yourorder" value="<?php echo $row_rsProductPrefs['text_yourorder']; ?>" size="50" maxlength="50" class="form-control"/></td>
            </tr>
            <tr>
              <td align="right"><label for="minimumorder">Minimum order:</label></td>
              <td>
                <input name="minimumorder" type="text" id="minimumorder" value="<?php echo number_format($row_rsProductPrefs['minimumorder'],2,".",","); ?>" size="10" maxlength="10" class="form-control"></td>
            </tr>
            <tr>
              <td align="right">Number of items in bag text:</td>
              <td><input name="text_bagitems" type="text" id="text_bagitems" value="<?php echo $row_rsProductPrefs['text_bagitems']; ?>" size="50" maxlength="50" class="form-control"/></td>
            </tr>
            <tr>
              <td align="right">No items in bag text:</td>
              <td><input name="text_noitems" type="text" id="text_noitems" value="<?php echo $row_rsProductPrefs['text_noitems']; ?>" size="50" maxlength="50" class="form-control"/></td>
            </tr>
            <tr>
              <td align="right">Buy button text:</td>
              <td><input name="addtobasket" type="text" id="addtobasket" value="<?php echo $row_rsProductPrefs['addtobasket']; ?>" size="50" maxlength="20" class="form-control" /></td>
            </tr>
            <tr>
              <td align="right">More info text:</td>
              <td><input name="moreinfotext" type="text" id="moreinfotext" value="<?php echo $row_rsProductPrefs['moreinfotext']; ?>" size="50" maxlength="20" class="form-control"/></td>
            </tr>
            <tr>
              <td align="right">Checkout button text:</td>
              <td><input name="checkouttext" type="text" id="checkouttext" value="<?php echo $row_rsProductPrefs['checkouttext']; ?>" size="50" maxlength="20" class="form-control"/></td>
            </tr>
            <tr>
              <td align="right">Payment button text:</td>
              <td><input name="paymenttext" type="text" id="paymenttext" value="<?php echo isset($row_rsProductPrefs['paymenttext']) ? htmlentities($row_rsProductPrefs['paymenttext'], ENT_COMPAT, "UTF-8" ): "Payment"; ?>" size="50" maxlength="20" class="form-control"/></td>
            </tr>
            <tr>
              <td align="right">Back to shop button text:</td>
              <td><input name="continueshoppingtext" type="text" id="continueshoppingtext" value="<?php echo $row_rsProductPrefs['continueshoppingtext']; ?>" size="50" maxlength="20" class="form-control"/></td>
            </tr>
            <tr>
              <td align="right">Update Basket button text:</td>
              <td><input name="updatebaskettext" type="text" id="updatebaskettext" value="<?php echo $row_rsProductPrefs['updatebaskettext']; ?>" size="50" maxlength="20" class="form-control"/></td>
            </tr>
            
             <tr>
              <td align="right">Remove confirm text:</td>
              <td><input name="text_confirmremovebasket" type="text" id="text_confirmremovebasket" value="<?php echo $row_rsProductPrefs['text_confirmremovebasket']; ?>" size="50" maxlength="255"class="form-control" /></td>
            </tr>
            
            
            <tr>
              <td align="right">Promo code text:</td>
              <td class="form-inline"><input name="promocodetext" type="text" id="promocodetext" value="<?php echo $row_rsProductPrefs['promocodetext']; ?>" size="50" maxlength="100" class="form-control"/> Button:<input name="promobuttontext" type="text" id="promobuttontext" value="<?php echo $row_rsProductPrefs['promobuttontext']; ?>" size="10" maxlength="20" class="form-control"/></td>
            </tr>
            
            
              <tr>
              <td align="right">Promo entered:</td>
              <td><input name="text_usingpromo" type="text" id="text_usingpromo" value="<?php echo $row_rsProductPrefs['text_usingpromo']; ?>" size="50" maxlength="100" class="form-control"/> </td>
            </tr>
            
            
            
              <tr>
              <td align="right">Promo not valid:</td>
              <td><input name="text_promonotexists" type="text" id="text_promonotexists" value="<?php echo $row_rsProductPrefs['text_promonotexists']; ?>" size="50" maxlength="100" class="form-control"/> </td>
            </tr>
            <tr>
              <td align="right">Headers:</td>
              <td class="form-inline"><label>Quantity:<input name="quantitytext" type="text" id="quantitytext" value="<?php echo $row_rsProductPrefs['quantitytext']; ?>" size="20" maxlength="20" class="form-control"/></label>
              
              <label>Item:<input name="itemtext" type="text" id="itemtext" value="<?php echo $row_rsProductPrefs['itemtext']; ?>" size="20" maxlength="20" class="form-control" /></label>
              
              
              <label>Update:<input name="text_update" type="text" id="text_update" value="<?php echo $row_rsProductPrefs['text_update']; ?>" size="20" maxlength="20"class="form-control" /></label>
              
               <label>Remove:<input name="text_remove" type="text" id="text_remove" value="<?php echo $row_rsProductPrefs['text_remove']; ?>" size="20" maxlength="20" class="form-control"/></label>
              
              <label>Price:<input name="pricetext" type="text" id="pricetext" value="<?php echo $row_rsProductPrefs['pricetext']; ?>" size="20" maxlength="20"class="form-control" /></label>
              
              <label>VAT:<input name="taxname" type="text" id="taxname" value="<?php echo $row_rsProductPrefs['taxname']; ?>" size="20" maxlength="20" class="form-control"/></label><br>
              
              <label>Sub Total:<input name="subtotaltext" type="text" id="subtotaltext" value="<?php echo $row_rsProductPrefs['subtotaltext']; ?>" size="20" maxlength="20" class="form-control"/></label>
              
              <label>Grand Total:<input name="grandtotaltext" type="text" id="grandtotaltext" value="<?php echo $row_rsProductPrefs['grandtotaltext']; ?>" size="20" maxlength="50"class="form-control" /></label>
              
              
              </td>
            </tr>
            <tr>
              <td align="right">Buy button position:</td>
              <td><label>
                <input <?php if (!(strcmp($row_rsProductPrefs['buyposition'],"1"))) {echo "checked=\"checked\"";} ?> type="radio" name="buyposition" value="1" id="buyposition_0">
                Top</label>
                &nbsp;&nbsp;&nbsp;
                <label>
                  <input <?php if (!(strcmp($row_rsProductPrefs['buyposition'],"2"))) {echo "checked=\"checked\"";} ?> type="radio" name="buyposition" value="2" id="buyposition_1">
                  Bottom</label>
                (of product detail page)</td>
            </tr>
            <tr>
              <td align="right">Custom basket URL:</td>
              <td><input name="basketpageURL" type="text" id="basketpageURL" value="<?php echo $row_rsProductPrefs['basketpageURL']; ?>" size="50" maxlength="50" class="form-control"/></td>
            </tr>
            <tr>
              <td align="right"><label for="basketrelatedcategoryID">Related products:</label></td>
              <td><select name="basketrelatedcategoryID" id="basketrelatedcategoryID" class="form-control">
                <option value="-1" <?php if (!(strcmp(-1, $row_rsProductPrefs['basketrelatedcategoryID']))) {echo "selected=\"selected\"";} ?>>Do not show in basket</option>
                <option value="0" <?php if (!(strcmp(0, $row_rsProductPrefs['basketrelatedcategoryID']))) {echo "selected=\"selected\"";} ?>>As for previous product bought</option>
                <?php
do {  
?>
                <option value="<?php echo $row_rsCategories['ID']?>"<?php if (!(strcmp($row_rsCategories['ID'], $row_rsProductPrefs['basketrelatedcategoryID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsCategories['title']?></option>
                <?php
} while ($row_rsCategories = mysql_fetch_assoc($rsCategories));
  $rows = mysql_num_rows($rsCategories);
  if($rows > 0) {
      mysql_data_seek($rsCategories, 0);
	  $row_rsCategories = mysql_fetch_assoc($rsCategories);
  }
?>
              </select>
                (if set in General tab)</td>
            </tr>
            <tr>
              <td align="right">Basket columns:</td>
              <td><label class="text-nowrap">
                <input type="checkbox" name="basketshowitem" id="basketshowitem" disabled checked>
                Item</label>
                &nbsp;&nbsp;
                <label class="text-nowrap">
                  <input <?php if (!(strcmp($row_rsProductPrefs['basketshowpricem2'],1))) {echo "checked=\"checked\"";} ?> name="basketshowpricem2" type="checkbox" id="basketshowpricem2" value="1">
                  Price/m&sup2;</label>
                &nbsp;&nbsp;
                <label class="text-nowrap">
                  <input type="checkbox" name="basketshowprice" id="basketshowprice" disabled checked>
                  Price</label>
                &nbsp;&nbsp;
                <label class="text-nowrap">
                  <input <?php if (!(strcmp($row_rsProductPrefs['basketshowupdatequantity'],1))) {echo "checked=\"checked\"";} ?> name="basketshowupdatequantity" type="checkbox" id="basketshowupdatequantity" value="1">
                  Updateable Quantity</label>
                &nbsp;&nbsp;
                <label class="text-nowrap">
                  <input <?php if (!(strcmp($row_rsProductPrefs['basketshowadjustablequantity'],1))) {echo "checked=\"checked\"";} ?> name="basketshowadjustablequantity" type="checkbox" id="basketshowadjustablequantity" value="1">
                  Adjustable Quantity</label>
                &nbsp;&nbsp;
                <label class="text-nowrap">
                  <input <?php if (!(strcmp($row_rsProductPrefs['basketshowremove'],1))) {echo "checked=\"checked\"";} ?> name="basketshowremove" type="checkbox" id="basketshowremove" value="1">
                  Item remove</label>
                &nbsp;&nbsp;
                <label class="text-nowrap">
                  <input <?php if (!(strcmp($row_rsProductPrefs['basketshowweight'],1))) {echo "checked=\"checked\"";} ?> name="basketshowweight" type="checkbox" id="basketshowweight" value="1">
                  Weight</label>
                &nbsp;&nbsp;</td>
            </tr>
          </table>
        </div>
        <div class="TabbedPanelsContent">
        <p>
            <input <?php if (!(strcmp($row_rsProductPrefs['askbillingdetails'],1))) {echo "checked=\"checked\"";} ?> name="askbillingdetails" type="checkbox" id="askbillingdetails" value="1">
            <label for="askbillingdetails">Ask billing details</label>
          </p>
          
           <p>
            <input <?php if (!(strcmp($row_rsProductPrefs['askcompany'],1))) {echo "checked=\"checked\"";} ?> name="askcompany" type="checkbox" id="askcompany" value="1">
            <label for="askcompany">Ask for company name</label>
          </p>


          <h3>Mandatory Fields</h3>
          <p>
            <input <?php if (!(strcmp($row_rsProductPrefs['checkoutmandatorytelephone'],1))) {echo "checked=\"checked\"";} ?> name="checkoutmandatorytelephone" type="checkbox" id="checkoutmandatorytelephone" value="1">
            <label for="checkoutmandatorytelephone">Contact telephone number</label>
          </p>
          
          <p>
            <input <?php if (!(strcmp($row_rsProductPrefs['checkouttermsagree'],1))) {echo "checked=\"checked\"";} ?> name="checkouttermsagree" type="checkbox" id="checkouttermsagree" value="1">
            <label for="checkouttermsagree">Must agree to <a href="../../../members/admin/options/legal/index.php">terms and conditions</a></label>
          </p>
          
          
          <h3>Extra Fields</h3>
          <p>Extra Question: 
            <input name="checkoutquestion1" type="text" id="checkoutquestion1" value="<?php echo $row_rsProductPrefs['checkoutquestion1']; ?>" size="150" maxlength="255" class="form-control">
          </p>
          <h3>Text fields</h3>
          <table border="0" cellpadding="0" cellspacing="0" class="form-table">
            <tr>
              <th scope="row"><label for="text_yourdetails">Your Details:</label></th>
              <td><input name="text_yourdetails" type="text" id="text_yourdetails" value="<?php echo htmlentities($row_rsProductPrefs['text_yourdetails'], ENT_COMPAT, "UTF-8"); ?>" size="50" maxlength="20" class="form-control"></td>
            </tr>
            
              <tr>
              <th scope="row"><label for="text_firstname">First name(s):</label></th>
              <td><input name="text_firstname" type="text" id="text_firstname" value="<?php echo htmlentities($row_rsProductPrefs['text_firstname'], ENT_COMPAT, "UTF-8"); ?>" size="50" maxlength="20" class="form-control"></td>
            </tr>
            <tr>
              <th scope="row"><label for="text_surname">Surname:</label></th>
              <td><input name="text_surname" type="text" id="text_surname" value="<?php echo htmlentities($row_rsProductPrefs['text_surname'], ENT_COMPAT, "UTF-8"); ?>" size="50" maxlength="20" class="form-control"></td>
            </tr><tr>
              <th scope="row"><label for="text_email">Email:</label></th>
              <td><input name="text_email" type="text" id="text_email" value="<?php echo htmlentities($row_rsProductPrefs['text_email'], ENT_COMPAT, "UTF-8"); ?>" size="50" maxlength="20" class="form-control"></td>
            </tr><tr>
              <th scope="row"><label for="text_emailinfo">Email info:</label></th>
              <td><input name="text_emailinfo" type="text" id="text_emailinfo" value="<?php echo htmlentities($row_rsProductPrefs['text_emailinfo'], ENT_COMPAT, "UTF-8"); ?>" size="50" maxlength="50" class="form-control"></td>
            </tr><tr>
              <th scope="row"><label for="text_address">Address:</label></th>
              <td><input name="text_address" type="text" id="text_address" value="<?php echo htmlentities($row_rsProductPrefs['text_address'], ENT_COMPAT, "UTF-8"); ?>" size="50" maxlength="20" class="form-control"></td>
            </tr><tr>
              <th scope="row"><label for="text_city">City:</label></th>
              <td><input name="text_city" type="text" id="text_city" value="<?php echo htmlentities($row_rsProductPrefs['text_city'], ENT_COMPAT, "UTF-8"); ?>" size="50" maxlength="20" class="form-control"></td>
            </tr><tr>
              <th scope="row"><label for="text_postcode">Postcode:</label></th>
              <td class="form-inline"><input name="text_postcode" type="text" id="text_postcode" value="<?php echo htmlentities($row_rsProductPrefs['text_postcode'], ENT_COMPAT, "UTF-8"); ?>" size="50" maxlength="20" class="form-control"> <label><input <?php if (!(strcmp($row_rsProductPrefs['askpostcode'],1))) {echo "checked=\"checked\"";} ?> name="askpostcode" type="checkbox" value="1"> Show</label></td>
            </tr><tr>
              <th scope="row"><label for="text_country">Country:</label></th>
              <td><input name="text_country" type="text" id="text_country" value="<?php echo htmlentities($row_rsProductPrefs['text_country'], ENT_COMPAT, "UTF-8"); ?>" size="50" maxlength="20" class="form-control"></td>
            </tr><tr>
              <th scope="row"><label for="text_telephone">Telephone:</label></th>
              <td><input name="text_telephone" type="text" id="text_telephone" value="<?php echo htmlentities($row_rsProductPrefs['text_telephone'], ENT_COMPAT, "UTF-8"); ?>" size="50" maxlength="20" class="form-control"></td>
            </tr><tr>
              <th scope="row"><label for="text_mobile">Mobile:</label></th>
              <td><input name="text_mobile" type="text" id="text_mobile" value="<?php echo htmlentities($row_rsProductPrefs['text_mobile'], ENT_COMPAT, "UTF-8"); ?>" size="50" maxlength="20" class="form-control"></td>
            </tr><tr>
              <th scope="row"><label for="text_mobileinfo">Mobile info:</label></th>
              <td><input name="text_mobileinfo" type="text" id="text_mobileinfo" value="<?php echo htmlentities($row_rsProductPrefs['text_mobileinfo'], ENT_COMPAT, "UTF-8"); ?>" size="50" maxlength="50"class="form-control"></td>
            </tr><tr>
              <th scope="row"><label for="text_howfound">How discovered:</label></th>
              <td><input name="text_howfound" type="text" id="text_howfound" value="<?php echo htmlentities($row_rsProductPrefs['text_howfound'], ENT_COMPAT, "UTF-8"); ?>" size="50" maxlength="50" class="form-control"></td>
            </tr><tr>
              <th scope="row"><label for="text_billingdetails">Billing Details:</label></th>
              <td><input name="text_billingdetails" type="text" id="text_billingdetails" value="<?php echo htmlentities($row_rsProductPrefs['text_billingdetails'], ENT_COMPAT, "UTF-8"); ?>" size="50" maxlength="50" class="form-control"></td>
            </tr><tr>
              <th scope="row"><label for="text_choosesaved">Choose address:</label></th>
              <td><input name="text_choosesaved" type="text" id="text_choosesaved" value="<?php echo htmlentities($row_rsProductPrefs['text_choosesaved'], ENT_COMPAT, "UTF-8"); ?>" size="50" maxlength="50" class="form-control"></td>
            </tr><tr>
              <th scope="row"><label for="text_deliverydetails">Delivery Details:</label></th>
              <td><input name="text_deliverydetails" type="text" id="text_deliverydetails" value="<?php echo htmlentities($row_rsProductPrefs['text_deliverydetails'], ENT_COMPAT, "UTF-8"); ?>" size="50" maxlength="255" class="form-control"></td>
            </tr><tr>
              <th scope="row"><label for="text_deliveryaddress">Delivery Address:</label></th>
              <td><input name="text_deliveryaddress" type="text" id="text_deliveryaddress" value="<?php echo htmlentities($row_rsProductPrefs['text_deliveryaddress'], ENT_COMPAT, "UTF-8"); ?>" size="50" maxlength="50" class="form-control"></td>
            </tr><tr>
              <th scope="row"><label for="text_sameasbilling">Same as billing:</label></th>
              <td><input name="text_sameasbilling" type="text" id="text_sameasbilling" value="<?php echo htmlentities($row_rsProductPrefs['text_sameasbilling'], ENT_COMPAT, "UTF-8"); ?>" size="50" maxlength="50" class="form-control"></td>
            </tr><tr>
              <th scope="row"><label for="text_differentaddress">Different Address:</label></th>
              <td><input name="text_differentaddress" type="text" id="text_differentaddress" value="<?php echo htmlentities($row_rsProductPrefs['text_differentaddress'], ENT_COMPAT, "UTF-8"); ?>" size="50" maxlength="50" class="form-control"></td>
            </tr><tr>
              <th scope="row"><label for="text_willcollectfrom">Will collect from:</label></th>
              <td><input name="text_willcollectfrom" type="text" id="text_willcollectfrom" value="<?php echo htmlentities($row_rsProductPrefs['text_willcollectfrom'], ENT_COMPAT, "UTF-8"); ?>" size="50" maxlength="50" class="form-control"></td>
            </tr><tr>
              <th scope="row"><label for="text_addspecialinstructions">Add special instructions:</label></th>
              <td><input name="text_addspecialinstructions" type="text" id="text_addspecialinstructions" value="<?php echo htmlentities($row_rsProductPrefs['text_addspecialinstructions'], ENT_COMPAT, "UTF-8"); ?>" size="50" maxlength="50" class="form-control"></td>
            </tr><tr>
              <th scope="row"><label for="text_deliveryinstructions">Delivery Instructions:</label></th>
              <td><input name="text_deliveryinstructions" type="text" id="text_deliveryinstructions" value="<?php echo htmlentities($row_rsProductPrefs['text_deliveryinstructions'], ENT_COMPAT, "UTF-8"); ?>" size="50" maxlength="50" class="form-control"></td>
            </tr>
            
            
            <tr>
              <th scope="row"><label for="text_calcshipping">Calculate Shipping:</label></th>
              <td><input name="text_calcshipping" type="text" id="text_calcshipping" value="<?php echo htmlentities($row_rsProductPrefs['text_calcshipping'], ENT_COMPAT, "UTF-8"); ?>" size="50" maxlength="50" class="form-control"></td>
            </tr>
            
            
            
            
            <tr>
              <th scope="row"><label for="text_payby">Pay by:</label></th>
              <td><input name="text_payby" type="text" id="text_payby" value="<?php echo htmlentities($row_rsProductPrefs['text_payby'], ENT_COMPAT, "UTF-8"); ?>" size="50" maxlength="20" class="form-control"></td>
            </tr><tr>
              <th scope="row"><label for="text_creditcard">Credit card:</label></th>
              <td><input name="text_creditcard" type="text" id="text_creditcard" value="<?php echo htmlentities($row_rsProductPrefs['text_creditcard'], ENT_COMPAT, "UTF-8"); ?>" size="50" maxlength="20"class="form-control"></td>
            </tr>
            <tr>
              <th scope="row"><label for="text_myaccount">My Account:</label></th>
              <td><input name="text_myaccount" type="text" id="text_myaccount" value="<?php echo htmlentities($row_rsProductPrefs['text_myaccount'], ENT_COMPAT, "UTF-8"); ?>" size="50" maxlength="20" class="form-control"></td>
            </tr>
            <tr>
              <th scope="row"><label for="text_ordersummary">Order Summary:</label></th>
              <td><input name="text_ordersummary" type="text" id="text_ordersummary" value="<?php echo htmlentities($row_rsProductPrefs['text_ordersummary'], ENT_COMPAT, "UTF-8"); ?>" size="50" maxlength="20" class="form-control"></td>
            </tr>
            
            
             <tr>
          <th scope="row" ><label for="text_addresserror">Address error:</label></th>
          <td>
            <input name="text_addresserror" type="text" id="text_addresserror" value="<?php echo htmlentities($row_rsProductPrefs['text_addresserror'], ENT_COMPAT, "UTF-8"); ?>" size="50" maxlength="150" class="form-control"></td>
        </tr>
        
        <tr> 
        <th scope="row" ><label for="text_returningmember">Returning member?:</label></th>
          <td>
            <input name="text_returningmember" type="text" id="text_returningmember" value="<?php echo htmlentities($row_rsProductPrefs['text_returningmember'], ENT_COMPAT, "UTF-8"); ?>" size="50" maxlength="50" class="form-control"></td>
        </tr>
        
        <tr> 
        <th scope="row" ><label for="text_returningmemberinfo">Returning member text:</label></th>
          <td>
            <input name="text_returningmemberinfo" type="text" id="text_returningmemberinfo" value="<?php echo htmlentities($row_rsProductPrefs['text_returningmemberinfo'], ENT_COMPAT, "UTF-8"); ?>" size="50" maxlength="255" class="form-control"></td>
        </tr>
        
        
        <tr> 
        <th scope="row" ><label for="text_viewinvoice">View Invoice:</label></th>
          <td>
            <input name="text_viewinvoice" type="text" id="text_viewinvoice" value="<?php echo htmlentities($row_rsProductPrefs['text_viewinvoice'], ENT_COMPAT, "UTF-8"); ?>" size="50" maxlength="255" class="form-control"></td>
        </tr>
        
        
         <tr> 
        <th scope="row" ><label for="text_welcomeback">Welcome back:</label></th>
          <td>
            <input name="text_welcomeback" type="text" id="text_welcomeback" value="<?php echo htmlentities($row_rsProductPrefs['text_welcomeback'], ENT_COMPAT, "UTF-8"); ?>" size="50" maxlength="255" class="form-control"></td>
        </tr>
        
        
         <tr> 
        <th scope="row" ><label for="text_notyou">Not You?:</label></th>
          <td>
            <input name="text_notyou" type="text" id="text_notyou" value="<?php echo htmlentities($row_rsProductPrefs['text_notyou'], ENT_COMPAT, "UTF-8"); ?>" size="50" maxlength="255" class="form-control"></td>
        </tr>
        
        
         <tr> 
        <th scope="row" ><label for="text_mypurchases">My Purchases:</label></th>
          <td>
            <input name="text_mypurchases" type="text" id="text_mypurchases" value="<?php echo htmlentities($row_rsProductPrefs['text_mypurchases'], ENT_COMPAT, "UTF-8"); ?>" size="50" maxlength="255" class="form-control"></td>
        </tr>
        
        
           
          </table>
          <p>&nbsp;</p>
          
          <h3>Checkout confirmation footer</h3>
          <p>
            <textarea name="checkoutconfirmfooter" id="checkoutconfirmfooter" class="form-control"><?php echo $row_rsProductPrefs['checkoutconfirmfooter']; ?></textarea>
          </p>
          
          
        </div>
        <div class="TabbedPanelsContent">
          <table  class="form-table">
            <tr>
              <td align="right">Main payment provider:</td>
              <td><select name="paymentproviderID" id="paymentproviderID" class="form-control">
                <option value="0" <?php if (!(strcmp(0, $row_rsProductPrefs['paymentproviderID']))) {echo "selected=\"selected\"";} ?>>None</option>
                
                
               
                
                
                 <option value="10" <?php if (!(strcmp(10, $row_rsProductPrefs['paymentproviderID']))) {echo "selected=\"selected\"";} ?>>Barclays</option>
                 
                 <option value="12" <?php if (!(strcmp(12, $row_rsProductPrefs['paymentproviderID']))) {echo "selected=\"selected\"";} ?>>Elavon</option>
                 
                 <option value="4" <?php if (!(strcmp(4, $row_rsProductPrefs['paymentproviderID']))) {echo "selected=\"selected\"";} ?>>Google Checkout</option>
                 
                 <option value="9" <?php if (!(strcmp(9, $row_rsProductPrefs['paymentproviderID']))) {echo "selected=\"selected\"";} ?>>Ingenico</option>
                 
                 <option value="7" <?php if (!(strcmp(7, $row_rsProductPrefs['paymentproviderID']))) {echo "selected=\"selected\"";} ?>>Nochex</option>
                 
                 <option value="1" <?php if (!(strcmp(1, $row_rsProductPrefs['paymentproviderID']))) {echo "selected=\"selected\"";} ?>>PayPal Standard</option>
                <option value="6" <?php if (!(strcmp(6, $row_rsProductPrefs['paymentproviderID']))) {echo "selected=\"selected\"";} ?>>PayPal Pro Hosted</option>               
                
                <option value="8" <?php if (!(strcmp(8, $row_rsProductPrefs['paymentproviderID']))) {echo "selected=\"selected\"";} ?>>PayTrail</option>
                
                <option value="2" <?php if (!(strcmp(2, $row_rsProductPrefs['paymentproviderID']))) {echo "selected=\"selected\"";} ?>>SagePay Server</option>
                <option value="11" <?php if (!(strcmp(11, $row_rsProductPrefs['paymentproviderID']))) {echo "selected=\"selected\"";} ?>>SagePay Form v3</option>
                 
                  <option value="5" <?php if (!(strcmp(5, $row_rsProductPrefs['paymentproviderID']))) {echo "selected=\"selected\"";} ?>>Secure Trading</option>
                  
                  <option value="3" <?php if (!(strcmp(3, $row_rsProductPrefs['paymentproviderID']))) {echo "selected=\"selected\"";} ?>>WorldPay</option>
                 
              </select></td>
            </tr>
            <tr>
              <td align="right">Payment client ID:</td>
              <td><input name="paymentclientID" type="text"  id="paymentclientID" value="<?php echo $row_rsProductPrefs['paymentclientID']; ?>" size="50" maxlength="255" autocomplete='off' readonly onfocus="this.removeAttribute('readonly');" class="form-control" /></td>
            </tr>
            
            
            <tr>
              <td align="right"><label for="paymentclientcode">Merchant code:</label></td>
              <td>
                <input name="paymentclientcode" type="text" id="paymentclientcode" value="<?php echo $row_rsProductPrefs['paymentclientcode']; ?>" size="50" maxlength="255" placeholder="(optional - if required)" class="form-control">
              </td>
            </tr>
            
            
            <tr>
              <td align="right"><label for="paymentclientpassword">Payment client password:</label></td>
              <td>
                <input name="paymentclientpassword" type="text" id="paymentclientpassword" value="<?php echo $row_rsProductPrefs['paymentclientpassword']; ?>" size="50" maxlength="255" placeholder="(optional - if required)" class="form-control">
              </td>
            </tr>
            <tr>
              <td align="right"><p>PayPal as alternative  ID:</p></td>
              <td>
                <input name="paypalID" type="text"  id="paypalID" value="<?php echo $row_rsProductPrefs['paypalID']; ?>" size="50" maxlength="255" placeholder="(optional - if offering PayPal as an additional method)" class="form-control"/>
              </td>
            </tr>
            <tr>
              <td align="right">Alternative payment methods: </td>
              <td><label>
                <input <?php if (!(strcmp($row_rsProductPrefs['cheque'],1))) {echo "checked=\"checked\"";} ?> name="cheque" type="checkbox" id="cheque" value="1" />
                Cheque/Postal Order </label>
                &nbsp;&nbsp;&nbsp;
                <label>
                  <input <?php if (!(strcmp($row_rsProductPrefs['cashdelivery'],1))) {echo "checked=\"checked\"";} ?> name="cashdelivery" type="checkbox" id="cashdelivery" value="1" />
                  Cash on delivery </label>
                &nbsp;&nbsp;&nbsp;
                <label>
                  <input <?php if (!(strcmp($row_rsProductPrefs['cashcollection'],1))) {echo "checked=\"checked\"";} ?> name="cashcollection" type="checkbox" id="cashcollection" value="1" />
                  Cash on collection </label>
                &nbsp;&nbsp;&nbsp;
                <label>
                  <input <?php if (!(strcmp($row_rsProductPrefs['invoice'],1))) {echo "checked=\"checked\"";} ?> name="invoice" type="checkbox" id="invoice" value="1" />
                  Invoice </label></td>
            </tr>
            <tr>
              <td align="right"><label for="askhowdiscovered">Ask how discovered:</label></td>
              <td><input <?php if (!(strcmp($row_rsProductPrefs['askhowdiscovered'],1))) {echo "checked=\"checked\"";} ?> name="askhowdiscovered" type="checkbox" id="askhowdiscovered" value="1" >
                <a href="../../../members/admin/options/discovered/index.php" class="btn btn-default btn-secondary">Manage</a></td>
            </tr>
            <tr>
              <td align="right">Payment success URL:</td>
              <td><input name="successURL" type="text"  id="successURL" value="<?php $protocol = getProtocol()."://"; echo (isset($row_rsProductPrefs['successURL']) && strlen($row_rsProductPrefs['successURL'])>0 && substr($row_rsProductPrefs['successURL'],0,4)!="http") ? $protocol.$_SERVER['HTTP_HOST'].$row_rsProductPrefs['successURL'] : $row_rsProductPrefs['successURL']; ?>" size="50" maxlength="255"class="form-control" /></td>
            </tr>
            <tr>
              <td align="right">Payment fail URL:</td>
              <td><input name="failURL" type="text"  id="failURL" value="<?php echo $row_rsProductPrefs['failURL']; ?>" size="50" maxlength="255" class="form-control"/></td>
            </tr>
            <tr>
              <td align="right">Custom Invoice URL:</td>
              <td><input name="invoiceURL" type="text"  id="invoiceURL" value="<?php echo $row_rsProductPrefs['invoiceURL']; ?>" size="50" maxlength="255" placeholder="Optional"class="form-control" /></td>
            </tr>
            
             <tr>
              <td align="right">Invoice Name:</td>
              <td><input name="text_invoice" type="text"  id="text_invoice" value="<?php echo $row_rsProductPrefs['text_invoice']; ?>" size="50" maxlength="50" class="form-control" /></td>
            </tr>
            
            
            <tr>
              <td align="right" valign="top"><label for="bacsdetails">BACS Details:</label></td>
              <td><textarea name="bacsdetails" id="bacsdetails" cols="45" rows="5" class="form-control"><?php echo $row_rsProductPrefs['bacsdetails']; ?></textarea></td>
            </tr>
          </table>
      </div>
<div class="TabbedPanelsContent">
<p><label><input <?php if (!(strcmp($row_rsProductPrefs['salesnotifications'],1))) {echo "checked=\"checked\"";} ?> name="salesnotifications" type="checkbox" value="1"> Add sales to user notifications</label></p>

<h3>Transaction Success Page Content:</h3>
  <textarea name="successpage" cols="50" rows="5" class="tinymce"><?php echo htmlentities($row_rsProductPrefs['successpage'], ENT_COMPAT, 'UTF-8'); ?></textarea>
  
</div>

</div>
    </div>
    
      <div><input type="hidden" name="MM_update" value="form1" />
    <button type="submit" class="btn btn-primary" >Save changes</button>
      <input name="ID" type="hidden" id="ID" value="<?php echo $regionID; ?>" />
      <input type="hidden" name="returnURL" id="returnURL" /></div>
    </form>
<script>
<!--
var TabbedPanels1 = new Spry.Widget.TabbedPanels("TabbedPanels1");

//-->
</script>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsProductPrefs);

mysql_free_result($rsCategories);
?>
