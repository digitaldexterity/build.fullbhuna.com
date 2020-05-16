<?php require_once('../../../Connections/aquiescedb.php'); ?><?php require_once('../includes/tags.inc.php'); ?>
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

mysql_select_db($database_aquiescedb, $aquiescedb);


$tagID = isset($_GET['tagID']) &&  intval($_GET['tagID'])>0 ? intval($_GET['tagID']) : 0;

if((isset($_GET['blogentryID']) && intval($_GET['blogentryID'])>0) || (isset($_GET['eventgroupID']) && intval($_GET['eventgroupID'])>0)
 || (isset($_GET['newsID']) && intval($_GET['newsID'])>0)) {
	if(isset($_GET['tagname']) && trim($_GET['tagname']) !="") {
		$insert = "INSERT INTO tag (tagname, createdbyID, createddatetime) VALUES (".GetSQLValueString($_GET['tagname'], "text").",".GetSQLValueString($_GET['createdbyID'], "int").",NOW())";
		mysql_query($insert, $aquiescedb) or die(mysql_error().":".$insert);
		$tagID =  mysql_insert_id();
	}	
	$blogentryID = isset($_GET['blogentryID']) ? $_GET['blogentryID'] : 0;
	$eventgroupID = isset($_GET['eventgroupID']) ? $_GET['eventgroupID'] : 0;
	$newsID = isset($_GET['newsID']) ? $_GET['newsID'] : 0;
	return addTag($tagID,$blogentryID,$eventgroupID,$newsID, $_GET['createdbyID']);
}



?>


