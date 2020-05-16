<?php require_once('../Connections/aquiescedb.php'); 

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
?>
<?php
// *** Validate request to login to this site.
if (!isset($_SESSION)) {
  session_start();
}

if(isset($_GET['logout'])) {
	unset($_SESSION);
}

$loginFormAction = $_SERVER['PHP_SELF'];
if (isset($_GET['accesscheck'])) {
  $_SESSION['PrevUrl'] = $_GET['accesscheck'];
}

$secure = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == "on") ? true : false;	

if (isset($_POST['username'])) {
	if($_POST['login_token']!=$_COOKIE['login_token']) die("Bad token".$_POST['login_token'].":".$_COOKIE['login_token']);
	
	
	setcookie('login_token', '', time()-42000, '/','',$secure, true);
  $loginUsername=$_POST['username'];
  $MM_fldUserAuthorization = "usertypeID";
  $MM_redirectLoginSuccess = "index.php";
 $password = md5($_POST['password']);
  $MM_redirecttoReferrer = true;
  mysql_select_db($database_aquiescedb, $aquiescedb);
  
  $result = mysql_query("SHOW COLUMNS FROM `users` LIKE 'password_salt'");
$exists = (mysql_num_rows($result))?TRUE:FALSE;
	if($exists) {
   		$LoginRS__query="SELECT username, password, usertypeID FROM users WHERE username=".GetSQLValueString($loginUsername, "text")." AND password=MD5(CONCAT(".GetSQLValueString($_POST['password'], "text").",COALESCE(`password_salt`,'')))";
   
   // COALESCE makes null = '' - required for CONCAT
	} else {
  		$LoginRS__query=sprintf("SELECT username, password, usertypeID FROM users WHERE username=%s AND password=%s",
  GetSQLValueString($loginUsername, "text"), GetSQLValueString($password, "text")); 
	}
  	$LoginRS = mysql_query($LoginRS__query, $aquiescedb) or die(mysql_error());
   
  $loginFoundUser = mysql_num_rows($LoginRS);
  
 // die( $LoginRS__query."=".$loginFoundUser);
 
  if ($loginFoundUser) { 	  
    $loginStrGroup  = mysql_result($LoginRS,0,'usertypeID');    
	session_regenerate_id();
    //declare two session variables and assign them
    $_SESSION['MM_Username'] = $loginUsername;
    $_SESSION['MM_UserGroup'] = $loginStrGroup;	      

    if (isset($_SESSION['PrevUrl']) && true) {
      $MM_redirectLoginSuccess = $_SESSION['PrevUrl'];	
    }
    header("Location: " . $MM_redirectLoginSuccess );
  }
  else {
    $submit_error = "Sorry you have entered the wrong username and password.";
  }
} 
	
	
	if(function_exists("openssl_random_pseudo_bytes")) {
		$login_token = bin2hex(openssl_random_pseudo_bytes(16));
	} else {
		$login_token = md5(uniqid(rand(), true));
	}
	setcookie("login_token", $login_token, time() + 60 * 60 * 24,"/","", $secure, true);



?>
<!DOCTYPE html>
<!-- Copyright Paul Egan. Any unauthorised copying, reproduction or alteration is strictly prohibited -->
<html lang="en" class="full_bhuna install <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Install.dwt.php" codeOutsideHTMLIsLocked="false" --><!-- Copyright Paul Egan. Any unauthorised copying, reproduction or alteration is strictly prohibited -->
<head>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Install Full Bhuna - Log In</title>
<!-- InstanceEndEditable -->
<?php require_once('includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceEndEditable -->
</head>
<body>
<?php require_once('includes/header.inc.php'); ?>
<main>
<div class="container"><!-- InstanceBeginEditable name="Body" -->
  <h1>Login Required</h1><?php if (isset($submit_error)) { ?><p class="alert alert-danger" role="alert"><?php echo $submit_error; ?></p>
  <?php } ?>
  <form action="<?php echo $loginFormAction; ?>" method="post" id="form1" class="form-horizontal">
   
      <div class="form-group">
        <label for="username" class="col-md-2">Username:</label>
        <div class="col-md-4">
          <input name="username" type="text" id="username" size="20" maxlength="50" value="<?php echo isset($_REQUEST['username']) ? htmlentities($_REQUEST['username']) : "";?>"  class="form-control"/></div></div>
      <div class="form-group">
        <label for="password" class="col-md-2">Password:</label>
           <div class="col-md-4"><input name="password" type="password" id="password" value="" size="20" maxlength="50"  class="form-control"/></div>
      </div>
     <div class="form-group">
      <div class="col-md-offset-2 col-md-4">
        <button type="submit" class="btn btn-primary" >Log in...</button><input type="hidden" name="login_token" id="login_token" value="<?php echo $login_token; ?>" /></div></div>
  </form>
 
  <!-- InstanceEndEditable --></div>
</main>
<?php require_once('includes/footer.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>