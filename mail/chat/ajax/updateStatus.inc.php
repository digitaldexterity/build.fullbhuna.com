<?php require_once('../../../Connections/aquiescedb.php'); ?>
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
$select = "SELECT statusID FROM chatprefs WHERE ID = 1";
$result = mysql_query($select, $aquiescedb) or die(mysql_error());
$row = mysql_fetch_assoc($result);
$chatstatus = isset($row['statusID']) ? $row['statusID'] : 0;
$usertypeID = ($chatstatus == 1) ? 8 : 0;

if(!isset($_SESSION['MM_Username'])) die();

if(isset($_GET['statusID']) && isset($_GET['userID']) && intval($_GET['userID'])>0 ) {
	
	$update = "UPDATE chatusersonline SET statusID = ".intval($_GET['statusID'])." WHERE createdbyID = ".intval($_GET['userID']);
	$result = mysql_query($update, $aquiescedb) or die(mysql_error());
	
	// also set users default value
	$update = "UPDATE users SET chatstatus = ".intval($_GET['statusID'])." WHERE ID = ".intval($_GET['userID']);
	$result = mysql_query($update, $aquiescedb) or die(mysql_error());
	

}

?>