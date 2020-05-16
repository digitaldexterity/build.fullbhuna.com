function checkLiveInput(keyEvent,targetDiv,checkType)
{
	keyEvent = (keyEvent) ? keyEvent :window.event; // Firefox or IE
	input = (keyEvent.target) ? keyEvent.target : keyEvent.srcElement; // Firefox or IE
	if(keyEvent.type == "keyup") {
		document.getElementById(targetDiv).innerHTML ="";
		if(input.value) {
			if((checkType == "username") && (input.value.length < 6 || input.value.length >20 || input.value.indexOf(' ')>=0)) {
					document.getElementById(targetDiv).innerHTML ="(6 or more letters/numbers, no spaces)";
				} 
				else if (checkType == "password") {
					document.getElementById(targetDiv).innerHTML = checkPasswordStrength(input.value);
				}
				else if (checkType == "password2" && input.value != document.getElementById('password').value)  {
					document.getElementById(targetDiv).innerHTML ="(Must match password above)";
				}
				else if (checkType == "password2") {
					document.getElementById(targetDiv).innerHTML ='<img src="/core/images/icons/tick-green.png" alt="Tick" style="vertical-align:middle; width:16px; height:16px;" >';
				}
				else if (checkType == "email" && input.value.length >7) {
					getData("/login/ajax/checkemail.php?email="+input.value,targetDiv,targetDiv,"");
				} else {
					if (checkType == "username") getData("/login/ajax/checkusername.php?qu="+input.value,targetDiv);
				}
			}
		}
}

function checkPasswordStrength(password) {
	score = 0;
	strength = "(6 to 20 characters, no spaces)";
	score += (password.length>=6); // greater than 6 characters
	score += (password.length>10); // greater than 10 characters
	if(password.match(/[0-9]/)) score++; // contains digits
	if(password.match(/[a-z]/) && password.match(/[A-Z]/)) score++; // contains both upper and lowercase
	if(password.match(/[^0-9a-zA-Z ]/)) score++;	// contains non-alphanumeric
	if(password.indexOf(' ')>=0 || password.length<6) score = 0; // invalid
	if(score==1 || score ==2) { strength = "Weak, we suggest uppercase, lowercase & numbers"; }
	else if (score==3) { strength = "Medium"; }
	else if (score==4) { strength = "Strong"; }
	else if (score==5) { strength = "Very strong"; }
	strength = (score > 0) ? '<img src="/core/images/icons/tick-green.png" alt="Tick" style="vertical-align:middle;width:16px; height:16px;"> ('+strength+')': strength;
	return strength;
}