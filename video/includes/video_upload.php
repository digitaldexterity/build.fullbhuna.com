<?php 
set_time_limit(600);
ini_set("session.gc_maxlifetime","10800"); // prevent timeout before upload
$video_width = isset($_POST['videowidth']) ? $_POST['videowidth'] : VIDEO_DEFAULT_WIDTH;
$video_height = isset($_POST['videoheight']) ? $_POST['videoheight'] : VIDEO_DEFAULT_HEIGHT;
$video_bitrate = isset($_POST['videobitrate']) ? $_POST['videobitrate'] : VIDEO_DEFAULT_BITRATE;
$audiobitrate = isset($_POST['audiobitrate']) ? $_POST['audiobitrate'] : AUDIO_DEFAULT_BITRATE;
$allowed_formats = array("video/x-flv","application/vnd.ms-asf","video/x-ms-asf","video/x-ms-asx","video/x-msvideo","video/x-ms-wmv","video/x-m4v","video/quicktime","video/mp4","video/mpeg","video/avi","video/3gpp","video/3gpp2","video/vnd.vivo");

function convert_media($filename, $width, $height, $videobitrate, $audiobitrate, $samplingrate)
{
  settype($width,"int"); settype($height,"int"); // cleansing user input as using in exec command
  $size=($width > 0 && $height >0) ? " -s ".$width."x".$height : ""; // 0 leave as default
  $outfile = substr($filename, 0, strrpos($filename, ".")).".flv";
  $ffmpegcmd1 = FFMPEG_PATH." -i ".$filename. " -acodec ".FFMPEG_AUDIO_ENCODER." -ar " .$samplingrate." -ab ".$audiobitrate." -b ".$videobitrate." -f flv".$size." ".$outfile;
  //die($ffmpegcmd1);
  $ret = shell_exec($ffmpegcmd1); 
  return $outfile;
}

function grab_image($filename, $no_of_thumbs, $frame_number, $image_format, $width, $height)
{
	$size = $width."x".$height;
	$outfile = substr($filename, 0, strrpos($filename, ".")).".png"; // add .png
	$outfile = str_replace(UPLOAD_ROOT, UPLOAD_ROOT."t_",$outfile); // add thumbnail marker
	$ffmpegcmd1 = FFMPEG_PATH." -i ".$filename." -vframes ".$no_of_thumbs." -ss 00:00:03 -an -vcodec ". $image_format." -f rawvideo -s ".$size. " ".$outfile;
	//die($ffmpegcmd1);
	$ret = shell_exec($ffmpegcmd1); 
	return $outfile;
}

function set_buffering($filename)
{
	$ffmpegcmd1 = "flvtool2 -U ".$filename;
	$ret = shell_exec($ffmpegcmd1);
}

if (!isset($error) && isset($_FILES['videofile']['name']) && $_FILES['videofile']['name'] !="") { // file posted and no previous error - i.e. from image thumbnail generation
	$mimetype = $_FILES['videofile']['type'];
	if(in_array($mimetype,$allowed_formats)) { // is supported video file
		$source = $_FILES['videofile']['tmp_name'];
		$video_filename = $_FILES['videofile']['name'];
		$video_filename = time()."_".str_replace(" ", "_" , $video_filename); //replace spaces and prefix timestanp
		$_POST['originalFile'] = $video_filename; // put original filename into database post before any conversion below
		$video_filename = UPLOAD_ROOT.$video_filename;	
		move_uploaded_file($source, $video_filename); chmod($video_filename,0666);	
		if (defined("VIDEO_TO_FLV") && VIDEO_TO_FLV == true) { // auto convert
			if(defined("FFMPEG_PATH") && is_readable(FFMPEG_PATH)) { // does FFMPEG exist on server a Yes..
				if($_FILES['videofile']['type'] != "video/x-flv") { // not FLV so use FFMPEG
					$video_filename = convert_media($video_filename, $video_width, $video_height, $video_bitrate, $audiobitrate, 22050); chmod($video_filename,0666);
					$mimetype = "video/x-flv";
				} else { // no FFMPEG on server
					$error = "Server software called FFMPEG is required to convert video to FLV format. Please convert your video file to FLV format before you upload or ask your server administrator to install FFMPEG on the web server."; }
				} 
			} // end auto convert to FLV
		} // end is supported video
	else { 
		$error = "The file (".$mimetype.") is not one of the supported video formats. Supported versions of MPEG, AVI, QuickTime, FLV, 3GP or WMV can be used.";
	} // end is not supported video
} // end file posted


// create thumbnail
if(isset($_POST['videotitle']) && !isset($error) && defined("FFMPEG_PATH") &&  is_readable(FFMPEG_PATH) && isset($_POST['autothumbnail'])) {  
	$thumb_size = isset($image_sizes['thumb']['width']) ? $image_sizes['thumb']['width'] : 100;
	$image_name = grab_image($video_filename, 1, 2, "png", $thumb_size, 90); chmod($image_name,0666);
	$_POST['imageURL'] = str_replace(UPLOAD_ROOT."t_","",$image_name);
}

if (isset($error)) { // there was an error during upload
	unset($_POST["MM_insert"],$_POST["MM_update"],$_POST['imageURL']); 
} else {
	$_POST['videoURL'] = str_replace(UPLOAD_ROOT,"",$video_filename);
	$_POST['mimetype'] = $mimetype;
}
//Set_Buffering($outfile);
?>

 