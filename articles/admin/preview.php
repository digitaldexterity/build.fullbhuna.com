<?php require_once('../../Connections/aquiescedb.php'); ?><?php require_once('../includes/functions.inc.php'); ?>
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
?><?php
$varArticleID_rsArticle = "-1";
if (isset($_GET['articleID'])) {
  $varArticleID_rsArticle = $_GET['articleID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsArticle = sprintf("SELECT article.*, articlesection.accesslevel, articlesection.`description` AS section, articlesection.longID AS sectionlongID, parentsection.`description` AS parentsection, parentsection.ID AS parentsectionID, parentsection.longID AS parentsectionlongID FROM article LEFT JOIN articlesection ON (article.sectionID = articlesection.ID) LEFT JOIN articlesection AS parentsection ON (articlesection.subsectionofID = parentsection.ID) WHERE (CAST(article.ID AS CHAR) = %s OR article.longID=%s)", GetSQLValueString($varArticleID_rsArticle, "text"),GetSQLValueString($varArticleID_rsArticle, "text"));
$rsArticle = mysql_query($query_rsArticle, $aquiescedb) or die(mysql_error());
$row_rsArticle = mysql_fetch_assoc($rsArticle);
$totalRows_rsArticle = mysql_num_rows($rsArticle);

if($totalRows_rsArticle == 0) { // not found, try old ID
	$select = "SELECT article.ID, article.longID, article.sectionID, articlesection.longID AS sectionlongID  FROM article LEFT JOIN articlesection ON (article.sectionID = articlesection.ID) WHERE article.oldlongID=". GetSQLValueString($varArticleID_rsArticle, "text");
	$result = mysql_query($select, $aquiescedb) or die(mysql_error());
	$row = mysql_fetch_assoc($result);
	if(mysql_num_rows($result)>0) {
		$url = articleLink($row['ID'], $row['longID'], $row['sectionID'], $row['sectionlongID']); 
		header( "HTTP/1.1 301 Moved Permanently" ); 
		header( "Status: 301 Moved Permanently" );
		header("location: ".$url); exit;
	} else {
		$url = "/articles/index.php";
		$url .= isset($_GET['sectionID']) ? "?sectionID=".intval($_GET['sectionID']) : "?msg=".urlencode("The page you were trying to access to longer exists.");
		header("location: ".$url); exit;
	}
}
?>

<?php
$currentPage = $_SERVER["PHP_SELF"];

?>
<?php
$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT usertypeID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

$regionID = (isset($_GET['regionID']) && intval($_GET['regionID'])>0)  ? intval($_GET['regionID']) : ((isset($regionID) && intval($regionID)>0 ) ?intval($regionID) : 1);



mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsArticlePrefs = "SELECT * FROM articleprefs WHERE ID = ".$regionID;
$rsArticlePrefs = mysql_query($query_rsArticlePrefs, $aquiescedb) or die(mysql_error());
$row_rsArticlePrefs = mysql_fetch_assoc($rsArticlePrefs);
$totalRows_rsArticlePrefs = mysql_num_rows($rsArticlePrefs);



?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = $row_rsArticle['title']; echo (isset($row_rsArticle['seotitle']) && $row_rsArticle['seotitle']!="") ? $row_rsArticle['seotitle'] : $pageTitle." | ".$site_name;?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<meta name="description" content="<?php echo  $row_rsArticle['metadescription']; ?>" />
<meta name="keywords" content="<?php echo  $row_rsArticle['metakeywords']; ?>" />
<?php echo  $row_rsArticle['headHTML']; ?>
<script src="/3rdparty/tinymce/jscripts/tiny_mce/tiny_mce_popup.js"></script>
<script src="/3rdparty/tinymce/jscripts/tiny_mce/plugins/preview/jscripts/embed.js"></script>
<script><!--
document.write('<base href="' + tinyMCEPopup.getWindowArg("base") + '">');
// -->
</script>
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" -->
  <div id="articlePage" class="container pageBody <?php echo "section".$row_rsArticle['sectionID']." article".$row_rsArticle['ID']." ".$row_rsArticle['class']; ?>">
   
    <div class="crumbs"><div><span class="you_are_in">You are in: </span><a href="/">Home</a><span class="separator">&nbsp;&rsaquo;&nbsp;</span><a href="/articles/index.php" rel="index">Articles</a><?php if(isset($row_rsArticle['parentsectionID']) && $row_rsArticle['parentsectionID']!=0) { $link = articleLink(0,"",$row_rsArticle['parentsectionID'],$row_rsArticle['parentsectionlongID']); ?><span class="separator">&nbsp;&rsaquo;&nbsp;</span><a href="<?php echo $link; ?>"  rel="section"><?php echo $row_rsArticle['parentsection']; ?></a><?php } if (isset($row_rsArticle['section']) && $row_rsArticle['section']!="") { $link = articleLink(0,"",$row_rsArticle['sectionID'],$row_rsArticle['sectionlongID']); ?><span class="separator">&nbsp;&rsaquo;&nbsp;</span><a href="<?php echo $link; ?>" rel="section"><?php echo $row_rsArticle['section']; ?></a><?php } ?><span class="separator">&nbsp;&rsaquo;&nbsp;</span><?php echo $pageTitle; ?></div></div>
    <div id="articlesectionmenu" class="subMenu">
    <?php 
	// if parent section not root, build menu from section above to show place in structure
	$sectionID = (isset($row_rsArticle['parentsectionID']) && $row_rsArticle['parentsectionID']!=0) ? $row_rsArticle['parentsectionID'] : $row_rsArticle['sectionID'];
	echo buildMenu($sectionID,1); ?>
    </div>
    
   <?php if(@$row_rsArticlePrefs['titleheading']==1) { ?> <h1 id="articleTitle"><?php echo $pageTitle; ?></h1><?php } ?>
    <div id = "articleBody" class="menuitems<?php echo @$totalRows_rsSectionArticles + @$numParentSections; ?>"><script>
document.write(tinyMCEPopup.editor.getContent());
</script>
<?php if (isset($row_rsArticle['photogalleryID']) && $row_rsArticle['photogalleryID'] >0) { ?>
      <p><a href="/photos/index.php?categoryID=<?php echo $row_rsArticle['photogalleryID']; ?>" >View more pictures</a> </p>
      <?php } ?>
     <?php if (@$row_rsArticlePrefs['membersubmit']==1) { ?>
      <p><a href="/articles/members/suggest_article.php?sectionID=<?php echo $row_rsArticle['sectionID']; ?>" rel="nofollow">Submit an article</a></p>
      <?php } ?>
   </div>
  
   </div>
    <!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsArticle);

mysql_free_result($rsLoggedIn);

mysql_free_result($rsArticlePrefs);
?>
