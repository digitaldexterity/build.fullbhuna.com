<?php require_once('../../../Connections/aquiescedb.php'); ?><?php require_once('../../includes/reminders.inc.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "7,8,9,10";
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

if(isset($_POST['interval'])) {
	if($_POST['interval'] == 0) { // months
		$_POST['months'] = $_POST['multiple'];
	} else {
		$_POST['seconds'] = $_POST['multiple']*$_POST['interval'];
	}
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
	$repeat = isset($_POST['repeat']) ? 1 : 0;
	$viaemail = isset($_POST['viaemail']) ? 1 : 0;
	$viasms = isset($_POST['viasms']) ? 1 : 0;
	$ignoreoptout = isset($_POST['ignoreoptout']) ? 1 : 0;
	//die($_POST['months'].":". $_POST['seconds']);
	addReminder($_POST['firstsend'],$_POST['recipientID'],$_POST['subject'], $_POST['message'], $repeat , $_POST['createdbyID'],   "", "", "", "","", $viaemail , $viasms , "",  $_POST['months'], $_POST['seconds'],$_POST['cc'], "", $ignoreoptout) ;
	


  $insertGoTo = "index.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
    $insertGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $insertGoTo)); exit;
}

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsAdminUsers = "SELECT ID,firstname,surname, email, mobile FROM users WHERE usertypeID >= 7 ORDER BY surname ASC";
$rsAdminUsers = mysql_query($query_rsAdminUsers, $aquiescedb) or die(mysql_error());
$row_rsAdminUsers = mysql_fetch_assoc($rsAdminUsers);
$totalRows_rsAdminUsers = mysql_num_rows($rsAdminUsers);

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT * FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Add Scheduled Message"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script src="../../../SpryAssets/SpryValidationSelect.js"></script>
<script src="../../../SpryAssets/SpryValidationTextField.js"></script>
<script src="../../../SpryAssets/SpryValidationTextarea.js"></script>
<link href="../../../SpryAssets/SpryValidationSelect.css" rel="stylesheet" type="text/css" />
<link href="../../../SpryAssets/SpryValidationTextField.css" rel="stylesheet" type="text/css" />
<link href="../../../SpryAssets/SpryValidationTextarea.css" rel="stylesheet" type="text/css" />
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
        <div class="page reminder">
    <h1><i class="glyphicon glyphicon-envelope"></i> Add Scheduled Message    </h1>
    <form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1">
      <table class="form-table"> <tr>
          <td class="text-nowrap text-right">Method</td>
          <td><label>
            <input name="viaemail" type="checkbox" id="viaemail" value="1" checked>
            Email</label>&nbsp;&nbsp;&nbsp;<label>
            <input name="viasms" type="checkbox" id="viaemail" value="1">
            SMS (requires  account)</label></td>
        </tr> <tr>
          <td class="text-nowrap text-right">Recipient:</td>
          <td class="form-inline"><span id="spryselect1">
            <select name="recipientID" class="form-control">
              <option value="" ><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
              <?php
do {  
?>
              <option value="<?php echo $row_rsAdminUsers['ID']?>"><?php echo $row_rsAdminUsers['firstname']."  ".$row_rsAdminUsers['surname']."  ".$row_rsAdminUsers['email']."  ".$row_rsAdminUsers['mobile']; ?></option>
              <?php
} while ($row_rsAdminUsers = mysql_fetch_assoc($rsAdminUsers));
  $rows = mysql_num_rows($rsAdminUsers);
  if($rows > 0) {
      mysql_data_seek($rsAdminUsers, 0);
	  $row_rsAdminUsers = mysql_fetch_assoc($rsAdminUsers);
  }
?>
            </select> <label><input type="checkbox" value="1" onClick="if(this.checked) alert('This will send to users who have opted out of receiving communications. Please enure this complies with GDPR or similar legislation');" name="ignoreoptout">&nbsp;Override  opt-out</label>
          <span class="selectRequiredMsg">Please select an item.</span></span></td>
        </tr> <tr>
          <td class="text-nowrap text-right">Email Cc:</td>
          <td><span id="sprytextfield3">
            <input name="cc" type="text" id="cc" size="50" maxlength="50"  class="form-control"/>
</span></td>
        </tr> <tr>
          <td class="text-nowrap text-right">Email Subject:</td>
          <td><span id="sprytextfield1">
            <input name="subject" type="text"  value="" size="50" maxlength="50"  class="form-control"/>
          <span class="textfieldRequiredMsg">A value is required.</span></span></td>
        </tr> <tr>
          <td class="text-nowrap text-right top"> Message:</td>
          <td><span id="sprytextarea1">
            <textarea name="message" cols="50" rows="5"  class="form-control"></textarea>
          <span class="textareaRequiredMsg">A value is required.</span></span></td>
        </tr> <tr>
          <td class="text-nowrap text-right">First send:</td>
          <td><input name="firstsend" type="hidden" id="firstsend" value="<?php $setvalue = date('Y-m-d 00:00:00'); $time = true; $inputname= "firstsend"; echo $setvalue; ?>" /><?php require_once('../../../core/includes/datetimeinput.inc.php'); ?>
</td>
        </tr> <tr>
          <td height="17" class="text-nowrap text-right top">Repeat:</td>
          <td class="form-inline">
            <input name="repeat" type="checkbox" id="repeat" value="1" checked="checked" onclick="if(this.checked) { document.getElementById('span_repeat').style.visibility = 'visible' } else { document.getElementById('span_repeat').style.visibility = 'hidden'; }" /><span id="span_repeat">&nbsp;every
            <span id="sprytextfield2"><input name="multiple" type="text"  id="multiple" value="1" size="5" maxlength="5"  class="form-control"/>
            <span class="textfieldRequiredMsg">A value is required.</span><span class="textfieldInvalidFormatMsg">Invalid format.</span><span class="textfieldMinValueMsg">The entered value is less than the minimum required.</span></span>
            <select name="interval" id="interval" class="form-control">
              <!--<option value="1">Seconds</option>
              <option value="60">Minutes</option>
              <option value="3600">Hours</option>-->
              <option value="86400">Days</option>
              <option value="604800" selected="selected">Weeks</option>
              <option value="0">Months</option>
            </select>
          </span></td>
        </tr> <tr>
          <td class="text-nowrap text-right">Active:</td>
          <td><input type="checkbox" name="statusID" value="" checked="checked" /></td>
        </tr> <tr>
          <td class="text-nowrap text-right">&nbsp;</td>
          <td><button type="submit" class="btn btn-primary" >Add message</button>
          <input name="createdbyID" type="hidden" id="createdbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
          <input name="createddatetime" type="hidden" id="createddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
          <input name="months" type="hidden" value="0" />
          <input name="seconds" type="hidden" value="0" /></td>
        </tr>
      </table>
      <input type="hidden" name="MM_insert" value="form1" />
    </form>
    </div>
    <script>
<!--
var spryselect1 = new Spry.Widget.ValidationSelect("spryselect1");
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1");
var sprytextarea1 = new Spry.Widget.ValidationTextarea("sprytextarea1");
var sprytextfield3 = new Spry.Widget.ValidationTextField("sprytextfield3", "none", {isRequired:false, hint:"(optional additional email)"});
//-->
    </script>
  <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsAdminUsers);

mysql_free_result($rsLoggedIn);
?>
