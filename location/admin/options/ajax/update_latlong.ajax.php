<?php require_once('../../../../Connections/aquiescedb.php'); ?>
<?php require_once('../../../../core/includes/adminAccess.inc.php'); ?>
<?php 

$table = (isset($_GET['table']) && $_GET['table']=='directory') ? 'directory' : 'location';

$update = "UPDATE ".$table." SET latitude = ".floatval($_GET['latitude']).", longitude = ".floatval($_GET['longitude'])." WHERE ID = ".intval($_GET['ID']);

echo $update;

mysql_select_db($database_aquiescedb, $aquiescedb);
mysql_query($update, $aquiescedb) or die(mysql_error());


?>