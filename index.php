<?php if(!is_readable('Connections/aquiescedb.php')) {
	if(is_readable("install/index.php")) {
		header("location: install/index.php"); exit;
	} else {
		die("The site does not appear to be properly installed. Please install before use.");
	}
}?>
<?php require_once('Connections/aquiescedb.php'); ?>
<?php require_once('articles/includes/functions.inc.php'); ?><?php require_once('core/includes/framework.inc.php'); ?>
<?php require_once("core/includes/generate_tokens.inc.php"); ?>
<?php require_once('login/includes/login.inc.php'); // log in the user with cookies if applicable ?>
<?php  /*
if (substr(PHP_OS, 0, 3) == 'WIN') {
	pclose(popen("start php ".SITE_ROOT."core/admin/backup/backup.php", "r")); // win 
} else {
	pclose(popen("php ".SITE_ROOT."core/admin/backup/backup.php &", "r")); // unix
} */
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

require_once('articles/includes/quickedit_save.inc.php');

$varRegionID_rsHomePage = "1";
if (isset($regionID)) {
  $varRegionID_rsHomePage = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsHomePage = sprintf("SELECT article.*, articlesection.accesslevel, articlesection.`description` AS section, CONCAT(firstname, ' ', surname) AS author FROM article LEFT JOIN articlesection ON (article.sectionID = articlesection.ID) LEFT JOIN users ON (article.createdbyID = users.ID) WHERE article.sectionID = 0 AND article.regionID = %s  AND versionofID IS NULL LIMIT 1", GetSQLValueString($varRegionID_rsHomePage, "int"));
$rsHomePage = mysql_query($query_rsHomePage, $aquiescedb) or die(mysql_error());
$row_rsHomePage = mysql_fetch_assoc($rsHomePage);
$totalRows_rsHomePage = mysql_num_rows($rsHomePage);

if (isset($row_rsHomePage['redirectURL']) && $row_rsHomePage['redirectURL']!="") { // redirect in article
	header( "HTTP/1.1 301 Moved Permanently" ); 
	header( "Status: 301 Moved Permanently" );
	header("location: ".$row_rsHomePage['redirectURL']); exit;
}

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT users.ID, users.usertypeID, users.firstname, usergroupmember.groupID FROM users LEFT JOIN usergroupmember ON (users.ID = usergroupmember.userID) WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

$varRegionID_rsMerge = "1";
if (isset($regionID)) {
  $varRegionID_rsMerge = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsMerge = sprintf("SELECT * FROM merge WHERE statusID = 1 AND (%s = regionID OR regionID = 0)", GetSQLValueString($varRegionID_rsMerge, "int"));
$rsMerge = mysql_query($query_rsMerge, $aquiescedb) or die(mysql_error());
$row_rsMerge = mysql_fetch_assoc($rsMerge);
$totalRows_rsMerge = mysql_num_rows($rsMerge);

$canonicalURL = getProtocol()."://". $_SERVER['HTTP_HOST']."/";
$canonicalURL = htmlentities($canonicalURL, ENT_COMPAT, "UTF-8");


$body = $row_rsHomePage['body']; // required for quick edit
$mergebody = articleMerge($row_rsHomePage['body']);
$articleID = $row_rsHomePage['ID'];




$pageTitle =  $row_rsHomePage['title'];
$body_class = "home section0";
?>
<!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php  echo isset($row_rsHomePage['seotitle']) ? $row_rsHomePage['seotitle'] : $site_name." | ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('core/seo/includes/seo.inc.php'); ?>
<?php require_once('local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<meta name="keywords" content="<?php echo isset($row_rsHomePage['metakeywords']) ? $row_rsHomePage['metakeywords'] :  substr(strip_tags($articleBody),0,255); ?>" />
<meta name="author" content="<?php echo isset($row_rsHomePage['author']) ? $row_rsHomePage['author'] : "Paul Egan"; ?>" />
<link rel="canonical" href="<?php  echo $canonicalURL; ?>">
<meta name="description"  content="<?php $metadescription = isset($row_rsHomePage['metadescription']) ? $row_rsHomePage['metadescription'] : $pageTitle; echo $metadescription; ?>" />
<!-- Twitter Card data -->
<meta name="twitter:card" content="summary">
<!--<meta name="twitter:site" content="@publisher_handle">
<meta name="twitter:creator" content="@author_handle">-->
<meta name="twitter:title" content="<?php echo htmlentities($pageTitle, ENT_COMPAT, "UTF-8"); ?>">
<meta name="twitter:description" content="<?php echo $row_rsHomePage['metadescription']; ?>">
<!-- Open Graph data -->
<meta property="og:url" content="<?php  echo $canonicalURL; ?>"/>
<meta property="og:site_name" content="<?php echo htmlentities($site_name, ENT_COMPAT, "UTF-8"); ?>"/>
<meta property="og:title" content="<?php echo htmlentities($pageTitle, ENT_COMPAT, "UTF-8"); ?>" />
<meta property="og:description" content="<?php echo $row_rsHomePage['metadescription']; ?>" />
<meta property="og:type" content="website" />
<?php if(isset($row_rsHomePage['ogimageURL'])) { 
$shareimageURL = getProtocol()."://". $_SERVER['HTTP_HOST'].getImageURL($row_rsHomePage['ogimageURL']); ?>
<meta property="og:image" content="<?php echo  htmlentities($shareimageURL, ENT_COMPAT, "UTF-8"); ?>" />
<meta name="twitter:image" content="<?php echo  htmlentities($shareimageURL, ENT_COMPAT, "UTF-8"); ?>">
<?php } ?>
<?php echo metaTags(); echo $row_rsHomePage['headHTML']; ?>
<style><!-- 

--></style>
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" --> <?php require_once('core/includes/alert.inc.php'); ?>
 <pre>
  <?php echo htmlspecialchars_decode ("&quot;&nbsp; TEST &pound;"); ?></pre>
  <div class="quickeditcontainer">
    <?php echo $mergebody;  ?>
  </div>
  <img src="/search/auto_spider_img.php"  alt="Search spider image" style="width:1px; height:1px; position:absolute; left:-9999em;" /><!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsHomePage);

mysql_free_result($rsLoggedIn);



?>
