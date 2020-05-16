// JavaScript Document


addListener("load",init);

function init() 
{
	toggleEmailGroups();
	
	getData("/login/ajax/checkemail.php?email="+document.getElementById('fb-signup-email').value,'emailAlert');
	checkLiveInput('keyup','emailAlert','email');
	addListener("keyup",checkUnique,document.getElementById('fb-signup-email'));	
	addListener("click",toggleEmailGroups,document.getElementById('emailoptin'));
	
}

function usePointFromPostcode(postcode) {
  
  localSearch.setSearchCompleteCallback(null, 
    function() {
      
      if (localSearch.results[0]) {    
        var resultLat = localSearch.results[0].lat;
        var resultLng = localSearch.results[0].lng;
		document.getElementById('latitude').value = resultLat;		
		document.getElementById('longitude').value = resultLng;
      }else{
       // do nowt
      }
    });  
    
  localSearch.execute(postcode + ", UK");
}


function checkUnique(event) {
	if(document.getElementById('usertypeID').value>=0) {
		checkLiveInput(event,'emailAlert','email');
	}
}

function validateForm() { 
 var errors = "";
 if(document.getElementById('userscanlogin').value == 1 && document.getElementById('autousername').value !=1) {
 if (document.getElementById('username').value == "" && document.getElementById('emailasusername').value ==0) errors = errors + "Please enter a username.\n";
  if(!document.getElementById('username').value.match(/^[a-zA-Z0-9_]+$/) && document.getElementById('emailasusername').value ==0) errors = errors + "Your username must only contain alphanumeric characters, i.e. 0-9, a-z, A-Z, and no spaces.\n";
  if(!document.getElementById('fb-password-field').value.match(/^[a-zA-Z0-9_]+$/)) errors = errors + "Your password must only contain alphanumeric characters, i.e. 0-9, a-z, A-Z, and no spaces.\n";
if (document.getElementById('fb-password-field').value == "") errors = errors + "Please enter a password.\n"; else if(document.getElementById('fb-password-field').value < 6) errors = errors + "For security reasons, your password must be at least six characters in length.\n";
  if (document.getElementById('fb-password-field').value != document.getElementById('fb-password-field2').value) errors = errors + "The two passwords you entered do not match.\n";
  } // end not auto username
   if (!(document.getElementById('fb-signup-email').value.indexOf('@')>0 && document.getElementById('fb-signup-email').value.indexOf('.')>0)) errors = errors + "A valid email address is required. If you do not have one, please use a friend or colleague's for now.\n";
   if (!document.getElementById('termsagree').checked) errors = errors + "You cannot sign up unless you agree to the terms and conditions and check the appropriate check box.\n";

  return errors;
 
}

function toggleEmailGroups() {
	if(document.getElementById('optingroups')) {
	if((document.getElementById('emailoptin') && document.getElementById('emailoptin').checked) || (document.getElementById('emailoptin_1') && document.getElementById('emailoptin_1').checked)) {
		document.getElementById('optingroups').style.display = 'block';
	} else {
		document.getElementById('optingroups').style.display = 'none';
	}
	}
}

