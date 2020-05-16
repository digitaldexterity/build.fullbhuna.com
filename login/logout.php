<?php


// do a soft log out
require_once('../core/includes/framework.inc.php'); 
require_once('includes/logout.inc.php');




$_SESSION['PrevUrl'] = "";
unset($_SESSION['PrevUrl']);

$_SESSION['survey_session'] = "";
unset($_SESSION['survey_session']);


if(isset($_GET['fulllogout'])) {
	// DO A HARD LOG OUT.,,,

	$_SESSION = array();

	if(function_exists("session_regenerate_id")) {
		if (defined('PHP_VERSION') && PHP_VERSION >= 5.1) {
			session_regenerate_id(true);
		} else {
			session_regenerate_id();
		}
	}
	
	
	// If it's desired to kill the session, also delete the session cookie.
	// Note: This will destroy the session, and not just the session data!
	if (isset($_COOKIE[session_name()])) {
		$secure = getProtocol()=="https" ? true : false;	
		setcookie(session_name(), '', time()-42000, '/','',$secure, true);
	}
	
	// Finally, destroy the session.
	@session_destroy();

}

$redirectURL = isset($_GET['returnURL']) ? $_GET['returnURL'] : "index.php";
$redirectURL .= strpos($redirectURL,"?")>0 ? "&" : "?";
$redirectURL .= "loggedout=true";

if(isset($_GET['autologout']) && isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER']!="") {
	$redirectURL .= "&accesscheck=".urlencode($_SERVER['HTTP_REFERER']);
}

$redirectURL .= (isset($_SERVER['QUERY_STRING']) && trim($_SERVER['QUERY_STRING'])!="") ? "&".$_SERVER['QUERY_STRING'] : ""; 


echo "<!doctype html>\n<html lang=\"en\">\n<html><head>\n<meta charset=\"utf-8\" /><meta name=\"robots\" content=\"noindex,nofollow\"/><meta http-equiv=\"refresh\" content=\"0;URL=".htmlentities($redirectURL, ENT_COMPAT, "UTF-8")."\" /><title>Logging Out...</title><link href=\"/css/layout.css\" rel=\"stylesheet\" type=\"text/css\" /><link href=\"/login/css/defaultLogin.css\" rel=\"stylesheet\" type=\"text/css\" /></head><body><p class=\"loginmessage\"><a href=\"".htmlentities($redirectURL, ENT_COMPAT, "UTF-8")."\" title = \"Logging out... If your browser does not redirect click here.\" >Logging out</a></p></body></html>";exit;
?>