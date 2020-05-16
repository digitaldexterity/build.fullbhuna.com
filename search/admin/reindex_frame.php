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
require_once("$isearch_path/inc/spider.inc.php");

/* Display the frame set */

$reset = isset($_REQUEST['reset']) ? $_REQUEST['reset'] : 'true';

// replacing framset for html5
header("location: reindex.php?reset=".$reset);
/*

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>iSearch Spider</title>
<meta http-equiv="Content-Type" content="text/html; charset=' . $isearch_config['char_set'] . '">
<meta http-equiv="Content-Language" content="' . $isearch_languageCode . '">
<meta name="robots" content="noindex,nofollow">
<link rel=StyleSheet href="admin.css" >
</head>
<frameset cols="100%" rows="110,*" FRAMEBORDER="YES" FRAMESPACING="2" BORDER="2">
<frame frameborder="0" src="reindex_menu.php" name="isearch_reindex_menu">
<frame frameborder="0" src="reindex.php?reset='.$reset.'" name="isearch_reindex">
</frameset>

<script>
<!--
self.focus();
// -->
</script>

<noframes>
<body>
';*/

include("$isearch_path/inc/header.inc.php");

echo "<h3>Sorry - your browser does not support frames. Please <a href=\"reindex.php?reset=".$reset."\">click here</a></h3>

</body>
</noframes>
</html>";

?>