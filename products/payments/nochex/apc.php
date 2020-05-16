<?php require_once('../../../Connections/aquiescedb.php'); ?><?php require_once('../includes/logtransaction.inc.php'); ?>
<?php

$regionID = isset($regionID) ? $regionID : 1; 
$amount = 0;

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsProductPrefs = "SELECT * FROM productprefs WHERE ID=".$regionID."";
$rsProductPrefs = mysql_query($query_rsProductPrefs, $aquiescedb) or die(mysql_error());
$row_rsProductPrefs = mysql_fetch_assoc($rsProductPrefs);
$totalRows_rsProductPrefs = mysql_num_rows($rsProductPrefs);


 
// Payment confirmation from http post
$your_email = $row_rsProductPrefs['paymentclientID'];  // your merchant account email address 
function http_post($server, $port, $url, $vars) {
// get urlencoded vesion of $vars array
$urlencoded = "";
foreach ($vars as $Index => $Value) // get all variables to be used in query
$urlencoded .= urlencode($Index ) . "=" . urlencode($Value) . "&";
$urlencoded = substr($urlencoded,0,-1); // returns portion of string, everything but last character
$headers = "POST $url HTTP/1.0\r\n" // headers to be sent to the server 
. "Content-Type: application/x-www-form-urlencoded\r\n"
. "Content-Length: ". strlen($urlencoded) . "\r\n\r\n"; // length of the string
$fp = fsockopen($server, $port, $errno, $errstr, 10); // returns file pointer
if (!$fp) return "ERROR: fsockopen failed.\r\nError no: $errno - $errstr"; // if cannot open socket then display error message
fputs($fp, $headers); //writes to file pointer 
fputs($fp, $urlencoded);
$ret = "";
while (!feof($fp)) $ret .= fgets($fp, 1024); // while it’s not the end of the file it will loop 
fclose($fp); // closes the connection
return $ret; // array
}

// uncomment below to force a DECLINED response 
//$_POST['order_id'] = "1";
$response = http_post("www.nochex.com", 80, "/nochex.dll/apc/apc", $_POST);
// stores the response from the Nochex server
$debug = "IP -> " . $_SERVER['REMOTE_ADDR'] ."\r\n\r\nPOST DATA:\r\n"; foreach($_POST as $Index => $Value)
$debug .= "$Index -> $Value\r\n";
$debug .= "\r\nRESPONSE:\r\n$response";
if (!strstr($response, "AUTHORISED")) { // searches response to see if AUTHORISED is present if it isn’t a failure message is displayed
$msg = "APC was not AUTHORISED.\r\n\r\n$debug"; // displays debug message 
$status = "DECLINED";
}
else {
$msg = "APC was AUTHORISED."; // if AUTHORISED was found in the response then it was successful
$status = "AUTHORISED";
$amount = isset($_POST['amount']) ? $_POST['amount']: 0;
// whatever else you want to do
}
//mail($your_email, "APC Debug", $msg); // sends an email explaining whether APC was successful or not, the subject will be “APC Debug” but you can change this to whatever you want.

logtransaction($_POST['order_id'],"",$status, 0, "", "", 0, $amount);
?>