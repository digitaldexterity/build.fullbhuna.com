<?php // just kill the login session
if (!isset($_SESSION)) {
  session_start();
}
$secure = (getProtocol()=="https") ? true : false;
$cookenameprepend  = $secure ? "__Secure-" : "";

if(isset($_SESSION['MM_Username'])) { 
setcookie($cookenameprepend."cookieusername", "",time()-3600, '/',"",$secure,true);
setcookie($cookenameprepend."cookiepassword", "",time()-3600, '/',"",$secure,true);
setcookie("cookiestayloggedin", "",time()-3600, '/',"",$secure,true);
$_SESSION['MM_Username'] = ""; unset($_SESSION['MM_Username']); 
} 
if(isset($_SESSION['MM_UserGroup'])) {  
// cookie is not used in standrad Full Bhuna
	setcookie("cookieusergroup","",time()-3600, '/',"",$secure,true);
$_SESSION['MM_UserGroup'] = ""; unset($_SESSION['MM_UserGroup']); 
 }




?>