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
		
		$select = "SELECT chatuser.ID, chatuser.chatID, chatuser.statusID, creator.firstname, creator.surname 
		FROM chatuser 
		INNER JOIN users AS recipient ON (chatuser.userID = recipient.ID) 
		INNER JOIN users AS creator ON (chatuser.createdbyID = creator.ID) 
		LEFT JOIN chat ON (chat.ID = chatuser.chatID) 
		WHERE chat.statusID = 1
		AND chatuser.statusID = 0
		AND recipient.username = ".GetSQLValueString($_SESSION['MM_Username'], "text")." 
		AND creator.username != ".GetSQLValueString($_SESSION['MM_Username'],"text"); 
		$result = mysql_query($select, $aquiescedb) or die(mysql_error());
		if(mysql_num_rows($result)>0) { 
			while($row = mysql_fetch_assoc($result)) {
				echo "<div><img src=\"/images/warning_blink.gif\" width=\"16\" height=\"16\" alt=\"Alert\" style=\"vertical-align:middle;\" />&nbsp;<a href=\"javascript:void(0);\" onclick=\"openChat(".$row['chatID']."); return false;\">".$row['firstname']." ".$row['surname']."</a></div>\n";
			}
		}
		
		
?> 