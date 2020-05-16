<?php require_once('../../Connections/aquiescedb.php'); ?><?php require_once('../../core/includes/adminAccess.inc.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "9,10";
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

$MM_restrictGoTo = "../../login/index.php?notloggedin=true";
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

/******************************************************************************
 * iSearch2 - website search engine                                           *
 *                                                                            *
 * Visit the iSearch homepage at http://www.iSearchTheNet.com/isearch         *
 *                                                                            *
 * Copyright (C) 2002-2005 Ian Willis. All rights reserved.                   *
 *                                                                            *
 ******************************************************************************/

// PHPLOCKITOPT NOENCODE

$isearch_path = '..';
define('IN_ISEARCH', true);

require_once("$isearch_path/inc/core.inc.php");
isearch_open();
require_once("$isearch_path/inc/admin_auth.inc.php");
require_once("$isearch_path/inc/search.inc.php");

echo <<<EOF
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html lang="en">
<head>
<title>iSearch Admin Log</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Content-Language" content="EN-GB">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="Fri, 01 Jan 1999 00:00:01 GMT">
<meta name="author" content="Ian Willis">
<meta name="copyright" content="Copyright Ian Willis. All rights reserved.">
<meta name="robots" content="noindex,nofollow">
<LINK REL=StyleSheet href="admin.css" >
</head>

<body BGCOLOR="#ffffc0" TEXT="#000000" onLoad="window.resizeTo(600, 600);self.focus();">

<H1>iSearch Admin Log</H1>

EOF;

echo "<PRE>\n";
echo isearch_getAdminLog();
echo "</PRE>\n";

isearch_close();

echo "</body>\n";

echo "</html>\n";

?>
