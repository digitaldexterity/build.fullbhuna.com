<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php require_once('../../../core/includes/framework.inc.php'); ?>
<?php

$regionID = isset($regionID) ? intval($regionID)  : 1;
$startID = isset($_SESSION['startID']) ? intval($_SESSION['startID']) : 0;

$table = isset($_GET['directory']) ? "directory" : "location";
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


mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT googlemapsAPI, googlesearchAPI, openspaceAPI, defaultzoom, defaultlongitude, defaultlatitude, uselocationcategory FROM preferences WHERE ID =".$regionID;
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);

$maxRows_rsLocation = 10;
$pageNum_rsLocation = 0;
if (isset($_GET['pageNum_rsLocation'])) {
  $pageNum_rsLocation = $_GET['pageNum_rsLocation'];
}
$startRow_rsLocation = $pageNum_rsLocation * $maxRows_rsLocation;

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLocation = "SELECT * FROM ".$table." WHERE ID>".$startID." AND latitude IS NULL AND (address1 IS NOT NULL OR postcode IS NOT NULL) ORDER BY ID";
$query_limit_rsLocation = sprintf("%s LIMIT %d, %d", $query_rsLocation, $startRow_rsLocation, $maxRows_rsLocation);
$rsLocation = mysql_query($query_limit_rsLocation, $aquiescedb) or die(mysql_error());
$row_rsLocation = mysql_fetch_assoc($rsLocation);

if (isset($_GET['totalRows_rsLocation'])) {
  $totalRows_rsLocation = $_GET['totalRows_rsLocation'];
} else {
  $all_rsLocation = mysql_query($query_rsLocation);
  $totalRows_rsLocation = mysql_num_rows($all_rsLocation);
}
$totalPages_rsLocation = ceil($totalRows_rsLocation/$maxRows_rsLocation)-1;

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLocationPrefs = "SELECT * FROM locationprefs WHERE ID=".$regionID;
$rsLocationPrefs = mysql_query($query_rsLocationPrefs, $aquiescedb) or die(mysql_error());
$row_rsLocationPrefs = mysql_fetch_assoc($rsLocationPrefs);
$totalRows_rsLocationPrefs = mysql_num_rows($rsLocationPrefs);
?>
<!doctype html>
<!-- Web design by Paul Egan, Jim Campbell -->
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php echo $site_name; ?> <?php echo $admin_name; ?> - Find Locations  </title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<?php 
$googlemapsAPI =isset($googlemapsAPI) ? $googlemapsAPI : $row_rsPreferences['googlemapsAPI'];

if($googlemapsAPI!="") { // load GoogleMaps the API - key can be overriden if neccesary ?>
<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo $googlemapsAPI; ?>&v=3" ></script>
<script>


var initLatitude = <?php echo isset($_GET['latitude']) ? floatval($_GET['latitude']) : $row_rsPreferences['defaultlatitude']; ?>;
var initLongitude =<?php echo isset($_GET['longitude']) ? floatval($_GET['longitude']) : $row_rsPreferences['defaultlongitude']; ?>;
var initZoom = <?php echo isset($_GET['zoom']) ? $_GET['zoom']: $row_rsPreferences['defaultzoom']; ?>;



var map;
var geocoder;
var marker;

$(document).ready(function(e) {
	resetMap();
});




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
	
}

//]]>

$(document).ready(function(e) {
    $(".locationID").each(function(index, element) {
        var locationID = $(this).val();
		
		var address = $("input[name='address["+locationID+"]']").val();
		//alert(address);
    geocoder.geocode( { 'address': address}, function(results, status) {
      if (status == google.maps.GeocoderStatus.OK) {		 
		//map.panTo(results[0].geometry.location);		
		//marker.setPosition(results[0].geometry.location);	
		
		$("input[name='latitude["+locationID+"]']").val(results[0].geometry.location.lat());
		$("input[name='longitude["+locationID+"]']").val(results[0].geometry.location.lng());	
		url = "/location/admin/options/ajax/update_latlong.ajax.php?table=directory&ID="+locationID+"&latitude="+results[0].geometry.location.lat()+"&longitude="+results[0].geometry.location.lng();	
		
		$.get( url, function( data ) {
  
  //alert(data);
});
			
      } else {
		  // do nothing
       // alert("Geocode was not successful for the following reason: " + status);
      }
    });
	
    });
	setTimeout(function() {
		location.reload();
	},10000);
});
</script>



<?php } // end Google maps ?>
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" -->
        <div class="page location">
          <h1>Find Locations...</h1>
       
<?php if($googlemapsAPI!="") { ?>
  
    <div class="googlemap" id="googlemap"></div>
     

         <form method="post" enctype="multipart/form-data" id="form2" role="form">
           <?php if ($totalRows_rsLocation > 0) { // Show if recordset not empty ?>
  <table class="table table-hover">
    <tr>
     
      <td><strong>Address</strong></td>
      <td><strong>Latitude</strong></td>
      <td><strong>Longitude</strong></td>
    </tr>
    <?php do { ?><?php $address = "";  
  $address .= (isset($row_rsLocation['address1']) && $row_rsLocation['address1']!="") ? trim($row_rsLocation['address1'])." " : "";
   $address .= (isset($row_rsLocation['address2']) && $row_rsLocation['address2']!="") ? trim($row_rsLocation['address2'])." " : ""; 
   $address .= (isset($row_rsLocation['address3']) && $row_rsLocation['address3']!="") ? trim($row_rsLocation['address3'])." " : "";
   $address .= (isset($row_rsLocation['address4']) && $row_rsLocation['address4']!="") ? trim($row_rsLocation['address4'])." " : "";
  $address .= (isset($row_rsLocation['postcode']) && $row_rsLocation['postcode']!="") ? trim($row_rsLocation['postcode']) : "";
  ?>
      <tr>
        <td><a href="modify_location.php?locationID=<?php echo $row_rsLocation['ID']; ?>"><?php echo isset($row_rsLocation['locationname']) ? $row_rsLocation['locationname'] : (isset($row_rsLocation['name']) ?  $row_rsLocation['name'] : ""); ?> <?php echo $address; ?></a></td>
        <td>
        <input type="hidden" class="locationID" value="<?php echo $row_rsLocation['ID']; ?>">
         <input type="hidden" name="address[<?php echo $row_rsLocation['ID']; ?>]" value="<?php echo htmlentities($address, ENT_COMPAT, "UTF-8"); ?>">
          <input  name="latitude[<?php echo $row_rsLocation['ID']; ?>]" type="text"  id="latitude[<?php echo $row_rsLocation['ID']; ?>]" value="<?php echo $row_rsLocation['latitude']; ?>" readonly />
        </td>
        <td><input name="longitude[<?php echo $row_rsLocation['ID']; ?>]" type="text"  id="longitude[<?php echo $row_rsLocation['ID']; ?>]" value="<?php echo $row_rsLocation['longitude']; ?>" readonly />
		</td>
      </tr>
      
      <?php $lastID= $row_rsLocation['ID']; ?>
      <?php } while ($row_rsLocation = mysql_fetch_assoc($rsLocation)); $_SESSION['startID']= $lastID;?>
  </table>


        
        
       <?php } // Show if recordset not empty ?>
    </form>
         <?php if ($totalRows_rsLocation == 0) { // Show if recordset empty ?>
  <p>All locations have a position on the map.</p>
  <?php } // Show if recordset empty ?>
  
  <?php }  else { ?>
  <p>Google Maps not set up</p>
  <?php } ?>
  </div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsPreferences);

mysql_free_result($rsLocation);

mysql_free_result($rsLocationPrefs);
?>
