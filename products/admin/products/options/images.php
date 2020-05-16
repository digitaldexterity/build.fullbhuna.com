<?php require_once('../../../../Connections/aquiescedb.php'); ?>
<?php require_once('../../../../core/includes/adminAccess.inc.php'); ?><?php require_once('../../../../core/includes/framework.inc.php'); ?>
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

$MM_restrictGoTo = "/login/index.php?notloggedin=true&alert=".urlencode("You need to be logged in as an Administrator to access this page.");
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

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {


 require_once(SITE_ROOT.'core/includes/upload.inc.php');
 
 	$uploaded = getUploads();
	 $_POST['defaultImageURL'] = (isset($uploaded["filename"][0]["newname"]) && $uploaded["filename"][0]["newname"]!="") ? $uploaded["filename"][0]["newname"] :  $_POST['defaultImageURL'];
	$_POST['imageOverlayURL'] = (isset($uploaded["filename"][10]["newname"]) && $uploaded["filename"][10]["newname"]!="") ? $uploaded["filename"][10]["newname"] : (isset($_POST['noImage'][10]) ? "" :  $_POST['imageOverlayURL']);
	
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE productprefs SET imagesize_index=%s, imagesize_product=%s, imagesize_basket=%s, imagesize_related=%s, imagesize_viewed=%s, imagesize_enlarged=%s, imagesize_category=%s, imagesize_productthumbs=%s, defaultImageURL=%s, imageOverlayURL=%s, gallerytype=%s WHERE ID=%s",
                       GetSQLValueString($_POST['imagesize_index'], "text"),
                       GetSQLValueString($_POST['imagesize_product'], "text"),
                       GetSQLValueString($_POST['imagesize_basket'], "text"),
                       GetSQLValueString($_POST['imagesize_related'], "text"),
                       GetSQLValueString($_POST['imagesize_viewed'], "text"),
                       GetSQLValueString($_POST['imagesize_enlarged'], "text"),
                       GetSQLValueString($_POST['imagesize_category'], "text"),
                       GetSQLValueString($_POST['imagesize_productthumbs'], "text"),
                       GetSQLValueString($_POST['defaultImageURL'], "text"),
                       GetSQLValueString($_POST['imageOverlayURL'], "text"),
                       GetSQLValueString($_POST['gallerytype'], "int"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}
if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {

  $updateGoTo = "index.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo)); exit;
}

$regionID = isset($regionID) ? $regionID : 1;

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsProductPrefs = "SELECT * FROM productprefs WHERE ID = ".$regionID."";
$rsProductPrefs = mysql_query($query_rsProductPrefs, $aquiescedb) or die(mysql_error());
$row_rsProductPrefs = mysql_fetch_assoc($rsProductPrefs);
$totalRows_rsProductPrefs = mysql_num_rows($rsProductPrefs);

$varRegionID_rsCategories = "1";
if (isset($regionID)) {
  $varRegionID_rsCategories = $regionID;
}
?>
<?php if(isset($body_class)) $body_class .= " products ";  ?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Product Images"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<style>
<!--
-->
</style>
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
    <div class="page class">
    <?php require_once('../../../../core/region/includes/chooseregion.inc.php'); ?>
    
      <h1><i class="glyphicon glyphicon-shopping-cart"></i> Product Images</h1>
      <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
<li><a href="index.php" class="link_undo"><i class="glyphicon glyphicon-arrow-left"></i> Product Options</a></li>

   

</ul></div></nav> 
      <?php require_once('../../../../core/includes/alert.inc.php'); ?><form action="<?php echo $editFormAction; ?>" method="POST" enctype="multipart/form-data" name="form1" id="form1">
      <table class="form-table">
        <tr>
          <td align="right">Category products:</td>
          <td><select name="imagesize_index" id="imagesize_index" class="form-control">
            <option value="" <?php if (!(strcmp("", $row_rsProductPrefs['imagesize_index']))) {echo "selected=\"selected\"";} ?>>Choose image size...</option>
            <?php foreach($image_sizes as $size=>$values) {
					if(!isset($values['regionID']) || $values['regionID'] == $regionID) {
					$values['width'] = isset($values['width']) ? $values['width'] : "any" ;
					$values['height'] = isset($values['height']) ? $values['height'] : "any" ;?>
            <option value="<?php echo $size; ?>" <?php if (!(strcmp($size, $row_rsProductPrefs['imagesize_index']))) {echo "selected=\"selected\"";} ?>><?php echo ucwords(str_replace("_", " ",$size))." "; echo trim("(".$values['width']." x ".$values['height'].")","x "); ?></option>
            <?php } } ?>
            <option value="" <?php if (!(strcmp("", $row_rsProductPrefs['imagesize_index']))) {echo "selected=\"selected\"";} ?>>Full size</option>
          </select></td>
        </tr>
        <tr>
          <td align="right">Sub categories:</td>
          <td><select name="imagesize_category" id="imagesize_category" class="form-control">
            <option value="" <?php if (!(strcmp("", $row_rsProductPrefs['imagesize_category']))) {echo "selected=\"selected\"";} ?>>Choose image size...</option>
            <?php foreach($image_sizes as $size=>$values) {
					if(!isset($values['regionID']) || $values['regionID'] == $regionID) {
					$values['width'] = isset($values['width']) ? $values['width'] : "any" ;
					$values['height'] = isset($values['height']) ? $values['height'] : "any" ;?>
            <option value="<?php echo $size; ?>" <?php if (!(strcmp($size, $row_rsProductPrefs['imagesize_category']))) {echo "selected=\"selected\"";} ?>><?php echo ucwords(str_replace("_", " ",$size))." "; echo trim("(".$values['width']." x ".$values['height'].")","x "); ?></option>
            <?php } } ?>
            <option value="" <?php if (!(strcmp("", $row_rsProductPrefs['imagesize_category']))) {echo "selected=\"selected\"";} ?>>Full size</option>
          </select></td>
        </tr>
        <tr>
          <td align="right">&nbsp;</td>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td align="right">Product main image:</td>
          <td><select name="imagesize_product" id="imagesize_product" class="form-control">
            <option value="" <?php if (!(strcmp("", $row_rsProductPrefs['imagesize_product']))) {echo "selected=\"selected\"";} ?>>Choose image size...</option>
            <?php foreach($image_sizes as $size=>$values) { if(!isset($values['regionID']) || $values['regionID'] == $regionID) {
				$values['width'] = isset($values['width']) ? $values['width'] : "any" ;
					$values['height'] = isset($values['height']) ? $values['height'] : "any" ;?>
            <option value="<?php echo $size; ?>" <?php if (!(strcmp($size, $row_rsProductPrefs['imagesize_product']))) {echo "selected=\"selected\"";} ?>><?php echo ucwords(str_replace("_", " ",$size))." "; echo trim("(".$values['width']." x ".$values['height'].")","x "); ?></option>
            <?php } } ?>
            <option value="" <?php if (!(strcmp("", $row_rsProductPrefs['imagesize_product']))) {echo "selected=\"selected\"";} ?>>Full size</option>
          </select></td>
        </tr>
        <tr>
          <td align="right">Product thumbs:</td>
          <td><select name="imagesize_productthumbs" id="imagesize_productthumbs" class="form-control">
            <option value="" <?php if (!(strcmp("", $row_rsProductPrefs['imagesize_productthumbs']))) {echo "selected=\"selected\"";} ?>>Choose image size...</option>
            <?php foreach($image_sizes as $size=>$values) { if(!isset($values['regionID']) || $values['regionID'] == $regionID) {
				$values['width'] = isset($values['width']) ? $values['width'] : "any" ;
					$values['height'] = isset($values['height']) ? $values['height'] : "any" ;?>
            <option value="<?php echo $size; ?>" <?php if (!(strcmp($size, $row_rsProductPrefs['imagesize_productthumbs']))) {echo "selected=\"selected\"";} ?>><?php echo ucwords(str_replace("_", " ",$size))." "; echo trim("(".$values['width']." x ".$values['height'].")","x "); ?></option>
            <?php } } ?>
            <option value="" <?php if (!(strcmp("", $row_rsProductPrefs['imagesize_productthumbs']))) {echo "selected=\"selected\"";} ?>>Full size</option>
          </select></td>
        </tr>
        <tr>
          <td align="right">Product enlarged:</td>
          <td><select name="imagesize_enlarged" id="imagesize_enlarged" class="form-control">
            <option value="" <?php if (!(strcmp("", $row_rsProductPrefs['imagesize_enlarged']))) {echo "selected=\"selected\"";} ?>>Choose image size...</option>
            <?php foreach($image_sizes as $size=>$values) { if(!isset($values['regionID']) || $values['regionID'] == $regionID) {
				$values['width'] = isset($values['width']) ? $values['width'] : "any" ;
					$values['height'] = isset($values['height']) ? $values['height'] : "any" ;?>
            <option value="<?php echo $size; ?>" <?php if (!(strcmp($size, $row_rsProductPrefs['imagesize_enlarged']))) {echo "selected=\"selected\"";} ?>><?php echo ucwords(str_replace("_", " ",$size))." "; echo trim("(".$values['width']." x ".$values['height'].")","x "); ?></option>
            <?php } } ?>
            <option value="" <?php if (!(strcmp("", $row_rsProductPrefs['imagesize_enlarged']))) {echo "selected=\"selected\"";} ?>>Full size</option>
          </select></td>
        </tr>
        <tr>
          <td align="right">&nbsp;</td>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td align="right">Related products:</td>
          <td><select name="imagesize_related" id="imagesize_related" class="form-control">
            <option value="" <?php if (!(strcmp("", $row_rsProductPrefs['imagesize_related']))) {echo "selected=\"selected\"";} ?>>Choose image size...</option>
            <?php foreach($image_sizes as $size=>$values) { if(!isset($values['regionID']) || $values['regionID'] == $regionID) {
				$values['width'] = isset($values['width']) ? $values['width'] : "any" ;
					$values['height'] = isset($values['height']) ? $values['height'] : "any" ;?>
            <option value="<?php echo $size; ?>" <?php if (!(strcmp($size, $row_rsProductPrefs['imagesize_related']))) {echo "selected=\"selected\"";} ?>><?php echo ucwords(str_replace("_", " ",$size))." "; echo trim("(".$values['width']." x ".$values['height'].")","x "); ?></option>
            <?php } }?>
            <option value="" <?php if (!(strcmp("", $row_rsProductPrefs['imagesize_related']))) {echo "selected=\"selected\"";} ?>>Full size</option>
          </select></td>
        </tr>
        <tr>
          <td align="right">Viewed products:</td>
          <td><select name="imagesize_viewed" id="imagesize_viewed" class="form-control">
            <option value="" <?php if (!(strcmp("", $row_rsProductPrefs['imagesize_viewed']))) {echo "selected=\"selected\"";} ?>>Choose image size...</option>
            <?php foreach($image_sizes as $size=>$values) {if(!isset($values['regionID']) || $values['regionID'] == $regionID) {
					$values['width'] = isset($values['width']) ? $values['width'] : "any" ;
					$values['height'] = isset($values['height']) ? $values['height'] : "any" ; ?>
            <option value="<?php echo $size; ?>" <?php if (!(strcmp($size, $row_rsProductPrefs['imagesize_viewed']))) {echo "selected=\"selected\"";} ?>><?php echo ucwords(str_replace("_", " ",$size))." "; echo trim("(".$values['width']." x ".$values['height'].")","x "); ?></option>
            <?php }} ?>
            <option value="" <?php if (!(strcmp("", $row_rsProductPrefs['imagesize_related']))) {echo "selected=\"selected\"";} ?>>Full size</option>
          </select></td>
        </tr>
        <tr>
          <td align="right">Basket items:</td>
          <td><select name="imagesize_basket" id="imagesize_basket" class="form-control">
            <option value="" <?php if (!(strcmp("", $row_rsProductPrefs['imagesize_basket']))) {echo "selected=\"selected\"";} ?>>Choose image size...</option>
            <?php foreach($image_sizes as $size=>$values) { if(!isset($values['regionID']) || $values['regionID'] == $regionID) {
				$values['width'] = isset($values['width']) ? $values['width'] : "any" ;
					$values['height'] = isset($values['height']) ? $values['height'] : "any" ;?>
            <option value="<?php echo $size; ?>" <?php if (!(strcmp($size, $row_rsProductPrefs['imagesize_basket']))) {echo "selected=\"selected\"";} ?>><?php echo ucwords(str_replace("_", " ",$size))." "; echo trim("(".$values['width']." x ".$values['height'].")","x "); ?></option>
            <?php }} ?>
            <option value="" <?php if (!(strcmp("", $row_rsProductPrefs['imagesize_basket']))) {echo "selected=\"selected\"";} ?>>Full size</option>
          </select></td>
        </tr>
        <tr>
          <td align="right" valign="top">Default image:</td>
          <td><?php if (isset($row_rsProductPrefs['defaultImageURL'])) { ?>
            <img src="<?php echo getImageURL($row_rsProductPrefs['defaultImageURL'],"medium"); ?>" alt="Default image" /><br />
            <input name="noImage[0]" type="checkbox" value="1" />
            Remove image
            <?php } else { ?>
            No default image set.
            <?php } ?>
            <br />
            Add/change image below:<br />
            <input name="filename[0]" type="file" class="fileinput" id="filename[0]" size="20" />
            <input name="defaultImageURL" type="hidden" id="defaultImageURL" value="<?php echo $row_rsProductPrefs['defaultImageURL']; ?>" /></td>
        </tr>
        <tr>
          <td align="right" valign="top">Gallery type:</td>
          <td><p>
            <label>
              <input <?php if (!(strcmp($row_rsProductPrefs['gallerytype'],"1"))) {echo "checked=\"checked\"";} ?> type="radio" name="gallerytype" value="1" id="gallerytype_0" />
              Lightbox/thumbs</label>
            &nbsp;&nbsp;&nbsp;
            <label>
              <input <?php if (!(strcmp($row_rsProductPrefs['gallerytype'],"2"))) {echo "checked=\"checked\"";} ?> type="radio" name="gallerytype" value="2" id="gallerytype_1" />
              Carousel</label>
            &nbsp;&nbsp;&nbsp;
            <input <?php if (!(strcmp($row_rsProductPrefs['gallerytype'],"3"))) {echo "checked=\"checked\"";} ?> type="radio" name="gallerytype" value="3" id="gallerytype_2" />
            Zoom/thumbs/replace
            </label>
            <br />
          </p></td>
        </tr>
        <tr>
          <td align="right" valign="top">Sale image overlay:</td>
          <td><?php if (isset($row_rsProductPrefs['imageOverlayURL'])) { ?>
            <img src="<?php echo getImageURL($row_rsProductPrefs['imageOverlayURL'],"medium"); ?>" alt="Sale image overlay" /><br />
            <input name="noImage[10]" type="checkbox" value="1" />
            Remove image
            <?php } else { ?>
            No default image set.
            <?php } ?>
            <br />
            Add/change image below:<br />
            <input name="filename[10]" type="file" class="fileinput" id="filename[10]" size="20" />
            <input name="imageOverlayURL" type="hidden" id="imageOverlayURL" value="<?php echo $row_rsProductPrefs['imageOverlayURL']; ?>" /></td>
        </tr>
      </table>
     <button type="submit" class="btn btn-primary">Save changes</button>
      <input name="ID" type="hidden" id="ID" value="<?php echo $regionID; ?>" />
      <input type="hidden" name="returnURL" id="returnURL" />
      <input type="hidden" name="MM_update" value="form1">
      </form>
    </div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);
?>
