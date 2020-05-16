<?php require_once('../../../../Connections/aquiescedb.php'); ?>
<?php if(isset($_SESSION['MM_UserGroup']) && $_SESSION['MM_UserGroup']>=7) { ?>
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

$varURL_rsAlreadyFavourite = "-1";
if (isset($_GET['url'])) {
  $varURL_rsAlreadyFavourite = $_GET['url'];
}
$varUsername_rsAlreadyFavourite = "-1";
if (isset($_SESSION['MM_Username'])) {
  $varUsername_rsAlreadyFavourite = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsAlreadyFavourite = sprintf("SELECT favourites.url FROM favourites LEFT JOIN users ON (favourites.userID = users.ID) WHERE (favourites.userID = 0 OR users.username = %s) AND favourites.url = %s", GetSQLValueString($varUsername_rsAlreadyFavourite, "text"),GetSQLValueString($varURL_rsAlreadyFavourite, "text"));
$rsAlreadyFavourite = mysql_query($query_rsAlreadyFavourite, $aquiescedb) or die(mysql_error());
$row_rsAlreadyFavourite = mysql_fetch_assoc($rsAlreadyFavourite);
$totalRows_rsAlreadyFavourite = mysql_num_rows($rsAlreadyFavourite);

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);
 

	if($totalRows_rsAlreadyFavourite == 0) {
		$insert = "INSERT INTO favourites (url,pagetitle,userID,createdbyID,createddatetime) VALUES (".GetSQLValueString($_GET['url'], "text").",".GetSQLValueString($_GET['pagetitle'], "text").",".GetSQLValueString($row_rsLoggedIn['ID'], "int").",".GetSQLValueString($row_rsLoggedIn['ID'], "int").",NOW())";
		$result = mysql_query($insert, $aquiescedb) or die(mysql_error());
		echo " (Added to favourites)";
	} else {
		echo " (Already a favourite)";
	}
	
mysql_free_result($rsAlreadyFavourite);

mysql_free_result($rsLoggedIn);

} // OK to access 
?>
