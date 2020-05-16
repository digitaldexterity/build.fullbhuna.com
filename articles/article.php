<?php require_once('../Connections/aquiescedb.php'); ?>
<?php require_once('includes/functions.inc.php'); ?>
<?php require_once('../core/includes/framework.inc.php'); ?>
<?php require_once('../members/includes/userfunctions.inc.php'); ?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?>
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
$regionID = (isset($regionID) && $regionID >0) ? $regionID  : 1;
$regionID = (isset($_GET['regionID']) && intval($_GET['regionID'])>0) ? intval($_GET['regionID']) : $regionID; 

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsArticlePrefs = "SELECT * FROM articleprefs WHERE ID = ".$regionID;
$rsArticlePrefs = mysql_query($query_rsArticlePrefs, $aquiescedb) or die(mysql_error());
$row_rsArticlePrefs = mysql_fetch_assoc($rsArticlePrefs);
$totalRows_rsArticlePrefs = mysql_num_rows($rsArticlePrefs);


if(!isset($totalRows_rsLoggedIn)) {
	
$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID, usertypeID, firstname FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

}

require_once(SITE_ROOT.'articles/includes/quickedit_save.inc.php'); 

$varArticleID_rsArticle = "-1";
if (isset($_GET['articleID'])) {
  $varArticleID_rsArticle = $_GET['articleID'];
}
$varPreview_rsArticle = "0";
if (isset($_GET['preview'])) {
  $varPreview_rsArticle = $_GET['preview'];
}
$varRegionID_rsArticle = "1";
if (isset($regionID)) {
  $varRegionID_rsArticle = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsArticle = sprintf("SELECT article.*, articlesection.accesslevel AS sectionaccesslevel, articlesection.`description` AS section, articlesection.longID AS sectionlongID, parentsection.`description` AS parentsection, parentsection.ID AS parentsectionID, parentsection.longID AS parentsectionlongID, articlesection.class AS sectionclass, articlesection.groupreadID, CONCAT(users.firstname, ' ',users.surname) AS author FROM article LEFT JOIN articlesection ON (article.sectionID = articlesection.ID) LEFT JOIN articlesection AS parentsection ON (articlesection.subsectionofID = parentsection.ID) LEFT JOIN users ON (article.modifiedbyID = users.ID) WHERE (article.versionofID IS NULL OR %s =1) AND (articlesection.regionID = %s OR articlesection.regionID=0 OR article.regionID = %s) AND (CAST(article.ID AS CHAR) = %s OR article.longID=%s)", GetSQLValueString($varPreview_rsArticle, "int"),GetSQLValueString($varRegionID_rsArticle, "int"),GetSQLValueString($varRegionID_rsArticle, "int"),GetSQLValueString($varArticleID_rsArticle, "text"),GetSQLValueString($varArticleID_rsArticle, "text"));
$rsArticle = mysql_query($query_rsArticle, $aquiescedb) or die(mysql_error());
$row_rsArticle = mysql_fetch_assoc($rsArticle);
$totalRows_rsArticle = mysql_num_rows($rsArticle);

$canonicalURL= $_SERVER['REQUEST_URI'];
$canonicalURL = htmlentities($canonicalURL, ENT_COMPAT, "UTF-8");
//FIX NON-FRIENDLY URLs
if(!isset($_GET['preview']) && (defined("MOD_REWRITE") || isset($_SERVER['HTTP_MOD_REWRITE'])) && strpos($_SERVER['REQUEST_URI'],"articleID=")!==false) {
	$canonicalURL = articleLink($row_rsArticle['ID'], $row_rsArticle['longID'], $row_rsArticle['sectionID'], $row_rsArticle['sectionlongID']);
	if(strpos($canonicalURL,$_SERVER['REQUEST_URI'])===false && (!isset($_SESSION['redirect'] ) || $_SESSION['redirect'] != $_SERVER['REQUEST_URI'])) {	
		$_SESSION['redirect'] = $_SERVER['REQUEST_URI'];
		header( "HTTP/1.1 301 Moved Permanently" ); 
		header( "Status: 301 Moved Permanently" );
		header("location: ".$canonicalURL); exit;
	}
}

if($totalRows_rsArticle == 0) { // not found, try old ID
	$select = "SELECT article.ID, article.longID, article.sectionID, articlesection.longID AS sectionlongID  FROM article LEFT JOIN articlesection ON (article.sectionID = articlesection.ID) WHERE article.versionofID IS NULL AND article.statusID = 1 AND article.oldlongID=". GetSQLValueString($varArticleID_rsArticle, "text");
	$result = mysql_query($select, $aquiescedb) or die(mysql_error());
	$row = mysql_fetch_assoc($result);
	if(mysql_num_rows($result)>0) {
		$url = articleLink($row['ID'], $row['longID'], $row['sectionID'], $row['sectionlongID']); 
		header( "HTTP/1.1 301 Moved Permanently" ); 
		header( "Status: 301 Moved Permanently" );
		header("location: ".$url); exit;
	} else { // not found
		$notfound = true;		
	}	
}


// NOT FOUND
if(isset($notfound) || ($row_rsArticle['statusID']!=1 && (!isset($_SESSION['MM_UserGroup']) || $_SESSION['MM_UserGroup']<7))) {
	if(isset($row_rsArticlePrefs['notfoundURL']) && strlen($row_rsArticlePrefs['notfoundURL'])>0 && isset($_GET['sectionID'])) {
			$url = $row_rsArticlePrefs['notfoundURL'];
		} else {		
			$url = "articles/index.php";
		}
		//correct PHP for 404
		http_response_code(404);
		include(SITE_ROOT.$url); // provide your own HTML for the error page
		die();	
} // end not found

$pageTitle = $row_rsArticle['title']; 
if (isset($row_rsArticle['redirectURL']) && $row_rsArticle['redirectURL']!="") { // redirect in article
	if(substr($row_rsArticle['redirectURL'],0,1) == "#") { // anchor
		$url = $_SERVER['HTTP_REFERER'].$row_rsArticle['redirectURL'];
	} else if(substr($row_rsArticle['redirectURL'],0,7) == "mailto:") {
		// mailto link
		$url = $row_rsArticle['redirectURL'];
	} else { // redirect
		$urlparts = parse_url($row_rsArticle['redirectURL']);
		$url = isset($urlparts['scheme']) ? $urlparts['scheme']."://" : "";
		$url .= isset($urlparts['host']) ? $urlparts['host'] : "";
		$url .= isset($urlparts['path']) ? $urlparts['path'] : "";
		$url .= isset($urlparts['query']) ? "?".$urlparts['query'] : "";
		$url .= isset($urlparts['fragment']) ? "#".$urlparts['fragment'] : "";
		$url = str_replace("hash=", "#", $url); // for mod rewrite we need no use hash in query string
	}
	require_once('../core/seo/includes/seo.inc.php'); 
	trackPage($pageTitle);
	if(isset($row_rsArticle['redirecttype']) && $row_rsArticle['redirecttype']==301) {
		header( "HTTP/1.1 301 Moved Permanently" ); 
		header( "Status: 301 Moved Permanently" );
	}
	
	header("location: ".$url); exit;
} 


$currentPage = $_SERVER["PHP_SELF"];




mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPrefs = "SELECT userscansignup, userscanlogin FROM preferences";
$rsPrefs = mysql_query($query_rsPrefs, $aquiescedb) or die(mysql_error());
$row_rsPrefs = mysql_fetch_assoc($rsPrefs);
$totalRows_rsPrefs = mysql_num_rows($rsPrefs);




if($row_rsArticle['allowcomments']==1) { // allow comments
require_once('../mail/includes/sendmail.inc.php'); 
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


if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) { 

$insertSQL = sprintf("INSERT INTO comments (articleID, commenttext, createdbyID, createddatetime, statusID) VALUES (%s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['articleID'], "int"),
                       GetSQLValueString($_POST['commenttext'], "text"),
                       GetSQLValueString($_POST['createdbyID'], "int"),
                       GetSQLValueString($_POST['createddatetime'], "date"),
                       GetSQLValueString($_POST['statusID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($insertSQL, $aquiescedb) or die(mysql_error());
  
  // email all concerned
$select = "SELECT x.userID, x.useremail FROM ((SELECT users.ID AS userID, users.email AS useremail FROM comments LEFT JOIN users ON (comments.createdbyID = users.ID) WHERE comments.articleID = ".$row_rsArticle['ID'].")
 UNION 
 (SELECT users.ID AS userID, users.email AS useremail  FROM article LEFT JOIN users ON (article.createdbyID = users.ID) WHERE article.ID = ".$row_rsArticle['ID']."))
 AS x GROUP BY x.useremail HAVING x.userID != ".GetSQLValueString($_POST['createdbyID'], "int")." LIMIT 10";
  mysql_select_db($database_aquiescedb, $aquiescedb);
  $errorsql = (isset($_SESSION['MM_UserGroup']) && $_SESSION['MM_UserGroup']==10) ? ":<br><br>".$select : ""; // only have full select statement if webadmin
  $result = mysql_query($select, $aquiescedb) or die(mysql_error().$errorsql);

  while($row = mysql_fetch_assoc($result)) {
	  $to = $row['useremail'];
  

	  if($to !="") { 
			$subject = "Comment added to page ".$row_rsArticle['title'];
			$message = "A comment has been added by ".$row_rsLoggedIn['firstname']. " ".$row_rsLoggedIn['surname']." to ";
			$message .= ($row_rsArticle['createdbyID']==$row_rsLoggedIn['ID']) ? "your post." : "the post that you commented on.";
			$message .="\n\nClick on the link below to view all comments:\n\n";
			$message .= getProtocol()."://".$_SERVER['HTTP_HOST']."/articles/article.php?articleID=".$row_rsArticle['ID'];
			sendMail($to, $subject, $message);
	  
	  	}
 	}
}


$maxRows_rsComments = 50;
$pageNum_rsComments = 0;
if (isset($_GET['pageNum_rsComments'])) {
  $pageNum_rsComments = $_GET['pageNum_rsComments'];
}
$startRow_rsComments = $pageNum_rsComments * $maxRows_rsComments;

$varArticleID_rsComments = "-1";
if (isset($row_rsArticle['ID'])) {
  $varArticleID_rsComments = $row_rsArticle['ID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsComments = sprintf("SELECT comments.ID, comments.commenttext, comments.createddatetime, users.firstname, users.surname, users.imageURL, comments.createdbyID FROM comments LEFT JOIN users ON comments.createdbyID = users.ID WHERE comments.articleID = %s AND comments.statusID = 1 ORDER BY comments.createddatetime DESC", GetSQLValueString($varArticleID_rsComments, "int"));
$query_limit_rsComments = sprintf("%s LIMIT %d, %d", $query_rsComments, $startRow_rsComments, $maxRows_rsComments);
$rsComments = mysql_query($query_limit_rsComments, $aquiescedb) or die(mysql_error());
$row_rsComments = mysql_fetch_assoc($rsComments);

if (isset($_GET['totalRows_rsComments'])) {
  $totalRows_rsComments = $_GET['totalRows_rsComments'];
} else {
  $all_rsComments = mysql_query($query_rsComments);
  $totalRows_rsComments = mysql_num_rows($all_rsComments);
}
$totalPages_rsComments = ceil($totalRows_rsComments/$maxRows_rsComments)-1;



$queryString_rsComments = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsComments") == false && 
        stristr($param, "totalRows_rsComments") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsComments = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsComments = sprintf("&totalRows_rsComments=%d%s", $totalRows_rsComments, $queryString_rsComments);

} // end allow comments


if(thisUserHasAccess($row_rsArticle['sectionaccesslevel'], $row_rsArticle['groupreadID'], $row_rsLoggedIn['ID']) && thisUserHasAccess($row_rsArticle['accesslevel'], 0, $row_rsLoggedIn['ID'])) {
		// can access
} else {
	header("location: /login/index.php?msg=".urlencode("To view this page you must log in with the correct access credentials.").
	"&accesscheck=".
	urlencode($_SERVER['REQUEST_URI'])); 
}


// MERGES

$canonicalURL = getProtocol()."://".$_SERVER['HTTP_HOST'].$canonicalURL; 
$canonicalURL = htmlentities($canonicalURL, ENT_COMPAT, "UTF-8");

if(!isset($sectionID)) {
	$sectionID = (isset($row_rsArticle['parentsectionID']) && $row_rsArticle['parentsectionID']!=0) ? $row_rsArticle['parentsectionID'] : $row_rsArticle['sectionID'];
}
// for include submenu merge
$submenu =  buildMenu($sectionID,1,"",1);

$body = $row_rsArticle['body']; // required for quick edit
$mergebody = articleMerge($row_rsArticle['body']);

$articleID = $row_rsArticle['ID'];
$googleconversions= $row_rsArticle['googleconversions'];

$page_class = " section".$row_rsArticle['sectionID']." article".$articleID." ".$row_rsArticle['class'];
$page_class.= ($submenu != "") ? " submenu " : "";
$body_class = isset($body_class) ? $body_class : "";
$body_class .= $page_class;
/* container class only on actual page content */
$page_class.= " ".$row_rsArticlePrefs['pageclass'];
 
?>
<!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php echo (isset($row_rsArticle['seotitle']) && $row_rsArticle['seotitle']!="") ? $row_rsArticle['seotitle'] : $pageTitle." | ".$site_name;?></title>
<!-- InstanceEndEditable -->
<?php require_once('../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<link rel="canonical" href="<?php  echo $canonicalURL; ?>" />
<meta name="keywords" content="<?php echo  $row_rsArticle['metakeywords'];  $metadescription = isset($row_rsArticle['metadescription']) ? htmlentities($row_rsArticle['metadescription'], ENT_COMPAT, "UTF-8") : htmlentities($pageTitle, ENT_COMPAT, "UTF-8"); ?>" />
<?php if(isset($row_rsArticle['author'])) { ?>
<meta name="author" content="<?php echo $row_rsArticle['author']; ?>" />
<?php } ?>
<meta name="description" content="<?php echo $metadescription; ?>">
<!-- Twitter Card data -->
<meta name="twitter:card" content="summary">
<!--<meta name="twitter:site" content="@publisher_handle">
<meta name="twitter:creator" content="@author_handle">-->
<meta name="twitter:title" content="<?php echo htmlentities($pageTitle, ENT_COMPAT, "UTF-8"); ?>">
<meta name="twitter:description" content="<?php echo $metadescription; ?>">
<!-- Open Graph data -->
<meta property="og:url" content="<?php echo $canonicalURL; ?>" />
<?php if(isset($row_rsArticle['ogimageURL'])) { 
$shareimageURL = getProtocol()."://".$_SERVER['HTTP_HOST'].getImageURL($row_rsArticle['ogimageURL']); ?>
<meta property="og:image" content="<?php echo  htmlentities($shareimageURL, ENT_COMPAT, "UTF-8"); ?>" />
<meta name="twitter:image" content="<?php echo  htmlentities($shareimageURL, ENT_COMPAT, "UTF-8"); ?>">
<?php } ?>
<meta property="og:site_name" content="<?php echo htmlentities($site_name, ENT_COMPAT, "UTF-8"); ?>"/>
<meta property="og:title" content="<?php echo htmlentities($pageTitle, ENT_COMPAT, "UTF-8"); ?>" />
<meta property="og:description" content="<?php echo $metadescription; ?>" />
<meta property="og:type" content="article" />
<!-- TBI add facebook profiles to CMS
<meta property="article:author" content="https://www.facebook.com/fareedzakaria" />
<meta property="article:publisher" content="https://www.facebook.com/cnn" />-->
<?php 
 if($row_rsArticle['robots'] !=1 ) {
	switch($row_rsArticle['robots'] ) {
		case 2 : echo "<meta name=\"robots\" content=\"index, nofollow\">"; break;
		case 3 : echo "<meta name=\"robots\" content=\"noindex, follow\">"; break;
		case 4 : echo "<meta name=\"robots\" content=\"noindex, nofollow\">"; break;
		default : echo "<meta name=\"robots\" content=\"index, follow\">";
	}
}?>
<link href="/articles/css/defaultArticles.css" rel="stylesheet"  />
<?php if(is_readable(SITE_ROOT."news/css/newsDefault.css")) { ?>
<link href="/news/css/newsDefault.css" rel="stylesheet"  />
<?php } ?><script src="/3rdparty/jquery/jquery.timeago.js"></script>
<script >
jQuery(document).ready(function() {
  jQuery("abbr.timeago").timeago();
								});
 
 </script>
<?php echo  $row_rsArticle['headHTML']; 
 ?>
<?php if (isset($row_rsArticle['photogalleryID']) && $row_rsArticle['photogalleryID'] > 0 ) { ?>
<script src="/photos/scripts/slimbox-2/js/slimbox2-autosize.js"></script>
<link href="/photos/scripts/slimbox-2/css/slimbox2-autosize.css" rel="stylesheet"  />
<link href="/photos/css/defaultGallery.css" rel="stylesheet"  />
<script>
    $(function() {
        $('a.lightbox').slimbox();
    });
</script>
<?php } ?>
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" -->
    <div id="articlePage" class="pageBody <?php  echo $page_class; ?>"> 
      
      <!-- ISEARCH_END_INDEX -->
      <div class="crumbs ">
        <div class="container"><span class="you_are_in">You are in: </span>
          <ol itemscope itemtype="http://schema.org/BreadcrumbList">
            <li itemprop="itemListElement" itemscope
      itemtype="http://schema.org/ListItem" class="home"><a itemprop="item" href="/"><span itemprop="name">Home</span></a>
              <meta itemprop="position" content="<?php $position=1; echo $position++; ?>" />
            </li>
            <li itemprop="itemListElement" itemscope
      itemtype="http://schema.org/ListItem" class="articles"><a href="/articles/index.php" itemprop="item"  rel="index"><span itemprop="name">Articles</span></a>
              <meta itemprop="position" content="<?php echo $position++; ?>" />
            </li>
            <?php if(isset($row_rsArticle['parentsectionID']) && $row_rsArticle['parentsectionID']!=0) { $link = articleLink(0,"",$row_rsArticle['parentsectionID'],$row_rsArticle['parentsectionlongID']); ?>
            <li itemprop="itemListElement" itemscope
      itemtype="http://schema.org/ListItem" class="parent"> <a itemprop="item" href="<?php echo $link; ?>"  rel="section"><span itemprop="name"><?php echo $row_rsArticle['parentsection']; ?></span></a>
              <meta itemprop="position" content="<?php echo $position++; ?>" />
            </li>
            <?php } if (isset($row_rsArticle['section']) && $row_rsArticle['section']!="") { $link = articleLink(0,"",$row_rsArticle['sectionID'],$row_rsArticle['sectionlongID']); ?>
            <li itemprop="itemListElement" itemscope
      itemtype="http://schema.org/ListItem" class="section"><a itemprop="item" href="<?php echo $link; ?>" rel="section"><span itemprop="name"><?php echo $row_rsArticle['section']; ?></span></a>
              <meta itemprop="position" content="<?php echo $position++; ?>" />
            </li>
            <?php } ?>
            <li itemprop="itemListElement" itemscope
      itemtype="http://schema.org/ListItem" class="page"><a href="<?php echo $canonicalURL; ?>"  itemprop="item"><span itemprop="name"><?php echo $pageTitle; ?></span></a>
              <meta itemprop="position" content="<?php echo $position++; ?>" />
            </li>
          </ol>
        </div>
      </div>
      <?php if(is_readable(SITE_ROOT.'/local/includes/articlesectionmenu.inc.php')) { 
	require(SITE_ROOT.'/local/includes/articlesectionmenu.inc.php');
	} else { 
	require(SITE_ROOT.'/articles/includes/articlesectionmenu.inc.php');
	 } ?>
      <!-- ISEARCH_BEGIN_INDEX -->
      <article>
        <?php if(@$row_rsArticlePrefs['titleheading']==1) { ?>
        <h1 id="articleTitle"><?php echo $pageTitle; ?></h1>
        <?php } ?>
        <div id = "articleBody" class="articleBody  menuitems<?php echo (isset($totalRows_rsSectionArticles) ? $totalRows_rsSectionArticles : 0) + (isset($numParentSections) ? $numParentSections : 0); ?>" >
          <?php require_once('../core/includes/alert.inc.php'); ?>
         
            <div class="quickeditcontainer" data-quickedit-articleID="<?php echo $articleID; ?>"> <?php echo $mergebody; ?> </div>
         
          <?php if (isset($row_rsArticle['photogalleryID']) && $row_rsArticle['photogalleryID'] > 0 && is_readable('../photos/includes/gallery.inc.php')) { $galleryID = $row_rsArticle['photogalleryID']; 
$showAllPhotos = true;
	 require(SITE_ROOT.'photos/includes/gallery.inc.php'); 
 } ?>
          <?php if($row_rsArticlePrefs['articleshare']==1 && (!isset($row_rsArticle['sectionaccesslevel']) || $row_rsArticle['sectionaccesslevel']==0) && (!isset($row_rsArticle['groupreadID']) || $row_rsArticle['groupreadID']==0)) {
	  require_once('../core/share/includes/share.inc.php'); 
 }?>
          <?php // COMMENTS
  if($row_rsArticle['allowcomments']==1) { // allow comments
   if(isset($_SESSION['MM_UserGroup'])) { // logged in ?>
          <h2>Comments</h2>
          <?php if ($totalRows_rsComments == 0) { // Show if recordset empty ?>
            <p>Be the first to comment on this item:</p>
            <?php } // Show if recordset empty ?>
          <form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1">
            <label for="commenttext"></label>
            <textarea name="commenttext" id="commenttext" cols="60" rows="5"></textarea>
            <br />
            <button type="submit"  class="btn btn-primary">Submit</button>
            <input name="createddatetime" type="hidden" id="createddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
            <input name="createdbyID" type="hidden" id="createdbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
            <input name="articleID" type="hidden" id="articleID" value="<?php echo $row_rsArticle['ID']; ?>" />
            <input name="statusID" type="hidden" id="statusID" value="1" />
            <input type="hidden" name="MM_insert" value="form1" />
          </form>
          <?php } // end logged in
  else if(!isset($_SESSION['MM_UserGroup']) && $row_rsArticle['allowcomments']==1) { // not logged in?>
          <p><a href="/login/index.php?accesscheck=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>">Log in</a> or <a href="/login/signup.php?accesscheck=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>">sign up</a> to post your comments</p>
          <?php }  // end not logged in?>
          <?php  // comments
      if ($totalRows_rsComments > 0) { // Show if recordset not empty ?>
          <p>Comments <?php echo ($startRow_rsComments + 1) ?> to <?php echo min($startRow_rsComments + $maxRows_rsComments, $totalRows_rsComments) ?> of <?php echo $totalRows_rsComments ?></p>
          <div id="memberfeed">
            <?php do { ?>
              <div class="item"> <a class="fb_avatar" style="background-image:url(<?php echo isset($row_rsComments['imageURL']) ? getImageURL($row_rsComments['imageURL'],"thumb") : "/members/images/user-anonymous.png"; ?>);" href="/members/profile/index.php?userID=<?php echo $row_rsComments['createdbyID']; ?>" title="<?php echo $row_rsComments['firstname']." ".$row_rsComments['surname']; ?>"><?php echo $row_rsComments['firstname']." ".$row_rsComments['surname']; ?></a>
                <div class="editpost mouseOutHide">
                  <?php if($row_rsLoggedIn['ID']==$row_rsComments['createdbyID'] || $row_rsLoggedIn['ID']==$row_rsArticle['createdbyID'] || (isset($_SESSION['MM_UserGroup']) && $_SESSION['MM_UserGroup']>7)) { // allowed to delete? ?>
                  <a href="/articles/article.php?articleID=<?php echo $row_rsArticle['ID']; ?>&amp;deletecommentID=<?php echo $row_rsComments['ID']; ?>" onclick="return confirm('Are you sure you want to delete this post?');" class="link_delete" data-toggle="tooltip" title="Delete this post"><i class="glyphicon glyphicon-trash"></i> Delete</a>
                  <?php } // end allowed to delete?>
                </div>
                <div class="creator"><a href="/members/profile/index.php?userID=<?php echo $row_rsComments['createdbyID']; ?>"><?php echo $row_rsComments['firstname']." ".$row_rsComments['surname']; ?></a></div>
                <div class="summary"><?php echo $row_rsComments['commenttext']; ?></div>
                <div class="datetime"><abbr class="timeago" title="<?php echo $row_rsComments['createddatetime']; ?>"><?php echo date('d M Y H:s',strtotime($row_rsComments['createddatetime'])); ?></abbr></div>
              </div>
              <!-- end item -->
              <?php } while ($row_rsComments = mysql_fetch_assoc($rsComments)); ?>
          </div>
          <!-- end feed -->
          <?php } // end not empty ?>
          <?php } // end allow comments ?>
          <?php if (@$row_rsArticlePrefs['membersubmit']==1) { ?>
          <p><a href="/articles/members/suggest_article.php?sectionID=<?php echo $row_rsArticle['sectionID']; ?>" rel="nofollow">Submit an article</a></p>
          <?php } ?>
        </div>
      </article>
    </div>
    <!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsArticle);

mysql_free_result($rsLoggedIn);

mysql_free_result($rsArticlePrefs);

mysql_free_result($rsPrefs);

if(isset($rsComments) && is_resource($rsComments)) mysql_free_result($rsComments);


?>
