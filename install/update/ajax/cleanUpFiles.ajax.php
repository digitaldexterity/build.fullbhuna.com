<?php 
require_once('../../../Connections/aquiescedb.php');
?><?php require_once('../../includes/mysqli_connection.inc.php'); ?><?php require_once('../../includes/install.inc.php'); ?>
<?php


$site_root =  site_root();	

	if(!createDirectories($site_root)) {
		
		echo "COULDN'T UPDATE FILE SYSTEM<br>";
	}
	
cleanUpFiles();	

echo "Done.<br>";



?>