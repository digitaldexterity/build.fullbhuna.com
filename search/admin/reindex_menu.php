<?php require_once('../../Connections/aquiescedb.php'); ?>
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

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html lang="en">
<head>
<title>iSearch Reindex Site</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Content-Language" content="EN-GB">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="Fri, 01 Jan 1999 00:00:01 GMT">
<meta name="author" content="Ian Willis">
<meta name="copyright" content="Copyright Ian Willis. All rights reserved.">
<meta name="robots" content="noindex,nofollow">
<LINK REL=StyleSheet href="admin.css" >
</head>

<body BGCOLOR="#ffffc0" TEXT="#000000">

<h1>iSearch Reindex Site</h1>

<?php

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

if ($action == 'copy')
{
    if (isearch_getUrlCount(True, 'ok') > 0)
    {
        ?>
<p>Copying tables - please wait...</p>



<?php echo"</body>";
        echo str_pad(' ', 1024);

        @ini_set('max_execution_time', 0);
        isearch_copyUrlTables();
        $delay = 1;
    }
    else
    {
        echo "<p>No results available to copy.</p>";
        $delay = 3;
    }

  echo '<head><meta http-equiv="refresh" content="'.$delay.'; URL='.$_SERVER["PHP_SELF"] .'"></head>';



}
else
{
   ?>
<script>
<!--
function pauseClicked()
{
    if (isFinished())
    {
        alert("Spidering has already finished.");
    }
    else
    {
        document.form1.pause.value='true';
        document.form1.state.value='pausing - please wait';
    }
}

function isFinished()
{
    if (document.form1.finished.value=='true')
    {
        return true;
    }
    return false;
}

function copyClicked()
{
    if (isFinished())
    {
        alert("Spidering has already finished. The spidered pages have already been copied to the search tables.");
    }
    else
    {
        if (confirm("This will copy the pages that the spider has already spidered into the search tables so that you can search them immediately. If the spider has not yet spidered a page it will not be found when searching.\\n\\nAre you sure?"))
        {
            window.location='<?php echo $_SERVER['PHP_SELF']; ?>?action=copy';
        }
    }
}

function resetClicked()
{
    if (isFinished())
    {
        if (confirm("This will start the spider engine again.\\n\\nAre you sure?"))
        {
            document.form1.state.value='starting';
            parent.isearch_reindex.window.location='reindex.php?reset=true';
        }
    }
    else
    {
        if (confirm("This will reset the spider engine.\\n\\nAre you sure?"))
        {
            document.form1.state.value='starting';
            parent.isearch_reindex.window.location='reindex.php?reset=true';
        }
    }
}


// -->
</script>

<table class="form-table">
<tr>
<td>
 <FORM name="form1">
 <INPUT NAME="pause" TYPE="hidden">
 <INPUT NAME="finished" TYPE="hidden">
 State: <INPUT NAME="state" SIZE="40" VALUE="starting">
 </FORM>
</td>

<td>&nbsp;&nbsp;<A href="$_SERVER['PHP_SELF']?action=pause" onClick="pauseClicked(); return false;" onMouseOver="window.status='Pause the Spider Engine';return true;" onMouseOut="window.status=' ';return true;">Pause</A></td>
<td>&nbsp;&nbsp;<A href="reindex.php?reset=true" TARGET="isearch_reindex" onClick="resetClicked(); return false;" onMouseOver="window.status='Reset the Spider Engine';return true;" onMouseOut="window.status=' ';return true;">Reset</A></td>
<td>&nbsp;&nbsp;<A href="$_SERVER['PHP_SELF']?action=copy" onClick="copyClicked(); return false;" onMouseOver="window.status='Copy the current Spidered Pages to the Search Index';return true;" onMouseOut="window.status=' ';return true;">Copy</A></td>
<td>&nbsp;&nbsp;<A href="log.php" TARGET="isearch_log" onMouseOver="window.status='View the Spider Log';return true;" onMouseOut="window.status=' ';return true;">View Log</A></td>

<noscript>
<p>Please enable JavaScript before using the &quot;Pause&quot; feature.
</noscript>

</tr>
</table>
</body>
<?php } ?></html>
