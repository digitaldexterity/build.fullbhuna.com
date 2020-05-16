// JavaScript Document

var fb_maps_version = 2;

var map = null;
var geocoder = null;
var marker = null;
var markers = [];
var mgr = null;
var svOverlay = null;
// These global initial values should be overridden by PHP preferences values 
// Copy and Paste before setupMap():
var initLatitude = 0;
var initLongitude = 0;
var initZoom = 2;
var initMapType = G_NORMAL_MAP;
var showMapType = false;
var mapControlType = "small"; // delete for  normal 
var markerLatitude = null;
var markerLongitude = null;
var defaultIcon = new GIcon(G_DEFAULT_ICON);
var isEditable = false;


function setupMap() {
	if (GBrowserIsCompatible()) {
		map = new GMap2(document.getElementById("googlemap"));
		map.setCenter(new GLatLng(initLatitude, initLongitude), initZoom);
		map.setMapType(initMapType);
		map.addControl(new GScaleControl());
		map.enableScrollWheelZoom();
		map.disableDoubleClickZoom();
		geocoder = new GClientGeocoder(); 
		if (showMapType) {
			map.addControl(new MapTypeControl());
		}
		if (mapControlType = "small" ) {
			map.addControl(new GSmallMapControl());
		} else {
			map.addControl(new GLargeMapControl());
		}
		if(isEditable) {
			GEvent.addListener(map, 'click', function(overlay,point) 
			{
				if (marker) 
				{ // if marker already exists or not editable do nothing
				} 
				else 
				{
					createDraggableMarker(point);
					updateLatLong(point);
				}
			});
		
		} // end is editable
	} // end is compatible
} // end setUpMap

function createMarker(markerLat,markerLong,setIcon,isDraggable) {
	marker = new GMarker(new GLatLng(markerLat, markerLong), {draggable: isDraggable, icon:setIcon});
	map.addOverlay(marker);
	if(isDraggable) {
		GEvent.addListener(marker, "dragend", function() {
			var point = marker.getLatLng();
			//map.panTo(point);
			updateLatLong(point); 
		});
	}
}


function createFixedMarker(point,setIcon) {
	marker = new GMarker(point, {draggable: true, icon:setIcon});
	map.addOverlay(marker);
} // end createFixedMarker

function createDraggableMarker(point) {
	createFixedMarker(point,defaultIcon); // create normal marker, then add drag listener
	GEvent.addListener(marker, "dragend", function() {
		var point = marker.getLatLng();
		map.panTo(point);
		updateLatLong(point);
	});
} // end createDraggableMarker


function updateLatLong (point)
{
	document.getElementById("latitude").value=point.y;
	document.getElementById("longitude").value=point.x;
}

function makeAddress() { // make up address field from adddress1, address2, address3 etc. and then place on map
	address = "";
	if(document.getElementById('address')!=null && document.getElementById('address1')!=null) { //address fields exist
		address += (document.getElementById('address1').value!="") ? document.getElementById('address1').value.replace(/^\s+|\s+$/g,"")+" " : "";
		address += (document.getElementById('address2').value!="") ? document.getElementById('address2').value.replace(/^\s+|\s+$/g,"")+" " : "";
		address += (document.getElementById('address3').value!="") ? document.getElementById('address3').value.replace(/^\s+|\s+$/g,"")+" " : "";
		address += (document.getElementById('address4').value!="") ? document.getElementById('address4').value.replace(/^\s+|\s+$/g,"")+" " : "";
		address += (document.getElementById('postcode').value!="") ? document.getElementById('postcode').value.replace(/^\s+|\s+$/g,"")+" " : "";
		document.getElementById('address').value = address;
		if(!marker) { // no marker exists so try an d find address
			findLocation(address);
		}
	}
	return address;
}


function findLocation(location) { // find location from any kind of data input

    if(location !="") {
		if(typeof(localSearch) !== 'undefined') {
			usePointFromPostcode(location, placeMarkerAtLonLat); 
		} 
		else
		{
			findAddress(location);
		}	
	}
}



function findAddress(address) { // use geocoder to find a specific address
    if (address == null || address =="") {
		address = makeAddress();
	}
 	if (geocoder) {
 		geocoder.getLatLng(address,
 		function(point) {
 			if (!point) {
 				//alert(address + " not found on map.\nTry simplifying address.");
 			} else {
 				placeMarkerAtLonLat(point.x,point.y);
 			}
 		});
 	} // end if geocoder
} // end func

function placeMarkerAtLonLat(lon,lat) {
	point = new GLatLng(lat,lon)
	map.setCenter(point,15);
	updateLatLong (point);
	if (marker) { // move current
		marker.setPoint(point);
	} else {
	  createDraggableMarker(point);
	}	
}





function clearMap() {
	map.clearOverlays();
	document.getElementById('address').value = "";
	document.getElementById('latitude').value = "";
	document.getElementById('longitude').value = "";
	map.setCenter(new GLatLng(markerLatitude, markerLongitude), initZoom);
	marker = null;
}

function addMarkers() {
	if(!markerLatitude) { // if no lat long initially set, try and find address
		
			findLocation();
		
	} // end no lat long
	else {
		point = new GLatLng(markerLatitude, markerLongitude);
		createDraggableMarker(point);
		

	}
}

function resetMap() { // used to reset map with Spry Tabs
	map.checkResize();
	makeAddress(); // insert new address into field
	if(marker) {
		map.setCenter(marker.getLatLng());
	}
}

function getMapStatus() {
	var ctr = map.getCenter();
document.getElementById('defaultlongitude').value  =ctr.lng();
document.getElementById('defaultlatitude').value  = ctr.lat();
document.getElementById('defaultzoom').value =map.getZoom();
}

// GOOGLE SEARCH API FUNCTIONS

function usePointFromPostcode(postcode, callbackFunc) {
	if(postcode == null || postcode == "") { // undefined so pull from html field
		postcode = document.getElementById('postcode').value;
	}
	
	if(postcode == null || postcode =="" ) { // still undefined
		postcode = makeAddress();
	}
  
  	localSearch.setSearchCompleteCallback(null, 
    function() {      
      	if (localSearch.results[0]) {    
        var resultLat = localSearch.results[0].lat;
        var resultLng = localSearch.results[0].lng;
		callbackFunc(resultLng, resultLat);
      } else {
        // do nothing
      }
    });      
  	localSearch.execute(postcode + ", UK");
}

// Google Street View

function switchToStreetView(lat,long) {
	
// put the blue overlay on the map first. If Street voew fails, then this will show.
svOverlay = new GStreetviewOverlay();
map.addOverlay(svOverlay);

var streetPos = new GLatLng(lat,long);
panoramaOptions = { latlng:streetPos };
var svPano = new GStreetviewPanorama(document.getElementById("googlemap"), panoramaOptions);
GEvent.addListener(svPano, "error", handleNoFlash); 

if(typeof(document.getElementById('streetviewclose')) != "undefined") {
	document.getElementById('streetviewclose').style.display = "block";
	}

}

function removeStreetView() { 
 window.location.reload(true);
}

function handleNoFlash(errorCode) {
  if (errorCode == 603) {
    alert("Error: Flash doesn't appear to be supported by your browser, which is required for Street View");
    return;
  }
} 

function streetViewLink() {
}

// To "subclass" the GControl, we set the prototype object to
// an instance of the GControl object
streetViewLink.prototype = new GControl();

// Creates a one DIV for each of the buttons and places them in a container
// DIV which is returned as our control element. We add the control to
// to the map container and return the element for the map class to
// position properly.
streetViewLink.prototype.initialize = function(map) {
  var container = document.createElement("div");

  var svDiv = document.createElement("div");
  this.setButtonStyle_(svDiv);
  container.appendChild(svDiv);
  svDiv.appendChild(document.createTextNode("Street View"));
  GEvent.addDomListener(svDiv, "click", function() {
    switchToStreetView(markerLatitude,markerLongitude);
  });

  map.getContainer().appendChild(container);
  return container;
}

// By default, the control will appear in the top left corner of the
// map with 7 pixels of padding.
streetViewLink.prototype.getDefaultPosition = function() {
  return new GControlPosition(G_ANCHOR_TOP_RIGHT, new GSize(310, 7));
}

// Sets the proper CSS for the given button element.
streetViewLink.prototype.setButtonStyle_ = function(button) {
  button.style.textDecoration = "none";
  button.style.color = "#000";
  button.style.backgroundColor = "white";
  button.style.font = "small Arial";
  button.style.border = "1px solid black";
  button.style.paddingBottom = "1px";
  button.style.marginBottom = "3px";
  button.style.textAlign = "center";
  button.style.width = "6em";
  button.style.cursor = "pointer";
}

/* use the following on the page init function -
map.addControl(new streetViewLink());
*/

