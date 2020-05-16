<?php require_once('../Connections/aquiescedb.php'); ?><?php require_once('../core/includes/framework.inc.php'); ?>
<?php require_once('../core/includes/upload.inc.php'); ?>
<?php $_GET['galleryID'] = (isset($_GET['galleryID']) && $_GET['galleryID'] >=1) ? $_GET['galleryID'] : 0;
$regionID = (isset($regionID) && intval($regionID)>0) ? intval($regionID) : 1; ?>
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

$dir = isset($_POST['directory']) ? $_POST['directory'] : "";
$files =  getUploads(UPLOAD_ROOT.$dir,$image_sizes,"","",0,"",array("gif","png","jpeg","jpg"),"longest");
if($files) { echo $files; die(); }

$currentPage = $_SERVER["PHP_SELF"];


$colname_rsLoggedIn = "1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID, usertypeID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

$colname_rsPhotoCategory = "1";
if (isset($_GET['galleryID'])) {
  $colname_rsPhotoCategory = $_GET['galleryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPhotoCategory = sprintf("SELECT categoryname, photocategories.description FROM photocategories WHERE ID = %s", GetSQLValueString($colname_rsPhotoCategory, "int"));
$rsPhotoCategory = mysql_query($query_rsPhotoCategory, $aquiescedb) or die(mysql_error());
$row_rsPhotoCategory = mysql_fetch_assoc($rsPhotoCategory);
$totalRows_rsPhotoCategory = mysql_num_rows($rsPhotoCategory);

if(isset($_GET['pageNum_rsGalleries'])) $_GET['pageNum_rsGalleries'] = intval($_GET['pageNum_rsGalleries']);
if(isset($_GET['totalRows_rsGalleries'])) $_GET['totalRows_rsGalleries'] = intval($_GET['totalRows_rsGalleries']);



$maxRows_rsGalleries = 20;
$pageNum_rsGalleries = 0;
if (isset($_GET['pageNum_rsGalleries'])) {
  $pageNum_rsGalleries = $_GET['pageNum_rsGalleries'];
}
$startRow_rsGalleries = $pageNum_rsGalleries * $maxRows_rsGalleries;

$varUserLevel_rsGalleries = "0";
if (isset($_SESSION['MM_UserGroup'])) {
  $varUserLevel_rsGalleries = $_SESSION['MM_UserGroup'];
}
$varRegionID_rsGalleries = "1";
if (isset($regionID)) {
  $varRegionID_rsGalleries = $regionID;
}
$varThisGalleryID_rsGalleries = "0";
if (isset($_GET['galleryID'])) {
  $varThisGalleryID_rsGalleries = $_GET['galleryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsGalleries = sprintf("SELECT photocategories.*, users.firstname, users.surname,  COUNT(photos.ID) as numphotos, COUNT(photoincategory.ID) AS numphotos2, photos.title, photos.imageURL, photos.width,photos.height,coverphoto.imageURL AS coverimageURL, coverphoto.width AS coverwidth, coverphoto.height AS coverheight FROM photocategories LEFT JOIN users ON (photocategories.addedbyID = users.ID) LEFT JOIN photos ON (photos.categoryID = photocategories.ID AND photos.active=1) LEFT JOIN photos AS coverphoto ON (coverphoto.ID = photocategories.coverphotoID) LEFT JOIN photoincategory ON (photoincategory.categoryID = photocategories.ID) WHERE (photocategories.regionID = 0 OR photocategories.regionID = %s) AND photocategories.active = 1 AND photocategories.accesslevel <= %s GROUP BY photocategories.ID HAVING photocategories.ID != %s ORDER BY photocategories.ordernum ASC, photocategories.createddatetime DESC ", GetSQLValueString($varRegionID_rsGalleries, "int"),GetSQLValueString($varUserLevel_rsGalleries, "int"),GetSQLValueString($varThisGalleryID_rsGalleries, "int"));
$query_limit_rsGalleries = sprintf("%s LIMIT %d, %d", $query_rsGalleries, $startRow_rsGalleries, $maxRows_rsGalleries);
$rsGalleries = mysql_query($query_limit_rsGalleries, $aquiescedb) or die(mysql_error());
$row_rsGalleries = mysql_fetch_assoc($rsGalleries);

if (isset($_GET['totalRows_rsGalleries'])) {
  $totalRows_rsGalleries = $_GET['totalRows_rsGalleries'];
} else {
  $all_rsGalleries = mysql_query($query_rsGalleries);
  $totalRows_rsGalleries = mysql_num_rows($all_rsGalleries);
}
$totalPages_rsGalleries = ceil($totalRows_rsGalleries/$maxRows_rsGalleries)-1;

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsMediaPrefs = "SELECT * FROM mediaprefs WHERE ID = ".$regionID;
$rsMediaPrefs = mysql_query($query_rsMediaPrefs, $aquiescedb) or die(mysql_error());
$row_rsMediaPrefs = mysql_fetch_assoc($rsMediaPrefs);
$totalRows_rsMediaPrefs = mysql_num_rows($rsMediaPrefs);

$queryString_rsGalleries = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsGalleries") == false && 
        stristr($param, "totalRows_rsGalleries") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsGalleries = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsGalleries = sprintf("&totalRows_rsGalleries=%d%s", $totalRows_rsGalleries, $queryString_rsGalleries);

	  if ($row_rsMediaPrefs['galleryhome']==0) { 
	  header("location: gallery/index.php"); exit;
	  }
	  ?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Photos - "; $pageTitle .= ($_GET['galleryID']>0) ? $row_rsPhotoCategory['categoryname'] : "Latest"; echo $pageTitle." | ".$site_name; 
?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<?php if($row_rsLoggedIn['usertypeID']<8) { echo "<style> #link_options { display:none; } </style>"; } ?>

<link href="css/defaultGallery.css" rel="stylesheet"  />
  <script src="/3rdparty/dropzone/dropzone.js"></script>
<link rel="stylesheet" href="/3rdparty/dropzone/dropzone.css">
<script>
$(document).ready(function(e) {
  /* Dropzone requires no configuration whatsoever - just the js file - but as there may be mpre forms on this page added below */  
Dropzone.autoDiscover = false;

var myDropzone = new Dropzone("#dropzone1", { url: "/photos/index.php", 
init: function() {
    this.on("queuecomplete", function(file) { 
	document.location.href = "/photos/members/review.php?galleryID=<?php echo isset($_GET['galleryID']) ? intval($_GET['galleryID']) : 0; ?>";
	 });
  }

}); 

});
</script> 
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" -->
    <div id = "photoPage" class="galleryindex container pageBody">
      <?php 
	  
	
	$accesslevel = isset($accesslevel) ? $accesslevel : 0;
	if ((isset($_SESSION['MM_UserGroup']) && $_SESSION['MM_UserGroup'] >= $accesslevel) || $accesslevel == 0) { // OK to access ?>
     <h1><?php echo $row_rsMediaPrefs['galleriesname']; ?></h1>
     
     <?php 
	if (isset($_SESSION['MM_UserGroup']) && $_SESSION['MM_UserGroup'] >= $row_rsMediaPrefs['uploadrankID']) { // OK to upload?> <div id="photosMenu"><nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
      
     
        <li class="nav-item"><a href="members/galleries/add_gallery.php" class="nav-link"><i class="glyphicon glyphicon-plus-sign"></i> Add  gallery</a></li>
        <li  class="nav-item" id="link_options"><a href="/photos/admin/" target="_blank" class="nav-link" rel="noopener"><i class="glyphicon glyphicon-cog"></i> Setting</a></li>
      </ul></div></nav>      </div>
	  
	   <form class="dropzone" name = "dropzone1" id="dropzone1" action="/photos/index.php" enctype="multipart/form-data" method="post" >
<div class="fallback">
    <input name="file" type="file" multiple >
    <input type="hidden" name="nodropzone" value="true">
  </div><input name="directory" type="hidden" value="users/<?php echo $_SESSION['MM_Username']; ?>/<?php echo session_id(); ?>/"></form>
  
  <?php } //ok to upload	?>
      <?php if ($totalRows_rsGalleries == 0) { // Show if recordset empty ?>
  <p class="alert alert-danger" role="alert">There are no galleries available to you. You may need to <a href="/login/index.php?accesscheck=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>">log in</a>.</p>
  <?php } // Show if recordset empty ?>
<?php if ($totalRows_rsGalleries > 0) { // Show if recordset not empty ?>
  <div id = "galleries" class="photoGallery">
   <div class="clearfix" >
        <ul class="galleryList gallery-square-grid"><?php  do { ?><li> 
           
            <?php if(isset($row_rsGalleries['imageURL'])) { ?>
            
            <a href="/photos/gallery/index.php?galleryID=<?php echo $row_rsGalleries['ID']; ?>" class="img" title="<?php echo htmlentities($row_rsGalleries['categoryname']); ?>">
            <img src="<?php 
			$imageURL = isset($row_rsGalleries['coverimageURL']) ? $row_rsGalleries['coverimageURL'] : $row_rsGalleries['imageURL'];
			$width = ($row_rsGalleries['coverwidth']>0) ? $row_rsGalleries['coverwidth'] : $row_rsGalleries['width'];
			$height = ($row_rsGalleries['coverheight']>0) ? $row_rsGalleries['coverheight'] : $row_rsGalleries['height']; 
			
			
			 $longestlength = ($width > $height) ? $width : $height; 
			  $ratio = ($longestlength>0) ? $image_sizes[$row_rsMediaPrefs['imagesize_gallery']]["width"]/$longestlength : 1; 
			  $width = ($width >0) ? "width=\"".intval($width * $ratio)."\" " : "";
			  $height = ($height > 0) ? "height=\"".intval($height * $ratio)."\"" : "";		
			
			
			
			
			
			echo getImageURL($imageURL,$row_rsMediaPrefs['imagesize_gallery']) ?>" alt="<?php echo htmlentities($row_rsGalleries['categoryname']); ?>" <?php echo $width;  echo $height; ?> class="<?php echo $row_rsMediaPrefs['imagesize_gallery']; ?>" />
            
            </a><?php } ?>
            <div class="photoTitle"><?php echo ($row_rsGalleries['categoryname']=="Untitled") ? "Gallery ".$row_rsGalleries['ID'] : htmlentities($row_rsGalleries['categoryname']); ?>&nbsp;(<?php echo $row_rsGalleries['numphotos']+$row_rsGalleries['numphotos2']; ?>)</div></li><?php    } while ($row_rsGalleries = mysql_fetch_assoc($rsGalleries)); ?></ul>
    </div>
  </div>
 
<table border="0" class="galleryBottomNav">
        <tr>
          
          <td><?php if ($pageNum_rsGalleries > 0) { // Show if not first page ?>
              <a href="<?php printf("%s?pageNum_rsGalleries=%d%s", $currentPage, max(0, $pageNum_rsGalleries - 1), $queryString_rsGalleries); ?>" rel="prev">Previous</a>
              <?php } // Show if not first page ?></td>
              
              <td><div class="photosCount">Galleries <?php echo ($startRow_rsGalleries + 1) ?> to <?php echo min($startRow_rsGalleries + $maxRows_rsGalleries, $totalRows_rsGalleries) ?> of <?php echo $totalRows_rsGalleries ?>. </div></td>
              
              
          <td><?php if ($pageNum_rsGalleries < $totalPages_rsGalleries) { // Show if not last page ?>
              <a href="<?php printf("%s?pageNum_rsGalleries=%d%s", $currentPage, min($totalPages_rsGalleries, $pageNum_rsGalleries + 1), $queryString_rsGalleries); ?>" rel="next" class="img"><img src="/core/images/icons/go-next.png" alt="Next image" width="16" height="16" /></a>
              <?php } // Show if not last page ?></td>
          
        </tr>
      </table>
       <?php } // Show if recordset not empty ?>
      <?php } else { //not OK to access ?>
      <p class="alert alert-danger" role="alert">The photo galleries are only available to members. Please <a href="/login/index.php?accesscheck=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>">log in</a>.</p>
      <?php } ?>
    </div>
    <script>
$("ul.galleryList li img").each(function(index, element) {
	var imageURL = $(this).attr("src");
	$(this).hide();
	$(this).parent().css("background-image","url("+imageURL+")");
    
});
</script>
    <!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php

mysql_free_result($rsLoggedIn);

mysql_free_result($rsPhotoCategory);

mysql_free_result($rsGalleries);
?>
