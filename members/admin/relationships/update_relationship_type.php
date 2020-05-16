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
  $updateSQL = sprintf("UPDATE userrelationshiptype SET relationshiptype=%s, modifiedbyID=%s, modifieddatetime=%s, statusID=%s, accessID=%s WHERE ID=%s",
                       GetSQLValueString($_POST['relationshiptype'], "text"),
                       GetSQLValueString($_POST['modifiedbyID'], "int"),
                       GetSQLValueString($_POST['modifieddatetime'], "date"),
                       GetSQLValueString(isset($_POST['statusID']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['accessID'], "int"),
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

$colname_rsRelationshipType = "-1";
if (isset($_GET['relationshiptypeID'])) {
  $colname_rsRelationshipType = $_GET['relationshiptypeID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsRelationshipType = sprintf("SELECT * FROM userrelationshiptype WHERE ID = %s", GetSQLValueString($colname_rsRelationshipType, "int"));
$rsRelationshipType = mysql_query($query_rsRelationshipType, $aquiescedb) or die(mysql_error());
$row_rsRelationshipType = mysql_fetch_assoc($rsRelationshipType);
$totalRows_rsRelationshipType = mysql_num_rows($rsRelationshipType);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsUserType = "SELECT * FROM usertype WHERE ID >= 1 ORDER BY ID ASC";
$rsUserType = mysql_query($query_rsUserType, $aquiescedb) or die(mysql_error());
$row_rsUserType = mysql_fetch_assoc($rsUserType);
$totalRows_rsUserType = mysql_num_rows($rsUserType);
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Update Relationship Type"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
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
    <!-- InstanceBeginEditable name="Body" -->
        <h1><i class="glyphicon glyphicon-user"></i> Update Relationship Type</h1>
      
        <form action="<?php echo $editFormAction; ?>" method="POST" name="form1">
          <table class="form-table" > <tr>
              <td class="text-nowrap text-right top">Relationship type:</td>
              <td><span id="sprytextfield1">
                <input name="relationshiptype" type="text" value="<?php echo $row_rsRelationshipType['relationshiptype']; ?>" size="50" maxlength="100" class="form-control">
              <span class="textfieldRequiredMsg">A value is required.</span></span></td>
            </tr> <tr>
              <td class="text-nowrap text-right top"><label for="accessID">Can create:</label></td>
              <td><select name="accessID" id="accessID" class="form-control">
                <option value="0" <?php if (!(strcmp(0, $row_rsRelationshipType['accessID']))) {echo "selected=\"selected\"";} ?>>Everyone</option>
                <?php
do {  
?>
                <option value="<?php echo $row_rsUserType['ID']?>"<?php if (!(strcmp($row_rsUserType['ID'], $row_rsRelationshipType['accessID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsUserType['name']?></option>
                <?php
} while ($row_rsUserType = mysql_fetch_assoc($rsUserType));
  $rows = mysql_num_rows($rsUserType);
  if($rows > 0) {
      mysql_data_seek($rsUserType, 0);
	  $row_rsUserType = mysql_fetch_assoc($rsUserType);
  }
?>
              </select></td>
            </tr> <tr>
              <td class="text-nowrap text-right top"><label for="statusID">Active:</label></td>
              <td><input <?php if (!(strcmp($row_rsRelationshipType['statusID'],1))) {echo "checked=\"checked\"";} ?> name="statusID" type="checkbox" id="statusID" value="1">
              </td>
            </tr> <tr>
              <td class="text-nowrap text-right top">&nbsp;</td>
              <td><button type="submit" class="btn btn-primary" >Save changes</button></td>
            </tr>
          </table>
          <input type="hidden" name="modifiedbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>">
          
          <input type="hidden" name="modifieddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" >
          <input name="ID" type="hidden" id="ID" value="<?php echo $row_rsRelationshipType['ID']; ?>">
          <input type="hidden" name="MM_update" value="form1">
        </form>
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

mysql_free_result($rsRelationshipType);

mysql_free_result($rsUserType);
?>
