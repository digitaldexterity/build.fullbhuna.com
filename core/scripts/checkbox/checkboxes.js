// JavaScript Document

// IMPORTANT checkboxes should be in format name = "checkboxname[n]" id="checkboxnamen" where checkboxname can be anything and MUST have a value = ID

// the check all box MUST be called checkAll 

// suggested on page (<span id="checkedCount"></span> selected)

/* if using session include after this:
<?php require_once('/javascript/checkboxsession.inc.php'); ?>
*/

var checkboxForm = 'form1'; // we assume this is the ID

var useCheckboxSession = false; // this is changes to true in session file.

var totalChecked = 0; // global

var preChecked = new Array();

var rootDrectory = ""; // for ADS we must make this /ads


addListener("load", checkboxInit);

function checkboxInit() {
	if(document.getElementById(checkboxForm)) {
		totalChecked = countCheckedOnPage(); // do this first to get listeners added
		if(useCheckboxSession) { // we have session so use this as count instead
			totalChecked = preChecked.length;
			for(z=0; z<preChecked.length;z++){ 
				if(typeof(document.getElementById(preChecked[z])) !== "undefined" && document.getElementById(preChecked[z]) !== null) { 	
				
					document.getElementById(preChecked[z]).checked = true;
				}
			}
		} 
		displayCheckedCount(totalChecked);
	}
}
	




function displayCheckedCount(count) {
	if(document.getElementById('checkedCount') != null) {
		document.getElementById('checkedCount').innerHTML = count;
	}
}

function countCheckedOnPage() {	
	var theForm = document.getElementById(checkboxForm);
	z = 0, count = 0;
	for(z=0; z<theForm.length;z++){
      if(theForm[z].type == 'checkbox' && theForm[z].name != 'checkAll') {
		  removeListener ("click",function(){}, theForm[z]) ;
		  addListener("click",toggleCheckbox ,theForm[z]);
		  if(theForm[z].checked) {
	  		count ++;
		  }
	  }
    }
	return count;
}


function toggleCheckbox(e) {
	if (!e) var e = window.event; // moz or ie
	e.target = e.target || e.srcElement;  // moz or ie
	totalChecked += (e.target.checked) ? 1 : -1;
	thisValue = (e.target.checked) ? e.target.value : -1;
	if(useCheckboxSession) {
		getData(rootDrectory+"/core/scripts/checkbox/checkboxsession.ajax.php?checkboxID="+e.target.id+"&value="+thisValue);
		//alert(rootDrectory+"/core/scripts/checkbox/checkboxsession.ajax.php?checkboxID="+e.target.id+"&value="+thisValue);
	}
	if(document.getElementById('checkAll')) { // check all box existis
		if(e.target.checked && !document.getElementById('checkAll').checked) {
			//document.getElementById('checkAll').checked = true;
		} else if(!e.target.checked && document.getElementById('checkAll').checked) {
			document.getElementById('checkAll').checked = false;
		}
	
	}
	displayCheckedCount(totalChecked);
}


function checkUncheckAll(theElement) {
	
	
	
     var theForm = theElement.form, z = 0;	 

	 for(z=0; z<theForm.length;z++){
      	if(theForm[z].type == 'checkbox' && theForm[z].id != theElement.id){
			
			if(theElement.checked && !theForm[z].checked) {
				totalChecked ++;
				
			}	
		theForm[z].checked = theElement.checked;
		thisValue = (theForm[z].checked) ? theForm[z].value : -1;
		theCheckbox = theElement.checked ? theForm[z].id : 0;
		if(useCheckboxSession) {
			if(!theElement.checked) {
				preChecked.length = 0;
			}
			getData(rootDrectory+"/core/scripts/checkbox/checkboxsession.ajax.php?checkboxID="+theCheckbox+"&value="+thisValue);
		}
	  }
    }
	if(!theElement.checked) {
		totalChecked = 0;
	}
	displayCheckedCount(totalChecked);
}

// will check if any of the checkboxes in specified form object are selected
function anyChecked(form) {
	var theForm = form, z = 0, numChecked=0;;
	 for(z=0; z<theForm.length;z++){
      if(theForm[z].type == 'checkbox' && theForm[z].checked === true){
	   numChecked ++;
	  }
     }
	 if(numChecked >0) {
		
		 return true;
	 } else {
		 alert("Please select checkbox(es) on current page.");
		 return false;
	 }
}
	

