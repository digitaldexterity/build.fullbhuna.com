<?php if(@$row_rsProductPrefs['alsobought']>0) {  
 // requires /includes/productFunctions.inc.php on parent page

$varThisOrderCode =  isset($_SESSION['VendorTxCode']) ? $_SESSION['VendorTxCode'] : "-1";
$varProductID = intval($_GET['productID']);
$select = "SELECT DISTINCT product.ID, product.title, COUNT(product.ID) as rank,
product.longID, product.imageURL, productcategory.longID AS categorylongID, product.productcategoryID , product.price
FROM product LEFT JOIN productcategory ON (product.productcategoryID = productcategory.ID) JOIN productorderproducts ul
ON product.id = ul.ProductId
WHERE product.id<> ".$varProductID." AND
  ul.VendorTxCode IN
  (SELECT DISTINCT productorders.VendorTxCode FROM productorders
   JOIN productorderproducts ls ON productorders.VendorTxCode = ls.VendorTxCode
   WHERE ls.ProductId = ".$varProductID."
      AND productorders.VendorTxCode<> '".$varThisOrderCode."')
GROUP BY product.ID
ORDER BY rank DESC, product.title LIMIT 4";

$result = mysql_query($select, $aquiescedb) or die(mysql_error()).": ".$select;


if ( mysql_num_rows($result) > 0) { // Show if recordset not empty ?>
  <div id="alsoboughtsmenu"  class="clearfix">
    <h2><?php echo  $row_rsProductPrefs['alsoboughttext'];  ?></h2>
   <?php echo  displayProducts($result); ?>
  </div>
  <?php } // Show if recordset not empty 

} ?>