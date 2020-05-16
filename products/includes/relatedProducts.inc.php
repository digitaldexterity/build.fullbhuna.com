<?php if($row_rsProductPrefs['relatedproducts']>0) {   // requires /includes/functions.inc.php on parent page
 
 if(!isset($row_rsProductPrefs['relatedcategoryID']) || $row_rsProductPrefs['relatedcategoryID'] == 0) { // this category
	 $categoryID = isset($row_rsProduct['productcategoryID']) ? $row_rsProduct['productcategoryID'] : (isset($categoryID) ? $categoryID: -1);
 } else {
	 $categoryID = $row_rsProductPrefs['relatedcategoryID'];
 }
 



// get from either product page or basket
$productID = isset($row_rsProduct['ID']) ? $row_rsProduct['ID'] : (isset($productID) ? $productID : 0);
$regionID = isset($regionID) ? $regionID : 1;
$manufacturerID = (isset($row_rsProductPrefs['relatedmanufacturerID']) && $row_rsProductPrefs['relatedmanufacturerID']>0) ?  $row_rsProductPrefs['relatedmanufacturerID'] : (isset($_REQUEST['manufacturerID']) ? $_REQUEST['manufacturerID'] : 0);
$tags = isset($_REQUEST['tag']) ? $_REQUEST['tag'] : array();
$versions = isset($_REQUEST['version']) ? $_REQUEST['version'] : array();
$finishes = isset($_REQUEST['finish']) ? $_REQUEST['finish'] : array();
$prices = isset($_REQUEST['price']) ? $_REQUEST['price'] : array();
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : (isset($row_rsProductPrefs['defaultsort']) ? $row_rsProductPrefs['defaultsort']   : "ordernum");
$page = isset($_GET['pageNum_rsProduct']) ? $_GET['pageNum_rsProduct']+1 : 1;
 ?>
  <br />
  <div id="relatedproductsmenu" class="clearfix">
    <h2><?php echo  $row_rsProductPrefs['relatedtext']; // product or basket ?></h2>
    
    <?php
	
	



echo getProducts($regionID, "", $categoryID, $manufacturerID, $row_rsProductPrefs['relatedproducts'], 1, false, $productID, "", $tags, $versions , $finishes);
	?>
  
  </div>
  <?php 
 }?>