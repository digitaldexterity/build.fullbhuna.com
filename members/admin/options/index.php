<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php require_once('../../../core/includes/adminAccess.inc.php'); ?>
<?php require_once('../../includes/userfunctions.inc.php'); ?>
<?php require_once('../../../core/includes/framework.inc.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "9,10";
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

$MM_restrictGoTo = "../../../login/index.php?notloggedin=true";
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

$regionID = (isset($regionID) && $regionID>0) ? intval($regionID) : 1;

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if(isset($_POST['passwordencrypted'])) { // delete all unencrypred passwords
$update = "UPDATE users SET plainpassword = NULL";
 mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($update, $aquiescedb) or die(mysql_error());

}

$_POST['deletedataperiod'] = (isset($_POST['deletedataperiod']) && intval($_POST['deletedataperiod'])>0) ? intval($_POST['deletedataperiod']*604800) : 0;

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE preferences SET userscansignup=%s, userscanlogin=%s, stayloggedin=%s, passwordmulticase=%s, passwordnumber=%s, passwordspecialchar=%s, multisitesignup=%s, addressrequired=%s, passwordencrypted=%s, defaultcrypttype=%s, emailasusername=%s, askdateofbirth=%s, minimumage=%s, emailverify=%s, emailoptintype=%s, emailoptinset=%s, emailoptintext=%s, partneremailoptintype=%s, partneremailoptinset=%s, partneremailoptintext=%s, manualverify=%s, securityletters=%s, memberdirectory=%s, memberdirectoryemail=%s, memberdirectoryname=%s, registertext=%s, logintext=%s, logouttext=%s, membernetwork=%s, memberpubliclocation=%s, newuseralert=%s, userupdatealert=%s, welcomeemailID=%s, communityguidelines=%s, askmiddlename=%s, askmiddleprofile=%s, askhowdiscovered=%s, askethnicity=%s, askgender=%s, askdisability=%s, asktelephone=%s, askmobile=%s, askjobtitle=%s, asktwitter=%s, askfacebook=%s, askwebsiteURL=%s, askhowdiscoveredprofile=%s, askethnicityprofile=%s, askgenderprofile=%s, askdisabilityprofile=%s, asktelephoneprofile=%s, askmobileprofile=%s, askjobtitleprofile=%s, asktwitterprofile=%s, askfacebookprofile=%s, askwebsiteURLprofile=%s, askhowdiscoveredcompulsary=%s, askethnicitycompulsary=%s, askgendercompulsary=%s, askdisabilitycompulsary=%s, asktelephonecompulsary=%s, askmobilecompulsary=%s, askjobtitlecompulsary=%s, asktwittercompulsary=%s, askfacebookcompulsary=%s, askwebsiteURLcompulsary=%s, askaboutmeprofile=%s, askaboutmecompulsary=%s, askphotoprofile=%s, askphotocompulsary=%s, askcompanydetails=%s, usesalutation=%s, loginattempts=%s, autousername=%s, memberspageURL=%s, usercontactform=%s, text_existingusers=%s, text_loggedout=%s, text_loginnow=%s, text_newpassword=%s, text_emailverified=%s, text_username=%s, text_password=%s, text_middlename=%s, text_retypepassword=%s, text_choosepassword=%s, text_stayloggedin=%s, text_forgotpass=%s, text_logintips=%s, text_loginfail=%s, text_registerinfo=%s, text_signupinfo=%s, text_salutation=%s, text_firstname=%s, text_surname=%s, text_email=%s, user_list_email=%s, user_list_phone=%s, user_list_mobile=%s, user_page_tabs=%s, captcha_login=%s, captcha_type=%s, recaptcha_site_key=%s, recaptcha_secret_key=%s, text_address_book=%s, groupoptintext=%s, text_role=%s, deletedatausertypeID=%s, deletedataperiod=%s, text_signup1=%s, text_signup2=%s WHERE ID=%s",
                       GetSQLValueString(isset($_POST['usercansignup']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['userscanlogin']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['stayloggedin'], "int"),
                       GetSQLValueString(isset($_POST['passwordmulticase']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['passwordnumber']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['passwordspecialchar']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['multisitesignup']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['addressrequired'], "int"),
                       GetSQLValueString(isset($_POST['passwordencrypted']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['defaultcrypttype'], "int"),
                       GetSQLValueString(isset($_POST['emailasusername']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['askdateofbirth'], "int"),
                       GetSQLValueString($_POST['minimumage'], "int"),
                       GetSQLValueString(isset($_POST['emailverify']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['emailoptintype'], "int"),
                       GetSQLValueString(isset($_POST['emailoptinset']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['emailoptintext'], "text"),
                       GetSQLValueString($_POST['partneremailoptintype'], "int"),
                       GetSQLValueString(isset($_POST['partneremailoptinset']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['partneremailoptintext'], "text"),
                       GetSQLValueString(isset($_POST['manualverify']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['usesecurityletters']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['memberdirectory']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['memberdirectoryemail']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['memberdirectoryname'], "text"),
                       GetSQLValueString($_POST['registertext'], "text"),
                       GetSQLValueString($_POST['logintext'], "text"),
                       GetSQLValueString($_POST['logouttext'], "text"),
                       GetSQLValueString(isset($_POST['membernetwork']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['memberprofilelocation']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['newuseralert']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['userupdatealert']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['welcomeemailID'], "int"),
                       GetSQLValueString(isset($_POST['communityguidelines']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['askmiddlename']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['askmiddleprofile']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['askhowdiscovered']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['askethnicity']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['askgender']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['askdisability']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['asktelephone']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['askmobile']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['askjobtitle']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['asktwitter']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['askfacebook']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['askwebsiteURL']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['askhowdiscoveredprofile']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['askethnicityprofile']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['askgenderprofile']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['askdisabilityprofile']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['asktelephoneprofile']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['askmobileprofile']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['askjobtitleprofile']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['asktwitterprofile']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['askfacebookprofile']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['askwebsiteURLprofile']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['askhowdiscoveredcompulsary']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['askethnicitycompulsary']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['askgendercompulsary']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['askdisabilitycompulsary']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['asktelephonecompulsary']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['askmobilecompulsary']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['askjobtitlecompulsary']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['asktwittercompulsary']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['askfacebookcompulsary']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['askwebsiteURLcompulsary']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['askaboutmeprofile']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['askaboutmecompulsary']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['askphotoprofile']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['askphotocompulsary']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['askcompanydetails']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['usesalutation']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['loginattempts'], "int"),
                       GetSQLValueString(isset($_POST['autousername']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['memberspageURL'], "text"),
                       GetSQLValueString(isset($_POST['usercontactform']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['text_existingusers'], "text"),
                       GetSQLValueString($_POST['text_loggedout'], "text"),
                       GetSQLValueString($_POST['text_loginnow'], "text"),
                       GetSQLValueString($_POST['text_newpassword'], "text"),
                       GetSQLValueString($_POST['text_emailverified'], "text"),
                       GetSQLValueString($_POST['text_username'], "text"),
                       GetSQLValueString($_POST['text_password'], "text"),
                       GetSQLValueString($_POST['text_middlename'], "text"),
                       GetSQLValueString($_POST['text_retypepassword'], "text"),
                       GetSQLValueString($_POST['text_choosepassword'], "text"),
                       GetSQLValueString($_POST['text_stayloggedin'], "text"),
                       GetSQLValueString($_POST['text_forgotpass'], "text"),
                       GetSQLValueString($_POST['text_logintips'], "text"),
                       GetSQLValueString($_POST['text_loginfail'], "text"),
                       GetSQLValueString($_POST['text_registerinfo'], "text"),
                       GetSQLValueString($_POST['text_signupinfo'], "text"),
                       GetSQLValueString($_POST['text_salutation'], "text"),
                       GetSQLValueString($_POST['text_firstname'], "text"),
                       GetSQLValueString($_POST['text_surname'], "text"),
                       GetSQLValueString($_POST['text_email'], "text"),
                       GetSQLValueString(isset($_POST['user_list_email']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['user_list_phone']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['user_list_mobile']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['user_page_tabs']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['captcha_login']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['captcha_type'], "int"),
                       GetSQLValueString($_POST['recaptcha_site_key'], "text"),
                       GetSQLValueString($_POST['recaptcha_secret_key'], "text"),
                       GetSQLValueString($_POST['text_address_book'], "text"),
                       GetSQLValueString($_POST['groupoptintext'], "text"),
                       GetSQLValueString($_POST['text_role'], "text"),
                       GetSQLValueString($_POST['deletedatausertypeID'], "int"),
                       GetSQLValueString($_POST['deletedataperiod'], "int"),
                       GetSQLValueString($_POST['text_signup1'], "text"),
                       GetSQLValueString($_POST['text_signup2'], "text"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateGoTo = "../index.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo));exit;
}

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT * FROM preferences WHERE ID = ".$regionID."";
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsUserTypes = "SELECT * FROM usertype WHERE ID >= 0 ORDER BY ID ASC";
$rsUserTypes = mysql_query($query_rsUserTypes, $aquiescedb) or die(mysql_error());
$row_rsUserTypes = mysql_fetch_assoc($rsUserTypes);
$totalRows_rsUserTypes = mysql_num_rows($rsUserTypes);

$varRegionID_rsEmailTemplates = "1";
if (isset($regionID)) {
  $varRegionID_rsEmailTemplates = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsEmailTemplates = sprintf("SELECT ID, templatename FROM groupemailtemplate WHERE statusID = 1 AND (regionID = 0 OR regionID = %s) ORDER BY templatename ASC", GetSQLValueString($varRegionID_rsEmailTemplates, "int"));
$rsEmailTemplates = mysql_query($query_rsEmailTemplates, $aquiescedb) or die(mysql_error());
$row_rsEmailTemplates = mysql_fetch_assoc($rsEmailTemplates);
$totalRows_rsEmailTemplates = mysql_num_rows($rsEmailTemplates);




if($totalRows_rsPreferences==0) {
	duplicateMySQLRecord ("preferences", 1, "ID", $regionID) ;
	header("location: index.php"); exit;
}

?><?php require_once('../../../core/tinymce/tinymce.inc.php'); ?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" --><title><?php $pageTitle = "User Options"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title><!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script src="../../../SpryAssets/SpryTabbedPanels.js"></script>
<link href="../../../SpryAssets/SpryTabbedPanels.css" rel="stylesheet"  />
<script >
$(document).ready(function(e) {
	toggleMemberDirectory();
	toggleRecaptcha();
    
});
function toggleMemberDirectory() {
	if(document.getElementById('memberdirectory').checked) {
		document.getElementById('memberdirectoryoptions').style.display = 'block';
	} else {
		document.getElementById('memberdirectoryoptions').style.display = 'none';
	}
}

function  toggleRecaptcha() {
	if(document.form1.captcha_type.value==2 || document.form1.captcha_type.value==3) {
		$('.recaptcha').show();
	} else {
		$('.recaptcha').hide();
	}
}

function togglePreChecked() {
	if(document.form1.emailoptintype.value==3 || document.form1.captcha_type.value==0) {
		$('.row_pre_checked').hide();
	} else {
		$('.row_pre_checked').show();
	}
}


</script>
<style>   
   <?php 
if ($_SESSION['MM_UserGroup'] <9) { echo ".managerOnly {display:none;}"; } 
if ($row_rsPreferences['useregions'] !=1) { echo ".region {display:none;}"; } ?></style>
<link href="../../css/membersDefault.css" rel="stylesheet"  />
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" --><div class="page users">
   <?php require_once('../../../core/region/includes/chooseregion.inc.php'); ?> <h1><i class="glyphicon glyphicon-user"></i>  User  Options </h1>
   <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
     
     <li><a href="discovered/index.php" class="link_manage"><i class="glyphicon glyphicon-cog"></i> Discovery Methods</a></li><li class="managerOnly"><a href="../merge_users.php" class="link_manage"><i class="glyphicon glyphicon-cog"></i> Merge Users</a></li>
     <li><a href="import.php" class="link_manage"><i class="glyphicon glyphicon-cog"></i> Import</a></li>
     <li><a href="legal/index.php" class="link_manage"><i class="glyphicon glyphicon-cog"></i> Legal</a></li>
     
     <li><a href="../index.php" class="link_undo"><i class="glyphicon glyphicon-arrow-left"></i> Back to users</a></li>
   </ul></div></nav>
   <form action="<?php echo $editFormAction; ?>" method="POST" name="form1" id="form1">
     <div id="TabbedPanels1" class="TabbedPanels">
       <ul class="TabbedPanelsTabGroup">
         <li class="TabbedPanelsTab" tabindex="0">Sign up Options</li>
         <li class="TabbedPanelsTab" tabindex="0">Security</li>
         <li class="TabbedPanelsTab" tabindex="0">Profile</li>
         <li class="TabbedPanelsTab" tabindex="0" id="tab_memberoptions">Member Options</li>
         <li class="TabbedPanelsTab" tabindex="0">Email &amp; GDPR</li>
         <li class="TabbedPanelsTab" tabindex="0"> View Options</li>
<li class="TabbedPanelsTab" tabindex="0">Text</li>
       </ul>
       <div class="TabbedPanelsContentGroup">
         <div class="TabbedPanelsContent">
           <p>
             <label>
               <input name="usercansignup" type="checkbox" id="usercansignup" value="1" <?php if (!(strcmp($row_rsPreferences['userscansignup'],1))) {echo "checked";} ?>  />
               Allow web site users to sign up to site</label>
           </p>
           <p>
             <label>
               <input name="userscanlogin" type="checkbox" id="userscanlogin" value="1" <?php if (!(strcmp($row_rsPreferences['userscanlogin'],1))) {echo "checked";} ?>  />
               Allow sign ups to log in</label>
           </p>
           <p class="region">
             <label>
               <input name="multisitesignup" type="checkbox" id="multisitesignup" value="1" <?php if (!(strcmp($row_rsPreferences['multisitesignup'],1))) {echo "checked";} ?>  />
               Member credentials cover all sites</label>
           </p>
           <p>
             <label>
               <input <?php if (!(strcmp($row_rsPreferences['autousername'],1))) {echo "checked=\"checked\"";} ?> name="autousername" type="checkbox" id="autousername" value="1" />
               Automatically generate username and password (otherwise user will choose their own)</label>
           </p>
           <label>
             <input <?php if (!(strcmp($row_rsPreferences['emailasusername'],1))) {echo "checked=\"checked\"";} ?> name="emailasusername" type="checkbox" id="emailasusername" value="1" />
             Use email address as username</label>
           (instead of auto generated or user chosen username)
  <p>
    <label>
      <input name="communityguidelines" type="checkbox" id="communityguidelines" value="1" <?php if (!(strcmp($row_rsPreferences['communityguidelines'],1))) {echo "checked=\"checked\"";} ?>  />
      Show membership guidelines</label>
  </p>
  <p>
    <label>
      <input <?php if (!(strcmp($row_rsPreferences['newuseralert'],1))) {echo "checked=\"checked\"";} ?> name="newuseralert" type="checkbox" id="newuseralert"  value="1" />
      Send email  to administrator when a new user signs up</label>
  </p>
  <p>
    <label>
      <input <?php if (!(strcmp($row_rsPreferences['userupdatealert'],1))) {echo "checked=\"checked\"";} ?> name="userupdatealert" type="checkbox" id="userupdatealert"  value="1" />
      Send email  to administrator when a user updates their profile</label>
  </p>
  <p>
    <label>Welcome email to new sign ups:
      <select name="welcomeemailID" id="welcomeemailID">
        <option value="-1" <?php if (!(strcmp(-1, $row_rsPreferences['welcomeemailID']))) {echo "selected=\"selected\"";} ?>>Do not send welcome email</option>
        <option value="0" <?php if (!(strcmp(0, $row_rsPreferences['welcomeemailID']))) {echo "selected=\"selected\"";} ?>>Send default welcome email</option>
        <?php
do {  
?>
        <option value="<?php echo $row_rsEmailTemplates['ID']?>"<?php if (!(strcmp($row_rsEmailTemplates['ID'], $row_rsPreferences['welcomeemailID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsEmailTemplates['templatename']?></option>
        <?php
} while ($row_rsEmailTemplates = mysql_fetch_assoc($rsEmailTemplates));
  $rows = mysql_num_rows($rsEmailTemplates);
  if($rows > 0) {
      mysql_data_seek($rsEmailTemplates, 0);
	  $row_rsEmailTemplates = mysql_fetch_assoc($rsEmailTemplates);
  }
?>
      </select>
    </label>
  </p>
         </div>
         <div class="TabbedPanelsContent">
           <p>
             <label>
               <input <?php if (!(strcmp($row_rsPreferences['manualverify'],1))) {echo "checked=\"checked\"";} ?> name="manualverify" type="checkbox" id="manualverify" value="1" />
               New sign ups must be manually checked before membership </label>
           </p>
           <p>
             <label>
               <input <?php if (!(strcmp($row_rsPreferences['emailverify'],1))) {echo "checked=\"checked\"";} ?> type="checkbox" name="emailverify" value="1" id="emailverify"  />
               New sign ups must verify their email address before membership</label>
           </p>
           <p>
             
             Use &quot;CAPTCHA&quot; to help prevent robot spam on:
             <input <?php if (!(strcmp($row_rsPreferences['securityletters'],1))) {echo "checked=\"checked\"";} ?> name="usesecurityletters" type="checkbox" id="usesecurityletters" value="1" /> <label for="usesecurityletters">Sign up</label>
             &nbsp;&nbsp;&nbsp;
             
             <input <?php if (!(strcmp($row_rsPreferences['captcha_login'],1))) {echo "checked=\"checked\"";} ?> name="captcha_login" type="checkbox" id="captcha_login" value="1" /> <label for="captcha_login">Log in</label>
             
             
             
           </p>
           <p>Captcha type:
           <label data-toggle="Type security letters to match image to prove user is human"><input <?php if (!(strcmp($row_rsPreferences['captcha_type'],"1"))) {echo "checked=\"checked\"";} ?> type="radio" name="captcha_type" value="1" onClick="toggleRecaptcha()"> 
           Security letters</label>
           &nbsp;&nbsp;&nbsp;
            <label><input <?php if (!(strcmp($row_rsPreferences['captcha_type'],"2"))) {echo "checked=\"checked\"";} ?> type="radio" name="captcha_type" value="2" onClick="toggleRecaptcha()">  ReCaptcha 2</label>
            
             &nbsp;&nbsp;&nbsp;
            <label><input <?php if (!(strcmp($row_rsPreferences['captcha_type'],"3"))) {echo "checked=\"checked\"";} ?> type="radio" name="captcha_type" value="3" onClick="toggleRecaptcha()"> Invisible ReCaptcha</label>
           
           
           </p>
           <p class="recaptcha form-inline"><input name="recaptcha_site_key" type="text" placeholder="Site Key" value="<?php echo $row_rsPreferences['recaptcha_site_key']; ?>" size="50" maxlength="50" class="form-control"> <input name="recaptcha_secret_key" type="text" placeholder="Secret Key" value="<?php echo $row_rsPreferences['recaptcha_secret_key']; ?>" size="50" maxlength="50" class="form-control"> <a href="https://www.google.com/recaptcha/admin#list" target="_blank">API</a></p>
           <p>
             <input <?php if (!(strcmp($row_rsPreferences['passwordencrypted'],1))) {echo "checked=\"checked\"";} ?> name="passwordencrypted" type="checkbox" id="passwordencrypted" value="1" onClick="if(this.checked) alert('All existing passwords stored on the server will be encrypted and no longer be retrievable.\n\nIf a user forgets their password they will be issued with a new one.'); else alert('All new passwords will be stored on the server in plain text and will be retrievable.\n\nHowever, all existing passwords are still encrypted and if an existing user forgets their password they will be issued with a new one.');" />
             <label for="passwordencrypted">Only store encrypted passwords.</label>  &nbsp;&nbsp;&nbsp;
           
          Password encryption: <label>
               <input <?php if (!(strcmp($row_rsPreferences['defaultcrypttype'],"1"))) {echo "checked=\"checked\"";} ?> type="radio" name="defaultcrypttype" value="1" >
               MD5</label>
             &nbsp;&nbsp;&nbsp;
             <label>
               <input <?php if (!(strcmp($row_rsPreferences['defaultcrypttype'],"0"))) {echo "checked=\"checked\"";} ?> type="radio" name="defaultcrypttype" value="2" >
               Bcrypt (recommended if supported by server)</label>
               
               
               
               </p>
               <p>Passwords must contain:
             <label>
               <input name="passwordmulticase" type="checkbox" id="passwordmulticase" value="1" <?php if (!(strcmp($row_rsPreferences['passwordmulticase'],1))) {echo "checked=\"checked\"";} ?> >
               upper and lowercase characters</label>
             &nbsp;&nbsp;&nbsp;
             <label>
               <input type="checkbox" name="passwordnumber" id="passwordnumber" <?php if (!(strcmp($row_rsPreferences['passwordnumber'],1))) {echo "checked=\"checked\"";} ?> >
               a number</label>
             &nbsp;&nbsp;&nbsp;
             <label>
               <input type="checkbox" name="passwordspecialchar" id="passwordspecialchar" <?php if (!(strcmp($row_rsPreferences['passwordspecialchar'],1))) {echo "checked=\"checked\"";} ?> >
               special character (£$#!)</label></p>
           <p>Stay logged in option:
             <label>
               <input <?php if (!(strcmp($row_rsPreferences['stayloggedin'],"-1"))) {echo "checked=\"checked\"";} ?> type="radio" name="stayloggedin" value="-1" id="stayloggedin_0">
               Never</label>
             &nbsp;&nbsp;&nbsp;
             <label>
               <input <?php if (!(strcmp($row_rsPreferences['stayloggedin'],"0"))) {echo "checked=\"checked\"";} ?> type="radio" name="stayloggedin" value="0" id="stayloggedin_1">
               Default off</label>
             &nbsp;&nbsp;&nbsp;
             <label>
               <input <?php if (!(strcmp($row_rsPreferences['stayloggedin'],"1"))) {echo "checked=\"checked\"";} ?> type="radio" name="stayloggedin" value="1" id="stayloggedin_2">
               Default on</label>
             &nbsp;&nbsp;&nbsp; </p>
           <p class="form-inline"><label>Maximum login attempts before account lock
             
               <input name="loginattempts" type="text"  id="loginattempts" value="<?php echo $row_rsPreferences['loginattempts']; ?>" size="3" maxlength="3"  class="form-control" />
             </label>
             (0 = unlimited) </p>
         </div>
         <div class="TabbedPanelsContent">
           <table border="0" class="listTable">
             <tr>
               <th scope="col">Ask</th>
               <th scope="col">On sign up</th>
               <th scope="col">Profile page</th>
               <th scope="col">Compulsary</th>
               <th scope="col">Text</th>
             </tr>
             <tr>
               <td><label> Title (e.g. Mr, Ms, Dr, etc.)</label></td>
               <td><input <?php if (!(strcmp($row_rsPreferences['usesalutation'],1))) {echo "checked=\"checked\"";} ?> name="usesalutation" type="checkbox" id="usesalutation" value="1" /></td>
               <td>&nbsp;</td>
               <td>&nbsp;</td>
               <td><input name="text_salutation" type="text" id="text_salutation" value="<?php echo isset($row_rsPreferences['text_salutation']) ? htmlentities($row_rsPreferences['text_salutation'], ENT_COMPAT, "UTF-8") : "Title"; ?>" size="50" maxlength="50" class="form-control"></td>
             </tr>
             <tr>
               <td>First name</td>
               <td>x</td>
               <td>x</td>
               <td>x</td>
               <td><input name="text_firstname" type="text" id="text_firstname" value="<?php echo isset($row_rsPreferences['text_firstname']) ? htmlentities($row_rsPreferences['text_firstname'], ENT_COMPAT, "UTF-8") : "First name"; ?>" size="50" maxlength="50" class="form-control"></td>
             </tr>
             <tr>
               <td><label for="askmiddlename">Middle name</label></td>
               <td><input name="askmiddlename" type="checkbox" id="askmiddlename" value="1" <?php if (!(strcmp($row_rsPreferences['askmiddlename'],1))) {echo "checked=\"checked\"";} ?> /></td>
               <td><input name="askmiddleprofile" type="checkbox" id="askmiddleprofile" value="1" <?php if (!(strcmp($row_rsPreferences['askmiddleprofile'],1))) {echo "checked=\"checked\"";} ?> /></td>
               <td>&nbsp;</td>
               <td><input name="text_middlename" type="text" id="text_middlename" value="<?php echo isset($row_rsPreferences['text_middlename']) ? htmlentities($row_rsPreferences['text_middlename'], ENT_COMPAT, "UTF-8") : "Middle name"; ?>" size="50" maxlength="50" class="form-control"></td>
             </tr>
             
             
             
             <tr>
               <td>Surname</td>
               <td>x</td>
               <td>x</td>
               <td>x</td>
               <td><input name="text_surname" type="text" id="text_surname" value="<?php echo isset($row_rsPreferences['text_surname']) ? htmlentities($row_rsPreferences['text_surname'], ENT_COMPAT, "UTF-8") : "Surname"; ?>" size="50" maxlength="50" class="form-control"></td>
             </tr>
             <tr>
               <td>Email</td>
               <td>x</td>
               <td>x</td>
               <td>x</td>
               <td><input name="text_email" type="text" id="text_email" value="<?php echo isset($row_rsPreferences['text_email']) ? htmlentities($row_rsPreferences['text_email'], ENT_COMPAT, "UTF-8") : "Email"; ?>" size="50" maxlength="50" class="form-control"></td>
             </tr>
             <tr>
               <td>Username</td>
               <td>x</td>
               <td>x</td>
               <td>x</td>
               <td><input name="text_username" type="text" idtext_username"text_username" value="<?php echo isset($row_rsPreferences['text_surname']) ? htmlentities($row_rsPreferences['text_username'], ENT_COMPAT, "UTF-8") : "Username"; ?>" size="50" maxlength="50" class="form-control"></td>
             </tr>
             <tr>
               <td>Password</td>
               <td>x</td>
               <td>x</td>
               <td>x</td>
               <td><input name="text_password" type="text" id="text_password" value="<?php echo isset($row_rsPreferences['text_password']) ? htmlentities($row_rsPreferences['text_password'], ENT_COMPAT, "UTF-8") : "Password"; ?>" size="50" maxlength="50" class="form-control"></td>
             </tr>
             <tr>
               <td>Re-type password</td>
               <td>x</td>
               <td>x</td>
               <td>x</td>
               <td><input name="text_retypepassword" type="text" id="text_retypepassword" value="<?php echo isset($row_rsPreferences['text_retypepassword']) ? htmlentities($row_rsPreferences['text_retypepassword'], ENT_COMPAT, "UTF-8") : "Re-type password"; ?>" size="50" maxlength="50" class="form-control"></td>
             </tr>
             <tr>
               <td><label for="askhowdiscovered">How they found site (edit discovery methods with link above)</label></td>
               <td><input name="askhowdiscovered" type="checkbox" id="askhowdiscovered" value="1" <?php if (!(strcmp($row_rsPreferences['askhowdiscovered'],1))) {echo "checked=\"checked\"";} ?> /></td>
               <td><input name="askhowdiscoveredprofile" type="checkbox" id="askhowdiscoveredprofile" value="1" <?php if (!(strcmp($row_rsPreferences['askhowdiscoveredprofile'],1))) {echo "checked=\"checked\"";} ?> /></td>
               <td><input name="askhowdiscoveredcompulsary" type="checkbox" id="askhowdiscoveredcompulsary" value="1" <?php if (!(strcmp($row_rsPreferences['askhowdiscoveredcompulsary'],1))) {echo "checked=\"checked\"";} ?> /></td>
               <td>&nbsp;</td>
             </tr>
             <tr>
               <td><a href="gender/index.php">Gender (options)</a></td>
               <td><input name="askgender" type="checkbox" id="askgender" value="1" <?php if (!(strcmp($row_rsPreferences['askgender'],1))) {echo "checked=\"checked\"";} ?> /></td>
               <td><input name="askgenderprofile" type="checkbox" id="askgenderprofile" value="1" <?php if (!(strcmp($row_rsPreferences['askgenderprofile'],1))) {echo "checked=\"checked\"";} ?> /></td>
               <td><input name="askgendercompulsary" type="checkbox" id="askgendercompulsary" value="1" <?php if (!(strcmp($row_rsPreferences['askgendercompulsary'],1))) {echo "checked=\"checked\"";} ?> /></td>
               <td>&nbsp;</td>
             </tr>
             <tr>
               <td><a href="ethnicity/index.php">Ethnic background (edit list)</a></td>
               <td><input name="askethnicity" type="checkbox" id="askethnicity" value="1" <?php if (!(strcmp($row_rsPreferences['askethnicity'],1))) {echo "checked=\"checked\"";} ?> /></td>
               <td><input name="askethnicityprofile" type="checkbox" id="askethnicityprofile" value="1" <?php if (!(strcmp($row_rsPreferences['askethnicityprofile'],1))) {echo "checked=\"checked\"";} ?> style="display:none;" /></td>
               <td><input name="askethnicitycompulsary" type="checkbox" id="askethnicitycompulsary" value="1" <?php if (!(strcmp($row_rsPreferences['askethnicitycompulsary'],1))) {echo "checked=\"checked\"";} ?> /></td>
               <td>&nbsp;</td>
             </tr>
             <tr>
               <td><a href="disability/index.php">Disabilities (edit list)</a></td>
               <td><input name="askdisability" type="checkbox" id="askdisability" value="1" <?php if (!(strcmp($row_rsPreferences['askdisability'],1))) {echo "checked=\"checked\"";} ?> /></td>
               <td><input name="askdisabilityprofile" type="checkbox" id="askdisabilityprofile" value="1" <?php if (!(strcmp($row_rsPreferences['askdisabilityprofile'],1))) {echo "checked=\"checked\"";} ?>  style="display:none;" /></td>
               <td><input name="askdisabilitycompulsary" type="checkbox" id="askdisabilitycompulsary" value="1" <?php if (!(strcmp($row_rsPreferences['askdisabilitycompulsary'],1))) {echo "checked=\"checked\"";} ?> /></td>
               <td>&nbsp;</td>
             </tr>
             <tr>
               <td>Twitter link</td>
               <td><input <?php if (!(strcmp($row_rsPreferences['asktwitter'],1))) {echo "checked=\"checked\"";} ?> name="asktwitter" type="checkbox" id="asktwitter" value="1"></td>
               <td><input <?php if (!(strcmp($row_rsPreferences['asktwitterprofile'],1))) {echo "checked=\"checked\"";} ?> name="asktwitterprofile" type="checkbox" id="asktwitterprofile" value="1"></td>
               <td><input <?php if (!(strcmp($row_rsPreferences['asktwittercompulsary'],1))) {echo "checked=\"checked\"";} ?> name="asktwittercompulsary" type="checkbox" id="asktwittercompulsary" value="1"></td>
               <td>&nbsp;</td>
             </tr>
             <tr>
               <td>Facebook link</td>
               <td><input <?php if (!(strcmp($row_rsPreferences['askfacebook'],1))) {echo "checked=\"checked\"";} ?> name="askfacebook" type="checkbox" id="askfacebook" value="1"></td>
               <td><input <?php if (!(strcmp($row_rsPreferences['askfacebookprofile'],1))) {echo "checked=\"checked\"";} ?> name="askfacebookprofile" type="checkbox" id="askfacebookprofile" value="1"></td>
               <td><input <?php if (!(strcmp($row_rsPreferences['askfacebookcompulsary'],1))) {echo "checked=\"checked\"";} ?> name="askfacebookcompulsary" type="checkbox" id="askfacebookcompulsary" value="1"></td>
               <td>&nbsp;</td>
             </tr>
             <tr>
               <td><?php echo isset($row_rsPreferences['text_role']) ? $row_rsPreferences['text_role'] : "Job Title"; ?></td>
               <td><input <?php if (!(strcmp($row_rsPreferences['askjobtitle'],1))) {echo "checked=\"checked\"";} ?> name="askjobtitle" type="checkbox" id="askjobtitle" value="1"></td>
               <td><input <?php if (!(strcmp($row_rsPreferences['askjobtitleprofile'],1))) {echo "checked=\"checked\"";} ?> name="askjobtitleprofile" type="checkbox" id="askjobtitleprofile" value="1"></td>
               <td><input <?php if (!(strcmp($row_rsPreferences['askjobtitlecompulsary'],1))) {echo "checked=\"checked\"";} ?> name="askjobtitlecompulsary" type="checkbox" id="askjobtitlecompulsary" value="1"></td>
               <td>&nbsp;</td>
             </tr>
             <tr>
               <td>Telephone</td>
               <td><input <?php if (!(strcmp($row_rsPreferences['asktelephone'],1))) {echo "checked=\"checked\"";} ?> name="asktelephone" type="checkbox" id="asktelephone" value="1" /></td>
               <td><input <?php if (!(strcmp($row_rsPreferences['asktelephoneprofile'],1))) {echo "checked=\"checked\"";} ?> name="asktelephoneprofile" type="checkbox" id="asktelephoneprofile" value="1" /></td>
               <td><input <?php if (!(strcmp($row_rsPreferences['asktelephonecompulsary'],1))) {echo "checked=\"checked\"";} ?> name="asktelephonecompulsary" type="checkbox" id="asktelephonecompulsary" value="1" /></td>
               <td>&nbsp;</td>
             </tr>
             
             <tr>
               <td>Mobile</td>
               <td><input <?php if (!(strcmp($row_rsPreferences['askmobile'],1))) {echo "checked=\"checked\"";} ?> name="askmobile" type="checkbox" id="askmobile" value="1" /></td>
               <td><input <?php if (!(strcmp($row_rsPreferences['askmobileprofile'],1))) {echo "checked=\"checked\"";} ?> name="askmobileprofile" type="checkbox" id="askmobileprofile" value="1" /></td>
               <td><input <?php if (!(strcmp($row_rsPreferences['askmobilecompulsary'],1))) {echo "checked=\"checked\"";} ?> name="askmobilecompulsary" type="checkbox" id="askmobilecompulsary" value="1" /></td>
               <td>&nbsp;</td>
             </tr>
             
             
             <tr>
               <td>Web site</td>
               <td><input <?php if (!(strcmp($row_rsPreferences['askwebsiteURL'],1))) {echo "checked=\"checked\"";} ?> name="askwebsiteURL" type="checkbox" id="askwebsiteURL" value="1" /></td>
               <td><input <?php if (!(strcmp($row_rsPreferences['askwebsiteURLprofile'],1))) {echo "checked=\"checked\"";} ?> name="askwebsiteURLprofile" type="checkbox" id="askwebsiteURLprofile" value="1" /></td>
               <td><input <?php if (!(strcmp($row_rsPreferences['askwebsiteURLcompulsary'],1))) {echo "checked=\"checked\"";} ?> name="askwebsiteURLcompulsary" type="checkbox" id="askwebsiteURLcompulsary" value="1" /></td>
               <td>&nbsp;</td>
             </tr>
             <tr>
               <td>User profile (&quot;About me&quot;)</td>
               <td>&nbsp;</td>
               <td><input <?php if (!(strcmp($row_rsPreferences['askaboutmeprofile'],1))) {echo "checked=\"checked\"";} ?> name="askaboutmeprofile" type="checkbox" id="askaboutmeprofile" value="1" /></td>
               <td><input <?php if (!(strcmp($row_rsPreferences['askaboutmecompulsary'],1))) {echo "checked=\"checked\"";} ?> name="askaboutmecompulsary" type="checkbox" id="askaboutmecompulsary" value="1" /></td>
               <td>&nbsp;</td>
             </tr>
             <tr>
               <td>Profile photo</td>
               <td>&nbsp;</td>
               <td><input <?php if (!(strcmp($row_rsPreferences['askphotoprofile'],1))) {echo "checked=\"checked\"";} ?> name="askphotoprofile" type="checkbox" id="askphotoprofile" value="1" /></td>
               <td><input <?php if (!(strcmp($row_rsPreferences['askphotocompulsary'],1))) {echo "checked=\"checked\"";} ?> name="askphotocompulsary" type="checkbox" id="askphotocompulsary" value="1" /></td>
               <td>&nbsp;</td>
             </tr>
           </table>
           <p>
             <label>Ask for company details
               <input <?php if (!(strcmp($row_rsPreferences['askcompanydetails'],1))) {echo "checked=\"checked\"";} ?> name="askcompanydetails" type="checkbox" id="askcompanydetails" value="1">
             </label>
             (this will happen at the end of sign up process and every log in until details are entered)</p>
           <p> Ask for date of birth?
             <input <?php if (!(strcmp($row_rsPreferences['askdateofbirth'],"0"))) {echo "checked=\"checked\"";} ?> type="radio" name="askdateofbirth" id="radio" value="0" />
             <label for="radio">No</label>
             &nbsp;&nbsp;&nbsp;
             <input <?php if (!(strcmp($row_rsPreferences['askdateofbirth'],"2"))) {echo "checked=\"checked\"";} ?> type="radio" name="askdateofbirth" id="radio3" value="2" />
             <label for="radio3">Age range (<a href="agerange/index.php">edit</a>)</label>
             &nbsp;&nbsp;&nbsp;
             <input <?php if (!(strcmp($row_rsPreferences['askdateofbirth'],"1"))) {echo "checked=\"checked\"";} ?> type="radio" name="askdateofbirth" id="radio2" value="1" />
             <label for="radio2">Yes:</label>
             &nbsp;
             <label>Minimum age:
               <input name="minimumage" type="text" id="minimumage" value="<?php echo $row_rsPreferences['minimumage']; ?>" size="5" maxlength="2" />
             </label>
           </p>
           <p> Ask for home address?
             <input <?php if (!(strcmp($row_rsPreferences['addressrequired'],"0"))) {echo "checked=\"checked\"";} ?> type="radio" name="addressrequired" id="address1" value="0" />
             <label for="radio">No</label>
             &nbsp;&nbsp;&nbsp;
             <input <?php if (!(strcmp($row_rsPreferences['addressrequired'],"1"))) {echo "checked=\"checked\"";} ?> type="radio" name="addressrequired" id="address2" value="1" />
             <label for="radio2">Yes</label>
             &nbsp;&nbsp;&nbsp;
             <input <?php if (!(strcmp($row_rsPreferences['addressrequired'],"2"))) {echo "checked=\"checked\"";} ?> type="radio" name="addressrequired" id="address3" value="2" />
             <label for="radio3">Postcode only</label>
           </p
           >
         </div>
         <div class="TabbedPanelsContent">
           <p>
             <label>
               <input <?php if (!(strcmp($row_rsPreferences['usercontactform'],1))) {echo "checked=\"checked\"";} ?> name="usercontactform" type="checkbox" id="usercontactform" value="1" />
               Allow users to be  contactable via a web form (public and/or other members)</label>
           </p>
           <p>
             <label>
               <input <?php if (!(strcmp($row_rsPreferences['memberpubliclocation'],1))) {echo "checked=\"checked\"";} ?> name="memberprofilelocation" type="checkbox" id="memberprofilelocation" value="1" />
               Allow members to add themselves to public location</label>
           </p>
           <p>
             <label>
               <input <?php if (!(strcmp($row_rsPreferences['memberdirectory'],1))) {echo "checked=\"checked\"";} ?> name="memberdirectory" type="checkbox" id="memberdirectory" value="1" onClick="toggleMemberDirectory()" />
               Allow members to access directory of other members</label>
           </p>
           <fieldset id="memberdirectoryoptions">
             <p>
               <label>
                 <input <?php if (!(strcmp($row_rsPreferences['memberdirectoryemail'],1))) {echo "checked=\"checked\"";} ?> name="memberdirectoryemail" type="checkbox" id="memberdirectoryemail" value="1" />
                 Show email addresses in member directory (where user has agreed to be contacted)</label>
             </p>
             <p>
               <label>
                 <input <?php if (!(strcmp($row_rsPreferences['membernetwork'],1))) {echo "checked=\"checked\"";} ?> name="membernetwork" type="checkbox" id="membernetwork" value="1" />
                 Allow members to network</label>
             </p>
           </fieldset>
           
           <p class="form-inline">Custom members page URL:
             <input name="memberspageURL" type="text"  id="memberspageURL" value="<?php echo $row_rsPreferences['memberspageURL']; ?>" size="50" maxlength="100"  class="form-control"/>
           </p>
         </div>
         <div class="TabbedPanelsContent">
           <table  class="form-table">
             <tr>
               <td colspan="2" class="text-nowrap"><h2>From this site</h2></td>
             </tr>
             <tr>
               <td class="text-right text-nowrap">Email opt in type:</td>
               <td><label>
                 <input <?php if (!(strcmp($row_rsPreferences['emailoptintype'],"1"))) {echo "checked=\"checked\"";} ?> type="radio" name="emailoptintype" value="1"  onClick="togglePreChecked()" >
                 Opt in checkbox</label>
                 &nbsp;&nbsp;&nbsp;
                 <label>
                   <input <?php if (!(strcmp($row_rsPreferences['emailoptintype'],"2"))) {echo "checked=\"checked\"";} ?> type="radio" name="emailoptintype" value="2"  onClick="togglePreChecked()" >
                   Opt out checkbox</label>
                    &nbsp;&nbsp;&nbsp;
                 <label>
                   <input <?php if (!(strcmp($row_rsPreferences['emailoptintype'],"3"))) {echo "checked=\"checked\"";} ?> type="radio" name="emailoptintype" value="3"  onClick="togglePreChecked()" >
                   Opt in yes/no choice</label>
                 &nbsp;&nbsp;&nbsp;
                 <label>
                   <input <?php if (!(strcmp($row_rsPreferences['emailoptintype'],"0"))) {echo "checked=\"checked\"";} ?> type="radio" name="emailoptintype" value="0" onClick="togglePreChecked()" >
                   None</label></td>
             </tr>
             <tr class="row_pre_checked">
               <td class="text-right text-nowrap"><label for="emailoptinset">Checked by default: </label></td>
               <td><input <?php if (!(strcmp($row_rsPreferences['emailoptinset'],1))) {echo "checked=\"checked\"";} ?> name="emailoptinset" type="checkbox" id="emailoptinset" value="1"></td>
             </tr>
             <tr>
               <td class="text-right text-nowrap"><label for="emailoptintext">Option text:</label></td>
               <td><input name="emailoptintext" type="text" id="emailoptintext" value="<?php echo htmlentities($row_rsPreferences['emailoptintext'], ENT_COMPAT, "UTF-8"); ?>" size="80" maxlength="255" class="form-control"></td>
             </tr>
             <tr>
               <td colspan="2"><h2>From partner sites</h2></td>
             </tr>
             <tr>
               <td class="text-right text-nowrap">Email opt in type:</td>
               <td><label>
                 <input <?php if (!(strcmp($row_rsPreferences['partneremailoptintype'],"1"))) {echo "checked=\"checked\"";} ?> type="radio" name="partneremailoptintype" value="1" id="partneremailoptintype_3">
                 Opt in</label>
                 &nbsp;&nbsp;&nbsp;
                 <label>
                   <input <?php if (!(strcmp($row_rsPreferences['partneremailoptintype'],"2"))) {echo "checked=\"checked\"";} ?> type="radio" name="partneremailoptintype" value="2" id="partneremailoptintype_4">
                   Opt out</label>
                 &nbsp;&nbsp;&nbsp;
                 <label>
                   <input <?php if (!(strcmp($row_rsPreferences['partneremailoptintype'],"0"))) {echo "checked=\"checked\"";} ?> type="radio" name="partneremailoptintype" value="0" id="partneremailoptintype_5">
                   None</label></td>
             </tr>
             <tr>
               <td class="text-right text-nowrap"><label for="partneremailoptinset">Checked by default: </label></td>
               <td><input <?php if (!(strcmp($row_rsPreferences['partneremailoptinset'],1))) {echo "checked=\"checked\"";} ?> name="partneremailoptinset" type="checkbox" id="partneremailoptinset" value="1"></td>
             </tr>
             <tr>
               <td class="text-right text-nowrap"><label for="partneremailoptintext">Option text:</label></td>
               <td><input name="partneremailoptintext" type="text" id="partneremailoptintext" value="<?php echo htmlentities($row_rsPreferences['partneremailoptintext'], ENT_COMPAT, "UTF-8"); ?>" size="80" maxlength="255"  class="form-control"></td>
             </tr>
             <tr>
               <td colspan="2"><h2>GDPR Data Retention</h2></td>
             </tr>
             <tr>
               <td class="text-right text-nowrap"><label for="deletedatausertypeID">Delete user data:</label></td>
               <td class="form-inline">
                 <select name="deletedatausertypeID" id="deletedatausertypeID" class="form-control">
                   <option value="">No users</option>
                   <?php
do {  
if ($row_rsUserTypes['ID']>=0 && $row_rsUserTypes['ID']<7 ) {?>
                   <option value="<?php echo $row_rsUserTypes['ID']?>" <?php if($row_rsUserTypes['ID']==$row_rsPreferences['deletedatausertypeID']) echo " selected "; ?>><?php echo $row_rsUserTypes['name']; ?> or lower rank</option>
                   <?php }
} while ($row_rsUserTypes = mysql_fetch_assoc($rsUserTypes));
  $rows = mysql_num_rows($rsUserTypes);
  if($rows > 0) {
      mysql_data_seek($rsUserTypes, 0);
	  $row_rsUserTypes = mysql_fetch_assoc($rsUserTypes);
  }
?>
                 </select> 
                 after 
                 <label for="deletedataperiod">
                 <input name="deletedataperiod" type="text" id="deletedataperiod" value="<?php echo $row_rsPreferences['deletedataperiod']/604800; ?>" size="5" maxlength="5"  class="form-control"> 
                 weeks inactivity (unless user marked do not delete)</label></td>
             </tr>
           </table>
           <?php echo "*".autoDeleteUserData(); ?>
         </div>
         <div class="TabbedPanelsContent">
           <h3>Show the following in list view:</h3>
           <p>
             <label>
               <input <?php if (!(strcmp($row_rsPreferences['user_list_email'],1))) {echo "checked=\"checked\"";} ?> type="checkbox" value="1" name="user_list_email">
               Email</label>
           </p>
           <p>
             <label>
               <input type="checkbox" value="1" name="user_list_phone" <?php if (!(strcmp($row_rsPreferences['user_list_phone'],1))) {echo "checked=\"checked\"";} ?>>
               Telephone</label>
           </p>
           <p>
             <label>
               <input type="checkbox" value="1" name="user_list_mobile" <?php if (!(strcmp($row_rsPreferences['user_list_mobile'],1))) {echo "checked=\"checked\"";} ?>>
               Mobile</label>
           </p>
           <p>Profile page:</p>
           <p>
             <label>
               <input type="checkbox" value="1" name="user_page_tabs" <?php if (!(strcmp($row_rsPreferences['user_page_tabs'],1))) {echo "checked=\"checked\"";} ?>>
               Tabs</label>
           </p>
         </div>
<div class="TabbedPanelsContent">
  <h2>Text preferences</h2>
           
                 
              
           <table class="form-table">
             <tr>
               <td align="right"><label for="memberdirectoryname">Member directory name:</label></td>
               <td><input name="memberdirectoryname" type="text" id="memberdirectoryname" value="<?php echo $row_rsPreferences['memberdirectoryname']; ?>" size="50" maxlength="50" class="form-control" /></td>
             </tr>
             <tr>
               <td align="right">Register:</td>
               <td><input name="registertext" type="text" id="registertext" value="<?php echo $row_rsPreferences['registertext']; ?>" size="50" maxlength="50"  class="form-control"/></td>
             </tr>
             
              <tr>
               <td align="right">Exiting users:</td>
               <td><input name="text_existingusers" type="text" id="text_existingusers" value="<?php echo $row_rsPreferences['text_existingusers']; ?>" size="50" maxlength="50"  class="form-control"/></td>
             </tr>
             
              <tr>
               <td align="right">Log in to continue:</td>
               <td><input name="text_loginnow" type="text" id="text_loginnow" value="<?php echo $row_rsPreferences['text_loginnow']; ?>" size="50" maxlength="50"  class="form-control"/></td>
             </tr>
             
             <tr>
               <td align="right">Log in:</td>
               <td><input name="logintext" type="text" id="logintext" value="<?php echo $row_rsPreferences['logintext']; ?>" size="50" maxlength="50"  class="form-control"/></td>
             </tr>
             <tr>
               <td align="right">Log out:</td>
               <td><input name="logouttext" type="text" id="logouttext" value="<?php echo $row_rsPreferences['logouttext']; ?>" size="50" maxlength="50"  class="form-control"/></td>
             </tr>
            <tr>
               <td align="right">Username:</td>
               <td><input name="text_username" type="text" id="text_username" value="<?php echo $row_rsPreferences['text_username']; ?>" size="50" maxlength="50"  class="form-control"/></td>
             </tr>
             
             <tr>
               <td align="right">Password:</td>
               <td><input name="text_password" type="text" id="text_password" value="<?php echo $row_rsPreferences['text_password']; ?>" size="50" maxlength="50" class="form-control" /></td>
             </tr>
             <tr>
               <td align="right">Forgotten password?:</td>
               <td><input name="text_forgotpass" type="text" id="text_forgotpass" value="<?php echo $row_rsPreferences['text_forgotpass']; ?>" size="50" maxlength="50" class="form-control" /></td>
             </tr>
             <tr>
               <td align="right">Stay logged in:</td>
               <td><input name="text_stayloggedin" type="text" id="text_stayloggedin" value="<?php echo $row_rsPreferences['text_stayloggedin']; ?>" size="50" maxlength="50" class="form-control" /></td>
             </tr>
             
             <tr>
               <td align="right">Logged out:</td>
               <td><input name="text_loggedout" type="text" id="text_loggedout" value="<?php echo $row_rsPreferences['text_loggedout']; ?>" size="50" maxlength="255"  class="form-control"/></td>
             </tr>
             
             <tr>
               <td align="right">New password:</td>
               <td><input name="text_newpassword" type="text" id="text_newpassword" value="<?php echo $row_rsPreferences['text_newpassword']; ?>" size="50" maxlength="255"  class="form-control"/></td>
             </tr>
             
             <tr>
               <td align="right">Email verified:</td>
               <td><input name="text_emailverified" type="text" id="text_emailverified" value="<?php echo $row_rsPreferences['text_emailverified']; ?>" size="50" maxlength="255" class="form-control" /></td>
             </tr>
             <tr>
               <td align="right">Choose your login:</td>
               <td><input name="text_choosepassword" type="text" id="text_choosepassword" value="<?php echo $row_rsPreferences['text_choosepassword']; ?>" size="50" maxlength="255" class="form-control" /></td>
             </tr>
             
             
              <tr>
               <td align="right">Address Book:</td>
               <td><input name="text_address_book" type="text" id="text_address_book" value="<?php echo $row_rsPreferences['text_address_book']; ?>" size="50" maxlength="255" class="form-control" /></td>
             </tr>
              <tr>
                <td align="right">Group optin:</td>
                <td><input name="groupoptintext" type="text" id="groupoptintext" value="<?php echo $row_rsPreferences['groupoptintext']; ?>" size="50" maxlength="255" class="form-control" /></td>
              </tr>
              
               <tr>
                <td align="right">Role:</td>
                <td>&nbsp;</td>
              </tr>
             
             
             
             
             
             <tr>
               <td class="text-right top"><label for="text_logintips">Login tips</label>:</td>
               <td>
                 <textarea name="text_logintips" id="text_logintips" class="tinymce"><?php echo (isset($row_rsPreferences['text_logintips']) && $row_rsPreferences['text_logintips']!="") ? $row_rsPreferences['text_logintips'] : ""; ?></textarea></td>
             </tr>
              <tr>
               <td class="text-right top"><label for="text_loginfail">Login fail</label>:</td>
               <td>
                 <textarea name="text_loginfail" id="text_loginfail" class="tinymce"><?php echo (isset($row_rsPreferences['text_loginfail']) && $row_rsPreferences['text_loginfail']!="") ? $row_rsPreferences['text_loginfail'] : "<p>Sorry, the login details you provided do not allow access to this part of the site.<br />
        <br />
        IMPORTANT:</p>
      <ul>
        <li> Passwords are case sensistive. Ensure Caps Lock is off.</li>
        <li> If you have forgotten your password <a href=\"/login/forgot_password.php\" rel =\"nofollow\">click here</a>.</li>
        <li> Check you are authorised to view the page. If you have recently signed up, your account may not yet be activated. Please check your email for a confirmation and directions.</li>
      </ul>"; ?></textarea></td>
             </tr>
             
             
             
             <tr>
               <td class="text-right top"><label for="text_registerinfo">New users</label>:</td>
               <td>
                 <textarea name="text_registerinfo" id="text_registerinfo" class="tinymce"><?php echo (isset($row_rsPreferences['text_registerinfo']) && $row_rsPreferences['text_registerinfo']!="") ? $row_rsPreferences['text_registerinfo'] : "<h1><i class=\"glyphicon glyphicon-user\"></i> New users...</h1><h2>Please register to continue...</h2><p>It's quick, easy and best of all free!</p><p><a href=\"signup.php\" class=\"btn btn-default btn-secondary\" >Register here</a></p><h2>Registration Benefits</h2><p>As a registered user you will benefit from: </p><ul><li>We'll remember your details for each visit</li><li>A personal page with site tools</li><li>You can keep your own profile up to date</li></ul>"; ?></textarea></td>
             </tr>
             <tr>
               <td class="text-right top"><label for="text_signupinfo">Sign up info</label>:</td>
               <td><textarea name="text_signupinfo" id="text_signupinfo" class="tinymce"><?php echo (isset($row_rsPreferences['text_signupinfo']) && $row_rsPreferences['text_signupinfo']!="") ? $row_rsPreferences['text_signupinfo'] : "<h1 id=\"signUpHeader\">Your details...</h1><hr><div class=\"privacypolicy\"><h3 class=\"small\"><br />YOUR PRIVACY:</h3><p class=\"small\"> We will <strong> NEVER</strong> divulge any of your details to anyone else unless you explicitly ask us to. </p><p class=\"small\">We will only send you important information and updates you ask for. See our privacy policy. </p></div>  "; ?></textarea></td>
             </tr>
             
             
             <tr>
               <td class="text-right top"><label for="text_signup1">Sign up text</label>:</td>
               <td><textarea name="text_signup1" id="text_signup1" class="tinymce"><?php echo (isset($row_rsPreferences['text_signup1']) && $row_rsPreferences['text_signup1']!="") ? $row_rsPreferences['text_signup1'] : "<h2>New users...</h2><p>Please complete the form below.</p>"; ?></textarea></td>
             </tr>
             
  
             <tr>
               <td class="text-right top"><label for="text_signup2">Sign up page, login</label>:</td>
               <td><textarea name="text_signup2" id="text_signup2" class="tinymce"><?php echo (isset($row_rsPreferences['text_signup2']) && $row_rsPreferences['text_signup2']!="") ? $row_rsPreferences['text_signup2'] : '<h2>Already registered as a site member?</h2><p><a href="index.php" class="btn btn-default btn-secondary">Log in here</a></p>'; ?></textarea></td>
             </tr>
           </table>
           
     </div>
       </div>
     </div>
 <p>     
           <button name="save" type="submit"  id="save" class="btn btn-primary" >Save changes</button>
           <input name="ID" type="hidden" id="ID" value="<?php echo $regionID; ?>" />
</p>
   <input type="hidden" name="MM_update" value="form1">
   </form>
   </div>
<script>
<!--
var TabbedPanels1 = new Spry.Widget.TabbedPanels("TabbedPanels1");
//-->
   </script>
  <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsPreferences);

mysql_free_result($rsUserTypes);

mysql_free_result($rsEmailTemplates);
?>


