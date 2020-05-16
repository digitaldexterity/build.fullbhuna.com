<?php 
require_once('../../../Connections/aquiescedb.php');
?><?php require_once('../../includes/mysqli_connection.inc.php'); ?>
<?php require_once('../../includes/install.inc.php'); ?>
<?php

if(!isset($_SESSION['MM_UserGroup']) || $_SESSION['MM_UserGroup']!=10) die("Not authorised");
if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
	global $fb_mysqli_con;
  if (PHP_VERSION < 6) {
    $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
  }

  $theValue = function_exists("mysqli_real_escape_string") ? mysqli_real_escape_string($fb_mysqli_con, $theValue) : mysqli_escape_string($fb_mysqli_con, $theValue);

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

echo "Preparing tables...<br>";


/* remove zero vales so auto increment can be added */
$delete = "DELETE FROM disability WHERE ID = 0";
mysqli_query($fb_mysqli_con, $delete) or die(mysqli_error($fb_mysqli_con).": ".$delete);
	
	
// ADD auto increment ID COLUMN TO productorders TABLE IF DOESN'T EXIST
$sql = "LOCK TABLE productorders WRITE"; 
$result = mysqli_query($fb_mysqli_con, $sql) or die(mysqli_error($fb_mysqli_con).": ".$sql);


$sql = "SELECT * FROM information_schema.COLUMNS WHERE TABLE_NAME = 'productorders'  AND COLUMN_NAME = 'ID'"; 
$result = mysqli_query($fb_mysqli_con, $sql) or die(mysqli_error($fb_mysqli_con).": ".$sql);
		
if(mysqli_num_rows($result)==0) {
	// if there was no ID column;
	echo "NO ID on productorders<br> ";
	$sql = "ALTER TABLE productorders DROP PRIMARY KEY, ADD ID INT NOT NULL AUTO_INCREMENT PRIMARY KEY";
	mysqli_query($fb_mysqli_con, $sql) or die(mysqli_error($fb_mysqli_con).": ".$sql);
} else {	
	
	// if column ID was already there,  check auto increment  is set (when half done)
	$sql = "SELECT * FROM information_schema.COLUMNS WHERE TABLE_NAME = 'productorders'  AND COLUMN_NAME = 'ID' AND EXTRA like '%auto_increment%'"; 
	$result = mysqli_query($fb_mysqli_con, $sql) or die(mysqli_error($fb_mysqli_con).": ".$sql);	
	if(mysqli_num_rows($result)==0) {
		echo "NO ID auto inc on productorders<br> ";
		// add auto increment ot ID
		$sql = "ALTER TABLE productorders DROP COLUMN ID, DROP PRIMARY KEY, ADD ID INT NOT NULL AUTO_INCREMENT PRIMARY KEY";
		mysqli_query($fb_mysqli_con,$sql) or die(mysqli_error($fb_mysqli_con).": ".$sql);
	} else {
		//echo "TABLE productorders up to date.<br> ";
	}
}
$sql = "UNLOCK TABLES"; 
$result = mysqli_query($fb_mysqli_con, $sql) or die(mysqli_error($fb_mysqli_con).": ".$sql);


/*if ID does not exist DUPLICATE FROM country_id but keep both for compatibilty*/

$sql = "SELECT * FROM information_schema.COLUMNS WHERE TABLE_NAME = 'countries'  AND COLUMN_NAME = 'ID'"; 
$result = mysqli_query($fb_mysqli_con, $sql) or die(mysqli_error($fb_mysqli_con).": ".$sql);
if(mysqli_num_rows($result)==0) {
	$sql = "ALTER TABLE countries CHANGE country_id country_id INT(11) NOT NULL";
	mysqli_query($fb_mysqli_con, $sql) or die(mysqli_error($fb_mysqli_con).": ".$sql);
	
	$sql = "ALTER TABLE countries DROP PRIMARY KEY";
	mysqli_query($fb_mysqli_con,$sql) or die(mysqli_error($fb_mysqli_con).": ".$sql);
	$sql = "ALTER TABLE countries ADD ordernum int(11) NOT NULL default '0'"; 
	mysqli_query($fb_mysqli_con,$sql) or die(mysqli_error($fb_mysqli_con).": ".$sql);
	
	$sql = "UPDATE countries SET ordernum = -1*ordernum"; 
	mysqli_query($fb_mysqli_con,$sql) or die(mysqli_error($fb_mysqli_con).": ".$sql);
	
	$sql = "ALTER TABLE countries ADD ID int(11) NOT NULL PRIMARY KEY auto_increment"; 
	mysqli_query($fb_mysqli_con,$sql) or die(mysqli_error($fb_mysqli_con).": ".$sql);
	$sql = "UPDATE countries SET ID = country_id;"; 
	mysqli_query($fb_mysqli_con,$sql) or die(mysqli_error($fb_mysqli_con).": ".$sql);
	// converting tracking session from TEXT to INT
	
	echo "Updated countries table.<br>";
	//$sql = "ALTER TABLE track_page FOREIGN KEY (sessionID) REFERENCES track_session(ID) ON UPDATE CASCADE";
	
	
}
	
	
	// move first comment from topic table to comments table
	$result = mysqli_query($fb_mysqli_con,"SELECT * FROM forumtopic");
	if(mysqli_num_rows($result)>0) {
	while($row = mysqli_fetch_assoc($result)) {
		if(isset($row['postedbyID']) && $row['postedbyID']>0) {
			$insert = "INSERT INTO forumcomment (topicID, imageURL, emailme, postedbyID, posteddatetime, statusID, message, IPaddress, rating) VALUES (".$row['ID'].",".GetSQLValueString($row['imageURL'], "text").",".GetSQLValueString($row['mailme'], "int").",".GetSQLValueString($row['postedbyID'], "int").",".GetSQLValueString($row['posteddatetime'], "date").",".GetSQLValueString($row['statusID'], "int").",".GetSQLValueString($row['message'], "text").",".GetSQLValueString($row['IPaddress'], "text").",".GetSQLValueString($row['rating'], "int").")";
			mysqli_query($fb_mysqli_con,$insert) or die(mysqli_error($fb_mysqli_con).": ".$insert);
			$update = "UPDATE forumtopic SET statusID = 1, postedbyID = 0 WHERE ID = ".$row['ID'];
			mysqli_query($fb_mysqli_con,$update) or die(mysqli_error($fb_mysqli_con).": ".$update);
		}
	}
	}

echo "Done.<br>";
?>