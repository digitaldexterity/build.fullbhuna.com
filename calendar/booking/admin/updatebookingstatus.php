<?php require_once('../../../Connections/aquiescedb.php'); ?>
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

$MM_restrictGoTo = "../../../login/index.php";
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
?><?php
$newStatus = (($_GET['statusID']+1) >2) ? 0 : $_GET['statusID']+1;
$newStatus = get_magic_quotes_gpc() ? stripslashes($newStatus) : $newStatus;
$newStatus = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($newStatus) : mysql_escape_string($newStatus);
mysql_select_db($database_aquiescedb, $aquiescedb);
$update = "UPDATE bookinginstance SET statusID = ".intval($newStatus)." WHERE ID = ".intval($_GET['bookingID']);
$result = mysql_query($update, $aquiescedb) or die(mysql_error());
?>
<a href="javascript:void(0);" onclick="getData('updatebookingstatus.php?bookingID=<?php echo intval($_GET['bookingID']); ?>&statusID=<?php echo $newStatus; ?>','status<?php echo intval($_GET['bookingID']); ?>')" title="Click here to change booking status"><?php

if (($newStatus == 1)) { ?>
                    <img src="../../../core/images/icons/green-light.png" alt="This booking is confirmed" style="vertical-align:
middle;" />
                  <?php } else if (($newStatus == 0)) { ?>
                  <img src="../../../core/images/icons/amber-light.png" alt="This booking has not been confirmed" width="16" height="16" style="vertical-align:
middle;" />
                  <?php } else { ?>
                  <img src="../../../core/images/icons/red-light.png" alt="This booking has been refused or cancelled" width="16" height="16" style="vertical-align:
middle;" />
                    <?php } ?></a>