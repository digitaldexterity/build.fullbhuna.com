<?php require_once('../includes/framework.inc.php'); ?>
<?php /* to allow cross-domain URL getting with AJAX functions as well as general scrape and content getting

url - the URL to get
idclass (OPTIONAL) - get just content within tag of class OR id


*/


if($_GET['url'])
{
	$url=$_GET['url'];
	$opts = array('http' => array('header' => 'Accept-Charset: UTF-8, *;q=0'));
	$context = stream_context_create($opts);
	$html = file_get_contents($url, false, $context);
	if($html =="") { 
	// above function deowan't always work, so try cURL fallback
		$html = get_web_page($url);
	}
	
	if(isset($_GET['idclass'])) {
		// ID or CLASS must be singular
		preg_match('#<([a-z]+).+?(?:id|class)\s*=\s*("|\')'.preg_quote($_GET['idclass']).'\2[^>]*>(.+?)</\1>#is', $html, $match);	
		$html =  $match[3];
	}
	echo $html;
}


	
?>