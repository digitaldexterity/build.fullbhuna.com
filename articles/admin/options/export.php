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
?>
<?php
$varRegionID_rsAllArtciles = "1";
if (isset($regionID)) {
  $varRegionID_rsAllArtciles = $regionID;
}
$varUserGroup_rsAllArtciles = "0";
if (isset($_SESSION['MM_UserGroup'])) {
  $varUserGroup_rsAllArtciles = $_SESSION['MM_UserGroup'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsAllArtciles = sprintf("SELECT article.title, article.body, articlesection.`description`, articlesection.regionID FROM article LEFT JOIN articlesection ON (article.sectionID = articlesection.ID) WHERE article.statusID  = 1 AND article.regionID  = %s AND article.accesslevel <= %s AND (articlesection.allregions = 1 OR articlesection.regionID = %s) ORDER BY articlesection.ordernum, article.ordernum", GetSQLValueString($varRegionID_rsAllArtciles, "int"),GetSQLValueString($varUserGroup_rsAllArtciles, "int"),GetSQLValueString($varRegionID_rsAllArtciles, "int"));
$rsAllArtciles = mysql_query($query_rsAllArtciles, $aquiescedb) or die(mysql_error());
$row_rsAllArtciles = mysql_fetch_assoc($rsAllArtciles);
$totalRows_rsAllArtciles = mysql_num_rows($rsAllArtciles);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsArticlePrefs = "SELECT * FROM articleprefs";
$rsArticlePrefs = mysql_query($query_rsArticlePrefs, $aquiescedb) or die(mysql_error());
$row_rsArticlePrefs = mysql_fetch_assoc($rsArticlePrefs);
$totalRows_rsArticlePrefs = mysql_num_rows($rsArticlePrefs);
?><!doctype html>
<html class="" lang="en">
<head>
<meta charset="utf-8" />
<title>Export Pages</title>
</head>

<body>

  <?php $section= "";
  do { ?>
  <?php if($section != $row_rsAllArtciles['description']) {
	  $section = $row_rsAllArtciles['description'];
	  echo "<hr><h1 style= \"text-transform:uppercase\">".$section."</h1><hr>"; } ?>
      <div style="page-break-after:always; border-bottom: 1px dotted black; padding: 2em 0;" ><?php echo (!isset($row_rsArticlePrefs['titleheading']) || $row_rsArticlePrefs['titleheading']==1) ? "<h1>". $row_rsAllArtciles['title']."</h1>" : ""; 
 echo $row_rsAllArtciles['body']; ?>
      </div>
    <?php } while ($row_rsAllArtciles = mysql_fetch_assoc($rsAllArtciles)); ?>
</body>
</html>
<?php
mysql_free_result($rsAllArtciles);

mysql_free_result($rsArticlePrefs);
?>
