<?php define("SITE_ROOT", "/home/limecatc/public_html/build.fullbhuna.com/");
define('USE_SSL', true);
require_once(SITE_ROOT.'core/includes/sslcheck.inc.php'); ?>
<?php 

//https://www.dmxzone.com/go/23295/dmxzone-server-connect/?utm_source=extension&utm_medium=link&utm_content=Enable+Server+Behaviors+and+Data+Bindings+Panel+Support+for+Dreamweaver+CC+&utm_campaign=DMXzone+Server+Connect

//ini_set( 'session.cookie_httponly', 1 ); // if not set by server and MUST go before session start
//header('X-Frame-Options: SAMEORIGIN'); // REMOVE if THIS site is to go in 3rd party iframe

if (!isset($_SESSION)) {
  session_start();
}



if(!function_exists("mysql_connect")) {
	//require_once(SITE_ROOT."core/includes/mysqli.inc.php"); 
}
/*

$host = "build.fullbhuna.com";					
if(!isset($is_cron) && isset($_SERVER["HTTP_HOST"]) && $_SERVER["HTTP_HOST"]!=$host) {
	$page = ($_SERVER["REQUEST_URI"] == "/index.php") ? "/" : $_SERVER["REQUEST_URI"];
	$protocol = (!isset($_SERVER["HTTPS"]) || strtolower($_SERVER["HTTPS"]) != "on") ? "http://" : "https://" ;
	$url = $protocol.$host.$page;
	
	if(isset($_SERVER["HTTP_REFERER"]) && $_SERVER["HTTP_REFERER"]!="") {
		 $_SESSION["referer"] = $_SERVER["HTTP_REFERER"];
	 }
	header( "HTTP/1.1 301 Moved Permanently" ); 
	header( "Status: 301 Moved Permanently" );
	header( "Location: ".$url); exit;
}  */




$html_class =  isset($_SESSION["MM_UserGroup"]) ? " rank".intval($_SESSION["MM_UserGroup"]) : "";


define("DEBUG_EMAIL", "giganticego@gmail.com"); // send errors to theis email
define("DEBUG", true); // turn on all debugging
if(defined("DEBUG")  || isset($_SESSION["debug"])) {
	$_SESSION["log"] = isset($_SESSION["log"]) ? $_SESSION["log"] : ""; // start session log if required
	error_reporting(32767); // 0 = display no errors, 32767 display all
	@ini_set("display_errors", 1); // 0 = don't display none, 1 = display/
} else {
	error_reporting(0); 
	@ini_set("display_errors", 0); 
}

set_error_handler("fb_error_handler");
function fb_error_handler ($errno,$errstr,$errfile, $errline) {
	if(defined("DEBUG_EMAIL") && intval($errno)<8) { // only send real errors
		$error= "Error [".intval($errno)."] ".$errstr." in file ".$errfile." line ".$errline. "(IP: ".$_SERVER["REMOTE_ADDR"].")";
		mail(DEBUG_EMAIL, "Error reported from ".$_SERVER["HTTP_HOST"], $error);
		echo $error;
	}
}




# FileName="Connection_php_mysql.htm"
# Type="MYSQL"
# HTTP="true"

$hostname_aquiescedb = "localhost"; // between the quotes, put the host of your database - usually localhost
$database_aquiescedb = "limecatc_fb"; // between the quotes, put the name of your database
$username_aquiescedb = "limecatc_fb"; // between the quotes put the username for yourdatabase
$password_aquiescedb = "Fu11Bhuna!!!"; // between the quotes put the password for your database
$aquiescedb = mysql_pconnect($hostname_aquiescedb, $username_aquiescedb, $password_aquiescedb) or trigger_error(mysql_error(),E_USER_ERROR); // leave
?><?php

$regionID=1;
mysql_select_db($database_aquiescedb, $aquiescedb); // more often than not!

require_once(SITE_ROOT."Connections/preferences.php");



?>