<?php require_once('../Connections/aquiescedb.php'); ?><?php require_once('../core/includes/sslcheck.inc.php');

header('Referrer-Policy: origin-when-cross-origin');
header("X-XSS-Protection: 0");
header('X-Frame-Options: SAMEORIGIN');


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

$regionID = isset($regionID) ? $regionID : 1;

$loginFormAction = $_SERVER["PHP_SELF"];

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT * FROM preferences WHERE ID = ".intval($regionID);
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);

$_GET['stayloggedin'] = isset($_GET['stayloggedin']) ? $_GET['stayloggedin'] : 0;
$body_class="login";
?><?php require_once("includes/login.inc.php"); ?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Login"; $pageTitle .= isset($_REQUEST['username']) ? " retry by ".htmlentities($_REQUEST['username']) : ""; echo $pageTitle." | ".$site_name; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<meta http-equiv="Expires" content="Fri, Jun 12 1981 00:00:00 GMT" />
<meta http-equiv="Pragma" content="no-cache" />
<meta http-equiv="Cache-Control" content="no-store" />
<meta http-equiv="Cache-Control" content="no-cache" />
<meta name="robots" content="noindex,nofollow" />
<link href="css/defaultLogin.css" rel="stylesheet"  />
<script src="scripts/jscapslock.js"></script>
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" --><section><div id= "pageLogin" class="container pageBody">
 
      

    <?php if(isset($_GET['notloggedin'])) {
		$msg = "You need to log in to access this page.";
	} else if(isset($_GET['badlogin'])) { ?>
  <div class="alert alert-danger" role="alert">
  <?php if(isset($row_rsPreferences['text_loginfail']) && $row_rsPreferences['text_loginfail']!="") { 
  echo $row_rsPreferences['text_loginfail']; 
  } else { ?>
      <p>Sorry, the login details you provided do not allow access to this part of the site.<br />
        <br />
        IMPORTANT:</p>
      <ul>
        <li> Passwords are case sensistive. Ensure Caps Lock is off.</li>
        <li> If you have forgotten your password <a href="/login/forgot_password.php" >click here</a>.</li>
        <li> Check you are authorised to view the page. If you have recently signed up, your account may not yet be activated. Please check your email for a confirmation and directions.</li>
      </ul><?php } ?>
    </div>
 <?php  }   ?>
 <?php require_once(SITE_ROOT.'core/includes/alert.inc.php'); // for alerts  ?>
    <div class="row row-equal-height-md flex" id="loginBoxes">
    <div class="col-md-6" >
      <div id="loginBox">
        <h1><?php echo isset($row_rsPreferences['text_existingusers']) ? $row_rsPreferences['text_existingusers'] : 'Existing users...'; ?></h1>
       
          <?php  if (isset($_GET['changed_password'])) {?>
          <p class="alert alert-danger" role="alert"><?php echo isset($row_rsPreferences['text_newpassword']) ? $row_rsPreferences['text_newpassword'] : 'You have successfully changed your password. <br />
            Please log in again with your new password.'; ?></p>
          <?php } else if (isset($_GET['email_verify'])) {?>
          <p class="alert warning alert-warning" role="alert"><?php echo isset($row_rsPreferences['text_emailverified']) ? $row_rsPreferences['text_emailverified'] : 'You have succesfully verified your email address. Now log in to continue with your chosen username and password.'; ?></p>
          
          <?php } else if (isset($_GET['loggedout'])) {?>
          <p class="alert alert-danger" role="alert"><?php echo isset($row_rsPreferences['text_loggedout']) ? $row_rsPreferences['text_loggedout'] : 'You have successfully logged out. Log in again below.'; ?></p>
          <?php } else if (isset($_GET['alert'])) {?>
          <p class="alert alert-danger" role="alert"><?php echo htmlentities($_GET['alert']); ?></p>
          <?php } else { ?>
          <h2><?php echo isset($row_rsPreferences['text_loginnow']) ? $row_rsPreferences['text_loginnow'] : 'Log in to continue:'; ?></h2>
          <?php } ?> <?php require_once('includes/loginform.inc.php'); ?>
           <div id="loginTips">
    <?php if (isset($row_rsPreferences['text_logintips'])) { echo $row_rsPreferences['text_logintips']; }  ?>
    
    </div>
        
      </div> </div>  <div  class="col-md-6" >
      <?php if ($row_rsPreferences['userscansignup']==1) { ?>
    
       <div   id="signUpBox">
        <?php if (is_readable("../local/includes/loginjoin.inc.php")) { require_once("../local/includes/loginjoin.inc.php"); } else if(isset($row_rsPreferences['text_registerinfo']) && $row_rsPreferences['text_registerinfo']!="") { 
		echo $row_rsPreferences['text_registerinfo'];
		} else { ?>
        <h1>New users...</h1>
       
        <h2>
        
          Please register to continue...
         
         
        </h2>
        <p>It's quick, easy and best of all free!</p>
        <p><a href="signup.php" class="btn btn-default btn-secondary" >Register here</a></p>
        <h2>Registration Benefits</h2>
        <p>As a registered user you will benefit from: </p>
        <ul>
          <li>We'll remember your details for each visit</li>
          <li>A personal page with site tools</li>
          <li>You can keep your own profile up to date</li>
        </ul>
        <?php } ?>
      </div> 
      <?php } ?> <?php if(defined("WEB_DEV_LOGIN_STRAP")) { echo WEB_DEV_LOGIN_STRAP; } 
	 ?>
   </div>
   </div></div></section><script>document.getElementById('username').focus();//alert(document.cookie);
      </script>
    <!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsPreferences);
?>
