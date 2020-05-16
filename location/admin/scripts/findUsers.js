function checkLiveInput(keyEvent,targetDiv)
{
	keyEvent = (keyEvent) ? keyEvent :window.event; // Firefox or IE
	input = (keyEvent.target) ? keyEvent.target : keyEvent.srcElement; // Firefox or IE
	if(keyEvent.type == "keyup") {
		document.getElementById(targetDiv).innerHTML ="";
		if(input.value.length>2) {
		
				getData("/location/admin/ajax/ajax_users.php?locationID="+document.getElementById('ID').value+"&returnURL="+escape(document.getElementById('returnURL').value)+"&surname="+input.value,targetDiv);
				
			}
		}
}

