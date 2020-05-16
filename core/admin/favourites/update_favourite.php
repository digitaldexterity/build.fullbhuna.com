<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php require_once('../../includes/adminAccess.inc.php'); ?>
<?php

$regionID = isset($regionID) ? intval($regionID ): 1;

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

$MM_restrictGoTo = "/login/index.php?notloggedin=true";
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

if(isset($_POST['allusers'])) { $_POST['userID'] = 0; }

$_POST['regionID'] = isset($_POST['allsites']) ? 0 : $_POST['regionID'];

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE favourites SET url=%s, pagetitle=%s, userID=%s, modifiedbyID=%s, modifieddatetime=%s, statusID=%s, regionID=%s, newwindow=%s WHERE ID=%s",
                       GetSQLValueString($_POST['url'], "text"),
                       GetSQLValueString($_POST['pagetitle'], "text"),
                       GetSQLValueString($_POST['userID'], "int"),
                       GetSQLValueString($_POST['modifiedbyID'], "int"),
                       GetSQLValueString($_POST['modifieddatetime'], "date"),
                       GetSQLValueString(isset($_POST['statusID']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['regionID'], "int"),
                       GetSQLValueString(isset($_POST['newwindow']) ? "true" : "", "defined","1","0"),
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
$query_rsLoggedIn = sprintf("SELECT ID, users.usertypeID, users.firstname, users.surname, users.changepassword, users.lastlogin FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

$colname_rsFavourite = "-1";
if (isset($_GET['favouriteID'])) {
  $colname_rsFavourite = $_GET['favouriteID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsFavourite = sprintf("SELECT * FROM favourites WHERE ID = %s", GetSQLValueString($colname_rsFavourite, "int"));
$rsFavourite = mysql_query($query_rsFavourite, $aquiescedb) or die(mysql_error());
$row_rsFavourite = mysql_fetch_assoc($rsFavourite);
$totalRows_rsFavourite = mysql_num_rows($rsFavourite);
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Update Favourite"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../seo/includes/seo.inc.php'); ?>
<?php require_once('../../includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script src="../../../SpryAssets/SpryValidationTextField.js"></script>
<link href="../../../SpryAssets/SpryValidationTextField.css" rel="stylesheet"  />
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" -->
    <div class="page favourites"><h1><i class="glyphicon glyphicon-heart"></i> Update Favourite</h1>
    
    <form action="<?php echo $editFormAction; ?>" method="POST" name="form1" id="form1">
      <table class="form-table"> <tr>
          <td class="text-nowrap text-right">Page name:</td>
          <td><span id="sprytextfield1">
            <input name="pagetitle" type="text" value="<?php echo $row_rsFavourite['pagetitle']; ?>" size="50" maxlength="100"  class="form-control"/>
          <span class="textfieldRequiredMsg">A page name is required.</span></span></td>
        </tr><tr>
          <td class="text-nowrap text-right">Page link:</td>
          <td><span id="sprytextfield2">
          <input name="url" type="text" value="<?php echo $row_rsFavourite['url']; ?>" size="50" maxlength="255"  class="form-control"/>
          <span class="textfieldRequiredMsg">A link is required.</span></span></td>
        </tr> <tr>
          <td class="text-nowrap text-right">Add to all users' favourites:</td>
          <td><input <?php if (!(strcmp($row_rsFavourite['userID'],0))) {echo "checked=\"checked\"";} ?> name="allusers" type="checkbox" id="allusers" value="1" /> &nbsp;&nbsp;&nbsp; <label>Add to all sites: 
              <input type="checkbox" name="allsites" id="allsites" <?php if ($row_rsFavourite['regionID']==0) {echo "checked=\"checked\"";} ?>  /></label></td>
        </tr> <tr>
          <td class="text-nowrap text-right">Display:</td>
          <td><input <?php if (!(strcmp($row_rsFavourite['statusID'],1))) {echo "checked=\"checked\"";} ?> name="statusID" type="checkbox" id="statusID" value="1" /> &nbsp;&nbsp;&nbsp; <label>Open in new window: 
              <input <?php if (!(strcmp($row_rsFavourite['newwindow'],1))) {echo "checked=\"checked\"";} ?> type="checkbox" name="newwindow" id="newwindow" value="1" /></label></td>
        </tr> <tr>
          <td class="text-nowrap text-right">&nbsp;</td>
          <td><button type="submit" class="btn btn-primary">Save changes...</button></td>
        </tr>
      </table>
      <input type="hidden" name="userID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
      <input type="hidden" name="modifiedbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
      <input type="hidden" name="modifieddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>"  />
      <input name="ID" type="hidden" id="ID" value="<?php echo $row_rsFavourite['ID']; ?>" />
      <input name="regionID" type="hidden" value="<?php echo $regionID; ?>">
      <input type="hidden" name="MM_update" value="form1" />
    </form>
    <p>&nbsp;</p>
<p>&nbsp;</p>
    <script>
<!--
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1", "none", {hint:"e.g. http://www.google.com/"});
var sprytextfield2 = new Spry.Widget.ValidationTextField("sprytextfield2", "none", {hint:"e.g. Google home page"});
//-->
    </script></div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsFavourite);
?>
