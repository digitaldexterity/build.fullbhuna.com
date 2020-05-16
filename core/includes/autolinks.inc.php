<?php // Copyright 2015 Paul Egan
$regionID = isset($regionID) ? intval($regionID) : 1;

if(!function_exists("addLinks")) {
function addLinks($html, $clickable = true) {
	// function to make keywords clickable and also optionally add clickable links to URLs within text
	global $database_aquiescedb, $aquiescedb, $regionID;
	
	
	if($clickable) {
		//$html =  preg_replace('#</?a[^>]*>#is', '', $html); // remove existing a tags
		$html =  preg_replace('#href=\"http#is', 'href="hxxp', $html); // encode src= to prevent these being made clickable
		$html =  preg_replace('#src=\"http#is', 'src="hxxp', $html); // encode src= to prevent these being made clickable
		$html = clickableLinks($html);
		$html =  preg_replace('#href=\"hxxp#is', 'href="http', $html); // recode src= 
		$html =  preg_replace('#src=\"hxxp#is', 'src="http', $html); // recode src= 
	}
	// Keywords link insertion script
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$query_rsKeywords = "SELECT keywordlinks.ID, keywordlinks.linkkeywords, keywordlinks.linkURL, keywordlinks.linktitle FROM keywordlinks WHERE keywordlinks.statusID = 1 AND keywordlinks.regionID=".$regionID;
	$rsKeywords = mysql_query($query_rsKeywords, $aquiescedb) or die(mysql_error());
	$row_rsKeywords = mysql_fetch_assoc($rsKeywords);
	$totalRows_rsKeywords = mysql_num_rows($rsKeywords);
	
	if($totalRows_rsKeywords>0 && isset($html)) {
	 	do { // replace each keyword
	 		
							$replace[$row_rsKeywords['ID']] = "<a href=\"".$row_rsKeywords['linkURL']."\" title = \"".$row_rsKeywords['linktitle']."\" target=\"_top\" class=\"dont-break-out\">".$row_rsKeywords['linkkeywords']."</a>";
							//$html = str_ireplace($row_rsKeywords['linkkeywords'], $replace, $html);
							$html = preg_replace("/(".$row_rsKeywords['linkkeywords'].")\s/i", $replace[$row_rsKeywords['ID']]." ", $html);
							$html = preg_replace("/(".$row_rsKeywords['linkkeywords'].")\,/i", $replace[$row_rsKeywords['ID']].",", $html);
							$html = preg_replace("/(".$row_rsKeywords['linkkeywords'].")\./i", $replace[$row_rsKeywords['ID']].".", $html);
						
							
 				
		} while ($row_rsKeywords = mysql_fetch_assoc($rsKeywords)); // end each keyword
	} // end if keywords
 
	
	
 	return $html;
} // end keywords link insertion function
}


if(!function_exists("clickableLinks")) {
	function clickableLinks($string) { 
	
	  
	  // web links
	  $string = preg_replace('@(https?://([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.#-]*(\?\S+)?[^\.\s])?)?)@', '<a href="$1" target="_blank">$1</a>', $string);
	 // emails
    $string = preg_replace('#([0-9a-z]([-_.]?[0-9a-z])*@[0-9a-z]([-.]?[0-9a-z])*\\.[a-wyz][a-z](fo|g|l|m|mes|o|op|pa|ro|seum|t|u|v|z)?)#i', '<a href="mailto:\\1">\\1</a>', $string);

return  $string;

	}
}


?>
