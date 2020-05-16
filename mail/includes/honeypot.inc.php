<?php // create honeypot fields on compatible pages to fight spam!
if(!defined("HONEYPOT_FIELD")) define("HONEYPOT_FIELD", "message"); // usual
/********
$_SESSION['honeypot_field'] = name of secret honeypot field
$_SESSION['honeypot_swap'] = random name generated for real field that needs to be swapped back
$_SESSION['honeypot_token'] = unique session key so form in present state can only be posted once
********/
if (!isset($_SESSION)) {
  session_start();
}
if(isset($_SESSION['honeypot_field']) && isset($_SESSION['honeypot_swap']) && isset($_POST[$_SESSION['honeypot_swap']])) {
	

	// active and posted	
	if($_POST['hp_token'] !=$_SESSION['honeypot_token']) {
		header('HTTP/1.0 403 Forbidden', true, 403);
		die("Incorrect token.");
	}
	if($_POST[$_SESSION['honeypot_field']] !="") {
		// trap!
		header('HTTP/1.0 403 Forbidden', true, 403);
		die("Spam detected. Access forbidden.");
	}
	// or reinstate correct post fields
	$_POST[$_SESSION['honeypot_field']] = $_POST[$_SESSION['honeypot_swap']];
	// delete Honeypot posts so as not to appear in messages
	unset($_POST[$_SESSION['honeypot_swap']]);	
	if(isset($_POST['hp_token'])) unset($_POST['hp_token']);	
	$_SESSION['honeypot_field'] = HONEYPOT_FIELD;
	$_SESSION['honeypot_swap'] = HONEYPOT_FIELD."_".rand();
	$_SESSION['honeypot_token'] = md5(PRIVATE_KEY.rand());
}

// done this way as ajax call could potetially reset 
if(!isset($_SESSION['honeypot_field'])) {
	$_SESSION['honeypot_field'] = HONEYPOT_FIELD;
	$_SESSION['honeypot_swap'] = HONEYPOT_FIELD."_".rand();
	$_SESSION['honeypot_token'] = md5(PRIVATE_KEY.rand());
}
?>