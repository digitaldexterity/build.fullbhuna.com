<?php
if(!isset($_SESSION['MM_UserGroup']) || $_SESSION['MM_UserGroup']<1) {
	// if not logged in
	foreach($_REQUEST as $key => $value) {
		if(preg_match("#(update|select|wp-)#i",$value)) {
			if(defined("DEBUG_EMAIL")) {
				$server = var_export($_SERVER, true);
				$message = "SERVER:\n".$server."\n\n" ;
				$message .= "REQUEST:\n".$key."=".$value;
				mail(DEBUG_EMAIL,$site_name." HACK ATTEMPT",$message);
			}
			header('HTTP/1.0 403 Forbidden');
			die("Forbidden Request");			
		}
	}
}

// BAN BY COUNTRY
/*
require_once("/home/paulegan/htdocs/digitaldexterity.net/3rdparty/geoip/geoip.inc");
$gi = geoip_open("/home/paulegan/htdocs/digitaldexterity.net/3rdparty/geoip/GeoIP.dat",GEOIP_STANDARD);
$country_code = geoip_country_code_by_addr($gi,$_SERVER['REMOTE_ADDR']);
if($country_code == "UA" || $country_code == "CN" || $country_code == "RU" || $country_code == "IN" || $country_code == "BR") { // ukraine, china, india, brazil or russia main culprits
	header('HTTP/1.1 403 Forbidden', true, 403);
	die("Forbidden Country");
}

*/




?>