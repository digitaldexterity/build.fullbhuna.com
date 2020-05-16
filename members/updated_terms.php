<?php require_once('../Connections/aquiescedb.php'); ?><?php

$regionID = isset($regionID ) ? intval($regionID)  : 1;



if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "";
$MM_donotCheckaccess = "true";

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
    if (($strUsers == "") && true) { 
      $isValid = true; 
    } 
  } 
  return $isValid; 
}

$MM_restrictGoTo = "../login/index.php?notloggedin=true";
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


$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}
if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
	$_POST['termsagreedate'] = date('Y-m-d H:i:s');
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE users SET termsagree=%s, termsagreedate=%s WHERE ID=%s",
                       GetSQLValueString($_POST['termsagree'], "int"),
                       GetSQLValueString($_POST['termsagreedate'], "date"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateGoTo =  isset($_GET['returnURL']) ? addslashes($_GET['returnURL']) : "index.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo));
}

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = (get_magic_quotes_gpc()) ? $_SESSION['MM_Username'] : addslashes($_SESSION['MM_Username']);
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID, termsagreedate FROM users WHERE username = '%s'", $colname_rsLoggedIn);
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT preferences.termsconditions, preferences.orgname, preferences.termsarticleID, article.body FROM preferences LEFT JOIN article ON (termsarticleID = article.ID)  WHERE preferences.ID = ".$regionID." LIMIT 1";
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);
?>
<?php $termsURL =(isset($row_rsPreferences['termsarticleID']) && $row_rsPreferences['termsarticleID']>0) ? "/articles/article.php?articleID=".intval($row_rsPreferences['termsarticleID']) : "";

$privacyURL =(isset($row_rsPreferences['privacyarticleID']) && $row_rsPreferences['privacyarticleID']>0) ? "/articles/article.php?articleID=".intval($row_rsPreferences['privacyarticleID']) : "";
?><?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php echo $site_name; ?>
<?php $pageTitle = "Members - Updated Terms"; echo $pageTitle." | ".$site_name; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" -->
           <div  class="container pageBody members"> <h2>Our Terms &amp; Conditions</h2>
            <h3>Please review and accept our latest terms and condtions to continue to use this web site.</h3>
           <?php require_once('../core/includes/alert.inc.php'); ?>
<form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1">
             
        <?php 
		$termsURL =(isset($row_rsPreferences['termsarticleID']) && $row_rsPreferences['termsarticleID']>0) ? "/articles/article.php?articleID=".intval($row_rsPreferences['termsarticleID']) : "";

$privacyURL =(isset($row_rsPreferences['privacyarticleID']) && $row_rsPreferences['privacyarticleID']>0) ? "/articles/article.php?articleID=".intval($row_rsPreferences['privacyarticleID']) : "";

?>     
             <p>View our <a href="<?php echo $termsURL; ?>" title="Click to view our web site use terms and conditions." target="_blank" rel="noopener"  >Terms and conditions</a> and <a href="<?php echo $privacyURL; ?>" title="Click to view our privacy policy." target="_blank" rel="noopener">Privacy Policy</a>.</p>
             
              <p>
                <button type="submit" >I Agree</button>
                <button  type="button"  onclick="if(confirm('Please confirm you do not agree with our terms of use and will longer be able to log in.')) { window.location.href = '/'; }">I Disagree</button>
                <input name="termsagree" type="hidden" id="termsagree" value="1" />
                <input name="gdpr_date" type="hidden"  value="<?php echo date('Y-m-d H:i:s'); ?>" />
                <input name="termsagreedate" type="hidden" id="termsagreedate" value="<?php echo $row_rsLoggedIn['termsagreedate']; ?>" />
                <input name="ID" type="hidden" id="ID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
              </p>
                <input type="hidden" name="MM_update" value="form1" />
</form></div>
    <!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsPreferences);
?>