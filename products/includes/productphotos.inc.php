<?php require_once(SITE_ROOT.'photos/includes/galleryfunctions.inc.php'); 
$attr = "";

if($row_rsProductPrefs['gallerytype']==1) {  // lightbox
$attr = "data-fancybox=\"images\"";  ?>
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

 <?php } else if($row_rsProductPrefs['gallerytype']==3) { // zoom 
?>
<script src="/core/scripts/jquery.zoom.min.js"></script>
<script>
// https://www.jacklmoore.com/zoom/
$(function() {
	
	initImageZoom();	 
	$("#productImage a").click(function(e) {
		// stop link function on all images
		e.preventDefault();
	});
	 
	// swap images in tumb gallery on click with main
	$("#productThumbs a").click(function(e) {	   
	   $('img.zoomable').attr("src",$(this).attr("href"));
	   $('img.zoomable').parent().attr("href",$(this).attr("href"));
	   initImageZoom();
	   // for smaller screens auto scroll up after clicking thumb
	   $("html, body").animate({
        	scrollTop: $("#productImage").offset().top
    	}, 1000);
   });
});
 
function initImageZoom() {
	
	// zoom not needed on touch devices
	if(!is_touch_device()) {
		// if running already and gallery clicked		
		$('img.zoomable').parent().trigger('zoom.destroy');		
		$('img.zoomable').parent().css('display', 'block').zoom({url: $('img.zoomable').parent().attr("href")});
 	}
}

function is_touch_device() {
	return 'ontouchstart' in window // works on most browsers 
      || 'onmsgesturechange' in window; // works on ie10
};

</script>
<?php } ?><?php if(isset($row_rsProduct['galleryID'])) { 

$colname_rsPhotos = "-1";
if (isset($row_rsProduct['galleryID'])) {
  $colname_rsPhotos = $row_rsProduct['galleryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPhotos = sprintf("SELECT photos.*, productoptions.ID AS productoptionID FROM photos LEFT JOIN productoptions ON (productoptions.photoID = photos.ID) WHERE  photos.active = 1 AND categoryID = %s", GetSQLValueString($colname_rsPhotos, "int"));
$rsPhotos = mysql_query($query_rsPhotos, $aquiescedb) or die(mysql_error());
$row_rsPhotos = mysql_fetch_assoc($rsPhotos);
$totalRows_rsPhotos = mysql_num_rows($rsPhotos);

}

if(isset($row_rsPhotos['imageURL']) && $row_rsProduct['altimage']==1) { // swap main and first gallery image
	$swap = $row_rsPhotos['imageURL']; $row_rsPhotos['imageURL'] = $row_rsProduct['imageURL']; $row_rsProduct['imageURL'] = $swap;
}


?>

<div id="productImage">
  <ul>
    <li> <a href="<?php echo getImageURL($row_rsProduct['imageURL'],$row_rsProductPrefs['imagesize_enlarged']); ?>"  title="<?php echo htmlentities($row_rsProduct['title'], ENT_COMPAT, "UTF-8"); ?> - enlarged view" id="productImageLink" class="mainImage slimbox" <?php echo $attr; ?>><img src="<?php echo getImageURL($row_rsProduct['imageURL'],$row_rsProductPrefs['imagesize_product']); ?>" alt="<?php echo htmlentities($row_rsProduct['title'], ENT_COMPAT, "UTF-8"); ?>" class="mainimage  zoomable"  />
      <div class="productImageOverlay">
    <?php   $overlayimageURL = isset($row_rsProduct['imageURL3']) ? $row_rsProduct['imageURL3'] : (isset($row_rsProductPrefs['imageOverlayURL']) ? $row_rsProductPrefs['imageOverlayURL'] : "");
        if(trim($overlayimageURL)!="") { ?>
        <img src = "/Uploads/<?php echo $overlayimageURL; ?>" alt="Overlay image" />
        <?php } ?>
      </div>
      </a></li>
    <?php if($row_rsProductPrefs['gallerytype']==2) { // slide show
 if(isset($row_rsProduct['galleryID'])) { 
 $size = $row_rsProductPrefs['imagesize_product'];
	require_once('includes/productphotos.inc.php'); 
  } echo "</ul>";
    } else { // thumbs
   $size = $row_rsProductPrefs['imagesize_productthumbs']; ?>
  </ul>
 
  <div id="productThumbs"> 
    
    <!-- other thumbs gallery <?php echo $row_rsProduct['galleryID']; ?>--><?php if(isset($totalRows_rsPhotos) && $totalRows_rsPhotos>0) {?>
    <ul>
      <?php if($row_rsProductPrefs['gallerytype']==3) { // zoom and gallery add mainproduct image first too ?>
      <li><a href="<?php echo getImageURL($row_rsProduct['imageURL'],$row_rsProductPrefs['imagesize_enlarged']); ?>" class="slimbox" title="<?php echo $row_rsProduct['title']; ?> - enlarged view" id="productImageLink" data-image="<?php echo getImageURL($row_rsProduct['imageURL'],$row_rsProductPrefs['imagesize_product']); ?>" alt="<?php echo $row_rsProduct['title']; ?>"  <?php echo $attr; ?>><img src="<?php echo getImageURL($row_rsProduct['imageURL'],$size); ?>" alt="<?php echo $row_rsProduct['title']; ?> - enlarged view" class="<?php echo $size; ?>" >
        <div class="productImageOverlay"></div>
        </a></li><?php } 
		 do { ?><li class="<?php if(isset($row_rsPhotos['productoptionID'])) echo " productoption".$row_rsPhotos['productoptionID']." "; echo isset($row_rsPhotos['videoURL']) ? "  video " : "";  ?>"><?php if(isset($row_rsPhotos['videoURL'])) { // is video ?>
         <a data-fancybox href="<?php echo (strpos($row_rsPhotos['videoURL'],"http")===0) ? $row_rsPhotos['videoURL'] : "/Uploads/".$row_rsPhotos['videoURL']; ?>">
		<?php if($row_rsPhotos['imageURL']) { // video has thumbnail image ?>
        <img src="<?php echo getImageURL($row_rsPhotos['imageURL'],$size); ?>" alt="<?php echo $row_rsPhotos['title']; ?> - view video" class="<?php echo $size; ?>" >
		<?php } else { // justembed video
			embedVideo($row_rsPhotos['videoURL']);
		}?></a>
		 <?php 	 } else { ?>
         
         
         <a href="<?php echo getImageURL($row_rsPhotos['imageURL'],$row_rsProductPrefs['imagesize_enlarged']); ?>"  title="<?php echo $row_rsPhotos['title']; ?> - enlarged view" id="productImageLink" class="slimbox" data-image="<?php echo getImageURL($row_rsPhotos['imageURL'],$row_rsProductPrefs['imagesize_product']); ?>" alt="<?php echo $row_rsPhotos['title']; ?>" data-zoom-image="<?php echo getImageURL($row_rsPhotos['imageURL'],$row_rsProductPrefs['imagesize_enlarged']); ?>" <?php echo $attr; ?>><img src="<?php echo getImageURL($row_rsPhotos['imageURL'],$size); ?>" alt="<?php echo $row_rsPhotos['title']; ?> - enlarged view" class="<?php echo $size; ?>" ><div class="productImageOverlay"></div></a><?php } // end is image ?></li><?php } while ($row_rsPhotos = mysql_fetch_assoc($rsPhotos));  ?></ul><?php } // end is thumbs
	 } //end type thumbs?>
  </div><!-- end product thumbs --> 
  
 
</div><!-- end product image -->
<?php 
if(isset($rsPhotos) && is_resource($rsPhotos)) {
mysql_free_result($rsPhotos); 
}
?>
