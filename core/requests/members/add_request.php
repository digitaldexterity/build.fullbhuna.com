<?php require_once('../../../Connections/aquiescedb.php'); ?>
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

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
  $insertSQL = sprintf("INSERT INTO changerequest (requesttypeID, pagetitle, requestdetails, ip4address, hostsystem, createddatetime, createdbyID, statusID, URL) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['requesttype'], "int"),
                       GetSQLValueString($_POST['pagetitle'], "text"),
                       GetSQLValueString($_POST['requestdetails'], "text"),
                       GetSQLValueString($_POST['ip4address'], "text"),
                       GetSQLValueString($_POST['hostsystem'], "text"),
                       GetSQLValueString($_POST['createddatetime'], "date"),
                       GetSQLValueString($_POST['createdbyID'], "int"),
                       GetSQLValueString($_POST['statusID'], "int"),
                       GetSQLValueString($_POST['URL'], "text"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($insertSQL, $aquiescedb) or die(mysql_error());
}

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID, CONCAT(users.firstname, ' ',users.surname) AS fullname, users.email FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT * FROM preferences";
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsMailPrefs = "SELECT feedbackpost, feedbackemail FROM mailprefs";
$rsMailPrefs = mysql_query($query_rsMailPrefs, $aquiescedb) or die(mysql_error());
$row_rsMailPrefs = mysql_fetch_assoc($rsMailPrefs);
$totalRows_rsMailPrefs = mysql_num_rows($rsMailPrefs);

$accesslevel = $row_rsMailPrefs['feedbackpost'];

?>
<?php require_once('../../../members/includes/restrictaccess.inc.php'); ?><?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php echo $site_name; ?> | Help</title>
<!-- InstanceEndEditable -->
<?php require_once('../../seo/includes/seo.inc.php'); ?>
<?php require_once('../../../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<style>
#help_search, #backtotop, #footer, #home_link, .link_divider { display:none; }
</style>
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../../../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" -->
    <h2>Feedback</h2>
    <?php if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1"))  { 
	$to = $row_rsMailPrefs['feedbackemail'];
	$from = $_POST['email'];
	$friendlyfrom = $_POST['fullname'];
	if($_POST['requesttype']==1) { $type = "Change Request"; } else if($_POST['requesttype']==2) { $type = "--BUG/ERROR--"; } else { $type = "New feature suggestion"; }
	$subject = $site_name." Feedback: ".$type;
	$message = "The following message has been added to the Feedback Log:\n\n";
	$message .= $_POST['requestdetails']."\n\n";
	$message .= "Log on to ";
	$message .= getProtocol()."://". $_SERVER['HTTP_HOST']."/requests/admin/";
	require_once('../../../mail/includes/sendmail.inc.php'); 
	sendMail($to,$subject,$message,$from,$friendlyfrom);
	$to = $_POST['email'];
	$from = $row_rsPreferences['noreplyemail'];
	$friendlyfrom = $row_rsPreferences['orgname'];
	$subject = "Change request received";
	$message = "Thank you,\n";
	$message .= "We have received your change request and we shall keep you informed of progress.\n\nThis is an automated message, please do not respond.\n\n";
	$message .= "Request details:\n";
	$message .= $_POST['requestdetails']."\n\n";
	sendMail($to,$subject,$message,$from,$friendlyfrom);
	?>
    <script>
	<?php if(isset($_GET['adminpage'])) { // if opened from admin page , refresh this to show this entry ?>
	window.opener.location.reload();
	<?php } ?>
	
	if(window.opener) {
		window.close();
	} else {
		window.location.href="/admin/requests/";
	} 
	</script>
    <p>Your request has been submitted.</p>
    <?php } else { ?>
    <p>Report bugs, request changes or suggest new features:</p>
    <form action="<?php echo $editFormAction; ?>" method="post" id="form1">
      <table border="0" cellpadding="2" cellspacing="2" class="form-table">
       <tr>
          <td>Type: 
            
          <label><input type="radio" name="requesttype" id="requesttype1" value="1" />
            <span class="red">Bug/error</span></label>
          &nbsp;&nbsp;&nbsp;<label>
              <input name="requesttype" type="radio" id="requesttype2" value="3" checked="checked" />
          Change</label>&nbsp;&nbsp;&nbsp;<label>
              <input type="radio" name="requesttype" id="requesttype3" value="3" />
          Suggestion</label></td>
        </tr>
        <tr>
          <td>Your email:</td>
        </tr>
        <tr>
          <td><input name="email" type="email" multiple id="email" value="<?php echo $row_rsLoggedIn['email']; ?>" size="40" maxlength="100"  class="form-control"/></td>
        </tr>
        <tr>
          <td>Feedback:</td>
        </tr>
        <tr>
          <td><textarea name="requestdetails" id="requestdetails"  class="form-control" cols="40" rows="6"></textarea></td>
        </tr>
        <tr>
          <td>
            <button type="submit" id="submit2" class="btn btn-primary" >Submit</button>
            <input type="hidden" name="ip4address" id="ip4address" value="<?php echo getClientIP(); ?>" />
            <input type="hidden" name="hostsystem" id="hostsystem" value="<?php echo $_SERVER['HTTP_USER_AGENT']; ?>" />
            
            <input type="hidden" name="URL" id="URL" value="" />
            <input name="createdbyID" type="hidden" id="createdbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
            <input name="createddatetime" type="hidden" id="createddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
            <input name="statusID" type="hidden" id="statusID" value="0" />
            <input type="hidden" name="pagetitle" id="pagetitle" />
            
            <input name="fullname" type="hidden" id="fullname" value="<?php echo $row_rsLoggedIn['fullname']; ?>" />
          <?php if(isset($_SESSION['MM_UserGroup']) && $_SESSION['MM_UserGroup']>=8) { ?>&nbsp;<a href="/requests/admin/" onclick="top.opener.location.href='/requests/admin/'; window.close(); return false;">View previous</a><?php } ?></td>
        </tr>
      </table>
      <input type="hidden" name="MM_insert" value="form1" />
    </form>
<script>
	document.getElementById('URL').value = window.opener.location;
	document.getElementById('pagetitle').value = window.opener.document.title;
	document.getElementById('requestdetails').value = window.opener.document.title;
	</script>
    <?php } ?>
  <!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../../../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsPreferences);

mysql_free_result($rsMailPrefs);
?>
