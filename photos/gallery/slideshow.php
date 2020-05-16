<?php require_once('../../Connections/aquiescedb.php'); ?>
<?php require_once('../../core/includes/framework.inc.php'); ?>
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


$varUserGroup_rsGalleries = "0";
if (isset($_SESSION['MM_UserGroup'])) {
  $varUserGroup_rsGalleries = $_SESSION['MM_UserGroup'];
}
$varRegionID_rsGalleries = "1";
if (isset($regionID)) {
  $varRegionID_rsGalleries = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsGalleries = sprintf("SELECT photocategories.ID AS catID, photocategories.categoryname, photocategories.`description`,  cover.imageURL AS coverimageURL, (SELECT imageURL FROM photos WHERE categoryID = catID ORDER BY ordernum ASC LIMIT 1) AS photoimageURL  FROM photocategories  LEFT JOIN photos  AS cover ON (photocategories.coverphotoID = cover.ID) WHERE  regionID = %s AND accesslevel <= %s AND photocategories.active = 1   GROUP BY catID  HAVING photoimageURL IS NOT NULL  ORDER BY photocategories.ordernum", GetSQLValueString($varRegionID_rsGalleries, "int"),GetSQLValueString($varUserGroup_rsGalleries, "int"));
$rsGalleries = mysql_query($query_rsGalleries, $aquiescedb) or die(mysql_error());
$row_rsGalleries = mysql_fetch_assoc($rsGalleries);
$totalRows_rsGalleries = mysql_num_rows($rsGalleries);



if(!isset($_GET['categoryID'])) { // no category set as first
	$_GET['categoryID'] = isset($row_rsGalleries['catID']) ? $row_rsGalleries['catID'] : -1;

}


$colname_rsThumbs = "0";
if (isset($_GET['categoryID'])) {
  $colname_rsThumbs = $_GET['categoryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThumbs = sprintf("SELECT * FROM photos WHERE photos.active = 1 AND (categoryID = %s OR %s = 0)", GetSQLValueString($colname_rsThumbs, "int"),GetSQLValueString($colname_rsThumbs, "int"));
$rsThumbs = mysql_query($query_rsThumbs, $aquiescedb) or die(mysql_error());
$row_rsThumbs = mysql_fetch_assoc($rsThumbs);
$totalRows_rsThumbs = mysql_num_rows($rsThumbs);

$colname_rsThisGallery = "-1";
if (isset($_GET['categoryID'])) {
  $colname_rsThisGallery = $_GET['categoryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisGallery = sprintf("SELECT categoryname, accesslevel, active FROM photocategories WHERE ID = %s", GetSQLValueString($colname_rsThisGallery, "int"));
$rsThisGallery = mysql_query($query_rsThisGallery, $aquiescedb) or die(mysql_error());
$row_rsThisGallery = mysql_fetch_assoc($rsThisGallery);
$totalRows_rsThisGallery = mysql_num_rows($rsThisGallery);

$_GET['sectionID'] = 4;
?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Gallery"; echo $pageTitle." | ".$site_name; ?>
</title>
<!--[if IE]><![endif]-->
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
  <link rel="stylesheet" type="text/css" href="/photos/scripts/ad-gallery/jquery.ad-gallery.css" />
  <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
  <script src="/photos/scripts/ad-gallery/jquery.ad-gallery.js"></script>
  <script>
  $(function() {
   
    var galleries = $('.ad-gallery').adGallery({loader_image: '/photos/scripts/ad-gallery/loader.gif', slideshow: {
                       enable: false}});
  
  });
  </script>


  
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" -->
   <div id="pageSlideshow">
    <div id="gallery" class="ad-gallery">
      <div class="ad-image-wrapper">
      </div>
      <div class="ad-controls">
      </div>
      <div class="ad-nav">
        <div class="ad-thumbs">
          <ul class="ad-thumb-list">
           
              
                <?php $image = 0; do { ?>
                <li>
                <a href="<?php echo getImageURL($row_rsThumbs['imageURL'],"/gallery_large/"); ?>">
                <img src="<?php echo getImageURL($row_rsThumbs['imageURL'],"/gallery_thumb/"); ?>" class="image<?php echo $image; $image++; ?>" alt="<?php echo $row_rsThumbs['description']; ?>" />
              </a>
              </li>
                  
                  <?php } while ($row_rsThumbs = mysql_fetch_assoc($rsThumbs)); ?>
              

          </ul>
        </div>
      </div>
    </div>
    <h1><?php echo $row_rsThisGallery['categoryname']; ?></h1>
    <p>Click the images below to view the different wedding day photo galleries.</p>
    <div id="galleryMenu">
    <ul>
      <?php do { ?>
        <li><a href="slideshow.php?categoryID=<?php echo $row_rsGalleries['catID']; ?>"><img src="<?php $imageURL = isset($row_rsGalleries['coverimageURL']) ? $row_rsGalleries['coverimageURL'] : $row_rsGalleries['photoimageURL']; echo getImageURL($imageURL,"/gallery_medium/"); ?>" class="image<?php echo $image; $image++; ?>" alt="<?php echo $row_rsGalleries['categoryname']; ?>" /><span><?php echo $row_rsGalleries['categoryname']; ?></a></span></li>
        <?php } while ($row_rsGalleries = mysql_fetch_assoc($rsGalleries)); ?>
        </ul></div></div>
    <!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsThumbs);

mysql_free_result($rsGalleries);

mysql_free_result($rsThisGallery);
?>
