<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php require_once('../../../core/includes/adminAccess.inc.php'); ?>
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
$query_rsDups = "SELECT product.ID, product.title, product.longID FROM product INNER JOIN (SELECT longID FROM product GROUP BY product.longID HAVING count(ID) > 1) dup ON product.longID = dup.longID";
$rsDups = mysql_query($query_rsDups, $aquiescedb) or die(mysql_error());
$row_rsDups = mysql_fetch_assoc($rsDups);
$totalRows_rsDups = mysql_num_rows($rsDups);
?>
<!DOCTYPE html>
<html lang="en" class="full_bhuna install <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Install.dwt.php" codeOutsideHTMLIsLocked="false" --><!-- Copyright Paul Egan. Any unauthorised copying, reproduction or alteration is strictly prohibited -->
<head>
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Fix Duplicate Product URLs"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<style>
<!--
-->
</style>
<!-- InstanceEndEditable -->
</head>
<body>
<?php require_once('../../includes/header.inc.php'); ?>
<main>
<div class="container"><!-- InstanceBeginEditable name="Body" -->
    <div class="page class">
      <h1>Fix Duplicate Product URLs</h1>
      <?php if ($totalRows_rsDups == 0) { // Show if recordset empty ?>
  <p>All OK</p>
  <?php } // Show if recordset empty ?>
<?php if ($totalRows_rsDups > 0) { // Show if recordset not empty ?>
  <table border="0" cellpadding="0" cellspacing="0" class="form-table">
    
    <?php $prev = ""; $i= 1; do { ?>
      <tr>
        <td><?php echo $row_rsDups['ID']; ?></td>
        <td><?php echo $row_rsDups['title']; ?></td>
        <td><?php 
			 if($row_rsDups['longID']!=$prev) {
			 $prev = $row_rsDups['longID']; $i=1; } else {
				 $i++; 
				 $row_rsDups['longID'].="-".$i;
				 $update = "UPDATE product SET longID = ".GetSQLValueString($row_rsDups['longID'],"text")." WHERE ID = ".$row_rsDups['ID'];
				 //mysql_query($update, $aquiescedb) or die(mysql_error());
				 echo $update;
			 } 
			 echo $row_rsDups['longID']; ?></td>
      </tr>
      <?php } while ($row_rsDups = mysql_fetch_assoc($rsDups)); ?>
  </table>
  <?php } // Show if recordset not empty ?>
    </div>
  <!-- InstanceEndEditable --></div>
</main>
<?php require_once('../../includes/footer.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsDups);
?>
