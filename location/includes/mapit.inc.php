<?php

function getDataFromPostCode($postcode="") {
	
	$data = array();
	if($postcode !="") {
		$postcode = strtolower(preg_replace("/[^A-Za-z0-9]/","",$postcode));
		$url = "http://mapit.mysociety.org/postcode/";
		$url .= (strlen($postcode)<5) ? "partial/".$postcode : $postcode;
		$file = getWebPage($url);
		
		if($file !="") {
			
			//var_dump(json_decode($file, true));die();
			$file_array =json_decode($file, true);
			
			if(is_array($file_array)) {
				
				$data["councilname"] = isset($file_array["areas"][$file_array["shortcuts"]["council"]]["name"]) ? $file_array["areas"][$file_array["shortcuts"]["council"]]["name"] : "";
				$data["latitude"] = $file_array["wgs84_lat"];
				$data["longitude"] = $file_array["wgs84_lon"];
				
			}
		}
	}
	
	return $data;
	
}

function getDataFromLonLat($lat="", $lon="") {
	if($lat !="" && $lon !="") {
		$url = "http://mapit.mysociety.org/point/4326/".floatval($lat).",".$floatval($lon);
		$file = getWebPage($url);
		
		if($file !="") {
			
			//var_dump(json_decode($file, true));die();
			$file_array =json_decode($file, true);
			
			if(is_array($file_array)) {
				$councilID = $file_array["shortcuts"]["council"];
				
				
				$data["councilname"] = $file_array["areas"][$councilID]["name"];
				
			}
		}
	}
	
	
}

function getWebPage($url) {
	
	if(function_exists('curl_version')) {
		
		
		$header = "Content-type: application/json";		
   
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		//curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
		//curl_setopt($ch, CURLOPT_HTTPHEADER,                array($header, "X-requested-with: XMLHttpRequest"));
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION  ,1);
		$response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		

        curl_close($ch);
		return $response;
	} else {
	
	$file = "";
	$fp = @fopen( $url, "r" );
	if($fp) {
		while ( ! feof( $fp )){
			$file .= fgets( $fp, 1024 );
		}
		fclose($fp);
		
	}
	return $file;
	}
}

?>