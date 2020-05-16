<?php require_once('../../Connections/aquiescedb.php'); ?><?php require_once('../../core/includes/adminAccess.inc.php'); ?>
<?php require_once('../../core/includes/upload.inc.php'); ?>
<?php require_once('../../mail/includes/sendmail.inc.php'); ?><?php require_once('../../core/includes/framework.inc.php'); ?><?php require_once('../../members/includes/userfunctions.inc.php'); ?>
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

if (isset($_POST["url"]) && $_POST["url"] !="") { // fix URL
$_POST['url'] = substr($_POST['url'],0,7) == "http://" ? $_POST['url'] : "http://".$_POST['url'];
}
if (isset($_POST["localweburl"]) && $_POST["localweburl"] !="") { // fix URL
$_POST['localweburl'] = substr($_POST['localweburl'],0,7) == "http://" ? $_POST['localweburl'] : "http://".$_POST['localweburl'];
}

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

$uploaded = getUploads();
if (isset($uploaded) && is_array($uploaded)) {
	if(isset($uploaded["filename"][0]["newname"]) && $uploaded["filename"][0]["newname"]!="") { 
		$_POST['imageURL'] = $uploaded["filename"][0]["newname"]; 
	}
	$_POST['imageURL'] = (isset($_POST["noimage"])) ? "" : $_POST['imageURL'];
}
if(isset($_POST['subcategoryID']) && $_POST['subcategoryID'] > 0) { $_POST['categoryID'] = $_POST['subcategoryID']; }


if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE directory SET directorytype=%s, isparent=%s, parentID=%s, userID=%s, name=%s, `description`=%s, address1=%s, address2=%s, address3=%s, address4=%s, address5=%s, postcode=%s, telephone=%s, fax=%s, mobile=%s, imageURL=%s, mapurl=%s, latitude=%s, longitude=%s, streetview=%s, email=%s, url=%s, statusID=%s, locationcategoryID=%s, modifiedbyID=%s, modifieddatetime=%s, bankname=%s, bankaccountname=%s, bankaddress=%s, bankpostcode=%s, banksortcode=%s, bankaccountnumber=%s, companynumber=%s, vatnumber=%s, charitynumber=%s, favourite=%s WHERE ID=%s",
                       GetSQLValueString($_POST['directorytype'], "int"),
                       GetSQLValueString(isset($_POST['isparent']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['parentID'], "int"),
                       GetSQLValueString($_POST['userID'], "int"),
                       GetSQLValueString($_POST['name'], "text"),
                       GetSQLValueString($_POST['description'], "text"),
                       GetSQLValueString($_POST['address1'], "text"),
                       GetSQLValueString($_POST['address2'], "text"),
                       GetSQLValueString($_POST['address3'], "text"),
                       GetSQLValueString($_POST['address4'], "text"),
                       GetSQLValueString($_POST['address5'], "text"),
                       GetSQLValueString($_POST['postcode'], "text"),
                       GetSQLValueString($_POST['telephone'], "text"),
                       GetSQLValueString($_POST['fax'], "text"),
                       GetSQLValueString($_POST['mobile'], "text"),
                       GetSQLValueString($_POST['imageURL'], "text"),
                       GetSQLValueString($_POST['mapurl'], "text"),
                       GetSQLValueString($_POST['latitude'], "double"),
                       GetSQLValueString($_POST['longitude'], "double"),
                       GetSQLValueString(isset($_POST['streetview']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['email'], "text"),
                       GetSQLValueString($_POST['url'], "text"),
                       GetSQLValueString($_POST['statusID'], "int"),
                       GetSQLValueString($_POST['locationcategoryID'], "int"),
                       GetSQLValueString($_POST['modifiedbyID'], "int"),
                       GetSQLValueString($_POST['modifieddatetime'], "date"),
                       GetSQLValueString($_POST['bankname'], "text"),
                       GetSQLValueString($_POST['bankaccountname'], "text"),
                       GetSQLValueString($_POST['bankaddress'], "text"),
                       GetSQLValueString($_POST['bankpostcode'], "text"),
                       GetSQLValueString($_POST['banksortcode'], "text"),
                       GetSQLValueString($_POST['bankaccount'], "text"),
                       GetSQLValueString($_POST['companynumber'], "text"),
                       GetSQLValueString($_POST['vatnumber'], "text"),
                       GetSQLValueString($_POST['charitynumber'], "text"),
                       GetSQLValueString(isset($_POST['favourite']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}

if ((isset($_POST["notes"])) && trim($_POST["notes"])!="") {
  $insertSQL = sprintf("INSERT INTO communication (commtypeID, userID, directoryID, notes, thiscommdatetime, createdbyID, createddatetime, modifiedbyID, modifieddatetime, commcatID) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['commtypeID'], "int"),
                       GetSQLValueString($_POST['createdbyID'], "int"),
                       GetSQLValueString($_POST['directoryID'], "int"),
                       GetSQLValueString($_POST['notes'], "text"),
                       GetSQLValueString($_POST['thiscommdatetime'], "date"),
                       GetSQLValueString($_POST['createdbyID'], "int"),
                       GetSQLValueString($_POST['createddatetime'], "date"),
                       GetSQLValueString($_POST['modifiedbyID'], "int"),
                       GetSQLValueString($_POST['modifieddatetime'], "date"),
                       GetSQLValueString($_POST['commcatID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($insertSQL, $aquiescedb) or die(mysql_error());
  
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
	
	$delete = "DELETE FROM directoryinarea WHERE directoryID = ". GetSQLValueString($_POST['ID'], "int");
	mysql_query($delete, $aquiescedb) or die(mysql_error());
	
	if(isset($_POST['directoryarea'])) {
		 foreach($_POST['directoryarea'] as $value) {
	$insert = "INSERT INTO directoryinarea (directoryID, directoryareaID, createdbyID, createddatetime) VALUES (". GetSQLValueString($_POST['ID'], "int").",".$value.",".GetSQLValueString($_POST['modifiedbyID'], "int").",NOW())";
	$result = mysql_query($insert, $aquiescedb) or die(mysql_error());
		 }
}
	if(isset($_POST['informuser']) && strpos($_POST['useremail'],"@")>0) {
		$to = $_POST['useremail'];
		$subject = "Your ".$site_name." directory details have been updated";
		$message = "This is an automated message to inform you that the details for the following entry have been updated:\n\n";
		$message .= $_POST['name']."\n\n";
		$message .= "Click on the link below to view your entry:\n\n";
		$message .= (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == "on") ? "https://" : "http://";
		$message .= $_SERVER['HTTP_HOST']."/directory/members/";
		sendMail($to,$subject,$message);
	}

  $updateGoTo = (isset($_REQUEST['returnURL']) && $_REQUEST['returnURL'] !="") ? $_REQUEST['returnURL'] : "/directory/admin/index.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  $updateGoTo = removeQueryVarFromURL($updateGoTo,"returnURL");
  header(sprintf("Location: %s", $updateGoTo));exit();
}

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsStatus = "SELECT * FROM status ORDER BY ID ASC";
$rsStatus = mysql_query($query_rsStatus, $aquiescedb) or die(mysql_error());
$row_rsStatus = mysql_fetch_assoc($rsStatus);
$totalRows_rsStatus = mysql_num_rows($rsStatus);

$varDirectoryID_rsDirectory = "-1";
if (isset($_GET['directoryID'])) {
  $varDirectoryID_rsDirectory = $_GET['directoryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsDirectory = sprintf("SELECT directory.*, users.firstname, users.surname, users.email AS useremail, directorycategory.subCatOfID, directorycategory.regionID FROM directory LEFT JOIN users ON (directory.createdbyID = users.ID) LEFT JOIN directorycategory ON (directory.categoryID = directorycategory.ID) WHERE directory.ID = %s  ", GetSQLValueString($varDirectoryID_rsDirectory, "int"));
$rsDirectory = mysql_query($query_rsDirectory, $aquiescedb) or die(mysql_error());
$row_rsDirectory = mysql_fetch_assoc($rsDirectory);
$totalRows_rsDirectory = mysql_num_rows($rsDirectory);

$varDirectoryRegion_rsParentCategories = "0";
if (isset($row_rsDirectory['regionID'])) {
  $varDirectoryRegion_rsParentCategories = $row_rsDirectory['regionID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsParentCategories = sprintf("SELECT ID, description FROM directorycategory WHERE directorycategory.subcatofID = 0 AND directorycategory.statusID  =1 AND (directorycategory.regionID = %s OR  directorycategory.regionID =  0 OR %s = 0) ORDER BY description ASC", GetSQLValueString($varDirectoryRegion_rsParentCategories, "int"),GetSQLValueString($varDirectoryRegion_rsParentCategories, "int"));
$rsParentCategories = mysql_query($query_rsParentCategories, $aquiescedb) or die(mysql_error());
$row_rsParentCategories = mysql_fetch_assoc($rsParentCategories);
$totalRows_rsParentCategories = mysql_num_rows($rsParentCategories);

$varOrgID_rsLastModified = "-1";
if (isset($_GET['directoryID'])) {
  $varOrgID_rsLastModified = (get_magic_quotes_gpc()) ? $_GET['directoryID'] : addslashes($_GET['directoryID']);
}

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLastModified = sprintf("SELECT directory.modifieddatetime, users.firstname, users.surname FROM directory LEFT JOIN users ON (directory.modifiedbyID = users.ID)  WHERE directory.ID = '%s'", $varOrgID_rsLastModified);
$rsLastModified = mysql_query($query_rsLastModified, $aquiescedb) or die(mysql_error());
$row_rsLastModified = mysql_fetch_assoc($rsLastModified);
$totalRows_rsLastModified = mysql_num_rows($rsLastModified);

$varDirectoryID_rsDirectoryUser = "-1";
if (isset($_GET['directoryID'])) {
  $varDirectoryID_rsDirectoryUser = $_GET['directoryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsDirectoryUser = sprintf("SELECT users.ID, users.firstname, users.surname, users.jobtitle, directoryuser.ID AS directoryuserID, directoryrelationship.relationshipname FROM users, directoryuser LEFT JOIN directoryrelationship ON (directoryuser.relationshiptype = directoryrelationship.ID), usertype  WHERE users.ID = directoryuser.userID AND  directoryuser.relationshiptype = usertype.ID AND directoryuser.directoryID = %s AND (directoryuser.enddate IS NULL OR directoryuser.enddate > CURDATE()) AND directoryuser.relationshiptype >=0", GetSQLValueString($varDirectoryID_rsDirectoryUser, "int"));
$rsDirectoryUser = mysql_query($query_rsDirectoryUser, $aquiescedb) or die(mysql_error());
$row_rsDirectoryUser = mysql_fetch_assoc($rsDirectoryUser);
$totalRows_rsDirectoryUser = mysql_num_rows($rsDirectoryUser);

$varDirectoryID_rsProjects = "-1";
if (isset($_GET['directoryID'])) {
  $varDirectoryID_rsProjects = $_GET['directoryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsProjects = sprintf("SELECT projects.ID, projects.title, projects.createddatetime,projects.invoicenumber, price,invoicedate, paidbydate FROM projects WHERE projects.directoryID = %s ORDER BY projects.createddatetime DESC", GetSQLValueString($varDirectoryID_rsProjects, "int"));
$rsProjects = mysql_query($query_rsProjects, $aquiescedb);
$row_rsProjects = mysql_fetch_assoc($rsProjects);
$totalRows_rsProjects = mysql_num_rows($rsProjects);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT * FROM preferences";
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);

$varRegionID_rsLocationCategory = "0";
if (isset($regionID)) {
  $varRegionID_rsLocationCategory = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLocationCategory = sprintf("SELECT locationcategory.ID, locationcategory.categoryname FROM locationcategory WHERE locationcategory.statusID = 1 AND (locationcategory.regionID = %s OR %s = 0)", GetSQLValueString($varRegionID_rsLocationCategory, "int"),GetSQLValueString($varRegionID_rsLocationCategory, "int"));
$rsLocationCategory = mysql_query($query_rsLocationCategory, $aquiescedb) or die(mysql_error());
$row_rsLocationCategory = mysql_fetch_assoc($rsLocationCategory);
$totalRows_rsLocationCategory = mysql_num_rows($rsLocationCategory);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsRegions = "SELECT region.ID, region.title FROM region WHERE region.statusID = 1 ";
$rsRegions = mysql_query($query_rsRegions, $aquiescedb) or die(mysql_error());
$row_rsRegions = mysql_fetch_assoc($rsRegions);
$totalRows_rsRegions = mysql_num_rows($rsRegions);

$varDirectoryID_rsLocations = "-1";
if (isset($_GET['directoryID'])) {
  $varDirectoryID_rsLocations = $_GET['directoryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLocations = sprintf("SELECT location.ID, location.address1, location.address2, location.address3, location.address4, location.address5, location.postcode, location.telephone1, location.telephone2, location.telephone3, location.fax, location.locationname FROM location LEFT JOIN directorylocation ON (directorylocation.locationID = location.ID) WHERE directorylocation.directoryID = %s", GetSQLValueString($varDirectoryID_rsLocations, "int"));
$rsLocations = mysql_query($query_rsLocations, $aquiescedb) or die(mysql_error());
$row_rsLocations = mysql_fetch_assoc($rsLocations);
$totalRows_rsLocations = mysql_num_rows($rsLocations);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsDirectoryPrefs = "SELECT * FROM directoryprefs";
$rsDirectoryPrefs = mysql_query($query_rsDirectoryPrefs, $aquiescedb) or die(mysql_error());
$row_rsDirectoryPrefs = mysql_fetch_assoc($rsDirectoryPrefs);
$totalRows_rsDirectoryPrefs = mysql_num_rows($rsDirectoryPrefs);

$colname_rsThisLocation = "-1";
if (isset($_GET['locationID'])) {
  $colname_rsThisLocation = $_GET['locationID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisLocation = sprintf("SELECT locationname FROM location WHERE ID = %s", GetSQLValueString($colname_rsThisLocation, "int"));
$rsThisLocation = mysql_query($query_rsThisLocation, $aquiescedb) or die(mysql_error());
$row_rsThisLocation = mysql_fetch_assoc($rsThisLocation);
$totalRows_rsThisLocation = mysql_num_rows($rsThisLocation);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsParents = "SELECT ID, name FROM directory WHERE statusID = 1 AND directory.isparent = 1 ORDER BY name ASC";
$rsParents = mysql_query($query_rsParents, $aquiescedb) or die(mysql_error());
$row_rsParents = mysql_fetch_assoc($rsParents);
$totalRows_rsParents = mysql_num_rows($rsParents);

$colname_rsChildren = "-1";
if (isset($_GET['directoryID'])) {
  $colname_rsChildren = $_GET['directoryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsChildren = sprintf("SELECT ID, name FROM directory WHERE parentID = %s AND statusID = 1 ORDER BY name ASC", GetSQLValueString($colname_rsChildren, "int"));
$rsChildren = mysql_query($query_rsChildren, $aquiescedb) or die(mysql_error());
$row_rsChildren = mysql_fetch_assoc($rsChildren);
$totalRows_rsChildren = mysql_num_rows($rsChildren);

$varDirectoryID_rsDirectoryAreas = "-1";
if (isset($_GET['directoryID'])) {
  $varDirectoryID_rsDirectoryAreas = $_GET['directoryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsDirectoryAreas = sprintf("SELECT directoryarea.ID, directoryarea.areaname, directoryinarea.directoryareaID FROM directoryarea LEFT JOIN directoryinarea ON (directoryarea.ID = directoryinarea.directoryareaID AND directoryinarea.directoryID = %s) WHERE directoryarea.statusID ORDER BY directoryarea.areaname", GetSQLValueString($varDirectoryID_rsDirectoryAreas, "int"));
$rsDirectoryAreas = mysql_query($query_rsDirectoryAreas, $aquiescedb) or die(mysql_error());
$row_rsDirectoryAreas = mysql_fetch_assoc($rsDirectoryAreas);
$totalRows_rsDirectoryAreas = mysql_num_rows($rsDirectoryAreas);

$varDirectoryID_rsNotes = "-1";
if (isset($_GET['directoryID'])) {
  $varDirectoryID_rsNotes = $_GET['directoryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsNotes = sprintf("SELECT communication.ID, communication.notes, communication.thiscommdatetime, users.firstname, users.surname FROM communication LEFT JOIN users ON communication.userID = users.ID WHERE communication.commcatID = 1 AND communication.directoryID = %s ORDER BY communication.thiscommdatetime DESC", GetSQLValueString($varDirectoryID_rsNotes, "int"));
$rsNotes = mysql_query($query_rsNotes, $aquiescedb) or die(mysql_error());
$row_rsNotes = mysql_fetch_assoc($rsNotes);
$totalRows_rsNotes = mysql_num_rows($rsNotes);

if(isset($_GET['deletelocationID'])) { // delete location
	$delete = "DELETE FROM directorylocation WHERE directoryID = ".GetSQLValueString($_GET['directoryID'], "int")." AND locationID = ".GetSQLValueString($_GET['deletelocationID'], "int");
	$result = mysql_query($delete, $aquiescedb) or die(mysql_error());
	header("location: update_directory.php?defaultTab=1&directoryID=".GetSQLValueString($_GET['directoryID'], "int")); exit;
} // end delete location


?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Update Organisation: ".$row_rsDirectory['name']; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" --><script src="../../core/scripts/formUpload.js"></script>
<?php 
// set initial parameters for select menus
$parentID = ($row_rsDirectory['subCatOfID']>0) ? $row_rsDirectory['subCatOfID'] : $row_rsDirectory['categoryID'];
$categoryID = ($row_rsDirectory['subCatOfID']>0) ? $row_rsDirectory['categoryID'] : 0;
?><style><!--
<?php if ($totalRows_rsDirectoryAreas < 1) { echo ".directoryareas { display:none; }"; }  ?>
<?php if (!isset($allowlocalwebpage) || $allowlocalwebpage == false) {  echo ".localweb { display:none; } ";
 } ?>
 <?php if ($totalRows_rsParents==0) {  echo ".hasparent { display:none; } ";
 } ?>
 -->
 </style>
<?php if ($totalRows_rsLocationCategory < 1) { // not using location categories so hide
echo "<style>.locationCategory {display: none;} </style>" ; }
if ($row_rsPreferences['useregions'] != 1) { // not using regions so hide
echo "<style>.region {display: none;} </style>"; 
}
?>
<script src="/SpryAssets/SpryTabbedPanels.js"></script>
<link href="/SpryAssets/SpryTabbedPanels.css" rel="stylesheet"  />
<?php $googlemapsAPI =isset($googlemapsAPI) ? $googlemapsAPI : $row_rsPreferences['googlemapsAPI'];
if($googlemapsAPI!="") { ?>
<script src="/location/scripts/location.js"></script>
<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo $googlemapsAPI; ?>&v=3" ></script>
<script>
var initLatitude = <?php echo isset($row_rsDirectory['latitude']) ? $row_rsDirectory['latitude']: 0; ?>;
var initLongitude =<?php echo isset($row_rsDirectory['longitude']) ? $row_rsDirectory['longitude']: 0; ?>;
var initZoom = <?php echo isset($row_rsDirectory['latitude']) ? 16 : 2; ?> ;
var markerLatitude = <?php echo isset($row_rsDirectory['latitude']) ? $row_rsDirectory['latitude']: "null"; ?>;
var markerLongitude = <?php echo isset($row_rsDirectory['longitude']) ? $row_rsDirectory['longitude']: "null"; ?>;
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
	if(markerLatitude) { 
		markerpos = new google.maps.LatLng(markerLatitude, markerLongitude);
		marker.setPosition(markerpos);			
		google.maps.event.addListener(marker, 'dragend', function (event) {    		
			map.panTo(this.getPosition());
			document.getElementById("latitude").value = this.getPosition().lat();
    		document.getElementById("longitude").value = this.getPosition().lng();
		});	
	} else { // if we don't have a marker position
		findAddress();
	}
}

//]]>
</script>
<?php } 
else { echo "<style> #tabMaps { display:none; } </style>" ; } ?>
<script>
function init() {

getData("/directory/ajax/directoryCategories.inc.php?directoryID="+document.getElementById('ID').value,"directoryCategories");
}

addListener("load",init);

function addToCategory(cat, subcat) {
	if(parseInt(subcat)>0) {
		cat = subcat;
	}
	if(parseInt(cat)>0) {
		getData("/directory/ajax/directoryCategories.inc.php?directoryID="+document.getElementById('ID').value+"&addToCategory="+parseInt(cat)+"&userID="+document.getElementById('modifiedbyID').value,"directoryCategories");
	}
}

function deleteFromCategory(cat) {
	if(parseInt(cat)>0) {
		getData("/directory/ajax/directoryCategories.inc.php?directoryID="+document.getElementById('ID').value+"&deleteFromCategory="+parseInt(cat)+"&userID="+document.getElementById('modifiedbyID').value,"directoryCategories");
	}
}

//]]>
</script>
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
        <div class="page directory">
    <h1><i class="glyphicon glyphicon-book"></i> Update <?php echo $row_rsDirectory['name']; echo isset($row_rsThisLocation['locationname']) ? " (".$row_rsThisLocation['locationname'].")" : ""; ?></h1>
    <?php if(isset($submit_error)) { ?>
    <p class="alert alert-danger" role="alert"><?php echo $submit_error; ?></p>
    <?php } ?>
    <form action="<?php echo $editFormAction; ?>" method="post" enctype="multipart/form-data" name="form1" id="form1"  >
      <div id="TabbedPanels1" class="TabbedPanels">
        <ul class="TabbedPanelsTabGroup">
          <li class="TabbedPanelsTab" tabindex="0">Details</li>
          <li class="TabbedPanelsTab" tabindex="0">Category</li>
          <li class="TabbedPanelsTab" tabindex="0">Locations</li>
          <li class="TabbedPanelsTab" tabindex="0">Map</li>
          <li class="TabbedPanelsTab" tabindex="0">Contacts</li>
          <li class="TabbedPanelsTab" tabindex="0" <?php if(!defined("PROJECTS_MODULE")) { ?>style="display:none;"<?php } ?>>Projects</li>
          <li class="TabbedPanelsTab" tabindex="0">Financial</li>
          <li class="TabbedPanelsTab" tabindex="0">Notes</li>
        </ul>
        <div class="TabbedPanelsContentGroup">
          <div class="TabbedPanelsContent">
            <table class="form-table">
              <tr  class="region">
                <td class="text-nowrap text-right">Site:</td>
                <td><select name="regionID" id="regionID"   onchange="getData('/directory/ajax/createParentCats.php?regionID='+this.value+'','parentCat'); getData('/directory/ajax/createSubCatSelect.php?parentCatID='+this.value+'&amp;categoryID=<?php echo $row_rsDirectory['categoryID']; ?>','subCat');" class="form-control">
                  <option value="0" <?php if (!(strcmp(0, $row_rsDirectory['regionID']))) {echo "selected=\"selected\"";} ?>><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
                  <option value="0" <?php if (!(strcmp(0, $row_rsDirectory['regionID']))) {echo "selected=\"selected\"";} ?>>All</option>
                  <?php
do {  
?>
                  <option value="<?php echo $row_rsRegions['ID']?>"<?php if (!(strcmp($row_rsRegions['ID'], $row_rsDirectory['regionID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsRegions['title']?></option>
                  <?php
} while ($row_rsRegions = mysql_fetch_assoc($rsRegions));
  $rows = mysql_num_rows($rsRegions);
  if($rows > 0) {
      mysql_data_seek($rsRegions, 0);
	  $row_rsRegions = mysql_fetch_assoc($rsRegions);
  }
?>
                </select></td>
              </tr>
              <tr class="locationCategory">
                <td class="text-nowrap text-right">Location Category:</td>
                <td><select name="locationcategoryID" id="locationcategoryID" class="form-control">
                  <option value="0" <?php if (!(strcmp(0, $row_rsDirectory['locationcategoryID']))) {echo "selected=\"selected\"";} ?>><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
                  <?php
do {  
?>
                  <option value="<?php echo $row_rsLocationCategory['ID']?>"<?php if (!(strcmp($row_rsLocationCategory['ID'], $row_rsDirectory['locationcategoryID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsLocationCategory['categoryname']?></option>
                  <?php
} while ($row_rsLocationCategory = mysql_fetch_assoc($rsLocationCategory));
  $rows = mysql_num_rows($rsLocationCategory);
  if($rows > 0) {
      mysql_data_seek($rsLocationCategory, 0);
	  $row_rsLocationCategory = mysql_fetch_assoc($rsLocationCategory);
  }
?>
                </select></td>
              </tr>
                  
              <tr class="directoryareas">
                <td class="text-nowrap text-right top">Areas covered:</td>
                <td><?php do { ?>
                  <label>
                    <input name="directoryarea[<?php echo $row_rsDirectoryAreas['ID']; ?>]" type="checkbox" value="<?php echo $row_rsDirectoryAreas['ID']; ?>" <?php if($row_rsDirectoryAreas['directoryareaID']) { echo "checked=\"checked\""; } ?> />
                    <?php echo $row_rsDirectoryAreas['areaname']; ?></label>
                  &nbsp;&nbsp;&nbsp;
                  <?php } while ($row_rsDirectoryAreas = mysql_fetch_assoc($rsDirectoryAreas)); ?></td>
              </tr> <tr>
                <td class="text-nowrap text-right">Name:</td>
                <td><input type="text"  name="name" value="<?php echo $row_rsDirectory['name']; ?>" class="form-control" maxlength="255" /></td>
              </tr> <tr>
                <td class="text-nowrap text-right top">Description:</td>
                <td><textarea name="description" cols="50" rows="5" class="form-control" ><?php echo $row_rsDirectory['description']; ?></textarea></td>
              </tr> <tr>
                <td class="text-nowrap text-right">Image:</td>
                <td><?php if (isset($row_rsDirectory['imageURL']) && is_readable(SITE_ROOT.getImageURL($row_rsDirectory['imageURL'],"medium"))) { ?>
                  <img src="<?php echo getImageURL($row_rsDirectory['imageURL'],"medium"); ?>" /><br />
                  <input name="noImage" type="checkbox" value="1" />
                  <?php } else { ?>
                  No image associated with this organisation.
                  <?php } ?>
                  <span class="upload"><br />
                    Add/change image below:<br />
                    <input name="filename" type="file" id="filename" size="20" />
                  </span></td>
              </tr> <tr>
                <td class="text-nowrap text-right">External web site :</td>
                <td><input name="url" type="text"  value="<?php echo $row_rsDirectory['url']; ?>" class="form-control" maxlength="100" /></td>
              </tr>
              <tr class="localweb">
                <td class="text-nowrap text-right">Local web page:</td>
                <td class="form-inline"><input <?php if (!(strcmp($row_rsDirectory['localwebpage'],1))) {echo "checked=\"checked\"";} ?> name="localwebpage" type="checkbox" id="localwebpage" value="." />
                  <label for="localwebpage"></label>
                  <label for="localweburl">Address (if any):</label>
                  <input name="localweburl" type="text"  id="localweburl" value="<?php echo $row_rsDirectory['localweburl']; ?>" size="30" maxlength="50" class="form-control"/></td>
              </tr> <tr>
                <td class="text-nowrap text-right">Map URL: </td>
                <td><input name="mapurl" type="text"  id="mapurl" value="<?php echo $row_rsDirectory['mapurl']; ?>" class="form-control" maxlength="100" /></td>
              </tr> <tr>
                <td class="text-nowrap text-right">Status:</td>
                <td class="form-inline"><select name="statusID" class="form-control">
                  <?php 
do {  
?>
                  <option value="<?php echo $row_rsStatus['ID']?>" <?php if (!(strcmp($row_rsStatus['ID'], $row_rsDirectory['statusID']))) {echo "SELECTED";} ?>><?php echo $row_rsStatus['description']?></option>
                  <?php
} while ($row_rsStatus = mysql_fetch_assoc($rsStatus));
?>
                </select>
                  <label>
                    <input <?php if (!(strcmp($row_rsDirectory['favourite'],1))) {echo "checked=\"checked\"";} ?> name="favourite" type="checkbox" id="favourite" value="1">
                    Favourite</label></td>
              </tr>
            </table>
            <input type="hidden" name="ID" id="ID" value="<?php echo $row_rsDirectory['ID']; ?>" />
            <input type="hidden" name="modifiedbyID" id="modifiedbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
            <input type="hidden" name="modifieddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
            <input type="hidden" name="MM_update" value="form1" />
            <input name="referer" type="hidden" id="referer" value="<?php echo $_SERVER['HTTP_REFERER']; ?>" />
            <input name="imageURL" type="hidden" value="<?php echo $row_rsDirectory['imageURL']; ?>" />
          </div>
          <div class="TabbedPanelsContent">
            <h3>Categories:</h3>
            <div id="directoryCategories"></div>
            <fieldset>
            <legend>Add to category:</legend>
            <table border="0" cellpadding="2" cellspacing="0" class="form-table"> <tr>
                <td class="text-nowrap text-right" >Choose Category:</td>
                <td id="parentCat"><select name="categoryID" class="form-control" id="categoryID" onChange="getData('/directory/ajax/createSubCatSelect.php?parentCatID='+this.value+'&amp;categoryID=<?php echo $row_rsDirectory['categoryID']; ?>','subCat')">
                  <?php 
do {  
?>
                  <option value="<?php echo $row_rsParentCategories['ID']?>" ><?php echo $row_rsParentCategories['description']?></option>
                  <?php
} while ($row_rsParentCategories = mysql_fetch_assoc($rsParentCategories));
?>
                </select></td>
                <td id="subCat"><select name="subcategoryID" id="subcategoryID" class="form-control" >
                  <option value="0">None</option>
                </select></td>
                <td><a href="javascript:void(0);" class="link_add icon_with_text" onClick="addToCategory(document.getElementById('categoryID').value,document.getElementById('subcategoryID').value); return false;">Add</a></td>
              </tr>
            </table></fieldset>
          </div>
          <div class="TabbedPanelsContent">
            <h3>Main Location:</h3>
            <table class="form-table"> <tr>
                <td class="text-nowrap text-right top">Address:</td>
                <td><input name="address1" type="text"  id="address1" value="<?php echo $row_rsDirectory['address1']; ?>" size="50" maxlength="50" class="form-control"  /></td>
              </tr> <tr>
                <td class="text-nowrap text-right top">&nbsp;</td>
                <td><input name="address2" type="text"  id="address2" value="<?php echo $row_rsDirectory['address2']; ?>" size="50" maxlength="50" class="form-control"  /></td>
              </tr> <tr>
                <td class="text-nowrap text-right top">&nbsp;</td>
                <td><input name="address3" type="text"  id="address3" value="<?php echo $row_rsDirectory['address3']; ?>" size="50" maxlength="50"  class="form-control" /></td>
              </tr> <tr>
                <td class="text-nowrap text-right top">&nbsp;</td>
                <td><input name="address4" type="text"  id="address4" value="<?php echo $row_rsDirectory['address4']; ?>" size="50" maxlength="50"  class="form-control" /></td>
              </tr> <tr>
                <td class="text-nowrap text-right top">&nbsp;</td>
                <td><input name="address5" type="text"  id="address5" value="<?php echo $row_rsDirectory['address5']; ?>" size="50" maxlength="50"  class="form-control" /></td>
              </tr> <tr>
                <td class="text-nowrap text-right">Postcode:</td>
                <td><input name="postcode" id = "postcode" type="text"  value="<?php echo $row_rsDirectory['postcode']; ?>" size="32" maxlength="10"  class="form-control" /></td>
              </tr> <tr>
                <td class="text-nowrap text-right">Telephone:</td>
                <td><input name="telephone" type="text"  id="telephone" value="<?php echo $row_rsDirectory['telephone']; ?>" size="32" maxlength="20" class="form-control"  /></td>
              </tr> <tr>
                <td class="text-nowrap text-right">Mobile:</td>
                <td><input name="mobile" type="text"  id="mobile" value="<?php echo $row_rsDirectory['mobile']; ?>" size="32" maxlength="20"  class="form-control" /></td>
              </tr> <tr>
                <td class="text-nowrap text-right">Fax:</td>
                <td><input name="fax" type="text"  id="fax" value="<?php echo $row_rsDirectory['fax']; ?>" maxlength="20"  class="form-control" /></td>
              </tr> <tr>
                <td class="text-nowrap text-right">Email:</td>
                <td><input name="email" type="email" multiple value="<?php echo $row_rsDirectory['email']; ?>"  placeholder="(recommended accounts email)" maxlength="100" class="form-control"  /></td>
              </tr>
            </table>
            <p><a href="../../location/admin/add_location.php?directoryID=<?php echo $row_rsDirectory['ID']; ?>" class="link_add icon_with_text">Add new location</a></p>
            <?php if ($totalRows_rsLocations > 0) { // Show if recordset not empty ?>
            <?php do { ?>
            <div class="location">
              <div class="locationname clearfix"><span class="fltlft"><?php echo $row_rsLocations['locationname']; ?><?php if($row_rsLocations['ID'] == $row_rsDirectory['registeredaddressID']) echo " (Registered address)"; ?></span><a href="../../location/admin/modify_location.php?locationID=<?php echo $row_rsLocations['ID']; ?>" class="fltlft link_edit">Edit</a><a href="update_directory.php?deletelocationID=<?php echo $row_rsLocations['ID']; ?>&amp;directoryID=<?php echo $row_rsDirectory['ID']; ?>" class="fltlft link_delete">Delete</a></div>
              <?php echo (isset($row_rsLocations['address1']) && $row_rsLocations['address1']!="") ? $row_rsLocations['address1']."<br />" : ""; ?> <?php echo (isset($row_rsLocations['address2']) && $row_rsLocations['address2']!="") ? $row_rsLocations['address2']."<br />" : ""; ?> <?php echo (isset($row_rsLocations['address3']) && $row_rsLocations['address3']!="") ? $row_rsLocations['address3']."<br />" : ""; ?> <?php echo (isset($row_rsLocations['address4']) && $row_rsLocations['address4']!="") ? $row_rsLocations['address4']."<br />" : ""; ?> <?php echo (isset($row_rsLocations['address5']) && $row_rsLocations['address5']!="") ? $row_rsLocations['address5']."<br />" : ""; ?> <?php echo (isset($row_rsLocations['postcode']) && $row_rsLocations['postcode']!="") ? $row_rsLocations['postcode']."<br />" : ""; ?> <?php echo (isset($row_rsLocations['telephone1']) && $row_rsLocations['telephone1']!="") ? "Telephone: ".$row_rsLocations['telephone1']."<br />" : ""; ?> <?php echo (isset($row_rsLocations['telephone2']) && $row_rsLocations['telephone2']!="") ? "Telephone: ".$row_rsLocations['telephone2']."<br />" : ""; ?> <?php echo (isset($row_rsLocations['fax']) && $row_rsLocations['fax']!="") ? "Fax: ".$row_rsLocations['fax']."<br />" : ""; ?> <br />
            </div>
            <?php } while ($row_rsLocations = mysql_fetch_assoc($rsLocations)); ?>
            <?php } // Show if recordset not empty ?>
          </div>
          <div class="TabbedPanelsContent" >
            <p class="form-inline">Drag marker or find address:
              <input name="address" type="text"  id="address" value="<?php echo $row_rsDirectory['address1']." ".$row_rsDirectory['postcode'].", UK"; ?>" size="30" maxlength="100"  class="form-control" />
                <button name="find" type="button" class="btn btn-default btn-secondary" id="find" onclick="findAddress();" >Find</button>
              <button name="Button" type="button" class="btn btn-default btn-secondary" onClick="clearMap();" >Clear</button>
              <button name="skip" type="submit" class="btn btn-primary" id="skip" >Save location</button>
            </p>
            <div class="googlemap" id="googlemap"></div>
            <label>
              <input <?php if (!(strcmp($row_rsDirectory['streetview'],1))) {echo "checked=\"checked\"";} ?> name="streetview" type="checkbox" id="streetview" value="1" />
              Show Street View (if Street View is enabled)</label>
            <input size="20" type="hidden" id="latitude" name="latitude" value="<?php echo $row_rsDirectory['latitude']; ?>" />
            <input size="20" type="hidden" id="longitude" name="longitude" value="<?php echo $row_rsDirectory['longitude']; ?>" />
            <label><br />
              or embed:
              <input name="mapURL" type="text"  id="mapURL" value="<?php echo $row_rsDirectory['mapurl']; ?>" size="60"  class="form-control" />
            </label>
          </div>
          <div class="TabbedPanelsContent">
            
            <?php if ($totalRows_rsDirectoryUser == 0) { // Show if recordset empty ?>
            <p>There are no other users associated with <?php echo $row_rsDirectory['name']; ?>.</p>
            <?php } // Show if recordset empty ?>
            <p><a href="associate_user.php?directoryID=<?php echo intval($_GET['directoryID']); ?>">Add an <strong>existing</strong> user as a contact of <?php echo $row_rsDirectory['name']; ?></a></p>
            <p><a href="/members/admin/add_user.php?directoryID=<?php echo $row_rsDirectory['ID']; ?>&amp;returnURL=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>">Add a <strong>new</strong> user as a contact of <?php echo $row_rsDirectory['name']; ?></a></p>
            <?php if ($totalRows_rsDirectoryUser > 0) { // Show if recordset not empty ?>
            <table class="table table-hover">
            <thead>
             <tr>
                    <th>Main</th>
                    <th>Name</th>
                    <th>Role</th>
                    <th>Delete</th>
                  </tr></thead><tbody> <?php do { ?>
                  
                  <tr><td><input name="userID" type="radio" value="<?php echo $row_rsDirectoryUser['ID']; ?>" <?php if($row_rsDirectoryUser['ID']== $row_rsDirectory['userID']) { echo "checked=\"checked\""; } ?>></td>
                <td><a href="../../members/admin/modify_user.php?userID=<?php echo $row_rsDirectoryUser['ID']; ?>"><?php echo $row_rsDirectoryUser['firstname']." ".$row_rsDirectoryUser['surname']; ?></a></td>
                <td><em><?php echo $row_rsDirectoryUser['jobtitle']; echo isset($row_rsDirectoryUser['relationshipname']) ? "&nbsp;(".$row_rsDirectoryUser['relationshipname'].")" : ""; ?></em></td>
                <td><a href="delete_directory_user.php?directoryID=<?php echo $row_rsDirectory['ID']; ?>&amp;directoryuserID=<?php echo $row_rsDirectoryUser['directoryuserID']; ?>" onClick="document.returnValue = confirm('Are you sure you want to delete this user’s association with this organisation?'); return document.returnValue;" class="link_delete"><i class="glyphicon glyphicon-trash"></i> Delete</a></td>
              </tr>
              <?php } while ($row_rsDirectoryUser = mysql_fetch_assoc($rsDirectoryUser)); ?></tbody>
            </table>
            <?php } else { ?><input name="userID" type="hidden" value="">
            <?php } ?>
          </div>
          <div class="TabbedPanelsContent">
          <a href="../../projects/admin/add_project.php?directoryID=<?php echo intval($_GET['directoryID']); ?>" class="btn btn-default btn-secondary"><i class="glyphicon glyphicon-plus-sign"></i> New Project</a>
            <?php if ($totalRows_rsProjects > 0) { // Show if recordset not empty ?>
            <table class="table table-hover">
            <thead>
              <tr>
                <th>Date</th>
                <th >Project</th>
                <th>Cost</th>
                <th>Invoice </th>
                <th>Invoiced</th>
                <th>Paid</th>
               
              </tr></thead><tbody>
              <?php do { ?>
              <tr>
                <td><?php echo date('d M Y',strtotime($row_rsProjects['createddatetime'])); ?></td>
                <td><a href="/projects/admin/update.php?projectID=<?php echo $row_rsProjects['ID']; ?>" ><?php echo $row_rsProjects['ID']; ?> <?php echo $row_rsProjects['title']; ?></a></td>
                 <td><?php echo number_format($row_rsProjects['price'],2,".",","); ?></td>
                 <td><?php echo $row_rsProjects['invoicenumber']; ?></td>
                  <td><?php echo isset($row_rsProjects['invoicedate']) ? date('d M Y', strtotime($row_rsProjects['invoicedate'])) : "&nbsp;"; ?></td>
                   <td><?php echo isset($row_rsProjects['paidbydate']) ? date('d M Y', strtotime($row_rsProjects['paidbydate'])) : "&nbsp;"; ?></td>
               
              </tr>
              <?php } while ($row_rsProjects = mysql_fetch_assoc($rsProjects)); ?></tbody>
            </table>
            <?php } // Show if recordset not empty ?>
            <?php if ($totalRows_rsProjects == 0) { // Show if recordset empty ?>
            <p>There are no projects associated with this organisation.</p>
            <?php } // Show if recordset empty ?>
          </div>
          <div class="TabbedPanelsContent">
            <table border="0" cellpadding="2" cellspacing="0" class="form-table">
              <tr>
                <td align="right" valign="top">Bank account name:</td>
                <td><input name="bankaccountname" type="text" id="bankaccountname" value="<?php echo isset($row_rsDirectory['bankaccountname']) ? $row_rsDirectory['bankaccountname'] : $row_rsDirectory['name'];  ?>" size="50" maxlength="50" class="form-control"  /></td>
              </tr>
              <tr>
                <td align="right" valign="top">Bank account no:</td>
                <td><input name="bankaccount" type="text" id="bankaccount" value="<?php echo $row_rsDirectory['bankaccountnumber']; ?>" size="20" maxlength="20"  class="form-control" /></td>
              </tr>
              <tr>
                <td align="right" valign="top">Bank sort code:</td>
                <td><input name="banksortcode" type="text" id="banksortcode" value="<?php echo $row_rsDirectory['banksortcode']; ?>" size="20" maxlength="10"  class="form-control" /></td>
              </tr>
              <tr>
                <td align="right" valign="top">Bank name:</td>
                <td><input name="bankname" type="text" id="bankname" value="<?php echo $row_rsDirectory['bankname']; ?>" size="50" maxlength="50"  class="form-control" /></td>
              </tr>
              <tr>
                <td align="right" valign="top">Bank address:</td>
                <td><textarea name="bankaddress" id="bankaddress" cols="45" rows="5" class="form-control" ><?php echo $row_rsDirectory['bankaddress']; ?></textarea></td>
              </tr>
              <tr>
                <td align="right" valign="top">Bank Postcode:</td>
                <td><input name="bankpostcode" type="text" id="bankpostcode" value="<?php echo $row_rsDirectory['bankpostcode']; ?>" size="20" maxlength="10"  class="form-control" /></td>
              </tr>
              <tr>
                <td align="right" valign="top">VAT number:</td>
                <td><input name="vatnumber" type="text" id="vatnumber" value="<?php echo $row_rsDirectory['vatnumber']; ?>" size="20" maxlength="20" class="form-control"  /></td>
              </tr> <tr>
                <td class="text-nowrap text-right top">Company type:</td>
                <td><label>
                  <input <?php if (!(strcmp($row_rsDirectory['directorytype'],"1"))) {echo "checked=\"checked\"";} ?> type="radio" name="directorytype" value="1" id="directorytype_1" />
                  Ltd</label>
                  <label>
                    <input <?php if (!(strcmp($row_rsDirectory['directorytype'],"2"))) {echo "checked=\"checked\"";} ?> type="radio" name="directorytype" value="2" id="directorytype_2" />
                    LLP</label>
                  <label>
                    <input <?php if (!(strcmp($row_rsDirectory['directorytype'],"3"))) {echo "checked=\"checked\"";} ?> type="radio" name="directorytype" value="3" id="directorytype_3" />
                    Sole trader</label>
                  <label>
                    <input <?php if (!(strcmp($row_rsDirectory['directorytype'],"4"))) {echo "checked=\"checked\"";} ?> type="radio" name="directorytype" value="4" id="directorytype_4" />
                    Charity</label>
                  <label>
                    <input <?php if (!(strcmp($row_rsDirectory['directorytype'],"5"))) {echo "checked=\"checked\"";} ?> type="radio" name="directorytype" value="5" id="directorytype_5" />
                    Private </label>
                  <label>
                    <input <?php if (!(strcmp($row_rsDirectory['directorytype'],"6"))) {echo "checked=\"checked\"";} ?> type="radio" name="directorytype" value="6" id="directorytype_6" />
                    Social Enterprise</label>
                  <label>
                    <input <?php if (!(strcmp($row_rsDirectory['directorytype'],"7"))) {echo "checked=\"checked\"";} ?> type="radio" name="directorytype" value="7" id="directorytype_7" />
                    Public Sector</label>
                  <label>
                    <input <?php if (!isset($row_rsDirectory['directorytype']) || !(strcmp($row_rsDirectory['directorytype'],"0"))) {echo "checked=\"checked\"";} ?> type="radio" name="directorytype" value="0" id="directorytype_0" />
                    Other</label>
                    </td>
              </tr>
              <tr>
                <td align="right" valign="top">Company  number:</td>
                <td><input name="companynumber" type="text" id="companynumber" value="<?php echo $row_rsDirectory['companynumber']; ?>" size="20" maxlength="20" class="form-control"  /></td>
              </tr>
              <tr>
                <td align="right" valign="top">Charity number:</td>
                <td><input name="charitynumber" type="text" id="charitynumber" value="<?php echo $row_rsDirectory['charitynumber']; ?>" size="20" maxlength="20"  class="form-control" /></td>
              </tr>
              <tr class="row_parent">
                <td class="text-nowrap text-right top">Parent:</td>
                <td class="form-inline"><label>
                  <input <?php if (!(strcmp($row_rsDirectory['isparent'],1))) {echo "checked=\"checked\"";} ?> name="isparent" type="checkbox" id="isparent" value="1" />
                  is parent.</label>
                  &nbsp;&nbsp;&nbsp;
                  <label class="hasparent">Has parent:
                    <select name="parentID" id="parentID" class="form-control" >
                      <option value="" <?php if (!(strcmp("", $row_rsDirectory['parentID']))) {echo "selected=\"selected\"";} ?>>No parent</option>
                      <?php
do {  
?>
                      <option value="<?php echo $row_rsParents['ID']?>"<?php if (!(strcmp($row_rsParents['ID'], $row_rsDirectory['parentID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsParents['name']?></option>
                      <?php
} while ($row_rsParents = mysql_fetch_assoc($rsParents));
  $rows = mysql_num_rows($rsParents);
  if($rows > 0) {
      mysql_data_seek($rsParents, 0);
	  $row_rsParents = mysql_fetch_assoc($rsParents);
  }
?>
                    </select>
                  </label>
                  <?php if ($totalRows_rsChildren > 0) { // Show if recordset not empty ?>
                  <p>Subsidiaries:</p>
                  <ul>
                    <?php do { ?>
                    <li> <a href="update_directory.php?directoryID=<?php echo $row_rsChildren['ID']; ?>"><?php echo $row_rsChildren['name']; ?></a></li>
                    <?php } while ($row_rsChildren = mysql_fetch_assoc($rsChildren)); ?>
                  </ul>
                  <?php } // Show if recordset not empty ?></td>
              </tr>
            </table>
          </div>
          <div class="TabbedPanelsContent"><p><label>Add note:<br />
            
              <textarea name="notes" id="notes" cols="45" rows="5"  class="form-control" ></textarea>
            </label>
            <br />
            <button type="submit" name="addnote" id="addnote" class="btn btn-default btn-secondary"  >Add note</button>
            
            <input name="createdbyID" type="hidden" id="createdbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
            <input name="createddatetime" type="hidden" id="createddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
            <input name="directoryID" type="hidden" id="directoryID" value="<?php echo $row_rsDirectory['ID']; ?>" />
            <input name="commcatID" type="hidden" id="commcatID" value="1" />
            <input name="commtypeID" type="hidden" id="commtypeID" value="1" />
            <input type="hidden" name="thiscommdatetime" id="thiscommdatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
          </p>
          <?php if ($totalRows_rsNotes == 0) { // Show if recordset empty ?>
            <p>There are currently no  notes associated with this organisation.</p>
            <?php } // Show if recordset empty ?>
            <?php if ($totalRows_rsNotes > 0) { // Show if recordset not empty ?>
  <table border="0" cellpadding="0" cellspacing="0" class="form-table">
    <?php do { ?>
      
      <tr>
        <td><em><?php echo date('d M Y H:i', strtotime($row_rsNotes['thiscommdatetime'])); ?></em>&nbsp;</td>
        <td><em><?php echo $row_rsNotes['firstname']; ?> <?php echo $row_rsNotes['surname']; ?> wrote:</em></td>
      </tr><tr>
        <td>&nbsp;</td>
        <td><?php echo nl2br($row_rsNotes['notes']); ?></td>
      </tr>
      <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
      </tr>
      <?php } while ($row_rsNotes = mysql_fetch_assoc($rsNotes)); ?>
  </table>
  <?php } // Show if recordset not empty ?></div>
        </div>
      </div>
    
   <div><button type="submit" class="btn btn-primary" >Save changes</button> 
     <label>
       <input type="checkbox" name="informuser" id="informuser" value="1" />
       Inform main contact that directory details have been updated</label> <input name="useremail" type="hidden" id="useremail" value="<?php echo $row_rsDirectory['useremail']; ?>" />
   </div></form>
    <p><em>Originally created by <a href="../../members/admin/modify_user.php?userID=<?php echo $row_rsDirectory['createdbyID']; ?>"><?php echo $row_rsDirectory['firstname']; ?> <?php echo $row_rsDirectory['surname']; ?></a> at <?php echo date('g:ia',strtotime( $row_rsDirectory['createddatetime'])); ?> on <?php echo date('l jS F Y',strtotime($row_rsDirectory['createddatetime'])); ?></em></p>
            <em>
            <?php if(isset($row_rsLastModified['modifieddatetime'])) { ?>
            </em>
            <p><em>Last updated by <?php echo $row_rsLastModified['firstname']; ?> <?php echo $row_rsLastModified['surname']; ?> at <?php echo date('g:ia',strtotime($row_rsLastModified['modifieddatetime'])); ?> on <?php echo date('l jS F Y',strtotime($row_rsLastModified['modifieddatetime'])); ?></em></p>
            <?php } ?>
      <?php if (isset($_GET['defaultTab'])) { echo '<script>
<!--
var TabbedPanels1 = new Spry.Widget.TabbedPanels("TabbedPanels1", {defaultTab:'.intval($_GET['defaultTab']).'});
//-->
    </script>'; } else { ?>
      <script>
<!--
var TabbedPanels1 = new Spry.Widget.TabbedPanels("TabbedPanels1");
//-->
    </script><?php } ?><script>
	  getData('/directory/ajax/createSubCatSelect.php?parentCatID='+document.getElementById('categoryID').value+'&categoryID=<?php echo $row_rsDirectory['categoryID']; ?>','subCat');
	 
	  </script>
      
 </div>
  <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsParentCategories);

mysql_free_result($rsStatus);

mysql_free_result($rsDirectory);

mysql_free_result($rsLastModified);

mysql_free_result($rsDirectoryUser);

mysql_free_result($rsProjects);

mysql_free_result($rsPreferences);

mysql_free_result($rsLocationCategory);

mysql_free_result($rsRegions);

mysql_free_result($rsLocations);

mysql_free_result($rsDirectoryPrefs);

mysql_free_result($rsThisLocation);

mysql_free_result($rsParents);

mysql_free_result($rsChildren);

mysql_free_result($rsDirectoryAreas);

mysql_free_result($rsNotes);
?>
