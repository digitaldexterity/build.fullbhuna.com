<?php require_once(SITE_ROOT.'core/includes/framework.inc.php'); ?>
<?php 
$regionID = (isset($regionID) && $regionID>0) ? intval($regionID) : 1;
$console = isset($console ) ? $console  : "";



if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
  if (PHP_VERSION < 6) {
    $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
  }

  $theValue = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($theValue) : mysql_escape_string($theValue);

  switch ($theType) {
    case "text":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;    
    case "long":
    case "int":
      $theValue = ($theValue != "") ? intval($theValue) : "NULL";
      break;
    case "double":
      $theValue = ($theValue != "") ? doubleval($theValue) : "NULL";
      break;
    case "date":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;
    case "defined":
      $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
      break;
  }
  return $theValue;
}
}


mysql_select_db($database_aquiescedb, $aquiescedb);
$select ="SELECT googleanalytics, googleanalyticsecommerce, googleconversions, googleconversionsall, googlemeta, bingmeta, alexameta,facebookconversions FROM preferences WHERE ID = ".intval($regionID)." LIMIT 1";
$result = mysql_query($select, $aquiescedb) or die(mysql_error());
$seoPrefs = mysql_fetch_assoc($result);


	
	
$pageTracked = false;// only track each page once
$analyticsTracked = false;// only track each page once




if(!function_exists("trackPage")) {	
function trackPage($pageTitle,$pageURL="", $newusername = "") {
	global $database_aquiescedb, $aquiescedb, $regionID, $pageTracked, $console;
	$console .= "*** TRACK PAGE \n";
	
	
	if (!isset($_SESSION)) session_start();
	if (!$pageTracked && isset($aquiescedb) && getClientIP() != @$_SERVER['SERVER_ADDR'] && getClientIP() != @TRACKER_EXCLUDE_IP && (!isset($_SESSION['MM_UserGroup']) || $_SESSION['MM_UserGroup'] !=10) && (defined("TRACKER_INCLUDE_BOTS") || (isset($_SERVER['HTTP_USER_AGENT']) && preg_match("/(Mozilla|MOT|Blackberry|Nokia|SAMSUNG|LG|SonyEricsson)/",$_SERVER['HTTP_USER_AGENT']) && !preg_match("/(phpSitemapNG|bot|search|spider|slurp|crawl|monitor|jeeves)/i",$_SERVER['HTTP_USER_AGENT'])))) { //valid host and not web admin
		$pageTracked = true; // only once per page
		$console .= "TRACKING... \n";
		mysql_select_db($database_aquiescedb, $aquiescedb); 
		if (!isset($_SESSION['fb_tracker'])) { //set up new session
			$new_session = true;			
			//$_SESSION['fb_tracker'] = md5(uniqid(rand(), true));
			// in prep for conversion to INT
			$_SESSION['fb_tracker'] = intval(microtime(true)*10000);
			$console .= "NEW TRACKER: ".$_SESSION['fb_tracker']." \n";
			$regionID = isset($regionID) ? $regionID : 1; // back compat
			$referer = isset($_SESSION['referer']) ? $_SESSION['referer'] : (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER']: "");
			$adwords = preg_match("/gclid|adurl/i",$referer.$_SERVER['REQUEST_URI']) ? 1 : 0;
			$usersession = isset($_SESSION['MM_Username']) ? $_SESSION['MM_Username'] : "";
			$insertSQL = "INSERT INTO track_session (ID, datetime, remote_address, user_agent, referer, username, regionID, adwords) VALUES (".GetSQLValueString($_SESSION['fb_tracker'],"int").",'".date('Y-m-d H:i:s')."',".GetSQLValueString(getClientIP(),"text").",".GetSQLValueString($_SERVER['HTTP_USER_AGENT'],"text").",".GetSQLValueString($referer,"text").",".GetSQLValueString($usersession,"text").",".intval($regionID).",".$adwords.")";
			mysql_query($insertSQL, $aquiescedb) or die(mysql_error());
			
			//archive old sessions
			$interval = defined('TRACKER_PERIOD') ? TRACKER_PERIOD : "2 MONTH";
			$log_month_start = date('Y-m-01', strtotime($interval." AGO - 1 MONTH"));
			$select = "SELECT ID FROM track_month WHERE logmonth = '".$log_month_start."'";
			$result = mysql_query($select, $aquiescedb) or die(mysql_error());
			if(mysql_num_rows($result)<1) { // last months log not done yet - we're not taking into account missed months here
				$insert = "INSERT INTO track_month (logmonth, hits, regionID) VALUES ('".$log_month_start."',(SELECT COUNT(ID) AS x FROM track_session WHERE DATE(datetime)>='".$log_month_start."' AND DATE(datetime)<('".$log_month_start."' + INTERVAL 1 MONTH)),".intval($regionID).")";		
				mysql_query($insert, $aquiescedb) or die(mysql_error());
				$deleteSQL = "DELETE track_session, track_page FROM track_session LEFT JOIN track_page ON (track_session.ID = track_page.sessionID) WHERE  DATE(track_session.datetime) < ('".$log_month_start."' + INTERVAL 1 MONTH)";
    			mysql_query($deleteSQL, $aquiescedb) or die(mysql_error());
			}
			
		} // end new session	
			
			
		// update page table
		$pageURL = ($pageURL=="") ? $_SERVER['REQUEST_URI'] : $pageURL; 
		$post = http_build_query($_POST);
		$pageURL .= $post != "" ? "#POST:".$post  : "";
		$pageURL = substr($pageURL, 0,254);
		$insertSQL = "INSERT INTO track_page (datetime, sessionID, page, pageTitle) VALUES ('".date('Y-m-d H:i:s')."',".GetSQLValueString($_SESSION['fb_tracker'],"text").",".GetSQLValueString($pageURL,"text").",".GetSQLValueString($pageTitle,"text").")"; 
		mysql_query($insertSQL, $aquiescedb) or die(mysql_error());
		$pageID = mysql_insert_id();
		
		// add entry page and username to tracker session if started
		
		if($newusername!="") { // just logged in		
			$updateSQL = "UPDATE track_session SET  username=".GetSQLValueString($newusername, "text")." WHERE ID= ".GetSQLValueString($_SESSION["fb_tracker"], "text")."";
			mysql_query($updateSQL, $aquiescedb) or die(mysql_error().": ".$updateSQL);
		}
		
		if(isset($new_session)) { // new session, set entry page	
			$updateSQL = "UPDATE track_session SET entrypageID = ".GetSQLValueString($pageID, "int")." WHERE ID= ".GetSQLValueString($_SESSION["fb_tracker"], "text")."";
			mysql_query($updateSQL, $aquiescedb) or die(mysql_error().": ".$updateSQL);
		}
		
	}
} // end func
}


if(!function_exists("googleAnalytics")) {	
function googleAnalytics() {
	global $seoPrefs, $aquiescedb, $regionID, $transactions, $items, $grandtotal, $site_name, $totalvat, $shippingnettotal, $strVendorTxCode, $billingCountry, $track_ecommerce, $tagmanager, $prepay, $postpay, $analyticsTracked ;
	
	
	
	$analyticscode = $seoPrefs['googleanalytics'];
	$optoutcode = "";
	$transactioncode = "";
	$googleconversions = "";
	$fbconversions = $seoPrefs['facebookconversions'];
	
	if(!$analyticsTracked && trim($analyticscode)!="") { // we have analytics
		$analyticsTracked = true;
		preg_match_all("/UA-[0-9]{5,}-[0-9]{1,}/", $analyticscode, $matches);
		$ga_account = $matches[0][0];
		
		$optoutcode = "<script> 
		// use <a href='javascript:gaOutput()'>Click here to opt-out of Google Analytics</a> on page
		// Set to the same value as the web property used on the site
		var gaProperty = '".$ga_account."';
		
		
		
		// Disable tracking if the opt-out cookie exists.
		var disableStr = 'ga-disable-' + gaProperty;
		if (document.cookie.indexOf(disableStr + '=true') > -1) {
		  window[disableStr] = true;
		}
		
		// Opt-out function
		function gaOptout() {
		  document.cookie = disableStr + '=true; expires=Thu, 31 Dec 2099 23:59:59 UTC; path=/';
		  window[disableStr] = true;
		} //
		</script>";

		if(isset($track_ecommerce) && is_array($items) && count($items)>0) {
			
			
				$transactioncode = "<!-- TRACK ECOMMERCE --><script>\n\n";
			if(isset($tagmanager)) { // USE TAG MANAGER SET IN INCLUDE AFTER BODY TAG
				$transactioncode .= "ga('require', 'ecommerce');
			
			ga('ecommerce:addTransaction', {
  				'id': '".$strVendorTxCode."',                     // Transaction ID. Required.
  				'affiliation': '".htmlentities($site_name,ENT_COMPAT, 'UTF-8')."',   // Affiliation or store name.
  				'revenue': '".floatval($grandtotal)."',               // Grand Total.
  				'shipping': '".floatval($shippingnettotal)."',                  // Shipping.
  				'tax': '".floatval($totalvat)."'                     // Tax.
			});\n";
		foreach($items as $key => $item) {
			$transactioncode .="ga('ecommerce:addItem', {
  				'id': '".$strVendorTxCode."',                     // Transaction ID. Required.
 				 'name': '".htmlentities($item['title'],ENT_COMPAT, 'UTF-8')."',    // Product name. Required.
  				'sku': '".htmlentities($item['sku'],ENT_COMPAT, 'UTF-8')."',                 // SKU/code.
  				'category': '".htmlentities($item['explanation'],ENT_COMPAT, 'UTF-8')."',         // Category or variation.
  				'price': '".$item['price']."',                 // Unit price.
  				'quantity': '".$item['quantity']."'                   // Quantity.
			});";
		  }
		  $transactioncode .="ga('ecommerce:send');\n";
		  
			} // end tag manager version
			
			else { // use new GA ccode
			
			
$transactioncode .= "window.dataLayer = window.dataLayer || [];
dataLayer.push({
   'transactionId': '".$strVendorTxCode."',
   'transactionAffiliation': '".htmlentities($site_name,ENT_COMPAT, 'UTF-8')."',
   'transactionTotal': ".floatval($grandtotal).",
   'transactionTax': ".floatval($totalvat).",
   'transactionShipping': ".floatval($shippingnettotal).",
   'transactionProducts': [";
   foreach($items as $key => $item) {
	   $transactioncode .= "{
       'sku': '".htmlentities($item['sku'],ENT_COMPAT, 'UTF-8')."',
       'name': '".htmlentities($item['title'],ENT_COMPAT, 'UTF-8')."',
       'category': '".htmlentities($item['explanation'],ENT_COMPAT, 'UTF-8')."',
       'price': ".$item['price'].",
       'quantity': ".$item['quantity']."
   }";
   }
   
  $transactioncode .= "]
});
";
			}
			
			
			
		/*	// old analytics version
		  $transactioncode .="var _gaq = _gaq || [];
		  _gaq.push(['_setAccount', '".$ga_account."']);
		  _gaq.push(['_trackPageview']);
		  _gaq.push(['_addTrans',
			'".$strVendorTxCode."',           // transaction ID - required
			'".htmlentities($site_name,ENT_COMPAT, 'UTF-8')."',  // affiliation or store name
			'".floatval($grandtotal)."',          // total - required
			'".floatval($totalvat)."',           // tax
			'".floatval($shippingnettotal)."',              // shipping
			'".htmlentities($_SESSION['strBillingCity'],ENT_COMPAT, 'UTF-8')."',       // city
			'".htmlentities($_SESSION['strBillingCity'],ENT_COMPAT, 'UTF-8')."',     // state or province
			'".htmlentities($billingCountry['fullname'],ENT_COMPAT, 'UTF-8')."'             // country
		  ]);
		
		   // add item might be called for every item in the shopping cart
		   // where your ecommerce engine loops through each item in the cart and
		   // prints out _addItem for each 
		  
		  \n";
		  
			 
			 foreach($items as $key => $item) {
				$transactioncode .=" _gaq.push(['_addItem',
			  '".$strVendorTxCode."',           // transaction ID - required
			'".htmlentities($item['sku'],ENT_COMPAT, 'UTF-8')."',           // SKU/code - required
			'".htmlentities($item['title'],ENT_COMPAT, 'UTF-8')."',        // product name
			'".htmlentities($item['explanation'],ENT_COMPAT, 'UTF-8')."',   // category or variation
			'".$item['price']."',          // unit price - required
			'".$item['quantity']."'               // quantity - required
			]);\n"; } 
			
		  
		 $transactioncode .=" _gaq.push(['_trackTrans']); //submits transaction to the Analytics servers
				  (function() {
			var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
			ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
			var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
		  })();\n";
			} // end old analytics version
	*/
		 $transactioncode .= "</script>";
		} //show transaction code
	} // is analytic code
	
	
	
	if(($seoPrefs['googleconversionsall'] == 0 || ($seoPrefs['googleconversionsall'] == 1 && isset($prepay)) || ($seoPrefs['googleconversionsall'] == 2 && isset($postpay)))) {
		
		$googleconversions =  $seoPrefs['googleconversions'];
		$googleconversions = isset($grandtotal) ? str_replace("1.00", number_format($grandtotal,2,".",","), $googleconversions) : $googleconversions; // update with actual total if available		
		
		$fbconversions .= (trim($fbconversions) !=="") ? "<script>

fbq('track', 'Purchase', {value: ".floatval($grandtotal).",currency: 'GBP'});

</script>": "";
		
		
	}
	
	$googlescripts = $analyticscode.$optoutcode.$transactioncode;
	return "<!-- GOOGLE SCRIPTS -->"
	.$analyticscode.$optoutcode.$googleconversions.$transactioncode."
	<!-- END GOOGLE SCRIPTS --><!-- START AD ROLL CONVERSION -->".$fbconversions."<script>
  adroll_conversion_value = ".floatval($grandtotal).";
  adroll_currency = 'GBP';
</script>";
	
	
} 
}

if(!function_exists("metaTags")) {	
function metaTags() {
	global $seoPrefs;	
	return $seoPrefs['googlemeta']."\n".$seoPrefs['bingmeta']."\n".$seoPrefs['alexameta'];
}
}

if(!function_exists("getSearchTerms")) {	
function getSearchTerms($referer) {
	$return = "";
	$url = parse_url($referer); 
	
	if(isset($url['query'])) { 
		   parse_str($url['query']);
		   if(isset($q)) $return = $q; // most searches
		   else if(isset($p)) $return = $p; // yahoo
		   else if(isset($term)) $return = $term; // sky		   
	 }
	return trim(strtolower(stripslashes($return)));
}
}


if(!function_exists("getHostReferer")) {	
function getHostReferer($referer) {
	$return = "";
	$url = parse_url($referer); 
	$return .= isset($url['host']) ? $url['host'] : ""; 
	return $return;
}
}

if(!function_exists("mod_rewrite_on")) {	
function mod_rewrite_on() { /* depracated */
	if(function_exists("apache_get_modules")) {
		return in_array('mod_rewrite', apache_get_modules());
	} else if(defined("MOD_REWRITE") || isset($_SERVER['HTTP_MOD_REWRITE'])) {
		return true;
	} else {
		return false;
	}
}
}

if(!function_exists("deleteTracker")) {	

function deleteTracker() {
	global $database_aquiescedb, $aquiescedb; 
	$delete = "DELETE FROM track_page WHERE sessionID = ".$_SESSION['fb_tracker'];
	mysql_query($delete, $aquiescedb) or die(mysql_error());
	$delete = "DELETE FROM track_session WHERE ID = ".$_SESSION['fb_tracker'];
	mysql_query($delete, $aquiescedb) or die(mysql_error());
	unset($_SESSION['fb_tracker']);
}
}
?>