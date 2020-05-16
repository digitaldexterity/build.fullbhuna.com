<?php require_once('../Connections/aquiescedb.php'); ?><?php require_once('../core/includes/framework.inc.php'); ?>
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

$regionID = (isset($regionID) && intval($regionID)>0) ? intval($regionID) : 1;

$maxRows_rsVideos = 20;
$pageNum_rsVideos = 0;
if (isset($_GET['pageNum_rsVideos'])) {
  $pageNum_rsVideos = $_GET['pageNum_rsVideos'];
}
$startRow_rsVideos = $pageNum_rsVideos * $maxRows_rsVideos;

$varCategoryID_rsVideos = "0";
if (isset($_GET['categoryID'])) {
  $varCategoryID_rsVideos = $_GET['categoryID'];
}
$varRegionID_rsVideos = "1";
if (isset($regionID)) {
  $varRegionID_rsVideos = $regionID;
}
$varUserGroup_rsVideos = "0";
if (isset($_SESSION['MM_UserGroup'])) {
  $varUserGroup_rsVideos = $_SESSION['MM_UserGroup'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsVideos = sprintf("SELECT video.* FROM video LEFT JOIN videocategory ON (video.categoryID = videocategory.ID) WHERE video.statusID = 1 AND (videocategory.statusID = 1 OR videocategory.statusID IS NULL) AND (categoryID = %s OR %s = 0) AND (videocategory.accesslevel <= %s OR videocategory.accesslevel IS NULL) AND (videocategory.regionID IS NULL OR videocategory.regionID  = 0 OR videocategory.regionID = %s) ORDER BY createddatetime DESC", GetSQLValueString($varCategoryID_rsVideos, "int"),GetSQLValueString($varCategoryID_rsVideos, "int"),GetSQLValueString($varUserGroup_rsVideos, "int"),GetSQLValueString($varRegionID_rsVideos, "int"));
$query_limit_rsVideos = sprintf("%s LIMIT %d, %d", $query_rsVideos, $startRow_rsVideos, $maxRows_rsVideos);
$rsVideos = mysql_query($query_limit_rsVideos, $aquiescedb) or die(mysql_error());
$row_rsVideos = mysql_fetch_assoc($rsVideos);

if (isset($_GET['totalRows_rsVideos'])) {
  $totalRows_rsVideos = $_GET['totalRows_rsVideos'];
} else {
  $all_rsVideos = mysql_query($query_rsVideos);
  $totalRows_rsVideos = mysql_num_rows($all_rsVideos);
}
$totalPages_rsVideos = ceil($totalRows_rsVideos/$maxRows_rsVideos)-1;

$varVideoID_rsThisVideo = "-1";
if (isset($_GET['videoID'])) {
  $varVideoID_rsThisVideo = $_GET['videoID'];
}
$varCategoryID_rsThisVideo = "1";
if (isset($_GET['categoryID'])) {
  $varCategoryID_rsThisVideo = $_GET['categoryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisVideo = sprintf("SELECT video.* FROM video WHERE (video.ID = %s OR %s = -1) AND video.categoryID = %s AND video.statusID = 1 ORDER BY video.createddatetime DESC LIMIT 1", GetSQLValueString($varVideoID_rsThisVideo, "int"),GetSQLValueString($varVideoID_rsThisVideo, "int"),GetSQLValueString($varCategoryID_rsThisVideo, "int"));
$rsThisVideo = mysql_query($query_rsThisVideo, $aquiescedb) or die(mysql_error());
$row_rsThisVideo = mysql_fetch_assoc($rsThisVideo);
$totalRows_rsThisVideo = mysql_num_rows($rsThisVideo);

$varCategoryID_rsThisVideoCategory = "-1";
if (isset($_GET['categoryID'])) {
  $varCategoryID_rsThisVideoCategory = $_GET['categoryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisVideoCategory = sprintf("SELECT videocategory.categoryname, videocategory.categorydescription FROM videocategory WHERE videocategory.ID = %s ", GetSQLValueString($varCategoryID_rsThisVideoCategory, "int"));
$rsThisVideoCategory = mysql_query($query_rsThisVideoCategory, $aquiescedb) or die(mysql_error());
$row_rsThisVideoCategory = mysql_fetch_assoc($rsThisVideoCategory);
$totalRows_rsThisVideoCategory = mysql_num_rows($rsThisVideoCategory);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsCategories = "SELECT videocategory.ID, videocategory.categoryname, videocategory.accesslevel FROM videocategory WHERE videocategory.statusID = 1";
$rsCategories = mysql_query($query_rsCategories, $aquiescedb) or die(mysql_error());
$row_rsCategories = mysql_fetch_assoc($rsCategories);
$totalRows_rsCategories = mysql_num_rows($rsCategories);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT videoupload FROM preferences WHERE ID = 1";
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);
?><?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Video - "; $pageTitle .= isset($row_rsThisVideoCategory['categoryname']) ? $row_rsThisVideoCategory['categoryname'] : "Latest clips"; echo $pageTitle." | ".$site_name; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<link href="css/video_default.css" rel="stylesheet"  />
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" -->
   <div id="videoPage" class="container">
    <div id="videoIndex" class="subMenu">
      
   <?php if ($row_rsPreferences['videoupload']==1) { ?>
         <p><a href="members/post_video.php">Add your own video - it's easy!</a></p><?php } ?>
         
         <?php if ($totalRows_rsCategories > 0) { // Show if recordset not empty ?>
          <div id="videoCategoryMenu">
          <ul id="videoCategories">
          <li<?php if(!isset($_GET['categoryID']) || $_GET['categoryID']==0) { echo " class=\"selected\" "; } ?>><a href="index.php?categoryID=0">Latest clips</a></li>
             <?php do { ?>
                 <li<?php if($row_rsCategories['ID'] == $_GET['categoryID']) { echo " class=\"selected\" "; } ?>><a href="index.php?categoryID=<?php echo $row_rsCategories['ID']; ?>"><?php echo htmlentities($row_rsCategories['categoryname']); ?></a>
                   <?php echo ($row_rsCategories['accesslevel']>0) ? "*" : ""; $accesslevel += $row_rsCategories['accesslevel']; ?></li>
                 
                 <?php } while ($row_rsCategories = mysql_fetch_assoc($rsCategories)); ?>
            </ul><?php if (isset($accesslevel) && $accesslevel >0) { ?>*Member access only<?php } ?></div>
           <?php } // Show if recordset not empty ?>

         <?php if ($totalRows_rsVideos > 0) { // Show if recordset not empty ?><ul id="videoThumbs">
        <?php do { ?><li class="videoThumb">
        
          <a href="index.php?videoID=<?php echo $row_rsVideos['ID']; ?>&amp;categoryID=<?php echo $row_rsVideos['categoryID']; ?>" class="img" ><?php if (is_readable(SITE_ROOT.getImageURL($row_rsVideos['imageURL']),"thumb")) { ?><img src="<?php echo getImageURL($row_rsVideos['imageURL'],"thumb"); ?>" alt="<?php echo htmlentities($row_rsVideos['videotitle']); ?> - click to view" /><?php } else { ?>
              <img src="/video/images/video_icon.png" width="110" height="90" alt="<?php echo $row_rsVideos['videotitle']; ?> - click to view"  />
              <?php } ?></a>
          <span class="videoIndexTitle"><a href="index.php?videoID=<?php echo $row_rsVideos['ID']; ?>&amp;categoryID=<?php echo $row_rsVideos['categoryID']; ?>"><?php echo htmlentities($row_rsVideos['videotitle']); ?></a></span><span class="videoIndexDescription"><br /></span>
        </li>
          <?php } while ($row_rsVideos = mysql_fetch_assoc($rsVideos)); ?></ul><?php } // Show if recordset not empty ?>
<?php if ($totalRows_rsVideos == 0) { // Show if recordset empty ?>
            <p>There are currently no clips in this category.</p>
            <?php } // Show if recordset empty ?>
</div>
    <h1 id="videoPageTitle"><?php echo isset($row_rsThisVideoCategory['categoryname']) ? $row_rsThisVideoCategory['categoryname'] : "Latest" ; ?> Clips</h1>
    <?php if(isset($row_rsThisVideoCategory['categorydescription'])) { ?><p><?php echo nl2br(htmlentities($row_rsThisVideoCategory['categorydescription'])); ?></p><?php } ?>
    <div id="videoPlayer">
      <?php if ($totalRows_rsThisVideo > 0) { // Show if recordset not empty ?>
        <h2><?php echo htmlentities($row_rsThisVideo['videotitle']); ?> </h2> 
        <?php if($row_rsThisVideo['method']==1) { // embed 
		echo $row_rsThisVideo['videoURL']; } else if (is_readable(SITE_ROOT."Uploads/".$row_rsThisVideo['videoURL'])) { 
		
		// for compayitbility we shall assume if no mimetype it is flv
		$videoURL = "/Uploads/".$row_rsThisVideo['videoURL'];
		$quicktime = array("video/x-m4v","video/quicktime","video/mp4","video/3gpp","video/3gpp2");
		$windowsmedia = array("application/vnd.ms-asf","video/x-ms-asf","video/x-ms-asx","video/x-msvideo","video/x-ms-wmv","video/mpeg","video/avi");
		$realmedia = array("application/vnd.rn-realmedia","audio/vnd.rn-realaudio","video/vnd.rn-realvideo","audio/x-pn-realaudio","audio/x-pn-realaudio-plugin");
		
		if(in_array(@$row_rsThisVideo['mimetype'],$quicktime))  { // quicktime
		 require_once('includes/embedQuicktime.inc.php');
		} else if(in_array(@$row_rsThisVideo['mimetype'],$windowsmedia)) { // windows media
			require_once('includes/embedWindowsMedia.inc.php');
			} else if(in_array(@$row_rsThisVideo['mimetype'],$realmedia)) { // real media
			require_once('includes/embedRealMedia.inc.php');
		} else { // undefined - set as FLV
		
	 require_once('includes/embedFlashVideo.inc.php');
		
		} // end unedfined - i.e. FLV
		 } else { ?><p>We're sorry, this video is unavailable.</p><?php } ?>
          
        <p><?php echo nl2br($row_rsThisVideo['videodescription']); ?> </p>
        <?php } // Show if recordset not empty ?>
</div></div>
   
      <!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsVideos);

mysql_free_result($rsThisVideo);

mysql_free_result($rsThisVideoCategory);

mysql_free_result($rsCategories);

mysql_free_result($rsPreferences);
?>
