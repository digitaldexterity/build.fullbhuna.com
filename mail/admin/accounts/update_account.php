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

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE mailaccount SET accountname=%s, mailserver=%s, port=%s, usessl=%s, username=%s, password=%s, deletemail=%s, modifiedbyID=%s, modifieddatetime=%s, statusID=%s, protocol=%s, bounceaccount=%s WHERE ID=%s",
                       GetSQLValueString($_POST['accountname'], "text"),
                       GetSQLValueString($_POST['mailserver'], "text"),
                       GetSQLValueString($_POST['port'], "int"),
                       GetSQLValueString(isset($_POST['usessl']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['username'], "text"),
                       GetSQLValueString($_POST['password'], "text"),
                       GetSQLValueString(isset($_POST['deletemail']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['modifiedbyID'], "int"),
                       GetSQLValueString($_POST['modifieddatetime'], "date"),
                       GetSQLValueString(isset($_POST['statusID']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['protocol'], "int"),
                       GetSQLValueString(isset($_POST['bounceaccount']) ? "true" : "", "defined","1","0"),
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

$colname_rsAccount = "-1";
if (isset($_GET['accountID'])) {
  $colname_rsAccount = $_GET['accountID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsAccount = sprintf("SELECT * FROM mailaccount WHERE ID = %s", GetSQLValueString($colname_rsAccount, "int"));
$rsAccount = mysql_query($query_rsAccount, $aquiescedb) or die(mysql_error());
$row_rsAccount = mysql_fetch_assoc($rsAccount);
$totalRows_rsAccount = mysql_num_rows($rsAccount);
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Update Mail Account"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
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
    <!-- InstanceBeginEditable name="Body" -->
        <div class="page mail">
    <h1><i class="glyphicon glyphicon-envelope"></i> Update Mail Account</h1><form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1">
    <p class="form-inline">
      <label>Account name: <span id="sprytextfield1">
            <input name="accountname" type="text" value="<?php echo $row_rsAccount['accountname']; ?>" size="50" maxlength="50" class="form-control" />
          <span class="textfieldRequiredMsg">A value is required.</span></span></label></p>
    <div id="TabbedPanels1" class="TabbedPanels">
      <ul class="TabbedPanelsTabGroup">
        <li class="TabbedPanelsTab" tabindex="0">Settings</li>
        <li class="TabbedPanelsTab" tabindex="0">Advanced</li>
      </ul>
      <div class="TabbedPanelsContentGroup">
        <div class="TabbedPanelsContent"><table class="form-table"> <tr>
          <td class="text-nowrap text-right"><label for="protocol">Account type:</label></td>
          <td><select name="protocol" id="protocol"  class="form-control" >
            <option value="1" <?php if (!(strcmp(1, $row_rsAccount['protocol']))) {echo "selected=\"selected\"";} ?>>POP</option>
            <option value="2" <?php if (!(strcmp(2, $row_rsAccount['protocol']))) {echo "selected=\"selected\"";} ?>>IMAP</option>
          </select></td>
        </tr> <tr>
          <td class="text-nowrap text-right">Mail server:</td>
          <td><span id="sprytextfield2">
            <input type="text" name="mailserver" value="<?php echo $row_rsAccount['mailserver']; ?>" size="50" maxlength="50"  class="form-control" />
            <span class="textfieldRequiredMsg">A value is required.</span></span></td>
        </tr> <tr>
          <td class="text-nowrap text-right">Username:</td>
          <td><span id="sprytextfield4">
            <input name="username" type="text" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');" value="<?php echo $row_rsAccount['username']; ?>" size="50" maxlength="50"  class="form-control" />
          <span class="textfieldRequiredMsg">A value is required.</span></span></td>
        </tr> <tr>
          <td class="text-nowrap text-right">Password:</td>
          <td><span id="sprypassword1">
            <input type="password" name="password" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');" value="<?php echo $row_rsAccount['password']; ?>" size="50" maxlength="50" class="form-control"  />
          <span class="passwordRequiredMsg">A value is required.</span></span></td>
        </tr> <tr>
          <td class="text-nowrap text-right"><label for="statusID">Active:</label></td>
          <td><input <?php if (!(strcmp($row_rsAccount['statusID'],1))) {echo "checked=\"checked\"";} ?> name="statusID" type="checkbox" id="statusID" value="1" />
          </td>
        </tr>
      </table></div>
        <div class="TabbedPanelsContent">
          <table class="form-table"> <tr>
              <td class="text-nowrap text-right">Port:</td>
              <td class="form-inline"><span id="sprytextfield3">
                <input name="port"  id="port" type="text" value="<?php echo $row_rsAccount['port']; ?>" size="5" maxlength="5"  class="form-control"  />
                <span class="textfieldRequiredMsg">A value is required.</span><span class="textfieldInvalidFormatMsg">Invalid format.</span></span></td>
            </tr> <tr>
              <td class="text-nowrap text-right"><label for="usessl">Use SSL:</label></td>
              <td><input <?php if (!(strcmp($row_rsAccount['usessl'],1))) {echo "checked=\"checked\"";} ?> name="usessl" type="checkbox" id="usessl" value="1"  onclick="if(this.checked &amp;&amp; document.getElementById('port').value == '110') { document.getElementById('port').value = '995' } else if(!this.checked &amp;&amp; document.getElementById('port').value == '995') { document.getElementById('port').value = '110' }" /></td>
            </tr>
            <tr>
              <td><label for="deletemail">Delete mail from server:</label></td>
              <td><input <?php if (!(strcmp($row_rsAccount['deletemail'],1))) {echo "checked=\"checked\"";} ?> name="deletemail" type="checkbox" id="deletemail" value="1" /></td>
</tr>
            <tr>
              <td  class="text-nowrap text-right"><label for="bounceaccount">Bounce Account:</label></td>
              <td><input <?php if (!(strcmp($row_rsAccount['bounceaccount'],1))) {echo "checked=\"checked\"";} ?> name="bounceaccount" type="checkbox" id="bounceaccount" value="1"></td>
            </tr>
          </table>
        </div>
      </div>
    </div>
    
      
      <input type="hidden" name="modifiedbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
      <input type="hidden" name="modifieddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
      <input name="ID" type="hidden" id="ID" value="<?php echo $row_rsAccount['ID']; ?>" />
      <input type="hidden" name="MM_update" value="form1" /><p><button type="submit" class="btn btn-primary">Save changes</button></p>
    </form>
    
    <script>
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1");
var sprytextfield2 = new Spry.Widget.ValidationTextField("sprytextfield2");
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

mysql_free_result($rsAccount);
?>
