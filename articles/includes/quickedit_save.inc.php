<?php /** QUICK EDIT - MUST HAVE logged in record **/
require_once(SITE_ROOT.'core/includes/framework.inc.php');
if(isset($_POST['updatearticleID']) && intval($_POST['updatearticleID'])>0) {
	
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

	
	if(!isset($row_rsLoggedIn['usertypeID'])) {
		$colname_rsLoggedIn = "-1";
		if (isset($_SESSION['MM_Username'])) {
  			$colname_rsLoggedIn = $_SESSION['MM_Username'];
		}
		mysql_select_db($database_aquiescedb, $aquiescedb);
		$query_rsLoggedIn = sprintf("SELECT ID, usertypeID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
		$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
		$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
		$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);
	}
	if($row_rsLoggedIn['usertypeID']>=8 && $_POST['token'] == md5($_POST['updatearticleID'].PRIVATE_KEY)) { // update article
		// make backup first
		$versionID = duplicateMySQLRecord ("article", $_POST['updatearticleID']);
		$update = "UPDATE article SET versionofID = ".intval($_POST['updatearticleID'])." WHERE ID = ".$versionID;
		mysql_query($update, $aquiescedb) or die(mysql_error().": ".$update);
 		$update = "UPDATE article SET body = ".GetSQLValueString($_POST['quickedithtml'], "text").", modifiedbyID = ".$row_rsLoggedIn['ID'].", modifieddatetime = NOW() WHERE ID = ".intval($_POST['updatearticleID']);
		mysql_query($update, $aquiescedb) or die(mysql_error().": ".$update);
		$msg = "Your changes have been saved.";
	} else {
		$error = "Sorry the edit was not saved due to a security issue.";
	}
}

?>
