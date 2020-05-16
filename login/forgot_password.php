<?php require_once('../Connections/aquiescedb.php');?>
<?php require_once('../mail/includes/sendmail.inc.php'); ?>
<?php $body_class="login";

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
$error = "";

if(isset($_GET['resetpassword'])) {
	
	$varUsername_rsUser = "-1";
	if (isset($_GET['username'])) {
	  $varUsername_rsUser = $_GET['username'];
	}
	$varRegionID_rsUser = "0";
	if (isset($regionID)) {
	  $varRegionID_rsUser = $regionID;
	}
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$query_rsUser = sprintf("SELECT users.password, users.plainpassword, users.email, users.username, users.usertypeID, users.firstname, users.ID, users.surname, users.emailverified, users.canchangepassword FROM users WHERE usertypeID >= 0 AND (email = %s OR username = %s) AND (regionID = %s OR %s = 0 OR regionID IS NULL OR regionID =0) ORDER BY usertypeID DESC LIMIT 1", GetSQLValueString($varUsername_rsUser, "text"),GetSQLValueString($varUsername_rsUser, "text"),GetSQLValueString($varRegionID_rsUser, "int"),GetSQLValueString($varRegionID_rsUser, "int"));
	$rsUser = mysql_query($query_rsUser, $aquiescedb) or die(mysql_error());
	$row_rsUser = mysql_fetch_assoc($rsUser);
	$totalRows_rsUser = mysql_num_rows($rsUser);
	if($totalRows_rsUser>0) { // valid user found 
		if($row_rsUser['firstname'] =="" && $row_rsUser['surname'] == "" && (!isset($_GET['firstname']) || $_GET['firstname']=="")) { // no  name
			$noname = true;
		} else { // has name
			if(isset($_GET['firstname']) && $_GET['firstname']!="") {
				$update = "UPDATE  users SET firstname = ".GetSQLValueString($_GET['firstname'], "text").", surname = ".GetSQLValueString(trim($_GET['surname']), "text")." WHERE ID = ".$row_rsUser['ID'];
	 $result = mysql_query($update, $aquiescedb) or die(mysql_error());
	 			$firstname = $_GET['firstname'];	
			} else {
				$firstname = isset($row_rsUser['firstname']) ? $row_rsUser['firstname'] : "User";	
			}
			if(!isset($row_rsUser['username']) || $row_rsUser['username'] == "") { // no username exists so insert email
				$update = "UPDATE users SET usertypeID = 1, username = ".GetSQLValueString($row_rsUser['email'],"text")." WHERE ID = ".GetSQLValueString($row_rsUser['ID'],"int");
				$result = mysql_query($update, $aquiescedb) or die(mysql_error());
				$username = $row_rsUser['email'];
			} else {
				$username = $row_rsUser['username'];
			}
			if($row_rsUser['usertypeID']<7 && $userPreferences['userscanlogin']!=1) { 
				$error .= "Sorry, users are currently not permitted to log into the  site.";
			} else if (isset($row_rsUser['email'])) { // got an email address 
				if(isset($_SESSION['PrevUrl'])) {
					$returnURL = $_SESSION['PrevUrl'];
					unset($_SESSION['PrevUrl']);
				} else { 
					$returnURL= "";
				}
				
				sendPasswordResetEmail($row_rsUser['ID'], $returnURL);
				
				
			
				
				$emailsent = true;
			} else { //  has no email 
				$error .= "Sorry, there is no email address associated with your username. Please contact the site administrator for help. ";
			}
		} // end has name
	} else { // no user found
		$error .= "Sorry we cannot find a user with the username or email: ".htmlentities($_GET['username'], ENT_COMPAT, "UTF-8");
	}
}?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Forgotten Password"; echo $pageTitle." | ".$site_name; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<meta name="robots" content="noindex,nofollow" /><script src="../SpryAssets/SpryValidationTextField.js"></script>
<link href="../SpryAssets/SpryValidationTextField.css" rel="stylesheet"  />
<style >
<!--
<?php if(!isset($noname)) { echo "#fullname { display: none; }";} ?>
-->
</style>
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" -->
    <p>&nbsp;</p>
    <section>
          <div  class="container pageBody login">
     <div class="crumbs"><div><span class="you_are_in">You are in: </span><a href="/">Home</a><span class="separator">&nbsp;&rsaquo;&nbsp;</span><a href="/login/">Log in</a><span class="separator">&nbsp;&rsaquo;&nbsp;</span>Forgotten Password</div></div><?php require_once('../core/includes/alert.inc.php'); ?>
     <?php if(isset($emailsent)){ ?>
     <h1>Password Reset</h1>
     <p>You should  shortly receive an email with instructions on how to reset your password.</p>
   
    <h2>Didn&#8217;t get the email?</h2>
    <ul>
      <li>First, be patient, sometimes it takes a short time for the email to arrive.</li>
      <li>Ensure you entered your email address correctly.</li>
      <li>Check your junk email box, the message might have been filtered as junk.</li>
      <li><a href="../contact/index.php">Contact us</a> if you still can't get it to work and we'll resend your email.</li>
    </ul>
    
    <a href="/login/"  class="btn btn-default btn-secondary">Back to Log in</a>
    
     <?php } else { ?>
      <form method="get" name="form1" id="form1">
  <h1>Not sure of  your log in details?</h1>
  <div class="form-group">
      <p><label for="username">We should have your email address on file linked with your user profile. Enter it below and we'll send you a link to reset your password:</label></p>
     
        
      
          <span id="sprytextfield1">
            <input name="username" type="text"  id="username" value="<?php echo isset($_REQUEST['username']) ? htmlentities($_REQUEST['username']) : ""; ?>" size="60" maxlength="100" autocomplete="off" class="form-control" />
            <span class="textfieldRequiredMsg">A value is required.</span></span></div>
            <div id="fullname">
       <p class="alert warning alert-warning" role="alert">We have found your email address on the system, but we do not have your name. Please enter below:</p>
       <span id="sprytextfield2">
          <label>
            <input name="firstname" type="text"  id="firstname" size="30" maxlength="50" />
            </label>
  </span><span id="sprytextfield3">
    <label>
      <input name="surname" type="text"  id="surname" size="30" maxlength="50" />
      </label>
</span></div>
    <input name="returnURL" type="hidden" id="returnURL" value="<?php echo isset($_GET['returnURL']) ? htmlentities($_GET['returnURL']) : ""; ?>" /><input name="resetpassword" type="hidden" id="resetpassword" value="true" />          <button type="submit" class="btn btn-primary">Reset my password</button> <a href="/login/"  class="btn btn-default btn-secondary">Back to Log in</a>
      </form>
      <?php } // end email not sent yet ?>
      <p>&nbsp;</p>
    <script>
<!--
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1", "none", {hint:"Enter your email or username"});
var sprytextfield2 = new Spry.Widget.ValidationTextField("sprytextfield2", "none", {isRequired:false, hint:"First name"});
var sprytextfield3 = new Spry.Widget.ValidationTextField("sprytextfield3", "none", {isRequired:false, hint:"Surname"});
//-->
    </script>
  </div></section>
  <!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
