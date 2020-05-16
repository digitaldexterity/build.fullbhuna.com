<?php require_once('../../Connections/aquiescedb.php'); ?><?php require_once('../includes/upload.inc.php'); ?>
<?php if(isset($_SESSION['MM_UserGroup'])) {
	$uploaded = getUploads();
	if (isset($uploaded) && is_array($uploaded)) {
		if(isset($uploaded["filename"][0]["newname"]) && $uploaded["filename"][0]["newname"]!="") { 
			$_POST['imageURL'] = $uploaded["filename"][0]["newname"]; 
		}
		
		echo "Done";
	} else {
		echo "Error:";
	}
}
	exit;
 ?>