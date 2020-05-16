<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php if(!isset($_SESSION['MM_UserGroup'])) die();
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




if(isset($_GET['taggedID']) &&  intval($_GET['taggedID'])>0) {
	$delete = "DELETE FROM tagged WHERE ID = ".GetSQLValueString($_GET['taggedID'], "int");
	mysql_query($delete, $aquiescedb) or die(mysql_error().":".$insert);
	return true;
	
}

$eventgroupID =  isset($_GET['eventgroupID']) ? $_GET['eventgroupID'] : 0;
$newsID =  isset($_GET['newsID']) ? $_GET['newsID'] : 0;

if(isset($_GET['tagID']) &&  intval($_GET['tagID'])>0 ) {
	$delete = "DELETE FROM tagged WHERE tagID = ".GetSQLValueString($_GET['tagID'], "int")." AND eventgroupID = ".GetSQLValueString($eventgroupID, "int")." AND newsID = ".GetSQLValueString($newsID, "int");
	mysql_query($delete, $aquiescedb) or die(mysql_error().":".$insert);
	//echo $delete;
	return true;
}

?>