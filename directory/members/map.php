<?php require_once('../../Connections/aquiescedb.php'); ?>
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

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "mapform")) {
  $updateSQL = sprintf("UPDATE directory SET latitude=%s, longitude=%s, streetview=%s WHERE ID=%s",
                       GetSQLValueString($_POST['lat'], "double"),
                       GetSQLValueString($_POST['lon'], "double"),
                       GetSQLValueString(isset($_POST['streetview']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}
  
  
if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "mapform")) {
 $updateGoTo = "index.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  $updateGoTo = isset($_GET['returnURL']) ? $_GET['returnURL'] : $updateGoTo;
  header(sprintf("Location: %s", $updateGoTo));exit;
}

$varUsername_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $varUsername_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT users.firstname, users.surname FROM users WHERE users.username = %s", GetSQLValueString($varUsername_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

$colname_rsDirectory = "-1";
if (isset($_GET['directoryID'])) {
  $colname_rsDirectory = $_GET['directoryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsDirectory = sprintf("SELECT ID, postcode, latitude, longitude, streetview FROM directory WHERE ID = %s", GetSQLValueString($colname_rsDirectory, "int"));
$rsDirectory = mysql_query($query_rsDirectory, $aquiescedb) or die(mysql_error());
$row_rsDirectory = mysql_fetch_assoc($rsDirectory);
$totalRows_rsDirectory = mysql_num_rows($rsDirectory);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT googlemapsAPI, googlesearchAPI, openspaceAPI, streetview FROM preferences";
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);

$varUsername_rsIsAuthorised = "-1";
if (isset($_SESSION['MM_Username'])) {
  $varUsername_rsIsAuthorised = $_SESSION['MM_Username'];
}
$varDirectoryID_rsIsAuthorised = "-1";
if (isset($_GET['directoryID'])) {
  $varDirectoryID_rsIsAuthorised = $_GET['directoryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsIsAuthorised = sprintf("SELECT DISTINCT(directory.ID), name FROM directory LEFT JOIN users AS creator ON (directory.createdbyID = creator.ID) LEFT JOIN directoryuser ON (directory.ID = directoryuser.directoryID) LEFT JOIN users ON (directoryuser.userID = users.ID) WHERE (creator.username = %s OR users.username = %s) AND directory.ID= %s", GetSQLValueString($varUsername_rsIsAuthorised, "text"),GetSQLValueString($varUsername_rsIsAuthorised, "text"),GetSQLValueString($varDirectoryID_rsIsAuthorised, "int"));
$rsIsAuthorised = mysql_query($query_rsIsAuthorised, $aquiescedb) or die(mysql_error());
$row_rsIsAuthorised = mysql_fetch_assoc($rsIsAuthorised);
$totalRows_rsIsAuthorised = mysql_num_rows($rsIsAuthorised);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsDirectoryPrefs = "SELECT * FROM directoryprefs";
$rsDirectoryPrefs = mysql_query($query_rsDirectoryPrefs, $aquiescedb) or die(mysql_error());
$row_rsDirectoryPrefs = mysql_fetch_assoc($rsDirectoryPrefs);
$totalRows_rsDirectoryPrefs = mysql_num_rows($rsDirectoryPrefs);
?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Directory Map"; echo $pageTitle." | ".$site_name; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<?php $latitude = isset($row_rsDirectory['latitude']) ? $row_rsDirectory['latitude']: 55.8544; // centre on glasgow if not set
$longitude = isset($row_rsDirectory['longitude']) ? $row_rsDirectory['longitude']: -4.240963;
$magnification = isset($row_rsDirectory['latitude']) ? 16 : 10; ?>
<script src="//maps.google.com/?file=api&amp;v=2.x&amp;key=<?php echo isset($googlemapsAPI) ? $googlemapsAPI : $row_rsPreferences['googlemapsAPI']; ?>"></script>
<script src="/core/scripts/googlemap.js" ></script>
<script>
//<![CDATA[
// Latitude and Longitude math routines are from: http://www.fcc.gov/mb/audio/bickel/DDDMMSS-decimal.html

var map = null;
var geocoder = null;
var latsgn = 1;
var lgsgn = 1;
var zm = 0; 
var marker = null;

function xz() {
if (GBrowserIsCompatible()) {
map = new GMap2(document.getElementById("googlemap"));
map.setCenter(new GLatLng(<?php echo $latitude; ?>, <?php echo $longitude; ?>), <?php echo $magnification; ?>);
map.setMapType(G_NORMAL_MAP);zm=1;
map.addControl(new GLargeMapControl());
//map.addControl(new MapTypeControl());
map.addControl(new GScaleControl());
map.enableScrollWheelZoom();
map.disableDoubleClickZoom();
geocoder = new GClientGeocoder();

marker = new GMarker(new GLatLng(<?php echo $latitude; ?>, <?php echo $longitude; ?>), {draggable: true});
map.addOverlay(marker);

GEvent.addListener(marker, "dragend", function() {
var point = marker.getLatLng();
if (zm == 0)
{map.setCenter(point,7); zm = 1;}
else
{map.setCenter(point);}
computepos(point);
});


GEvent.addListener(marker, "click", function() {
var point = marker.getLatLng();
//marker.openInfoWindowHtml(marker.getLatLng().toUrlValue(6));
computepos (point);
});

}}

function computepos (point)
{
var latA = Math.abs(Math.round(value=point.y * 1000000.));
var lonA = Math.abs(Math.round(value=point.x * 1000000.));

if(value=point.y < 0)
{
	var ls = '-' + Math.floor((latA / 1000000));
}
else
{
	var ls = Math.floor((latA / 1000000));
}

var lm = Math.floor(((latA/1000000) - Math.floor(latA/1000000)) * 60);
var ld = ( Math.floor(((((latA/1000000) - Math.floor(latA/1000000)) * 60) - Math.floor(((latA/1000000) - Math.floor(latA/1000000)) * 60)) * 100000) *60/100000 );

if(value=point.x < 0)
{
  var lgs = '-' + Math.floor((lonA / 1000000));
}
else
{
	var lgs = Math.floor((lonA / 1000000));
}

var lgm = Math.floor(((lonA/1000000) - Math.floor(lonA/1000000)) * 60);
var lgd = ( Math.floor(((((lonA/1000000) - Math.floor(lonA/1000000)) * 60) - Math.floor(((lonA/1000000) - Math.floor(lonA/1000000)) * 60)) * 100000) *60/100000 );

document.getElementById("latbox").value=point.y; 
document.getElementById("latboxm").value=ls;
document.getElementById("latboxmd").value=lm;
document.getElementById("latboxms").value=ld;

document.getElementById("lonbox").value=point.x;
document.getElementById("lonboxm").value=lgs;
document.getElementById("lonboxmd").value=lgm;
document.getElementById("lonboxms").value=lgd;
}

function showAddress(address) {
 if (geocoder) {
 geocoder.getLatLng(address,
 function(point) {
 if (!point) {
 alert(address + " not found");
 } else {
 //map.setMapType(G_HYBRID_MAP);
 map.setMapType(G_NORMAL_MAP);
 map.setCenter(point,16);
 zm = 1;
 marker.setPoint(point);
 GEvent.trigger(marker, "click");
 }
 }
 );
 }
}






function createMarker(point, html) 
{
 var marker = new GMarker(point);
 GEvent.addListener(marker, "click", function()
 {
 marker.openInfoWindowHtml(html);
 });
 return marker;
}

function reset() {
map.clearOverlays();
document.getElementById("latbox").value='';
document.getElementById("latboxm").value='';
document.getElementById("latboxmd").value='';
document.getElementById("latboxms").value='';
document.getElementById("lonbox").value='';
document.getElementById("lonboxm").value='';
document.getElementById("lonboxmd").value='';
document.getElementById("lonboxms").value='';
marker = new GMarker(new GLatLng(20.0, -10.0), {draggable: true});
map.addOverlay(marker);
marker.setPoint(map.getCenter());

GEvent.addListener(marker, "dragend", function() {
var point = marker.getLatLng();
if (zm == 0)
{map.setCenter(point,7); zm = 1;}
else
{map.setCenter(point);}
computepos(point);
});

GEvent.addListener(marker, "click", function() {
var point = marker.getLatLng();
marker.openInfoWindowHtml(marker.getLatLng().toUrlValue(6));
computepos (point);
});
}

function resetMap() {
map.checkResize();map.setCenter(marker.getLatLng());
}


function init() {
xz();
if (document.getElementById('latbox').value=="" && document.getElementById('address').value !="") { showAddress(document.getElementById('address').value+' UK'); }
}

addListener("load",init);

//]]>
</script>
<?php if($row_rsPreferences['streetview']!=1) { echo "<style> #checkboxstreetview { display:none; } </style>"; } ?>
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" -->


    <h1 class="directoryheader">Update  map location</h1> 
    <?php //if ($totalRows_rsIsAuthorised >0 || $row_rsLoggedIn['usertypeID'] >=8) { //authorsied to access ?>
    <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
      <li><a href="<?php echo isset($_GET['returnURL']) ? $_GET['returnURL'] : "update_directory.php?directoryID=".intval($_GET['directoryID']); ?>" class="link_undo"><i class="glyphicon glyphicon-arrow-left"></i> Back</a></li>
    </ul></div></nav>
    <form name = "addressform" class="form-inline">
    <p>Zoom or drag the map, drag the marker, or enter the address to help pinpoint your location, then click save button below.<br />
  Address/Postcode:
  <input type="text"  style="width:250px" name="address" id="address" value="<?php echo htmlentities($row_rsDirectory['postcode'], ENT_COMPAT, "UTF-8"); ?>" onfocus="if (this.value == '<?php echo htmlentities($row_rsDirectory['postcode'], ENT_COMPAT, "UTF-8"); ?>'){this.value='';} "  class="form-control" /> 
  <button type="button" class="btn btn-default btn-secondary" onclick="showAddress(addressform.address.value+' Glasgow UK');" >Find</button>
</p></form>
<div class="googlemap" id="googlemap"></div><form action="<?php echo $editFormAction; ?>" method="POST" name="mapform" id="mapform"><input type="hidden" id="ID" name="ID" value="<?php echo $row_rsDirectory['ID']; ?>" />
<input size="20" type="hidden" id="latbox" name="lat" value="<?php echo $row_rsDirectory['latitude']; ?>" />
<input size="20" type="hidden" id="lonbox" name="lon" value="<?php echo $row_rsDirectory['longitude']; ?>" />
<input size="5" type="hidden" id="latboxm" name="latm" value="" /><input size="6" type="hidden" id="latboxmd" name="latmd" value="" /><input size="8" type="hidden" id="latboxms" name="latms" value="" />
 <input size="5" type="hidden" id="lonboxm" name="lonm" value="" /><input size="6" type="hidden" id="lonboxmd" name="lonmd" value="" /><input size="8" type="hidden" id="lonboxms" name="lonms" value="" /><button type="submit" class="btn btn-primary">Save map position</button>
 <input type="hidden" name="MM_update" value="mapform" />
 <label id="checkboxstreetview"><input <?php if (!(strcmp($row_rsDirectory['streetview'],1))) {echo "checked=\"checked\"";} ?> name="streetview" type="checkbox" id="streetview" value="1" />
    Enable street view </label>
</form><?php //} else { //not authorised ?>
      <!--<p class="alert warning alert-warning" role="alert">You are not authorised to edit this entry.</p>-->
      <?php //} ?>
<!-- END CONTENT -->

<!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsDirectory);

mysql_free_result($rsPreferences);

mysql_free_result($rsIsAuthorised);

mysql_free_result($rsDirectoryPrefs);
?>
