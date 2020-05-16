<?php require_once('../Connections/aquiescedb.php'); ?><?php require_once('includes/productHeader.inc.php'); ?><?php require_once('../core/includes/framework.inc.php'); ?>
<?php require_once('includes/products.inc.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "";
$MM_donotCheckaccess = "true";

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
    if (($strUsers == "") && true) { 
      $isValid = true; 
    } 
  } 
  return $isValid; 
}

$MM_restrictGoTo = "../login/signup.php?notloggedin=true";
$MM_restrictGoTo .= isset($_GET['email']) ? "&email=".$_GET['email'] : "";
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

$varUsername_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $varUsername_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT users.ID, users.firstname FROM users WHERE users.username = %s", GetSQLValueString($varUsername_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

$colname_rsProduct = "-1";
if (isset($_GET['productID'])) {
  $colname_rsProduct = $_GET['productID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsProduct = sprintf("SELECT ID, title FROM product WHERE ID = %s", GetSQLValueString($colname_rsProduct, "int"));
$rsProduct = mysql_query($query_rsProduct, $aquiescedb) or die(mysql_error());
$row_rsProduct = mysql_fetch_assoc($rsProduct);
$totalRows_rsProduct = mysql_num_rows($rsProduct);

$select = "SELECT * FROM productnotify WHERE userID = ".$row_rsLoggedIn['ID']." AND productID = ".$row_rsProduct['ID'];
$result = mysql_query($select, $aquiescedb) or die(mysql_error());
if(mysql_num_rows($result)==0) {
$insert = "INSERT INTO productnotify (userID, productID, createddatetime) VALUES (".$row_rsLoggedIn['ID'].",".$row_rsProduct['ID'].",'".date('Y-m-d H:i:s')."')";
$result = mysql_query($insert, $aquiescedb) or die(mysql_error());
}

 
?><?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!DOCTYPE html>
<html class="" lang="en"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Product availibility notification"; echo $pageTitle." | ".$site_name; ?>
</title>
<!-- InstanceEndEditable -->
<!--[if IE]><![endif]-->
<?php require_once('../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<!-- InstanceEndEditable -->
</head>
<body id="OffTemplate" class="bootstrap <?php echo $body_class;  ?>">
<?php require_once('../local/includes/header.inc.php'); ?>
<main id="content"><!-- InstanceBeginEditable name="Body" -->
      <section>
<div id="pageProductNotify" class="container">
  <h1>Thank you, <?php echo $row_rsLoggedIn['firstname']; ?></h1>
  <p>We will send you an email once <?php echo $row_rsProduct['title']; ?> is back in stock.</p>
  <p><a href="index.php<?php echo isset($_GET['categoryID']) ? "?categoryID=".intval($_GET['categoryID']) : "";  ?>">Continue shopping...</a></p>
   <?php $productID = isset($_GET['productID']) ? $_GET['productID']  : 0;
$regionID = isset($regionID) ? $regionID : 1;
$categoryID = isset($_GET['categoryID']) ? $_GET['categoryID'] : 0;
require_once('includes/relatedProducts.inc.php'); ?>
   </div></section>
  <!-- InstanceEndEditable --></main>
<?php require_once('../local/includes/footer.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsProduct);
?>
