var gl = null;

try {
    gl = navigator.geolocation;
}catch(e){}

if(!gl) {
	// show location services features (hidden by default)
	document.write("<style> .locationServices { display:none } </style>");
}
 
function storePosition(position) {	
	document.getElementById('latitude').value = position.coords.latitude;
	document.getElementById('longitude').value = position.coords.longitude;
	//alert(position.coords.latitude+", "+position.coords.longitude);
	if(map) { // if google map exists, move marker also
	
		point = new GLatLng(position.coords.latitude, position.coords.longitude);
	
		if (marker) 
		{ 
			placeMarkerAtLonLat(position.coords.longitude, position.coords.latitude);
		} 
		else 
		{
			marker = new GMarker(point, {draggable: true});
			map.addOverlay(marker);
	
			GEvent.addListener(marker, "dragend", function() {
			var point = marker.getLatLng();
			});
	
		}
		map.setZoom(14);
		map.panTo(point);
		
		
	}
}
 
function displayError(positionError) {
  alert("Sorry, but your location can not be established.");
}
 


function getGeoLocation() { 
	if (gl) {
  		gl.getCurrentPosition(storePosition, displayError); // callback and error handler
	} else {  
  		alert("Sorry, but geolocation services are not supported by your browser.");  
	}
}