<?php require_once('../../Connections/aquiescedb.php'); ?>
<?php require_once('geoip.inc');
include("geoipcity.inc");
include("geoipregionvars.php"); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>GEO IP Demo</title>
</head>

<body><?php /* 
$gi = geoip_open(SITE_ROOT."location/geoip/GeoIP.dat",GEOIP_STANDARD);

echo geoip_country_code_by_addr($gi, "24.24.24.24") . "\t" .
     geoip_country_name_by_addr($gi, "24.24.24.24") . "\n";
echo geoip_country_code_by_addr($gi, $_SERVER['REMOTE_ADDR']) . "\t" .
     geoip_country_name_by_addr($gi, $_SERVER['REMOTE_ADDR']) . "\n";

geoip_close($gi);

*/


// uncomment for Shared Memory support
// geoip_load_shared_mem("/usr/local/share/GeoIP/GeoIPCity.dat");
// $gi = geoip_open("/usr/local/share/GeoIP/GeoIPCity.dat",GEOIP_SHARED_MEMORY);

$gi = geoip_open(SITE_ROOT."3rdparty/geoip/GeoLiteCity.dat",GEOIP_STANDARD);

$record = geoip_record_by_addr($gi,"195.11.198.1");
print $record->country_code . " * " . $record->country_code3 . " * " . $record->country_name . "\n";

print ($record->region != "") ? $record->region . " " . $GEOIP_REGION_NAME[$record->country_code][$record->region] . "\n" : "";
print $record->city . "\n";
print $record->postal_code . "\n";
print "Lat: ".$record->latitude . "\n";
print "Long: ".$record->longitude . "\n";
print $record->metro_code . "\n";
print $record->area_code . "\n";

geoip_close($gi);

?>
</body>
</html>