<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php 
@error_reporting(6143); // 0 = display no errors, 6143 display all
@ini_set("display_errors", 1); // 0 = don't display none, 1 = display/
	
	
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

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsWPEvents = "SELECT * FROM wp_calevents WHERE activeEvent = 1";
$rsWPEvents = mysql_query($query_rsWPEvents, $aquiescedb) or die(mysql_error());
$row_rsWPEvents = mysql_fetch_assoc($rsWPEvents);
$totalRows_rsWPEvents = mysql_num_rows($rsWPEvents);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsSections = "SELECT * FROM newssection";
$rsSections = mysql_query($query_rsSections, $aquiescedb) or die(mysql_error());
$row_rsSections = mysql_fetch_assoc($rsSections);
$totalRows_rsSections = mysql_num_rows($rsSections);
?>
<!DOCTYPE html>
<html lang="en" class="full_bhuna install <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Install.dwt.php" codeOutsideHTMLIsLocked="false" --><!-- Copyright Paul Egan. Any unauthorised copying, reproduction or alteration is strictly prohibited -->
<head>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Import WordPress</title>
<!-- InstanceEndEditable -->
<?php require_once('../../includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" --><!-- InstanceEndEditable -->
</head>
<body>
<?php require_once('../../includes/header.inc.php'); ?>
<main>
<div class="container"><!-- InstanceBeginEditable name="Body" -->
<p>WordPress Posts to Full Bhuna News</p>
<p>1. import the WordPress Tables (wp_ prefixed)</p>
<form><select name="sectionID">
  <?php do { ?>
    <option value="<?php echo $row_rsSections['ID']; ?>"><?php echo $row_rsSections['sectioname']; ?></option>
    <?php } while ($row_rsSections = mysql_fetch_assoc($rsSections)); ?></select>
<input value="Import" type="submit" /><input type="hidden" name="import" /></form>
<?php if(isset($_GET['import'])) { ?>
<table class="form-table">

<?php do { ?>
<tr>
  <td><?php 
	  $eventdatetime = date('Y-m-d', $row_rsWPEvents['date'])." ".$row_rsWPEvents['start_time'];
	  $insert = "INSERT INTO news (title, summary, body,sectionID, status, postedbyID, eventdatetime,posteddatetime) VALUES(".GetSQLValueString($row_rsWPEvents['title'], "text").",".GetSQLValueString($row_rsWPEvents['shortdesc'], "text").",".GetSQLValueString($row_rsWPEvents['description'], "text").",".GetSQLValueString($_GET['sectionID'].".", "int").",1,0,".GetSQLValueString($eventdatetime, "date").",".GetSQLValueString($eventdatetime, "date").")";
	  mysql_query($insert, $aquiescedb) or die(mysql_error());
	  echo $insert;
	  ?></td></tr>
  
  <?php } while ($row_rsWPEvents = mysql_fetch_assoc($rsWPEvents)); ?></table>
  <?php } ?>
<!-- InstanceEndEditable --></div>
</main>
<?php require_once('../../includes/footer.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsWPEvents);

mysql_free_result($rsSections);
?>
