addListener("load",init);

function init() {
	addListener("click",toggleHeadline,document.getElementById("headline"));
}

function toggleHeadline() {
	if(this.checked) { // can't intercept click here, so use reversal technique
		this.checked = confirm('Checking this box will make this news story appear at the top of the listings.\n\nThis will also replace any current headline.');
	}	
}

function validateForm(form) {
	var errors = '';
	if (document.getElementById('title').value == "" ) errors += "Please enter a news title\n";
	return errors;
}
 
 