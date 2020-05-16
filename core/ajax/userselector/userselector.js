// User select

// just drop this into head and add class "userIdSelector" to any text userID unput field to convert into live name seach
// requires common.js
// basic styles in /core/styles/userselector.css

 addListener("load", initUserSelector);
 
 function initUserSelector() {
	 num_forms = document.forms.length;
	 for(i=0; i<num_forms; i++) {
		 thisForm = document.forms[i];
		 num_elements = thisForm.length;
		 for(z=0; z<=num_elements; z++) {
			 if(thisForm[z].className == "userIdSelector") {
				 inputID = thisForm[z].id;
				 thisForm[z].style.display = 'none';
				 theHTML = thisForm[z].parentNode.innerHTML;
				 theHTML +="<div id='"+inputID+"_wrapper' class='userIdSelectorWrapper'><input type='text' id='"+inputID+"_ajax' onkeyup='liveSearch(inputID)' onfocus='usersearchfocus("+inputID+")' onblur='usersearchblur("+inputID+")' value='Search by surname...'   maxlength='100'><div id='"+inputID+"_ajax_results' class='userIdSelectorResults'></div></div>";
				 thisForm[z].parentNode.innerHTML = theHTML;
				 
			 }
		 }
	 }
 }
 
 function liveSearch(inputID) {
	 searchterm = document.getElementById(inputID+"_ajax").value;
	 if(searchterm.length>2) {
		 url = "/core/ajax/userselector/livesearch.php?inputID="+escape(inputID)+"&search="+escape(searchterm);
		 getData(url,inputID+"_ajax_results",inputID+"_ajax_results","");
	 } else {
		 document.getElementById(inputID+"_ajax_results").innerHTML = "";
	 }
 }
 
 function selectuserID(inputID, userID, name) {
	 document.getElementById(inputID).value = userID;
	 document.getElementById(inputID+"_ajax").value = name;
	 document.getElementById(inputID+"_ajax_results").innerHTML = "";
 }
 
 function usersearchfocus(id) {
	 if(document.getElementById(id).value=='Search by surname...') {
		 document.getElementById(id).value="";
	 }
 }
 
 function usersearchblur(id) {
	 if(document.getElementById(id).value=='') {
		 document.getElementById(id).value="Search by surname...";
		 document.getElementById(inputID).value = '';
	 }
 }