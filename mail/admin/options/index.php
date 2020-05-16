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
  $updateSQL = sprintf("UPDATE mailprefs SET showemail=%s, noreplyemail=%s, envelopefrom=%s, webdevelopersURL=%s, livechat=%s, mailchimpapi=%s, mailchimplistid=%s, mailgunapi=%s, mailgundomain=%s, mailgunregion=%s, replytoemail=%s WHERE ID=%s",
                       GetSQLValueString(isset($_POST['showemail']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['noreplyemail'], "text"),
                       GetSQLValueString($_POST['envelopefrom'], "text"),
                       GetSQLValueString($_POST['webdevelopersURL'], "text"),
                       GetSQLValueString(isset($_POST['livechat']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['mailchimpapi'], "text"),
                       GetSQLValueString($_POST['mailchimplistid'], "text"),
                       GetSQLValueString($_POST['mailgunapi'], "text"),
                       GetSQLValueString($_POST['mailgundomain'], "text"),
					   GetSQLValueString($_POST['mailgunregion'], "int"),
                       GetSQLValueString($_POST['replytoemail'], "text"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
	$update = "UPDATE preferences SET contactemail = ".GetSQLValueString($_POST['contactemail'], "text")." WHERE ID = ".GetSQLValueString($_POST['ID'], "int");
	 $result = mysql_query($update, $aquiescedb) or die(mysql_error());
  $updateGoTo = isset($_GET['recipient']) ? "index.php" : "../index.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo)); exit;
}

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsMailPrefs = "SELECT * FROM mailprefs WHERE ID = ".intval($regionID) . "";
$rsMailPrefs = mysql_query($query_rsMailPrefs, $aquiescedb) or die(mysql_error());
$row_rsMailPrefs = mysql_fetch_assoc($rsMailPrefs);
$totalRows_rsMailPrefs = mysql_num_rows($rsMailPrefs);


mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT contactemail FROM preferences WHERE ID = ".intval($regionID);
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT email FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);
?>
<?php
if ($totalRows_rsMailPrefs<1) { // no MailPrefs yet
mysql_query("INSERT INTO mailprefs (ID) VALUES (1)", $aquiescedb) or die(mysql_error());
header("location: ".$_SERVER['REQUEST_URI']); exit;
} // end no mail prefs
?>
<?php if(isset($_GET['recipient']) && $_GET['recipient']!="") {
	require_once('../../includes/sendmail.inc.php'); 
	
	$message = "This is a test email. Please do not reply, although you may be contacted later by the system administrator to determine if and when you received this. You may also be asked for the following information below:\n\n";
	$message .= "Sent on ".date('d M Y')." at ".date('H:i:s')."\n\n";
	
	$message .= "return address: ".$envelopeFrom;
   
	$to = $_GET['recipient'];
	$from = ""; // send from set server address
	$friendlyfrom = $site_name;
	$subject = "Test email from ".$site_name;
	    sendMail($to,$subject,$message,$from,$friendlyfrom);
		header("location: index.php?msg=".urlencode($_GET['msg'])); exit;
} ?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Mail Options"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script src="../../../SpryAssets/SpryValidationTextField.js"></script>
<script>
function sendTest(recipient) {
recipient = prompt("The system will now send a test email to the address below:\n\n(Change if required)",recipient);
if(recipient) {
	document.getElementById('form1').action= "index.php?msg=The+email+has+been+sent&recipient="+recipient;
	document.getElementById('form1').submit();
//window.location.href = "index.php?msg=The+two+emails+have+been+sent&recipient="+recipient;
}
}
</script>
<link href="../../../SpryAssets/SpryValidationTextField.css" rel="stylesheet" type="text/css" />
<link href="../../css/mailDefault.css" rel="stylesheet" type="text/css" />
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
        <div class="page mail"><?php require_once('../../../core/region/includes/chooseregion.inc.php'); ?>
    <h1><i class="glyphicon glyphicon-envelope"></i> Mail Options</h1>
    <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
      <li><a href="../index.php" class="link_undo"><i class="glyphicon glyphicon-arrow-left"></i> Back to Manage Mail</a></li>
      <li><a href="../accounts/index.php" class="link_manage"><i class="glyphicon glyphicon-cog"></i> Email Accounts</a></li>
    </ul></div></nav>
    <form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1">
      <?php if(isset($_GET['msg'])) { ?> <p class="message alert alert-info" role="alert"><?php echo htmlentities($_GET['msg']); ?></p><?php } ?>
    
      <table class="form-table">
        <tr>
          <td align="right">Default  receiving email address:</td>
          <td class="form-inline"><span id="sprytextfield3">
            <input name="contactemail" type="email" multiple id="contactemail" value="<?php echo $row_rsPreferences['contactemail']; ?>" class="form-control" maxlength="100" />
</span></td>
        </tr>
        <tr>
          <td align="right">Default sender email address: </td>
          <td class="form-inline"><span id="sprytextfield2">
            <input name="noreplyemail" type="email"  id="noreplyemail" value="<?php echo $row_rsMailPrefs['noreplyemail']; ?>" size="50" maxlength="100" placeholder="website@<?php echo gethostname(); ?>"  class="form-control"/>
          <span class="textfieldInvalidFormatMsg">Invalid email address.</span></span></td>
        </tr>
        <tr>
          <td align="right">Default &quot;reply-to&quot; address:</td>
          <td class="form-inline"><input name="replytoemail" type="email"  id="replytoemail" value="<?php echo $row_rsMailPrefs['replytoemail']; ?>" size="50" maxlength="100" placeholder="(Optional - otherwise will use sender)"  class="form-control"/></td>
        </tr>
        <tr>
          <td align="right">Default return path (bounce back) email address:</td>
          <td class="form-inline">
            <label>
              <input name="envelopefrom" type="email"  id="envelopefrom" value="<?php echo $row_rsMailPrefs['envelopefrom']; ?>" size="50" maxlength="50" placeholder="(Optional - otherwise will use sender)"  class="form-control"/>
            </label> 
           
</td>
        </tr>
        <tr>
          <td><h2>Mailgun</h2></td>
          <td>Will send all mail via MailGun account instead of via web server</td>
        </tr>
        <tr>
          <td class="text-right">Mailgun API key:</td>
          <td class="form-inline"><input name="mailgunapi" type="text"   value="<?php echo $row_rsMailPrefs['mailgunapi']; ?>" size="50" maxlength="50"  class="form-control"/> <a href="https://www.mailgun.com" target="_blank" rel="noopener">Get a Mailgun account</a></td>
        </tr>
        <tr>
          <td class="text-right">Mailgun Domain:</td>
          <td class="form-inline"><input name="mailgundomain" type="text"   value="<?php echo $row_rsMailPrefs['mailgundomain']; ?>" size="50" maxlength="50"  class="form-control"/>
          <label for="mailgun_us"><input <?php if (!(strcmp($row_rsMailPrefs['mailgunregion'],"1"))) {echo "checked=\"checked\"";} ?> type="radio" id="mailgun_us" name="mailgunregion" value="1"> US</label> &nbsp;&nbsp; <label for="mailgun_eu"><input <?php if (!(strcmp($row_rsMailPrefs['mailgunregion'],"2"))) {echo "checked=\"checked\"";} ?> type="radio" name="mailgunregion"  id="mailgun_us" value="2"> EU</label></td>
        </tr>
        <tr>
          <td><h2>MailChimp </h2></td>
          <td>Send all qualifying user sign ups to your MailChimp account</td>
        </tr>
        <tr>
          <td align="right">MailChimp API Key:</td>
          <td class="form-inline"><input name="mailchimpapi" type="text"  id="mailchimpapi" value="<?php echo $row_rsMailPrefs['mailchimpapi']; ?>" size="50" maxlength="50"  class="form-control"/> 
          <a href="https://admin.mailchimp.com/account/api/" target="_blank" rel="noopener">Get an API key</a></td>
        </tr>
        <tr>
          <td align="right">MailChimp Subscriber List ID:</td>
          <td class="form-inline"><input name="mailchimplistid" type="text"  id="mailchimplistid" value="<?php echo $row_rsMailPrefs['mailchimplistid']; ?>" size="50" maxlength="50"  class="form-control"/> 
            <a href="https://admin.mailchimp.com/lists/" target="_blank" rel="noopener">Get your list ID</a> &gt; List &gt; Settings &gt; List name</td>
        </tr>
        <tr>
          <td colspan="2"><h2>Contact form</h2></td>
        </tr>
        <tr>
          <td align="right">Show email address:</td>
          <td><input <?php if (!(strcmp($row_rsMailPrefs['showemail'],1))) {echo "checked=\"checked\"";} ?> name="showemail" type="checkbox" id="showemail" value="1" /></td>
        </tr>
        <tr>
          <td align="right">Enable live chat:</td>
          <td><input <?php if (!(strcmp($row_rsMailPrefs['livechat'],1))) {echo "checked=\"checked\"";} ?> name="livechat" type="checkbox" id="livechat" value="1" /></td>
        </tr>
        <tr>
          <td align="right">Technical support URL:</td>
          <td class="form-inline">
          <input name="webdevelopersURL" type="text"  id="webdevelopersURL" value="<?php echo $row_rsMailPrefs['webdevelopersURL']; ?>" size="50" maxlength="50"  class="form-control" placeholder="(optional link to web developers site)"/>
</td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td>
            <button name="sendtest" id="sendtest" type="button"  onclick="javascript:sendTest('<?php echo $row_rsLoggedIn['email']; ?>'); return document.returnValue;" class="btn btn-default btn-secondary">Send test email...</button>
          </td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td><button name="save" type="submit"  id="save" class="btn btn-primary" >Save changes</button></td>
        </tr>
      </table>
      
    <input name="ID" type="hidden" id="ID" value="<?php echo $row_rsMailPrefs['ID']; ?>" />
      
      <input type="hidden" name="MM_update" value="form1" />
    Custom contact page can be set in <a href="../../../core/region/admin/index.php">Site Preferences</a>
  </form>
  
    <script>
<!--
var sprytextfield2 = new Spry.Widget.ValidationTextField("sprytextfield2", "email", {isRequired:false, hint:"for mail sent automatically from server"});
var sprytextfield3 = new Spry.Widget.ValidationTextField("sprytextfield3", "none", {isRequired:false, hint:"The address all mail gets sent by default"});

//-->
    </script></div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsMailPrefs);

mysql_free_result($rsPreferences);

mysql_free_result($rsLoggedIn);
?>
