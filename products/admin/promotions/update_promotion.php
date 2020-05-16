<?php require_once('../../../Connections/aquiescedb.php'); ?><?php require_once('../../../core/includes/adminAccess.inc.php'); ?>
<?php require_once('../../../core/includes/framework.inc.php'); ?>
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

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if(isset($_GET['cancel'])) {
	$update = "UPDATE productpromocode SET statusID = 0 WHERE ID = ".intval($_GET['promocodeID']);	
	mysql_select_db($database_aquiescedb, $aquiescedb);
	mysql_query($update, $aquiescedb) or die(mysql_error());
} else if(isset($_GET['reset'])) {
	$update = "UPDATE productpromocode SET statusID = 1 WHERE ID = ".intval($_GET['promocodeID']);	
	mysql_select_db($database_aquiescedb, $aquiescedb);
	mysql_query($update, $aquiescedb) or die(mysql_error());
}

require_once('../../../core/includes/upload.inc.php');
$uploaded = getUploads();

if(!empty($_FILES) && is_array($uploaded)) { //print_r($uploaded); die();
	if(isset($uploaded['codefile'][0]['newname'])) {
		$uploadID = "".$uploaded['codefile'][0]['uploadID']; // no idea why but quotes mean value can be assigned
		require_once('../../../documents/admin/import/includes/importCSV.inc.php'); 
		$messages = insertCSVtoTable(UPLOAD_ROOT.$uploaded['codefile'][0]['newname'], "productpromocode", array("promocode"),array("promocode"),$_POST['modifiedbyID'],$uploadID);
		$error = implode("\n",$messages['error']);	
	}
	if(isset($uploaded['filename'][0]['newname'])) {
		$_POST['imageURL'] = $uploaded['filename'][0]['newname'];
	}
	
	
}

if(isset($error) && strlen($error)>1) {
	unset($_POST["MM_update"]); 
	$delete = "DELETE FROM productpromocode WHERE uploadID = ".intval($uploadID);
	mysql_query($delete, $aquiescedb) or die(mysql_error().$update);
}

$_POST['promocode'] = isset($_POST['promocode']) ? str_replace(" ", "", strtoupper($_REQUEST['promocode'])) : "";

$_POST['progressivediscountgroup'] = isset($_POST['progressivediscountgroup'])  && intval($_POST['progressivediscountgroup'])> 0 ?  intval($_POST['progressivediscountgroup']) : 0;


if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE productpromo SET promotitle=%s, promocodetype=%s, promocode=%s, imageURL=%s, linkURL=%s, startdatetime=%s, enddatetime=%s, actiontypeID=%s, actionproductID=%s, actionamount=%s, resulttypeID=%s, resultproduct=%s, resultamount=%s, regionID=%s, modifieddatetime=%s, modifiedbyID=%s, statusID=%s, standalone=%s, usergroupID=%s, resultcategoryID=%s, promodetails=%s, actioncategoryID=%s, display=%s, addbasket=%s, actionmanufacturerID=%s, progressivediscountgroup=%s WHERE ID=%s",
                       GetSQLValueString($_POST['promotitle'], "text"),
                       GetSQLValueString($_POST['promocodetype'], "int"),
                       GetSQLValueString($_POST['promocode'], "text"),
                       GetSQLValueString($_POST['imageURL'], "text"),
                       GetSQLValueString($_POST['linkURL'], "text"),
                       GetSQLValueString($_POST['startdatetime'], "date"),
                       GetSQLValueString($_POST['enddatetime'], "date"),
                       GetSQLValueString($_POST['actiontypeID'], "int"),
                       GetSQLValueString($_POST['actionproductID'], "int"),
                       GetSQLValueString($_POST['actionamount'], "double"),
                       GetSQLValueString($_POST['resulttypeID'], "int"),
                       GetSQLValueString($_POST['resultproduct'], "int"),
                       GetSQLValueString($_POST['resultamount'], "double"),
                       GetSQLValueString($_POST['regionID'], "int"),
                       GetSQLValueString($_POST['modifieddatetime'], "date"),
                       GetSQLValueString($_POST['modifiedbyID'], "int"),
                       GetSQLValueString(isset($_POST['statusID']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['standalone']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['usergroupID'], "int"),
                       GetSQLValueString($_POST['categoryID'], "int"),
                       GetSQLValueString($_POST['promodetails'], "text"),
                       GetSQLValueString($_POST['actioncategoryID'], "int"),
                       GetSQLValueString(isset($_POST['display']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['addbasket']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['actionmanufacturerID'], "int"),
                       GetSQLValueString($_POST['progressivediscountgroup'], "int"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) { 
	if(isset($uploadID) && $uploadID >0) { // csv uploaded
	$update = "UPDATE productpromocode SET promoID = ".GetSQLValueString($_POST['ID'], "int").", 
	validfrom = ".GetSQLValueString($_POST['startdatetime'], "date").", 
	validuntil = ".GetSQLValueString($_POST['enddatetime'], "date")." WHERE uploadID = ".$uploadID;
	mysql_query($update, $aquiescedb) or die(mysql_error().$update);
	} 
	// update all promoID in case dates changed etc. statusID can only go from 1 to 0
	$update = "UPDATE productpromocode SET 
	statusID = ".GetSQLValueString(isset($_POST['statusID']) ? "true" : "", "defined","1","0").", 
	validfrom = ".GetSQLValueString($_POST['startdatetime'], "date").", 
	validuntil = ".GetSQLValueString($_POST['enddatetime'], "date")."
	WHERE statusID = 1 AND promoID = ".GetSQLValueString($_POST['ID'], "int");
	mysql_query($update, $aquiescedb) or die(mysql_error().$update);
	
	
  $updateGoTo = "index.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo)); exit;
}

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsRegions = "SELECT ID, title FROM region ORDER BY title ASC";
$rsRegions = mysql_query($query_rsRegions, $aquiescedb) or die(mysql_error());
$row_rsRegions = mysql_fetch_assoc($rsRegions);
$totalRows_rsRegions = mysql_num_rows($rsRegions);

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID FROM users WHERE username = '%s'", $colname_rsLoggedIn);
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

$colname_rsPromotion = "-1";
if (isset($_GET['promoID'])) {
  $colname_rsPromotion = $_GET['promoID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPromotion = sprintf("SELECT * FROM productpromo WHERE ID = %s", GetSQLValueString($colname_rsPromotion, "int"));
$rsPromotion = mysql_query($query_rsPromotion, $aquiescedb) or die(mysql_error());
$row_rsPromotion = mysql_fetch_assoc($rsPromotion);
$totalRows_rsPromotion = mysql_num_rows($rsPromotion);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsUserGroups = "SELECT ID, groupname FROM usergroup WHERE statusID = 1 ORDER BY groupname ASC";
$rsUserGroups = mysql_query($query_rsUserGroups, $aquiescedb) or die(mysql_error());
$row_rsUserGroups = mysql_fetch_assoc($rsUserGroups);
$totalRows_rsUserGroups = mysql_num_rows($rsUserGroups);

$varRegionID_rsCategories = "1";
if (isset($regionID)) {
  $varRegionID_rsCategories = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsCategories = sprintf("SELECT ID, title FROM productcategory WHERE statusID = 1 AND productcategory.regionID = %s ORDER BY title ASC", GetSQLValueString($varRegionID_rsCategories, "int"));
$rsCategories = mysql_query($query_rsCategories, $aquiescedb) or die(mysql_error());
$row_rsCategories = mysql_fetch_assoc($rsCategories);
$totalRows_rsCategories = mysql_num_rows($rsCategories);



$varRegionID_rsManufacturers = "1";
if (isset($regionID)) {
  $varRegionID_rsManufacturers = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsManufacturers = sprintf("SELECT productmanufacturer.ID, productmanufacturer.manufacturername, parent.manufacturername AS parentname FROM productmanufacturer LEFT JOIN productmanufacturer AS parent ON (productmanufacturer.subsidiaryofID = parent.ID) WHERE (productmanufacturer.regionID = 0 OR productmanufacturer.regionID = %s) AND productmanufacturer.statusID = 1 ORDER BY productmanufacturer.manufacturername", GetSQLValueString($varRegionID_rsManufacturers, "int"));
$rsManufacturers = mysql_query($query_rsManufacturers, $aquiescedb) or die(mysql_error());
$row_rsManufacturers = mysql_fetch_assoc($rsManufacturers);
$totalRows_rsManufacturers = mysql_num_rows($rsManufacturers);

$varRegionID_rsProducts = "1";
if (isset($regionID)) {
  $varRegionID_rsProducts = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsProducts = sprintf("SELECT product.ID, title FROM product LEFT JOIN productinregion ON (product.ID = productinregion.productID) WHERE ((productinregion.regionID IS NULL AND %s = 1) OR productinregion.regionID  = %s) AND statusID = 1 GROUP BY product.ID ORDER BY title ASC", GetSQLValueString($varRegionID_rsProducts, "int"),GetSQLValueString($varRegionID_rsProducts, "int"));
$rsProducts = mysql_query($query_rsProducts, $aquiescedb) or die(mysql_error());
$row_rsProducts = mysql_fetch_assoc($rsProducts);
$totalRows_rsProducts = mysql_num_rows($rsProducts);
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Update Promotion"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<style><!--
<?php echo "#universalcode, #individualcodes, #usergroup { display:none; }"; ?>
--></style>
<script src="/core/scripts/date-picker/js/datepicker.js"></script>
<script src="../../../SpryAssets/SpryValidationTextField.js"></script>
<script src="../../../SpryAssets/SpryValidationSelect.js"></script>
<script src="../../../SpryAssets/SpryTabbedPanels.js"></script>
<link href="../../../SpryAssets/SpryValidationTextField.css" rel="stylesheet"  />
<link href="../../../SpryAssets/SpryValidationSelect.css" rel="stylesheet"  />
<link href="../../../SpryAssets/SpryTabbedPanels.css" rel="stylesheet"  />
<script src="/core/scripts/formUpload.js"></script>
<script src="scripts/promotions.js"></script>
<script>
$(document).ready(function(e) {
    displayUniqueCodes(<?php echo $_GET['promoID']; ?>);
});

function displayUniqueCodes(promoID) {
    $.get("ajax/promocodes.ajax.php?promoID="+promoID, function(data, status){
        $("#uniquecodes").html(data);
    });
}

function addPromoCode(promoID) {
	$.get("ajax/addpromocode.ajax.php?promoID="+promoID+"&promocode="+escape(document.getElementById('uniquecode').value)+"&validfrom="+escape(document.getElementById('validfrom').value)+"&validuntil="+escape(document.getElementById('validuntil').value)+"&quantity="+escape(document.getElementById('quantity').value), function(data, status){
		
        displayUniqueCodes(promoID);
    });
}

function toggleGenerate(theValue) {
	if(theValue==1) {
		$(".manual").hide();
		$(".auto").show();
		$("input[name=uniquecode]").val("");
	} else {
		$(".manual").show();
		$(".auto").hide();
		$("input[name=quantity]").val(1);
	}
}
</script>
<style><!--
.manual {
	display:none;
}
--></style>
<link href="../../css/defaultProducts.css" rel="stylesheet"  />
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
    <!-- InstanceBeginEditable name="Body" --><form action="<?php echo $editFormAction; ?>" method="post" enctype="multipart/form-data" name="form1" id="form1">
    <h1><i class="glyphicon glyphicon-shopping-cart"></i> Update Promotion</h1>
   <?php require_once('../../../core/includes/alert.inc.php'); ?>
    <div id="TabbedPanels1" class="TabbedPanels">
      <ul class="TabbedPanelsTabGroup">
        <li class="TabbedPanelsTab" tabindex="0">Promotion</li>
        <li class="TabbedPanelsTab" tabindex="0">Details, image and link</li>
        <li class="TabbedPanelsTab" tabindex="0">Unique Codes</li>
      </ul>
      <div class="TabbedPanelsContentGroup">
        <div class="TabbedPanelsContent"> <table class="form-table"> <tr>
          <td class="text-nowrap text-right">Promotion:</td>
          <td><span id="sprytextfield1">
            <input name="promotitle" type="text"  id="promotitle" value="<?php echo $row_rsPromotion['promotitle']; ?>" size="50" maxlength="255" class="form-control" />
            <span class="textfieldRequiredMsg">A title is required.</span></span></td>
        </tr> <tr>
          <td class="text-nowrap text-right">Validation:</td>
          <td class="form-inline"><label>
            <input <?php if (!(strcmp($row_rsPromotion['promocodetype'],"0"))) {echo "checked=\"checked\"";} ?> name="promocodetype" type="radio" id="promocodetype_0" value="0" onclick="togglePromoCode();" />
            None required</label>
            &nbsp;&nbsp;&nbsp;
            <label>
              <input <?php if (!(strcmp($row_rsPromotion['promocodetype'],"1"))) {echo "checked=\"checked\"";} ?> type="radio" name="promocodetype" value="1" id="promocodetype_1" onclick="togglePromoCode();" />
              Universal code</label>
            &nbsp;&nbsp;&nbsp;
            <label>
              <input <?php if (!(strcmp($row_rsPromotion['promocodetype'],"2"))) {echo "checked=\"checked\"";} ?> type="radio" name="promocodetype" value="2" id="promocodetype_2" onclick="togglePromoCode();" />
              Individual codes</label> 
            &nbsp;&nbsp;&nbsp;
            <label>
              <input <?php if (!(strcmp($row_rsPromotion['promocodetype'],"3"))) {echo "checked=\"checked\"";} ?>  type="radio" name="promocodetype" value="3" id="promocodetype_3" onclick="togglePromoCode();" />
              By user group</label>
            <div id="universalcode">
             
                <input name="promocode" type="text"  id="promocode" value="<?php echo $row_rsPromotion['promocode']; ?>" size="20" maxlength="20" class="form-control" />
             
            </div>
            <div id="individualcodes"> Select CSV file:
              <input type="file" name="codefile" id="codefile" />
            </div><div id="usergroup"><select name="usergroupID" class="form-control">
              <option value="0" <?php if (!(strcmp(0, $row_rsPromotion['usergroupID']))) {echo "selected=\"selected\"";} ?>><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
              <?php if($totalRows_rsUserGroups>0) {
do {  
?>
              <option value="<?php echo $row_rsUserGroups['ID']?>"<?php if (!(strcmp($row_rsUserGroups['ID'], $row_rsPromotion['usergroupID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsUserGroups['groupname']?></option>
              <?php
} while ($row_rsUserGroups = mysql_fetch_assoc($rsUserGroups));
  $rows = mysql_num_rows($rsUserGroups);
  if($rows > 0) {
      mysql_data_seek($rsUserGroups, 0);
	  $row_rsUserGroups = mysql_fetch_assoc($rsUserGroups);
  }
			  }
?>
            </select></div></td>
        </tr> <tr>
          <td class="text-nowrap text-right">Valid from:</td>
          <td><input type="hidden" name="startdatetime" id="startdatetime" value="<?php $time = true; $inputname="startdatetime"; $setvalue = $row_rsPromotion['startdatetime']; echo $setvalue; ?>"  class='highlight-days-67 split-date format-y-m-d divider-dash' />
            <?php require('../../../core/includes/datetimeinput.inc.php'); ?> (optional)
            
           </td>
        </tr> <tr>
          <td class="text-nowrap text-right">Valid until:</td>
          <td> <input type="hidden" name="enddatetime" id="enddatetime" value="<?php $time = true; $inputname="enddatetime";  $setvalue = $row_rsPromotion['enddatetime']; echo $setvalue; ?>"  class='highlight-days-67 split-date format-y-m-d divider-dash' />
            <?php require('../../../core/includes/datetimeinput.inc.php'); ?> 
            (optional)</td>
        </tr> <tr>
          <td class="text-nowrap text-right">&nbsp;</td>
          <td>&nbsp;</td>
        </tr> <tr>
          <td class="text-nowrap text-right">Action:</td>
          <td><span id="spryselect1">
            <select name="actiontypeID"  id="actiontypeID" onChange="toggleAction()" class="form-control">
              <option selected="selected" value="" <?php if (!(strcmp("", $row_rsPromotion['actiontypeID']))) {echo "selected=\"selected\"";} ?>>Choose action...</option>
              <option value="0" <?php if (!(strcmp(0, $row_rsPromotion['actiontypeID']))) {echo "selected=\"selected\"";} ?>>No action required</option>
              <option value="1" <?php if (!(strcmp(1, $row_rsPromotion['actiontypeID']))) {echo "selected=\"selected\"";} ?>>Customer buys minimum number of specific product...</option>
              <option value="8" <?php if (!(strcmp(8, $row_rsPromotion['actiontypeID']))) {echo "selected=\"selected\"";} ?>>Customer buys maximum number of specific product...</option>
                <option value="7"  <?php if (!(strcmp(7, $row_rsPromotion['actiontypeID']))) {echo "selected=\"selected\"";} ?>>Customer buys minimum number of any products in category...</option>
                
                 <option value="9"  <?php if (!(strcmp(9, $row_rsPromotion['actiontypeID']))) {echo "selected=\"selected\"";} ?>>Customer buys maximum number of any products in category...</option>
                 
                 
              <option value="2" <?php if (!(strcmp(2, $row_rsPromotion['actiontypeID']))) {echo "selected=\"selected\"";} ?>>Customer spends minimum amount on specific product...</option>
              <option value="3" <?php if (!(strcmp(3, $row_rsPromotion['actiontypeID']))) {echo "selected=\"selected\"";} ?>>Customer spends minimum amount on specific category...</option>
              <option value="4" <?php if (!(strcmp(4, $row_rsPromotion['actiontypeID']))) {echo "selected=\"selected\"";} ?>>Customer spends minimum amount on specific manufacturer/range...</option>
                       <option value="6" <?php if (!(strcmp(6, $row_rsPromotion['actiontypeID']))) {echo "selected=\"selected\"";} ?>>Customer buys minimum square metres on specific category...</option>
                        <option value="5" <?php if (!(strcmp(5, $row_rsPromotion['actiontypeID']))) {echo "selected=\"selected\"";} ?>>Customer buys minimum square metres on specific manufacturer/range...</option>
            </select>
          <span class="selectRequiredMsg">Please select an item.</span></span></td>
        </tr>
        <tr  id="rowActionProduct">
          <td class="text-nowrap text-right">Product:</td>
          <td><select name="actionproductID" class="form-control">
            <option value="0"  <?php if (!(strcmp(0, $row_rsPromotion['actionproductID']))) {echo "selected=\"selected\"";} ?>><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
            <option value="0" <?php if (!(strcmp(0, $row_rsPromotion['actionproductID']))) {echo "selected=\"selected\"";} ?>>Any product</option>
            <?php
do {  
?>
            <option value="<?php echo $row_rsProducts['ID']?>"<?php if (!(strcmp($row_rsProducts['ID'], $row_rsPromotion['actionproductID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsProducts['title']?></option>
<?php
} while ($row_rsProducts = mysql_fetch_assoc($rsProducts));
  $rows = mysql_num_rows($rsProducts);
  if($rows > 0) {
      mysql_data_seek($rsProducts, 0);
	  $row_rsProducts = mysql_fetch_assoc($rsProducts);
  }
?>
          </select></td>
        </tr><tr id="rowActionCategory">
          <td class="text-nowrap text-right"><label for="actioncategoryID">Category:</label></td>
          <td><select name="actioncategoryID" id="actioncategoryID" class="form-control">
            <option value="0" <?php if (!(strcmp(0, $row_rsPromotion['actioncategoryID']))) {echo "selected=\"selected\"";} ?>>Whole order</option>
            <?php
do {  
?>
            <option value="<?php echo $row_rsCategories['ID']?>" <?php if (!(strcmp($row_rsCategories['ID'], $row_rsPromotion['actioncategoryID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsCategories['title']?></option>
            <?php
} while ($row_rsCategories = mysql_fetch_assoc($rsCategories));
  $rows = mysql_num_rows($rsCategories);
  if($rows > 0) {
      mysql_data_seek($rsCategories, 0);
	  $row_rsCategories = mysql_fetch_assoc($rsCategories);
  }
?>
            </select></td>
        </tr>
        <tr id="rowActionManufacturer">
          <td class="text-nowrap text-right"><label for="manufacturerID">Manufacturer/range:</label></td>
          <td><select name="actionmanufacturerID" id="actionmanufacturerID" class="form-control">
            <option value="0">Whole order</option>
            <?php
do {  
?>
            <option value="<?php echo $row_rsManufacturers['ID']?>" <?php if ($row_rsManufacturers['ID'] == $row_rsPromotion['actionmanufacturerID']) {echo "selected=\"selected\"";} ?>><?php echo  isset($row_rsManufacturers['parentname']) ? $row_rsManufacturers['parentname']." > " : ""; echo  $row_rsManufacturers['manufacturername']?></option>
            <?php
} while ($row_rsManufacturers = mysql_fetch_assoc($rsManufacturers));
  $rows = mysql_num_rows($rsManufacturers);
  if($rows > 0) {
      mysql_data_seek($rsManufacturers, 0);
	  $row_rsManufacturers = mysql_fetch_assoc($rsManufacturers);
  }
?>
          </select></td>
        </tr> <tr>
          <td class="text-nowrap text-right">Amount:</td>
          <td class="form-inline"><span id="sprytextfield3">
            <input name="actionamount" type="text"  value="<?php echo $row_rsPromotion['actionamount']; ?>" size="5" maxlength="10" class="form-control" />
            <span class="textfieldRequiredMsg">A value is required.</span><span class="textfieldInvalidFormatMsg">Invalid format.</span><span class="textfieldMinValueMsg">The entered value is less than the minimum required.</span></span>(number or currency)</td>
        </tr> <tr>
          <td class="text-nowrap text-right">&nbsp;</td>
          <td>&nbsp;</td>
        </tr> <tr>
          <td class="text-nowrap text-right">Result:</td>
          <td><span id="spryselect2">
            <select name="resulttypeID" id="resulttypeID" onChange="toggleResult()" class="form-control">
              <option selected="selected" value="" <?php if (!(strcmp("", $row_rsPromotion['resulttypeID']))) {echo "selected=\"selected\"";} ?>>Choose result...</option>
              <option value="0" <?php if (!(strcmp(0, $row_rsPromotion['resulttypeID']))) {echo "selected=\"selected\"";} ?>>No effect of actual order</option>
               <option value="5" <?php if (!(strcmp(5, $row_rsPromotion['resulttypeID']))) {echo "selected=\"selected\"";} ?>>Customer gets monetary value discount on whole order</option>
               <option value="6" <?php if (!(strcmp(6, $row_rsPromotion['resulttypeID']))) {echo "selected=\"selected\"";} ?>>Customer gets monetary value discount on category:</option>      
              <option value="1" <?php if (!(strcmp(1, $row_rsPromotion['resulttypeID']))) {echo "selected=\"selected\"";} ?>>Customer gets % discount on specific product:</option>
              <option value="4" <?php if (!(strcmp(4, $row_rsPromotion['resulttypeID']))) {echo "selected=\"selected\"";} ?>>Customer gets % discount on whole order or category:</option>
              <option value="2" <?php if (!(strcmp(2, $row_rsPromotion['resulttypeID']))) {echo "selected=\"selected\"";} ?>>Customer gets free product(s):</option>
              <option value="7" <?php if (!(strcmp(7, $row_rsPromotion['resulttypeID']))) {echo "selected=\"selected\"";} ?>>Customer gets free product(s) from category:</option>
              <option value="3" <?php if (!(strcmp(3, $row_rsPromotion['resulttypeID']))) {echo "selected=\"selected\"";} ?>>Customer gets free specified shipping</option>
              </select>
            <span class="selectRequiredMsg">Please select an item.</span></span></td>
        </tr>
        <tr id="rowResultProduct">
          <td class="text-nowrap text-right">Product:</td>
          <td><select name="resultproduct" class="form-control">
            <option value="0"  <?php if (!(strcmp(0, $row_rsPromotion['resultproduct']))) {echo "selected=\"selected\"";} ?>>Any product/Whole order</option>
            <?php
do {  
?>
            <option value="<?php echo $row_rsProducts['ID']?>"<?php if (!(strcmp($row_rsProducts['ID'], $row_rsPromotion['resultproduct']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsProducts['title']?></option>
<?php
} while ($row_rsProducts = mysql_fetch_assoc($rsProducts));
  $rows = mysql_num_rows($rsProducts);
  if($rows > 0) {
      mysql_data_seek($rsProducts, 0);
	  $row_rsProducts = mysql_fetch_assoc($rsProducts);
  }
?>
          </select></td>
        </tr>
        <tr id="rowResultCategory">
          <td class="text-nowrap text-right"><label for="categoryID">Category:</label></td>
          <td><select name="categoryID" id="categoryID" class="form-control">
            <option value="0" <?php if (!(strcmp(0, $row_rsPromotion['resultcategoryID']))) {echo "selected=\"selected\"";} ?>>Whole order</option>
            <?php
do {  
?>
<option value="<?php echo $row_rsCategories['ID']?>"<?php if (!(strcmp($row_rsCategories['ID'], $row_rsPromotion['resultcategoryID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsCategories['title']?></option>
            <?php
} while ($row_rsCategories = mysql_fetch_assoc($rsCategories));
  $rows = mysql_num_rows($rsCategories);
  if($rows > 0) {
      mysql_data_seek($rsCategories, 0);
	  $row_rsCategories = mysql_fetch_assoc($rsCategories);
  }
?>
          </select></td>
        </tr> <tr>
          <td class="text-nowrap text-right">Amount:</td>
          <td class="form-inline"><span id="sprytextfield4">
            <input name="resultamount" type="text"  value="<?php echo $row_rsPromotion['resultamount']; ?>" size="5" maxlength="10" class="form-control" />
            <span class="textfieldRequiredMsg">A value is required.</span><span class="textfieldInvalidFormatMsg">Invalid format.</span><span class="textfieldMinValueMsg">The entered value is less than the minimum required.</span></span>(number, % or currency)</td>
        </tr> <tr>
          <td class="text-nowrap text-right">Site:</td>
          <td><select name="regionID" class="form-control">
            <option value="0" <?php if (!(strcmp(0, $row_rsPromotion['regionID']))) {echo "selected=\"selected\"";} ?>>All sites</option>
            <?php
do {  
?>
            <option value="<?php echo $row_rsRegions['ID']?>"<?php if (!(strcmp($row_rsRegions['ID'], $row_rsPromotion['regionID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsRegions['title']?></option>
            <?php
} while ($row_rsRegions = mysql_fetch_assoc($rsRegions));
  $rows = mysql_num_rows($rsRegions);
  if($rows > 0) {
      mysql_data_seek($rsRegions, 0);
	  $row_rsRegions = mysql_fetch_assoc($rsRegions);
  }
?>
          </select></td>
        </tr> <tr>
          <td class="text-nowrap text-right">Standalone</td>
          <td><label>
            <input <?php if (!(strcmp($row_rsPromotion['standalone'],1))) {echo "checked=\"checked\"";} ?> name="standalone" type="checkbox" id="standalone" value="1" />
          Customer cannot use this offer along with any other standalone offers</label></td>
        </tr> <tr>
                  <td class="text-nowrap text-right">Discount Group ID:</td>
                  <td class="form-inline"><label><input name="progressivediscountgroup" type="text" value="<?php echo $row_rsPromotion['progressivediscountgroup']; ?>" class="form-control">
                      
                      Part of a progressive discount group from which only one can be active</label></td>
                </tr> <tr>
          <td class="text-nowrap text-right">Active:</td>
          <td><input <?php if (!(strcmp($row_rsPromotion['statusID'],1))) {echo "checked=\"checked\"";} ?> name="statusID" type="checkbox" id="statusID" value="1" /></td>
        </tr>
      </table></div>
        <div class="TabbedPanelsContent">
          <p>You can add more details for this promotion, e.g. terms and conditions. Fon non-code promotions (i.e. available to all) you can add an image and optional link that will appear in right-hand margin on product pages. Please ensure that image is correct width.</p>
        <p>  <label>
            <input <?php if (!(strcmp($row_rsPromotion['display'],1))) {echo "checked=\"checked\"";} ?> name="display" type="checkbox" id="display" value="1">
            Display in promo lists</label>&nbsp;&nbsp;&nbsp;<label>
                  <input <?php if (!(strcmp($row_rsPromotion['addbasket'],1))) {echo "checked=\"checked\"";} ?> name="addbasket" type="checkbox" id="addbasket" value="1" >
                  Display in basket</label></p>
          <h2>Details </h2>
          <textarea name="promodetails" cols="100" rows="10" id="promodetails" class="form-control"><?php echo htmlentities($row_rsPromotion['promodetails'], ENT_COMPAT, "UTF-8"); ?></textarea>
          <h2>Image (optional)</h2>
          <p>
            <?php if(isset($row_rsPromotion['imageURL'])) { ?>
            <img src="/Uploads/<?php echo $row_rsPromotion['imageURL']; ?>" /><br />
            <label>
              <input name="noImage" id="noImage" type="checkbox" value="1" />
              Remove image</label>
            <br />
            <?php } ?>
            <input type="file" name="filename" id="filename" />
            <input name="imageURL" type="hidden" id="imageURL" value="<?php echo $row_rsPromotion['imageURL']; ?>" />
          </p>
          <h2>Link (optional)</h2>
          <p>
            <input name="linkURL" type="text"  id="linkURL" value="<?php echo $row_rsPromotion['linkURL']; ?>" size="50" maxlength="100"  placeholder="http://" class="form-control"/>
         </p>
          <p>&nbsp;</p>
        </div>
        <div class="TabbedPanelsContent">
        <fieldset class="form-inline"><legend>Add code</legend>
        <label><input type="radio" name="generate" value="1" checked onClick="toggleGenerate(this.value)"> Auto-generate</label> &nbsp;&nbsp; <label><input type="radio" name="generate" value="0" onClick="toggleGenerate(this.value)"> Manual</label>  <br>
        <label class="manual">Code: <input name="uniquecode" id="uniquecode" type="text" size="32" maxlength="32" class="form-control"></label>
        <label class="auto">Quantity: <input name="quantity" id="quantity" type="text" value="1" size="10" maxlength="10" class="form-control"></label>
        valid from <input name="validfrom" id="validfrom" type="hidden" value="<?php  $setvalue = date('Y-m-d'); $inputname = "validfrom"; echo $setvalue; ?>"><?php require('../../../core/includes/datetimeinput.inc.php'); ?>
 until <input name="validuntil" id="validuntil" type="hidden" value="<?php  $setvalue = date('Y-m-d', strtotime("+ 1 YEAR")); $inputname = "validuntil"; echo $setvalue; ?>"><?php require('../../../core/includes/datetimeinput.inc.php'); ?> <button  type="button" class="btn btn-default btn-secondary" onClick="addPromoCode(<?php echo intval($_GET['promoID']); ?>)" >Add code</button>
        </fieldset>
          <div id="uniquecodes"></div>
        </div>
      </div>
    </div>
   
    
     
      <input name="modifiedbyID" type="hidden" id="modifiedbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
      <input name="modifieddatetime" type="hidden" id="modifieddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
      <input name="ID" type="hidden" id="ID" value="<?php echo $row_rsPromotion['ID']; ?>" />
      <input type="hidden" name="MM_update" value="form1" />
      <div><button type="submit" class="btn btn-primary">Save changes</button></div>
  </form><?php if (isset($_GET['defaultTab'])) { echo '<script>
<!--
var TabbedPanels1 = new Spry.Widget.TabbedPanels("TabbedPanels1", {defaultTab:'.intval($_GET['defaultTab']).'});
//-->
    </script>'; } else { ?>
    <script>
<!--
var TabbedPanels1 = new Spry.Widget.TabbedPanels("TabbedPanels1", {defaultTab:0});
//-->
    </script>
    <?php } ?>
    <script>
<!--
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1", "none", {hint:"Add a descriptive title..."});
var spryselect1 = new Spry.Widget.ValidationSelect("spryselect1");
var spryselect2 = new Spry.Widget.ValidationSelect("spryselect2");
var sprytextfield3 = new Spry.Widget.ValidationTextField("sprytextfield3", "real", {minValue:1});
var sprytextfield4 = new Spry.Widget.ValidationTextField("sprytextfield4", "real", {minValue:1});

//-->
    </script>
    
    
    
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsRegions);

mysql_free_result($rsLoggedIn);

mysql_free_result($rsPromotion);

mysql_free_result($rsUserGroups);

mysql_free_result($rsCategories);

mysql_free_result($rsManufacturers);

mysql_free_result($rsProducts);
?>
