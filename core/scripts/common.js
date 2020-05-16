// JQUERY NOW REQUIRED BEFORE THIS LOADS 

//pop-up window

var commonjsversion = 2.0;
var spaceReplace = "-"; // this can be altered for what you might need Google PREFERS dashes to underscores

document.write("<style> .javascriptOnly { display:block; }</style>"); // show javascript reliant content - these should be hidden by previous CSS

/*** add prototype functions  ****/

if (!String.prototype.trim) {
   String.prototype.trim=function(){return this.replace(/^\s\s*/, '').replace(/\s\s*$/, '');};
}

/** QUEUE AJAX REQUESTS **/

var ajaxManager = {
    requests: [],
    addReq: function(opt) {
        this.requests.push(opt);

        if (this.requests.length == 1) {
            this.run();
        }
    },
    removeReq: function(opt) {
        if($.inArray(opt, requests) > -1)
            this.requests.splice($.inArray(opt, requests), 1);
    },
    run: function() {
        // original complete callback
        oricomplete = this.requests[0].complete;

        // override complete callback
        var ajxmgr = this;
        ajxmgr.requests[0].complete = function() {
             if (typeof oricomplete === 'function')
                oricomplete();

             ajxmgr.requests.shift();
             if (ajxmgr.requests.length > 0) {
                ajxmgr.run();
             }
        };

        $.ajax(this.requests[0]);
    },
    stop: function() {
        this.requests = [];
    },
}

/* E.G. TO USE:
$("a.button").click(function(){
       ajaxManager.addReq({
           type: 'POST',
           url: 'whatever.html',
           data: params,
           success: function(data){
              // do stuff
           }
       });
    });
*/


function MM_openBrWindow(theURL,winName,features) { // firefox 3+ IE7+ no longer allow location=false
  newWindow = window.open(theURL,winName,features);
  newWindow.focus();
}

// Populate SEO fields
function seoPopulate(title,content) {
	if (typeof content == 'undefined') var content = title; // if no content sent - just use title
	title = title.replace(/[^a-zA-Z 0-9-]+/g,''); // get rid of tags, non-alphanumeric, spaces and dashes
	content = removeHTMLTags(content);
	content = content.replace(/[^a-zA-Z 0-9/-]+/g,''); // get rid of tags, non-alphanumeric, spaces and dashes
	longID = title.replace('- ',''); // get rid of long dashes
	longID = longID.replace(/[ ]+/g,spaceReplace); // replace spaces with dashes
	if(longID.match(/(admin|articles|calendar|contact|core|documents|directory|forms|forum|furniture|help|local|location|login|mail|members|news|photos|products|requests|search|seo|surveys|terms|video)/gi)) longID += "-section";
	if (document.getElementById('longID').value == "") document.getElementById('longID').value = longID.toLowerCase();
	if (document.getElementById('metadescription').value == "") document.getElementById('metadescription').value = truncate(content,250);
	if (document.getElementById('metakeywords').value == "") document.getElementById('metakeywords').value = title;
} // end function

//Bookmark code
function addBookmark(title, url) {
	url = url.replace(/\?SearchTerm=(\d)*/,"")
	url = 'http://' + window.location.host + url
	if (window.sidebar) { // firefox
					window.sidebar.addPanel(title, url,"");
				} else if( document.all ) { //MSIE
	                window.external.AddFavorite(url,title);
				} else {
					alert("Your browser doesn't support automatic bookmarking.\n\nIn Firefox or Safari use the Bookmarks menu.");
				}
}
function addToFavourites(url,pagetitle) { 
if(confirm('Do you want to add this page to your favourites?\n\nIt will appear on the list in your Control Panel home page.')) {
getData("/core/admin/favourites/ajax/addtofavourites.php?url="+escape(url)+"&pagetitle="+escape(pagetitle),"favouritescallback");
}
return false;
}


/* This script and many more are available free online at
The JavaScript Source!! http://javascript.internet.com
Created by: Robert Nyman | http://robertnyman.com/ */
function removeHTMLTags(HTML){
 	
	
 	 	/*HTML = HTML.replace(/&(lt|gt);/g, function (strMatch, p1) {
 		 	return (p1 == "lt")? "<" : ">";
 		});*/
 		var cleaned = HTML.replace(/(<([^>]+)>)/ig," "); // replace tags with spaces
		cleaned = cleaned.replace(/^\s+|\s+$/g,'').replace(/\s+/g,' '); // get rid of extra spaces
 		return cleaned;	
   
 	
}

// Input parameters:
// String text, [Number length, String ellipsis]
// Returns:
// String text

function truncate(text, length, ellipsis) {    
// Set length and ellipsis to defaults if not defined
if (typeof length == 'undefined') var length = 100;
if (typeof ellipsis == 'undefined') var ellipsis = '...';
// Return if the text is already lower than the cutoff
    if (text.length < length) return text;    
	 // Otherwise, check if the last character is a space.   
	 // If not, keep counting down from the last character   
	 // until we find a character that is a space   
	 for (var i = length-1; text.charAt(i) != ' '; i--) {         
	 length--;
	 }
	 // The for() loop ends when it finds a space, and the length var
	 // has been updated so it doesn't cut in the middle of a word.
	 return text.substr(0, length) + ellipsis; 
	 }   
	 
function setCookie( name, value, expires, path, domain, secure )
{
// backwards compat	
path = (typeof(path)=="undefined") ? "/" : path;
domain = (typeof(path)=="undefined") ? "" : domain;
secure = (typeof(path)=="undefined") ? "" : secure;

// set time, it's in milliseconds
var today = new Date();

today.setTime( today.getTime() );

/*
if the expires variable is set, make the correct
expires time, the current script below will set
it for x number of days, to make it for hours,
delete * 24, for minutes, delete * 60 * 24
*/
if ( expires ) {
	expires = expires * 1000 * 60 * 60 * 24;
} else {
	expires = 0;
}
var expires_date = new Date( today.getTime() + (expires) );

var cookieString = name + "=" +escape( value ) +
( ( expires>0) ? ";expires=" + expires_date.toGMTString() : "" ) +
( ( path ) ? ";path=" + path : "" ) +
( ( domain ) ? ";domain=" + domain : "" ) +
( ( secure ) ? ";secure" : "" );
document.cookie = cookieString;

}



function getCookie(check_name) {
	// first we'll split this cookie up into name/value pairs
	// note: document.cookie only returns name=value, not the other components
	var a_all_cookies = document.cookie.split( ';' );
	var a_temp_cookie = '';
	var cookie_name = '';
	var cookie_value = '';
	var b_cookie_found = false; // set boolean t/f default f

	for ( i = 0; i < a_all_cookies.length; i++ )
	{
		// now we'll split apart each name=value pair
		a_temp_cookie = a_all_cookies[i].split( '=' );


		// and trim left/right whitespace while we're at it
		cookie_name = a_temp_cookie[0].replace(/^\s+|\s+$/g, '');

		// if the extracted name matches passed check_name
		if ( cookie_name == check_name )
		{
			b_cookie_found = true;
			// we need to handle case where cookie has no value but exists (no = sign, that is):
			if ( a_temp_cookie.length > 1 )
			{
				cookie_value = unescape( a_temp_cookie[1].replace(/^\s+|\s+$/g, '') );
			}
			// note that in cases where cookie is initialized but no value, null is returned
			return cookie_value;
			break;
		}
		a_temp_cookie = null;
		cookie_name = '';
	}
	if ( !b_cookie_found )
	{
		return null;
	}
}


// this deletes the cookie when called
function deleteCookie( name, path, domain ) {
	
path = (typeof(path)=="undefined") ? "/" : path;
domain = (typeof(path)=="undefined") ? "" : domain;

if ( getCookie( name ) ) document.cookie = name + "=" +
( ( path ) ? ";path=" + path : "") +
( ( domain ) ? ";domain=" + domain : "" ) +
";expires=Thu, 01-Jan-1970 00:00:01 GMT";
}


function getRadioValue(idOrName) {
        var value = null;
        var element = document.getElementById(idOrName);
        var radioGroupName = null;  
        
        // if null, then the id must be the radio group name
        if (element == null) {
                radioGroupName = idOrName;
        } else {
                radioGroupName = element.name;     
        }
        if (radioGroupName == null) {
                return null;
        }
        var radios = document.getElementsByTagName('input');
        for (var i=0; i<radios.length; i++) {
                var input = radios[ i ];    
                if (input.type == 'radio' && input.name == radioGroupName && input.checked) {                          
                        value = input.value;
                        break;
                }
        }
        return value;
}

function setSelectListToValue(value, selectId){
	var i, si, v, args=setSelectListToValue.arguments;
	if ((obj=document.getElementById(args[1])) != null){
		v = args[0];
		for(i=0; i<obj.length; i++){
			if(obj.options[i].value == v){
				si = i;
			}
		}
		obj.selectedIndex = si;
	}
}



	
function getUrlVars()
{ // returns vars[variable1name] = variable1value, vars[variable2name] = variable2value, etc...
    var vars = [], hash;
    var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
 
    for(var i = 0; i < hashes.length; i++)
    {
        hash = hashes[i].split('=');
        vars.push(hash[0]);
        vars[hash[0]] = hash[1];
    }
 
    return vars;
}



function submitAndGo(url) {
	document.getElementById('returnURL').value = url;
	document.forms[0].submit();
	return false;
}

function writeEmail(recipient, domain, prefix, suffix) { // email obfuscation routines
	prefix = prefix || "";
	suffix = suffix || "";
   email = recipient+"@"+domain;
	document.write(prefix+"<a href='mailto:"+email+"' class='safe_email'><span>"+email+"</span></a>"+suffix);
 }
 
function printFormatWindow() {
	// offers to convert all texboxes into paragraphs then prints
	
	var pageContent = document.getElementById('content').innerHTML;
	if(pageContent.indexOf("<textarea")>0) {
		if(confirm('Do you want to convert text boxes for print?\n\nScroll bars will be removed and text boxes will no longer be editable.')) {
			pageContent = pageContent.replace(/<textarea/g,"<p style='white-space:pre-wrap; height: auto;'"); 
			pageContent = pageContent.replace(/textarea/g,"p");
			document.getElementById('content').innerHTML = pageContent;
		}	
	}	
	window.print();
}

function openMainWindow(url) { // function to open the main site from Control Panel in sepcified url
	if(typeof(fb_editor_domain) !="undefined" && url.substr(0,7)!="http://") url = "http://"+fb_editor_domain+url;	
	if(typeof(mainSiteWindow)=="undefined") {
		if(top.opener && !top.opener.closed) {
			mainSiteWindow = top.opener;
		} else { 
			mainSiteWindow = window.open(url,'mainSite');
		}
	}
	mainSiteWindow.location.href = url; 
	mainSiteWindow.focus();
}

function logOutCheck() {
	message = "Are you sure you want to log out?";
	if(getCookie("stayloggedin")) {
		message +="\n\nThis will override the 'Stay logged in' checkbox.";
	}
	return confirm(message);
}

  // this function is needed to work around 
  // a bug in IE related to element attributes
  function hasClass(obj) {
     var result = false;
     if (obj.getAttributeNode("class") != null) {
         result = obj.getAttributeNode("class").value;
     }
     return result;
  }   


function getFileName() {
  //this gets the full url
  var url = document.location.href;
  //this removes the anchor at the end, if there is one
  url = url.substring(0, (url.indexOf("#") == -1) ? url.length : url.indexOf("#"));
  //this removes the query after the file name, if there is one
  url = url.substring(0, (url.indexOf("?") == -1) ? url.length : url.indexOf("?"));
  //this removes everything before the last slash in the path
  url = url.substring(url.lastIndexOf("/") + 1, url.length);
  //return
return url;
}

function insertAfter(referenceNode, newNode) { /* complements js insertBefore() */
    referenceNode.parentNode.insertBefore(newNode, referenceNode.nextSibling);
}


function goFullscreen(id) {
    // Get the element that we want to take into fullscreen mode
    var element = document.getElementById(id);
    
    // These function will not exist in the browsers that don't support fullscreen mode yet, 
    // so we'll have to check to see if they're available before calling them.
    
    if (element.requestFullScreen) {
      // This is how to go into fullscren mode in  HTML5 when supported
      element.requestFullScreen();
    } else if  (element.mozRequestFullScreen) {
      // This is how to go into fullscren mode in Firefox
      // Note the "moz" prefix, which is short for Mozilla.
      element.mozRequestFullScreen();
    } else if (element.webkitRequestFullScreen) {
      // This is how to go into fullscreen mode in Chrome and Safari
      // Both of those browsers are based on the Webkit project, hence the same prefix.
      element.webkitRequestFullScreen();
   }
   // Hooray, now we're in fullscreen mode!
  }
  
  /*** AJAX ***/
  
  // Ajax Framework for Full Bhuna by Paul Egan

// Main get and put functions...

var fbAjaxFrameworkVersion = 3;

function getData(url,divID,loadingDIV,loadingHTML, callback) // loading vars added later and optional
{
	var XMLHttpRequestObject = false;
	if (window.XMLHttpRequest) {
		XMLHttpRequestObject = new XMLHttpRequest();
	} else if (window.ActiveXObject) {
		XMLHttpRequestObject = new ActiveXObject("Microsoft.XMLHttp");
	}
	if(XMLHttpRequestObject) {
		if(typeof(divID)!=="undefined" && divID !="") {
			var obj = document.getElementById(divID);		
			loadingDIV = typeof(loadingDIV)==="undefined" ? obj : document.getElementById(loadingDIV); 
			loadingHTML = typeof(loadingHTML)==="undefined" ? "<img src='/core/images/loading_16x16.gif'  border='0' style='vertical-align: middle; width:16px; height:16px;'>" : loadingHTML;
			if(loadingDIV!="" && loadingHTML!="") { loadingDIV.innerHTML = loadingHTML; }
		}
		XMLHttpRequestObject.open("GET", url);
		XMLHttpRequestObject.onreadystatechange = function()
		{
			if (XMLHttpRequestObject.readyState == 4 && XMLHttpRequestObject.status == 200) {
				if(loadingDIV!="" && typeof(obj)!=="undefined") {
					loadingDIV.innerHTML = "";
					obj.innerHTML = XMLHttpRequestObject.responseText;
				}
			delete XMLHttpRequestObject;
			XMLHttpRequestObject = null;
			}
			if(typeof(callback)!=="undefined") {
				callback();
			}
		}
		XMLHttpRequestObject.send(null);
	}
}

function postData(url, data, divID)
{
	var XMLHttpRequestObject = false;
	if (window.XMLHttpRequest) {
	XMLHttpRequestObject = new XMLHttpRequest();
	} else if (window.ActiveXObject) {
	XMLHttpRequestObject = new ActiveXObject("Microsoft.XMLHttp");
	}
	if(XMLHttpRequestObject) {
		var obj = document.getElementById(divID);
		obj.innerHTML = "<img src='/core/images/loading_16x16.gif' border='0' style='vertical-align: middle; width:16px; height:16px;'>";
		XMLHttpRequestObject.open("POST", url);
		XMLHttpRequestObject.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
		
		XMLHttpRequestObject.onreadystatechange = function()
		{
			if (XMLHttpRequestObject.readyState == 4 && XMLHttpRequestObject.status == 200) {
			obj.innerHTML = XMLHttpRequestObject.responseText;
			delete XMLHttpRequestObject;
			XMLHttpRequestObject = null;
			}
		}
		XMLHttpRequestObject.send(data);
	}
}







// search suggest

function getXmlHttpRequestObject() {
	if (window.XMLHttpRequest) {
		return new XMLHttpRequest();
	} else if(window.ActiveXObject) {
		return new ActiveXObject("Microsoft.XMLHTTP");
	} 
}

//Our XmlHttpRequest object to get the auto suggest
var searchReq = getXmlHttpRequestObject();

//Called from keyup on the search textbox.
//Starts the AJAX request.
function searchSuggest(suggestURL) {
	if (searchReq.readyState == 4 || searchReq.readyState == 0) {
		var str = escape(document.getElementById('s').value);
		if(str.length>2) { // only do if longer than 2 chars
		document.getElementById('search_suggest').style.display = "block";
		suggestURL = suggestURL ? suggestURL : '/includes/searchSuggest.php';
		searchReq.open("GET", suggestURL+'?wordsearch=' + str, true);
		searchReq.onreadystatechange = handleSearchSuggest; 
		searchReq.send(null);
		} else {
			document.getElementById('search_suggest').innerHTML = '';
		}
	}		
}

//Called when the AJAX response is returned.
function handleSearchSuggest() {
	if (searchReq.readyState == 4) {
		var ss = document.getElementById('search_suggest')
		ss.innerHTML = '';
		
		var str = searchReq.responseText.split("\n");
		for(i=0; i < str.length - 1; i++) {
			//Build our element string.  This is cleaner using the DOM, but
			//IE doesn't support dynamically added attributes.
			var suggest = '<div onmouseover="javascript:suggestOver(this);" ';
			suggest += 'onmouseout="javascript:suggestOut(this);" ';
			suggest += 'onclick="javascript:setSearch(this.innerHTML);" ';
			suggest += 'class="suggest_link">' + str[i] + '</div>';
			ss.innerHTML += suggest;
		}
	}
}

//Mouse over function
function suggestOver(div_value) {
	div_value.className = 'suggest_link_over';
}
//Mouse out function
function suggestOut(div_value) {
	div_value.className = 'suggest_link';
}
//Click function
function setSearch(value) {
	document.getElementById('s').value = decodeHTML(value); // uses new function below to clean
	document.getElementById('search_suggest').innerHTML = '';
	document.getElementById('search_suggest').style.display = "none";
}

function addListener(type,callback,obj) // handles cross browser add listeners
{
	obj = (typeof(obj) === "undefined") ? window : obj; // backward compat as function initially has 2 args
	if(obj.addEventListener) {
		obj.addEventListener(type, callback, false); //FF
	} else if (obj.attachEvent) {
		obj.attachEvent("on" +type, callback,false); //IE
	}
}

function removeListener (type,callback, obj) // handles cross browser remove listeners
{
	obj = (typeof(obj) === "undefined") ? window : obj; // backward compat as function initially has 2 args
	if(obj.removeEventListener) {
		obj.removeEventListener(type,callback,false);
	} else if (obj.detachEvent) {
		obj.detachEvent("on"+type,callback,false);
	}
}


function decodeHTML(html){
	/* turn HTML characters into normal text, e.g. for insertion into text boxes */
	var entities=[
			['&','&'],
			[' ',' ']
		];

	var clean = html.replace(/<[^>]*>/g,"");
	for( var i=0, limit=entities.length; i < limit; ++i)
	{
		clean = clean.replace( new RegExp(entities[i][0],"ig"), entities[i][1]);
	}
return clean;
}

function phoneHome(licence, host, installdatetime) {	
	if(window.jQuery) {
		$.ajax({
			url: "https://www.digitaldexterity.co.uk/local/phonehome/index.php?licence="+escape(licence)+"&host="+escape(host)+"&installed="+escape(installdatetime),
			timeout: 5000, 
			error: function(xhr, textStatus, errorThrown){
				//alert('Sorry, there was a problem getting the App data. Please check your internet connection and try again.\n\n('+textStatus+')');
			},
			success: function(result){
        	//$("#div1").html(result);
			
				if(result!="") {
					alert(result);
				}
    		}		
		});		
	}	
}




/*** SESSION TIMEOUT ***/
// https://github.com/maxfierke/jquery-sessionTimeout-bootstrap

(function(e){jQuery.sessionTimeout=function(t){function u(t){switch(t){case"start":s=setTimeout(function(){e.each(i.closeModals,function(t,n){e("#"+n).modal("hide")});document.title=i.titleMessage;e("#sessionTimeout-dialog").modal("show");a("start")},i.warnAfter);break;case"stop":clearTimeout(s);break}}function a(e){switch(e){case"start":o=setTimeout(function(){window.location=i.redirUrl},i.redirAfter-i.warnAfter);break;case"stop":clearTimeout(o);break}}var n=[];var r={title:"Your session is about to expire!",message:"For security, you will shortly be logged out automatically if you do not respond to this message.",titleMessage:"Warning: Time Out",stayConnectedBtn:"Stay connected",logoutBtn:"Logout",closeModals:n,keepAliveUrl:"/login/ajax/keep_session_alive.ajax.php",redirUrl:"/login/logout.php?autologout=true&msg=For+security+you+have+been+automatically+logged+out+after+a+period+of+no+activity.",logoutUrl:"/login/logout.php",warnAfter:9e5,redirAfter:12e5};var i=r,s,o;if(t){i=e.extend(r,t)}e("body").append('<div class="modal fade" id="sessionTimeout-dialog">'+'<div class="modal-dialog">'+'<div class="modal-content">'+'<div class="modal-header">'+'<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>'+'<h4 class="modal-title">'+i.title+"</h4>"+"</div>"+'<div class="modal-body">'+i.message+"</div>"+'<div class="modal-footer">'+'<div class="btn-group">'+'<button id="sessionTimeout-dialog-logout" type="button" class="btn btn-danger">'+i.logoutBtn+"</button>"+'<button id="sessionTimeout-dialog-keepalive" type="button" class="btn btn-success" data-dismiss="modal">'+i.stayConnectedBtn+"</button>"+"</div>"+"</div>"+"</div>"+"</div>"+"</div>");e("#sessionTimeout-dialog-logout").on("click",function(){window.location=i.logoutUrl});e("#sessionTimeout-dialog").on("hide.bs.modal",function(){e.ajax({type:"POST",url:i.keepAliveUrl});a("stop");u("start")});u("start")}})(jQuery)


/* SEARCH PICKER BY PAUL EGAN */


function create_search_picker_select(theInputID, ajaxURL, placeholder) {	
// AVOID LABEL TAG
	$("#"+theInputID).addClass("search_picker_id");
	$("#"+theInputID).css("left","-999em"); // hide input so still submits
	$("#"+theInputID).wrap("<div class='search_picker_wrapper form-inline' data-search-picker-id='"+theInputID+"'></div>" );
	$("#"+theInputID).after('<input type="hidden" class="search_picker_initial_val" id="search_picker_initial_val_'+theInputID+'" value="'+$("#"+theInputID).val()+'"><input type="text" maxlength="50" size="50" placeholder="'+placeholder+'" class="search_picker_textbox form-control" id="search_picker_textbox_'+theInputID+'" autocomplete="off"><span class="search_picker_selected_text"></span><div class="search_picker_list"></div>');
	$("#search_picker_initial_val_"+theInputID).val($("#"+theInputID).val());
	$("#search_picker_textbox_"+theInputID).keydown(function(e) {
		// detect up/down and enter for submit TO DO!
		// 13 - return
		// 27 - escape
		// 38 - up
		// 40 - down
		
		if(e.which == 27) {
			search_picker_reset(theInputID);
		}
		if(e.which == 40) {
			$(this).parents('.search_picker_wrapper').find('.search_picker_list').css("background","red");
		}
	});
	
	$("#search_picker_textbox_"+theInputID).keyup(function() {		
		var text_input = $(this);
		var text_input_value = 	$(this).val();	
		if(text_input_value.length>2) {
			$.get(ajaxURL,{ search: text_input_value}, function(data, status){
        		text_input.parents('.search_picker_wrapper').find('.search_picker_list').html(data);
    		});
		} else {
			text_input.parents('.search_picker_wrapper').find('.search_picker_list').html("");
		}
	});	
}

function search_picker_select(theAnchor, callbackFn) {
	thePickerWrapper = $(theAnchor).parents('.search_picker_wrapper');
	selectedID = $(theAnchor).attr("data-id");
	thePickerWrapper.find(".search_picker_id").val(selectedID);
	theInputID = thePickerWrapper.find(".search_picker_id").attr("id");
	var search_picker_selected_text = $(theAnchor).html() + " <a onclick='search_picker_reset(\""+theInputID+"\")'><span class='glyphicon glyphicon-remove'></span></a>";
	thePickerWrapper.find(".search_picker_selected_text").html(search_picker_selected_text);
	thePickerWrapper.find(".search_picker_textbox").hide();
	thePickerWrapper.find(".search_picker_textbox").val('');
	thePickerWrapper.find(".search_picker_list").html('');
	// ??? search_picker_close(thePickerWrapper);
	/** EXTENSION CALLBACK FOR THIS PAGE **/
	//selectCallback
	if (typeof callbackFn !== 'undefined') { 
		callbackFn(selectedID);
	}	
}


function search_picker_reset(theInputID) {
	/* resets all - but this is probably OK */
	$(".search_picker_selected_text").html("");
	$(".search_picker_list").html('');
	$(".search_picker_textbox").val('');
	$(".search_picker_textbox").show();
	
	// revert value back to what it was when created	
	$("#"+theInputID).val($("#search_picker_initial_val_"+theInputID).val());
	
}


function copyToClipboard(inputElement) {
	/* any text input element but not type hidden */
	inputElement.select(); 
 	inputElement.setSelectionRange(0, 99999); /*For mobile devices*/

 	/* Copy the text inside the text field */
  	document.execCommand("copy");

  	/* Alert the copied text */
  	alert("Copied the text: " + inputElement.value);
	
}

