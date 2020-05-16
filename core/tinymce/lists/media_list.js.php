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

$maxRows_rsMediaFiles = 50;
$pageNum_rsMediaFiles = 0;
if (isset($_GET['pageNum_rsMediaFiles'])) {
  $pageNum_rsMediaFiles = $_GET['pageNum_rsMediaFiles'];
}
$startRow_rsMediaFiles = $pageNum_rsMediaFiles * $maxRows_rsMediaFiles;

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsMediaFiles = "SELECT 'swf', documents.documentname AS title, documents.type, documents.filename, documents.uploaddatetime AS createddatetime FROM documents WHERE documents.active AND documents.type = 'application/x-shockwave-flash' UNION SELECT 'fla', video.videotitle AS title, 'video/x-flv' AS type, video.videoURL as filename, video.createddatetime FROM video ORDER BY createddatetime DESC";
$query_limit_rsMediaFiles = sprintf("%s LIMIT %d, %d", $query_rsMediaFiles, $startRow_rsMediaFiles, $maxRows_rsMediaFiles);
$rsMediaFiles = mysql_query($query_limit_rsMediaFiles, $aquiescedb) or die(mysql_error());
$row_rsMediaFiles = mysql_fetch_assoc($rsMediaFiles);

if (isset($_GET['totalRows_rsMediaFiles'])) {
  $totalRows_rsMediaFiles = $_GET['totalRows_rsMediaFiles'];
} else {
  $all_rsMediaFiles = mysql_query($query_rsMediaFiles);
  $totalRows_rsMediaFiles = mysql_num_rows($all_rsMediaFiles);
}
$totalPages_rsMediaFiles = ceil($totalRows_rsMediaFiles/$maxRows_rsMediaFiles)-1;
?>
<?php // This list may be created by a server logic page PHP/ASP/ASPX/JSP in some backend system.
// There flash movies will be displayed as a dropdown in all media dialog if the "media_external_list_url"
  // option is defined in TinyMCE init.

echo"var tinyMCEMediaList = [";
do { 
echo"[\"".$row_rsMediaFiles['title']."\", \"/Uploads/".$row_rsMediaFiles['filename']."\"],";
} while ($row_rsMediaFiles = mysql_fetch_assoc($rsMediaFiles)); 
echo "];";
?>
<?php
mysql_free_result($rsMediaFiles);
?>
