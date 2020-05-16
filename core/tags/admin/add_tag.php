<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php require_once('../../includes/adminAccess.inc.php'); ?>
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

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
  $insertSQL = sprintf("INSERT INTO tag (tagname, taggroupID, ordernum, regionID, createdbyID, createddatetime) VALUES (%s, %s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['tagname'], "text"),
                       GetSQLValueString($_POST['taggroupID'], "int"),
                       GetSQLValueString($_POST['ordernum'], "int"),
                       GetSQLValueString($_POST['regionID'], "int"),
                       GetSQLValueString($_POST['createdbyID'], "int"),
                       GetSQLValueString($_POST['createddatetime'], "date"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($insertSQL, $aquiescedb) or die(mysql_error());

  $insertGoTo = "index.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
    $insertGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $insertGoTo));
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

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsTagGroups = "SELECT ID, taggroupname FROM taggroup ORDER BY ordernum ASC";
$rsTagGroups = mysql_query($query_rsTagGroups, $aquiescedb) or die(mysql_error());
$row_rsTagGroups = mysql_fetch_assoc($rsTagGroups);
$totalRows_rsTagGroups = mysql_num_rows($rsTagGroups);
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Add Tag"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../seo/includes/seo.inc.php'); ?>
<?php require_once('../../includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<style>
<!--
-->
</style>
<link href="../../../SpryAssets/SpryValidationTextField.css" rel="stylesheet" type="text/css">
<script src="../../../SpryAssets/SpryValidationTextField.js" type="text/javascript"></script>
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
    <div class="page class">
      <h1>Add Tag</h1>
     
      <form method="post" name="form1" action="<?php echo $editFormAction; ?>">
        <table class="form-table" ><tr class="<?php if(mysql_num_rows($rsTagGroups)==0) { echo " hide-form-item "; } ?>">
            <td class="text-nowrap text-right top">Tag group:</td>
            <td class="form-inline"><select name="taggroupID" class="form-control">
              <option value="">Choose...</option>
              <?php
do {  
?>
              <option value="<?php echo $row_rsTagGroups['ID']?>"><?php echo $row_rsTagGroups['taggroupname']?></option>
              <?php
} while ($row_rsTagGroups = mysql_fetch_assoc($rsTagGroups));
  $rows = mysql_num_rows($rsTagGroups);
  if($rows > 0) {
      mysql_data_seek($rsTagGroups, 0);
	  $row_rsTagGroups = mysql_fetch_assoc($rsTagGroups);
  }
?>
            </select>   <a href="groups/index.php">Manage Tag Groups</a></td>
          </tr>
          <tr>
            <td class="text-nowrap text-right top">Tag name:</td>
            <td><span id="sprytextfield1">
              <input name="tagname" type="text" value="" size="50" maxlength="50" class="form-control">
            <span class="textfieldRequiredMsg">A value is required.</span></span></td>
          </tr>
          
          <tr>
            <td class="text-nowrap text-right top">&nbsp;</td>
            <td><button type="submit" class="btn btn-primary" >Add Tag</button></td>
          </tr>
        </table>
        <input type="hidden" name="ordernum" value="0">
        <input type="hidden" name="regionID" value="$regionID">
        <input type="hidden" name="createdbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>">
        <input type="hidden" name="createddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>">
        <input type="hidden" name="MM_insert" value="form1">
      </form>
      <p>&nbsp;</p>
<p>&nbsp;</p>
    </div>
    <script type="text/javascript">
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1");
    </script>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsTagGroups);
?>
