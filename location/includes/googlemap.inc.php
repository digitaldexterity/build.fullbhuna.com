<?php
$regionID = (isset($regionID) && intval($regionID)>0) ? intval($regionID) : 1;
mysql_select_db($database_aquiescedb, $aquiescedb);
$select = "SELECT googlemapsAPI, maplat, maplong, defaultzoom FROM preferences WHERE ID = ".$regionID;
$result = mysql_query($select, $aquiescedb) or die(mysql_error());
$mapPrefs = mysql_fetch_assoc($result);

$googlemapsAPI =isset($googlemapsAPI) ? $googlemapsAPI : $mapPrefs['googlemapsAPI'];
$latitude = isset($mapPrefs['maplat']) ? $mapPrefs['maplat'] : 0;
$longitude = isset($mapPrefs['maplong']) ? $mapPrefs['maplong'] : 0;
$defaultzoom = isset($mapPrefs['defaultzoom']) ? $mapPrefs['defaultzoom'] : 16;
if($googlemapsAPI!="") { ?>
<script src="/location/scripts/location.js"></script>
<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo $googlemapsAPI; ?>&v=3" ></script>
<script>


var mapLatitude = <?php echo $latitude; ?>;
var mapLongitude =<?php echo $longitude; ?>;
var mapZoom = <?php echo $defaultzoom; ?>;
var markerLatitude = <?php echo $latitude; ?>;
var markerLongitude = <?php echo $longitude; ?>;
var map;
var geocoder;
var marker;


$(document).ready(function(e) {
	resetMap();
});

function resetMap() {
	geocoder = new google.maps.Geocoder();
   var mapOptions = {
        zoom: mapZoom ,
        center: new google.maps.LatLng(mapLatitude, mapLongitude),
        scaleControl: true,
        overviewMapControl: true,
        overviewMapControlOptions:{opened:true},
        mapTypeId: google.maps.MapTypeId.ROADMAP,
		streetViewControl:true
	};
	map = new google.maps.Map(document.getElementById('googlemap'), mapOptions);
	
	
	if(markerLatitude) {  // if marker pos
		markerpos = new google.maps.LatLng(markerLatitude, markerLongitude);
		placeMarker(markerpos);
	} 	
}

function placeMarker(location) {
    var marker = new google.maps.Marker({
        position: location, 
        map: map,
		draggable:false
    });
	
	
}




//]]>
</script>
<?php } else { 
 ?>Cannot load map<?php } ?>

              <div class="googlemap" id="googlemap"></div>
              
              

              
              
             