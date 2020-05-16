<?php require_once(SITE_ROOT.'Connections/aquiescedb.php'); ?>
<?php require_once('calendar.inc.php'); ?>
<?php 

$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

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


$maxRows_rsEventResource = 100;
$pageNum_rsEventResource = 0;
if (isset($_GET['pageNum_rsEventResource'])) {
  $pageNum_rsEventResource = $_GET['pageNum_rsEventResource'];
}
$startRow_rsEventResource = $pageNum_rsEventResource * $maxRows_rsEventResource;

$varCategoryID_rsEventResource = "0";
if (isset($_GET['categoryID'])) {
  $varCategoryID_rsEventResource = $_GET['categoryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsEventResource = sprintf("SELECT ID, resourcename FROM eventresource WHERE statusID = 1 AND (%s = 0 OR eventresource.categoryID = %s) ORDER BY ordernum ASC, resourcename ASC", GetSQLValueString($varCategoryID_rsEventResource, "int"),GetSQLValueString($varCategoryID_rsEventResource, "int"));
$query_limit_rsEventResource = sprintf("%s LIMIT %d, %d", $query_rsEventResource, $startRow_rsEventResource, $maxRows_rsEventResource);
$rsEventResource = mysql_query($query_limit_rsEventResource, $aquiescedb) or die(mysql_error());
$row_rsEventResource = mysql_fetch_assoc($rsEventResource);

if (isset($_GET['totalRows_rsEventResource'])) {
  $totalRows_rsEventResource = $_GET['totalRows_rsEventResource'];
} else {
  $all_rsEventResource = mysql_query($query_rsEventResource);
  $totalRows_rsEventResource = mysql_num_rows($all_rsEventResource);
}
$totalPages_rsEventResource = ceil($totalRows_rsEventResource/$maxRows_rsEventResource)-1;


 ?>
<div class="fb-event-resource-grid-wrapper">
<div class="fb-event-resource-grid">
 <?php echo getDiaryDay($date, 1800, '08:00:00','19:00:00', 0, -1, "horizontal", 37); 
 // line for just times?>
  <?php do { ?>
   <?php echo getDiaryDay($date, 1800, '08:00:00','19:00:00', $varCategoryID_rsEventResource, $row_rsEventResource['ID'], "horizontal", 37); ?>
    <?php } while ($row_rsEventResource = mysql_fetch_assoc($rsEventResource)); ?></div><!-- end grid--></div><?php
mysql_free_result($rsEventResource);
?><style><!--



--></style>
