<?php require_once('../../../../Connections/aquiescedb.php'); ?>
<?php require_once('../../../../core/includes/adminAccess.inc.php'); ?>
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

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE disability SET disabilityname=%s, statusID=%s WHERE ID=%s",
                       GetSQLValueString($_POST['disabilityname'], "text"),
                       GetSQLValueString(isset($_POST['statusID']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateGoTo = "index.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo)); exit;
}

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

$colname_rsDisability = "-1";
if (isset($_GET['disabilityID'])) {
  $colname_rsDisability = $_GET['disabilityID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsDisability = sprintf("SELECT * FROM disability WHERE ID = %s", GetSQLValueString($colname_rsDisability, "int"));
$rsDisability = mysql_query($query_rsDisability, $aquiescedb) or die(mysql_error());
$row_rsDisability = mysql_fetch_assoc($rsDisability);
$totalRows_rsDisability = mysql_num_rows($rsDisability);
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Update Disability"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<style>
<!--
-->
</style>
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" --><div class="page class">
          <h1><i class="glyphicon glyphicon-user"></i> Update Disability</h1>
       
          <form method="post" name="form1" action="<?php echo $editFormAction; ?>">
            <table class="form-table">
              <tr>
                <td class="text-nowrap text-right top">Disability name:</td>
                <td><input type="text" name="disabilityname" value="<?php echo htmlentities($row_rsDisability['disabilityname'], ENT_COMPAT, 'UTF-8'); ?>"  class="form-control"></td>
              </tr>
              <tr>
                <td class="text-nowrap text-right top">Active:</td>
                <td><input type="checkbox" name="statusID"  <?php if (!(strcmp(htmlentities($row_rsDisability['statusID'], ENT_COMPAT, 'UTF-8'),"1"))) {echo "checked=\"checked\"";} ?>></td>
              </tr>
              <tr>
                <td class="text-nowrap text-right top">&nbsp;</td>
                <td><button type="submit" class="btn btn-primary" >Save changes</button></td>
              </tr>
            </table>
            <input type="hidden" name="MM_update" value="form1">
            <input type="hidden" name="ID" value="<?php echo $row_rsDisability['ID']; ?>">
          </form>
          <p>&nbsp;</p>
<p>&nbsp;</p>
      </div><!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsDisability);
?>
