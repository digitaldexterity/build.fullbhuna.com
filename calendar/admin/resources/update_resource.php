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
  $updateSQL = sprintf("UPDATE eventresource SET resourcename=%s, `description`=%s, statusID=%s, modifiedbyID=%s, modifieddatetime=%s, categoryID=%s WHERE ID=%s",
                       GetSQLValueString($_POST['resourcename'], "text"),
                       GetSQLValueString($_POST['description'], "text"),
                       GetSQLValueString(isset($_POST['statusID']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['modifiedbyID'], "int"),
                       GetSQLValueString($_POST['modifieddatetime'], "date"),
                       GetSQLValueString($_POST['categoryID'], "int"),
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

$colname_rsResouce = "-1";
if (isset($_GET['resourceID'])) {
  $colname_rsResouce = $_GET['resourceID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsResouce = sprintf("SELECT * FROM eventresource WHERE ID = %s", GetSQLValueString($colname_rsResouce, "int"));
$rsResouce = mysql_query($query_rsResouce, $aquiescedb) or die(mysql_error());
$row_rsResouce = mysql_fetch_assoc($rsResouce);
$totalRows_rsResouce = mysql_num_rows($rsResouce);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsCategories = "SELECT * FROM eventcategory WHERE active = 1 ORDER BY title ASC";
$rsCategories = mysql_query($query_rsCategories, $aquiescedb) or die(mysql_error());
$row_rsCategories = mysql_fetch_assoc($rsCategories);
$totalRows_rsCategories = mysql_num_rows($rsCategories);
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Update Resource"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
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
    <!-- InstanceBeginEditable name="Body" --><div class="page calendar">
          <h1><i class="glyphicon glyphicon-calendar"></i> Update Resource</h1>
        
          <form action="<?php echo $editFormAction; ?>" method="POST" name="form1" role="form">
            <table class="form-table" >
              <tr>
                <td class="text-nowrap text-right top">Resource name:</td>
                <td><span id="sprytextfield1">
                  <input name="resourcename" type="text" value="<?php echo $row_rsResouce['resourcename']; ?>" size="50" maxlength="50">
                <span class="textfieldRequiredMsg">A value is required.</span></span></td>
              </tr>
              <tr>
                <td class="text-nowrap text-right top">Description:</td>
                <td><textarea name="description" cols="50" rows="5"><?php echo $row_rsResouce['description']; ?></textarea></td>
              </tr>
              <tr>
                <td class="text-nowrap text-right top"><label for="categoryID">Restrict to category:</label></td>
                <td><select name="categoryID" id="categoryID">
                  <option value="" <?php if (!(strcmp("", $row_rsResouce['categoryID']))) {echo "selected=\"selected\"";} ?>>Choose (optional)...</option>
                  <?php
do {  
?>
                  <option value="<?php echo $row_rsCategories['ID']?>"<?php if (!(strcmp($row_rsCategories['ID'], $row_rsResouce['categoryID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsCategories['title']?></option>
                  <?php
} while ($row_rsCategories = mysql_fetch_assoc($rsCategories));
  $rows = mysql_num_rows($rsCategories);
  if($rows > 0) {
      mysql_data_seek($rsCategories, 0);
	  $row_rsCategories = mysql_fetch_assoc($rsCategories);
  }
?>
                </select></td>
              </tr>
              <tr>
                <td class="text-nowrap text-right top">Active</td>
                <td><input <?php if (!(strcmp($row_rsResouce['statusID'],1))) {echo "checked=\"checked\"";} ?> name="statusID" type="checkbox" id="statusID" value="1"></td>
              </tr>
              <tr>
                <td class="text-nowrap text-right top">&nbsp;</td>
                <td><button type="submit" class="btn btn-primary">Save changes</button></td>
              </tr>
            </table>
            <input type="hidden" name="modifiedbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>">
            <input type="hidden" name="modifieddatetime" value="<?php echo date("Y-m-d H:i:s"); ?>">
            <input name="ID" type="hidden" id="ID" value="<?php echo $row_rsResouce['ID']; ?>">
            <input type="hidden" name="MM_update" value="form1">
          </form>
        
        </div>
        <script>
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1");
        </script>
      <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsResouce);

mysql_free_result($rsCategories);
?>
