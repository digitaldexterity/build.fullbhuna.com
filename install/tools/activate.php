<?php require_once('../../Connections/aquiescedb.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "1,2,3,4,5,6,7,8,9,10";
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

$MM_restrictGoTo = "/login/logout.php";
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
  $updateSQL = sprintf("UPDATE preferences SET license_key=%s WHERE ID=%s",
                       GetSQLValueString($_POST['license_key'], "text"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());

  $updateGoTo = isset($_GET['firstrun']) ? "setup.php" : "/core/admin/";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo));
}

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT preferences.license_key FROM preferences";
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);
?><!DOCTYPE html>
<html lang="en" class="full_bhuna install <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Install.dwt.php" codeOutsideHTMLIsLocked="false" --><!-- Copyright Paul Egan. Any unauthorised copying, reproduction or alteration is strictly prohibited -->
<head>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Install Full Bhuna</title>
<!-- InstanceEndEditable -->
<?php require_once('../includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<!-- InstanceEndEditable -->
</head>
<body>
<?php require_once('../includes/header.inc.php'); ?>
<main>
<div class="container"><!-- InstanceBeginEditable name="Body" -->
<table class="form-table" >
  <tr>
    <td><h1>Activation</h1>
      <p>This Full Bhuna installation needs activation.</p>
      <ul>
        <li>Please enter your licence key below:</li>
      </ul>
      <?php if(isset($submit_error)) { ?><p class="alert alert-danger" role="alert"><?php echo $submit_error; ?></p><?php } ?>
<form action="<?php echo $editFormAction; ?>" method="post" id="form1" class="form-inline">
 
  <input name="license_key" type="text"  id="license_key" value="<?php echo isset($row_rsPreferences['license_key']) ? $row_rsPreferences['license_key'] : ""; ?>" size="50" maxlength="50"  placeholder="Please enter key here..." class="form-control"/>
 
  <button type="submit" class="btn btn-primary" >Submit</button>
        <input name="ID" type="hidden" id="ID" value="1" />
        <input type="hidden" name="MM_update" value="form1" />
      </form>
    <p> If you have not been given an activation key, <a href="http://activate.fullbhuna.com/" target="_blank" rel="noopener">get one here</a>.</p>
    <p>IMPORTANT: Each activation key only works on a specified domain, so if you need to move your site to a new domain or upload to another domain, you will need to obtain a new activation key.</p></td>
  </tr>
</table>

<!-- InstanceEndEditable --></div>
</main>
<?php require_once('../includes/footer.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsPreferences);
?>
