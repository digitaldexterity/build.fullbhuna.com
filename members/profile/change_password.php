<?php require_once('../../core/includes/sslcheck.inc.php'); ?>
<?php require_once('../../Connections/aquiescedb.php'); ?><?php require_once('../includes/userfunctions.inc.php'); ?><?php require_once('../../login/includes/login.inc.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
if(isset($_GET['key'],$_GET['username'],$_GET['expires'])) {
	
	$result = loginWithKey($_GET['key'],$_GET['username'],$_GET['expires']);
	if(!$result) {
		header("location: /login/index.php?msg=".urlencode("The link you clicked on is invalid. It may have expired or been typed incorrectly.")); exit;
	} else {
		$loggedInWithKey = true;
		//print_r($_SESSION); die();
	}
	
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
    if (($strUsers == "") && true) { 
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

$colname_rsLoggedIn = "1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID, username, password, users.canchangepassword, users.changepassword FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

?>
<?php // my own script to validate and insert new password into  database
if($row_rsLoggedIn['canchangepassword']==1) { 
	$missmatch = false;
	$wrong_password = false;
	if (isset($_POST['updatepassword']) && $_POST['updatepassword'] ==true) { // post
		if  (!isset($loggedInWithKey) && !checkPassword($_SESSION['MM_Username'], $_POST['currentpassword'])) { // wrong password
			$error = "The current password you have entered is incorrect. This may be a new one you have recently been issued with.";
		} else if(strpos($_POST['password']," ")) { // contains spaces
			$error= "Your new password cannot contain spaces.";
		} else if($_POST['password']!=$_POST['password2']) { // don't match
			$error= "Your new password fields do not match.";
		} else if(strlen($_POST['password'])<6) { // password too short
			$error="The chosen new password is too short.";
		} else	{ // Ok to post
			$msg = passwordSecurity($_POST['password']);
			if($msg=="") {
				if($userPreferences['passwordencrypted'] != 1) { 	$_POST['plainpassword'] = $_POST['password']; }
				$md5salt = md5(generateSalt());
				$_POST['password'] = encryptPassword($_POST['password'],$userPreferences['defaultcrypttype'],$md5salt);
				$updateSQL = "UPDATE users SET password=".GetSQLValueString($_POST['password'], "text").", plainpassword = ".GetSQLValueString($_POST['plainpassword'], "text").", password_salt = ".GetSQLValueString($md5salt, "text").", changepassword = 0, crypttype = ".$userPreferences['defaultcrypttype'].", modifiedbyID =".GetSQLValueString($_POST['modifiedbyID'], "int").", modifieddatetime = ".GetSQLValueString($_POST['modifieddatetime'], "date")." WHERE ID=".GetSQLValueString($_POST['ID'], "int");
				mysql_select_db($database_aquiescedb, $aquiescedb);
								   //die($updateSQL);
				$Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
			  
				$returnURL = isset($_GET['returnURL']) ? $_GET['returnURL'] : "../index.php?changed_password=true";
				header(sprintf("Location: %s", $returnURL));exit;
			} else {
				$error = "Sorry, your new password is not secure enough:\n\n";
			}
		} // end OK to post
	} // end post
} // end can change
?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Change password"; echo $pageTitle." | ".$site_name; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<?php if(isset($_GET['currentpassword'])) { echo "<style> .currentpassword { display:none; } </style>"; } ?>
<meta http-equiv="Expires" content="Fri, Jun 12 1981 00:00:00 GMT" />
<meta http-equiv="Pragma" content="no-cache" />
<meta http-equiv="Cache-Control" content="no-store" />
<meta http-equiv="Cache-Control" content="no-cache" />
<script src="/login/ajax/checkChosenLogin.js"></script><script src="/core/scripts/formUpload.js"></script>
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" -->
          <div  class="container pageBody members">
     <script>
<!--

function validateForm() { 
 var errors = "";

  
if (document.passwords.password.value == "") errors = errors + "Please enter a password.\n"; 

  if (document.passwords.password.value != document.passwords.password2.value) errors = errors + "The two new passwords you entered do not match.\n";
  return errors;
 
}
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


//-->
</script>
<div class="container pageBody">
      <h1><?php echo isset($row_rsLoggedIn['password']) ? "Update" : "Choose"; ?> your  password</h1>
      <h2>You are logged in as: <?php echo htmlentities($row_rsLoggedIn['username'], ENT_COMPAT, "UTF-8"); ?></h2>
     
      
     <?php require_once('../../core/includes/alert.inc.php'); ?>
      <?php if($row_rsLoggedIn['canchangepassword']==1 || $row_rsUser['changepassword']==1) {  // can or must
	  
	 $new =  isset($row_rsLoggedIn['password']) ? "new" : "";?>
      <form method="post" name="passwords" id="passwords" ><h3>Choose a <?php echo $new; ?> password...</h3>
        <table class="form-table">
          <tr class="form-group">
            <td class="text-right text-nowrap"><label for="password">Enter your <strong><?php echo $new; ?></strong> password:</label></td>
            <td><div class="input-group"><input name="password" type="password"  id="fb-password-field"  maxlength="50" onfocus = "document.getElementById('document.getElementById('passwordAlert').innerHTML=' ';document.getElementById('password2Alert').innerHTML=' ';"  onkeyup="checkLiveInput(event,'passwordAlert','password')"  autocomplete="off" class="form-control"  /><span class="input-group-btn">
        <button class="btn btn-default btn-secondary  toggle-password" type="button" toggle="#fb-password-field"><i class="glyphicon glyphicon-eye-open  "></i></button></span></div></td>
            <td><span id="passwordAlert">&nbsp;</span></td>
          </tr>
          <tr class="form-group">
            <td class="text-right text-nowrap"><label for="fb-password-field2">Re-type your <strong><?php echo $new; ?></strong> password:</label></td>
            <td><div class="input-group"><input name="password2" type="password"  id="fb-password-field2"  maxlength="50" onkeyup="checkLiveInput(event,'password2Alert','password')" autocomplete="off" class="form-control" /><span class="input-group-btn">
        <button class="btn btn-default btn-secondary  toggle-password" type="button" toggle="#fb-password-field2"><i class="glyphicon glyphicon-eye-open  "></i></button></span></div></td>
            <td><input type="hidden" name="plainpassword" id="plainpassword" /><span id="password2Alert">&nbsp;</span></td>
          </tr>
          <?php if(!isset($loggedInWithKey)) { ?>
          <tr>
            <td class="text-nowrap text-right top">&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
          </tr>
          <tr class="currentpassword form-group">
            <td class="text-right text-nowrap"><label for="currentpassword">For security, enter your <strong><abbr title="This will be the password you last used to log in - this may be a temporary password you were issued with">current</abbr></strong> password:</label></td>
            <td><input name="currentpassword" type="password"  id="currentpassword" value="<?php echo isset($_GET['currentpassword']) ? htmlentities($_GET['currentpassword']) : ""; ?>"  maxlength="50" autocomplete="off" class="form-control" /></td>
            <td>&nbsp;</td>
          </tr>
          <?php } ?>
          <tr>
            <td class="text-right text-nowrap" ><input name="modifiedbyID" type="hidden" id="modifiedbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
              <input name="modifieddatetime" type="hidden" id="modifieddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
            <input name="updatepassword" type="hidden" id="updatepassword" value="true"  />                <input name="ID" type="hidden" id="ID" value="<?php echo $row_rsLoggedIn['ID']; ?>" /></td>
            <td><button type="submit" class="btn btn-primary" >Change Password</button> <a href="/login/logout.php" class="btn btn-default" onClick="return confirm('Are you sure you want to log out?');"> Log Out</a></td>
            <td>&nbsp;</td>
          </tr>
        </table>
</form><?php } else { ?>
		<p class="alert alert-danger" role="alert">Sorry, you can not set your password at this time.</p>
	<?php } ?></div></div>
    <!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);
?>
