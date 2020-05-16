<?php require_once('../Connections/aquiescedb.php'); ?>
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

$currentPage = $_SERVER["PHP_SELF"];

$maxRows_rsDirectory = 50;
$pageNum_rsDirectory = 0;
if (isset($_GET['pageNum_rsDirectory'])) {
  $pageNum_rsDirectory = $_GET['pageNum_rsDirectory'];
}
$startRow_rsDirectory = $pageNum_rsDirectory * $maxRows_rsDirectory;

$varRegionID_rsDirectory = "0";
if (isset($regionID)) {
  $varRegionID_rsDirectory = $regionID;
}
$varCategoryID_rsDirectory = "0";
if (isset($_GET['categoryID'])) {
  $varCategoryID_rsDirectory = $_GET['categoryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsDirectory = sprintf("SELECT directory.ID, directory.name, directory.description, directory.latitude, directory.longitude FROM directory LEFT JOIN directorycategory ON (directory.categoryID = directorycategory.ID) WHERE directory.statusID = 1 AND directorycategory.statusID = 1 AND (directorycategory.regionID = %s OR %s = 0) AND (directory.categoryID = %s OR %s = 0)", GetSQLValueString($varRegionID_rsDirectory, "int"),GetSQLValueString($varRegionID_rsDirectory, "int"),GetSQLValueString($varCategoryID_rsDirectory, "int"),GetSQLValueString($varCategoryID_rsDirectory, "int"));
$query_limit_rsDirectory = sprintf("%s LIMIT %d, %d", $query_rsDirectory, $startRow_rsDirectory, $maxRows_rsDirectory);
$rsDirectory = mysql_query($query_limit_rsDirectory, $aquiescedb) or die(mysql_error());
$row_rsDirectory = mysql_fetch_assoc($rsDirectory);

if (isset($_GET['totalRows_rsDirectory'])) {
  $totalRows_rsDirectory = $_GET['totalRows_rsDirectory'];
} else {
  $all_rsDirectory = mysql_query($query_rsDirectory);
  $totalRows_rsDirectory = mysql_num_rows($all_rsDirectory);
}
$totalPages_rsDirectory = ceil($totalRows_rsDirectory/$maxRows_rsDirectory)-1;

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT googlemapsAPI, defaultzoom, defaultlongitude, defaultlatitude FROM preferences";
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsDirectoryPrefs = "SELECT * FROM directoryprefs";
$rsDirectoryPrefs = mysql_query($query_rsDirectoryPrefs, $aquiescedb) or die(mysql_error());
$row_rsDirectoryPrefs = mysql_fetch_assoc($rsDirectoryPrefs);
$totalRows_rsDirectoryPrefs = mysql_num_rows($rsDirectoryPrefs);

$queryString_rsDirectory = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsDirectory") == false && 
        stristr($param, "totalRows_rsDirectory") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsDirectory = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsDirectory = sprintf("&totalRows_rsDirectory=%d%s", $totalRows_rsDirectory, $queryString_rsDirectory);
?><?php $accesslevel = $row_rsDirectoryPrefs['accesslevel']; require_once('../members/includes/restrictaccess.inc.php'); ?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Directory Map"; $pageTitle .= ($totalRows_rsThisCategory > 0) ? " - ".$row_rsThisCategory['categoryname'] : ""; echo $pageTitle." | ".$site_name; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<?php 
$latitude = isset($_GET['latitude']) ? $_GET['latitude'] : $row_rsPreferences['defaultlatitude']; 
$longitude =  isset($_GET['longitude']) ? $_GET['longitude'] : $row_rsPreferences['defaultlongitude'];
$magnification = isset($_GET['magnification']) ? $_GET['magnification'] : $row_rsPreferences['defaultzoom']; ?>
<script src="//maps.google.com/?file=api&amp;v=2.x&amp;key=<?php echo isset($googlemapsAPI) ? $googlemapsAPI : $row_rsPreferences['googlemapsAPI']; ?>"></script>
<script src="/core/scripts/googlemaps/googlemap.js" ></script>
<script>
//<![CDATA[
// Latitude and Longitude math routines are from: http://www.fcc.gov/mb/audio/bickel/DDDMMSS-decimal.html

var map = null;
var geocoder = null;
var latsgn = 1;
var lgsgn = 1;
var zm = 0; 
var marker = null;

function init() {
if (GBrowserIsCompatible()) {
map = new GMap2(document.getElementById("googlemap"));
map.setCenter(new GLatLng(<?php echo $latitude; ?>, <?php echo $longitude; ?>), <?php echo $magnification; ?>);
map.setMapType(G_NORMAL_MAP);zm=1;
map.addControl(new GSmallMapControl());
//map.addControl(new MapTypeControl());
map.addControl(new GScaleControl());
map.enableScrollWheelZoom();
map.disableDoubleClickZoom();
geocoder = new GClientGeocoder();
<?php if($totalRows_rsDirectory>0) { do { if(isset($row_rsDirectory['latitude'])) { ?>
marker<?php echo $row_rsDirectory['ID']; ?> = new GMarker(new GLatLng(<?php echo $row_rsDirectory['latitude']; ?>, <?php echo $row_rsDirectory['longitude']; ?>), {title:"<?php echo $row_rsDirectory['name']; ?>"});
map.addOverlay(marker<?php echo $row_rsDirectory['ID']; ?>);
GEvent.addListener(marker<?php echo $row_rsDirectory['ID']; ?>, "click", function()
 {
 window.location.href='directory.php?directoryID=<?php echo $row_rsDirectory['ID']; echo ($regionID==1) ? "&defaultTab=3" : ""; ?>';
 });
<?php } } while ($row_rsDirectory = mysql_fetch_assoc($rsDirectory)); mysql_data_seek($rsDirectory,0); $row_rsDirectory = mysql_fetch_assoc($rsDirectory);
 } ?>

}}


function createMarker(point, html) 
{
 var marker = new GMarker(point);
 GEvent.addListener(marker, "click", function()
 {
 marker.openInfoWindowHtml(html);
 });
 return marker;
}


addListener("load",init);

//]]>
</script>
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" -->
    <div class="crumbs"><div><span class="you_are_in">You are in: </span><a href="/">Home</a><span class="separator">&nbsp;&rsaquo;&nbsp;</span><a href="/directory/index.php">The Directory</a><span class="separator">&nbsp;&rsaquo;&nbsp;</span>Map</div></div>
    <h1 class="directoryheader">Locations</h1>
    <p>Hover over a marker to view organisation name, or click on marker to visit their entry. You can also drag and zoom the map using the controls.</p>
    <div class="googlemap" id="googlemap"></div>
    <!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsDirectory);

mysql_free_result($rsPreferences);

mysql_free_result($rsDirectoryPrefs);
?>