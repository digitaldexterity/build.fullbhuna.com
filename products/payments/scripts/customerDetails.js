function checkCustomerDetails() {
	errors = "";
	if(document.getElementById("shippingrateID") && document.getElementById("shippingrateID").value == ""  && document.getElementById("IsDeliverySame").value !=2) {
		errors +="Please choose a shipping rate.\n";
		$("#shippingrateID").addClass("error");
	}
	if(document.getElementById("checkouttermsagree") && document.getElementById("checkouttermsagree").value == 1 && !document.getElementById("termsagree").checked) {
		errors +="You must agree to the site terms and conditions to proceed.\n";
		$(".terms").addClass("error");
	}
	if(errors=="") {
		return true;
	} else {
		alert(errors);
		return false;
	}
}
// run when checkbox is clicked to synchronise the delivery details with billing details
function IsDeliverySame_clicked() {

    if (getRadioValue("IsDeliverySame")==0) { // DIFFERENT ADDRESS
	
	$("#deliveryDetails").slideDown();
        document.customerform.DeliveryFirstnames.disabled = false;
         $("#DeliveryFirstnames").removeClass("inputBoxDisable").addClass("inputBoxEnable");        
        document.customerform.DeliverySurname.disabled = false;
         $("#DeliverySurname").removeClass("inputBoxDisable").addClass("inputBoxEnable");
        document.customerform.DeliveryAddress1.disabled = false;
        $("#DeliveryAddress1").removeClass("inputBoxDisable").addClass("inputBoxEnable");
        document.customerform.DeliveryAddress2.disabled = false;
        $("#DeliveryAddress2").removeClass("inputBoxDisable").addClass("inputBoxEnable");
        document.customerform.DeliveryCity.disabled = false;
         $("#DeliveryCity").removeClass("inputBoxDisable").addClass("inputBoxEnable");
        document.customerform.DeliveryPostCode.disabled = false;
         $("#DeliveryPostCode").removeClass("inputBoxDisable").addClass("inputBoxEnable");
        //document.customerform.DeliveryCountry.disabled = false;
        //document.customerform.DeliveryCountry.removeClass("inputBoxDisable").addClass("inputBoxEnable");
        document.customerform.DeliveryState.disabled = false;
         $("#DeliveryState").removeClass("inputBoxDisable").addClass("inputBoxEnable");
        document.customerform.DeliveryPhone.disabled = false;
         $("#DeliveryPhone").removeClass("inputBoxDisable").addClass("inputBoxEnable");
		
		document.customerform.DeliveryFirstnames.focus();
		//$(".delivery").show();
		
	

       
    } 
    else // delivery same or collection
    {
		
		if(getRadioValue("IsDeliverySame")==2) { // will collect
			$(".delivery").hide();
		} 
		
		
		 document.customerform.DeliveryFirstnames.value = "";
         $("#DeliveryFirstnames").removeClass("inputBoxEnable").addClass("inputBoxDisable");
        document.customerform.DeliveryFirstnames.disabled = true;
        
        document.customerform.DeliverySurname.value = "";
         $("#DeliverySurname").removeClass("inputBoxEnable").addClass("inputBoxDisable");
        document.customerform.DeliverySurname.disabled = true;
        
        document.customerform.DeliveryAddress1.value = "";
         $("#DeliveryAddress1").removeClass("inputBoxEnable").addClass("inputBoxDisable");
        document.customerform.DeliveryAddress1.disabled = true;

        document.customerform.DeliveryAddress2.value = "";
         $("#DeliveryAddress2").removeClass("inputBoxEnable").addClass("inputBoxDisable");
        document.customerform.DeliveryAddress2.disabled = true; 

        document.customerform.DeliveryCity.value = "";
         $("#DeliveryCity").removeClass("inputBoxEnable").addClass("inputBoxDisable");
        document.customerform.DeliveryCity.disabled = true;

        document.customerform.DeliveryPostCode.value = "";
         $("#DeliveryPostCode").removeClass("inputBoxEnable").addClass("inputBoxDisable");
        document.customerform.DeliveryPostCode.disabled = true;

        //document.customerform.DeliveryCountry.value = "";
       //document.customerform.DeliveryCountry.removeClass("inputBoxEnable").addClass("inputBoxDisable");
        //document.customerform.DeliveryCountry.disabled = true;

        document.customerform.DeliveryState.value = "";
         $("#DeliveryState").removeClass("inputBoxEnable").addClass("inputBoxDisable");
        document.customerform.DeliveryState.disabled = true;

        document.customerform.DeliveryPhone.value = "";
         $("#DeliveryPhone").removeClass("inputBoxEnable").addClass("inputBoxDisable");
        document.customerform.DeliveryPhone.disabled = true;
		
		$("#deliveryDetails").slideUp();
		
		
		
    }
}

function prepopulateAddress(addressType) {
		selectID = addressType+"AddressID";
		addressFields = document.getElementById(selectID).value.split(":@");
		names = addressFields[0].split(" ");
		document.getElementById(addressType+"Firstnames").value = names[0];
		document.getElementById(addressType+"Surname").value = (names.length>1)? names[1] : "";
		document.getElementById(addressType+"Address1").value = addressFields[1];
		document.getElementById(addressType+"Address2").value = addressFields[2];
		document.getElementById(addressType+"City").value = addressFields[3];
		document.getElementById(addressType+"PostCode").value = addressFields[6];
		document.getElementById(addressType+"State").value = addressFields[5];
		document.getElementById(addressType+"Phone").value = addressFields[8];
		setSelectListToValue(addressFields[7], addressType+"Country");
	}
	
	
function submitForm(formName, navigateValue) {
	if (navigateValue != null && navigateValue != "") {
		document.forms[formName].navigate.value = navigateValue;
	}
	document.forms[formName].submit();
}
	
function toggleDeliveryInstructions() {
	if(document.getElementById('showdeliveryinstructions').checked) {
		$('#deliveryinstructionsbox').slideDown();
	} else {
		$('#deliveryinstructionsbox').hide();
	}
}



function toggleLogin() {
	if($('input[name=returning]:checked').val()==1) {
		$('.login').show();
		$('.nologin').hide();
	} else {
		$('.login').hide();
		$('.nologin').show();
	}
}

function toggleBillingState() {
	if($("#BillingCountry").val()==242) {
		$(".billingState").show();
	} else {
		$(".billingState").hide();
	}
}
function toggleDeliveryState() {
	if($("#DeliveryCountry").val()==242) {
		$(".deliveryState").show();
	} else {
		$(".deliveryState").hide();
	}
		
}
