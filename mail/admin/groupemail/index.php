<?php require_once('../../../Connections/aquiescedb.php'); ?><?php require_once('../../../core/includes/adminAccess.inc.php'); ?><?php require_once('../../includes/sendmail.inc.php'); ?>
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

$currentPage = $_SERVER["PHP_SELF"];

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if (isset($_GET["deleteemailID"])) {
	 mysql_select_db($database_aquiescedb, $aquiescedb);
	$select = "SELECT active FROM groupemail WHERE ID = ".GetSQLValueString($_GET['deleteemailID'], "int");
	$result = mysql_query($select, $aquiescedb) or die(mysql_error());
	$row = mysql_fetch_assoc($result);
	if($row['active']==1) {
		$submit_error = "You cannot delete an email that is sending. Please stop sending first.";
	} else {
	
  $delete = "DELETE FROM groupemail WHERE ID=".GetSQLValueString($_GET['deleteemailID'], "int");

 
  mysql_query($delete, $aquiescedb) or die(mysql_error());
  $delete = "DELETE FROM groupemailclick WHERE groupemailID=".GetSQLValueString($_GET['deleteemailID'], "int");

 
  mysql_query($delete, $aquiescedb) or die(mysql_error());
  deleteMailList($_GET['deleteemailID']);
	}
  
}


if (isset($_GET["pauseemailID"])) {
	pauseGroupEmail($_GET["pauseemailID"]);
	
}

if (isset($_GET["duplicateemailID"])) {
	$newID = duplicateMySQLRecord("groupemail", intval($_GET["duplicateemailID"]));
	if($newID>0) {
		$updateSQL = "UPDATE groupemail SET active = 0, readcount = 0, clickcount = 0, createddatetime = NOW(), subject = CONCAT(subject,' (copy)') WHERE ID = ".$newID ;
		 mysql_select_db($database_aquiescedb, $aquiescedb);
  $query = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
	}
}


mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = "SELECT usertypeID, users.username FROM users";
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);


?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Manage Group Email"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<style>
<!--
-->
</style>
<script>
var fb_keepAlive = true;
</script>
<script>
$(document).ready(function(e) {
	var sendInterval = 1000 * <?php echo defined('GROUP_EMAIL_SEND_PERIOD') ? GROUP_EMAIL_SEND_PERIOD : 3; ?> * <?php echo defined('GROUP_EMAIL_SEND_COUNT') ? GROUP_EMAIL_SEND_COUNT : 1; ?>;
setInterval(function(){refeshEmails()}, sendInterval);
    
});

function refeshEmails() {
	getData('/mail/ajax/groupemail.ajax.php');
	getData('/mail/admin/includes/groupEmailList.inc.php?refresh=true<?php 
	echo isset($_GET['totalRows_rsGroupEmails']) ? "&totalRows_rsGroupEmails=".intval($_GET['totalRows_rsGroupEmails']) : "" ; 
	echo isset($_GET['pageNum_rsGroupEmails']) ? "&pageNum_rsGroupEmails=".intval($_GET['pageNum_rsGroupEmails']) : "" ; ?>','groupEmailList','loadingDIV','')
}

</script>


<link href="../../css/mailDefault.css" rel="stylesheet" type="text/css" />
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" --><div class="page mail"><?php require_once('../../../core/region/includes/chooseregion.inc.php'); ?>
  <h1><i class="glyphicon glyphicon-envelope"></i> Group Mail Manager</h1>
  <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
    <li class="nav-item"><a href="add_mail.php" class="nav-link"><i class="glyphicon glyphicon-plus-sign"></i> Add Group Email</a></li>
    <li class="nav-item"><a href="../templates/index.php" class="nav-link"><i class="glyphicon glyphicon-file"></i> Templates</a></li>
    <li class="nav-item"><a href="clicktrack.php" class="nav-link"><i class="glyphicon glyphicon-user"></i> Click tracking</a></li>
    <li class="nav-item"><a href="opt-outs.php" class="nav-link"><i class="glyphicon glyphicon-remove"></i> Opt Outs</a></li>
    <li class="nav-item"><a href="bounces.php" class="nav-link"><i class="glyphicon glyphicon-ban-circle"></i> Bounces</a></li>
    <li  class="nav-item" id="link_correspondence"><a href="../index.php" class="nav-link"><i class="glyphicon glyphicon-arrow-left"></i> Correspondence</a></li>
    </ul></div></nav><?php if(isset($_GET['refresh'])) { ?>
  <p class="message alert alert-info" role="alert">Your email is now sending. Keeping this page open will ensure continuous delivery. <?php if($dailymax>0) { ?>(For server compliance there is a daily cap of <?php echo $dailymax; ?>)<?php } ?></p>
  <?php } ?>
  
  <?php if(isset($submit_error)) { ?>
  <p class="alert alert-danger" role="alert"><?php echo $submit_error; ?></p>
  <?php } ?>
  <div id="groupEmailList">
    
  <?php require_once('../includes/groupEmailList.inc.php'); ?></div>
  
  <table class="form-table">
  <thead>
    <tr>
      <th>&nbsp;</th>
      <th>Key</th>
      <th>&nbsp;</th>
      </tr></thead><tbody>
    <tr>
      <td class="top"><img src="../../images/loading_16x16.png" alt="Currently sending group email" width="16" height="16" style="vertical-align:
middle;" /></td>
      <td class="top">Sending </td>
      <td class="top"><em>This group email is currently being sent.</em></td>
      </tr>
    <tr>
      <td class="top"><i class="glyphicon glyphicon-time"></i></td>
      <td class="top">Pending</td>
      <td class="top"><em>This group email is awaiting to be sent. </em></td>
      </tr>
    <tr>
      <td class="top"><i class="glyphicon glyphicon-ok text-success"></i></td>
      <td class="top">Complete</td>
      <td class="top"><em>This group email has completed sending.</em></td>
      </tr>
    <tr>
      <td class="top"><i class="glyphicon glyphicon-pencil text-warning"></i></td>
      <td class="top">Editing</td>
      <td class="top"><em>This document is still under construction.</em></td>
      </tr>
    </table></tbody>
  <p>*Views - detection works  when activated with graphic emails and only if recipient views images within email. Clicks only reported if click detection is on.</p>
  <div id="loadingDIV"><!-- not used as we want loading to be transparent -->&nbsp;</div>
  
   </div><!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);
?>
