<?php require_once('../Connections/aquiescedb.php'); ?>
<?php // force download doc at specified URL

// NOTE for security will only download docs within Uploads unless key is present


if(isset($_GET['filename'])) { 
	if(strpos($_GET['filename'], "/Uploads/")!==false || $_GET['key'] == md5(PRIVATE_KEY.$_GET['filename'])) {
		// remove extra slash after SITE_ROOT ...
		$filePath = str_replace("//","/",SITE_ROOT.urldecode($_GET['filename']));
		if(is_readable($filePath)) { // can access			 
	// Fetch the file info.   				
			header("Cache-Control: ");// leave blank to avoid IE errors
 			header("Pragma: ");// leave blank to avoid IE errors
			header("Content-Type: application/stream");
			header("Content-Length: ".filesize($filePath));
			header("Content-Disposition: attachment; filename=".basename($filePath));				  
			readfile ($filePath);     die(); 
		} else {
			die("Could not read file:" .htmlentities($filePath,ENT_COMPAT,"UTF-8"));
		}				  
	} else { // not valid
		header('HTTP/1.0 403 Forbidden');
		die("Forbidden");
	}
} // end is filename

?>
