<?php require_once('framework.inc.php'); ?>
<?php // MUST BE PLACED BEFORE HEAD TAG BUT ONLY IN MAIN TEMPLATE AS CANNOT BE CALLED VIA OTHER AJAX CALLS ETC
if(!function_exists("setToken")) {
function setToken($tokenname="CSRFtoken") {	
	global $console;
	$token = bin2hex(openssl_random_pseudo_bytes(16));
	$secure = (getProtocol()=="https") ? true : false;
	$cookenameprepend = $secure ? "__Secure-" : "";		
	setcookie($cookenameprepend.$tokenname, $token, time() + 60 * 60 * 24, "/","",$secure,true);
	$console .= "SET COOKIE: ".$cookenameprepend.$tokenname."=".$token."\n";		
	return $token;
}
}

if(!function_exists("getToken")) {
function getToken($tokenname="CSRFtoken") {
	$secure = (getProtocol()=="https") ? true : false;
	$cookenameprepend = $secure ? "__Secure-" : "";		
	$token = isset($_COOKIE[$cookenameprepend.$tokenname]) ? $_COOKIE[$cookenameprepend.$tokenname] : false;
    return $token;
}
}

$CSRFtoken = setToken();

/** EXAMPLE TEST ON RECIPIENT PAGE


// basic token security
if(isset($_REQUEST['s'])) {
	if(!isset($_REQUEST['searchtoken']) || $_REQUEST['searchtoken'] != getToken()) {
		
		die("Security token mismatch. If this problem persists please contact the web adminsitrator"); 
		
	}
}


**/
?>