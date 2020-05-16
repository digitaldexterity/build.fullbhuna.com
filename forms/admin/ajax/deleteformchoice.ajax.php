<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php 

if (!isset($_SESSION)) {
  session_start();
}

//if($_SESSION['MM_UserGroup']<7) die();

mysql_select_db($database_aquiescedb, $aquiescedb);
$delete = "DELETE formfieldchoice, formfieldresponse FROM  formfieldchoice LEFT JOIN formfieldresponse ON (formfieldresponse.formfieldchoiceID = formfieldchoice.ID) WHERE formfieldchoice.ID = ".intval($_GET['formchoiceID']);

mysql_query($delete, $aquiescedb) or die(mysql_error());

echo $delete." = ".mysql_affected_rows();

?>