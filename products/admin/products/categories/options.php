<?php require_once('../../../../Connections/aquiescedb.php'); ?>
<?php require_once('../../../../core/includes/adminAccess.inc.php'); ?>
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
  $updateSQL = sprintf("UPDATE productprefs SET categorytextposition=%s, colourchooser=%s, subcatsposition=%s, searchsubcats=%s WHERE ID=%s",
                       GetSQLValueString($_POST['categorytextposition'], "int"),
                       GetSQLValueString($_POST['colourchooser'], "int"),
                       GetSQLValueString($_POST['subcatsposition'], "int"),
                       GetSQLValueString($_POST['searchsubcats'], "int"),
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
$query_rsProductPrefs = "SELECT * FROM productprefs WHERE ID = $regionID";
$rsProductPrefs = mysql_query($query_rsProductPrefs, $aquiescedb) or die(mysql_error());
$row_rsProductPrefs = mysql_fetch_assoc($rsProductPrefs);
$totalRows_rsProductPrefs = mysql_num_rows($rsProductPrefs);

if($totalRows_rsProductPrefs==0) {
	mysql_query("INSERT INTO productprefs (ID) VALUES (".$regionID.")", $aquiescedb);
	header("location: options.php"); exit;
	
}
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Category Options"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" --><style><!--
--></style>
<style><!--
<?php if($totalRows_rsRegions<2) { echo ".region { display:none; } "; } ?>
--></style>
<link href="../../../css/defaultProducts.css" rel="stylesheet"  />
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
    <!-- InstanceBeginEditable name="Body" --><?php require_once('../../../../core/region/includes/chooseregion.inc.php'); ?><h1><i class="glyphicon glyphicon-shopping-cart"></i>  Category Options</h1>
<form action="<?php echo $editFormAction; ?>" method="POST" name="form1">
  <p>Category description text position:
    <label>
      <input <?php if (!(strcmp($row_rsProductPrefs['categorytextposition'],"1"))) {echo "checked=\"checked\"";} ?> type="radio" name="categorytextposition" value="1" id="categorytextposition_0">
      Top</label>
    &nbsp;&nbsp;&nbsp;
    <label>
      <input <?php if (!(strcmp($row_rsProductPrefs['categorytextposition'],"2"))) {echo "checked=\"checked\"";} ?> type="radio" name="categorytextposition" value="2" id="categorytextposition_1">
      Bottom</label>
  
    <input name="ID" type="hidden" id="ID" value="<?php echo $regionID; ?>">
  </p>
Colour/finish chooser: 
    <label>
      <input <?php if (!(strcmp($row_rsProductPrefs['colourchooser'],"1"))) {echo "checked=\"checked\"";} ?> type="radio" name="colourchooser" value="1" id="colourchooser_0">
      Select menu</label>
   &nbsp;&nbsp;&nbsp;
    <label>
      <input <?php if (!(strcmp($row_rsProductPrefs['colourchooser'],"2"))) {echo "checked=\"checked\"";} ?> type="radio" name="colourchooser" value="2" id="colourchooser_1">
      Swatches (requires images to be uploaded for all colours)</label>
   
  <p>Sub category position: <label>
      <input <?php if (!(strcmp($row_rsProductPrefs['subcatsposition'],"1"))) {echo "checked=\"checked\"";} ?> type="radio" name="subcatsposition" value="1" id="subcatsposition_0">
      Above products listing</label>&nbsp;&nbsp;&nbsp;
    <label>
      <input <?php if (!(strcmp($row_rsProductPrefs['subcatsposition'],"2"))) {echo "checked=\"checked\"";} ?> type="radio" name="subcatsposition" value="2" id="subcatsposition_1">
      Below products listing</label>
   </p>
   
   <p>Show sub category products: 
     <label>
      <input <?php if (!(strcmp($row_rsProductPrefs['searchsubcats'],"0"))) {echo "checked=\"checked\"";} ?> type="radio" name="searchsubcats" value="0" id="searchsubcats_0">
      Never</label>&nbsp;&nbsp;&nbsp;
    <label>
      <input <?php if (!(strcmp($row_rsProductPrefs['searchsubcats'],"1"))) {echo "checked=\"checked\"";} ?> type="radio" name="searchsubcats" value="1" id="searchsubcats_1">
     When filtered (recommended)</label>
      <input <?php if (!(strcmp($row_rsProductPrefs['searchsubcats'],"2"))) {echo "checked=\"checked\"";} ?> type="radio" name="searchsubcats" value="2" id="searchsubcats_2">
      Always</label>
   </p>
  <p>
    <button class="btn btn-primary" type="submit" name="savebutton" id="savebutton" >Save changes</button>
  </p>
  <input type="hidden" name="MM_update" value="form1">
</form>
<p>&nbsp;</p>
      <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsProductPrefs);
?>
