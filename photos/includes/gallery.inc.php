<?php // Copyright 2009 Paul Egan
if(!isset($aquiescedb)) {
	// if via ajax
	require_once('../../Connections/aquiescedb.php'); 
}
require_once('galleryfunctions.inc.php'); 
if(!isset($gallery_loaded)) {
	// only load scripts once if multiple includes

if($row_rsMediaPrefs['uselightbox']==1) { ?>
<script src="/photos/scripts/slimbox-2/js/slimbox2-autosize.js"></script>
<link href="/photos/scripts/slimbox-2/css/slimbox2-autosize.css" rel="stylesheet"  />
<script> 
$(function() {
	// add var use_slimbox = true in head to force its use
	if(!use_slimbox && typeof $.fancybox == 'function') {
		// fancy box loaded;
		console.log("Gallery using FancyBox");
	} else {
		// use slimbox
		console.log("Gallery using Slimbox");
		  $('a.slimbox').slimbox();
	}
});
</script>
<?php } ?>
<script>
if(!window.jQuery.ui){
	var script = document.createElement('script');
    script.type = "text/javascript";
    script.src = "https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js";
    document.getElementsByTagName('head')[0].appendChild(script);	
}
	
// When the document is ready set up our sortable with it's inherant function(s) 
$(document).ready(function() { 	
	$("ul.gallery-square-grid li img").each(function(index, element) {
		var imageURL = $(this).attr("src");
		$(this).hide();
		$(this).parent().css("background-image","url("+imageURL+")");    
	});
	if($(".galleryList").sortable("instance")) {
	 // if from previosu ajax call
		$(".galleryList").sortable().refresh();
		alert("Refesh");
	} else {
	$(".galleryList").sortable({ 
    	handle : '.handle', 
		update : function () { 
		
			var order = $('.galleryList').sortable('serialize'); 
        	$(".draginfo").load("/core/ajax/sort.ajax.php?table=photos&"+order); 
		} 
	}); 
	}
});



window.onload = function() {
 // executes when complete page is fully loaded, including all frames, objects and images

	
}
	</script>
    <link href="/photos/css/defaultGallery.css?v=b" rel="stylesheet"  />
    <?php
} // end load once
	
$gallery_loaded = true;

require_once(SITE_ROOT.'core/includes/framework.inc.php'); 

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
$userID = isset($userID) ? $userID : "-1";
$regionID = (isset($regionID) && intval($regionID)>0) ? intval($regionID): 1;
$currentPage = $_SERVER["PHP_SELF"];


$maxRows_rsPhotos = (isset($showAllPhotos) || isset($_GET['showall'])) ? 5000  :25;

//die("*".$maxRows_rsPhotos);

$pageNum_rsPhotos = 0;
if (isset($_GET['pageNum_rsPhotos'])) {
  $pageNum_rsPhotos = $_GET['pageNum_rsPhotos'];
}
$startRow_rsPhotos = $pageNum_rsPhotos * $maxRows_rsPhotos;


$galleryID = (isset($galleryID)) ? $galleryID : (isset($_GET['galleryID']) ? $_GET['galleryID'] : "-1"); 

$varUsername_rsPhotos = "-1";
if (isset($_SESSION['MM_Username'])) {
  $varUsername_rsPhotos = $_SESSION['MM_Username'];
}
$varUserID_rsPhotos = "-1";
if (isset($userID)) {
  $varUserID_rsPhotos = $userID;
}
$varUserTypeID_rsPhotos = "0";
if (isset($_SESSION['MM_UserGroup'])) {
  $varUserTypeID_rsPhotos = $_SESSION['MM_UserGroup'];
}
$varShowHidden_rsPhotos = "-1";
if (isset($_GET['showhidden'])) {
  $varShowHidden_rsPhotos = $_GET['showhidden'];
}
$varGalleryID_rsPhotos = "0";
if (isset($galleryID)) {
  $varGalleryID_rsPhotos = $galleryID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPhotos = sprintf("SELECT photos.ID, title,  photos.imageURL, photos.videoURL, photos.userID, COUNT(photocomments.ID) AS numcomments, photos.active, photos.description, photos.width, photos.height, users.username FROM photos LEFT JOIN photoincategory ON (photos.ID =photoincategory.photoID) LEFT JOIN photocomments ON (photocomments.photoID = photos.ID) LEFT JOIN users ON (photos.userID = users.ID) LEFT JOIN photocategories ON (photos.categoryID = photocategories.ID) WHERE (photos.active = 1 OR %s >=9 OR users.username = %s) AND (photocategories.active !=2 OR photocategories.active IS NULL) AND (photocategories.accesslevel <= %s OR photocategories.accesslevel IS NULL) AND (photos.categoryID = %s OR %s < 1 OR photoincategory.categoryID=%s) AND (photos.userID = %s OR %s < 1)  AND (photos.active < 2 OR %s = 1) GROUP BY photos.ID ORDER BY photos.ordernum", GetSQLValueString($varUserTypeID_rsPhotos, "int"),GetSQLValueString($varUsername_rsPhotos, "text"),GetSQLValueString($varUserTypeID_rsPhotos, "int"),GetSQLValueString($varGalleryID_rsPhotos, "int"),GetSQLValueString($varGalleryID_rsPhotos, "int"),GetSQLValueString($varGalleryID_rsPhotos, "int"),GetSQLValueString($varUserID_rsPhotos, "int"),GetSQLValueString($varUserID_rsPhotos, "int"),GetSQLValueString($varShowHidden_rsPhotos, "int"));
//$query_limit_rsPhotos = sprintf("%s LIMIT %d, %d", $query_rsPhotos, $startRow_rsPhotos, $maxRows_rsPhotos);
$query_limit_rsPhotos = $query_rsPhotos;
$rsPhotos = mysql_query($query_limit_rsPhotos, $aquiescedb) or die(mysql_error());
$row_rsPhotos = mysql_fetch_assoc($rsPhotos);

if (isset($_GET['totalRows_rsPhotos'])) {
  $totalRows_rsPhotos = $_GET['totalRows_rsPhotos'];
} else {
  $all_rsPhotos = mysql_query($query_rsPhotos);
  $totalRows_rsPhotos = mysql_num_rows($all_rsPhotos);
}
$totalPages_rsPhotos = ceil($totalRows_rsPhotos/$maxRows_rsPhotos)-1;



mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsIncMediaPrefs = "SELECT * FROM mediaprefs WHERE ID =".$regionID;
$rsIncMediaPrefs = mysql_query($query_rsIncMediaPrefs, $aquiescedb) or die(mysql_error());
$row_rsIncMediaPrefs = mysql_fetch_assoc($rsIncMediaPrefs);
$totalRows_rsIncMediaPrefs = mysql_num_rows($rsIncMediaPrefs);

$colname_rsThisGallery = "-1";
if (isset($galleryID)) {
  $colname_rsThisGallery = $galleryID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisGallery = sprintf("SELECT * FROM photocategories WHERE ID = %s", GetSQLValueString($colname_rsThisGallery, "int"));
$rsThisGallery = mysql_query($query_rsThisGallery, $aquiescedb) or die(mysql_error());
$row_rsThisGallery = mysql_fetch_assoc($rsThisGallery);
$totalRows_rsThisGallery = mysql_num_rows($rsThisGallery);

$queryString_rsPhotos = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsPhotos") == false && 
        stristr($param, "totalRows_rsPhotos") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsPhotos = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsPhotos = sprintf("&totalRows_rsPhotos=%d%s", $totalRows_rsPhotos, $queryString_rsPhotos); ?>

<div class="photoGallery">
  <h2><?php echo isset($row_rsThisGallery['categoryname']) ? $row_rsThisGallery['categoryname'] : "Gallery"; ?></h2>

<?php if ($totalRows_rsPhotos > 0) { // Show if recordset not empty ?>
 
  
  <div class="galleryNav galleryTopNav">
    <?php if ($pageNum_rsPhotos > 0) { // Show if not first page ?>
      <a href="<?php printf("%s?pageNum_rsPhotos=%d%s", $currentPage, max(0, $pageNum_rsPhotos - 1), $queryString_rsPhotos); ?>" class="prev">Previous</a><?php } // Show if not first page ?>
    <span class="photosCount">Photos <?php echo ($startRow_rsPhotos + 1) ?> to <?php echo min($startRow_rsPhotos + $maxRows_rsPhotos, $totalRows_rsPhotos) ?> of <?php echo $totalRows_rsPhotos ?> <a href="<?php echo "/photos/gallery/index.php?galleryID=".$row_rsThisGallery['ID']."&showall=true"; ?>" class="showall">Show all</a></span>
        <?php if ($pageNum_rsPhotos < $totalPages_rsPhotos) { // Show if not last page ?>
          <a href="<?php printf("%s?pageNum_rsPhotos=%d%s", $currentPage, min($totalPages_rsPhotos, $pageNum_rsPhotos + 1), $queryString_rsPhotos); ?>" class="next">Next</a>
          <?php } // Show if not last page ?>
          <span class="draginfo"></span>
      </div>
      <div class="clearfix" >
        <ul class="galleryList <?php switch($row_rsIncMediaPrefs['gallerytype']) {
			case 1 : echo "gallery-square-grid"; break;
			case 2 : echo "gallery-masonry masonry"; break;
			default :  break;
		} ?>"><?php  $rec = 0; do {
			  
			// authorised to edit 
			$photoEdit =  (isset($row_rsLoggedIn['ID']) && ($row_rsLoggedIn['ID'] == $row_rsPhotos['userID'] || (isset($_SESSION['MM_UserGroup']) && $_SESSION['MM_UserGroup'] >=9)))  ?
			"" : "display: none;"; ?><li <?php $rec++; if ($rec <= $startRow_rsPhotos || $rec > ($startRow_rsPhotos + $maxRows_rsPhotos)) { echo "style=\"display: none;\""; }  ?> id="listItem_<?php echo $row_rsPhotos['ID']; ?>">
           
              <?php  $title = ($row_rsPhotos['title']=="Untitled") ? "Photo ".$row_rsPhotos['ID'] : $row_rsPhotos['title'];
			  $description = (trim($row_rsPhotos['description']!="")) ? " - ".$row_rsPhotos['description'] : "";
			 // $exif =  @exif_read_data(UPLOAD_ROOT.$row_rsPhotos['imageURL']);
			 // $description.= isset($exif['Make']) ? "\nCamera: ".$exif['Make']." ".$exif['Model'] : "";
			  
			  // get image width and height for HTML 
			  if((isset($image_sizes[$row_rsIncMediaPrefs['imagesize_gallery']]["resizetype"]) && $image_sizes[$row_rsIncMediaPrefs['imagesize_gallery']]["resizetype"]=="crop")) {
				   $width = $image_sizes[$row_rsIncMediaPrefs['imagesize_gallery']]["width"];
			  		$height = $image_sizes[$row_rsIncMediaPrefs['imagesize_gallery']]["height"];		 
			  } else {
			  	$longestlength = ($row_rsPhotos['width'] > $row_rsPhotos['height']) ? $row_rsPhotos['width'] : $row_rsPhotos['height']; 
			  	$ratio = ($longestlength>0) ? $image_sizes[$row_rsIncMediaPrefs['imagesize_gallery']]["width"]/$longestlength : 1;  
			 
			  	$width = ($row_rsPhotos['width'] >0) ? "width=\"".intval($row_rsPhotos['width'] * $ratio)."\"" : "";
			  	$height = ($row_rsPhotos['height'] > 0) ? "height=\"".intval($row_rsPhotos['height'] * $ratio)."\"" : "";
			   } 
			   $thumbURL = getImageURL($row_rsPhotos['imageURL'],"medium");
			  $largeURL = getImageURL($row_rsPhotos['imageURL'],"large");
			  
              if(isset($row_rsPhotos['videoURL'])) {
				 
				  echo "<a href=\"/photos/video.php?videoID=".$row_rsPhotos['ID']."\" target=\"_blank\" title=\"".htmlentities($title.$description)."\"";
				 echo ( $row_rsIncMediaPrefs['uselightbox']==1)  ? " class=\"fancybox\" >" : ">"; 
			  } else { 
			  
			   ?><a href="<?php echo ( $row_rsIncMediaPrefs['uselightbox']==1)  ?  $largeURL : "/photos/photo.php?photoID=".$row_rsPhotos['ID'];  ?>"  title="<?php echo htmlentities($title.$description); ?>" class="img slimbox" data-fancybox="images"  ><?php } ?> 
               
               <?php if(isset($row_rsPhotos['imageURL'])) { // is image or video thumbnail - otherwise just embed video 
			    ?>
               <img src="<?php echo $thumbURL; ?>" alt="<?php echo strip_tags($description); ?>"  title="<?php echo $title; ?>" class="<?php echo $thumbURL; ?>"  /><?php } else { embedVideo($row_rsPhotos['videoURL']); } ?>
               
               
               
                </a>
              <?php if((isset($_SESSION['MM_Username']) && $_SESSION['MM_Username'] == $row_rsPhotos['username']) || (isset($_SESSION['MM_UserGroup']) && $_SESSION['MM_UserGroup']>=8)) { // if uploader or admin can edit photo ?>
              <div class="photoUpdateLink">
                <div class="status status<?php echo $row_rsPhotos['active']; ?>"></div><div class="handle"></div>
                <a href="/photos/members/update_photo.php?photoID=<?php echo $row_rsPhotos['ID']; ?>&returnURL=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>">Edit</a>
                </div>
              <?php } ?>
           
            <div class="photoTitle"> <?php echo $title; ?></div>
            <?php if ($row_rsIncMediaPrefs['showcomments']==1) { ?>
            <div class="photoCommentsLink"><a href="/photos/photo.php?photoID=<?php echo $row_rsPhotos['ID']; ?>">Comments</a>: <?php echo $row_rsPhotos['numcomments']; ?> </div><?php } ?></li><?php    } while ($row_rsPhotos = mysql_fetch_assoc($rsPhotos)); ?></ul>
      </div>
      
       <div class="galleryNav galleryBottomNav">
    <?php if ($pageNum_rsPhotos > 0) { // Show if not first page ?>
      <a href="<?php printf("%s?pageNum_rsPhotos=%d%s", $currentPage, max(0, $pageNum_rsPhotos - 1), $queryString_rsPhotos); ?>" class="prev">Previous</a><?php } // Show if not first page ?>
    <span class="photosCount">Photos <?php echo ($startRow_rsPhotos + 1) ?> to <?php echo min($startRow_rsPhotos + $maxRows_rsPhotos, $totalRows_rsPhotos) ?> of <?php echo $totalRows_rsPhotos ?></span>
        <?php if ($pageNum_rsPhotos < $totalPages_rsPhotos) { // Show if not last page ?>
          <a href="<?php printf("%s?pageNum_rsPhotos=%d%s", $currentPage, min($totalPages_rsPhotos, $pageNum_rsPhotos + 1), $queryString_rsPhotos); ?>" class="next">Next</a>
          <?php } // Show if not last page ?>
      </div>
      
      
      

  <?php } // Show if recordset not empty ?>
<?php if ($totalRows_rsPhotos == 0) { // Show if recordset empty ?>
  <p>There are currently no photos in this gallery.</p>
  <?php } // Show if recordset empty ?>
  </div>
  <?php 
mysql_free_result($rsPhotos);

mysql_free_result($rsIncMediaPrefs);

mysql_free_result($rsThisGallery);
?>
