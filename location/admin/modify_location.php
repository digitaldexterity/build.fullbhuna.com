<?php require_once('../../Connections/aquiescedb.php'); ?>
<?php require_once('../../core/includes/adminAccess.inc.php'); ?>
<?php require_once('../../core/includes/framework.inc.php'); ?>
<?php require_once('../../members/includes/userfunctions.inc.php'); ?><?php require_once('../../core/includes/upload.inc.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "10,9,8,7";
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

$regionID = (isset($regionID) && intval($regionID)>0) ? intval($regionID) : 1;

$uploaded = getUploads();
if (isset($uploaded) && is_array($uploaded)) {
	if(isset($uploaded["filename"][0]["newname"]) && $uploaded["filename"][0]["newname"]!="") { 
		$_POST['imageURL'] = $uploaded["filename"][0]["newname"]; 
	}
	$_POST['imageURL'] = (isset($_POST["noimage"])) ? "" : $_POST['imageURL'];
}

if (isset($_GET['deletelocationuserID']) && intval($_GET['deletelocationuserID']) > 0) {
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$select = "SELECT userID FROM locationuser WHERE ID = ".intval($_GET['deletelocationuserID']);
	$result = mysql_query($select, $aquiescedb) or die(mysql_error());
	if(mysql_num_rows($result )>0) {
		$user = mysql_fetch_assoc($result);
		
		$delete = "DELETE FROM locationuser WHERE ID = ".intval($_GET['deletelocationuserID']);	
		mysql_query($delete, $aquiescedb) or die(mysql_error());	
		$update = "UPDATE location SET userID = NULL WHERE userID = ".$user['userID']." AND ID =".intval($_GET['locationID']);
		mysql_query($update, $aquiescedb) or die(mysql_error());
		
		$update = "UPDATE users SET defaultaddressID = NULL WHERE ID = ".$user['userID']." AND defaultaddressID = ".intval($_GET['locationID']);
		mysql_query($update, $aquiescedb) or die(mysql_error());
	}
}

if (isset($_GET['adduserID']) && intval($_GET['adduserID']) > 0) {
	addUserToLocation($_GET['adduserID'], $_GET['locationID'], $_GET['createdbyID']);
}

$_POST['userID'] = isset($_POST["userID"]) ? $_POST['userID'] : "";


if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "add")) {
  $updateSQL = sprintf("UPDATE location SET userID=%s, categoryID=%s, `public`=%s, locationname=%s, `description`=%s, address1=%s, address2=%s, address3=%s, address4=%s, address5=%s, postcode=%s, telephone1=%s, telephone2=%s, telephone3=%s, fax=%s, imageURL=%s, mapURL=%s, locationURL=%s, locationemail=%s, latitude=%s, longitude=%s, active=%s, modifiedbyID=%s, modifieddatetime=%s WHERE ID=%s",
                       GetSQLValueString($_POST['userID'], "int"),
                       GetSQLValueString($_POST['categoryID'], "int"),
                       GetSQLValueString(isset($_POST['public']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['locationname'], "text"),
                       GetSQLValueString($_POST['description'], "text"),
                       GetSQLValueString($_POST['address1'], "text"),
                       GetSQLValueString($_POST['address2'], "text"),
                       GetSQLValueString($_POST['address3'], "text"),
                       GetSQLValueString($_POST['address4'], "text"),
                       GetSQLValueString($_POST['address5'], "text"),
                       GetSQLValueString($_POST['postcode'], "text"),
                       GetSQLValueString($_POST['telephone1'], "text"),
                       GetSQLValueString($_POST['telephone2'], "text"),
                       GetSQLValueString($_POST['telephone3'], "text"),
                       GetSQLValueString($_POST['fax'], "text"),
                       GetSQLValueString($_POST['imageURL'], "text"),
                       GetSQLValueString($_POST['mapURL'], "text"),
                       GetSQLValueString($_POST['locationURL'], "text"),
                       GetSQLValueString($_POST['locationemail'], "text"),
                       GetSQLValueString($_POST['latitude'], "double"),
                       GetSQLValueString($_POST['longitude'], "double"),
                       GetSQLValueString(isset($_POST['active']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['modifiedbyID'], "int"),
                       GetSQLValueString($_POST['modifieddatetime'], "date"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}
  
if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "add")) {
  if (isset($_POST['makedefault']) && $_POST['userID'] > 0) {
$updateSQL = "UPDATE users SET defaultaddressID = ".GetSQLValueString($_POST['ID'], "int")." WHERE users.ID = ". GetSQLValueString($_POST['userID'], "int");
mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}

  $updateGoTo = isset($_GET['returnURL']) ? $_GET['returnURL'] : "index.php?orderby=datetime"; //die($updateSQL);
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
$query_rsLocation = sprintf("SELECT * FROM location WHERE ID = %s", GetSQLValueString($colname_rsLocation, "int"));
$rsLocation = mysql_query($query_rsLocation, $aquiescedb) or die(mysql_error());
$row_rsLocation = mysql_fetch_assoc($rsLocation);
$totalRows_rsLocation = mysql_num_rows($rsLocation);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT * FROM preferences WHERE ID = ".$regionID;
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsCategories = "SELECT ID, categoryname FROM locationcategory WHERE statusID = 1 ORDER BY categoryname ASC";
$rsCategories = mysql_query($query_rsCategories, $aquiescedb) or die(mysql_error());
$row_rsCategories = mysql_fetch_assoc($rsCategories);
$totalRows_rsCategories = mysql_num_rows($rsCategories);

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID, usertypeID,  users.regionID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

$colname_rsDefaultAddress = "-1";
if (isset($_GET['userID'])) {
  $colname_rsDefaultAddress = $_GET['userID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsDefaultAddress = sprintf("SELECT defaultaddressID FROM users WHERE ID = %s", GetSQLValueString($colname_rsDefaultAddress, "int"));
$rsDefaultAddress = mysql_query($query_rsDefaultAddress, $aquiescedb) or die(mysql_error());
$row_rsDefaultAddress = mysql_fetch_assoc($rsDefaultAddress);
$totalRows_rsDefaultAddress = mysql_num_rows($rsDefaultAddress);

$varLocationID_rsUser = "-1";
if (isset($_GET['locationID'])) {
  $varLocationID_rsUser = $_GET['locationID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsUser = sprintf("SELECT location.userID, users.firstname, users.surname, users.email FROM location INNER JOIN users ON (location.userID = users.ID) WHERE location.ID = %s", GetSQLValueString($varLocationID_rsUser, "int"));
$rsUser = mysql_query($query_rsUser, $aquiescedb) or die(mysql_error());
$row_rsUser = mysql_fetch_assoc($rsUser);
$totalRows_rsUser = mysql_num_rows($rsUser);

$varLocationID_rsLocationUsers = "-1";
if (isset($_GET['locationID'])) {
  $varLocationID_rsLocationUsers = $_GET['locationID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLocationUsers = sprintf("SELECT locationuser.ID, locationuser.userID, users.firstname, users.surname, users.email, locationuserrelationship.relationship FROM locationuser LEFT JOIN users ON (locationuser.userID = users.ID) LEFT JOIN locationuserrelationship ON (relationshipID = locationuserrelationship.ID) WHERE locationuser.locationID = %s", GetSQLValueString($varLocationID_rsLocationUsers, "int"));
$rsLocationUsers = mysql_query($query_rsLocationUsers, $aquiescedb) or die(mysql_error());
$row_rsLocationUsers = mysql_fetch_assoc($rsLocationUsers);
$totalRows_rsLocationUsers = mysql_num_rows($rsLocationUsers);

$maxRows_rsCommunication = 100;
$pageNum_rsCommunication = 0;
if (isset($_GET['pageNum_rsCommunication'])) {
  $pageNum_rsCommunication = $_GET['pageNum_rsCommunication'];
}
$startRow_rsCommunication = $pageNum_rsCommunication * $maxRows_rsCommunication;

$varLocationID_rsCommunication = "-1";
if (isset($_GET['locationID'])) {
  $varLocationID_rsCommunication = $_GET['locationID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsCommunication = sprintf("SELECT communication.*, communicationtype.typename, CONCAT(users.firstname, ' ', users.surname) AS username, CONCAT(client.firstname, ' ' ,client.surname) AS clientname FROM communication LEFT JOIN communicationtype ON (communication.commtypeID = communicationtype.ID) LEFT JOIN users ON (communication.userID = users.ID) LEFT JOIN users AS client ON (communication.clientID = client.ID) WHERE communication.locationID = %s ORDER BY thiscommdatetime DESC", GetSQLValueString($varLocationID_rsCommunication, "int"));
$query_limit_rsCommunication = sprintf("%s LIMIT %d, %d", $query_rsCommunication, $startRow_rsCommunication, $maxRows_rsCommunication);
$rsCommunication = mysql_query($query_limit_rsCommunication, $aquiescedb) or die(mysql_error());
$row_rsCommunication = mysql_fetch_assoc($rsCommunication);

if (isset($_GET['totalRows_rsCommunication'])) {
  $totalRows_rsCommunication = $_GET['totalRows_rsCommunication'];
} else {
  $all_rsCommunication = mysql_query($query_rsCommunication);
  $totalRows_rsCommunication = mysql_num_rows($all_rsCommunication);
}
$totalPages_rsCommunication = ceil($totalRows_rsCommunication/$maxRows_rsCommunication)-1;

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLocationPrefs = "SELECT * FROM locationprefs WHERE ID = ".$regionID;
$rsLocationPrefs = mysql_query($query_rsLocationPrefs, $aquiescedb) or die(mysql_error());
$row_rsLocationPrefs = mysql_fetch_assoc($rsLocationPrefs);
$totalRows_rsLocationPrefs = mysql_num_rows($rsLocationPrefs);

$varLocationID_rsThisDirectory = "-1";
if (isset($_GET['locationID'])) {
  $varLocationID_rsThisDirectory = $_GET['locationID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisDirectory = sprintf("SELECT directory.name FROM directory LEFT JOIN directorylocation ON (directory.ID = directorylocation.directoryID) WHERE directorylocation.locationID = %s  ORDER BY directorylocation.createddatetime DESC LIMIT 1", GetSQLValueString($varLocationID_rsThisDirectory, "int"));
$rsThisDirectory = mysql_query($query_rsThisDirectory, $aquiescedb) or die(mysql_error());
$row_rsThisDirectory = mysql_fetch_assoc($rsThisDirectory);
$totalRows_rsThisDirectory = mysql_num_rows($rsThisDirectory);


if(intval($row_rsLocation['userID'])>0 && $totalRows_rsLocationUsers==0) { // fix old data
	addUserToLocation($row_rsLocation['userID'], $row_rsLocation['ID'],0);
	header("location: ".$_SERVER['REQUEST_URI']); exit;
}
?>
<!doctype html>
<!-- Web design by Paul Egan, Jim Campbell -->
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php echo $site_name; ?>
<?php echo $admin_name; ?> - Modify <?php echo ucwords($row_rsLocationPrefs['locationdescriptor']); ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script>window.jQuery || document.write('<script src="/3rdparty/jquery/jquery-1.12.1.min.js"><\/script>'); // if not already loaded
</script>
<script src="scripts/findUsers.js"></script>
<script src="../../SpryAssets/SpryValidationTextField.js"></script>
<?php if ($row_rsPreferences['uselocationcategory']!=1) { ?>
<style>
.areas { display:none; }
</style>
<?php } ?>
<script src="../../SpryAssets/SpryTabbedPanels.js"></script>
<link href="../../SpryAssets/SpryTabbedPanels.css" rel="stylesheet"  />
<?php $googlemapsAPI =isset($googlemapsAPI) ? $googlemapsAPI : $row_rsPreferences['googlemapsAPI'];
if($googlemapsAPI!="") { ?>
<script src="/location/scripts/location.js"></script>
<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo $googlemapsAPI; ?>&v=3" ></script>

<script>


var initLatitude = <?php echo isset($row_rsLocation['latitude']) ? $row_rsLocation['latitude']: 0; ?>;
var initLongitude =<?php echo isset($row_rsLocation['longitude']) ? $row_rsLocation['longitude']: 0; ?>;
var initZoom = 16;
var markerLatitude = <?php echo isset($row_rsLocation['latitude']) ? $row_rsLocation['latitude']: "null"; ?>;
var markerLongitude = <?php echo isset($row_rsLocation['longitude']) ? $row_rsLocation['longitude']: "null"; ?>;
var map;
var geocoder;
var marker;

$(document).ready(function(e) {
	resetMap();
});


function findAddress() {
    var address = document.getElementById("address").value;
    geocoder.geocode( { 'address': address}, function(results, status) {
      if (status == google.maps.GeocoderStatus.OK) {		 
		map.panTo(results[0].geometry.location);		
		marker.setPosition(results[0].geometry.location);			
		document.getElementById("latitude").value = results[0].geometry.location.lat();
		document.getElementById("longitude").value = results[0].geometry.location.lng();		
      } else {
        alert("Geocode was not successful for the following reason: " + status);
      }
    });
  }

function resetMap() {
	geocoder = new google.maps.Geocoder();
   var mapOptions = {
        zoom: initZoom ,
        center: new google.maps.LatLng(initLatitude, initLongitude),
        scaleControl: true,
        overviewMapControl: true,
        overviewMapControlOptions:{opened:true},
        mapTypeId: google.maps.MapTypeId.ROADMAP,
		streetViewControl:true
	};
	map = new google.maps.Map(document.getElementById('googlemap'), mapOptions);
	var properties = {			
			map: map,
			draggable:true			
		};
	marker = new google.maps.Marker(properties);
	google.maps.event.addListener(marker, 'dragend', function (event) {    		
			map.panTo(this.getPosition());
			document.getElementById("latitude").value = this.getPosition().lat();
    		document.getElementById("longitude").value = this.getPosition().lng();
			
		});	
	if(markerLatitude) { 
		markerpos = new google.maps.LatLng(markerLatitude, markerLongitude);
		marker.setPosition(markerpos);			
			
	} else { // if we don't have a marker position
		findAddress();
	}
}

//]]>
</script>
<?php } 
else { echo "<style> #tabMaps { display:none; } </style>" ; } ?>
<style>
<!--
<?php if(!isset($_GET['userID'])) { echo ".defaultAddress { display:none; }" ; } ?>
-->
</style>
<link href="../../SpryAssets/SpryValidationTextField.css" rel="stylesheet"  />
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" -->
        <div class="page location">
      <h1><i class="glyphicon glyphicon-flag"></i> <?php echo isset($row_rsThisDirectory['name']) ? $row_rsThisDirectory['name']." - ": ""; ?><?php echo $row_rsLocation['locationname']; ?></h1>
      <?php if(isset($submit_error)) { ?>
      <p class="alert alert-danger" role="alert"><?php echo $submit_error; ?></p>
      <?php } ?>
      <form action="<?php echo $editFormAction; ?>" method="post" enctype="multipart/form-data" name="add" id="add">
        <div id="TabbedPanels1" class="TabbedPanels">
          <ul class="TabbedPanelsTabGroup">
            <li class="TabbedPanelsTab" tabindex="0">Address</li>
            <li class="TabbedPanelsTab" tabindex="0" id="tabMaps">Map</li>
            <li class="TabbedPanelsTab" tabindex="0">Contacts</li>
            <li class="TabbedPanelsTab" tabindex="0">Notes</li>
          </ul>
          <div class="TabbedPanelsContentGroup">
            <div class="TabbedPanelsContent">
              <table class="form-table">
                <tr class="areas">
                  <td class="text-right">Category:</td>
                  <td class="form-inline"><select name="categoryID"  id="categoryID"  class="form-control">
                    <option value="">None specified</option>
                    <?php
do {  
?>
                    <option value="<?php echo $row_rsCategories['ID']; ?>" <?php if ($row_rsCategories['ID'] == $row_rsLocation['categoryID']) {echo "selected=\"selected\"";} ?>><?php echo $row_rsCategories['categoryname']?></option>
                    <?php
} while ($row_rsCategories = mysql_fetch_assoc($rsCategories));
  $rows = mysql_num_rows($rsCategories);
  if($rows > 0) {
      mysql_data_seek($rsCategories, 0);
	  $row_rsCategories = mysql_fetch_assoc($rsCategories);
  }
?>
                  </select>
                    <a href="category/index.php">Manage Categories</a></td>
                </tr>
                <tr>
                  <td class="text-right"><?php echo ucwords($row_rsLocationPrefs['locationdescriptor']); ?> Name: </td>
                  <td><input name="locationname" type="text"  id="locationname" value="<?php echo $row_rsLocation['locationname']; ?>" size="40" maxlength="50"  class="form-control"/></td>
                </tr>
                <tr>
                  <td class="text-right"> Address:</td>
                  <td><input name="address1" type="text"  id="address1" value="<?php echo $row_rsLocation['address1']; ?>" size="40" maxlength="50" class="form-control" /></td>
                </tr>
                <tr>
                  <td class="text-right">&nbsp;</td>
                  <td><input name="address2" type="text"  id="address2" value="<?php echo $row_rsLocation['address2']; ?>" size="40" maxlength="30"  class="form-control"/></td>
                </tr>
                <tr>
                  <td class="text-right">&nbsp;</td>
                  <td><input name="address3" type="text"  id="address3" value="<?php echo $row_rsLocation['address3']; ?>" size="40" maxlength="30" class="form-control" /></td>
                </tr>
                <tr>
                  <td class="text-right">&nbsp;</td>
                  <td><input name="address4" type="text"  id="address4" value="<?php echo $row_rsLocation['address4']; ?>" size="40" maxlength="30"  class="form-control"/></td>
                </tr>
                <tr>
                  <td class="text-right">&nbsp;</td>
                  <td><input name="address5" type="text"  id="address5" value="<?php echo $row_rsLocation['address5']; ?>" size="40" maxlength="30"  class="form-control"/></td>
                </tr>
                <tr>
                  <td class="text-right">Postcode: </td>
                  <td><input name="postcode" type="text"  id="postcode" value="<?php echo $row_rsLocation['postcode']; ?>" size="20" maxlength="10"  class="form-control"/>
                    <?php if(trim($row_rsLocationPrefs['postcodecheckerkey'])!="") { ?>
                    <script src="//services.postcodeanywhere.co.uk/popups/javascript.aspx?account_code=indiv46069&amp;license_key=<?php echo $row_rsLocationPrefs['postcodecheckerkey']; ?>"></script>
                    <?php } ?></td>
                </tr>
                <tr>
                  <td class="text-right">Telephone 1:</td>
                  <td><input name="telephone1" type="text"  id="telephone1" value="<?php echo $row_rsLocation['telephone1']; ?>" size="40" maxlength="50"  class="form-control"/></td>
                </tr>
                <tr>
                  <td class="text-right">Telephone 2:</td>
                  <td><input name="telephone2" type="text"  id="telephone2" value="<?php echo $row_rsLocation['telephone2']; ?>" size="40" maxlength="50"  class="form-control"/></td>
                </tr>
                <tr>
                  <td class="text-right">Telephone 3:</td>
                  <td><input name="telephone3" type="text"  id="telephone3" value="<?php echo $row_rsLocation['telephone3']; ?>" size="40" maxlength="50"  class="form-control"/></td>
                </tr>
                <tr>
                  <td class="text-right">Fax:</td>
                  <td><input name="fax" type="text"  id="fax" value="<?php echo $row_rsLocation['fax']; ?>" size="40" maxlength="20"  class="form-control"/></td>
                </tr>
                <tr>
                  <td class="text-right" ><?php echo ucwords($row_rsLocationPrefs['locationdescriptor']); ?> email:</td>
                  <td><span id="sprytextfield3">
                    <input name="locationemail" type="text"  id="locationemail" size="40" maxlength="50" value="<?php echo $row_rsLocation['locationemail']; ?>"  class="form-control"/>
                    <span class="textfieldInvalidFormatMsg">Invalid format.</span></span></td>
                </tr>
                <tr>
                  <td class="text-right">Web address:</td>
                  <td>
                    <input name="locationURL" type="text"  id="locationURL" value="<?php echo $row_rsLocation['locationURL']; ?>" size="40" maxlength="100"  class="form-control"/>
                   
                    <?php if(isset($row_rsLocation['locationURL'])) { ?>
                    <a href="javascript:void(0);" target="_blank" rel="noopener" onclick="this.href=document.getElementById('locationURL').value">Go there</a>
                    <?php } ?></td>
                </tr>
                <tr>
                  <td class="text-right" valign="top"><?php echo ucwords($row_rsLocationPrefs['locationdescriptor']); ?> Description: </td>
                  <td><textarea name="description" cols="40" rows="4" id="description" class="form-control"><?php echo $row_rsLocation['description']; ?></textarea></td>
                </tr> <tr>
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
                <tr class="defaultAddress">
                  <td class="text-nowrap text-right">Default:</td>
                  <td><input name="makedefault" type="checkbox" id="makedefault" value="1" <?php if ($_REQUEST['locationID'] == $row_rsDefaultAddress['defaultaddressID']) { ?>checked="checked"<?php } ?> /></td>
                </tr>
                <tr>
                  <td class="text-right">Active:</td>
                  <td><input <?php if (!(strcmp($row_rsLocation['active'],1))) {echo "checked";} ?> name="active" type="checkbox" id="active" value="1" /></td>
                </tr>
                <tr>
                  <td class="text-right">
                    <input name="modifiedbyID" type="hidden" id="modifiedbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
                    <input name="modifieddatetime" type="hidden" id="modifieddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
                   
                    <input name="ID" type="hidden" id="ID" value="<?php echo $row_rsLocation['ID']; ?>" />
                    Public:</td>
                  <td><input <?php if (!(strcmp($row_rsLocation['public'],1))) {echo "checked=\"checked\"";} ?> name="public" type="checkbox" id="public" value="1" /></td>
                </tr>
              </table>
              <input type="hidden" name="MM_update" value="add" />
            </div>
            <div class="TabbedPanelsContent">
            <fieldset class="form-inline">
              <p>Drag marker or find address:
                <input name="address" type="text"  id="address" value="<?php echo $row_rsLocation['address1']." ".$row_rsLocation['postcode'].", UK"; ?>" size="30" maxlength="100"  class="form-control"/>
                <input name="find" type="button" class="btn btn-default btn-secondary" id="find" onclick="findAddress();" value="Find" />
                <input name="Button" type="button" class="btn btn-default btn-secondary"onclick="clearMap();" value="Clear" />
                <span id="getLocation" class="locationServices">
                  <input type="button" onclick="getGeoLocation(); return false;" value="Get my location" class="btn btn-default btn-secondary" />
                </span>
                <input name="skip" type="submit" class="submit btn btn-primary" id="skip" value="Save location"/>
              </p></fieldset>
              <div class="googlemap" id="googlemap"></div>
              <input size="20" type="hidden" id="latitude" name="latitude" value="<?php echo $row_rsLocation['latitude']; ?>" />
              <input size="20" type="hidden" id="longitude" name="longitude" value="<?php echo $row_rsLocation['longitude']; ?>" />
              <label class="form-inline">or embed:
                <input name="mapURL" type="text"  id="mapURL" value="<?php echo $row_rsLocation['mapURL']; ?>" size="60" class="form-control" />
              </label>
            </div>
            <div class="TabbedPanelsContent">
              <?php if ($totalRows_rsUser == 0 && $totalRows_rsLocationUsers == 0) {  ?>
              <p>There are no people associated with this location.</p>
              <?php } else { ?>
              <h2>People associated with this location:</h2>
              <?php } ?>
              <?php if ($totalRows_rsUser > 0) { // Show if recordset not empty ?>
                <p>Address for: <a href="/members/admin/modify_user.php?userID=<?php echo $row_rsUser['userID']; ?>&amp;defaultTab=2&amp;returnURL=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>"><?php echo $row_rsUser['firstname']; ?> <?php echo $row_rsUser['surname']; ?></a>
                  <?php if(isset($row_rsUser['email'])) { ?>
                  <a href="mailto:<?php echo $row_rsUser['email']; ?>">(<?php echo $row_rsUser['email']; ?>)</a>
                  <?php } ?>
              </p>
              <?php } // Show if recordset not empty ?>
              <?php if ($totalRows_rsLocationUsers > 0) { // Show if recordset not empty ?>
              <table class="table table-hover">
              <thead>
                <tr>
                      <th>Main</th>
                      <th class="text-nowrap">Name</th>
                      <th class="text-nowrap">Email</th>
                      <th class="text-nowrap">&nbsp;</th>
                        <th class="text-nowrap">Delete</th></tr></thead><tbody>
                      <?php do { ?>                   
                  <tr>
                  <td><input name="userID" type="radio" value="<?php echo $row_rsLocationUsers['userID']; ?>" <?php if($row_rsLocationUsers['userID']==$row_rsLocation['userID']) { echo "checked=\"checked\""; } ?> /></td>
                    <td class="text-nowrap"><a href="/members/admin/modify_user.php?userID=<?php echo $row_rsLocationUsers['userID']; ?>&amp;defaultTab=2&amp;returnURL=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>"><?php echo $row_rsLocationUsers['firstname']; ?> <?php echo $row_rsLocationUsers['surname']; ?></a></td>
                    <td class="text-nowrap"><?php echo (isset($row_rsLocationUsers['email'])) ?
                      "<a href=\"mailto:".$row_rsLocationUsers['email']."\">(".$row_rsLocationUsers['email'].")</a>" : "&nbsp;";
                     ?>
                  &nbsp;
                  </td>
                    <td class="text-nowrap"><em><?php echo $row_rsLocationUsers['relationship']; ?></em></td>
                  
                  <td><a href="modify_location.php?locationID=<?php echo $row_rsLocation['ID']; ?>&amp;deletelocationuserID=<?php echo $row_rsLocationUsers['ID']; ?>&amp;defaultTab=2<?php echo (isset($_GET['returnURL']) && $_GET['returnURL']!="") ? "&returnURL=".urlencode($_GET['returnURL']) : ""; ?>" class="link_delete" onclick="return confirm('Are you sure you want to remove this user from this location?');"><i class="glyphicon glyphicon-trash"></i> Remove</a></td>
                  </tr>
                  <?php } while ($row_rsLocationUsers = mysql_fetch_assoc($rsLocationUsers)); ?></tbody>
              </table>
              <?php } // Show if recordset not empty ?>
              <p><span id="sprytextfield1">
                <label class="form-inline">Associate existing user:
                  <input name="adduser" type="text" id="adduser" size="50" maxlength="50" onkeyup="checkLiveInput(event,'adduserlist','adduser')" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');" class="form-control" />
                </label>
              </span></p>
              <div id="adduserlist"></div>
              <p><a href="/members/admin/add_user.php?locationID=<?php echo $row_rsLocation['ID']; ?>&amp;returnURL=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>">Add a new user and associate with this location</a></p>
            </div>
<div class="TabbedPanelsContent">
            <p><a href="../../mail/admin/communication/add_communication.php?locationID=<?php echo $row_rsLocation['ID']; echo isset($_GET['directoryID']) ? "&directoryID=".intval($_GET['directoryID']) : ""; ?>" class="link_add icon_with_text">Add note</a></p>
            <?php if ($totalRows_rsCommunication == 0) { // Show if recordset empty ?>
                <p>There are no notes related to this location at present.</p>
                <?php } // Show if recordset empty ?>
            <?php if ($totalRows_rsCommunication > 0) { // Show if recordset not empty ?>
                <table class="table table-hover">
                <thead>
                  <tr>
                    <th>&nbsp;</th>
                    <th>Time</th>
                    <th>Type</th>
                    <th>User</th>
                    <th>Client</th>
                    <th>Notes</th>
                    <th>Follow up</th>
                    <th>Edit</th>
                  </tr></thead><tbody>
                  <?php do { ?>
                    <tr>
                      <td class="status<?php echo $row_rsCommunication['statusID']; ?>">&nbsp;</td>
                      <td class="text-nowrap"><?php echo date('d M Y', strtotime($row_rsCommunication['thiscommdatetime'])); ?></td>
                      <td><?php echo $row_rsCommunication['typename']; ?></td>
                      <td class="text-nowrap "><?php echo $row_rsCommunication['username']; ?></td>
                      <td class="text-nowrap "><?php echo $row_rsCommunication['clientname']; ?></td>
                      <td><?php echo $row_rsCommunication['notes']; ?></td>
                      <td><?php echo date('d M Y', strtotime($row_rsCommunication['nextcommdatetime'])); ?></td>
                      <td><a href="../../mail/admin/communication/edit_communication.php?communicationID=<?php echo $row_rsCommunication['ID']; ?>" class="link_edit icon_only">Edit</a></td>
                    </tr>
                    <?php } while ($row_rsCommunication = mysql_fetch_assoc($rsCommunication)); ?></tbody>
                </table>
                <?php } // Show if recordset not empty ?>
            </div>
          </div>
        </div>
        <br class="clearfloat" />
        <button type="submit" class="btn btn-primary" >Save changes</button>
        <input name="returnURL" type="hidden" id="returnURL" value="<?php echo isset($_GET['returnURL']) ? htmlentities($_GET['returnURL']) : ""; ?>" />
      </form>
      <?php if (isset($_GET['defaultTab'])) { echo '<script>
<!--
var TabbedPanels1 = new Spry.Widget.TabbedPanels("TabbedPanels1", {defaultTab:'.intval($_GET['defaultTab']).'});
//-->
    </script>'; } else { ?>
      <script>
<!--
var TabbedPanels1 = new Spry.Widget.TabbedPanels("TabbedPanels1", {defaultTab:1});
//-->
    </script>
      <?php } ?>
      <script>
<!--
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1", "none", {isRequired:false, hint:"Enter surname of user"});
var sprytextfield3 = new Spry.Widget.ValidationTextField("sprytextfield3", "email", {isRequired:false});
//-->
</script></div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLocation);

mysql_free_result($rsPreferences);

mysql_free_result($rsCategories);

mysql_free_result($rsLoggedIn);

mysql_free_result($rsDefaultAddress);

mysql_free_result($rsUser);

mysql_free_result($rsLocationUsers);

mysql_free_result($rsCommunication);

mysql_free_result($rsLocationPrefs);

mysql_free_result($rsThisDirectory);
?>
