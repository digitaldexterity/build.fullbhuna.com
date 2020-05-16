<?php require_once('../../Connections/aquiescedb.php'); ?><?php require_once('../../core/includes/adminAccess.inc.php'); ?>
<?php require_once('../includes/userfunctions.inc.php'); ?>
<?php require_once('../../mail/includes/sendmail.inc.php'); ?>
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

$MM_restrictGoTo = "../../login/index.php?notloggedin=true";
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



if (isset($_POST['pword']) && isset($_POST['pword2']) && $_POST['pword'] !=$_POST['pword2']) {
	$error = "The two passwords entered do not match";
	unset($_POST["MM_update"]);
}


// added by me m5  5 encrypt
if (isset($_POST['pword'])) { 
	$plainpassword = $_POST['pword'];
	if($userPreferences['passwordencrypted'] != 1) {
		$_POST['plainpassword'] = $plainpassword ;
	}
	$md5salt = md5(generateSalt());
	$_POST['pword'] = encryptPassword($plainpassword ,$userPreferences['defaultcrypttype'],$md5salt);
} // end post password
// end added by me

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "updatelogin")) {
  $updateSQL = sprintf("UPDATE users SET username=%s, password=%s, plainpassword=%s, password_salt = ".GetSQLValueString($md5salt, "text").", crypttype=%s, changepassword=%s, failedlogin=%s, modifieddatetime=%s, modifiedbyID=%s WHERE ID=%s",
                       GetSQLValueString($_POST['username'], "text"),
                       GetSQLValueString($_POST['pword'], "text"),
                       GetSQLValueString($_POST['plainpassword'], "text"),
					   GetSQLValueString($userPreferences['defaultcrypttype'], "text"),
                       GetSQLValueString(isset($_POST['changepassword']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['failedlogin'], "int"),
                       GetSQLValueString($_POST['modifieddatetime'], "date"),
                       GetSQLValueString($_POST['modifiedbyID'], "int"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}


if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "updatelogin")) {
	if(isset($_POST['notifyemail']) && trim($_POST['email']) !="") { // notify by email
		$to = $_POST['email'];
		$subject = "Your ".$site_name." log in details";
		$message = "Dear ".$_POST['firstname'].",\n\n";
		$message .= "This is an automated email to let you know your password to the ".$site_name." web site has been updated. You can log in with the following details:\n\n";
		$message .= "Username: ".$_POST['username']."\n";
		$message .= "Password: ".$plainpassword ."\n\n";
		$message .= isset($_POST['changepassword']) ? "NOTE: This is a temporary password. You will be asked to change this when you first log in\n\n" : "";
			
		sendMail($to,$subject,$message); 
	}
  $updateGoTo = "modify_user.php?msg=".urlencode("This user's password has been updated.");
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo));exit;
}

$varUserID_rsUser = "1";
if (isset($_GET['userID'])) {
  $varUserID_rsUser = $_GET['userID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsUser = sprintf("SELECT ID, username, firstname, surname, usertypeID, users.email, users.firstname FROM users WHERE ID = %s", GetSQLValueString($varUserID_rsUser, "int"));
$rsUser = mysql_query($query_rsUser, $aquiescedb) or die(mysql_error());
$row_rsUser = mysql_fetch_assoc($rsUser);
$totalRows_rsUser = mysql_num_rows($rsUser);

$colname_rsLoggedIn = "1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = (get_magic_quotes_gpc()) ? $_SESSION['MM_Username'] : addslashes($_SESSION['MM_Username']);
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID, usertypeID FROM users WHERE username = '%s'", $colname_rsLoggedIn);
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);


if(!isset($_POST['modifiedbyID'])) {

	$password = generatePassword(8,1,$userPreferences['passwordspecialchar'],$userPreferences['passwordmulticase']);
	$msg = "An auto-generated password has been entered, but you can change this to one of your choice.";
} else {
	$msg = "Please enter new password twice for user below:";
}


?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Update user password"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<meta http-equiv="Expires" content="Fri, Jun 12 1981 00:00:00 GMT">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Cache-Control" content="no-store">
<meta http-equiv="Cache-Control" content="no-cache">
<link href="../css/membersDefault.css" rel="stylesheet"  /> <script>
<!--

$(document).ready(function(e) {
	$(".toggle-password").click(function() {

  $(this).find("i").toggleClass("glyphicon-eye-open glyphicon-eye-close");
  var input = $($(this).attr("toggle"));
  if (input.attr("type") == "password") {
    input.attr("type", "text");
  } else {
    input.attr("type", "password");
  }
});
    
});

function validateForm() { 
 var errors = "";

  if(document.updatelogin.pword.value.match(/^[ ]+$/)) errors = errors + "The password must not contain spaces.\n";
if (document.updatelogin.pword.value == "") errors = errors + "Please enter a password.\n"; else if(document.updatelogin.pword.value.length < 6) errors = errors + "For security reasons, your password must be at least six characters in length.\n";
  if (document.updatelogin.pword.value != document.updatelogin.pword2.value) errors = errors + "The two new passwords you entered do not match.\n";
     if (errors) window.alert("There was a problem:\n\n"+errors);


   document.returnValue = (!errors);
 
}
//-->
</script>
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" -->
   <div class="page users">
    <h1><i class="glyphicon glyphicon-user"></i> Change Password</h1>
			  <?php if (($row_rsUser['usertypeID'] < $row_rsLoggedIn['usertypeID']) || ($row_rsLoggedIn['usertypeID']>=9 && $row_rsUser['usertypeID'] <= $row_rsLoggedIn['usertypeID'])) { ?>
  
      <?php require_once('../../core/includes/alert.inc.php'); ?>
<form action="<?php echo $editFormAction; ?>" method="post" name="updatelogin" id="updatelogin"  onsubmit="validateForm();return document.returnValue;" >
        <table border="0" cellpadding="2" cellspacing="0" class="form-table">
          <tr>
            <td align="right">Full Name:</td>
            <td><?php echo $row_rsUser['firstname']; ?> <?php echo $row_rsUser['surname']; ?> </td>
          </tr>
          <tr>
            <td align="right">User name:</td>
            <td>
            <input type="hidden" name="username" id="username" value="<?php echo $row_rsUser['username']; ?>" readonly /><?php echo $row_rsUser['username']; ?></td>
          </tr>
          <tr>
            <td align="right">New Password: </td>
            <td><div class="input-group"><input name="pword" type="password"  id="pword" autocomplete="off" value="<?php echo isset($password) ? $password : ""; ?>" size="32" maxlength="32" class="form-control" /><span class="input-group-btn">
        <button class="btn btn-default btn-secondary  toggle-password" type="button" toggle="#pword"><i class="glyphicon glyphicon-eye-open  "></i></button></span></div></td>
          </tr>
          <tr>
            <td align="right">New Password (confirm):</td>
            <td><div class="input-group"><input name="pword2" type="password"  id="pword2" size="32" maxlength="32" value="<?php echo isset($password) ? $password : "";  ?>" class="form-control" /><span class="input-group-btn">
        <button class="btn btn-default btn-secondary  toggle-password" type="button" toggle="#pword2"><i class="glyphicon glyphicon-eye-open  "></i></button></span></div>
            <input type="hidden" name="plainpassword" id="plainpassword"  class="form-control" /></td>
          </tr>
          <tr>
            <td align="right">Email new password to user:</td>
            <td><label>
              <input name="notifyemail" type="checkbox" id="notifyemail" value="1" />
            </label></td>
          </tr>
          <tr>
            <td align="right">Must change password at next login:</td>
            <td><label>
              <input type="checkbox" name="changepassword" id="changepassword" />
            </label></td>
          </tr>
          <tr>
            <td><input name="ID" type="hidden" id="ID" value="<?php echo $row_rsUser['ID']; ?>" />
            <input name="referer" type="hidden" id="referer" value="<?php echo $_SERVER['HTTP_REFERER']; ?>" />
            <input name="email" type="hidden" id="email" value="<?php echo $row_rsUser['email']; ?>" />
            <input name="firstname" type="hidden" id="firstname" value="<?php echo $row_rsUser['firstname']; ?>" />
            <input name="modifieddatetime" type="hidden" id="modifieddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
            <input name="modifiedbyID" type="hidden" id="modifiedbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
            <td><button type="submit" class="btn btn-primary">Save changes</button></td>
          </tr>
        </table>
      <input type="hidden" name="MM_update" value="updatelogin" />
      <input name="failedlogin" type="hidden" id="failedlogin" value="0" />
</form>
	  <?php } else { //no authority ?>
      <p>You are not authorised to change this data.</p>
	  <?php } ?>
      </div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsUser);

mysql_free_result($rsLoggedIn);

mysql_free_result($rsUserPrefs);
?>
