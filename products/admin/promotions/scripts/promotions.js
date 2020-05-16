function togglePromoCode() { 
	if(getRadioValue("promocodetype") ==0) {
		document.getElementById('promocode').value = "";
		document.getElementById('codefile').value = "";
		document.getElementById('universalcode').style.display = 'none';
		document.getElementById('individualcodes').style.display = 'none';
		document.getElementById('usergroup').style.display = 'none';
	} else if(getRadioValue("promocodetype") ==1) {
		document.getElementById('individualcodes').style.display = 'none';
		document.getElementById('universalcode').style.display = 'block';
		document.getElementById('codefile').value = "";
		document.getElementById('usergroup').style.display = 'none';
	} else if(getRadioValue("promocodetype") ==2) {
		document.getElementById('promocode').value = "";
		document.getElementById('universalcode').style.display = 'none';
		document.getElementById('individualcodes').style.display = 'block';
		document.getElementById('usergroup').style.display = 'none';

	} else {
		document.getElementById('codefile').value = "";
		document.getElementById('promocode').value = "";
		document.getElementById('universalcode').style.display = 'none';
		document.getElementById('individualcodes').style.display = 'none';
		document.getElementById('usergroup').style.display = 'block';
	}
}


function toggleAction() {
	if(document.getElementById('actiontypeID').value == 3 || document.getElementById('actiontypeID').value == 6 || document.getElementById('actiontypeID').value == 7 || document.getElementById('actiontypeID').value == 9 ) { //  cat
		document.getElementById('rowActionCategory').style.display =  "table-row";
	} else {
		document.getElementById('rowActionCategory').style.display ="none";
	}
	
	if(document.getElementById('actiontypeID').value == 4 || document.getElementById('actiontypeID').value == 5) { // manufacturer
		document.getElementById('rowActionManufacturer').style.display =  "table-row";
	} else {
		document.getElementById('rowActionManufacturer').style.display ="none";
	}
	
	if(document.getElementById('actiontypeID').value == 1 || document.getElementById('actiontypeID').value == 2 || document.getElementById('actiontypeID').value == 8) { // product
		document.getElementById('rowActionProduct').style.display =  "table-row";
	} else {
		document.getElementById('rowActionProduct').style.display = "none";
	}
}


function toggleResult() {
	if(document.getElementById('resulttypeID').value == 4 || document.getElementById('resulttypeID').value == 6 || document.getElementById('resulttypeID').value == 7) { // cat
		document.getElementById('rowResultCategory').style.display =  "table-row";
	} else {
		document.getElementById('rowResultCategory').style.display ="none";
	}
	
	if(document.getElementById('resulttypeID').value == 1 || document.getElementById('resulttypeID').value == 2) { // product
		document.getElementById('rowResultProduct').style.display =  "table-row";
	} else {
		document.getElementById('rowResultProduct').style.display = "none";
	}
}




addListener("load", init);

function init() {
	toggleResult();
	togglePromoCode();
	toggleAction();
}