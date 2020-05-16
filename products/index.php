<?php chdir(dirname(__FILE__)); // if used as include in 404 page
require_once('../Connections/aquiescedb.php'); ?><?php require_once('../core/seo/includes/seo.inc.php'); ?>
<?php /* 
Major CSS changes h2 heading - replaced by h1
h2 category titles replaced by h3

*/
require_once('includes/productHeader.inc.php'); ?>
<?php require_once('../members/includes/userfunctions.inc.php'); ?>
<?php $regionID = isset($regionID) ? $regionID : 1; ?>
<?php require_once('../core/includes/framework.inc.php'); ?>
<?php require_once('includes/productFunctions.inc.php'); ?>
<?php require_once('includes/products.inc.php'); ?>
<?php



$_GET['manufacturerID'] = isset($_REQUEST['manufacturerID']) ? $_REQUEST['manufacturerID'] : "";
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

// variable sent from 404 or take REQUEST
$categoryID = isset($categoryID ) ? $categoryID  : (isset($_REQUEST['categoryID']) ? $_REQUEST['categoryID'] : 0);


$currentPage = $_SERVER["PHP_SELF"];

$varRegionID_rsPreferences = "1";
if (isset($regionID)) {
  $varRegionID_rsPreferences = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = sprintf("SELECT * FROM preferences WHERE ID = %s", GetSQLValueString($varRegionID_rsPreferences, "int"));
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);


$varCategoryID_rsThisCategory = "0";
if (isset($categoryID)) {
  $varCategoryID_rsThisCategory = $categoryID;
}
$varRegionID_rsThisCategory = "1";
if (isset($regionID)) {
  $varRegionID_rsThisCategory = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisCategory = sprintf("SELECT productcategory.*, parentcategory.title AS parenttitle, parentcategory.ID as parentID, parentcategory.longID AS parentlongID, parentcategory.imageURL AS parentimageURL, parentcategory.imageURL2 AS parentimageURL2 FROM productcategory LEFT JOIN productcategory AS parentcategory ON (productcategory.subcatofID = parentcategory.ID) WHERE (productcategory.regionID = 0 OR productcategory.regionID = %s) AND (productcategory.ID = %s OR productcategory.longID = %s)", GetSQLValueString($varRegionID_rsThisCategory, "int"),GetSQLValueString($varCategoryID_rsThisCategory, "text"),GetSQLValueString($varCategoryID_rsThisCategory, "text"));
$rsThisCategory = mysql_query($query_rsThisCategory, $aquiescedb) or die(mysql_error());
$row_rsThisCategory = mysql_fetch_assoc($rsThisCategory);
$totalRows_rsThisCategory = mysql_num_rows($rsThisCategory);



if(isset($row_rsThisCategory['redirectURL']) && trim($row_rsThisCategory['redirectURL'])!="") { 
	header("location: ".$row_rsThisCategory['redirectURL']); exit;
}

// GET takes precedence over $_POST so convert below for as well as convert longID to short ID

$_GET['categoryID'] = ($categoryID!="-1") ? $row_rsThisCategory['ID'] : $categoryID; 

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);



$varParentCategory_rsSubCategories = "0";
if (isset($_GET['categoryID'])) {
  $varParentCategory_rsSubCategories = $_GET['categoryID'];
}
$varUserID_rsSubCategories = "0";
if (isset($row_rsLoggedIn['ID'])) {
  $varUserID_rsSubCategories = $row_rsLoggedIn['ID'];
}
$varUserGroup_rsSubCategories = "0";
if (isset($_SESSION['MM_UserGroup'])) {
  $varUserGroup_rsSubCategories = $_SESSION['MM_UserGroup'];
}
$varRegionID_rsSubCategories = "1";
if (isset($regionID)) {
  $varRegionID_rsSubCategories = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsSubCategories = sprintf("SELECT productcategory.ID AS catID, productcategory.longID, productcategory.accesslevel,productcategory.summary,  productcategory.categorysale, productcategory.groupID, productcategory.title, productcategory.description, productcategory.colour, productcategory.imageURL,usergroupmember.userID,  (SELECT imageURL FROM product LEFT JOIN productincategory ON (product.ID = productincategory.productID) WHERE productcategory.regionID = %s AND imageURL IS NOT NULL AND statusID = 1 AND (product.productcategoryID =  productcategory.ID OR productincategory.categoryID = productcategory.ID) ORDER BY ordernum ASC LIMIT 1 ) AS productimageURL FROM productcategory LEFT JOIN usergroupmember ON (productcategory.groupID  = usergroupmember.groupID AND usergroupmember.userID = %s)  WHERE productcategory.regionID = %s AND productcategory.subcatofID = %s AND productcategory.statusID = 1 AND productcategory.showinmenu = 1 AND (productcategory.groupID = 0 OR  userID IS NOT NULL  OR %s>=7) AND (productcategory.accesslevel=0 OR productcategory.accesslevel>=%s OR %s>=7) GROUP BY productcategory.ID ORDER BY productcategory.ordernum", GetSQLValueString($varRegionID_rsSubCategories, "int"),GetSQLValueString($varUserID_rsSubCategories, "int"),GetSQLValueString($varRegionID_rsSubCategories, "int"),GetSQLValueString($varParentCategory_rsSubCategories, "int"),GetSQLValueString($varUserGroup_rsSubCategories, "int"),GetSQLValueString($varUserGroup_rsSubCategories, "int"),GetSQLValueString($varUserGroup_rsSubCategories, "int"));
$rsSubCategories = mysql_query($query_rsSubCategories, $aquiescedb) or die(mysql_error());
$row_rsSubCategories = mysql_fetch_assoc($rsSubCategories);
$totalRows_rsSubCategories = mysql_num_rows($rsSubCategories);



$colname_rsThisManufacturer = "-1";
if (isset($_GET['manufacturerID'])) {
  $colname_rsThisManufacturer = $_GET['manufacturerID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisManufacturer = sprintf("SELECT ID, longID, manufacturername, `description`, imageURL FROM productmanufacturer  WHERE ID = %s OR longID LIKE %s", GetSQLValueString($colname_rsThisManufacturer, "int"),GetSQLValueString($colname_rsThisManufacturer, "text"));
$rsThisManufacturer = mysql_query($query_rsThisManufacturer, $aquiescedb) or die(mysql_error());
$row_rsThisManufacturer = mysql_fetch_assoc($rsThisManufacturer);
$totalRows_rsThisManufacturer = mysql_num_rows($rsThisManufacturer);
//if($_SESSION['MM_UserGroup']==10) die($_SERVER['QUERY_STRING'].$query_rsThisManufacturer);
$varManufacturerID_rsIndexManufacturers = "-1";
if (isset($_GET['manufacturerID'])) {
  $varManufacturerID_rsIndexManufacturers = $_GET['manufacturerID'];
}
$varCategoryID_rsIndexManufacturers = "0";
if (isset($_GET['categoryID'])) {
  $varCategoryID_rsIndexManufacturers = $_GET['categoryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsIndexManufacturers = sprintf("SELECT productmanufacturer.* FROM productmanufacturer LEFT JOIN productmanufacturer  AS parentmanufacturer ON (productmanufacturer.subsidiaryofID = parentmanufacturer.ID) LEFT JOIN product ON (product.manufacturerID = productmanufacturer.ID) WHERE productmanufacturer.statusID = 1 AND (parentmanufacturer.ID = %s OR parentmanufacturer.longID LIKE %s) AND (%s = 0 OR product.productcategoryID = %s ) GROUP BY productmanufacturer.ID", GetSQLValueString($varManufacturerID_rsIndexManufacturers, "text"),GetSQLValueString($varManufacturerID_rsIndexManufacturers, "text"),GetSQLValueString($varCategoryID_rsIndexManufacturers, "text"),GetSQLValueString($varCategoryID_rsIndexManufacturers, "text"));
$rsIndexManufacturers = mysql_query($query_rsIndexManufacturers, $aquiescedb) or die(mysql_error());
$row_rsIndexManufacturers = mysql_fetch_assoc($rsIndexManufacturers);
$totalRows_rsIndexManufacturers = mysql_num_rows($rsIndexManufacturers);

$varRegionID_rsHeaderPromo = "1";
if (isset($regionID)) {
  $varRegionID_rsHeaderPromo = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsHeaderPromo = sprintf("SELECT promotitle, imageURL, linkURL FROM productpromo WHERE promocode IS NULL AND statusID = 1 AND imageURL IS NOT NULL AND  (startdatetime IS NULL OR startdatetime < '".date('Y-m-d H:i:s')."') AND (enddatetime IS NULL OR enddatetime > '".date('Y-m-d H:i:s')."') AND (regionID = 0  OR regionID = %s) ORDER BY RAND()  LIMIT 1", GetSQLValueString($varRegionID_rsHeaderPromo, "int"));
$rsHeaderPromo = mysql_query($query_rsHeaderPromo, $aquiescedb) or die(mysql_error());
$row_rsHeaderPromo = mysql_fetch_assoc($rsHeaderPromo);
$totalRows_rsHeaderPromo = mysql_num_rows($rsHeaderPromo);




if(isset($row_rsProductPrefs['shopfrontURL']) && empty($_REQUEST)  && ($row_rsThisCategory['ID'] == 0 || !isset($row_rsThisCategory['ID']))) { // home page, no search and shop front set, so go there
	header("location: ".$row_rsProductPrefs['shopfrontURL']); exit;
}
if ($totalRows_rsSubCategories == 1 && $totalRows_rsThisCategory < 1) { // if just one sub category and no products, jump to within that category....

	$url = productLink(0, "", $row_rsSubCategories['catID'], 			$row_rsSubCategories['longID']);
	header("location: ".$url); exit;
} 
$defaultImage = isset($row_rsProductPrefs['defaultImageURL']) ? "/Uploads/".$row_rsProductPrefs['imagesize_index'].$row_rsProductPrefs['defaultImageURL'] : "/products/images/".$row_rsProductPrefs['imagesize_index']."no_image.gif";

if(!defined("SHOP_COLUMNS")) {
	define("SHOP_COLUMNS",2);

}

$page = isset($_GET['pageNum_rsProduct']) ? $_GET['pageNum_rsProduct']+1 : 1;
$showproducts = isset($_GET['showproducts']) ? $_GET['showproducts'] : "";
$productclass = (isset($productclass) && $productclass != "") ? $productclass : (defined("PRODUCT_CLASS") ? PRODUCT_CLASS : "col-md-4 col-sm-3");

$body_class= isset($body_class) ?  $body_class : "";
$body_class .= isset($row_rsThisCategory['parentID']) ? " productparentcategory".$row_rsThisCategory['parentID']." " : "";
$body_class .= " productcategory".$row_rsThisCategory['ID']." ";


?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!DOCTYPE html>
<html class="" lang="en"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php  $pageTitle = "Shop"; $pageTitle .= isset($row_rsThisCategory['title']) ? " - ".$row_rsThisCategory['title'] : ""; $pageTitle .= isset($row_rsThisManufacturer['manufacturername']) ? " - ".$row_rsThisManufacturer['manufacturername'] : ""; $pageTitle .=  $page>1 ? " (page ".$page.")" : "";echo isset($row_rsThisCategory['seotitle']) ? $row_rsThisCategory['seotitle'] :  $pageTitle." | ".$site_name; ?>
</title>
<!-- InstanceEndEditable -->
<!--[if IE]><![endif]-->
<?php require_once('../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<?php $canonicalURL = productLink("","", $row_rsThisCategory['ID'], $row_rsThisCategory['longID'],$row_rsThisManufacturer['ID'],$row_rsThisManufacturer['longID']); ?>
<link rel="canonical" href="<?php  $canonicalURL = getProtocol()."://".$_SERVER['HTTP_HOST'].$canonicalURL; $canonicalURL = htmlentities($canonicalURL, ENT_COMPAT, "UTF-8"); echo $canonicalURL; ?>" />
<?php if($row_rsThisCategory['noindex']==1 || isset($_GET['sortby']) || isset($_GET['noindex'])) {
	echo "<meta name=\"robots\" content=\"noindex, nofollow\">";
} else { ?>
<meta name="Description" content="<?php echo $pageTitle." - ".$row_rsThisCategory['metadescription']; // adding page title to ensure unique meta descriptions for filter searches ?>" />
<meta name="Keywords" content="<?php echo $row_rsThisCategory['metakeywords']; ?>" />
<meta property="og:url" content="<?php  echo $canonicalURL; ?>"/>
<meta property="og:site_name" content="<?php echo $site_name; ?>"/>
<meta property="og:title" content="<?php echo $pageTitle; ?>" />
<meta property="og:description" content="<?php echo $row_rsThisCategory['metadescription']; ?>" />
<meta property="og:type" content="article" />
<!-- TBI add facebook profiles to CMS
<meta property="article:author" content="https://www.facebook.com/fareedzakaria" />
<meta property="article:publisher" content="https://www.facebook.com/cnn" />-->
<?php } ?>
<link href="/products/css/defaultProducts.css" rel="stylesheet"  />
<script src="/core/scripts/checkbox/checkboxes.js"></script>
<script src="/products/scripts/productFunctions.js"></script>
<script ><!--
var checkboxForm = 'productFilterForm';




--></script>
<!-- InstanceEndEditable -->
</head>
<body id="OffTemplate" class="bootstrap <?php echo $body_class;  ?>">
<?php require_once('../local/includes/header.inc.php'); ?>
<main id="content"><!-- InstanceBeginEditable name="Body" -->
<section> <div id="productsIndexPage" class="container pageBody clearfix optionsview<?php echo $row_rsProductPrefs['indexoptionsdisplay']; ?> productcategory<?php echo $row_rsThisCategory['ID']; ?>">
  <?php if(thisUserHasAccess($row_rsThisCategory['accesslevel'], $row_rsThisCategory['groupID'], $row_rsLoggedIn['ID'])) { // has access ?>
 
    <div class="crumbs">
      <div><span class="you_are_in">You are in: </span>
      
      
      <ol itemscope itemtype="http://schema.org/BreadcrumbList">
            <li itemprop="itemListElement" itemscope
      itemtype="http://schema.org/ListItem" class="home"><a itemprop="item" href="/"><span itemprop="name">Home</span></a>
              <meta itemprop="position" content="<?php $position=1; echo $position; ?>" />
            </li>
            
            <li itemprop="itemListElement" itemscope
      itemtype="http://schema.org/ListItem" class="productshome" ><a itemprop="item" href="/products/"><span itemprop="name"><?php echo isset($row_rsProductPrefs['shopTitle']) ? $row_rsProductPrefs['shopTitle'] : "Shop";  ?></span></a>
      <meta itemprop="position" content="<?php  echo $position++; ?>" />
      </li>
      
      
        <?php if (isset($row_rsThisCategory['parenttitle'])) { ?>
        <li itemprop="itemListElement" itemscope
      itemtype="http://schema.org/ListItem" ><a itemprop="item"  href="<?php echo productLink(0, "", $row_rsThisCategory['parentID'], $row_rsThisCategory['parentlongID']); ?>"><span itemprop="name"><?php echo $row_rsThisCategory['parenttitle']; ?></span></a>
      <meta itemprop="position" content="<?php  echo $position++; ?>" />
      </li>
        <?php }  if (isset($row_rsThisCategory['title'])) { ?> 
        <li itemprop="itemListElement" itemscope
      itemtype="http://schema.org/ListItem" >
     <a itemprop="item" href="<?php echo $canonicalURL; ?>"><span itemprop="name">
      <?php echo $row_rsThisCategory['title']; ?></span></a>
      <meta itemprop="position" content="<?php echo $position++; ?>" />
	  </li><?php } ?></ol>
      </div>
    </div>
    
    
    
    <!-- promo-->
    <?php if(isset($row_rsHeaderPromo['imageURL'])) { 
 $promoLink = isset($row_rsHeaderPromo['linkURL']) ? $row_rsHeaderPromo['linkURL'] : "javascript:void(0);"; ?>
    <div id="productPromo"><a href="<?php echo $promoLink; ?>" title="<?php echo $row_rsHeaderPromo['promotitle']; ?>"><img src="<?php echo getImageURL($row_rsHeaderPromo['imageURL']); ?>" alt="<?php echo $row_rsHeaderPromo['promotitle']; ?>"  /></a> </div>
    <?php } ?>
    <!-- end promo -->
    
    
    
    <h1 id="productCategoryTitle" class="productcategory<?php echo $row_rsThisCategory['ID']; ?>"><?php echo ($row_rsThisCategory['ID']==0) ? htmlentities($row_rsProductPrefs['shopTitle'], ENT_COMPAT, "UTF-8") : htmlentities($row_rsThisCategory['title'], ENT_COMPAT, "UTF-8"); ?><?php echo isset($row_rsThisManufacturer['manufacturername'] ) ? " - ".$row_rsThisManufacturer['manufacturername'] : ""; ?></h1>
    <?php if(is_readable('../local/includes/categoryMenu.inc.php')) { 
	require_once('../local/includes/categoryMenu.inc.php'); } else { ?>
    <?php require_once('includes/categoryMenu.inc.php'); ?>
    <?php } ?>
    <div id="productContent">
      
      <?php require_once('../core/includes/alert.inc.php'); ?>
      <?php if ($totalRows_rsThisCategory > 0) { // Show if recordset not empty ?>
        <?php $description = "<div id=\"productsIndexCatDescription\">".$row_rsThisCategory['description']."</div>"; 
	$description = isset($row_rsThisManufacturer['description'] ) ? "<div class=\"manufacturer\">".$row_rsThisManufacturer['description']."</div>" : $description;
	if($row_rsProductPrefs['categorytextposition']==1) echo $description; } // Show if recordset not empty ?>
      <?php if(isset($_REQUEST['filtertitle'])) { // product filter ?>
      <h2 id="productCategoryTitle"><?php echo htmlentities($_REQUEST['filtertitle'], ENT_COMPAT, "UTF-8"); ?></h2>
      <?php } ?>
      <?php if(is_readable('../local/includes/productFilter.inc.php')) { 
	require_once('../local/includes/productFilter.inc.php'); } ?>
      <?php $regionID = isset($regionID) ? $regionID : 1;
$categoryID = isset($_GET['categoryID']) ? intval($_GET['categoryID']) : -1;
$manufacturerID = isset($_REQUEST['manufacturerID']) ? $_REQUEST['manufacturerID'] : array();
$tags = isset($_REQUEST['tagID']) ? $_REQUEST['tagID'] : array();
$versions = isset($_REQUEST['version']) ? $_REQUEST['version'] : array();
$finishes = isset($_REQUEST['finish']) ? $_REQUEST['finish'] : array();
$prices = isset($_REQUEST['price']) ? $_REQUEST['price'] : array();
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : (isset($row_rsProductPrefs['defaultsort']) ? $row_rsProductPrefs['defaultsort']   : "ordernum");
 if($row_rsProductPrefs['subcatsposition']==2) { // products above sub cats?>
      <!-- products -->
      <div id="products-container">
        <?php 
echo getProducts($regionID, $sortby, $categoryID, $manufacturerID, $count = 0, $page, true, 0, "", $tags, $versions , $finishes, $prices, $showproducts,"",$row_rsProductPrefs['indexoptionsdisplay']);
?>
      </div>
      <!-- end products -->
      <?php } ?>
      <?php if(!isset($_REQUEST['hidecats'])) { ?>
      <!---- SUB CATEGORIES --->
      
      <?php if ($totalRows_rsSubCategories > 0) { // Show if recordset not empty and not to hide
  $row = 1;  ?>
      <div id = "productCategories" class="row subcategories productCategory<?php echo $row_rsThisCategory['ID']; ?>"> <a id="subcategories"></a>
        <?php  $item = 0; do { $item ++ ?>
        <div class="shopItem item<?php echo $item;  echo ($row_rsSubCategories['categorysale']==1 || $row_rsThisCategory['categorysale']==1 ) ? " sale" : ""; ?> rank<?php echo $row_rsSubCategories['accesslevel']; ?> group<?php echo $row_rsSubCategories['groupID']; echo " ".$productclass; ?>" >
          <div>
            <div class="producttext">
              <h3><a href="<?php echo productLink(0, "", $row_rsSubCategories['catID'], $row_rsSubCategories['longID']); ?>" title="View <?php echo $row_rsSubCategories['title']; ?> range..."><?php echo htmlentities($row_rsSubCategories['title'], ENT_COMPAT, "UTF-8"); ?></a></h3>
            </div>
            <div class="productCatImg"><a href="<?php echo productLink(0, "", $row_rsSubCategories['catID'], $row_rsSubCategories['longID']); ?>" title="View <?php echo $row_rsSubCategories['title']; ?> range..."><img src="<?php  $imageURL = (isset($row_rsSubCategories['imageURL']) && $row_rsSubCategories['imageURL']!="") ? $row_rsSubCategories['imageURL'] : $row_rsSubCategories['productimageURL'] ; echo getImageURL($imageURL,$row_rsProductPrefs['imagesize_category']); ?>" alt="<?php echo isset($row_rsSubCategories['regionTitle']) ? htmlentities($row_rsSubCategories['regionTitle'], ENT_CMPAT, "UTF-8") : htmlentities($row_rsSubCategories['title'], ENT_COMPAT, "UTF-8"); ?>" class="<?php echo $row_rsProductPrefs['imagesize_category']; ?>" /></a>
              <div class="productImageOverlay">
                <?php if(isset($row_rsProductPrefs['imageOverlayURL'])) { ?>
                <img src = "/Uploads/<?php echo $row_rsProductPrefs['imageOverlayURL']; ?>"  alt="Overlay image" />
                <?php } ?>
              </div>
              <div class="summary"><?php echo isset($row_rsSubCategories['summary']) ? nl2br(htmlentities($row_rsSubCategories['summary'], ENT_COMPAT, "UTF-8")) : ""; ?></div>
            </div>
          </div>
        </div>
        
        <!-- end item -->
        <?php } while ($row_rsSubCategories = mysql_fetch_assoc($rsSubCategories)); ?>
      </div>
      <?php } // Show if recordset not empty ?>
      
      <!-- MANUFACTURERS -->
      <?php if ($totalRows_rsIndexManufacturers > 0) { // Show if recordset not empty and not to hide
  $row = 1; ?>
      <div id = "productCategories" class="manufacturers productCategory<?php echo $row_rsThisCategory['ID']; ?>">
        <?php  $item = 0; do { $item ++ ;?>
        <a href="<?php echo productLink(0, "", $row_rsThisCategory['ID'], $row_rsThisCategory['longID'], $row_rsIndexManufacturers['ID'],$row_rsIndexManufacturers['longID']);  ?>" title="View <?php echo htmlentities($row_rsSubCategories['title'],  ENT_COMPAT, "UTF-8"); ?> range...">
        
        <div class="shopItem item<?php echo $item; echo ($row_rsIndexManufacturers['manufacturersale']==1) ? " sale" : "";  echo " ".$productclass; ?>" >
          <div>
            <div class="producttext">
            
            
        
          <h3><a href="<?php echo productLink(0, "", $row_rsThisCategory['ID'], $row_rsThisCategory['longID'], $row_rsIndexManufacturers['ID'],$row_rsIndexManufacturers['longID']);  ?>" title="View <?php echo htmlentities($row_rsSubCategories['title'],  ENT_COMPAT, "UTF-8"); ?> range..."><?php echo $row_rsIndexManufacturers['manufacturername']; ?></a></h3>
        </div>
        <div class="productCatImg"><img src="<?php  $imageURL = (isset($row_rsIndexManufacturers['imageURL']) && $row_rsIndexManufacturers['imageURL']!="") ? $row_rsIndexManufacturers['imageURL'] : "" ; echo getImageURL($imageURL,$row_rsProductPrefs['imagesize_category']); ?>" alt="<?php echo $row_rsIndexManufacturers['manufacturername']; ?>" class="<?php echo $row_rsProductPrefs['imagesize_category']; ?>" />
          <div class="productImageOverlay">
            <?php if(isset($row_rsProductPrefs['imageOverlayURL'])) { ?>
            <img src = "/Uploads/<?php echo $row_rsProductPrefs['imageOverlayURL']; ?>" alt="Overlay Image"  />
            <?php } ?>
          </div>
          
          
          <div class="summary"><?php //echo $row_rsIndexManufacturers['description']; ?></div>
            </div>
          </div>
        </div>
          
          
       
        
        <!-- end item -->
        <?php } while ($row_rsIndexManufacturers = mysql_fetch_assoc($rsIndexManufacturers)); ?>
      </div>
      <?php } // Show if recordset not empty ?>
      
      <!-- end  SUB categories -->
      
      <?php } ?>
      <?php if($row_rsProductPrefs['subcatsposition']==1) { // products below sub cats?>
      <!-- products -->
      <div id="products-container">
        <?php 
echo getProducts($regionID, $sortby, $categoryID, $manufacturerID, $count = 0, $page, true, 0, "", $tags, $versions , $finishes, $prices, $showproducts); ?>
      </div>
      <!-- end products -->
      <?php } ?>
      <?php 
if(isset($description) && $row_rsProductPrefs['categorytextposition']==2) echo $description;

?>
    </div>
    <!-- end products content-->
    <div id="productsviewed-container">
      <?php  require_once('includes/viewedProducts.inc.php'); ?>
    </div>
 
  <?php } else { // end has access ?>
  <p class="message alert alert-info" role="alert">Sorry, you do not have access to this section of the shop. You may need to <a href="../login/index.php?accesscheck=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>">log in</a>.</p>
  <?php } ?> </div>
</section>
<!-- end productsIndexPage --> 
<!-- InstanceEndEditable --></main>
<?php require_once('../local/includes/footer.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php

mysql_free_result($rsThisCategory);

mysql_free_result($rsSubCategories);

mysql_free_result($rsLoggedIn);

mysql_free_result($rsThisManufacturer);

if(is_resource($rsIndexManufacturers)) {
	mysql_free_result($rsIndexManufacturers);

mysql_free_result($rsHeaderPromo);

mysql_free_result($rsPreferences);
}

if(is_resource($rsProductPrefs)) {
	mysql_free_result($rsProductPrefs);
}
mysql_free_result($rsThisRegion);

?>
