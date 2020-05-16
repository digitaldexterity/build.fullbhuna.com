<?php if(!isset($aquiescedb)) die();



function sendSMS($number="", $message= "", $providerID=1, $mobileonly = 1, $from = "") {
	global $console;
	$console = isset($console) ? $console : "";
	$account = getSMSAccountDetails();	 
	// mobile only - send only to mobiles starting 07 (not landline voice texts)
	//$providerID=1= clickatell			
	
	$number = preg_replace("[^0-9\+]","", $number); // only numbers and plus
	$number = preg_replace('/^0/', "+44", $number); // replace leading zero with country code UK 44
	
	if($providerID==1 && $number !="" && $message !="") {
		$console .= "SMS: ".$providerID.":".$number.":".$message."\n";
		if($mobileonly!=1 || substr($number,0,2)=="07"  || substr($number,0,4)=="+447") {			
			$concat = ceil(strlen($message)/140);
			if(strlen($account['password'])>3) {
				$console .= "API old\n";
				//old version of API
				$url = "http://api.clickatell.com/http/sendmsg?user=".urlencode($account['username'])."&password=".urlencode($account['password'])."&api_id=".urlencode($account['apiID'])."&to=".urlencode($number)."&text=".urlencode($message);
				$url .= ($from != "") ? "&from=".urlencode($from) : ""; // from needs to be apporved number in Clickatel settings
				$url .= ($concat >1) ? "&concat=".intval($concat) : ""; 
			} else {
				$console .= "API new\n";
				$url = "https://platform.clickatell.com/messages/http/send?apiKey=".urlencode($account['apiID'])."&to=".urlencode($number)."&content=".urlencode($message);
			}
			
			
			
	
			// Get cURL resource
			$curl = curl_init();
			// Set some options - we are passing in a useragent too here
			curl_setopt_array($curl, array(
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_URL => $url,
			CURLOPT_USERAGENT => 'Full Bhuna'
			));
			// Send the request & save response to $resp
			$response = curl_exec($curl);
			// Close request to clear up some resources
			curl_close($curl);

 			return  $url.$response;
		} // number OK
	} // we have text
	return false;
}

function getSMSAccountDetails($ID=1) {
	global $database_aquiescedb, $aquiescedb, $regionID;
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$select = "SELECT * FROM smsaccount WHERE statusID = 1 AND  (regionID = 0 || regionID = ".$regionID.") LIMIT 1";
	$result = mysql_query($select, $aquiescedb) or die(mysql_error());
	$row = mysql_fetch_assoc($result);
	return $row;
	

	
}

?>