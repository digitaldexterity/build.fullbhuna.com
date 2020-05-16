// JavaScript Document



function usersOnline() {
	getData("/mail/chat/ajax/usersOnline.php","selectuser","loading","");
	
}

function refreshChat(chatID, text) {
	beforeHTML = document.getElementById('chatwindow').innerHTML;
	getData("/mail/chat/ajax/refreshConversation.php?chatID="+chatID+"&text="+escape(text),"chatwindow","loading","");
	setTimeout(function(){ // focus if any changes
		afterHTML = document.getElementById('chatwindow').innerHTML;
		if(beforeHTML!=afterHTML) { this.window.focus(); }
	}, 1000);
	
}

function addUserToChat(chatID, userID, statusID) {
	getData("/mail/chat/ajax/initiatechat.php?chatID="+chatID+"&userID="+userID+"&statusID="+statusID,"chatusers","loading","");
}

function updateMyConversations() {
	getData("/mail/chat/ajax/myConversations.php","chatlist","loading","");
}


function startChat(inviteuserID) {
	MM_openBrWindow("/mail/chat/chat.php?inviteuserID="+inviteuserID,"chat","location=no,scrollbars=yes,width=400,height=500"); 
}

function openChat(chatID) {
	MM_openBrWindow("/mail/chat/chat.php?chatID="+chatID,"chat"+chatID,"location=no,scrollbars=yes,width=400,height=500"); 
}

function addResponse() {
refreshChat(document.getElementById('chatID').value, document.getElementById('chattext').value); 
document.getElementById('chattext').value='';
document.getElementById('chattext').focus();
}

function updateStatus(userID, statusID) {
	getData("/mail/chat/ajax/updateStatus.inc.php?userID="+userID+"&statusID="+statusID,"chatStatusReturnValue","loading","");
}

function keyCheck(e)

{

   var keyID = (window.event) ? event.keyCode : e.keyCode;
  if(keyID==13) {
	  addResponse();	  
  }
}