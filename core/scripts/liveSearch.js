// just include this file and add link to a server handling script

var liveSearchReq = getXmlHttpRequestObject();
var liveSearchFieldID = 'search';
var liveSearchURL = ""; // IMPORTANT - fill in handler AJAX server script  url
var liveSearchDIV = "liveSearchDIV"; // enter results DIV ID

function liveSearchInit() {
	addListener("keyup", liveSearch, document.getElementById(liveSearchFieldID));
}

function liveSearch() { 
	if (liveSearchReq.readyState == 4 || liveSearchReq.readyState == 0) {
		var str = escape(document.getElementById(liveSearchFieldID).value);
		
		if(str.length>2) { // only do if longer than 2 chars	
			// get other form varaibles
			thisForm = document.getElementById(liveSearchFieldID).form.elements;
			query = "";
			for(z=0; z<thisForm.length;z++) {			
				if(thisForm[z].tagName == 'SELECT' || thisForm[z].type == 'hidden' || thisForm[z].type == 'checkbox' || thisForm[z].type == 'text' ) {
					query += query.indexOf("?")>=0 ? "&" : "?";
					query += thisForm[z].name+"="+escape(thisForm[z].value);
				}
			}
			
			liveSearchReq.open("GET",liveSearchURL+query, true);
			liveSearchReq.onreadystatechange = handleLiveSearch; 
			liveSearchReq.send(null);
		} 
	}		
}


function handleLiveSearch() {
	if (liveSearchReq.readyState == 4) {	
		document.getElementById(liveSearchDIV).innerHTML = liveSearchReq.responseText;	
		if(typeof(checkboxInit) !=="undefined") { // update checkbox total if used
			checkboxInit();
		}
	}
}


addListener("load", liveSearchInit);
