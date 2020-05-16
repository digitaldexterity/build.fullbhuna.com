<?php  // restrict access to admin pages based on region, rank, group etc.
// start with standard
if (!isset($_SESSION)) {
  session_start();
}

if(isset($_GET['crossoversessions'])) {
	if(isset($_GET['token']) && $_GET['token'] == md5($_GET['MM_Username'].$_GET['MM_UserGroup'].PRIVATE_KEY)) {
		$_SESSION = $_GET;
		header("location: ".$_SERVER['PHP_SELF']); exit; // refresh without URL
	} else {
		die("SECURITY ERROR");
	}
}
$MM_authorizedUsers = isset($MM_authorizedUsers) ? $MM_authorizedUsers : "7,8,9,10";
$MM_donotCheckaccess = "false";

// *** Restrict Access To Page: Grant or deny access to this page
function isGroupAuthorized($strUsers, $strGroups, $UserName, $UserGroup) { 
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
if (!((isset($_SESSION['MM_Username'])) && (isGroupAuthorized("",$MM_authorizedUsers, $_SESSION['MM_Username'], $_SESSION['MM_UserGroup'])))) {   
  $MM_qsChar = "?";
  $MM_referrer = $_SERVER['PHP_SELF'];
  if (strpos($MM_restrictGoTo, "?")) $MM_qsChar = "&";
  if (isset($_SERVER['QUERY_STRING']) && strlen($_SERVER['QUERY_STRING']) > 0) 
  $MM_referrer .= "?" . $_SERVER['QUERY_STRING'];
  $MM_restrictGoTo = $MM_restrictGoTo. $MM_qsChar . "accesscheck=" . urlencode($MM_referrer);
  header("Location: ". $MM_restrictGoTo); 
  exit;
}




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



if(isset($_POST['setbootstrap'])) {
	setcookie('setbootstrap', $_POST['setbootstrap'], time() + (86400 * 30), "/"); // 86400 = 1 day
}

if(isset($_POST['debugging'])) {
	if($_POST['debugging'] == 1) { 
		$_SESSION['debug'] = true;
	} else  { 
		unset($_SESSION['debug']); 
	}	
}

if(isset($_POST['setregionID'])) { // change region 
	$_SESSION['regionID'] = intval($_POST['setregionID']);
	$resetDomain = true;
}

$domain = preg_replace("/www\./i","",$_SERVER['HTTP_HOST'] );

if(isset($_SESSION['regionID']) && $_SESSION['regionID'] >0) {
	$where = " WHERE ID = ".intval($_SESSION['regionID']);
} else {
	
	$where = " WHERE ID = 1 OR hostdomain LIKE ".GetSQLValueString($domain, "text")." ORDER BY ID DESC";
}

mysql_select_db($database_aquiescedb, $aquiescedb);
$select = "SELECT * FROM region ".$where." LIMIT 1";
$result = mysql_query($select, $aquiescedb) or die(mysql_error());
$thisRegion = mysql_fetch_assoc($result);

if(isset($resetDomain) && isset($thisRegion['hostdomain']) && $thisRegion['hostdomain'] !=$domain && isset($_SESSION['MM_UserGroup'])) { 
// we have changed site so correct domain if available and copy over sessions
	$crossoversessions = "crossoversessions=true&".http_build_query($_SESSION);
	$crossoversessions .= "&token=".md5($_SESSION['MM_Username'].$_SESSION['MM_UserGroup'].PRIVATE_KEY);
	$url = $thisRegion['https'] == 1 ? "https://" : "http://";
	$url .= $thisRegion['www'] ==1 ? "www." : "";
	$url .= $thisRegion['hostdomain'].$_SERVER['PHP_SELF']."?".$crossoversessions;
	header("location: ".$url); exit;
}

$site_name = isset($thisRegion['title']) ? $thisRegion['title'] : $site_name;
$regionID = isset($thisRegion['ID']) ? $thisRegion['ID'] : 1;


if(isset($_SESSION['MM_Username'])) { // get user details
	
	$select = "SELECT ID, usertypeID, regionID FROM users WHERE username = ".GetSQLValueString($_SESSION['MM_Username'], "text")." LIMIT 1";
	$admin_result = mysql_query($select, $aquiescedb) or die(mysql_error());
	$adminUser = mysql_fetch_assoc($admin_result); 
}

if($adminUser['usertypeID']<9 && isset($adminUser['regionID']) && $adminUser['regionID'] != 0 && $adminUser['regionID'] != $regionID) {
	// OK but must switch region to users to continue access
	
	 $_SESSION['regionID'] = $adminUser['regionID'];
	 header("location: ".$_SERVER['REQUEST_URI']); exit;
	
}

// access only if user id Manager, set for all regions or has same region

if(!(isset($admin_result) && mysql_num_rows($admin_result)> 0 && ($adminUser['usertypeID']>=9 || $adminUser['regionID']==$regionID || $adminUser['regionID'] ==0))) {
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




mysql_select_db($database_aquiescedb, $aquiescedb);
$select = "SELECT ID, title FROM region WHERE statusID = 1";
$rsAdminRegions = mysql_query($select, $aquiescedb) or die(mysql_error());
$totalRegions = mysql_num_rows($rsAdminRegions); 


?>