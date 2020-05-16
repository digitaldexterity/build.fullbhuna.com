<?php require_once('../Connections/aquiescedb.php'); ?><?php require_once('../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../mail/includes/sendmail.inc.php'); ?><?php require_once('../articles/includes/functions.inc.php'); ?>
<?php require_once('../core/includes/framework.inc.php'); ?><?php require_once('../core/includes/autolinks.inc.php'); ?><?php require_once('../members/includes/userfunctions.inc.php'); ?><?php require_once('includes/newsfunctions.inc.php'); ?>
<?php $regionID = (isset($regionID) && intval($regionID)>0) ? intval($regionID) : 1;
?>
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


											

$currentPage = $_SERVER["PHP_SELF"];

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID, users.firstname, users.surname FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
	addNewsComment($_POST['commenttext'], $_POST['newsID'], $row_rsLoggedIn['ID']);
}


if(isset($_GET['deletecommentID']) && intval($_GET['deletecommentID'])>0) {
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$select = "SELECT createdbyID FROM comments WHERE ID = ".intval($_GET['deletecommentID'])." LIMIT 1";
	$result = mysql_query($select, $aquiescedb) or die(mysql_error());
	$row = mysql_fetch_assoc($result);
	
	if($row_rsLoggedIn['ID']==$row['createdbyID'] || $row_rsLoggedIn['ID']==$row_rsNewsStory['postedbyID'] || $_SESSION['MM_UserGroup']>7) {
	$delete = "DELETE FROM comments WHERE ID = ".intval($_GET['deletecommentID']);
	
	mysql_query($delete, $aquiescedb) or die(mysql_error());
	}
}





$varNewsID_rsNewsStory = "-1";
if (isset($_GET['newsID'])) {
  $varNewsID_rsNewsStory = $_GET['newsID'];
}
$varPreview_rsNewsStory = "false";
if (isset($_GET['preview'])) {
  $varPreview_rsNewsStory = $_GET['preview'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsNewsStory = sprintf("SELECT news.*,  newssection.sectioname, newssection.longID AS sectionlongID, newssection.accesslevel, newssection.allowcomments, users.firstname, users.surname, newssection.classes, newssection.sectionID AS pagesectionID, newssection.parentsectionID, newssection.noindex, newssection.articleID, CONCAT(users.firstname, ' ', users.surname) AS author, newssection.groupreadID, newssection.wysiwyg FROM news LEFT JOIN newssection ON (news.sectionID = newssection.ID)  LEFT JOIN users ON (news.modifiedbyID = users.ID) WHERE (CAST(news.ID+100 AS CHAR) = %s OR news.longID = %s) AND (%s = 'true' OR news.status = 1)", GetSQLValueString($varNewsID_rsNewsStory, "text"),GetSQLValueString($varNewsID_rsNewsStory, "text"),GetSQLValueString($varPreview_rsNewsStory, "text"));
$rsNewsStory = mysql_query($query_rsNewsStory, $aquiescedb) or die(mysql_error());
$row_rsNewsStory = mysql_fetch_assoc($rsNewsStory);
$totalRows_rsNewsStory = mysql_num_rows($rsNewsStory);
// not found
if($totalRows_rsNewsStory == 0) {
	$msg = "Sorry we could not find the post you were looking for. It may no longer be available.";
	$msg .= (isset($_SESSION['MM_UserGroup']) && $_SESSION['MM_UserGroup']==10 ) ? urlencode(" [Admin: newsID=".$_GET['newsID']."]") : "";
	/*
	$url = "index.php?msg=".urlencode($msg);
	header( "HTTP/1.1 301 Moved Permanently" ); 
	header( "Location: ".$url);  exit;
	*/
	//correct PHP for 404
	$url = "news/index.php";
		http_response_code(404);
		include(SITE_ROOT.$url); // provide your own HTML for the error page
		die();	
}

//FIX NON-FRIENDLY URLs

$canonicalURL = ((defined("MOD_REWRITE") || isset($_SERVER['HTTP_MOD_REWRITE'])) && isset($row_rsNewsStory['longID']) && isset($row_rsNewsStory['sectionlongID'])) ? "/items/".$row_rsNewsStory['sectionlongID']."/".$row_rsNewsStory['longID'].$newQueryString : "/news/story.php?newssectionID=".$row_rsNewsStory['sectionID']."&newsID=".($row_rsNewsStory['ID']+100);
$canonicalURL = htmlentities($canonicalURL, ENT_COMPAT, "UTF-8");


if((defined("MOD_REWRITE") || isset($_SERVER['HTTP_MOD_REWRITE'])) && strpos($_SERVER['REQUEST_URI'],"newsID=")!==false && !isset($_GET['preview'])) {
	// add query string to frienly URL - largely for email tracking URLs
	if (!empty($_SERVER['QUERY_STRING'])) {
	  $params = explode("&", $_SERVER['QUERY_STRING']);
	  $newParams = array();
	  foreach ($params as $param) {
		if (stristr($param, "newsID") == false && 
			stristr($param, "newssectionID") == false) {
		  array_push($newParams, $param);
		}
	  }
	  if (count($newParams) != 0) {
		$newQueryString =  implode("&", $newParams);
	  }
	}	
	
	if(strpos($canonicalURL,$_SERVER['REQUEST_URI'])===false) {
		header("HTTP/1.1 301 Moved Permanently"); 
		$redirectURL = $canonicalURL;
		if(isset($newQueryString)) {
			$redirectURL .= strpos($redirectURL,"?")>0 ? "&" : "?";
			$redirectURL .= $newQueryString;
		}
			
		header("location: ".$redirectURL); exit;
	}
}










mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsMediaPrefs = "SELECT * FROM mediaprefs WHERE ID = ".$regionID;
$rsMediaPrefs = mysql_query($query_rsMediaPrefs, $aquiescedb) or die(mysql_error());
$row_rsMediaPrefs = mysql_fetch_assoc($rsMediaPrefs);
$totalRows_rsMediaPrefs = mysql_num_rows($rsMediaPrefs);

$varGalleryID_rsSlideshow = "-1";
if (isset($row_rsNewsStory['photogalleryID'])) {
  $varGalleryID_rsSlideshow = $row_rsNewsStory['photogalleryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsSlideshow = sprintf("SELECT ID, imageURL, title FROM photos WHERE categoryID = %s ORDER BY ordernum ASC", GetSQLValueString($varGalleryID_rsSlideshow, "int"));
$rsSlideshow = mysql_query($query_rsSlideshow, $aquiescedb) or die(mysql_error());
$row_rsSlideshow = mysql_fetch_assoc($rsSlideshow);
$totalRows_rsSlideshow = mysql_num_rows($rsSlideshow);

$galleryID = $row_rsNewsStory['photogalleryID']; 

$varGalleryID_rsThisGallery = "-1";
if (isset($galleryID)) {
  $varGalleryID_rsThisGallery = $galleryID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisGallery = sprintf("SELECT photocategories.ID, photocategories.active FROM photocategories LEFT JOIN photos ON (photos.categoryID = photocategories.ID) WHERE photocategories.ID = %s AND photos.ID IS NOT NULL AND photocategories.active = 1", GetSQLValueString($varGalleryID_rsThisGallery, "int"));
$rsThisGallery = mysql_query($query_rsThisGallery, $aquiescedb) or die(mysql_error());
$row_rsThisGallery = mysql_fetch_assoc($rsThisGallery);
$totalRows_rsThisGallery = mysql_num_rows($rsThisGallery);





if(isset($row_rsNewsStory['redirectURL']) && strlen($row_rsNewsStory['redirectURL'])>5) {
	header("location: ".$row_rsNewsStory['redirectURL']); exit;
}





$body_class = "news newssection".$row_rsNewsStory['sectionID'];
$rank = isset($_SESSION['MM_UserGroup']) ? $_SESSION['MM_UserGroup'] : 0; 
 
 
if(thisUserHasAccess($row_rsNewsStory['accesslevel'], $row_rsNewsStory['groupreadID'], $row_rsLoggedIn['ID']) || (isset($_GET['key']) && $_GET['key']==md5(PRIVATE_KEY.$row_rsNewsStory['ID']) )) {
		// can access
} else {
	header("location: /login/index.php?msg=".urlencode("To view this page you must log in with the correct access credentials.").
	"&accesscheck=".
	urlencode($_SERVER['REQUEST_URI'])); 
}

$newsbody = ($row_rsNewsStory['wysiwyg']==1) ?  articleMerge($row_rsNewsStory['body']) : articleMerge(nl2br(addLinks($row_rsNewsStory['body'])));

    
?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = $row_rsNewsStory['sectioname']." - ".$row_rsNewsStory['title']; echo $pageTitle." | ".$site_name; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<?php 
 if($row_rsNewsStory['noindex'] ==1 ) {
	 echo "<meta name=\"robots\" content=\"noindex, nofollow\">"; 
}?>
<link rel="canonical" href="<?php $canonicalURL = getProtocol()."://". $_SERVER['HTTP_HOST'].$canonicalURL;  echo $canonicalURL; 
$metadescription = (strlen($row_rsNewsStory['metadescription'])>20) ? htmlentities($row_rsNewsStory['metadescription'], ENT_COMPAT, "UTF-8" ) : htmlentities(strip_tags($row_rsNewsStory['summary']), ENT_COMPAT, "UTF-8");  ?>" />
<?php if(isset($row_rsNewsStory['author'])) { ?>
<meta name="author" content="<?php echo $row_rsNewsStory['author']; ?>" />
<?php } ?>
<?php if (isset($row_rsNewsStory['imageURL'])) { 
		  $shareimageURL = getProtocol()."://". $_SERVER['HTTP_HOST'].getImageURL($row_rsNewsStory['imageURL'],"large");
		  } ?>
<meta  name="description" content="<?php echo $metadescription; ?>">
<!-- Twitter Card data -->
<meta name="twitter:card" content="summary">
<!--<meta name="twitter:site" content="@publisher_handle">
<meta name="twitter:creator" content="@author_handle">-->
<meta name="twitter:title" content="<?php echo htmlentities($row_rsNewsStory['title'], ENT_COMPAT, "UTF-8"); ?>">
<meta name="twitter:description" content="<?php echo $metadescription; ?>">
<?php if (isset( $shareimageURL )) { ?>
<meta name="twitter:image" content="<?php echo  htmlentities($shareimageURL, ENT_COMPAT, "UTF-8"); ?>">
<?php } ?>
<meta property="og:site_name" content="<?php echo htmlentities($site_name, ENT_COMPAT, "UTF-8"); ?>"/>
<meta property="og:title" content="<?php echo htmlentities($row_rsNewsStory['title'], ENT_COMPAT, "UTF-8"); ?>"/>
<meta property="og:description" content="<?php echo $metadescription; ?>" />
<meta property="og:url" content="<?php  echo $canonicalURL; ?>"/>
<meta property="og:type" content="article" />
<?php if (isset( $shareimageURL )) { ?>
<meta property="og:image" content="<?php echo  htmlentities($shareimageURL, ENT_COMPAT, "UTF-8"); ?>"/>
<?php } ?>

<script src="/3rdparty/jquery/jquery.timeago.js"></script>
<script >
jQuery(document).ready(function() {
  jQuery("abbr.timeago").timeago();
								});
 if (top.location.href != window.location.href) { /* opened in lightwindow iframe, so remove header and footer etc */
 document.write("<link rel='stylesheet' href='/css/layout_lightbox.css' type='text/css'>"); 
 }
 </script>
<style>
.linkNewsEvents a {
	background-image: none;
}

.news-slideshow {
	overflow:hidden;
}
 </style>
<?php

if($row_rsMediaPrefs['uselightbox']==1) { ?>
<script src="/photos/scripts/slimbox-2/js/slimbox2-autosize.js"></script>
<link href="/photos/scripts/slimbox-2/css/slimbox2-autosize.css" rel="stylesheet" type="text/css" />
<script>
    $(function() {
        $('a.lightbox').slimbox();
    });
</script>
<?php } ?>
<link href="/news/css/newsDefault.css" rel="stylesheet" type="text/css" />
<link href="/core/seo/css/defaultShare.css" rel="stylesheet" type="text/css" />
<link href="/photos/css/defaultGallery.css" rel="stylesheet" type="text/css"><?php if(
$row_rsNewsStory['slideshow']==1 && isset($row_rsNewsStory['photogalleryID']) && $row_rsNewsStory['photogalleryID'] > 0 && $totalRows_rsSlideshow>0) { $slideshow = true;  ?><script src="/3rdparty/jquery/jquery.cycle2.min.js"></script>
       <script>
        $(function() {
        $(".news-slideshow").cycle({	"auto-height" : 1,  "slides" : "> img"	});
    });
    </script><?php } ?>
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" -->
    <div id="newsStoryPage" class="container pageBody <?php  echo "newsSection".$row_rsNewsStory['sectionID']; echo isset($row_rsNewsStory['classes']) ? $row_rsNewsStory['classes'] : ""; ?>">
     
      <div class="crumbs"><div><span class="you_are_in">You are in: </span>
      
      <ol itemscope itemtype="http://schema.org/BreadcrumbList">
            <li itemprop="itemListElement" itemscope
      itemtype="http://schema.org/ListItem"><a itemprop="item" href="/"><span itemprop="name">Home</span></a>
      <meta itemprop="position" content="1" /></li>
      
     <li itemprop="itemListElement" itemscope
      itemtype="http://schema.org/ListItem"> 
      <a itemprop="item" href="<?php echo ((defined("MOD_REWRITE") || isset($_SERVER['HTTP_MOD_REWRITE'])) && isset($row_rsNewsStory['sectionlongID'])) ? "/items/".$row_rsNewsStory['sectionlongID']."/" : "index.php?newssectionID=".$row_rsNewsStory['sectionID']; ?>"><span itemprop="name"><?php echo isset($row_rsNewsStory['sectioname']) ? $row_rsNewsStory['sectioname'] : "News"; ?></span></a>
       <meta itemprop="position" content="2" />
      </li>
      
	  
	  <li itemprop="itemListElement" itemscope
      itemtype="http://schema.org/ListItem">
	  <a itemprop="item" href="<?php echo $canonicalURL; ?>"><span itemprop="name">
	  <?php echo $row_rsNewsStory['title']; ?></span></a> <meta itemprop="position" content="3" /></li></ol>
      
      
      </div></div><!-- end crumbs -->
      
      <?php if($row_rsNewsPrefs['sectionindextype']==3 && isset($row_rsNewsStory['parentsectionID'])) {
		   $sectionID = $row_rsNewsStory['parentsectionID'];
		  $_GET['sectionID'] = $row_rsNewsStory['pagesectionID'];
		  $_GET['parentsectionID'] = $row_rsNewsStory['parentsectionID'];
		  $_GET['articleID'] = $row_rsNewsStory['articleID'];
		   $submenu =  buildMenu($sectionID,1,"",1);
		   if(is_readable(SITE_ROOT.'/local/includes/articlesectionmenu.inc.php')) { 
	require(SITE_ROOT.'/local/includes/articlesectionmenu.inc.php');
	} else { 
	require(SITE_ROOT.'/articles/includes/articlesectionmenu.inc.php');
	 } 
	  }?>
     
     <article>
      <div id="newsStoryContent" itemscope itemtype="http://schema.org/NewsArticle">
      <link itemprop="mainEntityOfPage" value="<?php  echo $canonicalURL; ?>" itemscope itemtype="http://schema.org/WebPage">
      <span itemprop="publisher"  itemscope itemtype="http://schema.org/Organization">
          <meta itemprop="name"  content="<?php  echo $site_name; ?>" /><span itemprop="logo" itemscope itemtype="http://schema.org/ImageObject"  /><meta itemprop="url"  content="https://www.digitaldexterity.co.uk/news/images/newspaper.png" /></span></span>
    
        <h1 itemprop="headline"><?php echo $row_rsNewsStory['title']; ?></h1>
        
       <?php  if (isset($slideshow)) { ?>
       <div class="news-image news-slideshow" itemprop="image" itemscope itemtype="http://schema.org/ImageObject">
         
           <?php do { ?>
            <img src="<?php echo getImageURL($row_rsSlideshow['imageURL'],$row_rsNewsPrefs['imagesize_story']); ?>" itemprop="url" >
              
             <?php } while ($row_rsSlideshow = mysql_fetch_assoc($rsSlideshow)); ?>
         
       </div><!-- end slideshow -->
        
        <?php } else if (isset($row_rsNewsStory['imageURL'])) { ?>
        <figure>
          <div id="newsStoryImage" class="news-image" itemprop="image" itemscope itemtype="http://schema.org/ImageObject"><a href="/core/images/view_large_image.php?imageURL=<?php echo $row_rsNewsStory['imageURL']; ?>" target="_top" title="Click to enlarge this image"><img src="<?php $imageURL = getImageURL($row_rsNewsStory['imageURL'],$row_rsNewsPrefs['imagesize_story']); echo $imageURL; $imagesize = getimagesize(SITE_ROOT.$imageURL); ?>" alt="<?php echo $row_rsNewsStory['imagealt']; ?> - Click for larger version"  class="<?php echo $row_rsNewsPrefs['imagesize_story']; ?>" itemprop="url" =  /></a>
          <meta itemprop="width"  content="<?php echo $imagesize[0]; ?>" />
          <meta itemprop="height"  content="<?php echo $imagesize[1]; ?>" /></div></figure>
          <?php } ?>
        <p class="text-muted" id="newsLastUpdated"><span class="dateposted" >Last updated:
          <span><span class="time"><?php  $lastupdated = isset($row_rsNewsStory['displayfrom']) ? $row_rsNewsStory['displayfrom'] : $row_rsNewsStory['posteddatetime']; $lastupdated = (isset($row_rsNewsStory['modifieddatetime']) && $row_rsNewsStory['modifieddatetime']>$lastupdated) ? $row_rsNewsStory['modifieddatetime'] : $lastupdated;  echo date('g.ia, ',strtotime($lastupdated)); ?></span><span class="date" ><?php  echo date('l jS F Y',strtotime($lastupdated)); ?></span></span></span>
          <meta itemprop="datePublished" content="<?php  echo date('Y-m-d',strtotime($row_rsNewsStory['posteddatetime'])); ?>" />
          <meta itemprop="dateModified" content="<?php  echo date('Y-m-d',strtotime($lastupdated)); ?>" />         
          <span class="author" > by <span itemprop="author"><?php echo $row_rsNewsStory['author']; ?></span></span></p>
          
          
          
        <div id="newsStoryText">
         <div id="newsStorySummary" itemprop="description"><?php echo ($row_rsNewsStory['wysiwyg']==1) ? str_replace("<p>","<p class='lead'>",$row_rsNewsStory['summary']) :  "<p class='lead'>".nl2br(addLinks($row_rsNewsStory['summary']))."</p>"; ?></div>
          <p><?php echo $newsbody; ?></p>
        </div>
        <?php  echo isset($row_rsNewsStory['youtube']) ? $row_rsNewsStory['youtube'] : ""; ?>
        <div class="gallery clearfix notLightbox">
          <?php if ($row_rsNewsStory['slideshow']!=1 && $totalRows_rsThisGallery > 0 && is_readable('../photos/includes/gallery.inc.php')) { 
		  
			$showAllPhotos = true;
	 require_once('../photos/includes/gallery.inc.php');
 } ?></div><!-- end gallery -->
          <?php if(isset($row_rsNewsStory['attachment1']) && $row_rsNewsStory['attachment1']!="") { ?>
          <p class="attachment"><?php showMedia($row_rsNewsStory['attachment1']); ?></p>
          <?php } ?>
          
          <div class="news-back">
          <?php $newsbackURL = isset($_GET['returnURL']) ? htmlentities($_GET['returnURL']) : ( ((defined("MOD_REWRITE") || isset($_SERVER['HTTP_MOD_REWRITE'])) && isset($row_rsNewsStory['sectionlongID'])) ? "/items/".$row_rsNewsStory['sectionlongID'] : "index.php?newssectionID=".$row_rsNewsStory['sectionID'] );; ?><a href="<?php echo $newsbackURL; ?>" class="btn btn-default btn-secondary" >Back to posts</a></div>
          
          
        </div><!-- end news story content
         -->
        </article>
        <?php if($row_rsNewsPrefs['newsshare']==1 && (!isset($row_rsNewsStory['accesslevel']) || $row_rsNewsStory['accesslevel']==0)) {
	 require('../core/share/includes/share.inc.php'); 
 }?>
 <?php require_once('includes/newstags.inc.php'); ?>
        <?php require_once('includes/newscomments.inc.php'); ?>
    
 
    </div>
    <!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsNewsStory);

mysql_free_result($rsNewsPrefs);



mysql_free_result($rsLoggedIn);

mysql_free_result($rsMediaPrefs);

mysql_free_result($rsSlideshow);

mysql_free_result($rsThisGallery);


?>
