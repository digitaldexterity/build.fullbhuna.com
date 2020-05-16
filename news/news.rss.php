<?php require_once('../Connections/aquiescedb.php'); ?><?php require_once('../core/includes/framework.inc.php'); ?>
<?php
if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
  $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;

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
      $theValue = ($theValue != "") ? "'" . doubleval($theValue) . "'" : "NULL";
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

$varSectionID_rsThisSection = "1";
if (isset($_GET['newssectionID'])) {
  $varSectionID_rsThisSection = $_GET['newssectionID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisSection = sprintf("SELECT * FROM newssection WHERE ID = %s OR %s = longID", GetSQLValueString($varSectionID_rsThisSection, "text"),GetSQLValueString($varSectionID_rsThisSection, "text"));
$rsThisSection = mysql_query($query_rsThisSection, $aquiescedb) or die(mysql_error());
$row_rsThisSection = mysql_fetch_assoc($rsThisSection);
$totalRows_rsThisSection = mysql_num_rows($rsThisSection);


switch($row_rsThisSection['orderby']) {
	case 1: $orderby = "ORDER BY headline DESC,  displayfrom DESC"; break; // date posted (newest first)
	case 2: $orderby = "ORDER BY headline DESC,  displayfrom ASC"; break; // date posted (oldest first)
	case 3: $orderby = "ORDER BY headline DESC, news.ordernum ASC, displayfrom DESC"; break; // draggable 
	case 4: $orderby = "ORDER BY headline DESC, news.eventdatetime ASC, displayfrom ASC"; break; // event 
	default : $orderby = "ORDER BY headline DESC,  displayfrom DESC"; break; // default event date
}

$varSectionID_rsNews = "1";
if (isset($_GET['newssectionID'])) {
  $varSectionID_rsNews = $_GET['newssectionID'];
}
$varRegionID_rsNews = "1";
if (isset($regionID)) {
  $varRegionID_rsNews = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsNews = sprintf("SELECT news.ID, title, summary, news.imageURL, posteddatetime, newssection.sectioname FROM news LEFT JOIN newssection ON (news.sectionID = newssection.ID) WHERE status = 1 AND news.rss = 1 AND (DATE(news.displayfrom) <= CURDATE() OR news.displayfrom IS NULL) AND (news.displayto IS NULL OR DATE(news.displayto) >= CURDATE())  AND (news.regionID = %s OR news.regionID IS NULL OR news.regionID=0 OR  newssection.regionID=0) AND (news.sectionID = %s OR news.sectionID IS NULL)  ".$orderby."", GetSQLValueString($varRegionID_rsNews, "int"),GetSQLValueString($varSectionID_rsNews, "int"),GetSQLValueString($varSectionID_rsNews, "int"));
$rsNews = mysql_query($query_rsNews, $aquiescedb) or die(mysql_error());
$row_rsNews = mysql_fetch_assoc($rsNews);
$totalRows_rsNews = mysql_num_rows($rsNews);

$colname_rsThisSection = "1";
if (isset($_GET['newssectionID'])) {
  $colname_rsThisSection = $_GET['newssectionID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisSection = sprintf("SELECT sectioname FROM newssection WHERE ID = %s", GetSQLValueString($colname_rsThisSection, "int"));
$rsThisSection = mysql_query($query_rsThisSection, $aquiescedb) or die(mysql_error());
$row_rsThisSection = mysql_fetch_assoc($rsThisSection);
$totalRows_rsThisSection = mysql_num_rows($rsThisSection);
?><?php header("Content-Type: application/rss+xml; UTF-8"); 
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"; 
echo "<rss version=\"2.0\"  xmlns:atom=\"http://www.w3.org/2005/Atom\">\n"; ?>
<channel>
<atom:link href="<?php echo getProtocol()."://". htmlentities($_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']); ?>" rel="self" type="application/rss+xml" />
	<title><?php echo isset($row_rsThisSection['sectioname']) ? $row_rsThisSection['sectioname'] : "News"; ?> Feed</title>
	<link><?php echo getProtocol()."://".htmlentities($_SERVER['HTTP_HOST'])."/"; ?></link>
	<description>The latest <?php echo isset($row_rsThisSection['sectioname']) ? htmlentities($row_rsThisSection['sectioname']) : "News"; ?> from <?php echo $site_name; ?></description>
	<lastBuildDate><?php echo date('r'); ?></lastBuildDate>
	<language>en-gb</language>
	<?php if($totalRows_rsNews>0) { do { ?>
	<item>
		<title><?php echo htmlspecialchars($row_rsNews['title']); ?></title>
		<link><?php echo getProtocol()."://".  htmlentities($_SERVER['HTTP_HOST'])."/"; ?>news/story.php?newssectionID=<?php echo intval($row_rsNews['sectionID']); ?>&amp;newsID=<?php echo intval($row_rsNews['ID']+100); ?></link>
		<guid isPermaLink="false"><?php echo getProtocol()."://". $_SERVER['HTTP_HOST']."/"; ?>news/story.php?newsID=<?php echo $row_rsNews['ID']+100; ?></guid>
		<pubDate><?php echo (isset($row_rsNews['modifieddatetime']) && $row_rsNews['modifieddatetime']> "1971") ? date('r',strtotime($row_rsNews['modifieddatetime'])) :  (isset($row_rsNews['posteddatetime']) && $row_rsNews['posteddatetime']> "1971" ? date('r',strtotime($row_rsNews['posteddatetime'])) : date('r')); ?></pubDate>
		<description><?php echo htmlspecialchars(strip_tags($row_rsNews['summary'])); ?></description>
        <?php if(isset($row_rsNews['imageURL']) && $row_rsNews['imageURL']!="") {
			if(stripos($row_rsNews['imageURL'],".gif")!==false) {
				$mimetype= "image/gif";
			} else if(stripos($row_rsNews['imageURL'],".png")!==false) {
				$mimetype= "image/png";
			} else {
				$mimetype= "image/jpeg";
			}?>
        <enclosure url="<?php echo getProtocol()."://".  htmlentities($_SERVER['HTTP_HOST']).getImageURL($row_rsNews['imageURL']); ?>" length="0"  type="<?php echo $mimetype; ?>" />
       <?php } ?>
	</item>
<?php } while ($row_rsNews = mysql_fetch_assoc($rsNews)); } ?>
</channel>
</rss>
<?php
mysql_free_result($rsNews);
mysql_free_result($rsThisSection);
?>