<?php require_once('../Connections/aquiescedb.php');
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
$url = $_GET['clicktrackurl'];
$url .= (strpos($url, '?')) ? "&" : "?";

if(strpos($url,"gdpr.php")!==false) {
	$url .= "email=".$_GET['clicktrackuseremail']; // required for GDPR
} else if(isset($_GET['token'])) {
	// pass token on
	$url .= "email=".$_GET['clicktrackuseremail']; 
	$url .= "&token=".$_GET['token'];
}
$url = str_replace("&amp;","&",$url);
//$url = str_replace("http://".$_SERVER['HTTP_HOST'],"",$url); // why?

$groupemailID = intval($_GET['clicktrackemailID']);

// any tracking?
if(intval($groupemailID)>0) {
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$update = "UPDATE groupemail SET clickcount = clickcount + 1 WHERE ID = ".$groupemailID;
	mysql_query($update, $aquiescedb);
	$select = "SELECT users.ID, users.firstname, users.surname, users.email, directory.name AS company FROM users  LEFT JOIN directory ON (directory.userID = users.ID) WHERE users.email = ".GetSQLValueString($_GET['clicktrackuseremail'],"text")." LIMIT 1";
	$query= mysql_query($select, $aquiescedb) or die(mysql_error());
	$user = mysql_fetch_assoc($query);
	$insert = "INSERT INTO groupemailclick (userID, groupemailID, url, createddatetime) VALUES (".GetSQLValueString($user['ID'],"int").",".intval($groupemailID).",".GetSQLValueString($url,"text").",NOW())";
	mysql_query($insert, $aquiescedb);
	
	// append info to URL for CANDDI tracking (Novograf)?
	if(defined("CANDDI")) {
		$url .= (strpos($url, '?')) ? "&" : "?";
		$url .= "cc=".urlencode($user['company'])."&ce=".urlencode($user['email'])."&cfn=".urlencode($user['firstname'])."&cln=".urlencode($user['surname']);
	}
	

}


header("location: ".$url); exit;

?>