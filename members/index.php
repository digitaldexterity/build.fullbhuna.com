<?php require_once('../Connections/aquiescedb.php'); ?>
<?php require_once('../core/includes/sslcheck.inc.php'); ?><?php require_once('includes/userfunctions.inc.php'); ?>
<?php

$regionID = (isset($regionID ) && intval($regionID)  >0) ? intval($regionID) : 1;
if (!isset($_SESSION)) {
  session_start();
  session_regenerate_id();
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

$MM_restrictGoTo = "../login/index.php";
if (!((isset($_SESSION['MM_Username'])) && (isAuthorized("",$MM_authorizedUsers, $_SESSION['MM_Username'], $_SESSION['MM_UserGroup'])))) {   
  $MM_qsChar = "?";
  $MM_referrer = $_SERVER['PHP_SELF'];
  if (strpos($MM_restrictGoTo, "?")) $MM_qsChar = "&";
  if (isset($_SERVER['QUERY_STRING']) && strlen($_SERVER['QUERY_STRING']) > 0) 
  $MM_referrer .= "?" . $_SERVER['QUERY_STRING'];
  $MM_restrictGoTo = $MM_restrictGoTo. $MM_qsChar . "accesscheck=" . urlencode($MM_referrer);
  session_write_close();
  header("Location: ". $MM_restrictGoTo); exit;
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

if(isset($_GET['groupID']) && intval($_GET['groupID']>0) && isset($_GET['groupkey']) && md5($_GET['groupID'].PRIVATE_KEY) == $_GET['groupkey']) {
	// add to group
	addUsertoGroup(thisUser(), $_GET['groupID'],thisUser());	
}

$varUsername_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $varUsername_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT users.usertypeID, users.firstname, users.username, usertype.name, users.ID, users.aboutme, users.termsagree, users.changepassword, users.canchangepassword, users.updateprofile, COUNT(directoryuser.ID) AS directoryusers, directory.userID AS directoryuser FROM users LEFT JOIN usertype ON (users.usertypeID = usertype.ID) LEFT JOIN directoryuser ON (directoryuser.userID = users.ID) LEFT JOIN directory ON directory.userID = users.ID WHERE username = %s GROUP BY (users.ID)", GetSQLValueString($varUsername_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);



mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT * FROM preferences WHERE ID=".$regionID;
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);


if($row_rsPreferences['askcompanydetails']==1 && $row_rsLoggedIn['directoryusers'] ==0 && !isset($row_rsLoggedIn['directoryuser'])) {
	$url = "/directory/members/add_directory.php?addentry=true";
	$url .="&returnURL=".urlencode("/members/");
	header("location: ".$url); exit;
}
?>
<?php if (isset($row_rsPreferences['memberspageURL'])) { 
$url = $row_rsPreferences['memberspageURL'];
$query = strpos($url,"?") ? "&" : "?";
$url .= (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING']!="")  ? $query.$_SERVER['QUERY_STRING'] : "";
header("Location: ".$url);exit; } ?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Member Home Page"; echo $pageTitle." | ".$site_name; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<meta name="robots" content="noindex,nofollow" />
<style><!--
<?php if($row_rsLoggedIn['canchangepassword']==0) {  echo "password { display: none;"; } ?>
--></style>
<link href="css/membersDefault.css" rel="stylesheet"  />
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" -->
    <div class="container pageBody members">
    <h1>Good
      <?php if (date('H') <12) echo "morning"; else  if (date('H') <17) echo "afternoon"; else echo "evening"; echo ", ".htmlentities($row_rsLoggedIn['firstname'], ENT_COMPAT, "UTF-8"); ?>...</h1>
    <h2>Welcome to the members section.</h2>
     <ul class="memberbuttons">
        
        <li><a href="/members/profile/update_profile.php" class="profile" title="Keep other members informed of who you are">My profile </a> </li>
        <li ><a href="/members/profile/contact_addresses.php" class="mail" title="Add one or more addresses so we can contact you">My Addresses</a> </li><?php if ($row_rsPreferences['memberdirectory']==1) { ?>
        <li ><a href="/directory/members/index.php" class="info" title="Add details of any organisations(s) you are affliated with">My Organisations</a> </li>
        <li ><a href="/members/directory/index.php" class="group" title="View contact details for other members of the site">Member Directory</a> </li> <?php } ?>
      <li><a  class="documents" href="/documents/index.php">Documents</a></li>
         <li class="forum"><a href="/forum/index.php" class="forum">Forum</a></li>
        <li class="news"><a href="/news/members/add_news.php" class="news">Post news</a></li><?php if ($row_rsLoggedIn['usertypeID'] >= 8) { ?>
    <li><a href="/core/admin/" target="_blank" class="admin" rel="noopener">Control Panel</a> </li>
    <?php } ?>
      <li><a href="/login/logout.php" class="login"  onclick="return logOutCheck();">Log Out</a></li>
      
        
        
    </ul>
    
   
  
       
	
	
	
	<?php if(is_readable('../directory/members/includes/myorganisations.inc.php')) {
		require_once('../directory/members/includes/myorganisations.inc.php');  }?>
      <?php if(is_readable('../calendar/members/includes/registrations.inc.php')) {
		  require_once('../calendar/members/includes/registrations.inc.php');
	  } ?>
    <h2> Alerts:</h2>
    <?php if(isset($_GET['changed_password'])) { ?>
    <p>Congratulations, you have successfully changed your password! </p>
    <?php } ?>
    <?php  if ($row_rsLoggedIn['aboutme'] =="") {  ?>
    <p>You can let others know more about you in your user profile. &nbsp;<a href="profile/update_profile.php">Add one here</a>.</p>
    <?php } ?>
			 
			
    <p><a href="../login/logout.php" class="link_back"  onclick="return logOutCheck();"><i class="glyphicon glyphicon-arrow-left"></i>  Log out</a></p></div>
    <!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);



mysql_free_result($rsPreferences);

?>
