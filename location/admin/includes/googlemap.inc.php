<?php $googlemapsAPI =isset($googlemapsAPI) ? $googlemapsAPI : $row_rsPreferences['googlemapsAPI'];
if($googlemapsAPI!="") { ?>
<script src="/location/scripts/location.js"></script>
<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo $googlemapsAPI; ?>&v=3" ></script>
<script>


var mapLatitude = <?php echo isset($latitude) && $latitude !="" ? $latitude: (isset($row_rsPreferences['maplat']) ? $row_rsPreferences['maplat'] : 0); ?>;
var mapLongitude =<?php echo isset($longitude) && $longitude !="" ? $longitude: (isset($row_rsPreferences['maplong']) ? $row_rsPreferences['maplong'] : 0); ?>;
var mapZoom = 16;
var markerLatitude = <?php echo isset($latitude) && $latitude !="" ? $latitude: "null"; ?>;
var markerLongitude = <?php echo isset($longitude) && $longitude !="" ? $longitude: "null"; ?>;
var map;
var geocoder;
var marker;


$(document).ready(function(e) {
	resetMap();
});


function findAddress() {
    var address = document.getElementById("address").value;
	if(address!="") {
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
}

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
	} else { // if we don't have a marker position add one on click
		//findAddress();
		google.maps.event.addListener(map, 'click', function(event) {
			placeMarker(event.latLng);
			document.getElementById("latitude").value = event.latLng.lat();
    		document.getElementById("longitude").value = event.latLng.lng();	
		});
	}	
}

function placeMarker(location) {
    var marker = new google.maps.Marker({
        position: location, 
        map: map,
		draggable:true
    });
	
	// update lat / long on marker drag
	google.maps.event.addListener(marker, 'dragend', function (event) 							{    		
		map.panTo(this.getPosition());
		document.getElementById("latitude").value = this.getPosition().lat();
    	document.getElementById("longitude").value = this.getPosition().lng();
			
		});	
}

function clearMap() {
	if(confirm('Are you sure you want to clear marker from map?')) {
		document.getElementById("latitude").value = "";
    	document.getElementById("longitude").value = "";
		resetMap();
	}
}


//]]>
</script>
<?php } 
else { echo "<style> #tabMaps { display:none; } </style>" ; } ?>
<p>Drag marker or find address:
                <input name="address" type="text"  id="address" value="<?php echo isset($placeholder_address) ? $placeholder_address : ""; ?>" size="30" maxlength="100" />
                <input name="find" type="button" class="button" id="find" onclick="findAddress();" value="Find" />
                <input name="Button" type="button" class="button" onclick="clearMap();" value="Clear" />
                <span id="getLocation" class="locationServices">
                  <input type="button" onclick="getGeoLocation(); return false;" value="Get my location" />
                </span></p>
              <div class="googlemap" id="googlemap"></div>
              
              

              
              
              <input size="20" type="hidden" id="latitude" name="latitude" value="<?php echo isset($latitude) ? $latitude : ""; ?>" />
              <input size="20" type="hidden" id="longitude" name="longitude" value="<?php echo isset($longitude) ? $longitude : ""; ?>" />