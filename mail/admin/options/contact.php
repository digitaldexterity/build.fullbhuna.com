<?php require_once('../../../Connections/aquiescedb.php'); ?><?php require_once('../../../core/includes/adminAccess.inc.php'); ?>
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

$MM_restrictGoTo = "/login/index.php?notloggedin=true&alert=".urlencode("You need to be logged in as an Administrator to access this page.");
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

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE mailprefs SET contactformactive=%s, contactpageURL=%s, useCaptcha=%s, captchatype=%s, allowattachments=%s, showemail=%s, showcontact=%s, inperson=%s, confirmationURL=%s, askcompany=%s, companyrequired=%s, askname=%s, namerequired=%s, askdob=%s, dobrequired=%s, askemail=%s, emailrequired=%s, emailconfirm=%s, asktelephone=%s, telephonerequired=%s, askaddress=%s, addressrequired=%s, askdiscovered=%s, discoveredrequired=%s, asksubject=%s, askmessage=%s, messagerequired=%s, messagelabel=%s, askcustom=%s, customrequired=%s, text_custom=%s, showmap=%s, addtocontacts=%s, contacttitle=%s, contactheader=%s, contactfooter=%s, contactmetadescription=%s, responsesubject=%s, responsemessage=%s, defaultsubject=%s, askrespondby=%s, html=%s, autoreply=%s, text_callback_time=%s, text_callback_option_1=%s, text_callback_option_2=%s, text_callback_option_3=%s, formbuilderID=%s WHERE ID=%s",
                       GetSQLValueString(isset($_POST['contactformactive']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['contactpageURL'], "text"),
                       GetSQLValueString($_POST['useCaptcha'], "int"),
                       GetSQLValueString($_POST['captchatype'], "int"),
                       GetSQLValueString(isset($_POST['allowattachments']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['showemai']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['showcontact']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['inperson']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['confirmationURL'], "text"),
                       GetSQLValueString(isset($_POST['askcompany']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['companyrequired']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['askname']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['namerequired']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['askdob']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['dobrequired']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['askemail']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['emailrequired']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['emailconfirm']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['asktelephone']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['telephonerequired']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['askaddress']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['addressrequired']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['askdiscovered']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['discoveredrequired']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['asksubject'], "int"),
                       GetSQLValueString(isset($_POST['askmessage']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['messagerequired']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['messagelabel'], "text"),
                       GetSQLValueString(isset($_POST['askcustom']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['customrequired']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['text_custom'], "text"),
                       GetSQLValueString(isset($_POST['showmap']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['addtocontacts']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['contacttitle'], "text"),
                       GetSQLValueString($_POST['contactheader'], "text"),
                       GetSQLValueString($_POST['contactfooter'], "text"),
                       GetSQLValueString($_POST['contactmetadescription'], "text"),
                       GetSQLValueString($_POST['responsesubject'], "text"),
                       GetSQLValueString($_POST['responsemessage'], "text"),
                       GetSQLValueString($_POST['defaultsubject'], "text"),
                       GetSQLValueString(isset($_POST['askrespondby']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['html']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['autoreply']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['text_callback_time'], "text"),
                       GetSQLValueString($_POST['text_callback_option_1'], "text"),
                       GetSQLValueString($_POST['text_callback_option_2'], "text"),
                       GetSQLValueString($_POST['text_callback_option_3'], "text"),
                       GetSQLValueString($_POST['formbuilderID'], "int"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
	$update = "UPDATE preferences SET recaptcha_site_key = ".GetSQLValueString($_POST['recaptcha_site_key'], "text").", recaptcha_secret_key = ".GetSQLValueString($_POST['recaptcha_secret_key'], "text")." WHERE ID = ".GetSQLValueString($_POST['ID'], "int");
	mysql_select_db($database_aquiescedb, $aquiescedb);
  	mysql_query($update, $aquiescedb) or die(mysql_error());
	if(isset($_POST['deleterecipientID']) && intval($_POST['deleterecipientID'])>0) {
		$delete = "DELETE FROM mailrecipient WHERE ID = ".intval($_POST['deleterecipientID']);
		mysql_select_db($database_aquiescedb, $aquiescedb);
  		mysql_query($delete, $aquiescedb) or die(mysql_error());
		$updateGoTo = "contact.php?defaultTab=3";
		
	} else 	if(isset($_POST['recipientemail']) && $_POST['recipientemail'] !="") {
		$insert = "INSERT INTO mailrecipient (recipient, email, regionID, createdbyID, createddatetime) VALUES (".GetSQLValueString($_POST['recipientname'], "text").
		",".GetSQLValueString($_POST['recipientemail'], "text").
		",".GetSQLValueString($_POST['regionID'], "int").
		",".GetSQLValueString($_POST['createdbyID'], "int").",NOW())";
		mysql_select_db($database_aquiescedb, $aquiescedb);
  		mysql_query($insert, $aquiescedb) or die(mysql_error());
		$updateGoTo = "contact.php?defaultTab=4";
	} else {
  $updateGoTo = "../index.php";
	}
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo)); exit;
}

$colname_rsMailPrefs = "1";
if (isset($regionID)) {
  $colname_rsMailPrefs = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsMailPrefs = sprintf("SELECT * FROM mailprefs WHERE ID = %s", GetSQLValueString($colname_rsMailPrefs, "int"));
$rsMailPrefs = mysql_query($query_rsMailPrefs, $aquiescedb) or die(mysql_error());
$row_rsMailPrefs = mysql_fetch_assoc($rsMailPrefs);
$totalRows_rsMailPrefs = mysql_num_rows($rsMailPrefs);

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

$varRegionID_rsRecipients = "1";
if (isset($regionID)) {
  $varRegionID_rsRecipients = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsRecipients = sprintf("SELECT * FROM mailrecipient WHERE regionID = %s", GetSQLValueString($varRegionID_rsRecipients, "int"));
$rsRecipients = mysql_query($query_rsRecipients, $aquiescedb) or die(mysql_error());
$row_rsRecipients = mysql_fetch_assoc($rsRecipients);
$totalRows_rsRecipients = mysql_num_rows($rsRecipients);

if($totalRows_rsMailPrefs==0) {
	$insert = "INSERT INTO mailprefs (ID) VALUES (".$colname_rsMailPrefs.")";
	mysql_query($insert, $aquiescedb) or die(mysql_error());
	header("location: contact.php"); exit;
	
}

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT * FROM preferences WHERE ID = ".$regionID."";
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);

$varRegionID_rsForms = "1";
if (isset($regionID)) {
  $varRegionID_rsForms = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsForms = sprintf("SELECT `form`.ID, `form`.formname FROM `form` WHERE (regionID = 0 OR regionID = %s) AND `form`.statusID = 1 ORDER BY formname ASC", GetSQLValueString($varRegionID_rsForms, "int"));
$rsForms = mysql_query($query_rsForms, $aquiescedb) or die(mysql_error());
$row_rsForms = mysql_fetch_assoc($rsForms);
$totalRows_rsForms = mysql_num_rows($rsForms);


if($totalRows_rsPreferences==0) {
	duplicateMySQLRecord ("preferences", 1, "ID", $regionID) ;
	header("location: contact.php"); exit;
}

?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="../../../Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Manage Contact Form"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" --><?php require_once('../../../core/tinymce/tinymce.inc.php'); ?>
<script src="../../../SpryAssets/SpryTabbedPanels.js"></script>
<script src="../../../SpryAssets/SpryValidationTextField.js"></script>
<link href="../../../SpryAssets/SpryTabbedPanels.css" rel="stylesheet" type="text/css" />
<link href="../../css/mailDefault.css" rel="stylesheet" type="text/css" />
<link href="../../../SpryAssets/SpryValidationTextField.css" rel="stylesheet" type="text/css" />
<script>
$(document).ready(function(e) {
	toggleFormBuilder();
    toggleCaptchaType();
	toggleRecaptcha();
	$('input[name=useCaptcha]').click(function() {
		toggleCaptchaType();
	});
});



function toggleCaptchaType() {
	if($('input[name=useCaptcha]:checked').val()==0) {
		$(".captchatype").hide();
	} else {
		$(".captchatype").show();
	}
}

function  toggleRecaptcha() {
	if(document.form1.captchatype.value==2 || document.form1.captchatype.value==3) {
		$('.recaptcha').show();
	} else {
		$('.recaptcha').hide();
	}
}

function toggleFormBuilder() {
	if(document.form1.formbuilderID.value=="") {
		$('#TabbedPanels1').removeClass("hide-form-item");
	} else {
		$('#TabbedPanels1').addClass("hide-form-item");
	}
}
</script>
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" --><div class="page mail">
   <?php require_once('../../../core/region/includes/chooseregion.inc.php'); ?>
 <h1><i class="glyphicon glyphicon-envelope"></i> Contact Page</h1>
 <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
 <li class="nav-item"><a href="/mail/contact.php" target="_blank" class="nav-link" rel="noopener" ><i class="glyphicon glyphicon-new-window"></i> View Contact Form</a></li>
 </ul></div></nav>
   
    <form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1">
      <fieldset class="form-inline">
    <label>Contact form active
     
        <input <?php if (!(strcmp($row_rsMailPrefs['contactformactive'],1))) {echo "checked=\"checked\"";} ?> name="contactformactive" type="checkbox" id="contactformactive" value="1"></label>
        &nbsp;&nbsp;&nbsp;
        
        <label>Send as HTML
                <input <?php if (!(strcmp($row_rsMailPrefs['html'],1))) {echo "checked=\"checked\"";} ?> name="html" type="checkbox" id="html" value="1" />
                </label>  &nbsp;&nbsp;&nbsp; <label>Use Form Builder form: <select name="formbuilderID" class="form-control" onChange="toggleFormBuilder()">
                  <option value="" <?php if (!(strcmp("", $row_rsMailPrefs['formbuilderID']))) {echo "selected=\"selected\"";} ?>>Choose...</option>
                  <?php
do {  
?>
                  <option value="<?php echo $row_rsForms['ID']?>"<?php if (!(strcmp($row_rsForms['ID'], $row_rsMailPrefs['formbuilderID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsForms['formname']?></option>
                  <?php
} while ($row_rsForms = mysql_fetch_assoc($rsForms));
  $rows = mysql_num_rows($rsForms);
  if($rows > 0) {
      mysql_data_seek($rsForms, 0);
	  $row_rsForms = mysql_fetch_assoc($rsForms);
  }
?>
                </select></label></fieldset>
      
   
      <div id="TabbedPanels1" class="TabbedPanels">
        <ul class="TabbedPanelsTabGroup">
          <li class="TabbedPanelsTab" tabindex="0">Form</li>
          <li class="TabbedPanelsTab" tabindex="0">Header</li>
          <li class="TabbedPanelsTab" tabindex="0">Footer</li>
          <li class="TabbedPanelsTab" tabindex="0">Auto Response</li>
          <li class="TabbedPanelsTab" tabindex="0">Alternate Recipients</li>
<li class="TabbedPanelsTab" tabindex="0">SEO</li>
</ul>
        <div class="TabbedPanelsContentGroup">
          <div class="TabbedPanelsContent"> 
            <fieldset>
        <legend>Collect the following information:</legend>
        <table class="form-table">
        <tr>
          <th>&nbsp;</th>
          <th>Ask&nbsp;&nbsp;&nbsp;</th>
          <th>Required</th>
          <th>Confirm</th>
        </tr>
        <tr>
          <td class="text-right">Name:</td>
          <td><input <?php if (!(strcmp($row_rsMailPrefs['askname'],1))) {echo "checked=\"checked\"";} ?> name="askname" type="checkbox" id="askname" value="1" onclick="if(this.checked===false) { this.form.namerequired.checked = false; }" />
          <label for="askname"></label></td>
          <td><input <?php if (!(strcmp($row_rsMailPrefs['namerequired'],1))) {echo "checked=\"checked\"";} ?> name="namerequired" type="checkbox" id="namerequired" value="1" onclick="if(this.checked===true) { this.form.askname.checked = true; }"/>
          </td>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td class="text-right">Email:</td>
          <td><label for="email">
            <input <?php if (!(strcmp($row_rsMailPrefs['askemail'],1))) {echo "checked=\"checked\"";} ?> name="askemail" type="checkbox" id="askemail" value="1" onclick="if(this.checked===false) { this.form.emailrequired.checked = false; }"  />
          </label></td>
          <td><input <?php if (!(strcmp($row_rsMailPrefs['emailrequired'],1))) {echo "checked=\"checked\"";} ?> name="emailrequired" type="checkbox" id="emailrequired" value="1" onclick="if(this.checked===true) { this.form.askemail.checked = true; }" />
         </td>
          <td><input <?php if (!(strcmp($row_rsMailPrefs['emailconfirm'],1))) {echo "checked=\"checked\"";} ?> name="emailconfirm" type="checkbox" id="emailconfirm" value="1" /></td>
        </tr>
        <tr>
          <td class="text-right">Company:</td>
          <td><input <?php if (!(strcmp($row_rsMailPrefs['askcompany'],1))) {echo "checked=\"checked\"";} ?> name="askcompany" type="checkbox" id="askcompany" value="1" onclick="if(this.checked===false) { this.form.companyrequired.checked = false; }"  /></td>
          <td><input <?php if (!(strcmp($row_rsMailPrefs['companyrequired'],1))) {echo "checked=\"checked\"";} ?> name="companyrequired" type="checkbox" id="companyrequired" value="1" onclick="if(this.checked===true) { this.form.askcompany.checked = true; }" />
         </td>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td class="text-right">Telephone:</td>
          <td><input <?php if (!(strcmp($row_rsMailPrefs['asktelephone'],1))) {echo "checked=\"checked\"";} ?> name="asktelephone" type="checkbox" id="asktelephone" value="1" onclick="if(this.checked===false) { this.form.telephonerequired.checked = false; }"  />
          </td>
          <td><input <?php if (!(strcmp($row_rsMailPrefs['telephonerequired'],1))) {echo "checked=\"checked\"";} ?> name="telephonerequired" type="checkbox" id="telephonerequired" value="1"  onclick="if(this.checked===true) { this.form.asktelephone.checked = true; }"/>
         </td>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td class="text-right">Address:</td>
          <td><input <?php if (!(strcmp($row_rsMailPrefs['askaddress'],1))) {echo "checked=\"checked\"";} ?> name="askaddress" type="checkbox" id="askaddress" value="1" onclick="if(this.checked===false) { this.form.addressrequired.checked = false; }" />
          </td>
          <td><input <?php if (!(strcmp($row_rsMailPrefs['addressrequired'],1))) {echo "checked=\"checked\"";} ?> name="addressrequired" type="checkbox" id="addressrequired" value="1" onclick="if(this.checked===true) { this.form.askaddress.checked = true; }" /></td>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td class="text-right form-inline"><input name="text_custom" type="text" id="text_custom" style="text-align:right;" value="<?php echo $row_rsMailPrefs['text_custom']; ?>" size="30" maxlength="50" class="form-control" />:</td>
          <td><input <?php if (!(strcmp($row_rsMailPrefs['askcustom'],1))) {echo "checked=\"checked\"";} ?> name="askcustom" type="checkbox" id="askcustom" value="1" onclick="if(this.checked===false) { this.form.customrequired.checked = false; }"  /></td>
          <td><input <?php if (!(strcmp($row_rsMailPrefs['customrequired'],1))) {echo "checked=\"checked\"";} ?> name="customrequired" type="checkbox" id="customrequired" value="1"  onclick="if(this.checked===true) { this.form.askcustom.checked = true; }"/></td>
          <td>&nbsp;</td>
        </tr>
       <tr>
          <td class="text-right form-inline">
            <input name="messagelabel" type="text" id="messagelabel" style="text-align:right;" value="<?php echo $row_rsMailPrefs['messagelabel']; ?>" size="30" maxlength="50" class="form-control" />:</td>
          <td><input <?php if (!(strcmp($row_rsMailPrefs['askmessage'],1))) {echo "checked=\"checked\"";} ?> name="askmessage" type="checkbox" id="askmessage" value="1" onclick="if(this.checked===false) { this.form.messagerequired.checked = false; }"  />
          </td>
          <td><input <?php if (!(strcmp($row_rsMailPrefs['messagerequired'],1))) {echo "checked=\"checked\"";} ?> name="messagerequired" type="checkbox" id="messagerequired" value="1"  onclick="if(this.checked===true) { this.form.askmessage.checked = true; }"/>
          </td>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td class="text-right">Date of birth:</td>
          <td><input <?php if (!(strcmp($row_rsMailPrefs['askdob'],1))) {echo "checked=\"checked\"";} ?> name="askdob" type="checkbox" id="askdob" value="1" onclick="if(this.checked===false) { this.form.dobrequired.checked = false; }"  /></td>
          <td><input <?php if (!(strcmp($row_rsMailPrefs['dobrequired'],1))) {echo "checked=\"checked\"";} ?> name="dobrequired" type="checkbox" id="dobrequired" value="1"  onclick="if(this.checked===true) { this.form.askdob.checked = true; }"/></td>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td class="text-right">Discovery method:</td>
          <td><input <?php if (!(strcmp($row_rsMailPrefs['askdiscovered'],1))) {echo "checked=\"checked\"";} ?> name="askdiscovered" type="checkbox" id="askdiscovered" value="1" onclick="if(this.checked===false) { this.form.discoveredrequired.checked = false; }"  />
          </td>
          <td><input <?php if (!(strcmp($row_rsMailPrefs['discoveredrequired'],1))) {echo "checked=\"checked\"";} ?> name="discoveredrequired" type="checkbox" id="discoveredrequired" value="1"  onclick="if(this.checked===true) { this.form.askdiscovered.checked = true; }"/>
         </td>
          <td>&nbsp;</td>
        </tr> 
        <tr>
          <td class="text-right">Attachments:</td>
          <td><input <?php if (!(strcmp($row_rsMailPrefs['allowattachments'],1))) {echo "checked=\"checked\"";} ?> name="allowattachments" type="checkbox" id="allowattachments" value="1" /></td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
        </tr>
       
        <tr>
          <td class="text-right">Subject:</td>
          <td colspan="3">
            
            <label>
              <input <?php if (!(strcmp($row_rsMailPrefs['asksubject'],"0"))) {echo "checked=\"checked\"";} ?> type="radio" name="asksubject" value="0" id="asksubject_1" />
              No</label>
           &nbsp;&nbsp;<label>
              <input <?php if (!(strcmp($row_rsMailPrefs['asksubject'],"1"))) {echo "checked=\"checked\"";} ?> type="radio" name="asksubject" value="1" id="asksubject_0" />
              Yes</label>
            &nbsp;&nbsp;
            <label>
              <input <?php if (!(strcmp($row_rsMailPrefs['asksubject'],"2"))) {echo "checked=\"checked\"";} ?> type="radio" name="asksubject" value="2" id="asksubject_2" />
              Drop down list</label>
            <a href="subject.php">Edit</a>
          </td>
          </tr>
           <tr>
          <td class="text-right"><label for="text_callback_time">Best time to call back:</label></td>
          <td colspan="3">
            <input name="text_callback_time" type="text" class="form-control" id="text_callback_time" value="<?php echo $row_rsMailPrefs['text_callback_time']; ?>"></td>
        </tr>
           <tr>
             <td class="text-right">Options:</td>
             <td>
               <input name="text_callback_option_1" type="text"  class="form-control" placeholder="e.g. Morning 9-12" value="<?php echo $row_rsMailPrefs['text_callback_option_1']; ?>"></td>
             <td><input type="text" name="text_callback_option_2"  value="<?php echo $row_rsMailPrefs['text_callback_option_2']; ?>" class="form-control" placeholder="e.g. Afternoon 12-5"></td>
             <td><input type="text" name="text_callback_option_3"  value="<?php echo $row_rsMailPrefs['text_callback_option_3']; ?>" class="form-control" placeholder="e.g. Evening 5-12"></td>
           </tr>
        
        </table>
        </fieldset>
        <fieldset><legend>Page options</legend>
        <table class="form-table">
        <tr>
          <td class="text-right">Default subject:</td>
          <td colspan="2">
          <input name="defaultsubject" type="text" id="defaultsubject" value="<?php echo $row_rsMailPrefs['defaultsubject']; ?>" size="50" maxlength="50"  class="form-control"/></td>
        </tr>
        <tr>
          <td class="text-right">Custom contact page URL:</td>
          <td colspan="2"><input name="contactpageURL" type="text" id="contactpageURL" placeholder="Optional alternative contact page URL" value="<?php echo $row_rsMailPrefs['contactpageURL']; ?>" size="50" maxlength="100"  class="form-control"></td>
        </tr>
        
        <tr>
          <td class="text-right">Ask response type:</td>
          <td colspan="2"><input <?php if (!(strcmp($row_rsMailPrefs['askrespondby'],1))) {echo "checked=\"checked\"";} ?> name="askrespondby" type="checkbox" id="askrespondby" value="1" />
          <label for="respondby"></label></td>
        </tr>
        <tr>
          <td class="text-right top">Use Captcha: </td>
          <td colspan="2" class="form-inline"><label>
              <input <?php if (!(strcmp($row_rsMailPrefs['useCaptcha'],"0"))) {echo "checked=\"checked\"";} ?> type="radio" name="useCaptcha" id="useCaptcha0" value="0" />
              Never</label>  &nbsp;&nbsp;&nbsp; <label>
            <input <?php if (!(strcmp($row_rsMailPrefs['useCaptcha'],"2"))) {echo "checked=\"checked\"";} ?> type="radio" name="useCaptcha" id="useCaptcha2" value="2" />
            On suspect post</label>
             &nbsp;&nbsp;&nbsp;
            <label>
              <input <?php if (!(strcmp($row_rsMailPrefs['useCaptcha'],"1"))) {echo "checked=\"checked\"";} ?> type="radio" name="useCaptcha" id="useCaptcha1" value="1" />
              Always  </label>
           &nbsp;&nbsp;&nbsp;&nbsp;<span  class="captchatype">Type:
              <label>
                <input <?php if (!(strcmp($row_rsMailPrefs['captchatype'],"1"))) {echo "checked=\"checked\"";} ?> type="radio" name="captchatype" value="1" onClick="toggleRecaptcha()" >
                Simple Letters</label>
               &nbsp;&nbsp;&nbsp;
              <label>
                <input <?php if (!(strcmp($row_rsMailPrefs['captchatype'],"2"))) {echo "checked=\"checked\"";} ?> type="radio" name="captchatype" value="2" onClick="toggleRecaptcha()">
                ReCaptca 2</label>
                
                 &nbsp;&nbsp;&nbsp;
                <label>
                <input <?php if (!(strcmp($row_rsMailPrefs['captchatype'],"3"))) {echo "checked=\"checked\"";} ?> type="radio" name="captchatype" value="3" onClick="toggleRecaptcha()" >
                Invisible ReCaptca</label>
                
                </span><p class="recaptcha"><input name="recaptcha_site_key" type="text" placeholder="Site Key" value="<?php echo $row_rsPreferences['recaptcha_site_key']; ?>" size="50" maxlength="50"  class="form-control"> <input name="recaptcha_secret_key" type="text" placeholder="Secret Key" value="<?php echo $row_rsPreferences['recaptcha_secret_key']; ?>" size="50" maxlength="50"  class="form-control">  <a href="https://www.google.com/recaptcha/admin#list" target="_blank">API</a></p>
              </td>
        </tr>
        <tr>
          <td class="text-right">Show email address:</td>
          <td colspan="2"><input <?php if (!(strcmp($row_rsMailPrefs['showemail'],1))) {echo "checked=\"checked\"";} ?> name="showemai" type="checkbox" id="showemai" value="1" />
            (as alternative direct mail)</td>
        </tr>
        <tr>
          <td class="text-right">Show other contact info:</td>
          <td colspan="2"><input <?php if (!(strcmp($row_rsMailPrefs['showcontact'],1))) {echo "checked=\"checked\"";} ?> name="showcontact" type="checkbox" id="showcontact" value="1" />
            (where entered, e.g. address, fax, Skype, etc.) </td>
        </tr>
        <tr>
          <td class="text-right"><label for="inperson">Can contact in person:</label></td>
          <td colspan="2"><input <?php if (!(strcmp($row_rsMailPrefs['inperson'],1))) {echo "checked=\"checked\"";} ?> name="inperson" type="checkbox" id="inperson" value="(" />
            (if address shown)
            </td>
        </tr>
        <tr>
          <td class="text-right">Show Google map:</td>
          <td colspan="2"><input <?php if (!(strcmp($row_rsMailPrefs['showmap'],1))) {echo "checked=\"checked\"";} ?> name="showmap" type="checkbox" id="showmap" value="1" />
          <label for="showmap">(if available)</label></td>
        </tr>
        <tr>
          <td class="text-right">Confirmation URL:</td>
          <td colspan="2"><label for="confirmationURL"></label>
            <span id="sprytextfield1">
            <input name="confirmationURL" type="text" id="confirmationURL" value="<?php echo $row_rsMailPrefs['confirmationURL']; ?>" size="50" maxlength="255"  class="form-control"/>
</span></td>
        </tr>
        <tr>
          <td class="text-right"><label for="addtocontacts">Show opt-in mailing lists:</label></td>
          <td colspan="2"><input <?php if (!(strcmp($row_rsMailPrefs['addtocontacts'],1))) {echo "checked=\"checked\"";} ?> name="addtocontacts" type="checkbox" id="addtocontacts" value="1" />
            (will add sender to contacts list)</td>
        </tr>
        </table>
        </fieldset></div>
          <div class="TabbedPanelsContent">
            <p>This will appear above the form and replace the automatically generated intro text:</p>
      <p>
        
        <textarea name="contactheader" id="contactheader" cols="45" rows="5" class="tinymce form-control"><?php echo $row_rsMailPrefs['contactheader']; ?></textarea>
      </p></div>
          <div class="TabbedPanelsContent">
            <p>This content will appear below the form.</p>
            <p>
              <textarea name="contactfooter" id="contactfooter" cols="45" rows="5"  class="tinymce form-control"><?php echo $row_rsMailPrefs['contactfooter']; ?></textarea>
            </p>
          </div>
          <div class="TabbedPanelsContent">
            <p>
              
                
                <label>
                <input <?php if (!(strcmp($row_rsMailPrefs['autoreply'],1))) {echo "checked=\"checked\"";} ?> name="autoreply" type="checkbox" id="autoreply" value="1" />
                Send auto-response</label>
                
            </p>
            <h3>Response (in email and on confirmation page):</h3>
            <table class="form-table">
              <tbody>
                <tr>
                  <td  class="top text-right"><label for="responsesubject">Response subject:</label></td>
                  <td><input name="responsesubject" type="text" id="responsesubject" value="<?php echo isset($row_rsMailPrefs['responsesubject']) ? $row_rsMailPrefs['responsesubject'] : "Thank you!"; ?>" size="50" maxlength="255"  class="form-control"></td>
                </tr>
                <tr>
                  <td class="top text-right"><label for="responsemessage">Response Message:</label></td>
                  <td><textarea name="responsemessage" cols="50" rows="5" id="responsemessage"  class="form-control"><?php echo isset($row_rsMailPrefs['responsemessage']) ? $row_rsMailPrefs['responsemessage'] : "We have received your message sent via our website.\n\nMany thanks for your enquiry and we will get back to you as soon as possible."; ?></textarea></td>
                </tr>
              </tbody>
            </table>
            <p>&nbsp;</p>
          </div>
          <div class="TabbedPanelsContent">
            <p>The contact form will be sent to the main contact unless a recipient list is set up which will appear as a drop down menu on contact form. You can optionally add custom response messages by clicking edit.</p>
            <?php if ($totalRows_rsRecipients == 0) { // Show if recordset empty ?>
            <p>There is currently no recipient list.</p>
            <?php } // Show if recordset empty ?>
            <?php if ($totalRows_rsRecipients > 0) { // Show if recordset not empty ?>
            <table class="table table-hover">
            <thead>
              <tr>
                <th>&nbsp;</th>
                <th>ID</th>
                <th>Recipient/Dept.</th>
                <th>Email</th>
                <th>Response</th>
                <th colspan="2">Actions</th>
              </tr></thead><tbody>
              <?php do { ?>
              <tr>
                <td class="status<?php echo $row_rsRecipients['statusID']; ?>">&nbsp;</td>
                <td><?php echo $row_rsRecipients['ID']; ?></td>
                <td><?php echo $row_rsRecipients['recipient']; ?></td>
                <td><?php echo $row_rsRecipients['email']; ?></td>
                <td><?php echo $row_rsRecipients['responsesubject']; ?></td>
                <td><a href="edit_recipient.php?recipientID=<?php echo $row_rsRecipients['ID']; ?>" class="link_edit icon_only">Edit</a></td>
                <td><input type="image" src="../../../core/images/icons/trash.png" alt="delete" onclick="if(confirm('Are you sure you want to delete this recipient?')) { document.getElementById('deleterecipientID').value = '<?php echo $row_rsRecipients['ID']; ?>'; } else { return false; }" /></td>
              </tr>
              <?php } while ($row_rsRecipients = mysql_fetch_assoc($rsRecipients)); ?></tbody>
            </table>
            <?php } // Show if recordset not empty ?>
            <p class="form-inline">Add recipient:
              <input name="recipientname" type="text" id="recipientname" size="30" maxlength="100" placeholder="Name/Dept"  class="form-control"/>
              <input name="recipientemail" type="email" multiple id="recipientemail" size="30" maxlength="255" placeholder="Email"  class="form-control"/>
              <button type="submit" name="addrecipient" id="addrecipient" class="btn btn-default btn-secondary" >Add recipient</button>
            </p>
          </div>
<div class="TabbedPanelsContent">
  <table class="form-table">
              <tr>
                <th scope="row"><label for="contacttitle">Page Title:</label></th>
                <td>
                  <input class="form-control" type="text" name="contacttitle" id="contacttitle" value="<?php echo $row_rsMailPrefs['contacttitle']; ?>">
                </td>
              </tr>
              <tr>
                <th scope="row"><label for="contactmetadescription">Meta description:</label></th>
                <td>
                  <textarea name="contactmetadescription" id="contactmetadescription" class="form-control"><?php echo $row_rsMailPrefs['contactmetadescription']; ?></textarea>
                </td>
              </tr>
            </table>
        </div>
</div>
      </div>
      
      
      
     
      <p>
        <button type="submit" name="savebutton" id="savebutton" class="btn btn-primary" >Save changes...</button><input name="ID" type="hidden" value="<?php echo $row_rsMailPrefs['ID']; ?>" />
      </p>
      <input type="hidden" name="MM_update" value="form1" />
      <input name="regionID" type="hidden" id="regionID" value="<?php echo isset($regionID) ? $regionID : 1; ?>" />
      <input name="createdbyID" type="hidden" id="createdbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
      <input type="hidden" name="deleterecipientID" id="deleterecipientID" />
    </form>
      <?php if (isset($_GET['defaultTab'])) { echo '<script>
<!--
var TabbedPanels1 = new Spry.Widget.TabbedPanels("TabbedPanels1", {defaultTab:'.intval($_GET['defaultTab']).'});
//-->
    </script>'; } else { ?>
    <script>
<!--
var TabbedPanels1 = new Spry.Widget.TabbedPanels("TabbedPanels1");
//-->
    </script>
    <?php } ?>
    <script>
<!--
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1", "none", {isRequired:false, hint:"(optional)"});

//-->
    </script></div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsMailPrefs);

mysql_free_result($rsLoggedIn);

mysql_free_result($rsRecipients);

mysql_free_result($rsForms);
?>
