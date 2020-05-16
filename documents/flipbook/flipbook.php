<?php require_once('../../Connections/aquiescedb.php'); ?>
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

$colname_rsFlipbook = "-1";
if (isset($_GET['flipbookID'])) {
  $colname_rsFlipbook = $_GET['flipbookID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsFlipbook = sprintf("SELECT * FROM flipbook WHERE ID = %s", GetSQLValueString($colname_rsFlipbook, "int"));
$rsFlipbook = mysql_query($query_rsFlipbook, $aquiescedb) or die(mysql_error());
$row_rsFlipbook = mysql_fetch_assoc($rsFlipbook);
$totalRows_rsFlipbook = mysql_num_rows($rsFlipbook);
?>
<!doctype html>
<html class="" lang="en">
<head>
<meta charset="utf-8" />
<title><?php $pageTitle = $row_rsFlipbook['flipbookname']; echo $pageTitle." | ".$site_name; ?></title>

<link href="css/flipbook.css" rel="stylesheet"  />

<script src="js/liquid.js"></script>
<script src="js/swfobject.js"></script>
<script src="js/flipbook.js"></script>
<script src="js/flipbook_settings.js.php?flipbookID=<?php echo $row_rsFlipbook['ID']; ?>"></script>
</head>
<body>
	<div id="fbContainer">
    	<a class="altlink" href="http://www.adobe.com/shockwave/download/download.cgi?P1_Prod_Version=ShockwaveFlash"><div id="altmsg">Download Adobe Flash Player.</div></a>
    </div>
   	<div id="fbFooter">
		<div id="fbContents">
   			<select id="fbContentsMenu" name="fbContentsMenu"></select>
			<span class="fbPaginationMinor">p.&nbsp;</span>
			<span id="fbCurrentPages">1</span>
			<span id="fbTotalPages" class="fbPaginationMinor"></span>
		</div>
		<div id="fbMenu">
			<img src="img/btnZoom.gif" width="36" height="40" id="fbZoomButton" /><img src="img/btnPrint.gif" width="36" height="40" id="fbPrintButton" /><img src="img/btnDownload.gif" width="36" height="40" id="fbDownloadButton" /><img src="img/btnDiv.gif" width="13" height="40" /><img src="img/btnPrevious.gif" width="36" height="40" id="fbBackButton" /><img src="img/btnNext.gif" width="36" height="40" id="fbForwardButton" /></div>
</div>
</body>
</html>
<?php
mysql_free_result($rsFlipbook);
?>
