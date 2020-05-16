<?php require_once('../../../Connections/aquiescedb.php'); ?><?php require_once('../../../core/includes/adminAccess.inc.php'); ?><?php require_once('../../../core/includes/upload.inc.php'); ?><?php require_once('../../../core/includes/framework.inc.php'); ?>
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

$MM_restrictGoTo = "../../../login/index.php?notloggedin=true";
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


if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
	$uploaded = getUploads();
if (isset($uploaded) && is_array($uploaded)) {
	if(isset($uploaded["filename"][0]["newname"]) && $uploaded["filename"][0]["newname"]!="") { 
		$_POST['imageURL'] = $uploaded["filename"][0]["newname"]; 
	}
}

$_POST['longID'] = createURLname($_POST['longID'], $_POST['title'], "-",  "product", $_POST['ID']);
}


if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
  $insertSQL = sprintf("INSERT INTO product (longID, productcategoryID, metadescription, metakeywords, datetimecreated, createdbyID, title, `description`, price, pricetype, imageURL, statusID, `condition`) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['longID'], "text"),
                       GetSQLValueString($_POST['productcategoryID'], "int"),
                       GetSQLValueString($_POST['metadescription'], "text"),
                       GetSQLValueString($_POST['metakeywords'], "text"),
                       GetSQLValueString($_POST['createddatetime'], "date"),
                       GetSQLValueString($_POST['createdbyID'], "int"),
                       GetSQLValueString($_POST['title'], "text"),
                       GetSQLValueString($_POST['description'], "text"),
                       GetSQLValueString($_POST['price'], "double"),
                       GetSQLValueString($_POST['pricetype'], "int"),
                       GetSQLValueString($_POST['imageURL'], "text"),
                       GetSQLValueString($_POST['statusID'], "int"),
                       GetSQLValueString($_POST['condition'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($insertSQL, $aquiescedb) or die(mysql_error());
}
if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {  
  $lastInsertID = mysql_insert_id();
  $update = "UPDATE product SET ordernum = ".$lastInsertID." WHERE ID = ".$lastInsertID.""; // set ordernum same as ID
  mysql_query($update, $aquiescedb) or die(mysql_error());
   $insert = "INSERT INTO productinregion (productID, regionID, createdbyID, createddatetime) VALUES (".$lastInsertID.",".$regionID.",".GetSQLValueString($_POST['createdbyID'], "int").",'".date('Y-m-d H:i:s')."')";
   mysql_query($insert, $aquiescedb) or die(mysql_error());

  $insertGoTo = "modify_product.php?defaulTab=1&productID=".$lastInsertID;
  if (isset($_SERVER['QUERY_STRING'])) {
    $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
    $insertGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $insertGoTo));exit;
}

$varUsername_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $varUsername_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT users.ID, users.usertypeID, users.regionID FROM users WHERE users.username = %s", GetSQLValueString($varUsername_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

// set userID to filter categories by if Agent
$userID = ($_SESSION['MM_UserGroup'] ==6) ?  $row_rsLoggedIn['ID'] : 0;

$varRegionID_rsProductCategories = "1";
if (isset($regionID)) {
  $varRegionID_rsProductCategories = $regionID;
}
$varUserID_rsProductCategories = "0";
if (isset($userID)) {
  $varUserID_rsProductCategories = $userID ;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsProductCategories = sprintf("SELECT productcategory.ID, productcategory.title FROM productcategory LEFT JOIN directoryuser ON (productcategory.directoryID = directoryuser.directoryID) WHERE productcategory.statusID = 1 AND (productcategory.regionID = %s   OR productcategory.regionID =0) AND (%s = 0 OR %s = directoryuser.userID) GROUP BY productcategory.ID ORDER BY productcategory.title ASC", GetSQLValueString($varRegionID_rsProductCategories, "int"),GetSQLValueString($varUserID_rsProductCategories, "int"),GetSQLValueString($varUserID_rsProductCategories, "int"));
$rsProductCategories = mysql_query($query_rsProductCategories, $aquiescedb) or die(mysql_error());
$row_rsProductCategories = mysql_fetch_assoc($rsProductCategories);
$totalRows_rsProductCategories = mysql_num_rows($rsProductCategories);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsManufacturers = "SELECT * FROM productmanufacturer WHERE statusID = 1 ORDER BY manufacturername ASC";
$rsManufacturers = mysql_query($query_rsManufacturers, $aquiescedb) or die(mysql_error());
$row_rsManufacturers = mysql_fetch_assoc($rsManufacturers);
$totalRows_rsManufacturers = mysql_num_rows($rsManufacturers);

$varRegionID_rsProductPrefs = "1";
if (isset($regionID)) {
  $varRegionID_rsProductPrefs = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsProductPrefs = sprintf("SELECT * FROM productprefs WHERE ID = %s", GetSQLValueString($varRegionID_rsProductPrefs, "int"));
$rsProductPrefs = mysql_query($query_rsProductPrefs, $aquiescedb) or die(mysql_error());
$row_rsProductPrefs = mysql_fetch_assoc($rsProductPrefs);
$totalRows_rsProductPrefs = mysql_num_rows($rsProductPrefs);


?>
<!doctype html>

<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Add Product"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script src="../../../SpryAssets/SpryValidationTextField.js"></script>
<script src="/core/scripts/formUpload.js"></script>
<style>
<!--
<?php if (!(defined("MOD_REWRITE") || isset($_SERVER['HTTP_MOD_REWRITE']))) { // no mod re-write so hide URL option ?>
.longID { display:none; }
<?php }  if ($totalRows_rsManufacturers==0) { ?>
.manufacturer { display:none; } 
<?php } 
if($_SESSION['MM_UserGroup']<8) {
	echo ".rank8 { display:none; } ";
}
 ?>
--></style>
<?php $WYSIWYGstyle = "simpletext"; $editor_height = 100; require_once('../../../core/tinymce/tinymce.inc.php'); ?>

<link href="../../../SpryAssets/SpryValidationTextField.css" rel="stylesheet"  />
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
    <!-- InstanceBeginEditable name="Body" -->
   
<h1><i class="glyphicon glyphicon-shopping-cart"></i> Add Product </h1>
        <?php if(isset($submit_error)) { ?><p class="alert alert-danger" role="alert"><?php echo $submit_error; ?></p><?php } ?>
<form action="<?php echo $editFormAction; ?>" method="post" enctype="multipart/form-data" name="form1" id="form1"   onsubmit="if(Spry.Widget.Form.validate(form1)) { seoPopulate(document.getElementById('title').value,top.frames[0].document.getElementById('tinymce').innerHTML); } else return false;"  >
        <table class="form-table form-inline"> <tr>
            <td colspan="2" class="text-nowrap"><select name="productcategoryID" class="form-control">
              <option value="0" <?php if (!(strcmp(0, @$_GET['categoryID']))) {echo "selected=\"selected\"";} ?>>Choose category...</option>
              <option value="0" <?php if (!(strcmp(0, @$_GET['categoryID']))) {echo "selected=\"selected\"";} ?>>None</option>
              <?php
do {  
?><option value="<?php echo $row_rsProductCategories['ID']?>"<?php if (!(strcmp($row_rsProductCategories['ID'], @$_GET['categoryID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsProductCategories['title']?></option>
              <?php
} while ($row_rsProductCategories = mysql_fetch_assoc($rsProductCategories));
  $rows = mysql_num_rows($rsProductCategories);
  if($rows > 0) {
      mysql_data_seek($rsProductCategories, 0);
	  $row_rsProductCategories = mysql_fetch_assoc($rsProductCategories);
  }
?>
              </select> 
            <a href="categories/index.php?returnURL=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="link_add icon_with_text rank8">Add Category</a>            </td>
            </tr>
          <tr class="manufacturer">
            <td colspan="2" class="text-nowrap form-inline">
              <select name="manufacturerID" id="manufacturerID" class="form-control">
                <option value="">Choose manufacturer...</option>
                <?php
do {  
?>
                <option value="<?php echo $row_rsManufacturers['ID']?>"><?php echo $row_rsManufacturers['manufacturername']?></option>
                <?php
} while ($row_rsManufacturers = mysql_fetch_assoc($rsManufacturers));
  $rows = mysql_num_rows($rsManufacturers);
  if($rows > 0) {
      mysql_data_seek($rsManufacturers, 0);
	  $row_rsManufacturers = mysql_fetch_assoc($rsManufacturers);
  }
?>
              </select>
            <a href="categories/manufacturer/add_manufacturer.php?returnURL=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="link_add icon_with_text rank8">Add Manufacturer</a> </td>
          </tr> <tr>
            <td colspan="2" class="text-nowrap"><span id="sprytextfield2">
              <input name="title" type="text"  id="title" value="<?php echo isset($_POST['title']) ? htmlentities($_POST['title'], ENT_COMPAT, "UTF-8") : ""; ?>" size="50" maxlength="100" class="form-control" />
            <span class="textfieldRequiredMsg">A name is required.</span></span></td>
          </tr> 
          <tr class="upload">
            <td class="text-nowrap text-right">Image:</td>
            <td><input name="filename" type="file" class="fileinput " accept=".jpg,.jpeg,.gif,.png" /></td>
          </tr> <tr>
            <td class="text-nowrap text-right">Price:</td>
            <td class="form-inline"><span id="sprytextfield3">
            <input name="price" type="text"  id="price" size="8" maxlength="8"  class="form-control" /><select name="pricetype" class="form-control">
                    <option value="1" selected="selected">per item</option>
                    <option value="2">per kg</option>
                    <option value="3">per hour</option>
                    <option value="4">per day</option>
                  </select>
GBP <span class="textfieldRequiredMsg">A price is required. For POA enter zero.</span><span class="textfieldInvalidFormatMsg">Invalid format.</span></span>
            </td>
          </tr> <tr>
            <td><input name="longID" type="hidden"  id="longID" value="" size="50" maxlength="100" />
              
              <input name="description" id="description" type="hidden" value="<?php echo (isset($_REQUEST['description'])) ? htmlentities($_REQUEST['description'], ENT_COMPAT, "UTF-8") : $row_rsProductPrefs['producttemplateHTML']; ?>">
            
              <input name="createdbyID" type="hidden" id="createdbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
            <input name="createddatetime" type="hidden" id="createddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" /></td>
            <td><div><button type="submit" class="btn btn-primary" >Next...</button>
              </div></td>
          </tr>
        </table>
      
      
       
<input type="hidden" name="statusID" value="1" />
          <input type="hidden" name="MM_insert" value="form1" />
          <input name="imageURL" type="hidden" id="imageURL" />
       
          <input type="hidden" name="metadescription" id="metadescription" />
          <input type="hidden" name="metakeywords" id="metakeywords" />
          <input type="hidden" name="condition" value="<?php echo intval($row_rsProductPrefs['defaultcondition']); ?>" />
          
</form>
<script>
<!--
var sprytextfield2 = new Spry.Widget.ValidationTextField("sprytextfield2", "none", {hint:"Enter product name"});
var sprytextfield3 = new Spry.Widget.ValidationTextField("sprytextfield3", "currency", {useCharacterMasking:true});
//-->
if(!commonjsversion || commonjsversion<2.0) alert("This page requires a later version of the Javascript library. Please reinstall.");
    </script>
  <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsProductCategories);

mysql_free_result($rsLoggedIn);

mysql_free_result($rsManufacturers);

mysql_free_result($rsProductPrefs);
?>