<?php require_once('../../../Connections/aquiescedb.php'); ?><?php
?>
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


$varRegionID_rsImages = 1;
if (isset($_SESSION['regionID'])) {
  $varRegionID_rsImages = $_SESSION['regionID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsImages = "SELECT uploads.ID, uploads.newfilename, uploads.filename FROM uploads WHERE uploads.newfilename LIKE '%jpg' OR uploads.newfilename LIKE '%png' OR uploads.newfilename LIKE '%gif' OR uploads.newfilename LIKE '%jpeg'";
$rsImages = mysql_query($query_rsImages, $aquiescedb) or die(mysql_error());
$row_rsImages = mysql_fetch_assoc($rsImages);
$totalRows_rsImages = mysql_num_rows($rsImages);
 
// This list may be created by a server logic page PHP/ASP/ASPX/JSP in some backend system.
// There flash movies will be displayed as a dropdown in all media dialog if the "media_external_list_url"
  // option is defined in TinyMCE init.

echo "[\n";

$first = true;


	
	do { 
	echo $first ? "" : ",\n"; $first = false;
	echo "{title:";
	echo  json_encode($row_rsImages['filename']);
	echo ", value: "; 
	echo   json_encode(str_replace(SITE_ROOT,"/",$row_rsImages['newfilename']));
	echo "}";
	} while ($row_rsImages = mysql_fetch_assoc($rsImages)); 
	
	
	

echo "\n]";


mysql_free_result($rsImages);
?>
