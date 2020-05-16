<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php 

if (!isset($_SESSION)) {
  session_start();
}

//if($_SESSION['MM_UserGroup']<7) die();

mysql_select_db($database_aquiescedb, $aquiescedb);
$delete = "DELETE formfield, formfieldchoice, formfieldresponse FROM formfield LEFT JOIN  formfieldchoice ON (formfieldchoice.formfieldID = formfield.ID) LEFT JOIN formfieldresponse ON (formfieldresponse.formfieldID = formfield.ID) LEFT JOIN formfieldresponse AS formfieldresponsechoice ON (formfieldresponsechoice.formfieldchoiceID = formfieldchoice.ID) WHERE formfield.ID = ".intval($_GET['formitemID']);

mysql_query($delete, $aquiescedb) or die(mysql_error());

echo $delete." = ".mysql_affected_rows();

?>