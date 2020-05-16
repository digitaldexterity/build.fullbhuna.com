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

if(!isset($_SESSION['MM_Username']) || $chatstatus==0) die();



// add me if not in online list otherwise upldate time
$select = "SELECT ID, chatstatus FROM users WHERE users.username = ".GetSQLValueString($_SESSION['MM_Username'], "text")." LIMIT 1";
$result = mysql_query($select, $aquiescedb) or die(mysql_error());
if(mysql_num_rows($result)>0) { // is user logged in 
	$row = mysql_fetch_assoc($result);
	$userID = $row['ID'];
	$status = $row['chatstatus'];
	$select = "SELECT createdbyID FROM chatusersonline WHERE chatusersonline.createdbyID = ".$userID;
	$result = mysql_query($select, $aquiescedb) or die(mysql_error());
	if(mysql_num_rows($result)==0 && $status>0) { // not in list and status online
		$insert = "INSERT INTO chatusersonline (statusID, createdbyID, createddatetime, modifiedbyID, modifieddatetime) VALUES (".$status.",".$userID.",NOW(),".$userID.",NOW())";
		$result = mysql_query($insert, $aquiescedb) or die(mysql_error());
	} else {
		$update = "UPDATE chatusersonline SET statusID = ".$status.", modifieddatetime = NOW() WHERE createdbyID = ".$userID;
		$result = mysql_query($update, $aquiescedb) or die(mysql_error());
	}
}

// remove expired
$delete = "DELETE FROM chatusersonline WHERE DATE_ADD(modifieddatetime, INTERVAL 30 second) < NOW()";
$result = mysql_query($delete, $aquiescedb) or die(mysql_error());

// get list

echo "Users online: ";

$select = "SELECT chatusersonline.statusID, users.ID, users.firstname, users.surname FROM chatusersonline LEFT JOIN users ON (chatusersonline.createdbyID = users.ID) WHERE chatusersonline.statusID>0 AND users.usertypeID >= ".$usertypeID." AND users.ID != ".$userID." ORDER BY users.surname";
$result = mysql_query($select, $aquiescedb) or die(mysql_error());
if(mysql_num_rows($result)>0) {
	echo "<select id=\"userlist\">\n";
	while($row = mysql_fetch_assoc($result)) {
		echo "<option value = \"".$row['ID']."\">".$row['firstname']." ".$row['surname'];
		echo ($row['statusID'] == 2) ? " (back soon)" : "";
		echo "</option>\n";
	}
	echo "</select>\n";
	echo "<input type=\"button\" name=\"startbutton\" id=\"startbutton\" value=\"Start chat...\" onclick=\"startChat(document.getElementById('userlist').value);\" />";
} else {
	echo "There is currently no one available to chat online. <a href=\"index.php?requestchat=true\" onclick=\"return confirm('You can request a chat with a member of web site administration team. If there is a member available, they will come online within a few minutes but we regret that this may not always be the case.');\">Request chat</a>.";
}


?>