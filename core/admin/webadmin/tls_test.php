<?php
$ch = curl_init('https://www.howsmyssl.com/a/check'); 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
$data = curl_exec($ch); 
if($data === false)
{
    echo "<pre>Curl error: " . curl_error($ch)."</pre>\n";
} else {
	$json = json_decode($data); 
	echo "<pre>TLS version: " . $json->tls_version . "</pre>\n";
}
curl_close($ch); 

?>