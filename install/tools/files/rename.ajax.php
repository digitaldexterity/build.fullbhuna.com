<?php require_once('../../../Connections/aquiescedb.php');?>
<?php require_once('../../../core/includes/upload.inc.php'); ?><?php require_once('../../../core/includes/framework.inc.php'); ?>
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



if(isset($_SESSION['MM_UserGroup']) && $_SESSION['MM_UserGroup']>=7) { 
	$path_parts = pathinfo($_GET['newfilename']);
	if(is_readable(UPLOAD_ROOT.$_GET['oldfilename']) || is_readable(UPLOAD_ROOT.$_GET['newfilename'])) { // may already have been moved
		createDirectory(UPLOAD_ROOT.$path_parts['dirname']);
		if(@copy(UPLOAD_ROOT.$_GET['oldfilename'] , UPLOAD_ROOT.$_GET['newfilename'])) { //write new 
			echo "Copied. ";	
		}
		$isImage = false;
		$basename = $path_parts['basename'];
		if(preg_match("/(.jpeg$|.jpg$|.gif$|.png$)/i",$basename) && isset($image_sizes)) { 	
			$isImage = true;	
			$prefix = "";					
			if($_GET['findreplace']==0 && strpos($basename,"_",1)===false || strpos($basename,"_",1)>2) { // not already a resample image
				$imagesizes = createImageSizes(UPLOAD_ROOT.$_GET['newfilename']);
				echo "Resampled. ";
			} else {
				echo "Already resampled. ";
				$parts = explode("_",$basename,2);
				$basename = count($parts)>0 ? $parts[1] : $basename;
			}
		 } // end is an image
		  else {
			  echo "Not image. ";
		}
		
		$table= str_replace(" ","",$_GET['table']);
		$field = str_replace(" ","",$_GET['field']);
		
		if($_GET['findreplace']==1) {
			$select = "SELECT ".$field." FROM ".$table." WHERE ID = ".intval($_GET['ID']);
			$result = mysql_query($select, $aquiescedb) or die(mysql_error());
			$row = mysql_fetch_assoc($result);
			$before = $row[$field];
			$after = str_replace($_GET['oldfilename'],$_GET['newfilename'],$before);
			$update = "UPDATE ".$table." SET ".$field." = ".GetSQLValueString($after, "text")." WHERE ID = ".intval($_GET['ID']);
			mysql_query($update, $aquiescedb) or die(mysql_error());
		} else {	
			$update = "UPDATE ".$table." SET ".$field." = ".GetSQLValueString($_GET['newfilename'], "text")." WHERE ".$field." = ".GetSQLValueString($_GET['oldfilename'], "text");	
			mysql_select_db($database_aquiescedb, $aquiescedb);
			mysql_query($update, $aquiescedb) or die(mysql_error());
		}
		$update = "UPDATE uploads SET systemversion = 2, uploads.newfilename = ".GetSQLValueString(UPLOAD_ROOT.$_GET['newfilename'], "text")." WHERE uploads.newfilename = ".GetSQLValueString(UPLOAD_ROOT.$_GET['oldfilename'], "text");
		mysql_query($update, $aquiescedb) or die(mysql_error());
		echo "Database updated. ";
		deleteFile(UPLOAD_ROOT.$_GET['oldfilename']) ;
		if($isImage) {
			foreach($image_sizes as $key => $attributes) {
				deleteFile(UPLOAD_ROOT.$attributes['prefix'].$basename);	
				echo "Deleted: ".UPLOAD_ROOT.$attributes['prefix'].$basename."; ";		
			}
			deleteFile(UPLOAD_ROOT.$basename) ;
		}
		echo "Original Deleted. ";
		
	} else {
		echo "CANNOT READ FILE: ".UPLOAD_ROOT.$_GET['oldfilename'];
	}				
}
		
?>