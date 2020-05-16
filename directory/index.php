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

$currentPage = $_SERVER["PHP_SELF"];

$varParentCatID_rsSubCategories = "0";
if (isset($_GET['categoryID'])) {
  $varParentCatID_rsSubCategories = $_GET['categoryID'];
}
$varRegionID_rsSubCategories = "0";
if (isset($regionID)) {
  $varRegionID_rsSubCategories = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsSubCategories = sprintf("SELECT directorycategory.ID, directorycategory.description FROM directorycategory LEFT JOIN directory ON (directory.categoryID = directorycategory.ID) WHERE (subcatofID = %s OR (subcatofID = 0 AND %s = 0)) AND directorycategory.statusID = 1 AND (directorycategory.regionID =0 OR directorycategory.regionID IS NULL OR directorycategory.regionID = %s OR %s = 0) GROUP BY directorycategory.ID ORDER BY description ASC", GetSQLValueString($varParentCatID_rsSubCategories, "int"),GetSQLValueString($varParentCatID_rsSubCategories, "int"),GetSQLValueString($varRegionID_rsSubCategories, "int"),GetSQLValueString($varRegionID_rsSubCategories, "int"));
$rsSubCategories = mysql_query($query_rsSubCategories, $aquiescedb) or die(mysql_error());
$row_rsSubCategories = mysql_fetch_assoc($rsSubCategories);
$totalRows_rsSubCategories = mysql_num_rows($rsSubCategories);

if(isset($_GET['pageNum_rsCatOrganisations'])) $_GET['pageNum_rsCatOrganisations'] = intval($_GET['pageNum_rsCatOrganisations']);
if(isset($_GET['totalRows_rsCatOrganisations'])) $_GET['totalRows_rsCatOrganisations'] = intval($_GET['totalRows_rsCatOrganisations']);



$maxRows_rsCatOrganisations = 50;
$pageNum_rsCatOrganisations = 0;
if (isset($_GET['pageNum_rsCatOrganisations'])) {
  $pageNum_rsCatOrganisations = $_GET['pageNum_rsCatOrganisations'];
}
$startRow_rsCatOrganisations = $pageNum_rsCatOrganisations * $maxRows_rsCatOrganisations;

$varCategoryID_rsCatOrganisations = "0";
if (isset($_GET['categoryID'])) {
  $varCategoryID_rsCatOrganisations = $_GET['categoryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsCatOrganisations = sprintf("SELECT directory.ID, name, description, directory.latitude, directory.longitude, imageURL FROM directory LEFT JOIN directoryincategory ON (directory.ID = directoryincategory.directoryID) WHERE  directory.statusID = 1 AND (directory.categoryID = %s OR  directoryincategory.categoryID = %s)   GROUP BY directory.ID ORDER BY name ASC", GetSQLValueString($varCategoryID_rsCatOrganisations, "int"),GetSQLValueString($varCategoryID_rsCatOrganisations, "int"));
$query_limit_rsCatOrganisations = sprintf("%s LIMIT %d, %d", $query_rsCatOrganisations, $startRow_rsCatOrganisations, $maxRows_rsCatOrganisations);
$rsCatOrganisations = mysql_query($query_limit_rsCatOrganisations, $aquiescedb) or die(mysql_error());
$row_rsCatOrganisations = mysql_fetch_assoc($rsCatOrganisations);

if (isset($_GET['totalRows_rsCatOrganisations'])) {
  $totalRows_rsCatOrganisations = $_GET['totalRows_rsCatOrganisations'];
} else {
  $all_rsCatOrganisations = mysql_query($query_rsCatOrganisations);
  $totalRows_rsCatOrganisations = mysql_num_rows($all_rsCatOrganisations);
}
$totalPages_rsCatOrganisations = ceil($totalRows_rsCatOrganisations/$maxRows_rsCatOrganisations)-1;

$colname_rsThisCategory = "0";
if (isset($_GET['categoryID'])) {
  $colname_rsThisCategory = $_GET['categoryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisCategory = sprintf("SELECT directorycategory.description AS categoryname, directorycategory.ID FROM directorycategory WHERE ID = %s", GetSQLValueString($colname_rsThisCategory, "int"));
$rsThisCategory = mysql_query($query_rsThisCategory, $aquiescedb) or die(mysql_error());
$row_rsThisCategory = mysql_fetch_assoc($rsThisCategory);
$totalRows_rsThisCategory = mysql_num_rows($rsThisCategory);

$varSubCatOf_rsParentCategory = "-1";
if (isset($row_rsThisCategory['subcatofID'])) {
  $varSubCatOf_rsParentCategory = $row_rsThisCategory['subcatofID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsParentCategory = sprintf("SELECT directorycategory.`description` FROM directorycategory WHERE directorycategory.ID = %s", GetSQLValueString($varSubCatOf_rsParentCategory, "int"));
$rsParentCategory = mysql_query($query_rsParentCategory, $aquiescedb) or die(mysql_error());
$row_rsParentCategory = mysql_fetch_assoc($rsParentCategory);
$totalRows_rsParentCategory = mysql_num_rows($rsParentCategory);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT googlemapsAPI, defaultzoom, defaultlongitude, defaultlatitude FROM preferences";
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsDirectoryPrefs = "SELECT * FROM directoryprefs";
$rsDirectoryPrefs = mysql_query($query_rsDirectoryPrefs, $aquiescedb) or die(mysql_error());
$row_rsDirectoryPrefs = mysql_fetch_assoc($rsDirectoryPrefs);
$totalRows_rsDirectoryPrefs = mysql_num_rows($rsDirectoryPrefs);

$queryString_rsCatOrganisations = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsCatOrganisations") == false && 
        stristr($param, "totalRows_rsCatOrganisations") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsCatOrganisations = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsCatOrganisations = sprintf("&totalRows_rsCatOrganisations=%d%s", $totalRows_rsCatOrganisations, $queryString_rsCatOrganisations);
?>
<?php $accesslevel = $row_rsDirectoryPrefs['accesslevel']; require_once('../members/includes/restrictaccess.inc.php'); ?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = $row_rsDirectoryPrefs['directoryname']; $pageTitle .= ($totalRows_rsThisCategory > 0) ? " - ".$row_rsThisCategory['categoryname'] : ""; echo $pageTitle." | ".$site_name; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<?php 

$latitude = isset($_GET['latitude']) ? $_GET['latitude'] : $row_rsPreferences['defaultlatitude']; 
$longitude =  isset($_GET['longitude']) ? $_GET['longitude'] : $row_rsPreferences['defaultlongitude'];
$magnification = isset($_GET['magnification']) ? $_GET['magnification'] : $row_rsPreferences['defaultzoom']; 

?>
<script src="//maps.google.com/?file=api&amp;v=2.x&amp;key=<?php echo isset($googlemapsAPI) ? $googlemapsAPI : $row_rsPreferences['googlemapsAPI']; ?>"></script>
<script src="/core/scripts/googlemaps/googlemap.js" ></script>
<script>
//<![CDATA[

var map = null;
var geocoder = null;
var latsgn = 1;
var lgsgn = 1;
var zm = 0; 
var marker = null;

function init() {
if (GBrowserIsCompatible()) {
map = new GMap2(document.getElementById("googlemap"));
map.setCenter(new GLatLng(<?php echo $latitude; ?>, <?php echo $longitude; ?>), <?php echo $magnification; ?>);
map.setMapType(G_NORMAL_MAP);zm=1;
map.addControl(new GSmallMapControl());
//map.addControl(new MapTypeControl());
map.addControl(new GScaleControl());
map.enableScrollWheelZoom();
map.disableDoubleClickZoom();
geocoder = new GClientGeocoder();
<?php if($totalRows_rsCatOrganisations>0) { do { if(isset($row_rsCatOrganisations['latitude'])) { ?>
marker<?php echo $row_rsCatOrganisations['ID']; ?> = new GMarker(new GLatLng(<?php echo $row_rsCatOrganisations['latitude']; ?>, <?php echo $row_rsCatOrganisations['longitude']; ?>), {title:"<?php echo $row_rsCatOrganisations['name']; ?>"});
map.addOverlay(marker<?php echo $row_rsCatOrganisations['ID']; ?>);
GEvent.addListener(marker<?php echo $row_rsCatOrganisations['ID']; ?>, "click", function()
 {
window.location.href='/directory/directory.php?directoryID=<?php echo $row_rsCatOrganisations['ID']; ?>&defaultTab=<?php echo (@$_GET['categoryID']==1) ? "3" : "0"; ?><?php echo isset($_GET['categoryID']) ? "&referCatID=".intval($_GET['categoryID']) : ""; ?>';
 });
<?php } } while ($row_rsCatOrganisations = mysql_fetch_assoc($rsCatOrganisations)); mysql_data_seek($rsCatOrganisations,0); $row_rsCatOrganisations = mysql_fetch_assoc($rsCatOrganisations);
 } ?>

}}


function createMarker(point, html) 
{
 var marker = new GMarker(point);
 GEvent.addListener(marker, "click", function()
 {
 marker.openInfoWindowHtml(html);
 });
 return marker;
}

addListener("load",init);

//]]>
</script>
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" -->
    <div id="pageDirectoryIndex" class="container pageBody">
      <div class="crumbs"><div><span class="you_are_in">You are in: </span><a href="/">Home</a><?php if ($totalRows_rsThisCategory > 0) {  ?><span class="separator">&nbsp;&rsaquo;&nbsp;</span><a href="/directory/index.php"><?php echo $row_rsDirectoryPrefs['directoryname']; ?></a><span class="separator">&nbsp;&rsaquo;&nbsp;</span><?php echo $row_rsThisCategory['categoryname'];  } else {  ?><span class="separator">&nbsp;&rsaquo;&nbsp;</span><?php echo $row_rsDirectoryPrefs['directoryname']; ?><?php } ?></div></div>
      
       
        
      
     
        <h1><?php echo $row_rsDirectoryPrefs['directoryname']; ?>: <?php echo htmlentities($row_rsThisCategory['categoryname'],ENT_COMPAT,"UTF-8"); ?></h1> <div class="googlemap" id="googlemap"></div>
        <?php if ($totalRows_rsSubCategories > 0) { // Show if recordset not empty ?>
          <?php if ($totalRows_rsThisCategory == 0) { // Show if recordset empty ?>
            <h2>Main Categories:</h2>
            <?php } // Show if recordset empty ?>
          <ul id="directorySubCategories">
            <?php do { ?>
              <li><a href="index.php?categoryID=<?php echo $row_rsSubCategories['ID']; ?>"><?php echo htmlentities($row_rsSubCategories['description'],ENT_COMPAT,"UTF-8"); ?></a></li>
              <?php } while ($row_rsSubCategories = mysql_fetch_assoc($rsSubCategories)); ?>
          </ul>
          <?php } // Show if recordset not empty ?>
        <?php if ($totalRows_rsCatOrganisations == 0 && $totalRows_rsSubCategories == 0) { // Show if recordset empty and not root cat?>
        <p>This category is empty.</p>
        <?php } // Show if recordset empty ?>
        <?php if ($totalRows_rsCatOrganisations > 0) { // Show if recordset not empty ?>
          <p class="text-muted">Entries <?php echo ($startRow_rsCatOrganisations + 1) ?> to <?php echo min($startRow_rsCatOrganisations + $maxRows_rsCatOrganisations, $totalRows_rsCatOrganisations) ?> of <?php echo $totalRows_rsCatOrganisations ?> </p>
          <ul id="directoryEntries">
            <?php do { ?>
              <li>
                <p><a href="directory.php?directoryID=<?php echo $row_rsCatOrganisations['ID']; ?>&referCatID=<?php echo intval($_GET['categoryID']); ?>&defaultTab=<?php echo ($_GET['categoryID']==1) ? "3" : "0"; ?>">
                  <?php if (isset($row_rsCatOrganisations['imageURL'])) { ?>
                  <div class="fb_avatar fltlft" style="background-image:url(<?php echo getImageURL($row_rsCatOrganisations['imageURL'], "thumb"); ?>);"><?php echo htmlentities($row_rsCatOrganisations['name'],ENT_COMPAT,"UTF-8"); ?></div>
                    
                    <?php } ?>
                  <strong><?php echo htmlentities($row_rsCatOrganisations['name'],ENT_COMPAT,"UTF-8"); ?></strong></a><br />
                  <?php echo strlen($row_rsCatOrganisations['description']) <255 ? htmlentities($row_rsCatOrganisations['description'],ENT_COMPAT,"UTF-8") : htmlentities(substr($row_rsCatOrganisations['description'],0,strrpos(substr($row_rsCatOrganisations['description'],0,255)," "))."&hellip;",ENT_COMPAT,"UTF-8"); ?> <a href="directory.php?directoryID=<?php echo $row_rsCatOrganisations['ID']; ?>&referCatID=<?php echo isset($_GET['categoryID']) ? intval($_GET['categoryID']) : 0; ?>" >More info...</a></p>
              </li>
              <?php } while ($row_rsCatOrganisations = mysql_fetch_assoc($rsCatOrganisations)); ?>
          </ul>
          <?php } // Show if recordset not empty ?>
        <table class="form-table">
          <tr>
            <td><?php if ($pageNum_rsCatOrganisations > 0) { // Show if not first page ?>
                <a href="<?php printf("%s?pageNum_rsCatOrganisations=%d%s", $currentPage, 0, $queryString_rsCatOrganisations); ?>">First</a>
                <?php } // Show if not first page ?></td>
           <td><?php if ($pageNum_rsCatOrganisations > 0) { // Show if not first page ?>
                <a href="<?php printf("%s?pageNum_rsCatOrganisations=%d%s", $currentPage, max(0, $pageNum_rsCatOrganisations - 1), $queryString_rsCatOrganisations); ?>" rel="prev">Previous</a>
                <?php } // Show if not first page ?></td>
            <td><?php if ($pageNum_rsCatOrganisations < $totalPages_rsCatOrganisations) { // Show if not last page ?>
                <a href="<?php printf("%s?pageNum_rsCatOrganisations=%d%s", $currentPage, min($totalPages_rsCatOrganisations, $pageNum_rsCatOrganisations + 1), $queryString_rsCatOrganisations); ?>" rel="next">Next</a>
                <?php } // Show if not last page ?></td>
            <td><?php if ($pageNum_rsCatOrganisations < $totalPages_rsCatOrganisations) { // Show if not last page ?>
                <a href="<?php printf("%s?pageNum_rsCatOrganisations=%d%s", $currentPage, $totalPages_rsCatOrganisations, $queryString_rsCatOrganisations); ?>">Last</a>
                <?php } // Show if not last page ?></td>
          </tr>
        </table>
        <?php if (isset($_GET['categoryID']) && $_GET['categoryID'] !=0 && $row_rsDirectoryPrefs['allowsuggestions']==1) { ?>
        <p class="suggestboxout"><a href="members/add_directory.php?categoryID=<?php echo $row_rsThisCategory['ID']; ?>" rel="nofollow">Suggest an organisation for this category.</a></p>
        <?php } ?>
        <?php if ($row_rsThisCategory['ID'] >=1) { ?>
        <p><a href="index.php?categoryID=<?php echo isset($row_rsParentCategory['ID']) ? $row_rsParentCategory['ID'] : "0"; ?>" class="link_back"><i class="glyphicon glyphicon-arrow-left"></i> Back to
          <?php if (isset($row_rsParentCategory['ID'])) { echo $row_rsParentCategory['title']; } else echo "directory index";  ?>
          </a></p>
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
mysql_free_result($rsSubCategories);

mysql_free_result($rsCatOrganisations);

mysql_free_result($rsThisCategory);

mysql_free_result($rsParentCategory);

mysql_free_result($rsPreferences);

mysql_free_result($rsDirectoryPrefs);

?>