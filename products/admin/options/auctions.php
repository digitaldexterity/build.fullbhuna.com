<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php require_once('../../../core/includes/adminAccess.inc.php'); ?>
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
  $updateSQL = sprintf("UPDATE productprefs SET auctions=%s, auctiondays=%s, auctionhours=%s, auctionminbid=%s, auctionminincrement=%s WHERE ID=%s",
                       GetSQLValueString(isset($_POST['auctions']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['auctiondays'], "int"),
                       GetSQLValueString($_POST['auctionhours'], "int"),
                       GetSQLValueString($_POST['auctionminbid'], "double"),
                       GetSQLValueString($_POST['auctionminincrement'], "double"),
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

$varRegionID_rsProductPrefs = "1";
if (isset($regionID)) {
  $varRegionID_rsProductPrefs = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsProductPrefs = sprintf("SELECT * FROM productprefs WHERE ID = %s", GetSQLValueString($varRegionID_rsProductPrefs, "int"));
$rsProductPrefs = mysql_query($query_rsProductPrefs, $aquiescedb) or die(mysql_error());
$row_rsProductPrefs = mysql_fetch_assoc($rsProductPrefs);
$totalRows_rsProductPrefs = mysql_num_rows($rsProductPrefs);
?><?php if(isset($body_class)) $body_class .= " products ";  ?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = ""; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<style>
<!--
-->
</style>
<link href="../../../SpryAssets/SpryValidationTextField.css" rel="stylesheet" >
<script src="../../../SpryAssets/SpryValidationTextField.js"></script>
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" --><h1><i class="glyphicon glyphicon-shopping-cart"></i> Manage Auctions</h1>
        <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
          <li><a href="../index.php" class="link_undo"><i class="glyphicon glyphicon-arrow-left"></i> Back</a></li></ul></div></nav>
        <form action="<?php echo $editFormAction; ?>" method="POST" enctype="multipart/form-data" name="form1" class="form-inline">
          <p>
            <label>
              <input <?php if (!(strcmp($row_rsProductPrefs['auctions'],1))) {echo "checked=\"checked\"";} ?> name="auctions" type="checkbox" id="auctions" value="1">
              Auctions active - allow customers to bid on items in shop</label>
          </p>
        
          <table class="form-table">
            <tr>
              <td class="text-right"><label for="auctiondays">Default auction length:</label></td>
              <td>
                <input name="auctiondays" type="text" id="auctiondays" value="<?php echo $row_rsProductPrefs['auctiondays']; ?>" size="3" maxlength="3" class="form-control">
                days 
               
                  <input name="auctionhours" type="text" id="auctionhours" value="<?php echo $row_rsProductPrefs['auctionhours']; ?>" size="3" maxlength="3" class="form-control">
              hours</td>
            </tr>
            <tr>
              <td class="text-right"><label for="auctionminbid">Default minimum bid:</label></td>
              <td><span id="sprytextfield1">
              <input name="auctionminbid" type="text" id="auctionminbid" value="<?php echo number_format($row_rsProductPrefs['auctionminbid'],2); ?>" size="8" maxlength="8" class="form-control">
              <span class="textfieldRequiredMsg">A value is required.</span><span class="textfieldInvalidFormatMsg">Invalid format.</span></span></td>
            </tr>
            <tr>
              <td class="text-right"><label for="auctionminincrement">Minimum bid increment:</label></td>
              <td><span id="sprytextfield2">
              <input name="auctionminincrement" type="text" id="auctionminincrement" value="<?php echo number_format($row_rsProductPrefs['auctionminincrement'],2); ?>" size="8" maxlength="8" class="form-control">
              <span class="textfieldRequiredMsg">A value is required.</span><span class="textfieldInvalidFormatMsg">Invalid format.</span></span></td>
            </tr>
            <tr>
              <td><input name="ID" type="hidden" id="ID" value="<?php echo $row_rsProductPrefs['ID']; ?>">
              <input type="hidden" name="MM_update" value="form1"></td>
              <td><button type="submit" name="button" id="button" class="btn btn-primary" >Save changes...</button></td>
            </tr>
          </table>
       
        </form>
       
        <script>
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1", "currency");
var sprytextfield2 = new Spry.Widget.ValidationTextField("sprytextfield2", "currency");
        </script>
      <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsProductPrefs);
?>
