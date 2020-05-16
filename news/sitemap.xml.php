<?php header ("Content-Type:text/xml");  require_once('../Connections/aquiescedb.php'); ?><?php require_once('includes/newsfunctions.inc.php'); ?><?php require_once('../core/includes/framework.inc.php'); ?>
<?php
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

$varRegionID_rsNews = "1";
if (isset($regionID)) {
  $varRegionID_rsNews = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsNews = sprintf("SELECT news.ID, news.longID, news.posteddatetime, news.modifieddatetime, newssection.longID AS sectionlongID, news.sectionID FROM news LEFT JOIN newssection ON (news.sectionID = newssection.ID) WHERE news.status= 1   AND newssection.noindex = 0 AND (newssection.accesslevel IS NULL OR newssection.accesslevel =0) AND (newssection.groupreadID IS NULL OR newssection.groupreadID =0)  AND (newssection.regionID = %s OR newssection.regionID = 0) AND (news.displayfrom <= NOW() OR news.displayfrom IS NULL) AND (news.displayto >= NOW() OR news.displayto IS NULL) ORDER BY news.sectionID, news.posteddatetime", GetSQLValueString($varRegionID_rsNews, "int"));
$rsNews = mysql_query($query_rsNews, $aquiescedb) or die(mysql_error());
$row_rsNews = mysql_fetch_assoc($rsNews);
$totalRows_rsNews = mysql_num_rows($rsNews);



echo '<?xml version="1.0" encoding="UTF-8"?>
'; ?>
<urlset xmlns="http://www.google.com/schemas/sitemap/0.84"

xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"

xsi:schemaLocation="http://www.google.com/schemas/sitemap/0.84 http://www.google.com/schemas/sitemap/0.84/sitemap.xsd">
<!-- Created by Full Bhuna CMS version 1.0 -->
  <?php $prev_secionID = 0;
  $baseurl = getProtocol()."://". $_SERVER['HTTP_HOST'];
  
  if($totalRows_rsNews>0)  { 
do { 



$url = $baseurl.newsLink($row_rsNews['ID'], $row_rsNews['longID'], $row_rsNews['sectionID'], $row_rsNews['sectionlongID']) ;
$lastmod = isset($row_rsNews['modifieddatetime']) ? $row_rsNews['modifieddatetime'] : $row_rsNews['datetimeposted'];
			
	if($row_rsNews['sectionID']!=$prev_secionID) { 
	$prev_secionID = $row_rsNews['sectionID'];
	$indexurl = $baseurl.newsLink(0, 0, $row_rsNews['sectionID'], $row_rsNews['sectionlongID']) ;
	// new section index		?>
    <url>
  <loc><?php echo htmlentities($indexurl); ?></loc>
  <lastmod><?php echo date('c', strtotime(date('Y-m-d 00:00:00'))); ?></lastmod>
  <changefreq>weekly</changefreq>
  <priority>0.5</priority>
</url>
    <?php } ?>
  <url>
  <loc><?php echo htmlentities($url); ?></loc>
  <lastmod><?php echo date('c', strtotime($lastmod)); ?></lastmod>
  <changefreq>weekly</changefreq>
  <priority>0.5</priority>
</url>

   
    <?php } while ($row_rsNews = mysql_fetch_assoc($rsNews));} ?>
</urlset>
<?php
mysql_free_result($rsNews);
?>
