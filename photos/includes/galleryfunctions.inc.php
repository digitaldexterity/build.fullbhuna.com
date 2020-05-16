<?php 
if(!function_exists("addPhoto")) {
function addPhoto( $imageURL,  $title="", $description="", $linkURL="", $createdbyID=0,  $statusID = 1, $width="", $height="") {
	global $database_aquiescedb, $aquiescedb;
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$insert = sprintf("INSERT INTO photos (userID, imageURL, linkURL, title, `description`, active, width, height, createddatetime) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)",
                       GetSQLValueString($createdbyID, "int"),
                       GetSQLValueString($imageURL, "text"),
					   GetSQLValueString($linkURL, "text"),
                       GetSQLValueString($title, "text"),
                       GetSQLValueString($description, "text"),
                       GetSQLValueString($statusID, "int"),
					   GetSQLValueString($width, "int"),
					   GetSQLValueString($height, "int"),
                       
                       "NOW()");
					  
			 mysql_query($insert, $aquiescedb) or die(mysql_error().": ".$insert);
			 $photoID = mysql_insert_id();
			 $update = "UPDATE photos SET ordernum = ".$photoID." WHERE ID = ".$photoID;
			 mysql_query($update, $aquiescedb) or die(mysql_error());
			 return $photoID;
}
}

if(!function_exists("addGallery")) {
function addGallery($categoryname, $createdbyID=0, $active=1,$accesslevel=0) {
	global $database_aquiescedb, $aquiescedb, $regionID;
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$regionID = isset($regionID) ? intval($regionID) : 0;
	$insert = "INSERT INTO photocategories (regionID, accesslevel, active, addedbyID, categoryname, createddatetime) VALUES (".$regionID.",0,1,".GetSQLValueString($createdbyID, "int").",".GetSQLValueString($categoryname, "text").", NOW())";
		mysql_query($insert, $aquiescedb) or die(mysql_error());
		$galleryID = mysql_insert_id();
		return $galleryID;
}
}




if(!function_exists("addPhotoToGallery")) {
function addPhotoToGallery($photoID=0,$galleryID=0,$createdbyID=0) {
	global $database_aquiescedb, $aquiescedb;
	mysql_select_db($database_aquiescedb, $aquiescedb);
	if($photoID>0 && $galleryID>0) {
		$select = "SELECT photos.ID, photos.categoryID, photoincategory.categoryID AS incategoryID FROM photos LEFT JOIN photoincategory ON (photos.ID = photoincategory.photoID  AND photoincategory.categoryID = ".intval($galleryID).") WHERE photos.ID = ".intval($photoID);
		$result = mysql_query($select, $aquiescedb) or die(mysql_error());
		$photo = mysql_fetch_assoc($result);
		if(!isset($photo['categoryID'])) {
			$update = "UPDATE photos SET categoryID = ".intval($galleryID)." WHERE ID = ".intval($photoID);
			mysql_query($update, $aquiescedb) or die(mysql_error());
		}
		if(!isset($photo['incategoryID'])) {
			$insert = "INSERT INTO photoincategory (photoID, categoryID, createdbyID, createddatetime) VALUES (".intval($photoID).",".intval($galleryID).",".intval($createdbyID).",NOW())";
			mysql_query($insert, $aquiescedb) or die(mysql_error());
		}
	}
}
}


if(!function_exists("deletePhoto")) {
function deletePhoto($photoID) {
	global $database_aquiescedb, $aquiescedb, $image_sizes;
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$select = "SELECT ID,imageURL FROM photos WHERE ID  =".GetSQLValueString($photoID, "int");
   $result = mysql_query($select, $aquiescedb) or die(mysql_error());
   $photo = mysql_fetch_assoc($result);
   // delete resized pictures
 
   	unlink(SITE_ROOT.getImageURL($photo['imageURL']));
	foreach($image_sizes as $size => $value) {
		unlink(SITE_ROOT.getImageURL($photo['imageURL'], $size));
	}
	
	$delete = "DELETE FROM photos WHERE ID = ".GetSQLValueString($photoID, "int");
   mysql_query($delete, $aquiescedb) or die(mysql_error());
   $delete = "DELETE FROM photoincategory WHERE photoID = ".GetSQLValueString($photoID, "int");
   mysql_query($delete, $aquiescedb) or die(mysql_error());
    $delete = "DELETE FROM photocomments WHERE photoID = ".GetSQLValueString($photoID, "int");
   mysql_query($delete, $aquiescedb) or die(mysql_error());
   
				
   
}}

if(!function_exists("embedVideo")) {
function embedVideo($videoURL, $controls = false, $autoplay=false, $mute = false, $class="") {
	$embedHTML = "";
	if(strpos($videoURL,"<iframe") ) {
		// ready-made
		$embedHTML = $videoURL;
	} else if (strpos($videoURL,"youtube.com")) {
		$embedHTML = youTubeURLembed($videoURL);
	} else { // plain mp4 file
		$videoURL = strpos($videoURL,"Uploads/") ? $videoURL : "/Uploads".$videoURL;
		$embedHTML .= "<video class=\"".$class."\">";
		$embedHTML .= "<source src='".$videoURL."'>";
		$embedHTML .= "</video>";
	}
	echo $embedHTML;
}
}

if(!function_exists("youTubeURLembed")) {
function youTubeURLembed($url, $width="100%", $height = "auto") {
	preg_match('/[\\?\\&]v=([^\\?\\&]+)/', $url, $matches);
    $id = $matches[1];
	return "<iframe type=\"text/html\" style=\"width:".$width.";  height:".$height.";\" src=\"https://www.youtube.com/embed/".$id."?rel=0&showinfo=0&color=white&iv_load_policy=3\" frameborder=\"0\" allowfullscreen></iframe>";
}
}
?>