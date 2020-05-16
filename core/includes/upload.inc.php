<?php // Copyright 2013 Paul Egan ?>
<?php if(!defined("SITE_ROOT")) die(); // can only be called from FB ?>
<?php require_once(SITE_ROOT."core/includes/framework.inc.php"); 
/* recommend set ini values for 'post_max_size', 'upload_max_filesize' and 'memory_limit' - to eg '8M' these cannot be changed by script
also use <input type="hidden" name="MAX_FILE_SIZE" value="8000000" />
variables below can be defined:

$serverSavePath - the path within Uploads to save to (must end with a slash - e.g. admin/)
$formfield - $formfield can be set for uploaded file field - default 'filename'

OUTPUT array $uploaded['sameasfile'][key]
												
*/
$regionID = isset($regionID) ? $regionID : 1;


if(!defined('UPLOAD_VERSION')) define('UPLOAD_VERSION',2);

if(!defined('GD_VERSION')) define('GD_VERSION',2); // backward compaibility should be defind in prefs
$blacklist = array(".php", ".phtml", ".php3", ".php4", ".js", ".shtml", ".pl" ,".py");
$allowed_images = array("image/jpeg","image/jpg","image/png","image/gif","image/pjpeg","image/svg+xml");
$allowed_video = array("video/x-flv","application/vnd.ms-asf","video/x-ms-asf","video/x-ms-asx","video/x-msvideo","video/x-ms-wmv","video/x-m4v","video/quicktime","video/mp4","video/mpeg","video/avi","video/3gpp","video/3gpp2","video/vnd.vivo");
$allowed_audio = array("audio/mpeg","audio/mpeg3","audio/x-mpeg-3");
$allowed_files = array("application/postscript","application/eps","image/tiff","image/bmp","image/photoshop", "image/x-photoshop", "image/psd", "application/photoshop", "application/psd", "zz-application/zz-winassoc-psd", "application/octet-stream","binary/octet-stream","application/vnd.ms-powerpoint","application/vnd.ms-xpsdocument","application/vnd.ms-excel","application/pdf","application/x-octet-stream","text/rtf","application/rtf","text/plain","text/csv","text/comma-separated-values","application/msword","application/zip", "application/x-zip-compressed","application/x-stuffit","application/x-rar-compressed","application/x-shockwave-flash","application/vnd.openxmlformats-officedocument.wordprocessingml.document",					   "application/vnd.openxmlformats-officedocument.wordprocessingml.template","application/vnd.openxmlformats-officedocument.presentationml.presentation",
"application/vnd.openxmlformats-officedocument.presentationml.slideshow",
"application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
"application/vnd.openxmlformats-officedocument.spreadsheetml.template",
"application/x-mspublisher","application/vnd.ms-publisher","application/force-download","application/x-force-download","application/x-download");

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


if (!function_exists("Image")) {
function Image($file, $resizetype ="crop", $size = null, $dest = null, $quality = 90, $rotate = 0, $radius = 0 ) {
	
	@ini_set('memory_limit', '512M');
	// $size ="WIDTHxHEIGHT:XSTART:YSTART (last two optional if crop from specified position)

    $image = @imagecreatefromstring(file_get_contents($file));
	if($image !== false) { // is an image
		$info = getimagesize($file);
		$crop = ($resizetype == "crop" && $size != null) ? str_replace("x",":",$size) : null;
		$newsizes = explode(":", $size);
		//$image = substr_replace($image, pack("cnn", 1, 300, 300), 13, 5); //convert to 72dpi

	
		if (is_resource($image) === true && defined("GD_VERSION")) { // we have an image and GD library
			$x = 0;
			$y = 0;
			$width = imagesx($image);
			$height = imagesy($image);
			
			if($resizetype=="contain") {
				if($width > $height) {
					$resizetype="scalewidth";
				} else {
					$resizetype="scaleheight";
				}				
			}
	
			/*
			CROP (Aspect Ratio) Section			
			width:height[:startx:starty]
			*/
	
			if (is_null($crop) === true) {
				$crop = array($width, $height);
			} else {
				$crop = array_filter(explode(':', $crop));
	
				if (empty($crop) === true) {
					$crop = array($width, $height);
				} else {
					if ((empty($crop[0]) === true) || (is_numeric($crop[0]) === false)) {
							$crop[0] = $crop[1];
					} else if ((empty($crop[1]) === true) || (is_numeric($crop[1]) === false)) {
							$crop[1] = $crop[0];
					}
				}
				
				if(isset($crop[2])) { // new x:y set (from cropping scripts)
					$width = $crop[0]; $height= $crop[1];
					$x = $crop[2]; $y = $crop[3];
				} else { // best fit using crop size
					$ratio = array(0 => $width / $height, 1 => $crop[0] / $crop[1]);
		
					if ($ratio[0] > $ratio[1]) {
						$width = $height * $ratio[1];
						$x = (imagesx($image) - $width) / 2;
					}
		
					else if ($ratio[0] < $ratio[1]) {
						$height = $width / $ratio[1];
						$y = (imagesy($image) - $height) / 2;
					}
				}	
			}
			
			
	
			/* Resize Section*/
	
			if (is_null($size) === true) {
				$size = array($width, $height);
			} else {
				$size = array_filter(explode('x', $newsizes[0]));	
				if (empty($size) === true || $size[0]> imagesx($image) || (isset($size[1]) && $size[1]> imagesy($image))) { // if not set or either new size is bigger than original
					$size = array(imagesx($image), imagesy($image)); // new size = origial size
				} else {
					if ($resizetype=="scaleheight" || (empty($size[0]) === true) || (is_numeric($size[0]) === false)) {
						$size[0] = round($size[1] * $width / $height);
					} else if ($resizetype=="scalewidth" ||  (empty($size[1]) === true) || (is_numeric($size[1]) === false)) {
						$size[1] = round($size[0] * $height / $width);
					}
				}
			}
	
		   $newimage = (GD_VERSION>=2) ? ImageCreateTrueColor($size[0], $size[1]) : ImageCreate($size[0], $size[1]);
	
			if (is_resource($newimage) === true) {
				if(GD_VERSION>=2) {				
					if (($info[2] == IMAGETYPE_GIF) || ($info[2] == IMAGETYPE_PNG) ) {
						$trnprt_indx = imagecolortransparent($image);
		   				$palletsize = imagecolorstotal($image);
							   
						// If we have a specific transparent color
						if ($trnprt_indx >= 0 && $trnprt_indx<$palletsize) {
		  
						// Get the original image's transparent color's RGB values
						$trnprt_color    = imagecolorsforindex($image, $trnprt_indx);
		   
						// Allocate the same color in the new image resource
						$trnprt_indx    = imagecolorallocate($newimage, $trnprt_color['red'], $trnprt_color['green'], $trnprt_color['blue']);
		   
						// Completely fill the background of the new image with allocated color.
						imagefill($newimage, 0, 0, $trnprt_indx);
		   
						// Set the background color for new image to transparent
						imagecolortransparent($newimage, $trnprt_indx);	  
					} 
					// Always make a transparent background color for PNGs that don't have one allocated already
					elseif ($info[2] == IMAGETYPE_PNG) {
	   
						// Turn off transparency blending (temporarily)
						imagealphablending($newimage, false);
				   
						// Create a new transparent color for image
						$color = imagecolorallocatealpha($newimage, 0, 0, 0, 127);
				   
						// Completely fill the background of the new image with allocated color.
						imagefill($newimage, 0, 0, $color);
				   
						// Restore transparency blending
						imagesavealpha($newimage, true);
					  } // end transparancy
					} // end gif or png
					ImageCopyResampled($newimage, $image, 0, 0, $x, $y, $size[0], $size[1], $width, $height);
					ImageInterlace($newimage, true);
				} else { // gd <2
					imagecopyresized($newimage, $image, 0, 0, $x, $y, $size[0], $size[1], $width, $height);
				} // end gd <2
				if (preg_match("/png$/i",$file)) {					
					imagepng($newimage,$dest,0); // 0 = no compression
				} else if (preg_match("/gif$/i",$file)) {				
					imagegif($newimage,$dest); 
				} else {			
					imagejpeg($newimage,$dest,$quality);
				}
				//die($file.":".$dest.":".$width.":".$height.":".$size[0].":".$size[1].":".$x.":".$y);
				@chmod($dest,0666);
				if($radius>0|| $rotate>0) {
					roundCorners($dest,$radius, $rotate);
					
					
				}
				imagedestroy($newimage); 
				imagedestroy($image); 
				return $size;
			}
		}
	}
    return false;
}
}


if(!function_exists("createImageSizes")) {
function createImageSizes($filename,$roundedpx=0,$rotate=0,$watermark="", $create_sizes=array()) {
	global $image_sizes;
	$imagesize = array();
	$newsize = array();
	$basename = basename($filename);
	$dirname = dirname($filename)."/";
	
	$create_sizes = (is_array($create_sizes) && !empty($create_sizes)) ? $create_sizes : $image_sizes;	
	
	foreach($create_sizes as $key => $newimage) {
		
		if($key == "square") { // back compat
			$newimage['height'] = $newimage['width'];		
		} 
		
		// round images if set in sizes if not in attribute
		$thisroundedpx = ($roundedpx == 0 && isset($newimage['roundedpx'])) ? $newimage['roundedpx'] : $roundedpx;
		
		if (isset($newimage['resizetype'])) { 
		/* can be:
		 crop  [scale then crop to fit]
		 fit [scale then unconstrained squeeze to fit] 
		 contain [scale to fit whole image into size ]
		 scalewidth [default if only width given] 
		 scaleheight [default if only height given]
		 */
			$resizetype = $newimage['resizetype'];
		} else if(!isset($newimage['height'])) {
			$resizetype = "fitwidth";
		} else if(!isset($newimage['width'])){
			$resizetype = "fitheight";
		} else {
			$resizetype = "crop";
		}
		
		if(strpos($newimage['prefix'],"/")!==false) { // prefix is a directory
			createDirectory($dirname.$newimage['prefix']);
		}
		$size = isset($newimage['width']) ? $newimage['width'] : "";
		$size .= "x";
		$size .= isset($newimage['height']) ? $newimage['height'] : "";		
		$dest = $dirname.$newimage['prefix'].$basename;
		$quality = isset($newimage['quality']) ? $newimage['quality'] : 90;
		$newsize = Image($filename, $resizetype, $size, $dest, $quality,$rotate, $thisroundedpx);				
		$imagesize[$newimage['prefix'].'width'] = $newsize[0];
		$imagesize[$newimage['prefix'].'height'] = $newsize[1];
	}
	return $imagesize;
}
}

if(!function_exists("addtodatabase")) {
function addtodatabase($filename, $newfilename, $mimetype, $size = 0, $filesystemversion = 2) { 

	global $database_aquiescedb, $aquiescedb, $regionID;
	$regionID = isset($regionID) ? intval($regionID) : 0;
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$username = isset($_SESSION['MM_Username']) ? $_SESSION['MM_Username'] : "-1";
	$select = "SELECT ID FROM users WHERE username = ".GetSQLValueString($username, "text")." LIMIT 1";
	$rows = mysql_query($select, $aquiescedb) or die(mysql_error());
	$row = mysql_fetch_assoc($rows);
	$userID = isset($row['ID']) ? $row['ID'] : 0;
	$insert = "INSERT INTO uploads (filename, newfilename, mimetype, filesize, systemversion, regionID, createdbyID, createddatetime) VALUES (".GetSQLValueString($filename, "text").",".GetSQLValueString($newfilename, "text").",".GetSQLValueString($mimetype, "text").",".GetSQLValueString($size, "int").",".GetSQLValueString($filesystemversion, "int").",".intval($regionID).",".intval($userID).",NOW())";
	mysql_query($insert, $aquiescedb) or die(mysql_error());
	$uploadID = "".mysql_insert_id(); // doesn't work without cncatenation - why?
	return $uploadID;
} // end addto database
}

if (!function_exists("getUploads")) {
function getUploads($serverSavePath=UPLOAD_ROOT,$create_sizes=array(),$suffix="",$roundedpx=0,$rotate=0,$watermark="",$allowed_extensions=array(),$resize_images=true) { 
// save path is specific, otherwise upload root
	// add date to save path
	global $image_sizes, $allowed_images, $allowed_video,$allowed_audio, $allowed_files, $blacklist, $regionID;
	if(isset($_FILES) && !empty($_FILES)) { // file(s) uploaded
		$uploaded = array();
		$imagesizes = array();
		if(is_array($create_sizes) && empty($create_sizes)) {
			 $create_sizes = $image_sizes; // create sizes specified or otherwise those in preferences, if ===false no sizes are created
		} 
		// add folder for date if server path not pre-set
	
		$prefix = date('Y').DIRECTORY_SEPARATOR.date('m').DIRECTORY_SEPARATOR.date('d').DIRECTORY_SEPARATOR;	
		createDirectory($serverSavePath.$prefix); 
		$prefix .= substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 8)."_"; // set random security prefix
		foreach($_FILES as $formfield => $value) { // count through form fields
			if(!is_array($_FILES[$formfield]['name'])) {  // if not array make into one
				$_FILES[$formfield]['name']=array($_FILES[$formfield]['name']);
				$_FILES[$formfield]['type']=array($_FILES[$formfield]['type']);
				$_FILES[$formfield]['size']=array($_FILES[$formfield]['size']);
				$_FILES[$formfield]['tmp_name']=array($_FILES[$formfield]['tmp_name']);
				$_FILES[$formfield]['error']=array(@$_FILES[$formfield]['error']);
			}	// end make into array				
			foreach($_FILES[$formfield]['name'] as $key => $filename) { 
			// count through all files in array
				foreach ($blacklist as $file)
				{
					if(preg_match("/$file\$/i", $filename))
					{
						die("ERROR: Uploading executable files Not Allowed");
					}
				}
				if($filename != "") { 
				$error = "";
				// only process if has a name (necessary for multiple upload fields where some may be blank)
					if (isset($_FILES[$formfield]['error'][$key]) && $_FILES[$formfield]['error'][$key] >0) { // is error
						switch ($_FILES[$formfield]['error'][$key]) {  
						case 1: $error= $filename." is bigger than the web site server allows."; break;
           				case 2: $error= $filename." is bigger than the web site page allows."; break;
            			case 3: $error= "Only part of ".$filename." was uploaded. Please try again."; break;
            			case 4: $error= "No file was uploaded. Please try again"; break;
						default: $error = "There was an unidentified error with: ".$filename;
         				} 
					} else { // no error so far
						$filetype = $_FILES[$formfield]['type'][$key];
						$ext = strtolower(pathinfo($_FILES[$formfield]['name'][$key], PATHINFO_EXTENSION));
						if (!(in_array($filetype,$allowed_images) ||
						 in_array($filetype,$allowed_files) || 
						 in_array($filetype,$allowed_video) ||
						 in_array($filetype,$allowed_audio) || 
						 defined("ALLOW_UPLOAD_ANY")) || 
						 (is_array($allowed_extensions) && count($allowed_extensions)>0 && !in_array($ext,$allowed_extensions))) { // not allowed
							$error = $filename." (".$filetype.") is not one of the supported file types.";
						} else { // allowed type
							$filename = str_replace(" ", "-",$filename); // replace spaces to keep readable and remove all other chars below
							$new_filename = $prefix.strtolower(preg_replace("/[^a-zA-Z0-9\-.]/", "_", $filename)); /* some image manipulation scripts seem to need lower case */
							$newfile = $serverSavePath.$new_filename;		// still to add image size prefix									
							move_uploaded_file($_FILES[$formfield]['tmp_name'][$key], $newfile); @chmod($newfile,0666);
							if($resize_images && preg_match("/(.jpeg$|.jpg$|.gif$|.png$)/i",$newfile) && is_array($create_sizes) && (!isset($create_sizes['regionID']) || $create_sizes['regionID']==$regionID)) { 						 								
								// use exif to fix lopsided images:
								adjustPicOrientation($newfile);
								$imagesizes = createImageSizes($newfile,$roundedpx,0,"",$create_sizes);
								// delete original if needed
								list($width, $height, $type, $attr) = getimagesize($newfile);
								$uploaded[$formfield][$key]['width'] = $width;
								$uploaded[$formfield][$key]['height'] = $height;
								if(isset($keep_original) && $keep_original === false) {	unlink($im_file_name); }								
								foreach($imagesizes as $dimension =>$value) {
									$uploaded[$formfield][$key][$dimension] = $imagesizes[$dimension];
								}
							} // end is an image
							// add to database and add to $uploadID
							$uploaded[$formfield][$key]['uploadID'] = addtodatabase($filename,$newfile, $_FILES[$formfield]['type'][$key], $_FILES[$formfield]['size'][$key]); 
							
							$uploaded[$formfield][$key]['name']=$filename; 
							$uploaded[$formfield][$key]['type']=$_FILES[$formfield]['type'][$key];
							$uploaded[$formfield][$key]['size']=$_FILES[$formfield]['size'][$key]; 	
							$uploaded[$formfield][$key]['newname']=$new_filename;
							// also make corresposing post value same if input filed starts with file_							
							if(substr($formfield,0,5)=="file_") {	
								$fieldname = str_replace("file_","",$formfield);		
								$_POST[$fieldname] = $new_filename;			
							}						
						} // end allowed type
					} // no upload error
					if($error !="") {
						$uploaded[$formfield][$key]['error'] = $error;
					}
				}// has name			
			} // end count through array
		} // end count through form fields
		return $uploaded;
	} // end file(s) uploaded
}// end function
}

if (!function_exists("roundCorners")) {
function roundCorners($image_file,$corner_radius=0,$rotate=0,$topleft=true,$topright=true,$bottomright=true,$bottomleft=true) {
	
	$image = imagecreatetruecolor($corner_width, $corner_height); 
	
	
	$size = getimagesize($image_file); 
	$white = ImageColorAllocate($image,255,255,255);
	$black = ImageColorAllocate($image,0,0,0);
	
	if (preg_match("/png$/i",$image_file)) {
		$image = imagecreatefrompng($image_file); 
	} else if (preg_match("/gif$/i",$image_file)) {
		$image = imagecreatefromgif($image_file); 
	} else {
		$image = imagecreatefromjpeg($image_file); 
	}
	
	if($corner_radius>0) {
	$corner_source = imagecreatefrompng(SITE_ROOT.'documents/images/rounded_corner_40px.png');

	$corner_width = imagesx($corner_source);  
	$corner_height = imagesy($corner_source);  
	$corner_resized = ImageCreateTrueColor($corner_radius, $corner_radius);
	ImageCopyResampled($corner_resized, $corner_source, 0, 0, 0, 0, $corner_radius, $corner_radius, $corner_width, $corner_height);

	$corner_width = imagesx($corner_resized);  
	$corner_height = imagesy($corner_resized);  
	

	// Top-left corner
	if ($topleft == true) {
    	$dest_x = 0;  
    	$dest_y = 0;  
    	imagecolortransparent($corner_resized, $black); 
    	imagecopymerge($image, $corner_resized, $dest_x, $dest_y, 0, 0, $corner_width, $corner_height, 100);
	} 

	// Bottom-left corner
	if ($bottomleft == true) {
   		$dest_x = 0;  
    	$dest_y = $size[1] - $corner_height; 
    	$rotated = imagerotate($corner_resized, 90, 0);
    	imagecolortransparent($rotated, $black); 
    	imagecopymerge($image, $rotated, $dest_x, $dest_y, 0, 0, $corner_width, $corner_height, 100);  
	}

	// Bottom-right corner
	if ($bottomright == true) {
    	$dest_x = $size[0] - $corner_width;  
    	$dest_y = $size[1] - $corner_height;  
    	$rotated = imagerotate($corner_resized, 180, 0);
    	imagecolortransparent($rotated, $black); 
    	imagecopymerge($image, $rotated, $dest_x, $dest_y, 0, 0, $corner_width, $corner_height, 100);  
	}

	// Top-right corner
	if ($topright == true) {
    	$dest_x = $size[0] - $corner_width;  
    	$dest_y = 0;  
    	$rotated = imagerotate($corner_resized, 270, 0);
    	imagecolortransparent($rotated, $black); 
    	imagecopymerge($image, $rotated, $dest_x, $dest_y, 0, 0, $corner_width, $corner_height, 100);  
	}
	}

	// Rotate image
	$image = imagerotate($image, $rotate, $white);
	

	// Output final image
	
	if (preg_match("/png$/i",$image_file)) {
		imagepng($image,$image_file);
	} else if (preg_match("/gif$/i",$image_file)) {
		imagegif($image,$image_file);
	} else {
		imagejpeg($image,$image_file);
	}
	

	imagedestroy($image);  
	imagedestroy($corner_source);
	

}
}

function _mirrorImage ( $imgsrc)
{
    $width = imagesx ( $imgsrc );
    $height = imagesy ( $imgsrc );

    $src_x = $width -1;
    $src_y = 0;
    $src_width = -$width;
    $src_height = $height;

    $imgdest = imagecreatetruecolor ( $width, $height );

    if ( imagecopyresampled ( $imgdest, $imgsrc, 0, 0, $src_x, $src_y, $width, $height, $src_width, $src_height ) )
    {
        return $imgdest;
    }

    return $imgsrc;
}

function adjustPicOrientation($full_filename){  
	$exif = false;
	if(function_exists("exif_read_data") && preg_match("/\.(jpg|jpeg)$/i",$full_filename)) {    
	try {  
    	$exif = @exif_read_data($full_filename);
	}
	catch (Exception $e) {
		$exif = false;
	}
	}
    if($exif && isset($exif['Orientation'])) {
        $orientation = $exif['Orientation'];
        if($orientation != 1){
            $img = imagecreatefromjpeg($full_filename);

            $mirror = false;
            $deg    = 0;

            switch ($orientation) {
              case 2:
                $mirror = true;
                break;
              case 3:
                $deg = 180;
                break;
              case 4:
                $deg = 180;
                $mirror = true;  
                break;
              case 5:
                $deg = 270;
                $mirror = true; 
                break;
              case 6:
                $deg = 270;
                break;
              case 7:
                $deg = 90;
                $mirror = true; 
                break;
              case 8:
                $deg = 90;
                break;
            }
            if ($deg) $img = imagerotate($img, $deg, 0); 
            if ($mirror) $img = _mirrorImage($img);
            $full_filename = str_replace('.jpg', "-O$orientation.jpg",  $full_filename); 
            imagejpeg($img, $full_filename, 95);
        }
    }
    return $full_filename;
}


?>