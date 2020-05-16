<?php require_once('../Connections/aquiescedb.php');?>
<?php require_once('includes/functions.inc.php'); ?><?php require_once('../core/includes/framework.inc.php'); ?>
<?php if(is_readable('../products/includes/productFunctions.inc.php')) { ?>
<?php require_once('../products/includes/productFunctions.inc.php'); ?>
<?php } ?>
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
?>
<?php

$currentPage = $_SERVER["PHP_SELF"];

if(isset($_GET['pageNum_rsSectionArticles'])) $_GET['pageNum_rsSectionArticles'] = intval($_GET['pageNum_rsSectionArticles']);
if(isset($_GET['totalRows_rsSectionArticles'])) $_GET['totalRows_rsSectionArticles'] = intval($_GET['totalRows_rsSectionArticles']);


$maxRows_rsSectionArticles = 1;
$pageNum_rsSectionArticles = 0;
if (isset($_GET['pageNum_rsSectionArticles'])) {
  $pageNum_rsSectionArticles = $_GET['pageNum_rsSectionArticles'];
}
$startRow_rsSectionArticles = $pageNum_rsSectionArticles * $maxRows_rsSectionArticles;

$varSectionID_rsSectionArticles = "-1";
if (isset($_GET['sectionID'])) {
  $varSectionID_rsSectionArticles = $_GET['sectionID'];
}
$varRegionID_rsSectionArticles = "1";
if (isset($regionID)) {
  $varRegionID_rsSectionArticles = $regionID;
}
$varUserType_rsSectionArticles = "0";
if (isset($_SESSION['MM_UserGroup'])) {
  $varUserType_rsSectionArticles = $_SESSION['MM_UserGroup'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsSectionArticles = sprintf("SELECT article.ID, article.longID, article.title, article.newWindow, articlesection.`description` AS section, articlesection.ID AS sectionID, articlesection.longID AS sectionlongID, articlesection.ordernum AS secOrder, article.ordernum FROM article LEFT JOIN articlesection ON (article.sectionID = articlesection.ID) WHERE article.statusID = 1  AND (article.sectionID  = %s OR articlesection.longID = %s ) AND (%s >= articlesection.accesslevel OR articlesection.accesslevel IS NULL OR articlesection.accesslevel =0)  AND versionofID IS NULL   AND article.sectionID >0 AND (articlesection.regionID = %s OR articlesection.regionID = 0) ORDER BY article.ordernum, article.ID", GetSQLValueString($varSectionID_rsSectionArticles, "text"),GetSQLValueString($varSectionID_rsSectionArticles, "text"),GetSQLValueString($varUserType_rsSectionArticles, "int"),GetSQLValueString($varRegionID_rsSectionArticles, "int"));
$query_limit_rsSectionArticles = sprintf("%s LIMIT %d, %d", $query_rsSectionArticles, $startRow_rsSectionArticles, $maxRows_rsSectionArticles);
$rsSectionArticles = mysql_query($query_limit_rsSectionArticles, $aquiescedb) or die(mysql_error());
$row_rsSectionArticles = mysql_fetch_assoc($rsSectionArticles);

if (isset($_GET['totalRows_rsSectionArticles'])) {
  $totalRows_rsSectionArticles = $_GET['totalRows_rsSectionArticles'];
} else {
  $all_rsSectionArticles = mysql_query($query_rsSectionArticles);
  $totalRows_rsSectionArticles = mysql_num_rows($all_rsSectionArticles);
}
$totalPages_rsSectionArticles = ceil($totalRows_rsSectionArticles/$maxRows_rsSectionArticles)-1;
?>
<?php



$varSectionID_rsThisSection = "events";
if (isset($_GET['sectionID'])) {
  $varSectionID_rsThisSection = $_GET['sectionID'];
}
$varRegionID_rsThisSection = "1";
if (isset($regionID)) {
  $varRegionID_rsThisSection = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisSection = sprintf("SELECT * FROM articlesection WHERE (CAST(articlesection.ID AS CHAR) = %s OR longID = %s) AND (articlesection.regionID = %s OR %s = 0 OR articlesection.regionID = 0)", GetSQLValueString($varSectionID_rsThisSection, "text"),GetSQLValueString($varSectionID_rsThisSection, "text"),GetSQLValueString($varRegionID_rsThisSection, "int"),GetSQLValueString($varRegionID_rsThisSection, "int"));
$rsThisSection = mysql_query($query_rsThisSection, $aquiescedb) or die(mysql_error());
$row_rsThisSection = mysql_fetch_assoc($rsThisSection);
$totalRows_rsThisSection = mysql_num_rows($rsThisSection);



mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsProductPrefs = "SELECT sitemap FROM productprefs WHERE ID = ".$regionID;
$rsProductPrefs = mysql_query($query_rsProductPrefs, $aquiescedb) or die(mysql_error());
$row_rsProductPrefs = mysql_fetch_assoc($rsProductPrefs);
$totalRows_rsProductPrefs = mysql_num_rows($rsProductPrefs);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsArticlePrefs = "SELECT * FROM articleprefs WHERE ID = ".$regionID;
$rsArticlePrefs = mysql_query($query_rsArticlePrefs, $aquiescedb) or die(mysql_error());
$row_rsArticlePrefs = mysql_fetch_assoc($rsArticlePrefs);
$totalRows_rsArticlePrefs = mysql_num_rows($rsArticlePrefs);


if((isset($_GET['sectionID']) && $_GET['sectionID']!="") || isset($_GET['notfoundURL'])) { 
	if($row_rsSectionArticles['ID'] >0) { // we have an article in section
		if($row_rsThisSection['linkaction'] == 1) { // do a redirect to first article
			$link = articleLink($row_rsSectionArticles['ID'],$row_rsSectionArticles['longID'], $row_rsSectionArticles['sectionID'], 			$row_rsSectionArticles['sectionlongID'],$row_rsThisSection['subsectionofID']) ;
			header( "HTTP/1.1 301 Moved Permanently" ); 
			header( "Status: 301 Moved Permanently" );
			header("location: ".$link); exit;
		}  else {
			// just show index page
		}
	}  else { // no articles means 404 
		if(isset($row_rsArticlePrefs['notfoundURL']) && strlen($row_rsArticlePrefs['notfoundURL'])>0) {
			// 404 redirect for no article sections
			header( "HTTP/1.1 301 Moved Permanently" ); 
			header( "Status: 301 Moved Permanently" );
			header("location: ".$row_rsArticlePrefs['notfoundURL']); exit;
		} else {
			header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found", true, 404);
			$notfoundURL = isset($_GET['notfoundURL']) ? $_GET['notfoundURL'] : $_SERVER['REQUEST_URI'];
			$error = "Not found: ".htmlentities($notfoundURL,ENT_COMPAT, "utf-8")."\n\nWe're sorry, the page you were looking for may have moved or no longer exist. You may find what you're looking for below.";
			
		}
	}	
}
?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = isset($row_rsThisSection['description']) ? $row_rsThisSection['description'] : (isset($row_rsArticlePrefs['text_siteindex']) ? htmlentities($row_rsArticlePrefs['text_siteindex'], ENT_COMPAT, "UTF-8") : "Site Index"); echo $pageTitle." | ".$site_name; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<link href="/articles/css/defaultArticles.css" rel="stylesheet"  />
<meta name="description" content="<?php echo (isset($row_rsArticlePrefs['indexmetadescription']) && trim($row_rsArticlePrefs['indexmetadescription']) !="") ? $row_rsArticlePrefs['indexmetadescription'] : "This is the official site index page for ".$site_name.". Here you will find links to all the main pages within the ".$site_name." web site."; ?>" />
<meta name="keywords" content="<?php echo $site_name; ?> site,  map, index, contents" />
<link rel="canonical" href="<?php $canonicalURL =  getProtocol()."://". $_SERVER['HTTP_HOST']."/articles/"; echo $canonicalURL; ?>">
<style>
<!--
.sitemap li.article0 > a {
	font-weight: bold;
}
.sitemap li.anchor {
	display: none;
}
-->
</style>
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" -->
  <div class="sitemap container">
  <div class="crumbs">
    <div><span class="you_are_in">You are in: </span>
      <ol itemscope itemtype="http://schema.org/BreadcrumbList">
        <li itemprop="itemListElement" itemscope
      itemtype="http://schema.org/ListItem" class="home"><a itemprop="item" href="/"><span itemprop="name">Home</span></a>
          <meta itemprop="position" content="1" />
        </li>
        <li itemprop="itemListElement" itemscope
      itemtype="http://schema.org/ListItem" > <a itemprop="item" href="/articles/index.php"><span itemprop="name">Articles</span></a>
          <meta itemprop="position" content="2" />
        </li>
        <?php if(isset($row_rsThisSection['description'])) { ?>
        <li itemprop="itemListElement" itemscope
      itemtype="http://schema.org/ListItem" > <a itemprop="item" href="<?php echo $canonicalURL; ?>"><span itemprop="name"><?php echo $row_rsThisSection['description']; ?></span></a>
          <meta itemprop="position" content="3" />
        </li>
        <?php } ?>
      </ol>
    </div>
  </div>
  <?php require_once('../core/includes/alert.inc.php'); ?>
  <div class="row">
    <div class="col-md-6">
      <div id="articleIndex">
        <h1><?php echo isset($row_rsArticlePrefs['text_siteindex']) ? htmlentities($row_rsArticlePrefs['text_siteindex'], ENT_COMPAT, "UTF-8") : "Site Index"; ?></h1>
        <div id="sitemap_articles">
          <?php $extras['sitemap']=true; echo buildMenu(0,4,"",1,$extras); ?>
        </div>
        <?php if($row_rsProductPrefs['sitemap']==1 && function_exists("productMenu")) { ?>
        <div id="sitemap_products"><?php echo  productMenu(0, 2, "", false, true);   ?></div>
        <?php } ?>
      </div>
    </div>
    <div class="col-md-6">
      <?php if(is_readable(SITE_ROOT."search/index.php")) {
		   if($row_rsArticlePrefs['indexshowsearch']==1) { ?>
      <h2>Search Site</h2>
      <form action="/search/" method="get" class="form-inline">
      <input name="CSRFtoken" type="hidden" value="<?php echo $CSRFtoken; ?>">
        <input name="s" type="text" class="form-control">
        <button   type="submit" class="btn btn-default btn-secondary btn-secondary">Search...</button>
      </form>
      <?php } ?>
      <img src="/search/auto_spider_img.php" width="1" height="1" alt="Search spider image" /><?php } ?></div>
  </div></div>
  <!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsThisSection);


mysql_free_result($rsProductPrefs);

mysql_free_result($rsArticlePrefs);
?>
