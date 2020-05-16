<?php





    // Send a 1x1 pixel transparent GIF
    header("Content-type: image/gif");

    echo "\x47\x49\x46\x38\x39\x61\x04\x00\x04\x00\x80\x00\x00\xff\xff\xff\x00\x00\x00\x21\xf9\x04\x01\x00\x00\x00\x00\x2c\x00\x00\x00\x00\x04\x00\x04\x00\x00\x02\x04\x84\x8f\x09\x05\x00\x3b";
	
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




if(isset($_GET['clicktrackemailID']) && intval($_GET['clicktrackemailID'])>0) {// any tracking?



	require_once("../../Connections/aquiescedb.php");

	$groupemailID = intval($_GET['clicktrackemailID']);



	mysql_select_db($database_aquiescedb, $aquiescedb);
	$update = "UPDATE groupemail SET readcount = readcount + 1 WHERE ID = ".$groupemailID;
	mysql_query($update, $aquiescedb);
	$select = "SELECT ID FROM users WHERE email = ".GetSQLValueString($_GET['clicktrackuseremail'],"text");
	$query= mysql_query($select, $aquiescedb) or die(mysql_error());
	$row = mysql_fetch_assoc($query);
	$userID = isset($row['ID']) ? $row['ID'] : "NULL";
	$insert = "INSERT INTO groupemailclick (userID, groupemailID, createddatetime) VALUES (".$userID.",".$groupemailID.",NOW())";
	mysql_query($insert, $aquiescedb);

}
?>