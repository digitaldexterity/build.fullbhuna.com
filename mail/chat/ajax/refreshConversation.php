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
if(!isset($_SESSION['MM_Username'])) die();

if(isset($_GET['chatID']) && intval($_GET['chatID'])>0) { // is a chat
	if(isset($_GET['text']) && strlen($_GET['text'])>0) { // insert text
		$select = "SELECT ID FROM users WHERE users.username = ".GetSQLValueString($_SESSION['MM_Username'], "text"); 
		$result = mysql_query($select, $aquiescedb) or die(mysql_error());
		$row = mysql_fetch_assoc($result);
		$insert = "INSERT INTO chatitem (chatID, chattext, createdbyID, createddatetime) VALUES (".intval($_GET['chatID']).",".GetSQLValueString($_GET['text'], "text").",".$row['ID'].",NOW())";
		mysql_query($insert, $aquiescedb) or die(mysql_error());
	} // end insert text
	
	
	$select = "SELECT chattext, firstname, surname FROM chatitem LEFT JOIN users ON (chatitem.createdbyID = users.ID) WHERE chatID = ".intval($_GET['chatID'])."  ORDER BY createddatetime DESC LIMIT 20";
	$result = mysql_query($select, $aquiescedb) or die(mysql_error());
	if(mysql_num_rows($result)>0) {
		while($row = mysql_fetch_assoc($result)) { 
			echo"<div class=\"chatitem\"><em>".$row['firstname']." ".$row['surname'].":</em><br />".htmlentities($row['chattext'])."</div";
		}
	}
} // end is a chat
?>