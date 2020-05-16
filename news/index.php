<?php require_once('../Connections/aquiescedb.php'); ?><?php require_once('../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../core/includes/framework.inc.php'); ?>
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

if(isset($_GET['pageNum_rsNews'])) $_GET['pageNum_rsNews'] = intval($_GET['pageNum_rsNews']);
if(isset($_GET['totalRows_rsNews'])) $_GET['totalRows_rsNews'] = intval($_GET['totalRows_rsNews']);

 
if (!isset($_SESSION)) { session_start(); }?>
<?php
$currentPage = $_SERVER["PHP_SELF"];

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsNewsPrefs = "SELECT * FROM newsprefs";
$rsNewsPrefs = mysql_query($query_rsNewsPrefs, $aquiescedb) or die(mysql_error());
$row_rsNewsPrefs = mysql_fetch_assoc($rsNewsPrefs);
$totalRows_rsNewsPrefs = mysql_num_rows($rsNewsPrefs);

$maxRows_rsNews = 30;
$pageNum_rsNews = 0;
if (isset($_GET['pageNum_rsNews'])) {
  $pageNum_rsNews = $_GET['pageNum_rsNews'];
}
$startRow_rsNews = $pageNum_rsNews * $maxRows_rsNews;

$_GET['newssectionID'] = isset($_GET['newssectionID']) ? $_GET['newssectionID'] : $row_rsNewsPrefs['initialsection'];

$varSectionID_rsThisSection = "1";
if (isset($_GET['newssectionID'])) {
  $varSectionID_rsThisSection = $_GET['newssectionID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisSection = sprintf("SELECT * FROM newssection WHERE ID = %s OR %s = longID", GetSQLValueString($varSectionID_rsThisSection, "text"),GetSQLValueString($varSectionID_rsThisSection, "text"));
$rsThisSection = mysql_query($query_rsThisSection, $aquiescedb) or die(mysql_error());
$row_rsThisSection = mysql_fetch_assoc($rsThisSection);
$totalRows_rsThisSection = mysql_num_rows($rsThisSection);

$where = $row_rsThisSection['orderby'] == 4 ? " AND DATE(news.eventdatetime) >= CURDATE() " : "";

switch($row_rsThisSection['orderby']) {
	case 1: $orderby = "ORDER BY headline DESC,  displayfrom DESC"; break; // date posted (newest first)
	case 2: $orderby = "ORDER BY headline DESC,  displayfrom ASC"; break; // date posted (oldest first)
	case 3: $orderby = "ORDER BY headline DESC, news.ordernum ASC, displayfrom DESC"; break; // draggable 
	case 4: $orderby = "ORDER BY headline DESC, news.eventdatetime ASC, displayfrom ASC"; break; // event 
	default : $orderby = "ORDER BY headline DESC,  displayfrom DESC"; break; // default event date
}

if (isset($_GET['newssectionID'])) {
  $varSectionID_rsNews = $_GET['newssectionID'];
}
$varRegionID_rsNews = "1";
if (isset($regionID)) {
  $varRegionID_rsNews = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsNews = sprintf("SELECT news.ID, news.longID, title, summary,  news.imageURL, CHAR_LENGTH(news.body) AS bodylength, news.sectionID, newssection.sectioname, news.youtube, news.photogalleryID, news.attachment1, news.redirectURL, newssection.redirectURL AS sectionredirectURL FROM news LEFT JOIN newssection ON (news.sectionID = newssection.ID) WHERE status = 1 AND (DATE(news.displayfrom) <= CURDATE() OR news.displayfrom IS NULL) AND (news.displayto IS NULL OR DATE(news.displayto) >= CURDATE())  AND (news.regionID = %s OR news.regionID IS NULL OR news.regionID=0 OR  newssection.regionID=0) AND (news.sectionID = %s OR newssection.longID = %s OR news.sectionID IS NULL OR %s = '0') ".$where.$orderby."", GetSQLValueString($varRegionID_rsNews, "int"),GetSQLValueString($varSectionID_rsNews, "text"),GetSQLValueString($varSectionID_rsNews, "text"),GetSQLValueString($varSectionID_rsNews, "text"));
$rsNews = mysql_query($query_rsNews, $aquiescedb) or die(mysql_error());
$row_rsNews = mysql_fetch_assoc($rsNews);

if (isset($_GET['totalRows_rsNews'])) {
  $totalRows_rsNews = $_GET['totalRows_rsNews'];
} else {
  $all_rsNews = mysql_query($query_rsNews);
  $totalRows_rsNews = mysql_num_rows($all_rsNews);
}
$totalPages_rsNews = ceil($totalRows_rsNews/$maxRows_rsNews)-1;


if(isset($row_rsNews['sectionredirectURL']) && strlen($row_rsNews['sectionredirectURL'])>5) {
	header("location: ".$row_rsNews['sectionredirectURL']); exit;
}




$varUserGroup_rsNewsSection = "0";
if (isset($_SESSION['MM_UserGroup'])) {
  $varUserGroup_rsNewsSection = $_SESSION['MM_UserGroup'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsNewsSection = sprintf("SELECT ID, longID, sectioname , newssection.redirectURL FROM newssection WHERE statusID = 1 AND %s >= accesslevel ORDER BY ordernum ASC", GetSQLValueString($varUserGroup_rsNewsSection, "int"));
$query_limit_rsNews = sprintf("%s LIMIT %d, %d", $query_rsNews, $startRow_rsNews, $maxRows_rsNews);
$rsNews = mysql_query($query_limit_rsNews, $aquiescedb) or die(mysql_error());
$row_rsNews = mysql_fetch_assoc($rsNews);

$queryString_rsNews = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsNews") == false && 
        stristr($param, "totalRows_rsNews") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsNews = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsNews = sprintf("&totalRows_rsNews=%d%s", $totalRows_rsNews, $queryString_rsNews);
?>
<?php $newsfeed = isset($newsfeed) ? $newsfeed : $row_rsNewsPrefs['newspagefeed']; 
$newsfeedtitle = isset($newsfeedtitle) ? $newsfeedtitle : $row_rsNewsPrefs['newspagefeedtitle']; // backward compatible from prefs file 

$accesslevel = $row_rsThisSection['accesslevel'];
$groupID = $row_rsThisSection['groupreadID'];
require_once('../members/includes/restrictaccess.inc.php'); 

if(!defined("NEWS_INDEX_IMAGE_SIZE")) {
	define("NEWS_INDEX_IMAGE_SIZE", "t_");
}

if(!defined("NEWS_INDEX_SUMMARY_LENGTH")) {
	define("NEWS_INDEX_SUMMARY_LENGTH", 1000);
}

$body_class = "news";

$canonicalURL = "/news/";

?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = isset($row_rsThisSection['sectiontitle']) ? $row_rsThisSection['sectiontitle'] : (isset($row_rsThisSection['sectioname']) ? $row_rsThisSection['sectioname'] : "Posts"); echo $pageTitle." | ".$site_name; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<meta name="description" content="<?php echo  $row_rsThisSection['metadescription']; ?>" />
<link href="<?php echo (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == "on") ? "https://" : "http://"; echo $_SERVER['HTTP_HOST']."/"; ?>news/news.rss.php" rel="alternate" type="application/rss+xml" title="<?php echo $site_name; ?> News Feed" /><?php if(isset($row_rsThisSection['indexstyle']) && $row_rsThisSection['indexstyle']==1) { ?><script src="/3rdparty/masonry.js"></script>
<script>
$(window).load(function() {
 // executes when complete page is fully loaded, including all frames, objects and images
 var $grid = $('.items').masonry({
  		// options
  		itemSelector: '.item',
  		percentPosition: true
	});
	
});
</script>
<link href="/news/css/news-masonry.css" rel="stylesheet"  />
<?php } else { ?>
<link href="/news/css/newsDefault.css" rel="stylesheet"  /><?php } ?>
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" --><div id="newsIndexPage" class="container pageBody <?php  echo "newsSection".$row_rsThisSection['ID']; echo isset($row_rsThisSection['classes']) ? $row_rsThisSection['classes'] : ""; ?>">
      <div class="crumbs"><div><span class="you_are_in">You are in: </span><ol itemscope itemtype="http://schema.org/BreadcrumbList">
            <li itemprop="itemListElement" itemscope
      itemtype="http://schema.org/ListItem"><a itemprop="item" href="/"><span itemprop="name">Home</span></a>
      <meta itemprop="position" content="1" /></li>
	  
	  
	  <li itemprop="itemListElement" itemscope
      itemtype="http://schema.org/ListItem">
	  <a itemprop="item" href="<?php echo $canonicalURL; ?>"><span itemprop="name">
	  <?php echo isset($row_rsNews['sectioname']) ? $row_rsNews['sectioname'] : "News"; ?>
      </span></a><meta itemprop="position" content="2" />
      </li></ol></div></div>
      
      
       <?php if($row_rsNewsPrefs['sectionindextype']==3 && isset($row_rsThisSection['parentsectionID'])) {
		   $sectionID = $row_rsThisSection['parentsectionID']; if($sectionID>0) {
		  $_GET['sectionID'] = $row_rsThisSection['sectionID'];
		  $_GET['parentsectionID'] = $row_rsThisSection['parentsectionID'];
		  $_GET['articleID'] = $row_rsThisSection['articleID'];
		   $submenu =  buildMenu($sectionID,1,"",1);
		   if(is_readable(SITE_ROOT.'/local/includes/articlesectionmenu.inc.php')) { 
	require(SITE_ROOT.'/local/includes/articlesectionmenu.inc.php');
	} else { 
	require(SITE_ROOT.'/articles/includes/articlesectionmenu.inc.php');
	 } 
		   }
	  } else  {?>
    
	 
	 <?php if($totalRows_rsNewsSection>1 && $row_rsNewsPrefs['sectionindextype']>0 && $row_rsNewsPrefs['sectionindextype']<3) { ?> <div id="newsCategories" class="subMenu">
     <?php if($row_rsNewsPrefs['sectionindextype']==2) { // select menu ?>
     <form action="index.php" method="get"><select name="newssectionID" id="newssectionID" onChange="this.form.submit()">
       <option value="0" <?php if (!(strcmp(0, $varSectionID_rsNews))) {echo "selected=\"selected\"";} ?>>View all</option>
       <?php
do {  
?>
       <option value="<?php echo $row_rsNewsSection['ID']?>"<?php if (!(strcmp($row_rsNewsSection['ID'], $varSectionID_rsNews))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsNewsSection['sectioname']?></option>
       <?php
} while ($row_rsNewsSection = mysql_fetch_assoc($rsNewsSection));
  $rows = mysql_num_rows($rsNewsSection);
  if($rows > 0) {
      mysql_data_seek($rsNewsSection, 0);
	  $row_rsNewsSection = mysql_fetch_assoc($rsNewsSection);
  }
?>
     </select></form>
      
      <?php } else { // menu ?>
      
       <ul>
          <?php do { 
		  if(isset($row_rsNewsSection['redirectURL'])) {
			  $link = $row_rsNewsSection['redirectURL'];
		  } else {
		   $link = ((defined("MOD_REWRITE") || isset($_SERVER['HTTP_MOD_REWRITE'])) && isset($row_rsNewsSection['longID']) && isset($row_rsNewsSection['longID'])) ? "/items/".$row_rsNewsSection['longID']."/" : "/news/index.php?newssectionID=".$row_rsNewsSection['ID']; 
		  } ?>
            <li id="newsSectionLink<?php echo $row_rsNewsSection['ID']; ?>" <?php if($row_rsNewsSection['ID']== $row_rsThisSection['ID']) { echo "class=\"currentItem\""; } ?> ><a href="<?php echo $link; ?>"><?php echo $row_rsNewsSection['sectioname']; ?></a></li>
            <?php } while ($row_rsNewsSection = mysql_fetch_assoc($rsNewsSection)); ?>
        </ul>
      
	 <?php  } ?></div>  
	 <?php  } 
	 
	  }
	  
	  ?><h1 id="newsTitle"><?php echo isset($row_rsThisSection['sectioname']) ? $row_rsThisSection['sectioname'] : $site_name." News"; ?></h1>  
      <div id="newsIndexContent"> <?php require_once('../core/includes/alert.inc.php'); ?>
   
   <?php echo $row_rsThisSection['description']; ?>
      <?php if ($totalRows_rsNews == 0) { // Show if recordset empty ?>
        <p>There are currently no posts. Check back soon.</p>
        <?php } // Show if recordset empty ?>
      <?php if ($totalRows_rsNews > 0) { // Show if recordset not empty ?>
        <p id="newsItemsCount">Posts <?php echo ($startRow_rsNews + 1) ?> to <?php echo min($startRow_rsNews + $maxRows_rsNews, $totalRows_rsNews) ?> of <?php echo $totalRows_rsNews ?>.&nbsp;&nbsp;<span id="newsFeedLink"><a href="/news/news.rss.php<?php echo isset($row_rsNews['sectionID']) ? "?newssectionID=".$row_rsNews['sectionID'] : ""; ?>" class="link_rss">Subscribe to  <?php echo isset($row_rsThisSection['sectioname']) ? $row_rsThisSection['sectioname'] : "news"; ?> feed</a></span></p>
        <div class="items">
        <?php do { 
		
		
			$link = ((defined("MOD_REWRITE") || isset($_SERVER['HTTP_MOD_REWRITE'])) && isset($row_rsNews['longID']) && isset($row_rsThisSection['longID'])) ? "/items/".$row_rsThisSection['longID']."/".$row_rsNews['longID'] : "/news/story.php?newssectionID=".$row_rsNews['sectionID']."&amp;newsID=".($row_rsNews['ID']+100);
	
		
		
		
		 ?>
          <div class="newsItem-wrapper item<?php echo $item; ?> <?php echo (!isset($row_rsThisSection['indexstyle']) || $row_rsThisSection['indexstyle']==0) ? $row_rsNewsPrefs['item_class'] : ""; ?>"  itemscope itemtype="http://schema.org/NewsArticle">
          <div class="<?php echo (!isset($row_rsThisSection['indexstyle']) || $row_rsThisSection['indexstyle']==0) ? $row_rsNewsPrefs['image_class'] : "";  ?>">
          <figure><a href="<?php echo $link; ?>" title="<?php echo $row_rsNews['title']; ?>">
            
            <?php  $imageURL = isset($row_rsNews['imageURL2']) ? $row_rsNews['imageURL2'] : $row_rsNews['imageURL'];
			$imagesize = isset($row_rsNews['imageURL2']) ? "" : $row_rsNewsPrefs['imagesize_index'];
		if($imageURL!="" || $row_rsNewsPrefs['usedefaultimage']==1) {	?><img src="<?php 
			 echo $imageURL!="" ? getImageURL($imageURL, $imagesize): (isset($row_rsNewsPrefs['defaultImageURL']) ? getImageURL($row_rsNewsPrefs['defaultImageURL'], $row_rsNewsPrefs['imagesize_index']) : "/news/images/newspaper.png"); ?>" alt="<?php echo $row_rsNews['title']; ?>" class="newsIndexImage <?php echo $row_rsNewsPrefs['imagesize_index']; ?>"  />
            <?php } ?></a></figure>
            </div>
             <div class="<?php echo (!isset($row_rsThisSection['indexstyle']) || $row_rsThisSection['indexstyle']==0) ? $row_rsNewsPrefs['text_class'] : "";?>">
             
            <h2 class="newsIndexItemTitle"><a href="<?php echo $link; ?>" ><?php echo $row_rsNews['title']; ?></a></h2>
            <span class="newsIndexItemSummary" itemprop="description"><?php echo nl2br(truncate( $row_rsNews['summary'],NEWS_INDEX_SUMMARY_LENGTH)); ?>
              <?php 
			  
	
			  
			if($row_rsNews['bodylength'] > 5 || isset($row_rsNews['youtube']) || isset($row_rsNews['photogalleryID']) || isset($row_rsNews['redirectURL']) || isset($row_rsNews['attachment1'])) { ?>
              <span class="newsIndexReadMore">
              <a href="<?php echo $link; ?>" title="<?php echo $row_rsNews['title']; ?>">Read more</a></span>
              
              <?php } ?></span>
            
          </div>
          <hr></div>
          <?php } while ($row_rsNews = mysql_fetch_assoc($rsNews)); ?></div>
        <?php } // Show if recordset not empty ?>
<div class="clearfix">
<?php echo createPagination($pageNum_rsNews,$totalPages_rsNews,"rsNews");?>  
</div>
     
      </div><!-- end content -->
        
        
       <?php  if(is_readable(SITE_ROOT.'/local/includes/newsfooter.inc.php')) { 
	require(SITE_ROOT.'/local/includes/newsfooter.inc.php');
	}  ?>
        
<?php if(isset($_SESSION['MM_UserGroup']) && $_SESSION['MM_UserGroup'] >=$row_rsThisSection['editaccess']) { ?>
<div id="suggestNews"><a href="members/add_news.php?sectionID=<?php echo isset($_GET['newssectionID']) ? htmlentities($_GET['newssectionID']) : 1; ?>">Submit a Post...</a></div>
<?php } ?>
</div><!-- end page -->
     
    <!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsNews);

mysql_free_result($rsThisSection);

mysql_free_result($rsNewsPrefs);

mysql_free_result($rsNewsSection);
?>
