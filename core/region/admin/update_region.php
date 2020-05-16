<?php require_once('../../../Connections/aquiescedb.php'); ?><?php require_once('../../includes/adminAccess.inc.php'); ?><?php require_once('../../includes/upload.inc.php'); ?><?php require_once('../../includes/framework.inc.php'); ?><?php if(is_readable("../../../products/admin/inc/product_functions.inc.php")) { require_once('../../../products/admin/inc/product_functions.inc.php'); } ?><?php require_once('../includes/region_functions.inc.php'); ?><?php require_once('../../includes/framework.inc.php'); ?>
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

$MM_restrictGoTo = "/login/index.php?notloggedin=true";
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

if(isset($_GET['country_id'])) {
	$updateSQL = "UPDATE countries SET regionID= NULL WHERE ID=".GetSQLValueString($_GET['country_id'], "text");

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if(isset($_POST["copyfromregionID"]) && $_POST["copyfromregionID"]>0) {
	if(isset($_POST["copyproducts"])  && function_exists("copyProductsToRegion")) {
		copyProductsToRegion($_GET['regionID'], $_POST["copyfromregionID"]);
	}
	if(isset($_POST["copypages"])) { 
		copyPagesToRegion($_GET['regionID'], $_POST["copyfromregionID"]);
	}
	
}




if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {

	$uploaded = getUploads();
	if(isset($_POST['noImage2'])) {
		$_POST['adminheaderimageURL'] = "";
	}
	
	
	if (isset($uploaded) && is_array($uploaded)) { 
		if(isset($uploaded["filename"][0]["newname"]) && $uploaded["filename"][0]["newname"]!="") { 
			$_POST['imageURL'] = $uploaded["filename"][0]["newname"]; 
		}
		if(isset($uploaded["filename2"][0]["newname"]) && $uploaded["filename2"][0]["newname"]!="") { 
			$_POST['adminheaderimageURL'] = $uploaded["filename2"][0]["newname"]; 
		}
		
		if(isset($uploaded["filename3"][0]["newname"]) && $uploaded["filename3"][0]["newname"]!="") { 
			$_POST['backgroundimageURL'] = $uploaded["filename3"][0]["newname"]; 
		}
		
	}
}



if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE region SET showmenu=%s, title=%s, currencycode=%s, hostdomain=%s, address=%s, postcode=%s, telephone=%s, fax=%s, email=%s, skypeID=%s, facebookID=%s, twitterID=%s, blogURL=%s, flickrURL=%s, googleplusURL=%s, youtubeURL=%s, linkedinURL=%s, instagramURL=%s, pinterestURL=%s, signupemailtext=%s, statusID=%s, vatrate=%s, vatnumber=%s, imageURL=%s, adminheaderimageURL=%s, adminheadercolor=%s, backgroundimageURL=%s, themecolor1=%s, themecolor2=%s, h1color=%s, pcolor=%s, backgroundcolor=%s, faviconURL=%s, https=%s, www=%s, text_choose=%s, text_contactus=%s, text_emailerror=%s, text_yes=%s, text_no=%s, text_save=%s, text_continue=%s, text_previous=%s, text_next=%s, headhtml=%s, text_share=%s, text_back=%s, text_items=%s, text_to=%s, text_of=%s, text_show_all=%s, text_follow_us=%s, languagecode=%s, text_read_more=%s, text_other1=%s, text_other2=%s, text_other3=%s WHERE ID=%s",
                       GetSQLValueString(isset($_POST['showmenu']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['title'], "text"),
                       GetSQLValueString($_POST['currencycode'], "text"),
                       GetSQLValueString($_POST['hostdomain'], "text"),
                       GetSQLValueString($_POST['address'], "text"),
                       GetSQLValueString($_POST['postcode'], "text"),
                       GetSQLValueString($_POST['telephone'], "text"),
                       GetSQLValueString($_POST['fax'], "text"),
                       GetSQLValueString($_POST['email'], "text"),
                       GetSQLValueString($_POST['skypeID'], "text"),
                       GetSQLValueString($_POST['facebookID'], "text"),
                       GetSQLValueString($_POST['twitterID'], "text"),
                       GetSQLValueString($_POST['blogURL'], "text"),
                       GetSQLValueString($_POST['flickrURL'], "text"),
                       GetSQLValueString($_POST['googleplusURL'], "text"),
                       GetSQLValueString($_POST['youtubeURL'], "text"),
                       GetSQLValueString($_POST['linkedinURL'], "text"),
                       GetSQLValueString($_POST['instagramURL'], "text"),
                       GetSQLValueString($_POST['pinterestURL'], "text"),
                       GetSQLValueString($_POST['signupemailtext'], "text"),
                       GetSQLValueString($_POST['statusID'], "int"),
                       GetSQLValueString($_POST['vatrate'], "double"),
                       GetSQLValueString($_POST['vatnumber'], "text"),
                       GetSQLValueString($_POST['imageURL'], "text"),
                       GetSQLValueString($_POST['adminheaderimageURL'], "text"),
                       GetSQLValueString($_POST['adminheadercolour'], "text"),
                       GetSQLValueString($_POST['backgroundimageURL'], "text"),
                       GetSQLValueString($_POST['themecolor1'], "text"),
                       GetSQLValueString($_POST['themecolor2'], "text"),
                       GetSQLValueString($_POST['h1color'], "text"),
                       GetSQLValueString($_POST['pcolor'], "text"),
                       GetSQLValueString($_POST['backgroundcolor'], "text"),
                       GetSQLValueString($_POST['faviconURL'], "text"),
                       GetSQLValueString(isset($_POST['https']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['www']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['text_choose'], "text"),
                       GetSQLValueString($_POST['text_contactus'], "text"),
                       GetSQLValueString($_POST['text_emailerror'], "text"),
                       GetSQLValueString($_POST['text_yes'], "text"),
                       GetSQLValueString($_POST['text_no'], "text"),
                       GetSQLValueString($_POST['text_save'], "text"),
                       GetSQLValueString($_POST['text_continue'], "text"),
                       GetSQLValueString($_POST['text_previous'], "text"),
                       GetSQLValueString($_POST['text_next'], "text"),
                       GetSQLValueString($_POST['headhtml'], "text"),
                       GetSQLValueString($_POST['text_share'], "text"),
                       GetSQLValueString($_POST['text_back'], "text"),
                       GetSQLValueString($_POST['text_items'], "text"),
                       GetSQLValueString($_POST['text_to'], "text"),
                       GetSQLValueString($_POST['text_of'], "text"),
                       GetSQLValueString($_POST['text_show_all'], "text"),
                       GetSQLValueString($_POST['text_follow_us'], "text"),
                       GetSQLValueString($_POST['languagecode'], "text"),
                       GetSQLValueString($_POST['text_read_more'], "text"),
                       GetSQLValueString($_POST['text_other1'], "text"),
                       GetSQLValueString($_POST['text_other2'], "text"),
                       GetSQLValueString($_POST['text_other3'], "text"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
	
	if(isset($_POST['country_id']) && intval($_POST['country_id'])>0) {
	$_POST['regionID'] = isset($_POST['addcountrytoall']) ? 0 : $_POST['regionID'];
	$updateSQL = sprintf("UPDATE countries SET regionID=%s WHERE ID=%s",
                       GetSQLValueString($_POST['regionID'], "int"),
                       GetSQLValueString($_POST['country_id'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
	} else {
  
  
  
  $updateGoTo = "index.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo)); exit;
	}
}






$colname_rsRegion = "-1";
if (isset($_GET['regionID'])) {
  $colname_rsRegion = $_GET['regionID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsRegion = sprintf("SELECT * FROM region WHERE ID = %s", GetSQLValueString($colname_rsRegion, "int"));
$rsRegion = mysql_query($query_rsRegion, $aquiescedb) or die(mysql_error());
$row_rsRegion = mysql_fetch_assoc($rsRegion);
$totalRows_rsRegion = mysql_num_rows($rsRegion);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsStatus = "SELECT * FROM status";
$rsStatus = mysql_query($query_rsStatus, $aquiescedb) or die(mysql_error());
$row_rsStatus = mysql_fetch_assoc($rsStatus);
$totalRows_rsStatus = mysql_num_rows($rsStatus);

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = (get_magic_quotes_gpc()) ? $_SESSION['MM_Username'] : addslashes($_SESSION['MM_Username']);
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID, usertypeID, regionID FROM users WHERE username = '%s'", $colname_rsLoggedIn);
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

$varRegionID_rsRegionCountries = "-1";
if (isset($_GET['regionID'])) {
  $varRegionID_rsRegionCountries = $_GET['regionID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsRegionCountries = sprintf("SELECT countries.* FROM countries WHERE  (regionID=0 OR regionID = %s) ORDER BY fullname ASC", GetSQLValueString($varRegionID_rsRegionCountries, "int"));
$rsRegionCountries = mysql_query($query_rsRegionCountries, $aquiescedb) or die(mysql_error());
$row_rsRegionCountries = mysql_fetch_assoc($rsRegionCountries);
$totalRows_rsRegionCountries = mysql_num_rows($rsRegionCountries);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsCountries = "SELECT countries.* FROM countries WHERE statusID =1 AND regionID IS NULL ORDER BY fullname ASC";
$rsCountries = mysql_query($query_rsCountries, $aquiescedb) or die(mysql_error());
$row_rsCountries = mysql_fetch_assoc($rsCountries);
$totalRows_rsCountries = mysql_num_rows($rsCountries);

$colname_rsOtherRegions = "-1";
if (isset($_GET['regionID'])) {
  $colname_rsOtherRegions = $_GET['regionID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsOtherRegions = sprintf("SELECT ID, title FROM region WHERE ID <> %s ORDER BY title ASC", GetSQLValueString($colname_rsOtherRegions, "int"));
$rsOtherRegions = mysql_query($query_rsOtherRegions, $aquiescedb) or die(mysql_error());
$row_rsOtherRegions = mysql_fetch_assoc($rsOtherRegions);
$totalRows_rsOtherRegions = mysql_num_rows($rsOtherRegions);
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Update Site Options"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../seo/includes/seo.inc.php'); ?>
<?php require_once('../../includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" --><script src="../../scripts/formUpload.js"></script>
<script src="../../../SpryAssets/SpryTabbedPanels.js"></script>
<link href="../../../SpryAssets/SpryTabbedPanels.css" rel="stylesheet"  />
<link href="/core/scripts/bootstrap-colorpicker/css/bootstrap-colorpicker.min.css" rel="stylesheet" >
<script src="/core/scripts/bootstrap-colorpicker/js/bootstrap-colorpicker.min.js"></script>
<script>
$(function(){
    $('.colorpicker').colorpicker().on('changeColor.colorpicker', function(event){
  		$(this).css("background-color",event.color.toHex());
	});
});
</script>
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" -->
       <div class="page regions">
<h1><i class="glyphicon glyphicon-globe"></i> Site Options: <?php echo $row_rsRegion['title']; ?></h1>
<?php if ($row_rsLoggedIn['usertypeID'] >=9 || $row_rsLoggedIn['regionID'] == $_GET['regionID']) { //access allowed ?>
<nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
  <li><a href="/core/region/admin/add_region.php" ><i class="glyphicon glyphicon-plus-sign"></i> Add Site</a></li>
  <li><a href="/core/region/admin/index.php" class="link_undo"><i class="glyphicon glyphicon-arrow-left"></i> Manage Sites</a></li>
  <li class="countries"><a href="countries/index.php?regionID=<?php echo intval($_GET['regionID']); ?>"><i class="glyphicon glyphicon-globe"></i> Manage Countries</a></li>
</ul></div></nav>
<?php if(isset($submit_error)) { ?>
<p class="alert alert-danger" role="alert"><?php echo $submit_error; ?></p>
<?php } ?>
 <form action="<?php echo $editFormAction; ?>" method="post" enctype="multipart/form-data" name="form1" id="form1"> 
  <div id="TabbedPanels1" class="TabbedPanels">
  <ul class="TabbedPanelsTabGroup">
    <li class="TabbedPanelsTab" tabindex="0">Site details</li>
    <li class="TabbedPanelsTab" tabindex="0">Site Template</li>
    <li class="TabbedPanelsTab" tabindex="0">Admin Template</li>
    <li class="TabbedPanelsTab" tabindex="0">Translations</li>
<li class="TabbedPanelsTab countries" tabindex="0">Countries</li>
   
</ul>
  <div class="TabbedPanelsContentGroup">
    <div class="TabbedPanelsContent">
      <table class="form-table">
        <tr>
          <td class="text-nowrap text-right">Title:</td>
          <td><input name="title" type="text"  value="<?php echo $row_rsRegion['title']; ?>" size="50" maxlength="50"
  class="form-control"         /></td>
        </tr>
        <tr>
          <td class="text-nowrap text-right top">Address:</td>
          <td><textarea name="address" cols="50" rows="5" class="form-control"  ><?php echo $row_rsRegion['address']; ?></textarea></td>
        </tr>
        <tr>
          <td class="text-nowrap text-right top">Postcode:</td>
          <td><input name="postcode" type="text"  id="postcode" value="<?php echo $row_rsRegion['postcode']; ?>" size="50" maxlength="12"  class="form-control"  /></td>
        </tr>
        <tr>
          <td class="text-nowrap text-right">Telephone:</td>
          <td><input name="telephone" type="text"  value="<?php echo $row_rsRegion['telephone']; ?>" size="50" maxlength="20"  class="form-control"  /></td>
        </tr>
        <tr>
          <td class="text-nowrap text-right">Fax:</td>
          <td><input name="fax" type="text"  value="<?php echo $row_rsRegion['fax']; ?>" size="50" maxlength="20" class="form-control"   /></td>
        </tr>
        <tr>
          <td class="text-nowrap text-right">Email:</td>
          <td><input name="email" type="email" multiple value="<?php echo $row_rsRegion['email']; ?>" size="50" maxlength="255"  class="form-control"  /></td>
        </tr>
        
        <tr>
          <td class="text-nowrap text-right top">Facebook URL:</td>
          <td><label>
            <input name="facebookID" type="text"  id="facebookID" value="<?php echo $row_rsRegion['facebookID']; ?>" size="50" maxlength="255"  class="form-control"  />
          </label></td>
        </tr>
        <tr>
          <td class="text-nowrap text-right top">Twitter URL:</td>
          <td><label>
            <input name="twitterID" type="text"  id="twitterID" value="<?php echo $row_rsRegion['twitterID']; ?>" size="50" maxlength="255"  class="form-control"  />
          </label></td>
        </tr>
        <tr>
          <td class="text-nowrap text-right top">Blog URL:</td>
          <td><label>
            <input name="blogURL" type="text"  id="blogURL" value="<?php echo $row_rsRegion['blogURL']; ?>" size="50" maxlength="255" class="form-control"   />
          </label></td>
        </tr>
        <tr>
          <td class="text-nowrap text-right top">YouTube URL:</td>
          <td><label>
            <input name="youtubeURL" type="text"  id="youtubeURL" value="<?php echo $row_rsRegion['youtubeURL']; ?>" size="50" maxlength="255"  class="form-control"  />
          </label></td>
        </tr>
        <tr>
          <td class="text-nowrap text-right top">Flickr URL:</td>
          <td><label>
            <input name="flickrURL" type="text"  id="flickrURL" value="<?php echo $row_rsRegion['flickrURL']; ?>" size="50" maxlength="255"  class="form-control"  />
          </label></td>
        </tr>
        <tr>
          <td class="text-nowrap text-right top">LinkedIn URL:</td>
          <td><label>
            <input name="linkedinURL" type="text"  id="linkedinURL" value="<?php echo $row_rsRegion['linkedinURL']; ?>" size="50" maxlength="50"  class="form-control"  />
          </label></td>
        </tr>
        <tr>
          <td class="text-nowrap text-right top">Pinterest URL:</td>
          <td><label>
            <input name="pinterestURL" type="text"  id="pinterestURL" value="<?php echo $row_rsRegion['pinterestURL']; ?>" size="50" maxlength="50"  class="form-control"  />
          </label></td>
        </tr>
        <tr>
          <td class="text-nowrap text-right top">Instagram URL:</td>
          <td><label>
            <input name="instagramURL" type="text"  id="instagramURL" value="<?php echo $row_rsRegion['instagramURL']; ?>" size="50" maxlength="255"  class="form-control"  />
          </label></td>
        </tr>
        <tr>
          <td class="text-nowrap text-right top">Google+ URL:</td>
          <td><label>
            <input name="googleplusURL" type="text"  id="googleplusURL" value="<?php echo $row_rsRegion['googleplusURL']; ?>" size="50" maxlength="255"  class="form-control"   />
          </label></td>
        </tr>
        <tr>
          <td class="text-nowrap text-right top">Skype ID: </td>
          <td><input name="skypeID" type="text"  id="skypeID" value="<?php echo $row_rsRegion['skypeID']; ?>" size="50" maxlength="255"  class="form-control"  /></td>
        </tr>
        <tr>
          <td class="text-nowrap text-right top">Sign up email:</td>
          <td> Hello, first name,<br />
            <textarea name="signupemailtext" id="signupemailtext" cols="50" rows="6"  class="form-control"  ><?php echo (isset($row_rsRegion['signupemailtext'])) ? $row_rsRegion['signupemailtext'] : "Welcome to ".$row_rsRegion['title']."\n\nThank you for registering with us.\n\nRegards,\n\nThe ".$row_rsRegion['title']." team"; ?></textarea>
            <br />
            Log in details are displayed below.</td>
        </tr>
        <tr <?php if ($row_rsRegion['ID'] == 1) { echo "style=\"display:none;\""; } ?>>
          <td class="text-nowrap text-right">Status:</td>
          <td><select name="statusID"  class="form-control"  >
            <?php
do {  
?>
            <option value="<?php echo $row_rsStatus['ID']?>"<?php if (!(strcmp($row_rsStatus['ID'], $row_rsRegion['statusID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsStatus['description']?></option>
            <?php
} while ($row_rsStatus = mysql_fetch_assoc($rsStatus));
  $rows = mysql_num_rows($rsStatus);
  if($rows > 0) {
      mysql_data_seek($rsStatus, 0);
	  $row_rsStatus = mysql_fetch_assoc($rsStatus);
  }
?>
          </select></td>
        </tr>
        <tr>
          <td class="text-nowrap text-right">Show Site menu:</td>
          <td><input <?php if (!(strcmp($row_rsRegion['showmenu'],1))) {echo "checked=\"checked\"";} ?> name="showmenu" type="checkbox" id="showmenu" value="1" /></td>
        </tr>
       <tr>
          <td class="text-nowrap text-right">Language code:</td>
          <td class="form-inline"><input name="languagecode" type="text"  value="<?php echo $row_rsRegion['languagecode']; ?>" size="10" maxlength="10"  class="form-control"   /></td>
        </tr>
        <tr>
          <td class="text-nowrap text-right">Currency code:</td>
          <td class="form-inline"><input name="currencycode" type="text"  value="<?php echo $row_rsRegion['currencycode']; ?>" size="5" maxlength="5"  class="form-control"  /></td>
        </tr>
        <tr>
          <td class="text-nowrap text-right">VAT/Sales Tax Rate:</td>
          <td class="form-inline"><input name="vatrate" type="text"  id="vatrate" value="<?php echo $row_rsRegion['vatrate']; ?>" size="5" maxlength="5"  class="form-control"  />
            %</td>
        </tr>
        <tr>
          <td class="text-nowrap text-right top">VAT Number:</td>
          <td class="form-inline"><input name="vatnumber" type="text"  id="vatnumber" value="<?php echo $row_rsRegion['vatnumber']; ?>" size="50" maxlength="50"  class="form-control"   /></td>
        </tr>
        <tr>
          <td class="text-nowrap text-right">Host domain:</td>
          <td class="form-inline"><input name="hostdomain" type="text"  id="hostdomain" value="<?php echo $row_rsRegion['hostdomain']; ?>" size="50" maxlength="255"  class="form-control"  />
            &nbsp;&nbsp;
            <label>
              <input <?php if (!(strcmp($row_rsRegion['www'],1))) {echo "checked=\"checked\"";} ?> name="www" type="checkbox" id="www" value="1">
              Add www.</label>
            &nbsp;&nbsp;&nbsp;
            <label>
              <input <?php if (!(strcmp($row_rsRegion['https'],1))) {echo "checked=\"checked\"";} ?> name="https" type="checkbox" id="https" value="1">
              Connect via HTTPS</label></td>
        </tr>
        <tr>
          <td class="text-nowrap text-right"> Copy:</td>
          <td class="form-inline"><label>
            <input type="checkbox" name="copypages" id="copypages">
            Pages</label>
            &nbsp;&nbsp;&nbsp;
            <label>
              <input type="checkbox" name="copyproducts" id="copyproducts">
              Products</label>
            &nbsp;&nbsp;&nbsp;
            <label>from:
              <select name="copyfromregionID" id="copyfromregionID"  class="form-control"  >
                <option value=""><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
                <?php
do {  
?>
                <option value="<?php echo $row_rsOtherRegions['ID']?>"><?php echo $row_rsOtherRegions['title']?></option>
                <?php
} while ($row_rsOtherRegions = mysql_fetch_assoc($rsOtherRegions));
  $rows = mysql_num_rows($rsOtherRegions);
  if($rows > 0) {
      mysql_data_seek($rsOtherRegions, 0);
	  $row_rsOtherRegions = mysql_fetch_assoc($rsOtherRegions);
  }
?>
              </select>
            </label></td>
        </tr>
      </table>
      <input type="hidden" name="ID" value="<?php echo $row_rsRegion['ID']; ?>" />
      <input type="hidden" name="MM_update" value="form1" />
    </div>
    <div class="TabbedPanelsContent">
      <table border="0" cellpadding="0" cellspacing="0" class="form-table">
        <tr>
          <th scope="row">Header image</th>
          <td><?php if (isset($row_rsRegion['imageURL'])) { ?>
            <img src="<?php echo getImageURL($row_rsRegion['imageURL'],"medium"); ?>" alt="Current image" /> <br />
            <?php } ?>
            <input type="file" name="filename" id="filename" />
            <input name="imageURL" type="hidden" value="<?php echo $row_rsRegion['imageURL']; ?>" /></td>
        </tr>
        <tr>
          <th scope="row">Headings colour:</th>
          <td class="form-inline"><input name="h1color" id="h1color" type="text" class=" colorpicker form-control" value="<?php echo $row_rsRegion['h1color']; ?>" maxlength="25" />
            (h1, h2, h3, h4)</td>
        </tr>
        <tr>
          <th scope="row">Text colour:</th>
          <td class="form-inline"><input name="pcolor" id="pcolor" type="text" class=" colorpicker form-control" value="<?php echo $row_rsRegion['pcolor']; ?>" maxlength="25" /></td>
        </tr>
        <tr>
          <th scope="row">Theme colour 1</th>
          <td class="form-inline"><input name="themecolor1" id="themecolor1" type="text" class=" colorpicker form-control" value="<?php echo $row_rsRegion['themecolor1']; ?>" maxlength="25" /></td>
        </tr>
        <tr>
          <th scope="row">Theme colour 2</th>
          <td class="form-inline"><input name="themecolor2" id="themecolor2" type="text" class=" colorpicker form-control" value="<?php echo $row_rsRegion['themecolor2']; ?>" maxlength="25" /></td>
        </tr>
        <tr>
          <th scope="row">Background colour:</th>
          <td class="form-inline"><input name="backgroundcolor" id="backgroundcolor" type="text" class=" colorpicker form-control" value="<?php echo $row_rsRegion['backgroundcolor']; ?>" maxlength="25" /></td>
        </tr>
        <tr>
          <th scope="row">Background image:</th>
          <td><input type="file" name="filename3" id="filename3" />
            <input name="backgroundimageURL" type="hidden" value="<?php echo $row_rsRegion['backgroundimageURL']; ?>" /></td>
        </tr>
        <tr>
          <th scope="row" class="top text-right"><label for="headhtml">&lt;head&gt;</label></th>
          <td>
            <textarea name="headhtml" cols="100" rows="10" id="headhtml"  class="form-control"><?php echo $row_rsRegion['headhtml']; ?></textarea></td>
        </tr>
        <tr>
          <th scope="row" class="text-right"><label for="faviconURL">Favicon URL:</label></th>
          <td><input name="faviconURL" type="text"  id="faviconURL" value="<?php echo $row_rsRegion['faviconURL']; ?>" size="50" maxlength="50" class="form-control"></td>
        </tr>
      </table>
    </div>
    <div class="TabbedPanelsContent">
      <table border="0" cellpadding="0" cellspacing="0" class="form-table">
        <tr>
          <td align="right">Header Colour:</td>
          <td class="form-inline"><input name="adminheadercolour" id="adminheadercolour" type="text" class=" colorpicker form-control" value="<?php echo $row_rsRegion['adminheadercolor']; ?>" maxlength="25" /></td>
        </tr>
        <tr>
          <td align="right"><label for="filename2">Header Image:</label></td>
          <td><?php if (isset($row_rsRegion['adminheaderimageURL'])) { ?>
            <img src="<?php echo getImageURL($row_rsRegion['adminheaderimageURL'], "medium"); ?>" alt="Current image"  class="medium"/>
            <label>
              <input name="noImage2" type="checkbox" value="1" />
              Remove image</label>
            <br />
            <?php } ?>
            <input name="adminheaderimageURL" type="hidden" value="<?php echo $row_rsRegion['adminheaderimageURL']; ?>" />
            <input type="file" name="filename2" id="filename2" /></td>
        </tr>
      </table>
    </div>
    <div class="TabbedPanelsContent">
      <p>Various site-wide translatable items can be updated below:</p>
      <table  class="form-table form-inline">
        <tr>
          <th scope="row" class="text-right"><label for="text_yes">Yes:</label></th>
          <td>
            <input name="text_yes" type="text" id="text_yes" value="<?php echo htmlentities($row_rsRegion['text_yes'], ENT_COMPAT, "UTF-8"); ?>" size="50" maxlength="20" class="form-control"></td>
        </tr>
        <tr>
          <th scope="row" class="text-right"><label for="text_no">No:</label></th>
          <td>
            <input name="text_no" type="text" id="text_no" value="<?php echo htmlentities($row_rsRegion['text_no'], ENT_COMPAT, "UTF-8"); ?>" size="50" maxlength="20" class="form-control"></td>
        </tr>
        
        <tr>
          <th scope="row" class="text-right"><label for="text_choose">Choose:</label></th>
          <td>
            <input name="text_choose" type="text" id="text_choose" value="<?php echo htmlentities($row_rsRegion['text_choose'], ENT_COMPAT, "UTF-8"); ?>" size="50" maxlength="20" class="form-control"></td>
        </tr>
        <tr>
          <th scope="row" class="text-right"><label for="text_save">Save:</label></th>
          <td><input name="text_save" type="text" id="text_save" value="<?php echo htmlentities($row_rsRegion['text_save'], ENT_COMPAT, "UTF-8"); ?>" size="50" maxlength="20" class="form-control"></td>
        </tr>
        <tr>
          <th scope="row" class="text-right"><label for="text_continue">Continue:</label></th>
          <td><input name="text_continue" type="text" id="text_continue" value="<?php echo htmlentities($row_rsRegion['text_continue'], ENT_COMPAT, "UTF-8"); ?>" size="50" maxlength="20" class="form-control"></td>
        </tr>
        <tr>
          <th scope="row" class="text-right"><label for="text_previous">Previous:</label></th>
          <td><input name="text_previous" type="text" id="text_previous" value="<?php echo htmlentities($row_rsRegion['text_previous'], ENT_COMPAT, "UTF-8"); ?>" size="50" maxlength="20" class="form-control"></td>
        </tr>
        <tr>
          <th scope="row" class="text-right"><label for="text_next">Next:</label></th>
          <td><input name="text_next" type="text" id="text_next" value="<?php echo htmlentities($row_rsRegion['text_next'], ENT_COMPAT, "UTF-8"); ?>" size="50" maxlength="20" class="form-control"></td>
        </tr>
        <tr>
          <th scope="row" class="text-right"><label for="text_contactus">Contact Us:</label></th>
          <td>
            <input name="text_contactus" type="text" id="text_contactus" value="<?php echo htmlentities($row_rsRegion['text_contactus'], ENT_COMPAT, "UTF-8"); ?>" size="50" maxlength="20" class="form-control"></td>
        </tr>
        
        
        
        
    
  
  <tr>
          <th scope="row" class="text-right"><label for="text_follow_us">Follow Us:</label></th>
          <td>
            <input name="text_follow_us" type="text" id="text_follow_us" value="<?php echo htmlentities($row_rsRegion['text_follow_us'], ENT_COMPAT, "UTF-8"); ?>" size="50" maxlength="20" class="form-control"></td>
        </tr>
        <tr>
          <th scope="row" class="text-right"><label for="text_share">Share:</label></th>
          <td>
            <input name="text_share" type="text" id="text_share" value="<?php echo htmlentities($row_rsRegion['text_share'], ENT_COMPAT, "UTF-8"); ?>" size="50" maxlength="20" class="form-control"></td>
        </tr>
        <tr>
          <th scope="row" class="text-right"><label for="text_back">Back:</label></th>
          <td>
            <input name="text_back" type="text" id="text_back" value="<?php echo htmlentities($row_rsRegion['text_back'], ENT_COMPAT, "UTF-8"); ?>" size="50" maxlength="20" class="form-control"></td>
        </tr>
        <tr>
          <th scope="row" class="text-right"><label for="text_items">Items:</label></th>
          <td>
            <input name="text_items" type="text" id="text_items" value="<?php echo htmlentities($row_rsRegion['text_items'], ENT_COMPAT, "UTF-8"); ?>" size="50" maxlength="20" class="form-control"> (Items x to y of z)</td>
        </tr>
        <tr>
          <th scope="row" class="text-right"><label for="text_to">To:</label></th>
          <td>
            <input name="text_to" type="text" id="text_to" value="<?php echo htmlentities($row_rsRegion['text_to'], ENT_COMPAT, "UTF-8"); ?>" size="50" maxlength="20" class="form-control"> (Items x to y of z)</td>
        </tr>
        <tr>
          <th scope="row" class="text-right"><label for="text_of">Of:</label></th>
          <td>
            <input name="text_of" type="text" id="text_of" value="<?php echo htmlentities($row_rsRegion['text_of'], ENT_COMPAT, "UTF-8"); ?>" size="50" maxlength="20" class="form-control"> (Items x to y of z)</td>
        </tr>
        <tr>
          <th scope="row" class="text-right"><label for="text_show_all">Show all:</label></th>
          <td>
            <input name="text_show_all" type="text" id="text_show_all" value="<?php echo htmlentities($row_rsRegion['text_show_all'], ENT_COMPAT, "UTF-8"); ?>" size="50" maxlength="20" class="form-control"></td>
        </tr>
        
        <tr>
          <th scope="row" class="text-right"><label for="text_read_more">Read More:</label></th>
          <td>
            <input name="text_read_more" type="text" id="text_read_more" value="<?php echo htmlentities($row_rsRegion['text_read_more'], ENT_COMPAT, "UTF-8"); ?>" size="50" maxlength="20" class="form-control"></td>
        </tr>
        
        
        <tr>
          <th scope="row" class="text-right"><label for="text_other1">Other 1:</label></th>
          <td>
            <input name="text_other1" type="text" id="text_other1" value="<?php echo htmlentities($row_rsRegion['text_other1'], ENT_COMPAT, "UTF-8"); ?>" size="50" maxlength="255" class="form-control"></td>
        </tr>
        <tr>
          <th scope="row" class="text-right"><label for="text_other2">Other 2:</label></th>
          <td>
            <input name="text_other2" type="text" id="text_other2" value="<?php echo htmlentities($row_rsRegion['text_other2'], ENT_COMPAT, "UTF-8"); ?>" size="50" maxlength="255" class="form-control"></td>
        </tr>
        <tr>
          <th scope="row" class="text-right"><label for="text_other3">Other 3:</label></th>
          <td>
            <input name="text_other3" type="text" id="text_other3" value="<?php echo htmlentities($row_rsRegion['text_other3'], ENT_COMPAT, "UTF-8"); ?>" size="50" maxlength="255" class="form-control"></td>
        </tr>
        
        
        
        
        
        
        <tr>
          <th scope="row" class="text-right"><label for="text_emailerror">Email error:</label></th>
          <td>
            <input name="text_emailerror" type="text" id="text_emailerror" value="<?php echo htmlentities($row_rsRegion['text_emailerror'], ENT_COMPAT, "UTF-8"); ?>" size="150" maxlength="150" class="form-control"></td>
        </tr>
        
      </table>
     
    </div>
<div class="TabbedPanelsContent">
  <?php if ($totalRows_rsRegionCountries == 0) { // Show if recordset empty ?>
      <p class="alert warning alert-warning" role="alert">There are no countries so far associated with this site. Please add a country below...</p>
      <?php } // Show if recordset empty ?>
      <?php if ($totalRows_rsRegionCountries > 0) { // Show if recordset not empty ?>
      <p>Countries associated with this site:</p>
      <table  class="table"><tbody><?php do { ?>
        <tr>
          <td><?php echo $row_rsRegionCountries['fullname']; ?>&nbsp;<a href="update_region.php?regionID=<?php echo $row_rsRegion['ID']; ?>&amp;country_id=<?php echo $row_rsRegionCountries['ID']; ?>" onclick="document.returnValue = confirm('Are you sure you want to delete this country?'); return document.returnValue;" class="link_delete"><i class="glyphicon glyphicon-trash"></i> Delete</a></td>
        </tr>
        
      <?php } while ($row_rsRegionCountries = mysql_fetch_assoc($rsRegionCountries)); ?></tbody></table><table  class="table">
      <tbody>
<tr>
          <td>&nbsp;<a href="update_region.php?regionID=<?php echo $row_rsRegion['ID']; ?>&amp;country_id=<?php echo $row_rsRegionCountries['ID']; ?>" onclick="document.returnValue = confirm('Are you sure you want to delete this country?'); return document.returnValue;" class="link_delete"><i class="glyphicon glyphicon-trash"></i> Delete</a></td>
        </tr></tbody>
</table>
      <?php } // Show if recordset not empty ?><?php if(isset($submit_error)) { ?>
      <p class="alert warning alert-warning" role="alert"><?php echo $submit_error; ?></p>
      <?php } ?>
     
    <fieldset class="form-inline">    <label>
          <select name="country_id"  id="country_id" class="form-control">
            <option value=""><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
            <?php
do {  
?>
            <option value="<?php echo $row_rsCountries['ID']?>"><?php echo $row_rsCountries['fullname']?></option>
            <?php
} while ($row_rsCountries = mysql_fetch_assoc($rsCountries));
  $rows = mysql_num_rows($rsCountries);
  if($rows > 0) {
      mysql_data_seek($rsCountries, 0);
	  $row_rsCountries = mysql_fetch_assoc($rsCountries);
  }
?>
          </select>
        </label>
        <input name="regionID" type="hidden" id="regionID" value="<?php echo $row_rsRegion['ID']; ?>" />
       
          <button type="submit" class="btn btn-default btn-secondary" >Add country</button>
           <label><input type="checkbox" name="addcountrytoall" id="addcountrytoall">
          Add country to all sites</label></fieldset>
       
      
      <p>NOTE: Countires will only appear in this drop down list if they are not associated with a site.  For a full list of countries and their associate sites, <a href="../../../region/admin/countries/index.php">click here</a>. </p>
</div>
    
    
</div>
  </div>
  <div><button type="submit" class="btn btn-primary" >Save changes</button></div>
 </form>
<?php } else { // end authorsied?>
<p class="alert warning alert-warning" role="alert">You do not have access to update this site.</p>
<?php } ?></div>
<script>
<!--
var TabbedPanels1 = new Spry.Widget.TabbedPanels("TabbedPanels1");

//-->
</script>
<!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsRegion);

mysql_free_result($rsStatus);

mysql_free_result($rsLoggedIn);

mysql_free_result($rsRegionCountries);

mysql_free_result($rsCountries);

mysql_free_result($rsOtherRegions);
?>