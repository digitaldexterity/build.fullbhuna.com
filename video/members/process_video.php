<?php require_once('../../Connections/aquiescedb.php'); 

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

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);
?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php  $pageTitle = "Process Video"; echo $pageTitle." | ".$site_name;?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->


<link href="/local/css/styles.css" rel="stylesheet"  />
<script src="/core/scripts/common.js"></script>
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" -->

    <?php 
	
	$hot_directory = SITE_ROOT."Uploads".DIRECTORY_SEPARATOR."_ftpupload".DIRECTORY_SEPARATOR."users".DIRECTORY_SEPARATOR.$_SESSION['MM_Username'].DIRECTORY_SEPARATOR; 
	$uploaddir = SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR;
	if(is_dir($hot_directory)) { // directory exists
	$dh = opendir($hot_directory);
	$count = 0;
	while($filename = readdir($dh))
	{
		$processed = false;
		if(is_file($hot_directory.$filename)) { // is file
			if(preg_match("/(.flv|.mov|.mp4|.qt|.mpeg|.mpg|.mpe|.wmv|.avi)/i",$filename)) { // is supported video type
				if(strpos($filename,".flv")) { // is FLV
					$mimetype = "video/x-flv";
					} else if(preg_match("/(.mov|.mp4|.qt)/i",$filename)) {// is QT
						$mimetype = "video/quicktime";
					} else if(preg_match("/(.mpeg|.mpg|.mpe)/i",$filename)) {// is MPEG
						$mimetype = "video/mpeg";
					} else if(preg_match("/(.wmv|.avi)/i",$filename)) {// is Windows Media
						$mimetype = "video/x-msvideo";
					}
				$pathinfo = pathinfo($filename);
				$filetype = $pathinfo['extension'];
				$title = explode(".",$pathinfo['basename']);
				$title[0] = str_replace("_"," ",$title[0]);
				$newfilename = time()."_".str_replace(" ", "_" , $filename);
				copy($hot_directory.$filename,$uploaddir.$newfilename);
				if(unlink($hot_directory.$filename)) { // try to delete with usual method
				$processed = true;
				} else {  // try to delete with FTP as sometimes this is the only way to delete FTP uploaded files
				$conn_id = ftp_connect("localhost");
				// login with username and password
				$login_result = ftp_login($conn_id, FTP_USERNAME, FTP_PASSWORD);
				$ftp_dir = FTP_PATH."users/".$_SESSION['MM_Username']."/"; 
				// try to delete 
				if (ftp_delete($conn_id, $ftp_dir.$filename)) {
					$processed = true;
				} // end deleted FTP			
				}	// end try FTP	
				if($processed) { // file OK
				$insert = "INSERT INTO video (videotitle, videoURL, mimetype, createdbyID, createddatetime, categoryID, statusID) VALUES (".GetSQLValueString($title[0],"text").",".GetSQLValueString($newfilename,"text").",'".$mimetype."',".$row_rsLoggedIn['ID'].",NOW(),".GetSQLValueString($_GET['videoCategoryID'],"int").",1)";
				mysql_select_db($database_aquiescedb, $aquiescedb);
  				$result = mysql_query($insert, $aquiescedb) or die(mysql_error());
				echo "Processed: ".$filename." (FTP)<br />";
				$count++;
				} else { // failed
				echo "FAILED: ".$filename." (FTP)<br />";
				} // end failed
				
			} // end is video
		
		} // end is file
	}// directory loop
	echo "";
	} else { die("ERROR: The directory '".$hot_directory."' does not exist."); }
	?>
    <?php if($count>0) { ?>
    <p>Processing complete. <?php echo $count; ?> files processed.</p>
    <p>These files have been added to the video gallery. Note that these files will not have descriptions or thumbnails but these can be added manually later.</p>
<p><a href="/video/index.php?categoryID=<?php echo intval($_GET['videoCategoryID']); ?>" target="_top">Go to video gallery...</a></p>
<?php } else { // none processed ?>
<p>No video files found. <a href="../../ftp/index.php">Try again</a>.</p>
<?php } ?>
<!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);
?>
