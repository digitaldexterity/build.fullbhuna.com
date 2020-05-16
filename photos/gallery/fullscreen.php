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


$maxRows_rsPhotos = 100;
$pageNum_rsPhotos = 0;
if (isset($_GET['pageNum_rsPhotos'])) {
  $pageNum_rsPhotos = $_GET['pageNum_rsPhotos'];
}
$startRow_rsPhotos = $pageNum_rsPhotos * $maxRows_rsPhotos;

$varGalleryID_rsPhotos = "0";
if (isset($_GET['galleryID'])) {
  $varGalleryID_rsPhotos = $_GET['galleryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPhotos = sprintf("SELECT photos.*, photocategories.soundtrackURL FROM photos LEFT JOIN photocategories ON (photocategories.ID = photos.categoryID) WHERE (%s  = 0 OR categoryID = %s) AND photos.active = 1", GetSQLValueString($varGalleryID_rsPhotos, "int"),GetSQLValueString($varGalleryID_rsPhotos, "int"));
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
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Untitled Document</title>
<style>
<!--
body, figure {
	margin: 0;
	padding: 0;
	background: rgb(0,0,0);
	color: rgb(255,255,255);
	font-family: Arial, Helvetica, sans-serif;
}
a {
	display: inline-block;
	margin: 5px 20px;
	color: inherit;
}
@-webkit-keyframes fadey {
0% {
opacity:0;
}
15% {
opacity:1;
}
85% {
opacity:1;
}
100% {
opacity:0;
}
}
@keyframes fadey {
 0% {
opacity: 0;
}
 15% {
opacity: 1;
}
 85% {
opacity: 1;
}
 100% {
opacity: 0;
}
}
figure#slideshow {
	width: 80%;
	margin: 0 auto;
	position: relative;
}
figure#slideshow img {
	position: absolute;
	left: 0;
	top: 0;
	width: 100%;
	height: auto;
	opacity: 0;
}
figure#slideshow img:first-child {
	position: relative;
}
 #slideshow-container:fullscreen {
display: flex;
justify-content: center;
align-items: center;
}
#slideshow-container:fullscreen figure {
width: 80%;
margin: 0 auto;
}
:-webkit-fullscreen {
width: 100%;
height: 100%;
}
*:-moz-fullscreen {
background: #fff;
}
-->
</style>
<script>
window.onload = function() {
imgs = document.getElementById('slideshow').children;
	interval = 8000;
	currentPic = 0;
	imgs[currentPic].style.webkitAnimation = 'fadey '+interval+'ms';
	imgs[currentPic].style.animation = 'fadey '+interval+'ms';
	var infiniteLoop = setInterval(function(){
		imgs[currentPic].removeAttribute('style');
		if ( currentPic == imgs.length - 1) { currentPic = 0; } else { currentPic++; }
		imgs[currentPic].style.webkitAnimation = 'fadey '+interval+'ms';
		imgs[currentPic].style.animation = 'fadey '+interval+'ms';
	}, interval);
}

function fullScreen(element) {
	if(element.requestFullscreen) {
		element.requestFullscreen();
	} else if(element.webkitRequestFullscreen ) {
		element.webkitRequestFullscreen();
	} else if(element.mozRequestFullScreen) {
		element.mozRequestFullScreen();
	}
}
</script>
</head>

<body>
<a href="#" onclick=" fullScreen(document.getElementById('slideshow')) ">Full Screen</a> | <a href="index.php?galleryID=<?php echo isset($_GET['galleryID']) ? intval($_GET['galleryID']) : 0; ?>">Back</a>
<div id="slideshow-container">
  <figure id="slideshow">
    <?php do { ?>
      <img src="<?php echo getImageURL($row_rsPhotos['imageURL'],"large"); ?>" />
      <?php } while ($row_rsPhotos = mysql_fetch_assoc($rsPhotos)); ?>
  </figure>
  <?php if(isset($row_rsPhotos['soundtrackURL']) && strpos($row_rsPhotos['soundtrackURL'])>0) { ?>
  <audio loop autoplay>
    <source src="<?php echo $row_rsPhotos['soundtrackURL']; ?>" type="audio/mpeg">
  </audio>
  <?php } ?>
</div>
</body>
</html>
<?php
mysql_free_result($rsPhotos);
?>
