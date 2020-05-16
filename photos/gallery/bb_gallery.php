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

$varGalleryID_rsThisGallery = "0";
if (isset($_GET['galleryID'])) {
  $varGalleryID_rsThisGallery = $_GET['galleryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisGallery = sprintf("SELECT * FROM photocategories WHERE active = 1 AND  (ID = %s OR %s = 0)  ORDER BY ordernum  LIMIT 1", GetSQLValueString($varGalleryID_rsThisGallery, "int"),GetSQLValueString($varGalleryID_rsThisGallery, "int"));
$rsThisGallery = mysql_query($query_rsThisGallery, $aquiescedb) or die(mysql_error());
$row_rsThisGallery = mysql_fetch_assoc($rsThisGallery);
$totalRows_rsThisGallery = mysql_num_rows($rsThisGallery);

$_GET['galleryID'] = $row_rsThisGallery['ID'];

$maxRows_rsPhotos = 20;
$pageNum_rsPhotos = 0;
if (isset($_GET['pageNum_rsPhotos'])) {
  $pageNum_rsPhotos = $_GET['pageNum_rsPhotos'];
}
$startRow_rsPhotos = $pageNum_rsPhotos * $maxRows_rsPhotos;

$varGalleryID_rsPhotos = "-1";
if (isset($_GET['galleryID'])) {
  $varGalleryID_rsPhotos = $_GET['galleryID'];
}
$varPhotoID_rsPhotos = "0";
if (isset($_GET['photoID'])) {
  $varPhotoID_rsPhotos = $_GET['photoID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPhotos = sprintf("SELECT photos.*, photos.ID = %s AS selected FROM photos LEFT JOIN photoincategory ON (photos.ID = photoincategory.photoID) WHERE (photos .categoryID = %s OR photoincategory.categoryID = %s) AND photos.active = 1 GROUP BY photos.ID ORDER BY selected DESC, ordernum ASC", GetSQLValueString($varPhotoID_rsPhotos, "int"),GetSQLValueString($varGalleryID_rsPhotos, "int"),GetSQLValueString($varGalleryID_rsPhotos, "int"));
$query_limit_rsPhotos = sprintf("%s LIMIT %d, %d", $query_rsPhotos, $startRow_rsPhotos, $maxRows_rsPhotos);
$rsPhotos = mysql_query($query_limit_rsPhotos, $aquiescedb) or die(mysql_error());
$row_rsPhotos = mysql_fetch_assoc($rsPhotos);

if (isset($_GET['totalRows_rsPhotos'])) {
  $totalRows_rsPhotos = $_GET['totalRows_rsPhotos'];
} else {
  $all_rsPhotos = mysql_query($query_rsPhotos);
  $totalRows_rsPhotos = mysql_num_rows($all_rsPhotos);
}
$totalPages_rsPhotos = ceil($totalRows_rsPhotos/$maxRows_rsPhotos)-1;

$varRegionID_rsGalleries = "1";
if (isset($regionID)) {
  $varRegionID_rsGalleries = $regionID;
}
$varCategoryID_rsGalleries = "-1";
if (isset($_GET['galleryID'])) {
  $varCategoryID_rsGalleries = $_GET['galleryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsGalleries = sprintf("SELECT * FROM photocategories WHERE active = 1 AND photocategories.regionID = %s AND ID != %s ORDER BY photocategories.ordernum", GetSQLValueString($varRegionID_rsGalleries, "int"),GetSQLValueString($varCategoryID_rsGalleries, "int"));
$rsGalleries = mysql_query($query_rsGalleries, $aquiescedb) or die(mysql_error());
$row_rsGalleries = mysql_fetch_assoc($rsGalleries);
$totalRows_rsGalleries = mysql_num_rows($rsGalleries);

$thisURL = $_SERVER['PHP_SELF'];
$body_class="bb_gallery";
?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = $row_rsThisGallery['categoryname']; echo $pageTitle." | ".$site_name; ?>
</title>
<!--[if IE]><![endif]-->
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<style>
<!--

/* BACK COMPAT - add these to site css if need be
.bb_gallery #rightMargin {
	display: none;
}
.bb_gallery #container #content {
	width: 100%;
}
*/

.container * {
	box-sizing: border-box;
}
.gallery-menu {
	width: 16.66666%;
	float: left;
}
.gallery-menu ul, .gallery-menu ul li {
	list-style: none;
	margin: 0;
	padding: 0;
}
.gallery-menu ul li.selected {
	font-weight: bold;
}
.gallery {
	width: 83.333333%;
	float: right;
	overflow:hidden;
}
.gallery img {
	width: 100%;
	max-width: 100%;
	height: auto;
}
.gallery-main {
	position: relative;
	overflow: hidden;
}
.gallery-main img.fadeoverlay {
	top: 0;
	left: 0;
	position: absolute;
	width: 100%;
	height: auto;
	z-index: 4;
}
 @keyframes phototitle {
 from {
bottom: -100px;
opacity:0
}
to {
	bottom: 0;
	opacity: 1
}
}
@-webkit-keyframes phototitle {
 from {
bottom: -100px;
-webkit-opacity:0
}
to {
	bottom: 0;
	webkit-opacity: 1
}
}
.gallery-main .text {
	position: absolute;
	bottom: 0;
	left: 0;
	width: 100%;
	background-color: rgb(124,123,123);
	background-color: rgba(124,123,123,.8);
	color: rgb(255,255,255);
	padding: 10px;
	animation-name: phototitle;
	animation-duration: 1s;
	-webkit-animation-name: phototitle; /* Chrome, Safari, Opera */
	-webkit-animation-duration: 1s; /* Chrome, Safari, Opera */
	z-index: 100;
}
.gallery-main .text .title {
	display: block;
	font-size: 22px;
	font-family: 'EB Garamond', serif;
}
.gallery-main .text .description {
	display: block;
	font-size: 14px;
}
.gallery-thumbs {
	margin: 0 -10px;
	display: flex;
flex-wrap: wrap;
}
.gallery-thumb {
	width: 20%;
	float: left;
	padding: 10px;
	font-size: 14px;
	line-height: 1;
	min-height: 200px;
}
.gallery-thumb a {
	display: block;
}
.gallery-thumb a:hover {
	text-decoration: none;
}
.gallery-thumb a span {
	display: block;
}
.gallery-thumb a .image-container {
	padding-top: 100%;
	overflow: hidden;
	background: no-repeat center center;
	background-size: cover;
	-ms-transition: .5s;
	-webkit-transition: .5s;
	transition: .5s;
}
.gallery-thumb a .image-container:hover {
	-ms-transform: scale(1.06, 1.06); /* IE 9 */
	-webkit-transform: scale(1.06, 1.06); /* Safari */
	transform: scale(1.06, 1.06);
}
.gallery-thumb a .title {
	min-height: 2.5em;
	padding-top: .5em;
}
/*** RESPONSIVE UPDATE ***/

.responsive .gallery-menu {
	width: 100%;
	float: none;
}
.responsive .gallery {
	width: 100%;
	float: none;
}
.responsive .gallery-thumb {
	width: 50%;
}
@media (min-width: 768px) {
	.responsive .gallery-thumb {
	width: 25%;
}
}
@media (min-width: 992px) {
.responsive .gallery-thumb {
	width: 20%;
}
.responsive .gallery-menu {
	width: 16.66666%;
	float: left;
}
.responsive .gallery {
	width: 83.333333%;
	float: right;
}
}
-->
</style>
<script>window.jQuery || document.write('<script src="/3rdparty/jquery/jquery-1.12.1.min.js"><\/script>'); // if not already loaded
var currentImage = <?php echo $row_rsPhotos['ID']; ?>;
var photoID = new Array();
var phototitle = new Array();
var photodescription = new Array();
var photoURL = new Array();
var images = new Array();


$(document).ready(function(e) {
	getPhotoData();    
});

function getPhotoData() {	
	<?php do { ?>
	photoID.push(<?php echo $row_rsPhotos['ID']; ?>);
	phototitle[<?php echo $row_rsPhotos['ID']; ?>] = '<?php echo addslashes($row_rsPhotos['title']); ?>';
	photodescription[<?php echo $row_rsPhotos['ID']; ?>] = '<?php echo addslashes($row_rsPhotos['description']); ?>';
	photoURL[<?php echo $row_rsPhotos['ID']; ?>] = '<?php echo getImageURL($row_rsPhotos['imageURL']); ?>';
	// preload images
	images[<?php echo $row_rsPhotos['ID']; ?>] = new Image();
	images[<?php echo $row_rsPhotos['ID']; ?>].src = photoURL[<?php echo $row_rsPhotos['ID']; ?>];             
    <?php } while ($row_rsPhotos = mysql_fetch_assoc($rsPhotos)); 
	mysql_data_seek($rsPhotos,0); $row_rsPhotos = mysql_fetch_assoc($rsPhotos); ?>
}

function swapImage(imageID) {
	$(".gallery-main .text").animate({bottom: "-=100",}, function() {
		$(".gallery-menu li").removeClass("selected");
		$(".gallery-menu li.menuitem-"+imageID).addClass("selected");
		$(".gallery-main img.fadeoverlay").show();
		$(".gallery-main img.main").attr("src",photoURL[imageID]);
		$(".gallery-main .text").html('<span class="title">'+phototitle[imageID]+'</span><span class="description">'+photodescription[imageID]+'</span>');
		$(".gallery-main img.fadeoverlay").fadeOut(function() {
			$(".gallery-main img.fadeoverlay").attr("src",photoURL[imageID]);
			$(".gallery-main img.fadeoverlay").hide();
			$(".gallery-main .text").animate({bottom: "+=100"},1000);
			
		});
	});
	currentImage = imageID;	
}

function getNextImage() {
	photo = photoID.indexOf(currentImage)+1;
	if(photo>photoID.length) photo = 0;
	swapImage(photoID[photo]);
	
}

</script>
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" -->
          <div class="container">
            
            <div class="gallery">
              <?php if($totalRows_rsPhotos>0) { ?>
              <div class="gallery-main"><a href="<?php echo $thisURL."?galleryID=".$row_rsThisGallery['ID']."&photoID=".$row_rsPhotos['ID']; ?>" onClick="getNextImage(); return false;"> <img src="<?php echo getImageURL($row_rsPhotos['imageURL']); ?>" class="main"></a><img src="<?php echo getImageURL($row_rsPhotos['imageURL'],"large"); ?>" class="fadeoverlay">
                <div class="text"><span class="title"><?php echo $row_rsPhotos['title']; ?></span> <span class="description"><?php echo $row_rsPhotos['description']; ?></span></div>
              </div>
              <h3><?php echo $row_rsThisGallery['description']; ?></h3>
              <div class="gallery-thumbs">
                <?php mysql_data_seek($rsPhotos,0); while ($row_rsPhotos = mysql_fetch_assoc($rsPhotos)) { ?>
                <div class="gallery-thumb"><a href="<?php echo $thisURL."?galleryID=".$row_rsThisGallery['ID']."&photoID=".$row_rsPhotos['ID']; ?>" onClick="swapImage(<?php echo $row_rsPhotos['ID']; ?>); return false;"><span class="image-container" style="background-image:url(<?php echo getImageURL($row_rsPhotos['imageURL'], "medium"); ?>)"></span><span class="title"><?php echo $row_rsPhotos['title']; ?></span></a></div>
                <?php } ?>
              </div>
              <?php } ?>
            </div>
            <div class="gallery-menu">
              <h2><?php echo $row_rsThisGallery['categoryname']; ?></h2>
              <?php if($totalRows_rsPhotos>0) { ?>
              <ul>
                <?php  mysql_data_seek($rsPhotos,0); $row_rsPhotos = mysql_fetch_assoc($rsPhotos); do { ?>
                <li<?php if(isset($_GET['photoID']) && $row_rsPhotos['ID']==$_GET['photoID']) echo " class=\"selected\" "; ?> class="menuitem-<?php echo $row_rsPhotos['ID']; ?>"><a href="<?php echo $thisURL."?galleryID=".$row_rsThisGallery['ID']."&photoID=".$row_rsPhotos['ID']; ?>" onClick="swapImage(<?php echo $row_rsPhotos['ID']; ?>); return false;"><?php echo $row_rsPhotos['title']; ?></a></li>
                <?php } while ($row_rsPhotos = mysql_fetch_assoc($rsPhotos)); 
			   ?>
              </ul>
              <?php } ?>
              <?php if($totalRows_rsGalleries>0) { ?>
              <h2>Other Galleries</h2>
              <ul>
                <?php do { ?>
                 <li><a href="bb_gallery.php?galleryID=<?php echo $row_rsGalleries['ID']; ?>" ><?php echo $row_rsGalleries['categoryname']; ?></a></li>
                  <?php } while ($row_rsGalleries = mysql_fetch_assoc($rsGalleries)); 
			  ?>
              </ul>
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
mysql_free_result($rsThisGallery);

mysql_free_result($rsPhotos);

mysql_free_result($rsGalleries);
?>
