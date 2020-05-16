<?php require_once('../../../../Connections/aquiescedb.php'); ?><?php require_once('../../../includes/sendmail.inc.php'); ?>
<?php
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

$colname_rsTemplate = "-1";
if (isset($_REQUEST['templateID'])) {
  $colname_rsTemplate = $_REQUEST['templateID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsTemplate = sprintf("SELECT templatemessage, templatesubject, templateHTML, templatehead FROM groupemailtemplate WHERE ID = %s", GetSQLValueString($colname_rsTemplate, "int"));
$rsTemplate = mysql_query($query_rsTemplate, $aquiescedb) or die(mysql_error());
$row_rsTemplate = mysql_fetch_assoc($rsTemplate);
$totalRows_rsTemplate = mysql_num_rows($rsTemplate);

$colname_rsRecipient = "-1";
if (isset($_REQUEST['userID'])) {
  $colname_rsRecipient = $_REQUEST['userID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsRecipient = sprintf("SELECT * FROM users WHERE ID = %s", GetSQLValueString($colname_rsRecipient, "int"));
$rsRecipient = mysql_query($query_rsRecipient, $aquiescedb) or die(mysql_error());
$row_rsRecipient = mysql_fetch_assoc($rsRecipient);
$totalRows_rsRecipient = mysql_num_rows($rsRecipient);

if(isset($_POST['merge'])) {
	// get encoded query values for merge, if any
	 // decode into array
	parse_str(base64_decode($_POST['merge']), $post);
	foreach($post as $key=>$value) {
		$post[$key] = stripslashes($value);
	}
	
	//print_r($post);

	$_REQUEST = array_merge($_REQUEST, $post); // combine with existing request
	
}
$htmlmessage =  $row_rsTemplate['templateHTML'];
if($totalRows_rsRecipient>0) {
	$htmlmessage =  mailMerge($htmlmessage,$rsRecipient);
}

echo  $row_rsTemplate['templatesubject']."<!--template break-->".$htmlmessage;


mysql_free_result($rsTemplate);

mysql_free_result($rsRecipient);
?>
