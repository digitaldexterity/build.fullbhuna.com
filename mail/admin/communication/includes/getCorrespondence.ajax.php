<?php require_once('../../../../Connections/aquiescedb.php'); ?>
<?php //if(!isset($_SESSION['MM_UserGroup']) || $_SESSION['MM_UserGroup']<7 ) die; 
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

$colname_rsCorrespondence = "-1";
if (isset($_GET['correspondenceID'])) {
  $colname_rsCorrespondence = $_GET['correspondenceID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsCorrespondence = sprintf("SELECT * FROM correspondence WHERE ID = %s", GetSQLValueString($colname_rsCorrespondence, "int"));
$rsCorrespondence = mysql_query($query_rsCorrespondence, $aquiescedb) or die(mysql_error());
$row_rsCorrespondence = mysql_fetch_assoc($rsCorrespondence);
$totalRows_rsCorrespondence = mysql_num_rows($rsCorrespondence);
?>
Date: <?php echo date('d M Y H:i', strtotime($row_rsCorrespondence['createddatetime']))."\n"; ?>
From: <?php echo $row_rsCorrespondence['sendername']; ?> To: <?php echo $row_rsCorrespondence['recipient']."\n"; ?>
Subject: <?php echo $row_rsCorrespondence['subject']; ?><?php echo "\n\n".trim(strip_tags($row_rsCorrespondence['message'])); ?>
<?php
mysql_free_result($rsCorrespondence);
?>
