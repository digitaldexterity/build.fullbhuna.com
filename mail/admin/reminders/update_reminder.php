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
	if($_POST['nextsend'] != $_POST['firstsend']) { // new send date
		$_POST['lastsent'] = ""; $_POST['firstsend'] = $_POST['nextsend']; 
	}
}

if(!isset($_POST['repeat']) && isset($_POST['lastsent'])) {
	// if repeat unchecked and last send then status = 0
	unset($_POST['statusID']);
}

if(isset($_POST['htmlmessage']) && trim($_POST['htmlmessage'])!="") { // html message update so swap


	$_POST['htmlmessage'] = $_POST['message'];
	$_POST['message'] = "";
} 


if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE reminder SET cc=%s, statusID=%s, viaemail=%s, viasms=%s, subject=%s, message=%s, firstsend=%s, lastsent=%s, reminderrepeat=%s, months=%s, seconds=%s, modifiedbyID=%s, modifieddatetime=%s, ignoreoptout=%s WHERE ID=%s",
                       GetSQLValueString($_POST['cc'], "text"),
                       GetSQLValueString(isset($_POST['statusID']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['viaemail']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['viasms']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['subject'], "text"),
                       GetSQLValueString($_POST['message'], "text"),
                       GetSQLValueString($_POST['firstsend'], "date"),
                       GetSQLValueString($_POST['lastsent'], "date"),
                       GetSQLValueString(isset($_POST['repeat']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['months'], "int"),
                       GetSQLValueString($_POST['seconds'], "int"),
                       GetSQLValueString($_POST['modifiedbyID'], "int"),
                       GetSQLValueString($_POST['modifieddatetime'], "date"),
                       GetSQLValueString(isset($_POST['ignoreoptout']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
	if(isset($_POST['send'])) {
		sendReminder($_POST['ID']);
		$msg = "Reminder sent";
		} else {
	  $updateGoTo = "index.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo)); exit;
	
	}
}

$colname_rsReminder = "-1";
if (isset($_GET['reminderID'])) {
  $colname_rsReminder = $_GET['reminderID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsReminder = sprintf("SELECT reminder.*, users.firstname, users.surname, users.email, users.telephone, users.mobile FROM reminder LEFT JOIN users ON (reminder.recipientID = users.ID) WHERE reminder .ID = %s", GetSQLValueString($colname_rsReminder, "int"));
$rsReminder = mysql_query($query_rsReminder, $aquiescedb) or die(mysql_error());
$row_rsReminder = mysql_fetch_assoc($rsReminder);
$totalRows_rsReminder = mysql_num_rows($rsReminder);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsAdminUsers = "SELECT ID, firstname,surname, email,mobile FROM users WHERE (usertypeID >= 7 OR ID = ".$row_rsReminder['recipientID'].") ORDER BY surname ASC";
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
<title><?php $pageTitle = "Update Scheduled Message"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script src="../../../SpryAssets/SpryValidationSelect.js"></script>
<script src="../../../SpryAssets/SpryValidationTextarea.js"></script>
<script src="../../../SpryAssets/SpryValidationTextField.js"></script>
<link href="../../../SpryAssets/SpryValidationSelect.css" rel="stylesheet" type="text/css" />
<link href="../../../SpryAssets/SpryValidationTextarea.css" rel="stylesheet" type="text/css" />
<link href="../../../SpryAssets/SpryValidationTextField.css" rel="stylesheet" type="text/css" />
<?php if($row_rsReminder['reminderrepeat']==0) { // no repeat so hide select menu
echo "<style> #span_repeat { visibility: hidden; } </style>";
} ?><?php $remove_script_host = "false"; // default for tinymce is true
if(!defined("TINYMCE_CONTENT_CSS")) define("TINYMCE_CONTENT_CSS", "/core/css/global.css");
define("TINY_MCE_PLUGINS", "fullpage");require_once('../../../core/tinymce/tinymce.inc.php'); ?>
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
    <h1><i class="glyphicon glyphicon-envelope"></i> Update Scheduled Message    </h1>
    <p>
      <?php require_once('../../../core/includes/alert.inc.php'); ?>
    </p>
    <form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1">
      <table class="form-table"> <tr>
          <td class="nowrap text-right">Method</td>
          <td><label>
            <input <?php if (!(strcmp($row_rsReminder['viaemail'],1))) {echo "checked=\"checked\"";} ?> name="viaemail" type="checkbox" id="viaemail" value="1" >
            Email</label>
            &nbsp;&nbsp;&nbsp;
            <label>
              <input <?php if (!(strcmp($row_rsReminder['viasms'],1))) {echo "checked=\"checked\"";} ?> name="viasms" type="checkbox" id="viaemail" value="1">
              SMS (requires  account)</label></td>
        </tr> <tr>
          <td class="text-nowrap text-right">Recipient:</td>
          <td class="form-inline">
          <?php echo $row_rsReminder['firstname']; ?> <?php echo $row_rsReminder['surname']; ?> <?php echo $row_rsReminder['email']; ?> <?php echo $row_rsReminder['telephone']; ?> <?php echo $row_rsReminder['mobile']; ?><div><label><input <?php if (!(strcmp($row_rsReminder['ignoreoptout'],1))) {echo "checked=\"checked\"";} ?> name="ignoreoptout" type="checkbox" value="1" onClick="if(this.checked) alert('This will send to users who have opted out of receiving communications. Please enure this complies with GDPR or similar legislation');">&nbsp;Override  opt-out</label></div></td>
        </tr> <tr>
          <td class="text-nowrap text-right">Cc:</td>
          <td><span id="sprytextfield3">
            <input name="cc" type="text"  id="cc" value="<?php echo $row_rsReminder['cc']; ?>" size="50" maxlength="50"  class="form-control"/>
          </span></td>
        </tr> <tr>
          <td class="text-nowrap text-right">Email Subject:</td>
          <td><span id="sprytextfield1">
            <input name="subject" type="text"  value="<?php echo $row_rsReminder['subject']; ?>" size="50" maxlength="50"  class="form-control" />
          <span class="textfieldRequiredMsg">A value is required.</span></span></td>
        </tr> <tr>
          <td class="text-nowrap text-right top">Email Message:</td>
          <td><span id="sprytextarea1">
            <textarea name="message" cols="50" rows="5" class="form-control" <?php if(isset($row_rsReminder['htmlmessage']) && trim($row_rsReminder['htmlmessage']) !="") echo " class=\"tinymce\" "; ?>><?php echo (isset($row_rsReminder['htmlmessage']) && trim($row_rsReminder['htmlmessage']) !="") ? $row_rsReminder['htmlmessage'] : $row_rsReminder['message']; ?></textarea>
          <span class="textareaRequiredMsg">A value is required.</span></span><input name="htmlmessage" type="hidden" value="<?php echo (isset($row_rsReminder['htmlmessage']) && trim($row_rsReminder['htmlmessage']) !="") ? htmlentities($row_rsReminder['htmlmessage']) : ""; ?>"></td>
        </tr> 
        <tr>
          <td class="text-nowrap text-right top">SMS Message:</td>
          <td><textarea name="smsmessage" class="form-control" rows="2" ><?php echo  $row_rsReminder['smsmessage']  ?></textarea></td>
        </tr>
        <tr>
          <td class="text-nowrap text-right">First send:</td>
          <td><input name="firstsend" type="hidden" id="firstsend" value="<?php echo $row_rsReminder['firstsend']; ?>" />
            <input name="nextsend" type="hidden" id="nextsend" value="<?php $setvalue = $row_rsReminder['firstsend']; $time = true; $inputname= "nextsend"; echo $setvalue; ?>" />
            <?php require_once('../../../core/includes/datetimeinput.inc.php'); ?>
(changing if already sent will reset schedule)</td>
        </tr> <tr>
          <td class="text-nowrap text-right">Last sent:</td>
          <td><input name="lastsent" type="hidden" id="lastsent" value="<?php echo $row_rsReminder['lastsent']; ?>" /><?php echo isset($row_rsReminder['lastsent']) ? date('d M Y H:i',strtotime($row_rsReminder['lastsent'])) : "Not sent yet"; ?> </td>
        </tr> <tr>
          <td class="text-nowrap text-right">Repeat:</td>
          <td class="form-inline"><?php if($row_rsReminder['months']>0) { // MULTIPLE (not  months)
		  $multiple = $row_rsReminder['months'];
		  $increment = 0;
		  } else  if(($row_rsReminder['seconds']/604800) >= 1) { // weeks
		  	$multiple = floor($row_rsReminder['seconds']/604800);
		  	$increment = 604800;
		  } else  if(($row_rsReminder['seconds']/86400) >= 1) { // days
		  	$multiple = floor($row_rsReminder['seconds']/86400);
		  	$increment = 86400;
		  } else  if(($row_rsReminder['seconds']/3600) >= 1) { // hours
		  	$multiple = floor($row_rsReminder['seconds']/3600);
		  	$increment = 3600;
		  } else  if(($row_rsReminder['seconds']/60) >= 1) { // minutes
		  	$multiple = floor($row_rsReminder['seconds']/60);
		  	$increment = 60;
		  } else {
		  	$multiple = floor($row_rsReminder['seconds']);
		  	$increment = 1;
		  }
			  ?>
            <input <?php if (!(strcmp($row_rsReminder['reminderrepeat'],1))) {echo "checked=\"checked\"";} ?> name="repeat" type="checkbox" id="repeat" value="1" onclick="if(this.checked) { document.getElementById('span_repeat').style.visibility = 'visible' } else { document.getElementById('span_repeat').style.visibility = 'hidden'; }" /><span id="span_repeat">every
              <span id="sprytextfield2"><input name="multiple" type="text"  id="multiple" value="<?php echo $multiple>0 ? intval($multiple) : 1; ?>" size="5" maxlength="5"  class="form-control"/>
            
            <span class="textfieldRequiredMsg">A value is required.</span><span class="textfieldInvalidFormatMsg">Invalid format.</span><span class="textfieldMinValueMsg">The entered value is less than the minimum required.</span></span>
            <select name="interval" id="interval"  class="form-control">
              <!--<option value="1" <?php if (!(strcmp(1, $increment))) {echo "selected=\"selected\"";} ?>>Seconds</option>
              <option value="60" <?php if (!(strcmp(60, $increment))) {echo "selected=\"selected\"";} ?>>Minutes</option>
              <option value="3600" <?php if (!(strcmp(3600, $increment))) {echo "selected=\"selected\"";} ?>>Hours</option>-->
              <option value="86400" <?php if (!(strcmp(86400, $increment))) {echo "selected=\"selected\"";} ?>>Days</option>
              <option value="604800" <?php if (!(strcmp(604800, $increment))) {echo "selected=\"selected\"";} ?>>Weeks</option>
              <option value="0" <?php if (!(strcmp(0, $increment))) {echo "selected=\"selected\"";} ?>>Months</option>
          </select></span></td>
        </tr><tr>
          <td class="text-nowrap text-right">Active:</td>
          <td><input <?php if (!(strcmp($row_rsReminder['statusID'],1))) {echo "checked=\"checked\"";} ?> type="checkbox" name="statusID" value="1" /></td>
        </tr> <tr>
          <td class="text-nowrap text-right">Server time:</td>
          <td><?php echo date('H:i:s'); ?></td>
        </tr> <tr>
          <td class="text-nowrap text-right">&nbsp;</td>
          <td><button type="submit" name="save" class="btn btn-primary" >Save changes</button>  <button type="submit" name="send" class="btn btn-default btn-secondary">Send now...</button>
          <input name="modifiedbyID" type="hidden" id="modifiedbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
          <input name="modifieddatetime" type="hidden" id="modifieddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
          <input name="ID" type="hidden" id="ID" value="<?php echo $row_rsReminder['ID']; ?>" />
          <input name="months" type="hidden"  value="0"  />
          <input name="seconds" type="hidden" value="0" />
        </td>
        </tr>
      </table>
      <input type="hidden" name="MM_update" value="form1" />
    </form>
     <p>&nbsp;</p>
    <p><em>Created <?php echo date('H:i d M Y', strtotime($row_rsReminder['createddatetime'])); ?></em></p>
    <script>
<!--
var sprytextarea1 = new Spry.Widget.ValidationTextarea("sprytextarea1");
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1");
var sprytextfield2 = new Spry.Widget.ValidationTextField("sprytextfield2", "integer", {minValue:1});
var sprytextfield3 = new Spry.Widget.ValidationTextField("sprytextfield3", "none", {isRequired:false, hint:"(optional additional email)"});
//-->
    </script></div>
  <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsAdminUsers);

mysql_free_result($rsLoggedIn);

mysql_free_result($rsReminder);
?>
