<?php require_once('../Connections/aquiescedb.php'); ?>
<?php require_once('includes/productHeader.inc.php'); ?>
<?php require_once('../core/includes/framework.inc.php'); ?>
<?php require_once('includes/productFunctions.inc.php'); ?>
<?php require_once('includes/products.inc.php'); ?>
<?php require_once('../articles/includes/functions.inc.php'); ?>
<?php



$_GET['manufacturerID'] = isset($_GET['manufacturerID']) ? $_GET['manufacturerID'] : 0;

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

global $row_rsProduct;

$varProductID_rsProduct = "-1";
if (isset($_GET['productID'])) {
  $varProductID_rsProduct = $_GET['productID'];
}
$varRegionID_rsProduct = "1";
if (isset($regionID)) {
  $varRegionID_rsProduct = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsProduct = sprintf("SELECT product.*, product.statusID =1 AS active, productgallery.galleryID, productmanufacturer.manufacturersale, productcategory.imageURL3, productcategory.nextproductsku, productcategory.title AS categoryname, productcategory.nopricebuy, productcategory.nopricetext, nextproduct.ID AS nextproductID, nextproduct.title AS nextproducttitle, productmanufacturer.manufacturername, parentmanufacturer.manufacturername AS parentmanufacturername, productvatrate.ratepercent, productmanufacturer.manufacturershipping  FROM product LEFT JOIN productgallery ON (product.ID = productgallery.productID) LEFT JOIN productmanufacturer ON (product.manufacturerID = productmanufacturer.ID) LEFT JOIN productmanufacturer AS parentmanufacturer ON (productmanufacturer.subsidiaryofID = parentmanufacturer.ID) LEFT JOIN productcategory ON (product.productcategoryID = productcategory.ID) LEFT JOIN product AS nextproduct ON (productcategory.nextproductsku = nextproduct.sku) LEFT JOIN productvatrate ON (product.vattype = productvatrate.ID) LEFT JOIN productinregion ON (productinregion.productID = product.ID) WHERE (product.ID = %s OR product.longID = %s)  AND ((productinregion.regionID IS NULL AND %s = 1) OR productinregion.regionID = %s) ORDER BY active DESC", GetSQLValueString($varProductID_rsProduct, "text"),GetSQLValueString($varProductID_rsProduct, "text"),GetSQLValueString($varRegionID_rsProduct, "int"),GetSQLValueString($varRegionID_rsProduct, "int"));
$rsProduct = mysql_query($query_rsProduct, $aquiescedb) or die(mysql_error());
$row_rsProduct = mysql_fetch_assoc($rsProduct);
$totalRows_rsProduct = mysql_num_rows($rsProduct);


$varThisRegion_rsTopCategories = "1";
if (isset($regionID)) {
  $varThisRegion_rsTopCategories = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsTopCategories = sprintf("SELECT productcategory.ID, productcategory.longID, productcategory.title FROM productcategory WHERE productcategory.subcatofID = 0 AND productcategory.statusID = 1 AND (productcategory.regionID = 0 OR productcategory.regionID = %s)", GetSQLValueString($varThisRegion_rsTopCategories, "int"));
$rsTopCategories = mysql_query($query_rsTopCategories, $aquiescedb) or die(mysql_error());
$row_rsTopCategories = mysql_fetch_assoc($rsTopCategories);
$totalRows_rsTopCategories = mysql_num_rows($rsTopCategories);

$varCategoryID_rsLevelCategories = "-1";
if (isset($row_rsProduct['productcategoryID'])) {
  $varCategoryID_rsLevelCategories = $row_rsProduct['productcategoryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLevelCategories = sprintf("SELECT productcategory.ID, productcategory.longID, productcategory.title FROM productcategory LEFT JOIN productcategory AS thiscategory ON (productcategory.subcatofID = thiscategory.subcatofID) WHERE thiscategory.ID = %s AND productcategory.subcatofID !=0", GetSQLValueString($varCategoryID_rsLevelCategories, "int"));
$rsLevelCategories = mysql_query($query_rsLevelCategories, $aquiescedb) or die(mysql_error());
$row_rsLevelCategories = mysql_fetch_assoc($rsLevelCategories);
$totalRows_rsLevelCategories = mysql_num_rows($rsLevelCategories);

$thiscategoryID = (isset($_GET['categoryID']) && $_GET['categoryID']!="") ? $_GET['categoryID'] : $row_rsProduct['productcategoryID'];

$varCategoryID_rsThisCategory = "-1";
if (isset($thiscategoryID)) {
  $varCategoryID_rsThisCategory = $thiscategoryID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisCategory = sprintf("SELECT productcategory.*,parentcategory.ID AS parentID, parentcategory.longID AS parentlongID, parentcategory.title AS parenttitle, productcategory.imageURL, parentcategory.categorysale AS parentsale, parentcategory.statusID AS parentstatusID FROM productcategory LEFT JOIN productcategory AS parentcategory ON (productcategory.subcatofID = parentcategory.ID) WHERE (productcategory.ID = %s OR  productcategory.longID = %s)", GetSQLValueString($varCategoryID_rsThisCategory, "text"),GetSQLValueString($varCategoryID_rsThisCategory, "text"));
$rsThisCategory = mysql_query($query_rsThisCategory, $aquiescedb) or die(mysql_error());
$row_rsThisCategory = mysql_fetch_assoc($rsThisCategory);
$totalRows_rsThisCategory = mysql_num_rows($rsThisCategory);

$varRegionID_rsPromo = "1";
if (isset($regionID)) {
  $varRegionID_rsPromo = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPromo = sprintf("SELECT promotitle, imageURL, linkURL FROM productpromo WHERE promocode IS NULL AND statusID = 1 AND imageURL IS NOT NULL AND  (startdatetime IS NULL OR startdatetime < '".date('Y-m-d H:i:s')."') AND (enddatetime IS NULL OR enddatetime > '".date('Y-m-d H:i:s')."') AND (regionID = 0  OR regionID = %s) ORDER BY RAND()  LIMIT 1", GetSQLValueString($varRegionID_rsPromo, "int"));
$rsPromo = mysql_query($query_rsPromo, $aquiescedb) or die(mysql_error());
$row_rsPromo = mysql_fetch_assoc($rsPromo);
$totalRows_rsPromo = mysql_num_rows($rsPromo);



$varProductID_rsFinishes = "-1";
if (isset($row_rsProduct['ID'])) {
  $varProductID_rsFinishes = $row_rsProduct['ID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsFinishes = sprintf("SELECT productfinish.ID, productfinish.finishname, productfinish.imageURL FROM product LEFT JOIN productoptions ON (product.ID = productoptions.productID) LEFT JOIN productfinish ON (productoptions.finishID = productfinish.ID) WHERE product.ID = %s AND productfinish.statusID = 1   GROUP BY productfinish.ID", GetSQLValueString($varProductID_rsFinishes, "int"));
$rsFinishes = mysql_query($query_rsFinishes, $aquiescedb) or die(mysql_error());
$row_rsFinishes = mysql_fetch_assoc($rsFinishes);
$totalRows_rsFinishes = mysql_num_rows($rsFinishes);

$varProductID_rsProductTags = "-1";
if (isset($row_rsProduct['ID'])) {
  $varProductID_rsProductTags = $row_rsProduct['ID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsProductTags = sprintf("SELECT producttag.ID, producttag.tagname, producttag.taggroupID, producttaggroup.taggroupname, producttagged.productID FROM producttag LEFT JOIN  producttaggroup ON (producttag.taggroupID = producttaggroup.ID) LEFT JOIN producttagged ON (producttagged.tagID = producttag.ID) WHERE producttagged.productID = %s ORDER BY producttaggroup.ordernum, producttaggroup.ID,producttag.ordernum", GetSQLValueString($varProductID_rsProductTags, "int"));
$rsProductTags = mysql_query($query_rsProductTags, $aquiescedb) or die(mysql_error());
$row_rsProductTags = mysql_fetch_assoc($rsProductTags);
$totalRows_rsProductTags = mysql_num_rows($rsProductTags);


if ( ($totalRows_rsProduct == 0 || (isset($row_rsThisCategory['statusID']) && $row_rsThisCategory['statusID']!=1) || (isset($row_rsThisCategory['parentstatusID']) && $row_rsThisCategory['parentstatusID']!=1) || $row_rsProduct['statusID']!=1) && !isset($_GET['preview'])) {
		
		
		
		$msg = 
		"Sorry, the product you were trying to view may have moved or is no longer available. Please browse the shop to find what you are looking for...";
		// Aim to get this to go to actual category page if there is one otherwise home page
		//correct PHP for 404 - QUERY STRINGS NOT WORK FOR INCLUDES
		$url =  (isset($row_rsThisCategory['statusID']) && $row_rsThisCategory['statusID']==1) ? "products/index.php" : "index.php";
		$categoryID = $row_rsThisCategory['ID'];
		
		http_response_code(404);
		require(SITE_ROOT.$url); // provide your own HTML for the error page
		die();
			

}


if(isset($row_rsProduct['custompageURL']) && trim($row_rsProduct['custompageURL']) !="" && $row_rsProduct['statusID'] != 2) {
	$url = $row_rsProduct['custompageURL'];
	if($row_rsProduct['redirect301']==1) {
		header( "HTTP/1.1 301 Moved Permanently" ); 
		header( "Status: 301 Moved Permanently" );
	}	
	header("location: ".$url); exit;
}

if(defined("CUSTOM_PRODUCT_PAGE")) {
	$url = CUSTOM_PRODUCT_PAGE."?productID=".$row_rsProduct['ID'];
	header("location: ".$url); exit;
}



?>
<?php 
//FIX NON-FRIENDLY URLs
$canonicalURL = productLink($row_rsProduct['ID'], $row_rsProduct['longID'], $row_rsThisCategory['ID'], $row_rsThisCategory['longID']);
if((defined("MOD_REWRITE") || isset($_SERVER['HTTP_MOD_REWRITE']))  && strpos($_SERVER['REQUEST_URI'],"productID=")!==false) {	
	if($canonicalURL != $_SERVER['REQUEST_URI']) {
	//	header( "HTTP/1.1 301 Moved Permanently" ); 
		//header( "Status: 301 Moved Permanently" );
		//header( "Location: ".$canonicalURL); exit;
	}
}

$productTitle = productTitle($row_rsProduct);
$row_rsProduct['description'] = articleMerge($row_rsProduct['description']);

$defaultImage = isset($row_rsProductPrefs['defaultImageURL']) ? "/Uploads/".$row_rsProductPrefs['imagesize_product'].$row_rsProductPrefs['defaultImageURL'] : "/products/images/".$row_rsProductPrefs['imagesize_product']."no_image.gif";
$defaultRelatedImage = isset($row_rsProductPrefs['defaultImageURL']) ? "/Uploads/".$row_rsProductPrefs['imagesize_related'].$row_rsProductPrefs['defaultImageURL'] : "/products/images/".$row_rsProductPrefs['imagesize_related']."no_image.gif";
?>
<?php require_once('includes/review.head.inc.php'); ?>
<?php if(isset($row_rsProduct['seotitle'])) {
	$pageTitle = $row_rsProduct['seotitle'];
} else { $pageTitle = strip_tags($productTitle); $pageTitle .= isset($row_rsProduct['sku']) ?  " - ".$row_rsProduct['sku'] : ""; $pageTitle .= isset($row_rsThisCategory['title']) ? " - ".$row_rsThisCategory['title'] : "";   } 

$body_class= isset($body_class) ?  $body_class." productPage " : " productPage ";
$body_class .= isset($row_rsThisCategory['parentID']) ? " productparentcategory".$row_rsThisCategory['parentID']." " : "";
$body_class .= " productcategory".$row_rsThisCategory['ID']." ";
$body_class .=  isset($row_rsProduct['manufacturerID']) ? " manufacturer".$row_rsProduct['manufacturerID']." " : "";




$isProductPage = true;

 ?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!DOCTYPE html>
<html class="" lang="en"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php  echo $pageTitle." | ".$site_name; ?>
</title>
<!-- InstanceEndEditable -->
<!--[if IE]><![endif]-->
<?php require_once('../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script src="/products/scripts/productFunctions.js?v=2"></script>
<script src="/core/scripts/ratings/script.js"></script>
<link href="/core/scripts/ratings/stars.css" rel="stylesheet"  />
<link href="/products/css/defaultProducts.css" rel="stylesheet"   />
<?php if($row_rsThisCategory['noindex']==1) {
	echo "<meta name=\"robots\" content=\"noindex, nofollow\">";
} else { ?>
<meta name="keywords" content="<?php echo $row_rsProduct['metakeywords']; ?>" />
<link rel="canonical" href="<?php  $canonicalURL = getProtocol()."://".$_SERVER['HTTP_HOST'].$canonicalURL ; $canonicalURL = htmlentities($canonicalURL, ENT_COMPAT, "UTF-8"); echo $canonicalURL;  $metadescription = strlen($row_rsProduct['metadescription'])>100 ? htmlentities($row_rsProduct['metadescription'], ENT_COMPAT, "UTF-8") : ((strlen($row_rsProduct['h2'])>100)  ? htmlentities($row_rsProduct['h2'], ENT_COMPAT, "UTF-8") : htmlentities($row_rsProduct['description'], ENT_COMPAT, "UTF-8")); ?>" />
<?php if(isset($_GET['filter'])) {  //avoid Google duplicate page ?>
<meta name="robots" content="noindex">
<?php } ?>
<?php $imageURL = getProtocol()."://". $_SERVER['HTTP_HOST'].getImageURL($row_rsProduct['imageURL'],$row_rsProductPrefs['imagesize_product']); ?>
<!-- Schema.org markup for Google+ -->
<meta itemprop="name" content="<?php echo htmlentities($pageTitle, ENT_COMPAT, "UTF-8"); ?>">
<meta itemprop="description" name="description" content="<?php echo $metadescription; ?>">
<meta itemprop="image" content="<?php echo $imageURL; ?>">
<!-- Twitter Card data -->
<meta name="twitter:card" content="summary">
<!--<meta name="twitter:site" content="@publisher_handle">
<meta name="twitter:creator" content="@author_handle">-->
<meta name="twitter:title" content="<?php echo htmlentities($pageTitle, ENT_COMPAT, "UTF-8"); ?>">
<meta name="twitter:description" content="<?php echo $metadescription; ?>">
<meta name="twitter:image" content="<?php echo $imageURL; ?>">
<!-- Open Graph data -->
<meta property="og:url" content="<?php echo $canonicalURL; ?>" />
<meta property="og:site_name" content="<?php echo htmlentities($site_name, ENT_COMPAT, "UTF-8"); ?>"/>
<meta property="og:title" content="<?php echo htmlentities($pageTitle, ENT_COMPAT, "UTF-8"); ?>" />
<meta property="og:description" content="<?php echo $metadescription; ?>" />
<meta property="og:price:amount" content="<?php echo $row_rsProduct['price']; ?>" />
<meta property="og:price:currency" content="<?php echo $currency; ?>" />
<meta property="og:type" content="article" />
<!-- TBI add facebook profiles to CMS
<meta property="article:author" content="https://www.facebook.com/fareedzakaria" />
<meta property="article:publisher" content="https://www.facebook.com/cnn" />-->
<meta property="og:image" content="<?php echo $imageURL; ?>"/>
<?php } ?>
<link href="/photos/css/defaultGallery.css" rel="stylesheet"  />
<link href="/core/seo/css/defaultShare.css" rel="stylesheet"  />
<link href="/documents/css/documentsDefault.css" rel="stylesheet" >
<style>
<!--
<?php if(!isset($row_rsProduct['box_height'])) {
 echo ".productData .height { display: none; } \n";
}
if(!isset($row_rsProduct['box_length'])) {
 echo ".productData .length { display: none; } \n";
}
if(!isset($row_rsProduct['box_width'])) {
 echo ".productData .width { display: none; } \n";
}
 if ($row_rsProductPrefs['showcondition']==0) {
 echo ".itemCondition { display: none; } \n";
}
 ?>
-->
</style>
<!-- InstanceEndEditable -->
</head>
<body id="OffTemplate" class="bootstrap <?php echo $body_class;  ?>">
<?php require_once('../local/includes/header.inc.php'); ?>
<main id="content"><!-- InstanceBeginEditable name="Body" -->
<section>
  <div id="productPage" class="container clearfix <?php echo $row_rsProduct['class']; ?> instock<?php echo $row_rsProduct['instock']; ?> product<?php echo $row_rsProduct['ID']; ?> parentcategory<?php echo $row_rsThisCategory['parentID']; ?> category<?php echo $row_rsThisCategory['ID']; echo ($row_rsProduct['saleitem']==1) ? " sale productsale" : ""; echo ($row_rsThisCategory['categorysale']==1) ? " sale categorysale" : ""; echo ($row_rsThisCategory['parentsale']==1) ? " sale categorysale" : ""; if($totalRows_rsOtherCategories>0) { do { echo " category".$row_rsOtherCategories['ID']; echo ($row_rsOtherCategories['categorysale']==1) ? " sale categorysale": ""; } while ($row_rsOtherCategories = mysql_fetch_assoc($rsOtherCategories)); mysql_data_seek($rsOtherCategories,0); } echo ($row_rsProduct['manufacturersale']==1) ? " sale manufacturersale" : ""; 
  if(mysql_num_rows($rsProductTags)>0) { 
  	do { 
  		echo " tag-".$row_rsProductTags['ID']." "; 
  	} while($row_rsProductTags = mysql_fetch_assoc($rsProductTags)); mysql_data_seek($rsProductTags,0);$row_rsProductTags = mysql_fetch_assoc($rsProductTags);  
}  ?>"  >
  <!-- ISEARCH_END_INDEX -->
    <div class="crumbs">
    <div><span class="you_are_in">You are in: </span>
    
    <ol itemscope itemtype="http://schema.org/BreadcrumbList">
            <li itemprop="itemListElement" itemscope
      itemtype="http://schema.org/ListItem" class="home"><a itemprop="item" href="/"><span itemprop="name">Home</span></a>
              <meta itemprop="position" content="<?php $position=1; echo $position; ?>" />
            </li>
            
            <li itemprop="itemListElement" itemscope
      itemtype="http://schema.org/ListItem"  class="productshome" >
     
            <a itemprop="item"  href="/products/"><span itemprop="name"><?php echo isset($row_rsProductPrefs['shopTitle']) ? $row_rsProductPrefs['shopTitle'] : "Shop";  ?></span></a>
            <meta itemprop="position" content="<?php  echo $position++; ?>" />
            </li>
            
      <?php if (isset($row_rsThisCategory['parenttitle'])) { ?>
      
      
         <li itemprop="itemListElement" itemscope
      itemtype="http://schema.org/ListItem" ><a itemprop="item"  href="<?php echo productLink(0, "", $row_rsThisCategory['parentID'], $row_rsThisCategory['parentlongID']); ?>"><span itemprop="name"><?php echo $row_rsThisCategory['parenttitle']; ?></span></a>
      <meta itemprop="position" content="<?php  echo $position++; ?>" /></li>
      
      <?php } if (isset($row_rsThisCategory['title'])) { ?>
         <li itemprop="itemListElement" itemscope
      itemtype="http://schema.org/ListItem" >
        <a itemprop="item"  href="<?php $catLink = productLink(0, "", $row_rsThisCategory['ID'], $row_rsThisCategory['longID']);  echo $catLink; ?>"><span itemprop="name"><?php echo $row_rsThisCategory['title']; ?></span></a>
        <meta itemprop="position" content="<?php echo $position++; ?>" /></li>
        <?php } ?>
         <li  >
      <?php echo $productTitle; ?></li></ol></div></div><!-- end breadcrumbs -->
    <?php require_once(SITE_ROOT.'core/includes/alert.inc.php'); ?>
    <!-- ISEARCH_BEGIN_INDEX -->
    <?php 
if($row_rsProductPrefs['productpagetemplateID']>0) {
	echo articleMerge($row_rsProductPrefs['body']);
} else {
	require_once('includes/classic.inc.php');
	
	
} ?>
  </div><!-- end page -->
</section>
<!-- InstanceEndEditable --></main>
<?php require_once('../local/includes/footer.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsProduct);



mysql_free_result($rsThisRegion);

mysql_free_result($rsFinishes);




mysql_free_result($rsProductTags);


mysql_free_result($rsPromo);

mysql_free_result($rsThisCategory);


?>
