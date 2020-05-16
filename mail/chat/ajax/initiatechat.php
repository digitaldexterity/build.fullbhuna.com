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

if(isset($_GET['userID']) && intval($_GET['userID'])>0 && isset($_GET['chatID']) && intval($_GET['chatID'])>0) {
	$select = "SELECT ID FROM users WHERE users.username = ".GetSQLValueString($_SESSION['MM_Username'], "text");
	$result = mysql_query($select, $aquiescedb) or die(mysql_error());
	$row = mysql_fetch_assoc($result);
	$loggedin= $row['ID'];
	$statusID = (isset($_GET['statusID']) && intval($_GET['statusID'] == 1)) ? 1 : 0; // set status from URL
	$select = "SELECT ID, statusID FROM chatuser WHERE chatID = ".intval($_GET['chatID'])." AND userID = ".intval($_GET['userID']);
	$result = mysql_query($select, $aquiescedb) or die(mysql_error());
	if(mysql_num_rows($result)==0) { // not in list
		$insert = "INSERT INTO chatuser (userID, chatID, statusID, createdbyID, createddatetime) VALUES (".intval($_GET['userID']).",".intval($_GET['chatID']).",".$statusID.",".$loggedin.",NOW())";
		$result = mysql_query($insert, $aquiescedb) or die(mysql_error());
	} else { // in list 
		$row = mysql_fetch_assoc($result);
		if($statusID == 1 && $row['statusID'] == 0) { // status needs updating to accepted
		$update = "UPDATE chatuser SET statusID = 1 WHERE ID = ".$row['ID'];
		$result = mysql_query($update, $aquiescedb) or die(mysql_error());
		}
	}
	
	// output users in conversaion
	$select = "SELECT users.firstname, users.surname FROM chatuser LEFT JOIN users ON (chatuser.userID = users.ID) WHERE chatID = ".intval($_GET['chatID']);
	$result = mysql_query($select, $aquiescedb) or die(mysql_error());
	if(mysql_num_rows($result)>0) {
		echo "Conversation: ";
		while($row=mysql_fetch_assoc($result)) {
			echo $row['firstname']." ".$row['surname']."; ";
		}
	}
	
}

?>