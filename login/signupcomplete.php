<?php require_once('../Connections/aquiescedb.php'); 
if(!isset($_GET['userID']) || !preg_match("/signup/i",$_SERVER['HTTP_REFERER']) || !isset($_GET['signup_token']) || md5($_GET['userID'].PRIVATE_KEY) != $_GET['signup_token']) {
	header("location: /"); exit;
} ?><?php require_once('../core/includes/framework.inc.php'); ?><?php
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

$regionID = (isset($regionID) && $regionID > 0) ? intval($regionID) : 1;

if (!isset($_SESSION)) {
  session_start();
}

$varUserID_rsUser = "-1";
if (isset($_GET['userID'])) {
  $varUserID_rsUser = $_GET['userID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsUser = sprintf("SELECT users.password, users.email, users.username, users.firstname, users.ID, users.usertypeID, users.surname, users.emailoptin FROM users WHERE users.ID = %s", GetSQLValueString($varUserID_rsUser, "int"));
$rsUser = mysql_query($query_rsUser, $aquiescedb) or die(mysql_error());
$row_rsUser = mysql_fetch_assoc($rsUser);
$totalRows_rsUser = mysql_num_rows($rsUser);

if($totalRows_rsUser ==0) die();

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT license_key, orgname, addressrequired, emailverify, manualverify, newuseralert, contactemail, welcomeemailID, userscanlogin FROM preferences WHERE ID =".intval($regionID);
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);


$varRegionID_rsRegion = "1";
if (isset($regionID)) {
  $varRegionID_rsRegion = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsRegion = sprintf("SELECT region.signupemailtext, region.title FROM region WHERE ID = %s", GetSQLValueString($varRegionID_rsRegion, "int"));
$rsRegion = mysql_query($query_rsRegion, $aquiescedb) or die(mysql_error());
$row_rsRegion = mysql_fetch_assoc($rsRegion);
$totalRows_rsRegion = mysql_num_rows($rsRegion);


mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsEmailTemplate = "SELECT * FROM groupemailtemplate WHERE ID = ".$row_rsPreferences['welcomeemailID']."";
$rsEmailTemplate = mysql_query($query_rsEmailTemplate, $aquiescedb) or die(mysql_error());
$row_rsEmailTemplate = mysql_fetch_assoc($rsEmailTemplate);
$totalRows_rsEmailTemplate = mysql_num_rows($rsEmailTemplate);
 
// send mail
$orgname = (isset($row_rsRegion['title']) && $row_rsRegion['title'] != "Default Site") ? $row_rsRegion['title'] : $row_rsPreferences['orgname']; 
$orgemail = $row_rsPreferences['contactemail'];
$key = md5($row_rsUser['username'].$row_rsPreferences['license_key']); // unique key generated from details

$subject = "Your ".$orgname." account\n\n";
$message = "Hello ".$row_rsUser['firstname']."!\n\n";
		$message .= (isset($row_rsRegion['signupemailtext'])) ? $row_rsRegion['signupemailtext']."\n\n" : "Welcome to ".$orgname."\n\nThank you for registering with us.\n\nRegards,\n\nThe ".$orgname." team\n\n";
if ($row_rsPreferences['manualverify'] != 1) { // auto verify	
	if ($row_rsPreferences['emailverify'] == 1) { // email verify
		$message .= "IMPORTANT INFORMATION:\n\n";
		$message .="In order to activate your account, you need to verify your email address by clicking on the link below. If the link does not work, copy and paste the whole link into your browser.\n\n";
		$message .= getProtocol()."://";
		$message .= $_SERVER['HTTP_HOST']."/members/signup/verify_email.php?username=".$row_rsUser['username']."&key=".$key;
		$message .= isset($_SESSION['PrevUrl']) ? "&PrevUrl=".urlencode($_SESSION['PrevUrl'])."\n\n" : "\n\n";
		if(isset($_SESSION['PrevUrl'])) { unset($_SESSION['PrevUrl']); }
	} else { // no email verify
		if($row_rsUser['usertypeID'] >0 && $row_rsPreferences['userscanlogin']==1) {
			$message .= "You can now log in to the members section of our web site.\n\n";
		}
	} // end no email verify
	if($row_rsUser['usertypeID'] >=0 && $row_rsPreferences['userscanlogin']==1) { // can potentially log in
		$message .= "Your username is: ".$row_rsUser['username']."\n\n";
		if (isset($_SESSION['newpassword'])) { 
		// auto generated password or chosen
			$message .= "Your password is: ".$_SESSION['newpassword']."\n\n"; 
			
		} else { // no password available
			$message.="You can log in with this and the password you chose.\n\n";
		} // end no password available
		$message .= "You can view and update your profile and change your password at any time using the link below:\n\n";
		$message .= getProtocol()."://";
		$message .= $_SERVER['HTTP_HOST']."/members/profile/";
	} // can log in
} else { // manually verify
	$message .= "Thank you for registering for ".$orgname."\n\nIf your membership application is successful we will respond in due course.\n\n";
} // end mailly verify

$to = $row_rsUser['email'];
 require_once('../mail/includes/sendmail.inc.php');

if($row_rsPreferences['welcomeemailID']==0) {		
	sendMail($to,$subject,$message,$orgemail,$orgname); 
} else if($row_rsEmailTemplate['ID']>0) {
	sendMail($to,$subject,$message,$orgemail,$orgname,$row_rsEmailTemplate['templateHTML'],$attachments="",$log=false,$cc="",$bcc="",$row_rsEmailTemplate['templatehead'],$row_rsEmailTemplate['ID'],$unsubscribelink=true,$merge=true);
}

if ($row_rsPreferences['newuseralert'] && $orgemail!=="") { //new user alert
$to =  $orgemail ;
$subject = $site_name." web site sign up";
$message = $row_rsUser['firstname']." ".$row_rsUser['surname']." has signed up to your web site.\n\nThey have chosen to ";
$message .= ($row_rsUser['emailoptin']==1) ? "OPT IN to" : "OPT OUT of";
$message .= " site emails.\n\n";
$message .="This message is for information only. No action required.\n\n";
$message .= "You can view and update their details at: ";
$message .= getProtocol()."://"; 
$message .= $_SERVER['HTTP_HOST']."/members/admin/modify_user.php?userID=".intval($_GET['userID']);
$from = $row_rsUser['email'];
$friendlyfrom = $row_rsUser['firstname']." ".$row_rsUser['surname'];
require_once('../mail/includes/sendmail.inc.php'); 
sendMail($to,$subject,$message,$from,$friendlyfrom);

}

if ($row_rsPreferences['emailverify'] != 1 && $row_rsPreferences['manualverify']!= 1 && $row_rsUser['usertypeID']>=-1) // no need to verify email, so log user in...
{
	// this code below must be the same as verify email page code....
	$_SESSION['MM_Username'] = $row_rsUser['username'];
	$_SESSION['MM_UserGroup'] = $row_rsUser['usertypeID'];	
	// otherwise redirect to either previous URL or members page
	if(!isset($_SESSION['PrevUrl'])) {	
		if($row_rsUser['usertypeID']>=1 && $row_rsPreferences['userscanlogin'] ) {	
			header("location: /members/index.php?newuser=true");	exit;
		} else {
			header("location: /");	exit;
		}
	}		
}

if(isset($_SESSION['PrevUrl'])) {
	$redirectURL =  $_SESSION['PrevUrl']; unset($_SESSION['PrevUrl']);
	header("location: ".$redirectURL); exit;
} 

if(isset($_SESSION['newpassword'])) unset($_SESSION['newpassword']);



?><?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Confirmation"; echo $pageTitle." | ".$site_name; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" --><meta name="robots" content="noindex,nofollow" /><!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" -->
      <section>
          <div  class="container pageBody login">
   <div class="crumbs"><div><span class="you_are_in">You are in: </span><a href="/">Home</a><span class="separator">&nbsp;&rsaquo;&nbsp;</span><a href="/login/">Log in</a><span class="separator">&nbsp;&rsaquo;&nbsp;</span>Email Password</div></div>
	  <h1>Success! One more thing...</h1>
	   <p>Your confirmation has been emailed to your registered email address: <strong><?php echo $row_rsUser['email']; ?></strong></p>
		<h3><strong>IMPORTANT:</strong></h3>
		<p>Your account will not be active until you click on the activate link within this email.</p>
 

      
	  
    <h3>Didn’t receive the email?</h3>
      <ul class="bullets">
        <li>First, be patient, sometimes it can take a while for the email to arrive.</li>
        <li>Check your junk email box, the message might have been filtered as spam.</li>
        <li>Check above to ensure you entered your email address correctly. If it's wrong, please <a href="signup.php" >register again</a>.</li>
        
        <li><a href="../contact/index.php"> Contact us</a> if you still can't get it to work and we can activate your account for you.</li>
      </ul>
     </div></section>
    <!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php

mysql_free_result($rsPreferences);

mysql_free_result($rsRegion);

mysql_free_result($rsEmailTemplate);

mysql_free_result($rsUser);
?>
