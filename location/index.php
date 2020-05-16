<?php require_once('../Connections/aquiescedb.php'); ?>
<?php require_once('../core/includes/framework.inc.php'); ?>
<?php 
$_GET['categoryID'] = isset($_GET['categoryID']) ? intval($_GET['categoryID']) : 0; 
$_GET['locationsearch'] = (isset($_GET['locationsearch']) && $_GET['locationsearch'] !="Search by postcode or location") ? $_GET['locationsearch'] : "";
if (!isset($_SESSION)) {
  session_start();
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

$currentPage = $_SERVER["PHP_SELF"];

if(isset($_GET['pageNum_rsLocations'])) $_GET['pageNum_rsLocations'] = intval($_GET['pageNum_rsLocations']);
if(isset($_GET['totalRows_rsLocations'])) $_GET['totalRows_rsLocations'] = intval($_GET['totalRows_rsLocations']);

$_GET['categoryID'] = isset($_GET['categoryID']) ? intval($_GET['categoryID']) : 0; 
$orderby = ((isset($_GET['latitude']) && $_GET['latitude']!="") || (isset($_GET['locationsearch']) && $_GET['locationsearch']!="")) ?  "distance ASC" : "locationname ASC" ;
$where =  "WHERE location.active = 1 AND public = 1 ";
$where .= ($_GET['categoryID']>0) ? " AND location.categoryID = ".intval($_GET['categoryID'])." " : "";

$categoryID = isset($_GET['categoryID']) ? $_GET['categoryID'] : 0;
 
$maxRows_rsLocations = isset($_GET['address']) ? 500 : 10;
$pageNum_rsLocations = 0;
if (isset($_GET['pageNum_rsLocations'])) {
  $pageNum_rsLocations = $_GET['pageNum_rsLocations'];
}
$startRow_rsLocations = $pageNum_rsLocations * $maxRows_rsLocations;

$varLat_rsLocations = "0";
if (isset($_GET['latitude'])) {
  $varLat_rsLocations = $_GET['latitude'];
}
$varLong_rsLocations = "0";
if (isset($_GET['longitude'])) {
  $varLong_rsLocations = $_GET['longitude'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLocations = sprintf("SELECT location.ID, locationname,   location.active AS statusID,  location.address1,  location.address2, location.address3,  location.postcode, location.latitude, location.longitude, location.`description`, location.telephone1,  location.locationURL,  3956*2*ASIN(SQRT(POWER(SIN((location.latitude-%s)*pi()/180/2),2)+COS(location.latitude*pi()/180)*COS(%s*pi()/180)*POWER(SIN((location.longitude-%s)*pi()/180/2),2))) as distance FROM location  ".$where." ORDER BY ".$orderby."", GetSQLValueString($varLat_rsLocations, "double"),GetSQLValueString($varLat_rsLocations, "double"),GetSQLValueString($varLong_rsLocations, "double"));
$query_limit_rsLocations = sprintf("%s LIMIT %d, %d", $query_rsLocations, $startRow_rsLocations, $maxRows_rsLocations);
$rsLocations = mysql_query($query_limit_rsLocations, $aquiescedb) or die(mysql_error());
$row_rsLocations = mysql_fetch_assoc($rsLocations);

if (isset($_GET['totalRows_rsLocations'])) {
  $totalRows_rsLocations = $_GET['totalRows_rsLocations'];
} else {
  $all_rsLocations = mysql_query($query_rsLocations);
  $totalRows_rsLocations = mysql_num_rows($all_rsLocations);
}
$totalPages_rsLocations = ceil($totalRows_rsLocations/$maxRows_rsLocations)-1;


$queryString_rsLocations = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsLocations") == false && 
        stristr($param, "totalRows_rsLocations") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsLocations = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsLocations = sprintf("&totalRows_rsLocations=%d%s", $totalRows_rsLocations, $queryString_rsLocations);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT * FROM preferences";
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLocationCategories = "SELECT ID, categoryname FROM locationcategory WHERE statusID = statusID ORDER BY categoryname ASC";
$rsLocationCategories = mysql_query($query_rsLocationCategories, $aquiescedb) or die(mysql_error());
$row_rsLocationCategories = mysql_fetch_assoc($rsLocationCategories);
$totalRows_rsLocationCategories = mysql_num_rows($rsLocationCategories);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLocationPrefs = "SELECT * FROM locationprefs";
$rsLocationPrefs = mysql_query($query_rsLocationPrefs, $aquiescedb) or die(mysql_error());
$row_rsLocationPrefs = mysql_fetch_assoc($rsLocationPrefs);
$totalRows_rsLocationPrefs = mysql_num_rows($rsLocationPrefs);

$queryString_rsLocations = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsLocations") == false && 
        stristr($param, "totalRows_rsLocations") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsLocations = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsLocations = sprintf("&totalRows_rsLocations=%d%s", $totalRows_rsLocations, $queryString_rsLocations);
?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Locations"; echo $pageTitle." | ".$site_name; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<?php $googlemapsAPI =isset($googlemapsAPI) ? $googlemapsAPI : $row_rsPreferences['googlemapsAPI'];
if($googlemapsAPI!="") { ?>
<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo $googlemapsAPI; ?>&v=3" ></script>
<script src="/core/scripts/googlemaps/markermanager.v3.js"></script><script>


var initLatitude = <?php echo isset($_GET['latitude']) ? floatval($_GET['latitude']) : $row_rsPreferences['defaultlatitude']; ?>;
var initLongitude =<?php echo isset($_GET['longitude']) ? floatval($_GET['longitude']) : $row_rsPreferences['defaultlongitude']; ?>;
var initZoom = <?php echo isset($_GET['zoom']) ? $_GET['zoom']: $row_rsPreferences['defaultzoom']; ?>;

var map;
var geocoder;
var markers = [];
var infowindow = [];


$(document).ready(function(e) {
	
	<?php if(!isset($_GET['address'])) { ?>
	/* Find device location ? */
	var gl = null;
	try {
		gl = navigator.geolocation;
	} catch(e){}
	if(gl) {
		// show location services features (hidden by default)
		//document.write("<style> .locationServices { display:block } </style>");
		document.getElementById("address").value = "Finding your location...";
		gl.getCurrentPosition(setLocation, displayError); // callback and error handler
	}
	<?php } ?>

	geocoder = new google.maps.Geocoder();
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
	
	setupMarkers() 
});


function findAddress(theForm) {
    var address = theForm.address.value;
	if(address == "My current location") { // we already have a location
		theForm.submit();	
	} else {
    	geocoder.geocode( { 'address': address}, function(results, status) {
      if (status == google.maps.GeocoderStatus.OK) {		 	
		theForm.latitude.value = results[0].geometry.location.lat();
		theForm.longitude.value = results[0].geometry.location.lng();
		theForm.submit();		
      } else {
        alert("Geocode was not successful for the following reason: " + status);
      }
    });
	}
}
  
function setupMarkers() {		
<?php if($totalRows_rsLocations > 0) { $i=0; do { 
if(strlen($row_rsLocations['longitude'])>0) { $i++; ?>			 
	markers[<?php echo $i; ?>] = new google.maps.Marker({
		map: map,
		title: "Click for more information", position:new google.maps.LatLng(<?php echo floatval($row_rsLocations['latitude']); ?>, <?php echo floatval($row_rsLocations['longitude']); ?>) 
	}); 
	var contentString = "<div><?php echo htmlentities($row_rsLocations['locationname'])."<br>".htmlentities($row_rsLocations['address1'])."<br>".htmlentities($row_rsLocations['address2'])."<br>".htmlentities($row_rsLocations['address3'])."<br>".htmlentities($row_rsLocations['address4'])."<br>".htmlentities($row_rsLocations['address5'])."<br>".htmlentities($row_rsLocations['postcode']); ?><br><?php echo htmlentities($row_rsLocations['telephone1']); ?></div>";
  	infowindow[<?php echo $i; ?>] = new google.maps.InfoWindow({
      content: contentString
  	});
	google.maps.event.addListener(markers[<?php echo $i; ?>] , 'click', function() {
    	infowindow[<?php echo $i; ?>].open(map,markers[<?php echo $i; ?>]);
  	});         
<?php } } while ($row_rsLocations = mysql_fetch_assoc($rsLocations)); mysql_data_seek($rsLocations,0); $row_rsLocations = mysql_fetch_assoc($rsLocations); }  ?>		
	
}

/* Device geolocate functions */
function setLocation(position) {	
	document.getElementById('latitude').value = position.coords.latitude;
	document.getElementById('longitude').value = position.coords.longitude;
	document.getElementById("address").value = "My current location";
}

function displayError(positionError) {
	if(document.getElementById("address").value == "Finding your location...") document.getElementById("address").value = "";
	if(positionError.code !=1) { // 1 = prermission denied by user
  		alert("Sorry, but your location can not be established. ");
	}
}



//]]>
</script><?php } ?>
<?php if ($row_rsPreferences['uselocationcategory']!=1) { ?><style>.categories { display:none; }</style><?php } ?><!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" -->
  <div id="pageLocations" class="container pageBody">
 <div class="crumbs"><div>You are in: <a href="/">Home</a><span class="separator">&nbsp;&rsaquo;&nbsp;</span><?php echo ucwords($row_rsLocationPrefs['locationdescriptor']); ?></div></div>
          <h1><?php echo ucwords($row_rsLocationPrefs['locationdescriptor']); ?>s </h1> <?php if($row_rsLocationPrefs['publicaccess']==1 || (isset($_SESSION['MM_UserGroup']) && $_SESSION['MM_UserGroup'] >0)) { // allowed access ?><form action="index.php" method="get"><fieldset class="form-inline"><legend>Search</legend>

              <input name="address" type="text" id="address" value="<?php echo isset($_REQUEST['address']) ? htmlentities($_REQUEST['address'], ENT_COMPAT, "UTF-8") : ""; ?>" size="50" maxlength="50"  onfocus="if(this.value=='My current position') this.value='';" class="form-control" /><input name="zoom" type="hidden" value="13" />
            <input name="latitude" id="latitude" type="hidden" value="<?php echo isset($_GET['latitude']) ? htmlentities($_GET['latitude'], ENT_COMPAT, "UTF-8") : $row_rsPreferences['defaultlatitude']; ?>" />
            <input name="longitude" id="longitude" type="hidden" value="<?php echo isset($_GET['longitude']) ? htmlentities($_GET['longitude'], ENT_COMPAT, "UTF-8") : $row_rsPreferences['defaultlongitude']; ?>" />
        <select name="categoryID"  id="categoryID" class="categories form-control" >
  <option value="0" <?php if (!(strcmp(0, $_GET['categoryID']))) {echo "selected=\"selected\"";} ?>>All categories</option>
  <?php
do {  
?>
  <option value="<?php echo $row_rsLocationCategories['ID']?>"<?php if (!(strcmp($row_rsLocationCategories['ID'], $_GET['categoryID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsLocationCategories['categoryname']?></option>
  <?php
} while ($row_rsLocationCategories = mysql_fetch_assoc($rsLocationCategories));
  $rows = mysql_num_rows($rsLocationCategories);
  if($rows > 0) {
      mysql_data_seek($rsLocationCategories, 0);
	  $row_rsLocationCategories = mysql_fetch_assoc($rsLocationCategories);
  }
?>
</select>
  
  
  <button type="submit" name="searchbutton" id="searchbutton" class="btn btn-default btn-secondary " onclick="findAddress(this.form); return false;"  >Go</button>
    </fieldset>
</form><div class="googlemap" id="googlemap"></div>
        <?php if ($totalRows_rsLocations == 0) { // Show if recordset empty ?>
            <p>There are no locations matching your search.</p>
            <?php } // Show if recordset empty ?><?php if ($totalRows_rsLocations > 0) { // Show if recordset not empty ?>
            <p class="text-muted">Locations <?php echo ($startRow_rsLocations + 1) ?> to <?php echo min($startRow_rsLocations + $maxRows_rsLocations, $totalRows_rsLocations) ?> of <?php echo $totalRows_rsLocations ?> </p>
	    
		    <table class="table table-hover">
            <thead>
		      <tr>
		        <th>&nbsp;</th>
		        <th>Location</th>
		        <th class="categories">Address</th>
<th class="categories">Telephone</th>
                <th class="categories">Category</th>
                <th>View</th>
              </tr></thead><tbody>
		      <?php do { ?>
	          <tr>
	            <td class="status<?php echo $row_rsLocations['statusID']; ?> category<?php echo $row_rsLocations['categoryID']; ?>" >&nbsp;</td>
	            <td><?php echo isset($row_rsLocations['locationname']) ? $row_rsLocations['locationname'] : $row_rsLocations['address1']." ".$row_rsLocations['address2']." ".$row_rsLocations['postcode']; ?>&nbsp;&nbsp;</td>
	            <td class="categories"><?php echo trim($row_rsLocations['address1'].", ".$row_rsLocations['postcode'],", "); ?></td>
	            <td class="text-nowrap categories"><?php echo $row_rsLocations['telephone1']; ?></td>
	            <td class="categories"><em><?php echo (isset($row_rsLocations['categoryname']) && $row_rsLocations['categoryname'] != "") ? $row_rsLocations['categoryname'] : "<em>None</em>" ; ?></em></td>
	            <td><a href="location.php?locationID=<?php echo $row_rsLocations['ID']; ?>&amp;categoryID=<?php echo $categoryID; ?>" class="link_view">View</a></td>
	          </tr>
	          <?php } while ($row_rsLocations = mysql_fetch_assoc($rsLocations)); ?></tbody>
          </table>
		    <?php } // Show if recordset not empty ?>
            <table class="form-table">
              <tr>
                <td><?php if ($pageNum_rsLocations > 0) { // Show if not first page ?>
                    <a href="<?php printf("%s?pageNum_rsLocations=%d%s", $currentPage, 0, $queryString_rsLocations); ?>">First</a>
                    <?php } // Show if not first page ?></td>
                <td><?php if ($pageNum_rsLocations > 0) { // Show if not first page ?>
                    <a href="<?php printf("%s?pageNum_rsLocations=%d%s", $currentPage, max(0, $pageNum_rsLocations - 1), $queryString_rsLocations); ?>">Previous</a>
                    <?php } // Show if not first page ?></td>
                <td><?php if ($pageNum_rsLocations < $totalPages_rsLocations) { // Show if not last page ?>
                    <a href="<?php printf("%s?pageNum_rsLocations=%d%s", $currentPage, min($totalPages_rsLocations, $pageNum_rsLocations + 1), $queryString_rsLocations); ?>">Next</a>
                    <?php } // Show if not last page ?></td>
                <td><?php if ($pageNum_rsLocations < $totalPages_rsLocations) { // Show if not last page ?>
                    <a href="<?php printf("%s?pageNum_rsLocations=%d%s", $currentPage, $totalPages_rsLocations, $queryString_rsLocations); ?>">Last</a>
                    <?php } // Show if not last page ?></td>
              </tr>
            </table>
    
            <?php } else { // no access ?>
            <p class="alert warning alert-warning" role="alert">You do not have access to view locations. You may need to <a href="/login/index.php?accesscheck=/location/">log in</a>.</p>
            <?php } ?></div>
    <!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLocations);

mysql_free_result($rsPreferences);

mysql_free_result($rsLocationCategories);

mysql_free_result($rsLocationPrefs);
?>
