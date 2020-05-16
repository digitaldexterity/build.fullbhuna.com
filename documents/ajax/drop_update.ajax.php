<?php require_once('../../Connections/aquiescedb.php'); ?><?php require_once('../../members/includes/userfunctions.inc.php'); ?>
<?php 
mysql_select_db($database_aquiescedb, $aquiescedb);
// at present the system only checks target access, no checks are made to modifcation of source, so this needs to be added
$select = "SELECT writeaccess, groupwriteID FROM documentcategory WHERE ID = ".intval($_GET['documentcategoryID']);
$result = mysql_query($select, $aquiescedb) or die(mysql_error());
$access = mysql_fetch_assoc($result);

$select = "SELECT ID FROM users WHERE username = ".GetSQLValueString($_SESSION['MM_Username'],"text")." LIMIT 1";
$result = mysql_query($select, $aquiescedb) or die(mysql_error());
$user = mysql_fetch_assoc($result);

if(thisUserHasAccess($access['writeaccess'], $access['groupwriteID'], $user['ID'])) {
	if($_GET['table']=="documents") {
		$update = "UPDATE `documents` SET `documentcategoryID` = ".intval($_GET['documentcategoryID'])." WHERE ID = ".intval($_GET['id']);
	} else if($_GET['table']=="documentcategory") {
		$update = "UPDATE `documentcategory` SET `subcatofID` = ".intval($_GET['documentcategoryID'])." WHERE ID = ".intval($_GET['id']);
	} else if($_GET['table']=="flipbook") {
		$update = "UPDATE `flipbook` SET `categoryID` = ".intval($_GET['documentcategoryID'])." WHERE ID = ".intval($_GET['id']);
	}
	else if($_GET['table']=="documentshortcut") {
		$update = "UPDATE `documentshortcut` SET `categoryID` = ".intval($_GET['documentcategoryID'])." WHERE ID = ".intval($_GET['id']);
	}
	mysql_query($update, $aquiescedb) or die(mysql_error());
	echo "OK";
} else {
	echo "Sorry, you do not have access to put items in this folder. (".$access['writeaccess'].",".$access['groupwriteID'].")";
}


?>