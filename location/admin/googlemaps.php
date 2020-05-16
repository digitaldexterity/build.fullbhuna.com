<?php require_once('../../Connections/aquiescedb.php'); ?><?php require_once('../../core/includes/adminAccess.inc.php'); ?>
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

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE preferences SET mapURL=%s, maplat=%s, maplong=%s, googlemapsAPI=%s, defaultzoom=%s, defaultlongitude=%s, defaultlatitude=%s, streetview=%s WHERE ID=%s",
                       GetSQLValueString($_POST['mapURL'], "text"),
                       GetSQLValueString($_POST['latitude'], "double"),
                       GetSQLValueString($_POST['longitude'], "double"),
                       GetSQLValueString($_POST['googlemapsAPI'], "text"),
                       GetSQLValueString($_POST['defaultzoom'], "int"),
                       GetSQLValueString($_POST['defaultlongitude'], "double"),
                       GetSQLValueString($_POST['defaultlatitude'], "double"),
                       GetSQLValueString(isset($_POST['streetview']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}


$colname_rsPreferences = "-1";
if (isset($regionID)) {
  $colname_rsPreferences = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = sprintf("SELECT * FROM preferences WHERE ID = %s", GetSQLValueString($colname_rsPreferences, "int"));
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);

$colname_rsLocationPrefs = "-1";
if (isset($regionID)) {
  $colname_rsLocationPrefs = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLocationPrefs = sprintf("SELECT * FROM locationprefs WHERE ID = %s", GetSQLValueString($colname_rsLocationPrefs, "int"));
$rsLocationPrefs = mysql_query($query_rsLocationPrefs, $aquiescedb) or die(mysql_error());
$row_rsLocationPrefs = mysql_fetch_assoc($rsLocationPrefs);
$totalRows_rsLocationPrefs = mysql_num_rows($rsLocationPrefs);
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Google Maps"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->


<?php $googlemapsAPI =isset($googlemapsAPI) ? $googlemapsAPI : $row_rsPreferences['googlemapsAPI'];
if($googlemapsAPI!="") { ?>
<script src="/location/scripts/location.js"></script>
<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo $googlemapsAPI; ?>&v=3" ></script>

<script>


var initLatitude = <?php echo isset($row_rsPreferences['defaultlatitude']) ? $row_rsPreferences['defaultlatitude'] : 0; ?>;
var initLongitude =<?php echo isset($row_rsPreferences['defaultlongitude']) ? $row_rsPreferences['defaultlongitude']: 0;  ?>;
var initZoom = <?php echo isset($row_rsPreferences['defaultzoom']) ? $row_rsPreferences['defaultzoom']: "16";  ?>;
var markerLatitude = <?php echo isset($row_rsPreferences['maplat']) ? $row_rsPreferences['maplat']: "null"; ?>;
var markerLongitude = <?php echo isset($row_rsPreferences['maplong']) ? $row_rsPreferences['maplong']: "null";  ?>;
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
		document.getElementById("defaultlatitude").value = results[0].geometry.location.lat();
		document.getElementById("defaultlongitude").value = results[0].geometry.location.lng();		
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
	
	// get new cords for pan
	google.maps.event.addListener(map, 'center_changed', function() { 
  		document.getElementById("defaultlatitude").value =  map.getCenter().lat();
  		document.getElementById("defaultlongitude").value =  map.getCenter().lng();
	});
	// get new  zoom
	google.maps.event.addListener(map, 'zoom_changed', function() { 
  		document.getElementById("defaultzoom").value =  map.getZoom();
	});
	// new marker pos on click
	google.maps.event.addListener(map, 'click', function(event) {
            placeMarker(event.latLng);
     });

	
}

function placeMarker(location) {



            if (marker == undefined){
                marker = new google.maps.Marker({
                    position: location,
                    map: map,
                    animation: google.maps.Animation.DROP
                });
            }
            else{
                marker.setPosition(location);
            }
            map.setCenter(location);
			
			document.getElementById("latitude").value =  map.getCenter().lat();
  			document.getElementById("longitude").value =  map.getCenter().lng();

        }

//]]>
</script>
<?php } 
else { echo "<style> #tabMaps { display:none; } </style>" ; } ?>
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" --><?php require_once('../../core/region/includes/chooseregion.inc.php'); ?>
    <h1 class="locationheader">Google Maps</h1>
    <form action="<?php echo $editFormAction; ?>" method="POST" name="form1" id="form1">
      <p><label for="googlemapsAPI">Google API Key </label> (<a href="https://console.developers.google.com/cloud-resource-manager" target="_blank" rel="noopener">get one here</a>): <input name="googlemapsAPI" type="text"  id="googlemapsAPI" value="<?php echo $row_rsPreferences['googlemapsAPI']; ?>" size="80" maxlength="255" />
      </p>
      
      
      <p>Drag marker or find address:
                <input name="address" type="text"  id="address" value="<?php echo $row_rsLocation['address1']." ".$row_rsLocation['postcode'].", UK"; ?>" size="30" maxlength="100" />
                <input name="find" type="button" class="button" id="find" onclick="findAddress();" value="Find" />
                <input name="Button" type="button" class="button" onclick="clearMap();" value="Clear" />
                <span id="getLocation" class="locationServices">
                  <input type="button" onclick="getGeoLocation(); return false;" value="Get my location" />
                </span>
                
        </p>
      <div class="googlemap" id="googlemap"></div>
	  <table class="form-table">
      <tr>
      <td>Marker:</td><td>Latitude: <input name="latitude" type="text" id="latitude" value="<?php echo $row_rsPreferences['maplat']; ?>"  /></td><td>Longitude: <input name="longitude" type="text" id="longitude" value="<?php echo $row_rsPreferences['maplong']; ?>"  /></td>
        <td>&nbsp;</td>
      </tr>
       <tr>
         <td>Map centre: </td>
       <td>Latitude: <input name="defaultlatitude" type="text" id="defaultlatitude" value="<?php echo $row_rsPreferences['defaultlatitude']; ?>" /></td><td>Longitude: <input name="defaultlongitude" type="text" id="defaultlongitude" value="<?php echo $row_rsPreferences['defaultlongitude']; ?>" /></td>
         <td>Zoom: <input name="defaultzoom" type="text" id="defaultzoom" value="<?php echo isset($row_rsPreferences['defaultzoom']) ? $row_rsPreferences['defaultzoom']: "16";  ?>" size="5" maxlength="2" /></td>
        </tr></table>
        <input name="ID" type="hidden" id="ID" value="<?php echo $row_rsPreferences['ID']; ?>" />
        
        <input name="button" type="submit" class="button" id="button" onClick="getMapStatus();" value="Save changes" /> 
        &nbsp;&nbsp;&nbsp;<label>
	      <input <?php if (!(strcmp($row_rsPreferences['streetview'],1))) {echo "checked=\"checked\"";} ?> name="streetview" type="checkbox" id="streetview" value="1" />Enable Street View
        </label>
     
      <input type="hidden" name="MM_update" value="form1" />	  </p>

    </form>
    <p><a href="options/location_to_map.php">Find non-mapped locations</a></p>
    <p>To include this map on a page use the Add-in: {googlemap}</p>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsPreferences);

mysql_free_result($rsLocationPrefs);
?>
