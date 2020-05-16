  /**** formUpload MUST go before this BUT THIS must go BEFORE timyMCE - which alters fields *******/
  var values = new Array();
  var formSubmitted = false;
  var exitMessage = 'Unsaved changes have been made to this page.';
  
  addListener("load", saveInit);
  
  
  window.onbeforeunload = confirmExit;
  
  function saveInit() {
    // assign the default values to the items in the values array
	if(tinyMCE) { tinyMCE.triggerSave(); } // makes sure editors are reset both on enter and exit
	
	for (var count = 0; count < document.forms.length; count ++) {
		
		
    for (var i = 0; i < document.forms[count].length; i++)
    {
      var elem = document.forms[count].elements[i]; 
      if (elem)
	  	if (elem.type == 'submit' || elem.type == 'button') {
			
			addListener("click", function() { formSubmitted = true;  }, elem);
		}
        if (elem.type == 'checkbox' || elem.type == 'radio')
          values[i] = elem.checked;
        else
          values[i] = elem.value;
    }  // end elements
	} // end forms
  }
  
  



  function confirmExit()
  { 
  	if(tinyMCE) { tinyMCE.triggerSave(); } // makes sure editors are reset both on enter and exit
	
    if (!formSubmitted)
    {
		for (var count = 0; count < document.forms.length; count ++) 
		{
			for (var i = 0; i < document.forms[count].length; i++)
			{
				var elem = document.forms[count].elements[i];
				if (elem) {
				
				  if ((elem.type == 'checkbox' || elem.type == 'radio') && values[i] != elem.checked)
				  {
					  
						return exitMessage;
				  }
				  else if ((elem.type == 'select' || elem.type == 'text'  || elem.tagName == 'TEXTAREA') && elem.value != values[i]) {
							
				  		return exitMessage;
					  }
				  }
			  }
		  }

     // return nothing
    }
	formSubmitted = false;
  }