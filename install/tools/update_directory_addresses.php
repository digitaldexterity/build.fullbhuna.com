<?php require_once('../../Connections/aquiescedb.php'); ?>
<?php
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
<?php require_once('../../Connections/aquiescedb.php'); 

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
?>
<?php
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsDirectory = "SELECT * FROM directory";
$rsDirectory = mysql_query($query_rsDirectory, $aquiescedb) or die(mysql_error());
$row_rsDirectory = mysql_fetch_assoc($rsDirectory);
$totalRows_rsDirectory = mysql_num_rows($rsDirectory);
?>
<!DOCTYPE html>
<!-- Copyright Paul Egan. Any unauthorised copying, reproduction or alteration is strictly prohibited -->
<html lang="en" class="full_bhuna install <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Install.dwt.php" codeOutsideHTMLIsLocked="false" --><!-- Copyright Paul Egan. Any unauthorised copying, reproduction or alteration is strictly prohibited -->
<head>
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php echo $site_name; ?> - Install Aquiesce</title>
<!-- InstanceEndEditable -->
<?php require_once('../includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" --><!-- InstanceEndEditable -->
</head>
<body>
<?php require_once('../includes/header.inc.php'); ?>
<main>
<div class="container"><!-- InstanceBeginEditable name="Body" -->
<?php if(isset($_GET['update'])) { ?>
<table border="0" cellpadding="2" cellspacing="0" class="form-table">
  <tr>
    <td><strong>ID</strong></td>
    <td><strong>Entry</strong></td>
    <td><strong>Old address</strong></td>
    <td><strong>New address</strong></td>
  </tr>
  <?php do { ?>
    <tr>
      <td><?php echo $row_rsDirectory['ID']; ?></td>
      <td><?php echo $row_rsDirectory['name']; ?></td>
      <td><?php echo isset($row_rsDirectory['address']) ? $row_rsDirectory['address'] : ""; ?></td>
      <td><?php echo $row_rsDirectory['address1']; ?></td>
    </tr>
    <tr>
      <td colspan="4"><?php if(isset($row_rsDirectory['address']) && strlen(trim($row_rsDirectory['address']))>1 && !isset($row_rsDirectory['address1'])) { 
	  $addresslines = explode("\n",$row_rsDirectory['address']);
	  $address1 = trim($addresslines[0],"\r, ");
	  $address2 = trim($addresslines[1],"\r, ");
	  $address3 = trim($addresslines[2],"\r, ");
	  $address4 = trim($addresslines[3],"\r, ");
	  $address5 = trim($addresslines[4],"\r, ");
	  $update = "UPDATE directory SET address = '', address1 = ".GetSQLValueString($address1, "text").", address2 =".GetSQLValueString($address2, "text").", address3= ".GetSQLValueString($address3, "text").", address4=".GetSQLValueString($address4, "text").", address5= ".GetSQLValueString($address5, "text")." WHERE ID = ".$row_rsDirectory['ID'];
	  echo $update."<br />";
	  mysql_query($update, $aquiescedb) or die(mysql_error());
	  } 
	  // check for new category entries
	  $categoryID = intval($row_rsDirectory['categoryID']); 
	  $directoryID = intval($row_rsDirectory['ID']); 
	  $select = "SELECT ID FROM directoryincategory WHERE directoryID = ".$directoryID." AND categoryID = ".$categoryID;
	  $result = mysql_query($select, $aquiescedb) or die(mysql_error());
	  if(mysql_num_rows($result)==0) {
		  $insert = "INSERT INTO directoryincategory (directoryID, categoryID, createdbyID, createddatetime) VALUES (".$directoryID.",".$categoryID.",0,NOW())";
		  echo $insert;
		  mysql_query($insert, $aquiescedb) or die(mysql_error());
	  }?>&nbsp;</td>
    </tr>
    <?php } while ($row_rsDirectory = mysql_fetch_assoc($rsDirectory)); ?>
</table><?php } else { ?>
<p>This page will upgrade your directory tables as follows:</p>
<ol>
  <li>Update any existing single address box to multiple address fields</li>
  <li>Change one category per directory entry to new multiple category setup</li>
  </ol>
<form action="" method="get" id="form1">
  <label>
    <input type="submit" value="Update..." />
  </label>
  <input name="update" type="hidden" id="update" value="true" />
</form>
<p>&nbsp;</p>
<p>
  <?php } ?>
</p>
<!-- InstanceEndEditable --></div>
</main>
<?php require_once('../includes/footer.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsDirectory);
?>
