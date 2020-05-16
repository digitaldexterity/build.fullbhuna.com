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

if (isset($_POST["allregions"])) {
	$_POST['regionID'] = 0;
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
  $insertSQL = sprintf("INSERT INTO smsaccount (accountname, apiID, username, password, statusID, regionID, providerID, createdbyID, createddatetime, senderID) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['accountname'], "text"),
                       GetSQLValueString($_POST['apiID'], "text"),
                       GetSQLValueString($_POST['username'], "text"),
                       GetSQLValueString($_POST['password'], "text"),
                       GetSQLValueString(isset($_POST['statusID']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['regionID'], "int"),
                       GetSQLValueString($_POST['providerID'], "int"),
                       GetSQLValueString($_POST['createdbyID'], "int"),
                       GetSQLValueString($_POST['createddatetime'], "date"),
                       GetSQLValueString($_POST['senderID'], "text"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($insertSQL, $aquiescedb) or die(mysql_error());
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
  $insertGoTo = "index.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
    $insertGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $insertGoTo)); exit;
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
$query_rsSMSProviders = "SELECT * FROM smsprovider ORDER BY providername ASC";
$rsSMSProviders = mysql_query($query_rsSMSProviders, $aquiescedb) or die(mysql_error());
$row_rsSMSProviders = mysql_fetch_assoc($rsSMSProviders);
$totalRows_rsSMSProviders = mysql_num_rows($rsSMSProviders);
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Add SMS Account"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" --><style><!--
--></style>
<link href="../../../SpryAssets/SpryValidationSelect.css" rel="stylesheet" >
<link href="../../../SpryAssets/SpryValidationTextField.css" rel="stylesheet" >
<script src="../../../SpryAssets/SpryValidationSelect.js"></script>
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
      <h1>Add SMS Account</h1>
    
      <form action="<?php echo $editFormAction; ?>" method="post" name="form1">
        <table class="form-table"> <tr><tr>
            <td class="text-nowrap text-right top"><label for="providerID">Provider:</label></td>
            <td class="form-inline"><span id="spryselect1">
              <select name="providerID" id="providerID" class="form-control">
                <option value=""><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
                <?php
do {  
?>
                <option value="<?php echo $row_rsSMSProviders['ID']?>"><?php echo $row_rsSMSProviders['providername']?></option>
                <?php
} while ($row_rsSMSProviders = mysql_fetch_assoc($rsSMSProviders));
  $rows = mysql_num_rows($rsSMSProviders);
  if($rows > 0) {
      mysql_data_seek($rsSMSProviders, 0);
	  $row_rsSMSProviders = mysql_fetch_assoc($rsSMSProviders);
  }
?>
              </select>
            <span class="selectRequiredMsg">Please select an item.</span></span><a href="providers/index.php.php">Manage</a></td>
          </tr>
            <td class="text-nowrap text-right top">Account name:</td>
            <td><span id="sprytextfield1">
              <input type="text" name="accountname" value="" size="50" class="form-control">
              <span class="textfieldRequiredMsg">A value is required.</span></span></td>
          </tr> <tr>
            <td class="text-nowrap text-right top">API ID:</td>
            <td><input type="text" name="apiID" value="" size="50" class="form-control"></td>
          </tr> 
          <tr>
             <td class="text-nowrap text-right top">Sender ID:</td>
             <td><input type="text" name="senderID" value="" size="50" placeholder="(optional)"  class="form-control"></td>
          </tr>
          <tr>
            <td class="text-nowrap text-right top">Username:</td>
            <td><input name="username" type="text" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');" size="50" class="form-control"></td>
          </tr> <tr>
            <td class="text-nowrap text-right top">Password:</td>
            <td><input name="password" type="text" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');" size="50" class="form-control"></td>
          </tr> <tr>
            <td class="text-nowrap text-right top">Active:</td>
            <td><input name="statusID" type="checkbox" value="1" checked="CHECKED" ></td>
          </tr> <tr>
            <td class="text-nowrap text-right top">All sites:</td>
            <td><input type="checkbox" name="allregions" value="1" >
            <input name="regionID" type="hidden" id="regionID" value="<?php echo isset($regionID) ? $regionID : 1; ?>"></td>
          </tr> <tr>
            <td class="text-nowrap text-right top">&nbsp;</td>
            <td><button type="submit"  class="btn btn-primary">Add Account</button></td>
          </tr>
        </table>
        <input type="hidden" name="createdbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>">
        <input type="hidden" name="createddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>">
        <input type="hidden" name="MM_insert" value="form1">
      </form>
      <p>&nbsp;</p>
      <script>
var spryselect1 = new Spry.Widget.ValidationSelect("spryselect1");
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1");
      </script>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsSMSProviders);
?>
