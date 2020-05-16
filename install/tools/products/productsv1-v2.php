<?php require_once('../../../Connections/aquiescedb.php'); ?><?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "10";
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

$MM_restrictGoTo = "../../upgrade/login.php?notloggedin=true";
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

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsAllProducts = "SELECT product.ID, productdescription.title AS oldtitle, productdescription.`description` AS olddescription, productdescription.price AS oldprice, productdescription.statusID AS oldstatusID, product.title, product.`description`, product.price, product.statusID FROM product LEFT JOIN productdescription ON (product.ID = productdescription.productID) WHERE productdescription.regionID = 1";
$rsAllProducts = mysql_query($query_rsAllProducts, $aquiescedb) or die(mysql_error());
$row_rsAllProducts = mysql_fetch_assoc($rsAllProducts);
$totalRows_rsAllProducts = mysql_num_rows($rsAllProducts);

if(isset($_GET['upgrade'])) {
	do { 
  $update = "UPDATE product SET title = '".$row_rsAllProducts['oldtitle']."', description = '".$row_rsAllProducts['olddescription']."', price = ".$row_rsAllProducts['oldprice'].", statusID = ".$row_rsAllProducts['oldstatusID']." WHERE price = 0 AND ID = ".$row_rsAllProducts['ID'];
  $result = mysql_query($update, $aquiescedb) or die(mysql_error());
   echo $update."<br />";
     } while ($row_rsAllProducts = mysql_fetch_assoc($rsAllProducts)); 
	 }?>
<!DOCTYPE html>
<html lang="en" class="full_bhuna install <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Install.dwt.php" codeOutsideHTMLIsLocked="false" --><!-- Copyright Paul Egan. Any unauthorised copying, reproduction or alteration is strictly prohibited -->
<head>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Upgrade Products V1 to V2</title>
<!-- InstanceEndEditable -->
<?php require_once('../../includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<!-- InstanceEndEditable -->
</head>
<body>
<?php require_once('../../includes/header.inc.php'); ?>
<main>
<div class="container"><!-- InstanceBeginEditable name="Body" -->
<h1>Upgrade products version 1 to 2</h1>
<p>This page will upgrade the Products from version 1 to version 2. Currently only does so for region 1.</p>
<form action="" method="get" id="form1" role="form">
  <input type="submit" name="update" id="update" value="Submit" />
  <input name="upgrade" type="hidden" id="upgrade" value="true" />
</form>
  <?php if(isset($_GET['upgrade'])) { ?>

<p>Done!</p>
<?php } ?>
<!-- InstanceEndEditable --></div>
</main>
<?php require_once('../../includes/footer.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsAllProducts);
?>
