<?php require_once('../Connections/aquiescedb.php'); ?><?php require_once('../core/includes/framework.inc.php'); ?>
<?php
 
 if (!isset($_SESSION)) {
  session_start();
}if (!function_exists("GetSQLValueString")) {
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
$varOrgID_rsDirectory = "-1";
if (isset($_GET['directoryID'])) {
  $varOrgID_rsDirectory = $_GET['directoryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsDirectory = sprintf("SELECT directory.*, directorycategory.description AS categoryname FROM directory LEFT JOIN directorycategory ON (directory.categoryID = directorycategory.ID) WHERE directory.ID = %s", GetSQLValueString($varOrgID_rsDirectory, "int"));
$rsDirectory = mysql_query($query_rsDirectory, $aquiescedb) or die(mysql_error());
$row_rsDirectory = mysql_fetch_assoc($rsDirectory);
$totalRows_rsDirectory = mysql_num_rows($rsDirectory);

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID, usertypeID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT googlemapsAPI, streetview FROM preferences";
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);

$varUserGroup_rsDocuments = "0";
if (isset($_SESSION['MM_UserGroup'])) {
  $varUserGroup_rsDocuments = $_SESSION['MM_UserGroup'];
}
$varDirectoryID_rsDocuments = "-1";
if (isset($_GET['directoryID'])) {
  $varDirectoryID_rsDocuments = $_GET['directoryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsDocuments = sprintf("SELECT documents.ID, documents.documentname, documents.filename, documents.type, documentcategory.accessID FROM documents LEFT JOIN documentcategory ON (documents.documentcategoryID = documentcategory.ID) WHERE documents.directoryID = %s AND documents.active = 1 AND documentcategory.active = 1 AND documentcategory.accessID <= %s", GetSQLValueString($varDirectoryID_rsDocuments, "int"),GetSQLValueString($varUserGroup_rsDocuments, "int"));
$rsDocuments = mysql_query($query_rsDocuments, $aquiescedb) or die(mysql_error());
$row_rsDocuments = mysql_fetch_assoc($rsDocuments);
$totalRows_rsDocuments = mysql_num_rows($rsDocuments);

$varDirectoryID_rsDirectoryWeb = "-1";
if (isset($_GET['directoryID'])) {
  $varDirectoryID_rsDirectoryWeb = $_GET['directoryID'];
}
$varPageID_rsDirectoryWeb = "0";
if (isset($_GET['webPageID'])) {
  $varPageID_rsDirectoryWeb = $_GET['webPageID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsDirectoryWeb = sprintf("SELECT microsite.ID, microsite.pageTitle, microsite.headerHTML, microsite.bodyHTML, microsite.statusID FROM microsite WHERE microsite.directoryID = %s AND microsite.pageID = %s AND microsite.statusID = 1", GetSQLValueString($varDirectoryID_rsDirectoryWeb, "int"),GetSQLValueString($varPageID_rsDirectoryWeb, "int"));
$rsDirectoryWeb = mysql_query($query_rsDirectoryWeb, $aquiescedb) or die(mysql_error());
$row_rsDirectoryWeb = mysql_fetch_assoc($rsDirectoryWeb);
$totalRows_rsDirectoryWeb = mysql_num_rows($rsDirectoryWeb);

$varDirectoryID_rsOccupiers = "-1";
if (isset($_GET['directoryID'])) {
  $varDirectoryID_rsOccupiers = $_GET['directoryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsOccupiers = sprintf("SELECT directory.ID, directory.name FROM directory LEFT JOIN directory AS thisdirectory ON (directory.occupierofID = thisdirectory.ID) WHERE  thisdirectory.ID = %s AND  directory.statusID = 1", GetSQLValueString($varDirectoryID_rsOccupiers, "int"));
$rsOccupiers = mysql_query($query_rsOccupiers, $aquiescedb) or die(mysql_error());
$row_rsOccupiers = mysql_fetch_assoc($rsOccupiers);
$totalRows_rsOccupiers = mysql_num_rows($rsOccupiers);

$varDirReferer_rsDirReferer = "-1";
if (isset($_GET['refererDirectoryID'])) {
  $varDirReferer_rsDirReferer = $_GET['refererDirectoryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsDirReferer = sprintf("SELECT  directory.ID, directory.name AS referer FROM directory WHERE directory.ID = %s ", GetSQLValueString($varDirReferer_rsDirReferer, "int"));
$rsDirReferer = mysql_query($query_rsDirReferer, $aquiescedb) or die(mysql_error());
$row_rsDirReferer = mysql_fetch_assoc($rsDirReferer);
$totalRows_rsDirReferer = mysql_num_rows($rsDirReferer);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsDirectoryPrefs = "SELECT * FROM directoryprefs";
$rsDirectoryPrefs = mysql_query($query_rsDirectoryPrefs, $aquiescedb) or die(mysql_error());
$row_rsDirectoryPrefs = mysql_fetch_assoc($rsDirectoryPrefs);
$totalRows_rsDirectoryPrefs = mysql_num_rows($rsDirectoryPrefs);

$colname_rsGalleries = "-1";
if (isset($_GET['directoryID'])) {
  $colname_rsGalleries = $_GET['directoryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsGalleries = sprintf("SELECT galleryID, productcategory.title FROM directorygallery LEFT JOIN productcategory ON (directorygallery.galleryID = productcategory.ID) WHERE directorygallery.directoryID = %s AND productcategory.statusID = 1 ORDER BY productcategory.ordernum", GetSQLValueString($colname_rsGalleries, "int"));
$rsGalleries = mysql_query($query_rsGalleries, $aquiescedb) or die(mysql_error());
$row_rsGalleries = mysql_fetch_assoc($rsGalleries);
$totalRows_rsGalleries = mysql_num_rows($rsGalleries);

if(isset($_GET['pageNum_rsEvents'])) $_GET['pageNum_rsEvents'] = intval($_GET['pageNum_rsEvents']);
if(isset($_GET['totalRows_rsEvents'])) $_GET['totalRows_rsEvents'] = intval($_GET['totalRows_rsEvents']);



$maxRows_rsEvents = 50;
$pageNum_rsEvents = 0;
if (isset($_GET['pageNum_rsEvents'])) {
  $pageNum_rsEvents = $_GET['pageNum_rsEvents'];
}
$startRow_rsEvents = $pageNum_rsEvents * $maxRows_rsEvents;

$varDirectoryID_rsEvents = "-1";
if (isset($_GET['directoryID'])) {
  $varDirectoryID_rsEvents = $_GET['directoryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsEvents = sprintf("SELECT event.startdatetime, eventgroup.eventtitle, event.ID FROM eventgroup LEFT JOIN event ON (event.eventgroupID = eventgroup.ID) WHERE eventgroup.statusID = 1 AND eventgroup.directoryID = %s AND event.statusID = 1 AND DATE(event.startdatetime)>= CURDATE() ORDER BY event.startdatetime ", GetSQLValueString($varDirectoryID_rsEvents, "int"));
$query_limit_rsEvents = sprintf("%s LIMIT %d, %d", $query_rsEvents, $startRow_rsEvents, $maxRows_rsEvents);
$rsEvents = mysql_query($query_limit_rsEvents, $aquiescedb) or die(mysql_error());
$row_rsEvents = mysql_fetch_assoc($rsEvents);

if (isset($_GET['totalRows_rsEvents'])) {
  $totalRows_rsEvents = $_GET['totalRows_rsEvents'];
} else {
  $all_rsEvents = mysql_query($query_rsEvents);
  $totalRows_rsEvents = mysql_num_rows($all_rsEvents);
}
$totalPages_rsEvents = ceil($totalRows_rsEvents/$maxRows_rsEvents)-1;

$varDirectoryID_rsContacts = "-1";
if (isset($_GET['directoryID'])) {
  $varDirectoryID_rsContacts = $_GET['directoryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsContacts = sprintf("SELECT users.ID, users.firstname, users.surname, users.jobtitle FROM users LEFT JOIN directoryuser ON (directoryuser.userID = users.ID) WHERE showemail = 1 AND directoryuser.directoryID = %s AND (directoryuser.enddate IS NULL OR directoryuser.enddate > CURDATE()) AND directoryuser.relationshiptype >=0 ORDER BY surname ASC", GetSQLValueString($varDirectoryID_rsContacts, "int"));
$rsContacts = mysql_query($query_rsContacts, $aquiescedb) or die(mysql_error());
$row_rsContacts = mysql_fetch_assoc($rsContacts);
$totalRows_rsContacts = mysql_num_rows($rsContacts);
?>
<?php $accesslevel = $row_rsDirectoryPrefs['accesslevel']; require_once('../members/includes/restrictaccess.inc.php'); ?>
<?php if ($row_rsDirectoryWeb['ID']) { // if web page exists go to that instead
	header("location: /sites/index.php?directoryID=".intval($_GET['directoryID'])."&referer=".urlencode($_SERVER['HTTP_REFERER']));exit;
} ?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = htmlentities($row_rsDirectoryPrefs['directoryname'],ENT_COMPAT,"UTF-8")." - ".htmlentities($row_rsDirectory['name'],ENT_COMPAT,"UTF-8"); echo $pageTitle." | ".$site_name; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<meta name="Description" content="<?php echo strip_tags($row_rsDirectory['description']); ?>" />
<meta name="Keywords" content="<?php echo htmlentities($row_rsDirectory['name'],ENT_COMPAT,"UTF-8"); ?>" />
<script src="../SpryAssets/SpryTabbedPanels.js"></script>
<link href="../SpryAssets/SpryTabbedPanels.css" rel="stylesheet"  />
<?php $googlemapsAPI =isset($googlemapsAPI) ? $googlemapsAPI : $row_rsPreferences['googlemapsAPI'];
if($googlemapsAPI!="") { $latitude = isset($row_rsDirectory['latitude']) ? $row_rsDirectory['latitude']: 55.8544; // centre on glasgow if not set
$longitude = isset($row_rsDirectory['longitude']) ? $row_rsDirectory['longitude']: -4.240963;
$magnification = isset($row_rsDirectory['latitude']) ? 16 : 10; ?>
<script src="//maps.google.com/?file=api&amp;v=2.x&amp;key=<?php echo isset($googlemapsAPI) ? $googlemapsAPI : $row_rsPreferences['googlemapsAPI']; ?>"></script>
<script src="/core/scripts/googlemaps/googlemap.js" ></script>
<script src="/core/scripts/googlemaps/fb_maps.js" ></script>
<script>
//<![CDATA[
var showStreetView = <?php echo ($row_rsDirectory['streetview']==1 && $row_rsPreferences['streetview']==1) ? 1 : 0; ?>; 
var initLatitude = <?php echo $latitude; ?>;
var initLongitude = <?php echo $longitude; ?>;
var initZoom = <?php echo isset($row_rsDirectory['latitude']) ? 16 : 10; ?>;
var initMapType = G_NORMAL_MAP;
var showMapType = true;
var markerLatitude = <?php echo isset($row_rsDirectory['latitude']) ? $row_rsDirectory['latitude']: "null"; ?>;
var markerLongitude = <?php echo isset($row_rsDirectory['longitude']) ? $row_rsDirectory['longitude']: "null"; ?>;
var defaultIcon = new GIcon(G_DEFAULT_ICON);
var isEditable = false;
function init() {	
	setupMap();
	if(markerLatitude) {
		createMarker(markerLatitude,markerLongitude,defaultIcon,true) 
	} 

	if(showStreetView ==1) map.addControl(new streetViewLink());
}



addListener("load",init);

//]]>
</script>
<?php if(isset($row_rsPreferences['googlesearchAPI'])) { // load GoogleAjaxSearch ?>
<script src="//www.google.com/uds/api?file=uds.js&amp;v=1.0&amp;key=<?php echo $row_rsPreferences['googlesearchAPI']; ?>"></script>
<script>
var localSearch = new GlocalSearch();
</script>
<?php } ?>
<?php 
} else { echo "<style> #directoryMapTab { display:none; } </style>" ; } ?>
<style >
<!--
#directoryDocumentsTab {
	display: none;
}
#directoryDocumentsTabContent {
	display: none;
}
#directoryRelationshipTab {
	display: none;
}
#directoryRelationshipTabContent {
	display: none;
}
#directoryVacancyTab {
	display: none;
}
#directoryVacancyTabContent {
	display: none;
}
<?php  if($totalRows_rsGalleries==0) {
echo "#directoryGalleryTab { display: none; }\n";
}
?>
<?php  if($totalRows_rsEvents==0) {
echo "#directoryTabEvents { display: none; }\n";
} else {
	$_GET['defaultTab'] = isset($_GET['defaultTab']) ? $_GET['defaultTab'] : 1;
}
?>
-->
</style>
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" -->
      <div class="container pageBody">
      <?php if (isset($row_rsDirectory['statusID']) && $row_rsDirectory['statusID'] !=1) { ?>
      <p>This organisation is either not authorised for listing yet or has been de-listed.</p>
      <?php } else { ?>
      <div class="crumbs"><div><span class="you_are_in">You are in: </span><a href="/">Home</a><span class="separator">&nbsp;&rsaquo;&nbsp;</span><a href="/directory/"><?php echo $row_rsDirectoryPrefs['directoryname']; ?></a><?php if(isset($row_rsDirectory['categoryname'])) { ?><span class="separator">&nbsp;&rsaquo;&nbsp;</span><a href="/directory/index.php?categoryID=<?php echo $row_rsDirectory['categoryID']; ?>"><?php echo $row_rsDirectory['categoryname']; ?></a><?php } ?><span class="separator">&nbsp;&rsaquo;&nbsp;</span><?php echo htmlentities($row_rsDirectory['name'],ENT_COMPAT,"UTF-8"); ?> </div></div>
      <h1><?php echo htmlentities($row_rsDirectory['name'],ENT_COMPAT,"UTF-8"); ?></h1>
      <div id="TabbedPanels1" class="TabbedPanels">
        <ul class="TabbedPanelsTabGroup">
          <li class="TabbedPanelsTab" tabindex="0" id="directoryOrganisationTab">Details</li>
          <li class="TabbedPanelsTab" tabindex="0" id="directoryTabEvents">Events</li>
<li class="TabbedPanelsTab" tabindex="0" id="directoryDocumentsTab">Documents</li>
          <li class="TabbedPanelsTab" tabindex="0" id="directoryMapTab" onMouseOver="map.checkResize();">Map<?php echo ($row_rsDirectory['streetview']==1 && $row_rsPreferences['streetview']==1) ? "/Street View" : ""; ?></li>
          <li class="TabbedPanelsTab" tabindex="0" id="directoryRelationshipTab" >Occupiers</li>
          <li class="TabbedPanelsTab" tabindex="0" id="directoryVacancyTab" >Available Lets</li>
          <li class="TabbedPanelsTab" tabindex="0" id="directoryGalleryTab">Galleries</li>
        </ul>
        <div class="TabbedPanelsContentGroup">
          <div class="TabbedPanelsContent" id="directoryVacancyTabContent">
            <p style="zoom:1">
              <?php if (isset($row_rsDirectory['imageURL'])) { ?>
              <a href="/core/images/view_large_image.php?imageURL=<?php echo getImageURL($row_rsDirectory['imageURL']); ?>"><img src="<?php echo getImageURL($row_rsDirectory['imageURL'],"medium"); ?>" alt="<?php echo $row_rsDirectory['name']; ?> - click on image for larger view" class="fltrt" /></a>
              <?php } ?>
            <?php echo nl2br(htmlentities($row_rsDirectory['description'],ENT_COMPAT,"UTF-8")); ?></p>
            <?php if($row_rsDirectoryPrefs['contactform']==1) { ?>
            <p><a href="/contact/index.php?directoryID=<?php echo $row_rsDirectory['ID']; ?>">Contact us directly from this site</a></p>
            <?php } ?>
            <p><?php echo isset($row_rsDirectory['address1']) ? htmlentities($row_rsDirectory['address1'],ENT_COMPAT,"UTF-8")."<br />" : ""; ?><?php echo isset($row_rsDirectory['address2']) ? htmlentities($row_rsDirectory['address2'],ENT_COMPAT,"UTF-8")."<br />" : ""; ?><?php echo isset($row_rsDirectory['address3']) ? htmlentities($row_rsDirectory['address3'],ENT_COMPAT,"UTF-8")."<br />" : ""; ?><?php echo isset($row_rsDirectory['address4']) ? htmlentities($row_rsDirectory['address4'],ENT_COMPAT,"UTF-8")."<br />" : ""; ?><?php echo isset($row_rsDirectory['address5']) ? htmlentities($row_rsDirectory['address5'],ENT_COMPAT,"UTF-8")."<br />" : ""; ?> <?php echo isset($row_rsDirectory['postcode']) ? htmlentities($row_rsDirectory['postcode'],ENT_COMPAT,"UTF-8")."<br />" : ""; ?></p>
            <?php if (isset($row_rsDirectory['email'])) { ?>
            <p><strong>Email:</strong> <a href="mailto:<?php echo htmlentities($row_rsDirectory['email'],ENT_COMPAT,"UTF-8"); ?>"><?php echo htmlentities($row_rsDirectory['email'],ENT_COMPAT,"UTF-8"); ?></a></p>
            <?php } ?>
            <?php if (isset($row_rsDirectory['url']) && strlen($row_rsDirectory['url']) > 7) { ?>
            <p><strong>Web site:</strong> <a href="<?php echo htmlentities($row_rsDirectory['url'],ENT_COMPAT,"UTF-8"); ?>" target="_blank" rel="noopener"><?php echo htmlentities($row_rsDirectory['url'],ENT_COMPAT,"UTF-8"); ?></a></p>
            <?php } ?>
            <?php if (isset($row_rsDirectory['telephone'])) { ?>
            <p><strong>Telephone:</strong> <?php echo htmlentities($row_rsDirectory['telephone'],ENT_COMPAT,"UTF-8"); ?></p>
            <?php } ?>
            <?php if (isset($row_rsDirectory['mobile'])) { ?>
            <p><strong>Mobile:</strong> <?php echo htmlentities($row_rsDirectory['mobile'],ENT_COMPAT,"UTF-8"); ?></p>
            <?php } ?>
            <?php if (isset($row_rsDirectory['fax'])) { ?>
            <p><strong>Fax:</strong> <?php echo htmlentities($row_rsDirectory['fax'],ENT_COMPAT,"UTF-8"); ?></p>
            <?php } ?>
            <?php if ($row_rsDirectoryPrefs['showcontacts']== 1 && $totalRows_rsContacts > 0) { // contacts ?>
            <h2>Contacts:</h2>
 
    <ul>
    <?php do { ?>
     <li>
       
        <?php echo htmlentities($row_rsContacts['firstname']." ".$row_rsContacts['surname'],ENT_COMPAT,"UTF-8"); ?> <em><?php echo htmlentities($row_rsContacts['jobtitle'],ENT_COMPAT,"UTF-8"); ?></em> <a href="../members/message/index.php?userID=<?php echo $row_rsContacts['ID']; ?>&amp;key=<?php echo md5($row_rsContacts['ID'].PRIVATE_KEY); ?>" class="link_email icon_with_text">Send message</a></li>
        <?php } while ($row_rsContacts = mysql_fetch_assoc($rsContacts)); ?>
 </ul>
  <?php } // Show if recordset not empty ?>
<br class="clearfloat" />
          </div>
          <div class="TabbedPanelsContent">
            <table  class="table table-hover">
            <tbody>
              <?php do { ?>
                <tr>
                  <td><?php echo date('d M Y H:s', strtotime($row_rsEvents['startdatetime'])); ?></td>
                  <td><?php echo htmlentities($row_rsEvents['eventtitle'],ENT_COMPAT,"UTF-8"); ?></td>
                  <td><a href="../calendar/event.php?eventID=<?php echo $row_rsEvents['ID']; ?>&amp;returnURL=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="link_view">View</a></td>
                </tr>
                <?php } while ($row_rsEvents = mysql_fetch_assoc($rsEvents)); ?></tbody>
            </table>
          </div>
<div class="TabbedPanelsContent" id="directoryDocumentsTabContent">
            <?php if ($totalRows_rsDocuments == 0) { // Show if recordset empty ?>
              <p>There are no viewable documents uploaded by this organisation so far.</p>
              <?php if(!isset($_SESSION['MM_Username'])) { ?>
              <p>More may be available to you if you <a href="/members/index.php">log in</a>.</p>
              <?php } ?>
            <?php } // Show if recordset empty ?>
            <?php if ($totalRows_rsDocuments > 0) { // Show if recordset not empty ?>
              <table class="table table-hover">
              <tbody>
                <?php do { ?>
                  <tr>
                    
                    <td><a href="/Uploads/<?php echo $row_rsDocuments['filename']; ?>"><?php echo htmlentities($row_rsDocuments['documentname'],ENT_COMPAT,"UTF-8"); ?></a></td>
                  </tr>
                  <?php } while ($row_rsDocuments = mysql_fetch_assoc($rsDocuments)); ?></tbody>
              </table>
              <?php } // Show if recordset not empty ?>
            
          </div>
          <div class="TabbedPanelsContent" id="directoryMapTabContent"  >
            <div id="googlemapwrapper" >
              <div id="streetviewclose"><a href="javascript:removeStreetView();">Close street view</a></div>
              <div class="googlemap" id="googlemap" ></div>
            </div>
            <?php if (isset($row_rsDirectory['mapURL']) && $row_rsDirectory['mapURL']!="") { ?>
            <p><a href="<?php echo $row_rsDirectory['mapURL']; ?>" target="_blank" rel="noopener"  >Click here to view map</a></p>
            <?php } else { ?>
            <p><a href="http://maps.google.com/?q=<?php echo isset($row_rsDirectory['latitude']) ? $row_rsDirectory['latitude'].",".$row_rsLocation['longitude'] : $row_rsDirectory['postcode'] ; ?>&amp;z=16" target="_blank" rel="noopener" >Open in Google Maps</a></p>
            <?php } ?>
          </div>
          <div class="TabbedPanelsContent" id="directoryRelationshipTabContent" >
            <?php if ($totalRows_rsOccupiers > 0) { // Show if recordset not empty ?>
              <p>A list of current occupiers of this industrial/business area appears below. Click on a link to view the organisation's profile:</p>
              <table  class="form-table">
                <?php do { ?>
                  <tr>
                    <td><a href="directory.php?directoryID=<?php echo $row_rsOccupiers['ID']; ?>&amp;refererDirectoryID=<?php echo intval($_GET['directoryID']); ?>" ><?php echo htmlentities($row_rsOccupiers['name'],ENT_COMPAT,"UTF-8"); ?></a><br />
                      <br /></td>
                  </tr>
                  <?php } while ($row_rsOccupiers = mysql_fetch_assoc($rsOccupiers)); ?>
              </table>
              <?php } // Show if recordset not empty ?>
            <?php if ($totalRows_rsOccupiers == 0) { // Show if recordset empty ?>
              <p>There are currently no occupiers.</p>
              <?php } // Show if recordset empty ?>
          </div>
          <div class="TabbedPanelsContent" id="directoryRelationshipTabContent" >There are currently no available lets.</div>
          <div class="TabbedPanelsContent">
            <table  class="form-table">
              <?php do { ?>
                <tr>
                  <td><a href="../photos/gallery/index.php?galleryID=<?php echo $row_rsGalleries['galleryID']; ?>"><?php echo htmlentities($row_rsGalleries['title'],ENT_COMPAT,"UTF-8"); ?></a></td>
                </tr>
                <?php } while ($row_rsGalleries = mysql_fetch_assoc($rsGalleries)); ?>
            </table>
          </div>
        </div>
      </div>
      <?php } ?>
      <?php if (isset($_GET['defaultTab'])) { echo '<script>
<!--
var TabbedPanels1 = new Spry.Widget.TabbedPanels("TabbedPanels1", {defaultTab:'.intval($_GET['defaultTab']).'});
//-->
    </script>'; } else { ?>
      <script>
<!--
var TabbedPanels1 = new Spry.Widget.TabbedPanels("TabbedPanels1");
//-->
      </script>
      <?php } ?>
      <?php if (isset($row_rsDirReferer['referer'])) { // referer exists ?>
      <p><a href="/directory/directory.php?directoryID=<?php echo $row_rsDirReferer['ID']; ?>&amp;defaultTab=4" class="link_back"><i class="glyphicon glyphicon-arrow-left"></i> <?php echo $row_rsDirReferer['referer']; ?></a></p>
      <?php } ?>
      <?php if(isset($_GET['returnURL']) && trim($_GET['returnURL']) !="") { ?>
      <p><a href="<?php echo htmlentities($_GET['returnURL']) ?>" class="link_back"><i class="glyphicon glyphicon-arrow-left"></i> Back</a></p>
      <?php } else { ?>
      <p><a href="/directory/index.php?categoryID=<?php echo $row_rsDirectory['categoryID']; ?>" class="link_back"><i class="glyphicon glyphicon-arrow-left"></i> <?php echo isset($row_rsDirectory['categoryname']) ? $row_rsDirectory['categoryname'] : "Back"; ?></a></p>
      <?php } ?>
      <?php if (isset($row_rsLoggedIn['ID']) && ($row_rsLoggedIn['ID'] == $row_rsDirectory['createdbyID'] || $row_rsLoggedIn['usertypeID'] >=8)) { ?>
        <p><a href="members/update_directory.php?directoryID=<?php echo $row_rsDirectory['ID']; ?>"  rel="nofollow">Edit these details</a></p>
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
mysql_free_result($rsDirectory);

mysql_free_result($rsLoggedIn);

mysql_free_result($rsPreferences);

mysql_free_result($rsDocuments);

mysql_free_result($rsDirectoryWeb);

mysql_free_result($rsOccupiers);

mysql_free_result($rsDirReferer);

mysql_free_result($rsDirectoryPrefs);

mysql_free_result($rsGalleries);

mysql_free_result($rsEvents);

mysql_free_result($rsContacts);
?>
