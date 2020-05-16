<?php require_once('../Connections/aquiescedb.php'); ?>
<?php require_once('includes/sendmail.inc.php'); ?>
<?php require_once('../articles/includes/functions.inc.php'); ?>
<?php

$regionID = (isset($regionID) && intval($regionID)>0) ? intval($regionID) : 1;

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

$editFormAction = $_SERVER['PHP_SELF']."?enquirysent=true";
if (isset($_SERVER['QUERY_STRING']) && strlen($_SERVER['QUERY_STRING'])>0 ) {
  $querystring = str_replace("enquirysent=true","",$_SERVER['QUERY_STRING']);
  $editFormAction .= (strlen($querystring)>0) ? urlencode($querystring) : "";
}




$colname_rsRegionContact = "1";
if (isset($regionID)) {
  $colname_rsRegionContact = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsRegionContact = sprintf("SELECT ID, title, address, telephone, fax, email, skypeID FROM region WHERE ID = %s", GetSQLValueString($colname_rsRegionContact, "int"));
$rsRegionContact = mysql_query($query_rsRegionContact, $aquiescedb) or die(mysql_error());
$row_rsRegionContact = mysql_fetch_assoc($rsRegionContact);
$totalRows_rsRegionContact = mysql_num_rows($rsRegionContact);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT * FROM preferences WHERE ID=".intval($regionID)."";
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);

$colname_rsDirectoryContact = "-1";
if (isset($_REQUEST['directoryID'])) {
  $colname_rsDirectoryContact = $_REQUEST['directoryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsDirectoryContact = sprintf("SELECT name, address1, address2, address3, address4, postcode, telephone, fax, email FROM directory WHERE ID = %s", GetSQLValueString($colname_rsDirectoryContact, "int"));
$rsDirectoryContact = mysql_query($query_rsDirectoryContact, $aquiescedb) or die(mysql_error());
$row_rsDirectoryContact = mysql_fetch_assoc($rsDirectoryContact);
$totalRows_rsDirectoryContact = mysql_num_rows($rsDirectoryContact);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsSitePrefs = "SELECT facebookID, twitterID FROM region WHERE ID = ".$regionID."";
$rsSitePrefs = mysql_query($query_rsSitePrefs, $aquiescedb) or die(mysql_error());
$row_rsSitePrefs = mysql_fetch_assoc($rsSitePrefs);
$totalRows_rsSitePrefs = mysql_num_rows($rsSitePrefs);


$varRegionID_rsDiscovered = "1";
if (isset($regionID)) {
  $varRegionID_rsDiscovered = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsDiscovered = sprintf("SELECT * FROM discovered WHERE statusID = 1 AND regionID = %s ORDER BY ordernum", GetSQLValueString($varRegionID_rsDiscovered, "int"));
$rsDiscovered = mysql_query($query_rsDiscovered, $aquiescedb) or die(mysql_error());
$row_rsDiscovered = mysql_fetch_assoc($rsDiscovered);
$totalRows_rsDiscovered = mysql_num_rows($rsDiscovered);

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT users.ID, firstname, surname, users.email, directory.name, location.address1, location.address2, location.address3, location.postcode, location.telephone1 FROM users LEFT JOIN directory ON (directory.userID = users.ID) LEFT JOIN location ON (users.defaultaddressID = location.ID) WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsSubject = "SELECT `description` FROM contactsubject WHERE statusID = 1 ORDER BY ordernum ASC";
$rsSubject = mysql_query($query_rsSubject, $aquiescedb) or die(mysql_error());
$row_rsSubject = mysql_fetch_assoc($rsSubject);
$totalRows_rsSubject = mysql_num_rows($rsSubject);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsRecipients = "SELECT ID, recipient, email FROM mailrecipient WHERE regionID = ".$regionID."";
$rsRecipients = mysql_query($query_rsRecipients, $aquiescedb) or die(mysql_error());
$row_rsRecipients = mysql_fetch_assoc($rsRecipients);
$totalRows_rsRecipients = mysql_num_rows($rsRecipients);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsOptinGroups = "SELECT usergroup.ID, usergroup.groupname, usergrouptype.grouptype FROM usergroup LEFT JOIN usergrouptype ON (usergroup.grouptypeID = usergrouptype.ID) WHERE usergroup.statusID = 1 AND usergroup.optin = 1 GROUP BY usergroup.ID ORDER BY usergroup.grouptypeID";
$rsOptinGroups = mysql_query($query_rsOptinGroups, $aquiescedb) or die(mysql_error());
$row_rsOptinGroups = mysql_fetch_assoc($rsOptinGroups);
$totalRows_rsOptinGroups = mysql_num_rows($rsOptinGroups);










// redirect to custom contact page if required
if(!isset($_POST['sendmail']) && ((isset($row_rsRegionContact['contactpageURL']) && $row_rsRegionContact['contactpageURL']!="") ||  (isset($row_rsMailPrefs['contactpageURL']) && $row_rsMailPrefs['contactpageURL']!=""))) {
	// region field kept for backwards compatibility - mail prefs now takes precidence
	$url = ($row_rsMailPrefs['contactpageURL']!="") ? $row_rsMailPrefs['contactpageURL'] :  $row_rsRegionContact['contactpageURL'] ;
	header("location: ".$url); exit;
}

if(isset($_POST['sendmail']) && isset($row_rsMailPrefs['confirmationURL']) && $row_rsMailPrefs['confirmationURL']!="") {
	header("location: ".$row_rsMailPrefs['confirmationURL']); exit;
}


if (isset($_REQUEST['directoryID']) && $_REQUEST['directoryID']!="") {
$fb_name = $row_rsDirectoryContact['name'];
$fb_address = $row_rsDirectoryContact['address1']."\n". $row_rsDirectoryContact['address2']."\n". $row_rsDirectoryContact['address3']."\n". $row_rsDirectoryContact['address4']."\n".$row_rsDirectoryContact['postcode'];
$fb_phone = $row_rsDirectoryContact['telephone'];
$fb_fax = $row_rsDirectoryContact['fax'];
$fb_email = (isset($row_rsDirectoryContact['email']) && $row_rsDirectoryContact['email'] != "") ? $row_rsDirectoryContact['email'] : $row_rsPreferences['contactemail'] ; 
// if organisation has an email send it there otherwise to main email
} else {
$fb_name = ((isset($row_rsRegionContact['title']) && $row_rsRegionContact['title']!="") ? $row_rsRegionContact['title'] : $row_rsPreferences['orgname']);
$fb_address = isset($row_rsRegionContact['address']) ? $row_rsRegionContact['address'] : $row_rsPreferences['orgaddress'];
$fb_phone = isset($row_rsRegionContact['telephone']) ? $row_rsRegionContact['telephone'] : $row_rsPreferences['orgphone'];
$fb_fax = isset($row_rsRegionContact['fax']) ? $row_rsRegionContact['fax'] : $row_rsPreferences['orgfax'];
$fb_email = (isset($row_rsRegionContact['email']) && $row_rsRegionContact['email']!= "") ? $row_rsRegionContact['email'] : $row_rsPreferences['contactemail'];

$fb_skype = (isset($row_rsRegionContact['skypeID']) && $row_rsRegionContact['skypeID'] !="") ? $row_rsRegionContact['skypeID'] : $row_rsPreferences['orgskype'];
$fb_openinghours = $row_rsPreferences['openinghours'];
$fb_mapurl = $row_rsPreferences['mapURL'];
$fb_lat = $row_rsPreferences['maplat'];
$fb_long =$row_rsPreferences['maplong'];
$fb_gapi = $row_rsPreferences['googlemapsAPI'];
}
?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = $row_rsMailPrefs['contacttitle']; echo $pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<meta name="description" content="<?php echo $row_rsMailPrefs['contactmetadescription']; ?>" />
<link href="/SpryAssets/SpryCollapsiblePanel.css" rel="stylesheet"  />
<link href="/SpryAssets/SpryValidationTextField.css" rel="stylesheet"  />
<link href="/SpryAssets/SpryValidationTextarea.css" rel="stylesheet"  />
<link href="/SpryAssets/SpryValidationSelect.css" rel="stylesheet"  />
<link href="/mail/css/contactDefault.css" rel="stylesheet"  />
<style>
<!--
 <?php $required = 0;
if($row_rsMailPrefs['allowattachments']!=1) {
?> #rowAttachment {
display:none !important;
}
<?php
}
?> <?php if($row_rsMailPrefs['askcompany']!=1) {
?> #rowCompany {
display:none !important;
}
<?php
}
?> <?php if($row_rsMailPrefs['companyrequired']==1) {
$required++;
?> #rowCompany .required {
display:inline;
}
<?php
}
?> <?php if($row_rsMailPrefs['askname']!=1) {
?> #rowName {
display:none !important;
}
<?php
}
?> <?php if($row_rsMailPrefs['namerequired']==1) {
$required++;
?> #rowName .required {
display:inline;
}
<?php
}
?> <?php if($row_rsMailPrefs['askdob']!=1) {
?> #rowDOB {
display:none !important;
}
<?php
}
?>  <?php if($row_rsMailPrefs['dobrequired']==1) {
$required++;
?> #rowDOB .required {
display:inline;
}
<?php
}
?>  <?php if($row_rsMailPrefs['askemail']!=1) {
?> #rowEmail {
display:none !important;
}
<?php
}
?> <?php if($row_rsMailPrefs['emailconfirm']!=1) {
?> #rowEmail2 {
display:none !important;
}
<?php
}
?>  <?php if($row_rsMailPrefs['emailrequired']==1) {
$required++;
?> #rowEmail .required, #rowEmail2 .required {
display:inline;
}
<?php
}
?> <?php if($row_rsMailPrefs['askmessage']!=1) {
?> #rowMessage {
display:none !important;
}
<?php
}
?> <?php if($row_rsMailPrefs['messagerequired']==1) {
$required++;
?> #rowMessage .required {
display:inline;
}
<?php
}
?> <?php if($row_rsMailPrefs['asktelephone']!=1) {
?> #rowTelephone {
display:none !important;
}
<?php
}
?> <?php if($row_rsMailPrefs['telephonerequired']==1) {
$required++;
?> #rowTelephone .required {
display:inline;
}
<?php
}
?> <?php if($row_rsMailPrefs['askaddress']!=1) {
?> #rowAddress {
display:none !important;
}
<?php
}
?> <?php if($row_rsMailPrefs['addressrequired']==1) {
$required++;
?> #rowAddress .required {
display:inline;
}
<?php
}
?><?php if($row_rsMailPrefs['askdiscovered']!=1) {
?> #rowDiscovered {
display:none !important;
}
<?php
}
?> <?php if($row_rsMailPrefs['discoveredrequired']==1) {
$required++;
?> #rowDiscovered .required {
display:inline;
}
<?php
}
?> <?php if($row_rsMailPrefs['askcustom']!=1) {
?> #rowCustom {
display:none !important;
}
<?php
}
?> <?php if($row_rsMailPrefs['customrequired']==1) {
$required++;
?> #rowCustom .required {
display:inline;
}
<?php
}
?><?php if($row_rsMailPrefs['asksubject']<1) {
?> #rowSubject {
display:none !important;
}
<?php
}
?> <?php if($row_rsMailPrefs['askrespondby']!=1) {
?> #rowRespondBy {
display:none !important;
}
<?php
}
?>  <?php if($row_rsMailPrefs['addtocontacts']!=1) {
?> #optingroups {
display:none !important;
}
<?php
}
?> <?php if(mysql_num_rows($rsRecipients)==0) {
 echo "#rowTo { display: none !important; }";
}
else {
?> #rowTo .required {
display:inline;
}
<?php
}
if ($row_rsPreferences['emailoptintype'] ==0) {
 echo ".emailoptin { display:none !important; }";
}
 if ($row_rsPreferences['partneremailoptintype'] ==0) {
 echo ".partneremailoptin { display:none !important; }";
}
?>
-->
</style>
<?php if((isset($_SESSION['showCaptcha']) && $_SESSION['showCaptcha'] == true) || (isset($row_rsMailPrefs['useCaptcha']) && $row_rsMailPrefs['useCaptcha']==1)) {// show captcha 
  if(($row_rsMailPrefs['captchatype']==2 || $row_rsMailPrefs['captchatype']==3)  && trim($row_rsPreferences['recaptcha_site_key'])!="") { ?>
<script src='https://www.google.com/recaptcha/api.js' async defer></script>
<script>
/* function below only used for invisbile reCaptcha [3] */
       function onContactSubmit(token) {
         document.getElementById("contactform").submit();
       }
</script>
<?php } // end is reCaptcha 
 } // end show captcha ?>
<script src="/core/scripts/formUpload.js"></script>
<script src="/SpryAssets/SpryValidationSelect.js"></script>
<script src="/SpryAssets/SpryValidationTextField.js"></script>
<script src="/SpryAssets/SpryValidationTextarea.js"></script>
<?php if ($row_rsMailPrefs['showmap']==1 && isset($row_rsPreferences['googlemapsAPI']) && $row_rsPreferences['googlemapsAPI'] !="") { ?>
<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo $row_rsPreferences['googlemapsAPI']; ?>&v=3" ></script>
<script>

var map;
var markers = [];
var infowindow = [];
var initLatitude = <?php echo isset($row_rsPreferences['defaultlatitude']) ? $row_rsPreferences['defaultlatitude'] : 0; ?>;
var initLongitude = <?php echo isset($row_rsPreferences['defaultlongitude']) ? $row_rsPreferences['defaultlongitude'] : 0; ?>;
var initZoom = <?php echo isset($row_rsPreferences['defaultzoom']) ? $row_rsPreferences['defaultzoom'] : 2; ?>;


$(document).ready(function(e) {	
   var mapOptions = {
        zoom: initZoom ,
        center: new google.maps.LatLng(initLatitude, initLongitude),
        scaleControl: true,
        overviewMapControl: true,
        overviewMapControlOptions:{opened:true},
        mapTypeId: google.maps.MapTypeId.ROADMAP,
		streetViewControl:true,
		scrollwheel: false
	};
	map = new google.maps.Map(document.getElementById('googlemap'), mapOptions);
	var latLng = new google.maps.LatLng(<?php echo $row_rsPreferences['defaultlatitude']; ?>,<?php echo $row_rsPreferences['defaultlongitude']; ?>);
  var marker = new google.maps.Marker({position: latLng, map: map});
  $('#googlemap').show();
	
	
});

//]]>
</script>
<?php } ?>
<script>
//<![CDATA[ 	  
function get_check_value()
{
var c_value = "";
for (var i=0; i < document.contactform.reply_using.length; i++)
   {
   if (document.contactform.reply_using[i].checked)
      {
      c_value = c_value + document.contactform.reply_using[i].value + "\n";
      }
   }
   return c_value;
}

	 
function validateForm()
{
	var errors = '';
	if (document.getElementById('phone')) {
	if (document.getElementById('phone').value == "" && get_check_value() == 3) errors += "If you want a reply via post, please enter a postal address.\n";
	if (document.getElementById('address').value == "" && get_check_value() == 2 ) errors += "If you want a telephone reply, please provide a telephone number.\n";
	}
	return errors;
 }
 //]]></script>
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" -->
  <div id="pageContactUs" class="container pageBody clearfix" itemscope itemtype="http://schema.org/Organization">
  <div class="crumbs">
    <div><span class="you_are_in">You are in: </span><a href="/">Home</a><span class="separator">&nbsp;&rsaquo;&nbsp;</span>
      <?php if (isset($_REQUEST['directoryID']) && $_REQUEST['directoryID']!="") { ?>
      <a href="/directory/">Directory</a><span class="separator">&nbsp;&rsaquo;&nbsp;</span><a href="/directory/?directory.php?directoryID=<?php echo intval($_REQUEST['directoryID']); ?>"><?php echo $fb_name; ?></a><span class="separator">&nbsp;&rsaquo;&nbsp;</span>
      <?php } ?>
      Contact Us</div>
  </div>
  <!-- end crumbs-->
  <div id="contactForm">
    <?php if (isset($_POST['sendmail']) && (!isset($error) || trim($error) == "")) { //mail sent so confirmation message ?>
    <h1><?php echo isset($replysubject) ? $replysubject : "Thank you"; ?></h1>
    <p><?php echo isset($_POST['responsemessage']) ? nl2br($_POST['responsemessage']) : "We have received your message."; ?></p>
    <p><a href="/" class="link_back"><i class="glyphicon glyphicon-arrow-left"></i> Back to home page</a></p>
    <?php } else { // mail not sent yet ?>
    <?php if(strlen(trim($row_rsMailPrefs['contactheader']))>5) { echo "<div id=\"contactHeader\">".articleMerge($row_rsMailPrefs['contactheader'])."</div>";
					  } else { // show default intro ?>
    <h1>Contact <?php echo $fb_name; ?></h1>
    <h3>To contact  us,
      <?php if($row_rsMailPrefs['livechat']==1) { ?>
      <a href="chat/index.php">connect with Live Chat</a> or
      <?php } ?>
      <?php if($row_rsMailPrefs['contactformactive']==1) { ?>
      please complete the form below
      <?php } if(isset($row_rsSitePrefs['twitterID'])) { ?>
      or join us on <a href="http://www.twitter.com/<?php echo $row_rsSitePrefs['twitterID']; ?>" target="_blank" id="linkTwitter" rel="noopener">Twitter</a>
      <?php } if(isset($row_rsSitePrefs['twitterID']) && isset($row_rsSitePrefs['facebookID'])) { ?>
      and
      <?php } if (isset($row_rsSitePrefs['facebookID'])) { ?>
        <a href="http://www.facebook.com/<?php echo urlencode($row_rsSitePrefs['facebookID']); ?>" target="_blank" id="linkFacebook" rel="noopener">Facebook</a>
        <?php } ?>
    </h3>
    <?php } // end show default intro ?>
    <?php require_once('../core/includes/alert.inc.php'); ?>
    <?php if($row_rsMailPrefs['contactformactive']==1) { ?>
    <form action="<?php echo $editFormAction; ?>" method="post" enctype="multipart/form-data" name="contactform" id="contactform" class="form-horizontal">
      <div id="rowTo" class="form-group">
        <label for="recipientID" class="col-md-3">For<span class="required">*</span>: </label>
        <div class="col-md-9">
          <select name="recipientID" id="recipientID">
            <option value="" <?php if (!isset($_POST['recipientID']) || $_POST['recipientID']=="") {echo "selected=\"selected\"";} ?>><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
            <?php $rows = mysql_num_rows($rsRecipients);
  if($rows > 0) {
do {  
?>
            <option value="<?php echo $row_rsRecipients['ID']; ?>" <?php if ($row_rsRecipients['ID']== $_POST['recipientID']) {echo "selected=\"selected\"";} ?>><?php echo $row_rsRecipients['recipient']; ?></option>
            <?php
} while ($row_rsRecipients = mysql_fetch_assoc($rsRecipients));
 
      mysql_data_seek($rsRecipients, 0);
	  $row_rsRecipients = mysql_fetch_assoc($rsRecipients);
  }
?>
          </select>
        </div>
      </div>
      <!-- end form-group-->
      
      <div id="rowName" class="form-group">
        <label for="full_name" class="col-md-3">Your name<span class="required">*</span>: </label>
        <div class="col-md-9"><span id="sprytextfield1">
          <input name="full_name" type="text"  id="full_name" value="<?php echo isset($_REQUEST['full_name']) ? htmlentities($_REQUEST['full_name'], ENT_COMPAT, "UTF-8") : htmlentities(trim($row_rsLoggedIn['firstname']." ".$row_rsLoggedIn['surname']), ENT_COMPAT, "UTF-8"); ?>" size="40" maxlength="50" placeholder="Your name" class="form-control" />
          <span class="textfieldRequiredMsg">A name is required.</span></span></div>
      </div>
      <!-- end form-group-->
      
      <div id="rowEmail" class="form-group">
        <label for="email" class="col-md-3">Your email<span class="required">*</span>:</label>
        <div class="col-md-9"><span id="sprytextfield2">
          <input name="email"  id="email" type="email" multiple value="<?php echo isset($_REQUEST['email']) ? htmlentities($_REQUEST['email'], ENT_COMPAT, "UTF-8") : htmlentities($row_rsLoggedIn['email'], ENT_COMPAT, "UTF-8"); ?>"  size="40" maxlength="50" placeholder="Your email"  class="form-control" />
          <span class="textfieldRequiredMsg">A contact email is required.</span><span class="textfieldInvalidFormatMsg">Invalid email address.</span></span></div>
      </div>
      <!-- end form-group-->
      
      <div id="rowEmail2" class="form-group">
        <label for="email2" class="col-md-3">Confirm email<span class="required">*</span>:</label>
        <div class="col-md-9">
          <input name="email2"  id="email2" type="text"  value="<?php echo isset($_REQUEST['email2']) ? htmlentities($_REQUEST['email2'], ENT_COMPAT, "UTF-8") : htmlentities($row_rsLoggedIn['email'], ENT_COMPAT, "UTF-8"); ?>"  size="40" maxlength="50" placeholder="Confirm email"  class="form-control" />
        </div>
      </div>
      <!-- end form-group-->
      
      <div id="rowCompany" class="form-group">
        <label for="company" class="col-md-3">Company name<span class="required">*</span>:</label>
        <div class="col-md-9"><span id="sprytextfield3">
          <input name="company" type="text"  id="company" value="<?php echo isset($_REQUEST['company']) ? htmlentities($_REQUEST['company'], ENT_COMPAT, "UTF-8") : htmlentities($row_rsLoggedIn['name'], ENT_COMPAT, "UTF-8"); ?>" size="40" maxlength="50" placeholder="Company name"  class="form-control" />
          <span class="textfieldRequiredMsg">A name is required.</span></span></div>
      </div>
      <!-- end form-group-->
      
      <div id="rowTelephone" class="form-group">
        <label for="phone" class="col-md-3">Telephone<span class="required">*</span>:</label>
        <div class="col-md-9"><span id="sprytextfield4">
          <input name="phone" type="text"  id="phone" size="40" maxlength="50" value="<?php echo isset($_REQUEST['phone']) ? htmlentities($_REQUEST['phone'], ENT_COMPAT, "UTF-8") : htmlentities($row_rsLoggedIn['telephone1'], ENT_COMPAT, "UTF-8"); ?>" placeholder="Telephone"  class="form-control" />
          <span class="textfieldRequiredMsg">A contact number is required.</span></span></div>
      </div>
      <!-- end form-group -->
      
      <div id="rowAddress" class="form-group">
        <label for="address" class="col-md-3">Address<span class="required">*</span>:</label>
        <div class="col-md-9"><span id="sprytextarea2">
          <textarea name="address"  rows="4"  id="address" placeholder="Address" class="form-control" ><?php echo isset($_REQUEST['address']) ? htmlentities($_REQUEST['address'], ENT_COMPAT, "UTF-8") : trim($row_rsLoggedIn['address1']."\n".$row_rsLoggedIn['address2']."\n".$row_rsLoggedIn['address3']."\n".$row_rsLoggedIn['postcode']); ?></textarea>
          <span class="textareaRequiredMsg">A value is required.</span></span></div>
      </div>
      <!-- end form-group -->
      
      <div id="rowDOB" class="form-group">
        <label  class="col-md-3">Date of birth<span class="required">*</span>:</label>
        <div class="col-md-9">
          <input type="hidden" name="date_of_birth" id="date_of_birth" value="<?php $setvalue = isset($_REQUEST['date_of_birth']) ? htmlentities($_REQUEST['date_of_birth'], ENT_COMPAT, "UTF-8") : ""; echo $setvalue; ?>" />
          <?php $inputname = "date_of_birth"; $startyear = date('Y') - 100; include("../core/includes/datetimeinput.inc.php"); ?>
        </div>
      </div>
      <!-- end form-group-->
      
      <?php if($row_rsMailPrefs['asksubject']==2) { ?>
      <div id="rowSubject" class="form-group">
        <label for="subject" class="col-md-3">Subject<span class="required">*</span>:</label>
        <div class="col-md-9">
          <select name="subject"  class="form-control">
            <?php
do {  
?>
            <option value="<?php echo $row_rsSubject['description']?>"><?php echo $row_rsSubject['description']?></option>
            <?php
} while ($row_rsSubject = mysql_fetch_assoc($rsSubject));
  $rows = mysql_num_rows($rsSubject);
  if($rows > 0) {
      mysql_data_seek($rsSubject, 0);
	  $row_rsSubject = mysql_fetch_assoc($rsSubject);
  }
?>
          </select>
        </div>
      </div>
      <!-- end form-group -->
      
      <?php } else { ?>
      <div id="rowSubject" class="form-group">
        <label for="subject" class="col-md-3">Subject<span class="required">*</span>:</label>
        <div class="col-md-9">
          <input name="subject" type="text"  id="subject" value="<?php if(isset($_REQUEST['subject'])) { echo htmlentities($_REQUEST['subject']); } else { echo isset($row_rsMailPrefs['defaultsubject']) ?$row_rsMailPrefs['defaultsubject'] : "Web site contact" ; } ?>"  size="40" maxlength="50" placeholder="Subject"  class="form-control" />
        </div>
      </div>
      <!-- end form-group-->
      
      <?php } 
						  $custom_field = preg_replace("/[^a-zA-Z0-9_\-]/", "_", $row_rsMailPrefs['text_custom']); ?>
      <div id="rowCustom" class="form-group">
        <label for="<?php echo $custom_field; ?>" class="col-md-3"><?php echo htmlentities($row_rsMailPrefs['text_custom'],ENT_COMPAT,"UTF-8"); ?><span class="required">*</span>:</label>
        <div class="col-md-9">
          <input name="<?php echo $custom_field; ?>" type="text"  class="form-control <?php echo $custom_field; ?>" id="<?php echo $custom_field; ?>" value="<?php echo (isset($_REQUEST[$custom_field])) ? htmlentities($_REQUEST[$custom_field]) : "";   ?>"  size="40" maxlength="255" placeholder="<?php echo htmlentities($row_rsMailPrefs['text_custom'],ENT_COMPAT,"UTF-8"); ?>" />
        </div>
      </div>
      <!-- end form-group-->
      
      <div id="rowMessage" class="form-group">
        <?php /// HONEYPOT SPAM TRAP - HIDDEN FIELDS
				   if(isset($_SESSION['honeypot_swap']) && $_SESSION['honeypot_swap'] !="") {
					   echo "<label for =\"".$_SESSION['honeypot_field']."\" class=\"hp\">Message:</label><input id=\"".$_SESSION['honeypot_field']."\" name=\"".$_SESSION['honeypot_field']."\" type=\"text\" class=\"hp form-control\" value=\"\">";
					   // token to validate unique send
					   echo "<input name=\"hp_token\" type=\"hidden\"  value=\"".htmlentities($_SESSION['honeypot_token'])."\">"; 
				   } ?>
        <label for="<?php echo (isset($_SESSION['honeypot_swap']) && $_SESSION['honeypot_swap'] !="") ? htmlentities($_SESSION['honeypot_swap']) : "message"; ?>" class="col-md-3"><?php echo $row_rsMailPrefs['messagelabel']; ?><span class="required">*</span>:</label>
        <div class="col-md-9"><span id="sprytextarea1">
          <textarea name="<?php echo (isset($_SESSION['honeypot_swap']) && $_SESSION['honeypot_swap'] !="") ? htmlentities($_SESSION['honeypot_swap']) : "message"; ?>"  rows="6"  class="form-control"  id="<?php echo (isset($_SESSION['honeypot_swap']) && $_SESSION['honeypot_swap'] !="") ? htmlentities($_SESSION['honeypot_swap']) : "message"; ?>" placeholder="<?php echo $row_rsMailPrefs['messagelabel']; ?>"><?php echo isset($_POST['message']) ? htmlentities($_POST['message'], ENT_COMPAT, "UTF-8") : isset($_GET['message']) ? htmlentities($_GET['message'], ENT_COMPAT, "UTF-8") : ""; // DELIBERATELY DONE THIS WAY COMPATIBLE WITH HONEYTRAP ?></textarea>
          <span class="textareaRequiredMsg"> A message is required.</span></span></div>
      </div>
      <!-- end form-group-->
      
      <div id="rowAttachment" class="form-group">
        <label for="filename[0]" class="col-md-3">Attachment<span class="required">*</span>:</label>
        <div class="col-md-9">
          <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo defined("MAX_UPLOAD") ? MAX_UPLOAD : "2000000"; ?>" />
          <input type="file" name="filename[0]" id="filename[0]" />
        </div>
      </div>
      <!-- end form-group-->
      
      <div id="rowRespondBy" class="form-group">
        <label class="col-md-3">I would prefer you to respond using<span class="required">*</span>:</label>
        <div class="col-md-9" id="input_reply_using">
          <label>
            <input <?php if (!(strcmp(@$_REQUEST['reply_using'],"1"))) {echo "checked=\"checked\"";} ?> name="reply_using" type="radio" value="1"  checked="checked" />
            Email</label>
          &nbsp;&nbsp;&nbsp;
          <label>
            <input <?php if (!(strcmp(@$_REQUEST['reply_using'],"2"))) {echo "checked=\"checked\"";} ?> name="reply_using" type="radio" value="2" />
            Telephone</label>
          &nbsp;&nbsp;&nbsp;
          <label>
            <input <?php if (!(strcmp(@$_REQUEST['reply_using'],"3"))) {echo "checked=\"checked\"";} ?> name="reply_using" type="radio" value="3" />
            Post</label>
        </div>
      </div>
      <!-- end form-group -->
      
      <?php if(isset($row_rsMailPrefs['text_callback_time']) && strlen(trim($row_rsMailPrefs['text_callback_option_1'].$row_rsMailPrefs['text_callback_option_2'].$row_rsMailPrefs['text_callback_option_3']))>0) { ?>
      <div id="rowCallbackTime" class="form-group">
        <label class="col-md-3"><?php echo htmlentities($row_rsMailPrefs['text_callback_time'], ENT_COMPAT, "UTF-8"); ?><span class="required">*</span>:</label>
        <div class="col-md-9 form-inline">
          <?php if(trim($row_rsMailPrefs['text_callback_option_1'])!="") { ?>
          <label class="text-nowrap">
            <input  name="callback_time" type="radio" value="<?php echo htmlentities($row_rsMailPrefs['text_callback_option_1'], ENT_COMPAT, "UTF-8"); ?>" />
            <?php echo htmlentities($row_rsMailPrefs['text_callback_option_1'], ENT_COMPAT, "UTF-8"); ?></label>
          &nbsp;&nbsp;&nbsp;
          <?php } ?>
          <?php if(trim($row_rsMailPrefs['text_callback_option_2'])!="") { ?>
          <label class="text-nowrap">
            <input  name="callback_time" type="radio" value="<?php echo htmlentities($row_rsMailPrefs['text_callback_option_2'], ENT_COMPAT, "UTF-8"); ?>" />
            <?php echo htmlentities($row_rsMailPrefs['text_callback_option_2'], ENT_COMPAT, "UTF-8"); ?></label>
          &nbsp;&nbsp;&nbsp;
          <?php } ?>
          <?php if(trim($row_rsMailPrefs['text_callback_option_3'])!="") { ?>
          <label class="text-nowrap">
            <input  name="callback_time" type="radio" value="<?php echo htmlentities($row_rsMailPrefs['text_callback_option_3'], ENT_COMPAT, "UTF-8"); ?>" />
            <?php echo htmlentities($row_rsMailPrefs['text_callback_option_3'], ENT_COMPAT, "UTF-8"); ?></label>
          &nbsp;&nbsp;&nbsp;
          <?php } ?>
        </div>
      </div>
      <!-- end form-group -->
      <?php } // end is callback times  ?>
      <div class="emailoptin form-group">
        <div class="col-md-offset-3 col-md-9">
          <input <?php if (isset($_REQUEST['emailoptin']) || ($row_rsPreferences['emailoptinset']==1 && !isset($_REQUEST['token']))) {echo "checked=\"checked\"";} ?> name="emailoptin" type="checkbox" id="emailoptin" value="1"  />
          <?php echo $row_rsPreferences['emailoptintext']; ?></div>
        <div id="optingroups">
          <input name="updateoptingroups" type="hidden" value="1" />
          <label>I am interested in:</label>
          <?php if ($totalRows_rsOptinGroups > 0) { // Show if recordset not empty ?>
            <?php $lasttype = ""; do { 
	  if(isset($row_rsOptinGroups['grouptype']) && $row_rsOptinGroups['grouptype']!=$lasttype) { 
	  $lasttype = $row_rsOptinGroups['grouptype'];
	  echo "<h3>".$lasttype."</h3>"; } ?>
          <label class="text-nowrap fixedwidth">
            <input type="checkbox" name="optingroups[<?php echo $row_rsOptinGroups['ID']; ?>]" <?php if(isset($_POST['optingroups'][$row_rsOptinGroups['ID']])) { echo "checked=\"checked\""; } ?>  />
            &nbsp;<?php echo $row_rsOptinGroups['groupname']; ?>&nbsp;&nbsp;&nbsp;</label>
          <?php } while ($row_rsOptinGroups = mysql_fetch_assoc($rsOptinGroups)); ?>
          <?php } // Show if recordset not empty ?>
        </div>
      </div>
      <!-- end form-group-->
      
      <div id="rowDiscovered" class="form-group">
        <label for="discovered" class="col-md-3">How did you find out about us?<span class="required">*</span>:</label>
        <div class="col-md-9"><span id="spryselect1">
          <select name="discovered"  id="discovered" class="form-control">
            <option value="" <?php if (!(strcmp("", @$_REQUEST['discovered']))) {echo "selected=\"selected\"";} ?>><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
            <?php
do {  
?>
            <option value="<?php echo $row_rsDiscovered['description']?>"<?php if (!(strcmp($row_rsDiscovered['description'], @$_REQUEST['discovered']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsDiscovered['description']?></option>
            <?php
} while ($row_rsDiscovered = mysql_fetch_assoc($rsDiscovered));
  $rows = mysql_num_rows($rsDiscovered);
  if($rows > 0) {
      mysql_data_seek($rsDiscovered, 0);
	  $row_rsDiscovered = mysql_fetch_assoc($rsDiscovered);
  }
?>
          </select>
          <span class="selectRequiredMsg">Please select an item.</span></span></div>
      </div>
      <!-- end form-group -->
      
      <?php if((isset($_SESSION['showCaptcha']) && $_SESSION['showCaptcha'] == true) || (isset($row_rsMailPrefs['useCaptcha']) && $row_rsMailPrefs['useCaptcha']==1)) { // show captcha ?>
      <?php if(!isset($row_rsMailPrefs['captchatype']) || $row_rsMailPrefs['captchatype']==1) { ?>
      <div class="form-group">
        <div class="col-md-offset-3 col-md-9"><img src="/core/includes/random_image.php" alt="Security image" /></div>
      </div>
      <!-- end for-group-->
      
      <div class="form-group">
        <label for="captcha_answer" class="col-md-3">Security letters<span class="required">*</span>:</label>
        <div class="col-md-9">
          <input name="captcha_answer" type="text"  id="captcha_answer" value="Type the letters shown above" size="30" maxlength="30"  style="color:#999999" onfocus="this.value='';this.style.color= '#000000';"  class="form-control" />
        </div>
      </div>
      <!-- end form-group -->
      <?php } else if(($row_rsMailPrefs['captchatype']==2 || $row_rsMailPrefs['captchatype']==3) && trim($row_rsPreferences['recaptcha_site_key'])!="") {
		if($row_rsMailPrefs['captchatype']==2) {  ?>
      <div class="g-recaptcha" data-sitekey="<?php echo $row_rsPreferences['recaptcha_site_key']; ?>"></div>
      <?php }//  reCaptcha 2
	  } // reCaptcha
	   } // end show captcha ?>
      <div id="rowSubmit" class="form-group">
        <label class="col-md-3">&nbsp;
          <?php if($required>0) { ?>
          (<span class="required" style="display:inline">*</span>required items)
          <?php } ?>
        </label>
        <div class="col-md-9">
          <button type="submit"  class="btn btn-primary<?php if($row_rsMailPrefs['captchatype']==3) {  ?> g-recaptcha" data-sitekey="<?php echo $row_rsPreferences['recaptcha_site_key']; ?>" data-callback="onContactSubmit<?php } ?>"  >Send</button>
          <input name="logmail" type="hidden" id="logmail" value="true" />
          <input name="sendmail" type="hidden" value="true" />
          <input name="returnURL" type="hidden" value="<?php echo isset($_REQUEST['returnURL']) ? htmlentities($_REQUEST['returnURL'], ENT_COMPAT, "UTF-8") : ""; ?>" />
          <input name="mailfolderID" type="hidden" id="mailfolderID" value="1" />
          <input name="autoreply" type="hidden" id="autoreply" value="<?php echo isset($_GET['autoreply']) ? $_GET['autoreply'] : 1 ; ?>" />
          <input name="createddatetime" type="hidden" id="createddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
          <input type="hidden" id="directoryID" name="directoryID" value="<?php echo isset($_GET['directoryID']) ? $_GET['directoryID'] : ""; ?>" />
          <input type="hidden" id="key" name="key" value="<?php echo md5($fb_email.@PRIVATE_KEY) ?>" />
          <input type="hidden" id="recipient" name="recipient" value="<?php echo $fb_email; ?>" />
          <?php if(isset($logemail)) { ?>
          <input type="hidden" name="MM_insert" value="contactform" />
          <?php } ?>
        </div>
      </div>
      <!-- end form-group --> 
      <script>
				  <?php if($row_rsMailPrefs['namerequired']==1) { ?>
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1");
<?php } if($row_rsMailPrefs['emailrequired']==1) { ?>
var sprytextfield2 = new Spry.Widget.ValidationTextField("sprytextfield2", "email");
<?php } if($row_rsMailPrefs['messagerequired']==1) { ?>
var sprytextarea1 = new Spry.Widget.ValidationTextarea("sprytextarea1");
<?php } if($row_rsMailPrefs['companyrequired']==1) { ?>
var sprytextfield3 = new Spry.Widget.ValidationTextField("sprytextfield3");
<?php } if($row_rsMailPrefs['telephonerequired']==1) { ?>
var sprytextfield4 = new Spry.Widget.ValidationTextField("sprytextfield4");
<?php } if($row_rsMailPrefs['addressrequired']==1) { ?>
var sprytextarea2 = new Spry.Widget.ValidationTextarea("sprytextarea2");
<?php } if($row_rsMailPrefs['discoveredrequired']==1) { ?>
var spryselect1 = new Spry.Widget.ValidationSelect("spryselect1");
<?php } ?>
                  </script>
    </form>
    <?php } // end form active ?>
    <?php } // mail not sent yet ?>
  </div>
  <!-- end contact form-->
  <div class="googlemap">
    <div  id="googlemap"></div>
    <?php if(isset($row_rsPreferences['defaultlongitude']) && isset($row_rsPreferences['defaultlatitude']) && $row_rsPreferences['defaultlatitude']>0) { ?>
    <p><a href="https://www.google.com/maps/dir/Current+Location/<?php echo $row_rsPreferences['defaultlatitude']; ?>,<?php echo $row_rsPreferences['defaultlongitude']; ?>" target="_blank" class="button" rel="noopener">Get directions</a></p>
    <?php } ?>
  </div>
  <!-- end google map -->
  
  <?php if($row_rsMailPrefs['showcontact']==1) { // show extra contact info ?>
  <div id="contactInformation">
    <?php if(isset($fb_openinghours) && $fb_openinghours !=""){ ?>
    <h2>Opening Hours: </h2>
    <p class="contactDetails"><?php echo nl2br($fb_openinghours); ?></p>
    <?php } ?>
    <?php if (@$fb_address.@$fb_fax.@$fb_phone.@$fb_skype != "") { ?>
    <p>You can also contact us in the following ways:</p>
    <?php } ?>
    <?php if (isset($fb_address) && $fb_address!="") { ?>
    <div id="contactAddress">
      <h2>By post
        <?php if($row_rsMailPrefs['inperson']==1) { ?>
        or in person
        <?php } ?>
        : </h2>
      <address class="contactDetails">
      <p><span itemprop="name"><?php echo $fb_name; ?></span><br>
        <span itemprop="address"><?php echo nl2br($fb_address); ?></span></p>
      </address>
      <?php if (isset($row_rsPreferences['mapURL'])) { ?>
        <p><a href="<?php echo $row_rsPreferences['mapURL']; ?>" target="_blank" rel="noopener">Click here for a map</a></p>
        <?php } ?>
    </div>
    <!-- end contactAddress -->
    <?php } ?>
    <?php if (isset($fb_phone) && $fb_phone!="") { ?>
    <div id="contactPhone">
      <h2 >By phone: </h2>
      <p class="contactDetails"  itemprop="telephone"><?php echo $fb_phone; ?></p>
    </div>
    <!-- end contactPhone -->
    <?php } ?>
    <?php if (isset($fb_fax) && trim($fb_fax)!="") { ?>
    <div id="contactFax">
      <h2 >By fax:</h2>
      <p class="contactDetails"   itemprop="faxNumber"><?php echo $fb_fax; ?> </p>
    </div>
    <!-- end contactFax -->
    <?php } ?>
    <?php if($row_rsMailPrefs['showemail']==1) { ?>
    <div id="emailaddress">
      <h2>By email:</h2>
      <script>
	 document.write("<p itemprop='email'>"); writeEmail('<?php $email = explode("@",$fb_email); echo $email[0]."','".$email[1]; ?>'); document.write("</p>"); </script> 
    </div>
    <!-- end emailaddress -->
    <?php } ?>
    <?php if (isset($fb_skype) && $fb_skype!="") { ?>
    <div id="contactSkype">
      <h2 >Skype us:</h2>
      <script src="https://secure.skypeassets.com/i/scom/js/skype-uri.js"></script>
      <div id="SkypeButton_Call_fullbhuna"> 
        <script>// <![CDATA[
Skype.ui({ "name": "chat", "element": "SkypeButton_Call_fullbhuna", "participants": ["<?php echo $fb_skype; ?>"], "imageSize": 24 });
// ]]></script> 
      </div>
      <!-- end Skype -->
      <?php } ?>
      <?php if(isset($row_rsMailPrefs['webdevelopersURL']) && $row_rsMailPrefs['webdevelopersURL'] !="") { ?>
      <p id="webDevelopersLink">If you are having technical problems with this site, <a href="<?php echo $row_rsMailPrefs['../contact/webdevelopersURL']; ?>" title="Web designers link" target="_blank" rev="vote-for" rel="noopener">contact the web developers here</a>.</p>
      <?php } ?>
    </div>
    <!--  end contactInformation -->
    <?php } // end show extra info ?>
    <?php echo articleMerge($row_rsMailPrefs['contactfooter']); ?> </div>
  <!-- end pageContactUs --> 
  
  <!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsRegionContact);

mysql_free_result($rsPreferences);

mysql_free_result($rsDirectoryContact);

mysql_free_result($rsSitePrefs);


mysql_free_result($rsDiscovered);

mysql_free_result($rsLoggedIn);

mysql_free_result($rsSubject);

mysql_free_result($rsRecipients);

mysql_free_result($rsMailPrefs);

?>
