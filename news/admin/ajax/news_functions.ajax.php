<?php require_once('../../../Connections/aquiescedb.php'); ?><?php require_once('../../../core/includes/adminAccess.inc.php'); ?><?php require_once('../../../core/includes/framework.inc.php'); ?>
<?php if (!function_exists("GetSQLValueString")) {
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



if(isset($_GET['cmd'], $_GET['newsID']) && trim($_GET['cmd'])!="" && intval($_GET['newsID'])>0) { 
mysql_select_db($database_aquiescedb, $aquiescedb);
if($_GET['cmd']=="remove_image") {
	$update = "UPDATE news SET imageURL = NULL WHERE ID = ".intval($_GET['newsID']);
	 
  	$result = mysql_query($update, $aquiescedb) or die(mysql_error().$update);
	if(mysql_affected_rows()>0) {
		echo "SUCCESS";
	} else {
		echo "ERROR: Image not removed";
	}
} else if($_GET['cmd']=="swap_image") {
	$select = "SELECT photos.ID, photos.imageURL FROM news LEFT JOIN  photos ON photos.categoryID = news.photogalleryID WHERE news.ID = ".intval($_GET['newsID'])." ORDER BY photos.ordernum LIMIT 1";
	$result = mysql_query($select, $aquiescedb) or die(mysql_error().$select);
	if(mysql_num_rows($result)>0) {
		$galleryphoto = mysql_fetch_assoc($result);
	}
	$select = "SELECT ID, imageURL FROM news WHERE news.ID = ".intval($_GET['newsID'])." ";
	$result = mysql_query($select, $aquiescedb) or die(mysql_error().$select);
	if(mysql_num_rows($result)>0) {
		$newsphoto = mysql_fetch_assoc($result);
	}
	if(isset($galleryphoto['imageURL'])) {
		$update = "UPDATE  news SET imageURL = ".GetSQLValueString($galleryphoto['imageURL'], "text")." WHERE news.ID = ".intval($_GET['newsID'])." ";		
		$result = mysql_query($update, $aquiescedb) or die(mysql_error().$update);
		if(isset($newsphoto['imageURL'])) { // we have a main image
			$update = "UPDATE  photos SET imageURL = ".GetSQLValueString($newsphoto['imageURL'], "text")." WHERE photos.ID = ".$galleryphoto['ID'];		
			$result = mysql_query($update, $aquiescedb) or die(mysql_error().$update);
		} else { // remove image from gallery
			$update = "UPDATE  photos SET active = 0 WHERE photos.ID = ".$galleryphoto['ID'];		
			$result = mysql_query($update, $aquiescedb) or die(mysql_error().$update);
		}
		echo getImageURL($galleryphoto['imageURL'], "thumb"); // return Image Url to replace main image via JS on page
	} else {
		echo "ERROR: No swappable images";
	}
} else {
echo "ERROR: No command";
}


} else {
	echo "ERROR:  No string";
}?>