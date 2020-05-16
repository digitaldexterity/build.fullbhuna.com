<?php require_once('../../../../Connections/aquiescedb.php'); ?><?php require_once('../../../includes/adminAccess.inc.php'); ?>
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

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE countries SET cc1=%s, fullname=%s, iso2=%s, iso3=%s, num_code=%s, nationality=%s, tld=%s, regionID=%s WHERE ID=%s",
                       GetSQLValueString($_POST['cc1'], "text"),
                       GetSQLValueString($_POST['fullname'], "text"),
                       GetSQLValueString($_POST['iso2'], "text"),
                       GetSQLValueString($_POST['iso3'], "text"),
                       GetSQLValueString($_POST['num_code'], "int"),
                       GetSQLValueString($_POST['nationality'], "text"),
                       GetSQLValueString($_POST['tld'], "text"),
                       GetSQLValueString($_POST['regionID'], "int"),
                       GetSQLValueString($_POST['country_id'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());

  $updateGoTo = "index.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo));
}

$colname_rsCountry = "-1";
if (isset($_GET['countryID'])) {
  $colname_rsCountry = $_GET['countryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsCountry = sprintf("SELECT * FROM countries WHERE ID = %s", GetSQLValueString($colname_rsCountry, "int"));
$rsCountry = mysql_query($query_rsCountry, $aquiescedb) or die(mysql_error());
$row_rsCountry = mysql_fetch_assoc($rsCountry);
$totalRows_rsCountry = mysql_num_rows($rsCountry);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsRegions = "SELECT ID, title FROM region";
$rsRegions = mysql_query($query_rsRegions, $aquiescedb) or die(mysql_error());
$row_rsRegions = mysql_fetch_assoc($rsRegions);
$totalRows_rsRegions = mysql_num_rows($rsRegions);
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Update Country"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../../seo/includes/seo.inc.php'); ?>
<?php require_once('../../../includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script src="../../../../SpryAssets/SpryValidationTextField.js"></script>
<link href="../../../../SpryAssets/SpryValidationTextField.css" rel="stylesheet"  />
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../../includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" -->
    <div class="page regions"><h1><i class="glyphicon glyphicon-globe"></i> Update Country</h1>
    
    <form action="<?php echo $editFormAction; ?>" method="POST" name="form1" id="form1" class="form-inline">
      <table class="form-table"> <tr>
          <td class="text-nowrap text-right">Full name:</td>
          <td><span id="sprytextfield1">
            <input name="fullname" type="text"  value="<?php echo $row_rsCountry['fullname']; ?>" size="50" maxlength="50"  class="form-control"/>
          <span class="textfieldRequiredMsg">A value is required.</span></span></td>
        </tr> 
        <tr>
          <td class="text-nowrap text-right">Nationality:</td>
          <td><input name="nationality" type="text"  value="<?php echo htmlentities($row_rsCountry['nationality'], ENT_COMPAT, "UTF-8"); ?>" size="50" maxlength="50"  class="form-control"/></td>
        </tr>
        <tr>
          <td class="text-nowrap text-right">Numeric code:</td>
          <td><input name="num_code" type="text"  value="<?php echo $row_rsCountry['num_code']; ?>" size="5" maxlength="3"  class="form-control"/></td>
        </tr>
        <tr>
          <td class="text-nowrap text-right">Iso2:</td>
          <td><input name="iso2" type="text"  value="<?php echo $row_rsCountry['iso2']; ?>" size="5" maxlength="2"  class="form-control"/></td>
        </tr> <tr>
          <td class="text-nowrap text-right">Iso3:</td>
          <td><input name="iso3" type="text"  value="<?php echo $row_rsCountry['iso3']; ?>" size="5" maxlength="3"  class="form-control"/></td>
        </tr> <tr>
          <td class="text-nowrap text-right">Cc1:</td>
          <td><input name="cc1" type="text"  value="<?php echo $row_rsCountry['cc1']; ?>" size="5" maxlength="3"  class="form-control"/></td>
        </tr> <tr>
          <td class="text-nowrap text-right">Tld:</td>
          <td><input name="tld" type="text"  value="<?php echo $row_rsCountry['tld']; ?>" size="5" maxlength="2"  class="form-control"/></td>
        </tr> 
        <tr>
          <td class="text-nowrap text-right"><label for="regionID">Site:</label></td>
          <td>
            <select name="regionID" id="regionID"  class="form-control">
              <option value="" <?php if (!(strcmp("", $row_rsCountry['regionID']))) {echo "selected=\"selected\"";} ?>>Choose...</option>
              <option value="0" <?php if (!(strcmp(0, $row_rsCountry['regionID']))) {echo "selected=\"selected\"";} ?>>All sites</option>
              <?php
do {  
?>
              <option value="<?php echo $row_rsRegions['ID']?>"<?php if (!(strcmp($row_rsRegions['ID'], $row_rsCountry['regionID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsRegions['title']?></option>
              <?php
} while ($row_rsRegions = mysql_fetch_assoc($rsRegions));
  $rows = mysql_num_rows($rsRegions);
  if($rows > 0) {
      mysql_data_seek($rsRegions, 0);
	  $row_rsRegions = mysql_fetch_assoc($rsRegions);
  }
?>
            </select></td>
        </tr>
        <tr>
          <td class="text-nowrap text-right"><input name="country_id" type="hidden" id="country_id" value="<?php echo $row_rsCountry['ID']; ?>"></td>
          <td><button type="submit" class="btn btn-primary" >Save changes</button></td>
        </tr>
      </table>
      <input type="hidden" name="MM_update" value="form1">
    </form>
    <p>&nbsp;</p>
    <script>
<!--
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1");
//-->
    </script></div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsCountry);

mysql_free_result($rsRegions);
?>
