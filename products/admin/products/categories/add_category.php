<?php require_once('../../../../Connections/aquiescedb.php'); ?><?php require_once('../../../../core/includes/upload.inc.php'); ?><?php require_once('../../../../core/includes/adminAccess.inc.php'); ?><?php require_once('../../../../core/includes/framework.inc.php'); ?>
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

$MM_restrictGoTo = "../../../../login/index.php?notloggedin=true";
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

// redirect depending on first category from product or not
$returnURL = isset($_GET['firstcategory']) ? "../add_product.php" : "index.php";
$_GET['regionID'] = isset($_GET['regionID']) ? $_GET['regionID'] : $regionID; 

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
		
		if(isset($uploaded["filename2"][0]["newname"]) && $uploaded["filename2"][0]["newname"]!="") { 
			$_POST['imageURL2'] = $uploaded["filename2"][0]["newname"]; 
		}
	}
	$_POST['longID'] = createURLname($_POST['longID'], $_POST['description'], "-",  "productcategory");
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
  $insertSQL = sprintf("INSERT INTO productcategory (longID, regionID, metadescription, metakeywords, subcatofID, title, summary, `description`, imageURL, statusID, showinmenu, createddatetime, createdbyID) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['longID'], "text"),
                       GetSQLValueString($_POST['regionID'], "int"),
                       GetSQLValueString($_POST['metadescription'], "text"),
                       GetSQLValueString($_POST['metakeywords'], "text"),
                       GetSQLValueString($_POST['subCatOf'], "int"),
                       GetSQLValueString($_POST['category'], "text"),
                       GetSQLValueString($_POST['summary'], "text"),
                       GetSQLValueString($_POST['description'], "text"),
                       GetSQLValueString($_POST['imageURL'], "text"),
                       GetSQLValueString(isset($_POST['statusID']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['showinmenu']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['createddatetime'], "date"),
                       GetSQLValueString($_POST['createdbyID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($insertSQL, $aquiescedb) or die(mysql_error());
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
	$lastID = mysql_insert_id();
	$update = "UPDATE productcategory SET ordernum = ".$lastID." WHERE ID = ".$lastID;
	$result = mysql_query($update, $aquiescedb) or die(mysql_error());
  $insertGoTo = $returnURL."?categoryID=".mysql_insert_id();
  if (isset($_SERVER['QUERY_STRING'])) {
    $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
    $insertGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $insertGoTo));exit;
}

$currentPage = $_SERVER["PHP_SELF"];

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsStatus = "SELECT * FROM status";
$rsStatus = mysql_query($query_rsStatus, $aquiescedb) or die(mysql_error());
$row_rsStatus = mysql_fetch_assoc($rsStatus);
$totalRows_rsStatus = mysql_num_rows($rsStatus);

$varRegionID_rsCategories = "1";
if (isset($regionID)) {
  $varRegionID_rsCategories = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsCategories = sprintf("SELECT productcategory.ID, productcategory.title, productcategory.statusID, parentcategory.title AS parent FROM productcategory LEFT JOIN productcategory AS parentcategory ON (productcategory.subcatofID  = parentcategory.ID) WHERE productcategory.statusID = 1 AND productcategory.regionID = %s ORDER BY parentcategory.ordernum, productcategory.ordernum", GetSQLValueString($varRegionID_rsCategories, "int"));
$rsCategories = mysql_query($query_rsCategories, $aquiescedb) or die(mysql_error());
$row_rsCategories = mysql_fetch_assoc($rsCategories);
$totalRows_rsCategories = mysql_num_rows($rsCategories);

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

$queryString_rsCategories = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsCategories") == false && 
        stristr($param, "totalRows_rsCategories") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsCategories = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsCategories = sprintf("&totalRows_rsCategories=%d%s", $totalRows_rsCategories, $queryString_rsCategories);

$_GET['categoryID'] = isset($_GET['categoryID']) ? $_GET['categoryID'] : 0;
?><!doctype html>

<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Add Product Category"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<style><?php if (!(defined("MOD_REWRITE") || isset($_SERVER['HTTP_MOD_REWRITE']))) { // no mod re-write so hide URL option ?>
.longID { display:none; }
<?php } 
if($totalRows_rsRegions<=1) { ?>
.region { display:none; }
<?php } ?></style><script src="../../../../core/scripts/formUpload.js"></script>
<link href="../../../css/defaultProducts.css" rel="stylesheet"  />
<script src="../../../../SpryAssets/SpryValidationTextField.js"></script>
<link href="../../../../SpryAssets/SpryValidationTextField.css" rel="stylesheet"  />
<?php if(isset($body_class)) $body_class .= " products ";  ?>
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" -->
      <h1><i class="glyphicon glyphicon-shopping-cart"></i> Add Product Category</h1>


        <?php require_once('../../../../core/includes/alert.inc.php'); ?>
    <?php if(isset($_GET['firstcategory'])) { ?><p>Before you add your first product, you must first enter a category for it below.</p><?php } ?>
<form action="<?php echo $editFormAction; ?>" method="post" enctype="multipart/form-data" name="form1" id="form1" >
        <table class="form-table"> <tr>
            <td class="text-nowrap text-right">Category:</td>
            <td><span id="sprytextfield1">
              <input name="category" type="text"  id="category"   onblur="seoPopulate(this.value, this.value);" class="form-control" size="50" maxlength="50" />
            <span class="textfieldRequiredMsg">A value is required.</span></span></td>
          </tr>
          <tr class="region">
            <td class="text-nowrap text-right">Site:</td>
            <td><select name="regionID" id="regionID"  class="form-control" >
              <option value="1" <?php if (!(strcmp(1, $_GET['regionID']))) {echo "selected=\"selected\"";} ?>><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
              <?php
do {  
?>
<option value="<?php echo $row_rsRegions['ID']?>"<?php if (!(strcmp($row_rsRegions['ID'], $_GET['regionID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsRegions['title']?></option>
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
            <td class="text-nowrap text-right">Sub category of: </td>
            <td><select name="subCatOf" id="subCatOf"  class="form-control" >
              <option value="0" <?php if (!(strcmp(0, $_GET['categoryID']))) {echo "selected=\"selected\"";} ?>>None</option>
              <?php
do {  
?>
              <option value="<?php echo $row_rsCategories['ID']?>"<?php if (!(strcmp($row_rsCategories['ID'], $_GET['categoryID']))) {echo "selected=\"selected\"";} ?>><?php echo isset($row_rsCategories['parent']) ? $row_rsCategories['parent']." &rsaquo; " : "";echo $row_rsCategories['title']; ?></option>
              <?php
} while ($row_rsCategories = mysql_fetch_assoc($rsCategories));
  $rows = mysql_num_rows($rsCategories);
  if($rows > 0) {
      mysql_data_seek($rsCategories, 0);
	  $row_rsCategories = mysql_fetch_assoc($rsCategories);
  }
?>
            </select></td>
          </tr><tr class="longID">
            <td class="text-nowrap text-right">URL name:</td>
            <td><input name="longID" type="text"  id="longID" value="" size="50" maxlength="100"  class="form-control"  /></td>
          </tr> <tr>
            <td class="text-nowrap text-right top"><label for="summary">Summary: </label><br>
           (appears in category listing)</td>
            <td class="top">
              <textarea name="summary" cols="50" rows="3" id="summary"  class="form-control" ></textarea>
           </td>
          </tr> <tr>
            <td class="text-nowrap text-right top">Description:<br>
              (appears on category page)</td>
            <td class="top"><textarea name="description" cols="50" rows="5" id="description"  class="tinymce form-control" ></textarea></td>
          </tr>
          
          
          <tr class="upload">
            <td class="text-nowrap text-right"> Image:</td>
            <td><input name="filename" type="file" id="filename" size="20" class="fileinput " accept=".jpg,.jpeg,.gif,.png"  /></td>
          </tr>
          <tr class="upload">
            <td class="text-nowrap text-right">Image:</td>
            <td><input name="filename2" type="file" id="filename2" size="20" class="fileinput " accept=".jpg,.jpeg,.gif,.png"  /></td>
          </tr> 
          <tr>
            <td class="text-nowrap text-right"><label for="statusID" data-toggle="tooltip" title="Check to make live. Uncheck to prevent categories and products uniquely within from showing.">Active:</label></td>
            <td><input type="checkbox" name="statusID" id="statusID" value="1"  />
              &nbsp;&nbsp;&nbsp;
              <label data-toggle="tooltip" title="If checked, will show in menus and index pages. Otherwise hidden, and can be used for promotions.">Show on  site:
                <input  name="showinmenu" type="checkbox" id="showinmenu" value="1">
              </label></td>
          </tr>
          <tr>
            <td class="text-nowrap text-right">&nbsp;</td>
            <td><div><button type="submit" class="btn btn-primary" >Add Category</button></div>
            <input name="createdbyID" type="hidden" id="createdbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
            <input name="createddatetime" type="hidden" id="createddatetime" value="<?php echo date("Y-m-d H:i:s"); ?>" /></td>
          </tr>
        </table>
    <input type="hidden" name="MM_insert" value="form1" />
        <input name="imageURL" type="hidden" id="imageURL" />
        <input type="hidden" name="metadescription" id="metadescription" />
        <input type="hidden" name="metakeywords" id="metakeywords" />
</form>
     
    <script>
<!--
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1");
//-->
      </script>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsStatus);

mysql_free_result($rsCategories);

mysql_free_result($rsRegions);

mysql_free_result($rsLoggedIn);
?>
