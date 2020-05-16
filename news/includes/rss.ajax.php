<?php require_once('../../Connections/aquiescedb.php'); ?><?php require_once('../../core/includes/framework.inc.php'); ?>
<?php include_once "rss_fetch.php";
$regionID = isset($regionID) ? intval($regionID) : 1;
if(isset($_GET['feed'])) {
	$feed = $_GET['feed'];
	// either gets feed from GET or news prefs
} else {
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$select = "SELECT newstickerfeed FROM newsprefs WHERE ID = ".	$regionID;
	$rsNewsPrefs = mysql_query($select, $aquiescedb) or die(mysql_error());
	$row_rsNewsPrefs = mysql_fetch_assoc($rsNewsPrefs);
	$feed = $row_rsNewsPrefs['newstickerfeed'];
}
$itemcount = isset($_GET['itemcount']) ? intval($_GET['itemcount']) : 3;		
$html  = "<div class=\"item\"><h4>#{title} <abbr class=\"timeago text-nowrap\" title=\"#{pubDate}\">#{pubDate}</abbr></h4>";
if(!isset($_GET['body']) || $_GET['body'] == "true") {
	$html  .= "<div class=\"description\">#{description}</div>";
}
$html .= " <span class=\"link\"><a href=\"#{link}\" target=\"_blank\">Read more</a></span></div>";
$protocol = getProtocol()."://";
$url = is_readable("../../core/ajax/urlscrape.ajax.php") ? $protocol.$_SERVER['HTTP_HOST']."/core/ajax/urlscrape.ajax.php?url=".urlencode($feed) : $feed;
$rss = new rss_parser($feed,$itemcount, $html, 1 , 1, "Y-m-d H:i:s");

?>