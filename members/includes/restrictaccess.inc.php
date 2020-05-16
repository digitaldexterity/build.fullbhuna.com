<?php
if (!isset($_SESSION)) {
  session_start();
}
// to do add group
require_once('userfunctions.inc.php');

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


$accesslevel = isset($accesslevel) ?  intval($accesslevel) : 0;
$groupID = isset($groupID) ? intval($groupID) : 0;

if(!thisUserHasAccess($accesslevel, $groupID)) {

	$MM_restrictGoTo = "/login/index.php?notloggedin=true";
  
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