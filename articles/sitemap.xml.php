<?php header ("Content-Type:text/xml");  
require_once('../Connections/aquiescedb.php'); ?><?php require_once('includes/functions.inc.php'); ?><?php require_once('../core/includes/framework.inc.php'); ?>
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

$varRegionID_rsArticles = "1";
if (isset($regionID)) {
  $varRegionID_rsArticles = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsArticles = sprintf("SELECT article.ID, article.longID, article.sectionID, articlesection.longID AS sectionlongID, article.createddatetime, article.modifieddatetime FROM article LEFT JOIN articlesection ON (article.sectionID = articlesection.ID) WHERE article.statusID = 1 AND article.robots <3 AND article.showlink >=0 AND articlesection.showlink !=0 AND (articlesection.accesslevel IS NULL OR articlesection.accesslevel =0)  AND versionofID IS NULL   AND article.sectionID > 0  AND (article.regionID IS NULL OR article.regionID = 0  OR article.regionID = %s) AND (article.accesslevel IS NULL OR article.accesslevel = 0) AND (articlesection.groupreadID IS NULL OR articlesection.groupreadID = 0)", GetSQLValueString($varRegionID_rsArticles, "int"),GetSQLValueString($varRegionID_rsArticles, "int"));
$rsArticles = mysql_query($query_rsArticles, $aquiescedb) or die(mysql_error());
$row_rsArticles = mysql_fetch_assoc($rsArticles);
$totalRows_rsArticles = mysql_num_rows($rsArticles);



echo '<?xml version="1.0" encoding="UTF-8"?>
'; 


$protocol=getProtocol();
$baseurl =$protocol."://".$_SERVER['HTTP_HOST'];
$lastmod = date('Y-m-d 00:00:00');

?>
<urlset xmlns="http://www.google.com/schemas/sitemap/0.84"

xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"

xsi:schemaLocation="http://www.google.com/schemas/sitemap/0.84 http://www.google.com/schemas/sitemap/0.84/sitemap.xsd">
<!-- Created by Full Bhuna CMS version 1.0 -->
<url>
<loc>
<?php  echo htmlentities($baseurl)."/"; ?>
</loc>
<lastmod><?php echo date('c', strtotime($lastmod)); ?></lastmod>
<changefreq>weekly</changefreq>
<priority>1.0</priority>
</url>
<?php if($totalRows_rsArticles>0) { 
  do { 
		$url = $baseurl.articleLink($row_rsArticles['ID'], $row_rsArticles['longID'], $row_rsArticles['sectionID'], $row_rsArticles['sectionlongID']) ;
						
		$lastmod = isset($row_rsArticles['modifieddatetime']) ? $row_rsArticles['modifieddatetime'] : $row_rsArticles['createddatetime'];	?>
<url>
  <loc><?php  echo htmlentities($url); ?></loc>
  <lastmod><?php echo date('c', strtotime($lastmod)); ?></lastmod>
  <changefreq>weekly</changefreq>
  <priority>0.5</priority>
</url>

   
    <?php } while ($row_rsArticles = mysql_fetch_assoc($rsArticles));  }?>
<url>
<loc>
<?php  echo htmlentities($baseurl)."/articles/"; ?>
</loc>
<lastmod><?php echo date('c', strtotime($lastmod)); ?></lastmod>
<changefreq>weekly</changefreq>
<priority>0.1</priority>
</url>
</urlset>
<?php
mysql_free_result($rsArticles);
?>
