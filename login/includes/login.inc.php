<?php if(!defined("SITE_ROOT")) die(); // can only be called from FB
require_once(SITE_ROOT."members/includes/userfunctions.inc.php"); require_once(SITE_ROOT.'core/includes/framework.inc.php'); 
include(SITE_ROOT."core/seo/includes/seo.inc.php");
$msg = "";

$regionID = (isset($regionID)  && intval($regionID) >0) ? intval($regionID) : (isset($_SESSION["regionID"]) ? intval($_SESSION["regionID"]) : 1);
$stayloggedin = (isset($_POST["stayloggedin"]) && $_POST["stayloggedin"] == 1) ? 1 : 0;

$secure = (getProtocol()=="https") ? true : false;
$cookenameprepend = $secure ? "__Secure-" : "";		



// *** Validate request to login to this site.

if (isset($_GET["accesscheck"]))  {
	if(!isset($_SESSION["MM_Username"])) { 	
  		$_SESSION["PrevUrl"] = $_GET["accesscheck"];
	} else {
		/* if already logged in and redirected, then do not redirect after logging in as page may be inaccessible to this user */
		unset($_SESSION["PrevUrl"]);
	}
}

if (isset($_POST["username"], $_POST["password"])) {
	// log in via form
	$error = "";
	//clear any cookies
	
	setcookie($cookenameprepend."cookieusername", "",time()-42000,"/","",$secure,true);
	setcookie($cookenameprepend."cookiepassword", "",time()-42000,"/","",$secure,true);
	setcookie("cookiestayloggedin", "",time()-42000,"/","",$secure,true);

	if(!isset($_POST["login_token"]) || !isset($_COOKIE["login_token"]) || $_POST["login_token"] != $_COOKIE["login_token"]) { 
		$error .= "Sorry, there was a problem logging you in. Please try again. ";
	}
	//reset login token either way
	setcookie('login_token', '', time()-42000, '/','',$secure, true);
	

	if($row_rsPreferences['captcha_login']==1) {
		if($row_rsPreferences['captcha_type']==1) {
			if(md5(strtolower($_POST['captcha_answer'])) != $_SESSION['captcha'])	{ // security image incorrect
				$error .= "You have typed the security letters incorrectly. ";
			} // end letters wrong
		} else { // advanced captcha
			if(isset($_POST['g-recaptcha-response'])) {          
				$response=json_decode(curl_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".$row_rsPreferences['recaptcha_secret_key']."&response=".$_POST['g-recaptcha-response']."&remoteip=".getClientIP()), true);
				if($response['success'] == false) {
					$error .= "Sorry, you have failed the Captcha test. Please try again. ";
				} // end fail
			} else { // no captch post
				$error .= "Sorry, you have failed the Captcha test. Please try again. ";
			}
		} // end advanced
	} // end security image incorrect
	
	if($error=="") { // no form errors
		$user = checkPassword($_POST["username"], $_POST["password"], false, $regionID);
		if(is_array($user)) { 
			// if logging in as a lower ranked user than currently logged in, remove redirect
			logUserIn($user);
		
		} else { // failed
// log failed log in
	if(defined("DEBUG_EMAIL")) {
		$to = DEBUG_EMAIL;
		$subject= "LOG IN FAILED: ".@$site_name;
		$message = $_POST["username"]."/".$_POST["password"]."\nIP: ".$_SERVER["REMOTE_ADDR"];
		$post = var_export($_POST, true);
		$message .= "\n\nPOST VARS (encoded):\n";
		$message .= base64_encode($post);
		mail($to,$subject,$message);
	}
	trackPage("FAILED LOGIN (".$_POST["username"]."/".$_POST["password"].")");
	$select = "SELECT failedlogin FROM users WHERE username = ".GetSQLValueString($_POST["username"], "text");
	$result = mysql_query($select, $aquiescedb) or die(mysql_error());
	if(mysql_num_rows($result) && $userPreferences["loginattempts"] >0) { 
		// username exists but wrong password and check for login attempts
		$numattempts =  mysql_result($result,0,"failedlogin")+1;
		$update = "UPDATE users SET failedlogin = ".$numattempts." WHERE username = ".GetSQLValueString($_POST["username"], "text");
		$result = mysql_query($update, $aquiescedb) or die(mysql_error());
		if ($numattempts >= $userPreferences["loginattempts"]) { // disable user
			/*$update = "UPDATE users SET usertypeID = -1 WHERE username = ".GetSQLValueString($_POST["username"], "text");
			$result = mysql_query($update, $aquiescedb) or die(mysql_error());*/
			$msg = "You have reached the maximum incorrect login attempts.\nYour account has now been disabled. Please contact us for assistance.";
		} else { // warn user
			$msg = "You have ".($userPreferences["loginattempts"]-$numattempts)." remaining attempts to log in.";
		}// end warn user  
	} // check for login attempts
	 

	sleep(3);    /* Delay to help prevent password cracking */
	header("Location: /login/index.php?username=".urlencode($_POST["username"])."&stayloggedin=".$stayloggedin."&badlogin=true&msg=".urlencode($msg));exit;

} // end failed log in
	} else { // form errors
		$error = "Sorry there were errors logging in:\n".$error;
		header("Location: /login/index.php?username=".urlencode($_POST["username"])."&stayloggedin=".$stayloggedin."&msg=".urlencode($error));exit;
	}

} // end POST username



//Auto log in using remember me COOKIES
else if (!isset($_SESSION["MM_Username"])) { // if not already logged in
	if(isset($_COOKIE[$cookenameprepend."cookieusername"]) && isset($_COOKIE[$cookenameprepend."cookiepassword"]) && !isset($_GET["loggedout"]) && !isset($_GET["badlogin"])) { 
		//added the logged out to prevent slow erasure of cookies
		if (!isset($_SESSION["cookietried"])) { //first attempt
			// get user details from database for security
			$user = checkPassword($_COOKIE[$cookenameprepend."cookieusername"], $_COOKIE[$cookenameprepend."cookiepassword"], true, $regionID);
			if(is_array($user)) { 	 // success	
				$stayloggedin = true; // reset cookie for further month
				$_SESSION["cookietried"] = "loginattempt"; //to prevent multiple redirects due to cookies and not enough privileges				
				logUserIn($user, true);					
			}
		}  // end cookie tried
		// failed
		
		setcookie($cookenameprepend."cookieusername", "",time()-3600,"/","",$secure,true);
		setcookie($cookenameprepend."cookiepassword", "",time()-3600,"/","",$secure,true);
		setcookie("cookiestayloggedin", "",time()-3600,"/","",$secure,true);
		unset($_SESSION["MM_Username"]);
		unset($_SESSION["MM_UserGroup"]);
		unset($_SESSION["cookietried"]);			
			
	}//end check cookies
} // end if not already logged in


// not logging in, set token for form
if(!isset($login_token)) {
	
	if(function_exists("openssl_random_pseudo_bytes")) {
		$login_token = bin2hex(openssl_random_pseudo_bytes(16));
	} else {
		$login_token = md5(uniqid(rand(), true));
	}
	setcookie("login_token", $login_token, time() + 60 * 60 * 24,"/","", $secure, true);
}



?>