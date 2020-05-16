<?php require_once('../../Connections/aquiescedb.php'); ?>
<?php require_once('../../members/includes/userfunctions.inc.php'); ?><?php require_once('../../core/includes/upload.inc.php'); ?><?php require_once('../../core/includes/framework.inc.php'); ?>
<?php require_once('../../mail/includes/sendmail.inc.php'); ?>
<?php

$regionID = (isset($regionID)  && $regionID>0) ? intval($regionID ): 1;



if (!isset($_SESSION)) {
  session_start();
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

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT * FROM preferences WHERE ID=".$regionID;
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);

$uploaded = getUploads();
if (isset($uploaded) && is_array($uploaded)) {
	if(isset($uploaded["filename"][0]["newname"]) && $uploaded["filename"][0]["newname"]!="") { 
		$_POST['imageURL'] = $uploaded["filename"][0]["newname"]; 
	}
	$_POST['imageURL'] = (isset($_POST["noimage"])) ? "" : $_POST['imageURL'];
}

if (isset($_GET['deletelocationuserID']) && intval($_GET['deletelocationuserID']) > 0) {
	$delete = "DELETE FROM locationuser WHERE ID = ".intval($_GET['deletelocationuserID']);
	mysql_select_db($database_aquiescedb, $aquiescedb);
	mysql_query($delete, $aquiescedb) or die(mysql_error());										  
}

if (isset($_GET['adduserID']) && intval($_GET['adduserID']) > 0) {
	addUserToLocation($_GET['adduserID'], $_GET['locationID'], $_GET['createdbyID']);
}


if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "add")) {
  $updateSQL = sprintf("UPDATE location SET categoryID=%s, locationname=%s, `description`=%s, address1=%s, address2=%s, address3=%s, address4=%s, address5=%s, postcode=%s, countryID=%s, telephone1=%s, telephone2=%s, fax=%s, imageURL=%s, latitude=%s, longitude=%s, modifiedbyID=%s, modifieddatetime=%s WHERE ID=%s",
                       GetSQLValueString($_POST['categoryID'], "int"),
                       GetSQLValueString($_POST['locationname'], "text"),
                       GetSQLValueString($_POST['description'], "text"),
                       GetSQLValueString($_POST['address1'], "text"),
                       GetSQLValueString($_POST['address2'], "text"),
                       GetSQLValueString($_POST['address3'], "text"),
                       GetSQLValueString($_POST['address4'], "text"),
                       GetSQLValueString($_POST['address5'], "text"),
                       GetSQLValueString($_POST['postcode'], "text"),
                       GetSQLValueString($_POST['countryID'], "int"),
                       GetSQLValueString($_POST['telephone1'], "text"),
                       GetSQLValueString($_POST['telephone2'], "text"),
                       GetSQLValueString($_POST['fax'], "text"),
                       GetSQLValueString($_POST['imageURL'], "text"),
                       GetSQLValueString($_POST['latitude'], "double"),
                       GetSQLValueString($_POST['longitude'], "double"),
                       GetSQLValueString($_POST['modifiedbyID'], "int"),
                       GetSQLValueString($_POST['modifieddatetime'], "date"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}
  
if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "add")) {
	
	if($row_rsPreferences['userupdatealert']==1) {
			$to = $row_rsPreferences['contactemail'];
			$subject = $site_name." user address update";
			$message = $_POST['firstname']." ".$_POST['surname']." has updated an address.\n\n";
			$message .= "View their user profile here:\n\n";
			$message .= getProtocol()."://". $_SERVER['HTTP_HOST']."/members/admin/modify_user.php?userID=".intval($_POST['userID']);			
			sendMail($to,$subject,$message);
		}
		
  
  $updateGoTo = isset($_GET['returnURL']) ? $_GET['returnURL'] :  $_POST['referer'];
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo)); exit;
}

$colname_rsLocation = "-1";
if (isset($_GET['locationID'])) {
  $colname_rsLocation = $_GET['locationID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLocation = sprintf("SELECT location.*, users.firstname, users.surname FROM location LEFT JOIN users ON (location.userID = users.ID) WHERE location.ID = %s", GetSQLValueString($colname_rsLocation, "int"));
$rsLocation = mysql_query($query_rsLocation, $aquiescedb) or die(mysql_error());
$row_rsLocation = mysql_fetch_assoc($rsLocation);
$totalRows_rsLocation = mysql_num_rows($rsLocation);



mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsAreas = "SELECT ID, categoryname FROM locationcategory WHERE statusID = 1 ORDER BY categoryname ASC";
$rsAreas = mysql_query($query_rsAreas, $aquiescedb) or die(mysql_error());
$row_rsAreas = mysql_fetch_assoc($rsAreas);
$totalRows_rsAreas = mysql_num_rows($rsAreas);

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID, usertypeID,  users.regionID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

$varLocationID_rsUser = "-1";
if (isset($_GET['locationID'])) {
  $varLocationID_rsUser = $_GET['locationID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsUser = sprintf("SELECT location.userID, users.firstname, users.surname, users.email FROM location INNER JOIN users ON (location.userID = users.ID) WHERE location.ID = %s", GetSQLValueString($varLocationID_rsUser, "int"));
$rsUser = mysql_query($query_rsUser, $aquiescedb) or die(mysql_error());
$row_rsUser = mysql_fetch_assoc($rsUser);
$totalRows_rsUser = mysql_num_rows($rsUser);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLocationPrefs = "SELECT * FROM locationprefs";
$rsLocationPrefs = mysql_query($query_rsLocationPrefs, $aquiescedb) or die(mysql_error());
$row_rsLocationPrefs = mysql_fetch_assoc($rsLocationPrefs);
$totalRows_rsLocationPrefs = mysql_num_rows($rsLocationPrefs);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsCountries = "SELECT fullname, regionID, countries.ID FROM countries WHERE statusID = 1 ORDER BY ordernum ASC, fullname ASC";
$rsCountries = mysql_query($query_rsCountries, $aquiescedb) or die(mysql_error());
$row_rsCountries = mysql_fetch_assoc($rsCountries);
$totalRows_rsCountries = mysql_num_rows($rsCountries);
?>
<!doctype html>
<!-- Web design by Paul Egan, Jim Campbell -->
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "View/edit address location"; echo $pageTitle." | ".$site_name; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script src="../admin/scripts/findUsers.js"></script>
<?php if ($row_rsPreferences['uselocationcategory']!=1) { ?>
<style>
.areas {
	display:none;
}
</style>
<?php } ?>
<?php $googlemapsAPI =isset($googlemapsAPI) ? $googlemapsAPI : $row_rsPreferences['googlemapsAPI'];
if($googlemapsAPI!="") { ?>
<script src="/location/scripts/location.js"></script>
<script src="//maps.google.com/?file=api&amp;v=2.x&amp;key=<?php echo $googlemapsAPI; ?>"></script>
<script src="/core/scripts/googlemaps/googlemap.js" ></script>
<script src="/core/scripts/googlemaps/fb_maps.js" ></script>
<script>

if(typeof(fb_maps_version) === 'undefined' || fb_maps_version < 2) alert("Javascript library fb_maps.js needs updating.");

var initLatitude = <?php echo isset($row_rsLocation['latitude']) ? $row_rsLocation['latitude']: 0; ?>;
var initLongitude =<?php echo isset($row_rsLocation['longitude']) ? $row_rsLocation['longitude']: 0; ?>;
var initZoom = <?php echo isset($row_rsLocation['latitude']) ? 16 : 2; ?> ;
var initMapType = G_NORMAL_MAP;
var showMapType = false;
var mapControlType = "small"; // delete for  normal 
var markerLatitude = <?php echo isset($row_rsLocation['latitude']) ? $row_rsLocation['latitude']: "null"; ?>;
var markerLongitude = <?php echo isset($row_rsLocation['longitude']) ? $row_rsLocation['longitude']: "null"; ?>;
var defaultIcon = new GIcon(G_DEFAULT_ICON);
var isEditable = true;

function init() {
setupMap();
document.getElementById('address').value = document.getElementById('address1').value+" "+document.getElementById('postcode').value;
if(markerLatitude) {
	createMarker(markerLatitude,markerLongitude,defaultIcon,true) 
} else { // try and find a location
	findLocation(document.getElementById('address1').value+" "+document.getElementById('postcode').value);
}
if(gl) { // location services available
		document.getElementById('getLocation').style.display = 'inline'; // change from default 'block' in location.js
	}
	
}

addListener("load",init);

//]]>
</script>
<?php if(isset($row_rsPreferences['googlesearchAPI'])) { // load GoogleAjaxSearch ?>
<script src="//www.google.com/uds/api?file=uds.js&v=1.0&key=<?php echo $row_rsPreferences['googlesearchAPI']; ?>"></script>
<script>
var localSearch = new GlocalSearch();
</script>
<?php } } 
else { echo "<style> #tabMaps { display:none; } </style>" ; } ?>
<style>
<!--
<?php if(!isset($_GET['userID'])) {
echo ".defaultAddress { display:none; }";
}
?>
-->
</style>
<?php if ($row_rsPreferences['uselocationcategory']!=1) { ?>
<style>
.areas {
	display:none;
}
</style>
<?php } ?>
<?php if(isset($_GET['useraddress'])) { // user address so hide some fields
echo "<style>.areas {display:none;} .locationimage{display:none;} .locationdescription {display:none;} .fax{display:none;} </style>"; } ?>
<script src="../../SpryAssets/SpryTabbedPanels.js"></script>
<link href="../../SpryAssets/SpryTabbedPanels.css" rel="stylesheet" type="text/css" />
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" --> <div class="container pageBody location">
    <h1><?php echo isset($_GET['useraddress']) ? "Address" : ucwords($row_rsLocationPrefs['locationdescriptor']); ?></h1>
    <?php if(isset($submit_error)) { ?>
    <p class="alert alert-danger" role="alert"><?php echo $submit_error; ?></p>
    <?php } ?>
    <form action="<?php echo $editFormAction; ?>" method="post" enctype="multipart/form-data" name="add" id="add">
      <div id="TabbedPanels1" class="TabbedPanels">
        <ul class="TabbedPanelsTabGroup">
          <li class="TabbedPanelsTab" tabindex="0">Address</li>
          <li class="TabbedPanelsTab" tabindex="0" id="tabMaps">Map</li>
        </ul>
        <div class="TabbedPanelsContentGroup">
          <div class="TabbedPanelsContent">
            <table class="form-table">
              <tr class="areas">
                <td class="text-right">Category:</td>
                <td><select name="categoryID"  id="categoryID" class="form-control">
                    <option value="" <?php if (!(strcmp(0, $row_rsLocation['categoryID']))) {echo "selected=\"selected\"";} ?>>None specified</option>
                    <?php
do {  
?>
                    <option value="<?php echo $row_rsAreas['ID']?>"<?php if (!(strcmp($row_rsAreas['ID'], $row_rsLocation['categoryID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsAreas['categoryname']?></option>
                    <?php
} while ($row_rsAreas = mysql_fetch_assoc($rsAreas));
  $rows = mysql_num_rows($rsAreas);
  if($rows > 0) {
      mysql_data_seek($rsAreas, 0);
	  $row_rsAreas = mysql_fetch_assoc($rsAreas);
  }
?>
                  </select></td>
              </tr>
              <tr>
                <td class="text-right">Location Name: </td>
                <td><input name="locationname" type="text"  id="locationname" value="<?php echo htmlentities($row_rsLocation['locationname'], ENT_COMPAT, "UTF-8"); ?>" size="40" maxlength="50"  class="form-control" /></td>
              </tr>
              <tr class="locationdescription">
                <td class="text-right">Location Description: </td>
                <td><textarea name="description" cols="40" rows="4" id="description"  class="form-control"><?php echo htmlentities($row_rsLocation['description'], ENT_COMPAT, "UTF-8"); ?></textarea></td>
              </tr>
              <tr>
                <td class="text-right"> Address:</td>
                <td><input name="address1" type="text"  id="address1" value="<?php echo htmlentities($row_rsLocation['address1'], ENT_COMPAT, "UTF-8"); ?>" size="40" maxlength="50" /></td>
              </tr>
              <tr>
                <td class="text-right">&nbsp;</td>
                <td><input name="address2" type="text"  id="address2" value="<?php echo htmlentities($row_rsLocation['address2'], ENT_COMPAT, "UTF-8"); ?>" size="40" maxlength="30"  class="form-control"/></td>
              </tr>
              <tr>
                <td class="text-right">&nbsp;</td>
                <td><input name="address3" type="text"  id="address3" value="<?php echo htmlentities($row_rsLocation['address3'], ENT_COMPAT, "UTF-8"); ?>" size="40" maxlength="30" class="form-control" /></td>
              </tr>
              <tr>
                <td class="text-right">&nbsp;</td>
                <td><input name="address4" type="text"  id="address4" value="<?php echo htmlentities($row_rsLocation['address4'], ENT_COMPAT, "UTF-8"); ?>" size="40" maxlength="30"  class="form-control"/></td>
              </tr>
              <tr>
                <td class="text-right">&nbsp;</td>
                <td><input name="address5" type="text"  id="address5" value="<?php echo htmlentities($row_rsLocation['address5'], ENT_COMPAT, "UTF-8"); ?>" size="40" maxlength="30" class="form-control" /></td>
              </tr>
              <tr>
                <td class="text-right">Postcode: </td>
                <td><input name="postcode" type="text"  id="postcode" value="<?php echo htmlentities($row_rsLocation['postcode'], ENT_COMPAT, "UTF-8"); ?>" size="40" maxlength="10"  class="form-control"/></td>
              </tr>
              <tr class="country">
                <td class="text-right">Country:</td>
                <td><select name="countryID"  id="countryID" class="form-control">
                  <option value="" <?php if (!(strcmp("", $row_rsLocation['countryID']))) {echo "selected=\"selected\"";} ?>><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
                  <?php
do {  
?>
<option value="<?php echo $row_rsCountries['ID']?>"<?php if (!(strcmp($row_rsCountries['ID'], $row_rsLocation['countryID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsCountries['fullname']?></option>
                  <?php
} while ($row_rsCountries = mysql_fetch_assoc($rsCountries));
  $rows = mysql_num_rows($rsCountries);
  if($rows > 0) {
      mysql_data_seek($rsCountries, 0);
	  $row_rsCountries = mysql_fetch_assoc($rsCountries);
  }
?>
                </select></td>
              </tr>
              <tr>
                <td class="text-right">Telephone:</td>
                <td><input name="telephone1" type="text"  id="telephone1" value="<?php echo htmlentities($row_rsLocation['telephone1'], ENT_COMPAT, "UTF-8"); ?>" size="40" maxlength="20"  class="form-control"/></td>
              </tr>
              <tr>
                <td class="text-right">Alternative telephone:</td>
                <td><input name="telephone2" type="text"  id="telephone2" value="<?php echo htmlentities($row_rsLocation['telephone2'], ENT_COMPAT, "UTF-8"); ?>" size="40" maxlength="20"  class="form-control" /></td>
              </tr>
              <tr class="locationfax">
                <td class="text-right">Fax:</td>
                <td><input name="fax" type="text"  id="fax" value="<?php echo htmlentities($row_rsLocation['fax'], ENT_COMPAT, "UTF-8"); ?>" size="40" maxlength="20" /></td>
              </tr>
              <tr class="locationimage">
                <td class="text-nowrap text-right"><input type="hidden" name="imageURL" value="<?php echo $row_rsLocation['imageURL']; ?>" />
                  Image:</td>
                <td><?php if (isset($row_rsLocation['imageURL'])) { ?>
                    <img src="<?php echo getImageURL($row_rsLocation['imageURL'],"medium"); ?>" alt="" /><br />
                    <input name="noImage" type="checkbox" value="1" />
                    <?php } else { ?>
                    No image associated with this article location.
                    <?php } ?>
                  <span class="upload"><br />
                  Add/change image below:<br />
                  <input name="filename" type="file" class="fileinput" id="filename" size="20" />
                  </span></td>
              </tr>
            </table>
            <input type="hidden" name="MM_update" value="add" />
          </div>
          <div class="TabbedPanelsContent">
            <p class="form-inline">Drag marker or find address:
              <input name="address" type="text"  id="address" size="30" maxlength="100" class="form-control" />
              <button name="find" type="button" class="btn btn-default btn-secondary" id="find" onclick="findLocation(document.getElementById('address').value);" >Find</button>
              <button name="Button" type="button" class="btn btn-default btn-secondary" onclick="clearMap();" >Clear</button>
              <span id="getLocation" class="locationServices">
              <button type="button" class="btn btn-default btn-secondary" onclick="getGeoLocation(); return false;" >Get my location</button>
              </span>
              <button name="skip" type="submit"  id="skip" class="btn btn-primary submit" >Save location</button>
            </p>
            <div class="googlemap" id="googlemap"></div>
            <input type="hidden" id="latitude" name="latitude" value="<?php echo $row_rsLocation['latitude']; ?>" />
            <input type="hidden" id="longitude" name="longitude" value="<?php echo $row_rsLocation['longitude']; ?>" />
          </div>
        </div>
      </div>
      <br class="clearfloat" />
      <button type="submit" class="btn btn-primary" >Save changes</button>
      <input name="returnURL" type="hidden" id="returnURL" value="<?php echo isset($_GET['returnURL']) ? htmlentities($_GET['returnURL']) : ""; ?>" />
      <input name="userID" type="hidden" id="userID" value="<?php echo $row_rsLocation['userID']; ?>" />
      <input name="modifiedbyID" type="hidden" id="modifiedbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
      <input name="modifieddatetime" type="hidden" id="modifieddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
      <input name="referer" type="hidden" id="referer" value="<?php echo $_SERVER['HTTP_REFERER']; ?>" />
      <input name="ID" type="hidden" id="ID" value="<?php echo $row_rsLocation['ID']; ?>" />
      <input name="firstname" type="hidden" id="firstname" value="<?php echo htmlentities($row_rsLocation['firstname'], ENT_COMPAT, "UTF-8"); ?>">
      <input name="surname" type="hidden" id="surname" value="<?php echo htmlentities($row_rsLocation['surname'], ENT_COMPAT, "UTF-8"); ?>">
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
    <?php } ?></div>
    <!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLocation);

mysql_free_result($rsPreferences);

mysql_free_result($rsAreas);

mysql_free_result($rsLoggedIn);

mysql_free_result($rsUser);

mysql_free_result($rsLocationPrefs);

mysql_free_result($rsCountries);
?>
