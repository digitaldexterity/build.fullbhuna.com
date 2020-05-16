<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php require_once('../../../core/includes/adminAccess.inc.php'); ?><?php require_once('../includes/smsfunctions.inc.php'); ?>
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
} else {
	$_POST['regionID'] = ($_POST['regionID']!=0) ? $_POST['regionID'] : (isset($regionID) ? $regionID : 1);
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE smsaccount SET accountname=%s, apiID=%s, username=%s, password=%s, statusID=%s, regionID=%s, providerID=%s, modifiedbyID=%s, modifieddatetime=%s, senderID=%s WHERE ID=%s",
                       GetSQLValueString($_POST['accountname'], "text"),
                       GetSQLValueString($_POST['apiID'], "text"),
                       GetSQLValueString($_POST['username'], "text"),
                       GetSQLValueString($_POST['password'], "text"),
                       GetSQLValueString(isset($_POST['statusID']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['regionID'], "int"),
                       GetSQLValueString($_POST['providerID'], "int"),
                       GetSQLValueString($_POST['modifiedbyID'], "int"),
                       GetSQLValueString($_POST['modifieddatetime'], "date"),
                       GetSQLValueString($_POST['senderID'], "text"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
	if(isset($_POST['test'])) {
		$message = (isset($_POST['message']) && trim($_POST['message'])!="") ? $_POST['message'] : "Hello, this is a test SMS message from ".$site_name;
		$msg = "SMS attempted. ";
		$msg .= sendSMS($_POST['phonenumber'],$message);
		if(strpos($msg,"ID:")!==false) {
			$msg .= " SENT SUCCESSFULLY";
		}
	} else {
	  $updateGoTo = "index.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo)); exit;
	}
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

$colname_rsAccount = "-1";
if (isset($_GET['accountID'])) {
  $colname_rsAccount = $_GET['accountID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsAccount = sprintf("SELECT * FROM smsaccount WHERE ID = %s", GetSQLValueString($colname_rsAccount, "int"));
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
<?php $pageTitle = "Update SMS Account"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
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
      <h1>Update SMS Account</h1>
    <?php require_once('../../../core/includes/alert.inc.php'); ?>
      <form action="<?php echo $editFormAction; ?>" method="POST" name="form1">
        <table class="form-table"> <tr>
            <td class="text-nowrap text-right top"><label for="providerID">Provider:</label></td>
            <td class="form-inline"><span id="spryselect1">
              <select name="providerID" id="providerID" class="form-control">
                <option value="" <?php if (!(strcmp("", $row_rsAccount['providerID']))) {echo "selected=\"selected\"";} ?>><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
                <?php
do {  
?>
                <option value="<?php echo $row_rsSMSProviders['ID']?>"<?php if (!(strcmp($row_rsSMSProviders['ID'], $row_rsAccount['providerID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsSMSProviders['providername']?></option>
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
          </tr> <tr>
            <td class="text-nowrap text-right top">Account name:</td>
            <td><span id="sprytextfield1">
              <input type="text" name="accountname" value="<?php echo $row_rsAccount['accountname']; ?>" size="50" readonly onfocus="this.removeAttribute('readonly');" class="form-control">
            <span class="textfieldRequiredMsg">A value is required.</span></span></td>
          </tr> <tr>
            <td class="text-nowrap text-right top">API ID:</td>
            <td><input type="text" name="apiID" value="<?php echo $row_rsAccount['apiID']; ?>" size="50" readonly onfocus="this.removeAttribute('readonly');" class="form-control"></td>
          </tr> 
          <tr>
            <td class="text-nowrap text-right top">Sender ID:</td>
            <td><input type="text" name="senderID" value="<?php echo $row_rsAccount['senderID']; ?>" size="50" placeholder="(optional)" readonly onfocus="this.removeAttribute('readonly');" class="form-control"></td>
          </tr>
          <tr>
            <td class="text-nowrap text-right top">Username:</td>
            <td><input name="username" type="text" autocomplete="off" value="<?php echo $row_rsAccount['username']; ?>" size="50" readonly onfocus="this.removeAttribute('readonly');" class="form-control"></td>
          </tr> <tr>
            <td class="text-nowrap text-right top">Password:</td>
            <td><input name="password" type="text" autocomplete="off" value="<?php echo $row_rsAccount['password']; ?>" size="50" readonly onfocus="this.removeAttribute('readonly');" class="form-control"></td>
          </tr> <tr>
            <td class="text-nowrap text-right top">Active:</td>
            <td><input <?php if (!(strcmp($row_rsAccount['statusID'],1))) {echo "checked=\"checked\"";} ?> name="statusID" type="checkbox" value="1" ></td>
          </tr> <tr>
            <td class="text-nowrap text-right top">All sites:</td>
            <td><input type="checkbox" name="allregions" value="1" <?php echo ($row_rsAccount['regionID']==0) ? "checked" : ""; ?>>
            <input name="regionID" type="hidden" id="regionID" value="<?php echo $row_rsAccount['regionID']; ?>"></td>
          </tr> <tr>
            <td class="text-nowrap text-right top">&nbsp;</td>
            <td><button type="submit" name="submitbutton" class="btn btn-primary">Save changes</button><input name="phonenumber" id="phonenumber" type="hidden" value=""></td>
          </tr> <tr>
            <td class="text-nowrap text-right top"><label for="message">Send message:</label></td>
            <td>
            <textarea name="message" id="message"  class="form-control"><?php echo isset($_POST['message']) ? htmlentities($_POST['message'], ENT_COMPAT,"UTF-8") : ""; ?></textarea></td>
          </tr> <tr>
            <td class="text-nowrap text-right top">&nbsp;</td>
            <td><button type="submit" name="test" id="test"  onClick="var phonenumber = prompt('Send test SMS message to the following number:'); if(phonenumber!='') { document.getElementById('phonenumber').value = phonenumber; if(document.getElementById('message').value.length>140) return confirm('This message is longer than 140 characters so will be sent in multiple texts'); } else { return false;} " class="btn btn-default btn-secondary">Send message...</button></td>
          </tr>
        </table>
        <input type="hidden" name="modifiedbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>">
        <input type="hidden" name="modifieddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>">
        <input name="ID" type="hidden" id="ID" value="<?php echo $row_rsAccount['ID']; ?>">
        <input type="hidden" name="MM_update" value="form1">
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

mysql_free_result($rsAccount);
?>
