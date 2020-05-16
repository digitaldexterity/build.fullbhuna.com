<?php header ("Content-Type:text/xml");  require_once('../Connections/aquiescedb.php'); ?><?php require_once('includes/productFunctions.inc.php'); ?><?php require_once('../core/includes/framework.inc.php'); ?>
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


$varRegionID_rsProducts = "-1";
if (isset($regionID)) {
  $varRegionID_rsProducts = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsProducts = sprintf("SELECT product.ID, product.longID, product.productcategoryID, productcategory.longID AS categorylongID, product.datetimecreated, product.regionID, productinregion.regionID AS inregionID FROM product LEFT JOIN productcategory ON (product.productcategoryID = productcategory.ID) LEFT JOIN productcategory AS parentcategory ON (productcategory.subcatofID = parentcategory.ID)  LEFT JOIN productinregion ON (product.ID = productinregion.productID) WHERE product.statusID = 1 AND (parentcategory.statusID IS NULL OR parentcategory.statusID=1) AND productcategory.statusID = 1 AND (productcategory.accesslevel IS NULL OR productcategory.accesslevel =0)  AND ((productinregion.regionID = %s) OR (productinregion.regionID  IS NULL AND product.regionID = %s)) GROUP BY product.ID ", GetSQLValueString($varRegionID_rsProducts, "int"),GetSQLValueString($varRegionID_rsProducts, "int"));
$rsProducts = mysql_query($query_rsProducts, $aquiescedb) or die(mysql_error());
$row_rsProducts = mysql_fetch_assoc($rsProducts);
$totalRows_rsProducts = mysql_num_rows($rsProducts);



echo '<?xml version="1.0" encoding="UTF-8"?>
'; ?>
<urlset xmlns="http://www.google.com/schemas/sitemap/0.84"

xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"

xsi:schemaLocation="http://www.google.com/schemas/sitemap/0.84 http://www.google.com/schemas/sitemap/0.84/sitemap.xsd">
<!-- Created by Full Bhuna CMS version 1.0 

<?php //echo $query_rsProducts; ?>

-->
  <?php if($totalRows_rsProducts>0) { do { $url = getProtocol()."://". $_SERVER['HTTP_HOST'];
			$url .= productLink($row_rsProducts['ID'], $row_rsProducts['longID'], $row_rsProducts['productcategoryID'], $row_rsProducts['categorylongID']) ;
			$lastmod = isset($row_rsProducts['modifieddatetime']) ? $row_rsProducts['modifieddatetime'] : $row_rsProducts['datetimecreated'];
			
			?>
  <url>
 <loc><?php echo htmlentities($url); ?></loc>
  <lastmod><?php echo date('c', strtotime($lastmod)); ?></lastmod>
  <changefreq>weekly</changefreq>
  <priority>0.5</priority>
</url>

   
    <?php } while ($row_rsProducts = mysql_fetch_assoc($rsProducts));  }?>
</urlset>
<?php
mysql_free_result($rsProducts);
?>
