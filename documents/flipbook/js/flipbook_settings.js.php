<?php require_once('../../../Connections/aquiescedb.php'); ?><?php require_once('../../../core/includes/framework.inc.php'); ?>
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


$colname_rsPages = "-1";
if (isset($row_rsFlipbook['galleryID'])) {
  $colname_rsPages = $row_rsFlipbook['galleryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPages = sprintf("SELECT imageURL, description FROM photos WHERE categoryID = %s AND active = 1 ORDER BY ordernum,ID ASC", GetSQLValueString($colname_rsPages, "int"));
$rsPages = mysql_query($query_rsPages, $aquiescedb) or die(mysql_error());
$row_rsPages = mysql_fetch_assoc($rsPages);
$totalRows_rsPages = mysql_num_rows($rsPages);


 
header("content-type: application/x-javascript"); ?>

flippingBook.pages = [
<?php $pages = ""; $pagenum = 1;  $bookmarks = ""; $zoompath = dirname("/Uploads/".$row_rsPages['imageURL'])."/"; 
do { 
	$imageURL = getImageURL($row_rsPages['imageURL']);
	$pages .= "\"".$imageURL."\",\n";
	$bookmarks .=  "[ \"".$pagenum;
	$bookmarks .= ($row_rsPages['description']!="") ? " - ".addslashes($row_rsPages['description']) : "";
	$bookmarks .= "\", ".$pagenum." ],\n";
	$pagenum ++; 
} while ($row_rsPages = mysql_fetch_assoc($rsPages)); echo trim($pages,",");  ?>	
];

<?php  list($width, $height) = getimagesize ( SITE_ROOT.$imageURL );
$ratio = ($width>412) ? 412/$width : 1;
	

	$pagewidth = $ratio * $width *2;
	$pageheight = $ratio * $height;
	$zoomwidth =  $width ;
	$zoomheight = $height *2;
	
	?>

flippingBook.contents = [
	<?php echo trim($bookmarks,","); ?>
];

// define custom book settings here
flippingBook.settings.stageWidth = "90%";
flippingBook.settings.bookWidth = <?php echo defined("FLIPBOOK_WIDTH") ? FLIPBOOK_WIDTH*2 : $pagewidth; //824; ?>;
flippingBook.settings.bookHeight = <?php echo defined("FLIPBOOK_HEIGHT") ? FLIPBOOK_HEIGHT : $pageheight; //585; ?>;
flippingBook.settings.pageBackgroundColor = 0x999999;
flippingBook.settings.backgroundColor = 0x00788d;
flippingBook.settings.zoomUIColor = 0x00788d;
flippingBook.settings.useCustomCursors = false;
flippingBook.settings.dropShadowEnabled = false;
flippingBook.settings.zoomImageWidth = <?php echo defined("FLIPBOOK_WIDTH") ? FLIPBOOK_WIDTH*2 : $zoomwidth; //912; ?>;
flippingBook.settings.zoomImageHeight = <?php echo defined("FLIPBOOK_HEIGHT") ? FLIPBOOK_HEIGHT*2 : $zoomheight; //1294; ?>;
flippingBook.settings.downloadURL = "<?php echo $row_rsFlipbook['downloadURL']; ?>";
flippingBook.settings.flipSound = "sounds/01.mp3";
flippingBook.settings.flipCornerStyle = "first page only";
flippingBook.settings.zoomHintEnabled = true,
flippingBook.settings.zoomPath = "<?php echo $zoompath; ?>/"; // for development

// default settings can be found in the flippingbook.js file
flippingBook.create();
<?php
mysql_free_result($rsPages);

mysql_free_result($rsFlipbook);
?>
