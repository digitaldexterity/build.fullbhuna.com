<?php require_once('../../../Connections/aquiescedb.php'); ?><?php require_once('../../../core/includes/adminAccess.inc.php'); ?>
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
  $insertSQL = sprintf("INSERT INTO mailaccount (accountname, mailserver, port, usessl, username, password, deletemail, createdbyID, createddatetime, protocol, bounceaccount, regionID) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['accountname'], "text"),
                       GetSQLValueString($_POST['mailserver'], "text"),
                       GetSQLValueString($_POST['port'], "int"),
                       GetSQLValueString(isset($_POST['usessl']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['username'], "text"),
                       GetSQLValueString($_POST['password'], "text"),
                       GetSQLValueString(isset($_POST['deletemail']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['createdbyID'], "int"),
                       GetSQLValueString($_POST['createddatetime'], "date"),
                       GetSQLValueString($_POST['protocol'], "int"),
                       GetSQLValueString(isset($_POST['bounceaccount']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['regionID'], "int"));

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
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Add Mail Account"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script src="../../../SpryAssets/SpryValidationTextField.js"></script>
<script src="../../../SpryAssets/SpryValidationPassword.js"></script>
<script src="../../../SpryAssets/SpryTabbedPanels.js"></script>
<link href="../../../SpryAssets/SpryValidationTextField.css" rel="stylesheet" type="text/css" />
<link href="../../../SpryAssets/SpryValidationPassword.css" rel="stylesheet" type="text/css" />
<link href="../../../SpryAssets/SpryTabbedPanels.css" rel="stylesheet" type="text/css" />
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" --><div class="page mail"><form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1">
    <h1><i class="glyphicon glyphicon-envelope"></i> Add Mail Account</h1>
    <p>
      <label>Account name:<span id="sprytextfield1">
            <input name="accountname" type="text" value="" size="50" maxlength="50" class="form-control"/>
          <span class="textfieldRequiredMsg">A value is required.</span></span></label></p>
    <div id="TabbedPanels1" class="TabbedPanels">
      <ul class="TabbedPanelsTabGroup">
        <li class="TabbedPanelsTab" tabindex="0">Settings</li>
        <li class="TabbedPanelsTab" tabindex="0">Advanced</li>
      </ul>
      <div class="TabbedPanelsContentGroup">
        <div class="TabbedPanelsContent"> <table class="form-table"> <tr>
          <td class="text-nowrap text-right"><label for="protocol">Account type:</label></td>
          <td>
            <select name="protocol" id="protocol"  class="form-control">
              <option value="1">POP</option>
              <option value="2">IMAP</option>
            </select></td>
        </tr> <tr>
          <td class="text-nowrap text-right">Mail server:</td>
          <td><span id="sprytextfield2">
            <input type="text" name="mailserver" value="" size="50" maxlength="50" class="form-control" />
            <span class="textfieldRequiredMsg">A value is required.</span></span></td>
        </tr> <tr>
          <td class="text-nowrap text-right">Username:</td>
          <td><span id="sprytextfield4">
            <input type="text" name="username" value="" size="50" maxlength="50"  class="form-control" />
          <span class="textfieldRequiredMsg">A value is required.</span></span></td>
        </tr> <tr>
          <td class="text-nowrap text-right">Password:</td>
          <td><span id="sprypassword1">
            <input name="password" type="password" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');" size="50" maxlength="50"  class="form-control"/>
          <span class="passwordRequiredMsg">A value is required.</span></span></td>
        </tr>
      </table></div>
        <div class="TabbedPanelsContent">
          <table class="form-table"> <tr>
          <td class="text-nowrap text-right">Port:</td>
          <td class="form-inline"><span id="sprytextfield3">
          <input name="port" id="port" type="text" value="110" size="5" maxlength="5"  class="form-control" />
          <span class="textfieldRequiredMsg">A value is required.</span><span class="textfieldInvalidFormatMsg">Invalid format.</span></span></td>
        </tr> <tr>
              <td class="text-nowrap text-right"> <label for="usessl">Use SSL:</label></td>
              <td><input type="checkbox" name="usessl" id="usessl" onclick="if(this.checked &amp;&amp; document.getElementById('port').value == '110') { document.getElementById('port').value = '995' } else if(!this.checked &amp;&amp; document.getElementById('port').value == '995') { document.getElementById('port').value = '110' }" />
               </td>
            </tr>
                <tr>
                  <td><label for="deletemail">Delete mail from server:</label></td>
              <td><input type="checkbox" name="deletemail" id="deletemail" />
                </td>
            </tr>
                <tr>
                  <td  class="text-nowrap text-right"> <label for="bounceaccount">Bounce Account:</label></td>
                  <td><input type="checkbox" name="bounceaccount" id="bounceaccount">
                   </td>
                </tr>
          </table>
        </div>
      </div>
    </div>
    <p><button type="submit" class="btn btn-primary" >Add account</button>&nbsp;</p>
    
     <input type="hidden" name="regionID" value="<?php echo isset($regionID) ? $regionID : 1; ?>" />
      <input type="hidden" name="createdbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
      <input type="hidden" name="createddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
      <input type="hidden" name="MM_insert" value="form1" />
    </form>
    <p>&nbsp;</p>
    <script>
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1", "none", {hint:"Add a name for this account"});
var sprytextfield2 = new Spry.Widget.ValidationTextField("sprytextfield2", "none", {hint:"e.g. pop.yahoo.com"});
var sprytextfield3 = new Spry.Widget.ValidationTextField("sprytextfield3", "integer");
var sprytextfield4 = new Spry.Widget.ValidationTextField("sprytextfield4");
var sprypassword1 = new Spry.Widget.ValidationPassword("sprypassword1");
var TabbedPanels1 = new Spry.Widget.TabbedPanels("TabbedPanels1");
    </script></div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);
?>
