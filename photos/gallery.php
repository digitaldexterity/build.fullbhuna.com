<?php require_once('../Connections/aquiescedb.php'); ?><?php require_once('../core/includes/framework.inc.php'); ?><?php require_once('../members/includes/userfunctions.inc.php'); ?>
<?php $_GET['galleryID'] = (isset($_GET['galleryID']) && $_GET['galleryID'] >=1) ? $_GET['galleryID'] : 0; ?>
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

$currentPage = $_SERVER["PHP_SELF"];


$colname_rsLoggedIn = "1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID, usertypeID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

$colname_rsGallery = "-1";
if (isset($_GET['galleryID'])) {
  $colname_rsGallery = $_GET['galleryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsGallery = sprintf("SELECT photocategories.*, directory.ID AS directoryID, directory.name FROM photocategories LEFT JOIN directorygallery ON (photocategories.ID = directorygallery.galleryID) LEFT JOIN directory ON (directory.ID = directorygallery.directoryID) WHERE photocategories.ID = %s", GetSQLValueString($colname_rsGallery, "int"));
$rsGallery = mysql_query($query_rsGallery, $aquiescedb) or die(mysql_error());
$row_rsGallery = mysql_fetch_assoc($rsGallery);
$totalRows_rsGallery = mysql_num_rows($rsGallery);

$maxRows_rsGalleries = 100;
$pageNum_rsGalleries = 0;
if (isset($_GET['pageNum_rsGalleries'])) {
  $pageNum_rsGalleries = $_GET['pageNum_rsGalleries'];
}
$startRow_rsGalleries = $pageNum_rsGalleries * $maxRows_rsGalleries;

$varUserLevel_rsGalleries = "0";
if (isset($_SESSION['MM_UserGroup'])) {
  $varUserLevel_rsGalleries = $_SESSION['MM_UserGroup'];
}
$varThisGalleryID_rsGalleries = "0";
if (isset($_GET['galleryID'])) {
  $varThisGalleryID_rsGalleries = $_GET['galleryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsGalleries = sprintf("SELECT photocategories.*, users.firstname, users.surname,  COUNT(photos.ID) as numphotos, photos.title, photos.imageURL, coverphoto.imageURL AS coverimageURL FROM photocategories LEFT JOIN users ON (photocategories.addedbyID = users.ID) LEFT JOIN photos ON (photos.categoryID = photocategories.ID) LEFT JOIN photos AS coverphoto ON (photos.ID = photocategories.coverphotoID) WHERE photocategories.active = 1 AND photocategories.accesslevel <= %s GROUP BY photocategories.ID HAVING photocategories.ID != %s ORDER BY photocategories.ordernum ASC, photocategories.createddatetime DESC ", GetSQLValueString($varUserLevel_rsGalleries, "int"),GetSQLValueString($varThisGalleryID_rsGalleries, "int"));
$query_limit_rsGalleries = sprintf("%s LIMIT %d, %d", $query_rsGalleries, $startRow_rsGalleries, $maxRows_rsGalleries);
$rsGalleries = mysql_query($query_limit_rsGalleries, $aquiescedb) or die(mysql_error());
$row_rsGalleries = mysql_fetch_assoc($rsGalleries);

if (isset($_GET['totalRows_rsGalleries'])) {
  $totalRows_rsGalleries = $_GET['totalRows_rsGalleries'];
} else {
  $all_rsGalleries = mysql_query($query_rsGalleries);
  $totalRows_rsGalleries = mysql_num_rows($all_rsGalleries);
}
$totalPages_rsGalleries = ceil($totalRows_rsGalleries/$maxRows_rsGalleries)-1;

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsMediaPrefs = "SELECT * FROM mediaprefs";
$rsMediaPrefs = mysql_query($query_rsMediaPrefs, $aquiescedb) or die(mysql_error());
$row_rsMediaPrefs = mysql_fetch_assoc($rsMediaPrefs);
$totalRows_rsMediaPrefs = mysql_num_rows($rsMediaPrefs);

$queryString_rsGalleries = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsGalleries") == false && 
        stristr($param, "totalRows_rsGalleries") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsGalleries = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsGalleries = sprintf("&totalRows_rsGalleries=%d%s", $totalRows_rsGalleries, $queryString_rsGalleries);
?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Photos - "; $pageTitle .= ($_GET['galleryID']>0) ? $row_rsGallery['categoryname'] : "Latest"; echo $pageTitle." | ".$site_name; 
?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<style><!--
<?php if($row_rsLoggedIn['usertypeID']<8) { echo " #link_options { display:none; } "; } ?>
--></style>

<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" -->
    <div id = "photoPage">
      <?php 
	if (thisUserHasAccess($row_rsGallery['accesslevel'], $row_rsGallery['groupID'], $row_rsLoggedIn['ID'])) { // OK to access ?>
     
     
      <?php 
	if (isset($_SESSION['MM_UserGroup']) && $_SESSION['MM_UserGroup'] >= $row_rsMediaPrefs['uploadrankID']) { // OK to upload?>
      <div id="photosMenu"><nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
      <?php if (!isset($_GET['galleryID'])) { ?>
      <li class="nav-item"><a href="index.php" class="nav-link"><i class="glyphicon glyphicon-arrow-left"></i> Gallery Menu</a></li>
      <?php } ?>
      <li class="nav-item"><a href="members/add_photos.php?galleryID=<?php echo intval($_GET['galleryID']); ?>" class="nav-link"><i class="glyphicon glyphicon-plus-sign"></i> Add  photos</a></li>
      <li class="nav-item"><a href="members/galleries/update_gallery.php?galleryID=<?php echo $row_rsGallery['ID']; ?>" class="nav-link"><i class="glyphicon glyphicon-cog"></i> Edit Gallery</a></li>
        
        <li class="nav-item"><a href="members/galleries/add_gallery.php" class="nav-link"><i class="glyphicon glyphicon-plus-sign"></i> Add  gallery</a></li>
        <li class="nav-item" id="link_options"><a href="/photos/admin/options/index.php" target="_blank" class="nav-link" rel="noopener"><i class="glyphicon glyphicon-cog"></i> Options</a></li>
      </ul></div></nav>
      </div>
      
      <?php } //ok to upload ?>	
     <?php require_once('../core/includes/alert.inc.php'); ?>
	<?php echo isset($row_rsGallery['description']) ?  "<p>".nl2br(htmlentities($row_rsGallery['description']))."</p>" : ""; ?>
    <?php if(isset($row_rsGallery['name'])) { ?>
      
    <p><a href="../directory/directory.php?directoryID=<?php echo $row_rsGallery['directoryID']; ?>" class="link_forward"><?php echo $row_rsGallery['name']; ?></a></p>
    <?php } ?>
    
	<?php require_once('includes/gallery.inc.php'); ?>
    <a href="index.php" id="gallery_index_link">Back to <?php echo $row_rsMediaPrefs['galleriesname']; ?></a>
	
      <?php if ($totalRows_rsGalleries > 0) { // Show if recordset not empty ?>
        <div id="galleryIndex">
          <ul>
            <?php do { ?>
              <li><a href="gallery.php?galleryID=<?php echo $row_rsGalleries['ID']; ?>"><strong><?php echo isset($row_rsGalleries['categorydate']) ? date('d M Y',strtotime($row_rsGalleries['categorydate'])) : date('d M Y',strtotime($row_rsGalleries['createddatetime'])); ?></strong><br /><?php echo $row_rsGalleries['categoryname']; ?></a></li>
              <?php } while ($row_rsGalleries = mysql_fetch_assoc($rsGalleries)); ?>
          </ul>
        </div>
        <?php } // Show if recordset not empty ?>
      <?php } else { //not OK to access ?>
      <p class="alert alert-danger">You do not have access to this photo gallery. You might need to <a href="/login/index.php?accesscheck=<?php echo $_SERVER['REQUEST_URI']; ?>">log in</a>.</p>
      <?php } ?>
      
    </div>
    <!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsGallery);

mysql_free_result($rsGalleries);
?>
