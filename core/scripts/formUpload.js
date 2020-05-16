/*
extend the form validation using a validateForm() routine that returns an error if not OK

TO DO rather than inclde script add to common and trigger on from class */


$(document).ready(function() { 
	$("body").append('<span class="form-uploading" style="display:none">Processing. Please wait... <button type="button" class="btn btn-default" onclick="stopSubmit();" >Cancel</button></span>'); 
	$("form").on('submit', function(e){	
		if (!document.getElementById("submit")) { // avoid submit button with id = "submit" bug 
			e.preventDefault(); //Prevent the normal submission action
			theForm = this;			
			var SpryValidated= true;
			var fb_errors = '';
			if(typeof(validateForm) != "undefined") {
				fb_errors = validateForm();
			}
			if(typeof(Spry) !="undefined" && typeof(Spry.Widget.Form) !="undefined") {				
				SpryValidated=Spry.Widget.Form.validate(theForm); 			
			}	
			if(SpryValidated && fb_errors=='') { // no errors					
				$(theForm).find("button[type='submit']").prop('disabled', true);				
				$(".form-uploading").addClass("show"); 
				theForm.submit();
					
			} else {  // errors		
				alertText ="";
				alertText += (fb_errors) ? fb_errors+"\n" : "";
				alertText += (!SpryValidated) ? "There are highlighted problems on the page.\n\n" : "";
				alert(alertText+"Please review before submitting. Any changes have not yet been saved.");		
			}	
		}
	});	
});
 
 


 
 
function stopSubmit() {
	if (navigator.appVersion.indexOf("MSIE") != -1) {
		 document.execCommand("Stop");
 	} else {
		 window.stop();	 
	}
	$("input[type='submit'], button[type='submit']").prop('disabled', false);
   	$(".form-uploading").removeClass("show"); 
}
 
 
 
 

