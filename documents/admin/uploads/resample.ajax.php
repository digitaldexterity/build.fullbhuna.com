<?php require_once('../../../Connections/aquiescedb.php');?>
<?php require_once('../../../core/includes/upload.inc.php'); ?><?php 
if(isset($_SESSION['MM_UserGroup']) && $_SESSION['MM_UserGroup']>=7) { 
	$basename = basename($_GET['filename']);
	echo 	$basename;	
	if(preg_match("/(.jpeg$|.jpg$|.gif$|.png$)/i",$basename) && isset($image_sizes)) { 	
				
	 	if(strpos($basename,"_",1)===false || strpos($basename,"_",1)>2) { // not already a resample image
		  	$imagesizes = createImageSizes($_GET['filename']);
		  	echo " - Resampled";
	  	} else {
		  	echo " - Already resampled";
	  	}
	 } // end is an image
	  else {
		  echo " - Not image";
	}
				
}
		
?>