// Replace input field userID with selector

var userselectorfield = "#userID"; // backwards compatible
var showMiddleName = false;
var fuzzySearch = false;


$(document).ready(function(){
	if($(userselectorfield).val()<1) {// not set
		$(userselectorfield).hide(0,function(){ // if not hidden already
			addLiveFields($(this))	;	
			
		}); 
	}
	
	
});

function addLiveFields(selectedfield) {
	
	formHTML = '<input class="livecheck" name = "livefirstname"  id = "livefirstname" placeholder="First name" type = "text">';
	formHTML += (showMiddleName) ? '<input class="middlename livecheck" name = "livemiddlename"  id = "livemiddlename" placeholder="Middle name" type = "text">' : '';
	formHTML += '<input class="livecheck" id="livesurname" name = "livesurname" type = "text" placeholder="Surname"><span id="chooseuser-fullname"></span><div id="userresults"></div>';
	
	
	
	
	$("#chooseuser-fullname").html("");
	$(selectedfield).val("");
	$(selectedfield).after(formHTML);
	submitButton = $(this).parent().find("input[type=submit]");
	submitButton.hide();
	$(".livecheck").keyup(function() {
		finduser($("#livefirstname").val(),$("#livesurname").val(), selectedfield)
	});
}


function finduser(firstname, surname, inputfield) {
	if($("#livesurname").val() !="Surname" && $("#livesurname").val().length>1) {
		$.ajax({
			url:"/core/ajax/chooseuser.ajax.php",
			data: {"finduser" : true,  "firstname" : firstname, "surname": surname,  "fuzzy": fuzzySearch, "callerURL": document.location.href },
			success:function(result){
				$("#userresults").html(result);
				$("a.selectuser").click(function(){
					var userID = $(this).attr("userID");
					var fullname = $(this).attr("fullname");
					$(inputfield).val(userID);
					$(".livecheck").hide();
					$("#userresults").html("");
					$("#chooseuser-fullname").html(userID+" "+fullname+" <a>[X]</a>");
					$("#chooseuser-fullname a").click(function(){							
							addLiveFields(inputfield);
					});
					submitButton.show();
					if ($("#email").length > 0){
  						$(".email").show();
						$("#email").val($(this).attr("email"));
					}
					if ($("#mobile").length > 0){
  						$(".mobile").show();
						$("#mobile").val($(this).attr("mobile"));
					}
				});
			}
		});
	}	
}
